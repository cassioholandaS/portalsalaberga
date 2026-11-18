<?php

require_once dirname(__DIR__, 6) . '/app/main/assets/libs/FPDF/fpdf.php';

function textoManualApoio(string $texto): string
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
}

function marcadorManualApoio(FPDF $pdf, float $imagemX, float $imagemY, float $imagemW, float $imagemH, int $numero, float $px, float $py, int $capturaLargura, int $capturaAltura): void
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

class GuiaApoioPDF extends FPDF
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
        $this->Cell(145, 3.5, textoManualApoio('CONECTEPCD  |  GUIA DO USUÁRIO APOIO'));
        $this->SetTextColor(55, 65, 81);
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(9, 11);
        $this->Cell(205, 7, textoManualApoio($this->pageTitle));
        $this->SetTextColor(21, 128, 61);
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY(226, 14);
        $this->Cell(62, 4, textoManualApoio('PERFIL: APOIO'), 0, 0, 'R');
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
            $this->Cell(220, 4, textoManualApoio('CONECTEPCD  |  GUIA VISUAL DO PERFIL APOIO'));
            $this->Cell(59, 4, textoManualApoio('Página 1/{nb}'), 0, 0, 'R');
            return;
        }

        $this->SetDrawColor(21, 128, 61);
        $this->SetLineWidth(0.7);
        $this->Line(9, 197, 288, 197);
        $this->SetTextColor(107, 114, 128);
        $this->SetFont('Arial', '', 8);
        $this->SetXY(9, 199);
        $this->Cell(220, 4, textoManualApoio('Guia visual de uso  |  Capturas com dados fictícios para demonstração.'));
        $this->Cell(59, 4, textoManualApoio('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'R');
    }
}

function linhasManualApoio(FPDF $pdf, string $texto, float $largura): int
{
    $linhas = 1;
    $linha = '';
    foreach (explode(' ', $texto) as $palavra) {
        $teste = $linha === '' ? $palavra : $linha . ' ' . $palavra;
        if ($pdf->GetStringWidth(textoManualApoio($teste)) > $largura && $linha !== '') {
            $linhas++;
            $linha = $palavra;
        } else {
            $linha = $teste;
        }
    }
    return $linhas;
}

function itemManualApoio(FPDF $pdf, float $x, float $y, float $largura, int $numero, string $titulo, string $texto): float
{
    $textoX = $x + 9;
    $textoLargura = $largura - 11;
    $pdf->SetFont('Arial', '', 8);
    $nLinhas = linhasManualApoio($pdf, $texto, $textoLargura);
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
    $pdf->Cell($textoLargura, 4, textoManualApoio($titulo));
    $pdf->SetTextColor(55, 65, 81);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($textoX, $y + 6.1);
    $pdf->MultiCell($textoLargura, 3.35, textoManualApoio($texto));

    return $altura;
}

