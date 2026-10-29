<?php

require_once(__DIR__ . '/../config/connect.php');

class ProgressaoAnoLetivoModel extends connect
{
    public function executarSeNecessario(): void
    {
        $anoDestino = (int) date('Y');
        $anoOrigem = $anoDestino - 1;
        $lockName = 'system_pdt_progressao_anual';
        $lockAdquirido = false;

        try {
            $lock = $this->conn->prepare('SELECT GET_LOCK(:lock_name, 10)');
            $lock->execute([':lock_name' => $lockName]);
            $lockAdquirido = (int) $lock->fetchColumn() === 1;
            if (!$lockAdquirido) {
                throw new RuntimeException('Não foi possível obter o bloqueio para a progressão anual.');
            }

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'SELECT tipo_processamento
                 FROM progressao_ano_letivo
                 WHERE ano_destino = :ano_destino
                 LIMIT 1'
            );
            $stmt->execute([':ano_destino' => $anoDestino]);
            if ($stmt->fetchColumn()) {
                $this->conn->commit();
                return;
            }

            $stmt = $this->conn->prepare(
                'SELECT id_turma, id_curso, nome_turma, serie_turma, sala_turma,
                        descricao_turma, id_professor
                 FROM turmas
                 WHERE ano_letivo = :ano_origem
                   AND serie_turma IN (1, 2, 3)
                 ORDER BY serie_turma, id_turma
                 FOR UPDATE'
            );
            $stmt->execute([':ano_origem' => $anoOrigem]);
            $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$turmas) {
                $this->conn->commit();
                return;
            }

            $totalPromovidos = 0;
            $totalConcluintes = 0;
            $criarTurma = $this->conn->prepare(
                'INSERT INTO turmas
                    (id_curso, nome_turma, serie_turma, sala_turma, ano_letivo,
                     descricao_turma, id_professor, turma_origem_id)
                 VALUES
                    (:id_curso, :nome_turma, :serie_turma, :sala_turma, :ano_letivo,
                     :descricao_turma, :id_professor, :turma_origem_id)'
            );
            $buscarTurmaDestino = $this->conn->prepare(
                'SELECT id_turma
                 FROM turmas
                 WHERE turma_origem_id = :turma_origem_id
                 LIMIT 1
                 FOR UPDATE'
            );
            $moverAlunos = $this->conn->prepare(
                "UPDATE alunos
                 SET id_turma = :id_turma_destino
                 WHERE id_turma = :id_turma_origem
                   AND status_aluno = 'ativo'"
            );
            $registrarHistorico = $this->conn->prepare(
                'INSERT IGNORE INTO aluno_turma_historico (id_aluno, id_turma)
                 SELECT id_aluno, :id_turma_destino
                 FROM alunos
                 WHERE id_turma = :id_turma_origem
                   AND status_aluno = \'ativo\''
            );
            $concluirAlunos = $this->conn->prepare(
                "UPDATE alunos
                 SET status_aluno = 'concluinte',
                     ano_conclusao = :ano_conclusao
                 WHERE id_turma = :id_turma
                   AND status_aluno = 'ativo'"
            );

            foreach ($turmas as $turma) {
                $idTurmaOrigem = (int) $turma['id_turma'];
                $serie = (int) $turma['serie_turma'];

                if ($serie === 3) {
                    $concluirAlunos->execute([
                        ':ano_conclusao' => $anoOrigem,
                        ':id_turma' => $idTurmaOrigem
                    ]);
                    $totalConcluintes += $concluirAlunos->rowCount();
                    continue;
                }

                $buscarTurmaDestino->execute([':turma_origem_id' => $idTurmaOrigem]);
                $idTurmaDestino = $buscarTurmaDestino->fetchColumn();

                if (!$idTurmaDestino) {
                    $criarTurma->execute([
                        ':id_curso' => (int) $turma['id_curso'],
                        ':nome_turma' => $turma['nome_turma'],
                        ':serie_turma' => $serie + 1,
                        ':sala_turma' => $turma['sala_turma'],
                        ':ano_letivo' => $anoDestino,
                        ':descricao_turma' => $turma['descricao_turma'],
                        ':id_professor' => (int) $turma['id_professor'],
                        ':turma_origem_id' => $idTurmaOrigem
                    ]);
                    $idTurmaDestino = (int) $this->conn->lastInsertId();
                } else {
                    $validarTurmaDestino = $this->conn->prepare(
                        'SELECT id_curso, serie_turma, sala_turma, ano_letivo, id_professor
                         FROM turmas
                         WHERE id_turma = :id_turma'
                    );
                    $validarTurmaDestino->execute([':id_turma' => (int) $idTurmaDestino]);
                    $destino = $validarTurmaDestino->fetch(PDO::FETCH_ASSOC);
                    if (
                        !$destino
                        || (int) $destino['id_curso'] !== (int) $turma['id_curso']
                        || (int) $destino['serie_turma'] !== $serie + 1
                        || (string) $destino['sala_turma'] !== (string) $turma['sala_turma']
                        || (int) $destino['ano_letivo'] !== $anoDestino
                        || (int) $destino['id_professor'] !== (int) $turma['id_professor']
                    ) {
                        throw new RuntimeException('A turma de destino existente não corresponde à turma de origem.');
                    }
                }

                $registrarHistorico->execute([
                    ':id_turma_destino' => (int) $idTurmaDestino,
                    ':id_turma_origem' => $idTurmaOrigem
                ]);
                $moverAlunos->execute([
                    ':id_turma_destino' => (int) $idTurmaDestino,
                    ':id_turma_origem' => $idTurmaOrigem
                ]);
                $totalPromovidos += $moverAlunos->rowCount();
            }

            $stmt = $this->conn->prepare(
                "UPDATE usuarios u
                 SET u.atribuicao = NULL
                 WHERE u.atribuicao = 'pdt'
                   AND NOT EXISTS (
                       SELECT 1
                       FROM turmas t
                       WHERE t.id_professor = u.id
                         AND t.ano_letivo = :ano_destino
                   )"
            );
            $stmt->execute([':ano_destino' => $anoDestino]);

            $stmt = $this->conn->prepare(
                "INSERT INTO progressao_ano_letivo
                    (ano_destino, ano_origem, tipo_processamento, total_promovidos, total_concluintes)
                 VALUES (:ano_destino, :ano_origem, 'concluido', :total_promovidos, :total_concluintes)"
            );
            $stmt->execute([
                ':ano_destino' => $anoDestino,
                ':ano_origem' => $anoOrigem,
                ':total_promovidos' => $totalPromovidos,
                ':total_concluintes' => $totalConcluintes
            ]);

            $this->conn->commit();
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('Erro na progressão anual de turmas: ' . $e->getMessage());
            throw new RuntimeException('Não foi possível concluir a progressão anual das turmas.', 0, $e);
        } finally {
            if ($lockAdquirido) {
                $release = $this->conn->prepare('SELECT RELEASE_LOCK(:lock_name)');
                $release->execute([':lock_name' => $lockName]);
            }
        }
    }
}
