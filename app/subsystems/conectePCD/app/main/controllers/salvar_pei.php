<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Autorizacao.php';

try {
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!$dados) {
        echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido']);
        exit;
    }
    $usuario = currentUser();
    $autorizacao = new Autorizacao();
    if (!$autorizacao->podeEditarPlano((string) $usuario['tipo'], (int) $usuario['id'], (int) ($dados['aluno_id'] ?? 0))) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado ao aluno.']);
        exit;
    }

    $sql = "INSERT INTO pei (
    aluno_id,
    nome_documento,
    data_elaboracao,
    responsavel_preenchimento,
    objetivos_pedagogicos,
    habilidades_desenvolver,
    estrategias_ensino,
    recursos_acessibilidade,
    adaptacoes_curriculares,
    criterios_avaliacao,
    observacoes
) VALUES (
    :aluno_id,
    :nome_documento,
    :data_elaboracao,
    :responsavel_preenchimento,
    :objetivos_pedagogicos,
    :habilidades_desenvolver,
    :estrategias_ensino,
    :recursos_acessibilidade,
    :adaptacoes_curriculares,
    :criterios_avaliacao,
    :observacoes
)";

$stmt = $pdo->prepare($sql);

$resultado = $stmt->execute([
    ':aluno_id' => $dados['aluno_id'] ?? 0,
    ':nome_documento' => trim((string) ($dados['nome'] ?? '')),
    ':data_elaboracao' => $dados['dataElaboracao'] ?? null,
    ':responsavel_preenchimento' => $dados['responsavel'] ?? null,
    ':objetivos_pedagogicos' => $dados['objetivos'] ?? null,
    ':habilidades_desenvolver' => $dados['habilidades'] ?? null,
    ':estrategias_ensino' => $dados['estrategias'] ?? null,
    ':recursos_acessibilidade' => $dados['recursos'] ?? null,
    ':adaptacoes_curriculares' => $dados['adaptacoes'] ?? null,
    ':criterios_avaliacao' => $dados['criterios'] ?? null,
    ':observacoes' => $dados['observacoes'] ?? null
]);

    echo json_encode(['success' => $resultado, 'message' => $resultado ? 'PEI salvo com sucesso' : 'Erro ao salvar PEI']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
