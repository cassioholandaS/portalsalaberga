<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio', 'responsavel']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

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
    $sql = "SELECT r.aluno_id, r.data, r.periodo, r.criado_em AS data_hora_registro, q.id AS quesito_id,
                   q.titulo AS quesito, a.avaliacao, a.valencia, a.peso, a.nota, a.observacao
            FROM registros_apoio r
            INNER JOIN respostas_apoio a ON a.registro_id = r.id
            INNER JOIN quesitos_avaliacao q ON q.id = a.quesito_id
            ORDER BY r.data, r.criado_em, r.id, q.titulo";
    $stmt = $pdo->query($sql);
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

echo json_encode([
    'avaliacoes' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    'frequencias' => $frequencias,
], JSON_UNESCAPED_UNICODE);