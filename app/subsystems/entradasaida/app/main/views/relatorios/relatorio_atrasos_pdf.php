<?php
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../../assets/lib/fpdf/fpdf.php');
require_once(__DIR__ . '/_layout_pdf.php');
require_once(__DIR__ . '/../../model/select_model.php');

class PDFRelatorioAtrasosFiltrado extends FPDF
{
    public function Footer()
    {
        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 5, 'Pagina ' . $this->PageNo() . '/{nb} | Gerado em ' . date('d/m/Y H:i'), 0, 0, 'R');
    }
}

class RelatorioAtrasosFiltrado
{
    private $select;
    private $escopo;
    private $valor;
    private $periodo;

    public function __construct()
    {
        $this->select = new select_model();
        $this->escopo = $_GET['escopo'] ?? '';
        $this->periodo = $_GET['tipo_relatorio'] ?? '';
        $this->valor = $this->escopo === 'aluno'
            ? ($_GET['id_aluno'] ?? 0)
            : ($this->escopo === 'turma' ? ($_GET['id_turma'] ?? 0) : ($_GET['ano'] ?? 0));

        if ($this->escopo === 'geral') {
            if ($this->periodo !== '') {
                http_response_code(400);
                exit('O relatório geral não aceita filtros.');
            }
            $this->valor = null;
        } elseif (!in_array($this->escopo, ['aluno', 'ano', 'turma'], true)
            || !in_array($this->periodo, ['dia_atual', 'ultimos_30_dias', 'ultimos_12_meses'], true)) {
            http_response_code(400);
            exit('Parâmetros inválidos para o relatório de atrasos.');
        }

        $this->gerarPdf();
    }

    private function textoPdf($texto)
    {
        return mb_convert_encoding((string) $texto, 'ISO-8859-1', 'UTF-8');
    }

    private function nomePeriodo()
    {
        if ($this->escopo === 'geral') {
            return 'Todos os registros';
        }

        $nomes = [
            'dia_atual' => 'Dia atual',
            'ultimos_30_dias' => 'Últimos 30 dias',
            'ultimos_12_meses' => 'Últimos 12 meses'
        ];
        return $nomes[$this->periodo];
    }

    private function nomeEscopo()
    {
        if ($this->escopo === 'geral') {
            return 'Todos os alunos e turmas';
        }
        if ($this->escopo === 'aluno') {
            return 'Por aluno';
        }
        if ($this->escopo === 'ano') {
            return (int) $this->valor . 'º ano';
        }

        $idTurma = (int) $this->valor;
        $ano = (int) ceil($idTurma / 4);
        $letra = chr(ord('A') + (($idTurma - 1) % 4));
        return $ano . 'º Ano ' . $letra;
    }

    private function larguraColunas()
    {
        return [
            ['Data', 20],
            ['Hora', 16],
            ['Aluno', 48],
            ['Turma', 31],
            ['Justificativa', 67],
            ['Validação', 29],
            ['Observação', 62]
        ];
    }

    private function imprimirCabecalho($pdf)
    {
        aplicarCabecalhoRelatorio(
            $pdf,
            'Relatório de atrasos registrados',
            $this->nomeEscopo() . ' | ' . $this->nomePeriodo()
        );
    }

    private function imprimirCabecalhoTabela($pdf, $colunas)
    {
        $pdf->SetFillColor(240, 249, 244);
        $pdf->SetDrawColor(210, 218, 213);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->SetFont('Arial', 'B', 8);
        foreach ($colunas as $coluna) {
            $pdf->Cell($coluna[1], 8, $this->textoPdf($coluna[0]), 1, 0, 'C', true);
        }
        $pdf->Ln();
    }

