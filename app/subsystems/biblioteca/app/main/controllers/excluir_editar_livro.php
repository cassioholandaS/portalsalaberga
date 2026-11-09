<?php

require_once(__DIR__ . '/../models/main_model.php');

/*controller para excluir livro*/
if (isset($_POST['excluir_livro']) && !empty($_POST['excluir_livro'])) {
    $id_livro = $_POST['excluir_livro'];
    $model = new main_model();
    $result = $model->excluir_livro($id_livro);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=excluir_livro&true');
            break;
        case 2:
            header('Location: ../index.php?section=excluir_livro&error');
            break;
    }
    /*controller para editar livro*/
} else if (
    isset($_POST['id_livro']) && !empty($_POST['id_livro']) &&
    isset($_POST['titulo_livro']) && !empty($_POST['titulo_livro']) &&
    isset($_POST['edicao']) && !empty($_POST['edicao']) &&
    isset($_POST['editora']) && !empty($_POST['editora']) &&
    isset($_POST['quantidade']) && !empty($_POST['quantidade']) &&
    isset($_POST['corredor']) && !empty($_POST['corredor']) &&
    isset($_POST['estante']) && !empty($_POST['estante']) &&
    isset($_POST['prateleira']) && !empty($_POST['prateleira']) &&
    isset($_POST['subgenero']) && !empty($_POST['subgenero']) &&
    isset($_POST['genero']) && !empty($_POST['genero']) &&
    isset($_POST['ano_publicacao']) && !empty($_POST['ano_publicacao'])
) {
    $id_livro = $_POST['id_livro'];
    $titulo = $_POST['titulo_livro'];
    $edicao = $_POST['edicao'];
    $editora = $_POST['editora'];
    $quantidade = $_POST['quantidade'];
    $corredor = $_POST['corredor'];
    $estante = $_POST['estante'];
    $prateleira = $_POST['prateleira'];
    $literatura = (isset($_POST['literatura']) && $_POST['literatura'] == 1) ? 1 : 0;
    $categoria = $_POST['categoria'];
    $ficcao = (isset($_POST['ficcao']) && $_POST['ficcao'] == 1) ? 1 : 0;
    $cativo = (isset($_POST['cativo']) && $_POST['cativo'] == 1) ? 1 : 0;
    $genero = $_POST['genero'];
    $ano_publicacao = $_POST['ano_publicacao'];
    $subgenero = $_POST['subgenero'];
    $genero = $_POST['genero'];

    $model = new main_model();
    $result = $model->editar_livro($id_livro, $titulo, $ano_publicacao, $editora, $edicao, $quantidade, $corredor, $estante, $prateleira, $genero, $subgenero, $literatura, $categoria, $ficcao, $cativo);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=editar_livro&true');
            break;
        case 2:
            header('Location: ../index.php?section=editar_livro&error');
            break;
    }
    /*controller para editar autor*/
} else if (
    isset($_POST['id_autor']) && !empty($_POST['id_autor']) &&
    isset($_POST['nome']) && !empty($_POST['nome'])
) {
    $id_autor = $_POST['id_autor'];
    $nome = $_POST['nome'];

    $model = new main_model();
    $result = $model->editar_autor($id_autor, $nome);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=editar_autor&true');
            break;
        case 2:
            header('Location: ../index.php?section=editar_autor&error');
            break;
    }
    /*controller para editar categoria */
} else if (
    isset($_POST['id_categoria']) && !empty($_POST['id_categoria']) &&
    isset($_POST['nome']) && !empty($_POST['nome'])
) {
    $id_categoria = $_POST['id_categoria'];
    $nome = $_POST['nome'];

    $model = new main_model();
    $result = $model->editar_categoria($id_categoria, $nome);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=editar_categoria&true');
            break;
        case 2:
            header('Location: ../index.php?section=editar_categoria&error');
            break;
    }
    /*controller para editar gênero */
} else if (
    isset($_POST['id_genero']) && !empty($_POST['id_genero']) &&
    isset($_POST['nome']) && !empty($_POST['nome'])
) {
    $id_genero = $_POST['id_genero'];
    $nome = $_POST['nome'];

    $model = new main_model();
    $result = $model->editar_genero($id_genero, $nome);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=editar_genero&true');
            break;
        case 2:
            header('Location: ../index.php?section=editar_genero&error');
            break;
    }
    /*controller para editar subgênero */
} else if (
    isset($_POST['id_subgenero']) && !empty($_POST['id_subgenero']) &&
    isset($_POST['nome']) && !empty($_POST['nome'])
) {
    $id_subgenero = $_POST['id_subgenero'];
    $nome = $_POST['nome'];

    $model = new main_model();
    $result = $model->editar_subgenero($id_subgenero, $nome);

    switch ($result) {
        case 1:
            header('Location: ../index.php?section=editar_subgenero&true');
            break;
        case 2:
            header('Location: ../index.php?section=editar_subgenero&error');
            break;
    }
} else{

    header('Location: ../index.php?section=dashboard');
    exit();
}
