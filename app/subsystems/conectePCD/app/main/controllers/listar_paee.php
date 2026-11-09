<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio', 'responsavel']);

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Autorizacao.php';

$alunoId = (int)($_GET['aluno_id'] ?? 0);
$usuario = currentUser();
$autorizacao = new Autorizacao();
if (!$autorizacao->podeConsultarPlano((string) $usuario['tipo'], (int) $usuario['id'], $alunoId)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Acesso negado ao aluno.']);
	exit;
}

$sql = 'SELECT * FROM paee WHERE aluno_id = :aluno_id ORDER BY id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute([':aluno_id' => $alunoId]);

$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($registros);
