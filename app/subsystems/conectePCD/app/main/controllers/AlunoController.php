

<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Aluno.php';

class AlunoController {

    private $model;

    public function __construct($pdo){

        $this->model = new Aluno($pdo);

    }

    public function cadastrar(){

        if($_SERVER['REQUEST_METHOD'] == 'POST'){

            $dados = [

                'nome' => $_POST['nome'],
                'matricula' => $_POST['matricula'],
                'cpf' => $_POST['cpf'],
                'data_nascimento' => $_POST['data_nascimento'],
                'sexo' => $_POST['sexo'],
                'turma' => $_POST['turma'],
                'diagnostico' => $_POST['diagnostico'],
                'observacoes' => $_POST['observacoes']

            ];

            $this->model->cadastrar($dados);

            echo "Aluno cadastrado com sucesso!";

        }

    }

}