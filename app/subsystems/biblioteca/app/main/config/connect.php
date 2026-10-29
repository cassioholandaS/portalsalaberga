<?php
class connect
{
    protected $connect_biblioteca;
    protected $connect_alunos;

    function __construct()
    {
        $this->connect_database();
    }
    function connect_database()
    {
        try {
            $host = 'localhost';
            $database = 'salaberga_biblioteca';
            $user = 'root';
            $password = '';

            $this->connect_alunos = 'sistema_pdt'; // Altere para o schema de alunos externo no mesmo servidor
            $this->connect_biblioteca = new PDO(
                'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4',
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

        } catch (PDOException $e) {
            try {
                $host = 'localhost';
                $database = 'u750204740_sistBiblioteca';
                $user = 'u750204740_sistBiblioteca';
                $password = 'paoComOvo123!@##';

                $this->connect_alunos = 'u750204740_sysPDT';
                $this->connect_biblioteca = new PDO(
                    'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4',
                    $user,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                die('Erro! O sistema não possui conexão com o banco de dados.');
            }
        }
    }
}
