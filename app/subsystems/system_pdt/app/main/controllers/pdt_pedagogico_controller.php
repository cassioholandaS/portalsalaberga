<?php

require_once('../config/security.php');
require_once('../models/acompanhamento_pedagogico_model.php');
require_once('../models/notificacao_model.php');

iniciarSessaoSegura();
exigirAutenticacao(true);
header('Content-Type: application/json; charset=utf-8');

function responderPdtPedagogico(array $dados, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function dadosPdtPedagogico(): array
{
    $json = json_decode(file_get_contents('php://input'), true);
    return is_array($json) ? $json : $_POST;
}

function textoPdtPedagogico(array $dados, string $chave): string
{
    $valor = $dados[$chave] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

function dataValidaPdtPedagogico($valor, bool $opcional = false): bool
{
    if ($opcional && ($valor === null || $valor === '')) {
        return true;
    }
    if (!is_string($valor)) {
        return false;
    }
    $data = DateTime::createFromFormat('!Y-m-d', $valor);
    return $data instanceof DateTime && $data->format('Y-m-d') === $valor;
}

function tamanhoTextoPdtPedagogico(string $valor): int
{
    return function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
}

function obterTurmaAutorizada(int $idTurma): bool
{
    if ($idTurma < 1) {
        return false;
    }
    return validarPermissaoTurmaParaAcao($idTurma);
}

function notificarGestores(PDO $conn, string $titulo, string $mensagem, string $entidadeTipo, int $entidadeId): void
{
    try {
        $stmt = $conn->query("SELECT id FROM usuarios WHERE LOWER(tipo) = 'gestor'");
        $gestorIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!$gestorIds) {
            error_log('Notificação pedagógica não enviada: nenhum gestor cadastrado.');
            return;
        }

        $notificacoes = new NotificacaoModel();
        foreach ($gestorIds as $gestorId) {
            if (!$notificacoes->criar(
                (int) $gestorId,
                $titulo,
                $mensagem,
                'info',
                null,
                $entidadeTipo,
                $entidadeId
            )) {
                error_log('Falha ao enviar notificação pedagógica ao gestor ' . (int) $gestorId . '.');
            }
        }
    } catch (PDOException $e) {
        error_log('Erro ao localizar gestores para notificação pedagógica: ' . $e->getMessage());
    }
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';
$db = new connect();
$conn = $db->getConnection();
$usuarioId = (int) ($_SESSION['id_usuario'] ?? 0);

if ($metodo === 'GET' && $action === 'listar') {
    $idTurma = filter_var($_GET['id_turma'] ?? null, FILTER_VALIDATE_INT);
    if (!$idTurma || !obterTurmaAutorizada((int) $idTurma)) {
        responderPdtPedagogico(['sucesso' => false, 'erro' => 'Sem permissão para acessar esta turma'], 403);
    }

    try {
        $acompanhamentosModel = new AcompanhamentoPedagogicoModel();
        $acompanhamentos = $acompanhamentosModel->listar((int) $idTurma);

        $stmt = $conn->prepare(
            'SELECT id_conselho, data_conselho, bimestre, pauta, ata, status, criado_em
             FROM conselhos_classe
             WHERE id_turma = :id_turma
             ORDER BY data_conselho DESC, id_conselho DESC'
        );
        $stmt->execute([':id_turma' => $idTurma]);
        $conselhos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($conselhos) {
            $idsConselho = array_column($conselhos, 'id_conselho');
            $placeholders = implode(',', array_fill(0, count($idsConselho), '?'));
            $stmtItens = $conn->prepare(
                "SELECT cca.id_conselho, cca.id_aluno, cca.observacao, cca.encaminhamento, a.nome AS aluno_nome
                 FROM conselho_classe_alunos cca
                 INNER JOIN alunos a ON a.id_aluno = cca.id_aluno
                 WHERE cca.id_conselho IN ($placeholders)
                 ORDER BY a.nome"
            );
            $stmtItens->execute($idsConselho);
            $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
            $itensPorConselho = [];
            foreach ($itens as $item) {
                $itensPorConselho[(int) $item['id_conselho']][] = $item;
            }
            foreach ($conselhos as &$conselho) {
                $conselho['alunos'] = $itensPorConselho[(int) $conselho['id_conselho']] ?? [];
            }
            unset($conselho);
        }

        responderPdtPedagogico([
            'sucesso' => true,
            'acompanhamentos' => $acompanhamentos,
            'conselhos' => $conselhos,
        ]);
    } catch (PDOException $e) {
        error_log('Erro ao listar registros pedagógicos: ' . $e->getMessage());
        responderPdtPedagogico(['sucesso' => false, 'erro' => 'Não foi possível carregar os registros pedagógicos'], 500);
    }
}

if ($metodo !== 'POST') {
    responderPdtPedagogico(['sucesso' => false, 'erro' => 'Método não permitido'], 405);
}

$dados = dadosPdtPedagogico();
if (!validarCsrfToken($dados['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) {
    responderPdtPedagogico(['sucesso' => false, 'erro' => 'Token CSRF inválido'], 403);
}

try {
    if ($action === 'criar_acompanhamento') {
        $idTurma = filter_var($dados['id_turma'] ?? null, FILTER_VALIDATE_INT);
        $idAluno = filter_var($dados['id_aluno'] ?? null, FILTER_VALIDATE_INT);
        $motivo = textoPdtPedagogico($dados, 'motivo');
        $observacao = textoPdtPedagogico($dados, 'observacao');
        $encaminhamento = textoPdtPedagogico($dados, 'encaminhamento');
        $dataRegistro = $dados['data_registro'] ?? '';
        $dataRetorno = $dados['data_retorno'] ?? null;

        if (!$idTurma || !$idAluno || !obterTurmaAutorizada((int) $idTurma)) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Sem permissão para registrar acompanhamento nesta turma'], 403);
        }
        if (
            $motivo === '' || tamanhoTextoPdtPedagogico($motivo) > 150
            || $observacao === '' || tamanhoTextoPdtPedagogico($observacao) > 10000
            || tamanhoTextoPdtPedagogico($encaminhamento) > 10000
            || !dataValidaPdtPedagogico($dataRegistro)
            || !dataValidaPdtPedagogico($dataRetorno, true)
        ) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Confira os campos e as datas do acompanhamento'], 422);
        }

        $stmtAluno = $conn->prepare("SELECT 1 FROM alunos WHERE id_aluno = :id_aluno AND id_turma = :id_turma AND status_aluno = 'ativo' LIMIT 1");
        $stmtAluno->execute([':id_aluno' => $idAluno, ':id_turma' => $idTurma]);
        if (!$stmtAluno->fetchColumn()) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'O aluno não pertence à turma selecionada'], 422);
        }

        $id = (new AcompanhamentoPedagogicoModel())->criar([
            'id_turma' => (int) $idTurma,
            'id_aluno' => (int) $idAluno,
            'data_registro' => $dataRegistro,
            'motivo' => $motivo,
            'observacao' => $observacao,
            'encaminhamento' => $encaminhamento,
            'data_retorno' => $dataRetorno,
        ], $usuarioId);
        notificarGestores(
            $conn,
            'Novo acompanhamento pedagógico',
            'Um novo acompanhamento pedagógico foi registrado.',
            'acompanhamento_pedagogico',
            $id
        );
        responderPdtPedagogico(['sucesso' => true, 'id' => $id]);
    }

    if ($action === 'atualizar_status_acompanhamento') {
        $idAcompanhamento = filter_var($dados['id_acompanhamento'] ?? null, FILTER_VALIDATE_INT);
        $status = $dados['status'] ?? '';
        if (!$idAcompanhamento || !in_array($status, ['aberto', 'em_acompanhamento', 'concluido'], true)) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Status ou acompanhamento inválido'], 422);
        }

        $stmt = $conn->prepare('SELECT id_turma FROM acompanhamentos_pedagogicos WHERE id_acompanhamento = :id LIMIT 1');
        $stmt->execute([':id' => $idAcompanhamento]);
        $idTurma = (int) $stmt->fetchColumn();
        if (!$idTurma || !obterTurmaAutorizada($idTurma)) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Sem permissão para alterar este acompanhamento'], 403);
        }
        (new AcompanhamentoPedagogicoModel())->atualizarStatus((int) $idAcompanhamento, $status);
        responderPdtPedagogico(['sucesso' => true]);
    }

    if ($action === 'criar_conselho') {
        $idTurma = filter_var($dados['id_turma'] ?? null, FILTER_VALIDATE_INT);
        $dataConselho = $dados['data_conselho'] ?? '';
        $bimestre = filter_var($dados['bimestre'] ?? null, FILTER_VALIDATE_INT);
        $pauta = textoPdtPedagogico($dados, 'pauta');
        $ata = textoPdtPedagogico($dados, 'ata');

        if (!$idTurma || !obterTurmaAutorizada((int) $idTurma)) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Sem permissão para registrar conselho nesta turma'], 403);
        }
        if (
            !dataValidaPdtPedagogico($dataConselho)
            || !$bimestre || $bimestre < 1 || $bimestre > 4
            || $pauta === '' || tamanhoTextoPdtPedagogico($pauta) > 200
            || $ata === '' || tamanhoTextoPdtPedagogico($ata) > 20000
        ) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Confira os dados do conselho de classe'], 422);
        }

        $stmt = $conn->prepare(
            'INSERT INTO conselhos_classe (id_turma, id_responsavel, data_conselho, bimestre, pauta, ata)
             VALUES (:id_turma, :id_responsavel, :data_conselho, :bimestre, :pauta, :ata)'
        );
        $stmt->execute([
            ':id_turma' => $idTurma,
            ':id_responsavel' => $usuarioId,
            ':data_conselho' => $dataConselho,
            ':bimestre' => $bimestre,
            ':pauta' => $pauta,
            ':ata' => $ata,
        ]);
        $idConselho = (int) $conn->lastInsertId();
        notificarGestores(
            $conn,
            'Novo conselho de classe',
            'Um novo conselho de classe foi registrado.',
            'conselho_classe',
            $idConselho
        );
        responderPdtPedagogico(['sucesso' => true, 'id' => $idConselho]);
    }

    if ($action === 'adicionar_item_conselho') {
        $idConselho = filter_var($dados['id_conselho'] ?? null, FILTER_VALIDATE_INT);
        $idAluno = filter_var($dados['id_aluno'] ?? null, FILTER_VALIDATE_INT);
        $observacao = textoPdtPedagogico($dados, 'observacao');
        $encaminhamento = textoPdtPedagogico($dados, 'encaminhamento');
        if (!$idConselho || !$idAluno || $observacao === '' || tamanhoTextoPdtPedagogico($observacao) > 10000 || tamanhoTextoPdtPedagogico($encaminhamento) > 10000) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Confira os dados do registro individual do conselho'], 422);
        }

        $stmtConselho = $conn->prepare('SELECT id_turma, status FROM conselhos_classe WHERE id_conselho = :id LIMIT 1');
        $stmtConselho->execute([':id' => $idConselho]);
        $conselho = $stmtConselho->fetch(PDO::FETCH_ASSOC);
        if (!$conselho || !obterTurmaAutorizada((int) $conselho['id_turma'])) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Sem permissão para editar este conselho'], 403);
        }
        if ($conselho['status'] !== 'rascunho') {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Conselhos finalizados não podem ser alterados'], 409);
        }
        $stmtAluno = $conn->prepare("SELECT 1 FROM alunos WHERE id_aluno = :id_aluno AND id_turma = :id_turma AND status_aluno = 'ativo' LIMIT 1");
        $stmtAluno->execute([':id_aluno' => $idAluno, ':id_turma' => $conselho['id_turma']]);
        if (!$stmtAluno->fetchColumn()) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'O aluno não pertence à turma deste conselho'], 422);
        }

        $stmtItem = $conn->prepare(
            'INSERT INTO conselho_classe_alunos (id_conselho, id_aluno, observacao, encaminhamento)
             VALUES (:id_conselho, :id_aluno, :observacao, :encaminhamento)
             ON DUPLICATE KEY UPDATE observacao = VALUES(observacao), encaminhamento = VALUES(encaminhamento)'
        );
        $stmtItem->execute([
            ':id_conselho' => $idConselho,
            ':id_aluno' => $idAluno,
            ':observacao' => $observacao,
            ':encaminhamento' => $encaminhamento ?: null,
        ]);
        if ($stmtItem->rowCount() === 1) {
            $stmtIdItem = $conn->prepare(
                'SELECT id_item FROM conselho_classe_alunos
                 WHERE id_conselho = :id_conselho AND id_aluno = :id_aluno
                 LIMIT 1'
            );
            $stmtIdItem->execute([
                ':id_conselho' => $idConselho,
                ':id_aluno' => $idAluno,
            ]);
            $idItem = (int) $stmtIdItem->fetchColumn();
            if ($idItem > 0) {
                notificarGestores(
                    $conn,
                    'Nova deliberação de conselho',
                    'Uma nova deliberação individual foi registrada em conselho de classe.',
                    'deliberacao_conselho',
                    $idItem
                );
            }
        }
        responderPdtPedagogico(['sucesso' => true]);
    }

    if ($action === 'finalizar_conselho') {
        $idConselho = filter_var($dados['id_conselho'] ?? null, FILTER_VALIDATE_INT);
        if (!$idConselho) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Conselho inválido'], 422);
        }
        $stmt = $conn->prepare('SELECT id_turma, status FROM conselhos_classe WHERE id_conselho = :id LIMIT 1');
        $stmt->execute([':id' => $idConselho]);
        $conselho = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$conselho || !obterTurmaAutorizada((int) $conselho['id_turma'])) {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Sem permissão para finalizar este conselho'], 403);
        }
        if ($conselho['status'] !== 'rascunho') {
            responderPdtPedagogico(['sucesso' => false, 'erro' => 'Este conselho já está finalizado'], 409);
        }
        $stmtFinalizar = $conn->prepare("UPDATE conselhos_classe SET status = 'finalizado' WHERE id_conselho = :id");
        $stmtFinalizar->execute([':id' => $idConselho]);
        responderPdtPedagogico(['sucesso' => true]);
    }
} catch (PDOException $e) {
    error_log('Erro no módulo pedagógico PDT: ' . $e->getMessage());
    responderPdtPedagogico(['sucesso' => false, 'erro' => 'Não foi possível concluir a operação pedagógica'], 500);
}

responderPdtPedagogico(['sucesso' => false, 'erro' => 'Ação não reconhecida'], 404);
