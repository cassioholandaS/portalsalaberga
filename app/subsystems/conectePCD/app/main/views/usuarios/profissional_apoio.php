<?php
$titulo = 'Acompanhamento de apoio';
$descricao = 'registre e consulte o acompanhamento dos alunos sob sua responsabilidade.';
$acoes = [
    ['titulo' => 'Resumo', 'icone' => 'bi-grid-1x2', 'url' => 'index.php', 'active' => true],
    ['titulo' => 'Meus alunos', 'icone' => 'bi-person-wheelchair', 'url' => 'index.php#alunos'],
    ['titulo' => 'Agenda', 'icone' => 'bi-calendar3', 'url' => 'index.php#agenda'],
];
$cartoes = [
    ['titulo' => 'Meus alunos', 'descricao' => 'Consulte os perfis e informações dos alunos vinculados ao seu atendimento.', 'icone' => 'bi-person-wheelchair', 'url' => 'index.php#alunos', 'acao' => 'Ver alunos'],
    ['titulo' => 'Relatórios', 'descricao' => 'Registre observações, resultados, dificuldades e encaminhamentos.', 'icone' => 'bi-clipboard2-pulse', 'url' => 'index.php#relatorios', 'acao' => 'Registrar acompanhamento'],
    ['titulo' => 'PEI e PAEE', 'descricao' => 'Acompanhe objetivos, estratégias e recursos planejados para cada aluno.', 'icone' => 'bi-journal-text', 'url' => 'index.php#acompanhamentos', 'acao' => 'Consultar planos'],
];
require __DIR__ . '/_interface.php';
