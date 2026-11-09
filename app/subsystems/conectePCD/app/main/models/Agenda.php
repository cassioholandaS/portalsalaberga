<?php

class Agenda {
    private $pdo;
    public function __construct($pdo){ $this->pdo = $pdo; }

    public function listar(){
        $sql = 'SELECT * FROM agenda ORDER BY data_evento ASC, horario ASC, id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar($dados){
        $sql = "INSERT INTO agenda (titulo, data_evento, horario, descricao, status) VALUES (:titulo, :data_evento, :horario, :descricao, :status)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titulo' => $dados['titulo'],
            ':data_evento' => $dados['data_evento'],
            ':horario' => $dados['horario'] ?: null,
            ':descricao' => $dados['descricao'] ?? null,
            ':status' => $dados['status'] ?? 'ativo'
        ]);
    }

    public function excluir($id){
        $sql = 'DELETE FROM agenda WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
