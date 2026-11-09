<?php
date_default_timezone_set('America/Fortaleza');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$portalSessionActive = isset($_SESSION['id'], $_SESSION['email'], $_SESSION['nome'])
    && trim((string) $_SESSION['id']) !== ''
    && trim((string) $_SESSION['email']) !== ''
    && trim((string) $_SESSION['nome']) !== '';
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$sidebarMenu = [
    ['title' => 'Dashboard', 'icon' => 'fas fa-home', 'id' => 'dashboard', 'items' => []],
    ['title' => 'Classificação', 'icon' => 'fas fa-tags', 'section' => 'classificacao', 'id' => 'classificacao', 'items' => [
        ['title' => 'Cadastrar Categoria', 'icon' => 'fas fa-plus', 'section' => 'cadastrar_categoria', 'description' => 'Sessão para cadastrar novas categorias.'],
        ['title' => 'Cadastrar Gênero', 'icon' => 'fas fa-plus', 'section' => 'cadastrar_genero', 'description' => 'Sessão para cadastrar novos gêneros.'],
        ['title' => 'Cadastrar Subgênero', 'icon' => 'fas fa-plus', 'section' => 'cadastrar_subgenero', 'description' => 'Sessão para cadastrar subgêneros.'],
        ['title' => 'Editar Categoria', 'icon' => 'fas fa-edit', 'section' => 'editar_categoria', 'description' => 'Sessão para editar informações de categoria'],
        ['title' => 'Editar Gênero', 'icon' => 'fas fa-edit', 'section' => 'editar_genero', 'description' => 'Sessão para editar informações de gênero'],
        ['title' => 'Editar Subgênero', 'icon' => 'fas fa-edit', 'section' => 'editar_subgenero', 'description' => 'Sessão para editar informações de subgênero']
    ]],
    ['title' => 'Gerenciar Acervo', 'icon' => 'fas fa-book', 'section' => 'gerenciar_acervo', 'id' => 'acervo', 'items' => [
        ['title' => 'Cadastrar Livro', 'icon' => 'fas fa-plus', 'section' => 'cadastrar_livro', 'description' => 'Sessão para cadastrar livros e gerenciar o acervo.'],
        ['title' => 'Consultar Livros', 'icon' => 'fas fa-search', 'section' => 'consultar_livro', 'description' => 'Sessão para consultar o acervo de livros.'],
        ['title' => 'Editar Livro', 'icon' => 'fas fa-edit', 'section' => 'editar_livro', 'description' => 'Sessão para editar informações de livros existentes.'],
        ['title' => 'Editar Autor', 'icon' => 'fas fa-edit', 'section' => 'editar_autor', 'description' => 'Sessão para editar informações de autores.'],
        ['title' => 'Excluir Livro', 'icon' => 'fas fa-trash', 'section' => 'excluir_livro', 'description' => 'Sessão para excluir livros do acervo.'],
    ]],
    // ['title' => 'Empréstimos', 'icon' => 'fas fa-exchange-alt', 'id' => 'emprestimos', 'items' => [
    //     ['title' => 'Novo Empréstimo', 'icon' => 'fas fa-arrow-right', 'section' => 'emprestimo_livro', 'description' => 'Sessão para realizar novos empréstimos.'],
    //     ['title' => 'Devolver Livro', 'icon' => 'fas fa-arrow-left', 'section' => 'devolucao_livro', 'description' => 'Sessão para registrar devoluções de livros.'],
    // ]],
    ['title' => 'QR Codes', 'icon' => 'fas fa-qrcode', 'section' => 'qrcodes', 'id' => 'qrcodes', 'items' => [
        ['title' => 'QR Code Geral', 'icon' => 'fas fa-qrcode', 'section' => 'qrcode_geral', 'description' => 'Sessão para gerar QR Codes para todos os livros.'],
        ['title' => 'QR Code Específico', 'icon' => 'fas fa-qrcode', 'section' => 'qrcode_especifico', 'description' => 'Sessão para gerar QR Code para um livro específico.'],
        ['title' => 'QR Code Específico do Livro', 'icon' => 'fas fa-qrcode', 'section' => 'qrcode_especifico_livro', 'description' => 'Sessão para gerar QR Code exclusivo de um livro.'],
    ]],
    ['title' => 'Relatórios', 'icon' => 'fas fa-chart-bar', 'section' => 'relatorios', 'id' => 'relatorios', 'items' => [
        ['title' => 'Geral', 'icon' => 'fas fa-book-open', 'section' => 'relatorio_acervo', 'description' => 'Sessão para relatórios de acervo.'],
        ['title' => 'Empréstimos', 'icon' => 'fas fa-exchange-alt', 'section' => 'relatorio_emprestimo', 'description' => 'Sessão para relatórios de empréstimos.'],
        ['title' => 'Por Estante', 'icon' => 'fas fa-file-alt', 'section' => 'relatorio_pra_est', 'description' => 'Sessão para relatórios de prestação.'],
        ['title' => 'Por Turma', 'icon' => 'fas fa-users', 'section' => 'geral_turma', 'description' => 'Sessão para relatórios por turma.'],
        ['title' => 'Por Livro', 'icon' => 'fas fa-book', 'section' => 'relatorio_por_livro', 'description' => 'Sessão para relatórios por livro.']
    ]],
];

$sectionContents = [
    'gerenciar_acervo' => ['title' => 'Gerenciar Acervo', 'description' => 'Use esta sessão para gerenciar o acervo da biblioteca.', 'view' => 'views/gerenciar_acervo.php'],
    'classificacao' => ['title' => 'Classificação', 'description' => 'Use esta sessão para gerenciar a classificação dos livros.', 'view' => 'views/classificacao.php'],
    'cadastrar_livro' => ['title' => 'Cadastrar Livro', 'description' => 'Use esta sessão para cadastrar um novo livro no acervo.', 'view' => 'views/cadastrar_livro.php'],
    'cadastrar_categoria' => ['title' => 'Cadastrar Categoria', 'description' => 'Use esta sessão para cadastrar novas categorias.', 'view' => 'views/cadastrar_categoria.php'],
    'consultar_livro' => ['title' => 'Consultar Livros', 'description' => 'Use esta sessão para consultar os livros cadastrados.', 'view' => 'views/consultar_livro.php'],
    'editar_livro' => ['title' => 'Editar Livro', 'description' => 'Use esta sessão para editar dados dos livros.', 'view' => 'views/editar_livro.php'],
    'excluir_livro' => ['title' => 'Excluir Livro', 'description' => 'Use esta sessão para excluir livros do acervo.', 'view' => 'views/excluir_livro.php'],
    'editar_autor' => ['title' => 'Editar Autor', 'description' => 'Use esta sessão para editar informações de autores.', 'view' => 'views/editar_autor.php'],
    'cadastrar_genero' => ['title' => 'Cadastrar Gênero', 'description' => 'Use esta sessão para cadastrar novos gêneros.', 'view' => 'views/cadastrar_genero.php'],
    'cadastrar_subgenero' => ['title' => 'Cadastrar Subgênero', 'description' => 'Use esta sessão para cadastrar subgêneros.', 'view' => 'views/cadastrar_subgenero.php'],
    'editar_categoria' => ['title' => 'Editar Categoria', 'description' => 'Use esta sessão para editar categorias', 'view' => 'views/editar_categoria.php'],
    'editar_genero' => ['title' => 'Editar Gênero', 'description' => 'Use esta sessão para editar gêneros', 'view' => 'views/editar_genero.php'],
    'editar_subgenero' => ['title' => 'Editar Subgênero', 'description' => 'Use esta sessão para editar subgêneros', 'view' => 'views/editar_subgenero.php'],
    'emprestimo_livro' => ['title' => 'Novo Empréstimo', 'description' => 'Use esta sessão para registrar um novo empréstimo.', 'view' => 'views/emprestimo/emprestimo_livro.php'],
    'devolucao_livro' => ['title' => 'Devolver Livro', 'description' => 'Use esta sessão para registrar a devolução de livros.', 'view' => 'views/emprestimo/devolucao_livro.php'],
    'qrcodes' => ['title' => 'QR Codes', 'description' => 'Use esta sessão para acessar as opções de geração de QR Codes.', 'view' => 'views/QRCode/decisao.php'],
    'qrcode_geral' => ['title' => 'QR Code Geral', 'description' => 'Use esta sessão para gerar QR Codes para todos os livros do acervo.', 'view' => 'views/QRCode/geradorQR.php'],
    'qrcode_especifico' => ['title' => 'QR Code Específico', 'description' => 'Use esta sessão para gerar QR Code para um livro específico.', 'view' => 'views/QRCode/geradorQR_especifico.php'],
    'qrcode_especifico_livro' => ['title' => 'QR Code Específico do Livro', 'description' => 'Use esta sessão para gerar QR Code exclusivo de um livro.', 'view' => 'views/QRCode/geradorQR_especifico_livro.php'],
    'qrcode_selecionar_livros' => ['title' => 'Selecionar livros para QR Code', 'description' => 'Selecione os exemplares para gerar QR Codes.', 'view' => 'views/QRCode/ids_livros.php'],
    'relatorios' => ['title' => 'Relatórios', 'description' => 'Use esta sessão para acessar as opções de relatórios.', 'view' => 'views/relatorios/decisao.php'],
    'relatorio_emprestimo' => ['title' => 'Relatório de Empréstimos', 'description' => 'Use esta sessão para ver os relatórios de empréstimos.', 'view' => 'views/relatorios/relatorio_emprestimo.php'],
    'relatorio_acervo' => ['title' => 'Relatório de Acervo', 'description' => 'Use esta sessão para ver os relatórios gerados do acervo.', 'view' => 'views/relatorios/relatorio_acervo.php'],
    'geral_turma' => ['title' => 'Relatório por Turma', 'description' => 'Use esta sessão para gerar relatórios por turma.', 'view' => 'views/relatorios/geral_turma.php'],
    'relatorio_por_livro' => ['title' => 'Relatório por Livro', 'description' => 'Use esta sessão para ver os relatórios gerados por livro.', 'view' => 'views/relatorios/relatorio_livros.php'],
    'relatorio_pra_est' => ['title' => 'Relatório de Prestação', 'description' => 'Use esta sessão para gerar relatórios de prestação.', 'view' => 'views/relatorios/relatorios.php'],
    'relatorio_geral_turma' => ['title' => 'Relatório Geral por Turma', 'description' => 'Exibe o relatório da turma selecionada.', 'view' => 'views/relatorios/relatorio_geral_turma.php']
];

$libraryPageDirectories = [
    'views/cadastrar_categoria.php' => __DIR__ . '/views',
    'views/cadastrar_genero.php' => __DIR__ . '/views',
    'views/cadastrar_livro.php' => __DIR__ . '/views',
    'views/cadastrar_subgenero.php' => __DIR__ . '/views',
    'views/classificacao.php' => __DIR__ . '/views',
    'views/consultar_livro.php' => __DIR__ . '/views',
    'views/editar_autor.php' => __DIR__ . '/views',
    'views/editar_categoria.php' => __DIR__ . '/views',
    'views/editar_genero.php' => __DIR__ . '/views',
    'views/editar_livro.php' => __DIR__ . '/views',
    'views/editar_subgenero.php' => __DIR__ . '/views',
    'views/emprestimo/decisao.php' => __DIR__ . '/views',
    'views/emprestimo/devolucao_livro.php' => __DIR__ . '/views',
    'views/emprestimo/emprestimo_livro.php' => __DIR__ . '/views',
    'views/excluir_livro.php' => __DIR__ . '/views',
    'views/gerenciar_acervo.php' => __DIR__ . '/views',
    'views/QRCode/decisao.php' => __DIR__ . '/views/QRCode',
    'views/QRCode/geradorQR.php' => __DIR__ . '/views/QRCode',
    'views/QRCode/geradorQR_especifico.php' => __DIR__ . '/views/QRCode',
    'views/QRCode/geradorQR_especifico_livro.php' => __DIR__ . '/views/QRCode',
    'views/QRCode/ids_livros.php' => __DIR__ . '/views/QRCode',
    'views/QRCode/QRCode_especifico_livro.php' => __DIR__ . '/views/QRCode',
    'views/QRCode/QRCodes.php' => __DIR__ . '/views/QRCode',
    'views/QRCode/QRCodes_especifico.php' => __DIR__ . '/views/QRCode',
    'views/relatorios/decisao.php' => __DIR__ . '/views/relatorios',
    'views/relatorios/geral_turma.php' => __DIR__ . '/views/relatorios',
    'views/relatorios/relatorio_acervo.php' => __DIR__ . '/views/relatorios',
    'views/relatorios/relatorio_emprestimo.php' => __DIR__ . '/views/relatorios',
    'views/relatorios/relatorio_geral_turma.php' => __DIR__ . '/views/relatorios',
    'views/relatorios/relatorio_geral_turma_pdf.php' => __DIR__ . '/views/relatorios',
    'views/relatorios/relatorio_livros.php' => __DIR__ . '/views/relatorios',
    'views/relatorios/relatorio_pra_est.php' => __DIR__ . '/views/relatorios',
    'views/relatorios/relatorios.php' => __DIR__ . '/views/relatorios',
];
$renderEmbeddedPage = static function ($embeddedPage, $embeddedDirectory, $pageScope) {
    extract($pageScope, EXTR_SKIP);
    switch ($embeddedPage) {
            case 'views/cadastrar_categoria.php':?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Categorias Bibliográficas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'ceara-green': '#008C45',
                    'ceara-orange': '#FFA500',
                    'ceara-white': '#FFFFFF',
                    primary: '#4CAF50',
                    secondary: '#FFB74D',
                }
            }
        }
    }
</script>

<body class="min-h-screen bg-cover bg-center bg-no-repeat flex items-center justify-center p-4 sm:p-6 md:p-8 select-none"
    style="background-image: url('../assets/img/layout.png'); background-opacity: 0.3;">

    <?php if (isset($_GET['from']) && $_GET['from'] === 'classificacao'): ?>
        <a href="classificacao.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
            <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
            <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
        </a>
    <?php endif; ?>

    <div class="flex flex-col items-center">
        <!-- <img src="../assets/img/logo1.png" class="w-[250px] h-auto xs:w-[280px] sm:w-[320px] md:w-[350px] lg:w-[400px] mt-[-150px]" alt="Logo"> -->

        <div class="w-full max-w-xl h-full bg-white rounded-xl shadow-2xl overflow-hidden ">
            <div class="bg-[#007A33] p-4 sm:p-6">
                <h2 class="text-xl sm:text-2xl font-bold text-white text-center">
                    <i class="fas fa-book-open mr-2"></i>Cadastro de Categorias Bibliográficas
                </h2>
            </div>

            <form id="categoryForm" action="../controllers/main_controller.php" method="post" class="p-4 sm:p-6 space-y-4">

                <div class="relative">
                    <div class="relative group">
                        <i class="fas fa-book-open absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-[#007A33] transition-colors duration-200"></i>
                        <input type="text" id="nova_categoria" name="nova_categoria"
                            class="w-full pl-10 pr-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-[#007A33] focus:ring focus:ring-[#007A33]/20 focus:outline-none hover:border-gray-300 transition-all duration-200 shadow-sm text-gray-500"
                            placeholder="Digite a nova categoria" required>
                    </div>
                </div>


                <div class="mt-4 sm:mt-6">
                    <button type="submit"
                        class="w-full card-hover bg-[#FFA500] hover:bg-[#FFB74D] text-white font-medium py-2.5 sm:py-3 px-4 rounded-lg transition duration-300 ease-in-out flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Cadastrar
                    </button>
                </div>

                <div class="mt-2 space-y-2">
                    <?php if (isset($_GET['true'])) { ?>
                        <p class="text-green-600 bg-green-100 border border-green-300 rounded-md p-2 text-center font-medium text-sm sm:text-base"> Categoria cadastrada com sucesso!</p>
                    <?php } ?>
                    <?php if (isset($_GET['false'])) { ?>
                        <p class="text-red-600 bg-red-100 border border-red-300 rounded-md p-2 text-center font-medium text-sm sm:text-base">Erro ao cadastrar!</p>
                    <?php } ?>
                    <?php if (isset($_GET['ja_cadastrado'])) { ?>
                        <p class="text-yellow-600 bg-yellow-100 border border-yellow-300 rounded-md p-2 text-center font-medium text-sm sm:text-base">Categoria já cadastrada!</p>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>

    <style>
        .card-hover {
            transition: transform 0.2s ease-in-out;
        }

        .card-hover:hover {
            transform: translateY(-2px);
        }
    </style>

    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.querySelector('form'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/cadastrar_genero.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Gêneros Bibliográficos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'ceara-green': '#008C45',
                    'ceara-orange': '#FFA500',
                    'ceara-white': '#FFFFFF',
                    primary: '#4CAF50',
                    secondary: '#FFB74D',
                }
            }
        }
    }
</script>

<body class="min-h-screen bg-cover bg-center bg-no-repeat flex items-center justify-center p-4 sm:p-6 md:p-8 select-none"
    style="background-image: url('../assets/img/layout.png'); background-size: cover; background-attachment: fixed;">

    <?php if (isset($_GET['from']) && $_GET['from'] === 'classificacao'): ?>
        <a href="classificacao.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
            <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
            <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
        </a>
    <?php endif; ?>

    <div class="flex flex-col items-center">
        <!-- <img src="../assets/img/logo1.png" class="w-[250px] h-auto xs:w-[280px] sm:w-[320px] md:w-[350px] lg:w-[400px] mt-[-150px]" alt="Logo"> -->

        <div class="w-full max-w-xl h-full bg-white rounded-xl shadow-2xl overflow-hidden ">
            <div class="bg-[#007A33] p-4 sm:p-6">
                <h2 class="text-xl sm:text-2xl font-bold text-white text-center">
                    <i class="fas fa-book-open mr-2"></i>Cadastro de Gêneros Bibliográficos
                </h2>
            </div>

            <form id="genreForm" action="../controllers/main_controller.php" method="post" class="p-4 sm:p-6 space-y-4">
                <div class="relative">
                    <div class="relative group">
                        <i class="fas fa-book-open absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-[#007A33] transition-colors duration-200"></i>
                        <select id="nomeCategoria" name="categoria"
                            class="w-full pl-10 pr-3 py-2.5 border-2 border-gray-200 text-gray-400 rounded-lg focus:border-[#007A33] focus:ring focus:ring-[#007A33]/20 focus:outline-none appearance-none bg-white hover:border-gray-300 transition-all duration-200 cursor-pointer shadow-sm"
                            required>
                            <option value="" disabled selected>Categoria</option>
                            <?php
                            $categorias = $select_model->select_categoria();
                            foreach ($categorias as $categoria) {
                            ?>
                                <option value="<?= htmlspecialchars($categoria['categorias']) ?>"><?= htmlspecialchars($categoria['categorias']) ?></option>
                            <?php } ?>

                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="relative group">
                        <i class="fas fa-book-open absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-[#007A33] transition-colors duration-200"></i>
                        <input type="text" id="genero" name="genero"
                            class="w-full pl-10 pr-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-[#007A33] focus:ring focus:ring-[#007A33]/20 focus:outline-none hover:border-gray-300 transition-all duration-200 shadow-sm text-gray-500"
                            placeholder="Digite o novo gênero" required>
                    </div>
                </div>


                <div class="mt-4 sm:mt-6">
                    <button type="submit"
                        class="w-full card-hover bg-[#FFA500] hover:bg-[#FFB74D] text-white font-medium py-2.5 sm:py-3 px-4 rounded-lg transition duration-300 ease-in-out flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Cadastrar
                    </button>
                </div>

                <div class="mt-2 space-y-2">
                    <?php if (isset($_GET['true'])) { ?>
                        <p class="text-green-600 bg-green-100 border border-green-300 rounded-md p-2 text-center font-medium text-sm sm:text-base"> Gênero cadastrado com sucesso!</p>
                    <?php } ?>
                    <?php if (isset($_GET['false'])) { ?>
                        <p class="text-red-600 bg-red-100 border border-red-300 rounded-md p-2 text-center font-medium text-sm sm:text-base">Erro ao cadastrar!</p>
                    <?php } ?>
                    <?php if (isset($_GET['ja_cadastrado'])) { ?>
                        <p class="text-yellow-600 bg-yellow-100 border border-yellow-300 rounded-md p-2 text-center font-medium text-sm sm:text-base">Gênero já cadastrado!</p>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>

    <style>
        .card-hover {
            transition: transform 0.2s ease-in-out;
        }

        .card-hover:hover {
            transform: translateY(-2px);
        }
    </style>

    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.querySelector('form'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/cadastrar_livro.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();
$categorias = $select_model->select_categoria();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário Biblioteca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#007A33',
                        'ceara-green-dark': '#005F27',
                        'ceara-orange': '#FFA500',
                        'ceara-orange-dark': '#FF8C00',
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(0, 122, 51, 0.1), 0 8px 10px -6px rgba(0, 122, 51, 0.1)',
                    },
                    backgroundImage: {
                        'gradient-ceara': 'linear-gradient(to right, #007A33, #005F27)',
                        'gradient-orange': 'linear-gradient(to right, #FFA500, #FF8C00)',
                    }
                }
            }
        }
    </script>
    <style>
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #007A33 !important;
            border-color: #007A33 !important;
        }
        input[type="checkbox"]:checked:focus,
        input[type="radio"]:checked:focus {
            --tw-ring-color: rgba(0, 122, 51, 0.2);
        }
        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            outline: none;
            transition: all 0.2s ease-in-out;
        }
        input[type="radio"]:checked {
            border-color: #007A33;
            background-color: #007A33;
            box-shadow: inset 0 0 0 4px #fff;
        }
        input[type="radio"]:focus {
            box-shadow: 0 0 0 3px rgba(0, 122, 51, 0.2);
        }
        .tab-button.active {
            color: #007A33 !important;
            border-bottom: 2px solid #007A33 !important;
        }
        input[type="checkbox"] {
            accent-color: #007A33;
        }
    </style>
</head>

