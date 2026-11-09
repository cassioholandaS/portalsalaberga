<?php

require_once __DIR__ . '/../auth.php';
requireLogin(['master', 'aee', 'apoio']);
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $colunasDiagnosticos = $pdo->query("SHOW COLUMNS FROM diagnosticos")->fetchAll(PDO::FETCH_COLUMN);
    $temSiglaDiagnostico = in_array('sigla', $colunasDiagnosticos, true);
    $temDescricaoDiagnostico = in_array('descricao', $colunasDiagnosticos, true);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dados = json_decode(file_get_contents('php://input'), true) ?: [];
        $tipo = ($dados['tipo'] ?? '') === 'diagnostico' ? 'diagnosticos' : (($dados['tipo'] ?? '') === 'curso' ? 'cursos' : '');
        $acao = strtolower((string) ($dados['acao'] ?? ''));

        if ($acao === 'excluir') {
            $id = (int) ($dados['id'] ?? 0);
            if ($tipo === '' || $id <= 0) {
                throw new InvalidArgumentException('Informe o tipo e o ID da opção.');
            }

            $stmt = $pdo->prepare("UPDATE {$tipo} SET ativo = 0 WHERE id = :id AND ativo = 1");
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Opção removida com sucesso.']);
            exit;
        }

        $nome = trim((string) ($dados['nome'] ?? ''));
        $sigla = trim((string) ($dados['sigla'] ?? ''));
        $descricao = trim((string) ($dados['descricao'] ?? ''));
        if ($tipo === '' || $nome === '') {
            throw new InvalidArgumentException('Informe um nome válido.');
        }
        if ($tipo === 'diagnosticos' && $sigla === '') {
            throw new InvalidArgumentException('Informe a sigla da condição geral.');
        }
        if ($tipo === 'diagnosticos' && $descricao === '') {
            throw new InvalidArgumentException('Informe a descrição da condição geral.');
        }
        if ($tipo === 'diagnosticos' && !preg_match('/^[A-Za-z0-9.-]{1,30}$/', $sigla)) {
            throw new InvalidArgumentException('A sigla deve conter apenas letras, números, ponto ou hífen.');
        }
        if ($tipo === 'diagnosticos') {
            $campos = ['nome'];
            $parametros = [':nome' => $nome];
            if ($temSiglaDiagnostico) {
                $campos[] = 'sigla';
                $parametros[':sigla'] = strtoupper($sigla);
            }
            if ($temDescricaoDiagnostico) {
                $campos[] = 'descricao';
                $parametros[':descricao'] = $descricao;
            }
            $marcadores = array_map(static fn(string $campo): string => ':' . $campo, $campos);
            $stmt = $pdo->prepare("INSERT INTO {$tipo} (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $marcadores) . ")");
            $stmt->execute($parametros);
            $sigla = $temSiglaDiagnostico ? strtoupper($sigla) : $nome;
            $descricao = $temDescricaoDiagnostico ? $descricao : '';
        } else {
            $stmt = $pdo->prepare("INSERT INTO {$tipo} (nome) VALUES (:nome)");
            $stmt->execute([':nome' => $nome]);
        }
        echo json_encode(['success' => true, 'message' => 'Opção adicionada com sucesso.', 'id' => $pdo->lastInsertId(), 'nome' => $nome, 'sigla' => $sigla, 'descricao' => $descricao]);
        exit;
    }

    $cursos = $pdo->query("SELECT id, nome FROM cursos WHERE ativo = 1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    $camposDiagnostico = 'id, nome';
    if ($temSiglaDiagnostico) {
        $camposDiagnostico .= ", COALESCE(NULLIF(sigla, ''), nome) AS sigla";
    } else {
        $camposDiagnostico .= ', nome AS sigla';
    }
    if ($temDescricaoDiagnostico) {
        $camposDiagnostico .= ", COALESCE(descricao, '') AS descricao";
    } else {
        $camposDiagnostico .= ", '' AS descricao";
    }
    $diagnosticos = $pdo->query("SELECT {$camposDiagnostico} FROM diagnosticos WHERE ativo = 1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    $apoios = $pdo->query("SELECT u.id, u.nome
        FROM usuarios u
        WHERE u.tipo = 'apoio'
          AND u.ativo = 1
          AND u.status_cadastro = 'aprovado'
        ORDER BY u.nome")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'cursos' => $cursos, 'diagnosticos' => $diagnosticos, 'apoios' => $apoios]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível carregar cursos, diagnósticos e apoios.']);
}
