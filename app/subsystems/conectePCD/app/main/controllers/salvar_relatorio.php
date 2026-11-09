<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!$dados) {
        echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido']);
        exit;
    }

    $sql = "INSERT INTO relatorios_acompanhamento (
    aluno_id,
    data_atendimento,
    responsavel_registro,
    desenvolvimento_observado,
    dificuldades_observadas,
    potencialidades,
    recomendacoes,
    conclusao
) VALUES (
    :aluno_id,
    :data_atendimento,
    :responsavel_registro,
    :desenvolvimento_observado,
    :dificuldades_observadas,
    :potencialidades,
    :recomendacoes,
    :conclusao
)";

$stmt = $pdo->prepare($sql);

$resultado = $stmt->execute([
    ':aluno_id' => $dados['aluno_id'] ?? 0,
    ':data_atendimento' => $dados['dataAtendimento'] ?? null,
    ':responsavel_registro' => $dados['responsavel'] ?? null,
    ':desenvolvimento_observado' => $dados['desenvolvimento'] ?? null,
    ':dificuldades_observadas' => $dados['dificuldades'] ?? null,
    ':potencialidades' => $dados['potencialidades'] ?? null,
    ':recomendacoes' => $dados['recomendacoes'] ?? null,
    ':conclusao' => $dados['conclusao'] ?? null
]);

    echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Relatório salvo com sucesso' : 'Erro ao salvar relatório']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
