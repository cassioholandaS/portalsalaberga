<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$alunoId = (int)($_GET['aluno_id'] ?? 0);

$sql = "SELECT *
        FROM relatorios_acompanhamento
        WHERE aluno_id = :aluno_id
        ORDER BY data_atendimento DESC, id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':aluno_id' => $alunoId
]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));