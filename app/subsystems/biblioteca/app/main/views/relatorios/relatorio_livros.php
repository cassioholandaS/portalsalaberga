<?php
require_once(__DIR__ . '/../../assets/fpdf/fpdf.php');
require_once(__DIR__ . '/../../config/connect.php');
require_once __DIR__ . '/_layout_pdf.php';
class PDF extends connect
{
    public function __construct()
    {
        parent::__construct();
        $this->main();
    }
    function  main()
    {
        $pdf = new FPDF("L", "pt", "A4");
        $pdf->AliasNbPages();
        $pdf->AddPage();
        aplicarCabecalhoRelatorioBiblioteca($pdf, 'RELATÓRIO DE LIVROS EM ALTA');
        $livrosEmAlta = $this->connect_biblioteca->prepare("SELECT 
        c.id,
        c.titulo_livro,
        a.nome_autor,
        c.edicao,
        g.generos,
        COUNT(e.id) as total_emprestimos
        FROM catalogo c
        INNER JOIN livros_autores l ON c.id = l.id_livro
        INNER JOIN autores a ON l.id_autor = a.id
        LEFT JOIN genero g ON c.id_genero = g.id
        LEFT JOIN emprestimo e ON c.id = e.id_catalogo
        GROUP BY c.id, c.titulo_livro, a.nome_autor, c.edicao, g.generos
        ORDER BY total_emprestimos DESC
        LIMIT 10");
        $livrosEmAlta->execute();
        $result = $livrosEmAlta->fetchAll(PDO::FETCH_ASSOC);

        $pageWidth = $pdf->GetPageWidth() - 40;
        $colunas = array(
            array('largura' => $pageWidth * 0.20, 'texto' => utf8_decode('GÊNERO')),
            array('largura' => $pageWidth * 0.40, 'texto' => utf8_decode('TÍTULO')),
            array('largura' => $pageWidth * 0.25, 'texto' => 'AUTOR'),
            array('largura' => $pageWidth * 0.15, 'texto' => utf8_decode('EDIÇÃO'))
        );

        $pdf->SetX(20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(0, 122, 51);
        $pdf->SetTextColor(255, 255, 255);

        foreach ($colunas as $coluna) {
            $pdf->Cell($coluna['largura'], 20, $coluna['texto'], 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        foreach ($result as $i => $livro) {
            $pdf->SetX(20);
            $cor = $livro['id'] % 2 == 0 ? 255 : 240;
            $pdf->SetFillColor($cor, $cor, $cor);
            $pdf->SetTextColor(0, 0, 0);

            $genero = utf8_decode(mb_strtoupper($livro['generos'] ?? 'N/A', 'UTF-8'));
            $titulo = utf8_decode(mb_strtoupper($livro['titulo_livro'], 'UTF-8'));
            $autor = utf8_decode(mb_strtoupper($livro['nome_autor'] ?? 'N/A', 'UTF-8'));
            $edicao = empty($livro['edicao']) ? utf8_decode('ENI*') : utf8_decode(mb_strtoupper($livro['edicao'], 'UTF-8'));

            $pdf->Cell($colunas[0]['largura'], 20, $genero, 1, 0, 'L', true);
            $pdf->Cell($colunas[1]['largura'], 20, $titulo, 1, 0, 'L', true);
            $pdf->Cell($colunas[2]['largura'], 20, $autor, 1, 0, 'L', true);
            $pdf->Cell($colunas[3]['largura'], 20, $edicao, 1, 1, 'C', true);
        }

        $pdf->Ln(20);
        $pdf->SetX(20);
        $pdf->SetTextColor(0, 122, 51);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($pageWidth / 2, 10, 'TOTAL DE LIVROS EM ALTA: ' . count($result), 0, 0, 'L');
        
        $this->Footer($pdf);
    }

    function Footer($pdf)
    {
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(0, 122, 51);
        $pdf->Cell(0, 10, utf8_decode('Página ') . $pdf->PageNo() . '/{nb}', 0, 0, 'C');
        $pdf->Output('relatorio_livros_em_alta.pdf', 'I');
    }
}
$pdf = new PDF();
