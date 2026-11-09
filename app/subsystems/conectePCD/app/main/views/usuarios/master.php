<?php
$titulo = 'Painel administrativo';
$descricao = 'gerencie usuários, alunos e todos os registros do sistema.';
$acoes = [
    ['titulo' => 'Visão geral', 'icone' => 'bi-grid-1x2', 'url' => 'index.php', 'active' => true],
    ['titulo' => 'Usuários', 'icone' => 'bi-people', 'url' => 'index.php#usuarios'],
    ['titulo' => 'Alunos PCD', 'icone' => 'bi-person-wheelchair', 'url' => 'index.php#alunos'],
];
$cartoes = [
    ['titulo' => 'Usuários e permissões', 'descricao' => 'Cadastre, aprove e organize os perfis de acesso do sistema.', 'icone' => 'bi-shield-check', 'url' => 'index.php#usuarios', 'acao' => 'Gerenciar usuários'],
    ['titulo' => 'Alunos PCD', 'descricao' => 'Consulte cadastros, perfis e documentos de todos os alunos.', 'icone' => 'bi-person-wheelchair', 'url' => 'index.php#alunos', 'acao' => 'Ver alunos'],
    ['titulo' => 'Acompanhamentos', 'descricao' => 'Acesse PEI, PAEE, laudos, relatórios e agenda institucional.', 'icone' => 'bi-folder2-open', 'url' => 'index.php#acompanhamentos', 'acao' => 'Abrir registros'],
];
require __DIR__ . '/_interface.php';
