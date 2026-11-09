<?php
    require_once(__DIR__ . '/../config/connect.php');
    require_once(__DIR__ . '/notificacao_model.php');

    // inicialização do framework
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    class usuario_model extends connect
    {
        // atributos
        private $table1;

        // métodos
        function __construct()
        {
            parent::__construct();
            $this->table1 = 'usuarios';
        }

        public function contar_usuarios(): int
        {
            $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table1}");
            return (int) $stmt->fetchColumn();
        }

        // cadastrar usuário
        public function cadastrar($nome, $matricula, $email, $senha, $tipo)
        {
            try {

                // verificar email
                $stmtEmail = $this->conn->prepare("
                    SELECT id 
                    FROM {$this->table1} 
                    WHERE email = :email 
                    LIMIT 1
                ");

                $stmtEmail->bindValue(":email", $email);
                $stmtEmail->execute();

                if ($stmtEmail->fetch()) {
                    return 2; // email já existe
                }

                // verificar matrícula
                $stmtMatricula = $this->conn->prepare("
                    SELECT id 
                    FROM {$this->table1} 
                    WHERE matricula = :matricula 
                    LIMIT 1
                ");

                $stmtMatricula->bindValue(":matricula", $matricula);
                $stmtMatricula->execute();

                if ($stmtMatricula->fetch()) {
                    return 3; // matrícula já existe
                }

                // hash da senha
                $hash = password_hash($senha, PASSWORD_DEFAULT);

                // insert
                $stmtInsert = $this->conn->prepare("
                    INSERT INTO {$this->table1}
                    (
                        nome,
                        matricula,
                        email,
                        senha,
                        tipo,
                        criado_em
                    )
                    VALUES
                    (
                        :nome,
                        :matricula,
                        :email,
                        :senha,
                        :tipo,
                        NOW()
                    )
                ");

                $result = $stmtInsert->execute([
                    ':nome' => $nome,
                    ':matricula' => $matricula,
                    ':email' => $email,
                    ':senha' => $hash,
                    ':tipo' => $tipo
                ]);

                if ($result) {
                    $idUsuario = (int) $this->conn->lastInsertId();
                    $notificacao = new NotificacaoModel();
                    $notificacao->criar(
                        $idUsuario,
                        'Conta criada',
                        'Sua conta foi criada com sucesso no sistema.',
                        'success',
                        null,
                        'usuario',
                        $idUsuario
                    );
                    return 1; // sucesso
                } else {
                    return 4; // erro ao cadastrar
                }

            } catch (PDOException $e) {
                error_log("Erro ao cadastrar usuário: " . $e->getMessage());
                return 5;
            }
        }

        // login
        public function login($email, $senha)
        {
            try {

                $stmt = $this->conn->prepare("
                    SELECT * 
                    FROM {$this->table1}
                    WHERE email = :email
                    LIMIT 1
                ");

                $stmt->bindValue(":email", $email);
                $stmt->execute();

                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                // verificar email
                if (!$usuario) {
                    return [
                        'status' => 2,
                        'usuario' => null
                    ];
                }

                // verificar senha
                if (!password_verify($senha, $usuario['senha'])) {
                    return [
                        'status' => 3,
                        'usuario' => null
                    ];
                }

                return [
                    'status' => 1,
                    'usuario' => $usuario
                ];

            } catch (PDOException $e) {
                error_log("Erro no login: " . $e->getMessage());

                return [
                    'status' => 4,
                    'usuario' => null
                ];
            }
        }

        // listar usuários
        public function listar_usuarios()
        {
            try {

                $stmt = $this->conn->prepare("
                    SELECT
                        id,
                        nome,
                        matricula,
                        email,
                        tipo,
                        atribuicao,
                        criado_em
                    FROM {$this->table1}
                    ORDER BY nome ASC
                ");

                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                error_log("Erro ao listar usuários: " . $e->getMessage());
                return [];
            }
        }

        // buscar usuário por id
        public function buscar_por_id($id)
        {
            try {

                $stmt = $this->conn->prepare("
                    SELECT
                        id,
                        nome,
                        matricula,
                        email,
                        tipo,
                        atribuicao
                    FROM {$this->table1}
                    WHERE id = :id
                    LIMIT 1
                ");

                $stmt->bindValue(":id", $id);
                $stmt->execute();

                return $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                error_log("Erro ao buscar usuário: " . $e->getMessage());
                return [];
            }
        }

        // atualizar usuário
        public function atualizar($id, $nome, $matricula, $email, $tipo)
        {
            try {

                $stmt = $this->conn->prepare("
                    UPDATE {$this->table1}
                    SET
                        nome = :nome,
                        matricula = :matricula,
                        email = :email,
                        tipo = :tipo,
                        atribuicao = CASE WHEN :tipo = 'professor' THEN atribuicao ELSE NULL END
                    WHERE id = :id
                ");

                $result = $stmt->execute([
                    ':id' => $id,
                    ':nome' => $nome,
                    ':matricula' => $matricula,
                    ':email' => $email,
                    ':tipo' => $tipo
                ]);

                return $result;

            } catch (PDOException $e) {
                error_log("Erro ao atualizar usuário: " . $e->getMessage());
                return false;
            }
        }

        // excluir usuário
        public function excluir($id)
        {
            try {

                $stmt = $this->conn->prepare("
                    DELETE FROM {$this->table1}
                    WHERE id = :id
                ");

                return $stmt->execute([
                    ':id' => $id
                ]);

            } catch (PDOException $e) {
                error_log("Erro ao excluir usuário: " . $e->getMessage());
                return false;
            }
        }

        // listar apenas professores
        public function listar_professores()
        {
            try {
                $stmt = $this->conn->prepare(
                    "SELECT id, nome, matricula, email, tipo, atribuicao, criado_em FROM {$this->table1} WHERE LOWER(tipo) = 'professor' ORDER BY nome ASC"
                );

                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                error_log("Erro ao listar professores: " . $e->getMessage());
                return [];
            }
        }
    }

?>