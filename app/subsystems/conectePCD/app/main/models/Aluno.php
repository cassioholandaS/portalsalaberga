<?php

class Aluno {

    private $pdo;

    public function __construct($pdo){

        $this->pdo = $pdo;

    }

    public function cadastrar($dados){

        $sql = "INSERT INTO alunos (

            nome,
            matricula,
            cpf,
            data_nascimento,
            sexo,
            turma,
            curso,
            diagnostico,
            id_apoio

        )

        VALUES (

            :nome,
            :matricula,
            :cpf,
            :data_nascimento,
            :sexo,
            :turma,
            :curso,
            :diagnostico,
            :id_apoio

        )";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([

            ':nome' => $dados['nome'],
            ':matricula' => $dados['matricula'],
            ':cpf' => $dados['cpf'],
            ':data_nascimento' => $dados['data_nascimento'],
            ':sexo' => $dados['sexo'],
            ':turma' => $dados['turma'],
            ':curso' => $dados['curso'],
            ':diagnostico' => $dados['diagnostico'],
            ':id_apoio' => $dados['id_apoio'] ?: null

        ]);

    }

    public function atualizarPerfil(int $id, array $dados): bool
    {
        $sql = 'UPDATE alunos
            SET nome = :nome,
                matricula = :matricula,
                cpf = :cpf,
                data_nascimento = :data_nascimento,
                sexo = :sexo,
                turma = :turma,
                curso = :curso,
                diagnostico = :diagnostico,
                id_apoio = :id_apoio,
                foto = :foto
            WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':matricula' => $dados['matricula'],
            ':cpf' => $dados['cpf'],
            ':data_nascimento' => $dados['data_nascimento'],
            ':sexo' => $dados['sexo'],
            ':turma' => $dados['turma'],
            ':curso' => $dados['curso'],
            ':diagnostico' => $dados['diagnostico'],
            ':id_apoio' => $dados['id_apoio'],
            ':foto' => $dados['foto'],
            ':id' => $id,
        ]);
    }

}