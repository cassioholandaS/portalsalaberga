<?php

session_start();
require_once('../config/security.php');
require_once('../models/notificacao_model.php');

iniciarSessaoSegura();
exigirAutenticacao(true);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

require_once("../config/connect.php");

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'criar_ocorrencia':
        criarOcorrencia();
        break;

    case 'listar_ocorrencias':
        listarOcorrencias();
        break;

    case 'contar_ocorrencias':
        contarOcorrencias();
        break;

    case 'excluir_ocorrencia':
        excluirOcorrencia();
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação não reconhecida']);
        break;
}

function criarOcorrencia() {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!validarCsrfToken($dados['csrf_token'] ?? '')) {
        echo json_encode(['sucesso' => false, 'erro' => 'Token CSRF inválido']);
        return;
    }

    // Validação básica
    if (!isset($dados['turma_id']) || !isset($dados['aluno_id']) || !isset($dados['tipo'])) {
        echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
        return;
    }

    $turmaId = $dados['turma_id'];
    if (!validarPermissaoTurmaParaAcao($turmaId)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Sem permissão para gerenciar esta turma']);
        return;
    }
    $alunoId = $dados['aluno_id'];
    $tipo = $dados['tipo'];
    $descricao = $dados['descricao'] ?? '';
    $professorId = $_SESSION['id_usuario'];

    try {
        $db = new connect();
        $conn = $db->getConnection();

        $stmtAluno = $conn->prepare(
            "SELECT 1 FROM alunos
             WHERE id_aluno = :id_aluno
               AND id_turma = :id_turma
               AND status_aluno = 'ativo'
             LIMIT 1"
        );
        $stmtAluno->execute([
            ':id_aluno' => $alunoId,
            ':id_turma' => $turmaId
        ]);
        if (!$stmtAluno->fetchColumn()) {
            echo json_encode(['sucesso' => false, 'erro' => 'O aluno não pertence a esta turma ativa']);
            return;
        }

        $stmt = $conn->prepare("
            INSERT INTO ocorrencias (id_aluno, id_turma, id_professor, tipo_ocorrencia, descricao)
            VALUES (:id_aluno, :id_turma, :id_professor, :tipo_ocorrencia, :descricao)
        ");

        $stmt->execute([
            ':id_aluno' => $alunoId,
            ':id_turma' => $turmaId,
            ':id_professor' => $professorId,
            ':tipo_ocorrencia' => $tipo,
            ':descricao' => $descricao
        ]);

        $notificacoes = new NotificacaoModel();
        $notificacoes->criar(
            (int) ($_SESSION['id_usuario'] ?? 0),
            'Ocorrência registrada',
            'Uma ocorrência foi registrada para um aluno.',
            'warning',
            null,
            'ocorrencia',
            $turmaId
        );
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Ocorrência registrada com sucesso!'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}

function excluirOcorrencia() {
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!$dados) {
        echo json_encode(['sucesso' => false, 'erro' => 'JSON inválido']);
        return;
    }

    if (!validarCsrfToken($dados['csrf_token'] ?? '')) {
        echo json_encode(['sucesso' => false, 'erro' => 'Token CSRF inválido']);
        return;
    }

    if (!isset($dados['id_ocorrencia'])) {
        echo json_encode(['sucesso' => false, 'erro' => 'ID de ocorrência não informado']);
        return;
    }

    $idOcorrencia = (int) $dados['id_ocorrencia'];
    if (!$idOcorrencia) {
        echo json_encode(['sucesso' => false, 'erro' => 'ID de ocorrência inválido']);
        return;
    }

    try {
        $db = new connect();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT id_turma, id_professor FROM ocorrencias WHERE id_ocorrencia = :id_ocorrencia LIMIT 1");
        $stmt->execute([':id_ocorrencia' => $idOcorrencia]);
        $ocorrencia = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ocorrencia) {
            echo json_encode(['sucesso' => false, 'erro' => 'Ocorrência não encontrada']);
            return;
        }

        $turmaId = (int) ($ocorrencia['id_turma'] ?? 0);
        if (!validarPermissaoTurmaParaAcao($turmaId)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Sem permissão para gerenciar esta turma']);
            return;
        }

        $professorId = $_SESSION['id_usuario'];

        // Primeiro, verificar se a coluna deletado_em existe (soft delete)
        $checkCol = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ocorrencias' AND COLUMN_NAME = 'deletado_em'");
        $checkCol->execute();
        $colExists = $checkCol->fetch(PDO::FETCH_ASSOC);

        if ($colExists) {
            // Usar soft delete (UPDATE)
            $stmt = $conn->prepare("UPDATE ocorrencias SET deletado_em = NOW() WHERE id_ocorrencia = :id_ocorrencia AND id_professor = :id_professor");
        } else {
            // Fallback: hard delete se coluna não existir
            $stmt = $conn->prepare("DELETE FROM ocorrencias WHERE id_ocorrencia = :id_ocorrencia AND id_professor = :id_professor");
        }

        $stmt->execute([
            ':id_ocorrencia' => $idOcorrencia,
            ':id_professor' => $professorId
        ]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Ocorrência não encontrada ou sem permissão']);
            return;
        }

        echo json_encode(['sucesso' => true, 'mensagem' => 'Ocorrência cancelada com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}

function listarOcorrencias() {
    $professorId = $_SESSION['id_usuario'];
    $filtro = $_GET['filtro'] ?? 'mes'; // mes, trimestre, semestre, ano

    try {
        $db = new connect();
        $conn = $db->getConnection();

        // Determinar data de início baseado no filtro
        $dataInicio = '';
        switch ($filtro) {
            case 'mes':
                $dataInicio = date('Y-m-01');
                break;
            case 'trimestre':
                $mes = date('m');
                $trimestre = ceil($mes / 3);
                $mesInicio = ($trimestre - 1) * 3 + 1;
                $dataInicio = date('Y-') . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . '-01';
                break;
            case 'semestre':
                $mes = date('m');
                $semestre = ceil($mes / 6);
                $mesInicio = ($semestre - 1) * 6 + 1;
                $dataInicio = date('Y-') . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . '-01';
                break;
            case 'ano':
                $dataInicio = date('Y-01-01');
                break;
            default:
                $dataInicio = date('Y-m-01');
        }

        // Verificar se a coluna deletado_em existe
        $checkCol = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ocorrencias' AND COLUMN_NAME = 'deletado_em'");
        $checkCol->execute();
        $colExists = $checkCol->fetch(PDO::FETCH_ASSOC) ? true : false;

        $tipoUsuario = normalizarTipoUsuario($_SESSION['tipo'] ?? '');
        $isAdministrador = $tipoUsuario === 'gestor' || $tipoUsuario === 'coordenador';
        $whereClause = "DATE(o.data_ocorrencia) >= :data_inicio";
        if (!$isAdministrador) {
            $whereClause = "o.id_professor = :id_professor AND " . $whereClause;
        }
        if ($colExists) {
            $whereClause .= " AND o.deletado_em IS NULL";
        }

        $stmt = $conn->prepare("
            SELECT o.id_ocorrencia, o.tipo_ocorrencia, o.descricao, o.data_ocorrencia,
                   a.nome as nome_aluno, a.matricula,
                   t.nome_turma, t.serie_turma, u.nome as professor_nome
            FROM ocorrencias o
            JOIN alunos a ON o.id_aluno = a.id_aluno
            JOIN turmas t ON o.id_turma = t.id_turma
            JOIN usuarios u ON o.id_professor = u.id
            WHERE $whereClause
            ORDER BY o.data_ocorrencia DESC
            LIMIT 10
        ");

        $params = [':data_inicio' => $dataInicio];
        if (!$isAdministrador) {
            $params[':id_professor'] = $professorId;
        }
        $stmt->execute($params);

        $ocorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'sucesso' => true,
            'dados' => $ocorrencias,
            'total' => count($ocorrencias)
        ]);
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}

function contarOcorrencias() {
    $professorId = $_SESSION['id_usuario'];
    $filtro = $_GET['filtro'] ?? 'mes';

    try {
        $db = new connect();
        $conn = $db->getConnection();

        // Determinar data de início baseado no filtro
        $dataInicio = '';
        switch ($filtro) {
            case 'mes':
                $dataInicio = date('Y-m-01');
                break;
            case 'trimestre':
                $mes = date('m');
                $trimestre = ceil($mes / 3);
                $mesInicio = ($trimestre - 1) * 3 + 1;
                $dataInicio = date('Y-') . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . '-01';
                break;
            case 'semestre':
                $mes = date('m');
                $semestre = ceil($mes / 6);
                $mesInicio = ($semestre - 1) * 6 + 1;
                $dataInicio = date('Y-') . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . '-01';
                break;
            case 'ano':
                $dataInicio = date('Y-01-01');
                break;
            default:
                $dataInicio = date('Y-m-01');
        }

        // Verificar se a coluna deletado_em existe
        $checkCol = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ocorrencias' AND COLUMN_NAME = 'deletado_em'");
        $checkCol->execute();
        $colExists = $checkCol->fetch(PDO::FETCH_ASSOC) ? true : false;

        $tipoUsuario = normalizarTipoUsuario($_SESSION['tipo'] ?? '');
        $isAdministrador = $tipoUsuario === 'gestor' || $tipoUsuario === 'coordenador';
        $whereClause = "DATE(data_ocorrencia) >= :data_inicio";
        if (!$isAdministrador) {
            $whereClause = "id_professor = :id_professor AND " . $whereClause;
        }
        if ($colExists) {
            $whereClause .= " AND deletado_em IS NULL";
        }

        $stmt = $conn->prepare("
            SELECT COUNT(*) as total FROM ocorrencias
            WHERE $whereClause
        ");

        $params = [':data_inicio' => $dataInicio];
        if (!$isAdministrador) {
            $params[':id_professor'] = $professorId;
        }
        $stmt->execute($params);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'sucesso' => true,
            'total' => $resultado['total'] ?? 0
        ]);
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
}

?>
