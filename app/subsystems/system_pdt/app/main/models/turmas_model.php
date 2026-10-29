<?php

    require_once(__DIR__ . '/../config/connect.php');

    class TurmasModel extends connect
    {
        function __construct() {
            parent::__construct();
        }

        // =========================
        // LISTAR CURSOS
        // =========================
        public function listarCursos()
        {
            try {
                $stmt = $this->conn->prepare("SELECT id_curso, letra, nome_curso, descricao FROM cursos ORDER BY letra ASC");
                if (!$stmt->execute()) {
                    return [];
                }
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Erro ao listar cursos: " . $e->getMessage());
                return [];
            }
        }

        // =========================
        // VERIFICAR PROFESSOR VINCULADO
        // =========================
        public function verificarProfessorVinculado($id_professor, $exclude_turma_id = null)
        {
            try {
                $condition = '';
                if ($exclude_turma_id !== null) {
                    $condition = 'AND t.id_turma != :exclude_turma_id';
                }

                $sql = "SELECT t.id_turma 
                    FROM turmas t
                    WHERE t.id_professor = :id_professor
                      AND t.ano_letivo >= YEAR(CURRENT_DATE) " . $condition . "
                    LIMIT 1";

                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id_professor', $id_professor, PDO::PARAM_INT);
                if ($exclude_turma_id !== null) {
                    $stmt->bindValue(':exclude_turma_id', $exclude_turma_id, PDO::PARAM_INT);
                }
                if (!$stmt->execute()) {
                    return null;
                }
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? $result['id_turma'] : null;
            } catch (PDOException $e) {
                error_log("Erro ao verificar professor vinculado: " . $e->getMessage());
                return null;
            }
        }

        private function atribuirPdtAoProfessor($id_professor)
        {
            try {
                $stmt = $this->conn->prepare("UPDATE usuarios SET atribuicao = 'pdt' WHERE id = :id_professor AND tipo = 'professor'");
                return $stmt->execute([':id_professor' => $id_professor]);
            } catch (PDOException $e) {
                error_log("Erro ao atribuir PDT ao professor: " . $e->getMessage());
                return false;
            }
        }

        private function desatribuirPdt($id_professor)
        {
            try {
                $stmt = $this->conn->prepare(
                    "UPDATE usuarios
                     SET atribuicao = NULL
                     WHERE id = :id_professor
                       AND NOT EXISTS (
                           SELECT 1
                           FROM turmas
                           WHERE id_professor = :id_professor_turma
                             AND ano_letivo >= YEAR(CURRENT_DATE)
                       )"
                );
                return $stmt->execute([
                    ':id_professor' => $id_professor,
                    ':id_professor_turma' => $id_professor
                ]);
            } catch (PDOException $e) {
                error_log("Erro ao desatribuir PDT do professor: " . $e->getMessage());
                return false;
            }
        }

        // =========================
        // CADASTRAR TURMA
        // =========================
        public function cadastrarTurma($id_curso, $serie, $sala, $ano, $descricao, $id_professor)
        {
            try {
                if (!$this->conn) {
                    return false;
                }

                $stmt_check_tipo = $this->conn->prepare("SELECT tipo FROM usuarios WHERE id = :id_professor");
                $stmt_check_tipo->bindValue(':id_professor', $id_professor, PDO::PARAM_INT);
                $stmt_check_tipo->execute();
                $user = $stmt_check_tipo->fetch(PDO::FETCH_ASSOC);

                if (!$user || strtolower($user['tipo']) !== 'professor') {
                    return 6; // Apenas professores podem ser atribuídos como PDT
                }

                $turma_existente = $this->verificarProfessorVinculado($id_professor);
                if ($turma_existente) {
                    return 4; // Professor já vinculado a outra turma
                }

                $stmt_curso = $this->conn->prepare("SELECT letra FROM cursos WHERE id_curso = :id_curso");
                $stmt_curso->bindValue(':id_curso', $id_curso, PDO::PARAM_INT);
                $stmt_curso->execute();
                $curso = $stmt_curso->fetch(PDO::FETCH_ASSOC);

                if (!$curso) {
                    return 5; // Curso não encontrado
                }

                $nome_turma = $curso['letra'];

                $stmt_insert = $this->conn->prepare("
                    INSERT INTO turmas (id_curso, nome_turma, serie_turma, sala_turma, ano_letivo, descricao_turma, id_professor) 
                    VALUES (:id_curso, :nome_turma, :serie_turma, :sala_turma, :ano_letivo, :descricao_turma, :id_professor)
                ");
                
                $sucesso = $stmt_insert->execute([
                    ':id_curso' => $id_curso,
                    ':nome_turma' => $nome_turma,
                    ':serie_turma' => $serie,
                    ':sala_turma' => $sala,
                    ':ano_letivo' => $ano,
                    ':descricao_turma' => $descricao,
                    ':id_professor' => $id_professor,
                ]);

                if (!$sucesso) {
                    return 2;
                }

                $this->atribuirPdtAoProfessor($id_professor);

                $id_turma = $this->conn->lastInsertId();

                return [
                    "status" => 1,
                    "id" => (int) $id_turma
                ];
            } catch (PDOException $e) {
                error_log("Erro ao cadastrar turma: " . $e->getMessage());
                return 3;
            }
        }

        // =========================
        // LISTAR TURMAS
        // =========================
        public function listarTurmas(?int $id_professor = null)
        {
            try {
                if ($id_professor !== null) {
                    $stmt_select = $this->conn->prepare("
                        SELECT t.id_turma, t.id_curso, t.nome_turma, t.serie_turma, t.sala_turma, t.ano_letivo, t.descricao_turma, 
                               t.id_professor AS professor_id, u.nome AS professor_nome, c.letra, c.nome_curso
                        FROM turmas t
                        INNER JOIN usuarios u ON t.id_professor = u.id
                        INNER JOIN cursos c ON t.id_curso = c.id_curso
                        WHERE t.id_professor = :id_professor
                          AND t.ano_letivo = YEAR(CURRENT_DATE)
                        ORDER BY t.ano_letivo ASC
                    ");
                    $stmt_select->bindValue(':id_professor', $id_professor, PDO::PARAM_INT);
                } else {
                   $stmt_select = $this->conn->prepare("
                        SELECT t.id_turma, t.id_curso, t.nome_turma, t.serie_turma, t.sala_turma, t.ano_letivo, t.descricao_turma, 
                               t.id_professor AS professor_id, u.nome AS professor_nome, c.letra, c.nome_curso
                        FROM turmas t
                        INNER JOIN usuarios u ON t.id_professor = u.id
                        INNER JOIN cursos c ON t.id_curso = c.id_curso
                        ORDER BY t.ano_letivo ASC
                    ");
                }

                if (!$stmt_select->execute()) {
                    return [];
                }

                $turmas = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
                return $turmas ?: [];
            } catch (PDOException $e) {
                error_log("Erro ao listar turmas: " . $e->getMessage());
                return [];
            }
        }

        // =========================
        // OBTER TURMA POR ID
        // =========================
        public function obterTurmaById($id_turma)
        {
            try {
                $stmt = $this->conn->prepare("
                    SELECT t.id_turma, t.id_curso, t.nome_turma, t.serie_turma, t.sala_turma, t.ano_letivo, t.descricao_turma, 
                           t.id_professor AS professor_id, u.nome AS professor_nome, u.tipo, c.letra, c.nome_curso
                    FROM turmas t
                    INNER JOIN usuarios u ON t.id_professor = u.id
                    INNER JOIN cursos c ON t.id_curso = c.id_curso
                    WHERE t.id_turma = :id_turma
                ");
                $stmt->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
                if (!$stmt->execute()) {
                    return null;
                }
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Erro ao obter turma: " . $e->getMessage());
                return null;
            }
        }

        // =========================
        // ATUALIZAR TURMA
        // =========================
        public function atualizarTurma($id_turma, $id_curso, $serie, $sala, $descricao, $id_professor)
        {
            try {
                if (!$this->conn) {
                    return false;
                }

                $stmt_check_tipo = $this->conn->prepare("SELECT tipo FROM usuarios WHERE id = :id_professor");
                $stmt_check_tipo->bindValue(':id_professor', $id_professor, PDO::PARAM_INT);
                $stmt_check_tipo->execute();
                $user = $stmt_check_tipo->fetch(PDO::FETCH_ASSOC);

                if (!$user || strtolower($user['tipo']) !== 'professor') {
                    return 6; // Apenas professores podem ser atribuídos como PDT
                }

                $stmt_current = $this->conn->prepare("SELECT id_professor FROM turmas WHERE id_turma = :id_turma LIMIT 1");
                $stmt_current->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
                $stmt_current->execute();
                $current = $stmt_current->fetch(PDO::FETCH_ASSOC);
                $old_professor_id = $current['id_professor'] ?? null;

                $turma_existente = $this->verificarProfessorVinculado($id_professor, $id_turma);
                if ($turma_existente && $turma_existente != $id_turma) {
                    return 4; // Professor já vinculado a outra turma
                }

                $stmt_curso = $this->conn->prepare("SELECT letra FROM cursos WHERE id_curso = :id_curso");
                $stmt_curso->bindValue(':id_curso', $id_curso, PDO::PARAM_INT);
                $stmt_curso->execute();
                $curso = $stmt_curso->fetch(PDO::FETCH_ASSOC);

                if (!$curso) {
                    return 5; // Curso não encontrado
                }

                $nome_turma = $curso['letra'];

                $stmt_update = $this->conn->prepare("
                    UPDATE turmas 
                    SET id_curso = :id_curso, nome_turma = :nome_turma, serie_turma = :serie_turma, 
                        sala_turma = :sala_turma, descricao_turma = :descricao_turma, id_professor = :id_professor
                    WHERE id_turma = :id_turma
                ");

                $updated = $stmt_update->execute([
                    ':id_turma' => $id_turma,
                    ':id_curso' => $id_curso,
                    ':nome_turma' => $nome_turma,
                    ':serie_turma' => $serie,
                    ':sala_turma' => $sala,
                    ':descricao_turma' => $descricao,
                    ':id_professor' => $id_professor,
                ]);

                if ($updated) {
                    if ($old_professor_id && $old_professor_id != $id_professor) {
                        $this->desatribuirPdt($old_professor_id);
                    }
                    $this->atribuirPdtAoProfessor($id_professor);
                }

                return $updated;
            } catch (PDOException $e) {
                error_log("Erro ao atualizar turma: " . $e->getMessage());
                return false;
            }
        }

        // =========================
        // DELETAR TURMA
        // =========================
        public function deletarTurma($id_turma)
        {
            try {
                $stmt_prof = $this->conn->prepare("SELECT id_professor FROM turmas WHERE id_turma = :id_turma LIMIT 1");
                $stmt_prof->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
                $stmt_prof->execute();
                $prof = $stmt_prof->fetch(PDO::FETCH_ASSOC);
                $id_professor = $prof['id_professor'] ?? null;

                $stmt = $this->conn->prepare("DELETE FROM turmas WHERE id_turma = :id_turma");
                $stmt->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
                $deleted = $stmt->execute();

                if ($deleted && $id_professor) {
                    $this->desatribuirPdt($id_professor);
                }

                return $deleted;
            } catch (PDOException $e) {
                error_log("Erro ao deletar turma: " . $e->getMessage());
                return false;
            }
        }
    }
?>