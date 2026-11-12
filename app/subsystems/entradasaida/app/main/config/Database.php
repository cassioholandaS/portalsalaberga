<?php

class connect
{
    protected $connect;

    function __construct()
    {
        $this->connect_database();
    }

    function connect_database()
    {
        $production = strtolower(trim((string) getenv('APP_ENV'))) === 'production';
        $environmentKeys = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
        $databaseConfig = [];

        foreach ($environmentKeys as $key) {
            $value = getenv($key);
            if ($production && ($value === false || $value === '')) {
                error_log('Entrada/Saida database configuration is missing ' . $key . '.');
                die('Erro! A configuração do banco de dados está incompleta.');
            }
            $databaseConfig[$key] = $value;
        }

        $host = $databaseConfig['DB_HOST'] === false || $databaseConfig['DB_HOST'] === ''
            ? 'localhost'
            : $databaseConfig['DB_HOST'];
        $database = $databaseConfig['DB_NAME'] === false || $databaseConfig['DB_NAME'] === ''
            ? 'entradasaida'
            : $databaseConfig['DB_NAME'];
        $user = $databaseConfig['DB_USER'] === false || $databaseConfig['DB_USER'] === ''
            ? 'root'
            : $databaseConfig['DB_USER'];
        $password = $databaseConfig['DB_PASSWORD'] === false ? '' : $databaseConfig['DB_PASSWORD'];
        $port = getenv('DB_PORT');

        $dsn = 'mysql:host=' . $host;
        if ($port !== false && $port !== '') {
            $dsn .= ';port=' . $port;
        }
        $dsn .= ';dbname=' . $database . ';charset=utf8mb4';

        try {
            $this->connect = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            error_log('Entrada/Saida MySQL connection failed: ' . $e->getMessage());
            die('Erro! O sistema não possui conexão com o banco de dados.');
        }
    }

    public function getConnection()
    {
        return $this->connect;
    }
}
