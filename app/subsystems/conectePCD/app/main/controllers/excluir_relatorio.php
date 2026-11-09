<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$dados = json_decode(file_get_contents("php://input"), true);
$id = (int)($dados['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$sql = "DELETE FROM relatorios_acompanhamento WHERE id = :id";
$stmt = $pdo->prepare($sql);
$res = $stmt->execute([':id' => $id]);

echo json_encode(['success' => (bool)$res]);
