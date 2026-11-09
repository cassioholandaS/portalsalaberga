<?php

require_once(__DIR__ . '/../config/connect.php');

class FrequenciaModel extends connect
{
    function __construct()
    {
        parent::__construct();
    }

    public function listarPorTurmaData($id_turma, $data, $id_professor)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT f.id_aluno, f.status
                 FROM frequencias f
                 JOIN turmas t ON f.id_turma = t.id_turma
                 WHERE f.id_turma = :id_turma
                   AND f.data_frequencia = :data_frequencia
                   AND t.id_professor = :id_professor"
            );
            $stmt->execute([
                ':id_turma' => $id_turma,
                ':data_frequencia' => $data,
                ':id_professor' => $id_professor,
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erro listarPorTurmaData: ' . $e->getMessage());
            return [];
        }
    }

    public function salvarFrequencia($id_turma, $id_aluno, $id_professor, $data, $status)
    {
        try {
            $stmtAluno = $this->conn->prepare(
                "SELECT 1 FROM alunos
                 WHERE id_aluno = :id_aluno
                   AND id_turma = :id_turma
                   AND status_aluno = 'ativo'
                 LIMIT 1"
            );
            $stmtAluno->execute([
                ':id_aluno' => $id_aluno,
                ':id_turma' => $id_turma
            ]);
            if (!$stmtAluno->fetchColumn()) {
                return false;
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO frequencias (id_turma, id_aluno, id_professor, data_frequencia, status)
                 VALUES (:id_turma, :id_aluno, :id_professor, :data_frequencia, :status)
                 ON DUPLICATE KEY UPDATE status = :status_update, atualizado_em = CURRENT_TIMESTAMP"
            );

            return $stmt->execute([
                ':id_turma' => $id_turma,
                ':id_aluno' => $id_aluno,
                ':id_professor' => $id_professor,
                ':data_frequencia' => $data,
                ':status' => $status,
                ':status_update' => $status,
            ]);
        } catch (PDOException $e) {
            error_log('Erro salvarFrequencia: ' . $e->getMessage());
            return false;
        }
    }

    public function calcularMediaMensal($id_professor)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT ROUND(
                    CASE WHEN COUNT(*) = 0 THEN 0 ELSE 100 * SUM(status = 'presente') / COUNT(*) END
                 , 1) AS media
                 FROM frequencias f
                 JOIN turmas t ON f.id_turma = t.id_turma
                 WHERE t.id_professor = :id_professor
                   AND MONTH(f.data_frequencia) = MONTH(CURRENT_DATE())
                   AND YEAR(f.data_frequencia) = YEAR(CURRENT_DATE())"
            );
            $stmt->execute([':id_professor' => $id_professor]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return floatval($result['media'] ?? 0);
        } catch (PDOException $e) {
            error_log('Erro calcularMediaMensal: ' . $e->getMessage());
            return 0;
        }
    }
}
