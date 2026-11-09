<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['apoio']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

define('AVALIACAO_DATA_FORMATO', '!Y-m-d');

function avaliacaoResponder(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function avaliacaoAluno(PDO $pdo, int $alunoId, int $usuarioId): array
{
    $stmt = $pdo->prepare('SELECT a.id, a.nome, a.matricula, a.turma, a.curso, a.sexo, a.data_nascimento, a.diagnostico, a.foto, a.id_apoio, u.nome AS apoio_nome FROM alunos a LEFT JOIN usuarios u ON u.id = a.id_apoio WHERE a.id = ? LIMIT 1');
    $stmt->execute([$alunoId]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$aluno) avaliacaoResponder(404, ['success' => false, 'message' => 'Aluno não encontrado.']);
    if ((int) $aluno['id_apoio'] !== $usuarioId) {
        avaliacaoResponder(403, ['success' => false, 'message' => 'Você não possui vínculo com este aluno.']);
    }
    return $aluno;
}

function avaliacaoDataValida(string $data): bool
{
    $objeto = DateTime::createFromFormat(AVALIACAO_DATA_FORMATO, $data);
    return $objeto && $objeto->format('Y-m-d') === $data;
}

try {
    $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'GET') {
        $alunoId = (int) ($_GET['aluno_id'] ?? 0);
        if ($alunoId <= 0) {
            $stmtAluno = $pdo->prepare('SELECT id FROM alunos WHERE id_apoio = ? ORDER BY nome LIMIT 1');
            $stmtAluno->execute([$usuarioId]);
            $alunoId = (int) $stmtAluno->fetchColumn();
        }
        if ($alunoId <= 0) avaliacaoResponder(200, ['success' => true, 'aluno' => null, 'quesitos' => [], 'avaliacoes' => [], 'frequencia' => null]);

        $aluno = avaliacaoAluno($pdo, $alunoId, $usuarioId);
        $stmtQuesitos = $pdo->prepare('SELECT q.id, q.titulo, q.descricao, aq.peso FROM alunos_quesitos aq INNER JOIN quesitos_avaliacao q ON q.id = aq.quesito_id WHERE aq.aluno_id = ? AND aq.ativo = 1 AND q.ativo = 1 AND q.excluido = 0 ORDER BY q.titulo');
        $stmtQuesitos->execute([$alunoId]);
        $stmtFrequencia = $pdo->prepare('SELECT presente FROM frequencia_apoio WHERE aluno_id = ? AND data = ? LIMIT 1');
        $stmtFrequencia->execute([$alunoId, date('Y-m-d')]);
        $frequencia = $stmtFrequencia->fetchColumn();

        $stmtRegistros = $pdo->prepare('SELECT r.id, r.data, r.periodo, r.observacao_geral, r.usuario_id, u.nome AS usuario_nome, a.quesito_id, a.avaliacao, a.observacao AS resposta_observacao FROM registros_apoio r INNER JOIN usuarios u ON u.id = r.usuario_id LEFT JOIN respostas_apoio a ON a.registro_id = r.id WHERE r.aluno_id = ? ORDER BY r.data DESC, FIELD(r.periodo, \'manha\', \'tarde\'), r.id DESC');
        $stmtRegistros->execute([$alunoId]);
        $avaliacoes = [];
        foreach ($stmtRegistros->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $chave = $linha['data'] . '|' . $linha['periodo'];
            if (!isset($avaliacoes[$chave])) {
                $avaliacoes[$chave] = [
                    'id' => (int) $linha['id'],
                    'data' => $linha['data'],
                    'periodo' => $linha['periodo'],
                    'observacao_geral' => $linha['observacao_geral'] ?? '',
                    'usuario_nome' => $linha['usuario_nome'] ?? '—',
                    'respostas' => [],
                ];
            }
            if ($linha['quesito_id'] !== null) {
                $avaliacoes[$chave]['respostas'][(string) $linha['quesito_id']] = [
                    'avaliacao' => $linha['avaliacao'],
                    'observacao' => $linha['resposta_observacao'] ?? '',
                ];
            }
        }

        avaliacaoResponder(200, [
            'success' => true,
            'aluno' => $aluno,
            'quesitos' => $stmtQuesitos->fetchAll(PDO::FETCH_ASSOC),
            'avaliacoes' => array_values($avaliacoes),
            'frequencia' => $frequencia === false ? null : (int) $frequencia,
        ]);
    }

    if ($method === 'DELETE') {
        $dados = json_decode(file_get_contents('php://input'), true) ?: [];
        $registroId = (int) ($dados['registro_id'] ?? 0);
        if ($registroId <= 0) avaliacaoResponder(422, ['success' => false, 'message' => 'Informe uma avaliação válida.']);

        $stmtRegistro = $pdo->prepare('SELECT r.id, r.data, r.periodo, r.aluno_id FROM registros_apoio r INNER JOIN alunos a ON a.id = r.aluno_id WHERE r.id = ? AND a.id_apoio = ? LIMIT 1');
        $stmtRegistro->execute([$registroId, $usuarioId]);
        $registro = $stmtRegistro->fetch(PDO::FETCH_ASSOC);
        if (!$registro) avaliacaoResponder(404, ['success' => false, 'message' => 'Avaliação não encontrada ou sem permissão.']);
        if ($registro['data'] !== date('Y-m-d')) {
            avaliacaoResponder(422, ['success' => false, 'message' => 'Somente avaliações do dia atual podem ser excluídas.']);
        }

        $pdo->beginTransaction();
        $pdo->prepare('DELETE FROM respostas_apoio WHERE registro_id = ?')->execute([$registroId]);
        $pdo->prepare('DELETE FROM registros_apoio WHERE id = ? AND aluno_id = ? AND data = ?')->execute([$registroId, (int) $registro['aluno_id'], date('Y-m-d')]);
        $pdo->commit();
        avaliacaoResponder(200, ['success' => true, 'message' => 'Avaliação excluída com sucesso.']);
    }

    if ($method !== 'POST') avaliacaoResponder(405, ['success' => false, 'message' => 'Método não permitido.']);

    $dados = json_decode(file_get_contents('php://input'), true) ?: [];
    $alunoId = (int) ($dados['aluno_id'] ?? 0);
    $periodo = trim((string) ($dados['periodo'] ?? ''));
    $data = trim((string) ($dados['data'] ?? ''));
    $ontem = (new DateTimeImmutable('today'))->modify('-1 day')->format('Y-m-d');
    $respostas = is_array($dados['respostas'] ?? null) ? $dados['respostas'] : [];
    if ($alunoId <= 0 || !in_array($periodo, ['manha', 'tarde'], true) || !avaliacaoDataValida($data) || $data > date('Y-m-d') || !$respostas) {
        avaliacaoResponder(422, ['success' => false, 'message' => 'Informe aluno, data, período e todos os quesitos.']);
    }

    if ($data === date('Y-m-d') && $periodo === 'manha' && (int) date('G') >= 12) {
        avaliacaoResponder(422, ['success' => false, 'message' => 'O período da manhã só pode ser avaliado até as 12:00.']);
    }
    if ($data === date('Y-m-d')) {
        $horaAtual = (int) date('G');
        if ($horaAtual < 7 || $horaAtual >= 17) {
            avaliacaoResponder(422, ['success' => false, 'message' => 'As avaliações do dia só podem ser realizadas entre 07:00 e 17:00.']);
        }
        $periodoAtual = $horaAtual < 12 ? 'manha' : 'tarde';
        if ($periodo !== $periodoAtual) {
            avaliacaoResponder(422, ['success' => false, 'message' => 'O período permitido para o horário atual é ' . ($periodoAtual === 'manha' ? 'Manhã' : 'Tarde') . '.']);
        }
    }

    avaliacaoAluno($pdo, $alunoId, $usuarioId);
    $stmtQuesitos = $pdo->prepare('SELECT q.id, aq.peso FROM alunos_quesitos aq INNER JOIN quesitos_avaliacao q ON q.id = aq.quesito_id WHERE aq.aluno_id = ? AND aq.ativo = 1 AND q.ativo = 1 AND q.excluido = 0');
    $stmtQuesitos->execute([$alunoId]);
    $permitidos = [];
    foreach ($stmtQuesitos->fetchAll(PDO::FETCH_ASSOC) as $quesito) $permitidos[(int) $quesito['id']] = max(1, min(10, (int) $quesito['peso']));
    if (!$permitidos) avaliacaoResponder(422, ['success' => false, 'message' => 'Não há quesitos associados a este aluno.']);

    $idsRecebidos = array_map(static fn($item) => (int) ($item['quesito_id'] ?? 0), $respostas);
    sort($idsRecebidos);
    $idsPermitidos = array_keys($permitidos);
    sort($idsPermitidos);
    if ($idsRecebidos !== $idsPermitidos) avaliacaoResponder(422, ['success' => false, 'message' => 'A avaliação deve conter exatamente os quesitos associados ao aluno.']);

    $stmtExistente = $pdo->prepare('SELECT id FROM registros_apoio WHERE aluno_id = ? AND data = ? AND periodo = ? ORDER BY id DESC LIMIT 1');
    $stmtExistente->execute([$alunoId, $data, $periodo]);
    $registroId = (int) $stmtExistente->fetchColumn();
    if ($data !== date('Y-m-d') && $data !== $ontem && $registroId <= 0) {
        avaliacaoResponder(422, ['success' => false, 'message' => 'Somente hoje e ontem podem receber avaliações ou edições.']);
    }

    $pdo->beginTransaction();
    $foiAtualizacao = $registroId > 0;
    if ($foiAtualizacao) {
        $stmtRegistro = $pdo->prepare('UPDATE registros_apoio SET usuario_id = ?, observacao_geral = ? WHERE id = ?');
        $stmtRegistro->execute([$usuarioId, trim((string) ($dados['observacao_geral'] ?? '')), $registroId]);
        $pdo->prepare('DELETE FROM respostas_apoio WHERE registro_id = ?')->execute([$registroId]);
    } else {
        $stmtRegistro = $pdo->prepare('INSERT INTO registros_apoio (aluno_id, usuario_id, data, periodo, observacao_geral) VALUES (?, ?, ?, ?, ?)');
        $stmtRegistro->execute([$alunoId, $usuarioId, $data, $periodo, trim((string) ($dados['observacao_geral'] ?? ''))]);
        $registroId = (int) $pdo->lastInsertId();
    }

    $valencias = ['triste' => -1, 'neutro' => 0, 'feliz' => 1];
    $stmtResposta = $pdo->prepare('INSERT INTO respostas_apoio (registro_id, quesito_id, avaliacao, nota, valencia, peso, observacao) VALUES (?, ?, ?, NULL, ?, ?, ?)');
    foreach ($respostas as $resposta) {
        $quesitoId = (int) ($resposta['quesito_id'] ?? 0);
        $avaliacao = (string) ($resposta['avaliacao'] ?? '');
        if (!isset($permitidos[$quesitoId], $valencias[$avaliacao])) throw new InvalidArgumentException('Resposta de quesito inválida.');
        $stmtResposta->execute([$registroId, $quesitoId, $avaliacao, $valencias[$avaliacao], $permitidos[$quesitoId], trim((string) ($resposta['observacao'] ?? ''))]);
    }
    $pdo->commit();
    avaliacaoResponder(200, ['success' => true, 'message' => $foiAtualizacao ? 'Avaliação atualizada com sucesso.' : 'Avaliação criada com sucesso.', 'id' => $registroId]);
} catch (InvalidArgumentException | RuntimeException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    avaliacaoResponder(422, ['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Erro na avaliação: ' . $e->getMessage());
    avaliacaoResponder(500, ['success' => false, 'message' => 'Não foi possível concluir a avaliação.']);
}
