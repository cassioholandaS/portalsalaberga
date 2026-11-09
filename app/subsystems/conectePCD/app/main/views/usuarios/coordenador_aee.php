<?php
$titulo = 'Coordenação AEE';
$descricao = 'acompanhe a equipe e organize os atendimentos dos alunos PCD.';
$acoes = [
    ['titulo' => 'Resumo', 'icone' => 'bi-grid-1x2', 'url' => 'index.php', 'active' => true],
    ['titulo' => 'Alunos PCD', 'icone' => 'bi-person-wheelchair', 'url' => 'index.php#alunos'],
    ['titulo' => 'Agenda', 'icone' => 'bi-calendar3', 'url' => 'index.php#agenda'],
];
$cartoes = [
    ['titulo' => 'Alunos PCD', 'descricao' => 'Consulte e atualize os cadastros dos alunos acompanhados pelo AEE.', 'icone' => 'bi-person-wheelchair', 'url' => 'index.php#alunos', 'acao' => 'Gerenciar alunos'],
    ['titulo' => 'Equipe de apoio', 'descricao' => 'Visualize os profissionais responsáveis e a distribuição dos acompanhamentos.', 'icone' => 'bi-people', 'url' => 'index.php#equipe', 'acao' => 'Ver equipe'],
    ['titulo' => 'Documentação pedagógica', 'descricao' => 'Acesse PEI, PAEE, laudos e relatórios de acompanhamento.', 'icone' => 'bi-journal-text', 'url' => 'index.php#acompanhamentos', 'acao' => 'Abrir documentos'],
];
require __DIR__ . '/_interface.php';
