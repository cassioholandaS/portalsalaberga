<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio', 'responsavel']);

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Agenda.php';

$model = new Agenda($pdo);
$evs = $model->listar();

echo json_encode($evs);
