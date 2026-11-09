<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    if (!isset($_FILES['arquivo']) || !is_array($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Arquivo não enviado']);
        exit;
    }

    $alunoId = (int)($_POST['aluno_id'] ?? 0);
    if ($alunoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Aluno inválido']);
        exit;
    }

    $arquivoTmp = $_FILES['arquivo']['tmp_name'];
    $nomeOriginal = basename($_FILES['arquivo']['name']);
    $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

    if ($extensao !== 'pdf') {
        echo json_encode(['success' => false, 'message' => 'Apenas arquivos PDF são aceitos.']);
        exit;
    }

    $pasta = dirname(__DIR__) . '/uploads/laudos/';
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    $nomeArquivo = 'aluno_' . $alunoId . '_' . time() . '.' . $extensao;
    $destino = $pasta . $nomeArquivo;

    if (!move_uploaded_file($arquivoTmp, $destino)) {
        echo json_encode(['success' => false, 'message' => 'Falha ao salvar o arquivo']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO laudos (aluno_id, arquivo) VALUES (:aluno_id, :arquivo)');
    $resultado = $stmt->execute([
        ':aluno_id' => $alunoId,
        ':arquivo' => $nomeArquivo
    ]);

    echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Laudo salvo com sucesso' : 'Erro ao salvar laudo']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
