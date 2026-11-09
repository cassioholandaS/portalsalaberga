<?php
class connect 
{
    protected $conn;

    function __construct() 
    {
        $this->connect_database();
    }

    function connect_database(): void
    {
        $host = getenv('PDT_DB_HOST') ?: 'localhost';
        $database = getenv('PDT_DB_NAME') ?: 'sistema_pdt';
        $user = getenv('PDT_DB_USER') ?: 'root';
        $password = getenv('PDT_DB_PASS');
        $password = $password === false ? '' : $password;
        $port = getenv('PDT_DB_PORT');
        $dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4';
        if ($port !== false && $port !== '') {
            $dsn .= ';port=' . (int) $port;
        }

        try {
            $this->conn = new PDO($dsn, $user, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Falha ao conectar ao banco do PDT: ' . $e->getMessage());
            throw new RuntimeException(
                'Não foi possível conectar ao banco do PDT. Verifique a configuração do banco de dados.',
                0,
                $e
            );
        }
    }

    function getConnection(): PDO
    {
        return $this->conn;
    }
}
?>