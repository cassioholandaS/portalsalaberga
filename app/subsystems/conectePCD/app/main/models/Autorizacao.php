<?php

require_once __DIR__ . '/../config/database.php';

class Autorizacao
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = $GLOBALS['pdo'] ?? $GLOBALS['conn'];

        if (!$this->pdo instanceof PDO) {
            throw new RuntimeException('PDO não foi inicializado para autorização.');
        }
    }

    public function ehMaster(int $usuarioId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE id = :id AND tipo = 'master' LIMIT 1");
        $stmt->execute([':id' => $usuarioId]);
        return (bool) $stmt->fetchColumn();
    }

    public function ehCoordenadorAee(int $usuarioId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE id = :id AND tipo = 'aee' LIMIT 1");
        $stmt->execute([':id' => $usuarioId]);
        return (bool) $stmt->fetchColumn();
    }

    public function ehProfissionalApoio(int $usuarioId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE id = :id AND tipo = 'apoio' LIMIT 1");
        $stmt->execute([':id' => $usuarioId]);
        return (bool) $stmt->fetchColumn();
    }

    public function ehAlunoPcd(int $usuarioId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE id = :id AND tipo = 'responsavel' LIMIT 1");
        $stmt->execute([':id' => $usuarioId]);
        return (bool) $stmt->fetchColumn();
    }

    public function podeGerenciarAlunos(int $usuarioId): bool
    {
        return $this->ehMaster($usuarioId) || $this->ehCoordenadorAee($usuarioId) || $this->ehProfissionalApoio($usuarioId);
    }

    public function usuarioTemAcessoAluno(int $usuarioId, int $alunoId): bool
    {
        if ($alunoId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT 1 FROM alunos WHERE id = :aluno_id AND (id_apoio = :usuario_id_apoio OR EXISTS (SELECT 1 FROM usuarios WHERE id = :usuario_id_usuario AND tipo IN ('master', 'aee'))) LIMIT 1");
        $stmt->execute([
            ':aluno_id' => $alunoId,
            ':usuario_id_apoio' => $usuarioId,
            ':usuario_id_usuario' => $usuarioId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function alunoPertenceAoApoio(int $usuarioId, int $alunoId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM alunos WHERE id = :aluno_id AND id_apoio = :usuario_id LIMIT 1');
        $stmt->execute([
            ':aluno_id' => $alunoId,
            ':usuario_id' => $usuarioId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function disciplinaAssociadaAoAluno(int $alunoId, int $disciplinaId): bool
    {
        if ($alunoId <= 0 || $disciplinaId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT 1 FROM alunos_disciplinas WHERE aluno_id = :aluno_id AND disciplina_id = :disciplina_id AND ativa = 1 LIMIT 1');
        $stmt->execute([
            ':aluno_id' => $alunoId,
            ':disciplina_id' => $disciplinaId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function podeGerenciarPlano(string $tipoUsuario, int $usuarioId, int $alunoId): bool
    {
        if (in_array($tipoUsuario, ['master', 'aee'], true)) {
            return true;
        }

        if ($tipoUsuario === 'apoio') {
            return $this->alunoPertenceAoApoio($usuarioId, $alunoId);
        }

        return false;
    }

    public function podeConsultarPlano(string $tipoUsuario, int $usuarioId, int $alunoId): bool
    {
        return $this->usuarioTemAcessoAluno($usuarioId, $alunoId);
    }

    public function podeEditarPlano(string $tipoUsuario, int $usuarioId, int $alunoId): bool
    {
        return in_array($tipoUsuario, ['master', 'aee'], true)
            && $this->usuarioTemAcessoAluno($usuarioId, $alunoId);
    }

    public function planoPertenceAoContexto(string $tabela, int $planoId, int $alunoId, int $disciplinaId): bool
    {
        if (!in_array($tabela, ['pei', 'paee'], true) || $planoId <= 0 || $alunoId <= 0 || $disciplinaId <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT 1 FROM {$tabela} WHERE id = :id AND aluno_id = :aluno_id AND disciplina_id = :disciplina_id LIMIT 1");
        $stmt->execute([':id' => $planoId, ':aluno_id' => $alunoId, ':disciplina_id' => $disciplinaId]);
        return (bool) $stmt->fetchColumn();
    }

    public function alunoDoApoio(int $usuarioId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM alunos WHERE id_apoio = :usuario_id ORDER BY id LIMIT 1');
        $stmt->execute([':usuario_id' => $usuarioId]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }
}
