<?php

require_once(__DIR__ . '/../config/connect.php');
require_once(__DIR__ . '/classificacao_desempenho.php');

class AlunoModel extends connect
{
    function __construct()
    {
        parent::__construct();
    }

    public function listarPorTurma($id_turma = null)
    {
        try {
            if ($id_turma !== null) {
                // detectar nome da coluna de turma (turma_id ou id_turma)
                $col = null;
                $q = $this->conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'alunos' AND COLUMN_NAME IN ('turma_id','id_turma') LIMIT 1");
                if ($q->execute()) {
                    $res = $q->fetch(PDO::FETCH_ASSOC);
                    if ($res && isset($res['COLUMN_NAME'])) $col = $res['COLUMN_NAME'];
                }

                if ($col) {
                    $sql = "SELECT * FROM alunos WHERE " . $col . " = :id_turma AND status_aluno = 'ativo' ORDER BY data_insercao DESC";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
                    $ok = $stmt->execute();
                    if ($ok) return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                // se não existir coluna de turma, retorna vazio
                return [];
            }

            // fallback: retorna todos os alunos
            $stmt = $this->conn->prepare("SELECT * FROM alunos WHERE status_aluno = 'ativo' ORDER BY data_insercao DESC");
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return [];
        } catch (PDOException $e) {
            error_log('Erro listarPorTurma: ' . $e->getMessage());
            return [];
        }
    }

    public function cadastrarAluno($nome, $matricula, $email, $pcd = 'nao', $rendimento = 0.00, $turma_id = null)
    {
        try {

            // montar colunas básicas
            $cols = ['nome', 'matricula', 'email', 'pcd', 'rendimento'];
            $place = [':nome', ':matricula', ':email', ':pcd', ':rendimento'];
            $params = [
                ':nome' => $nome,
                ':matricula' => $matricula,
                ':email' => $email,
                ':pcd' => $pcd,
                ':rendimento' => $rendimento
            ];

            // detectar coluna de ligação com turma (se existir) e só usar se presente
            if ($turma_id !== null) {
                $q = $this->conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'alunos' AND COLUMN_NAME IN ('turma_id','id_turma') LIMIT 1");
                if ($q->execute()) {
                    $res = $q->fetch(PDO::FETCH_ASSOC);
                    if ($res && isset($res['COLUMN_NAME'])) {
                        $colName = $res['COLUMN_NAME'];
                        $cols[] = $colName;
                        $place[] = ':turma_id';
                        $params[':turma_id'] = $turma_id;
                    }
                }
            }

            $sql = "INSERT INTO alunos (" . implode(',', $cols) . ") VALUES (" . implode(',', $place) . ")";

            $stmt = $this->conn->prepare($sql);
            $this->conn->beginTransaction();
            $ok = $stmt->execute($params);

            if ($ok) {
                $idAluno = (int) $this->conn->lastInsertId();
                if ($turma_id !== null) {
                    $historico = $this->conn->prepare(
                        'INSERT INTO aluno_turma_historico (id_aluno, id_turma)
                         VALUES (:id_aluno, :id_turma)'
                    );
                    $historico->execute([
                        ':id_aluno' => $idAluno,
                        ':id_turma' => (int) $turma_id
                    ]);
                }
                $this->conn->commit();
                return ['success' => true, 'id' => $idAluno];
            }

            $this->conn->rollBack();
            // Tratamento de erros específicos
            $err = $stmt->errorInfo();
            $errorMsg = $err[2] ?? 'Erro desconhecido';
            
            // Detectar erros de constraint
            if (strpos($errorMsg, 'Duplicate entry') !== false) {
                if (strpos($errorMsg, 'matricula') !== false) {
                    $errorMsg = 'Matrícula já existe no sistema';
                } else if (strpos($errorMsg, 'email') !== false) {
                    $errorMsg = 'Email já está registrado no sistema';
                }
            }
            
            error_log('Erro ao cadastrar aluno: ' . $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $errorMsg = $e->getMessage();
            
            // Tratamento de erros específicos
            if (strpos($errorMsg, 'Duplicate entry') !== false) {
                if (strpos($errorMsg, 'matricula') !== false) {
                    $errorMsg = 'Matrícula já existe no sistema';
                } else if (strpos($errorMsg, 'email') !== false) {
                    $errorMsg = 'Email já está registrado no sistema';
                }
            } else if (strpos($errorMsg, 'FOREIGN KEY') !== false) {
                $errorMsg = 'Turma não existe ou foi removida';
            }
            
            error_log('Erro cadastrarAluno: ' . $e->getMessage());
            return ['success' => false, 'error' => $errorMsg];
        }
    }

    public function excluirAluno($id_aluno)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM alunos WHERE id_aluno = :id_aluno");
            $stmt->bindValue(':id_aluno', $id_aluno, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erro excluirAluno: ' . $e->getMessage());
            return false;
        }
    }

    public function buscarAlunoPorId($id_aluno)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM alunos WHERE id_aluno = :id_aluno LIMIT 1");
            $stmt->bindValue(':id_aluno', $id_aluno, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            }
            return [];
        } catch (PDOException $e) {
            error_log('Erro buscarAlunoPorId: ' . $e->getMessage());
            return [];
        }
    }

    public function atualizarAluno($id_aluno, $nome, $matricula, $email, $pcd = 'nao', $rendimento = 0.00)
    {
        try {
            $sql = "UPDATE alunos SET nome = :nome, matricula = :matricula, email = :email, pcd = :pcd, rendimento = :rendimento WHERE id_aluno = :id_aluno";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_aluno', $id_aluno, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':matricula', $matricula);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':pcd', $pcd);
            $stmt->bindValue(':rendimento', $rendimento);
            
            $ok = $stmt->execute();
            
            if ($ok) {
                return ['success' => true];
            }
            
            $err = $stmt->errorInfo();
            return ['success' => false, 'error' => ($err[2] ?? 'Erro desconhecido')];
        } catch (PDOException $e) {
            error_log('Erro atualizarAluno: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function contarAlunosPorProfessor($id_professor)
    {
        try {
            if (!$id_professor) {
                return 0;
            }

            $col = null;
            $q = $this->conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'alunos' AND COLUMN_NAME IN ('turma_id','id_turma') LIMIT 1");
            if ($q->execute()) {
                $res = $q->fetch(PDO::FETCH_ASSOC);
                if ($res && isset($res['COLUMN_NAME'])) {
                    $col = $res['COLUMN_NAME'];
                }
            }

            if (!$col) {
                return 0;
            }

            $sql = "SELECT COUNT(*) AS total FROM alunos a INNER JOIN turmas t ON a." . $col . " = t.id_turma WHERE t.id_professor = :id_professor AND t.ano_letivo = :ano_letivo AND a.status_aluno = 'ativo'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':id_professor' => (int) $id_professor,
                ':ano_letivo' => (int) date('Y')
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('Erro contarAlunosPorProfessor: ' . $e->getMessage());
            return 0;
        }
    }

    public function salvarNotas($id_aluno, $bimestre, $media_final = null, $id_turma = null, $id_professor = null, $disciplina = null)
    {
        try {
            if (
                !is_string($disciplina)
                || trim($disciplina) === ''
                || strlen(trim($disciplina)) > 100
                || !filter_var($id_aluno, FILTER_VALIDATE_INT)
                || !filter_var($id_turma, FILTER_VALIDATE_INT)
                || !filter_var($bimestre, FILTER_VALIDATE_INT)
                || $bimestre < 1
                || $bimestre > 4
                || !$this->validarNota($media_final)
            ) {
                return ['success' => false, 'error' => 'Confira aluno, turma, bimestre, disciplina e nota.'];
            }
            $disciplina = trim($disciplina);
            if (!$this->alunoPertenceATurma((int) $id_aluno, (int) $id_turma)) {
                return ['success' => false, 'error' => 'O aluno selecionado não pertence a esta turma.'];
            }
            $sql = "INSERT INTO media_final (id_aluno, id_turma, id_professor, bimestre, disciplina, valor) VALUES (:id_aluno, :id_turma, :id_professor, :bimestre, :disciplina, :valor) 
                    ON DUPLICATE KEY UPDATE valor = VALUES(valor), atualizado_em = CURRENT_TIMESTAMP";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_aluno', $id_aluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_turma', $id_turma !== null ? $id_turma : null, PDO::PARAM_INT);
            $stmt->bindValue(':id_professor', $id_professor !== null ? $id_professor : null, PDO::PARAM_INT);
            $stmt->bindValue(':bimestre', $bimestre, PDO::PARAM_INT);
            $stmt->bindValue(':disciplina', $disciplina);
            $stmt->bindValue(':valor', $media_final !== null && $media_final !== '' ? $media_final : null);

            $ok = $stmt->execute();
            if ($ok) {
                error_log('[salvarNotas] Sucesso: id_aluno='.$id_aluno.' bimestre='.$bimestre.' disciplina='.$disciplina.' valor='.$media_final);
                return ['success' => true];
            }
            $err = $stmt->errorInfo();
            error_log('[salvarNotas] PDO Error: '.json_encode($err));
            return ['success' => false, 'error' => ($err[2] ?? 'Erro ao salvar nota')];
        } catch (PDOException $e) {
            error_log('Erro salvarNotas: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Não foi possível salvar a nota.'];
        }
    }

    public function salvarNotasLote($bimestre, $notas = [], $id_turma = null, $id_professor = null, $disciplina = null)
    {
        try {
            if (
                !is_string($disciplina)
                || trim($disciplina) === ''
                || strlen(trim($disciplina)) > 100
                || !filter_var($id_turma, FILTER_VALIDATE_INT)
                || !filter_var($bimestre, FILTER_VALIDATE_INT)
                || $bimestre < 1
                || $bimestre > 4
                || !$notas
            ) {
                return ['success' => false, 'error' => 'Confira turma, bimestre, disciplina e notas.'];
            }
            $disciplina = trim($disciplina);
            $this->conn->beginTransaction();
            $sql = "INSERT INTO media_final (id_aluno, id_turma, id_professor, bimestre, disciplina, valor) VALUES (:id_aluno, :id_turma, :id_professor, :bimestre, :disciplina, :valor) 
                    ON DUPLICATE KEY UPDATE valor = VALUES(valor), atualizado_em = CURRENT_TIMESTAMP";
            $stmt = $this->conn->prepare($sql);
            foreach ($notas as $n) {
                if (!is_array($n)) {
                    $this->conn->rollBack();
                    return ['success' => false, 'error' => 'Há um registro de nota inválido. Nenhuma nota foi alterada.'];
                }
                $id_aluno = isset($n['id_aluno']) ? intval($n['id_aluno']) : 0;
                $media_final = $n['media_final'] ?? null;
                if (
                    !$id_aluno
                    || !$this->validarNota($media_final)
                    || !$this->alunoPertenceATurma($id_aluno, (int) $id_turma)
                ) {
                    $this->conn->rollBack();
                    return ['success' => false, 'error' => 'Há aluno ou nota inválida ou fora da turma. Nenhuma nota foi alterada.'];
                }

                $stmt->bindValue(':id_aluno', $id_aluno, PDO::PARAM_INT);
                $stmt->bindValue(':id_turma', $id_turma !== null ? $id_turma : null, PDO::PARAM_INT);
                $stmt->bindValue(':id_professor', $id_professor !== null ? $id_professor : null, PDO::PARAM_INT);
                $stmt->bindValue(':bimestre', $bimestre, PDO::PARAM_INT);
                $stmt->bindValue(':disciplina', $disciplina);
                $stmt->bindValue(':valor', $media_final !== null && $media_final !== '' ? $media_final : null);

                $ok = $stmt->execute();
                if (!$ok) {
                    $err = $stmt->errorInfo();
                    $this->conn->rollBack();
                    return ['success' => false, 'error' => ($err[2] ?? 'Erro ao salvar notas em lote')];
                }
            }
            $this->conn->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('Erro salvarNotasLote: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Não foi possível salvar as notas em lote.'];
        }
    }

    private function validarNota($nota): bool
    {
        if ($nota === null || $nota === '') {
            return true;
        }
        return is_numeric($nota) && is_finite((float) $nota) && (float) $nota >= 0 && (float) $nota <= 10;
    }

    private function alunoPertenceATurma(int $idAluno, int $idTurma): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT 1 FROM alunos WHERE id_aluno = :id_aluno AND id_turma = :id_turma AND status_aluno = 'ativo' LIMIT 1"
        );
        $stmt->execute([':id_aluno' => $idAluno, ':id_turma' => $idTurma]);
        return (bool) $stmt->fetchColumn();
    }

    public function obterPerfilPedagogico(int $idAluno): ?array
    {
        try {
            $stmt = $this->conn->prepare(
                'SELECT a.id_aluno, a.nome, a.matricula, a.status_aluno, a.ano_conclusao,
                        t.id_turma, t.nome_turma, t.serie_turma, t.ano_letivo, c.nome_curso
                 FROM alunos a
                 INNER JOIN turmas t ON t.id_turma = a.id_turma
                 INNER JOIN cursos c ON c.id_curso = t.id_curso
                 WHERE a.id_aluno = :id_aluno
                 LIMIT 1'
            );
            $stmt->execute([':id_aluno' => $idAluno]);
            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$aluno) {
                return null;
            }

            $stmt = $this->conn->prepare(
                'SELECT mf.bimestre, mf.disciplina, mf.valor, t.serie_turma, t.ano_letivo
                 FROM media_final mf
                 LEFT JOIN turmas t ON t.id_turma = mf.id_turma
                 WHERE mf.id_aluno = :id_aluno
                 ORDER BY t.ano_letivo, mf.bimestre, mf.disciplina'
            );
            $stmt->execute([':id_aluno' => $idAluno]);
            $notas = array_map(function (array $nota): array {
                $valor = $nota['valor'];
                $notaValida = $this->validarNota($valor) && $valor !== null && $valor !== '';
                return [
                    'bimestre' => (int) $nota['bimestre'],
                    'disciplina' => $nota['disciplina'],
                    'serie_turma' => $nota['serie_turma'] !== null ? (int) $nota['serie_turma'] : null,
                    'ano_letivo' => $nota['ano_letivo'] !== null ? (int) $nota['ano_letivo'] : null,
                    'nota' => $notaValida ? (float) $valor : null,
                    'classificacao' => $notaValida
                        ? $this->classificarDesempenho((float) $valor)
                        : ($valor === null || $valor === '' ? null : 'Nota inválida'),
                    'situacao' => $notaValida
                        ? 'lancada'
                        : ($valor === null || $valor === '' ? 'sem_nota' : 'invalida')
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));

            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) AS total_registros,
                        SUM(status = 'presente') AS presencas,
                        SUM(status = 'falta') AS faltas,
                        MIN(data_frequencia) AS primeira_data,
                        MAX(data_frequencia) AS ultima_data
                 FROM frequencias
                 WHERE id_aluno = :id_aluno"
            );
            $stmt->execute([':id_aluno' => $idAluno]);
            $frequencia = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $totalFrequencias = (int) ($frequencia['total_registros'] ?? 0);
            $totalPresencas = (int) ($frequencia['presencas'] ?? 0);

            $stmt = $this->conn->prepare(
                'SELECT o.id_ocorrencia, o.tipo_ocorrencia, o.descricao, o.data_ocorrencia,
                        t.serie_turma, t.ano_letivo
                 FROM ocorrencias o
                 INNER JOIN turmas t ON t.id_turma = o.id_turma
                 WHERE o.id_aluno = :id_aluno AND o.deletado_em IS NULL
                 ORDER BY o.data_ocorrencia DESC, o.id_ocorrencia DESC'
            );
            $stmt->execute([':id_aluno' => $idAluno]);

            $aluno['turma'] = sprintf(
                '%sº %s - %s',
                $aluno['serie_turma'],
                $aluno['nome_turma'],
                $aluno['nome_curso']
            );
            $aluno['notas'] = $notas;
            $aluno['frequencia'] = [
                'total_registros' => $totalFrequencias,
                'presencas' => $totalPresencas,
                'faltas' => (int) ($frequencia['faltas'] ?? 0),
                'percentual_presenca' => $totalFrequencias > 0
                    ? round(100 * $totalPresencas / $totalFrequencias, 1)
                    : null,
                'primeira_data' => $frequencia['primeira_data'] ?? null,
                'ultima_data' => $frequencia['ultima_data'] ?? null
            ];
            $aluno['ocorrencias'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->conn->prepare(
                '                SELECT ap.data_registro, ap.motivo, ap.observacao, ap.encaminhamento,
                       ap.data_retorno, ap.status, t.serie_turma, t.ano_letivo
                FROM acompanhamentos_pedagogicos ap
                INNER JOIN turmas t ON t.id_turma = ap.id_turma
                WHERE ap.id_aluno = :id_aluno
                ORDER BY ap.data_registro DESC, ap.id_acompanhamento DESC'
            );
            $stmt->execute([':id_aluno' => $idAluno]);
            $aluno['acompanhamentos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->conn->prepare(
                '                SELECT cc.data_conselho, cc.bimestre, cc.pauta,
                       cca.observacao, cca.encaminhamento, t.serie_turma, t.ano_letivo
                FROM conselho_classe_alunos cca
                INNER JOIN conselhos_classe cc ON cc.id_conselho = cca.id_conselho
                INNER JOIN turmas t ON t.id_turma = cc.id_turma
                WHERE cca.id_aluno = :id_aluno
                ORDER BY cc.data_conselho DESC, cc.id_conselho DESC'
            );
            $stmt->execute([':id_aluno' => $idAluno]);
            $aluno['deliberacoes_conselho'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $aluno;
        } catch (PDOException $e) {
            error_log('Erro ao obter perfil pedagógico do aluno: ' . $e->getMessage());
            throw new RuntimeException('Falha ao consultar o perfil pedagógico do aluno.', 0, $e);
        }
    }

    public function listarNotasPorTurmaBimestre($id_turma = null, $bimestre = null, $disciplina = null)
    {
        try {
            if (!$id_turma || !$bimestre || !$disciplina) {
                return [];
            }

            $sql = "SELECT mf.id_aluno, mf.valor
                    FROM media_final mf
                    INNER JOIN alunos a ON a.id_aluno = mf.id_aluno
                    WHERE mf.id_turma = :id_turma
                      AND mf.bimestre = :bimestre
                      AND mf.disciplina = :disciplina
                      AND a.status_aluno = 'ativo'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
            $stmt->bindValue(':bimestre', $bimestre, PDO::PARAM_INT);
            $stmt->bindValue(':disciplina', $disciplina);
            if ($stmt->execute()) {
                $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $result = [];
                foreach ($dados as $row) {
                    $result[intval($row['id_aluno'])] = [
                        'media_final' => $row['valor'],
                    ];
                }
                return $result;
            }
            return [];
        } catch (PDOException $e) {
            error_log('Erro listarNotasPorTurmaBimestre: ' . $e->getMessage());
            return [];
        }
    }

    public function classificarDesempenho($media): ?string
    {
        return ClassificacaoDesempenho::classificar($media);
    }

    public function calcularDesempenhoPorTurma($id_turma)
    {
        try {
            if (!$id_turma) {
                return null;
            }

            $sql = "SELECT
                        t.id_turma,
                        t.nome_turma,
                        t.serie_turma,
                        c.letra,
                        t.sala_turma,
                        t.ano_letivo,
                        c.nome_curso,
                        COALESCE(roster.total_alunos, 0) AS total_alunos,
                        COALESCE(grades.total_notas, 0) AS total_notas,
                        COALESCE(grades.notas_validas, 0) AS notas_validas,
                        COALESCE(grades.notas_invalidas, 0) AS notas_invalidas,
                        grades.media_geral
                    FROM turmas t
                    LEFT JOIN (
                        SELECT id_turma, COUNT(DISTINCT id_aluno) AS total_alunos
                        FROM aluno_turma_historico
                        GROUP BY id_turma
                    ) roster ON roster.id_turma = t.id_turma
                    LEFT JOIN (
                        SELECT id_turma, COUNT(*) AS total_notas,
                               SUM(CASE WHEN valor BETWEEN 0 AND 10 THEN 1 ELSE 0 END) AS notas_validas,
                               SUM(CASE WHEN valor IS NOT NULL AND (valor < 0 OR valor > 10) THEN 1 ELSE 0 END) AS notas_invalidas,
                               AVG(CASE WHEN valor BETWEEN 0 AND 10 THEN valor END) AS media_geral
                        FROM media_final
                        WHERE valor IS NOT NULL
                        GROUP BY id_turma
                    ) grades ON grades.id_turma = t.id_turma
                    LEFT JOIN cursos c ON t.id_curso = c.id_curso
                    WHERE t.id_turma = :id_turma
                    GROUP BY t.id_turma";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                return null;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }

            $media = $row['media_geral'] !== null ? (float) $row['media_geral'] : null;
            $classificacao = $this->classificarDesempenho($media);
            $notasValidas = (int) ($row['notas_validas'] ?? 0);
            $notasInvalidas = (int) ($row['notas_invalidas'] ?? 0);

            return [
                'id_turma' => intval($row['id_turma']),
                'nome_turma' => trim($row['nome_turma'] ?? ''),
                'serie_turma' => $row['serie_turma'] ?? '',
                'letra' => $row['letra'] ?? '',
                'sala_turma' => $row['sala_turma'] ?? '',
                'ano_letivo' => $row['ano_letivo'] ?? '',
                'nome_curso' => $row['nome_curso'] ?? '',
                'turma_descricao' => trim(sprintf('%sº %s - %s', $row['serie_turma'] ?? '', $row['letra'] ?? '', $row['nome_curso'] ?? '')),
                'total_alunos' => intval($row['total_alunos'] ?? 0),
                'total_notas' => intval($row['total_notas'] ?? 0),
                'media_geral' => $media === null ? null : round($media, 2),
                'classificacao' => $classificacao,
                'situacao_desempenho' => $notasValidas > 0
                    ? 'calculado'
                    : ($notasInvalidas > 0 ? 'dados_invalidos' : 'sem_dados'),
                'notas_validas' => $notasValidas,
                'notas_invalidas' => $notasInvalidas,
            ];
        } catch (PDOException $e) {
            error_log('Erro calcularDesempenhoPorTurma: ' . $e->getMessage());
            throw new RuntimeException('Falha ao calcular o desempenho da turma.', 0, $e);
        }
    }

    public function calcularDesempenhoMedioProfessor($id_professor, $bimestre = null)
    {
        try {
            if ($bimestre === null) {
                $bimestre = (date('n') <= 3) ? 1 : ((date('n') <= 6) ? 2 : ((date('n') <= 9) ? 3 : 4));
            }
            if (!filter_var($bimestre, FILTER_VALIDATE_INT) || $bimestre < 1 || $bimestre > 4) {
                throw new InvalidArgumentException('Bimestre inválido.');
            }

            $sql = "SELECT AVG(CASE WHEN n.valor BETWEEN 0 AND 10 THEN n.valor END) AS media_geral,
                           COUNT(CASE WHEN n.valor BETWEEN 0 AND 10 THEN n.id END) AS total_notas,
                           COUNT(CASE WHEN n.valor IS NOT NULL AND (n.valor < 0 OR n.valor > 10) THEN n.id END) AS notas_invalidas
                    FROM media_final n
                    INNER JOIN turmas t ON n.id_turma = t.id_turma
                    WHERE t.id_professor = :id_professor AND n.bimestre = :bimestre AND n.valor IS NOT NULL";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_professor', $id_professor, PDO::PARAM_INT);
            $stmt->bindValue(':bimestre', $bimestre, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new RuntimeException('Não foi possível calcular o desempenho do professor.');
            }

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado || !$resultado['total_notas']) {
                return !empty($resultado['notas_invalidas']) ? 'Nota inválida' : null;
            }

            return $this->classificarDesempenho($resultado['media_geral']);

        } catch (PDOException $e) {
            error_log('Erro calcularDesempenhoMedioProfessor: ' . $e->getMessage());
            throw new RuntimeException('Falha ao consultar o desempenho do professor.', 0, $e);
        }
    }

}

?>
