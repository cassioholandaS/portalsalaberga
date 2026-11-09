<?php
require_once(__DIR__ . '/../models/main_model.php');
 
//verificar tipo de relatório
if (isset($_POST['relatorio'])) {
    $relatorio = $_POST['relatorio'];

    switch ($relatorio) {
        case 'acervo':

            header('Location: ../index.php?section=relatorio_acervo');
            exit;
        case '':

            header('Location: ../index.php?section=relatorios&erro=1');
            exit;
        default:

            header('Location: ../index.php?section=relatorios&erro=2');
            exit;
    }
} else if (isset($_POST['estante']) && isset($_POST['prateleira'])) {

    $estante = $_POST['estante'];
    $prateleira = $_POST['prateleira'];

    header('Location: ../index.php?section=relatorio_pra_est&' . http_build_query([
        'estante' => $estante,
        'prateleira' => 'p' . $prateleira
    ]));
    exit();
}

//cadastrar livros 
else if (
    isset($_POST['nomesubGenero']) && !empty($_POST['nomesubGenero']) &&
    isset($_POST['nome']) && !empty($_POST['nome']) &&
    isset($_POST['titulo']) && !empty($_POST['titulo']) &&
    isset($_POST['editora']) && !empty($_POST['editora']) &&
    isset($_POST['data']) && !empty($_POST['data']) &&
    isset($_POST['corredor']) && !empty($_POST['corredor']) &&
    isset($_POST['quantidade']) && !empty($_POST['quantidade']) &&
    isset($_POST['livro_estante']) && !empty($_POST['livro_estante']) &&
    isset($_POST['livro_prateleira']) && !empty($_POST['livro_prateleira'])
) {
    $nome = $_POST['nome'];
    $titulo = $_POST['titulo'];
    $data = $_POST['data'];
    $editora = $_POST['editora'];
    $edicao = (!isset($_POST['edicao']) || empty($_POST['edicao'])) ? "ENI*" : $_POST['edicao'];
    $quantidade = $_POST['quantidade'];
    $corredor = $_POST['corredor'];
    $estante = $_POST['livro_estante'];
    $prateleira = $_POST['livro_prateleira'];
    $subgenero = $_POST['nomesubGenero'];
    $categoria =  $_POST['categoria'];
    $literatura = $_POST['literatura'] == "Brasileira" ? 1 : 0;
    $ficcao = $_POST['ficcao'] ?? 0;
    $cativo = $_POST['cativo'] ?? 0;


    $model = new main_model();
    $result = $model->
    cadastrar_livros(
        $nome,
        $titulo, 
        $data, 
        $editora, 
        $edicao, 
        $quantidade, 
        $corredor, 
        $estante, 
        $prateleira, 
        $subgenero,
        $literatura, 
        $categoria, 
        $ficcao, 
        $cativo);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=cadastrar_livro&true');
            exit();
        case 2:
            header('Location: ../index.php?section=cadastrar_livro&false');
            exit();
        case 3:
            header('Location: ../index.php?section=cadastrar_livro&ja_cadastrado');
            exit();
        default:
            header('Location: ../index.php?section=dashboard');
            exit();
    }
}
//cadastrar categoria
else if (isset($_POST['nova_categoria']) && !empty($_POST['nova_categoria'])) {
    $nova_categoria = $_POST['nova_categoria'];
    $model = new main_model();
    $result = $model->cadastrar_categoria($nova_categoria);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=cadastrar_categoria&true');
            break;
            case 2:
            header('Location: ../index.php?section=cadastrar_categoria&false');
            break;
        case 3:
            header('Location: ../index.php?section=cadastrar_categoria&ja_cadastrado');
            break;
        default:
            header('Location: ../index.php?section=dashboard');
            exit();
    }
}
//cadastrar genero
else if (isset($_POST['categoria']) && !empty($_POST['categoria']) && isset($_POST['genero']) && !empty($_POST['genero'])) {
    $categoria = $_POST['categoria'];
    $genero = $_POST['genero'];

    $model = new main_model();
    $result = $model->cadastrar_genero($categoria, $genero);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=cadastrar_genero&true');
            break;
        case 2:
            header('Location: ../index.php?section=cadastrar_genero&false');
            break;
        case 3:
            header('Location: ../index.php?section=cadastrar_genero&ja_cadastrado');
            break;
        default:
            header('Location: ../index.php?section=dashboard');
            exit();
    }
}
//cadastrar subgenero
else if (isset($_POST['genero']) && !empty($_POST['genero']) && isset($_POST['subgenero']) && !empty($_POST['subgenero'])) {
    $genero = $_POST['genero'];
    $subgenero = $_POST['subgenero'];

    $model = new main_model();
    $result = $model->cadastrar_subgenero($genero, $subgenero);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=cadastrar_subgenero&true');
            break;
        case 2:
            header('Location: ../index.php?section=cadastrar_subgenero&false');
            break;
        case 3:
            header('Location: ../index.php?section=cadastrar_subgenero&ja_cadastrado');
            break;
        default:
            header('Location: ../index.php?section=dashboard');
            exit();
    }
} else {

    header('Location: ../index.php?section=dashboard');
    exit();
}