    private function quebrarTexto($pdf, $texto, $largura)
    {
        $texto = $this->textoPdf($texto === '' ? '-' : $texto);
        $limite = $largura - 4;
        $linhas = [];
        $linha = '';

        foreach (preg_split('/\s+/', $texto) as $palavra) {
            $candidata = $linha === '' ? $palavra : $linha . ' ' . $palavra;
            if ($pdf->GetStringWidth($candidata) <= $limite) {
                $linha = $candidata;
                continue;
            }
            if ($linha !== '') {
                $linhas[] = $linha;
            }
            $linha = '';
            while ($pdf->GetStringWidth($palavra) > $limite) {
                $parte = '';
                foreach (str_split($palavra) as $caractere) {
                    if ($pdf->GetStringWidth($parte . $caractere) > $limite) {
                        break;
                    }
                    $parte .= $caractere;
                }
                $linhas[] = $parte;
                $palavra = substr($palavra, strlen($parte));
            }
            $linha = $palavra;
        }
        if ($linha !== '' || empty($linhas)) {
            $linhas[] = $linha;
        }
        return $linhas;
    }

    private function imprimirLinha($pdf, $dado, $colunas, $indice)
    {
        $status = [
            'pendente' => 'Pendente',
            'aprovada' => 'Válida',
            'recusada' => 'Inválida'
        ];
        $turma = strtoupper((string) ($dado['letra_turma'] ?? ''));
        $valores = [
            date('d/m/Y', strtotime($dado['date_time'])),
            date('H:i', strtotime($dado['date_time'])),
            $dado['nome'],
            $turma,
            $dado['motivo'] ?? 'Não informada',
            $status[$dado['status_justificativa'] ?? 'pendente'] ?? 'Pendente',
            $dado['observacao_validacao'] ?? '-'
        ];
        $linhasColunas = [];
        $quantidadeLinhas = 1;
        foreach ($colunas as $indiceColuna => $coluna) {
            $linhasColunas[$indiceColuna] = $this->quebrarTexto($pdf, (string) ($valores[$indiceColuna] ?? ''), $coluna[1]);
            $quantidadeLinhas = max($quantidadeLinhas, count($linhasColunas[$indiceColuna]));
        }
        $alturaTexto = 4.2;
        $altura = $quantidadeLinhas * $alturaTexto + 2;

        if ($pdf->GetY() + $altura > $pdf->GetPageHeight() - 16) {
            $pdf->AddPage();
            $this->imprimirCabecalho($pdf);
            $this->imprimirCabecalhoTabela($pdf, $colunas);
        }

        $xInicial = $pdf->GetX();
        $y = $pdf->GetY();
        $x = $xInicial;
        foreach ($colunas as $indiceColuna => $coluna) {
            $tom = $indice % 2 === 0 ? 255 : 245;
            $pdf->SetFillColor($tom, $tom, $tom);
            $pdf->SetDrawColor(225, 231, 227);
            $pdf->Rect($x, $y, $coluna[1], $altura, 'DF');
            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(55, 65, 81);
            $pdf->SetXY($x + 2, $y + 1);
            $pdf->MultiCell($coluna[1] - 4, $alturaTexto, implode("\n", $linhasColunas[$indiceColuna]), 0, 'L');
            $x += $coluna[1];
        }
        $pdf->SetXY($xInicial, $y + $altura);
    }

    private function gerarPdf()
    {
        $dados = $this->select->atrasosParaRelatorio($this->escopo, $this->valor, $this->periodo);
        if ($this->escopo === 'aluno' && !empty($dados)) {
            $this->valor = $dados[0]['id_aluno'];
        }

        $pdf = new PDFRelatorioAtrasosFiltrado('L', 'mm', 'A4');
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $this->imprimirCabecalho($pdf);
        $colunas = $this->larguraColunas();
        $this->imprimirCabecalhoTabela($pdf, $colunas);

        if (empty($dados)) {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(145, 145, 145);
            $mensagemVazia = $this->escopo === 'geral'
                ? 'Nenhum atraso registrado no sistema.'
                : 'Nenhum atraso encontrado para os filtros selecionados.';
            $pdf->Cell(0, 12, $this->textoPdf($mensagemVazia), 0, 1, 'C');
        } else {
            foreach ($dados as $indice => $dado) {
                $this->imprimirLinha($pdf, $dado, $colunas, $indice);
            }
        }

        $pdf->Output('I', 'Relatorio de atrasos.pdf');
    }
}

new RelatorioAtrasosFiltrado();
