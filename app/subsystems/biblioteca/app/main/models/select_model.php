<?php
$caminho = __DIR__ . '/../config/connect.php';
if (file_exists($caminho)) {
    require_once($caminho);
} else {
    require_once(__DIR__ . '/../config/connect.php');
}

class select_model extends connect
{
    private $catalogo;

    function __construct()
    {
        parent::__construct();
        $this->catalogo = "catalogo";
    }

    private function nomeTurmaExibicao($serie, $nomeTurma)
    {
        $serie = trim((string) $serie);
        $nomeTurma = trim((string) $nomeTurma);
        if ($serie === '') {
            return $nomeTurma;
        }

        $serieRegex = preg_quote($serie, '/');
        $nomeTurma = preg_replace('/^(?:' . $serieRegex . '\s*(?:º|°)?\s*)+/iu', '', $nomeTurma);

        return trim($serie . 'º ' . $nomeTurma);
    }

    // protected function alunoTable($table)
    // {
    //     if (!empty($this->alunosDatabase)) {
    //         return $this->alunosDatabase . '.' . $table;
    //     }

    //     return $table;
    // }

    public function select_subgenero($genero = '')
    {
        if (trim($genero) === '') {
            $select_subgenero = $this->connect_biblioteca->query("SELECT * FROM subgenero");
            return $select_subgenero->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql_idgenero = $this->connect_biblioteca->prepare("SELECT id FROM genero WHERE generos = :genero LIMIT 1");
        $sql_idgenero->bindValue(':genero', $genero, PDO::PARAM_STR);
        $sql_idgenero->execute();
        $id_generos = $sql_idgenero->fetch(PDO::FETCH_ASSOC);

        if (!$id_generos || !isset($id_generos['id'])) {
            return [];
        }

        $id_genero = $id_generos['id'];
        $select_subgenero = $this->connect_biblioteca->prepare("SELECT * FROM subgenero WHERE id_genero = :id_genero");
        $select_subgenero->bindValue(':id_genero', $id_genero, PDO::PARAM_INT);
        $select_subgenero->execute();

        return $select_subgenero->fetchAll(PDO::FETCH_ASSOC);
    }
    public function select_genero()
    {
        $sql_genero = $this->connect_biblioteca->query("SELECT * FROM genero");
        $generos = $sql_genero->fetchAll(PDO::FETCH_ASSOC);

        return $generos;
    }
    public function select_categoria()
    {
        $sql_categoria = $this->connect_biblioteca->query("SELECT id, categorias FROM categoria ORDER BY categorias ASC");
        $categorias = $sql_categoria->fetchAll(PDO::FETCH_ASSOC);

        return $categorias;
    }
    public function select_qrcode($prateleira, $estante)
    {

        $sql_acervo = $this->connect_biblioteca->prepare("SELECT * FROM catalogo WHERE prateleiras = :prateleira AND estantes = :estante");
        $sql_acervo->bindValue(':prateleira', $prateleira);
        $sql_acervo->bindValue(':estante', $estante);
        $sql_acervo->execute();
        $acervo = $sql_acervo->fetchAll(PDO::FETCH_ASSOC);

        return $acervo;
    }
    public function select_nome_livro()
    {
        $sql_nome_livro = $this->connect_biblioteca->query(
            "SELECT 
                c.id,
                c.titulo_livro,
                c.edicao,
                c.editora,
                c.quantidade,
                g.generos,
                sg.subgenero,
                c.ano_publicacao,
                c.corredor,
                c.estantes,
                c.prateleiras,
                cat.categorias AS categoria,
                c.ficcao,
                c.brasileira,
                c.cativo,
                -- GROUP_CONCAT(CONCAT(a.nome_autor, ' ', a.sobrenome_autor) SEPARATOR ', ') AS autores
                a.nome_autor AS autores
            FROM catalogo c 
            LEFT JOIN genero g ON c.id_genero = g.id 
            LEFT JOIN subgenero sg ON c.id_subgenero = sg.id 
            LEFT JOIN categoria cat ON c.id_categoria = cat.id
            LEFT JOIN livros_autores l ON c.id = l.id_livro 
            LEFT JOIN autores a ON l.id_autor = a.id 
            GROUP BY c.id, c.titulo_livro, c.edicao, c.editora, c.quantidade, g.generos, sg.subgenero, 
                     c.ano_publicacao, c.corredor, c.estantes, c.prateleiras, cat.categorias, c.ficcao, c.brasileira, c.cativo
            ORDER BY c.titulo_livro, c.edicao"
        );
        $nome = $sql_nome_livro->fetchAll(PDO::FETCH_ASSOC);

        return $nome;
    }

    public function select_nome_livro_paginated($limit, $offset)
    {
        $sql = "SELECT 
                c.id,
                c.titulo_livro,
                c.edicao,
                c.editora,
                c.quantidade,
                g.generos,
                sg.subgenero,
                c.ano_publicacao,
                c.corredor,
                c.estantes,
                c.prateleiras,
                cat.categorias AS categoria,
                c.ficcao,
                c.brasileira,
                c.cativo,
                a.nome_autor AS autores
            FROM catalogo c 
            LEFT JOIN genero g ON c.id_genero = g.id 
            LEFT JOIN subgenero sg ON c.id_subgenero = sg.id 
            LEFT JOIN categoria cat ON c.id_categoria = cat.id
            LEFT JOIN livros_autores l ON c.id = l.id_livro 
            LEFT JOIN autores a ON l.id_autor = a.id 
            GROUP BY c.id, c.titulo_livro, c.edicao, c.editora, c.quantidade, g.generos, sg.subgenero, 
                     c.ano_publicacao, c.corredor, c.estantes, c.prateleiras, cat.categorias, c.ficcao, c.brasileira, c.cativo
            ORDER BY c.titulo_livro, c.edicao
            LIMIT :limit OFFSET :offset";

        $stmt = $this->connect_biblioteca->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count_catalogo_total()
    {
        $stmt = $this->connect_biblioteca->query('SELECT SUM(quantidade) as total FROM catalogo');
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['total']) : 0;
    }

    public function count_emprestimos_total()
    {
        $stmt = $this->connect_biblioteca->query('SELECT COUNT(*) as total FROM emprestimo');
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['total']) : 0;
    }

