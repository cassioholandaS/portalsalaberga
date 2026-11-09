<?php

require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = $GLOBALS['pdo'] ?? $GLOBALS['conn'];

        if (!$this->pdo instanceof PDO) {
            throw new RuntimeException('PDO não foi inicializado para o modelo de usuários.');
        }
    }

    public function primeiroAcesso(string $nome, string $matricula, string $email, string $senha, string $tipo): int
    {
        $nome = trim($nome);
        $matricula = trim($matricula);
        $email = trim(strtolower($email));
        $tipo = trim($tipo);

        if ($nome === '' || $matricula === '' || $email === '' || $senha === '') {
            return 3;
        }

        if (!preg_match('/^\d{7}$/', $matricula)) {
            return 3;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 3;
        }

        if (!in_array($tipo, ['master', 'aee', 'apoio', 'responsavel'], true)) {
            return 3;
        }

        if (strlen($senha) < 6) {
            return 3;
        }

        $check = $this->pdo->prepare('SELECT 1 FROM usuarios WHERE email = :email OR matricula = :matricula LIMIT 1');
        $check->execute([
            ':email' => $email,
            ':matricula' => $matricula,
        ]);

        if ($check->fetchColumn()) {
            return 2;
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, matricula, email, senha, tipo, status_cadastro, ativo, criado_em) VALUES (:nome, :matricula, :email, :senha, :tipo, :status_cadastro, :ativo, NOW())'
        );

        $ok = $stmt->execute([
            ':nome' => $nome,
            ':matricula' => $matricula,
            ':email' => $email,
            ':senha' => $hash,
            ':tipo' => $tipo,
            ':status_cadastro' => 'aprovado',
            ':ativo' => 1,
        ]);

        return $ok ? 1 : 3;
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
}
