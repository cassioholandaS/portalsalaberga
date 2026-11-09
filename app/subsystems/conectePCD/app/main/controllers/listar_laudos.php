<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio', 'responsavel']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$alunoId = (int)($_GET['aluno_id'] ?? 0);

$sql = 'SELECT * FROM laudos WHERE aluno_id = :aluno_id ORDER BY id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute([':aluno_id' => $alunoId]);

$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($registros as &$registro) {
    $arquivo = basename($registro['arquivo'] ?? '');
    $registro['url'] = $arquivo ? '../uploads/laudos/' . $arquivo : '';
}

echo json_encode($registros);
