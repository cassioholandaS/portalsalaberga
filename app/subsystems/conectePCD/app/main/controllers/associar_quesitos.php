<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $dados = json_decode(file_get_contents('php://input'), true) ?: [];
    $alunoId = (int) ($_GET['aluno_id'] ?? $dados['aluno_id'] ?? 0);
    if ($alunoId <= 0) {
        throw new InvalidArgumentException('Informe um aluno válido.');
    }

    $stmtAluno = $pdo->prepare('SELECT id_apoio FROM alunos WHERE id = ? LIMIT 1');
    $stmtAluno->execute([$alunoId]);
    $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);
    if (!$aluno) {
        throw new InvalidArgumentException('Aluno não encontrado.');
    }
    if (($_SESSION['tipo'] ?? '') === 'apoio' && (int) $aluno['id_apoio'] !== (int) ($_SESSION['usuario_id'] ?? 0)) {
        http_response_code(403);
        throw new RuntimeException('Você não possui vínculo com este aluno.');
    }

    $tipoUsuario = strtolower(trim($_SESSION['tipo'] ?? ''));

    if ($method === 'GET') {
        $sql = 'SELECT q.id, q.titulo, q.descricao, COALESCE(aq.peso, 1) AS peso, COALESCE(aq.ativo, 0) AS associado FROM quesitos_avaliacao q LEFT JOIN alunos_quesitos aq ON aq.quesito_id = q.id AND aq.aluno_id = ? WHERE q.ativo = 1 AND q.excluido = 0 ORDER BY associado DESC, q.titulo ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$alunoId]);
        echo json_encode(['success' => true, 'quesitos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
        exit;
    }
    $quesitos = is_array($dados['quesitos'] ?? null) ? $dados['quesitos'] : [];
    $ids = [];
    foreach ($quesitos as $item) {
        $id = (int) ($item['quesito_id'] ?? 0);
        $peso = filter_var($item['peso'] ?? 1, FILTER_VALIDATE_INT);
        if ($id <= 0 || $peso === false || $peso < 1 || $peso > 10) {
            throw new InvalidArgumentException('Quesito ou peso inválido.');
        }
        $ids[$id] = $peso;
    }

    $stmtValidos = $pdo->query('SELECT id FROM quesitos_avaliacao WHERE ativo = 1 AND excluido = 0');
    $validos = array_fill_keys(array_map('intval', $stmtValidos->fetchAll(PDO::FETCH_COLUMN)), true);
    foreach ($ids as $id => $peso) {
        if (!isset($validos[$id])) {
            throw new InvalidArgumentException('Existe um quesito inválido na seleção.');
        }
    }

    if ($tipoUsuario === 'apoio') {
        http_response_code(403);
        throw new RuntimeException('Somente o AEE pode definir o peso dos quesitos.');
    }

    $pdo->beginTransaction();
    $stmtDesativar = $pdo->prepare('UPDATE alunos_quesitos SET ativo = 0 WHERE aluno_id = ?');
    $stmtDesativar->execute([$alunoId]);
    $sqlUpsert = $tipoUsuario === 'aee'
        ? 'INSERT INTO alunos_quesitos (aluno_id, quesito_id, ativo, peso) VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE ativo = 1, peso = VALUES(peso)'
        : 'INSERT INTO alunos_quesitos (aluno_id, quesito_id, ativo, peso) VALUES (?, ?, 1, 1) ON DUPLICATE KEY UPDATE ativo = 1';
    $stmtUpsert = $pdo->prepare($sqlUpsert);
    foreach ($ids as $id => $peso) {
        $stmtUpsert->execute($tipoUsuario === 'aee' ? [$alunoId, $id, $peso] : [$alunoId, $id]);
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Quesitos associados ao aluno com sucesso.']);
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Erro ao associar quesitos: ' . $e->getMessage());
    http_response_code(http_response_code() >= 400 ? http_response_code() : 500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível associar os quesitos.']);
}
