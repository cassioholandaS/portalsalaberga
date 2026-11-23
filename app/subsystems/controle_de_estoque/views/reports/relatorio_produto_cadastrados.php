<?php
require_once(__DIR__ . '/../../models/sessions.php');
$session = new sessions();
$session->autenticar_session();
$session->tempo_session();

require_once(__DIR__ . '/../../config/connect.php');
require_once(__DIR__ . '/../../assets/libs/FPDF/fpdf.php');

class relatorios extends connect
{

    function __construct()
    {
        parent::__construct();
        $this->relatoriocriticostoque();
    }
    public function exportarRelatorioProdutosPorData($data_inicio, $data_fim)
    {
        try {
            // Buscar produtos cadastrados no período
            $produtos = $this->buscarProdutosPorData($data_inicio, $data_fim);

            // Criar PDF personalizado
            $pdf = new PDF("L", "pt", "A4");
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(true, 60);

            // Paleta de cores consistente com o sistema
            $corPrimary = array(0, 90, 36);       // #005A24 - Verde principal
            $corDark = array(26, 60, 52);         // #1A3C34 - Verde escuro
            $corSecondary = array(255, 165, 0);   // #FFA500 - Laranja para destaques
            $corCinzaClaro = array(248, 250, 249); // #F8FAF9 - Fundo alternado
            $corBranco = array(255, 255, 255);    // #FFFFFF - Branco
            $corPreto = array(40, 40, 40);        // #282828 - Quase preto para texto
            $corTextoSubtil = array(100, 100, 100); // #646464 - Cinza para textos secundários

            // ===== CABEÇALHO COM FUNDO VERDE SÓLIDO =====
            $pdf->SetFillColor($corPrimary[0], $corPrimary[1], $corPrimary[2]);
            $pdf->Rect(0, 0, $pdf->GetPageWidth(), 95, 'F');

            // Logo
            $logoPath = "../assets/imagens/logostgm.png";
            $logoWidth = 60;
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 40, 20, $logoWidth);
                $pdf->SetXY(40 + $logoWidth + 15, 30);
            } else {
                $pdf->SetXY(40, 30);
            }

            // Título e subtítulo
            $pdf->SetFont('Arial', 'B', 24);
            $pdf->SetTextColor($corBranco[0], $corBranco[1], $corBranco[2]);
            $pdf->Cell(0, 24, utf8_decode("RELATÓRIO DE PRODUTOS CADASTRADOS"), 0, 1, 'L');

            $pdf->SetFont('Arial', '', 12);
            $pdf->SetXY(40 + $logoWidth + 15, $pdf->GetY());
            $pdf->Cell(0, 15, utf8_decode("EEEP Salaberga Torquato Gomes de Matos"), 0, 1, 'L');

            // Data de geração
            $pdf->SetXY($pdf->GetPageWidth() - 200, 30);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(160, 15, utf8_decode("Gerado no dia: " . date("d/m/Y", time())), 0, 1, 'R');

            // ===== RESUMO DE DADOS EM CARDS =====
            $totalProdutos = count($produtos);
            $categorias = array_unique(array_column($produtos, 'natureza'));
            $totalCategorias = count($categorias);

            // Criar cards para os resumos
            $cardWidth = 200;
            $cardHeight = 80;
            $cardMargin = 20;
            $startX = ($pdf->GetPageWidth() - (3 * $cardWidth + 2 * $cardMargin)) / 2;
            $startY = 110;

