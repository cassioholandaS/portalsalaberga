<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$dados = json_decode(file_get_contents("php://input"), true);
$id = (int)($dados['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Obtém o registro para excluir arquivo físico, se existir
$stmt = $pdo->prepare('SELECT arquivo, aluno_id FROM laudos WHERE id = :id');
$stmt->execute([':id' => $id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "DELETE FROM laudos WHERE id = :id";
$stmt = $pdo->prepare($sql);
$res = $stmt->execute([':id' => $id]);

if ($res && $registro && !empty($registro['arquivo'])) {
    $referencias = $pdo->prepare('SELECT COUNT(*) FROM laudos WHERE arquivo = :arquivo');
    $referencias->execute([':arquivo' => $registro['arquivo']]);
    if ((int) $referencias->fetchColumn() === 0) {
        $arquivo = str_replace('\\', '/', (string) $registro['arquivo']);
        $segmentosArquivo = explode('/', $arquivo);
        $caminhoNovo = count($segmentosArquivo) === 2
            && ctype_digit($segmentosArquivo[0])
            && $segmentosArquivo[1] !== ''
            && $segmentosArquivo[1] === basename($segmentosArquivo[1])
            && (int) $segmentosArquivo[0] === (int) $registro['aluno_id'];
        $caminhoRelativo = $caminhoNovo ? $arquivo : basename($arquivo);
        $pastaLaudos = dirname(__DIR__) . '/uploads/laudos/';
        $path = $pastaLaudos . $caminhoRelativo;
        if (is_file($path)) unlink($path);
    }
}

echo json_encode(['success' => (bool)$res]);
