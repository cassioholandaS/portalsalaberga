<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

function nomeArquivoLaudo(string $nome): string
{
    $partes = preg_split('/\s+/u', trim($nome), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $normalizar = static function (string $parte): string {
        $parte = mb_strtolower($parte, 'UTF-8');
        $parte = preg_replace('/[^\p{L}\p{N}]+/u', '_', $parte) ?? '';
        return trim(mb_substr($parte, 0, 48, 'UTF-8'), '_') ?: 'aluno';
    };

    $primeiroNome = $normalizar($partes[0] ?? 'aluno');
    $sobrenome = $normalizar($partes[count($partes) - 1] ?? $primeiroNome);
    return $primeiroNome . '_' . $sobrenome;
}

try {
    $destino = null;
    $arquivo = $_FILES['arquivo'] ?? null;
    if (!$arquivo || !is_array($arquivo) || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Selecione um arquivo PDF para enviar.');
    }

    $nomeOriginal = basename((string) ($arquivo['name'] ?? ''));
    if (strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION)) !== 'pdf') {
        throw new InvalidArgumentException('Apenas arquivos com extensão .pdf são aceitos.');
    }

    $arquivoTmp = (string) ($arquivo['tmp_name'] ?? '');
    if ($arquivoTmp === '' || !is_uploaded_file($arquivoTmp)) {
        throw new InvalidArgumentException('O arquivo enviado é inválido.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($arquivoTmp);
    $handle = fopen($arquivoTmp, 'rb');
    $assinatura = $handle ? fread($handle, 5) : false;
    if ($handle) fclose($handle);
    if (!in_array($mime, ['application/pdf', 'application/x-pdf'], true) || $assinatura !== '%PDF-') {
        throw new InvalidArgumentException('O conteúdo do arquivo não é um PDF válido.');
    }

    $alunoId = filter_var($_POST['aluno_id'] ?? null, FILTER_VALIDATE_INT);
    if ($alunoId === false || $alunoId <= 0) {
        throw new InvalidArgumentException('Aluno inválido.');
    }

    $pdo->beginTransaction();
    $alunoStmt = $pdo->prepare('SELECT nome FROM alunos WHERE id = :id FOR UPDATE');
    $alunoStmt->execute([':id' => $alunoId]);
    $aluno = $alunoStmt->fetch(PDO::FETCH_ASSOC);
    if (!$aluno) {
        throw new InvalidArgumentException('Aluno não encontrado.');
    }

    $laudosStmt = $pdo->prepare('SELECT arquivo FROM laudos WHERE aluno_id = :aluno_id ORDER BY id FOR UPDATE');
    $laudosStmt->execute([':aluno_id' => $alunoId]);
    $laudos = $laudosStmt->fetchAll(PDO::FETCH_COLUMN);
    $maiorNumero = 0;
    foreach ($laudos as $arquivoExistente) {
        if (preg_match('/_(\d+)_laudo\.pdf$/i', basename((string) $arquivoExistente), $correspondencia)) {
            $maiorNumero = max($maiorNumero, (int) $correspondencia[1]);
        }
    }
    $numero = max(count($laudos), $maiorNumero) + 1;
    $nomeArquivo = nomeArquivoLaudo((string) $aluno['nome']) . '_' . $numero . '_laudo.pdf';

    $pastaAluno = dirname(__DIR__) . '/uploads/laudos/' . $alunoId;
    if (!is_dir($pastaAluno) && !mkdir($pastaAluno, 0755, true) && !is_dir($pastaAluno)) {
        throw new RuntimeException('Não foi possível preparar a pasta de laudos.');
    }
    $destino = $pastaAluno . '/' . $nomeArquivo;
    if (!move_uploaded_file($arquivoTmp, $destino)) {
        throw new RuntimeException('Não foi possível salvar o arquivo enviado.');
    }

    $caminhoBanco = $alunoId . '/' . $nomeArquivo;
    $stmt = $pdo->prepare('INSERT INTO laudos (aluno_id, arquivo) VALUES (:aluno_id, :arquivo)');
    $stmt->execute([':aluno_id' => $alunoId, ':arquivo' => $caminhoBanco]);
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Laudo salvo com sucesso.']);
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($destino && is_file($destino)) unlink($destino);
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ($destino && is_file($destino)) unlink($destino);
    error_log('Falha ao salvar laudo: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível salvar o laudo.']);
}
