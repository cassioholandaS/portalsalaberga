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

// Obtém o registro para excluir arquivo físico, se existir
$stmt = $pdo->prepare('SELECT arquivo FROM laudos WHERE id = :id');
$stmt->execute([':id' => $id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "DELETE FROM laudos WHERE id = :id";
$stmt = $pdo->prepare($sql);
$res = $stmt->execute([':id' => $id]);

if ($res && $registro && !empty($registro['arquivo'])) {
    $path = dirname(__DIR__) . '/uploads/laudos/' . basename($registro['arquivo']);
    if (file_exists($path)) unlink($path);
}

echo json_encode(['success' => (bool)$res]);
