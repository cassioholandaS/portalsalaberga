<?php
require_once(__DIR__ . '/../../assets/fpdf/fpdf.php');
require_once(__DIR__ . '/../../config/connect.php');
require_once __DIR__ . '/_layout_pdf.php';
class PDF extends connect
{
    function __construct()
    {
        parent::__construct();
        $this->main();
    }

    function main()
    {
        $pdf = new FPDF("L", "pt", "A4");
        $pdf->AliasNbPages();
        $pdf->AddPage();
        aplicarCabecalhoRelatorioBiblioteca($pdf, 'RELATÓRIO DE EMPRÉSTIMOS');

        // Consulta os dados dos empréstimos
        $sql = $this->connect_biblioteca->prepare("
            SELECT e.id, e.data_emprestimo, e.data_devolucao_estipulada,
                   a.nome AS nome_aluno,
                   CONCAT(COALESCE(t.serie_turma, ''), CASE WHEN t.serie_turma <> '' THEN 'º ' ELSE '' END, COALESCE(t.nome_turma, '')) AS turma_aluno,
                   c.titulo_livro
            FROM emprestimo e
            INNER JOIN " . $this->connect_alunos . ".alunos a ON e.id_aluno = a.id_aluno
            INNER JOIN " . $this->connect_alunos . ".turmas t ON a.id_turma = t.id_turma
            INNER JOIN catalogo c ON e.id_catalogo = c.id
            ORDER BY e.data_emprestimo DESC
        ");
        $sql->execute();
        $emprestimos = $sql->fetchAll(PDO::FETCH_ASSOC);

        $pageWidth = $pdf->GetPageWidth() - 40;
        $colunas = array(
            array('largura' => $pageWidth * 0.20, 'texto' => 'ALUNO'),
            array('largura' => $pageWidth * 0.20, 'texto' => 'TURMA'),
            array('largura' => $pageWidth * 0.30, 'texto' => 'TÍTULO DO LIVRO'),
            array('largura' => $pageWidth * 0.15, 'texto' => 'EMPRÉSTIMO'),
            array('largura' => $pageWidth * 0.15, 'texto' => 'DEVOLUÇÃO')
        );

        $pdf->SetX(20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(0, 122, 51);
        $pdf->SetTextColor(255, 255, 255);

        foreach ($colunas as $coluna) {
            $pdf->Cell($coluna['largura'], 20, utf8_decode($coluna['texto']), 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $linha = 0;

        foreach ($emprestimos as $e) {
            $cor = $linha++ % 2 == 0 ? 255 : 240;
            $pdf->SetFillColor($cor, $cor, $cor);
            $pdf->SetX(20);

            $pdf->Cell($colunas[0]['largura'], 20, utf8_decode(mb_strtoupper($e['nome_aluno'], 'UTF-8')), 1, 0, 'L', true);
            $pdf->Cell($colunas[1]['largura'], 20, utf8_decode(mb_strtoupper($e['turma_aluno'], 'UTF-8')), 1, 0, 'L', true);
            $pdf->Cell($colunas[2]['largura'], 20, utf8_decode(mb_strtoupper($e['titulo_livro'], 'UTF-8')), 1, 0, 'L', true);
            $pdf->Cell($colunas[3]['largura'], 20, date('d/m/Y', strtotime($e['data_emprestimo'])), 1, 0, 'C', true);
            $pdf->Cell($colunas[4]['largura'], 20, date('d/m/Y', strtotime($e['data_devolucao_estipulada'])), 1, 1, 'C', true);
        }

        $this->Footer($pdf);
    }

    function Footer($pdf)
    {
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(0, 122, 51);
        $pdf->Cell(0, 10, utf8_decode('Página ') . $pdf->PageNo() . '/{nb}', 0, 0, 'C');
        $pdf->Output('relatorio_emprestimos.pdf', 'I');
    }
}
$pdf = new PDF();
