<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $dados = json_decode(file_get_contents('php://input'), true) ?: [];
    $alunoId = (int) ($dados['aluno_id'] ?? 0);
    $apoioId = (int) ($dados['id_apoio'] ?? 0);
    $acao = (string) ($dados['acao'] ?? 'vincular');

    if ($alunoId <= 0) {
        throw new InvalidArgumentException('Selecione um aluno válido.');
    }

    if ($acao === 'desvincular') {
        $stmt = $pdo->prepare('UPDATE alunos SET id_apoio = NULL WHERE id = :aluno_id AND id_apoio IS NOT NULL');
        $stmt->execute([':aluno_id' => $alunoId]);
        if ($stmt->rowCount() === 0) {
            throw new InvalidArgumentException('Este aluno não possui apoio vinculado.');
        }
        echo json_encode(['success' => true, 'message' => 'Apoio desvinculado com sucesso.']);
        exit;
    }

    if ($apoioId <= 0) {
        throw new InvalidArgumentException('Selecione um apoio válido.');
    }

    $apoioStmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = :id AND tipo = 'apoio' AND ativo = 1 AND status_cadastro = 'aprovado'");
    $apoioStmt->execute([':id' => $apoioId]);
    if (!$apoioStmt->fetchColumn()) {
        throw new InvalidArgumentException('O apoio selecionado não está disponível.');
    }

    $vinculoStmt = $pdo->prepare('SELECT id FROM alunos WHERE id_apoio = :id_apoio AND id <> :aluno_id LIMIT 1');
    $vinculoStmt->execute([':id_apoio' => $apoioId, ':aluno_id' => $alunoId]);
    if ($vinculoStmt->fetchColumn()) {
        throw new InvalidArgumentException('Este apoio já está vinculado a outro aluno.');
    }

    $stmt = $pdo->prepare('UPDATE alunos SET id_apoio = :id_apoio WHERE id = :aluno_id');
    $stmt->execute([':id_apoio' => $apoioId, ':aluno_id' => $alunoId]);
    if ($stmt->rowCount() === 0) {
        throw new InvalidArgumentException('Aluno não encontrado ou vínculo já definido.');
    }

    echo json_encode(['success' => true, 'message' => 'Apoio vinculado com sucesso.']);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível vincular o apoio.']);
}