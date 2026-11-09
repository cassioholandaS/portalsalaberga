<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio']);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Aluno.php';

$action = $_REQUEST['action'] ?? '';

if (in_array($action, ['upload_foto_aluno', 'remover_foto_aluno'], true)) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['tipo'] ?? '', ['master', 'aee'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'erro' => 'Sem permissão para alterar a foto do aluno.']);
            exit;
        }

        $alunoId = (int) ($_REQUEST['aluno_id'] ?? 0);
        if ($alunoId <= 0) {
            throw new InvalidArgumentException('Aluno inválido.');
        }

        $stmtAluno = $pdo->prepare('SELECT id, foto, id_apoio FROM alunos WHERE id = ? LIMIT 1');
        $stmtAluno->execute([$alunoId]);
        $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);
        if (!$aluno) {
            throw new InvalidArgumentException('Aluno não encontrado.');
        }

        if ($action === 'remover_foto_aluno') {
            $fotoAnterior = $aluno['foto'] ?? null;
            $stmtUpdate = $pdo->prepare('UPDATE alunos SET foto = NULL WHERE id = ?');
            $stmtUpdate->execute([$alunoId]);
            if ($fotoAnterior && is_file(__DIR__ . '/../' . ltrim($fotoAnterior, '/'))) {
                @unlink(__DIR__ . '/../' . ltrim($fotoAnterior, '/'));
            }
            echo json_encode(['success' => true, 'foto' => null, 'message' => 'Foto removida com sucesso.']);
            exit;
        }

        $arquivo = $_FILES['foto'] ?? null;
        $mimePermitido = ['image/jpeg', 'image/png', 'image/webp'];
        $info = $arquivo && $arquivo['error'] === UPLOAD_ERR_OK ? @getimagesize($arquivo['tmp_name']) : false;
        if (!$arquivo || !$info || !in_array($info['mime'], $mimePermitido, true) || $arquivo['size'] > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.');
        }

        $pasta = __DIR__ . '/../uploads/fotos_alunos';
        if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
            throw new RuntimeException('Não foi possível preparar a pasta de fotos do aluno.');
        }

        $nomeArquivo = 'aluno_' . $alunoId . '_' . bin2hex(random_bytes(6)) . '.jpg';
        $caminhoRelativo = 'uploads/fotos_alunos/' . $nomeArquivo;
        $caminhoCompleto = __DIR__ . '/../' . $caminhoRelativo;
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            throw new RuntimeException('Não foi possível salvar a foto do aluno.');
        }

        $fotoAnterior = $aluno['foto'] ?? null;
        $stmtUpdate = $pdo->prepare('UPDATE alunos SET foto = ? WHERE id = ?');
        $stmtUpdate->execute([$caminhoRelativo, $alunoId]);

        if ($fotoAnterior && is_file(__DIR__ . '/../' . ltrim($fotoAnterior, '/'))) {
            @unlink(__DIR__ . '/../' . ltrim($fotoAnterior, '/'));
        }

        echo json_encode(['success' => true, 'foto' => $caminhoRelativo, 'message' => 'Foto do aluno atualizada com sucesso.']);
        exit;
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'erro' => $e->getMessage()]);
        exit;
    }
}

try {

    // Accept only JSON payload (foto removed)
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    if (!$dados) {
        echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido']); exit;
    }

    $matricula = trim($dados['matricula'] ?? '');

    if (!preg_match('/^\d{7}$/', $matricula)) {

        echo json_encode([
            'success' => false,
            'message' => 'A matrícula deve conter exatamente 7 números.'
        ]);

        exit;
    }

    $model = new Aluno($pdo);
    $curso = trim((string) ($dados['curso'] ?? ''));
    $diagnosticos = $dados['diagnostico'] ?? [];
    if (!is_array($diagnosticos)) {
        $diagnosticos = [$diagnosticos];
    }
    $diagnosticos = array_values(array_unique(array_filter(array_map(static function ($item) {
        return trim((string) $item);
    }, $diagnosticos))));
    $diagnostico = implode(',', $diagnosticos);
    $apoioId = (int) ($dados['id_apoio'] ?? 0);

    if ($curso === '' || !$diagnosticos) {
        echo json_encode(['success' => false, 'message' => 'Informe curso e diagnóstico.']);
        exit;
    }

    if ($apoioId > 0) {
        $apoioStmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = :id AND tipo = 'apoio' AND ativo = 1 AND status_cadastro = 'aprovado'");
        $apoioStmt->execute([':id' => $apoioId]);
        if (!$apoioStmt->fetchColumn()) {
            echo json_encode(['success' => false, 'message' => 'O apoio selecionado não está disponível.']);
            exit;
        }

        $vinculoStmt = $pdo->prepare('SELECT id FROM alunos WHERE id_apoio = :id_apoio LIMIT 1');
        $vinculoStmt->execute([':id_apoio' => $apoioId]);
        if ($vinculoStmt->fetchColumn()) {
            echo json_encode(['success' => false, 'message' => 'Este apoio já está vinculado a outro aluno PCD.']);
            exit;
        }
    }

    $pdo->prepare('INSERT IGNORE INTO cursos (nome) VALUES (:nome)')->execute([':nome' => $curso]);
    $diagnosticoStmt = $pdo->prepare('INSERT IGNORE INTO diagnosticos (nome) VALUES (:nome)');
    foreach ($diagnosticos as $condicao) {
        $diagnosticoStmt->execute([':nome' => $condicao]);
    }

    $resultado = $model->cadastrar([

        'nome' => $dados['nome'] ?? '',
        'matricula' => $matricula,
        'cpf' => $dados['cpf'] ?? '',
        'data_nascimento' => $dados['data_nascimento'] ?? '',
        'sexo' => $dados['sexo'] ?? '',
        'turma' => $dados['turma'] ?? '',
        'curso' => $curso,
        'diagnostico' => $diagnostico,
        'id_apoio' => $apoioId

    ]);

    echo json_encode([
        'success' => $resultado,
        'message' => $resultado ? 'Aluno salvo com sucesso' : 'Erro ao salvar'
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

}