<?php

require_once __DIR__ . '/../config/database.php';

class usuario_model
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = $GLOBALS['pdo'] ?? $GLOBALS['conn'];

        if (!$this->pdo instanceof PDO) {
            throw new RuntimeException('PDO não foi inicializado para o modelo de usuários.');
        }
    }

    public function primeiro_acesso(string $nome, string $email, string $senha, string $tipo, string $cpf, string $telefone): int
    {
        $nome = trim($nome);
        $email = trim(strtolower($email));
        $tipo = trim($tipo);
        $cpf = preg_replace('/\D/', '', $cpf);
        $telefone = preg_replace('/\D/', '', $telefone);

        $tiposValidos = ['aee', 'apoio', 'responsavel'];

        if ($nome === '' || $email === '' || $senha === '') {
            return 3;
        }

        if (strlen($cpf) !== 11) {
            return 4;
        }

        if (!in_array(strlen($telefone), [10, 11], true)) {
            return 5;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 3;
        }

        if (!in_array($tipo, $tiposValidos, true)) {
            return 3;
        }

        if (strlen($senha) < 6) {
            return 3;
        }

        $stmtCheck = $this->pdo->prepare('SELECT 1 FROM usuarios WHERE email = :email LIMIT 1');
        $stmtCheck->execute([':email' => $email]);

        if ($stmtCheck->fetchColumn()) {
            return 2;
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, cpf, telefone, email, senha, tipo, status_cadastro, ativo, criado_em)
             VALUES (:nome, :cpf, :telefone, :email, :senha, :tipo, :status_cadastro, :ativo, NOW())'
        );

        $ok = $stmt->execute([
            ':nome' => $nome,
            ':cpf' => $cpf,
            ':telefone' => $telefone,
            ':email' => $email,
            ':senha' => $hash,
            ':tipo' => $tipo,
            ':status_cadastro' => 'pendente',
            ':ativo' => 1,
        ]);

        return $ok ? 1 : 3;
    }

    public function listar_solicitacoes_cadastro(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id, nome, email, tipo, cpf, telefone, criado_em
             FROM usuarios
             WHERE status_cadastro = 'pendente'
             ORDER BY criado_em ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function decidir_solicitacao_cadastro(int $usuarioId, string $decisao): bool
    {
        $status = $decisao === 'aprovar' ? 'aprovado' : ($decisao === 'recusar' ? 'recusado' : '');

        if ($status === '') {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE usuarios
             SET status_cadastro = :status
             WHERE id = :id AND status_cadastro = 'pendente'"
        );

        $stmt->execute([
            ':status' => $status,
            ':id' => $usuarioId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function login(string $email, string $senha): array
    {
        $email = trim(strtolower($email));

        if ($email === '' || $senha === '') {
            return ['status' => 3, 'usuario' => null];
        }

        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return ['status' => 2, 'usuario' => null];
        }

        if (($usuario['status_cadastro'] ?? 'aprovado') === 'pendente') {
            return ['status' => 6, 'usuario' => null];
        }

        if (($usuario['status_cadastro'] ?? 'aprovado') === 'recusado') {
            return ['status' => 7, 'usuario' => null];
        }

        if ((int) ($usuario['ativo'] ?? 1) !== 1) {
            return ['status' => 5, 'usuario' => null];
        }

        if (!password_verify($senha, $usuario['senha'] ?? '')) {
            return ['status' => 3, 'usuario' => null];
        }

        return ['status' => 1, 'usuario' => $usuario];
    }

    public function listar_usuarios(?string $pesquisa = null, ?int $ativo = null, ?int $usuarioAtualId = null): array
    {
        $sql = 'SELECT id, nome, cpf, telefone, email, tipo, status_cadastro, ativo, criado_em
            FROM usuarios WHERE status_cadastro = \'aprovado\'';
        $params = [];

        if ($usuarioAtualId !== null && $usuarioAtualId > 0) {
            $sql .= ' AND (tipo <> \'master\' OR id = :usuario_atual_id)';
            $params[':usuario_atual_id'] = $usuarioAtualId;
        }

        if ($pesquisa !== null && trim($pesquisa) !== '') {
            $sql .= ' AND (nome LIKE :pesquisa_nome OR email LIKE :pesquisa_email)';
            $valorPesquisa = '%' . trim($pesquisa) . '%';
            $params[':pesquisa_nome'] = $valorPesquisa;
            $params[':pesquisa_email'] = $valorPesquisa;
        }
        if ($ativo !== null) {
            $sql .= ' AND ativo = :ativo';
            $params[':ativo'] = $ativo;
        }
        $sql .= ' ORDER BY nome ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastrar_usuario(array $dados): array
    {
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = trim(strtolower((string) ($dados['email'] ?? '')));
        $senha = (string) ($dados['senha'] ?? '');
        $tipo = trim((string) ($dados['tipo'] ?? 'responsavel'));
        $tiposValidos = ['master', 'aee', 'apoio', 'responsavel'];

        if (strlen($nome) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
            return ['status' => 2, 'message' => 'Informe nome, e-mail válido e senha com no mínimo 6 caracteres.'];
        }
        if (!in_array($tipo, $tiposValidos, true)) {
            return ['status' => 2, 'message' => 'Perfil inválido.'];
        }

        $check = $this->pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            return ['status' => 2, 'message' => 'E-mail já cadastrado.'];
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios (nome, email, senha, tipo, status_cadastro, ativo)
             VALUES (:nome, :email, :senha, :tipo, 'aprovado', 1)"
        );
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => password_hash($senha, PASSWORD_DEFAULT),
            ':tipo' => $tipo,
        ]);
        return ['status' => 1, 'message' => 'Usuário cadastrado com sucesso.'];
    }

    public function atualizar_usuario(int $id, array $dados): array
    {
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = trim(strtolower((string) ($dados['email'] ?? '')));
        $tipo = trim((string) ($dados['tipo'] ?? 'responsavel'));
        $senha = (string) ($dados['senha'] ?? '');
        if ($id <= 0 || strlen($nome) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 2, 'message' => 'Dados inválidos.'];
        }
        $check = $this->pdo->prepare('SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1');
        $check->execute([':email' => $email, ':id' => $id]);
        if ($check->fetch()) {
            return ['status' => 2, 'message' => 'E-mail já pertence a outro usuário.'];
        }
        $sql = 'UPDATE usuarios SET nome = :nome, email = :email, tipo = :tipo';
        $params = [':nome' => $nome, ':email' => $email, ':tipo' => $tipo, ':id' => $id];
        if ($senha !== '') {
            if (strlen($senha) < 6) return ['status' => 2, 'message' => 'A nova senha deve ter no mínimo 6 caracteres.'];
            $sql .= ', senha = :senha';
            $params[':senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }
        $stmt = $this->pdo->prepare($sql . ' WHERE id = :id');
        $stmt->execute($params);
        return ['status' => 1, 'message' => 'Usuário atualizado com sucesso.'];
    }

    public function alternar_ativo_usuario(int $id, int $ativo): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET ativo = :ativo WHERE id = :id');
        $stmt->execute([':ativo' => $ativo ? 1 : 0, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function excluir_usuario(int $id): bool
    {
        $this->pdo->beginTransaction();

        try {
            $desvincular = $this->pdo->prepare('UPDATE alunos SET id_apoio = NULL WHERE id_apoio = :id');
            $desvincular->execute([':id' => $id]);

            $excluir = $this->pdo->prepare('DELETE FROM usuarios WHERE id = :id');
            $excluir->execute([':id' => $id]);
            $excluido = $excluir->rowCount() > 0;

            $this->pdo->commit();
            return $excluido;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function obter_foto(int $id): ?string
    {
        $stmt = $this->pdo->prepare('SELECT foto FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $foto = $stmt->fetchColumn();
        return $foto !== false ? ($foto ?: null) : null;
    }

    public function atualizar_foto(int $id, string $foto): ?string
    {
        $anterior = $this->obter_foto($id);
        $stmt = $this->pdo->prepare('UPDATE usuarios SET foto = :foto WHERE id = :id');
        $stmt->execute([':foto' => $foto, ':id' => $id]);
        return $anterior;
    }

    public function remover_foto(int $id): ?string
    {
        $anterior = $this->obter_foto($id);
        $stmt = $this->pdo->prepare('UPDATE usuarios SET foto = NULL WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $anterior;
    }
}
