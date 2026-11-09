<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function dadosRequisicaoCondicao(): array
{
    $dados = json_decode(file_get_contents('php://input'), true);
    return is_array($dados) ? $dados : $_POST;
}

function validarCondicao(array $dados): array
{
    $nome = trim((string) ($dados['nome'] ?? ''));
    $sigla = strtoupper(trim((string) ($dados['sigla'] ?? '')));
    $descricao = trim((string) ($dados['descricao'] ?? ''));

    if ($nome === '' || $sigla === '' || $descricao === '') {
        throw new InvalidArgumentException('Preencha nome, sigla e descrição.');
    }
    if (mb_strlen($nome) > 150 || mb_strlen($sigla) > 30 || mb_strlen($descricao) > 500) {
        throw new InvalidArgumentException('Um dos campos excede o limite permitido.');
    }
    if (!preg_match('/^[A-Z0-9.-]+$/', $sigla)) {
        throw new InvalidArgumentException('A sigla deve conter apenas letras, números, ponto ou hífen.');
    }

    return [$nome, $sigla, $descricao];
}

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id, nome, sigla, descricao, ativo, criado_em
            FROM diagnosticos
            ORDER BY nome');
        echo json_encode(['success' => true, 'condicoes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    $dados = dadosRequisicaoCondicao();
    $id = (int) ($_GET['id'] ?? $dados['id'] ?? 0);

    if ($method === 'POST') {
        [$nome, $sigla, $descricao] = validarCondicao($dados);
        $stmt = $pdo->prepare('INSERT INTO diagnosticos (nome, sigla, descricao) VALUES (?, ?, ?)');
        $stmt->execute([$nome, $sigla, $descricao]);
        echo json_encode(['success' => true, 'message' => 'Condição geral criada com sucesso.', 'id' => (int) $pdo->lastInsertId()]);
        exit;
    }

    if ($id <= 0) {
        throw new InvalidArgumentException('Informe uma condição geral válida.');
    }

    if ($method === 'PUT') {
        [$nome, $sigla, $descricao] = validarCondicao($dados);
        $stmt = $pdo->prepare('UPDATE diagnosticos SET nome = ?, sigla = ?, descricao = ? WHERE id = ?');
        $stmt->execute([$nome, $sigla, $descricao, $id]);
        echo json_encode(['success' => true, 'message' => 'Condição geral atualizada com sucesso.']);
        exit;
    }

    if ($method === 'PATCH' && ($dados['acao'] ?? '') === 'status') {
        $stmt = $pdo->prepare('UPDATE diagnosticos SET ativo = NOT ativo WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Condição geral não encontrada.');
        }
        echo json_encode(['success' => true, 'message' => 'Status da condição geral atualizado.']);
        exit;
    }

    if ($method === 'DELETE') {
        $stmt = $pdo->prepare('DELETE FROM diagnosticos WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Condição geral não encontrada.');
        }
        echo json_encode(['success' => true, 'message' => 'Condição geral excluída definitivamente.']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(422);
    $message = str_contains($e->getMessage(), 'Duplicate entry')
        ? 'Já existe uma condição geral com este nome ou sigla.'
        : 'Não foi possível salvar a condição geral.';
    echo json_encode(['success' => false, 'message' => $message]);
} catch (Throwable $e) {
    error_log('Erro ao gerenciar condições gerais: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível concluir a operação.']);
}