<?php

require_once dirname(__DIR__, 6) . '/app/main/assets/libs/FPDF/fpdf.php';

function textoManualAEE(string $texto): string
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
}

function marcadorManualAEE(FPDF $pdf, float $imagemX, float $imagemY, float $imagemW, float $imagemH, int $numero, float $px, float $py, int $capturaLargura, int $capturaAltura): void
{
    $lado = 3;
    $x = $imagemX + ($px / $capturaLargura) * $imagemW;
    $y = $imagemY + ($py / $capturaAltura) * $imagemH;
    $pdf->SetFillColor(21, 128, 61);
    $pdf->Rect($x, $y, $lado, $lado, 'F');
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($x, $y + 0.25);
    $pdf->Cell($lado, 3.2, (string) $numero, 0, 0, 'C');
}

class GuiaAEEPDF extends FPDF
{
    private string $faixaInstitucional;
    private string $logoAreaDev;
    public string $pageTitle = '';

    public function __construct(string $faixaInstitucional, string $logoAreaDev)
    {
        parent::__construct('L', 'mm', 'A4');
        $this->faixaInstitucional = $faixaInstitucional;
        $this->logoAreaDev = $logoAreaDev;
        $this->AliasNbPages();
    }

    public function Header(): void
    {
        if ($this->PageNo() === 1) {
            $this->SetFillColor(239, 244, 238);
            $this->Rect(0, 0, 297, 55, 'F');
            $this->Image($this->faixaInstitucional, 10, 3, 244);
            $this->Image($this->logoAreaDev, 260, 8, 27);
            $this->SetDrawColor(21, 128, 61);
            $this->SetLineWidth(1);
            $this->Line(9, 54, 288, 54);
            return;
        }

        $this->SetFillColor(5, 82, 50);
        $this->Rect(0, 0, 297, 7, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY(9, 1.8);
        $this->Cell(145, 3.5, textoManualAEE('CONECTEPCD  |  GUIA DO USUÁRIO AEE'));
        $this->SetTextColor(55, 65, 81);
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(9, 11);
        $this->Cell(205, 7, textoManualAEE($this->pageTitle));
        $this->SetTextColor(21, 128, 61);
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY(226, 14);
        $this->Cell(62, 4, textoManualAEE('PERFIL: AEE'), 0, 0, 'R');
    }

    public function Footer(): void
    {
        if ($this->PageNo() === 1) {
            $this->SetDrawColor(21, 128, 61);
            $this->SetLineWidth(0.7);
            $this->Line(10, 198, 287, 198);
            $this->SetTextColor(107, 114, 128);
            $this->SetFont('Arial', '', 8);
            $this->SetXY(14, 201);
            $this->Cell(220, 4, textoManualAEE('CONECTEPCD  |  GUIA VISUAL DO PERFIL AEE'));
            $this->Cell(59, 4, textoManualAEE('Página 1/{nb}'), 0, 0, 'R');
            return;
        }

        $this->SetDrawColor(21, 128, 61);
        $this->SetLineWidth(0.7);
        $this->Line(9, 197, 288, 197);
        $this->SetTextColor(107, 114, 128);
        $this->SetFont('Arial', '', 8);
        $this->SetXY(9, 199);
        $this->Cell(220, 4, textoManualAEE('Guia visual de uso  |  Capturas com dados fictícios para demonstração.'));
        $this->Cell(59, 4, textoManualAEE('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'R');
    }
}

function linhasManualAEE(FPDF $pdf, string $texto, float $largura): int
{
    $linhas = 1;
    $linha = '';
    foreach (explode(' ', $texto) as $palavra) {
        $teste = $linha === '' ? $palavra : $linha . ' ' . $palavra;
        if ($pdf->GetStringWidth(textoManualAEE($teste)) > $largura && $linha !== '') {
            $linhas++;
            $linha = $palavra;
        } else {
            $linha = $teste;
        }
    }
    return $linhas;
}

function itemManualAEE(FPDF $pdf, float $x, float $y, float $largura, int $numero, string $titulo, string $texto): float
{
    $textoX = $x + 9;
    $textoLargura = $largura - 11;
    $pdf->SetFont('Arial', '', 8);
    $nLinhas = linhasManualAEE($pdf, $texto, $textoLargura);
    $altura = 10.1 + ($nLinhas * 3.35);

    $pdf->SetFillColor(243, 248, 244);
    $pdf->SetDrawColor(224, 234, 226);
    $pdf->Rect($x, $y, $largura, $altura, 'DF');
    $pdf->SetFillColor(21, 128, 61);
    $pdf->Rect($x + 2.2, $y + 2.1, 4.2, 4.2, 'F');
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY($x + 2.2, $y + 2.55);
    $pdf->Cell(4.2, 3.2, (string) $numero, 0, 0, 'C');

    $pdf->SetTextColor(31, 75, 49);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY($textoX, $y + 1.8);
    $pdf->Cell($textoLargura, 4, textoManualAEE($titulo));
    $pdf->SetTextColor(55, 65, 81);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($textoX, $y + 6.1);
    $pdf->MultiCell($textoLargura, 3.35, textoManualAEE($texto));

    return $altura;
}

$pastaImagens = __DIR__ . '/storyboard_aee';
$paginas = [
    [
        'titulo' => 'Painel principal e guias',
        'imagem' => '01-inicio.png',
        'marcadores' => [[1, 550, 132], [2, 700, 190], [3, 1160, 190], [4, 700, 510], [5, 1190, 510]],
        'itens' => [
            ['Indicadores', 'Confira os totais de alunos, acompanhantes e turmas atendidas.'],
            ['Evolução por quesito', 'Veja um resumo das avaliações e dos resultados registrados para os alunos.'],
            ['Alunos recentes', 'Abra o perfil de um aluno para conferir seus dados e acompanhamentos.'],
            ['Guia do usuário - Apoio', 'Acesse o guia com orientações específicas para a rotina do profissional de Apoio.'],
            ['Guia do usuário - AEE', 'Acesse o guia com as funções de cadastro, planos, critérios e relatórios do perfil AEE.']
        ],
        'nota' => 'Os guias abrem em uma nova aba. Os dados das capturas são de demonstração; informações de outros registros locais foram ocultadas.'
    ],
    [
        'titulo' => 'Localizar e selecionar aluno',
        'imagem' => '02-perfis.png',
        'marcadores' => [[1, 640, 73], [2, 967, 73], [3, 1168, 73], [4, 590, 338]],
        'itens' => [
            ['Busca', 'Pesquise pelo nome ou pela matrícula do aluno.'],
            ['Filtros', 'Refine a lista por turma ou condição geral.'],
            ['Novo aluno', 'Inicie o cadastro de um estudante com os dados obrigatórios.'],
            ['Perfil do aluno', 'Selecione um cartão para abrir o cadastro, os quesitos e os documentos do estudante.']
        ],
        'nota' => 'O botão Exportar CSV baixa a relação exibida para conferência em planilha. Verifique os filtros antes de exportar.'
    ],
    [
        'titulo' => 'Perfil do aluno e critérios',
        'imagem' => '03-perfil-quesitos.png',
        'marcadores' => [[1, 520, 175], [2, 270, 625], [3, 1138, 418], [4, 1100, 173]],
        'itens' => [
            ['Dados do estudante', 'Confira matrícula, turma, curso, condições gerais e profissional de apoio vinculado.'],
            ['Vínculo de Apoio', 'Consulte o vínculo atual e use as opções disponíveis para ajustar a associação.'],
            ['Quesitos do aluno', 'Marque os critérios pertinentes e defina os pesos usados nas avaliações.'],
            ['Abas de acompanhamento', 'Alterne entre Quesitos do aluno, PEI, PAEE e Laudo.']
        ],
        'nota' => 'Salve as alterações dos quesitos somente depois de revisar os critérios selecionados e seus pesos.'
    ],
    [
        'titulo' => 'Atualizar cadastro e vincular Apoio',
        'imagem' => '04-editar-aluno.png',
        'marcadores' => [[1, 820, 255], [2, 820, 478], [3, 820, 630], [4, 820, 704]],
        'itens' => [
            ['Identificação', 'Atualize nome, matrícula, CPF, nascimento e sexo conforme o cadastro escolar.'],
            ['Turma e curso', 'Revise a turma, o curso e as condições gerais associadas ao estudante.'],
            ['Profissional de apoio', 'Selecione o profissional responsável ou deixe sem vínculo definido, conforme a organização da escola.'],
            ['Salvar alterações', 'Confirme os dados antes de salvar; use Cancelar para fechar sem aplicar mudanças.']
        ],
        'nota' => 'Mantenha os dados cadastrais corretos para que a equipe visualize o aluno e seus acompanhamentos adequadamente.'
    ],
    [
        'titulo' => 'Consultar o PEI do aluno',
        'imagem' => '05-perfil-pei.png',
        'marcadores' => [[1, 835, 152], [2, 1145, 285], [3, 1145, 425]],
        'itens' => [
            ['Aba PEI', 'Abra a aba para consultar os Planos Educacionais Individualizados associados ao aluno.'],
            ['Registro do plano', 'Confira o título, a data e o responsável pelo preenchimento.'],
            ['Conteúdo pedagógico', 'Leia objetivos, habilidades, estratégias, recursos de acessibilidade e critérios de avaliação.']
        ],
        'nota' => 'Esta aba reúne os documentos do aluno. O módulo PEI no menu também permite cadastrar e associar planos por disciplina.'
    ],
    [
        'titulo' => 'Consultar o PAEE do aluno',
        'imagem' => '06-perfil-paee.png',
        'marcadores' => [[1, 920, 152], [2, 1145, 285], [3, 1145, 425]],
        'itens' => [
            ['Aba PAEE', 'Abra a aba para consultar os Planos de Atendimento Educacional Especializado.'],
            ['Registro do atendimento', 'Confira o documento, a data e o profissional responsável.'],
            ['Estratégias e resultados', 'Consulte objetivos, recursos, estratégias, resultados observados e encaminhamentos.']
        ],
        'nota' => 'Use as informações do plano para alinhar o atendimento com os demais profissionais que acompanham o estudante.'
    ],
    [
        'titulo' => 'Cadastrar e associar um PEI',
        'imagem' => '07-pei.png',
        'marcadores' => [[1, 1128, 106], [2, 1128, 230], [3, 1128, 485]],
        'itens' => [
            ['Aluno selecionado', 'Confirme o nome do estudante associado ao formulário no cabeçalho.'],
            ['Identificação do PEI', 'Informe a disciplina e a data de elaboração do plano.'],
            ['Conteúdo pedagógico', 'Preencha objetivos, habilidades e estratégias, e continue rolando para completar os demais campos.']
        ],
        'nota' => 'O botão para salvar fica depois dos campos exibidos; role o formulário até o final antes de concluir. A aba PEI do perfil permite consultar os documentos associados.'
    ],
    [
        'titulo' => 'Cadastrar e associar um PAEE',
        'imagem' => '08-paee.png',
        'marcadores' => [[1, 1128, 106], [2, 1128, 230], [3, 1128, 485]],
        'itens' => [
            ['Aluno selecionado', 'Confirme o nome do estudante associado ao formulário no cabeçalho.'],
            ['Identificação do PAEE', 'Informe o nome do documento e a data de elaboração do atendimento.'],
            ['Plano de atendimento', 'Registre objetivos, habilidades e estratégias; continue rolando para preencher os demais campos.']
        ],
        'nota' => 'O botão para salvar fica depois dos campos exibidos; role o formulário até o final antes de concluir. Registre informações objetivas e alinhadas ao trabalho da equipe.'
    ],
    [
        'titulo' => 'Gerenciar acessos de usuários',
        'imagem' => '09-usuarios.png',
        'marcadores' => [[1, 1158, 260], [2, 730, 355], [3, 1170, 470]],
        'itens' => [
            ['Solicitações', 'Analise os cadastros pendentes e aprove ou recuse cada solicitação.'],
            ['Buscar usuário', 'Localize contas cadastradas e filtre os resultados conforme o status.'],
            ['Ações da conta', 'Use os controles da linha para consultar, editar ou alterar o status do usuário.']
        ],
        'nota' => 'Confira o perfil solicitado e os dados de identificação antes de liberar ou atualizar um acesso.'
    ],
    [
        'titulo' => 'Gerenciar quesitos de avaliação',
        'imagem' => '10-quesitos.png',
        'marcadores' => [[1, 1170, 90], [2, 1120, 150], [3, 1190, 393]],
        'itens' => [
            ['Novo quesito', 'Cadastre um critério com título e descrição para as avaliações.'],
            ['Critérios cadastrados', 'Revise a lista e o status dos quesitos disponíveis.'],
            ['Ações', 'Edite, ative ou desative e remova critérios conforme a necessidade da equipe.']
        ],
        'nota' => 'A alteração dos quesitos pode afetar as opções exibidas nas avaliações e os critérios associados aos alunos.'
    ],
    [
        'titulo' => 'Cadastrar condição geral',
        'imagem' => '11-condicao.png',
        'marcadores' => [[1, 8, 152], [2, 8, 230], [3, 540, 385]],
        'itens' => [
            ['Nova condição', 'Abra o formulário pelo menu Condições gerais.'],
            ['Dados da condição', 'Informe o nome, a sigla e a descrição da condição geral.'],
            ['Salvar', 'Revise as informações e salve para disponibilizar a condição nos cadastros dos alunos.']
        ],
        'nota' => 'Use denominações claras e siglas consistentes com os registros institucionais.'
    ],
    [
        'titulo' => 'Relatórios de evolução e frequência',
        'imagem' => '12-relatorios.png',
        'marcadores' => [[1, 495, 100], [2, 516, 480], [3, 947, 110], [4, 1186, 460], [5, 1168, 685]],
        'itens' => [
            ['Filtros', 'Escolha o aluno, o quesito, o período e as datas para delimitar os registros.'],
            ['Gerar relatório', 'Prepare a versão para impressão e use o navegador para imprimir ou salvar em PDF.'],
            ['Gráfico', 'Compare os resultados dos quesitos no intervalo selecionado.'],
            ['Tipo de registro', 'Alterne a tabela entre avaliações e frequência para consultar presenças e faltas.'],
            ['Registros detalhados', 'Confira as datas, os resultados, os quesitos e as observações disponíveis.']
        ],
        'nota' => 'Feliz equivale a Bom, Neutro permanece Neutro e Triste equivale a Ruim. Ajuste o período para incluir os registros desejados.'
    ],
    [
        'titulo' => 'Consultar registros de frequência',
        'imagem' => '13-frequencia.png',
        'marcadores' => [[1, 1186, 460], [2, 1168, 610], [3, 1168, 660]],
        'itens' => [
            ['Alternar para frequência', 'Selecione Frequência para trocar a tabela de avaliações pelos registros de presença.'],
            ['Data e presença', 'Confira em que data o aluno esteve presente ou faltou.'],
            ['Responsável pelo registro', 'Verifique qual profissional lançou cada registro de frequência.']
        ],
        'nota' => 'Os filtros de aluno e de datas também limitam os registros exibidos na tabela de frequência.'
    ],
];

$assetsInstitucionais = dirname(__DIR__, 6) . '/app/subsystems/biblioteca/app/main/assets/img';
$faixaInstitucional = $assetsInstitucionais . '/cabeçalho_relatorios.jpg';
$logoAreaDev = $assetsInstitucionais . '/logo_area_dev.png';
$logoSalaberga = $assetsInstitucionais . '/logo_salaberga.png';
foreach ([$faixaInstitucional, $logoAreaDev, $logoSalaberga] as $imagemInstitucional) {
    if (!is_file($imagemInstitucional)) {
        throw new RuntimeException('Imagem institucional não encontrada: ' . $imagemInstitucional);
    }
}

$pdf = new GuiaAEEPDF($faixaInstitucional, $logoAreaDev);
$pdf->SetMargins(9, 9, 9);
$pdf->SetAutoPageBreak(false);
$pdf->SetTitle(textoManualAEE('Guia do usuário - conectePCD - AEE'));
$pdf->SetAuthor(textoManualAEE('Sistema Salaberga'));

$pdf->AddPage('L');
$pdf->Image($logoSalaberga, 103.5, 57, 90);
$pdf->SetTextColor(21, 128, 61);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(14, 99);
$pdf->Cell(269, 5, textoManualAEE('MATERIAL DE APOIO  |  EDIÇÃO 2026'), 0, 0, 'C');
$pdf->SetTextColor(31, 41, 55);
$pdf->SetFont('Arial', 'B', 24);
$pdf->SetXY(14, 108);
$pdf->Cell(269, 11, textoManualAEE('GUIA DO USUÁRIO'), 0, 0, 'C');
$pdf->SetTextColor(21, 128, 61);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(14, 121);
$pdf->Cell(269, 8, textoManualAEE('Sistema conectePCD  |  Perfil AEE'), 0, 0, 'C');
$pdf->SetTextColor(75, 85, 99);
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(35, 134);
$pdf->MultiCell(227, 6, textoManualAEE('Roteiro visual para gerenciar alunos, organizar os planos PEI e PAEE, administrar critérios e acessos e acompanhar relatórios.'), 0, 'C');
$pdf->SetTextColor(55, 65, 81);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(14, 153);
$pdf->MultiCell(269, 5, textoManualAEE('PERFIS  •  PEI  •  PAEE  •  USUÁRIOS  •  QUESITOS  •  CONDIÇÕES  •  RELATÓRIOS'), 0, 'C');
$pdf->SetDrawColor(21, 128, 61);
$pdf->SetLineWidth(0.7);
$pdf->Line(45, 165, 252, 165);
$pdf->SetTextColor(31, 75, 49);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(14, 171);
$pdf->Cell(269, 5, textoManualAEE('ESCOLA ESTADUAL DE EDUCAÇÃO PROFISSIONAL'), 0, 0, 'C');
$pdf->SetTextColor(107, 114, 128);
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(14, 179);
$pdf->Cell(269, 5, textoManualAEE('SALABERGA TORQUATO GOMES DE MATOS  |  PERFIL: AEE'), 0, 0, 'C');

foreach ($paginas as $pagina) {
    $pdf->pageTitle = $pagina['titulo'];
    $pdf->AddPage();

    $imagem = $pastaImagens . '/' . $pagina['imagem'];
    if (!is_file($imagem)) {
        throw new RuntimeException('Captura não encontrada: ' . $pagina['imagem']);
    }

    $imagemX = 9;
    $imagemY = 31;
    $imagemW = $pagina['imagem'] === '11-condicao.png' ? 170 : 180;
    $dimensoes = getimagesize($imagem);
    if (!$dimensoes) {
        throw new RuntimeException('Não foi possível ler a captura: ' . $pagina['imagem']);
    }
    $imagemH = $imagemW * ($dimensoes[1] / $dimensoes[0]);
    $pdf->Image($imagem, $imagemX, $imagemY, $imagemW, $imagemH, 'PNG');

    foreach ($pagina['marcadores'] as [$numero, $px, $py]) {
        marcadorManualAEE($pdf, $imagemX, $imagemY, $imagemW, $imagemH, $numero, $px, $py, $dimensoes[0], $dimensoes[1]);
    }

    $xLegenda = 193;
    $larguraLegenda = 95;
    $yLegenda = 31;
    foreach ($pagina['itens'] as $indice => [$titulo, $texto]) {
        $altura = itemManualAEE($pdf, $xLegenda, $yLegenda, $larguraLegenda, $indice + 1, $titulo, $texto);
        $yLegenda += $altura + 1.6;
    }

    $yNota = max($imagemY + $imagemH + 2.5, $yLegenda + 1);
    $pdf->SetFillColor(254, 249, 235);
    $pdf->SetDrawColor(245, 223, 166);
    $pdf->Rect(9, $yNota, 279, 13, 'DF');
    $pdf->SetTextColor(92, 70, 24);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY(12, $yNota + 2);
    $pdf->MultiCell(273, 4, textoManualAEE($pagina['nota']));
}

$destino = __DIR__ . '/Guia_usuario_conectePCD_aee.pdf';
$pdf->Output('F', $destino);
echo 'Guia gerado: ' . $destino . PHP_EOL;
