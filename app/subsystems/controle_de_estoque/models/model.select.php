<?php
require_once(__DIR__ . '/sessions.php');
$session = new sessions();
$session->autenticar_session();
$session->tempo_session();

require_once(__DIR__ . '/../config/connect.php');
//print_r($_POST);
class select extends connect
{
    protected string $table1;
    protected string $table2;
    protected string $table3;
    protected string $table4;
    protected string $table5;
    protected string $table6;
    protected string $table7;
    protected string $table8;
    protected string $table9;
    protected string $table10;

    function __construct()
    {
        parent::__construct();
        $table = require(__DIR__.'/../../../../.env/tables.php');
        $this->table1 = $table['salaberga_estoque'][1];
        $this->table2 = $table['salaberga_estoque'][2];
        $this->table3 = $table['salaberga_estoque'][3];
        $this->table4 = $table['salaberga_estoque'][4];
        $this->table5 = $table['salaberga_users'][1];
        $this->table6 = $table['salaberga_users'][2];
        $this->table7 = $table['salaberga_users'][3];
        $this->table8 = $table['salaberga_users'][4];
        $this->table9 = $table['salaberga_users'][5];
        $this->table10 = $table['salaberga_estoque'][5];
    }

    public function select_produtos_id($id)
    {
        $query = $this->connect->query("SELECT p.*, c.nome_categoria AS categoria, c.id as id_categoria FROM $this->table4 p INNER JOIN $this->table1 c ON p.id_categoria = c.id WHERE p.id = '$id'");
        $resultado = $query->fetch(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function select_categoria()
    {
        $query = $this->connect->query("SELECT * FROM $this->table1");
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function select_produtos()
    {
        $query = $this->connect->query(
            "SELECT p.*, c.nome_categoria AS categoria, c.id AS id_categoria, a.nome_ambiente FROM $this->table4 p 
            INNER JOIN $this->table1 c ON p.id_categoria = c.id 
            INNER JOIN $this->table10 a ON p.id_ambiente = a.id 
            ORDER BY p.nome_produto ASC");
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function select_produtos_total()
    {
        $query = $this->connect->query("SELECT count(*) as total FROM $this->table4");
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function select_produtos_critico()
    {
        $query = $this->connect->query("SELECT count(*) as total FROM $this->table4 WHERE quantidade <= 5");
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function select_total_categorias()
    {
        $query = $this->connect->query("SELECT count(*) as total FROM $this->table1");
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function select_produto_nome($barcode)
    {
        $consulta = "SELECT p.*, c.nome_categoria AS categoria, c.id as id_categoria FROM $this->table4 p INNER JOIN $this->table1 c ON p.id_categoria = c.id WHERE p.barcode = :barcode";

        $query = $this->connect->prepare($consulta);
        $query->bindValue(":barcode", $barcode);
        $query->execute();

        return $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    public function select_responsavel()
    {
        $consulta = "SELECT u.nome, s.nome AS nome_setor FROM $this->table5 u INNER JOIN $this->table6 s ON u.id_setor = s.id";
        $query = $this->connect_users->query($consulta);

        return $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Métodos para estatísticas do gráfico
    public function select_produtos_em_estoque()
    {
        $query = $this->connect->query("SELECT count(*) as total FROM $this->table4 WHERE quantidade > 5");
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }

    public function select_produtos_estoque_critico()
    {
        $query = $this->connect->query("SELECT count(*) as total FROM $this->table4 WHERE quantidade <= 5");
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }

    public function select_produtos_sem_estoque()
    {
        $query = $this->connect->query("SELECT count(*) as total FROM $this->table4 WHERE quantidade = 0");
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }

    // Método para validar se a soma dos gráficos bate com o total
    public function select_produtos_total_grafico()
    {
        $em_estoque = $this->select_produtos_em_estoque();
        $critico = $this->select_produtos_estoque_critico();
        $sem_estoque = $this->select_produtos_sem_estoque();
        
        return $em_estoque + $critico + $sem_estoque;
    }

    public function select_ambientes(){
        $query = $this->connect->query("SELECT * FROM $this->table10");
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    
}

