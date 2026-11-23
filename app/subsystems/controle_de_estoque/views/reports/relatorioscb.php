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
    public function relatorioDeCodigosSCB()
    {

        $consulta = "SELECT * FROM produtos WHERE barcode LIKE 'SCB_%' ORDER BY natureza, nome_produto";
        $query = $this->connect->prepare($consulta);
        $query->execute();
        $result = $query->rowCount();

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
        $corAlerta = array(220, 53, 69);      // #DC3545 - Vermelho para alertas
        $corTextoSubtil = array(100, 100, 100); // #646464 - Cinza para textos secundários

        // ===== CABEÇALHO COM FUNDO VERDE SÓLIDO =====
        // Fundo verde sólido
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
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor($corBranco[0], $corBranco[1], $corBranco[2]);
        $pdf->Cell(0, 24, utf8_decode("RELATÓRIO DE PRODUTOS SEM CÓDIGO DE BARRA"), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(40 + $logoWidth + 15, $pdf->GetY());
        $pdf->Cell(0, 25, utf8_decode("EEEP Salaberga Torquato Gomes de Matos"), 0, 1, 'L');

        // Data de geração
        $pdf->SetXY($pdf->GetPageWidth() - 200, 30);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(160, 15, utf8_decode("Gerado no dia: " . date("d/m/Y", time())), 0, 1, 'R');

        // ===== RESUMO DE DADOS EM CARDS =====
        $consultaResumo = "SELECT 
        COUNT(*) as total_produtos,
        SUM(CASE WHEN quantidade <= 5 THEN 1 ELSE 0 END) as produtos_criticos,
        COUNT(DISTINCT natureza) as total_categorias
        FROM produtos WHERE barcode LIKE 'SCB_%' ORDER BY natureza, nome_produto";
        $queryResumo = $this->connect->prepare($consultaResumo);
        $queryResumo->execute();
        $resumo = $queryResumo->fetch(PDO::FETCH_ASSOC);

        // Criar cards para os resumos
        $cardWidth = 200;
        $cardHeight = 80;
        $cardMargin = 20;
        $startX = ($pdf->GetPageWidth() - (3 * $cardWidth + 2 * $cardMargin)) / 2;
        $startY = 110;



        // ===== TABELA DE PRODUTOS COM MELHOR DESIGN =====
        $margemTabela = 40;
        $larguraDisponivel = $pdf->GetPageWidth() - (2 * $margemTabela);

        /// Definindo colunas e larguras proporcionais
        $colunas = array('ID', 'Código', 'Produto', 'Quant.');
        $larguras = array(
            round($larguraDisponivel * 0.08), // ID
            round($larguraDisponivel * 0.20), // Código
            round($larguraDisponivel * 0.52), // Produto
            round($larguraDisponivel * 0.20)  // Quantidade
        );

        $pdf->SetXY($margemTabela, $startY + $cardHeight + 40);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor($corPrimary[0], $corPrimary[1], $corPrimary[2]);
        $pdf->SetTextColor($corBranco[0], $corBranco[1], $corBranco[2]);
        $pdf->SetDrawColor(220, 220, 220);

        // Cabeçalho da tabela com arredondamento personalizado
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
        $categoriaAtual = '';
        $linhaAlternada = false;
        $alturaLinhaDados = 24;

        if ($result > 0) {
            foreach ($query as $idx => $row) {
                // Cabeçalho de categoria
                if ($categoriaAtual != $row['natureza']) {
                    $categoriaAtual = $row['natureza'];

                    // Verificar se é necessário adicionar nova página
                    if ($y + 40 > $pdf->GetPageHeight() - 60) {
                        $pdf->AddPage();
                        $pdf->SetDrawColor($corSecondary[0], $corSecondary[1], $corSecondary[2]);
                        $pdf->SetLineWidth(2);
                        $pdf->Line(40, 40, 240, 40);
                        $pdf->SetLineWidth(0.5);
                        $y = 50;
                    } else {
                        $y += 10;
                    }

                    $pdf->SetXY($margemTabela, $y);
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->SetTextColor($corBranco[0], $corBranco[1], $corBranco[2]);
                    $pdf->SetFillColor($corSecondary[0], $corSecondary[1], $corSecondary[2]);

                    // Cabeçalho de categoria com cantos arredondados
                    $pdf->RoundedRect($margemTabela, $y, array_sum($larguras), 26, 5, 'FD');
                    $pdf->SetXY($margemTabela + 10, $y);
                    $pdf->Cell(array_sum($larguras) - 20, 26, utf8_decode(strtoupper($categoriaAtual)), 0, 1, 'L');

                    $y = $pdf->GetY();
                    $linhaAlternada = false;
                }

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
                    $pdf->SetFont('Arial', 'B', 11);
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

                    $pdf->Ln($alturaLinha);
                    $y = $pdf->GetY();

                    // Redesenhar cabeçalho de categoria
                    $pdf->SetXY($margemTabela, $y);
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->SetTextColor($corBranco[0], $corBranco[1], $corBranco[2]);
                    $pdf->SetFillColor($corSecondary[0], $corSecondary[1], $corSecondary[2]);

                    $pdf->RoundedRect($margemTabela, $y, array_sum($larguras), 26, 5, 'FD');
                    $pdf->SetXY($margemTabela + 10, $y);
                    $pdf->Cell(array_sum($larguras) - 20, 26, utf8_decode(strtoupper($categoriaAtual)), 0, 1, 'L');

                    $y = $pdf->GetY();

                    // Restaurar cor de fundo para a linha
                    if ($linhaAlternada) {
                        $pdf->SetFillColor($corCinzaClaro[0], $corCinzaClaro[1], $corCinzaClaro[2]);
                    } else {
                        $pdf->SetFillColor($corBranco[0], $corBranco[1], $corBranco[2]);
                    }
                }

                // Configurar texto
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);

                // Desenhar linha de dados
                $posX = $margemTabela;
                $estoqueCritico = $row['quantidade'] <= 5;

                // ID
                $pdf->Rect($posX, $y, $larguras[0], $alturaLinhaDados, 'FD');
                $pdf->SetXY($posX, $y);
                $pdf->Cell($larguras[0], $alturaLinhaDados, $row['id'], 0, 0, 'C');
                $posX += $larguras[0];

                // Barcode
                $pdf->Rect($posX, $y, $larguras[1], $alturaLinhaDados, 'FD');
                $pdf->SetXY($posX + 5, $y);
                $pdf->Cell($larguras[1] - 10, $alturaLinhaDados, $row['barcode'], 0, 0, 'L');
                $posX += $larguras[1];

                // Nome do produto
                $pdf->Rect($posX, $y, $larguras[2], $alturaLinhaDados, 'FD');
                $pdf->SetXY($posX + 5, $y);
                $pdf->Cell($larguras[2] - 10, $alturaLinhaDados, utf8_decode($row['nome_produto']), 0, 0, 'L');
                $posX += $larguras[2];

                // Quantidade
                $pdf->Rect($posX, $y, $larguras[3], $alturaLinhaDados, 'FD');
                $pdf->SetXY($posX, $y);
                if ($estoqueCritico) {
                    $pdf->SetTextColor($corAlerta[0], $corAlerta[1], $corAlerta[2]);
                    $pdf->SetFont('Arial', 'B', 10);
                }
                $pdf->Cell($larguras[3], $alturaLinhaDados, $row['quantidade'], 0, 0, 'C');
                $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);
                $pdf->SetFont('Arial', '', 10);
                $posX += $larguras[2];


                $y += $alturaLinhaDados;
                $linhaAlternada = !$linhaAlternada;

                // Verificar se é o último item
                if ($idx == $result - 1) {
                    // Adicionar cantos arredondados na última linha da tabela
                    $pdf->SetDrawColor(220, 220, 220);
                    $pdf->RoundedRect($margemTabela, $y - $alturaLinhaDados, $larguras[0], $alturaLinhaDados, 5, 'D', '4');
                    $pdf->RoundedRect($posX, $y - $alturaLinhaDados, $larguras[3], $alturaLinhaDados, 5, 'D', '3');

                    // ===== RODAPÉ PROFISSIONAL =====
                    $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);
                    $pdf->SetFont('Arial', '', 10);

                    $pdf->SetXY(40, $y + 15);
                    $pdf->Cell(0, 10, utf8_decode("SCB = SEM CÓDIGO DE BARRA"), 0, 0, 'L');

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
            $pdf->Cell(array_sum($larguras), 16, utf8_decode(""), 0, 1, 'C');

            // ===== RODAPÉ PROFISSIONAL =====
            $pdf->SetTextColor($corPreto[0], $corPreto[1], $corPreto[2]);
            $pdf->SetFont('Arial', '', 10);

            $pdf->SetXY(40, $y + 55);
            $pdf->Cell(0, 10, utf8_decode("SCB = SEM CÓDIGO DE BARRA"), 0, 0, 'L');



            $pdf->SetX(-60);
            $pdf->Cell(30, 10, utf8_decode('Página ' . $pdf->PageNo()), 0, 0, 'R');
        }

        // Saída do PDF
        $pdf->Output("relatorio_produtos_sem_codigo.pdf", "I");
    }
}
$x = new relatorios();
$x->relatorioDeCodigosSCB();
