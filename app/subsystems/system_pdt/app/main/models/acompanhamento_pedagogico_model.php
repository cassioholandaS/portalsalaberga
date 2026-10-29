<?php

require_once(__DIR__ . '/../config/connect.php');

class AcompanhamentoPedagogicoModel extends connect
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listar(int $idTurma): array
    {
        $stmt = $this->conn->prepare(
            'SELECT ap.id_acompanhamento, ap.id_turma, ap.id_aluno, ap.data_registro, ap.motivo,
                    ap.observacao, ap.encaminhamento, ap.data_retorno, ap.status, ap.criado_em,
                    a.nome AS aluno_nome, a.matricula, u.nome AS responsavel_nome
             FROM acompanhamentos_pedagogicos ap
             INNER JOIN alunos a ON a.id_aluno = ap.id_aluno
             LEFT JOIN usuarios u ON u.id = ap.id_responsavel
             WHERE ap.id_turma = :id_turma
               AND a.status_aluno = \'ativo\'
             ORDER BY ap.data_registro DESC, ap.id_acompanhamento DESC'
        );
        $stmt->execute([':id_turma' => $idTurma]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criar(array $dados, int $idResponsavel): int
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO acompanhamentos_pedagogicos
                (id_turma, id_aluno, id_responsavel, data_registro, motivo, observacao, encaminhamento, data_retorno, status)
             VALUES
                (:id_turma, :id_aluno, :id_responsavel, :data_registro, :motivo, :observacao, :encaminhamento, :data_retorno, :status)'
        );
        $stmt->execute([
            ':id_turma' => $dados['id_turma'],
            ':id_aluno' => $dados['id_aluno'],
            ':id_responsavel' => $idResponsavel,
            ':data_registro' => $dados['data_registro'],
            ':motivo' => $dados['motivo'],
            ':observacao' => $dados['observacao'],
            ':encaminhamento' => $dados['encaminhamento'] ?: null,
            ':data_retorno' => $dados['data_retorno'] ?: null,
            ':status' => 'aberto',
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function atualizarStatus(int $idAcompanhamento, string $status): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE acompanhamentos_pedagogicos
             SET status = :status
             WHERE id_acompanhamento = :id_acompanhamento'
        );
        return $stmt->execute([
            ':status' => $status,
            ':id_acompanhamento' => $idAcompanhamento,
        ]);
    }
}
