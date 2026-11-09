<?php
$titulo = 'Meu acompanhamento';
$descricao = 'consulte seus registros e acompanhe as informações disponíveis para você.';
$acoes = [
    ['titulo' => 'Meu resumo', 'icone' => 'bi-grid-1x2', 'url' => 'index.php', 'active' => true],
    ['titulo' => 'Meu perfil', 'icone' => 'bi-person', 'url' => 'index.php#perfil'],
    ['titulo' => 'Documentos', 'icone' => 'bi-folder2-open', 'url' => 'index.php#documentos'],
];
$cartoes = [
    ['titulo' => 'Meu perfil PCD', 'descricao' => 'Visualize seus dados cadastrais e informações de acompanhamento.', 'icone' => 'bi-person-wheelchair', 'url' => 'index.php#perfil', 'acao' => 'Consultar perfil'],
    ['titulo' => 'Planos de atendimento', 'descricao' => 'Consulte os objetivos e estratégias registrados no PEI e no PAEE.', 'icone' => 'bi-journal-text', 'url' => 'index.php#documentos', 'acao' => 'Ver planos'],
    ['titulo' => 'Relatórios e laudos', 'descricao' => 'Acesse os documentos liberados pela equipe responsável pelo seu acompanhamento.', 'icone' => 'bi-file-earmark-medical', 'url' => 'index.php#documentos', 'acao' => 'Ver documentos'],
];
require __DIR__ . '/_interface.php';