    public function count_emprestimos_ativos()
    {
        $stmt = $this->connect_biblioteca->query('SELECT COUNT(*) as total FROM emprestimo WHERE status = 1');
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['total']) : 0;
    }

    public function count_alunos_total()
    {
        $stmt = $this->connect_biblioteca->query('SELECT COUNT(*) as total FROM ' . $this->connect_alunos . '.alunos');
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['total']) : 0;
    }

    public function count_autores_total()
    {
        $stmt = $this->connect_biblioteca->query('SELECT COUNT(*) as total FROM autores');
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['total']) : 0;
    }

    public function count_generos_total()
    {
        $stmt = $this->connect_biblioteca->query('SELECT COUNT(*) as total FROM genero');
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['total']) : 0;
    }

    public function count_exemplares_disponiveis()
    {
        $stmt = $this->connect_biblioteca->query('SELECT SUM(quantidade) as total FROM catalogo');
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalCatalogo = $res ? intval($res['total']) : 0;

        $stmt = $this->connect_biblioteca->query('SELECT COUNT(*) as total FROM emprestimo WHERE status = 1');
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $activeLoans = $res ? intval($res['total']) : 0;

        return max(0, $totalCatalogo - $activeLoans);
    }

    public function count_low_stock_books($threshold = 3)
    {
        $stmt = $this->connect_biblioteca->prepare('SELECT COUNT(*) as total FROM catalogo WHERE quantidade <= ?');
        $stmt->bindValue(1, $threshold, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['total']) : 0;
    }

    public function count_overdue_loans()
    {
        $stmt = $this->connect_biblioteca->query("SELECT COUNT(*) as total FROM emprestimo WHERE status = 1 AND data_devolucao_estipulada < CURDATE()");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? intval($res['total']) : 0;
    }

    public function get_monthly_loan_counts($year = null)
    {
        $year = $year ?? date('Y');
        $stmt = $this->connect_biblioteca->prepare(
            'SELECT MONTH(data_emprestimo) AS mes, COUNT(*) AS total
             FROM emprestimo
             WHERE YEAR(data_emprestimo) = :ano
             GROUP BY MONTH(data_emprestimo)
             ORDER BY MONTH(data_emprestimo)'
        );
        $stmt->bindValue(':ano', (int)$year, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts = array_fill(1, 12, 0);
        foreach ($rows as $row) {
            $monthIndex = intval($row['mes']);
            $counts[$monthIndex] = intval($row['total']);
        }

        return array_values($counts);
    }

    public function get_weekly_loan_counts($date = null)
    {
        $date = $date ? date('Y-m-d', strtotime($date)) : date('Y-m-d');
        $dayOfWeek = intval(date('N', strtotime($date)));
        $monday = date('Y-m-d', strtotime($date . ' -' . ($dayOfWeek - 1) . ' days'));
        $friday = date('Y-m-d', strtotime($monday . ' +4 days'));

        $stmt = $this->connect_biblioteca->prepare(
            'SELECT DAYOFWEEK(data_emprestimo) AS weekday, COUNT(*) AS total
             FROM emprestimo
             WHERE data_emprestimo BETWEEN :monday AND :friday
             GROUP BY DAYOFWEEK(data_emprestimo)
             ORDER BY DAYOFWEEK(data_emprestimo)'
        );
        $stmt->bindValue(':monday', $monday, PDO::PARAM_STR);
        $stmt->bindValue(':friday', $friday, PDO::PARAM_STR);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts = array_fill(0, 5, 0);
        foreach ($rows as $row) {
            $weekday = intval($row['weekday']);
            if ($weekday >= 2 && $weekday <= 6) {
                $counts[$weekday - 2] = intval($row['total']);
            }
        }

        return array_values($counts);
    }

    public function get_recent_books($limit = 5)
    {
        $stmt = $this->connect_biblioteca->prepare("
            SELECT c.id, c.titulo_livro, c.editora, c.edicao, GROUP_CONCAT(DISTINCT a.nome_autor SEPARATOR ', ') as autores
            FROM catalogo c
            LEFT JOIN livros_autores l ON c.id = l.id_livro
            LEFT JOIN autores a ON l.id_autor = a.id
            GROUP BY c.id, c.titulo_livro, c.editora, c.edicao
            ORDER BY c.id DESC
            LIMIT :limit"
        );
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_genre_distribution($limit = 6)
    {
        $stmt = $this->connect_biblioteca->prepare("
            SELECT g.generos as genero, SUM(c.quantidade) as total
            FROM catalogo c
            JOIN genero g ON c.id_genero = g.id
            GROUP BY LOWER(TRIM(g.generos))
            ORDER BY total DESC
            LIMIT :limit"
        );
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_category_distribution($limit = 6)
    {
        $stmt = $this->connect_biblioteca->prepare(" 
            SELECT COALESCE(NULLIF(TRIM(cat.categorias), ''), 'Sem Categoria') as categoria, SUM(c.quantidade) as total
            FROM catalogo c
            LEFT JOIN categoria cat ON c.id_categoria = cat.id
            GROUP BY LOWER(TRIM(cat.categorias))
            ORDER BY total DESC
            LIMIT :limit"
        );
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function select_nome_autor()
    {
        $sql_nome_autor = $this->connect_biblioteca->query("SELECT * FROM autores");
        $nome_autor = $sql_nome_autor->fetchAll(PDO::FETCH_ASSOC);

        return $nome_autor;
    }
    public function select_nome_autor_livro()
    {
        $sql_nome_autor = $this->connect_biblioteca->query("SELECT * FROM autores");
        $nome_autor = $sql_nome_autor->fetchAll(PDO::FETCH_ASSOC);

        return array_column($nome_autor, 'nome_autor');
    }
    public function id_aluno_selecionado($id_aluno_selecionado) {
        if ($id_aluno_selecionado) {
            try {
                $stmt = $this->connect_biblioteca->prepare("
                SELECT 
                    aluno.id_aluno,
                    aluno.nome,
                    aluno.matricula,
                    turma.id_turma,
                    turma.serie_turma,
                    turma.nome_turma,
                    turma.sala_turma,
                    turma.ano_letivo,
                    curso.id_curso,
                    curso.nome_curso,
                    curso.letra
                FROM " . $this->connect_alunos . '.alunos' . " AS aluno
                JOIN " . $this->connect_alunos . '.turmas' . " AS turma ON aluno.id_turma = turma.id_turma
                LEFT JOIN " . $this->connect_alunos . '.cursos' . " AS curso ON turma.id_curso = curso.id_curso
                WHERE aluno.id_aluno = ?
            ");
            $stmt->execute([$id_aluno_selecionado]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($info) {
                $serie = isset($info['serie_turma']) && $info['serie_turma'] !== '' ? trim((string)$info['serie_turma']) : '';
                $nomeTurma = isset($info['nome_turma']) && $info['nome_turma'] !== '' ? trim((string)$info['nome_turma']) : '';
                $turma = $this->nomeTurmaExibicao($serie, $nomeTurma);
                if ($turma === '' && isset($info['sala_turma']) && $info['sala_turma'] !== '') {
                    $turma = 'Sala ' . $info['sala_turma'];
                }

                $curso = isset($info['nome_curso']) && $info['nome_curso'] !== '' ? trim((string)$info['nome_curso']) : null;
                if ($curso && isset($info['letra']) && $info['letra'] !== '') {
                    $curso .= ' (' . $info['letra'] . ')';
                }

                return [
                    'nome' => $info['nome'],
                    'turma' => $turma !== '' ? $turma : null,
                    'curso' => $curso,
                    'id_aluno' => (int)$id_aluno_selecionado,
                    'matricula' => $info['matricula'] ?? null,
                    'id_turma' => $info['id_turma'] ?? null,
                    'ano_letivo' => $info['ano_letivo'] ?? null,
                    'sala_turma' => $info['sala_turma'] ?? null,
                ];
            }
            } catch (PDOException $e) {
                error_log('Erro ao consultar aluno no PDT: ' . $e->getMessage());
            }
        }
        return null;
    }
    public function select_nome_livro_especifico()
    {
        $sql_nome_livro = $this->connect_biblioteca->query("SELECT DISTINCT titulo_livro FROM catalogo");
        $nome = $sql_nome_livro->fetchAll(PDO::FETCH_ASSOC);

        return $nome;
    }
    public function select_livro_especifico($titulos)
    {
        $array_dados = []; // Inicializar o array antes do loop

        foreach ($titulos as $titulo) {
            // Usar prepared statement para evitar SQL Injection
            $select_dados_livro = $this->connect_biblioteca->prepare("SELECT * FROM catalogo WHERE titulo_livro = ?");
            $select_dados_livro->execute([$titulo]);

            $dados_livros = $select_dados_livro->fetchAll(PDO::FETCH_ASSOC);

            array_push($array_dados, $dados_livros);
        }

        return $array_dados;
    } 

    public function select_livros()
    {

        $sql_livro = $this->connect_biblioteca->query(
            'SELECT catalogo.id, catalogo.titulo_livro
             FROM catalogo
             WHERE catalogo.quantidade > (
                 SELECT COUNT(*)
                 FROM emprestimo
                 WHERE emprestimo.id_catalogo = catalogo.id AND emprestimo.status = 1
             )'
        );
        $livros = $sql_livro->fetchAll(PDO::FETCH_ASSOC);

        return $livros;
    }
    public function id_livro_selecionado($id_livro_selecionado)
    {
        if ($id_livro_selecionado) {
            $stmt = $this->connect_biblioteca->prepare("SELECT * FROM catalogo WHERE id = ?");
            $stmt->execute([$id_livro_selecionado]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            return $info;
        }
        return null;
    }

    public function count_emprestimos_ativos_por_catalogo($id_catalogo)
    {
        if (!$id_catalogo) {
            return 0;
        }

        $stmt = $this->connect_biblioteca->prepare("SELECT COUNT(*) as total FROM emprestimo WHERE id_catalogo = ? AND status = 1");
        $stmt->execute([$id_catalogo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? intval($result['total']) : 0;
    }

    public function select_emprestimo_ativo_por_catalogo($id_catalogo)
    {
        if (!$id_catalogo) {
            return null;
        }

        $stmt = $this->connect_biblioteca->prepare(
            'SELECT emprestimo.id, emprestimo.id_aluno, emprestimo.id_catalogo, catalogo.titulo_livro, emprestimo.data_emprestimo, emprestimo.data_devolucao_estipulada
             FROM emprestimo
             JOIN catalogo ON emprestimo.id_catalogo = catalogo.id
             WHERE emprestimo.id_catalogo = ? AND emprestimo.status = 1
             ORDER BY emprestimo.id ASC
             LIMIT 1'
        );
        $stmt->execute([$id_catalogo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function select_emprestimo()
    {
        $sql_emprestimo = $this->connect_biblioteca->query(
            'SELECT emprestimo.id, emprestimo.id_aluno, emprestimo.id_catalogo, catalogo.titulo_livro 
                    FROM emprestimo 
                    JOIN catalogo ON emprestimo.id_catalogo = catalogo.id WHERE status = 1'
        );
        $emprestimos = $sql_emprestimo->fetchAll(PDO::FETCH_ASSOC);

        return $emprestimos;
    }
    public function id_emprestimo_selecionado($id_emprestimo_selecionado)
    {
        if ($id_emprestimo_selecionado) {
            $stmt = $this->connect_biblioteca->prepare("SELECT * FROM emprestimo WHERE id = ?");
            $stmt->execute([$id_emprestimo_selecionado]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            return $info;
        }
        return null;
    }
    public function dados_aluno($id_emprestimo)
    {
        if (empty($id_emprestimo)) {
            return null;
        }

        $sqls = [
            '
                SELECT 
                    aluno.nome,
                    turma.serie_turma,
                    turma.nome_turma,
                    turma.sala_turma,
                    curso.nome_curso,
                    curso.letra
                FROM emprestimo
                JOIN ' . $this->connect_alunos . '.alunos AS aluno ON emprestimo.id_aluno = aluno.id_aluno
                JOIN ' . $this->connect_alunos . '.turmas AS turma ON aluno.id_turma = turma.id_turma
                LEFT JOIN ' . $this->connect_alunos . '.cursos AS curso ON turma.id_curso = curso.id_curso
                WHERE emprestimo.id = ?
            ',
            '
                SELECT 
                    aluno.nome,
                    turma.serie_turma,
                    turma.nome_turma,
                    turma.sala_turma,
                    curso.nome_curso,
                    curso.letra
                FROM emprestimo
                JOIN ' . $this->connect_alunos . '.alunos AS aluno ON emprestimo.id_aluno = aluno.id_aluno
                JOIN ' . $this->connect_alunos . '.turmas AS turma ON aluno.id_turma = turma.id_turma
                LEFT JOIN ' . $this->connect_alunos . '.cursos AS curso ON turma.id_curso = curso.id_curso
                WHERE emprestimo.id_aluno = ?
            '
        ];

        foreach ($sqls as $sql) {
            $stmt = $this->connect_biblioteca->prepare($sql);
            $stmt->execute([$id_emprestimo]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($info) {
                $serie = isset($info['serie_turma']) && $info['serie_turma'] !== '' ? trim((string)$info['serie_turma']) : '';
                $nomeTurma = isset($info['nome_turma']) && $info['nome_turma'] !== '' ? trim((string)$info['nome_turma']) : '';
                $turma = $this->nomeTurmaExibicao($serie, $nomeTurma);
                if ($turma === '' && isset($info['sala_turma']) && $info['sala_turma'] !== '') {
                    $turma = 'Sala ' . $info['sala_turma'];
                }

                $curso = isset($info['nome_curso']) && $info['nome_curso'] !== '' ? trim((string)$info['nome_curso']) : null;
                if ($curso && isset($info['letra']) && $info['letra'] !== '') {
                    $curso .= ' (' . $info['letra'] . ')';
                }

                return [
                    'nome' => $info['nome'] ?? '',
                    'turma' => $turma !== '' ? $turma : null,
                    'curso' => $curso
                ];
            }
        }

        return null;
    }
    public function get_turma_nome($id_turma)
    {
        if ($id_turma === null) {
            return false;
        }
        try {
            $sql = "SELECT 
                        serie_turma,
                        nome_turma
                    FROM " . $this->connect_alunos . ".turmas
                    WHERE id_turma = :id_turma";
            $stmt = $this->connect_biblioteca->prepare($sql);
            $stmt->bindParam(':id_turma', $id_turma, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result
                ? $this->nomeTurmaExibicao($result['serie_turma'], $result['nome_turma'])
                : false;
        } catch (PDOException $e) {
            error_log("Erro ao buscar nome da turma no model: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Busca o aluno que mais pegou livros emprestados em uma turma e mês específicos.
     * Filtra por mês e pelo ano atual.
     * @param int $id_turma O ID da turma.
     * @param int $mes_numero O número do mês (1 a 12).
     * @return string|false Retorna o nome do aluno destaque ou false se não encontrado/erro.
     */

    public function get_aluno_destaque($id_turma, $mes_numero)
    {
        if ($id_turma === null || $mes_numero === null) {
            return false;
        }
        try {
            // Consulta para encontrar o aluno com mais empréstimos na turma e mês DO ANO ATUAL
            $sql = "
                 SELECT
                     a.nome
                 FROM
                     " . $this->connect_alunos . '.alunos' . " a
                 JOIN
                     emprestimo e ON a.id_aluno = e.id_aluno
                 WHERE
                     a.id_turma = :id_turma
                     AND MONTH(e.data_emprestimo) = :mes_numero
                     AND YEAR(e.data_emprestimo) = YEAR(CURDATE()) -- Filtra pelo ano atual
                 GROUP BY
                     a.id_aluno, a.nome
                 ORDER BY
                     COUNT(e.id) DESC
                 LIMIT 1
             ";


            $stmt = $this->connect_biblioteca->prepare($sql);
            $stmt->bindParam(':id_turma', $id_turma, PDO::PARAM_INT);
            $stmt->bindParam(':mes_numero', $mes_numero, PDO::PARAM_INT);
            // Não precisa bindar o ano, pois YEAR(CURDATE()) é resolvido no lado do BD


            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);


            // Retorna o nome do aluno ou uma string vazia se não houver resultado
            return $result ? $result['nome'] : '';
        } catch (PDOException $e) {
            error_log("Erro ao buscar aluno destaque no model: " . $e->getMessage());
            return false; // Retorna false em caso de erro de banco de dados
        }
    }

    public function get_top_readers($limit = 3)
    {
        try {
            // Consulta para obter os 3 alunos que mais pegaram livros emprestados, ordenados pelo total de empréstimos históricos
            $sql = "
                SELECT a.nome, COUNT(e.id) AS total_emprestimos
                FROM " . $this->connect_alunos . '.alunos' . " a
                JOIN emprestimo e ON a.id_aluno = e.id_aluno
                GROUP BY a.id_aluno, a.nome
                ORDER BY total_emprestimos DESC
                LIMIT :limit
            ";
            $stmt = $this->connect_biblioteca->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar top leitores no model: " . $e->getMessage());
            return false;
        }
    }

    public function select_turmas()
    {
        try {
            $sql = "SELECT 
                        id_turma,
                        id_curso,
                        nome_turma,
                        serie_turma,
                        sala_turma,
                        ano_letivo
                    FROM " . $this->connect_alunos . ".turmas
                    ORDER BY ano_letivo DESC, serie_turma, nome_turma";
            $stmt = $this->connect_biblioteca->prepare($sql);
            $stmt->execute();
            $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($turmas as &$turma) {
                $turma['nome_exibicao'] = $this->nomeTurmaExibicao(
                    $turma['serie_turma'],
                    $turma['nome_turma']
                );
            }
            unset($turma);

            return $turmas;
        } catch (PDOException $e) {
            error_log("Erro ao buscar turmas no model: " . $e->getMessage());
            return false;
        }
    }
    public function select_aluno()
    {
        try {
            $sql_aluno = $this->connect_biblioteca->query('SELECT id_aluno, nome FROM ' . $this->connect_alunos . '.alunos');
            return $sql_aluno->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erro ao listar alunos do PDT: ' . $e->getMessage());
            return [];
        }
    }
}
