<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relatórios Biblioteca</title>
  
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  
  <!-- Configuração do Tailwind -->
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
          backgroundImage: {
            'gradient-primary': 'linear-gradient(to right, #1D9A52, #D4A800)',
          }
        }
      }
    }
  </script>

  <!-- Estilos Personalizados -->
  <style>
    .library-back-button {
      left: calc(16vw + 1rem);
    }

    @media (max-width: 1023px) {
      .library-back-button {
        left: 1rem;
        top: 7rem;
      }
    }

    body {
      background-image: url('../../assets/img/layout.png');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      min-height: 100vh;
    }

    .gradient-text {
      background: linear-gradient(to right, #1D9A52, #D4A800);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .glass-effect {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
    }

    /* Força o dropdown a abrir para baixo */
    select {
      direction: ltr;
      writing-mode: horizontal-tb;
      text-orientation: mixed;
      text-combine-upright: none;
    }
    select option {
      direction: ltr;
      writing-mode: horizontal-tb;
      text-orientation: mixed;
      text-combine-upright: none;
    }
  </style>
  <link rel="stylesheet" href="../../assets/css/library-navigation.css">
  <script src="../../assets/js/library-navigation.js"></script>

</head>
<body class="p-2 sm:p-4 md:p-6 lg:p-8">

<?php if (isset($_GET['from']) && $_GET['from'] === 'decisao'): ?>
    <a href="decisao.php" class="library-back-button fixed top-5 z-50 group flex items-center space-x-2 bg-white/80 rounded-full px-4 py-2 shadow-lg hover:bg-ceara-green transition-all duration-300">
        <i class="fa-solid fa-arrow-left text-ceara-green group-hover:text-white"></i>
        <span class="text-ceara-green group-hover:text-white font-medium">Voltar</span>
    </a>
<?php endif; ?>

<div class="container mx-auto px-2 sm:px-4">
  <div class="max-w-[95%] sm:max-w-[85%] md:max-w-[75%] lg:max-w-lg mx-auto mt-12 sm:mt-16 md:mt-20 lg:mt-24">
    <div class="glass-effect rounded-xl sm:rounded-2xl shadow-lg sm:shadow-xl p-4 sm:p-6 md:p-8 lg:p-10">
      <div class="flex flex-col items-center mb-4 sm:mb-6 md:mb-8">
        <div class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 rounded-full bg-gradient-primary flex items-center justify-center mb-3 sm:mb-4 transform transition-transform duration-300 hover:scale-110">
          <i class="fas fa-book-reader text-white text-lg sm:text-xl md:text-2xl"></i>
        </div>
        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold gradient-text mb-1 sm:mb-2">Relatórios Biblioteca</h2>
        <p class="text-gray-600 text-xs sm:text-sm md:text-base">Relatório Geral de Turma</p>
      </div>

      <form class="space-y-4 sm:space-y-6" action="../../index.php?section=relatorio_geral_turma" method="get">
        <div class="text-left">
          <label for="turma" class="block text-xs sm:text-sm md:text-base font-medium text-gray-700 mb-1 sm:mb-2">
            Selecione a turma
          </label>
          <select id="turma" name="turma" class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors duration-300 text-xs sm:text-sm md:text-base" required>
            <option value="" selected disabled>Selecione a turma</option>
            <?php
            require_once(__DIR__ . '/../../models/select_model.php');
            $select_model = new select_model();
            $turmas = $select_model->select_turmas();
            if ($turmas) {
                foreach ($turmas as $turma) {
                    $label = $turma['nome_exibicao'] ?? null;
                    if (!$label) {
                        if (!empty($turma['serie_turma']) || !empty($turma['nome_turma'])) {
                            $label = trim((string)($turma['serie_turma'] ?? '') . 'º ' . (string)($turma['nome_turma'] ?? ''));
                        } elseif (!empty($turma['ano']) && !empty($turma['turma'])) {
                            $label = trim((string)$turma['ano'] . ' ' . (string)$turma['turma']);
                        }
                    }
                    echo '<option value="' . $turma['id_turma'] . '">' . htmlspecialchars($label ?: 'Turma ' . $turma['id_turma']) . '</option>';
                }
            }
            ?>
          </select>
        </div>

        <div class="text-left">
          <label for="mes" class="block text-xs sm:text-sm md:text-base font-medium text-gray-700 mb-1 sm:mb-2">
            Selecione o mês
          </label>
          <select id="mes" name="mes" class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors duration-300 text-xs sm:text-sm md:text-base" required>
            <option value="" selected disabled>Selecione o mês</option>
            <?php
            $meses = [
                1 => 'Janeiro',
                2 => 'Fevereiro',
                3 => 'Março',
                4 => 'Abril',
                5 => 'Maio',
                6 => 'Junho',
                7 => 'Julho',
                8 => 'Agosto',
                9 => 'Setembro',
                10 => 'Outubro',
                11 => 'Novembro',
                12 => 'Dezembro'
            ];
            foreach ($meses as $num => $nome) {
                echo '<option value="' . $num . '">' . $nome . '</option>';
            }
            ?>
          </select>
        </div>

        <button type="submit" class="w-full bg-gradient-primary text-white font-medium py-2 sm:py-3 px-4 sm:px-6 rounded-lg transform transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex items-center justify-center space-x-2">
          <i class="fas fa-file-alt text-sm sm:text-base"></i>
          <span class="text-xs sm:text-sm md:text-base">Gerar Relatório</span>
        </button>
      </form>
    </div>
  </div>
</div>

</body>
</html>