<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Autorizacao.php';

$dados = json_decode(file_get_contents("php://input"), true);
$id = (int)($dados['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}
$usuario = currentUser();
$autorizacao = new Autorizacao();
$contexto = $pdo->prepare('SELECT aluno_id FROM pei WHERE id = :id LIMIT 1');
$contexto->execute([':id' => $id]);
$alunoId = (int) $contexto->fetchColumn();
if (!$alunoId || !$autorizacao->podeEditarPlano((string) $usuario['tipo'], (int) $usuario['id'], $alunoId)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado ao documento.']);
    exit;
}

$sql = "DELETE FROM pei WHERE id = :id";
$stmt = $pdo->prepare($sql);
$res = $stmt->execute([':id' => $id]);

echo json_encode([
    'success' => $res && $stmt->rowCount() === 1,
    'message' => $res && $stmt->rowCount() === 1 ? 'PEI excluído com sucesso.' : 'O PEI não foi encontrado ou já foi excluído.'
]);