<body class="min-h-screen w-full bg-gradient-to-b from-gray-50 to-gray-100 flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-12"
    style="background-image: url('../assets/img/layout.png'); background-size: cover; background-attachment: fixed;">

    <?php if (isset($_GET['from']) && $_GET['from'] === 'gerenciar_acervo'): ?>
        <a href="gerenciar_acervo.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
            <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
            <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
        </a>
    <?php endif; ?>

    <div class="w-full max-w-4xl">
        <!-- <img src="../assets/img/logo1.png"
            class="w-[200px] sm:w-[250px] md:w-[300px] mx-auto mb-8 drop-shadow-xl hover:scale-105 transition-transform duration-300"
            alt="Logo"> -->
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-ceara p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white text-center tracking-wide">
                    <i class="fas fa-book mr-3"></i>Cadastro de Livros
                </h2>
            </div>

            <form id="bookForm" action="../controllers/main_controller.php" method="post" class="p-6 sm:p-8">
                <div class="mb-8">
                    <div class="flex border-b border-gray-200">
                        <button type="button"
                            class="tab-button py-2 px-4 font-medium text-ceara-green border-b-2 border-ceara-green cursor-default active"
                            data-tab="book-info">Informações do Livro</button>
                        <button type="button" class="tab-button py-2 px-4 font-medium text-gray-500 cursor-not-allowed"
                            data-tab="location" disabled>Localização e Estoque</button>
                    </div>
                </div>

                <div id="validationMessage" class="hidden"></div>

                <div id="book-info" class="tab-content space-y-6">
                    <div class="space-y-6">
                        <!-- Origem, Categoria, Tipo e Empréstimo -->
                        <div class="grid grid-cols-2 lg:grid-cols-5 md:grid-cols-4 sm:grid-cols-3 gap-x-6 gap-y-4">
                            <div class="space-y-2 sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Literatura</label>
                                <div class="flex flex-col space-y-2">
                                    <label class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                                        <input type="radio" name="literatura" value="Brasileira"
                                            class="h-5 w-5 text-ceara-green border-gray-300 focus:ring-ceara-green"
                                            required>
                                        <span class="text-sm">Brasileira</span>
                                    </label>
                                    <label class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                                        <input type="radio" name="literatura" value="Estrangeira"
                                            class="h-5 w-5 text-ceara-green border-gray-300 focus:ring-ceara-green">
                                        <span class="text-sm">Estrangeira</span>
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-2 col-span-1 sm:col-span-2">
                                <label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
                                <div class="relative">
                                    <select name="categoria" id="categoria" required
                                        class="w-full pl-3 pr-10 py-3 text-base border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none appearance-none bg-white hover:border-gray-300 text-gray-600 transition-all duration-200 cursor-pointer">
                                        <option value="" disabled selected>Selecione a categoria</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?= htmlspecialchars($categoria['categorias'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($categoria['categorias'], ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2 sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                <div class="flex flex-col space-y-2">
                                    <label class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                                        <input type="radio" name="ficcao" value="1"
                                            class="h-5 w-5 text-ceara-green border-gray-300 focus:ring-ceara-green"
                                            required>
                                        <span class="text-sm">Ficção</span>
                                    </label>
                                    <label class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                                        <input type="radio" name="ficcao" value="0"
                                            class="h-5 w-5 text-ceara-green border-gray-300 focus:ring-ceara-green">
                                        <span class="text-sm">Não Ficção</span>
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-2 sm:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Empréstimo</label>
                                <div class="flex flex-col space-y-2">
                                    <label class="flex items-center space-x-2 text-gray-600 cursor-pointer">
                                        <input type="radio" name="cativo" value="1"
                                            class="h-5 w-5 text-ceara-green border-gray-300 focus:ring-ceara-green">
                                        <span class="text-sm">Cativo</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Subgênero -->
                        <div class="space-y-2">
                            <label for="nomesubGenero" class="block text-sm font-medium text-gray-700">Subgênero</label>
                            <div class="relative">
                                <select id="nomesubGenero" name="nomesubGenero"
                                    class="w-full pl-3 pr-10 py-3 text-base border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none appearance-none bg-white hover:border-gray-300 text-gray-600 transition-all duration-200 cursor-pointer"
                                    required>
                                    <option value="" disabled selected>Selecione o Subgênero</option>
                                    <?php
                                    $generos = $select_model->select_genero();
                                    foreach ($generos as $genero) {
                                        $subgeneros = $select_model->select_subgenero(genero: $genero['generos']);
                                        ?>
                                        <optgroup label="<?= $genero['generos'] ?>"></optgroup>
                                        <?php
                                        foreach ($subgeneros as $subgenero) {
                                            ?>
                                            <option value="<?= $subgenero['subgenero'] ?>"><?= $subgenero['subgenero'] ?></option>
                                        <?php } ?>
                                        </optgroup>
                                    <?php } ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <button type="button" id="revertSubgenero"
                                class="hidden mt-2 text-ceara-green hover:text-ceara-green-dark text-sm">
                                <i class="fas fa-undo mr-1"></i> Reverter e mostrar Gênero
                            </button>
                        </div>

                        <!-- Título -->
                        <div>
                            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                            <input type="text" id="titulo" name="titulo"
                                class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200"
                                placeholder="Título do livro" required>
                        </div>

                        <!-- Autores -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Autores</label>
                            <div id="authorFields" class="space-y-4 w-full"></div>
                            <p class="text-xs text-gray-500 mt-2">Digite o nome completo do autor e selecione-o. Se não encontrar, use a opção <strong>Incluir autor</strong>.</p>
                        </div>

                        <!-- Editora e Data de Criação -->
                        <div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="">
                                    <label for="editora" class="block text-sm font-medium text-gray-700 mb-1">Editora</label>
                                    <input type="text" id="editora" name="editora"
                                        class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200"
                                        placeholder="Nome da editora" required>
                                </div>
                                <div class="">
                                    <label for="data" class="block text-sm font-medium text-gray-700 mb-1">Data de criação</label>
                                    <input type="text" id="data" name="data"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200"
                                        placeholder="DD/MM/AAAA" required>
                                    <span id="dataError" class="text-red-500 text-xs hidden mt-1">Data inválida</span>
                                </div>
                            </div>
                        </div>

                        <!-- Edição e Quantidade -->
                        <div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="">
                                    <label for="edicao" class="block text-sm font-medium text-gray-700 mb-1">Edição</label>
                                    <input type="text" id="edicao" name="edicao"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200"
                                        placeholder="Edição"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <div class="">
                                    <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                                    <input type="number" id="quantidade" name="quantidade" min="1"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200"
                                        placeholder="Quantidade" required>
                                </div>
                            </div>
                            <div class="mt-2">
                                <input type="checkbox" id="semEdicao" name="semEdicao"
                                    class="mr-2 h-4 w-4 text-ceara-green focus:ring-ceara-green border-gray-300 rounded accent-ceara-green"
                                    onchange="document.getElementById('edicao').disabled = this.checked; document.getElementById('edicao').value = this.checked ? '' : document.getElementById('edicao').value;">
                                <label for="semEdicao" class="text-sm text-gray-600">Não existe edição</label>
                            </div>
                        </div>

                        <?php if (isset($_GET['true'])): ?>
                            <div class="flex items-center p-4 mb-4 text-green-800 border-l-4 border-green-500 bg-green-50 rounded-md"
                                role="alert">
                                <i class="fas fa-check-circle text-xl mr-3"></i>
                                <span class="text-sm font-medium">Livro cadastrado com sucesso!</span>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['false'])): ?>
                            <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md"
                                role="alert">
                                <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                                <span class="text-sm font-medium">ERRO ao cadastrar livro!</span>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['ja_cadastrado'])): ?>
                            <div class="flex items-center p-4 mb-4 text-yellow-800 border-l-4 border-yellow-500 bg-yellow-50 rounded-md"
                                role="alert">
                                <i class="fas fa-exclamation-triangle text-xl mr-3"></i>
                                <span class="text-sm font-medium">Livro já cadastrado!</span>
                            </div>
                        <?php endif; ?>

                        <div class="mt-8">
                            <button type="button" id="nextButton"
                                class="w-full bg-gradient-ceara text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                <i class="fas fa-arrow-right mr-3"></i>
                                Avançar
                            </button>
                        </div>
                    </div>
                </div>

                <div id="location" class="tab-content space-y-6 hidden">
                    <div>
                        <label for="corredor" class="block text-sm font-medium text-gray-700 mb-1">Corredor</label>
                        <div class="relative">
                            <select id="corredor" name="corredor"
                                class="w-full pl-3 pr-10 py-3 text-base border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none appearance-none bg-white hover:border-gray-300 text-gray-600 transition-all duration-200 cursor-pointer"
                                required>
                                <option value="" disabled selected>Selecione o Corredor</option>
                                <option value="A">Corredor A</option>
                                <option value="B">Corredor B</option>
                                <option value="C">Corredor C</option>
                                <option value="D">Corredor D</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="estante" class="block text-sm font-medium text-gray-700 mb-1">Estante</label>
                        <div class="relative">
                            <select id="estante" name="livro_estante"
                                class="w-full pl-3 pr-10 py-3 text-base border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none appearance-none bg-white hover:border-gray-300 text-gray-600 transition-all duration-200 cursor-pointer"
                                required>
                                <option value="" disabled selected>Selecione a Estante</option>
                                <?php for ($i = 1; $i <= 40; $i++) { ?>
                                    <option value="<?= $i ?>">Estante <?= $i ?></option>
                                <?php } ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="prateleira" class="block text-sm font-medium text-gray-700 mb-1">Prateleira</label>
                        <div class="relative">
                            <select id="prateleira" name="livro_prateleira"
                                class="w-full pl-3 pr-10 py-3 text-base border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none appearance-none bg-white hover:border-gray-300 text-gray-600 transition-all duration-200 cursor-pointer"
                                required>
                                <option value="" disabled selected>Selecione a Prateleira</option>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="P<?= $i ?>">Prateleira <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 space-y-4">
                        <button type="button" id="backButton"
                            class="w-full bg-gradient-ceara text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                            <i class="fas fa-arrow-left mr-3"></i>
                            Voltar
                        </button>
                        <button type="submit"
                            class="w-full bg-gradient-orange text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                            <i class="fas fa-paper-plane mr-3"></i>
                            Cadastrar Livro
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const serverAuthors = <?= json_encode($select_model->select_nome_autor(), JSON_UNESCAPED_UNICODE); ?>;
        document.addEventListener('DOMContentLoaded', function () {
            // Autores
            const authorFields = document.getElementById('authorFields');
            let authorCount = 0;

            function formatAbnt(fullName) {
                const parts = fullName.trim().split(/\s+/).filter(Boolean);
                if (parts.length === 0) return '';
                if (parts.length === 1) return parts[0];
                const sobrenome = parts.pop().toUpperCase();
                const nome = parts
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
                return nome ? `${sobrenome}, ${nome}` : sobrenome;
            }

            const authors = serverAuthors.map(author => ({
                id: author.id,
                nome: author.nome_autor.trim(),
                display: formatAbnt(author.nome_autor.trim()),
                query: author.nome_autor.trim().toLowerCase()
            }));

            function updateRemoveButtons() {
                const rows = authorFields.querySelectorAll('.author-row');
                rows.forEach((row, index) => {
                    const button = row.querySelector('.remove-author');
                    if (!button) {
                        return;
                    }
                    button.style.display = index > 0 ? 'inline-flex' : 'none';
                });
            }

            function createAuthorRow() {
                authorCount++;
                const row = document.createElement('div');
                row.className = 'author-row space-y-2';
                row.setAttribute('data-index', authorCount);
                const buttonHtml = authorCount === 1 ? `
                    <button type="button" class="add-author text-ceara-green p-2 rounded-full transition-colors duration-200">
                        <i class="fas fa-plus"></i>
                    </button>
                ` : `
                    <button type="button" class="remove-author text-red-500 p-2 rounded-full transition-colors duration-200">
                        <i class="fas fa-minus"></i>
                    </button>
                `;
                row.innerHTML = `
                    <div class="flex items-center space-x-4">
                        <div class="relative flex-1">
                            <input type="text" class="author-search w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200" placeholder="Buscar autor..." autocomplete="off" required>
                            <div class="author-options hidden absolute z-20 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"></div>
                        </div>
                        ${buttonHtml}
                    </div>
                    <input type="hidden" name="nome[]" class="author-nome">
                `;

                const searchInput = row.querySelector('.author-search');
                const optionsBox = row.querySelector('.author-options');
                const hiddenNome = row.querySelector('.author-nome');
                const addButton = row.querySelector('.add-author');
                const removeButton = row.querySelector('.remove-author');

                function hideOptions() {
                    optionsBox.classList.add('hidden');
                }

                function renderOptions(value) {
                    const query = value.trim().toLowerCase();
                    optionsBox.innerHTML = '';

                    if (!query) {
                        const placeholder = document.createElement('div');
                        placeholder.className = 'px-4 py-3 text-gray-500 text-sm';
                        placeholder.textContent = 'Digite para buscar um autor.';
                        optionsBox.appendChild(placeholder);
                        optionsBox.classList.remove('hidden');
                        return;
                    }

                    const matches = authors.filter(author => author.display.toLowerCase().includes(query) || author.query.includes(query));

                    if (matches.length > 0) {
                        matches.slice(0, 10).forEach(author => {
                            const option = document.createElement('button');
                            option.type = 'button';
                            option.className = 'w-full text-left px-4 py-3 hover:bg-gray-100 transition-colors duration-150';
                            option.textContent = author.display;
                            option.addEventListener('click', function () {
                                searchInput.value = author.display;
                                hiddenNome.value = author.nome;
                                hideOptions();
                            });
                            optionsBox.appendChild(option);
                        });
                    }

                    const includeOption = document.createElement('button');
                    includeOption.type = 'button';
                    includeOption.className = 'w-full text-left px-4 py-3 border-t border-gray-200 text-ceara-green hover:bg-gray-100 transition-colors duration-150';
                    includeOption.textContent = `Incluir autor: "${value.trim()}"`;
                    includeOption.addEventListener('click', function () {
                        const trimmedValue = value.trim();
                        searchInput.value = formatAbnt(trimmedValue);
                        hiddenNome.value = trimmedValue;
                        hideOptions();
                    });
                    optionsBox.appendChild(includeOption);
                    optionsBox.classList.remove('hidden');
                }

                searchInput.addEventListener('input', function () {
                    hiddenNome.value = '';
                    renderOptions(searchInput.value);
                });

                searchInput.addEventListener('focus', function () {
                    if (searchInput.value.trim()) {
                        renderOptions(searchInput.value);
                    } else {
                        renderOptions('');
                    }
                });

                document.addEventListener('click', function (event) {
                    if (!row.contains(event.target)) {
                        hideOptions();
                    }
                });

                if (addButton) {
                    addButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        createAuthorRow();
                    });
                }

                if (removeButton) {
                    removeButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        row.remove();
                        updateRemoveButtons();
                    });
                }

                authorFields.appendChild(row);
                updateRemoveButtons();
            }

            createAuthorRow();

            // Tabs functionality
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            const nextButton = document.getElementById('nextButton');
            const backButton = document.getElementById('backButton');
            const validationMessage = document.getElementById('validationMessage');

            function switchTab(tabName) {
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'text-ceara-green', 'border-b-2', 'border-ceara-green');
                    if (btn.getAttribute('data-tab') === tabName) {
                        btn.classList.add('active', 'text-ceara-green', 'border-b-2', 'border-ceara-green');
                    } else {
                        btn.classList.add('text-gray-500');
                    }
                });

                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    if (content.id === tabName) {
                        content.classList.remove('hidden');
                    }
                });

                tabButtons.forEach(btn => {
                    if (btn.getAttribute('data-tab') === 'location' && tabName === 'location') {
                        btn.removeAttribute('disabled');
                        btn.classList.add('hover:text-ceara-green');
                        btn.classList.remove('cursor-not-allowed', 'text-gray-500');
                    } else if (btn.getAttribute('data-tab') === 'location' && !btn.classList.contains('active')) {
                        btn.setAttribute('disabled', 'true');
                        btn.classList.remove('hover:text-ceara-green');
                        btn.classList.add('cursor-not-allowed', 'text-gray-500');
                    }
                });
            }

            nextButton.addEventListener('click', function () {
                const requiredFields = document.querySelectorAll('#book-info [required]');
                const literatura = document.querySelector('input[name="literatura"]:checked');
                const ficcao = document.querySelector('input[name="ficcao"]:checked');
                const subgenero = document.getElementById('nomesubGenero');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('border-red-500');
                    } else {
                        field.classList.remove('border-red-500');
                    }
                });

                if (!literatura) {
                    isValid = false;
                    document.querySelectorAll('input[name="literatura"]').forEach(radio =>
                        radio.closest('label').classList.add('border-red-500')
                    );
                }
                if (!ficcao) {
                    isValid = false;
                    document.querySelectorAll('input[name="ficcao"]').forEach(radio =>
                        radio.closest('label').classList.add('border-red-500')
                    );
                }

                const authorSearches = document.querySelectorAll('.author-search');
                const authorNames = document.querySelectorAll('.author-nome');
                // const authorSurnames = document.querySelectorAll('.author-sobrenome');
                authorSearches.forEach(search => {
                    if (!search.value.trim()) {
                        isValid = false;
                        search.classList.add('border-red-500');
                    } else {
                        search.classList.remove('border-red-500');
                    }
                });
                authorNames.forEach(name => {
                    if (!name.value.trim()) {
                        isValid = false;
                        const search = name.closest('.author-row').querySelector('.author-search');
                        if (search) search.classList.add('border-red-500');
                    }
                });
                // authorSurnames.forEach(surname => {
                //     if (!surname.value.trim()) {
                //         isValid = false;
                //         const search = surname.closest('.author-row').querySelector('.author-search');
                //         if (search) search.classList.add('border-red-500');
                //     }
                // });

                if (isValid) {
                    switchTab('location');
                    validationMessage.classList.add('hidden');
                } else {
                    validationMessage.innerHTML = `
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Por favor, preencha todos os campos obrigatórios!</span>
                        </div>
                    `;
                    validationMessage.classList.remove('hidden');
                }
            });

            backButton.addEventListener('click', function () {
                switchTab('book-info');
                validationMessage.classList.add('hidden');
            });

            tabButtons.forEach(button => {
                if (button.getAttribute('data-tab') === 'location') {
                    button.addEventListener('click', (e) => {
                        if (button.hasAttribute('disabled')) {
                            e.preventDefault();
                        } else {
                            switchTab('location');
                        }
                    });
                } else if (button.getAttribute('data-tab') === 'book-info') {
                    button.addEventListener('click', () => switchTab('book-info'));
                }
            });

            // Validação de Data
            const dataInput = document.getElementById('data');
            const dataError = document.getElementById('dataError');

            dataInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.slice(0, 8);

                if (value.length >= 5) {
                    value = value.replace(/(\d{2})(\d{2})(\d{0,4})/, '$1/$2/$3');
                } else if (value.length >= 3) {
                    value = value.replace(/(\d{2})(\d{0,2})/, '$1/$2');
                }
                e.target.value = value;

                if (value.length === 10) {
                    const [day, month, year] = value.split('/').map(Number);
                    const date = new Date(year, month - 1, day);
                    const isValid = date.getFullYear() === year &&
                        date.getMonth() === month - 1 &&
                        date.getDate() === day &&
                        date <= new Date();

                    if (!isValid) {
                        dataError.classList.remove('hidden');
                    } else {
                        dataError.classList.add('hidden');
                    }
                }
            });
        });
    </script>
    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.getElementById('bookForm'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>
</html>
<?php
                break;
            case 'views/cadastrar_subgenero.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Gêneros Bibliográficos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'ceara-green': '#008C45',
                    'ceara-orange': '#FFA500',
                    'ceara-white': '#FFFFFF',
                    primary: '#4CAF50',
                    secondary: '#FFB74D',
                }
            }
        }
    }
</script>

<body class="min-h-screen bg-cover bg-center bg-no-repeat flex items-center justify-center p-4 sm:p-6 md:p-8 select-none"
    style="background-image: url('../assets/img/layout.png'); background-opacity: 0.3;">

    <?php if (isset($_GET['from']) && $_GET['from'] === 'classificacao'): ?>
        <a href="classificacao.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
            <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
            <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
        </a>
    <?php endif; ?>

    <div class="flex flex-col items-center">
        <!-- <img src="../assets/img/logo1.png" class="w-[250px] h-auto xs:w-[280px] sm:w-[320px] md:w-[350px] lg:w-[400px] mt-[-150px]" alt="Logo"> -->

        <div class="w-full max-w-xl h-full bg-white rounded-xl shadow-2xl overflow-hidden ">
            <div class="bg-[#007A33] p-4 sm:p-6">
                <h2 class="text-xl sm:text-2xl font-bold text-white text-center">
                    <i class="fas fa-book-open mr-2"></i>Cadastro de Subgêneros Bibliográficos
                </h2>
            </div>

            <form id="genreForm" action="../controllers/main_controller.php" method="post" class="p-4 sm:p-6 space-y-4">
                <div class="relative">
                    <div class="relative group">
                        <i class="fas fa-book-open absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-[#007A33] transition-colors duration-200"></i>
                        <select id="nomeGenero" name="genero"
                            class="w-full pl-10 pr-3 py-2.5 border-2 border-gray-200 text-gray-400 rounded-lg focus:border-[#007A33] focus:ring focus:ring-[#007A33]/20 focus:outline-none appearance-none bg-white hover:border-gray-300 transition-all duration-200 cursor-pointer shadow-sm"
                            required>
                            <option value="" disabled selected>Gênero</option>
                            <?php
                            $generos = $select_model->select_genero();
                            foreach ($generos as $genero) {
                            ?>
                                <option value="<?= $genero['generos'] ?>"><?= $genero['generos'] ?></option>
                            <?php } ?>

                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="relative group">
                        <i class="fas fa-book-open absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-[#007A33] transition-colors duration-200"></i>
                        <input type="text" id="subgenero" name="subgenero"
                            class="w-full pl-10 pr-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-[#007A33] focus:ring focus:ring-[#007A33]/20 focus:outline-none hover:border-gray-300 transition-all duration-200 shadow-sm text-gray-500"
                            placeholder="Digite o novo subgênero" required>
                    </div>
                </div>


                <div class="mt-4 sm:mt-6">
                    <button type="submit"
                        class="w-full card-hover bg-[#FFA500] hover:bg-[#FFB74D] text-white font-medium py-2.5 sm:py-3 px-4 rounded-lg transition duration-300 ease-in-out flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Cadastrar
                    </button>
                </div>

                <div class="mt-2 space-y-2">
                    <?php if (isset($_GET['true'])) { ?>
                        <p class="text-green-600 bg-green-100 border border-green-300 rounded-md p-2 text-center font-medium text-sm sm:text-base"> Subgênero cadastrado com sucesso!</p>
                    <?php } ?>
                    <?php if (isset($_GET['false'])) { ?>
                        <p class="text-red-600 bg-red-100 border border-red-300 rounded-md p-2 text-center font-medium text-sm sm:text-base">Erro ao cadastrar subgênero!</p>
                    <?php } ?>
                    <?php if (isset($_GET['ja_cadastrado'])) { ?>
                        <p class="text-yellow-600 bg-yellow-100 border border-yellow-300 rounded-md p-2 text-center font-medium text-sm sm:text-base">Subgênero já cadastrado!</p>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>

    <style>
        .card-hover {
            transition: transform 0.2s ease-in-out;
        }

        .card-hover:hover {
            transform: translateY(-2px);
        }
    </style>

    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.querySelector('form'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/classificacao.php':?>
<?php
require_once $embeddedDirectory . '/../models/select_model.php';
$selectModel = new select_model();

// Recuperar dados de categorias, gêneros e subgêneros
$categorias = $selectModel->select_categoria();
$generos = $selectModel->select_genero();
$subgeneros = $selectModel->select_subgenero();

// Contar totais
$totalCategorias = count($categorias);
$totalGeneros = count($generos);
$totalSubgeneros = count($subgeneros);
$totalItems = $totalCategorias + $totalGeneros + $totalSubgeneros;

// Cores para os cards
$colors = ['#008C45', '#FFA500', '#FFD700', '#FF6B6B', '#4ECDC4'];

// Construir hierarquia
$hierarchy = [];
foreach ($categorias as $categoria) {
    $cat_id = $categoria['id'];
    $hierarchy[$cat_id] = [
        'categoria' => $categoria['categorias'],
        'generos' => []
    ];
}

foreach ($generos as $genero) {
    $id_cat = $genero['id_categoria'] ?? null;
    if ($id_cat && isset($hierarchy[$id_cat])) {
        $hierarchy[$id_cat]['generos'][$genero['id']] = [
            'genero' => $genero['generos'],
            'subgeneros' => []
        ];
    }
}

foreach ($subgeneros as $subgenero) {
    $id_genero = $subgenero['id_genero'] ?? null;
    foreach ($hierarchy as &$cat_data) {
        if (isset($cat_data['generos'][$id_genero])) {
            $cat_data['generos'][$id_genero]['subgeneros'][] = $subgenero;
        }
    }
}

// Reordenar categorias para a ordem específica: Geral, Técnico, Didático, Paradidático
function __normalize_str($s) {
    if ($s === null) return '';
    $s = trim((string) $s);
    // tenta remover acentos para comparação mais confiável
    $trans = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    if ($trans !== false) $s = $trans;
    return mb_strtolower($s, 'UTF-8');
}

$desiredOrder = ['Geral', 'Técnico', 'Didático', 'Paradidático'];
$orderedHierarchy = [];

// adicionar as categorias na ordem desejada se existirem
foreach ($desiredOrder as $desiredName) {
    $normDesired = __normalize_str($desiredName);
    foreach ($hierarchy as $id => $data) {
        if (__normalize_str($data['categoria']) === $normDesired) {
            $orderedHierarchy[$id] = $data;
            unset($hierarchy[$id]);
            break;
        }
    }
}

// anexar o restante das categorias na ordem original
foreach ($hierarchy as $id => $data) {
    $orderedHierarchy[$id] = $data;
}

$hierarchy = $orderedHierarchy;

$months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$currentDate = date('j') . ' de ' . $months[intval(date('n')) - 1] . ' de ' . date('Y');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="./assets/img/icon.png" type="image/x-icon">
    <title>Classificação - Sistema de Biblioteca</title>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#008C45',
                        'ceara-orange': '#FFA500',
                        'ceara-white': '#FFFFFF',
                        primary: '#4CAF50',
                        secondary: '#FFB74D',
                    },
                },
            },
        };
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Anton&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        :root {
            --bg-principal: #f8fafc;
            --azul-escuro: #0f172a;
            --texto-claro: #f8fafc;
            --texto-escuro: #1e293b;
            --texto-mutado: #64748b;
            --branco: #ffffff;
            --borda: #e2e8f0;
            --borda-clara: #bfdbd0;
            --sucesso: #4DA73A;
            --sucesso-claro: #F8FCF9;
        }

        .gradient-text {
            background: linear-gradient(45deg, #008C45, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-principal);
            color: var(--texto-escuro);
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .card-indicador {
            background-color: var(--branco);
            border: 1px solid var(--borda);
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .card-indicador.animate-panel {
            opacity: 0;
            transform: translateY(48px);
        }

        .card-indicador:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .card-header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--texto-mutado);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background-color: rgba(0, 140, 69, 0.1);
            color: #008C45;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .card-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card-footer {
            font-size: 12px;
            color: var(--texto-mutado);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .painel-principal {
            background-color: var(--branco);
            border: 1px solid var(--borda);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .painel-principal.animate-panel {
            opacity: 0;
            transform: translateY(48px);
        }

        .painel-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--azul-escuro);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .show {
            opacity: 1 !important;
            transform: translateY(0) !important;
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .hierarchy-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            align-items: start;
        }

        .hierarchy-card {
            display: flex;
            flex-direction: column;
            min-height: none;
            max-height: 500px;
            overflow: hidden;
        }

        .hierarchy-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 14px 16px;
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.2s ease;
            margin-bottom: 14px;
            border: 1px solid #e2e8f0;
        }

        .hierarchy-card-header.category-card-header {
            background: #ffffff;
            border-color: #dbe2ea;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .hierarchy-card-header:hover {
            border-color: #cbd5e1;
        }

        .categoria-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-weight: 700;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
        }

        .categoria-name {
            font-weight: 600;
            font-size: 15px;
            color: #1e293b;
            flex: 1;
        }

        .categoria-label {
            font-size: 12px;
            color: #94a3b8;
            margin-left: auto;
            font-weight: 500;
            white-space: nowrap;
        }

        .hierarchy-content {
            flex: 1;
            overflow-y: auto;
            padding-right: 4px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .hierarchy-content::-webkit-scrollbar {
            width: 6px;
        }

        .hierarchy-content::-webkit-scrollbar-thumb {
            background: rgba(0, 140, 69, 0.25);
            border-radius: 999px;
        }

        .generos-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-left: 16px;
            padding-left: 12px;
            border-left: 2px solid rgba(0, 140, 69, 0.16);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .genero-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #fcfdff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
            min-width: 0;
        }

        .genero-item:hover {
            background: #f8fafc;
            border-color: #008C45;
        }

        .genero-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            font-weight: 700;
            color: white;
            font-size: 12px;
            flex-shrink: 0;
        }

        .genero-name {
            font-weight: 500;
            font-size: 14px;
            color: #1e293b;
            flex: 1;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .genero-label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }

        .expand-btn {
            cursor: pointer;
            color: #94a3b8;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .expand-btn.expanded {
            color: #008C45;
            transform: rotate(90deg);
        }

        /* Animação de painel para elementos com classe Tailwind */
        .animate-panel {
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
    </style>
</head>

<body class="pr-0 lg:ml-0 xl:ml-6 2xl:pl-6 2xl:pt-6 lg:px-6">

    <div class="dashboard-header flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 px-6 sm:px-0 pt-24 lg:pt-4 md:pt-12 pb-2 sm:mr-0">

        <section class="flex-1 flex flex-col items-center lg:items-start text-center lg:text-start gap-6">
            <section class="pt-24 lg:pt-0 items-center text-center">
                <div class="space-y-4 flex flex-col items-center lg:items-start text-center lg:text-start animate-panel transform translate-y-12 opacity-0">
                    <h2
                        class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold gradient-text"
                        style="font-family: 'Anton', sans-serif;"
                    >
                        Gerenciamento de Classificação
                    </h2>
                    <p
                        class="text-sm sm:text-lg text-gray-600 max-w-2xl"
                        style="font-family: 'Poppins', sans-serif;"
                    >
                        Consulte todas categorias, gêneros e subgêneros do sistema.
                    </p>
                </div>
            </section>
        </section>

        <div class="flex justify-start lg:justify-end">
            <div class="date flex items-center gap-3 text-sm sm:text-base border border-[var(--borda-clara)] px-4 py-2 rounded-lg animate-panel transform translate-y-12 opacity-0 bg-[var(--sucesso-claro)] shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 64 64"
                    fill="none"
                    stroke="#4DA73A"
                    stroke-width="4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="w-6 h-6"
                >
                    <rect x="10" y="14" width="44" height="40" rx="6"/>
                    <path d="M10 24H54"/>
                    <path d="M20 10V18"/>
                    <path d="M44 10V18"/>
                    <circle cx="20" cy="34" r="2"/>
                    <circle cx="32" cy="34" r="2"/>
                    <circle cx="44" cy="34" r="2"/>
                    <circle cx="20" cy="44" r="2"/>
                    <circle cx="32" cy="44" r="2"/>
                    <circle cx="44" cy="44" r="2"/>
                </svg>
                <span class="font-medium text-gray-500">
                    <?php echo $currentDate; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="cards-grid grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4 px-6">

        <div class="card-indicador animate-panel">
            <div class="card-header-box">
                <span class="card-title">Categorias</span>
                <div class="card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
            <div class="card-value text-ceara-green counter" data-target="<?php echo $totalCategorias; ?>">0</div>
            <span class="card-footer">
                <i class="fas fa-layer-group text-xs text-ceara-green"></i>
                <span>Nível 1</span>
            </span>
        </div>

        <div class="card-indicador animate-panel">
            <div class="card-header-box">
                <span class="card-title">Gêneros</span>
                <div class="card-icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
            <div class="card-value text-ceara-green counter" data-target="<?php echo $totalGeneros; ?>">0</div>
            <span class="card-footer">
                <i class="fas fa-tags text-xs text-ceara-green"></i>
                <span>Nível 2</span>
            </span>
        </div>

        <div class="card-indicador animate-panel">
            <div class="card-header-box">
                <span class="card-title">Subgêneros</span>
                <div class="card-icon">
                    <i class="fas fa-list-ul"></i>
                </div>
            </div>
            <div class="card-value text-ceara-green counter" data-target="<?php echo $totalSubgeneros; ?>">0</div>
            <span class="card-footer">
                <i class="fas fa-list-ul text-xs text-ceara-green"></i>
                <span>Nível 3</span>
            </span>
        </div>

        <div class="card-indicador animate-panel">
            <div class="card-header-box">
                <span class="card-title">Total de Itens</span>
                <div class="card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
            <div class="card-value text-ceara-green counter" data-target="<?php echo $totalItems; ?>">0</div>
            <span class="card-footer">
                <i class="fas fa-layer-group text-xs text-ceara-green"></i>
                <span>Toda a estrutura</span>
            </span>
        </div>
    </div>

    <!-- Estrutura de Classificação -->
    <div class="px-6">
        <!-- <div class="painel-principal animate-panel"> -->
            <!-- <div class="painel-title animate-panel">
                <span>
                    <i class="fas fa-sitemap text-ceara-green mr-3"></i>
                    Estrutura de Classificação
                </span>
                <button onclick="expandirTodos()" class="text-sm px-4 py-2 bg-ceara-green text-white rounded-lg hover:bg-green-700 transition">
                    Expandir todos
                </button>
            </div> -->

            <div class="hierarchy-grid">
                <?php
                $colorIndex = 0;
                foreach ($hierarchy as $cat_id => $cat_data):
                    $catColor = $colors[$colorIndex % count($colors)];
                    $generoCount = count($cat_data['generos']);
                ?>
                    <div class="painel-principal hierarchy-card animate-panel">
                        <div class="hierarchy-card-header category-card-header">
                            <div class="categoria-badge" style="background: <?php echo $catColor; ?>">
                                <?php echo $colorIndex + 1; ?>
                            </div>
                            <span class="categoria-name">
                                <i class="fas fa-folder mr-2" style="color: <?php echo $catColor; ?>"></i>
                                <?php echo htmlspecialchars($cat_data['categoria']); ?>
                            </span>
                            <span class="categoria-label">Categoria</span>
                            <!-- <span class="expand-btn expanded">
                                <i class="fas fa-chevron-right"></i>
                            </span> -->
                        </div>

                        <div class="hierarchy-content" style="display: flex;">
                            <div class="generos-container">
                                <?php
                                $generoIndex = 0;
                                foreach ($cat_data['generos'] as $gen_id => $gen_data):
                                    $subgeneroCount = count($gen_data['subgeneros']);
                                    $generoColor = $catColor;
                                ?>
                                    <div class="genero-item">
                                        <div class="genero-badge" style="background: <?php echo $generoColor; ?>">
                                            <?php echo $colorIndex + 1; ?>.<?php echo $generoIndex + 1; ?>
                                        </div>
                                        <span class="genero-name">
                                            <i class="fas fa-bookmark mr-2" style="color: <?php echo $generoColor; ?>"></i>
                                            <?php echo htmlspecialchars($gen_data['genero']); ?>
                                        </span>
                                        <span class="genero-label">Gênero</span>
                                        <span class="expand-btn" onclick="event.stopPropagation(); toggleSubgeneros(this)">
                                            <i class="fas fa-chevron-down"></i>
                                        </span>
                                    </div>

                                    <?php if ($subgeneroCount > 0): ?>
                                        <div class="subgeneros-list" style="display: block; margin-left: 16px; margin-top: 2px; border-left: 2px solid <?php echo $generoColor; ?>; padding-left: 12px;">
                                            <?php foreach ($gen_data['subgeneros'] as $subgen): ?>
                                                <div class="text-sm px-3 py-2 bg-gray-50 rounded-md mb-2 border border-gray-100">
                                                    <i class="fas fa-tag text-xs" style="color: <?php echo $generoColor; ?>; margin-right: 8px;"></i>
                                                    <span class="text-gray-700">
                                                        <?php echo htmlspecialchars($subgen['subgenero']); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                <?php
                                    $generoIndex++;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php
                    $colorIndex++;
                endforeach;
                ?>
            </div>
        <!-- </div> -->
    </div>

    <script>
        function toggleGeneros(header) {
            const container = header.nextElementSibling;
            const expandBtn = header.querySelector('.expand-btn');

            if (container.style.display === 'none') {
                container.style.display = 'flex';
                expandBtn.classList.add('expanded');
            } else {
                container.style.display = 'none';
                expandBtn.classList.remove('expanded');
            }
        }

        function toggleSubgeneros(btn) {
            const generoItem = btn.closest('.genero-item');
            const subgenList = generoItem.nextElementSibling;

            if (subgenList && subgenList.classList.contains('subgeneros-list')) {
                if (subgenList.style.display === 'none') {
                    subgenList.style.display = 'block';
                    btn.classList.add('expanded');
                } else {
                    subgenList.style.display = 'none';
                    btn.classList.remove('expanded');
                }
            }
        }

        // function expandirTodos() {
        //     const headers = document.querySelectorAll('.categoria-header');
        //     headers.forEach(header => {
        //         const container = header.nextElementSibling;
        //         container.style.display = 'flex';
        //         header.querySelector('.expand-btn').classList.add('expanded');

        //         // Expandir subgêneros também
        //         const generoItems = container.querySelectorAll('.genero-item');
        //         generoItems.forEach(item => {
        //             const subgenList = item.nextElementSibling;
        //             if (subgenList && subgenList.classList.contains('subgeneros-list')) {
        //                 subgenList.style.display = 'block';
        //                 item.querySelector('.expand-btn').classList.add('expanded');
        //             }
        //         });
        //     });
        // }

        function animateCounters() {
            document.querySelectorAll('.counter').forEach(counter => {
                const target = +counter.dataset.target;
                if (target === 0) {
                    counter.textContent = '0';
                    return;
                }
                let start = 0;
                const duration = 1000;
                const stepTime = Math.max(Math.floor(duration / Math.max(target, 1)), 15);

                const updateCount = () => {
                    start += Math.ceil(target / (duration / stepTime));
                    if (start >= target) {
                        counter.textContent = target.toLocaleString('pt-BR');
                        return;
                    }
                    counter.textContent = start.toLocaleString('pt-BR');
                    requestAnimationFrame(updateCount);
                };

                requestAnimationFrame(updateCount);
            });
        }

        function revealPanels() {
            document.querySelectorAll('.animate-panel').forEach((panel, index) => {
                setTimeout(() => {
                    panel.classList.add('show');
                }, 100 + index * 60);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            revealPanels();
            animateCounters();

            document.querySelectorAll('.hierarchy-content').forEach(content => {
                content.style.display = 'flex';
            });

            document.querySelectorAll('.subgeneros-list').forEach(list => {
                list.style.display = 'block';
            });

            document.querySelectorAll('.hierarchy-card-header .expand-btn').forEach(btn => {
                btn.classList.add('expanded');
            });

            document.querySelectorAll('.genero-item .expand-btn').forEach(btn => {
                btn.classList.add('expanded');
            });
        });
    </script>

</body>

</html>
<?php
                break;
            case 'views/consultar_livro.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select = new select_model();
$livros = $select->select_nome_livro();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Livro - Biblioteca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#007A33',
                        'ceara-green-dark': '#005F27',
                        'ceara-orange': '#FFA500',
                        'ceara-orange-dark': '#FF8C00',
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(0, 122, 51, 0.1), 0 8px 10px -6px rgba(0, 122, 51, 0.1)',
                    },
                    backgroundImage: {
                        'gradient-ceara': 'linear-gradient(to right, #007A33, #005F27)',
                        'gradient-orange': 'linear-gradient(to right, #FFA500, #FF8C00)',
                    }
                }
            }
        }
    </script>
    <style>
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #007A33 !important;
            border-color: #007A33 !important;
        }

        input[type="checkbox"]:checked:focus,
        input[type="radio"]:checked:focus {
            --tw-ring-color: rgba(0, 122, 51, 0.2);
        }

        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        input[type="radio"]:checked {
            border-color: #007A33;
            background-color: #007A33;
            box-shadow: inset 0 0 0 4px #fff;
        }

        input[type="radio"]:focus {
            box-shadow: 0 0 0 3px rgba(0, 122, 51, 0.2);
        }

        .tab-button.active {
            color: #007A33 !important;
            border-bottom: 2px solid #007A33 !important;
        }

        input[type="checkbox"] {
            accent-color: #007A33;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            background-color: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            min-height: 48px;
            padding: 4px;
            transition: all 0.2s ease-in-out;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #007A33;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4b5563;
            line-height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007A33;
            color: white;
        }

        .select2-dropdown {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 8px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #007A33;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e6f3ed;
            color: #007A33;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-12 select-none"
  style="background-image: url('../assets/img/layout.png'); background-size: cover; background-attachment: fixed;">

  <?php if (isset($_GET['from']) && $_GET['from'] === 'gerenciar_acervo'): ?>
    <a href="gerenciar_acervo.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
        <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
        <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
    </a>
  <?php endif; ?>

  <div class="w-full max-w-4xl">
        <!-- <img src="../assets/img/logo1.png"
            class="w-[200px] sm:w-[250px] md:w-[300px] mx-auto mb-8 drop-shadow-xl hover:scale-105 transition-transform duration-300"
            alt="Logo"> -->
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-ceara p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white text-center tracking-wide">
                    <i class="fas fa-search mr-3"></i>Consultar Livro
                </h2>
            </div>

    <div class="bg-white rounded-xl shadow p-6">
      <div class="mb-6">
        <div class="mb-8">
            <div class="flex border-b border-gray-200">
                <button type="button"
                    class="tab-button py-2 px-4 font-medium text-ceara-green border-b-2 border-ceara-green cursor-default active"
                    data-tab="search-book">Buscar Livro</button>
            </div>
        </div>
        <label for="searchBook" class="block text-base font-medium text-gray-700 mb-2">Buscar Livro</label>
        <select id="bookSelect" class="js-example-basic-single w-full" name="id_livro">
          <option value="" disabled selected>Digite o título do livro...</option>
          <?php foreach ($livros as $livro): ?>
            <option value="<?= htmlspecialchars($livro['id'], ENT_QUOTES) ?>"
              data-id="<?= htmlspecialchars($livro['id'], ENT_QUOTES) ?>"
              data-titulo="<?= htmlspecialchars($livro['titulo_livro'] ?? '', ENT_QUOTES) ?>"
              data-autores="<?= htmlspecialchars($livro['autores'] ?? '', ENT_QUOTES) ?>"
              data-edicao="<?= htmlspecialchars($livro['edicao'] ?? '', ENT_QUOTES) ?>"
              data-editora="<?= htmlspecialchars($livro['editora'] ?? '', ENT_QUOTES) ?>"
              data-quantidade="<?= htmlspecialchars($livro['quantidade'] ?? '', ENT_QUOTES) ?>"
              data-ano_publicacao="<?= htmlspecialchars($livro['ano_publicacao'] ?? '', ENT_QUOTES) ?>"
              data-genero="<?= htmlspecialchars($livro['generos'] ?? '', ENT_QUOTES) ?>"
              data-subgenero="<?= htmlspecialchars($livro['subgenero'] ?? '', ENT_QUOTES) ?>"
              data-estante="<?= htmlspecialchars($livro['estantes'] ?? '', ENT_QUOTES) ?>"
              data-prateleira="<?= htmlspecialchars($livro['prateleiras'] ?? '', ENT_QUOTES) ?>"
              data-corredor="<?= htmlspecialchars($livro['corredor'] ?? '', ENT_QUOTES) ?>"
              data-categoria="<?= htmlspecialchars($livro['categoria'] ?? '', ENT_QUOTES) ?>"
              data-ficcao="<?= htmlspecialchars($livro['ficcao'] ?? '', ENT_QUOTES) ?>"
              data-brasileira="<?= htmlspecialchars($livro['brasileira'] ?? '', ENT_QUOTES) ?>"
              data-cativo="<?= htmlspecialchars($livro['cativo'] ?? '', ENT_QUOTES) ?>"
              data-local="Estante <?= htmlspecialchars($livro['estantes'] ?? '', ENT_QUOTES) ?> - Prateleira <?= htmlspecialchars($livro['prateleiras'] ?? '', ENT_QUOTES) ?>"
            ><?= htmlspecialchars($livro['titulo_livro'] . ' — ' . ($livro['autores'] ?? ''), ENT_QUOTES) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="bookInfo" class="hidden mt-8 space-y-8">
        <div class="bg-gray-50 rounded-2xl p-8 border border-gray-200 shadow-inner">
          <h3 class="text-xl font-bold text-ceara-green mb-6 flex items-center gap-2"><i class="fas fa-id-card-alt"></i> Informações do Livro</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label class="block text-base font-medium text-gray-700 mb-2">Título</label>
              <p id="showTitulo" class="text-lg font-semibold text-gray-800 font-medium"></p>
            </div>
            <div>
              <label class="block text-base font-medium text-gray-700 mb-2">Autores</label>
              <p id="showAutores" class="text-lg font-semibold text-gray-800 font-medium"></p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label class="block text-base font-medium text-gray-700 mb-2">Editora</label>
              <p id="showEditora" class="text-lg font-semibold text-gray-800 font-medium"></p>
            </div>
            <div class="grid grid-cols-2 gap-6">
              <div>
                <label class="block text-base font-medium text-gray-700 mb-2">Edição</label>
                <p id="showEdicao" class="text-lg font-semibold text-gray-800 font-medium"></p>
              </div>
              <div>
                <label class="block text-base font-medium text-gray-700 mb-2">ID</label>
                <p id="showId" class="text-lg font-semibold text-gray-800 font-medium"></p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label class="block text-base font-medium text-gray-700 mb-2">Ano de Publicação</label>
              <p id="showAno" class="text-lg font-semibold text-gray-800 font-medium"></p>
            </div>
            <div class="grid grid-cols-2 gap-6">
              <div>
                <label class="block text-base font-medium text-gray-700 mb-2">Estante</label>
                <p id="showEstante" class="text-lg font-semibold text-gray-800 font-medium"></p>
              </div>
              <div>
                <label class="block text-base font-medium text-gray-700 mb-2">Prateleira</label>
                <p id="showPrateleira" class="text-lg font-semibold text-gray-800 font-medium"></p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label class="block text-base font-medium text-gray-700 mb-2">Quantidade</label>
              <p id="showQuantidade" class="text-lg font-semibold text-gray-800 font-medium"></p>
            </div>
            <div>
              <label class="block text-base font-medium text-gray-700 mb-2">Gênero / Subgênero</label>
              <p class="text-lg font-semibold text-gray-800 font-medium"><span id="showGenero"></span> <span id="showSubgenero" class="text-gray-600"></span></p>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6 mb-6">
            <div>
              <label class="block text-base font-medium text-gray-700 mb-2">Corredor</label>
              <p id="showCorredor" class="text-lg font-semibold text-gray-800 font-medium"></p>
            </div>
          </div>

          <div class="flex flex-wrap gap-3 mb-4">
            <span id="badgeCategoria" class="px-3 py-1 rounded-full text-sm font-medium bg-gray-300 text-gray-700 hidden"></span>
            <span id="badgeFiccao" class="px-3 py-1 rounded-full text-sm font-medium bg-gray-300 text-gray-700 hidden"></span>
            <span id="badgeLiteratura" class="px-3 py-1 rounded-full text-sm font-medium bg-gray-300 text-gray-700 hidden"></span>
            <span id="badgeCativo" class="px-3 py-1 rounded-full text-sm font-medium bg-gray-300 text-gray-700 hidden"></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $('.js-example-basic-single').select2({ placeholder: 'Digite o título do livro...' });

      $('#bookSelect').on('select2:select', function(e) {
        const el = e.params.data.element;
        if (!el) return;
        const dataset = el.dataset;
        $('#showTitulo').text(dataset.titulo || '—');
        $('#showAutores').text(dataset.autores || '—');
        $('#showEdicao').text(dataset.edicao || '—');
        $('#showId').text(dataset.id || '—');
        $('#showAno').text(dataset.ano_publicacao || '—');
        $('#showEditora').text(dataset.editora || '—');
        $('#showQuantidade').text(dataset.quantidade || '—');
        $('#showEstante').text(dataset.estante ? ('Estante ' + dataset.estante) : '—');
        $('#showPrateleira').text(dataset.prateleira ? ('Prateleira ' + dataset.prateleira) : '—');
        $('#showGenero').text(dataset.genero || '—');
        $('#showSubgenero').text(dataset.subgenero ? ('— ' + dataset.subgenero) : '');
        $('#showCorredor').text(dataset.corredor || '—');
        $('#showLocal').text(dataset.local || '—');

        // badges: Categoria, Ficcao, Literatura (Brasileira), Cativo
        const badgeT = $('#badgeCategoria');
        const badgeTLabel = {
          geral: 'Geral',
          didatico: 'Didático',
          paradidatico: 'Paradidático',
          tecnico: 'Técnico'
        };
        const badgeF = $('#badgeFiccao');
        const badgeL = $('#badgeLiteratura');
        const badgeC = $('#badgeCativo');
        if (dataset.categoria === 'Geral') {
          badgeT.text('Geral').removeClass('hidden');
        } else {
          badgeT.text(badgeTLabel[dataset.categoria]).removeClass('bg-gray-300 text-gray-700 hidden').addClass('bg-ceara-green text-white');;
        }

        if (dataset.ficcao === '1') {
          badgeF.text('Ficção').removeClass('bg-gray-300 text-gray-700 hidden').addClass('bg-ceara-green text-white');
        } else if (dataset.ficcao === '0') {
          badgeF.text('Não Ficção').removeClass('bg-ceara-green text-white hidden').addClass('bg-gray-300 text-gray-700');
        } else {
          badgeF.addClass('hidden');
        }

        if (dataset.brasileira === '1') {
          badgeL.text('Brasileira').removeClass('bg-gray-300 text-gray-700 hidden').addClass('bg-ceara-green text-white');
        } else if (dataset.brasileira === '0') {
          badgeL.text('Estrangeira').removeClass('bg-ceara-green text-white hidden').addClass('bg-gray-300 text-gray-700');
        } else {
          badgeL.addClass('hidden');
        }

        if (dataset.cativo === '1') {
          badgeC.text('Cativo').removeClass('bg-gray-300 text-gray-700 hidden').addClass('bg-ceara-green text-white');
        } else if (dataset.cativo === '0') {
          badgeC.text('Não Cativo').removeClass('bg-ceara-green text-white hidden').addClass('bg-gray-300 text-gray-700');
        } else {
          badgeC.addClass('hidden');
        }

        // $('#btnQR').attr('href', './QRCode/geradorQR_especifico_livro.php');
        // $('#btnAbrir').attr('href', './emprestimo/decisao.php?id_livro=' + $(this).val());

        $('#bookInfo').removeClass('hidden');
      });

      // limpando a seleção
      $('#bookSelect').on('select2:clear', function(){ $('#bookInfo').addClass('hidden'); });
    });
  </script>