$pastaImagens = __DIR__ . '/storyboard_apoio';
$paginas = [
    [
        'titulo' => 'Início e alunos vinculados',
        'imagem' => '01-inicio.png',
        'marcadores' => [[1, 510, 126], [2, 655, 409], [3, 1036, 396], [4, 211, 476], [5, 830, 500]],
        'itens' => [
            ['Resumo do acompanhamento', 'Os cartões do topo mostram os totais disponíveis para o perfil Apoio.'],
            ['Evolução por quesito', 'Resume a pontuação das avaliações registradas para o aluno acompanhado.'],
            ['Aluno vinculado', 'Abra o cartão para acessar o perfil do estudante associado à sua conta.'],
            ['Navegação', 'Use Início, Perfil do Aluno, PEI, PAEE, Avaliação e Relatórios conforme a tarefa.'],
            ['Guia do usuário', 'Abra o passo a passo ilustrado do perfil Apoio em uma nova aba.']
        ],
        'nota' => 'O acesso do Apoio é restrito aos alunos vinculados à própria conta. Os nomes e registros exibidos neste guia são fictícios.'
    ],
    [
        'titulo' => 'Perfil do aluno e quesitos',
        'imagem' => '02-perfil-aluno-quesitos.png',
        'marcadores' => [[1, 531, 90], [2, 1033, 120], [3, 1033, 162], [4, 1035, 90]],
        'itens' => [
            ['Identificação', 'Confira nome, matrícula, turma, curso e demais dados do aluno acompanhado.'],
            ['Abas do perfil', 'Alterne entre Quesitos do aluno, PEI, PAEE e Laudo para consultar as informações disponíveis.'],
            ['Quesitos associados', 'Leia as habilidades e orientações definidas para o estudante. No perfil Apoio, a consulta é somente leitura.'],
            ['Laudo', 'A aba reúne os documentos disponibilizados pela escola; consulte apenas os arquivos existentes.']
        ],
        'nota' => 'Se o estudante esperado não aparecer, confirme com a equipe responsável se o vínculo foi cadastrado corretamente.'
    ],
    [
        'titulo' => 'Consultar o PEI',
        'imagem' => '03-pei.png',
        'marcadores' => [[1, 790, 79], [2, 1022, 204], [3, 1008, 275], [4, 1008, 371]],
        'itens' => [
            ['Aba PEI', 'Selecione esta aba no perfil para consultar o Plano Educacional Individualizado.'],
            ['Registro e responsável', 'Confira o título, a data e a equipe que registrou o plano.'],
            ['Objetivos e habilidades', 'Use os objetivos pedagógicos e as habilidades a desenvolver como referência no acompanhamento.'],
            ['Estratégias', 'Consulte as estratégias descritas e role o painel para ler o restante do plano.']
        ],
        'nota' => 'O Apoio consulta o PEI, mas não altera o plano por esta tela. Alinhe dúvidas ou necessidade de atualização com a equipe responsável.'
    ],
    [
        'titulo' => 'Consultar o PAEE',
        'imagem' => '04-paee.png',
        'marcadores' => [[1, 870, 79], [2, 1022, 204], [3, 1008, 275], [4, 1008, 371]],
        'itens' => [
            ['Aba PAEE', 'Selecione a aba para consultar o Plano de Atendimento Educacional Especializado.'],
            ['Registro e responsável', 'Confira a data e o profissional que registrou o atendimento.'],
            ['Objetivos e estratégias', 'Leia as metas e estratégias para apoiar a autonomia, a comunicação e a participação.'],
            ['Recursos e observações', 'Role o painel para verificar recursos de acessibilidade e demais orientações.']
        ],
        'nota' => 'O plano é exibido para consulta. A edição deve ser encaminhada aos profissionais responsáveis pelo atendimento especializado.'
    ],
    [
        'titulo' => 'Avaliação diária e frequência',
        'imagem' => '05-avaliacao-frequencia.png',
        'marcadores' => [[1, 738, 32], [2, 1040, 112], [3, 1050, 265], [4, 1045, 468]],
        'itens' => [
            ['Data e períodos', 'Confira a data selecionada e os períodos Manhã e Tarde, com seus horários.'],
            ['Situação da avaliação', 'Veja se o período está avaliado. Use Editar para revisar uma avaliação já registrada.'],
            ['Frequência', 'Marque Veio ou Faltou e use Salvar frequência para registrar a presença do dia.'],
            ['Histórico', 'Consulte avaliações e frequência de outras datas; selecione uma data para abrir o registro.']
        ],
        'nota' => 'O período só pode ser registrado quando estiver disponível. Confira a data e o aluno antes de salvar alterações.'
    ],
    [
        'titulo' => 'Editar avaliação por quesito',
        'imagem' => '06-formulario-avaliacao.png',
        'marcadores' => [[1, 943, 215], [2, 943, 326], [3, 943, 439], [4, 938, 527], [5, 944, 644]],
        'itens' => [
            ['Escolha uma resposta', 'Para cada quesito, selecione Feliz, Neutro ou Triste conforme o acompanhamento observado.'],
            ['Segundo quesito', 'Avalie a habilidade apresentada usando a descrição como referência.'],
            ['Terceiro quesito', 'Responda também ao último quesito exibido no formulário.'],
            ['Observações', 'Registre fatos objetivos e relevantes sobre o apoio oferecido e a atividade realizada.'],
            ['Salvar ou cancelar', 'Salvar avaliação grava as alterações. Cancelar fecha o formulário sem salvá-las.']
        ],
        'nota' => 'Confira todas as respostas e observações antes de salvar. Nos relatórios, Feliz corresponde a Bom, Neutro a Neutro e Triste a Ruim.'
    ],
    [
        'titulo' => 'Relatório de evolução',
        'imagem' => '07-relatorio-evolucao.png',
        'marcadores' => [[1, 510, 32], [2, 512, 410], [3, 825, 109], [4, 1047, 416], [5, 1050, 519]],
        'itens' => [
            ['Filtros', 'Selecione o quesito, o período e as datas inicial e final para limitar a consulta.'],
            ['Gerar relatório', 'Abre uma versão para impressão com os filtros selecionados; escolha Salvar como PDF no navegador para exportá-la.'],
            ['Gráfico', 'Mostra a evolução das avaliações no intervalo. Feliz equivale a Bom; neutro e triste correspondem às demais legendas.'],
            ['Linhas e tendência', 'Compare os quesitos e leia a síntese da tendência calculada para o período filtrado.'],
            ['Registros', 'Confira data, período, quesito, resultado e observação na tabela detalhada.']
        ],
        'nota' => 'A captura demonstra três avaliações fictícias de um mesmo dia. Ajuste os filtros para consultar outros registros.'
    ],
    [
        'titulo' => 'Relatório de frequência',
        'imagem' => '08-relatorio-frequencia.png',
        'marcadores' => [[1, 510, 32], [2, 1050, 495], [3, 1042, 590], [4, 1042, 645]],
        'itens' => [
            ['Filtros de consulta', 'Use o intervalo de datas para localizar os registros de frequência do aluno acompanhado.'],
            ['Alternar registros', 'Selecione Frequência para exibir presença e responsável pelo registro na tabela.'],
            ['Presença', 'A tabela informa a data e se o estudante esteve presente ou faltou.'],
            ['Responsável pelo registro', 'Confira quem registrou a frequência em cada data.']
        ],
        'nota' => 'O gráfico permanece dedicado às avaliações por quesito; a tabela alterna entre avaliações e registros de frequência.'
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

$pdf = new GuiaApoioPDF($faixaInstitucional, $logoAreaDev);
$pdf->SetMargins(9, 9, 9);
$pdf->SetAutoPageBreak(false);
$pdf->SetTitle(textoManualApoio('Guia do usuário - conectePCD - Apoio'));
$pdf->SetAuthor(textoManualApoio('Sistema Salaberga'));

$pdf->AddPage('L');
$pdf->Image($logoSalaberga, 103.5, 57, 90);
$pdf->SetTextColor(21, 128, 61);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(14, 99);
$pdf->Cell(269, 5, textoManualApoio('MATERIAL DE APOIO  |  EDIÇÃO 2026'), 0, 0, 'C');
$pdf->SetTextColor(31, 41, 55);
$pdf->SetFont('Arial', 'B', 24);
$pdf->SetXY(14, 108);
$pdf->Cell(269, 11, textoManualApoio('GUIA DO USUÁRIO'), 0, 0, 'C');
$pdf->SetTextColor(21, 128, 61);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(14, 121);
$pdf->Cell(269, 8, textoManualApoio('Sistema conectePCD  |  Perfil Apoio'), 0, 0, 'C');
$pdf->SetTextColor(75, 85, 99);
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(35, 134);
$pdf->MultiCell(227, 6, textoManualApoio('Roteiro visual para consultar o aluno vinculado, acompanhar planos, registrar avaliações e frequência e analisar relatórios.'), 0, 'C');
$pdf->SetTextColor(55, 65, 81);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(14, 153);
$pdf->MultiCell(269, 5, textoManualApoio('PERFIL DO ALUNO  •  PEI  •  PAEE  •  AVALIAÇÃO  •  FREQUÊNCIA  •  RELATÓRIOS'), 0, 'C');
$pdf->SetDrawColor(21, 128, 61);
$pdf->SetLineWidth(0.7);
$pdf->Line(45, 165, 252, 165);
$pdf->SetTextColor(31, 75, 49);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(14, 171);
$pdf->Cell(269, 5, textoManualApoio('ESCOLA ESTADUAL DE EDUCAÇÃO PROFISSIONAL'), 0, 0, 'C');
$pdf->SetTextColor(107, 114, 128);
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(14, 179);
$pdf->Cell(269, 5, textoManualApoio('SALABERGA TORQUATO GOMES DE MATOS  |  PERFIL: APOIO'), 0, 0, 'C');

foreach ($paginas as $pagina) {
    $pdf->pageTitle = $pagina['titulo'];
    $pdf->AddPage();

    $imagem = $pastaImagens . '/' . $pagina['imagem'];
    if (!is_file($imagem)) {
        throw new RuntimeException('Captura não encontrada: ' . $pagina['imagem']);
    }

    $imagemX = 9;
    $imagemY = 31;
    $imagemW = 170;
    $dimensoes = getimagesize($imagem);
    if (!$dimensoes) {
        throw new RuntimeException('Não foi possível ler a captura: ' . $pagina['imagem']);
    }
    $imagemH = $imagemW * ($dimensoes[1] / $dimensoes[0]);
    $pdf->Image($imagem, $imagemX, $imagemY, $imagemW, $imagemH, 'PNG');

    foreach ($pagina['marcadores'] as [$numero, $px, $py]) {
        marcadorManualApoio($pdf, $imagemX, $imagemY, $imagemW, $imagemH, $numero, $px, $py, $dimensoes[0], $dimensoes[1]);
    }

    $xLegenda = 183;
    $larguraLegenda = 105;
    $yLegenda = 31;
    foreach ($pagina['itens'] as $indice => [$titulo, $texto]) {
        $altura = itemManualApoio($pdf, $xLegenda, $yLegenda, $larguraLegenda, $indice + 1, $titulo, $texto);
        $yLegenda += $altura + 1.6;
    }

    $yNota = max($imagemY + $imagemH + 2.5, $yLegenda + 1);
    $pdf->SetFillColor(254, 249, 235);
    $pdf->SetDrawColor(245, 223, 166);
    $pdf->Rect(9, $yNota, 279, 13, 'DF');
    $pdf->SetTextColor(92, 70, 24);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY(12, $yNota + 2);
    $pdf->MultiCell(273, 4, textoManualApoio($pagina['nota']));
}

$destino = __DIR__ . '/Guia_usuario_conectePCD_apoio.pdf';
$pdf->Output('F', $destino);
echo 'Guia gerado: ' . $destino . PHP_EOL;
