<?php

require_once __DIR__ . '/../assets/lib/fpdf/fpdf.php';

function textoPdf(string $texto): string
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
}

function marcador(FPDF $pdf, float $imagemX, float $imagemY, float $imagemW, float $imagemH, int $numero, float $px, float $py, int $capturaAltura): void
{
    $lado = 3.5;
    $x = $imagemX + ($px / 1280) * $imagemW;
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
    public function Header(): void
    {
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
        'imagem' => '01-dashboard.png',
        'marcadores' => [[1, 260, 171], [2, 652, 171], [3, 260, 307], [4, 652, 307], [5, 260, 443], [6, 652, 443], [7, 1097, 13]],
        'itens' => [
            ['Registrar atraso', 'Abre o formulário para registrar a entrada atrasada de um aluno.'],
            ['Saída antecipada', 'Abre o registro de saída antes do horário regular, com responsável e justificativa.'],
            ['Saída para estágio', 'Registra a saída do aluno para estágio e permite consultar as saídas do dia.'],
            ['Atrasos registrados', 'Acompanha os atrasos do dia, consulta o histórico por turma e permite as ações disponíveis para cada registro.'],
            ['Ausências registradas', 'Consulta os alunos sem saída por QR Code registrada até o horário definido para a apuração.'],
            ['Últimas saídas', 'Mostra a frequência de saídas do dia organizada por turma.'],
            ['Tema e sessão', 'O controle de tema alterna a aparência. “Sair” encerra a sessão; os dois ficam agrupados na barra superior.']
        ],
        'nota' => 'Escolha um dos seis cards para abrir a operação correspondente. O quadrado verde no canto de cada card repete o número da explicação ao lado.'
    ],
    [
        'titulo' => 'Registrar um atraso',
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
        'titulo' => 'Registrar uma saída antecipada',
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
        'titulo' => 'Atrasos do dia e histórico',
        'imagem' => '05-atrasos-registrados.png',
        'marcadores' => [[1, 245, 274], [1, 539, 274], [1, 833, 274], [1, 1127, 274], [2, 232, 311], [2, 526, 311], [2, 820, 311], [2, 1114, 311], [3, 540, 741], [4, 850, 221]],
        'itens' => [
            ['Turmas e contadores', 'Cada painel representa uma turma. O contador mostra os atrasos do dia; os registros e suas ações aparecem quando existem lançamentos.'],
            ['Buscar aluno', 'Use o campo no topo de cada turma para localizar um estudante na lista do dia.'],
            ['Histórico', 'Role a página até os cartões de histórico. Filtre por aluno, situação e data; “Limpar” remove os filtros. Repete-se para cada turma.'],
            ['Leitor e horário', 'O aviso informa a faixa horária considerada atraso e o estado do leitor QR. O leitor físico pode continuar disponível mesmo se a câmera do navegador estiver indisponível.']
        ],
        'nota' => 'A validação de atrasos fica disponível até as 17h do mesmo dia. Depois da validação, “Impressão” reúne o comprovante da ocorrência e a autorização de entrada. Role a página para acessar todo o histórico.'
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

$pdf = new GuiaRecepcaoPDF('L', 'mm', 'A4');
$pdf->SetMargins(9, 9, 9);
$pdf->SetAutoPageBreak(false);
$pdf->SetTitle(textoPdf('Guia visual - Entrada e Saída - Recepção'));
$pdf->SetAuthor(textoPdf('Sistema Salaberga'));

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
    $imagemH = $imagemW * ($dimensoes[1] / $dimensoes[0]);
    $pdf->Image($imagem, $imagemX, $imagemY, $imagemW, $imagemH, 'PNG');

    foreach ($pagina['marcadores'] as [$numero, $px, $py]) {
        marcador($pdf, $imagemX, $imagemY, $imagemW, $imagemH, $numero, $px, $py, $dimensoes[1]);
    }

    $xLegenda = 183;
    $larguraLegenda = 105;
    $yLegenda = 31;
    foreach ($pagina['itens'] as $indice => [$titulo, $texto]) {
        $altura = itemLegenda($pdf, $xLegenda, $yLegenda, $larguraLegenda, $indice + 1, $titulo, $texto);
        $yLegenda += $altura + 1.6;
    }

    $yNota = max($imagemY + $imagemH + 2.5, $yLegenda + 1);
    $pdf->SetFillColor(254, 249, 235);
    $pdf->SetDrawColor(245, 223, 166);
    $pdf->Rect(9, $yNota, 279, 13, 'DF');
    $pdf->SetTextColor(92, 70, 24);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY(12, $yNota + 2.2);
    $pdf->MultiCell(273, 4, textoPdf($pagina['nota']));
}

$destino = __DIR__ . '/Guia_de_instrucoes_entradasaida_recepcao.pdf';
$pdf->Output('F', $destino);
echo 'Guia gerado: ' . $destino . PHP_EOL;
