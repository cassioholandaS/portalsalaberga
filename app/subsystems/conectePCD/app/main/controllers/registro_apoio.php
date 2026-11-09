<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $alunoId = (int) ($_GET['aluno_id'] ?? 0);
    if ($method === 'POST') {
        if (($_SESSION['tipo'] ?? '') !== 'apoio') {
            http_response_code(403);
            throw new RuntimeException('Somente o apoio pode registrar a avaliação.');
        }
        $dados = json_decode(file_get_contents('php://input'), true) ?: [];
        $alunoId = (int) ($dados['aluno_id'] ?? 0);
        $periodo = trim((string) ($dados['periodo'] ?? ''));
        $data = trim((string) ($dados['data'] ?? ''));
        $respostas = is_array($dados['respostas'] ?? null) ? $dados['respostas'] : [];
        if ($alunoId <= 0 || !in_array($periodo, ['manha', 'tarde'], true) || $data === '' || !$respostas) {
            throw new InvalidArgumentException('Informe aluno, período, data e respostas.');
        }
        $dataValida = DateTime::createFromFormat('!Y-m-d', $data);
        if (!$dataValida || $dataValida->format('Y-m-d') !== $data || $data > date('Y-m-d')) {
            throw new InvalidArgumentException('A data do registro não pode ser posterior a hoje.');
        }
        $hoje = date('Y-m-d');
        if ($data === $hoje) {
            $horaAtual = (int) date('G');
            if ($horaAtual < 7 || $horaAtual >= 17) {
                throw new InvalidArgumentException('As avaliações do dia só podem ser realizadas entre 07:00 e 17:00.');
            }
            $periodoAtual = $horaAtual < 12 ? 'manha' : 'tarde';
            if ($periodo !== $periodoAtual) {
                throw new InvalidArgumentException('O período permitido para o horário atual é ' . ($periodoAtual === 'manha' ? 'Manhã' : 'Tarde') . '.');
            }
        }

        $stmtAluno = $pdo->prepare('SELECT diagnostico, id_apoio FROM alunos WHERE id = ? LIMIT 1');
        $stmtAluno->execute([$alunoId]);
        $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);
        if (!$aluno) throw new InvalidArgumentException('Aluno não encontrado.');
        if (($_SESSION['tipo'] ?? '') === 'apoio' && (int) $aluno['id_apoio'] !== (int) $_SESSION['usuario_id']) {
            http_response_code(403);
            throw new RuntimeException('Você não possui vínculo com este aluno.');
        }

        $stmtQuesitos = $pdo->prepare('SELECT q.id, aq.peso FROM alunos_quesitos aq INNER JOIN quesitos_avaliacao q ON q.id = aq.quesito_id WHERE aq.aluno_id = ? AND aq.ativo = 1 AND q.ativo = 1 AND q.excluido = 0');
        $stmtQuesitos->execute([$alunoId]);
        $permitidos = [];
        foreach ($stmtQuesitos->fetchAll(PDO::FETCH_ASSOC) as $quesito) {
            $permitidos[(int) $quesito['id']] = max(1, min(10, (int) $quesito['peso']));
        }
        if (!$permitidos) throw new InvalidArgumentException('Não há quesitos associados a este aluno.');
        $respostaIds = [];
        foreach ($respostas as $resposta) {
            $respostaIds[] = (int) ($resposta['quesito_id'] ?? 0);
        }
        sort($respostaIds);
        $permitidosIds = array_keys($permitidos);
        sort($permitidosIds);
        if ($respostaIds !== $permitidosIds) {
            throw new InvalidArgumentException('O registro deve conter exatamente os quesitos associados ao aluno.');
        }

        $pdo->beginTransaction();
        $stmtRegistro = $pdo->prepare('INSERT INTO registros_apoio (aluno_id, usuario_id, data, periodo, observacao_geral) VALUES (?, ?, ?, ?, ?)');
        $stmtRegistro->execute([$alunoId, (int) $_SESSION['usuario_id'], $data, $periodo, trim((string) ($dados['observacao_geral'] ?? ''))]);
        $registroId = (int) $pdo->lastInsertId();
        $stmtResposta = $pdo->prepare('INSERT INTO respostas_apoio (registro_id, quesito_id, avaliacao, nota, valencia, peso, observacao) VALUES (?, ?, ?, ?, ?, ?, ?)');
        foreach ($respostas as $resposta) {
            $quesitoId = (int) ($resposta['quesito_id'] ?? 0);
            $avaliacao = (string) ($resposta['avaliacao'] ?? '');
            $notaValor = $resposta['nota'] ?? null;
            $nota = ($notaValor === null || $notaValor === '' || $notaValor === 'null') ? null : filter_var($notaValor, FILTER_VALIDATE_INT);
            $valencias = ['triste' => -1, 'neutro' => 0, 'feliz' => 1];
            if (!isset($permitidos[$quesitoId]) || !isset($valencias[$avaliacao]) || ($nota !== null && ($nota < 1 || $nota > 10))) {
                throw new InvalidArgumentException('Resposta de quesito inválida.');
            }
            $stmtResposta->execute([$registroId, $quesitoId, $avaliacao, $nota, $valencias[$avaliacao], $permitidos[$quesitoId], trim((string) ($resposta['observacao'] ?? ''))]);
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Registro de apoio salvo com sucesso.', 'id' => $registroId]);
        exit;
    }

    if ($alunoId <= 0) throw new InvalidArgumentException('Informe um aluno válido.');
    $stmtAluno = $pdo->prepare('SELECT id, id_apoio FROM alunos WHERE id = ? LIMIT 1');
    $stmtAluno->execute([$alunoId]);
    $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);
    if (!$aluno) throw new InvalidArgumentException('Aluno não encontrado.');
    if (($_SESSION['tipo'] ?? '') === 'apoio' && (int) $aluno['id_apoio'] !== (int) ($_SESSION['usuario_id'] ?? 0)) {
        http_response_code(403);
        throw new RuntimeException('Você não possui vínculo com este aluno.');
    }
    $stmtQuesitos = $pdo->prepare('SELECT q.id, q.titulo, q.descricao, aq.peso FROM alunos_quesitos aq INNER JOIN quesitos_avaliacao q ON q.id = aq.quesito_id WHERE aq.aluno_id = ? AND aq.ativo = 1 AND q.ativo = 1 AND q.excluido = 0 ORDER BY q.titulo');
    $stmtQuesitos->execute([$alunoId]);
    $stmtHistorico = $pdo->prepare("SELECT r.id, r.data, r.periodo, COUNT(a.id) AS total_respostas, SUM(a.avaliacao = 'feliz') AS total_feliz, SUM(a.avaliacao = 'neutro') AS total_neutro, SUM(a.avaliacao = 'triste') AS total_triste, COALESCE(SUM(a.valencia * a.peso), 0) AS pontuacao, COALESCE(SUM(a.peso), 0) AS peso_total FROM registros_apoio r LEFT JOIN respostas_apoio a ON a.registro_id = r.id WHERE r.aluno_id = ? GROUP BY r.id, r.data, r.periodo ORDER BY r.data DESC, r.id DESC LIMIT 20");
    $stmtHistorico->execute([$alunoId]);
    echo json_encode(['success' => true, 'quesitos' => $stmtQuesitos->fetchAll(PDO::FETCH_ASSOC), 'historico' => $stmtHistorico->fetchAll(PDO::FETCH_ASSOC)]);
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Erro ao registrar apoio: ' . $e->getMessage());
    http_response_code(http_response_code() >= 400 ? http_response_code() : 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
