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
    $arquivo = str_replace('\\', '/', (string) ($registro['arquivo'] ?? ''));
    $segmentosArquivo = explode('/', $arquivo);
    $caminhoNovo = count($segmentosArquivo) === 2
        && ctype_digit($segmentosArquivo[0])
        && $segmentosArquivo[1] !== ''
        && $segmentosArquivo[1] === basename($segmentosArquivo[1])
        && (int) $segmentosArquivo[0] === (int) $registro['aluno_id'];
    $arquivo = $caminhoNovo ? $arquivo : basename($arquivo);
    $segmentos = array_map('rawurlencode', explode('/', $arquivo));
    $registro['arquivo'] = basename($arquivo);
    $registro['url'] = $arquivo !== '' ? 'uploads/laudos/' . implode('/', $segmentos) : '';
}

echo json_encode($registros);
