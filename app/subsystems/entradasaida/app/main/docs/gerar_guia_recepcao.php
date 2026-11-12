<?php

require_once __DIR__ . '/../assets/lib/fpdf/fpdf.php';

function textoPdf(string $texto): string
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
}

function marcador(FPDF $pdf, float $imagemX, float $imagemY, float $imagemW, float $imagemH, int $numero, float $px, float $py, int $capturaLargura, int $capturaAltura): void
{
    $lado = 3.5;
    $x = $imagemX + ($px / $capturaLargura) * $imagemW;
    $y = $imagemY + ($py / $capturaAltura) * $imagemH;
    $pdf->SetFillColor(21, 128, 61);
    $pdf->Rect($x, $y, $lado, $lado, 'F');
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY($x, $y + 0.12);
    $pdf->Cell($lado, 3.1, (string) $numero, 0, 0, 'C');
}

class GuiaRecepcaoPDF extends FPDF
{
    private string $faixaInstitucional;
    private string $logoAreaDev;

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
        $this->Cell(100, 3.5, textoPdf('SISTEMA SALABERGA  |  ENTRADAS E SAÍDAS'));
        $this->SetTextColor(55, 65, 81);
        $this->SetFont('Arial', 'B', 17);
        $this->SetXY(9, 11);
        $this->Cell(205, 7, textoPdf($this->pageTitle));
        $this->SetTextColor(21, 128, 61);
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY(226, 14);
        $this->Cell(62, 4, textoPdf('PERFIL: RECEPÇÃO'), 0, 0, 'R');
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
            $this->Cell(220, 4, textoPdf('SISTEMA SALABERGA  |  ENTRADAS E SAÍDAS'));
            $this->Cell(59, 4, textoPdf('Página 1/{nb}'), 0, 0, 'R');
            return;
        }

        $this->SetDrawColor(21, 128, 61);
        $this->SetLineWidth(0.7);
        $this->Line(9, 197, 288, 197);
        $this->SetTextColor(107, 114, 128);
        $this->SetFont('Arial', '', 8);
        $this->SetXY(9, 199);
        $this->Cell(220, 4, textoPdf('Guia visual de uso  •  As telas podem variar conforme os registros do dia.'));
        $this->Cell(59, 4, textoPdf('Página ' . $this->PageNo()), 0, 0, 'R');
    }

    public string $pageTitle = '';
}

function linhasTexto(FPDF $pdf, string $texto, float $largura): int
{
    $linhas = 1;
    $linha = '';
    foreach (explode(' ', $texto) as $palavra) {
        $teste = $linha === '' ? $palavra : $linha . ' ' . $palavra;
        if ($pdf->GetStringWidth(textoPdf($teste)) > $largura && $linha !== '') {
            $linhas++;
            $linha = $palavra;
        } else {
            $linha = $teste;
        }
    }
    return $linhas;
}

function itemLegenda(FPDF $pdf, float $x, float $y, float $largura, int $numero, string $titulo, string $texto): float
{
    $textoX = $x + 9;
    $textoLargura = $largura - 11;
    $nLinhas = linhasTexto($pdf, $texto, $textoLargura);
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
    $pdf->Cell($textoLargura, 4, textoPdf($titulo));
    $pdf->SetTextColor(55, 65, 81);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($textoX, $y + 6.1);
    $pdf->MultiCell($textoLargura, 3.35, textoPdf($texto));

    return $altura;
}

function limparDadosAusencias(string $arquivo): void
{
    $imagem = imagecreatefrompng($arquivo);
    if (!$imagem) {
        throw new RuntimeException('Não foi possível abrir a captura de ausências para remover os dados pessoais.');
    }

    $branco = imagecolorallocate($imagem, 255, 255, 255);
    $borda = imagecolorallocate($imagem, 229, 231, 235);
    $texto = imagecolorallocate($imagem, 107, 114, 128);
    imagefilledrectangle($imagem, 65, 383, 1198, 892, $branco);
    imageline($imagem, 65, 383, 1198, 383, $borda);
    imagestring(
        $imagem,
        5,
        240,
        615,
        'Dados dos alunos omitidos para preservar a privacidade.',
        $texto
    );
    imagepng($imagem, $arquivo);
    imagedestroy($imagem);
}

