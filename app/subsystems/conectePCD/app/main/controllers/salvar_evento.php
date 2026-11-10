<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio']);

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Agenda.php';

try {
    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);
    if (!$dados) { echo json_encode(['success'=>false,'message'=>'Nenhum dado recebido']); exit; }
    if (empty($dados['titulo']) || empty($dados['data_evento'])) {
        echo json_encode(['success'=>false,'message'=>'Título e data são obrigatórios']); exit;
    }
    $dataEvento = trim((string) $dados['data_evento']);
    $dataValida = DateTime::createFromFormat('!Y-m-d', $dataEvento);
    if (!$dataValida || $dataValida->format('Y-m-d') !== $dataEvento || $dataEvento > date('Y-m-d')) {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'A data do evento deve ser válida e igual ou anterior a hoje.']); exit;
    }
    $model = new Agenda($pdo);
    $res = $model->salvar([
        'titulo' => $dados['titulo'],
        'data_evento' => $dataEvento,
        'horario' => $dados['horario'] ?? null,
        'descricao' => $dados['descricao'] ?? null,
        'status' => 'ativo'
    ]);
    echo json_encode(['success' => (bool)$res]);
} catch(Exception $e){ echo json_encode(['success'=>false,'message'=>$e->getMessage()]); }
