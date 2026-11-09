<?php
require_once(__DIR__ . '/../models/select_model.php');
$select_model = new select_model();

if (isset($_POST['aluno'])) {
    $id_aluno_selecionado = $_POST['aluno'];
    $info = $select_model->id_aluno_selecionado($id_aluno_selecionado);
    if ($info) {
        echo json_encode($info);
    } else {
        echo json_encode(['turma' => null, 'curso' => null]);
    }
} else {
    echo json_encode(['turma' => null, 'curso' => null]);
}
?> 