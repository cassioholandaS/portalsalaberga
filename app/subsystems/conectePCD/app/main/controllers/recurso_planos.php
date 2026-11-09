<?php
require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio']);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Autorizacao.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$autorizacao = new Autorizacao();
$usuario = currentUser();
$tipo = strtolower((string) $usuario['tipo']);
$usuarioId = (int) $usuario['id'];
$entrada = json_decode(file_get_contents('php://input'), true) ?: [];
$action = (string) ($_GET['action'] ?? $entrada['action'] ?? '');
$modulo = (string) ($_GET['modulo'] ?? $entrada['modulo'] ?? 'pei');
$tabela = $modulo === 'paee' ? 'paee' : ($modulo === 'pei' ? 'pei' : '');

function respostaPlano(array $dados, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}
function inteiro($valor): int { return filter_var($valor, FILTER_VALIDATE_INT) !== false ? (int) $valor : 0; }

try {
    if ($action === 'cadastrar_disciplina' || $action === 'associar_disciplina') {
        if (!in_array($tipo, ['master', 'aee'], true)) respostaPlano(['success' => false, 'message' => 'Sem permissão para gerenciar disciplinas.'], 403);
        $alunoId = inteiro($entrada['aluno_id'] ?? $_GET['aluno_id'] ?? 0);
        if (!$autorizacao->usuarioTemAcessoAluno($usuarioId, $alunoId)) respostaPlano(['success' => false, 'message' => 'Acesso negado ao aluno.'], 403);
        if ($action === 'cadastrar_disciplina') {
            $nome = trim((string) ($entrada['nome'] ?? ''));
            if ($nome === '' || mb_strlen($nome) > 150) respostaPlano(['success' => false, 'message' => 'Informe um nome de disciplina válido.'], 422);
            $stmt = $pdo->prepare('INSERT INTO disciplinas (nome) VALUES (:nome) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), ativa = 1');
            $stmt->execute([':nome' => $nome]);
            $disciplinaId = (int) $pdo->lastInsertId();
        } else {
            $disciplinaId = inteiro($entrada['disciplina_id'] ?? 0);
        }
        if (!$disciplinaId) respostaPlano(['success' => false, 'message' => 'Disciplina inválida.'], 422);
        $stmt = $pdo->prepare('INSERT INTO alunos_disciplinas (aluno_id, disciplina_id) VALUES (:aluno_id, :disciplina_id) ON DUPLICATE KEY UPDATE ativa = 1');
        $stmt->execute([':aluno_id' => $alunoId, ':disciplina_id' => $disciplinaId]);
        respostaPlano(['success' => true, 'message' => 'Disciplina associada com sucesso.']);
    }

    if ($action === 'catalogo') {
        if (!in_array($tipo, ['master', 'aee'], true)) respostaPlano(['success' => false, 'message' => 'Sem permissão.'], 403);
        $stmt = $pdo->query('SELECT id, nome FROM disciplinas WHERE ativa = 1 ORDER BY nome');
        respostaPlano(['success' => true, 'disciplinas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'alunos') {
        if (!in_array($tipo, ['master', 'aee'], true)) {
            $alunoId = $autorizacao->alunoDoApoio($usuarioId);
            respostaPlano(['success' => true, 'alunos' => $alunoId ? [['id' => $alunoId]] : []]);
        }
        $stmt = $pdo->query('SELECT id, nome, matricula, turma, foto FROM alunos ORDER BY nome');
        respostaPlano(['success' => true, 'alunos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'cadastrar_paee') {
        if (!in_array($tipo, ['master', 'aee'], true)) respostaPlano(['success' => false, 'message' => 'Sem permissão para cadastrar documentos PAEE.'], 403);
        $alunoId = inteiro($entrada['aluno_id'] ?? 0);
        $nome = trim((string) ($entrada['nome_documento'] ?? ''));
        if (!$autorizacao->podeEditarPlano($tipo, $usuarioId, $alunoId)) respostaPlano(['success' => false, 'message' => 'Sem permissão ou contexto inválido.'], 403);
        if ($nome === '' || mb_strlen($nome) > 150) respostaPlano(['success' => false, 'message' => 'Informe um nome válido para o documento PAEE.'], 422);
        $stmt = $pdo->prepare('INSERT INTO paee (aluno_id, nome_documento, data_atendimento) VALUES (:aluno_id, :nome_documento, NULL)');
        $stmt->execute([':aluno_id' => $alunoId, ':nome_documento' => $nome]);
        respostaPlano(['success' => true, 'id' => (int) $pdo->lastInsertId(), 'message' => 'Documento PAEE cadastrado. Clique no cartão para preenchê-lo.']);
    }

    if ($action === 'peis_aluno') {
        $alunoId = inteiro($_GET['aluno_id'] ?? $entrada['aluno_id'] ?? 0);
        if ($tipo === 'apoio') $alunoId = $autorizacao->alunoDoApoio($usuarioId) ?? 0;
        if (!$autorizacao->podeConsultarPlano($tipo, $usuarioId, $alunoId)) respostaPlano(['success' => false, 'message' => 'Acesso negado ao aluno.'], 403);
        $alunoStmt = $pdo->prepare('SELECT id, nome, matricula, turma FROM alunos WHERE id = :aluno_id LIMIT 1');
        $alunoStmt->execute([':aluno_id' => $alunoId]);
        $aluno = $alunoStmt->fetch(PDO::FETCH_ASSOC);
        if (!$aluno) respostaPlano(['success' => false, 'message' => 'Aluno não encontrado.'], 404);
        $stmt = $pdo->prepare("SELECT p.*, d.nome AS disciplina_nome
            FROM pei p
            LEFT JOIN disciplinas d ON d.id = p.disciplina_id
            WHERE p.aluno_id = :aluno_id
            ORDER BY p.data_elaboracao DESC, p.id DESC");
        $stmt->execute([':aluno_id' => $alunoId]);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $disciplinasStmt = $pdo->prepare("SELECT d.id, d.nome
            FROM alunos_disciplinas ad
            INNER JOIN disciplinas d ON d.id = ad.disciplina_id
            WHERE ad.aluno_id = :aluno_id
              AND ad.ativa = 1
              AND NOT EXISTS (
                  SELECT 1
                  FROM pei p
                  WHERE p.aluno_id = ad.aluno_id
                    AND (
                        p.disciplina_id = ad.disciplina_id
                        OR (p.disciplina_id IS NULL AND LOWER(TRIM(p.nome_documento)) = LOWER(TRIM(d.nome)))
                    )
              )
            ORDER BY d.nome");
        $disciplinasStmt->execute([':aluno_id' => $alunoId]);
        foreach ($disciplinasStmt->fetchAll(PDO::FETCH_ASSOC) as $disciplina) {
            $registros[] = [
                'id' => 0,
                'aluno_id' => $alunoId,
                'disciplina_id' => (int) $disciplina['id'],
                'nome_documento' => $disciplina['nome'],
                'disciplina_nome' => $disciplina['nome'],
                'sem_informacoes' => true,
            ];
        }
        if ($registros === []) {
            $registros[] = [
                'id' => 0,
                'aluno_id' => $alunoId,
                'nome_documento' => 'PEI',
                'sem_informacoes' => true,
            ];
        }
        respostaPlano(['success' => true, 'aluno' => $aluno, 'registros' => $registros]);
    }

    if ($action === 'paees_aluno') {
        $alunoId = inteiro($_GET['aluno_id'] ?? $entrada['aluno_id'] ?? 0);
        if ($tipo === 'apoio') $alunoId = $autorizacao->alunoDoApoio($usuarioId) ?? 0;
        if (!$autorizacao->podeConsultarPlano($tipo, $usuarioId, $alunoId)) respostaPlano(['success' => false, 'message' => 'Acesso negado ao aluno.'], 403);
        $alunoStmt = $pdo->prepare('SELECT id, nome, matricula, turma FROM alunos WHERE id = :aluno_id LIMIT 1');
        $alunoStmt->execute([':aluno_id' => $alunoId]);
        $aluno = $alunoStmt->fetch(PDO::FETCH_ASSOC);
        if (!$aluno) respostaPlano(['success' => false, 'message' => 'Aluno não encontrado.'], 404);
        $stmt = $pdo->prepare("SELECT p.id, p.aluno_id, p.nome_documento,
            p.data_elaboracao, p.responsavel_preenchimento, p.data_atendimento, p.profissional_responsavel
            FROM paee p
            WHERE p.aluno_id = :aluno_id
            ORDER BY COALESCE(p.data_elaboracao, p.data_atendimento) DESC, p.id DESC");
        $stmt->execute([':aluno_id' => $alunoId]);
        respostaPlano(['success' => true, 'aluno' => $aluno, 'registros' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'disciplinas') {
        $alunoId = inteiro($_GET['aluno_id'] ?? $entrada['aluno_id'] ?? 0);
        if ($tipo === 'apoio') {
            $alunoVinculado = $autorizacao->alunoDoApoio($usuarioId);
            if (!$alunoVinculado) respostaPlano(['success' => true, 'aluno' => null, 'disciplinas' => []]);
            $alunoId = $alunoVinculado;
        }
        if (!$autorizacao->usuarioTemAcessoAluno($usuarioId, $alunoId)) respostaPlano(['success' => false, 'message' => 'Acesso negado ao aluno.'], 403);
        $alunoStmt = $pdo->prepare('SELECT id, nome, matricula, turma, foto FROM alunos WHERE id = :id LIMIT 1');
        $alunoStmt->execute([':id' => $alunoId]);
        $aluno = $alunoStmt->fetch(PDO::FETCH_ASSOC);
        if (!$aluno) respostaPlano(['success' => false, 'message' => 'Aluno não encontrado.'], 404);
        $stmt = $pdo->prepare("SELECT d.id, d.nome, d.ativa,
            EXISTS(SELECT 1 FROM pei p WHERE p.aluno_id = ad.aluno_id AND (
                p.disciplina_id = ad.disciplina_id
                OR (p.disciplina_id IS NULL AND LOWER(TRIM(p.nome_documento)) = LOWER(TRIM(d.nome)))
            )) AS tem_pei,
            EXISTS(SELECT 1 FROM paee p WHERE p.aluno_id = ad.aluno_id AND p.disciplina_id = ad.disciplina_id) AS tem_paee
            FROM alunos_disciplinas ad INNER JOIN disciplinas d ON d.id = ad.disciplina_id
            WHERE ad.aluno_id = :aluno_id AND ad.ativa = 1 ORDER BY d.nome");
        $stmt->execute([':aluno_id' => $alunoId]);
        respostaPlano(['success' => true, 'aluno' => $aluno, 'disciplinas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'historico' || $action === 'abrir') {
        if ($tabela === '') respostaPlano(['success' => false, 'message' => 'Módulo inválido.'], 422);
        $id = inteiro($_GET['id'] ?? $entrada['id'] ?? 0);
        $alunoId = inteiro($_GET['aluno_id'] ?? $entrada['aluno_id'] ?? 0);
        $disciplinaId = inteiro($_GET['disciplina_id'] ?? $entrada['disciplina_id'] ?? 0);
        if ($action === 'abrir' && $id > 0) {
            $selectDocumento = $tabela === 'paee'
                ? 'SELECT p.id, p.aluno_id, p.disciplina_id, p.nome_documento, p.data_elaboracao, p.responsavel_preenchimento, p.objetivos_pedagogicos, p.habilidades_desenvolver, p.estrategias_ensino, p.recursos_acessibilidade, p.adaptacoes_curriculares, p.criterios_avaliacao, p.observacoes, a.nome AS aluno_nome FROM paee p INNER JOIN alunos a ON a.id = p.aluno_id WHERE p.id = :id LIMIT 1'
                : "SELECT p.*, d.nome AS disciplina_nome, a.nome AS aluno_nome FROM {$tabela} p LEFT JOIN disciplinas d ON d.id = p.disciplina_id INNER JOIN alunos a ON a.id = p.aluno_id WHERE p.id = :id LIMIT 1";
            $stmt = $pdo->prepare($selectDocumento);
            $stmt->execute([':id' => $id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$registro || !$autorizacao->podeConsultarPlano($tipo, $usuarioId, (int) $registro['aluno_id'])) respostaPlano(['success' => false, 'message' => 'Acesso negado ao documento.'], 403);
            respostaPlano(['success' => true, 'registro' => $registro]);
        }
        if ($tipo === 'apoio') $alunoId = $autorizacao->alunoDoApoio($usuarioId) ?? 0;
        if (!$autorizacao->podeConsultarPlano($tipo, $usuarioId, $alunoId) || !$autorizacao->disciplinaAssociadaAoAluno($alunoId, $disciplinaId)) respostaPlano(['success' => false, 'message' => 'Acesso negado ao contexto.'], 403);
        $dataCampo = $tabela === 'pei' || $tabela === 'paee' ? 'data_elaboracao' : 'data_atendimento';
        $paramsHistorico = [':aluno_id' => $alunoId];
        if ($tabela === 'pei') {
            $stmt = $pdo->prepare("SELECT p.*, COALESCE(vinculada.nome, selecionada.nome) AS disciplina_nome
                FROM pei p
                INNER JOIN disciplinas selecionada ON selecionada.id = :disciplina_selecionada
                LEFT JOIN disciplinas vinculada ON vinculada.id = p.disciplina_id
                WHERE p.aluno_id = :aluno_id
                  AND (p.disciplina_id = :disciplina_filtro
                    OR (p.disciplina_id IS NULL AND LOWER(TRIM(p.nome_documento)) = LOWER(TRIM(selecionada.nome))))
                ORDER BY p.{$dataCampo} DESC, p.id DESC");
            $paramsHistorico[':disciplina_selecionada'] = $disciplinaId;
            $paramsHistorico[':disciplina_filtro'] = $disciplinaId;
        } else {
            $paramsHistorico[':disciplina_id'] = $disciplinaId;
            $stmt = $pdo->prepare("SELECT p.*, d.nome AS disciplina_nome FROM {$tabela} p LEFT JOIN disciplinas d ON d.id = p.disciplina_id WHERE p.aluno_id = :aluno_id AND p.disciplina_id = :disciplina_id ORDER BY p.{$dataCampo} DESC, p.id DESC");
        }
        $stmt->execute($paramsHistorico);
        respostaPlano(['success' => true, 'registros' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'salvar') {
        if ($tabela === '') respostaPlano(['success' => false, 'message' => 'Módulo inválido.'], 422);
        $id = inteiro($entrada['id'] ?? 0);
        $alunoId = inteiro($entrada['aluno_id'] ?? 0);
        $disciplinaId = inteiro($entrada['disciplina_id'] ?? 0);
        if (!$autorizacao->podeEditarPlano($tipo, $usuarioId, $alunoId)) respostaPlano(['success' => false, 'message' => 'Sem permissão ou contexto inválido.'], 403);
        if (($tabela === 'pei' && !$autorizacao->disciplinaAssociadaAoAluno($alunoId, $disciplinaId))
            || ($tabela === 'paee' && $disciplinaId > 0 && !$autorizacao->disciplinaAssociadaAoAluno($alunoId, $disciplinaId))) {
            respostaPlano(['success' => false, 'message' => 'Sem permissão ou contexto inválido.'], 403);
        }
        $campos = $tabela === 'pei'
            ? ['nome_documento','data_elaboracao','responsavel_preenchimento','objetivos_pedagogicos','habilidades_desenvolver','estrategias_ensino','recursos_acessibilidade','adaptacoes_curriculares','criterios_avaliacao','observacoes']
            : ['nome_documento','data_elaboracao','responsavel_preenchimento','objetivos_pedagogicos','habilidades_desenvolver','estrategias_ensino','recursos_acessibilidade','adaptacoes_curriculares','criterios_avaliacao','observacoes'];
        $valores = [];
        foreach ($campos as $campo) $valores[$campo] = array_key_exists($campo, $entrada) ? $entrada[$campo] : null;
        $nomeDisciplina = null;
        if ($tabela === 'pei') {
            $disciplinaStmt = $pdo->prepare('SELECT nome FROM disciplinas WHERE id = :disciplina_id LIMIT 1');
            $disciplinaStmt->execute([':disciplina_id' => $disciplinaId]);
            $nomeDisciplina = $disciplinaStmt->fetchColumn();
            if ($nomeDisciplina === false) respostaPlano(['success' => false, 'message' => 'A disciplina do PEI não foi encontrada.'], 422);
            $valores['nome_documento'] = (string) $nomeDisciplina;
            $valores['responsavel_preenchimento'] = trim((string) $valores['responsavel_preenchimento']);
            if (empty($valores['data_elaboracao']) || $valores['responsavel_preenchimento'] === '') {
                respostaPlano(['success' => false, 'message' => 'Informe a data de elaboração e o responsável pelo preenchimento.'], 422);
            }
        }
        if ($tabela === 'paee') {
            $valores['nome_documento'] = trim((string) $valores['nome_documento']);
            $valores['responsavel_preenchimento'] = trim((string) $valores['responsavel_preenchimento']);
            if ($valores['nome_documento'] === '' || mb_strlen($valores['nome_documento']) > 150
                || empty($valores['data_elaboracao']) || $valores['responsavel_preenchimento'] === '') {
                respostaPlano(['success' => false, 'message' => 'Informe a matéria escolar, a data de elaboração e o responsável pelo preenchimento.'], 422);
            }
        }
        if ($id > 0) {
            $existente = $pdo->prepare("SELECT aluno_id, disciplina_id, nome_documento FROM {$tabela} WHERE id = :id LIMIT 1");
            $existente->execute([':id' => $id]);
            $registro = $existente->fetch(PDO::FETCH_ASSOC);
            $peiLegadoCompativel = false;
            if ($tabela === 'pei' && $registro && $registro['disciplina_id'] === null) {
                $peiLegadoCompativel = mb_strtolower(trim((string) $registro['nome_documento'])) === mb_strtolower(trim((string) $nomeDisciplina));
            }
            if (!$registro || (int) $registro['aluno_id'] !== $alunoId
                || ($tabela === 'pei' && (int) $registro['disciplina_id'] !== $disciplinaId && !$peiLegadoCompativel)
                || ($tabela === 'paee' && $disciplinaId > 0 && (int) ($registro['disciplina_id'] ?? 0) !== $disciplinaId)) {
                respostaPlano(['success' => false, 'message' => 'Documento incompatível com o contexto.'], 403);
            }
            $sets = implode(', ', array_map(fn($campo) => "$campo = :$campo", $campos));
            $valores['id'] = $id; $valores['aluno_id'] = $alunoId;
            $whereDisciplina = $tabela === 'pei'
                ? ' AND (disciplina_id = :disciplina_id OR (disciplina_id IS NULL AND LOWER(TRIM(nome_documento)) = LOWER(TRIM(:disciplina_nome))))'
                : '';
            if ($whereDisciplina !== '') {
                $valores['disciplina_id'] = $disciplinaId;
                $valores['disciplina_nome'] = $nomeDisciplina;
            }
            $stmt = $pdo->prepare("UPDATE {$tabela} SET $sets WHERE id = :id AND aluno_id = :aluno_id{$whereDisciplina}");
            $stmt->execute($valores);
        } else {
            $valores['aluno_id'] = $alunoId; $valores['disciplina_id'] = $disciplinaId > 0 ? $disciplinaId : null;
            $colunasBase = ['aluno_id','disciplina_id'];
            $colunas = implode(', ', array_merge($colunasBase, $campos));
            $marcadores = ':' . implode(', :', array_merge($colunasBase, $campos));
            $stmt = $pdo->prepare("INSERT INTO {$tabela} ($colunas) VALUES ($marcadores)");
            $stmt->execute($valores); $id = (int) $pdo->lastInsertId();
        }
        respostaPlano(['success' => true, 'id' => $id, 'message' => 'Documento salvo com sucesso.']);
    }

    respostaPlano(['success' => false, 'message' => 'Operação inválida.'], 422);
} catch (Throwable $e) {
    error_log('recurso_planos: ' . $e->getMessage());
    respostaPlano(['success' => false, 'message' => 'Falha interna ao processar a solicitação.'], 500);
}
