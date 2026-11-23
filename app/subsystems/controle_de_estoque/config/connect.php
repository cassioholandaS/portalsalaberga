<?php
class connect
{
    protected $connect;
    protected $connect_users;

    function __construct()
    {
        $this->connect_database();
    }

    function connect_database()
    {
        $config = require(__DIR__ . "/../../../../.env/config.php");
        $tables = require(__DIR__ . "/../../../../.env/tables.php");
        $requiredStockTables = array_values($tables['salaberga_estoque']);
        $requiredUsersTables = [
            $tables['salaberga_users'][1],
            $tables['salaberga_users'][2],
        ];

        foreach ($this->getEnvironmentOrder() as $environment) {
            if (
                !isset($config[$environment]['salaberga_estoque']) ||
                !isset($config[$environment]['salaberga_users'])
            ) {
                continue;
            }

            try {
                $stockConnection = $this->openConnection($config[$environment]['salaberga_estoque']);
                $this->assertTablesExist($stockConnection, $requiredStockTables);

                $usersConnection = $this->openConnection($config[$environment]['salaberga_users']);
                $this->assertTablesExist($usersConnection, $requiredUsersTables);

                $this->connect = $stockConnection;
                $this->connect_users = $usersConnection;
                return;
            } catch (PDOException $e) {
                error_log("Banco de estoque indisponivel no ambiente {$environment}: " . $e->getMessage());
            }
        }

        $this->connect = null;
        $this->connect_users = null;
        $this->redirectToConnectionError();
    }

    private function getEnvironmentOrder(): array
    {
        $appEnvironment = strtolower((string) getenv('APP_ENV'));

        if (in_array($appEnvironment, ['production', 'prod'], true)) {
            return ['hospedagem', 'local'];
        }

        if (in_array($appEnvironment, ['local', 'development', 'dev'], true)) {
            return ['local', 'hospedagem'];
        }

        $requestHost = strtolower((string) ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? ''));
        $requestHost = preg_replace('/:\d+$/', '', $requestHost);
        $localHosts = ['localhost', '127.0.0.1', '::1'];
        $isLocalHost = in_array($requestHost, $localHosts, true)
            || substr($requestHost, -6) === '.local'
            || substr($requestHost, -5) === '.test';

        return $isLocalHost || $requestHost === ''
            ? ['local', 'hospedagem']
            : ['hospedagem', 'local'];
    }

    private function redirectToConnectionError(): void
    {
        $scriptPath = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $modulePath = '/subsystems/controle_de_estoque/';
        $modulePosition = strpos($scriptPath, $modulePath);

        if ($modulePosition === false) {
            http_response_code(500);
            exit('Não foi possível estabelecer conexão com o banco de dados do estoque.');
        }

        $moduleBasePath = substr($scriptPath, 0, $modulePosition + strlen($modulePath));
        header('Location: ' . $moduleBasePath . 'views/windows/desconnect.php');
        exit();
    }

    private function openConnection(array $databaseConfig): PDO
    {
        $dsn = 'mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['banco'] . ';charset=utf8mb4';
        $connection = new PDO($dsn, $databaseConfig['user'], $databaseConfig['senha']);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $connection;
    }

    private function assertTablesExist(PDO $connection, array $requiredTables): void
    {
        $statement = $connection->prepare(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name'
        );

        foreach ($requiredTables as $tableName) {
            $statement->execute(['table_name' => $tableName]);

            if ((int) $statement->fetchColumn() !== 1) {
                throw new PDOException("Tabela obrigatoria ausente: {$tableName}");
            }
        }
    }
}
