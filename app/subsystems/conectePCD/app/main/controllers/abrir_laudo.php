<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([]);
    exit;
}

$sql = 'SELECT * FROM laudos WHERE id = :id LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if ($registro) {
    $registro['url'] = '../uploads/laudos/' . $registro['arquivo'];
}

echo json_encode($registro ?: []);
