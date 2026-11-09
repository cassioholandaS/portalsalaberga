<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['apoio']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function responderFrequenciaApoio(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'GET') {
        $alunoId = (int) ($_GET['aluno_id'] ?? 0);
        $data = trim((string) ($_GET['data'] ?? date('Y-m-d')));

        if ($alunoId <= 0) {
            $stmtAluno = $pdo->prepare('SELECT id FROM alunos WHERE id_apoio = ? ORDER BY nome LIMIT 1');
            $stmtAluno->execute([$usuarioId]);
            $alunoId = (int) $stmtAluno->fetchColumn();
        }
        if ($alunoId <= 0) {
            responderFrequenciaApoio(200, ['success' => true, 'frequencia' => null]);
        }

        $stmtAluno = $pdo->prepare('SELECT id FROM alunos WHERE id = ? AND id_apoio = ? LIMIT 1');
        $stmtAluno->execute([$alunoId, $usuarioId]);
        if (!$stmtAluno->fetchColumn()) {
            responderFrequenciaApoio(403, ['success' => false, 'message' => 'Você não possui vínculo com este aluno.']);
        }

        $dataValida = DateTime::createFromFormat('!Y-m-d', $data);
        if (!$dataValida || $dataValida->format('Y-m-d') !== $data || $data > date('Y-m-d')) {
            responderFrequenciaApoio(422, ['success' => false, 'message' => 'Informe uma data válida, igual ou anterior a hoje.']);
        }

        $stmtFrequencia = $pdo->prepare('SELECT data, presente FROM frequencia_apoio WHERE aluno_id = ? AND data = ? LIMIT 1');
        $stmtFrequencia->execute([$alunoId, $data]);
        $frequencia = $stmtFrequencia->fetch(PDO::FETCH_ASSOC);
        if ($frequencia) {
            $frequencia['presente'] = (int) $frequencia['presente'];
        }

        responderFrequenciaApoio(200, ['success' => true, 'frequencia' => $frequencia ?: null]);
    }

    if ($method !== 'POST') {
        responderFrequenciaApoio(405, ['success' => false, 'message' => 'Método não permitido.']);
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        responderFrequenciaApoio(422, ['success' => false, 'message' => 'Envie os dados de frequência válidos.']);
    }

    $alunoId = filter_var($dados['aluno_id'] ?? null, FILTER_VALIDATE_INT);
    $presente = filter_var($dados['presente'] ?? null, FILTER_VALIDATE_INT);
    $data = trim((string) ($dados['data'] ?? ''));
    $dataValida = DateTime::createFromFormat('!Y-m-d', $data);
    if (!$alunoId || !$dataValida || $dataValida->format('Y-m-d') !== $data || $data > date('Y-m-d') || !in_array($presente, [0, 1], true)) {
        responderFrequenciaApoio(422, ['success' => false, 'message' => 'Informe aluno, data e presença válidos.']);
    }

    $stmtAluno = $pdo->prepare('SELECT id FROM alunos WHERE id = ? AND id_apoio = ? LIMIT 1');
    $stmtAluno->execute([$alunoId, $usuarioId]);
    if (!$stmtAluno->fetchColumn()) {
        responderFrequenciaApoio(403, ['success' => false, 'message' => 'Você não possui vínculo com este aluno.']);
    }

    $pdo->beginTransaction();
    $stmtBloqueioAluno = $pdo->prepare('SELECT id FROM alunos WHERE id = ? AND id_apoio = ? FOR UPDATE');
    $stmtBloqueioAluno->execute([$alunoId, $usuarioId]);
    if (!$stmtBloqueioAluno->fetchColumn()) {
        $pdo->rollBack();
        responderFrequenciaApoio(403, ['success' => false, 'message' => 'Você não possui vínculo com este aluno.']);
    }
    if ($presente === 0) {
        $stmtAvaliacoes = $pdo->prepare('SELECT COUNT(*) FROM registros_apoio WHERE aluno_id = ? AND data = ?');
        $stmtAvaliacoes->execute([$alunoId, $data]);
        if ((int) $stmtAvaliacoes->fetchColumn() > 0) {
            $pdo->rollBack();
            responderFrequenciaApoio(422, ['success' => false, 'message' => 'Este aluno já possui avaliação nesta data. Exclua as avaliações antes de registrar falta.']);
        }
    }

    $stmtSalvar = $pdo->prepare('INSERT INTO frequencia_apoio (aluno_id, usuario_id, data, presente) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE usuario_id = VALUES(usuario_id), presente = VALUES(presente)');
    $stmtSalvar->execute([$alunoId, $usuarioId, $data, $presente]);
    $pdo->commit();

    responderFrequenciaApoio(200, [
        'success' => true,
        'message' => 'Frequência registrada com sucesso.',
        'frequencia' => ['data' => $data, 'presente' => $presente],
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Erro ao registrar frequência do apoio: ' . $e->getMessage());
    responderFrequenciaApoio(500, ['success' => false, 'message' => 'Não foi possível registrar a frequência.']);
}