</body>
</html>
<?php
                break;
            case 'views/editar_autor.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Formulário Biblioteca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#007A33',
                        'ceara-green-dark': '#005F27',
                        'ceara-orange': '#FFA500',
                        'ceara-orange-dark': '#FF8C00',
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(0, 122, 51, 0.1), 0 8px 10px -6px rgba(0, 122, 51, 0.1)',
                    },
                    backgroundImage: {
                        'gradient-ceara': 'linear-gradient(to right, #007A33, #005F27)',
                        'gradient-orange': 'linear-gradient(to right, #FFA500, #FF8C00)',
                    }
                }
            }
        }
    </script>
    <style>
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #007A33 !important;
            border-color: #007A33 !important;
        }

        input[type="checkbox"]:checked:focus,
        input[type="radio"]:checked:focus {
            --tw-ring-color: rgba(0, 122, 51, 0.2);
        }

        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        input[type="radio"]:checked {
            border-color: #007A33;
            background-color: #007A33;
            box-shadow: inset 0 0 0 4px #fff;
        }

        input[type="radio"]:focus {
            box-shadow: 0 0 0 3px rgba(0, 122, 51, 0.2);
        }

        .tab-button.active {
            color: #007A33 !important;
            border-bottom: 2px solid #007A33 !important;
        }

        input[type="checkbox"] {
            accent-color: #007A33;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            background-color: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            min-height: 48px;
            padding: 4px;
            transition: all 0.2s ease-in-out;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #007A33;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4b5563;
            line-height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007A33;
            color: white;
        }

        .select2-dropdown {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 8px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #007A33;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e6f3ed;
            color: #007A33;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-12 select-none"
    style="background-image: url('../assets/img/layout.png'); background-size: cover; background-attachment: fixed;">

    <div class="w-full max-w-4xl">
        <!-- <img src="../assets/img/logo1.png"
            class="w-[200px] sm:w-[250px] md:w-[300px] mx-auto mb-8 drop-shadow-xl hover:scale-105 transition-transform duration-300"
            alt="Logo"> -->
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-ceara p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white text-center tracking-wide">
                    <i class="fas fa-user-edit mr-3"></i>Editar Autor
                </h2>
            </div>

            <form id="editAuthorForm" action="../controllers/excluir_editar_livro.php" method="post" class="p-6 sm:p-8">
                <div class="mb-8">
                    <div class="flex border-b border-gray-200">
                        <button type="button"
                            class="tab-button py-2 px-4 font-medium text-ceara-green border-b-2 border-ceara-green cursor-default active"
                            data-tab="search-author">Buscar Autor</button>
                    </div>
                </div>

                <div id="validationMessage" class="hidden"></div>

                <div id="search-author" class="tab-content space-y-6">
                    <div class="space-y-6">
                        <!-- Campo de Busca -->
                        <div class="relative">
                            <label for="searchAuthor" class="block text-sm font-medium text-gray-700 mb-1">Buscar Autor</label>
                            <div class="relative">
                                <select class="js-example-basic-single" name="id_autor">
                                    <option value="" selected disabled>Selecione um autor para editar</option>
                                    <?php
                                    $autores = $select_model->select_nome_autor();
                                    foreach ($autores as $autor) { ?>
                                        <option value="<?= $autor['id'] ?>"
                                            data-nome="<?= $autor['nome_autor'] ?>">
                                            <?= $autor['nome_autor'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Formulário de Edição -->
                        <div id="editForm" class="hidden mt-6 space-y-6">
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Informações do Autor</h3>

                                <div class="mb-4">
                                    <label for="editNome" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                                    <input type="text" id="editNome" name="nome" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200" required>
                                </div>

                                <!-- Botões de Ação -->
                                <div class="flex space-x-4">
                                    <button type="submit" name="action" value="edit"
                                        class="flex-1 bg-ceara-green hover:bg-ceara-green-dark text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-save mr-3"></i>
                                        Salvar Alterações
                                    </button>
                                    <button type="button" id="cancelEdit"
                                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-times mr-3"></i>
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botão de Exclusão -->
                        <!-- <button type="submit" name="action" value="delete"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                            <i class="fas fa-trash-alt mr-3"></i>
                            Excluir Autor
                        </button> -->
                    </div>

                    <?php if (isset($_GET['true'])): ?>
                        <div class="flex items-center p-4 mb-4 text-green-800 border-l-4 border-green-500 bg-green-50 rounded-md"
                            role="alert">
                            <i class="fas fa-check-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Autor editado com sucesso!</span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md"
                            role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">ERRO ao editar autor!</span>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validationMessage = document.getElementById('validationMessage');
            const editForm = document.getElementById('editForm');
            const cancelEditBtn = document.getElementById('cancelEdit');

            // Initialize Select2 with custom configuration
            $('.js-example-basic-single').select2({
                placeholder: "Digite o nome do autor...",
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Nenhum autor encontrado";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            }).on('select2:select', function(e) {
                const selectedOption = e.params.data.element;
                if (selectedOption) {
                    // Update form fields with selected author data
                    document.getElementById('editNome').value = selectedOption.dataset.nome;
                    editForm.classList.remove('hidden');
                    validationMessage.classList.add('hidden');
                }
            }).on('select2:clear', function() {
                editForm.classList.add('hidden');
                document.getElementById('editNome').value = '';
            });

            // Handle cancel button
            cancelEditBtn.addEventListener('click', function() {
                editForm.classList.add('hidden');
                $('.js-example-basic-single').val('').trigger('change');
                validationMessage.classList.add('hidden');
            });

            // Form validation
            document.getElementById('editAuthorForm').addEventListener('submit', function(e) {
                const selectedAuthor = $('.js-example-basic-single').val();
                if (!selectedAuthor) {
                    e.preventDefault();
                    validationMessage.innerHTML = `
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Por favor, selecione um autor para editar!</span>
                        </div>
                    `;
                    validationMessage.classList.remove('hidden');
                }
            });
        });
    </script>
    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.getElementById('editAuthorForm'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/editar_categoria.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();
$categorias = $select_model->select_categoria();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Formulário Biblioteca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#007A33',
                        'ceara-green-dark': '#005F27',
                        'ceara-orange': '#FFA500',
                        'ceara-orange-dark': '#FF8C00',
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(0, 122, 51, 0.1), 0 8px 10px -6px rgba(0, 122, 51, 0.1)',
                    },
                    backgroundImage: {
                        'gradient-ceara': 'linear-gradient(to right, #007A33, #005F27)',
                        'gradient-orange': 'linear-gradient(to right, #FFA500, #FF8C00)',
                    }
                }
            }
        }
    </script>
    <style>
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #007A33 !important;
            border-color: #007A33 !important;
        }

        input[type="checkbox"]:checked:focus,
        input[type="radio"]:checked:focus {
            --tw-ring-color: rgba(0, 122, 51, 0.2);
        }

        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        input[type="radio"]:checked {
            border-color: #007A33;
            background-color: #007A33;
            box-shadow: inset 0 0 0 4px #fff;
        }

        input[type="radio"]:focus {
            box-shadow: 0 0 0 3px rgba(0, 122, 51, 0.2);
        }

        .tab-button.active {
            color: #007A33 !important;
            border-bottom: 2px solid #007A33 !important;
        }

        input[type="checkbox"] {
            accent-color: #007A33;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            background-color: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            min-height: 48px;
            padding: 4px;
            transition: all 0.2s ease-in-out;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #007A33;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4b5563;
            line-height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007A33;
            color: white;
        }

        .select2-dropdown {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 8px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #007A33;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e6f3ed;
            color: #007A33;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-12 select-none"
    style="background-image: url('../assets/img/layout.png'); background-size: cover; background-attachment: fixed;">

    <?php if (isset($_GET['from']) && $_GET['from'] === 'classificacao'): ?>
        <a href="classificacao.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
            <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
            <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
        </a>
    <?php endif; ?>

    <div class="w-full max-w-4xl">
        <!-- <img src="../assets/img/logo1.png"
            class="w-[200px] sm:w-[250px] md:w-[300px] mx-auto mb-8 drop-shadow-xl hover:scale-105 transition-transform duration-300"
            alt="Logo"> -->
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-ceara p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white text-center tracking-wide">
                    <i class="fas fa-edit mr-3"></i>Editar Categoria
                </h2>
            </div>

            <form id="editCategoriaForm" action="../controllers/excluir_editar_livro.php" method="post" class="p-6 sm:p-8">
                <div class="mb-8">
                    <div class="flex border-b border-gray-200">
                        <button type="button"
                            class="tab-button py-2 px-4 font-medium text-ceara-green border-b-2 border-ceara-green cursor-default active"
                            data-tab="search-categoria">Buscar Categoria</button>
                    </div>
                </div>

                <div id="validationMessage" class="hidden"></div>

                <div id="search-categoria" class="tab-content space-y-6">
                    <div class="space-y-6">
                        <div class="relative">
                            <label for="searchCategoria" class="block text-base font-medium text-gray-700 mb-2">Buscar Categoria</label>
                            <div class="relative">
                                <select class="js-example-basic-single border-2 border-gray-200 rounded-lg px-5 py-3 w-full focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none text-lg bg-white text-gray-700 transition-all duration-200" name="id_categoria" required>
                                    <option value="" selected disabled>Selecione uma categoria para editar</option>
                                    <?php
                                    foreach ($categorias as $categoria) { ?>
                                        <option value="<?= $categoria['id'] ?>"
                                            data-nome="<?= $categoria['categorias'] ?>">
                                            <?= $categoria['categorias'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Formulário de Edição -->
                        <div id="editForm" class="hidden mt-6 space-y-6">
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Categoria</h3>

                                <!-- Nome da Categoria -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="editCategoria" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                        <input type="text" id="editCategoria" name="nome" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200">
                                    </div>
                                </div>

                                <!-- Botões de Ação -->
                                <div class="flex space-x-4">
                                    <button type="submit" name="action" value="edit"
                                        class="flex-1 bg-ceara-green hover:bg-ceara-green-dark text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-save mr-3"></i>
                                        Salvar Alterações
                                    </button>
                                    <button type="button" id="cancelEdit"
                                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-times mr-3"></i>
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botão de Exclusão -->
                        <!-- <button type="submit" name="action" value="delete"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                            <i class="fas fa-trash-alt mr-3"></i>
                            Excluir Categoria
                        </button> -->
                    </div>

                    <?php if (isset($_GET['true'])): ?>
                        <div class="flex items-center p-4 mb-4 text-green-800 border-l-4 border-green-500 bg-green-50 rounded-md"
                            role="alert">
                            <i class="fas fa-check-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Categoria editada com sucesso!</span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md"
                            role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">ERRO ao editar categoria!</span>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validationMessage = document.getElementById('validationMessage');
            const editForm = document.getElementById('editForm');
            const cancelEditBtn = document.getElementById('cancelEdit');

            // Initialize Select2 with custom configuration
            $('.js-example-basic-single').select2({
                placeholder: "Digite o nome da categoria...",
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Nenhuma categoria encontrado";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            }).on('select2:select', function(e) {
                const selectedOption = e.params.data.element;
                if (selectedOption) {
                    // Update form fields with selected category data
                    document.getElementById('editCategoria').value = selectedOption.dataset.nome;

                    // Show the edit form
                    editForm.classList.remove('hidden');
                }
            });

            // Handle cancel button
            cancelEditBtn.addEventListener('click', function() {
                editForm.classList.add('hidden');
                $('.js-example-basic-single').val('').trigger('change');
            });

            // Form validation
            document.getElementById('editCategoriaForm').addEventListener('submit', function(e) {
                const selectedCategoria = $('.js-example-basic-single').val();
                if (!selectedCategoria) {
                    e.preventDefault();
                    validationMessage.innerHTML = `
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Por favor, selecione uma categoria para editar!</span>
                        </div>
                    `;
                    validationMessage.classList.remove('hidden');
                }
            });
        });
    </script>
    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.getElementById('editCategoriaForm'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/editar_genero.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();
$generos = $select_model->select_genero();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Formulário Biblioteca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#007A33',
                        'ceara-green-dark': '#005F27',
                        'ceara-orange': '#FFA500',
                        'ceara-orange-dark': '#FF8C00',
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(0, 122, 51, 0.1), 0 8px 10px -6px rgba(0, 122, 51, 0.1)',
                    },
                    backgroundImage: {
                        'gradient-ceara': 'linear-gradient(to right, #007A33, #005F27)',
                        'gradient-orange': 'linear-gradient(to right, #FFA500, #FF8C00)',
                    }
                }
            }
        }
    </script>
    <style>
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #007A33 !important;
            border-color: #007A33 !important;
        }

        input[type="checkbox"]:checked:focus,
        input[type="radio"]:checked:focus {
            --tw-ring-color: rgba(0, 122, 51, 0.2);
        }

        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        input[type="radio"]:checked {
            border-color: #007A33;
            background-color: #007A33;
            box-shadow: inset 0 0 0 4px #fff;
        }

        input[type="radio"]:focus {
            box-shadow: 0 0 0 3px rgba(0, 122, 51, 0.2);
        }

        .tab-button.active {
            color: #007A33 !important;
            border-bottom: 2px solid #007A33 !important;
        }

        input[type="checkbox"] {
            accent-color: #007A33;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            background-color: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            min-height: 48px;
            padding: 4px;
            transition: all 0.2s ease-in-out;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #007A33;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4b5563;
            line-height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007A33;
            color: white;
        }

        .select2-dropdown {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 8px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #007A33;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e6f3ed;
            color: #007A33;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-12 select-none"
    style="background-image: url('../assets/img/layout.png'); background-size: cover; background-attachment: fixed;">

    <?php if (isset($_GET['from']) && $_GET['from'] === 'classificacao'): ?>
        <a href="classificacao.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
            <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
            <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
        </a>
    <?php endif; ?>

    <div class="w-full max-w-4xl">
        <!-- <img src="../assets/img/logo1.png"
            class="w-[200px] sm:w-[250px] md:w-[300px] mx-auto mb-8 drop-shadow-xl hover:scale-105 transition-transform duration-300"
            alt="Logo"> -->
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-ceara p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white text-center tracking-wide">
                    <i class="fas fa-edit mr-3"></i>Editar Gênero
                </h2>
            </div>

            <form id="editGeneroForm" action="../controllers/excluir_editar_livro.php" method="post" class="p-6 sm:p-8">
                <div class="mb-8">
                    <div class="flex border-b border-gray-200">
                        <button type="button"
                            class="tab-button py-2 px-4 font-medium text-ceara-green border-b-2 border-ceara-green cursor-default active"
                            data-tab="search-genero">Buscar Gênero</button>
                    </div>
                </div>

                <div id="validationMessage" class="hidden"></div>

                <div id="search-genero" class="tab-content space-y-6">
                    <div class="space-y-6">
                        <div class="relative">
                            <label for="searchGenero" class="block text-base font-medium text-gray-700 mb-2">Buscar Gênero</label>
                            <div class="relative">
                                <select class="js-example-basic-single border-2 border-gray-200 rounded-lg px-5 py-3 w-full focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none text-lg bg-white text-gray-700 transition-all duration-200" name="id_genero" required>
                                    <option value="" selected disabled>Selecione um gênero para editar</option>
                                    <?php
                                    foreach ($generos as $genero) { ?>
                                        <option value="<?= $genero['id'] ?>"
                                            data-nome="<?= $genero['generos'] ?>">
                                            <?= $genero['generos'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Formulário de Edição -->
                        <div id="editForm" class="hidden mt-6 space-y-6">
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Gênero</h3>

                                <!-- Nome do Gênero -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="editGenero" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                        <input type="text" id="editGenero" name="nome" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200">
                                    </div>
                                </div>

                                <!-- Botões de Ação -->
                                <div class="flex space-x-4">
                                    <button type="submit" name="action" value="edit"
                                        class="flex-1 bg-ceara-green hover:bg-ceara-green-dark text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-save mr-3"></i>
                                        Salvar Alterações
                                    </button>
                                    <button type="button" id="cancelEdit"
                                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-times mr-3"></i>
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botão de Exclusão -->
                        <!-- <button type="submit" name="action" value="delete"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                            <i class="fas fa-trash-alt mr-3"></i>
                            Excluir Genero
                        </button> -->
                    </div>

                    <?php if (isset($_GET['true'])): ?>
                        <div class="flex items-center p-4 mb-4 text-green-800 border-l-4 border-green-500 bg-green-50 rounded-md"
                            role="alert">
                            <i class="fas fa-check-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Gênero editado com sucesso!</span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md"
                            role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">ERRO ao editar gênero!</span>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validationMessage = document.getElementById('validationMessage');
            const editForm = document.getElementById('editForm');
            const cancelEditBtn = document.getElementById('cancelEdit');

            // Initialize Select2 with custom configuration
            $('.js-example-basic-single').select2({
                placeholder: "Digite o nome do gênero...",
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Nenhum gênero encontrado";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            }).on('select2:select', function(e) {
                const selectedOption = e.params.data.element;
                if (selectedOption) {
                    // Update form fields with selected gender data
                    document.getElementById('editGenero').value = selectedOption.dataset.nome;

                    // Show the edit form
                    editForm.classList.remove('hidden');
                }
            });

            // Handle cancel button
            cancelEditBtn.addEventListener('click', function() {
                editForm.classList.add('hidden');
                $('.js-example-basic-single').val('').trigger('change');
            });

            // Form validation
            document.getElementById('editGeneroForm').addEventListener('submit', function(e) {
                const selectedGenero = $('.js-example-basic-single').val();
                if (!selectedGenero) {
                    e.preventDefault();
                    validationMessage.innerHTML = `
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Por favor, selecione um gênero para editar!</span>
                        </div>
                    `;
                    validationMessage.classList.remove('hidden');
                }
            });
        });
    </script>
    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.getElementById('editGeneroForm'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/editar_livro.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();
$livros = $select_model->select_nome_livro();
$categorias = $select_model->select_categoria();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário Biblioteca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#007A33',
                        'ceara-green-dark': '#005F27',
                        'ceara-orange': '#FFA500',
                        'ceara-orange-dark': '#FF8C00',
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(0, 122, 51, 0.1), 0 8px 10px -6px rgba(0, 122, 51, 0.1)',
                    },
                    backgroundImage: {
                        'gradient-ceara': 'linear-gradient(to right, #007A33, #005F27)',
                        'gradient-orange': 'linear-gradient(to right, #FFA500, #FF8C00)',
                    }
                }
            }
        }
    </script>
    <style>
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #007A33 !important;
            border-color: #007A33 !important;
        }

        input[type="checkbox"]:checked:focus,
        input[type="radio"]:checked:focus {
            --tw-ring-color: rgba(0, 122, 51, 0.2);
        }

        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        input[type="radio"]:checked {
            border-color: #007A33;
            background-color: #007A33;
            box-shadow: inset 0 0 0 4px #fff;
        }

        input[type="radio"]:focus {
            box-shadow: 0 0 0 3px rgba(0, 122, 51, 0.2);
        }

        .tab-button.active {
            color: #007A33 !important;
            border-bottom: 2px solid #007A33 !important;
        }

        input[type="checkbox"] {
            accent-color: #007A33;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            background-color: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            min-height: 48px;
            padding: 4px;
            transition: all 0.2s ease-in-out;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #007A33;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4b5563;
            line-height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007A33;
            color: white;
        }

        .select2-dropdown {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 8px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #007A33;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e6f3ed;
            color: #007A33;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-12 select-none"
    style="background-image: url('../assets/img/layout.png'); background-size: cover; background-attachment: fixed;">

    <?php if (isset($_GET['from']) && $_GET['from'] === 'gerenciar_acervo'): ?>
        <a href="gerenciar_acervo.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
            <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
            <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
        </a>
    <?php endif; ?>

    <div class="w-full max-w-4xl">
        <!-- <img src="../assets/img/logo1.png"
            class="w-[200px] sm:w-[250px] md:w-[300px] mx-auto mb-8 drop-shadow-xl hover:scale-105 transition-transform duration-300"
            alt="Logo"> -->
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-ceara p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white text-center tracking-wide">
                    <i class="fas fa-edit mr-3"></i>Editar Livro
                </h2>
            </div>

            <form id="editBookForm" action="../controllers/excluir_editar_livro.php" method="post" class="p-6 sm:p-8">
                <div class="mb-8">
                    <div class="flex border-b border-gray-200">
                        <button type="button"
                            class="tab-button py-2 px-4 font-medium text-ceara-green border-b-2 border-ceara-green cursor-default active"
                            data-tab="search-book">Buscar Livro</button>
                    </div>
                </div>

                <div id="validationMessage" class="hidden"></div>

                <div id="search-book" class="tab-content space-y-6">
                    <div class="space-y-6">
                        <div class="relative">
                            <label for="searchBook" class="block text-base font-medium text-gray-700 mb-2">Buscar Livro</label>
                            <div class="relative">
                                <select class="js-example-basic-single border-2 border-gray-200 rounded-lg px-5 py-3 w-full focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none text-lg bg-white text-gray-700 transition-all duration-200" name="id_livro" required>
                                    <option value="" selected disabled>Selecione um livro para editar</option>
                                    <?php
                                    foreach ($livros as $livro) {
                                        $prateleira = strtoupper($livro['prateleiras']);
                                        $estante = $livro['estantes'];
                                        $displayEstante = $estante ?: 'N/A';
                                    ?>
                                        <option value="<?= htmlspecialchars($livro['id']) ?>"
                                            data-titulo="<?= htmlspecialchars($livro['titulo_livro']) ?>"
                                            data-edicao="<?= htmlspecialchars($livro['edicao']) ?>"
                                            data-editora="<?= htmlspecialchars($livro['editora']) ?>"
                                            data-estante="<?= htmlspecialchars($estante) ?>"
                                            data-prateleira="<?= htmlspecialchars($prateleira) ?>"
                                            data-quantidade="<?= htmlspecialchars($livro['quantidade']) ?>"
                                            data-genero="<?= htmlspecialchars($livro['generos']) ?>"
                                            data-subgenero="<?= htmlspecialchars($livro['subgenero']) ?>"
                                            data-ano_publicacao="<?= htmlspecialchars($livro['ano_publicacao']) ?>"
                                            data-corredor="<?= htmlspecialchars($livro['corredor']) ?>"
                                            data-categoria="<?= htmlspecialchars($livro['categoria']) ?>"
                                            data-ficcao="<?= htmlspecialchars($livro['ficcao']) ?>"
                                            data-brasileira="<?= htmlspecialchars($livro['brasileira']) ?>"
                                            data-cativo="<?= htmlspecialchars($livro['cativo']) ?>">
                                            <?= htmlspecialchars($livro['titulo_livro']) ?> | edição: <?= htmlspecialchars($livro['edicao']) ?> | editora: <?= htmlspecialchars($livro['editora']) ?> | estante/armário: <?= htmlspecialchars($displayEstante) ?> | prateleira: <?= htmlspecialchars($prateleira) ?> | quantidade: <?= htmlspecialchars($livro['quantidade']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div id="editForm" class="hidden mt-8 space-y-8">
                            <div class="bg-gray-50 rounded-2xl p-8 border border-gray-200 shadow-inner">
                                <h3 class="text-xl font-bold text-ceara-green mb-6 flex items-center gap-2"><i class="fas fa-id-card-alt"></i> Informações do Livro</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="editTitulo" class="block text-base font-medium text-gray-700 mb-2">Título</label>
                                        <input type="text" id="editTitulo" name="titulo_livro" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 placeholder-gray-400 transition-all duration-200 text-lg" required>
                                    </div>
                                    <div>
                                        <label for="editEdicao" class="block text-base font-medium text-gray-700 mb-2">Edição</label>
                                        <input type="text" id="editEdicao" name="edicao" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 placeholder-gray-400 transition-all duration-200 text-lg" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="editEditora" class="block text-base font-medium text-gray-700 mb-2">Editora</label>
                                        <input type="text" id="editEditora" name="editora" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 placeholder-gray-400 transition-all duration-200 text-lg" required>
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <label for="editEstante" class="block text-base font-medium text-gray-700 mb-2">Estante/Armário</label>
                                            <select id="editEstante" name="estante" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 transition-all duration-200 text-lg" required>
                                                <option value="">Selecione uma estante/armário</option>
                                                <?php for ($i = 1; $i <= 40; $i++) { ?>
                                                    <option value="<?= $i ?>">Estante <?= $i ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="editPrateleira" class="block text-base font-medium text-gray-700 mb-2">Prateleira</label>
                                            <select id="editPrateleira" name="prateleira" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 transition-all duration-200 text-lg" required>
                                                <?php for ($i = 1; $i <= 5; $i++) { ?>
                                                    <option value="P<?= $i ?>">Prateleira <?= $i ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="editQuantidade" class="block text-base font-medium text-gray-700 mb-2">Quantidade</label>
                                        <input type="number" id="editQuantidade" name="quantidade" min="1" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 placeholder-gray-400 transition-all duration-200 text-lg" required>
                                    </div>
                                    <div>
                                        <label for="editAnoPublicacao" class="block text-base font-medium text-gray-700 mb-2">Ano de Publicação</label>
                                        <input type="text" id="editAnoPublicacao" name="ano_publicacao" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 placeholder-gray-400 transition-all duration-200 text-lg" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="editGenero" class="block text-base font-medium text-gray-700 mb-2">Gênero</label>
                                        <select name="genero" id="editGenero" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 transition-all duration-200 text-lg" required>
                                            <option value="">Selecione um gênero</option>
                                            <?php
                                            $generos = $select_model->select_genero();
                                            foreach ($generos as $genero) { ?>
                                                <option value="<?= htmlspecialchars($genero['id']) ?>"><?= htmlspecialchars($genero['generos']) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="editSubgenero" class="block text-base font-medium text-gray-700 mb-2">Subgênero</label>
                                        <select name="subgenero" id="editSubgenero" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 transition-all duration-200 text-lg" required>
                                            <option value="">Selecione um subgênero</option>
                                            <?php
                                            $generos = $select_model->select_genero();
                                            foreach ($generos as $genero) {
                                                $subgeneros = $select_model->select_subgenero(genero: $genero['generos']);
                                            ?>
                                                <optgroup label="<?= htmlspecialchars($genero['generos']) ?>">
                                                <?php foreach ($subgeneros as $subgenero) { ?>
                                                    <option value="<?= htmlspecialchars($subgenero['id']) ?>"><?= htmlspecialchars($subgenero['subgenero']) ?></option>
                                                <?php } ?>
                                                </optgroup>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="editCorredor" class="block text-base font-medium text-gray-700 mb-2">Corredor</label>
                                        <input type="text" id="editCorredor" name="corredor" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 placeholder-gray-400 transition-all duration-200 text-lg" required>
                                    </div>
                                    <div>
                                        <label for="editCategoria" class="block text-base font-medium text-gray-700 mb-2">Categoria</label>
                                        <select id="editCategoria" name="categoria" class="w-full px-5 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-700 transition-all duration-200 text-lg" required>
                                            <option value="">Selecione a categoria</option>
                                            <?php foreach ($categorias as $categoria) { ?>
                                                <option value="<?= htmlspecialchars($categoria['categorias'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($categoria['categorias'], ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="flex items-center">
                                            <input type="radio" id="editFiccaoTrue" name="ficcao" value="1" class="w-5 h-5 text-ceara-green border-gray-300 rounded focus:ring-ceara-green" required>
                                            <label for="editFiccaoTrue" class="ml-2 text-base font-medium text-gray-700">Ficção</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="editFiccaoFalse" name="ficcao" value="0" class="w-5 h-5 text-ceara-green border-gray-300 rounded focus:ring-ceara-green" required>
                                            <label for="editFiccaoFalse" class="ml-2 text-base font-medium text-gray-700">Não Ficção</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="editLiteraturaTrue" name="literatura" value="1" class="w-5 h-5 text-ceara-green border-gray-300 rounded focus:ring-ceara-green" required>
                                            <label for="editLiteraturaTrue" class="ml-2 text-base font-medium text-gray-700">Brasileira</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="editLiteraturaFalse" name="literatura" value="0" class="w-5 h-5 text-ceara-green border-gray-300 rounded focus:ring-ceara-green" required>
                                            <label for="editLiteraturaFalse" class="ml-2 text-base font-medium text-gray-700">Estrangeira</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="editCativoTrue" name="cativo" value="1" class="w-5 h-5 text-ceara-green border-gray-300 rounded focus:ring-ceara-green" required>
                                            <label for="editCativoTrue" class="ml-2 text-base font-medium text-gray-700">Cativo</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="editCativoFalse" name="cativo" value="0" class="w-5 h-5 text-ceara-green border-gray-300 rounded focus:ring-ceara-green" required>
                                            <label for="editCativoFalse" class="ml-2 text-base font-medium text-gray-700">Não Cativo</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-row gap-4 mt-6">
                                    <button type="submit" name="action" value="edit"
                                        class="flex-1 bg-ceara-green hover:bg-ceara-green-dark text-white font-bold py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-save mr-3"></i>
                                        Salvar Alterações
                                    </button>
                                    <button type="button" id="cancelEdit"
                                        class="flex-1 bg-gray-400 hover:bg-gray-600 text-white font-bold py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-times mr-3"></i>
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($_GET['true'])): ?>
                        <div class="flex items-center p-4 mb-4 text-green-800 border-l-4 border-green-500 bg-green-50 rounded-md"
                            role="alert">
                            <i class="fas fa-check-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Livro editado com sucesso!</span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md"
                            role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">ERRO ao editar livro!</span>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validationMessage = document.getElementById('validationMessage');
            const editForm = document.getElementById('editForm');
            const cancelEditBtn = document.getElementById('cancelEdit');

            $('.js-example-basic-single').select2({
                placeholder: "Digite o título do livro...",
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Nenhum livro encontrado";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            }).on('select2:select', function(e) {
                const selectedOption = e.params.data.element;
                if (selectedOption) {
                    document.getElementById('editTitulo').value = selectedOption.dataset.titulo || '';
                    document.getElementById('editEdicao').value = selectedOption.dataset.edicao || '';
                    document.getElementById('editEditora').value = selectedOption.dataset.editora || '';
                    const estanteValue = selectedOption.dataset.estante;
                    const validEstante = estanteValue && (
                        (!isNaN(estanteValue) && estanteValue >= 1 && estanteValue <= 33) ||
                        /^armario-[1-5]$/.test(estanteValue)
                    ) ? estanteValue : '';
                    document.getElementById('editEstante').value = validEstante;
                    document.getElementById('editPrateleira').value = selectedOption.dataset.prateleira || '';
                    document.getElementById('editQuantidade').value = selectedOption.dataset.quantidade || '';
                    document.getElementById('editAnoPublicacao').value = selectedOption.dataset.ano_publicacao || '';
                    document.getElementById('editCorredor').value = selectedOption.dataset.corredor || '';
                    document.getElementById('editCategoria').value = selectedOption.dataset.categoria || '';

                    const generoSelect = document.getElementById('editGenero');
                    const generoOptions = Array.from(generoSelect.options);
                    const generoOption = generoOptions.find(option => option.text === selectedOption.dataset.genero);
                    if (generoOption) {
                        generoSelect.value = generoOption.value;
                    } else {
                        generoSelect.value = '';
                    }
                    const subgeneroSelect = document.getElementById('editSubgenero');
                    const subgeneroOptions = Array.from(subgeneroSelect.options);
                    const subgeneroOption = subgeneroOptions.find(option => option.text === selectedOption.dataset.subgenero);
                    if (subgeneroOption) {
                        subgeneroSelect.value = subgeneroOption.value;
                    } else {
                        subgeneroSelect.value = '';
                    }

                    document.getElementById('editFiccaoTrue').checked = selectedOption.dataset.ficcao === '1';
                    document.getElementById('editFiccaoFalse').checked = selectedOption.dataset.ficcao === '0';
                    document.getElementById('editLiteraturaTrue').checked = selectedOption.dataset.brasileira === '1';
                    document.getElementById('editLiteraturaFalse').checked = selectedOption.dataset.brasileira === '0';
                    document.getElementById('editCativoTrue').checked = selectedOption.dataset.cativo === '1';
                    document.getElementById('editCativoFalse').checked = selectedOption.dataset.cativo === '0';

                    editForm.classList.remove('hidden');
                }
            });

            cancelEditBtn.addEventListener('click', function() {
                editForm.classList.add('hidden');
                $('.js-example-basic-single').val('').trigger('change');
            });

            document.getElementById('editBookForm').addEventListener('submit', function(e) {
                const selectedBook = $('.js-example-basic-single').val();
                let isValid = true;

                if (!selectedBook) {
                    isValid = false;
                    validationMessage.innerHTML = `
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-base font-semibold">Por favor, selecione um livro para editar!</span>
                        </div>
                    `;
                    validationMessage.classList.remove('hidden');
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.getElementById('editBookForm'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/editar_subgenero.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();
$subgeneros = $select_model->select_subgenero($genero = ''); // Passa uma string vazia para buscar todos os subgêneros
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Formulário Biblioteca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#007A33',
                        'ceara-green-dark': '#005F27',
                        'ceara-orange': '#FFA500',
                        'ceara-orange-dark': '#FF8C00',
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(0, 122, 51, 0.1), 0 8px 10px -6px rgba(0, 122, 51, 0.1)',
                    },
                    backgroundImage: {
                        'gradient-ceara': 'linear-gradient(to right, #007A33, #005F27)',
                        'gradient-orange': 'linear-gradient(to right, #FFA500, #FF8C00)',
                    }
                }
            }
        }
    </script>
    <style>
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #007A33 !important;
            border-color: #007A33 !important;
        }

        input[type="checkbox"]:checked:focus,
        input[type="radio"]:checked:focus {
            --tw-ring-color: rgba(0, 122, 51, 0.2);
        }

        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        input[type="radio"]:checked {
            border-color: #007A33;
            background-color: #007A33;
            box-shadow: inset 0 0 0 4px #fff;
        }

        input[type="radio"]:focus {
            box-shadow: 0 0 0 3px rgba(0, 122, 51, 0.2);
        }

        .tab-button.active {
            color: #007A33 !important;
            border-bottom: 2px solid #007A33 !important;
        }

        input[type="checkbox"] {
            accent-color: #007A33;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            background-color: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            min-height: 48px;
            padding: 4px;
            transition: all 0.2s ease-in-out;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #007A33;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4b5563;
            line-height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007A33;
            color: white;
        }

        .select2-dropdown {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 8px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #007A33;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e6f3ed;
            color: #007A33;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-12 select-none"
    style="background-image: url('../assets/img/layout.png'); background-size: cover; background-attachment: fixed;">

    <?php if (isset($_GET['from']) && $_GET['from'] === 'classificacao'): ?>
        <a href="classificacao.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
            <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
            <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
        </a>
    <?php endif; ?>

    <div class="w-full max-w-4xl">
        <!-- <img src="../assets/img/logo1.png"
            class="w-[200px] sm:w-[250px] md:w-[300px] mx-auto mb-8 drop-shadow-xl hover:scale-105 transition-transform duration-300"
            alt="Logo"> -->
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-ceara p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white text-center tracking-wide">
                    <i class="fas fa-edit mr-3"></i>Editar Subgênero
                </h2>
            </div>

            <form id="editSubgeneroForm" action="../controllers/excluir_editar_livro.php" method="post" class="p-6 sm:p-8">
                <div class="mb-8">
                    <div class="flex border-b border-gray-200">
                        <button type="button"
                            class="tab-button py-2 px-4 font-medium text-ceara-green border-b-2 border-ceara-green cursor-default active"
                            data-tab="search-subgenero">Buscar Subgênero</button>
                    </div>
                </div>

                <div id="validationMessage" class="hidden"></div>

                <div id="search-subgenero" class="tab-content space-y-6">
                    <div class="space-y-6">
                        <div class="relative">
                            <label for="searchSubgenero" class="block text-base font-medium text-gray-700 mb-2">Buscar Subgênero</label>
                            <div class="relative">
                                <select class="js-example-basic-single border-2 border-gray-200 rounded-lg px-5 py-3 w-full focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none text-lg bg-white text-gray-700 transition-all duration-200" name="id_subgenero" required>
                                    <option value="" selected disabled>Selecione um subgênero para editar</option>
                                    <?php
                                    foreach ($subgeneros as $subgenero) { ?>
                                        <option value="<?= $subgenero['id'] ?>"
                                            data-nome="<?= $subgenero['subgenero'] ?>">
                                            <?= $subgenero['subgenero'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Formulário de Edição -->
                        <div id="editForm" class="hidden mt-6 space-y-6">
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Subgênero</h3>

                                <!-- Nome do Subgênero -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="editSubgenero" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                        <input type="text" id="editSubgenero" name="nome" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-lg focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/20 focus:outline-none hover:border-gray-300 text-gray-600 placeholder-gray-400 transition-all duration-200">
                                    </div>
                                </div>

                                <!-- Botões de Ação -->
                                <div class="flex space-x-4">
                                    <button type="submit" name="action" value="edit"
                                        class="flex-1 bg-ceara-green hover:bg-ceara-green-dark text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-save mr-3"></i>
                                        Salvar Alterações
                                    </button>
                                    <button type="button" id="cancelEdit"
                                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                                        <i class="fas fa-times mr-3"></i>
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botão de Exclusão -->
                        <!-- <button type="submit" name="action" value="delete"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                            <i class="fas fa-trash-alt mr-3"></i>
                            Excluir Subgênero
                        </button> -->
                    </div>

                    <?php if (isset($_GET['true'])): ?>
                        <div class="flex items-center p-4 mb-4 text-green-800 border-l-4 border-green-500 bg-green-50 rounded-md"
                            role="alert">
                            <i class="fas fa-check-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Subgênero editado com sucesso!</span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md"
                            role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">ERRO ao editar subgênero!</span>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validationMessage = document.getElementById('validationMessage');
            const editForm = document.getElementById('editForm');
            const cancelEditBtn = document.getElementById('cancelEdit');

            // Initialize Select2 with custom configuration
            $('.js-example-basic-single').select2({
                placeholder: "Digite o nome do subgênero...",
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Nenhum subgênero encontrado";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            }).on('select2:select', function(e) {
                const selectedOption = e.params.data.element;
                if (selectedOption) {
                    // Update form fields with selected subgenre data
                    document.getElementById('editSubgenero').value = selectedOption.dataset.nome;

                    // Show the edit form
                    editForm.classList.remove('hidden');
                }
            });

            // Handle cancel button
            cancelEditBtn.addEventListener('click', function() {
                editForm.classList.add('hidden');
                $('.js-example-basic-single').val('').trigger('change');
            });

            // Form validation
            document.getElementById('editSubgeneroForm').addEventListener('submit', function(e) {
                const selectedSubgenero = $('.js-example-basic-single').val();
                if (!selectedSubgenero) {
                    e.preventDefault();
                    validationMessage.innerHTML = `
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Por favor, selecione um subgênero para editar!</span>
                        </div>
                    `;
                    validationMessage.classList.remove('hidden');
                }
            });
        });
    </script>
    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.getElementById('editSubgeneroForm'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/emprestimo/decisao.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');

$select_model = new select_model();
$param = $_GET['id_livro'] ?? '';

if (!empty($param)) {
    // QR flow: id_livro passed via QR -> auto-decision
    $parts = explode('_', $param);
    $id_livro = intval($parts[0] ?? 0);

    if (!$id_livro) {
        http_response_code(400);
        echo 'Parâmetro id_livro inválido.';
        exit;
    }

    $livro = $select_model->id_livro_selecionado($id_livro);
    if (!$livro) {
        http_response_code(404);
        echo 'Livro não encontrado.';
        exit;
    }

    $activeCount = $select_model->count_emprestimos_ativos_por_catalogo($id_livro);
    $activeLoan = $select_model->select_emprestimo_ativo_por_catalogo($id_livro);

    // Definição do destino (emprestimo ou devolução)
        // Prioriza devolução quando houver empréstimo ativo para este catálogo
        if ($activeLoan) {
        $url_final = $mainUrl . 'index.php?section=devolucao_livro&id_emprestimo=' . rawurlencode((string) $activeLoan['id']);
        } else if (intval($livro['quantidade']) > $activeCount) {
        $url_final = $mainUrl . 'index.php?section=emprestimo_livro&id_livro=' . rawurlencode((string) $id_livro);
        } else {
        $url_final = $mainUrl . 'index.php?section=emprestimo_livro&id_livro=' . rawurlencode((string) $id_livro);
        }
        ?>
        <script>
            window.location.replace(<?= json_encode($url_final, JSON_UNESCAPED_SLASHES) ?>);
        </script>
        <?php
        exit;
    }
?>
<?php
                break;
            case 'views/emprestimo/devolucao_livro.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();

$livros_emprestados = $select_model->select_emprestimo();

$id_emprestimo_selecionado = null;
if (isset($_POST['livro_emprestimo']) && $_POST['livro_emprestimo'] !== '') {
  $id_emprestimo_selecionado = (int)$_POST['livro_emprestimo'];
} elseif (isset($_GET['id_emprestimo']) && $_GET['id_emprestimo'] !== '') {
  $id_emprestimo_selecionado = (int)$_GET['id_emprestimo'];
}

if ($id_emprestimo_selecionado) {
  $info_selecionada = $select_model->dados_aluno($id_emprestimo_selecionado);
} else {
  $info_selecionada = null;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro de Devolução</title>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="shortcut icon" href="../../assets/img/icon.png" type="image/x-icon">
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'ceara-green': '#007A33',
            'ceara-green-dark': '#005F27',
            'ceara-orange': '#FFA500',
            'ceara-orange-dark': '#FF8C00'
          }
        }
      }
    }
  </script>

  <style>
    body {
      background-image: url('../../assets/img/layout.png');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
    }
  </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

  <form id="devolucaoForm" method="POST" action="../../controllers/devolucao_controller.php" class="bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-xl w-full max-w-md space-y-4">
    <h2 class="text-xl font-bold text-center text-ceara-green-dark">Registro de Devolução</h2>

    <div class="space-y-4">
      <h3 class="text-lg font-semibold text-ceara-green-dark text-center">Informações do Empréstimo</h3>

      <div>
        <label for="livro_emprestimo" class="block text-sm font-medium text-gray-700">Selecione o Livro Emprestado:</label>
        <div>

          <select name="livro_emprestimo" id="livro_emprestimo" class="mt-1 w-full p-2 border border-gray-300 rounded">
            <option value="">-- Selecione --</option>
            <?php
            foreach ($livros_emprestados as $livro):
              $info = $select_model->dados_aluno($livro['id']);
              if (!$info && !empty($livro['id_aluno'])) {
                $info = $select_model->id_aluno_selecionado($livro['id_aluno']);
              }
              $aluno_nome = $info['nome'] ?? '';
              $aluno_turma = $info['turma'] ?? '';
              $aluno_curso = $info['curso'] ?? '';
            ?>
              <option value="<?= (int)$livro['id'] ?>" <?= (string)$id_emprestimo_selecionado === (string)$livro['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars((string)$livro['titulo_livro']) ?> | Aluno: <?= htmlspecialchars((string)$aluno_nome) ?> | Turma: <?= htmlspecialchars((string)$aluno_turma) ?> | Curso: <?= htmlspecialchars((string)$aluno_curso) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
        <label for="data_devolucao" class="block text-sm font-medium text-gray-700">Data de Devolução:</label>
        <input type="date" name="data_devolucao" value="<?php echo date('Y-m-d'); ?>" required class="mt-1 w-full p-2 border border-gray-300 rounded">
      </div>
        </div>
        <div id="devolucaoMessage"></div>
        <div class="flex justify-between">
        <button type="submit" class="bg-ceara-green text-white font-semibold py-2 px-4 rounded hover:bg-ceara-green-dark transition">
          Registrar Devolução
        </button>
  </form>

  <script>
    $(document).ready(function() {
      $('#livro_emprestimo').select2({
        placeholder: "-- Selecione o Livro --",
        allowClear: true
      });

      $('#devolucaoForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
          type: form.attr('method'),
          url: form.attr('action'),
          data: form.serialize(),
          success: function(response) {
            var data = response;
            if (typeof response === 'string') {
              try {
                data = JSON.parse(response);
              } catch (err) {
                data = {};
              }
            }

            if (data.success) {
              $('#devolucaoMessage').html('<div class="flex items-center p-4 mb-4 text-green-800 border-l-4 border-green-500 bg-green-50 rounded-md" role="alert"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" aria-hidden="true"><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.9 176.6L233.4 329.1c-4.7 4.7-12.3 4.7-17 0l-82.3-82.3c-4.7-4.7-4.7-12.3 0-17l28.3-28.3c4.7-4.7 12.3-4.7 17 0l45.5 45.5 107.6-107.6c4.7-4.7 12.3-4.7 17 0l28.3 28.3c4.8 4.7 4.8 12.3.1 16.9z"/></svg><span class="text-sm font-medium">Devolução registrada com sucesso!</span></div>');
              form[0].reset();
              $('#livro_emprestimo').val(null).trigger('change');
            } else {
              var message = 'Erro ao registrar devolução.';
              if (data.error) {
                if (data.error === 'registro') {
                  message = 'Falha ao salvar a devolução. Tente novamente.';
                } else if (data.error === 'dados_incompletos') {
                  message = 'Dados incompletos. Preencha o formulário corretamente.';
                }
              }
              $('#devolucaoMessage').html('<div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" class="w-6 h-6 mr-3 flex-shrink-0" fill="none" aria-hidden="true"><circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="4"/><line x1="50" y1="28" x2="50" y2="58" stroke="currentColor" stroke-width="6"/><rect x="47" y="64" width="6" height="6" fill="currentColor"/></svg><span class="text-sm font-medium">' + message + '</span></div>');
            }
          },
          error: function() {
            $('#devolucaoMessage').html('<div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" class="w-6 h-6 mr-3 flex-shrink-0" fill="none" aria-hidden="true"><circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="4"/><line x1="50" y1="28" x2="50" y2="58" stroke="currentColor" stroke-width="6"/><rect x="47" y="64" width="6" height="6" fill="currentColor"/></svg><span class="text-sm font-medium">Erro de conexão. Tente novamente.</span></div>');
          }
        });
      });
    });
  </script>

</body>

</html>
<?php
                break;
            case 'views/emprestimo/emprestimo_livro.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();

// Inicializa as variáveis
$id_aluno_selecionado = $_POST['aluno'] ?? null;
$id_livro_selecionado = $_GET['id_livro'] ?? null;
$turma = null;
$curso = null;

if ($id_aluno_selecionado) {
  $info = $select_model->id_aluno_selecionado($id_aluno_selecionado);
  if ($info) {
    $turma = $info['turma'];
    $curso = $info['curso'];
  }
}

$livros = $select_model->select_livros();
$dados_aluno = $select_model->select_aluno();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro de Empréstimo</title>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'ceara-green': '#007A33',
            'ceara-green-dark': '#005F27',
            'ceara-orange': '#FFA500',
            'ceara-orange-dark': '#FF8C00'
          }
        }
      }
    }
  </script>

  <style>
    body {
      background-image: url('../../assets/img/layout.png');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
    }
  </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

  <form id="emprestimoForm" method="POST" action="../../controllers/emprestimo_controller.php" class="bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-xl w-full max-w-md space-y-4">
    <h2 class="text-xl font-bold text-center text-ceara-green-dark">Registro de Empréstimo</h2>


    <div class="space-y-4">
      <h3 class="text-lg font-semibold text-ceara-green-dark text-center">Informações do Aluno</h3>


      <div>
        <label for="aluno" class="block text-sm font-medium text-gray-700">Selecione o Aluno:</label>
        <select name="aluno" id="aluno" class="mt-1 w-full p-2 border border-gray-300 rounded">
            <option value="">-- Selecione --</option>
            <?php
            if(isset($_GET['nome'])){
              echo '<option value="'.$_GET['id_aluno'].'" selected>'.$_GET['nome'].'</option>';
              }
              foreach ($dados_aluno as $aluno): ?>
              <option value="<?= $aluno['id_aluno']?>" <?= $id_aluno_selecionado == $aluno['id_aluno'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($aluno['nome']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="turma" class="block text-sm font-medium text-gray-700">Turma:</label>
          <select name="turma" id="turma" disabled class="mt-1 w-full p-2 border border-gray-300 rounded">
            <option value="">
              <?= $turma = !empty($_GET['turma']) ? htmlspecialchars($_GET['turma']) : '-- Selecione --' ?>
            </option>
          </select>
        </div>

        <div>
          <label for="curso" class="block text-sm font-medium text-gray-700">Curso:</label>
          <select name="curso" id="curso" disabled class="mt-1 w-full p-2 border border-gray-300 rounded">
            <option value="">
              <?= $curso = !empty($_GET['curso']) ? htmlspecialchars($_GET['curso']) : '-- Selecione --' ?>
            </option>
          </select>
        </div>

      </div>
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-ceara-green-dark text-center">Livro e Datas</h3>

        <div>
          <label for="livro" class="block text-sm font-medium text-gray-700">Selecione o Livro:</label>
          <select name="livro" id="livro" class="mt-1 w-full p-2 border border-gray-300 rounded">
            <?php
          foreach ($livros as $livro): ?>
            <option value="<?= $livro['id'] ?>" <?= $id_livro_selecionado == $livro['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($livro['titulo_livro']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="data_emprestimo" class="block text-sm font-medium text-gray-700">Data de Empréstimo:</label>
          <input type="date" name="data_emprestimo" value="<?php echo date('Y-m-d'); ?>" required class="mt-1 w-full p-2 border border-gray-300 rounded">
        </div>

        <div>
          <label for="data_devolucao_estipulada" class="block text-sm font-medium text-gray-700">Data de Devolução:</label>
          <input type="date" name="data_devolucao_estipulada" required class="mt-1 w-full p-2 border border-gray-300 rounded">
        </div>

      <div id="emprestimoMessage" aria-live="polite">
        <?php if (isset($_GET['erro'])):
          $loanErrors = [
            'aluno_obrigatorio' => 'Selecione um aluno antes de registrar o empréstimo.',
            'aluno_invalido' => 'O aluno selecionado não foi encontrado. Selecione-o novamente.',
            'livro_invalido' => 'O livro selecionado é inválido. Selecione-o novamente.',
            'livro_indisponivel' => 'Este livro não está disponível para empréstimo.',
            'data_invalida' => 'Informe datas válidas e uma devolução no mesmo dia ou após o empréstimo.',
            'dados_incompletos' => 'Preencha o aluno, o livro e as duas datas.',
          ];
          $loanErrorMessage = $loanErrors[(string) $_GET['erro']] ?? 'Não foi possível validar os dados do empréstimo.';
        ?>
          <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert">
            <span class="text-sm font-medium"><?= htmlspecialchars($loanErrorMessage, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endif; ?>
      </div>


      <div class="flex justify-between">
        <button type="submit" class="bg-ceara-green text-white font-semibold py-2 px-4 rounded hover:bg-ceara-green-dark transition">
          Registrar Empréstimo
        </button>
      </div>
    </div>
  </form>

  <script>
    $(document).ready(function() {
      $('#aluno').select2({
        placeholder: "-- Selecione --",
        allowClear: true
      });
      $('#livro').select2({
        placeholder: "-- Selecione --",
        allowClear: true
      });

      // When the selected student changes, request student info via AJAX
      $('#aluno').on('change', function() {
        var alunoId = $(this).val();
        if (!alunoId) {
          $('#turma').html('<option>-- Selecione --</option>');
          $('#curso').html('<option>-- Selecione --</option>');
          return;
        }

        $.ajax({
          type: 'POST',
          url: '../../controllers/emprestimo_controller.php',
          data: { aluno: alunoId },
          success: function(response) {
            var data = response;
            if (typeof response === 'string') {
              try { data = JSON.parse(response); } catch (err) { data = {}; }
            }
            if (data.info) {
              var info = data.info;
              $('#turma').html('<option value="">' + (info.turma ? info.turma : '-- Selecione --') + '</option>');
              $('#curso').html('<option value="">' + (info.curso ? info.curso : '-- Selecione --') + '</option>');
            }
          }
        });
      });

      $('#emprestimoForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var alunoId = $('#aluno').val();
        var message = $('#emprestimoMessage');

        if (!alunoId) {
          message.html('<div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert"><span class="text-sm font-medium">Selecione um aluno antes de registrar o empréstimo.</span></div>');
          $('#aluno').select2('open');
          return;
        }

        message.empty();

        $.ajax({
          type: form.attr('method'),
          url: form.attr('action'),
          data: form.serialize(),
          success: function(response) {
            var data = response;
            if (typeof response === 'string') {
              try {
                data = JSON.parse(response);
              } catch (err) {
                data = {};
              }
            }

            if (data.info) {
              // Preencher turma e curso quando o retorno for infos do aluno
              var info = data.info;
              $('#turma').html('<option value="">' + (info.turma ? info.turma : '-- Selecione --') + '</option>');
              $('#curso').html('<option value="">' + (info.curso ? info.curso : '-- Selecione --') + '</option>');
            } else if (data.success) {
              // Registro concluído com sucesso: limpar formulário e exibir mensagem
              $('#emprestimoMessage').html('<div class="flex items-center p-4 mb-4 text-green-800 border-l-4 border-green-500 bg-green-50 rounded-md" role="alert"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" aria-hidden="true"><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.9 176.6L233.4 329.1c-4.7 4.7-12.3 4.7-17 0l-82.3-82.3c-4.7-4.7-4.7-12.3 0-17l28.3-28.3c4.7-4.7 12.3-4.7 17 0l45.5 45.5 107.6-107.6c4.7-4.7 12.3-4.7 17 0l28.3 28.3c4.8 4.7 4.8 12.3.1 16.9z"/></svg><span class="text-sm font-medium">Empréstimo registrado com sucesso!</span></div>');
              form[0].reset();
              $('#aluno').val(null).trigger('change');
              $('#livro').val(null).trigger('change');
            } else if (data.error) {
              var errorMessage = 'ERRO ao registrar empréstimo!';
              if (data.error === 'aluno_obrigatorio') {
                errorMessage = 'Selecione um aluno antes de registrar o empréstimo.';
              } else if (data.error === 'aluno_invalido') {
                errorMessage = 'O aluno selecionado não foi encontrado. Selecione-o novamente.';
              } else if (data.error === 'livro_invalido') {
                errorMessage = 'O livro selecionado é inválido. Selecione-o novamente.';
              } else if (data.error === 'livro_indisponivel') {
                errorMessage = 'Este livro não está disponível para empréstimo.';
              } else if (data.error === 'data_invalida') {
                errorMessage = 'Informe datas válidas e uma devolução no mesmo dia ou após o empréstimo.';
              } else if (data.error === 'dados_incompletos') {
                errorMessage = 'Preencha o aluno, o livro e as duas datas.';
              }
              message.html('<div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert"><span class="text-sm font-medium"></span></div>');
              message.find('.text-sm').text(errorMessage);
            }
          },
          error: function(xhr) {
            var data = xhr.responseJSON || {};
            var messages = {
              aluno_obrigatorio: 'Selecione um aluno antes de registrar o empréstimo.',
              aluno_invalido: 'O aluno selecionado não foi encontrado. Selecione-o novamente.',
              livro_invalido: 'O livro selecionado é inválido. Selecione-o novamente.',
              livro_indisponivel: 'Este livro não está disponível para empréstimo.',
              data_invalida: 'Informe datas válidas e uma devolução no mesmo dia ou após o empréstimo.',
              dados_incompletos: 'Preencha o aluno, o livro e as duas datas.'
            };
            var errorMessage = messages[data.error] || 'Não foi possível registrar o empréstimo. Tente novamente.';
            message.html('<div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert"><span class="text-sm font-medium"></span></div>');
            message.find('.text-sm').text(errorMessage);
          }
        });
      });
    });
  </script>
  <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error')) {
                setTimeout(function(){
                    const f = document.querySelector('form'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/excluir_livro.php':?>
<?php
require_once(__DIR__ . '/models/select_model.php');
$select_model = new select_model();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Formulário Biblioteca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#007A33',
                        'ceara-green-dark': '#005F27',
                        'ceara-orange': '#FFA500',
                        'ceara-orange-dark': '#FF8C00',
                    },
                    boxShadow: {
                        'custom': '0 10px 25px -5px rgba(0, 122, 51, 0.1), 0 8px 10px -6px rgba(0, 122, 51, 0.1)',
                    },
                    backgroundImage: {
                        'gradient-ceara': 'linear-gradient(to right, #007A33, #005F27)',
                        'gradient-orange': 'linear-gradient(to right, #FFA500, #FF8C00)',
                    }
                }
            }
        }
    </script>
    <style>
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            background-color: #007A33 !important;
            border-color: #007A33 !important;
        }

        input[type="checkbox"]:checked:focus,
        input[type="radio"]:checked:focus {
            --tw-ring-color: rgba(0, 122, 51, 0.2);
        }

        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        input[type="radio"]:checked {
            border-color: #007A33;
            background-color: #007A33;
            box-shadow: inset 0 0 0 4px #fff;
        }

        input[type="radio"]:focus {
            box-shadow: 0 0 0 3px rgba(0, 122, 51, 0.2);
        }

        .tab-button.active {
            color: #007A33 !important;
            border-bottom: 2px solid #007A33 !important;
        }

        input[type="checkbox"] {
            accent-color: #007A33;
        }

        /* Select2 Custom Styling */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--multiple {
            background-color: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            min-height: 48px;
            padding: 4px;
            transition: all 0.2s ease-in-out;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #007A33;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007A33;
            border: none;
            border-radius: 0.375rem;
            color: white;
            padding: 4px 8px;
            margin: 2px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 4px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ff4444;
            background: none;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007A33;
        }

        .select2-dropdown {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 8px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #007A33;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e6f3ed;
            color: #007A33;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-12 select-none"
    style="background-image: url('../assets/img/layout.png'); background-size: cover; background-attachment: fixed;">

    <?php if (isset($_GET['from']) && $_GET['from'] === 'gerenciar_acervo'): ?>
        <a href="gerenciar_acervo.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
            <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
            <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
        </a>
    <?php endif; ?>

    <div class="w-full max-w-4xl">
        <!-- <img src="../assets/img/logo1.png"
            class="w-[200px] sm:w-[250px] md:w-[300px] mx-auto mb-8 drop-shadow-xl hover:scale-105 transition-transform duration-300"
            alt="Logo"> -->
        <div class="bg-white rounded-xl shadow-custom overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-lg">
            <div class="bg-gradient-ceara p-6 sm:p-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white text-center tracking-wide">
                    <i class="fas fa-trash-alt mr-3"></i>Excluir Livro
                </h2>
            </div>

            <form id="deleteBookForm" action="../controllers/excluir_editar_livro.php" method="post" class="p-6 sm:p-8">
                <div class="mb-8">
                    <div class="flex border-b border-gray-200">
                        <button type="button"
                            class="tab-button py-2 px-4 font-medium text-ceara-green border-b-2 border-ceara-green cursor-default active"
                            data-tab="search-book">Buscar Livro</button>
                    </div>
                </div>

                <div id="validationMessage" class="hidden"></div>

                <div id="search-book" class="tab-content space-y-6">
                    <div class="space-y-6">
                        <!-- Campo de Busca -->
                        <div class="relative">
                            <label for="searchBook" class="block text-sm font-medium text-gray-700 mb-1">Buscar Livro</label>
                            <div class="relative">
                                <select class="js-example-basic-multiple" name="excluir_livro[]" multiple="multiple">

                                    <?php
                                    $livros = $select_model->select_nome_livro();
                                    foreach ($livros as $livro) { ?>
                                        <option value="<?= $livro['id'] ?>"><?= $livro['titulo_livro'] ?> | edição: <?= $livro['edicao'] ?> | editora: <?= $livro['editora'] ?> | estante: <?= $livro['estantes'] ?> | prateleira: <?= $livro['prateleiras'] ?> | quantidade: <?= $livro['quantidade'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Botão de Exclusão -->
                        <button type="submit" name="action" value="delete"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-4 px-6 rounded-lg transition duration-300 ease-in-out hover:shadow-lg flex items-center justify-center text-lg shadow-md">
                            <i class="fas fa-trash-alt mr-3"></i>
                            Excluir Livro
                        </button>
                    </div>

                    <?php if (isset($_GET['true'])): ?>
                        <div class="flex items-center p-4 mb-4 text-green-800 border-l-4 border-green-500 bg-green-50 rounded-md"
                            role="alert">
                            <i class="fas fa-check-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Livro excluído com sucesso!</span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md"
                            role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">ERRO ao excluir livro!</span>
                        </div>
                    <?php endif; ?>
                </div>
        </div>
        </form>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchBook');
            const searchResults = document.getElementById('searchResults');
            const validationMessage = document.getElementById('validationMessage');

            // Initialize Select2 with custom configuration
            $('.js-example-basic-multiple').select2({
                placeholder: "Digite o título do livro...",
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Nenhum livro encontrado";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                },
                templateResult: formatBook,
                templateSelection: formatBookSelection
            });

            // Custom formatting for dropdown options
            function formatBook(book) {
                if (!book.id) return book.text;
                return $('<span>' + book.text + '</span>');
            }

            // Custom formatting for selected options
            function formatBookSelection(book) {
                if (!book.id) return book.text;
                return $('<span>' + book.text + '</span>');
            }

            // Form validation
            document.getElementById('deleteBookForm').addEventListener('submit', function(e) {
                const selectedBooks = $('.js-example-basic-multiple').val();
                if (!selectedBooks || selectedBooks.length === 0) {
                    e.preventDefault();
                    validationMessage.innerHTML = `
                        <div class="flex items-center p-4 mb-4 text-red-800 border-l-4 border-red-500 bg-red-50 rounded-md" role="alert">
                            <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                            <span class="text-sm font-medium">Por favor, selecione pelo menos um livro para excluir!</span>
                        </div>
                    `;
                    validationMessage.classList.remove('hidden');
                }
            });
        });
    </script>
    <script>
        (function(){
            const params = new URLSearchParams(window.location.search);
            if (params.has('true') || params.has('false') || params.has('erro') || params.has('error') || params.has('ja_cadastrado')) {
                setTimeout(function(){
                    const f = document.getElementById('deleteBookForm'); if (f) f.reset();
                }, 3000);
            }
        })();
    </script>
</body>

</html>
<?php
                break;
            case 'views/gerenciar_acervo.php':?>
<?php
require_once $embeddedDirectory . '/../models/select_model.php';
$selectModel = new select_model();

$totalBooks = $selectModel->count_catalogo_total();
$totalAvailable = $selectModel->count_exemplares_disponiveis();
$activeLoans = $selectModel->count_emprestimos_ativos();
$totalAuthors = $selectModel->count_autores_total();
$totalGenres = $selectModel->count_generos_total();
$topReaders = $selectModel->get_top_readers(10);
$recentBooks = $selectModel->get_recent_books(5);
$categoryDistribution = $selectModel->get_category_distribution(6);
$genreDistribution = $selectModel->get_genre_distribution(6);
$categoryTotal = array_sum(array_column($categoryDistribution, 'total')) ?: 1;
$genreTotal = array_sum(array_column($genreDistribution, 'total')) ?: 1;
$lowStockCount = $selectModel->count_low_stock_books(3);
$overdueCount = $selectModel->count_overdue_loans();
$months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$currentDate = date('j') . ' de ' . $months[intval(date('n')) - 1] . ' de ' . date('Y');
$monthBarValues = $selectModel->get_monthly_loan_counts();
$weeklyBarValues = $selectModel->get_weekly_loan_counts();
$weekLabels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex'];

// Cores para o gráfico de donut
$genreColors = ['#008C45', '#FFA500', '#FFD700', '#FF6B6B', '#4ECDC4', '#95A5A6'];

// Atribuir cores aos gêneros na ordem
foreach ($genreDistribution as $index => &$genre) {
    $genre['cor'] = $genreColors[$index % count($genreColors)];
}
unset($genre); // Limpar referência para evitar bugs futuros

// Atribuir cores às categorias na ordem
foreach ($categoryDistribution as $index => &$category) {
    $category['cor'] = $genreColors[$index % count($genreColors)];
}
unset($category);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="./assets/img/icon.png" type="image/x-icon">
    <title>Dashboard - Sistema de Biblioteca</title>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#008C45',
                        'ceara-orange': '#FFA500',
                        'ceara-white': '#FFFFFF',
                        primary: '#4CAF50',
                        secondary: '#FFB74D',
                    },
                },
            },
        };
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Anton&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        :root {
            --bg-principal: #f8fafc;
            --azul-escuro: #0f172a;
            --azul-card: #1e293b;
            --texto-claro: #f8fafc;
            --texto-escuro: #1e293b;
            --texto-mutado: #64748b;
            --branco: #ffffff;
            --borda: #e2e8f0;
            --borda-clara: #bfdbd0;
            --sucesso: #4DA73A;
            --sucesso-claro: #F8FCF9;
            --alerta: #f59e0b;
            --perigo: #ef4444;
            /* --azul-destaque: #3b82f6; */
        }
        .gradient-text {
            background: linear-gradient(45deg, #008C45, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-principal);
            color: var(--texto-escuro);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .dashboard-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--azul-escuro);
        }

        .dashboard-header .date {
            font-size: 14px;
            color: var(--texto-mutado);
            font-weight: 500;
        }

        /* Grid de Cards Principais - Estilo da Imagem */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .card-indicador {
            background-color: var(--branco);
            border: 1px solid var(--borda);
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            opacity: 0;
            transform: translateY(20px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-indicador:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Card de Destaque Escuro igual ao topo da Imagem */
        .card-indicador.destaque {
            background-color: var(--azul-card);
            color: var(--texto-claro);
            border: none;
        }

        .card-indicador.destaque .card-title {
            color: #94a3b8;
        }

        .card-indicador.destaque .card-icon {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--branco);
        }

        .card-header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--texto-mutado);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--azul-destaque);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .card-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card-footer {
            font-size: 12px;
            color: var(--texto-mutado);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .card-indicador.destaque .card-footer {
            color: #94a3b8;
        }

        .badge-status {
            padding: 2px 8px;
            border-radius: 12px;
            color: #94a3b8;
            font-weight: bold;
            font-size: 14px;
        }

        /* .badge-status.sucesso { background-color: rgba(16, 185, 129, 0.1); color: var(--sucesso); }
        .badge-status.alerta { background-color: rgba(245, 158, 11, 0.1); color: var(--alerta); }
        .badge-status.perigo { background-color: rgba(239, 68, 68, 0.1); color: var(--perigo); } */

        /* Seção Inferior: Gráficos e Listas Laterais */
        .dashboard-row {
            display: grid;
            gap: 16px;
        }

        .dashboard-bottom-grid {
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 32rem), 1fr));
            align-items: stretch;
        }

        .dashboard-bottom-grid > .painel-principal {
            min-width: 0;
            height: auto;
            min-height: 15rem;
        }

        .dashboard-resource-grid {
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 6rem), 1fr));
            align-content: start;
            gap: clamp(0.5rem, 1.2vw, 1rem);
        }

        .dashboard-resource-link {
            min-width: 0;
            aspect-ratio: 1;
            padding: 0.5rem;
        }

        @media (max-width: 1024px) {
            .dashboard-row {
                grid-template-columns: 1fr;
            }
        }

        .painel-principal {
            background-color: var(--branco);
            border: 1px solid var(--borda);
            border-radius: 16px;
            padding: 12px 20px 16px 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            opacity: 0;
            transform: translateY(20px);
        }

        .painel-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--azul-escuro);
            margin-top: 6px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Gráfico de Barras - Novo Layout */
        .chart-wrapper {
            display: flex;
            gap: 12px;
        }

        .chart-y-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: right;
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
            height: 260px;
            width: 30px;
            padding-right: 8px;
            user-select: none;
        }

        .chart-container {
            flex: 1;
            position: relative;
            height: 260px;
        }

        .chart-grid {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            pointer-events: none;
        }

        .chart-grid-line {
            width: 100%;
            border-bottom: 1px solid #f1f5f9;
        }

        .chart-grid-line:last-child {
            border-bottom-color: #cbd5e1;
        }

        .chart-bars-container {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            align-items: flex-end;
            gap: 0;
            padding: 0 8px;
            /* overflow-x: auto; */
        }

        .chart-bar-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
            padding: 0 2px;
        }

        .chart-bar-label {
            font-size: 11px;
            color: #334155;
            margin-bottom: 4px;
            line-height: 1.1;
        }

        .chart-bar {
            width: 15px;
            background-color: #008C45;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
            height: 0;
            transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .chart-labels {
            margin-top: 8px;
            padding-left: 46px;
            /* overflow-x: auto; */
        }

        .chart-labels-container {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 6px;
            padding: 0;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            user-select: none;
        }

        .chart-labels {
            margin-top: 8px;
            padding-left: 46px;
        }

        .chart-labels-y-space {
            width: 50px;
        }

        .chart-labels-container {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 6px;
            padding: 0;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            user-select: none;
        }

        .chart-label {
            width: 100%;
            text-align: center;
        }

        /* Listas Laterais e Alertas */
        .lista-livros, .lista-generos {
            list-style: none;
        }

        .lista-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--borda);
        }

        .lista-item:last-child {
            border-bottom: none;
        }

        .item-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--azul-escuro);
        }

        .item-info p {
            font-size: 12px;
            color: var(--texto-mutado);
        }

        /* Classes de Animação Ativadas pelo JS */
        .show {
            opacity: 1 !important;
            transform: translateY(0) !important;
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        /* Carousel Styles */
        .carousel-container {
            display: flex;
            align-items: center;
        }

        .carousel-track {
            gap: 12px;
            padding: 0 36px;
        }

        .carousel-item {
            flex-shrink: 0;
        }

        .carousel-btn {
            border: none;
            cursor: pointer;
            user-select: none;
            will-change: transform;
        }

        .carousel-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            box-shadow: none;
            background-color: #94a3b8;
            color: #ffffff;
        }

        .carousel-btn:not(:disabled):active {
            transform: translate(-50%, -50%) scale(0.95);
        }

        .carousel-btn-prev {
            left: 8px;
        }

        .carousel-btn-next {
            right: 8px;
        }

        .carousel-dot {
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .carousel-dot.active {
            width: 0.625rem;
            height: 0.625rem;
            background-color: #008C45;
        }

        .carousel-dot:hover:not(.active) {
            background-color: #cbd5e1;
        }
    </style>
</head>

<body class="pr-0 lg:ml-0 xl:ml-6 2xl:pl-6 2xl:pt-6 lg:px-6">

    <div class="dashboard-header flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 px-6 sm:px-0 pt-24 lg:pt-4 md:pt-12 pb-2 sm:mr-0">

    <section class="flex-1 flex flex-col items-center lg:items-start text-center lg:text-start gap-6">
        <section class="pt-24 lg:pt-0 items-center text-center">
            <div class="space-y-4 flex flex-col items-center lg:items-start text-center lg:text-start animate-panel transform translate-y-12 opacity-0">
                <h2
                    class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold gradient-text"
                    style="font-family: 'Anton', sans-serif;"
                >
                    Gerenciamento de Acervo
                </h2>
                <p
                    class="text-sm sm:text-lg text-gray-600 max-w-2xl"
                    style="font-family: 'Poppins', sans-serif;"
                >
                    Acompanhe e gerencie todos os itens do acervo da sua biblioteca.
                </p>
            </div>
        </section>
    </section>

    <div class="flex justify-start lg:justify-end">
        <div class="date flex items-center gap-3 text-sm sm:text-base border border-[var(--borda-clara)] px-4 py-2 rounded-lg transform translate-y-12 opacity-0 animate-panel bg-[var(--sucesso-claro)] shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 64 64"
                    fill="none"
                    stroke="#4DA73A"
                    stroke-width="4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="w-6 h-6"
                >
                    <!-- Corpo do calendário -->
                    <rect x="10" y="14" width="44" height="40" rx="6"/>

                    <!-- Barra superior -->
                    <path d="M10 24H54"/>

                    <!-- Pinos superiores -->
                    <path d="M20 10V18"/>
                    <path d="M44 10V18"/>

                    <!-- Dias -->
                    <circle cx="20" cy="34" r="2"/>
                    <circle cx="32" cy="34" r="2"/>
                    <circle cx="44" cy="34" r="2"/>

                    <circle cx="20" cy="44" r="2"/>
                    <circle cx="32" cy="44" r="2"/>
                    <circle cx="44" cy="44" r="2"/>
                </svg>
                <span class="font-medium text-gray-500">
                    <?php echo $currentDate; ?>
                </span>
                <!-- <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg> -->
            </div>
        </div>
    </div>

    <div class="cards-grid grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4">

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm flex flex-col justify-between h-full transform translate-y-12 opacity-0 animate-panel hover:shadow-lg transition-all duration-300 ease-in-out">
            <div class="flex items-start gap-4">
                <div class="acervo-metric-icon w-14 h-14 rounded-full flex items-center justify-center border bg-[#EEF7F0] text-green-600 shrink-0">
                    <i class="fas fa-book-open text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Livros Cadastrados</span>
                    <div class="text-3xl font-bold text-ceara-green counter mb-1" data-target="<?php echo $totalBooks; ?>">0</div>
                    <span class="text-xs text-gray-400 block">Total de livros no acervo</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm flex flex-col justify-between h-full transform translate-y-12 opacity-0 animate-panel hover:shadow-lg transition-all duration-300 ease-in-out">
            <div class="flex items-start gap-4">
                <div class="acervo-metric-icon w-14 h-14 rounded-full flex items-center justify-center border bg-[#EEF7F0] text-green-600 shrink-0">
                    <i class="fas fa-check text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Exemplares Disponíveis</span>
                    <div class="text-3xl font-bold text-ceara-green counter mb-1" data-target="<?php echo $totalAvailable; ?>">0</div>
                    <span class="text-xs text-gray-400 block">Exemplares disponíveis</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm flex flex-col justify-between h-[100%] transform translate-y-12 opacity-0 animate-panel hover:shadow-lg transition-all duration-300 ease-in-out">
            <div class="flex items-start gap-4">
                <div class="acervo-metric-icon w-14 h-14 rounded-full flex items-center justify-center border border-orange-100 bg-orange-50 text-[#FFA500] shrink-0">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 64 64"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="w-9 h-9 text-ceara-orange"
                    >
                        <!-- Seta superior -->
                        <path d="M16 24C19 17 24 15 33 15H46" />
                        <path d="M40 9L48 15L40 21" />

                        <!-- Seta inferior -->
                        <path d="M48 40C45 47 40 49 31 49H18" />
                        <path d="M24 43L16 49L24 55" />
                    </svg>
                </div>

                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Empréstimos Ativos</span>
                    <div class="text-3xl font-bold text-ceara-orange counter mb-1" data-target="<?php echo $activeLoans; ?>">0</div>
                    <span class="text-xs text-gray-400 block">Empréstimos realizados</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm flex flex-col justify-between h-full transform translate-y-12 opacity-0 animate-panel hover:shadow-lg transition-all duration-300 ease-in-out">
            <div class="flex items-start gap-4">
                <div class="acervo-metric-icon w-14 h-14 rounded-full flex items-center justify-center border bg-[#EEF7F0] text-green-600 shrink-0">
                    <i class="fas fa-user text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Autores Cadastrados</span>
                    <div class="text-3xl font-bold text-ceara-green counter mb-1" data-target="<?php echo $totalAuthors ?? 326; ?>">0</div>
                    <span class="text-xs text-gray-400 block">Autores no sistema</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm flex flex-col justify-between h-full transform translate-y-12 opacity-0 animate-panel hover:shadow-lg transition-all duration-300 ease-in-out">
            <div class="flex items-start gap-4">
                <div class="acervo-metric-icon w-14 h-14 rounded-full flex items-center justify-center border border-orange-100 bg-orange-50 text-[#FFA500] shrink-0">
                    <i class="fas fa-tags text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Gêneros Literários</span>
                    <div class="text-3xl font-bold text-ceara-orange counter mb-1" data-target="<?php echo $totalGenres; ?>">0</div>
                    <span class="text-xs text-gray-400 block">Gêneros cadastrados</span>
                </div>
            </div>
        </div>

    </div>

    <div class="dashboard-row grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div class="painel-principal animate-panel w-auto h-[400px] relative">
            <div class="painel-title">
                <span class="textLivros">Livros por Gênero</span>
                <select id="donutTypeSelect" name="" class="font-medium text-gray-500 bg-transparent focus:outline-none w-auto h-auto cursor-pointer flex items-center gap-2 border border-gray-300 bg-white px-2 py-1 rounded-lg text-sm sm:text-base shadow-sm hover:shadow-md transition-all duration-300">
                    <option value="category">Por categoria</option>
                    <option value="genre">Por gênero</option>
                </select>
            </div>

            <div class="flex flex-col xl:flex-row w-full min-h-[300px] items-center justify-center xl:gap-4 2xl:gap-8 px-0 lg:px-2">
                <div class="flex-1 flex items-center justify-center">
                    <div class="w-[180px] h-[180px] xl:w-[170px] xl:h-[170px] 2xl:w-[210px] 2xl:h-[210px] justify-center items-center relative" style="transform: translateY(-18px);">
                        <canvas id="genreChart"></canvas>
                    </div>
                </div>

                <div class="flex-1 h-full justify-center flex flex-col gap-4">
                    <ul id="donutLegend" class="lg:space-y-4 text-sm lg:text-md grid grid-cols-2 lg:grid-cols-1 gap-6 lg:gap-3"></ul>
                </div>
            </div>
        </div>

        <div class="painel-principal animate-panel lg:w-full xl:w-full h-[400px] xl:overflow-x-hidden relative">
            <div class="painel-title">
                <span class="textEmprestimos">Empréstimos por Mês</span>
                <select id="periodSelect" name="" class="font-medium text-gray-500 bg-transparent focus:outline-none w-auto h-auto cursor-pointer flex items-center gap-2 border border-gray-300 bg-white px-2 py-1 rounded-lg text-sm sm:text-base shadow-sm hover:shadow-md transition-all duration-300">
                    <option value="month">Este mês</option>
                    <option value="week">Esta semana</option>
                </select>
            </div>
            <div class="chart-wrapper">
                <div class="chart-y-axis">
                    <div>40</div>
                    <div>30</div>
                    <div>20</div>
                    <div>10</div>
                    <div>0</div>
                </div>

                <div class="chart-container">
                    <div class="chart-grid">
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                        <div class="chart-grid-line"></div>
                    </div>

                    <div class="chart-bars-container"></div>
                </div>
            </div>

            <div class="chart-labels">
                <div class="chart-labels-y-space"></div>
                <div class="chart-labels-container"></div>
            </div>
        </div>

        <div class="painel-principal animate-panel lg:w-full xl:w-full h-[400px] overflow-y-auto relative">
            <div class="painel-title">
                <span>Livros Recentemente Cadastrados</span>
            </div>
            <ul class="lista-livros">
                <?php if (!empty($recentBooks)): ?>
                    <?php foreach ($recentBooks as $book): ?>
                        <li class="lista-item">
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($book['titulo_livro']); ?></h4>
                                <p><?php echo htmlspecialchars($book['editora'] ?? 'Editora Geral'); ?></p>
                            </div>
                            <div class="badge-status border border-[#E6E9EB] bg-[#EEF7F0] text-[var(--sucesso)] text-xs font-bold px-2 py-1 rounded-full">
                                Novo
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="lista-item" style="color: var(--texto-mutado); font-size: 13px;">Nenhum livro recente.</li>
                <?php endif; ?>
            </ul>
        </div>


        <div class="dashboard-bottom-grid lg:col-span-3 grid grid-cols-1 gap-6 mb-12">

            <!-- CARD RECURSOS -->
            <div class="painel-principal animate-panel relative">
                <div class="painel-title">
                    <span>Recursos Principais</span>
                </div>

                <!-- Desktop: Grid Layout -->
                <div class="dashboard-resource-grid hidden lg:grid transition-all duration-300">

                    <a href="cadastrar_livro.php?from=gerenciar_acervo" class="dashboard-resource-link border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                        <div class="flex flex-col items-center justify-center h-full gap-2">
                            <i class="fas fa-book-open text-ceara-green text-3xl"></i>
                            <span class="text-center text-gray-700 font-semibold">
                                Cadastrar <br> Livro
                            </span>
                        </div>
                    </a>

                    <a href="consultar_livro.php?from=gerenciar_acervo" class="dashboard-resource-link border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                        <div class="flex flex-col items-center justify-center h-full gap-2">
                            <i class="fas fa-search text-ceara-green text-3xl"></i>
                            <span class="text-center text-gray-700 font-semibold">
                                Consultar <br> Livro
                            </span>
                        </div>
                    </a>

                    <a href="editar_livro.php?from=gerenciar_acervo" class="dashboard-resource-link border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                        <div class="flex flex-col items-center justify-center h-full gap-2">
                            <i class="fas fa-edit text-ceara-green text-3xl"></i>
                            <span class="text-center text-gray-700 font-semibold">
                                Editar <br> Livro
                            </span>
                        </div>
                    </a>

                    <a href="excluir_livro.php?from=gerenciar_acervo" class="dashboard-resource-link border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                        <div class="flex flex-col items-center justify-center h-full gap-2">
                            <i class="fas fa-trash-alt text-ceara-green text-3xl"></i>
                            <span class="text-center text-gray-700 font-semibold text-sm">
                                Excluir <br> Livro
                            </span>
                        </div>
                    </a>

                    <a href="relatorios/decisao.php?from=gerenciar_acervo" class="dashboard-resource-link border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                        <div class="flex flex-col items-center justify-center h-full gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"
                                fill="currentColor" class="w-10 h-10 text-ceara-green">
                                <rect x="8" y="30" width="12" height="26" rx="3"/>
                                <rect x="26" y="12" width="12" height="44" rx="3"/>
                                <rect x="44" y="22" width="12" height="34" rx="3"/>
                            </svg>
                            <span class="text-center text-gray-700 font-semibold">
                                Relatórios <br> do Acervo
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Mobile: Carousel -->
                <div class="lg:hidden relative h-[140px]">
                    <div class="carousel-container relative overflow-hidden w-full h-full">
                        <div class="carousel-track flex transition-transform duration-500 ease-in-out h-full" id="resourcesCarousel">

                            <a href="cadastrar_livro.php?from=gerenciar_acervo" class="carousel-item flex-shrink-0 w-[140px] h-[140px] border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                                <div class="flex flex-col items-center justify-center h-full gap-2">
                                    <i class="fas fa-book-open text-ceara-green text-3xl"></i>
                                    <span class="text-center text-gray-700 font-semibold text-sm">
                                        Cadastrar <br> Livro
                                    </span>
                                </div>
                            </a>

                            <a href="consultar_livro.php?from=gerenciar_acervo" class="carousel-item flex-shrink-0 w-[140px] h-[140px] border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                                <div class="flex flex-col items-center justify-center h-full gap-2">
                                    <i class="fas fa-search text-ceara-green text-3xl"></i>
                                    <span class="text-center text-gray-700 font-semibold text-sm">
                                        Consultar <br> Livro
                                    </span>
                                </div>
                            </a>

                            <a href="editar_livro.php?from=gerenciar_acervo" class="carousel-item flex-shrink-0 w-[140px] h-[140px] border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                                <div class="flex flex-col items-center justify-center h-full gap-2">
                                    <i class="fas fa-edit text-ceara-green text-3xl"></i>
                                    <span class="text-center text-gray-700 font-semibold text-sm">
                                        Editar <br> Livro
                                    </span>
                                </div>
                            </a>

                            <a href="excluir_livro.php?from=gerenciar_acervo" class="carousel-item flex-shrink-0 w-[140px] h-[140px] border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                                <div class="flex flex-col items-center justify-center h-full gap-2">
                                    <i class="fas fa-trash-alt text-ceara-green text-3xl"></i>
                                    <span class="text-center text-gray-700 font-semibold text-sm">
                                        Excluir <br> Livro
                                    </span>
                                </div>
                            </a>

                            <a href="relatorios/decisao.php?from=gerenciar_acervo" class="carousel-item flex-shrink-0 w-[140px] h-[140px] border border-gray-200 bg-gray-50 rounded-lg transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg flex items-center justify-center text-center no-underline">
                                <div class="flex flex-col items-center justify-center h-full gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"
                                        fill="currentColor" class="w-10 h-10 text-ceara-green">
                                        <rect x="8" y="30" width="12" height="26" rx="3"/>
                                        <rect x="26" y="12" width="12" height="44" rx="3"/>
                                        <rect x="44" y="22" width="12" height="34" rx="3"/>
                                    </svg>
                                    <span class="text-center text-gray-700 font-semibold text-sm">
                                        Relatórios <br> do Acervo
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Carousel Controls -->
                    <button id="carouselPrev" class="carousel-btn carousel-btn-prev absolute left-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-ceara-green text-white shadow-lg hover:bg-[#006b34] transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </button>
                    <button id="carouselNext" class="carousel-btn carousel-btn-next absolute right-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-ceara-green text-white shadow-lg hover:bg-[#006b34] transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>

                    <!-- Carousel Indicators -->
                    <div class="flex justify-center gap-2 mt-3">
                        <button class="carousel-dot active w-2 h-2 rounded-full bg-ceara-green transition-all duration-300" data-index="0"></button>
                        <button class="carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-all duration-300" data-index="1"></button>
                        <button class="carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-all duration-300" data-index="2"></button>
                        <button class="carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-all duration-300" data-index="3"></button>
                    </div>
                </div>
            </div>

            <!-- CARD RANK TOP 10 ALUNOS -->
            <div class="painel-principal animate-panel relative">
                <div class="painel-title">
                    <span>Top 10 Alunos Leitores</span>
                </div>

                <div class="p-2 pt-1 space-y-1 overflow-y-auto" style="max-height: calc(100% - 48px);">
                    <?php if (empty($topReaders)): ?>
                        <div class="text-center text-gray-500 text-xl py-8">Nenhum empréstimo foi realizado.</div>
                    <?php endif; ?>
                    <?php foreach ($topReaders as $index => $reader): ?>
                        <div class="flex items-center justify-between pb-2 pt-1 <?php echo $index !== array_key_last($topReaders) ? 'border-b' : ''; ?>">

                            <div class="flex items-center gap-3 text-sm lg:text-md">
                                <div class="w-7 h-7 rounded-full border bg-[#EEF7F0] flex items-center justify-center text-ceara-green">
                                    <i class="fas fa-user"></i>
                                </div>

                                <span class="text-gray-700 <?php echo $index < 3 ? 'font-bold' : ''; ?>">
                                    <?php echo htmlspecialchars($reader['nome']); ?>
                                </span>
                            </div>

                            <div class="pr-2 space-x-1 text-sm lg:text-md">
                                <span class="text-gray-700">Livros:</span>

                                <span class="text-ceara-green font-bold">
                                    <?php echo $reader['total_emprestimos']; ?>
                                </span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function animateCounters() {
            document.querySelectorAll('.counter').forEach(counter => {
                const target = +counter.dataset.target;
                if (target === 0) {
                    counter.textContent = '0';
                    return;
                }
                let start = 0;
                const duration = 1000;
                const stepTime = Math.max(Math.floor(duration / Math.max(target, 1)), 15);

                const updateCount = () => {
                    start += Math.ceil(target / (duration / stepTime));
                    if (start >= target) {
                        counter.textContent = target.toLocaleString('pt-BR');
                        return;
                    }
                    counter.textContent = start.toLocaleString('pt-BR');
                    requestAnimationFrame(updateCount);
                };

                requestAnimationFrame(updateCount);
            });
        }

        function updateYAxisLabels() {
            const yAxis = document.querySelector('.chart-y-axis');
            if (!yAxis) return;
            yAxis.innerHTML = '';
            [40, 30, 20, 10, 0].forEach(value => {
                const label = document.createElement('div');
                label.textContent = value.toString();
                yAxis.appendChild(label);
            });
        }

        function animateChartBars() {
            updateYAxisLabels();
            document.querySelectorAll('.chart-bar').forEach((bar, index) => {
                const heightPercent = Number(bar.dataset.height || 0);
                setTimeout(() => {
                    bar.style.height = heightPercent + '%';
                }, 100 + index * 30);
            });
        }

        function revealPanels() {
            document.querySelectorAll('.animate-panel').forEach((panel, index) => {
                setTimeout(() => {
                    panel.classList.add('show');
                }, 100 + index * 60);
            });
        }

        const monthBarValues = <?php echo json_encode($monthBarValues); ?>;
        const weekBarValues = <?php echo json_encode($weeklyBarValues); ?>;
        const monthLabels = <?php echo json_encode(array_map(function ($m) { return substr($m, 0, 3); }, $months)); ?>;
        const weekLabels = <?php echo json_encode($weekLabels); ?>;

        const categoryDistributionData = <?php echo json_encode($categoryDistribution); ?>;
        const genreDistributionData = <?php echo json_encode($genreDistribution); ?>;

        const donutModeLabels = {
            category: 'Livros por Categoria',
            genre: 'Livros por Gênero'
        };

        // const categoryLabelMap = {
        //     geral: 'Geral',
        //     didatico: 'Didático',
        //     paradidatico: 'Paradidático',
        //     tecnico: 'Técnico'
        // };

        let donutChart = null;
        let activeDonutMode = 'category';
        let donutResizeObserver = null;
        let donutThemeObserver = null;

        function getDonutBorderColor() {
            return document.documentElement.getAttribute('data-theme') === 'dark'
                ? '#17231c'
                : '#ffffff';
        }

        function formatNumber(value) {
            return Number(value).toLocaleString('pt-BR');
        }

        function getDonutDistribution(mode) {
            return mode === 'category' ? categoryDistributionData : genreDistributionData;
        }

        function getDonutTotal(mode) {
            return getDonutDistribution(mode).reduce((sum, item) => sum + (Number(item.total) || 0), 0) || 1;
        }

        // function getCategoryLabel(rawCategory) {
        //     if (!rawCategory) return 'N/A';
        //     const key = String(rawCategory).trim().toLowerCase();
        //     return categoryLabelMap[key] || rawCategory;
        // }

        function renderDonutLegend(mode) {
            const legendElement = document.getElementById('donutLegend');
            if (!legendElement) return;
            legendElement.innerHTML = '';

            const distribution = getDonutDistribution(mode);
            const total = getDonutTotal(mode);

            distribution.forEach((item) => {
                const percentage = total > 0 ? ((Number(item.total) || 0) / total) * 100 : 0;
                const label = item.categoria ?? item.genero ?? 'N/A';

                const li = document.createElement('li');
                li.className = 'flex items-center gap-2';
                li.innerHTML = `
                    <i class="fa-solid fa-circle text-[0.5rem] xl:text-[0.55rem] 2xl:text-[0.75rem]" style="color: ${item.cor || '#008C45'};"></i>
                    <span class="text-gray-700">${label}</span>
                    <span class="ml-auto font-semibold text-gray-900">${formatNumber(item.total)}</span>
                    <span class="text-gray-500">(${Math.round(percentage)}%)</span>
                `;
                legendElement.appendChild(li);
            });
        }

        function scheduleDonutChartRender() {
            const canvas = document.getElementById('genreChart');
            if (!canvas) return;

            window.requestAnimationFrame(() => {
                if (canvas.clientWidth === 0 || canvas.clientHeight === 0) {
                    window.setTimeout(scheduleDonutChartRender, 120);
                    return;
                }
                renderDonutChart(activeDonutMode);
            });
        }

        function initDonutChartResilience() {
            const canvas = document.getElementById('genreChart');
            if (!canvas) return;

            const container = canvas.parentElement;
            if (container && typeof ResizeObserver !== 'undefined') {
                donutResizeObserver?.disconnect();
                donutResizeObserver = new ResizeObserver(() => scheduleDonutChartRender());
                donutResizeObserver.observe(container);
            }

            window.addEventListener('resize', scheduleDonutChartRender);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    scheduleDonutChartRender();
                }
            });
        }

        function renderDonutChart(mode) {
            const ctx = document.getElementById('genreChart');
            const titleElement = document.querySelector('.textLivros');
            if (!ctx || !titleElement) return;

            activeDonutMode = mode;
            const distribution = getDonutDistribution(mode);
            const labels = distribution.map(item => item.categoria ?? item.genero ?? 'N/A');
            const data = distribution.map(item => Number(item.total) || 0);
            const backgroundColors = distribution.map(item => item.cor || '#008C45');

            titleElement.textContent = donutModeLabels[mode] || donutModeLabels.genre;
            renderDonutLegend(mode);

            const centerTextPlugin = {
                id: 'centerText',
                afterDatasetsDraw(chart) {
                    const { width, height, ctx } = chart;
                    ctx.save();

                    const total = chart.data.datasets[0].data.reduce((a, b) => {
                        const aNum = typeof a === 'number' ? a : parseInt(a) || 0;
                        const bNum = typeof b === 'number' ? b : parseInt(b) || 0;
                        return aNum + bNum;
                    }, 0);

                    const x = width / 2;
                    const y = height / 2;

                    ctx.font = 'bold 26px Poppins';
                    ctx.fillStyle = '#008C45';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(total.toLocaleString('pt-BR'), x, y - 12);

                    ctx.font = '14px Poppins';
                    ctx.fillStyle = '#5f6977';
                    ctx.fillText('Total', x, y + 18);

                    ctx.restore();
                }
            };

            if (donutChart) {
                donutChart.destroy();
            }

            donutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: getDonutBorderColor(),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = Number(context.parsed) || 0;
                                    const total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                    return `${label}: ${formatNumber(value)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                },
                plugins: [centerTextPlugin]
            });
        }

        function observeDonutThemeChanges() {
            donutThemeObserver?.disconnect();
            donutThemeObserver = new MutationObserver((mutations) => {
                const themeChanged = mutations.some((mutation) => mutation.attributeName === 'data-theme');
                if (!themeChanged || !donutChart) return;

                donutChart.data.datasets[0].borderColor = getDonutBorderColor();
                donutChart.update();
            });
            donutThemeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });
        }

        function renderBarChart(period) {
            const yAxis = document.querySelector('.chart-y-axis');
            const barContainer = document.querySelector('.chart-bars-container');
            const labelContainer = document.querySelector('.chart-labels-container');
            const titleElement = document.querySelector('.textEmprestimos');
            const periodMode = period === 'week' ? 'week' : 'month';
            const values = periodMode === 'week' ? weekBarValues : monthBarValues;
            const labels = periodMode === 'week' ? weekLabels : monthLabels;
            const axisMax = periodMode === 'month' ? 400 : 40;
            const titleText = periodMode === 'week' ? 'Empréstimos por Semana' : 'Empréstimos por Mês';

            titleElement.textContent = titleText;
            yAxis.innerHTML = '';
            barContainer.innerHTML = '';
            labelContainer.innerHTML = '';
            barContainer.style.gridTemplateColumns = `repeat(${values.length}, minmax(0, 1fr))`;
            labelContainer.style.gridTemplateColumns = `repeat(${labels.length}, minmax(0, 1fr))`;

            values.forEach(value => {
                const wrapper = document.createElement('div');
                wrapper.className = 'chart-bar-wrapper';

                const countLabel = document.createElement('span');
                countLabel.className = 'chart-bar-label';
                countLabel.textContent = value;
                wrapper.appendChild(countLabel);

                const bar = document.createElement('div');
                bar.className = 'chart-bar';
                bar.dataset.value = value;
                bar.style.height = '0%';
                wrapper.appendChild(bar);
                barContainer.appendChild(wrapper);

                setTimeout(() => {
                    const heightPercent = axisMax > 0 ? (value / axisMax) * 100 : 0;
                    bar.style.height = `${heightPercent}%`;
                }, 100);
            });

            const step = axisMax / 4;
            [axisMax, step * 3, step * 2, step, 0].forEach(labelValue => {
                const label = document.createElement('div');
                label.textContent = Number.isInteger(labelValue) ? labelValue : labelValue.toFixed(0);
                yAxis.appendChild(label);
            });

            labels.forEach(labelText => {
                const label = document.createElement('div');
                label.className = 'chart-label';
                label.textContent = labelText;
                labelContainer.appendChild(label);
            });
        }

        function initGenreChart() {
            activeDonutMode = 'category';
            initDonutChartResilience();
            scheduleDonutChartRender();
        }

        // Carousel functionality
        function initCarousel() {
            const carousel = document.getElementById('resourcesCarousel');
            const prevBtn = document.getElementById('carouselPrev');
            const nextBtn = document.getElementById('carouselNext');
            const dots = document.querySelectorAll('.carousel-dot');
            const items = document.querySelectorAll('.carousel-item');
            const itemWidth = 140; // Item width in pixels
            const gap = 12; // Gap between items in pixels
            const itemTotalWidth = itemWidth + gap;
            const maxIndex = Math.max(items.length - 2, 0);
            let currentIndex = 0;

            function updateCarousel() {
                const offset = -(currentIndex * itemTotalWidth);
                carousel.style.transform = 'translateX(' + offset + 'px)';

                // Update dots
                dots.forEach((dot, index) => {
                    if (index === currentIndex) {
                        dot.classList.add('active');
                        dot.style.backgroundColor = '#008C45';
                    } else {
                        dot.classList.remove('active');
                        dot.style.backgroundColor = '#d1d5db';
                    }
                });

                // Update button states
                if (prevBtn) {
                    prevBtn.disabled = currentIndex === 0;
                }
                if (nextBtn) {
                    nextBtn.disabled = currentIndex === maxIndex;
                }
            }

            function nextSlide() {
                if (currentIndex < maxIndex) {
                    currentIndex++;
                    updateCarousel();
                }
            }

            function prevSlide() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateCarousel();
                }
            }

            prevBtn?.addEventListener('click', prevSlide);
            nextBtn?.addEventListener('click', nextSlide);

            dots.forEach((dot) => {
                dot.addEventListener('click', (e) => {
                    currentIndex = parseInt(e.target.dataset.index, 10);
                    updateCarousel();
                });
            });

            // Initialize carousel state
            updateCarousel();

            // Optional: Auto-scroll carousel every 5 seconds
            // setInterval(nextSlide, 5000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            revealPanels();
            animateCounters();
            initGenreChart();
            observeDonutThemeChanges();
            renderBarChart('month');
            initCarousel();

            const periodSelect = document.getElementById('periodSelect');
            if (periodSelect) {
                periodSelect.addEventListener('change', (event) => {
                    const selectedPeriod = event.target.value;
                    document.querySelector('.chart-y-axis').innerHTML = '';
                    renderBarChart(selectedPeriod);
                });
            }

            const donutTypeSelect = document.getElementById('donutTypeSelect');
            if (donutTypeSelect) {
                donutTypeSelect.addEventListener('change', (event) => {
                    renderDonutChart(event.target.value || 'category');
                });
            }
        });

</script>
</body>

</html>
<?php
                break;
            default:
                throw new InvalidArgumentException('Página da biblioteca não incorporada: ' . $embeddedPage);
        }
    };
$embeddedPageDirectories = array_filter(
    $libraryPageDirectories,
    static function ($page) {
        return strpos($page, 'views/QRCode/') !== 0 && strpos($page, 'views/relatorios/') !== 0;
    },
    ARRAY_FILTER_USE_KEY
);
$renderLibraryPage = static function ($page, $directory, $pageScope) use ($embeddedPageDirectories, $renderEmbeddedPage) {
    if (isset($embeddedPageDirectories[$page])) {
        $renderEmbeddedPage($page, $directory, $pageScope);
        return;
    }

    $viewPath = __DIR__ . '/' . $page;
    if (!is_file($viewPath)) {
        http_response_code(500);
        error_log('Página da biblioteca não encontrada: ' . $viewPath);
        throw new RuntimeException('Não foi possível carregar esta página da biblioteca.');
    }

    extract($pageScope, EXTR_SKIP);
    require $viewPath;
};
$renderSectionContent = static function ($sectionId, $pageScope) use ($sectionContents, $libraryPageDirectories, $renderLibraryPage) {
    $page = $sectionContents[$sectionId]['view'];
    $directory = $libraryPageDirectories[$page];
    $viewPath = __DIR__ . '/' . $page;
    $previousDirectory = getcwd();
    if ($previousDirectory === false || !chdir($directory)) {
        throw new RuntimeException('Não foi possível preparar a página da biblioteca: ' . $directory);
    }

    $bufferLevel = ob_get_level();
    ob_start();
    try {
        $renderLibraryPage($page, $directory, $pageScope);
        $content = ob_get_clean();
    } catch (Throwable $exception) {
        while (ob_get_level() > $bufferLevel) {
            ob_end_clean();
        }
        throw $exception;
    } finally {
        if (!chdir($previousDirectory)) {
            throw new RuntimeException('Não foi possível restaurar o diretório após renderizar a página da biblioteca: ' . $viewPath);
        }
    }

    preg_match('/<body\b([^>]*)>/i', $content, $bodyMatch);
    $bodyAttributes = $bodyMatch[1] ?? '';
    preg_match('/\bclass=(["\'])(.*?)\1/i', $bodyAttributes, $bodyClassMatch);
    preg_match('/\bstyle=(["\'])(.*?)\1/i', $bodyAttributes, $bodyStyleMatch);
    $bodyClass = htmlspecialchars($bodyClassMatch[2] ?? '', ENT_QUOTES, 'UTF-8');
    $bodyStyle = htmlspecialchars($bodyStyleMatch[2] ?? '', ENT_QUOTES, 'UTF-8');
    $content = preg_replace('/<!doctype[^>]*>|<\/?(?:html|head|body)\b[^>]*>|<title\b[^>]*>.*?<\/title>/is', '', $content);

    return '<div class="library-section-content ' . $bodyClass . '" style="' . $bodyStyle . '">' . $content . '</div>';
};
$mainUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/') . '/';
$portalMainUrl = dirname(dirname(dirname(dirname(rtrim($mainUrl, '/'))))) . '/main/';
$portalExitUrl = $portalMainUrl . 'views/' . ($portalSessionActive ? 'subsystems.php' : 'autenticacao/login.php');
$pageRoute = $_GET['page'] ?? null;
if ($pageRoute !== null) {
    if (!is_string($pageRoute) || !isset($libraryPageDirectories[$pageRoute])) {
        http_response_code(404);
        exit('Página da biblioteca não encontrada.');
    }

    $previousDirectory = getcwd();
    $embeddedDirectory = $libraryPageDirectories[$pageRoute];
    if ($previousDirectory === false || !chdir($embeddedDirectory)) {
        throw new RuntimeException('Não foi possível preparar a página da biblioteca: ' . $embeddedDirectory);
    }

    $pageScope = get_defined_vars();
    $renderLibraryPage($pageRoute, $embeddedDirectory, $pageScope);
    if (!chdir($previousDirectory)) {
        throw new RuntimeException('Não foi possível restaurar o diretório após renderizar a página da biblioteca.');
    }
    exit;
}
$currentSection = $_GET['section'] ?? 'dashboard';
if (!is_string($currentSection) || ($currentSection !== 'dashboard' && !isset($sectionContents[$currentSection]))) {
    http_response_code(404);
    exit('Seção da biblioteca não encontrada.');
}

if (isset($_GET['qr_livro']) && is_scalar($_GET['qr_livro'])) {
    header('Location: ' . $mainUrl . 'index.php?page=' . rawurlencode('views/emprestimo/decisao.php') . '&id_livro=' . rawurlencode((string) $_GET['qr_livro']), true, 303);
    exit;
}

if (
    $currentSection === 'qrcode_selecionar_livros'
    && ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['titulo2']) || empty($_POST['titulo2']))
) {
    header('Location: ' . $mainUrl . 'index.php?section=qrcode_especifico_livro', true, 303);
    exit;
}

if ($currentSection === 'relatorio_geral_turma' && empty($_GET['turma'])) {
    header('Location: ' . $mainUrl . 'index.php?section=geral_turma', true, 303);
    exit;
}
$sectionQueryParams = $_GET;
unset($sectionQueryParams['section'], $sectionQueryParams['partial']);
$sectionQueryString = http_build_query($sectionQueryParams);

$pdfView = null;
$pdfSectionViews = [
    'relatorio_emprestimo' => 'views/relatorios/relatorio_emprestimo.php',
    'relatorio_acervo' => 'views/relatorios/relatorio_acervo.php',
    'relatorio_por_livro' => 'views/relatorios/relatorio_livros.php',
    'relatorio_pra_est' => 'views/relatorios/relatorio_pra_est.php'
];
if (in_array($currentSection, ['relatorio_emprestimo', 'relatorio_acervo', 'relatorio_por_livro'], true)) {
    $pdfView = $pdfSectionViews[$currentSection];
} elseif ($currentSection === 'relatorio_pra_est' && isset($_GET['estante'], $_GET['prateleira'])) {
    $pdfView = $pdfSectionViews[$currentSection];
} elseif ($currentSection === 'qrcode_geral' && isset($_GET['estante'], $_GET['prateleira'])) {
    $pdfView = 'views/QRCode/QRCodes.php';
} elseif ($currentSection === 'qrcode_especifico' && isset($_GET['gerar'])) {
    $pdfView = 'views/QRCode/QRCodes_especifico.php';
} elseif ($currentSection === 'qrcode_especifico_livro' && isset($_GET['gerar']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdfView = 'views/QRCode/QRCode_especifico_livro.php';
}

if ($pdfView !== null) {
    $pdfViewPath = __DIR__ . '/' . $pdfView;
    $previousDirectory = getcwd();
    $embeddedDirectory = dirname($pdfViewPath);
    if ($previousDirectory === false || !chdir($embeddedDirectory)) {
        throw new RuntimeException('Não foi possível preparar o relatório da biblioteca: ' . $embeddedDirectory);
    }

    $pageScope = get_defined_vars();
    $renderLibraryPage($pdfView, $embeddedDirectory, $pageScope);
    if (!chdir($previousDirectory)) {
        throw new RuntimeException('Não foi possível restaurar o diretório após gerar o relatório da biblioteca.');
    }
    exit;
}
if (isset($_GET['partial']) && $_GET['partial'] === '1') {
    header('Content-Type: text/html; charset=UTF-8');
    $pageScope = get_defined_vars();
    echo $renderSectionContent($currentSection, $pageScope);
    exit;
}
require_once __DIR__ . '/models/select_model.php';
$selectModel = new select_model();
$totalBooks = $selectModel->count_catalogo_total();
$totalLoans = $selectModel->count_emprestimos_total();
$totalStudents = $selectModel->count_alunos_total();
?> 
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <base href="<?= htmlspecialchars($mainUrl . ($currentSection === 'dashboard' ? '' : dirname($sectionContents[$currentSection]['view']) . '/'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" href="<?= htmlspecialchars($mainUrl . 'assets/img/icon.png', ENT_QUOTES, 'UTF-8') ?>" type="image/x-icon">
    <title>Sistema Biblioteca</title>
    <script>
        (() => {
            let theme = 'light';
            try {
                const storedTheme = localStorage.getItem('biblioteca-theme');
                theme = storedTheme === 'dark'
                    ? 'dark'
                    : (storedTheme === null && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            } catch (error) {
                console.warn('Não foi possível carregar a preferência do tema:', error);
            }
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = theme;
        })();
    </script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ceara-green': '#008C45',
                        'ceara-orange': '#FFA500',
                        'ceara-white': '#FFFFFF',
                        primary: '#4CAF50',
                        secondary: '#FFB74D',
                    },
                },
            },
        };
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Anton&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Poppins', sans-serif;        
        }
        .gradient-text {
            background: linear-gradient(45deg, #008C45, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        html, body {
            overflow-x: hidden;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .footer-link {
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            color: #FFA500;
            transform: translateY(-2px);
        }

        /* Menu lateral estilos */
        .sidebar {
            transition: transform 0.3s ease, width 0.3s ease;
        }

        #sidebar .sidebar-bottom {
            margin-top: auto;
            padding: 12px 8px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
        }

        #sidebar .sidebar-logout {
            display: flex;
            min-height: 40px;
            align-items: center;
            gap: 12px;
            padding: 0 11px;
            border-radius: 7px;
            color: #f0d5ce;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 180ms ease, color 180ms ease;
        }

        #sidebar .sidebar-logout i {
            width: 16px;
            color: #f1aa91;
            text-align: center;
        }

        #sidebar .sidebar-logout:hover,
        #sidebar .sidebar-logout:focus-visible {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        #sidebar .sidebar-logout:focus-visible {
            outline: 2px solid #ffc04d;
            outline-offset: 2px;
        }

        #sidebar .sidebar-logout:hover i,
        #sidebar .sidebar-logout:focus-visible i {
            color: #ffc04d;
        }

        .sidebar.-translate-x-full {
            transform: translateX(-100%);
        }

        .sidebar-text {
            transition: opacity 0.3s ease;
        }


        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .submenu.active {
            max-height: 500px;
        }

        .menu-item {
            transition: all 0.2s ease;
        }

        .menu-item {
            border-radius: 0.8rem;
            transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(0.5rem);
        }

        .menu-item.active,
        .menu-item.active:hover {
            background-color: rgba(255, 165, 0, 0.18);
            color: #FFA500;
        }

        .menu-item.active i,
        .menu-item.active .sidebar-text {
            color: #FFA500;
        }

        /* make icons follow text color on hover */
        .menu-item i {
            color: inherit;
            transition: color 0.18s ease, transform 0.18s ease;
        }

        .submenu .menu-item {
            padding-left: 2.5rem;
        }

        @media (min-width: 1024px) {
            body {
                grid-template-columns: 256px minmax(0, 1fr);
                transition: grid-template-columns 220ms cubic-bezier(0.2, 0.75, 0.3, 1);
            }

            #footer {
                left: 256px;
            }

            #sidebar {
                display: flex;
                width: 256px;
                height: 100vh;
                min-height: 100vh;
                max-height: none;
                flex-direction: column;
                padding: 22px 16px 18px;
                overflow: hidden;
                background: #064c2b;
                color: #f5fff8;
                border-right: 1px solid rgba(209, 231, 219, 0.18);
                scrollbar-color: #3c7254 transparent;
                scrollbar-width: thin;
                transition: transform 220ms cubic-bezier(0.2, 0.75, 0.3, 1);
            }

            body > .flex-1.flex.flex-col.min-h-screen {
                grid-column: 2;
                min-width: 0;
            }

            #main-content {
                width: 100% !important;
                margin-left: 0 !important;
            }

            #sidebar > div:first-child {
                justify-content: flex-start;
                margin: 2px 7px 24px;
                padding: 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.14);
                padding-bottom: 18px;
            }

            #sidebar > div:first-child > div:first-child {
                gap: 11px;
            }

            #sidebar > div:first-child img {
                width: 40px;
                height: 40px;
                border: 1px solid rgba(255, 255, 255, 0.33);
                border-radius: 11px;
                padding: 4px;
            }

            #sidebar > div:first-child h1 {
                margin: 0;
                color: #fff;
                font-size: 15px;
                letter-spacing: 0;
            }

            #sidebar > div:first-child > .h-0\.5 {
                display: none;
            }

            #sidebar > nav {
                flex: 1;
                min-height: 0;
                padding: 0 8px 0 0;
                overflow-y: auto;
                overscroll-behavior: contain;
                scrollbar-gutter: stable;
            }

            #sidebar .menu-group {
                margin-bottom: 16px;
            }

            #sidebar .menu-toggle {
                position: relative;
                min-height: 40px;
                margin: 3px 0;
                padding: 0 11px;
                border-radius: 7px;
                color: #e1f0e6;
                text-align: left;
                font-size: 13px;
                font-weight: 600;
                transition: background-color 180ms ease, color 180ms ease, transform 180ms ease;
            }

            #sidebar .menu-toggle i {
                color: #c5dfce;
            }

            #sidebar .menu-toggle:hover {
                transform: none;
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
            }

            #sidebar .menu-toggle.active,
            #sidebar .menu-toggle.active:hover {
                background: #276448;
                color: #ffc04d;
            }

            #sidebar .menu-toggle.active::before {
                position: absolute;
                inset: 8px auto 8px 0;
                width: 3px;
                border-radius: 0 3px 3px 0;
                background: #f3a11a;
                content: "";
            }

            #sidebar .menu-toggle.active i {
                color: #ffc04d;
            }

            #sidebar .menu-item {
                min-height: 38px;
                margin: 2px 0;
                padding-top: 8px;
                padding-bottom: 8px;
                border-radius: 7px;
                color: #e1f0e6;
                font-size: 12px;
                font-weight: 600;
                transition: background-color 180ms ease, color 180ms ease;
            }

            #sidebar .menu-item:hover {
                transform: none;
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
            }

            #sidebar .menu-item.active,
            #sidebar .menu-item.active:hover {
                position: relative;
                background: #276448;
                color: #ffc04d;
            }

            #sidebar .menu-item.active::before {
                position: absolute;
                inset: 8px auto 8px 0;
                width: 3px;
                border-radius: 0 3px 3px 0;
                background: #f3a11a;
                content: "";
            }

            #sidebar .menu-item i {
                color: #c5dfce;
            }

            #sidebar .menu-item.active i {
                color: #ffc04d;
            }

            #sidebar .submenu .menu-item {
                padding-left: 2rem;
            }

            body.library-sidebar-collapsed {
                grid-template-columns: minmax(0, 1fr);
            }

            body.library-sidebar-collapsed > .flex-1.flex.flex-col.min-h-screen {
                grid-column: 1;
            }

            body.library-sidebar-collapsed #sidebar {
                transform: translateX(-100%);
            }

            body.library-sidebar-collapsed #sidebar-collapse-toggle {
                left: 0;
            }

            body.library-sidebar-collapsed #footer {
                left: 0;
            }
        }

        #sidebar-collapse-toggle {
            position: fixed;
            top: 50%;
            left: 255px;
            z-index: 50;
            display: grid;
            width: 30px;
            height: 72px;
            place-items: center;
            padding: 0;
            border: 1px solid rgba(255, 255, 255, 0.33);
            border-left: 0;
            border-radius: 0 10px 10px 0;
            background: linear-gradient(145deg, #0b7042, #064c2b);
            color: #ffd06a;
            box-shadow: 4px 0 12px rgba(5, 45, 27, 0.25), inset -1px 0 rgba(255, 255, 255, 0.33);
            transform: translateY(-50%);
            cursor: pointer;
            transition: left 220ms ease, width 180ms ease, background 180ms ease, box-shadow 180ms ease;
        }

        #sidebar-collapse-toggle::before {
            position: absolute;
            top: 8px;
            bottom: 8px;
            left: 3px;
            width: 1px;
            background: rgba(255, 255, 255, 0.29);
            content: "";
        }

        #sidebar-collapse-toggle:hover {
            width: 35px;
            background: linear-gradient(145deg, #0d7a49, #075b34);
            box-shadow: 5px 0 15px rgba(5, 45, 27, 0.33), inset -1px 0 rgba(255, 255, 255, 0.44);
        }

        #sidebar-collapse-toggle:focus-visible {
            outline: 2px solid #f3a11a;
            outline-offset: 2px;
        }

        #sidebar-collapse-toggle i {
            transition: transform 220ms ease;
        }

        #sidebar-collapse-toggle:hover i,
        body.library-sidebar-collapsed #sidebar-collapse-toggle i {
            transform: rotate(180deg);
        }

        body.library-sidebar-collapsed #sidebar-collapse-toggle:hover i {
            transform: rotate(0deg);
        }

        @media (max-width: 1023px) {
            #sidebar-collapse-toggle {
                display: none;
            }

            #sidebar.max-h-screen {
                overflow-y: auto;
                padding-bottom: 4.25rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            body {
                transition: grid-template-columns 0s;
            }

            #sidebar,
            #sidebar-collapse-toggle,
            #sidebar-collapse-toggle i {
                transition: none;
            }
        }

        .menu-section:not(.active) {
            display: none;
        }

        .menu-section.active {
            display: block;
            animation: section-enter 180ms ease-out both;
        }

        #section-dashboard {
            background: linear-gradient(to bottom right, #fff, #f3f4f6);
        }

        #section-gerenciar_acervo {
            background: transparent;
        }

        #section-dashboard.menu-section.active {
            display: flex;
            justify-content: center;
        }

        .dashboard-guide-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            margin-top: 0.5rem;
            padding: 0.8rem 1.25rem;
            border: 1px solid rgba(0, 122, 51, 0.18);
            border-radius: 9999px;
            background: #fff;
            color: #007a33;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.1);
            transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        }

        .dashboard-guide-link:hover,
        .dashboard-guide-link:focus-visible {
            transform: translateY(-2px);
            background: #f0fdf4;
            box-shadow: 0 12px 26px rgba(0, 122, 51, 0.18);
        }

        .dashboard-guide-link:focus-visible {
            outline: 3px solid #f59e0b;
            outline-offset: 3px;
        }

        html[data-theme="dark"] .dashboard-guide-link {
            border-color: #385541;
            background: #17231c;
            color: #c8f3d5;
        }

        html[data-theme="dark"] .dashboard-guide-link:hover,
        html[data-theme="dark"] .dashboard-guide-link:focus-visible {
            background: #203b2a;
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.28);
        }

        #main-content {
            height: 100vh;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding-bottom: 3.25rem;
            scrollbar-gutter: stable;
            background-color: #f8fafc;
            background-image: url("<?= htmlspecialchars($mainUrl . 'assets/img/layout.png', ENT_QUOTES, 'UTF-8') ?>");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
        }

        html,
        body {
            height: 100%;
        }

        body {
            min-height: 0;
            overflow: hidden;
        }

        body > .flex-1.flex.flex-col.min-h-screen {
            height: 100vh;
            min-height: 0;
            overflow: hidden;
        }

        #main-content .library-section-content {
            box-sizing: border-box;
            min-height: calc(100vh - 3.25rem) !important;
            background-color: transparent !important;
            background-image: none !important;
        }

        #main-content .library-section-content.p-4 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        @media (min-width: 1536px) and (min-height: 900px) {
            #section-gerenciar_acervo .library-section-content {
                padding-top: 0 !important;
            }

            #section-gerenciar_acervo .dashboard-header {
                margin-bottom: 1.5rem;
            }
        }

        #main-content .library-section-content .bg-gradient-ceara {
            background: linear-gradient(135deg, #008c45, #006b35) !important;
            color: #fff !important;
        }

        #main-content .library-section-content [class~="bg-[#007A33]"] {
            background-color: #007a33 !important;
            color: #fff !important;
        }

        #main-content .library-section-content .bg-gradient-ceara h1,
        #main-content .library-section-content .bg-gradient-ceara h2,
        #main-content .library-section-content .bg-gradient-ceara h3,
        #main-content .library-section-content [class~="bg-[#007A33]"] h1,
        #main-content .library-section-content [class~="bg-[#007A33]"] h2,
        #main-content .library-section-content [class~="bg-[#007A33]"] h3 {
            color: #fff !important;
        }

        @keyframes section-enter {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .menu-section.active {
                animation: none;
            }
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            background: #0d1510;
            color: #e5eee8;
            --branco: #17231c;
            --borda: #35483c;
            --borda-clara: #3a5d47;
            --sucesso-claro: #1b2921;
            --texto-escuro: #e5eee8;
            --texto-mutado: #9eafa3;
            --azul-escuro: #e5eee8;
        }

        html[data-theme="dark"] body {
            background: #0d1510 !important;
            color: #e5eee8 !important;
        }

        html[data-theme="dark"] #main-content {
            background-color: #0d1510;
            background-image: url("<?= htmlspecialchars($mainUrl . 'assets/img/layout_dark.png', ENT_QUOTES, 'UTF-8') ?>");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            color: #e5eee8;
        }

        html[data-theme="dark"] #section-dashboard {
            background: #0d1510;
        }

        html[data-theme="dark"] #section-gerenciar_acervo {
            background: transparent;
        }

        html[data-theme="dark"] #main-content .library-section-content {
            background-color: transparent !important;
            background-image: none !important;
        }

        html[data-theme="dark"] #section-geral_turma .library-section-content .glass-effect {
            border: 1px solid #304338;
            background: linear-gradient(145deg, #1b2921 0%, #111b15 100%);
            color: #e5eee8;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .28);
            backdrop-filter: blur(10px);
        }

        html[data-theme="dark"] #section-geral_turma .library-section-content label {
            color: #d3ded6 !important;
        }

        html[data-theme="dark"] #section-geral_turma .library-section-content select {
            border-color: #405449 !important;
            background-color: #111b15 !important;
            color: #e5eee8 !important;
            color-scheme: dark;
        }

        html[data-theme="dark"] #section-geral_turma .library-section-content option {
            background-color: #17231c;
            color: #e5eee8;
        }

        html[data-theme="dark"] #section-emprestimo_livro #emprestimoForm,
        html[data-theme="dark"] #section-devolucao_livro #devolucaoForm {
            border: 1px solid #304338;
            background: linear-gradient(145deg, #1b2921 0%, #111b15 100%);
            color: #e5eee8;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .28);
            backdrop-filter: blur(10px);
        }

        html[data-theme="dark"] #section-emprestimo_livro #emprestimoForm [role="alert"].bg-red-50,
        html[data-theme="dark"] #section-devolucao_livro #devolucaoForm [role="alert"].bg-red-50 {
            border-color: #754343 !important;
            background-color: #351f20 !important;
            color: #ffd1d1 !important;
        }

        html[data-theme="dark"] #section-devolucao_livro #devolucaoForm [role="alert"].bg-green-50 {
            border-color: #385541 !important;
            background-color: #1c3a29 !important;
            color: #a6deb9 !important;
        }

        html[data-theme="dark"] #sidebar {
            background: #07110b !important;
            border-right-color: #26382c;
        }

        html[data-theme="dark"] #sidebar .sidebar-bottom {
            border-top-color: #26382c;
        }

        html[data-theme="dark"] #sidebar .menu-item.active,
        html[data-theme="dark"] #sidebar .menu-toggle.active {
            background-color: #173c27;
        }

        html[data-theme="dark"] #main-content .painel-principal,
        html[data-theme="dark"] #main-content .cards-section,
        html[data-theme="dark"] #main-content .card-indicador,
        html[data-theme="dark"] #main-content .card-hover,
        html[data-theme="dark"] #main-content .hierarchy-card {
            border-color: #304338 !important;
            background-color: #17231c !important;
            color: #e5eee8 !important;
            box-shadow: 0 8px 22px rgba(0, 0, 0, .2);
        }

        html[data-theme="dark"] #section-gerenciar_acervo .acervo-metric-icon {
            border-color: #405449 !important;
            background-color: #26382c !important;
        }

        html[data-theme="dark"] #main-content .hierarchy-card-header,
        html[data-theme="dark"] #main-content .genero-item,
        html[data-theme="dark"] #main-content .genero-item:hover {
            border-color: #35483c !important;
            background-color: #1b2921 !important;
            color: #e5eee8 !important;
        }

        html[data-theme="dark"] #main-content .hierarchy-card-header:hover {
            border-color: #4b7659 !important;
            background-color: #22352a !important;
        }

        html[data-theme="dark"] #main-content .categoria-name,
        html[data-theme="dark"] #main-content .genero-name {
            color: #e5eee8 !important;
        }

        html[data-theme="dark"] #main-content .categoria-label,
        html[data-theme="dark"] #main-content .genero-label {
            color: #9eafa3 !important;
        }

        html[data-theme="dark"] #main-content #features {
            background: linear-gradient(180deg, #17251c 0%, #101a14 100%) !important;
        }

        html[data-theme="dark"] #main-content [class~="bg-white"],
        html[data-theme="dark"] #main-content [class~="bg-gray-50"],
        html[data-theme="dark"] #main-content [class~="bg-gray-100"],
        html[data-theme="dark"] #main-content [class~="bg-gray-200"] {
            border-color: #35483c !important;
            background-color: #1b2921 !important;
        }

        html[data-theme="dark"] #section-consultar_livro #badgeCategoria.bg-gray-300,
        html[data-theme="dark"] #section-consultar_livro #badgeFiccao.bg-gray-300,
        html[data-theme="dark"] #section-consultar_livro #badgeLiteratura.bg-gray-300,
        html[data-theme="dark"] #section-consultar_livro #badgeCativo.bg-gray-300 {
            border: 1px solid #405449;
            background-color: #35483c !important;
            color: #e5eee8 !important;
        }

        html[data-theme="dark"] #main-content [class*="text-gray-"],
        html[data-theme="dark"] #main-content [class*="text-slate-"] {
            color: #cbd8cf !important;
        }

        html[data-theme="dark"] #main-content [class*="text-gray-400"],
        html[data-theme="dark"] #main-content [class*="text-gray-500"],
        html[data-theme="dark"] #main-content [class*="text-slate-400"],
        html[data-theme="dark"] #main-content [class*="text-slate-500"] {
            color: #9eafa3 !important;
        }

        html[data-theme="dark"] #main-content input:not([type="checkbox"]):not([type="radio"]),
        html[data-theme="dark"] #main-content select,
        html[data-theme="dark"] #main-content textarea {
            border-color: #405449 !important;
            background-color: #111b15 !important;
            color: #e5eee8 !important;
            color-scheme: dark;
        }

        html[data-theme="dark"] #main-content input::placeholder,
        html[data-theme="dark"] #main-content textarea::placeholder {
            color: #91a196 !important;
        }

        html[data-theme="dark"] #main-content table th,
        html[data-theme="dark"] #main-content table td,
        html[data-theme="dark"] #main-content hr {
            border-color: #35483c !important;
        }

        html[data-theme="dark"] #main-content .painel-title,
        html[data-theme="dark"] #main-content .painel-title span,
        html[data-theme="dark"] #main-content h1:not(.gradient-text):not(.text-white),
        html[data-theme="dark"] #main-content h2:not(.gradient-text):not(.text-white),
        html[data-theme="dark"] #main-content h3:not(.gradient-text):not(.text-white) {
            color: #e5eee8;
        }

        html[data-theme="dark"] #main-content .library-section-content .bg-gradient-ceara,
        html[data-theme="dark"] #main-content .library-section-content [class~="bg-[#007A33]"] {
            border-radius: .75rem .75rem 0 0;
            color: #fff;
        }

        html[data-theme="dark"] #main-content .library-section-content .bg-gradient-ceara h1,
        html[data-theme="dark"] #main-content .library-section-content .bg-gradient-ceara h2,
        html[data-theme="dark"] #main-content .library-section-content .bg-gradient-ceara h3,
        html[data-theme="dark"] #main-content .library-section-content [class~="bg-[#007A33]"] h1,
        html[data-theme="dark"] #main-content .library-section-content [class~="bg-[#007A33]"] h2,
        html[data-theme="dark"] #main-content .library-section-content [class~="bg-[#007A33]"] h3 {
            color: #fff !important;
        }

        html[data-theme="dark"] #main-content .item-info h4,
        html[data-theme="dark"] #main-content .item-info p {
            color: #d3ded6;
        }

        html[data-theme="dark"] #main-content .badge-status {
            border-color: #385541 !important;
            background-color: #1c3a29 !important;
            color: #a6deb9 !important;
        }

        html[data-theme="dark"] #main-content .select2-container--default .select2-selection--single,
        html[data-theme="dark"] #main-content .select2-container--default .select2-selection--multiple {
            border-color: #405449 !important;
            background-color: #111b15 !important;
            color: #e5eee8 !important;
        }

        html[data-theme="dark"] #main-content .select2-container--default .select2-selection--single .select2-selection__rendered,
        html[data-theme="dark"] #main-content .select2-container--default .select2-selection--multiple .select2-selection__rendered,
        html[data-theme="dark"] #main-content .select2-container--default .select2-selection__placeholder {
            color: #cbd8cf !important;
        }

        html[data-theme="dark"] #main-content .select2-container--default .select2-selection--multiple .select2-selection__choice {
            border-color: #405449 !important;
            background-color: #1b2921 !important;
            color: #e5eee8 !important;
        }

        html[data-theme="dark"] .select2-dropdown {
            border-color: #405449 !important;
            background-color: #17231c !important;
            color: #e5eee8 !important;
        }

        html[data-theme="dark"] .select2-container--default .select2-results__option {
            background-color: #17231c;
            color: #d3ded6;
        }

        html[data-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #08723d !important;
            color: #fff !important;
        }

        html[data-theme="dark"] .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #1c3a29;
            color: #e5eee8;
        }

        html[data-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
            border-color: #405449 !important;
            background-color: #111b15 !important;
            color: #e5eee8 !important;
        }

        html[data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #9eafa3 transparent transparent transparent;
        }

        #library-theme-toggle {
            position: fixed;
            bottom: 4.25rem;
            right: 1.25rem;
            z-index: 60;
            display: grid;
            width: 2.75rem;
            height: 2.75rem;
            place-items: center;
            border: 1px solid rgba(8, 100, 57, .2);
            border-radius: 9999px;
            background: linear-gradient(135deg, #fff 0%, #f0faf3 100%);
            color: #08723d;
            box-shadow: 0 5px 16px rgba(15, 65, 39, .16);
            cursor: pointer;
            transition: transform .2s ease, background .2s ease, color .2s ease;
        }

        #library-theme-toggle:hover {
            transform: translateY(-2px);
        }

        #library-theme-toggle:focus-visible {
            outline: 3px solid #f3a11a;
            outline-offset: 3px;
        }

        html[data-theme="dark"] #library-theme-toggle {
            border-color: rgba(255, 255, 255, .14);
            background: linear-gradient(135deg, #214b35 0%, #172d21 100%);
            color: #ffd06a;
        }

        @media (max-width: 600px) {
            #library-theme-toggle {
                bottom: 5rem;
                right: .75rem;
                width: 2.5rem;
                height: 2.5rem;
            }
        }

        .library-footer {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 45;
            display: flex;
            min-height: 3.25rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: .35rem .65rem;
            width: auto !important;
            margin: 0;
            padding: .75rem 1rem;
            background: #fff;
            color: #64748b;
            box-shadow: 0 -4px 18px rgba(0, 0, 0, .08);
            font-size: .8rem;
        }

        .library-footer::before {
            position: absolute;
            inset: 0 0 auto;
            height: 3px;
            background: linear-gradient(90deg, #008c45, #f6c400 48%, #ed8500 70%, #008c45);
            content: "";
        }

        .library-footer a {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            color: #1e293b;
            font-weight: 600;
            text-decoration: none;
            transition: color .2s ease, transform .2s ease;
        }

        .library-footer a:hover,
        .library-footer a:focus-visible {
            color: #008c45;
            transform: translateY(-2px);
        }

        html[data-theme="dark"] .library-footer {
            background: #111b15;
            color: #cbd8cf;
            box-shadow: 0 -4px 18px rgba(0, 0, 0, .3);
        }

        html[data-theme="dark"] .library-footer a {
            color: #e5eee8;
        }

        html[data-theme="dark"] .library-footer a:hover,
        html[data-theme="dark"] .library-footer a:focus-visible {
            color: #76d89e;
        }

    </style>
    <link rel="stylesheet" href="<?= htmlspecialchars($mainUrl . 'assets/css/library-navigation.css', ENT_QUOTES, 'UTF-8') ?>?v=<?= (int) filemtime(__DIR__ . '/assets/css/library-navigation.css') ?>-<?= substr(md5_file(__DIR__ . '/assets/css/library-navigation.css'), 0, 8) ?>">
</head>

<body class="bg-gradient-to-br from-ceara-white to-gray-100 text-gray-800 select-none flex flex-col relative items-center m-0 p-0 grid">

    <!-- MENU LATERAL -->
    <aside id="sidebar" class="sidebar w-full lg:w-[16vw] bg-[#075c2c] text-white shadow-lg fixed left-0 top-0 max-h-[100px]  lg:min-h-screen overflow-hidden lg:overflow-y-auto transition-[max-height] duration-500 ease-in-out z-40">
        <div class="p-4 lg:p-3 flex items-center justify-between lg:justify-center pt-6 lg:pt-8">
            <div class="flex items-center lg:justify-start">
                <img class="object-cover size-12 lg:size-12" src="<?= htmlspecialchars($mainUrl . 'assets/img/logo.png', ENT_QUOTES, 'UTF-8') ?>" alt="Simbolo da Biblioteca">
                <div class="flex items-center ml-2">
                    <h1 class="text-xl font-bold text-primary-400 mr-5 tracking-wider">Biblioteca <span class="text-secondary">STGM</span></h1>
                </div>
            </div>
            <div class="h-0.5 bg-primary-500/20 rounded-full mt-1"></div>
            <!-- MENU BTN MOBILE -->
            <div class="block lg:hidden translate-x-[-12px]">
                <button id="sidebar-toggle-mobile" type="button" aria-label="Abrir menu lateral" aria-expanded="false">
                    <i class="fa-solid fa-bars text-xl text-gray-400"></i>
                </button>
            </div>
        </div>


        <nav class="p-4 space-y-2 font-semibold">
            <?php foreach ($sidebarMenu as $group) : ?>
                <div class="menu-group">
                    <?php if (empty($group['items'])) : ?>
                        <button type="button" class="menu-toggle w-full flex items-center justify-between p-3 rounded-lg menu-item <?= $currentSection === $group['id'] ? 'active' : '' ?>" data-section="<?= $group['id'] ?>">
                            <div class="flex items-center space-x-3">
                                <i class="<?= $group['icon'] ?> text-lg"></i>
                                <span class="sidebar-text"><?= $group['title'] ?></span>
                            </div>
                        </button>
                    <?php else : ?>
                        <button type="button" class="menu-toggle w-full flex items-center justify-between p-3 rounded-lg menu-item <?= $currentSection === ($group['section'] ?? null) ? 'active' : '' ?>" data-submenu="<?= $group['id'] ?>" aria-expanded="<?= ($currentSection === ($group['section'] ?? null) || in_array($currentSection, array_column($group['items'], 'section'), true)) ? 'true' : 'false' ?>"<?= !empty($group['section']) ? ' data-section="' . $group['section'] . '"' : '' ?>>
                            <div class="flex items-center space-x-3">
                                <i class="<?= $group['icon'] ?> text-lg"></i>
                                <span class="sidebar-text"><?= $group['title'] ?></span>
                            </div>
                            <i class="fas fa-chevron-down sidebar-text transition-transform"></i>
                        </button>
                        <div id="<?= $group['id'] ?>-submenu" class="submenu <?= ($currentSection === ($group['section'] ?? null) || in_array($currentSection, array_column($group['items'], 'section'), true)) ? 'active' : '' ?>">
                            <?php foreach ($group['items'] as $subgroup) : ?>
                                <?php if (!empty($subgroup['items'])) : ?>
                                    <div class="menu-subgroup">
                                        <button type="button" class="menu-toggle w-full flex items-center justify-between p-3 rounded-lg menu-item" data-submenu="<?= $subgroup['id'] ?>" aria-expanded="false">
                                            <div class="flex items-center space-x-3">
                                                <i class="<?= $subgroup['icon'] ?> text-sm"></i>
                                                <span class="sidebar-text"><?= $subgroup['title'] ?></span>
                                            </div>
                                            <i class="fas fa-chevron-down sidebar-text transition-transform text-xs"></i>
                                        </button>
                                        <div id="<?= $subgroup['id'] ?>-submenu" class="submenu">
                                            <?php foreach ($subgroup['items'] as $item) : ?>
                                                <button type="button" class="w-full flex items-center space-x-3 p-3 rounded-lg menu-item text-left <?= $currentSection === $item['section'] ? 'active' : '' ?>" data-section="<?= $item['section'] ?>">
                                                    <i class="<?= $item['icon'] ?> text-xs"></i>
                                                    <span class="sidebar-text"><?= $item['title'] ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <button type="button" class="w-full flex items-center space-x-3 p-3 rounded-lg menu-item text-left <?= $currentSection === $subgroup['section'] ? 'active' : '' ?>" data-section="<?= $subgroup['section'] ?>">
                                        <i class="<?= $subgroup['icon'] ?> text-xs"></i>
                                        <span class="sidebar-text"><?= $subgroup['title'] ?></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-bottom">
            <a class="sidebar-logout" href="<?= htmlspecialchars($portalExitUrl, ENT_QUOTES, 'UTF-8') ?>" aria-label="Sair da biblioteca">
                <i class="fas fa-right-from-bracket" aria-hidden="true"></i>
                <span class="sidebar-text">Sair</span>
            </a>
        </div>
    </aside>
    <button id="sidebar-collapse-toggle" type="button" aria-label="Ocultar menu lateral" aria-expanded="true" title="Ocultar menu lateral">
        <i class="fas fa-chevron-left" aria-hidden="true"></i>
    </button>
    <button id="library-theme-toggle" type="button" aria-label="Mudar para o tema escuro" title="Mudar para o tema escuro">
        <i class="fas fa-moon" aria-hidden="true"></i>
    </button>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="flex-1 flex flex-col min-h-screen">

        <!-- CONTEÚDO -->
        <main id="main-content" class="flex-1 flex flex-col lg:ml-[16vw] lg:w-[84vw] pt-16 md:pt-24 lg:pt-0 w-full min-h-screen">

            <section id="section-dashboard" class="menu-section <?= $currentSection === 'dashboard' ? 'active' : '' ?> flex-1 flex flex-col gap-6">
                <section class="pt-24 md:pt-10 pb-16 md:pb-8 px-4">
                    <div class="max-w-7xl mx-auto gap-8">
                        <div class="space-y-6 flex flex-col items-center text-center">
                            <h2 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl 2xl:text-8xl font-bold gradient-text"
                                style="font-family: 'Anton', serif;">
                                GERENCIE SUA BIBLIOTECA
                            </h2>
                            <p class="text-base sm:text-lg text-gray-600 font-light max-w-xl mx-auto lg:mx-0"
                                style="font-family: 'Poppins', sans-serif;">
                                Organize livros, gerencie empréstimos e simplifique a administração da sua biblioteca.
                            </p>
                            <a
                                class="dashboard-guide-link"
                                href="<?= htmlspecialchars($mainUrl . 'docs/Guia_rapido_biblioteca.pdf?v=' . (int) filemtime(__DIR__ . '/docs/Guia_rapido_biblioteca.pdf'), ENT_QUOTES, 'UTF-8') ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Abrir o guia rápido do sistema da biblioteca em uma nova aba">
                                <i class="fas fa-book-open" aria-hidden="true"></i>
                                <span>Guia rápido do sistema</span>
                                <i class="fas fa-arrow-up-right-from-square text-xs" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </section>

                <section class="px-4">
                    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
                        <div class="cards-section hover:transition-all hover:shadow-lg bg-white border border-gray-100 rounded-3xl shadow-sm p-6 flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.24em] text-gray-500">Livros cadastrados</p>
                                    <p class="text-4xl font-bold text-ceara-green mt-4" data-counter-target="<?= $totalBooks ?>">0</p>
                                </div>
                                <i class="fas fa-book-open text-4xl text-ceara-green/80"></i>
                            </div>
                        </div>
                        <div class="cards-section hover:transition-all hover:shadow-lg bg-white border border-gray-100 rounded-3xl shadow-sm p-6 flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.24em] text-gray-500">Empréstimos realizados</p>
                                    <p class="text-4xl font-bold text-ceara-orange mt-4" data-counter-target="<?= $totalLoans ?>">0</p>
                                </div>
                                <i class="fas fa-exchange-alt text-4xl text-ceara-orange/80"></i>
                            </div>
                        </div>
                        <div class="cards-section hover:transition-all hover:shadow-lg bg-white border border-gray-100 rounded-3xl shadow-sm p-6 flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.24em] text-gray-500">Alunos cadastrados</p>
                                    <p class="text-4xl font-bold text-ceara-green mt-4" data-counter-target="<?= $totalStudents ?>">0</p>
                                </div>
                                <i class="fas fa-users text-4xl text-ceara-green/80"></i>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="py-12 md:py-12 lg:py-16 bg-gradient-to-b from-white to-gray-50/50">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div>
                            <div class="text-center mb-10">
                                <h3 class="text-2xl md:text-3xl xl:text-4xl font-bold text-ceara-green/90">Empréstimo / Devolução</h3>
                            </div>

                            <div class="w-full max-w-5xl mx-auto px-4">
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-ceara-green">
                                        <i class="fas fa-qrcode text-xl"></i>
                                    </span>
                                    <input
                                        id="qrcodeHome"
                                        type="text"
                                        placeholder="Escaneie o QRcode aqui para verificar empréstimo/devolução"
                                        class="w-full pl-12 pr-4 py-4 bg-white border-2 border-ceara-green/50 focus:border-ceara-green focus:ring-2 focus:ring-ceara-green/40 rounded-2xl outline-none placeholder:text-gray-400 text-gray-700 shadow-card text-lg"
                                        autocomplete="off" />
                                </div>
                                <!-- <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <p class="text-sm text-gray-500">
                                        Exemplo de teste: <span class="font-medium">id_livro=123_1_1_E1_p2</span> ou cole um URL completo do QR.
                                    </p>
                                    <button type="button" id="btn-qrcode-test" class="inline-flex items-center justify-center rounded-full border border-ceara-green px-4 py-2 text-sm font-semibold text-ceara-green hover:bg-ceara-green/10 transition">
                                        Testar QR de exemplo
                                    </button>
                                </div> -->
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                            <!-- Card 1 -->
                            <!-- <div class="group h-full">
                                <button type="button" data-section="gerenciar_acervo" class="w-full text-left bg-white rounded-xl shadow-sm hover:shadow-md overflow-hidden transition-all duration-300 h-full flex flex-col border border-gray-100 hover:border-ceara-green/20">
                                    <div class="h-1.5 bg-gradient-to-r from-ceara-green to-ceara-green/80"></div>
                                    <div class="block p-6 flex-grow">
                                        <div class="flex flex-col items-center text-center h-full">
                                            <div class="w-16 h-16 flex items-center justify-center bg-ceara-green/5 rounded-2xl mb-5 group-hover:bg-ceara-green/10 transition-all duration-300">
                                                <i class="fas fa-book text-ceara-green text-2xl"></i>
                                            </div>
                                            <h4 class="text-lg md:text-xl font-semibold mb-3 text-gray-800">Gerenciamento de Acervo</h4>
                                            <p class="text-gray-600 text-sm md:text-base mb-4 flex-grow">Cadastre e organize seus livros de forma eficiente e intuitiva.</p>
                                        </div>
                                    </div>
                                </button>
                            </div> -->

                            <!-- Card 2 -->
                            <!-- <div class="group h-full">
                                <button type="button" data-section="relatorio_acervo" class="w-full text-left bg-white rounded-xl shadow-sm hover:shadow-md overflow-hidden transition-all duration-300 h-full flex flex-col border border-gray-100 hover:border-ceara-green/20">
                                    <div class="h-1.5 bg-gradient-to-r from-ceara-green to-ceara-green/80"></div>
                                    <div class="block p-6 flex-grow">
                                        <div class="flex flex-col items-center text-center h-full">
                                            <div class="w-16 h-16 flex items-center justify-center bg-ceara-green/5 rounded-2xl mb-5 group-hover:bg-ceara-green/10 transition-all duration-300">
                                                <i class="fas fa-chart-line text-ceara-green text-2xl"></i>
                                            </div>
                                            <h4 class="text-lg md:text-xl font-semibold mb-3 text-gray-800">Relatórios</h4>
                                            <p class="text-gray-600 text-sm md:text-base mb-4 flex-grow">Gere relatórios detalhados para análise e tomada de decisões.</p>
                                        </div>
                                    </div>
                                </button>
                            </div> -->

                            <!-- Card 3 -->
                            <!-- <div class="group h-full">
                                <button type="button" data-section="qrcode_geral" class="w-full text-left bg-white rounded-xl shadow-sm hover:shadow-md overflow-hidden transition-all duration-300 h-full flex flex-col border border-gray-100 hover:border-ceara-orange/20">
                                    <div class="h-1.5 bg-gradient-to-r from-ceara-orange to-ceara-orange/70"></div>
                                    <div class="block p-6 flex-grow">
                                        <div class="flex flex-col items-center text-center h-full">
                                            <div class="w-16 h-16 flex items-center justify-center bg-ceara-orange/5 rounded-2xl mb-5 group-hover:bg-ceara-orange/10 transition-all duration-300">
                                                <i class="fas fa-qrcode text-ceara-orange text-2xl"></i>
                                            </div>
                                            <h4 class="text-lg md:text-xl font-semibold mb-3 text-gray-800">Gerar QR Code</h4>
                                            <p class="text-gray-600 text-sm md:text-base mb-4 flex-grow">Crie QR Codes para facilitar o acesso e catalogação dos livros.</p>
                                        </div>
                                    </div>
                                </button>
                            </div> -->
                        </div>
                    </div>
                </section>
            </section>

            <?php foreach ($sectionContents as $sectionId => $section): ?>
                <section id="section-<?= $sectionId ?>" class="menu-section <?= $currentSection === $sectionId ? 'active' : '' ?> lg:px-0 lg:-ml-6">
                    <div class="max-w-screen mx-auto">
                        <div data-section-content data-loaded="<?= $currentSection === $sectionId ? 'true' : 'false' ?>" data-query="<?= $currentSection === $sectionId ? htmlspecialchars($sectionQueryString, ENT_QUOTES, 'UTF-8') : '' ?>">
                            <?php if ($currentSection === $sectionId): ?>
                                <?php $pageScope = get_defined_vars(); ?>
                                <?= $renderSectionContent($sectionId, $pageScope) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>

            <footer id="footer" class="library-footer w-full" aria-label="Desenvolvedor do sistema">
                <span>Desenvolvido por</span>
                <a href="https://www.instagram.com/cassioholanda_/" target="_blank" rel="noopener noreferrer" aria-label="Instagram de Cassio Holanda">
                    <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                    <span>Cassio Holanda</span>
                </a>
            </footer>
        </main>
    </div>

    <script>
        // Menu lateral - Alternar abas (submenu)
        document.querySelectorAll('.menu-toggle').forEach(button => {
            button.addEventListener('click', (e) => {
                // If sidebar is hidden on mobile, do not open submenus
                const sidebarEl = document.getElementById('sidebar');
                if (sidebarEl && sidebarEl.classList.contains('-translate-x-full')) {
                    return;
                }

                const submenuId = button.getAttribute('data-submenu');
                const submenu = document.getElementById(submenuId + '-submenu');
                const chevron = button.querySelector('.fa-chevron-down');

                if (submenu) {
                    const isExpanded = submenu.classList.toggle('active');
                    button.setAttribute('aria-expanded', String(isExpanded));
                    if (chevron) {
                        chevron.style.transform = isExpanded ? 'rotate(180deg)' : 'rotate(0deg)';
                    }
                }
            });
        });

        // Configuração do sidebar
        const sidebar = document.getElementById('sidebar');
        const sidebarText = document.querySelectorAll('.sidebar-text');
        const sidebarToggleMobile = document.getElementById('sidebar-toggle-mobile');
        const sidebarToggleMobileHeader = document.getElementById('sidebar-toggle-mobile-header');
        const sidebarCollapseToggle = document.getElementById('sidebar-collapse-toggle');
        const libraryThemeToggle = document.getElementById('library-theme-toggle');
        const libraryThemeStorageKey = 'biblioteca-theme';
        const updateLibraryThemeToggle = () => {
            const darkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
            const label = darkTheme ? 'Mudar para o tema claro' : 'Mudar para o tema escuro';
            libraryThemeToggle.setAttribute('aria-label', label);
            libraryThemeToggle.title = label;
            libraryThemeToggle.innerHTML = `<i class="fas ${darkTheme ? 'fa-sun' : 'fa-moon'}" aria-hidden="true"></i>`;
        };
        libraryThemeToggle.addEventListener('click', () => {
            const nextTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', nextTheme);
            document.documentElement.style.colorScheme = nextTheme;
            try {
                localStorage.setItem(libraryThemeStorageKey, nextTheme);
            } catch (error) {
                console.warn('Não foi possível salvar a preferência do tema:', error);
            }
            updateLibraryThemeToggle();
        });
        updateLibraryThemeToggle();
        const desktopSidebarQuery = window.matchMedia('(min-width: 1024px)');
        const sidebarCollapseStorageKey = 'biblioteca-sidebar-collapsed';
        let sidebarCollapsedPreference = false;
        try {
            sidebarCollapsedPreference = localStorage.getItem(sidebarCollapseStorageKey) === 'true';
        } catch (error) {
            console.warn('Não foi possível carregar a preferência da barra lateral:', error);
        }

        const updateSidebarCollapse = () => {
            const collapsed = desktopSidebarQuery.matches && sidebarCollapsedPreference;
            document.body.classList.toggle('library-sidebar-collapsed', collapsed);
            sidebar.setAttribute('aria-hidden', String(collapsed));
            sidebar.inert = collapsed;
            const label = collapsed ? 'Mostrar menu lateral' : 'Ocultar menu lateral';
            sidebarCollapseToggle.setAttribute('aria-expanded', String(!collapsed));
            sidebarCollapseToggle.setAttribute('aria-label', label);
            sidebarCollapseToggle.title = label;
        };

        updateSidebarCollapse();
        sidebarCollapseToggle.addEventListener('click', () => {
            sidebarCollapsedPreference = !sidebarCollapsedPreference;
            try {
                localStorage.setItem(sidebarCollapseStorageKey, String(sidebarCollapsedPreference));
            } catch (error) {
                console.warn('Não foi possível salvar a preferência da barra lateral:', error);
            }
            updateSidebarCollapse();
        });
        desktopSidebarQuery.addEventListener('change', updateSidebarCollapse);

        sidebarToggleMobile?.addEventListener('click', () => {
            const isExpanded = sidebar.classList.toggle('max-h-screen');
            sidebarToggleMobile.setAttribute('aria-expanded', String(isExpanded));
            sidebarToggleMobile.setAttribute('aria-label', isExpanded ? 'Fechar menu lateral' : 'Abrir menu lateral');
        });

        const librarySections = <?= json_encode(array_merge(['dashboard' => null], array_map(function ($section) { return $section['view']; }, $sectionContents)), JSON_UNESCAPED_SLASHES) ?>;
        const libraryMainUrl = new URL(<?= json_encode($mainUrl, JSON_UNESCAPED_SLASHES) ?>, window.location.origin);
        const pdfSections = new Set(['relatorio_emprestimo', 'relatorio_acervo', 'relatorio_por_livro']);
        const sectionContentById = new Map(
            [...document.querySelectorAll('[data-section-content]')].map(content => [
                content.closest('.menu-section').id.replace('section-', ''),
                content
            ])
        );
        const suspendedSectionContent = new Map();
        let sectionLoadSequence = 0;

        const suspendSectionContent = section => {
            const sectionId = section.id.replace('section-', '');
            const content = sectionContentById.get(sectionId);
            if (!content || !content.childNodes.length) {
                return;
            }

            const fragment = document.createDocumentFragment();
            while (content.firstChild) {
                fragment.appendChild(content.firstChild);
            }
            suspendedSectionContent.set(sectionId, fragment);
        };

        const restoreSectionContent = (sectionId, content) => {
            const fragment = suspendedSectionContent.get(sectionId);
            if (!fragment) {
                return;
            }

            content.replaceChildren(fragment);
            suspendedSectionContent.delete(sectionId);
        };

        const deactivateActiveSections = keepSectionId => {
            document.querySelectorAll('.menu-section.active').forEach(section => {
                if (section.id === keepSectionId) {
                    return;
                }

                suspendSectionContent(section);
                section.classList.remove('active');
            });
        };

        const executeSectionScripts = async container => {
            const callbacks = [];
            const originalDocumentAddEventListener = document.addEventListener;
            const originalWindowAddEventListener = window.addEventListener;
            document.addEventListener = function (type, listener, options) {
                if (type === 'DOMContentLoaded') {
                    callbacks.push({ target: document, listener });
                    return;
                }
                return originalDocumentAddEventListener.call(this, type, listener, options);
            };
            window.addEventListener = function (type, listener, options) {
                if (type === 'DOMContentLoaded') {
                    callbacks.push({ target: window, listener });
                    return;
                }
                return originalWindowAddEventListener.call(this, type, listener, options);
            };

            try {
                for (const source of [...container.querySelectorAll('script')]) {
                    if (source.src) {
                        const sourceUrl = new URL(source.src, document.baseURI);
                        if (document.head.querySelector(`script[src="${CSS.escape(sourceUrl.href)}"]`)) {
                            source.remove();
                            continue;
                        }

                        await new Promise((resolve, reject) => {
                            const script = document.createElement('script');
                            [...source.attributes].forEach(attribute => script.setAttribute(attribute.name, attribute.value));
                            script.onload = resolve;
                            script.onerror = () => reject(new Error(`Não foi possível carregar o script ${sourceUrl.href}`));
                            script.src = sourceUrl.href;
                            document.head.appendChild(script);
                        });
                    } else if (!source.type || /(?:java|ecma)script/i.test(source.type)) {
                        new Function(source.textContent).call(window);
                    }
                    source.remove();
                }
            } finally {
                document.addEventListener = originalDocumentAddEventListener;
                window.addEventListener = originalWindowAddEventListener;
            }

            callbacks.forEach(({ target, listener }) => {
                if (typeof listener === 'function') {
                    listener.call(target, new Event('DOMContentLoaded'));
                } else if (listener && typeof listener.handleEvent === 'function') {
                    listener.handleEvent(new Event('DOMContentLoaded'));
                }
            });
        };

        const activateSection = async (sectionId, options = {}) => {
            const loadSequence = ++sectionLoadSequence;
            const targetSection = document.getElementById(`section-${sectionId}`);
            const content = sectionContentById.get(sectionId);
            const previousActiveSection = document.querySelector('.menu-section.active');
            if (!targetSection || !Object.prototype.hasOwnProperty.call(librarySections, sectionId)) {
                return;
            }

            if (sectionId === 'dashboard') {
                deactivateActiveSections(targetSection.id);
                targetSection.classList.add('active');
                document.querySelectorAll('.menu-item.active').forEach(item => item.classList.remove('active'));
                document.querySelectorAll(`[data-section="${CSS.escape(sectionId)}"]`).forEach(item => item.classList.add('active'));
                document.querySelector('base').href = libraryMainUrl.href;

                if (options.pushState !== false) {
                    const destination = new URL(window.location.href);
                    destination.search = '';
                    destination.searchParams.set('section', sectionId);
                    destination.hash = '';
                    window.history.pushState({ sectionId }, '', destination.href);
                }
                return;
            }

            if (!content) {
                return;
            }

            if (pdfSections.has(sectionId)) {
                const reportUrl = new URL('index.php', libraryMainUrl);
                reportUrl.search = options.search || '';
                reportUrl.searchParams.set('section', sectionId);
                window.open(reportUrl.href, '_blank', 'noopener');
                return;
            }

            const queryParams = new URLSearchParams(options.search || '');
            queryParams.delete('section');
            queryParams.delete('partial');
            queryParams.delete('page');
            const query = queryParams.toString();
            const sectionUrl = new URL(librarySections[sectionId], libraryMainUrl);
            document.querySelector('base').href = new URL('.', sectionUrl).href;

            if (content.dataset.loaded !== 'true' || content.dataset.query !== query) {
                content.setAttribute('aria-busy', 'true');
                content.innerHTML = '<div role="status" class="p-8 text-center text-gray-600">Carregando seção...</div>';

                const partialUrl = new URL('index.php', libraryMainUrl);
                partialUrl.search = query;
                partialUrl.searchParams.set('section', sectionId);
                partialUrl.searchParams.set('partial', '1');

                try {
                    const response = await fetch(partialUrl.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) {
                        throw new Error(`Falha ao carregar a seção (${response.status}).`);
                    }
                    const sectionHtml = await response.text();
                    if (loadSequence !== sectionLoadSequence) {
                        return;
                    }

                    deactivateActiveSections(targetSection.id);
                    suspendedSectionContent.delete(sectionId);
                    content.innerHTML = sectionHtml;
                    content.dataset.loaded = 'true';
                    content.dataset.query = query;
                    await executeSectionScripts(content);
                    if (loadSequence !== sectionLoadSequence) {
                        suspendSectionContent(targetSection);
                        return;
                    }
                } catch (error) {
                    console.error(error);
                    content.innerHTML = '<div role="alert" class="m-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">Não foi possível carregar esta seção. Tente novamente.</div>';
                    content.dataset.loaded = 'false';
                    content.removeAttribute('aria-busy');
                    if (previousActiveSection && previousActiveSection !== targetSection) {
                        const previousSectionId = previousActiveSection.id.replace('section-', '');
                        restoreSectionContent(previousSectionId, sectionContentById.get(previousSectionId));
                        previousActiveSection.classList.add('active');
                    }
                    return;
                }
                content.removeAttribute('aria-busy');
            } else {
                deactivateActiveSections(targetSection.id);
                restoreSectionContent(sectionId, content);
            }

            document.querySelectorAll('.menu-section.active').forEach(section => section.classList.remove('active'));
            targetSection.classList.add('active');
            document.querySelectorAll('.menu-item.active').forEach(item => item.classList.remove('active'));
            document.querySelectorAll(`[data-section="${CSS.escape(sectionId)}"]`).forEach(item => item.classList.add('active'));

            const submenu = targetSection.querySelector('.submenu');
            if (submenu) {
                submenu.classList.add('active');
            }

            if (options.pushState !== false) {
                const destination = new URL(window.location.href);
                destination.search = query;
                destination.searchParams.set('section', sectionId);
                destination.hash = '';
                window.history.pushState({ sectionId }, '', destination.href);
            }
        };

        document.addEventListener('click', event => {
            const targetElement = event.target instanceof Element ? event.target : null;
            if (!targetElement) return;

            const sectionButton = targetElement.closest('[data-section]');
            if (sectionButton) {
                const sectionId = sectionButton.getAttribute('data-section');
                if (sectionId && Object.prototype.hasOwnProperty.call(librarySections, sectionId)) {
                    event.preventDefault();
                    void activateSection(sectionId);
                }
                return;
            }

            const link = targetElement.closest('a[href]');
            if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') return;

            const targetUrl = new URL(link.href, document.baseURI);
            if (targetUrl.origin !== window.location.origin) return;

            const sectionId = Object.keys(librarySections).find(id =>
                new URL(librarySections[id], libraryMainUrl).pathname === targetUrl.pathname
            );
            if (!sectionId) return;

            event.preventDefault();
            void activateSection(sectionId, { search: targetUrl.search });
        }, true);

        window.addEventListener('popstate', () => {
            const params = new URLSearchParams(window.location.search);
            const sectionId = params.get('section') || 'dashboard';
            void activateSection(sectionId, { pushState: false, search: window.location.search });
        });

        const animateDashboardCounters = () => {
            document.querySelectorAll('[data-counter-target]').forEach(el => {
                const target = parseInt(el.dataset.counterTarget, 10) || 0;
                const duration = 1200;
                let startTime = null;

                const animate = timestamp => {
                    if (!startTime) startTime = timestamp;
                    const progress = Math.min((timestamp - startTime) / duration, 1);
                    const current = Math.floor(progress * target);
                    el.textContent = current.toLocaleString('pt-BR');
                    if (progress < 1) {
                        window.requestAnimationFrame(animate);
                    } else {
                        el.textContent = target.toLocaleString('pt-BR');
                    }
                };

                window.requestAnimationFrame(animate);
            });
        }

        animateDashboardCounters();

        // Funcionalidade do campo de QR Code
        const qrcodeInput = document.getElementById('qrcodeHome');
        // const qrcodeTestButton = document.getElementById('btn-qrcode-test');

        if (qrcodeInput) {
            function buildDecisionUrl(parameters) {
                const decisionUrl = new URL('index.php', libraryMainUrl);
                decisionUrl.searchParams.set('page', 'views/emprestimo/decisao.php');
                new URLSearchParams(parameters).forEach((value, key) => {
                    decisionUrl.searchParams.set(key, value);
                });
                return decisionUrl.href;
            }

            function resolveQrcodeUrl(qrcode) {
                const trimmed = qrcode.trim();
                if (!trimmed) return null;

                function extractIdFromUrl(url) {
                    try {
                        const u = new URL(url, window.location.origin);
                        const params = new URLSearchParams(u.search);
                        let id = params.get('id_livro') || params.get('qr_livro') || params.get('id');
                        if (!id) {
                            const hashParams = new URLSearchParams(u.hash.replace('#','?'));
                            id = hashParams.get('id_livro') || hashParams.get('qr_livro') || null;
                        }
                        if (!id) {
                            const m = url.match(/(?:id_livro|qr_livro)=([^&?#]+)/);
                            if (m) id = decodeURIComponent(m[1]);
                        }
                        return id;
                    } catch (e) {
                        return null;
                    }
                }

                const isFullUrl = trimmed.startsWith('http://') || trimmed.startsWith('https://');
                if (isFullUrl) {
                    const extracted = extractIdFromUrl(trimmed);
                    if (extracted) {
                        if (qrcodeInput) qrcodeInput.value = extracted;
                        return buildDecisionUrl(`id_livro=${encodeURIComponent(extracted)}`);
                    }
                    return trimmed;
                }

                const isDecisaoPath = trimmed.startsWith('views/emprestimo/decisao.php');
                if (isDecisaoPath) {
                    return buildDecisionUrl(trimmed.split('?')[1] || '');
                }

                const hasIdLivro = trimmed.includes('id_livro=');
                if (hasIdLivro) {
                    const query = trimmed.startsWith('?') ? trimmed.slice(1) : trimmed;
                    return buildDecisionUrl(query);
                }

                return buildDecisionUrl(`id_livro=${encodeURIComponent(trimmed)}`);
            }

            
            const debounceRedirect = (() => {
                let timer = null;
                return () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        if (qrcodeInput.value.trim().length > 8) {
                            redirecionarComQrcode();
                        }
                    }, 250);
                };
            })();

            qrcodeInput.addEventListener('input', function() {
                if (this.value.trim().length > 8) {
                    debounceRedirect();
                }
            });

            qrcodeInput.addEventListener('paste', function() {
                setTimeout(redirecionarComQrcode, 100);
            });

            qrcodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    redirecionarComQrcode();
                }
            });

            qrcodeInput.addEventListener('blur', function() {
                redirecionarComQrcode();
            });

            // if (qrcodeTestButton) {
            //     qrcodeTestButton.addEventListener('click', () => {
            //         const example = 'id_livro=123_1_1_E1_p2';
            //         qrcodeInput.value = example;
            //         redirecionarComQrcode();
            //     });
            // }
            
            qrcodeInput.focus();
        }

        function redirecionarComQrcode() {
            const qrcode = qrcodeInput.value.trim();
            const url = resolveQrcodeUrl(qrcode);
            if (url) {
                window.location.assign(new URL(url, document.baseURI).href);
                qrcodeInput.value = '';
            }
        }

        // Fechar menu ao redimensionar para desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
            }
        });

        // Menu mobile - Alternar abas (submenu)
        document.querySelectorAll('.menu-toggle').forEach(button => {
            button.addEventListener('click', (e) => {
                if (window.innerWidth < 768) {
                    e.preventDefault();
                }
            });
        });
        
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const qrLivro = urlParams.get('qr_livro');

            // Se a página foi aberta via QR Code externo (Câmera do Celular/Tablet)
            if (qrLivro) {
                window.location.assign(`<?= htmlspecialchars($mainUrl, ENT_QUOTES, 'UTF-8') ?>index.php?page=views%2Femprestimo%2Fdecisao.php&id_livro=${encodeURIComponent(qrLivro)}`);
            }
        });
    </script>
</body>

</html>