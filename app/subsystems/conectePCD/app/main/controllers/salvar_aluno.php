<?php

require_once __DIR__ . '/../auth.php';
$action = $_REQUEST['action'] ?? '';
requireLogin($action === 'atualizar_perfil_aluno' ? ['master', 'aee'] : ['master', 'aee', 'apoio']);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Aluno.php';

if ($action === 'atualizar_perfil_aluno') {
    header('Content-Type: application/json; charset=utf-8');

    $novaFoto = null;
    try {
        $alunoId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $matricula = trim((string) ($_POST['matricula'] ?? ''));
        $cpfDigitos = preg_replace('/\D/', '', (string) ($_POST['cpf'] ?? ''));
        $dataNascimento = trim((string) ($_POST['data_nascimento'] ?? ''));
        $sexo = trim((string) ($_POST['sexo'] ?? ''));
        $turma = trim((string) ($_POST['turma'] ?? ''));
        $curso = trim((string) ($_POST['curso'] ?? ''));
        $diagnosticosInformados = $_POST['diagnostico'] ?? [];
        if (!is_array($diagnosticosInformados)) {
            $diagnosticosInformados = [$diagnosticosInformados];
        }
        $diagnosticosInformados = array_values(array_unique(array_filter(array_map(
            static fn($valor): string => is_string($valor) ? trim($valor) : '',
            $diagnosticosInformados
        ))));
        $apoioInformado = trim((string) ($_POST['id_apoio'] ?? ''));
        $removerFoto = ($_POST['remover_foto'] ?? '0') === '1';

        if (!$alunoId || !preg_match('/^.{3,100}$/us', $nome)) {
            throw new InvalidArgumentException('Informe um nome válido para o aluno.');
        }
        if (!preg_match('/^\d{7}$/', $matricula)) {
            throw new InvalidArgumentException('A matrícula deve conter exatamente 7 números.');
        }
        if (strlen($cpfDigitos) !== 11) {
            throw new InvalidArgumentException('Informe um CPF com 11 números.');
        }
        $dataValida = DateTime::createFromFormat('!Y-m-d', $dataNascimento);
        if (!$dataValida || $dataValida->format('Y-m-d') !== $dataNascimento || $dataNascimento > date('Y-m-d')) {
            throw new InvalidArgumentException('A data de nascimento deve ser válida e igual ou anterior a hoje.');
        }
        if ($sexo === '' || !preg_match('/^.{1,20}$/us', $sexo)) {
            throw new InvalidArgumentException('Informe um sexo válido.');
        }
        if (!preg_match('/^.{1,100}$/us', $turma) || !preg_match('/^.{1,150}$/us', $curso) || !$diagnosticosInformados) {
            throw new InvalidArgumentException('Informe turma, curso e ao menos uma condição geral.');
        }

        $colunasDiagnosticos = $pdo->query('SHOW COLUMNS FROM diagnosticos')->fetchAll(PDO::FETCH_COLUMN);
        $temSiglaDiagnostico = in_array('sigla', $colunasDiagnosticos, true);
        $colunaValorDiagnostico = $temSiglaDiagnostico ? "COALESCE(NULLIF(sigla, ''), nome)" : 'nome';
        $opcoesDiagnosticos = $pdo->query("SELECT {$colunaValorDiagnostico} AS valor FROM diagnosticos WHERE ativo = 1")->fetchAll(PDO::FETCH_COLUMN);
        $opcoesDiagnosticos = array_fill_keys(array_map('strtolower', $opcoesDiagnosticos), true);
        $diagnosticos = [];
        foreach ($diagnosticosInformados as $diagnostico) {
            $chaveDiagnostico = strtolower($diagnostico);
            if (!isset($opcoesDiagnosticos[$chaveDiagnostico])) {
                throw new InvalidArgumentException('Uma ou mais condições selecionadas não estão cadastradas ou ativas.');
            }
            $diagnosticos[] = $diagnostico;
        }
        if (strlen(implode(', ', $diagnosticos)) > 2000) {
            throw new InvalidArgumentException('A seleção de condições gerais é muito extensa.');
        }

        $stmtMatricula = $pdo->prepare('SELECT id FROM alunos WHERE matricula = ? AND id <> ? LIMIT 1');
        $stmtMatricula->execute([$matricula, $alunoId]);
        if ($stmtMatricula->fetchColumn()) {
            throw new InvalidArgumentException('Esta matrícula já está cadastrada para outro aluno.');
        }

        $stmtAluno = $pdo->prepare('SELECT id, foto, id_apoio FROM alunos WHERE id = ? LIMIT 1');
        $stmtAluno->execute([$alunoId]);
        $alunoAnterior = $stmtAluno->fetch(PDO::FETCH_ASSOC);
        if (!$alunoAnterior) {
            throw new InvalidArgumentException('Aluno não encontrado.');
        }

        $apoioId = null;
        if ($apoioInformado !== '') {
            $apoioId = filter_var($apoioInformado, FILTER_VALIDATE_INT);
            if (!$apoioId || $apoioId <= 0) {
                throw new InvalidArgumentException('Selecione um profissional de apoio válido.');
            }
            $stmtApoio = $pdo->prepare("SELECT id, ativo, status_cadastro FROM usuarios WHERE id = ? AND tipo = 'apoio' LIMIT 1");
            $stmtApoio->execute([$apoioId]);
            $apoio = $stmtApoio->fetch(PDO::FETCH_ASSOC);
            if (!$apoio) {
                throw new InvalidArgumentException('O profissional de apoio selecionado não foi encontrado.');
            }
            if ((int) $apoioId !== (int) ($alunoAnterior['id_apoio'] ?? 0)) {
                if ((int) $apoio['ativo'] !== 1 || $apoio['status_cadastro'] !== 'aprovado') {
                    throw new InvalidArgumentException('O profissional de apoio selecionado não está disponível.');
                }
                $stmtVinculo = $pdo->prepare('SELECT id FROM alunos WHERE id_apoio = ? AND id <> ? LIMIT 1');
                $stmtVinculo->execute([$apoioId, $alunoId]);
                if ($stmtVinculo->fetchColumn()) {
                    throw new InvalidArgumentException('Este profissional de apoio já está vinculado a outro aluno.');
                }
            }
        }

        $arquivo = $_FILES['foto'] ?? null;
        $temNovaFoto = $arquivo && $arquivo['error'] !== UPLOAD_ERR_NO_FILE;
        if ($temNovaFoto && $removerFoto) {
            throw new InvalidArgumentException('Escolha uma nova foto ou marque a remoção da foto atual, não ambos.');
        }
        $foto = $alunoAnterior['foto'] ?: null;
        if ($removerFoto) {
            $foto = null;
        } elseif ($temNovaFoto) {
            $mimePermitido = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];
            $info = $arquivo['error'] === UPLOAD_ERR_OK ? @getimagesize($arquivo['tmp_name']) : false;
            if (!$info || !isset($mimePermitido[$info['mime']]) || $arquivo['size'] > 5 * 1024 * 1024) {
                throw new InvalidArgumentException('Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.');
            }

            $pastaFotos = __DIR__ . '/../uploads/fotos_alunos';
            if (!is_dir($pastaFotos) && !mkdir($pastaFotos, 0755, true)) {
                throw new RuntimeException('Não foi possível preparar a pasta de fotos do aluno.');
            }
            $nomeArquivo = 'aluno_' . $alunoId . '_' . bin2hex(random_bytes(6)) . '.' . $mimePermitido[$info['mime']];
            $novaFoto = 'uploads/fotos_alunos/' . $nomeArquivo;
            if (!move_uploaded_file($arquivo['tmp_name'], __DIR__ . '/../' . $novaFoto)) {
                $novaFoto = null;
                throw new RuntimeException('Não foi possível salvar a nova foto do aluno.');
            }
            $foto = $novaFoto;
        }

        $cpf = substr($cpfDigitos, 0, 3) . '.' . substr($cpfDigitos, 3, 3) . '.' . substr($cpfDigitos, 6, 3) . '-' . substr($cpfDigitos, 9, 2);
        $model = new Aluno($pdo);
        $salvo = $model->atualizarPerfil((int) $alunoId, [
            'nome' => $nome,
            'matricula' => $matricula,
            'cpf' => $cpf,
            'data_nascimento' => $dataNascimento,
            'sexo' => $sexo,
            'turma' => $turma,
            'curso' => $curso,
            'diagnostico' => implode(', ', $diagnosticos),
            'id_apoio' => $apoioId,
            'foto' => $foto,
        ]);
        if (!$salvo) {
            throw new RuntimeException('Não foi possível atualizar o perfil do aluno.');
        }

        if ($foto !== ($alunoAnterior['foto'] ?: null) && $alunoAnterior['foto']) {
            $pastaAnterior = realpath(__DIR__ . '/../uploads/fotos_alunos');
            $arquivoAnterior = realpath(__DIR__ . '/../' . ltrim((string) $alunoAnterior['foto'], '/\\'));
            if ($pastaAnterior && $arquivoAnterior && strpos($arquivoAnterior, $pastaAnterior . DIRECTORY_SEPARATOR) === 0 && is_file($arquivoAnterior) && !unlink($arquivoAnterior)) {
                error_log('Não foi possível remover a foto anterior do aluno ' . (int) $alunoId . '.');
            }
        }

        echo json_encode(['success' => true, 'message' => 'Perfil do aluno atualizado com sucesso.', 'foto' => $foto]);
    } catch (InvalidArgumentException $e) {
        if ($novaFoto) {
            $arquivoNovo = __DIR__ . '/../' . $novaFoto;
            if (is_file($arquivoNovo) && !unlink($arquivoNovo)) {
                error_log('Não foi possível limpar o arquivo de foto após erro de validação do aluno.');
            }
        }
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } catch (Throwable $e) {
        if ($novaFoto) {
            $arquivoNovo = __DIR__ . '/../' . $novaFoto;
            if (is_file($arquivoNovo) && !unlink($arquivoNovo)) {
                error_log('Não foi possível limpar o arquivo de foto após erro ao atualizar o aluno.');
            }
        }
        error_log('Erro ao atualizar perfil do aluno: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Não foi possível atualizar o perfil do aluno.']);
    }
    exit;
}

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

    $dataNascimento = trim((string) ($dados['data_nascimento'] ?? ''));
    $dataNascimentoValida = DateTime::createFromFormat('!Y-m-d', $dataNascimento);
    if (!$dataNascimentoValida || $dataNascimentoValida->format('Y-m-d') !== $dataNascimento || $dataNascimento > date('Y-m-d')) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'A data de nascimento deve ser válida e igual ou anterior a hoje.']);
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
        'data_nascimento' => $dataNascimento,
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