$pastaImagens = __DIR__ . '/storyboard_recepcao';
$capturaAusencias = $pastaImagens . '/06-ausencias.png';
limparDadosAusencias($capturaAusencias);

$paginas = [
    [
        'titulo' => 'Painel principal',
        'imagem' => '08-guia-dashboard.png',
        'marcadores' => [[1, 200, 179], [2, 592, 179], [3, 200, 315], [4, 592, 315], [5, 200, 451], [6, 592, 451], [7, 200, 587], [8, 978, 12]],
        'itens' => [
            ['Registrar atraso', 'Abre o formulário para registrar a entrada atrasada de um aluno.'],
            ['Saída antecipada', 'Abre o registro de saída antes do horário regular, com responsável e justificativa.'],
            ['Saída para estágio', 'Registra a saída do aluno para estágio e permite consultar as saídas do dia.'],
            ['Atrasos registrados', 'Acompanha os atrasos do dia, consulta o histórico por turma e permite as ações disponíveis para cada registro.'],
            ['Ausências registradas', 'Consulta os alunos sem saída por QR Code registrada até o horário definido para a apuração.'],
            ['Últimas saídas', 'Mostra a frequência de saídas do dia organizada por turma.'],
            ['Guia do usuário', 'Abre este guia em uma nova aba. O cartão aparece apenas no painel do perfil da recepção.'],
            ['Tema e sessão', 'O controle de tema alterna a aparência. “Sair” encerra a sessão; os dois ficam agrupados na barra superior.']
        ],
        'nota' => 'Escolha um card para abrir a operação correspondente. O cartão “Guia do usuário” abre este PDF em uma nova aba.'
    ],
    [
        'titulo' => 'Registrar atraso',
        'imagem' => '02-atraso.png',
        'marcadores' => [[1, 606, 313], [2, 1148, 313], [3, 1148, 482], [4, 1142, 636]],
        'itens' => [
            ['Aluno', 'Selecione o estudante na lista. Confira a identificação antes de prosseguir.'],
            ['Dados do registro', 'Escolha o motivo do atraso. Confira usuário, data e horário exibidos para o registro.'],
            ['Justificativa', 'Informe o motivo complementar, quando necessário, no campo de texto.'],
            ['Ações', '“Registrar Atraso” salva o lançamento. “Cancelar” abandona a operação e retorna ao painel.']
        ],
        'nota' => 'Os campos obrigatórios estão identificados com asterisco. Confirme o aluno e o motivo antes de registrar.'
    ],
    [
        'titulo' => 'Registrar saída antecipada',
        'imagem' => '03-saida-antecipada.png',
        'marcadores' => [[1, 608, 296], [2, 1150, 296], [3, 608, 408], [4, 1150, 408], [5, 1150, 578], [6, 1148, 649]],
        'itens' => [
            ['Dados do aluno', 'Selecione o estudante que deixará a escola.'],
            ['Responsável', 'Informe o nome de quem autoriza e escolha o tipo de responsável.'],
            ['Justificativa', 'Descreva por que a saída está sendo solicitada.'],
            ['Dados da saída', 'Selecione o motivo e confira o usuário, a data e o horário do lançamento.'],
            ['Condutor', 'Preencha o nome e o tipo de condutor quando aplicável.'],
            ['Ações', 'Use “Registrar Saída” para salvar ou “Cancelar” para voltar sem registrar. Os itens com * são obrigatórios.']
        ],
        'nota' => 'Revise o aluno, a autorização e a justificativa antes de salvar o registro.'
    ],
    [
        'titulo' => 'Registrar saída para estágio',
        'imagem' => '04-saida-estagio.png',
        'marcadores' => [[1, 870, 379], [2, 870, 486], [3, 870, 565]],
        'itens' => [
            ['Aluno', 'Abra a lista e selecione o estudante que sairá para estágio.'],
            ['Data e hora', 'Confira ou ajuste a data e a hora da saída nos campos correspondentes.'],
            ['Ações', 'O ícone de olho consulta as saídas. “Registrar Saída” confirma o lançamento; “Cancelar” volta sem salvar.']
        ],
        'nota' => 'Confirme os dados da saída antes de registrar. O ícone de olho abre a consulta de saídas para estágio.'
    ],
    [
        'titulo' => 'Atrasos registrados',
        'imagem' => '10-atrasos-do-dia.png',
        'marcadores' => [[1, 177, 270], [2, 37, 315], [3, 278, 379]],
        'itens' => [
            ['Turmas e contadores', 'Cada cartão representa uma turma. O contador mostra quantos atrasos foram registrados hoje.'],
            ['Busca por aluno', 'O campo de busca filtra os registros do cartão correspondente sem alterar as demais turmas.'],
            ['Validar', 'O botão abre a análise da justificativa. A validação fica disponível somente no mesmo dia, até as 17h.']
        ],
        'nota' => 'Clique no registro fora do botão “Validar” para abrir a confirmação de exclusão. Atrasos são considerados entre 07:40 e 11:40; o leitor físico pode funcionar mesmo com a câmera indisponível.'
    ],
    [
        'titulo' => 'Confirmar a exclusão de um atraso',
        'imagem' => '14-confirmar-exclusao-atraso.png',
        'marcadores' => [[1, 236, 124], [2, 292, 281], [3, 500, 281]],
        'itens' => [
            ['Conferir o registro', 'Revise aluno, matrícula, turma, data e horário antes de decidir.'],
            ['Manter registro', 'Fecha a confirmação sem alterar o histórico.'],
            ['Excluir atraso', 'Remove o registro após a confirmação. A exclusão não pode ser desfeita.']
        ],
        'nota' => 'A confirmação é aberta ao clicar no registro fora dos botões do card ou do histórico. Só confirme após revisar os dados; a exclusão não pode ser desfeita.'
    ],
    [
        'titulo' => 'Analisar e validar um atraso',
        'imagem' => '11-validar-atraso.png',
        'imagemLargura' => 120,
        'legendaX' => 137,
        'legendaLargura' => 151,
        'notaAltura' => 9,
        'marcadores' => [[1, 508, 114], [2, 508, 224], [3, 508, 315], [4, 508, 474], [5, 508, 568], [6, 508, 619]],
        'itens' => [
            ['Resumo do registro', 'Confira aluno, turma, data e horário antes de avaliar.'],
            ['Motivo', 'Selecione a classificação adequada quando ela ainda não estiver definida no registro.'],
            ['Justificativa', 'Informe o motivo do atraso. O campo é obrigatório quando a justificativa não veio preenchida.'],
            ['Avaliação', 'Escolha “Aprovar” ou “Recusar”. A recusa exige atenção às orientações exibidas pelo sistema.'],
            ['Responsável', 'O sistema identifica automaticamente a pessoa autenticada que está avaliando o registro.'],
            ['Confirmar', 'O botão só é habilitado após preencher os campos exigidos. Revise tudo antes de confirmar.']
        ],
        'nota' => 'A análise deve ocorrer no mesmo dia e até as 17h. A captura usa dados inteiramente fictícios; nenhuma validação foi enviada.'
    ],
    [
        'titulo' => 'Consultar o histórico de atrasos',
        'imagem' => '12-historico-atrasos.png',
        'marcadores' => [[1, 183, 58], [2, 321, 58], [3, 466, 58], [4, 552, 69], [5, 381, 120], [6, 478, 120]],
        'itens' => [
            ['Aluno', 'Pesquise pelo nome para localizar registros e atualizar o contador do cartão.'],
            ['Situação', 'Filtre por pendente, aprovada, recusada ou mantenha “Todas”.'],
            ['Data', 'Restringe a lista à data selecionada, sem aceitar datas futuras.'],
            ['Limpar', 'Remove os filtros e restaura a consulta completa da turma.'],
            ['Imprimir', 'Imprime a autorização para entrar em sala referente ao registro validado.'],
            ['Ver e excluir', '“Ver” consulta os detalhes do registro. Clicar no registro fora dos botões abre a confirmação de exclusão.']
        ],
        'nota' => 'O histórico é separado por turma. Use os filtros antes de consultar; confira os dados do registro antes de imprimir ou confirmar uma exclusão.'
    ],
    [
        'titulo' => 'Imprimir os documentos do atraso',
        'imagem' => '13-opcoes-impressao.png',
        'marcadores' => [[1, 508, 110], [2, 508, 173]],
        'itens' => [
            ['Registro de Atraso', 'Imprime os dados da ocorrência e a justificativa registrada.'],
            ['Autorização para entrar em sala', 'Imprime o horário e a aula autorizada após a validação, sem criar um segundo atraso.'],
            ['Conferência final', 'A opção “Impressão” fica disponível nos registros validados. Confira aluno, turma e data antes de escolher o documento.']
        ],
        'nota' => 'A escolha abre a janela de impressão do navegador. Se necessário, selecione “Salvar como PDF” em vez de uma impressora.'
    ],
    [
        'titulo' => 'Consultar ausências',
        'imagem' => '06-ausencias.png',
        'marcadores' => [[1, 225, 222], [2, 444, 222], [3, 598, 222], [4, 1170, 347]],
        'itens' => [
            ['Data', 'Escolha o dia da apuração. A tela informa a data e o horário de referência.'],
            ['Turma', 'Selecione uma turma específica ou mantenha “Todas as turmas”.'],
            ['Gerar relatório', 'Atualiza a consulta para a data e turma escolhidas e prepara a impressão do relatório.'],
            ['Resultados', 'A tabela organiza o resultado por aluno, matrícula, turma e situação. Os dados pessoais da captura foram omitidos.']
        ],
        'nota' => 'A apuração considera os alunos sem saída por QR Code registrada até 13h20. A lista exibida é referente à data e à turma selecionadas.'
    ],
    [
        'titulo' => 'Acompanhar últimas saídas',
        'imagem' => '07-ultimas-saidas.png',
        'marcadores' => [[1, 980, 119], [2, 246, 267], [2, 546, 267], [2, 846, 267], [2, 1146, 267], [3, 232, 312], [3, 532, 312], [3, 832, 312], [3, 1132, 312]],
        'itens' => [
            ['Frequência em tempo real', 'O cabeçalho apresenta o horário, as orientações e o estado do leitor QR.'],
            ['Turmas e contadores', 'Cada cartão mostra a turma e a quantidade de saídas registradas no dia. Os lançamentos aparecem na área abaixo do cabeçalho colorido.'],
            ['Buscar aluno', 'Pesquise pelo nome no campo da turma correspondente. O filtro se aplica somente àquele cartão.']
        ],
        'nota' => 'Use esta tela para acompanhar os registros recentes por turma. A captura não contém dados pessoais de alunos.'
    ]
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

$pdf = new GuiaRecepcaoPDF($faixaInstitucional, $logoAreaDev);
$pdf->SetMargins(9, 9, 9);
$pdf->SetAutoPageBreak(false);
$pdf->SetTitle(textoPdf('Guia visual - Entrada e Saída - Recepção'));
$pdf->SetAuthor(textoPdf('Sistema Salaberga'));

$pdf->pageTitle = 'Guia visual de uso';
$pdf->AddPage('L');
$pdf->Image($logoSalaberga, 103.5, 57, 90);
$pdf->SetTextColor(21, 128, 61);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(14, 99);
$pdf->Cell(269, 5, textoPdf('MATERIAL DE APOIO  |  EDIÇÃO 2026'), 0, 0, 'C');
$pdf->SetTextColor(31, 41, 55);
$pdf->SetFont('Arial', 'B', 24);
$pdf->SetXY(14, 108);
$pdf->Cell(269, 11, textoPdf('GUIA RÁPIDO'), 0, 0, 'C');
$pdf->SetTextColor(21, 128, 61);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(14, 121);
$pdf->Cell(269, 8, textoPdf('Sistema de Entradas e Saídas'), 0, 0, 'C');
$pdf->SetTextColor(75, 85, 99);
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(35, 134);
$pdf->MultiCell(227, 6, textoPdf('Um roteiro prático para registrar e acompanhar atrasos, saídas antecipadas, estágio, ausências e frequência dos estudantes.'), 0, 'C');
$pdf->SetTextColor(55, 65, 81);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(14, 153);
$pdf->MultiCell(269, 5, textoPdf('ATRASOS  •  SAÍDAS ANTECIPADAS  •  ESTÁGIO  •  AUSÊNCIAS  •  HISTÓRICO'), 0, 'C');
$pdf->SetDrawColor(21, 128, 61);
$pdf->SetLineWidth(0.7);
$pdf->Line(45, 165, 252, 165);
$pdf->SetTextColor(31, 75, 49);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(14, 171);
$pdf->Cell(269, 5, textoPdf('ESCOLA ESTADUAL DE EDUCAÇÃO PROFISSIONAL'), 0, 0, 'C');
$pdf->SetTextColor(107, 114, 128);
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(14, 179);
$pdf->Cell(269, 5, textoPdf('SALABERGA TORQUATO GOMES DE MATOS  |  PERFIL: RECEPÇÃO'), 0, 0, 'C');

foreach ($paginas as $pagina) {
    $pdf->pageTitle = $pagina['titulo'];
    $pdf->AddPage();

    $imagem = $pastaImagens . '/' . $pagina['imagem'];
    if (!is_file($imagem)) {
        throw new RuntimeException('Captura não encontrada: ' . $pagina['imagem']);
    }

    $imagemX = $pagina['imagemX'] ?? 9;
    $imagemY = 31;
    $imagemW = $pagina['imagemLargura'] ?? 170;
    $dimensoes = getimagesize($imagem);
    $imagemH = $imagemW * ($dimensoes[1] / $dimensoes[0]);
    $pdf->Image($imagem, $imagemX, $imagemY, $imagemW, $imagemH, 'PNG');

    foreach ($pagina['marcadores'] as [$numero, $px, $py]) {
        marcador($pdf, $imagemX, $imagemY, $imagemW, $imagemH, $numero, $px, $py, $dimensoes[0], $dimensoes[1]);
    }

    $xLegenda = $pagina['legendaX'] ?? 183;
    $larguraLegenda = $pagina['legendaLargura'] ?? 105;
    $yLegenda = 31;
    foreach ($pagina['itens'] as $indice => [$titulo, $texto]) {
        $altura = itemLegenda($pdf, $xLegenda, $yLegenda, $larguraLegenda, $indice + 1, $titulo, $texto);
        $yLegenda += $altura + 1.6;
    }

    $yNota = max($imagemY + $imagemH + 2.5, $yLegenda + 1);
    $pdf->SetFillColor(254, 249, 235);
    $pdf->SetDrawColor(245, 223, 166);
    $alturaNota = $pagina['notaAltura'] ?? 13;
    $pdf->Rect(9, $yNota, 279, $alturaNota, 'DF');
    $pdf->SetTextColor(92, 70, 24);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY(12, $yNota + ($alturaNota === 9 ? 1 : 2.2));
    $pdf->MultiCell(273, $alturaNota === 9 ? 3 : 4, textoPdf($pagina['nota']));
}

$destino = __DIR__ . '/Guia_de_instrucoes_entradasaida_recepcao.pdf';
$pdf->Output('F', $destino);
echo 'Guia gerado: ' . $destino . PHP_EOL;
