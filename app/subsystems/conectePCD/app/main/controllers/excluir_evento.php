<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Agenda.php';

$dados = json_decode(file_get_contents('php://input'), true);
$id = (int)($dados['id'] ?? 0);
if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'ID inválido']); exit; }
$model = new Agenda($pdo);
$res = $model->excluir($id);
echo json_encode(['success' => (bool)$res]);
