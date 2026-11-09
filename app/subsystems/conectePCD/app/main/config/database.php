<?php
class connect 
{
    protected $conn;

    function __construct() 
    {
        $this->connect_database();
    }

    function connect_database()
    {
        date_default_timezone_set('America/Sao_Paulo');
        $conexoes = [];

        $lerVariavel = static function (string $nome, string $padrao = ''): string {
            $valor = getenv($nome);
            if ($valor === false || $valor === '') {
                $valor = $_ENV[$nome] ?? $_SERVER[$nome] ?? $padrao;
            }

            return (string) $valor;
        };

        $configArquivo = dirname(__DIR__, 2) . '/.env/config.php';
        $config = is_file($configArquivo) ? require $configArquivo : [];

        $configHospedagem = is_array($config['host'] ?? null) ? $config['host'] : [];
        $ambiente = strtolower($lerVariavel('APP_ENV', $lerVariavel('DB_ENV')));
        if ($ambiente === '') {
            $hostAcesso = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
            $ehAcessoLocal = $hostAcesso !== '' && preg_match('/^(localhost|127\.0\.0\.1|::1)(:\d+)?$/', $hostAcesso);
            $ambiente = $ehAcessoLocal ? 'local' : ($hostAcesso === '' ? 'local' : 'production');
        }
        $emHospedagem = in_array($ambiente, ['production', 'prod', 'hosting', 'hospedagem'], true);

        if ($emHospedagem) {
            $conexoes[] = [
                $lerVariavel('DB_HOST', $configHospedagem['host'] ?? 'localhost'),
                $lerVariavel('DB_NAME', $configHospedagem['database'] ?? 'u750204740_registro_pcd'),
                $lerVariavel('DB_USER', $configHospedagem['user'] ?? 'u750204740_registro_pcd'),
                $lerVariavel('DB_PASSWORD', $configHospedagem['password'] ?? 'Pcd12345678!@##'),
            ];
        } else {
            $conexoes[] = ['localhost', 'sistema_pcd', 'root', ''];
        }

        $ultimoErro = null;
        foreach ($conexoes as [$host, $database, $user, $password]) {
            if ($database === '' || $user === '') {
                continue;
            }

            try {
                $porta = $lerVariavel('DB_PORT');
                $dsn = 'mysql:host=' . $host . ($porta !== '' ? ';port=' . $porta : '') . ';dbname=' . $database . ';charset=utf8mb4';
                $opcoes = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                    $opcoes[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4, time_zone = '-03:00'";
                }
                $this->conn = new PDO(
                    $dsn,
                    $user,
                    $password,
                    $opcoes
                );
                return;
            } catch (PDOException $e) {
                $ultimoErro = $e;
            }
        }

        error_log('Falha ao conectar ao banco de dados: ' . ($ultimoErro ? $ultimoErro->getMessage() : 'configuração ausente'));
        throw new RuntimeException('Não foi possível conectar ao banco de dados.', 0, $ultimoErro);
    }

    function getConnection() 
    {
        return $this->conn;
    }
}

$pdo = (new connect())->getConnection();
$conn = $pdo;