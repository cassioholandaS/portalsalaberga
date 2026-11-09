<?php

session_start();

require_once __DIR__ . '/../models/usuario_model.php';

$action = $_REQUEST['action'] ?? '';

if (in_array($action, ['listar_solicitacoes', 'decidir_solicitacao'], true)) {
    header('Content-Type: application/json; charset=utf-8');

    if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['tipo'] ?? '', ['master', 'aee'], true)) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'erro' => 'Apenas o master pode gerenciar solicitações.']);
        exit;
    }

    try {
        $usuarioModel = new usuario_model();

        if ($action === 'listar_solicitacoes') {
            echo json_encode(['sucesso' => true, 'dados' => $usuarioModel->listar_solicitacoes_cadastro()]);
            exit;
        }

        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
        $decisao = trim($_POST['decisao'] ?? '');
        $ok = $usuarioModel->decidir_solicitacao_cadastro($usuarioId, $decisao);
        echo json_encode(['sucesso' => $ok, 'mensagem' => $ok ? 'Solicitação atualizada.' : 'Solicitação inválida ou já decidida.']);
        exit;
    } catch (Throwable $e) {
        error_log('Erro ao gerenciar solicitação de cadastro: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['sucesso' => false, 'erro' => 'Erro interno ao processar solicitação.']);
        exit;
    }
}

$acoesGerenciamento = [
    'listar_usuarios', 'cadastrar_usuario', 'atualizar_usuario',
    'alternar_usuario', 'excluir_usuario', 'decidir_usuario'
];
if (in_array($action, $acoesGerenciamento, true)) {
    header('Content-Type: application/json; charset=utf-8');

    if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['tipo'] ?? '', ['master', 'aee'], true)) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'erro' => 'Apenas o master pode gerenciar usuários.']);
        exit;
    }

    try {
        $usuarioModel = new usuario_model();
        $dados = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        if ($action === 'listar_usuarios') {
            $pesquisa = trim((string) ($_GET['pesquisa'] ?? ''));
            $ativo = ($_GET['ativo'] ?? '') === '' ? null : (int) $_GET['ativo'];
            echo json_encode([
                'sucesso' => true,
                'usuarios' => $usuarioModel->listar_usuarios($pesquisa !== '' ? $pesquisa : null, $ativo, (int) $_SESSION['usuario_id']),
                'solicitacoes' => $usuarioModel->listar_solicitacoes_cadastro(),
            ]);
            exit;
        }

        $id = (int) ($dados['id'] ?? 0);
        if ($action === 'cadastrar_usuario') {
            $resultado = $usuarioModel->cadastrar_usuario($dados);
        } elseif ($action === 'atualizar_usuario') {
            if ($id <= 0 || $id === (int) $_SESSION['usuario_id']) {
                throw new InvalidArgumentException('Não é permitido editar a própria conta nesta tela.');
            }
            $resultado = $usuarioModel->atualizar_usuario($id, $dados);
        } elseif ($action === 'alternar_usuario') {
            if ($id <= 0 || $id === (int) $_SESSION['usuario_id']) {
                throw new InvalidArgumentException('Não é permitido alterar a própria conta.');
            }
            $resultado = ['status' => $usuarioModel->alternar_ativo_usuario($id, (int) ($dados['ativo'] ?? 0)) ? 1 : 2, 'message' => 'Status atualizado.'];
        } elseif ($action === 'excluir_usuario') {
            if ($id <= 0 || $id === (int) $_SESSION['usuario_id']) {
                throw new InvalidArgumentException('Não é permitido excluir a própria conta.');
            }
            $resultado = ['status' => $usuarioModel->excluir_usuario($id) ? 1 : 2, 'message' => 'Usuário excluído.'];
        } else {
            $decisao = trim((string) ($dados['decisao'] ?? ''));
            $resultado = ['status' => $usuarioModel->decidir_solicitacao_cadastro($id, $decisao) ? 1 : 2, 'message' => 'Solicitação atualizada.'];
        }

        echo json_encode([
            'sucesso' => ($resultado['status'] ?? 2) === 1,
            'message' => $resultado['message'] ?? null,
            'erro' => ($resultado['status'] ?? 2) === 1 ? null : ($resultado['message'] ?? 'Não foi possível concluir a operação.'),
        ]);
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}