            // Card 1 - Total Produtos
            $pdf->SetFillColor($corBranco[0], $corBranco[1], $corBranco[2]);
            $pdf->RoundedRect($startX, $startY, $cardWidth, $cardHeight, 8, 'F');

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);
            $pdf->SetXY($startX + 15, $startY + 15);
            $pdf->Cell($cardWidth - 30, 20, utf8_decode("TOTAL DE PRODUTOS"), 0, 1, 'L');

            $pdf->SetFont('Arial', 'B', 24);
            $pdf->SetTextColor($corPrimary[0], $corPrimary[1], $corPrimary[2]);
            $pdf->SetXY($startX + 15, $startY + 40);
            $pdf->Cell($cardWidth - 30, 25, $totalProdutos, 0, 1, 'L');

            // Card 2 - Categorias
            $pdf->SetFillColor($corBranco[0], $corBranco[1], $corBranco[2]);
            $pdf->RoundedRect($startX + $cardWidth + $cardMargin, $startY, $cardWidth, $cardHeight, 8, 'F');

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);
            $pdf->SetXY($startX + $cardWidth + $cardMargin + 15, $startY + 15);
            $pdf->Cell($cardWidth - 30, 20, utf8_decode("CATEGORIAS"), 0, 1, 'L');

            $pdf->SetFont('Arial', 'B', 24);
            $pdf->SetTextColor($corSecondary[0], $corSecondary[1], $corSecondary[2]);
            $pdf->SetXY($startX + $cardWidth + $cardMargin + 15, $startY + 40);
            $pdf->Cell($cardWidth - 30, 25, $totalCategorias, 0, 1, 'L');

            // Card 3 - Período
            $pdf->SetFillColor($corBranco[0], $corBranco[1], $corBranco[2]);
            $pdf->RoundedRect($startX + 2 * ($cardWidth + $cardMargin), $startY, $cardWidth, $cardHeight, 8, 'F');

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);
            $pdf->SetXY($startX + 2 * ($cardWidth + $cardMargin) + 15, $startY + 15);
            $pdf->Cell($cardWidth - 30, 20, utf8_decode("PERÍODO"), 0, 1, 'L');

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($corTextoSubtil[0], $corTextoSubtil[1], $corTextoSubtil[2]);
            $pdf->SetXY($startX + 2 * ($cardWidth + $cardMargin) + 15, $startY + 35);
            $pdf->Cell($cardWidth - 30, 15, date('d/m/Y', strtotime($data_inicio)), 0, 1, 'L');
            $pdf->SetXY($startX + 2 * ($cardWidth + $cardMargin) + 15, $startY + 50);
            $pdf->Cell($cardWidth - 30, 15, date('d/m/Y', strtotime($data_fim)), 0, 1, 'L');

            // ===== TÍTULO DA TABELA =====
            $pdf->SetXY(40, $startY + $cardHeight + 30);
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->SetTextColor($corPrimary[0], $corPrimary[1], $corPrimary[2]);
            $pdf->Cell(0, 20, utf8_decode("DETALHAMENTO DOS PRODUTOS CADASTRADOS"), 0, 1, 'L');

            // ===== TABELA DE PRODUTOS =====
            $margemTabela = 40;
            $larguraDisponivel = $pdf->GetPageWidth() - (2 * $margemTabela);

            // Definindo colunas e larguras proporcionais
            $colunas = array('ID', 'Código', 'Produto', 'Quant.', 'Categoria', 'Data Cadastro');
            $larguras = array(
                round($larguraDisponivel * 0.05), // 5% para ID
                round($larguraDisponivel * 0.15), // 15% para Código
                round($larguraDisponivel * 0.35), // 35% para Produto
                round($larguraDisponivel * 0.10), // 10% para Quantidade
                round($larguraDisponivel * 0.15), // 15% para Categoria
                round($larguraDisponivel * 0.20)  // 20% para Data Cadastro
            );

            $pdf->SetXY($margemTabela, $pdf->GetY() + 10);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetFillColor($corPrimary[0], $corPrimary[1], $corPrimary[2]);
            $pdf->SetTextColor($corBranco[0], $corBranco[1], $corBranco[2]);
            $pdf->SetDrawColor(220, 220, 220);

            // Cabeçalho da tabela
            $alturaLinha = 30;
            $posX = $margemTabela;

            // Célula de cabeçalho com primeiro canto arredondado (esquerda superior)
            $pdf->RoundedRect($posX, $pdf->GetY(), $larguras[0], $alturaLinha, 5, 'FD', '1');
            $pdf->SetXY($posX, $pdf->GetY());
            $pdf->Cell($larguras[0], $alturaLinha, utf8_decode($colunas[0]), 0, 0, 'C');
            $posX += $larguras[0];

            // Células de cabeçalho intermediárias
            for ($i = 1; $i < count($colunas) - 1; $i++) {
                $pdf->Rect($posX, $pdf->GetY(), $larguras[$i], $alturaLinha, 'FD');
                $pdf->SetXY($posX, $pdf->GetY());
                $pdf->Cell($larguras[$i], $alturaLinha, utf8_decode($colunas[$i]), 0, 0, 'C');
                $posX += $larguras[$i];
            }

            // Última célula com canto arredondado (direita superior)
            $pdf->RoundedRect($posX, $pdf->GetY(), $larguras[count($colunas) - 1], $alturaLinha, 5, 'FD', '2');
            $pdf->SetXY($posX, $pdf->GetY());
            $pdf->Cell($larguras[count($colunas) - 1], $alturaLinha, utf8_decode($colunas[count($colunas) - 1]), 0, 0, 'C');

            $pdf->Ln($alturaLinha);

            // Dados da tabela
            $y = $pdf->GetY();
            $linhaAlternada = false;
            $alturaLinhaDados = 24;

            if (count($produtos) > 0) {
                foreach ($produtos as $idx => $produto) {
                    // Cor de fundo alternada para linhas
                    if ($linhaAlternada) {
                        $pdf->SetFillColor($corCinzaClaro[0], $corCinzaClaro[1], $corCinzaClaro[2]);
                    } else {
                        $pdf->SetFillColor($corBranco[0], $corBranco[1], $corBranco[2]);
                    }

                    // Verificar se é necessário adicionar nova página
                    if ($y + $alturaLinhaDados > $pdf->GetPageHeight() - 60) {
                        $pdf->AddPage();

                        // Redesenhar cabeçalho da tabela na nova página
                        $y = 40;
                        $posX = $margemTabela;
                        $pdf->SetFillColor($corPrimary[0], $corPrimary[1], $corPrimary[2]);
                        $pdf->SetTextColor($corBranco[0], $corBranco[1], $corBranco[2]);

                        // Cabeçalho da tabela
                        $pdf->RoundedRect($posX, $y, $larguras[0], $alturaLinha, 5, 'FD', '1');
                        $pdf->SetXY($posX, $y);
                        $pdf->Cell($larguras[0], $alturaLinha, utf8_decode($colunas[0]), 0, 0, 'C');
                        $posX += $larguras[0];

                        for ($i = 1; $i < count($colunas) - 1; $i++) {
                            $pdf->Rect($posX, $y, $larguras[$i], $alturaLinha, 'FD');
                            $pdf->SetXY($posX, $y);
                            $pdf->Cell($larguras[$i], $alturaLinha, utf8_decode($colunas[$i]), 0, 0, 'C');
                            $posX += $larguras[$i];
                        }

                        $pdf->RoundedRect($posX, $y, $larguras[count($colunas) - 1], $alturaLinha, 5, 'FD', '2');
                        $pdf->SetXY($posX, $y);
                        $pdf->Cell($larguras[count($colunas) - 1], $alturaLinha, utf8_decode($colunas[count($colunas) - 1]), 0, 0, 'C');

                        $y = $pdf->GetY();
                        $linhaAlternada = false;
                    }

                    $posX = $margemTabela;

                    // ID
                    $pdf->Rect($posX, $y, $larguras[0], $alturaLinhaDados, 'FD');
                    $pdf->SetXY($posX, $y);
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);
                    $pdf->Cell($larguras[0], $alturaLinhaDados, $produto['id'], 0, 0, 'C');
                    $posX += $larguras[0];

                    // Barcode
                    $pdf->Rect($posX, $y, $larguras[1], $alturaLinhaDados, 'FD');
                    $pdf->SetXY($posX + 5, $y);
                    $pdf->Cell($larguras[1] - 10, $alturaLinhaDados, $produto['barcode'], 0, 0, 'L');
                    $posX += $larguras[1];

                    // Nome do produto
                    $pdf->Rect($posX, $y, $larguras[2], $alturaLinhaDados, 'FD');
                    $pdf->SetXY($posX + 5, $y);
                    $pdf->Cell($larguras[2] - 10, $alturaLinhaDados, utf8_decode($produto['nome_produto']), 0, 0, 'L');
                    $posX += $larguras[2];

                    // Quantidade
                    $pdf->Rect($posX, $y, $larguras[3], $alturaLinhaDados, 'FD');
                    $pdf->SetXY($posX, $y);
                    $pdf->Cell($larguras[3], $alturaLinhaDados, $produto['quantidade'], 0, 0, 'C');
                    $posX += $larguras[3];

                    // Categoria
                    $pdf->Rect($posX, $y, $larguras[4], $alturaLinhaDados, 'FD');
                    $pdf->SetXY($posX + 5, $y);
                    $pdf->Cell($larguras[4] - 10, $alturaLinhaDados, utf8_decode($produto['natureza']), 0, 0, 'L');
                    $posX += $larguras[4];

                    // Data de Cadastro
                    $pdf->Rect($posX, $y, $larguras[5], $alturaLinhaDados, 'FD');
                    $pdf->SetXY($posX, $y);
                    $pdf->Cell($larguras[5], $alturaLinhaDados, date('d/m/Y H:i', strtotime($produto['data'])), 0, 0, 'C');

                    $y += $alturaLinhaDados;
                    $linhaAlternada = !$linhaAlternada;

                    // Verificar se é o último item
                    if ($idx == count($produtos) - 1) {
                        // Adicionar cantos arredondados na última linha da tabela
                        $pdf->SetDrawColor(220, 220, 220);
                        $pdf->RoundedRect($margemTabela, $y - $alturaLinhaDados, $larguras[0], $alturaLinhaDados, 5, 'D', '4');
                        $pdf->RoundedRect($posX, $y - $alturaLinhaDados, $larguras[5], $alturaLinhaDados, 5, 'D', '3');

                        // ===== RODAPÉ PROFISSIONAL =====
                        $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);
                        $pdf->SetFont('Arial', '', 10);
                        $pdf->SetXY(40, $y + 15);
                        $pdf->Cell(0, 10, utf8_decode("Sistema de Gerenciamento de Estoque - STGM v1.2.0"), 0, 0, 'L');

                        $pdf->SetXY(40, $y + 25);
                        $pdf->Cell(0, 10, utf8_decode("© " . date('Y') . " - Desenvolvido por alunos EEEP STGM"), 0, 0, 'L');

                        $pdf->SetX(-60);
                        $pdf->Cell(30, 10, utf8_decode('Página ' . $pdf->PageNo()), 0, 0, 'R');
                    }
                }
            } else {
                $pdf->SetXY($margemTabela, $y);
                $pdf->SetFont('Arial', 'I', 12);
                $pdf->SetTextColor($corTextoSubtil[0], $corTextoSubtil[1], $corTextoSubtil[2]);
                $pdf->SetFillColor(250, 250, 250);
                $pdf->RoundedRect($margemTabela, $y, array_sum($larguras), 40, 5, 'FD');
                $pdf->SetXY($margemTabela, $y + 12);
                $pdf->Cell(array_sum($larguras), 16, utf8_decode("Não existem produtos cadastrados no período selecionado"), 0, 1, 'C');

                // ===== RODAPÉ PROFISSIONAL =====
                $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);
                $pdf->SetFont('Arial', '', 10);

                $pdf->SetXY(40, $y + 15);
                $pdf->Cell(0, 10, utf8_decode("SCB = SEM CÓDIGO DE BARRA"), 0, 0, 'L');

                $pdf->SetXY(40, $y + 25);
                $pdf->Cell(0, 10, utf8_decode("Sistema de Gerenciamento de Estoque - STGM v1.2.0"), 0, 0, 'L');

                $pdf->SetXY(40, $y + 35);
                $pdf->Cell(0, 10, utf8_decode("© " . date('Y') . " - Desenvolvido por alunos EEEP STGM"), 0, 0, 'L');

                $pdf->SetX(-60);
                $pdf->Cell(30, 10, utf8_decode('Página ' . $pdf->PageNo()), 0, 0, 'R');
            }

            $pdf->Output("relatorio_produtos_cadastrados_" . date("Y-m-d") . ".pdf", "D");
        } catch (PDOException $e) {
            error_log("Erro no relatório de produtos cadastrados: " . $e->getMessage());
            echo "Erro ao gerar relatório: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("Erro geral no relatório de produtos cadastrados: " . $e->getMessage());
            echo "Erro ao gerar relatório: " . $e->getMessage();
        }
    }
}
// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['data_inicio']) && isset($_GET['data_fim'])) {
    $data_inicio = $_GET['data_inicio'];
    $data_fim = $_GET['data_fim'];

    $relatorios = new relatorios();

    // Se for uma requisição para PDF
    if (isset($_GET['pdf']) && $_GET['pdf'] == '1') {
        $relatorios->exportarRelatorioProdutosPorData($data_inicio, $data_fim);
    }
    // Se for uma requisição para JSON (visualização)
    else if (isset($_GET['format']) && $_GET['format'] == 'json') {
        header('Content-Type: application/json');

        try {
            $produtos = $relatorios->buscarProdutosPorData($data_inicio, $data_fim);

            echo json_encode([
                'success' => true,
                'produtos' => $produtos,
                'total' => count($produtos)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    // Se for uma requisição normal (visualização HTML)
    else {
        header("Location: ../view/relatorio_produtos_cadastrados.php?data_inicio=" . urlencode($data_inicio) . "&data_fim=" . urlencode($data_fim));
        exit;
    }
} else {
    echo "Erro: Parâmetros de data não fornecidos.";
    exit;
}
