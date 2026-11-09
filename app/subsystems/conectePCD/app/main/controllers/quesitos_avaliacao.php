<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function dadosRequisicao(): array
{
    $dados = json_decode(file_get_contents('php://input'), true);
    return is_array($dados) ? $dados : $_POST;
}

function validarQuesito(array $dados): array
{
    $titulo = trim((string) ($dados['titulo'] ?? ''));
    $descricao = trim((string) ($dados['descricao'] ?? ''));
    if ($titulo === '' || $descricao === '') {
        throw new InvalidArgumentException('Preencha título e descrição.');
    }
    if (mb_strlen($titulo) > 150 || mb_strlen($descricao) > 1000) {
        throw new InvalidArgumentException('O título ou a descrição excede o limite permitido.');
    }
    return [$titulo, $descricao];
}

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id, titulo, descricao, ativo, criado_em, atualizado_em
            FROM quesitos_avaliacao
            WHERE excluido = 0');
        echo json_encode(['success' => true, 'quesitos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    $dados = dadosRequisicao();
    $id = (int) ($_GET['id'] ?? $dados['id'] ?? 0);

    if ($method === 'POST') {
        [$titulo, $descricao] = validarQuesito($dados);
        $stmt = $pdo->prepare('INSERT INTO quesitos_avaliacao (titulo, descricao) VALUES (?, ?)');
        $stmt->execute([$titulo, $descricao]);
        echo json_encode(['success' => true, 'message' => 'Quesito criado com sucesso.', 'id' => (int) $pdo->lastInsertId()]);
        exit;
    }

    if ($id <= 0) {
        throw new InvalidArgumentException('Informe um quesito válido.');
    }

    if ($method === 'PUT') {
        [$titulo, $descricao] = validarQuesito($dados);
        $stmt = $pdo->prepare('UPDATE quesitos_avaliacao SET titulo = ?, descricao = ? WHERE id = ?');
        $stmt->execute([$titulo, $descricao, $id]);
        echo json_encode(['success' => true, 'message' => 'Quesito atualizado com sucesso.']);
        exit;
    }

    if ($method === 'PATCH' && ($dados['acao'] ?? '') === 'status') {
        $stmt = $pdo->prepare('UPDATE quesitos_avaliacao SET ativo = NOT ativo WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Quesito não encontrado.');
        }
        echo json_encode(['success' => true, 'message' => 'Status do quesito atualizado.']);
        exit;
    }

    if ($method === 'DELETE') {
        $pdo->beginTransaction();
        $stmtExiste = $pdo->prepare('SELECT id FROM quesitos_avaliacao WHERE id = ? LIMIT 1');
        $stmtExiste->execute([$id]);
        if (!$stmtExiste->fetchColumn()) {
            $pdo->rollBack();
            throw new RuntimeException('Quesito não encontrado.');
        }

        $stmtRespostas = $pdo->prepare('DELETE FROM respostas_apoio WHERE quesito_id = ?');
        $stmtRespostas->execute([$id]);
        $stmtAssociacoes = $pdo->prepare('DELETE FROM alunos_quesitos WHERE quesito_id = ?');
        $stmtAssociacoes->execute([$id]);
        $stmt = $pdo->prepare('DELETE FROM quesitos_avaliacao WHERE id = ?');
        $stmt->execute([$id]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Quesito excluído definitivamente.']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('Erro ao gerenciar quesitos de avaliação: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível concluir a operação.']);
}