if (in_array($action, ['upload_foto', 'remover_foto'], true)) {
    header('Content-Type: application/json; charset=utf-8');
    $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
    if ($idUsuario <= 0) {
        http_response_code(401);
        echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada.']);
        exit;
    }

    try {
        $usuarioModel = new usuario_model();
        $pasta = __DIR__ . '/../uploads/fotos_usuarios';
        if ($action === 'remover_foto') {
            $anterior = $usuarioModel->remover_foto($idUsuario);
            if ($anterior && is_file(__DIR__ . '/../' . ltrim($anterior, '/'))) @unlink(__DIR__ . '/../' . ltrim($anterior, '/'));
            $_SESSION['foto'] = null;
            echo json_encode(['sucesso' => true, 'message' => 'Foto removida com sucesso.']);
            exit;
        }

        $arquivo = $_FILES['foto'] ?? null;
        $mimePermitido = ['image/jpeg', 'image/png', 'image/webp'];
        $info = $arquivo && $arquivo['error'] === UPLOAD_ERR_OK ? @getimagesize($arquivo['tmp_name']) : false;
        if (!$arquivo || !$info || !in_array($info['mime'], $mimePermitido, true) || $arquivo['size'] > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.');
        }
        if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) throw new RuntimeException('Não foi possível preparar o armazenamento da foto.');
        $nomeArquivo = 'usuario_' . $idUsuario . '_' . bin2hex(random_bytes(6)) . '.jpg';
        $caminhoRelativo = 'uploads/fotos_usuarios/' . $nomeArquivo;
        $caminhoCompleto = __DIR__ . '/../' . $caminhoRelativo;
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) throw new RuntimeException('Não foi possível salvar a foto.');
        $anterior = $usuarioModel->atualizar_foto($idUsuario, $caminhoRelativo);
        if ($anterior && is_file(__DIR__ . '/../' . ltrim($anterior, '/'))) @unlink(__DIR__ . '/../' . ltrim($anterior, '/'));
        $_SESSION['foto'] = $caminhoRelativo;
        echo json_encode(['sucesso' => true, 'foto' => $caminhoRelativo, 'message' => 'Foto atualizada com sucesso.']);
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'login') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        header('Location: ../index.php?erro=campos_vazios');
        exit;
    }

    try {
        $usuarioModel = new usuario_model();
        $resultado = $usuarioModel->login($email, $senha);

        if ($resultado['status'] !== 1 || empty($resultado['usuario'])) {
            $codigo = $resultado['status'] ?? 0;
            header('Location: ../index.php?erro=' . $codigo);
            exit;
        }

        $_SESSION['usuario_id'] = (int) $resultado['usuario']['id'];
        $_SESSION['nome'] = $resultado['usuario']['nome'];
        $_SESSION['email'] = $resultado['usuario']['email'];
        $_SESSION['tipo'] = $resultado['usuario']['tipo'] ?? 'responsavel';
        $_SESSION['foto'] = $resultado['usuario']['foto'] ?? null;

        header('Location: ../index.php?login=sucesso');
        exit;
    } catch (Throwable $e) {
        error_log('Erro no login do usuário: ' . $e->getMessage());
        header('Location: ../index.php?erro=erro_servidor');
        exit;
    }
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ../index.php');
    exit;
}

if ($action === 'primeiro_acesso') {
    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $tipo = trim($_POST['tipo'] ?? 'responsavel');

    if ($nome === '' || $email === '' || $senha === '' || $tipo === '' || $cpf === '' || $telefone === '') {
        header('Location: ../index.php?erro=campos_vazios');
        exit;
    }

    try {
        $usuarioModel = new usuario_model();
        $resultado = $usuarioModel->primeiro_acesso($nome, $email, $senha, $tipo, $cpf, $telefone);

        if ($resultado === 1) {
            header('Location: ../index.php?sucesso=primeiro_acesso');
            exit;
        }

        if ($resultado === 2) {
            header('Location: ../index.php?erro=usuario_ja_existe');
            exit;
        }

        if ($resultado === 4) {
            header('Location: ../index.php?erro=cpf_invalido');
            exit;
        }

        if ($resultado === 5) {
            header('Location: ../index.php?erro=telefone_invalido');
            exit;
        }

        header('Location: ../index.php?erro=erro_servidor');
        exit;
    } catch (Throwable $e) {
        error_log('Erro no primeiro acesso: ' . $e->getMessage());
        header('Location: ../index.php?erro=erro_servidor');
        exit;
    }
}

header('Location: ../index.php');
exit;
