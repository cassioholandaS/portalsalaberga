<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio', 'responsavel']);

require_once __DIR__ . '/../config/database.php';

$tipoUsuario = $_SESSION['tipo'] ?? 'responsavel';
$idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);

if ($tipoUsuario === 'apoio') {
    $sql = "SELECT a.*, u.nome AS apoio_nome
        FROM alunos a
        LEFT JOIN usuarios u ON u.id = a.id_apoio
        WHERE a.id_apoio = ?
        ORDER BY a.nome";
    $params = [$idUsuario];
} else {
    $sql = "SELECT a.*, u.nome AS apoio_nome
        FROM alunos a
        LEFT JOIN usuarios u ON u.id = a.id_apoio
        ORDER BY a.nome";
    $params = [];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($alunos); 