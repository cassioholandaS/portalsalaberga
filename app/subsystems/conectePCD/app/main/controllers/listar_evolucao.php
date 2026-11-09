<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio', 'responsavel']);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/database.php';

    $tipoUsuario = $_SESSION['tipo'] ?? 'responsavel';
    $idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);

    if ($tipoUsuario === 'apoio') {
        $sql = "SELECT r.aluno_id, r.data, r.periodo, r.criado_em AS data_hora_registro, q.id AS quesito_id,
                       q.titulo AS quesito, a.avaliacao, a.valencia, a.peso, a.nota, a.observacao
                FROM registros_apoio r
                INNER JOIN respostas_apoio a ON a.registro_id = r.id
                INNER JOIN quesitos_avaliacao q ON q.id = a.quesito_id
                INNER JOIN alunos al ON al.id = r.aluno_id
                WHERE al.id_apoio = ?
                ORDER BY r.data, r.criado_em, r.id, q.titulo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idUsuario]);
    } else {
        $sql = "SELECT r.aluno_id, r.data, r.periodo, r.criado_em AS data_hora_registro, q.id AS quesito_id,
                       q.titulo AS quesito, a.avaliacao, a.valencia, a.peso, a.nota, a.observacao
                FROM registros_apoio r
                INNER JOIN respostas_apoio a ON a.registro_id = r.id
                INNER JOIN quesitos_avaliacao q ON q.id = a.quesito_id
                ORDER BY r.data, r.criado_em, r.id, q.titulo";
        $stmt = $pdo->query($sql);
    }

    $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $avisos = [];
    try {
        if ($tipoUsuario === 'apoio') {
            $sqlFrequencias = "SELECT f.aluno_id, f.data, f.presente, f.criado_em AS data_hora_registro,
                                      u.nome AS usuario_nome
                               FROM frequencia_apoio f
                               INNER JOIN alunos al ON al.id = f.aluno_id
                               LEFT JOIN usuarios u ON u.id = f.usuario_id
                               WHERE al.id_apoio = ?
                               ORDER BY f.data, f.criado_em, f.id";
            $stmtFrequencias = $pdo->prepare($sqlFrequencias);
            $stmtFrequencias->execute([$idUsuario]);
        } else {
            $sqlFrequencias = "SELECT f.aluno_id, f.data, f.presente, f.criado_em AS data_hora_registro,
                                      u.nome AS usuario_nome
                               FROM frequencia_apoio f
                               LEFT JOIN usuarios u ON u.id = f.usuario_id
                               ORDER BY f.data, f.criado_em, f.id";
            $stmtFrequencias = $pdo->query($sqlFrequencias);
        }

        $frequencias = array_map(static function (array $frequencia): array {
            $frequencia['presente'] = (int) $frequencia['presente'];
            return $frequencia;
        }, $stmtFrequencias->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        $codigoMysql = (int) ($e->errorInfo[1] ?? 0);
        if ($e->getCode() !== '42S02' && $codigoMysql !== 1146) {
            throw $e;
        }

        error_log('Tabela frequencia_apoio ausente ao carregar relatórios do PCD: ' . $e->getMessage());
        $frequencias = [];
        $avisos[] = 'A frequência ainda não está disponível. Execute docs/atualizar_banco_pcd.sql no banco do sistema PCD.';
    }

    echo json_encode([
        'avaliacoes' => $avaliacoes,
        'frequencias' => $frequencias,
        'avisos' => $avisos,
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    error_log('Erro ao carregar relatórios de evolução do PCD: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível carregar os relatórios. Verifique a configuração do banco de dados do sistema PCD.',
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}