<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(array $allowedRoles = ['master', 'aee', 'apoio', 'responsavel']): void
{
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Não autenticado.']);
        exit;
    }

    $tipoAtual = $_SESSION['tipo'] ?? 'responsavel';
    if (!empty($allowedRoles) && !in_array($tipoAtual, $allowedRoles, true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Sem permissão para acessar este recurso.']);
        exit;
    }
}

function currentUser(): ?array
{
    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }

    return [
        'id' => (int) ($_SESSION['usuario_id'] ?? 0),
        'nome' => $_SESSION['nome'] ?? 'Usuário',
        'email' => $_SESSION['email'] ?? '',
        'tipo' => $_SESSION['tipo'] ?? 'responsavel',
    ];
}
