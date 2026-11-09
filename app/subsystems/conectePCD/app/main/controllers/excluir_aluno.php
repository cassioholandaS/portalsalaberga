<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$dados = json_decode(file_get_contents("php://input"), true);

$id = $dados['id'] ?? 0;

$sql = "DELETE FROM alunos WHERE id = ?";

$stmt = $pdo->prepare($sql);

$resultado = $stmt->execute([$id]);

echo json_encode([
    'success' => $resultado
]);