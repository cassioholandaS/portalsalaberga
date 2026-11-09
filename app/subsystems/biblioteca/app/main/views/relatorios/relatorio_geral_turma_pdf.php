<?php
require_once(__DIR__ . '/../../models/select_model.php');
require_once(__DIR__ . '/../../assets/fpdf/fpdf.php');
require_once(__DIR__ . '/../../config/connect.php');
require_once __DIR__ . '/_layout_pdf.php';

class Relatorio_geral_turma_pdf extends connect
{

    function __construct()
    {
        parent::__construct();
        $this->header();
        $this->main();
        $this->footer();
    }
    public function header() {}
    public function main()
    {
        try {
            $id_turma = isset($_GET['turma']) ? $_GET['turma'] : null;
            $mes = isset($_GET['mes']) ? $_GET['mes'] : date('n');

            if (!$id_turma) {
                throw new Exception("ID da turma não fornecido");
            }

            $select_model = new select_model();
            $nome_turma = $select_model->get_turma_nome($id_turma);

            if (!$nome_turma) {
                throw new Exception("Turma não encontrada");
            }

            $aluno_destaque = $select_model->get_aluno_destaque($id_turma, $mes);
            if (empty($aluno_destaque)) {
                $aluno_destaque = 'Nenhum aluno encontrado para este mês';
            }

            $stmt_livros_por_aluno = $this->connect_biblioteca->prepare("
                SELECT a.nome AS student_name, COUNT(e.id) AS book_count
                FROM emprestimo e
                JOIN " . $this->connect_alunos . ".alunos a ON e.id_aluno = a.id_aluno
                WHERE a.id_turma = :id_turma
                  AND MONTH(e.data_emprestimo) = :mes
                  AND YEAR(e.data_emprestimo) = YEAR(CURDATE())
                GROUP BY a.id_aluno, a.nome
                ORDER BY book_count DESC
            ");
            $stmt_livros_por_aluno->execute(['id_turma' => $id_turma, 'mes' => $mes]);
            $livros_por_aluno = $stmt_livros_por_aluno->fetchAll(PDO::FETCH_ASSOC);

            // Criar o gráfico usando GD Library
            if (!empty($livros_por_aluno) && function_exists('imagecreatetruecolor')) {
                $width = 600; // Largura do gráfico
                $height = 400; // Altura do gráfico
                $image = imagecreatetruecolor($width, $height);

                // Cores
                $white = imagecolorallocate($image, 255, 255, 255);
                $black = imagecolorallocate($image, 0, 0, 0);
                $colors = [
                    imagecolorallocate($image, 255, 99, 132),   // Rosa
                    imagecolorallocate($image, 54, 162, 235),   // Azul
                    imagecolorallocate($image, 255, 206, 86),   // Amarelo
                    imagecolorallocate($image, 75, 192, 192),   // Verde água
                    imagecolorallocate($image, 153, 102, 255),  // Roxo
                    imagecolorallocate($image, 255, 159, 64)    // Laranja
                ];

                // Definir fonte para GD
                $font = realpath(__DIR__ . '/../../assets/fpdf/pChart.1.27d/Fonts/tahoma.ttf');
                if ($font === false || !is_file($font)) {
                    $font = false;
                }
                $font_size_pie = 10; // Para porcentagens nas fatias
                $font_size_legend = 9; // Para texto da legenda

                // Preencher fundo
                imagefill($image, 0, 0, $white);

                // Calcular totais e ângulos
                $total_books = array_sum(array_column($livros_por_aluno, 'book_count'));
                $start_angle = 0;
                $center_x = $width / 2 - 100; // Deslocar o centro para a esquerda para a legenda
                $center_y = $height / 2;
                $radius = min($width, $height) / 2 - 50; // Raio ajustado

                // Desenhar fatias do gráfico e adicionar porcentagens
                foreach ($livros_por_aluno as $index => $data) {
                    $slice_angle = ($data['book_count'] / $total_books) * 360;
                    $color = $colors[$index % count($colors)];

                    imagefilledarc(
                        $image,
                        $center_x,
                        $center_y,
                        $radius * 2,
                        $radius * 2,
                        $start_angle,
                        $start_angle + $slice_angle,
                        $color,
                        IMG_ARC_PIE
                    );

                    // Adicionar porcentagem na fatia
                    $mid_angle = deg2rad($start_angle + ($slice_angle / 2));
                    $text_x = $center_x + ($radius * 0.6) * cos($mid_angle);
                    $text_y = $center_y + ($radius * 0.6) * sin($mid_angle);

                    $percentage_text = round(($data['book_count'] / $total_books) * 100) . '%';

                    if ($font !== false && function_exists('imagettftext')) {
                        $bbox = imagettfbbox($font_size_pie, 0, $font, $percentage_text);
                        $text_width = $bbox[2] - $bbox[0];
                        $text_height = $bbox[1] - $bbox[7];

                        imagettftext($image, $font_size_pie, 0, $text_x - ($text_width / 2), $text_y + ($text_height / 2), $black, $font, $percentage_text);
                    } else {
                        imagestring($image, 3, (int)$text_x, (int)$text_y, $percentage_text, $black);
                    }

                    $start_angle += $slice_angle;
                }

                // Adicionar legenda
                $legend_x = $center_x + $radius + 50; // Posicionar legenda à direita do gráfico
                $legend_y = 50;
                $line_height = 20;
                $box_size = 15;

                if ($font !== false && function_exists('imagettftext')) {
                    imagettftext($image, $font_size_legend + 2, 0, $legend_x, $legend_y, $black, $font, utf8_decode("Legenda:"));
                } else {
                    imagestring($image, 3, $legend_x, $legend_y, utf8_decode("Legenda:"), $black);
                }
                $legend_y += $line_height;

                foreach ($livros_por_aluno as $index => $data) {
                    $color = $colors[$index % count($colors)];
                    imagefilledrectangle($image, $legend_x, $legend_y, $legend_x + $box_size, $legend_y + $box_size, $color);
                    imagerectangle($image, $legend_x, $legend_y, $legend_x + $box_size, $legend_y + $box_size, $black); // Borda

                    $legend_text = utf8_decode(htmlspecialchars_decode($data['student_name'])) . " (" . $data['book_count'] . ")";
                    if ($font !== false && function_exists('imagettftext')) {
                        imagettftext($image, $font_size_legend, 0, $legend_x + $box_size + 10, $legend_y + $box_size - 3, $black, $font, $legend_text);
                    } else {
                        imagestring($image, 3, $legend_x + $box_size + 10, $legend_y + $box_size - 3, $legend_text, $black);
                    }
                    $legend_y += $line_height;
                }

                // Criar diretório temp se não existir
                $temp_dir = __DIR__ . '/../../temp';
                if (!file_exists($temp_dir)) {
                    mkdir($temp_dir, 0777, true);
                }

                // Salvar a imagem com extensão .png
                $temp_file = $temp_dir . '/chart_' . uniqid() . '.png';
                imagepng($image, $temp_file);
                imagedestroy($image);

                // Criar o PDF
                $pdf = new FPDF();
                $pdf->AddPage();
                aplicarCabecalhoRelatorioBiblioteca($pdf, 'RELATÓRIO GERAL DE TURMA');

                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 10, utf8_decode('Aluno(a) destaque: ') . utf8_decode($aluno_destaque), 0, 1);
                $pdf->Cell(0, 10, utf8_decode('Turma: ') . utf8_decode($nome_turma), 0, 1);
                $pdf->Ln(10);

                // Inserir o gráfico no PDF
                if (file_exists($temp_file)) {
                    $pdf->Image($temp_file, ($pdf->GetPageWidth() - 150) / 2, $pdf->GetY(), 150); // Ajustado para 150mm de largura
                    $pdf->Ln(160); // Ajustar esta linha com base na altura da imagem e espaçamento desejado
                } else {
                    $pdf->SetTextColor(255, 0, 0); // Cor vermelha para aviso
                    $pdf->Cell(0, 10, utf8_decode('Não foi possível gerar o gráfico.'), 0, 1, 'C');
                    $pdf->SetTextColor(0, 0, 0); // Volta para cor preta
                    $pdf->Ln(10);
                }

                // Adicionar detalhes da tabela
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 10, utf8_decode('Detalhes dos Livros Emprestados:'), 0, 1);
                $pdf->Ln(2);

                // Cabeçalho da tabela de livros
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetFillColor(230, 230, 230); // Cor de fundo para o cabeçalho da tabela
                $pdf->Cell(80, 7, utf8_decode('Aluno'), 1, 0, 'C', true);
                $pdf->Cell(30, 7, utf8_decode('Livros'), 1, 1, 'C', true);

                // Dados da tabela de livros
                $pdf->SetFont('Arial', '', 10);
                foreach ($livros_por_aluno as $aluno_data) {
                    $pdf->Cell(80, 7, utf8_decode($aluno_data['student_name']), 1);
                    $pdf->Cell(30, 7, $aluno_data['book_count'], 1, 1, 'C');
                }

                // Limpar arquivo temporário
                if (file_exists($temp_file)) {
                    unlink($temp_file);
                }

                // Saída do PDF
                $pdf->Output('D', 'relatorio_turma.pdf');
            } else {
                // Se não houver dados, criar PDF com mensagem
                $pdf = new FPDF();
                $pdf->AddPage();
                aplicarCabecalhoRelatorioBiblioteca($pdf, 'RELATÓRIO GERAL DE TURMA');
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 10, utf8_decode('Nenhum dado disponível para o período ou a geração de gráfico não está disponível no servidor.'), 0, 1);
                $pdf->Output('I', 'relatorio_turma.pdf');
            }
        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage();
        }
    }
    public function footer() {}
}

$pdf = new Relatorio_geral_turma_pdf;
