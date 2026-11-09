<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio', 'responsavel']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Autorizacao.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([]);
    exit;
}

$sql = 'SELECT * FROM pei WHERE id = :id LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);
if ($registro) {
    $usuario = currentUser();
    $autorizacao = new Autorizacao();
    if (!$autorizacao->podeConsultarPlano((string) $usuario['tipo'], (int) $usuario['id'], (int) $registro['aluno_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado ao documento.']);
        exit;
    }
}
echo json_encode($registro ?: []);
