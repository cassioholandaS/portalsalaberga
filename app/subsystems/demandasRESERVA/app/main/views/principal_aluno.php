<?php
require_once('../models/usuario_model.php');
require_once('../models/autorizacao.php');

// Verificar sessão e tipo de usuário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirecionar se não autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php?erro=nao_autenticado");
    exit;
}

// Obter dados da sessão
$nomeUsuario = $_SESSION['nome'] ?? 'Usuário';
$emailUsuario = $_SESSION['email'] ?? 'Email não disponível';
$tipoUsuario = $_SESSION['tipo'] ?? 'usuario';
$pontuacaoUsuario = $_SESSION['pontuacao'] ?? 0;
$fotoUsuario = $_SESSION['foto'] ?? null;
$idUsuarioLogado = (int) $_SESSION['id_usuario'];
$tipoSessao = $_SESSION['tipo'] ?? '';
$rankingPontuacao = [];
$projetosVinculadosAluno = [];

try {
    $rankingPontuacao = (new usuario_model())->listar_ranking_pontuacao($idUsuarioLogado);
    $projetosVinculadosAluno = array_values(array_filter(
        (new autorizacao())->listarProjetosDoUsuario($idUsuarioLogado),
        static fn (array $projeto): bool => (int) ($projeto['ativo'] ?? 0) === 1
    ));
} catch (Throwable $exception) {
    error_log('Erro ao carregar dados iniciais da área do aluno: ' . $exception->getMessage());
}

$ehAdministradorAluno = in_array($tipoSessao, ['administrador', 'master'], true);
$podeFiltrarProjetosAluno = !$ehAdministradorAluno && count($projetosVinculadosAluno) >= 2;
$perfilUsuarioExibicao = $tipoUsuario === 'master'
    ? 'Master'
    : ($tipoUsuario === 'administrador'
        ? 'Administrador'
        : 'Usuário - ' . (implode(', ', array_column($projetosVinculadosAluno, 'nome')) ?: 'Projeto não informado'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script src="../js/theme.js"></script>
    <title>Sistema | Gerenciamento de Demandas</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Allura&family=Anton&family=Assistant:wght@200..800&family=Bangers&family=Bebas+Neue&family=Chakra+Petch:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Comfortaa:wght@300..700&family=Epilogue:ital,wght@0,100..900;1,100..900&family=Exo+2:ital,wght@0,100..900;1,100..900&family=Funnel+Display:wght@300..800&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lilita+One&family=Love+Ya+Like+A+Sister&family=Monsieur+La+Doulaise&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Outfit:wght@100..900&family=Oxanium:wght@200..800&family=Passion+One:wght@400;700;900&family=Pinyon+Script&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Rock+Salt&family=Smooch+Sans:wght@100..900&family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&family=Space+Grotesk:wght@300..700&family=Vina+Sans&display=swap');
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body{
            overflow-x: hidden;
        }

        h1{
            font-family: "Comfortaa", sans-serif;
        }

        input, button, p, span, select, li{
            font-family: "Poppins", sans-serif;
        }

        :root{
            /* Cores Dark */
            --gray1: #121212;
            --gray2: #212121;
            --gray3: #181818;
            --gray4: #c0c2bc;
            --primary-green: #10C157;
            --primary-green-light: #2fd973;
            --primary-dark-green: #0c9a45;
            --gree-btnActive: #0C2014;

            /* Cores Light */
            --dark-green: #007A33;
            --dark-green-btns: #007a3363;
            --dark-green2: #064923;
            --green-icon-crede: #2B7246;
            --gray-crede: #E7F2E5;
            --gray-btnActive: #ffffff3b;
            --suave-green: #f2f3f3;
            --white-gray: #ececec79;

            /* Cores Neutras */
            --red1: #dc3030;
            --red2: #a12424;

            --red-transparent:  #e6423c44;
            --yellow-transparent: #ffa60054;
            --blue-transparent: #1450d369;
            
            --dark-green-transparent2: #16412d6e;
            --dark-green-transparent3: #1d5b3e40;
            --dark-green-transparent4: #1faa696e;
            --dark-blue-transparent: #3153b93e;
            --dark-red-transparent: #9d2a2a3e;
            --dark-yellow-transparent: #b995313e;
            --dark-gray-transparent: #b799993e;
            --dark-gray-transparent2: #9c82823e;
            --dark-gray-transparent3: #6454543e;
            --white: #fff;
            --accent-yellow:#FFA500;
            --accent-yellow-light: #ffb732;
            --dark-accent-yellow: #d6900d;
            --dark-accent-yellow2: #e49400;

            --theme-page-bg: #edf5ee;
            --theme-surface: #ffffff;
            --theme-surface-alt: #f3f8f4;
            --theme-surface-soft: #e7f2e5;
            --theme-border: rgba(15, 23, 42, 0.08);
            --theme-text: #0f172a;
            --theme-text-soft: #475569;
            --theme-text-muted: #64748b;
            --perfil-card-bg: #064923;
            --perfil-card-bg-strong: var(--dark-green);
            --perfil-button-bg: #ffffff;
            --perfil-button-hover: var(--accent-yellow);
            --perfil-button-alt: #fee2e2;
            --perfil-button-alt-hover: #fecaca;
            --perfil-button-text: var(--dark-green2);
            --perfil-button-alt-text: #b91c1c;
            --perfil-avatar-ring: #33945b;
            --perfil-text-on-accent: #ffffff;
            --demandas-text-main: #121212;
            --demandas-text-soft: #2b2b2b;
        }

        html[data-theme="dark"] {
            --dark-green: #1fae5a;
            --dark-green-btns: rgba(31, 174, 90, 0.18);
            --dark-green2: #168f49;
            --green-icon-crede: #76d89e;
            --gray-crede: #1c1c1c;
            --gray-btnActive: rgba(255, 255, 255, 0.08);
            --suave-green: #1f1f1f;
            --white-gray: rgba(255, 255, 255, 0.04);
            --white: #262626;
            --accent-yellow: #FFA500;
            --accent-yellow-light: #ffb732;
            --dark-accent-yellow: #d6900d;
            --dark-accent-yellow2: #e49400;
            --theme-page-bg: #121212;
            --theme-surface: #171717;
            --theme-surface-alt: #1f1f1f;
            --theme-surface-soft: #262626;
            --theme-border: rgba(255, 255, 255, 0.08);
            --theme-text: #f3f4f6;
            --theme-text-soft: #d1d5db;
            --theme-text-muted: #9ca3af;
            --perfil-card-bg: #2e2e2e;
            --perfil-card-bg-strong: #3a3a3a;
            --perfil-button-bg: #ffffff;
            --perfil-button-hover: var(--dark-green);
            --perfil-button-text: #2e2e2e;
            --perfil-button-alt: #2b2b2b;
            --perfil-button-alt-hover: #343434;
            --perfil-button-alt-text: #fca5a5;
            --perfil-avatar-ring: rgba(255, 255, 255, 0.18);
            --perfil-text-on-accent: #ffffff;
            --demandas-text-main: #f3f4f6;
            --demandas-text-soft: #d1d5db;
        }

        html[data-theme="lula"] {
            --dark-green: #c8102e;
            --dark-green-btns: rgba(200, 16, 46, 0.16);
            --dark-green2: #8e0b20;
            --primary-green: #c8102e;
            --primary-green-light: #e3344f;
            --primary-dark-green: #a30d26;
            --green-icon-crede: #c8102e;
            --gray-crede: #fff0f1;
            --gray-btnActive: rgba(200, 16, 46, 0.1);
            --suave-green: #fff5f5;
            --white-gray: rgba(200, 16, 46, 0.05);
            --dark-green-transparent2: rgba(200, 16, 46, 0.2);
            --dark-green-transparent3: rgba(200, 16, 46, 0.12);
            --dark-green-transparent4: rgba(200, 16, 46, 0.16);
            --accent-yellow: #c8102e;
            --accent-yellow-light: #e3344f;
            --dark-accent-yellow: #a30d26;
            --dark-accent-yellow2: #a30d26;
            --theme-page-bg: #fff4f4;
            --theme-surface: #fffafa;
            --theme-surface-alt: #fff0f1;
            --theme-surface-soft: #ffe3e6;
            --theme-border: rgba(142, 11, 32, 0.14);
            --theme-text: #351217;
            --theme-text-soft: #672630;
            --theme-text-muted: #8a5960;
            --perfil-card-bg: #8e0b20;
            --perfil-card-bg-strong: #c8102e;
            --perfil-button-hover: #c8102e;
            --perfil-button-text: #8e0b20;
            --perfil-avatar-ring: rgba(200, 16, 46, 0.28);
            --demandas-text-main: #351217;
            --demandas-text-soft: #672630;
            color-scheme: light;
        }

        body {
            background: var(--theme-page-bg);
            color: var(--theme-text);
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        html[data-theme="lula"] body {
            background-image: linear-gradient(rgba(255, 247, 247, 0.32), rgba(255, 244, 244, 0.46)), url("../assets/lula background.png");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: 100vw 100vh;
            background-attachment: fixed;
        }

        html[data-theme="lula"] #mainAlunos {
            background: transparent;
        }

        html[data-theme="lula"] .secs-adm,
        html[data-theme="lula"] .secs-aluno {
            background: transparent;
            border-radius: 18px;
        }

        .lula-theme-decorations {
            position: fixed;
            z-index: 5;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .lula-theme-logo {
            position: absolute;
            width: 48px;
            height: 48px;
            object-fit: contain;
            opacity: 0.18;
            filter: drop-shadow(0 2px 8px rgba(142, 11, 32, 0.2));
        }

        .lula-theme-logo--top-left { top: 12px; left: 12px; }
        .lula-theme-logo--top-right { top: 12px; right: 12px; }
        .lula-theme-logo--bottom-left { bottom: 12px; left: 12px; }
        .lula-theme-logo--bottom-right { right: 12px; bottom: 12px; }

        .lula-theme-falling-star {
            position: absolute;
            top: -24px;
            color: #c8102e;
            font-size: var(--star-size);
            opacity: 0;
            text-shadow: 0 1px 5px rgba(200, 16, 46, 0.24);
            animation: lula-star-fall linear infinite;
        }

        @keyframes lula-star-fall {
            0% { opacity: 0; transform: translate3d(0, -4vh, 0) rotate(0); }
            12% { opacity: 0.62; }
            86% { opacity: 0.52; }
            100% { opacity: 0; transform: translate3d(30px, 108vh, 0) rotate(300deg); }
        }

        html[data-theme="lula"] body .theme-toggle {
            background: linear-gradient(135deg, #c8102e, #8e0b20) !important;
            border-color: rgba(142, 11, 32, 0.2) !important;
            color: #fff !important;
        }

        html[data-theme="lula"] body .theme-toggle:hover {
            background: linear-gradient(135deg, #dc1838, #a30d26) !important;
        }

        html[data-theme="lula"] #sidebar,
        html[data-theme="lula"] #menuAlunoMobile {
            background: linear-gradient(180deg, #9f0b22 0%, #760817 100%) !important;
            border-right-color: rgba(255, 255, 255, 0.14);
        }

        html[data-theme="lula"] #sidebar nav li:has(> button.btnSelect),
        html[data-theme="lula"] #menuAlunoMobile nav li:has(> button.btnSelect) {
            background-color: rgba(255, 255, 255, 0.15);
        }

        html[data-theme="lula"] #sidebar nav li:has(> button.btnSelect)::before,
        html[data-theme="lula"] #menuAlunoMobile nav li:has(> button.btnSelect)::before {
            background: #fff;
        }

        html[data-theme="lula"] #sidebar .icon-nav-alunos.icon-select,
        html[data-theme="lula"] #menuAlunoMobile .icon-nav-alunos.icon-select,
        html[data-theme="lula"] #sidebar .option-nav-alunos.btnSelect,
        html[data-theme="lula"] #menuAlunoMobile .option-nav-alunos.btnSelect {
            color: #fff !important;
        }

        html[data-theme="lula"] .sidebar-collapse-toggle {
            background: linear-gradient(145deg, #c8102e, #8e0b20);
            color: #fff;
        }

        @media (prefers-reduced-motion: reduce) {
            .lula-theme-falling-star {
                display: none;
            }
        }

        .perfil-avatar-ring {
            border: 4px solid var(--perfil-avatar-ring);
        }

        .effect-bar{
            position: relative;
            overflow: hidden;
        }

        .effect-bar::after{
            content: "";
            position: absolute;
            height: 100%;
            width: 4px;
            left: 0;
            bottom: 0;
            background-color: var(--accent-yellow);
            transform-origin: center;
            transform: scaleY(0);
            transition: transform 0.4s ease-in-out;
        }

        .effect-bar:hover::after{
            transform: scaleY(1);
        }

        .effect-bar-select::after{
            transform: scaleY(1);
        }

        .btnSelect{
            background-color: var(--gray-btnActive);
            color: var(--accent-yellow);
            transform: translateX(4px);
        }

        .icon-hover{
            background-color: var(--gray-btnActive);
            color: var(--accent-yellow);
            transition: 0.4s ease-in-out;
        }

        .icon-select{
            background-color: var(--gray-btnActive);
            color: var(--accent-yellow);
        }

        [data-theme="dark"] .effect-bar::after,
        [data-theme="dark"] .effect-bar-select::after {
            background-color: var(--dark-green) !important;
        }

        [data-theme="dark"] .btnSelect,
        [data-theme="dark"] .icon-select,
        [data-theme="dark"] .icon-nav-adm,
        [data-theme="dark"] .icon-nav-alunos,
        [data-theme="dark"] .icon-nav-sair,
        [data-theme="dark"] .option-nav-adm:hover,
        [data-theme="dark"] .option-nav-alunos:hover,
        [data-theme="dark"] .option-nav-sair:hover,
        [data-theme="dark"] .option-nav-adm.btnSelect,
        [data-theme="dark"] .option-nav-alunos.btnSelect,
        [data-theme="dark"] .option-nav-sair.btnSelect {
            color: var(--dark-green) !important;
        }

        .btnSpecial{
            position: relative;
            overflow: hidden;
        }

        .btnSpecial::after{
            content: "";
            position: absolute;
            width: 75%;
            height: 100%;
            bottom: 0;
            left: -100%;
            background-image: linear-gradient(120deg, transparent, #ffffff5b, transparent);
            transition: left 0.3s ease-in-out;
        }

        .btnSpecial:hover::after{
            left: 100%;
        }

        label{
            pointer-events: none;
        }

        .btnDemandasSec_select{
            color: var(--dark-green);
        }

        [data-theme="dark"] .btnDemandasSec {
            color: var(--theme-text-soft);
        }

        [data-theme="dark"] .btnDemandasSec.btnDemandasSec_select {
            color: var(--dark-green) !important;
        }

        .no-scroll{
            overflow-y: hidden;
        }

        .card-prioridade-alta {
            background-color: #fde8e8;
            border-left-color: #dc3030;
        }

        .card-prioridade-media {
            background-color: #fff4d6;
            border-left-color: #d6900d;
        }

        .card-prioridade-baixa {
            background-color: #e5efff;
            border-left-color: #2b6cb0;
        }

        [data-theme="dark"] .card-prioridade-alta {
            background: rgba(220, 48, 48, 0.14);
            border-left-color: #ff6b6b;
            color: #f5d5d5;
        }

        [data-theme="dark"] .card-prioridade-media {
            background: rgba(245, 158, 11, 0.16);
            border-left-color: #fbbf24;
            color: #fff1c2;
        }

        [data-theme="dark"] .card-prioridade-baixa {
            background: rgba(59, 130, 246, 0.12);
            border-left-color: #60a5fa;
            color: #dfeaff;
        }

        [data-theme="dark"] .card-prioridade-alta .text-gray-500,
        [data-theme="dark"] .card-prioridade-media .text-gray-500,
        [data-theme="dark"] .card-prioridade-baixa .text-gray-500,
        [data-theme="dark"] .card-prioridade-alta .text-gray-600,
        [data-theme="dark"] .card-prioridade-media .text-gray-600,
        [data-theme="dark"] .card-prioridade-baixa .text-gray-600 {
            color: rgba(255,255,255,0.8) !important;
        }

        .linha-prioridade-alta { background-color: #f7d5d5; }
        .linha-prioridade-media { background-color: #f8e8b8; }
        .linha-prioridade-baixa { background-color: #d5e4fa; }

        .aviso-ponto-progresso {
            animation: avisoPontoProgresso 2.5s linear forwards;
        }

        .filtro-global-projeto {
            top: 0;
            right: 2rem;
            z-index: 70;
        }

        .filtro-global-painel {
            transform-origin: top center;
            animation: filtroGlobalDescer .28s cubic-bezier(.22, 1, .36, 1);
        }

        @keyframes filtroGlobalDescer {
            from { opacity: 0; transform: translateY(-14px) scale(.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @media (max-width: 1023px) {
            .filtro-global-projeto { top: 85px; right: 1rem; }
        }

        [data-theme="dark"] #filtroActiveBadgeAluno {
            background: rgba(59, 130, 246, 0.2) !important;
            color: #60a5fa !important;
            border: 1px solid rgba(59, 130, 246, 0.5);
        }

        .demandas-grid-aluno {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
            align-items: stretch;
        }

        @media (max-width: 1400px) {
            .demandas-grid-aluno { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        }

        @media (max-width: 900px) {
            .demandas-grid-aluno { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        }

        @media (max-width: 700px) {
            .demandas-grid-aluno { grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        }

        .calendario-ponto-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(18, 18, 18, 0.08);
            box-shadow: 0 16px 40px rgba(16, 34, 20, 0.08);
        }

        .calendario-ponto-header {
            border-bottom: 1px solid rgba(18, 18, 18, 0.08);
        }

        .calendario-dia {
            min-height: 88px;
            border-radius: 14px;
            border: 1px solid rgba(18, 18, 18, 0.06);
            background: rgba(247, 248, 247, 0.9);
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        [data-theme="dark"] .calendario-dia {
            background: rgba(18, 18, 18, 0.9);
            border-color: rgba(255, 255, 255, 0.08);
        }
    
        .calendario-dia:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0, 122, 51, 0.12);
            border-color: rgba(0, 122, 51, 0.25);
        }

        .calendario-dia--fora-mes {
            opacity: 0.42;
            background: rgba(241, 242, 242, 0.75);
        }

        .calendario-dia--hoje {
            border-color: rgba(255, 165, 0, 0.8);
            box-shadow: inset 0 0 0 1px rgba(255, 165, 0, 0.36);
        }

        .calendario-dia--ativo {
            background: linear-gradient(180deg, rgba(0, 122, 51, 0.08), rgba(0, 122, 51, 0.02));
            border-color: rgba(0, 122, 51, 0.3);
        }

        .calendario-indicador {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            min-height: 22px;
            padding: 0.15rem 0.4rem;
            border-radius: 9999px;
            font-size: 0.66rem;
            font-weight: 700;
            background: rgba(0, 122, 51, 0.12);
            color: var(--dark-green2);
        }

        .calendario-tooltip {
            position: absolute;
            left: 50%;
            bottom: calc(100% + 10px);
            transform: translateX(-50%);
            width: 210px;
            background: rgba(18, 18, 18, 0.96);
            color: #fff;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.7rem;
            line-height: 1.45;
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.18);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.18s ease;
            z-index: 10;
        }

        .calendario-dia:hover .calendario-tooltip,
        .calendario-dia:focus-within .calendario-tooltip {
            opacity: 1;
        }

        .calendario-tooltip::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -6px;
            transform: translateX(-50%) rotate(45deg);
            width: 12px;
            height: 12px;
            background: rgba(18, 18, 18, 0.96);
        }

        @keyframes avisoPontoProgresso {
            from { width: 100%; }
            to { width: 0; }
        }

        @keyframes modalOverlayEffect {
            from { opacity: 0; }
            to { opacity: 1; }
        }

            [id^="shadow"],
            .shadowDashBoard {
            left: 16rem !important;
            background-color: rgba(0, 0, 0, 0.6) !important;
            opacity: 1 !important;
            animation: modalOverlayEffect 0.2s ease-out both;
        }

        @media (max-width: 1023px) {
            [id^="shadow"],
            .shadowDashBoard {
                left: 0 !important;
            }
        }

        .shadowDashBoard,
        .shadowSecDemandas,
        .modal-overlay-animated {
            background-color: rgba(0, 0, 0, 0.6) !important;
        }

        #modalCorteFoto,
        #modalLeitorQrPonto,
        #divModalDemandaEncontrada,
        #modalConfirmacaoFoto,
        #modalJustificativaReabertura,
        #divModalCalendarioDetalhes {
            left: 16rem !important;
        }

        @media (max-width: 1023px) {
            #modalCorteFoto,
            #modalLeitorQrPonto,
            #divModalDemandaEncontrada,
            #modalConfirmacaoFoto,
            #modalJustificativaReabertura,
            #divModalCalendarioDetalhes {
                left: 0 !important;
            }
        }

        @media (max-width: 1023px){
            .demanda-card-layout .btnVer p,
            .demanda-card-layout .btnAssumir p,
            .demanda-card-layout .btnEditar p{
                display: none;
            }

            .demanda-card-layout .btnVer,
            .demanda-card-layout .btnAssumir{
                gap: 0;
            }

            .demanda-card-layout .btnVer i,
            .demanda-card-layout .btnAssumir i{
                width: 1rem;
                height: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
            }
        }

        .btnScale{
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btnScale::after{
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 100%;
            height: 300px;
            border-radius: 50%;
            transform-origin: center;
            background-color: var(--dark-green-transparent2);
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.3s ease-in-out;
            z-index: -1;
        }
 
        .btnScale:hover::after{
            transform: translate(-50%, -50%) scale(1);
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            padding: 0.55rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .theme-toggle:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.12);
        }

        .theme-toggle__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            line-height: 1;
        }

        .theme-toggle__label {
            display: none;
        }

        @media (min-width: 1024px) {
            .theme-toggle {
                position: fixed;
                right: 1.5rem;
                bottom: 1.5rem;
                width: 3.25rem;
                height: 3.25rem;
                padding: 0;
                border-radius: 9999px;
                z-index: 60;
                background: linear-gradient(135deg, rgba(255,255,255,0.12), rgba(255,255,255,0.04));
                border-color: rgba(255,255,255,0.18);
                box-shadow: 0 12px 28px rgba(0,0,0,0.22);
            }

            .theme-toggle:hover {
                transform: translateY(-2px) scale(1.02);
            }

            .theme-toggle__icon {
                font-size: 1.1rem;
            }
        }

        html:not([data-theme="dark"]) .theme-toggle {
            background: linear-gradient(135deg, #1ea55f, #0c9a45);
            border-color: rgba(255,255,255,0.12);
            color: #ffffff;
        }

        html:not([data-theme="dark"]) .theme-toggle:hover {
            background: linear-gradient(135deg, #22b96a, #0d9b48);
        }

        [data-theme="dark"] body {
            background: linear-gradient(180deg, #121212 0%, #262626 100%);
        }

        [data-theme="dark"] .dashboard-status-card:nth-of-type(2) i.fa-hourglass {
            color: #ffffff !important;
            background-color: var(--accent-yellow) !important;
        }

        [data-theme="dark"] .dashboard-status-card:nth-of-type(3) i.fa-check {
            background-color: #6b7280 !important;
            color: #ffffff !important;
        }

        [data-theme="dark"] .dashboard-status-card:nth-of-type(3) .quantFinalizadasViewAluno,
        [data-theme="dark"] .dashboard-status-card:nth-of-type(3) p {
            background-color: transparent !important;
            color: inherit !important;
        }

        [data-theme="dark"] .demanda-card-layout .tituloDemanda {
            color: #ffffff !important;
        }

        [data-theme="dark"] .demanda-card-layout .fa-clipboard-list,
        [data-theme="dark"] .demanda-card-layout .fa-clipboard-list::before {
            color: #ffffff !important;
        }

        [data-theme="dark"] .demanda-card-layout .btnVer i,
        [data-theme="dark"] .demanda-card-layout .btnExcluir i,
        [data-theme="dark"] .demanda-card-layout .btnVer .fa-eye,
        [data-theme="dark"] .demanda-card-layout .btnExcluir .fa-trash,
        [data-theme="dark"] .demanda-card-layout .btnVer svg,
        [data-theme="dark"] .demanda-card-layout .btnExcluir svg {
            color: #121212 !important;
            fill: #121212 !important;
        }

        [data-theme="dark"] .demanda-card-layout .text-\[var\(--gray1\)\],
        [data-theme="dark"] .demanda-card-layout .text-\[var\(--gray2\)\],
        [data-theme="dark"] .demanda-card-layout .prioridadeSpan,
        [data-theme="dark"] .demanda-card-layout .orientadorSpan,
        [data-theme="dark"] .demanda-card-layout .criacaoSpan,
        [data-theme="dark"] .demanda-card-layout .prazoSpan,
        [data-theme="dark"] .demanda-card-layout .statusDemanda,
        [data-theme="dark"] .demanda-card-layout .statusDemanda span,
        [data-theme="dark"] .demanda-card-layout .statusDemanda .text-\[var\(--gray1\)\],
        [data-theme="dark"] .demanda-card-layout .statusDemanda .text-\[var\(--gray2\)\],
        [data-theme="dark"] .demanda-card-layout .statusDemanda i {
            color: #111827 !important;
        }

        [data-theme="dark"] p.text-\[var\(--gray1\)\] .orientadorSpan,
        [data-theme="dark"] .linha-prioridade-alta .orientadorSpan,
        [data-theme="dark"] .linha-prioridade-media .orientadorSpan,
        [data-theme="dark"] .linha-prioridade-baixa .orientadorSpan {
            color: #111827 !important;
        }

        [data-theme="dark"] .demanda-card-layout .btnVer,
        [data-theme="dark"] .demanda-card-layout .btnExcluir,
        [data-theme="dark"] .demanda-card-layout .btnVer p,
        [data-theme="dark"] .demanda-card-layout .btnExcluir p {
            color: #121212 !important;
        }

        [data-theme="dark"] #btnRemoverFoto {
            background-color: #fee2e2 !important;
            border-color: #fecaca !important;
            color: #b91c1c !important;
        }

        [data-theme="dark"] #btnRemoverFoto * {
            color: #b91c1c !important;
            fill: #b91c1c !important;
        }

        [data-theme="dark"] .demanda-card-layout .btnExcluir i,
        [data-theme="dark"] .demanda-card-layout .btnExcluir .fa-trash,
        [data-theme="dark"] .demanda-card-layout .btnExcluir svg {
            color: #b91c1c !important;
            fill: #b91c1c !important;
        }

        [data-theme="dark"] .divModalInfs #modalInfs .text-\[var\(--gray2\)\],
        [data-theme="dark"] .divModalInfs #modalInfs .text-\[var\(--gray1\)\],
        [data-theme="dark"] .divModalInfs #modalInfs .tituloDemanda,
        [data-theme="dark"] .divModalInfs #modalInfs .responsavelSpan,
        [data-theme="dark"] .divModalInfs #modalInfs .orientadorSpan,
        [data-theme="dark"] .divModalInfs #modalInfs .categoriaSpan,
        [data-theme="dark"] .divModalInfs #modalInfs .situacaoSpan,
        [data-theme="dark"] .divModalInfs #modalInfs .criacaoSpan,
        [data-theme="dark"] .divModalInfs #modalInfs .prazoSpan,
        [data-theme="dark"] .divModalInfs #modalInfs .descriDemandaSpan,
        [data-theme="dark"] .divModalInfs #modalInfs .tituloEdicao,
        [data-theme="dark"] .divModalInfs #modalInfs .prioridadeEdicao,
        [data-theme="dark"] .divModalInfs #modalInfs .categoriaEdicao,
        [data-theme="dark"] .divModalInfs #modalInfs .descricaoEdicao {
            color: #ffffff !important;
        }

        [data-theme="dark"] .divModalInfs #modalInfs .text-\[var\(--gray2\)\] p,
        [data-theme="dark"] .divModalInfs #modalInfs .text-\[var\(--gray1\)\] p {
            color: #ffffff !important;
        }

        [data-theme="dark"] .dashboard-status-card,
        [data-theme="dark"] .bg-white,
        [data-theme="dark"] .categoria-relatorio-card,
        [data-theme="dark"] .calendario-ponto-card,
        [data-theme="dark"] .panel,
        [data-theme="dark"] #modalCriarDemanda,
        [data-theme="dark"] #modalAddDescri,
        [data-theme="dark"] #modalConclusao,
        [data-theme="dark"] #modalConclusaoDemandas,
        [data-theme="dark"] #modalRelatorioPersonalizado,
        [data-theme="dark"] .modal-content,
        [data-theme="dark"] .bg-\[var\(--white\)\],
        [data-theme="dark"] .bg-\[var\(--white-gray\)\],
        [data-theme="dark"] .bg-\[var\(--suave-green\)\] {
            background-color: var(--theme-surface) !important;
            border-color: var(--theme-border) !important;
            color: var(--theme-text) !important;
        }

        [data-theme="dark"] #sidebar,
        [data-theme="dark"] #menuAdmMobile,
        [data-theme="dark"] #menuAlunoMobile,
        [data-theme="dark"] #menuAdmMobile {
            background: linear-gradient(180deg, #1a1a1a 0%, #121212 100%) !important;
            color: var(--theme-text) !important;
        }

        [data-theme="dark"] #menuAlunos {
            background: transparent !important;
            color: var(--theme-text) !important;
        }

        [data-theme="dark"] .theme-toggle {
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.08);
            color: #f3f4f6;
        }

        [data-theme="dark"] .bg-\[var\(--dark-green\)\],
        [data-theme="dark"] .bg-\[var\(--dark-green2\)\],
        [data-theme="dark"] .bg-gray-300,
        [data-theme="dark"] .bg-gray-400,
        [data-theme="dark"] .bg-gray-500,
        [data-theme="dark"] .iconAluno {
            background-color: var(--dark-green) !important;
            color: #fff !important;
        }

        [data-theme="dark"] .bg-\[var\(--suave-green\)\] {
            background-color: #1f1f1f !important;
        }

        [data-theme="dark"] .bg-\[var\(--white-gray\)\] {
            background-color: rgba(255,255,255,0.04) !important;
        }

        [data-theme="dark"] .bg-\[#F9FCF8\],
        [data-theme="dark"] .bg-\[#FEF6E9\],
        [data-theme="dark"] .bg-white,
        [data-theme="dark"] .bg-\[var\(--white\)\] {
            background-color: var(--theme-surface) !important;
            border-color: var(--theme-border) !important;
        }

        [data-theme="dark"] .categoria-relatorio-card .text-gray-500,
        [data-theme="dark"] .categoria-relatorio-card .text-gray-600,
        [data-theme="dark"] .categoria-relatorio-card .text-gray-700,
        [data-theme="dark"] .categoria-relatorio-card .text-gray-400,
        [data-theme="dark"] .relatorios-grid .text-gray-500,
        [data-theme="dark"] .relatorios-grid .text-gray-600,
        [data-theme="dark"] .relatorios-grid .text-gray-700,
        [data-theme="dark"] .relatorios-grid .text-gray-400 {
            color: #ffffff !important;
        }

        [data-theme="dark"] .text-\[\#121212\],
        [data-theme="dark"] .text-gray-800,
        [data-theme="dark"] .text-gray-700,
        [data-theme="dark"] .text-gray-600,
        [data-theme="dark"] .text-gray-500,
        [data-theme="dark"] .text-gray-400,
        [data-theme="dark"] .text-gray-300,
        [data-theme="dark"] .text-gray-200,
        [data-theme="dark"] .text-\[var\(--gray2\)\],
        [data-theme="dark"] .text-\[--gray2\] {
            color: var(--theme-text) !important;
        }

        .secs-aluno .text-gray-500,
        .secs-aluno .text-gray-600,
        .secs-aluno .text-gray-700,
        .secs-aluno .text-gray-400,
        .secs-adm .text-gray-500,
        .secs-adm .text-gray-600,
        .secs-adm .text-gray-700,
        .secs-adm .text-gray-400,
        .geralDemandas .text-gray-500,
        .geralDemandas .text-gray-600,
        .geralDemandas .text-gray-700,
        .geralDemandas .text-gray-400,
        .divDemandasDisponiveis .text-gray-500,
        .divDemandasDisponiveis .text-gray-600,
        .divDemandasDisponiveis .text-gray-700,
        .divDemandasDisponiveis .text-gray-400,
        .minhasDemandas .text-gray-500,
        .minhasDemandas .text-gray-600,
        .minhasDemandas .text-gray-700,
        .minhasDemandas .text-gray-400 {
            color: var(--demandas-text-main) !important;
        }

        [data-theme="dark"] .secs-aluno .text-gray-500,
        [data-theme="dark"] .secs-aluno .text-gray-600,
        [data-theme="dark"] .secs-aluno .text-gray-700,
        [data-theme="dark"] .secs-aluno .text-gray-400,
        [data-theme="dark"] .secs-adm .text-gray-500,
        [data-theme="dark"] .secs-adm .text-gray-600,
        [data-theme="dark"] .secs-adm .text-gray-700,
        [data-theme="dark"] .secs-adm .text-gray-400,
        [data-theme="dark"] .geralDemandas .text-gray-500,
        [data-theme="dark"] .geralDemandas .text-gray-600,
        [data-theme="dark"] .geralDemandas .text-gray-700,
        [data-theme="dark"] .geralDemandas .text-gray-400,
        [data-theme="dark"] .divDemandasDisponiveis .text-gray-500,
        [data-theme="dark"] .divDemandasDisponiveis .text-gray-600,
        [data-theme="dark"] .divDemandasDisponiveis .text-gray-700,
        [data-theme="dark"] .divDemandasDisponiveis .text-gray-400,
        [data-theme="dark"] .minhasDemandas .text-gray-500,
        [data-theme="dark"] .minhasDemandas .text-gray-600,
        [data-theme="dark"] .minhasDemandas .text-gray-700,
        [data-theme="dark"] .minhasDemandas .text-gray-400 {
            color: var(--theme-text) !important;
        }

        [data-theme="dark"] .bg-\[var\(--gray-crede\)\],
        [data-theme="dark"] main {
            background-color: var(--theme-page-bg) !important;
        }

        [data-theme="dark"] .btnScale,
        [data-theme="dark"] button,
        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            transition: background-color 0.25s ease, color 0.25s ease, border-color 0.25s ease;
        }

        [data-theme="dark"] #mainAdm .w-\[90\%\],
        [data-theme="dark"] #mainAdm .bg-white,
        [data-theme="dark"] #mainAlunos .w-\[90\%\],
        [data-theme="dark"] #mainAlunos .bg-white {
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.35) !important;
        }

        [data-theme="dark"] .theme-toggle:hover {
            background: rgba(255,255,255,0.08);
        }

        @media (max-width: 1023px){
            .demanda-card-layout .btnVer p,
            .demanda-card-layout .btnAssumir p,
            .demanda-card-layout .btnEditar p{
                display: none;
            }

            .demanda-card-layout .btnVer,
            .demanda-card-layout .btnAssumir{
                gap: 0;
            }

            .demanda-card-layout .btnVer i,
            .demanda-card-layout .btnAssumir i{
                width: 1rem;
                height: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
            }
        }

        input:focus ~ label,
        input:not(:placeholder-shown) ~ label {
            font-size: 12px;
            transform: translateY(-20px);
            color: var(--accent-yellow);
            background-color: var(--gray-crede);
            font-weight: 600;
            padding: 1px 5px;
            border-radius: 12%;
        }

        label{
            pointer-events: none;
        }

        #formRelatorioPersonalizado label,
        #formRelatorioPersonalizado label input,
        #formRelatorioPersonalizado label span{
            pointer-events: auto;
        }

        input[type="radio"]{
            accent-color: var(--primary-dark-green);
        }

        #modalConcluirAluno .resultado-opcao{
            pointer-events: auto;
        }

        #modalConcluirAluno .radio-nao-concluida{
            accent-color: var(--red1);
        }

        .relatorios-grid {
            display: grid;
            grid-template-columns: 1.18fr 1fr;
            gap: 20px;
            margin-bottom: 32px;
        }

        .panel {
            background: rgba(255, 255, 255, 0.74);
            border: 1px solid rgba(17, 24, 39, 0.08);
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(17, 24, 39, 0.03);
            padding: 18px 20px 28px;
            min-height: 330px;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .panel-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e2a2d;
            margin: 0;
            font-family: "Poppins", sans-serif;
        }

        [data-theme="dark"] .panel-title,
        [data-theme="dark"] .legend-item,
        [data-theme="dark"] .legend-value,
        [data-theme="dark"] .legend-label,
        [data-theme="dark"] .progress-head,
        [data-theme="dark"] .legend-item span,
        [data-theme="dark"] .donut-center span,
        [data-theme="dark"] .donut-center strong,
        [data-theme="dark"] .categoria-relatorio-card__top,
        [data-theme="dark"] .categoria-relatorio-card__top span {
            color: #ffffff !important;
        }

        [data-theme="dark"] .effect-bar::after,
        [data-theme="dark"] .effect-bar-select::after,
        [data-theme="dark"] .btnSelect,
        [data-theme="dark"] .icon-select,
        [data-theme="dark"] .icon-hover,
        [data-theme="dark"] .option-nav-adm:hover,
        [data-theme="dark"] .option-nav-alunos:hover,
        [data-theme="dark"] .option-nav-sair:hover,
        [data-theme="dark"] .btnSelect p,
        [data-theme="dark"] .icon-select + button,
        [data-theme="dark"] .btnSelect .option-nav-adm {
            color: var(--dark-green) !important;
        }

        [data-theme="dark"] .effect-bar::after,
        [data-theme="dark"] .effect-bar-select::after {
            background-color: var(--dark-green) !important;
        }

        .donut-panel {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 240px;
        }

        .donut-shell {
            position: relative;
            width: min(38vw, 260px);
            aspect-ratio: 1;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            margin-left: 10px;
        }

        .dashboard-status-card {
            min-height: 68px;
            padding: 20px;
            box-shadow: none;
        }

        .dashboard-status-card i {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            pointer-events: none;
        }

        .donut-center strong {
            font-size: clamp(1.8rem, 2vw, 2.4rem);
            line-height: 1;
            color: var(--dark-green2);
            letter-spacing: -0.06em;
            font-family: "Poppins", sans-serif;
        }

        .donut-center span {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 4px;
        }

        .legend {
            display: flex;
            flex-direction: column;
            gap: 18px;
            min-width: 240px;
            padding-left: 0;
            margin-left: 0;
            justify-content: center;
        }

        .legend-item, .progress-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #374151;
            font-family: "Poppins", sans-serif;
        }

        .legend-item {
            font-size: clamp(1.12rem, 1.5vw, 1.55rem);
            line-height: 1.35;
        }

        .legend-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .legend-value {
            font-weight: 500;
            color: #4b5563;
            white-space: nowrap;
            font-size: clamp(1.12rem, 1.5vw, 1.55rem);
        }

        .progress-list { display: flex; flex-direction: column; gap: 16px; margin-top: 10px; }
        .progress-head { margin-bottom: 7px; font-size: 0.92rem; }
        .progress-bar { width: 100%; height: 10px; border-radius: 999px; background: #edf0ec; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, rgba(34, 161, 101, .9), rgba(30, 152, 93, .9)); }

        .relatorios-categorias {
            display: grid;
            grid-template-columns: repeat(5, minmax(150px, 1fr));
            gap: 12px;
            width: 90%;
            margin: 0 auto;
        }

        .categoria-relatorio-card {
            background: rgba(255, 255, 255, 0.74);
            border: 1px solid rgba(17, 24, 39, 0.08);
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(17, 24, 39, 0.03);
            padding: 14px 12px 12px;
            min-height: 142px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
        }

        .categoria-relatorio-card__top { display: flex; align-items: center; gap: 10px; color: #111827; font-weight: 600; font-size: .92rem; }
        .categoria-relatorio-card__icon { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; color: #fff; flex-shrink: 0; }
        .categoria-relatorio-card__icon--verde { background: var(--dark-green); }
        .categoria-relatorio-card__icon--amarelo { background: var(--accent-yellow); }
        .categoria-relatorio-card__icon--azul { background: #2b6cb0; }
        .categoria-relatorio-card p { margin: 0; color: #111827; font-size: .78rem; line-height: 1.45; }
        .categoria-relatorio-btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; background: var(--dark-green); color: #fff; border: none; border-radius: 10px; padding: .7rem .8rem; font-weight: 600; font-size: .78rem; text-decoration: none; }
        .categoria-relatorio-btn:hover { background: var(--dark-green2); }

        [data-theme="dark"] .categoria-relatorio-card__top,
        [data-theme="dark"] .categoria-relatorio-card p {
            color: #ffffff !important;
        }

        @media (max-width: 1024px) {
            .relatorios-grid { grid-template-columns: 1fr; }
            .relatorios-categorias { grid-template-columns: repeat(3, minmax(150px, 1fr)); }
        }

        @media (max-width: 640px) {
            .donut-panel { flex-direction: column; }
            .relatorios-categorias { grid-template-columns: repeat(2, minmax(130px, 1fr)); }
        }

        @media (max-width: 480px) {
            .relatorios-categorias { grid-template-columns: 1fr; }
        }

        @media (max-width: 1023px) {
            [id^="divModal"]:not(#divModalAviso),
            .divModalInfs,
            .divModalInfsSecDemandas,
            .modal-overlay-animated {
                top: 85px !important;
                bottom: auto !important;
                height: calc(100dvh - 85px) !important;
                transform: none !important;
                min-height: 0 !important;
                align-items: flex-start !important;
                overflow-y: auto !important;
                padding: 0.5rem !important;
            }

            [id^="divModal"] > div,
            .modal-overlay-animated > div,
            #modalInfs,
            #modalConcluirAluno,
            #modalRelatorioPersonalizado {
                max-height: calc(100dvh - 101px) !important;
                overflow-y: auto !important;
            }
        }

        @keyframes modalEffect {
            from {
                opacity: 0;
                margin-top: -20px;
            }
            to {
                opacity: 1;
                margin-top: 0;
            }
        }

        [id^="modal"],
        [id^="divModal"] > div {
            animation: modalEffect 0.45s cubic-bezier(0.22, 1, 0.36, 1);
        }

        #modalCriarDemanda,
        #modalConclusao,
        #modalConclusaoSecDemandas,
        #modalConclusaoDemandas,
        #modalAddDescri,
        #modalInfs,
        #modalEditarDemanda,
        #modalExclusao,
        #modalAssumirAluno,
        #modalConcluirAluno,
        #modalRelatorioPersonalizado,
        #modalUsuario,
        #modalDetalhesUsuario {
            animation: modalEffect 1s cubic-bezier(0.22, 1, 0.36, 1);
        }

        @media (min-width: 1024px) {
            #sidebar .sidebar-brand-title { font-size: 0.9rem; }
            #sidebar .fraseMenu { font-size: 0.65rem; line-height: 1.25; }
            #sidebar .option-nav-alunos,
            #sidebar .option-nav-sair { font-size: 0.75rem; }
            #sidebar .icon-nav-alunos,
            #sidebar .icon-nav-sair { font-size: 0.75rem; }
        }

        #sidebar,
        #menuAlunoMobile {
            background: #064c2b !important;
            border-right: 1px solid rgba(209, 231, 219, 0.18);
            color: #e8f2ed;
            scrollbar-color: rgba(209, 231, 219, 0.35) transparent;
            scrollbar-width: thin;
        }

        #sidebar nav ul,
        #menuAlunoMobile nav ul {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100%;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        #sidebar nav [hidden],
        #menuAlunoMobile nav [hidden] {
            display: none !important;
        }

        #sidebar nav li:not(.sidebar-section-heading),
        #menuAlunoMobile nav li:not(.sidebar-section-heading),
        #sidebar > div:last-child > div > li {
            display: grid;
            grid-template-columns: 26px minmax(0, 1fr);
            align-items: center;
            gap: 8px;
            width: 100%;
            margin-top: 0 !important;
        }

        #sidebar nav li > i,
        #menuAlunoMobile nav li > i,
        #sidebar > div:last-child > div > li > i {
            width: 26px !important;
            height: 40px !important;
            margin-left: 0 !important;
            border-radius: 6px;
            color: #c5dfce;
            font-size: 0.9rem !important;
        }

        #sidebar .option-nav-alunos,
        #sidebar .option-nav-sair,
        #menuAlunoMobile .option-nav-alunos,
        #menuAlunoMobile .option-nav-sair {
            width: auto !important;
            min-width: 0;
            height: 40px !important;
            padding: 0 10px !important;
            border-radius: 7px;
            color: #e8f2ed !important;
            font-size: 0.82rem !important;
            font-weight: 600;
            text-align: left;
            transform: none;
            transition: background-color 180ms ease, color 180ms ease, padding-left 200ms ease;
        }

        #sidebar .option-nav-alunos:hover,
        #sidebar .option-nav-sair:hover,
        #menuAlunoMobile .option-nav-alunos:hover,
        #menuAlunoMobile .option-nav-sair:hover,
        #sidebar .option-nav-alunos.btnSelect,
        #sidebar .option-nav-sair.btnSelect,
        #menuAlunoMobile .option-nav-alunos.btnSelect,
        #menuAlunoMobile .option-nav-sair.btnSelect {
            background-color: transparent !important;
        }

        #sidebar .option-nav-alunos:hover:not(.btnSelect),
        #sidebar .option-nav-sair:hover,
        #menuAlunoMobile .option-nav-alunos:hover:not(.btnSelect),
        #menuAlunoMobile .option-nav-sair:hover,
        #sidebar .option-nav-alunos.icon-hover:not(.btnSelect),
        #menuAlunoMobile .option-nav-alunos.icon-hover:not(.btnSelect) {
            color: #fff !important;
            padding-left: 16px !important;
        }

        #sidebar .option-nav-alunos.btnSelect,
        #menuAlunoMobile .option-nav-alunos.btnSelect,
        #sidebar .option-nav-sair.btnSelect,
        #menuAlunoMobile .option-nav-sair.btnSelect {
            color: #ffc04d !important;
            padding-left: 16px !important;
        }

        #sidebar nav .sidebar-item-enter,
        #menuAlunoMobile nav .sidebar-item-enter {
            animation: sidebar-item-enter 260ms cubic-bezier(0.2, 0.75, 0.3, 1) both;
            animation-delay: calc(var(--sidebar-item-index, 0) * 35ms);
        }

        @keyframes sidebar-item-enter {
            from {
                opacity: 0;
                transform: translateY(-7px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #sidebar nav .sidebar-item-enter,
            #menuAlunoMobile nav .sidebar-item-enter {
                animation: none;
            }

            #sidebar .option-nav-alunos,
            #sidebar .option-nav-sair,
            #menuAlunoMobile .option-nav-alunos,
            #menuAlunoMobile .option-nav-sair {
                transition-duration: 0.01ms;
            }
        }

        #sidebar .effect-bar::after,
        #menuAlunoMobile .effect-bar::after {
            display: none;
        }

        #sidebar .icon-hover,
        #menuAlunoMobile .icon-hover {
            background: transparent;
            color: #c5dfce !important;
        }

        #sidebar .icon-select,
        #menuAlunoMobile .icon-select {
            background: transparent;
            color: #ffc04d !important;
        }

        #sidebar nav li:has(> button.btnSelect),
        #menuAlunoMobile nav li:has(> button.btnSelect) {
            position: relative;
            overflow: hidden;
            border-radius: 7px;
            background-color: #276448;
            padding-left: 5px;
            transition: padding-left 180ms ease;
        }

        #sidebar nav li:has(> button.btnSelect)::before,
        #menuAlunoMobile nav li:has(> button.btnSelect)::before {
            position: absolute;
            inset: 8px auto 8px 0;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: #f3a11a;
            content: "";
        }

        #sidebar nav li:has(> button.btnSelect) > button,
        #menuAlunoMobile nav li:has(> button.btnSelect) > button {
            background: transparent !important;
        }

        #sidebar nav li:has(> button:hover):not(:has(> button.btnSelect)),
        #menuAlunoMobile nav li:has(> button:hover):not(:has(> button.btnSelect)),
        #sidebar nav li:focus-within:not(:has(> button.btnSelect)),
        #menuAlunoMobile nav li:focus-within:not(:has(> button.btnSelect)),
        #sidebar > div:last-child > div > li:has(button:hover),
        #menuAlunoMobile nav li:has(form > button:hover) {
            border-radius: 7px;
            background-color: rgba(255, 255, 255, 0.1);
        }

        #sidebar nav li:has(> button:hover) > button,
        #menuAlunoMobile nav li:has(> button:hover) > button,
        #sidebar nav li:focus-within > button,
        #menuAlunoMobile nav li:focus-within > button,
        #sidebar > div:last-child > div > li:has(button:hover) > form > button,
        #menuAlunoMobile nav li:has(form > button:hover) > form > button {
            background: transparent !important;
        }

        #sidebar .sidebar-section-heading,
        #menuAlunoMobile .sidebar-section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 34px;
            margin: 14px 0 3px !important;
            padding: 0 10px;
            border: 1px solid rgba(183, 215, 200, 0.2);
            border-radius: 6px;
            color: #bdd9cb;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        #sidebar .sidebar-section-heading:first-child,
        #menuAlunoMobile .sidebar-section-heading:first-child {
            margin-top: 0 !important;
        }

        #sidebar .sidebar-section-heading i,
        #menuAlunoMobile .sidebar-section-heading i {
            color: #8db7a1;
            font-size: 0.65rem;
        }

        #sidebar .sidebar-group-toggle,
        #menuAlunoMobile .sidebar-group-toggle {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: space-between;
            padding: 0;
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            letter-spacing: inherit;
            text-align: left;
            text-transform: inherit;
            cursor: pointer;
        }

        #sidebar .sidebar-group-toggle:hover,
        #menuAlunoMobile .sidebar-group-toggle:hover {
            color: #fff;
        }

        #sidebar .sidebar-group-toggle i,
        #menuAlunoMobile .sidebar-group-toggle i {
            transition: transform 180ms ease, color 180ms ease;
        }

        #sidebar .sidebar-group-toggle[aria-expanded="true"] i,
        #menuAlunoMobile .sidebar-group-toggle[aria-expanded="true"] i {
            transform: rotate(180deg);
        }

        #sidebar .sidebar-group-toggle:focus-visible,
        #menuAlunoMobile .sidebar-group-toggle:focus-visible {
            outline: 2px solid #f3a11a;
            outline-offset: 3px;
        }

        #menuAlunoMobile {
            overflow-y: auto;
        }

        #sidebar {
            transition: transform 220ms cubic-bezier(0.2, 0.75, 0.3, 1);
        }

        .sidebar-collapse-toggle {
            position: fixed;
            top: 50%;
            left: calc(16rem - 1px);
            z-index: 1001;
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

        .sidebar-collapse-toggle::before {
            position: absolute;
            top: 8px;
            bottom: 8px;
            left: 3px;
            width: 1px;
            background: rgba(255, 255, 255, 0.29);
            content: "";
        }

        .sidebar-collapse-toggle:hover {
            width: 35px;
            background: linear-gradient(145deg, #0d7a49, #075b34);
            box-shadow: 5px 0 15px rgba(5, 45, 27, 0.33), inset -1px 0 rgba(255, 255, 255, 0.44);
        }

        .sidebar-collapse-toggle:focus-visible {
            outline: 2px solid #f3a11a;
            outline-offset: 2px;
        }

        .sidebar-collapse-toggle i {
            transition: transform 220ms ease;
        }

        body.sidebar-collapsed .sidebar-collapse-toggle i {
            transform: rotate(180deg);
        }

        body.sidebar-collapsed .sidebar-collapse-toggle {
            left: 0;
        }

        @media (min-width: 1024px) {
            body.sidebar-collapsed #sidebar {
                transform: translateX(-100%);
            }

            body.sidebar-collapsed #mainAlunos {
                width: 100vw !important;
                margin-left: 0 !important;
            }
        }

        @media (max-width: 1023px) {
            .sidebar-collapse-toggle {
                display: none;
            }
        }

        #sidebar > div:last-child > div > li form,
        #menuAlunoMobile nav li form {
            width: 100%;
            min-width: 0;
        }

        [data-theme="dark"] #sidebar,
        [data-theme="dark"] #menuAlunoMobile {
            background: linear-gradient(180deg, #1a1a1a 0%, #121212 100%) !important;
            border-right-color: rgba(255, 255, 255, 0.06);
            color: var(--theme-text) !important;
        }

        [data-theme="dark"] .sidebar-collapse-toggle {
            border-color: rgba(255, 255, 255, 0.16);
            background: linear-gradient(145deg, #222, #151515);
            color: #10c157;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.35), inset -1px 0 rgba(255, 255, 255, 0.08);
        }

        [data-theme="dark"] .sidebar-collapse-toggle:hover {
            background: linear-gradient(145deg, #303030, #1d1d1d);
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.45), inset -1px 0 rgba(255, 255, 255, 0.12);
        }

        [data-theme="dark"] .sidebar-collapse-toggle::before {
            background: rgba(255, 255, 255, 0.18);
        }

        [data-theme="dark"] #sidebar .sidebar-section-heading,
        [data-theme="dark"] #menuAlunoMobile .sidebar-section-heading {
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--theme-text-soft);
        }

        [data-theme="dark"] #sidebar .sidebar-section-heading i,
        [data-theme="dark"] #menuAlunoMobile .sidebar-section-heading i,
        [data-theme="dark"] #sidebar nav li > i,
        [data-theme="dark"] #menuAlunoMobile nav li > i {
            color: var(--theme-text);
        }

        [data-theme="dark"] #sidebar .icon-nav-alunos.icon-select,
        [data-theme="dark"] #menuAlunoMobile .icon-nav-alunos.icon-select,
        [data-theme="dark"] #sidebar .option-nav-alunos.btnSelect,
        [data-theme="dark"] #menuAlunoMobile .option-nav-alunos.btnSelect {
            color: #10c157 !important;
        }

        [data-theme="dark"] #sidebar nav li:has(> button.btnSelect)::before,
        [data-theme="dark"] #menuAlunoMobile nav li:has(> button.btnSelect)::before {
            background: #10c157;
        }

        [data-theme="dark"] #sidebar .icon-nav-alunos.icon-hover,
        [data-theme="dark"] #menuAlunoMobile .icon-nav-alunos.icon-hover,
        [data-theme="dark"] #sidebar .option-nav-alunos.icon-hover:not(.btnSelect),
        [data-theme="dark"] #menuAlunoMobile .option-nav-alunos.icon-hover:not(.btnSelect) {
            color: #fff !important;
        }

        [data-theme="dark"] #sidebar nav li:has(> button.btnSelect),
        [data-theme="dark"] #menuAlunoMobile nav li:has(> button.btnSelect) {
            background-color: #292929;
        }

        [data-theme="dark"] #sidebar nav li:has(> button:hover):not(:has(> button.btnSelect)),
        [data-theme="dark"] #menuAlunoMobile nav li:has(> button:hover):not(:has(> button.btnSelect)),
        [data-theme="dark"] #sidebar nav li:focus-within:not(:has(> button.btnSelect)),
        [data-theme="dark"] #menuAlunoMobile nav li:focus-within:not(:has(> button.btnSelect)),
        [data-theme="dark"] #sidebar > div:last-child > div > li:has(button:hover),
        [data-theme="dark"] #menuAlunoMobile nav li:has(form > button:hover) {
            background-color: rgba(255, 255, 255, 0.08);
        }

        @media (min-width: 1024px) {
            #sidebar {
                padding: 24px 18px !important;
            }

            #sidebar #menuAlunos {
                padding-top: 8px !important;
            }

            #sidebar #menuAlunos nav {
                padding-top: 8px !important;
            }

            #sidebar .sidebar-brand-title {
                font-size: 0.95rem;
            }

            #sidebar > div:first-child > div:first-child img {
                width: 42px !important;
                height: 42px !important;
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
<div class="grid lg:grid-cols-[16rem_minmax(0,1fr)] h-full">
        <!-- Menu Lateral -->
        <div id="sidebar" class="fixed left-0 top-0 w-full lg:w-64 h-[85px] lg:h-screen bg-[var(--dark-green2)] lg:py-10 flex lg:flex-col items-center lg:items-center justify-between px-3 xl:px-6 z-50 overflow-y-auto">
            <div class="flex lg:flex-col justify-between lg:justify-center items-center lg:items-start w-full">
                <div class="flex items-center lg:justify-start ">
                    <img class="system-brand-logo object-cover size-12 lg:size-16" src="../assets/s_simbol.png" data-default-src="../assets/s_simbol.png" data-lula-src="../assets/pt-removebg-preview.png" alt="">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <p class="sidebar-brand-title font-semibold lg:text-[1.2vw] xl:text-[1vw]"><span class="text-white">Sistema</span> <span class="text-[var(--accent-yellow)]">STGM</span></p>
                        </div>
                        <p id="fraseMenu-id" class="fraseMenu text-gray-400 text-[10px] sm:text-xs">Conectando demandas a quem pode resolvê-las.</p>
                    </div>  
                </div>

                <button type="button" class="theme-toggle hidden lg:inline-flex" aria-label="Alterar tema">
                    <i class="theme-toggle__icon fa-solid fa-moon" aria-hidden="true"></i>
                    <span class="theme-toggle__label">Dark</span>
                </button>

                <!-- Menu btn Mobile -->
                <div class="block lg:hidden translate-x-[-12px]">
                    <button id="openMenuBtn" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="menuAlunoMobile">
                        <i class="fa-solid fa-bars text-xl text-gray-400"></i>
                    </button>
                </div>

                <div id="menuAlunos" class="pt-6 w-full hidden lg:block">
                    <nav class="pt-4 w-full">
                        <ul class="space-y-6 w-full">   
                            <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="demandas" aria-expanded="true"><span>Demandas</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fas fa-tasks h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1vw] icon-nav-alunos icon-select"></i>
                                <button data-secao="demandas" class="h-[50px] w-[100%] font-semibold flex justify-start items-center lg:text-[0.8vw] xl:text-[0.7vw] option-nav-alunos hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar btnSelect effect-bar-select">
                                    <p>Demandas</p>
                                </button>
                            </li>
                    
                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fas fa-history h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1vw] icon-nav-alunos"></i>
                                <button data-secao="historico" class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-alunos hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Histórico</p>
                                </button>
                            </li>

                            <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="acompanhamento" aria-expanded="true"><span>Acompanhamento</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fa-solid fa-chart-column h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1vw] icon-nav-alunos"></i>
                                <button data-secao="relatorios" class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-alunos hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Relatórios</p>
                                </button>
                            </li>

                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fa-solid fa-clock h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1vw] icon-nav-alunos"></i>
                                <button data-secao="registro" class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-alunos hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Registro de Ponto</p>
                                </button>
                            </li>
                    
                            <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="conta" aria-expanded="true"><span>Conta</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fa-solid fa-user h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1vw] icon-nav-alunos"></i>
                                <button data-secao="perfil" class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-alunos hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Perfil</p>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>

            <div class="flex lg:flex-col justify-between lg:justify-center items-center lg:items-start w-full hidden lg:block">
                <li class="flex items-center space-x-2.5 text-gray-200 w-full">
                    <i class="fa-solid fa-right-from-bracket h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1vw] icon-nav-sair"></i>
                    <form class="w-full" action="../controllers/usuario_controller.php?action=logout" method="POST">
                        <button class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-sair hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                            <p>Sair </p>
                        </button>
                    </form>
                </li>
            </div>
        </div>

        <button class="sidebar-collapse-toggle" type="button" aria-label="Ocultar menu lateral" aria-expanded="true" aria-controls="sidebar" title="Ocultar menu lateral">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
        </button>

        <!-- Menu mobile dos alunos -->
        <div id="menuAlunoMobile" class="pt-6 w-full h-[100vh] z-[9999] bg-[var(--dark-green2)] p-6 translate-x-[-100%] transition ease-in-out duration-300 hidden fixed lg:hidden">
            <div class="pt-4 flex justify-between items-center">
                <div class="flex items-center">
                    <img class="system-brand-logo object-cover size-16" src="../assets/s_simbol.png" data-default-src="../assets/s_simbol.png" data-lula-src="../assets/pt-removebg-preview.png" alt="">
                    <div class="space-y-1">
                        <p class="font-semibold"><span class="text-white">Sistema</span> <span class="text-[var(--accent-yellow)]">STGM</span></p>
                        <p class="fraseMenu text-gray-400 text-xs">Conectando demandas a quem pode resolvê-las.</p>
                    </div>
                </div>
                <button type="button" class="theme-toggle lg:hidden" aria-label="Alterar tema">
                    <i class="theme-toggle__icon fa-solid fa-moon" aria-hidden="true"></i>
                    <span class="theme-toggle__label">Dark</span>
                </button>
                <button id="closeMenuBtn" type="button" class="self-start text-white" aria-label="Fechar menu"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            <nav class="pt-8 w-full">
                <ul class="space-y-6 w-full">
                    <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="demandas" aria-expanded="true"><span>Demandas</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fas fa-tasks h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-alunos icon-select"></i>
                        <button data-secao="demandas" class="h-[55px] w-full font-semibold flex justify-start items-center text-sm option-nav-alunos py-2 px-3 rounded-lg effect-bar btnSelect effect-bar-select"><p>Demandas</p></button>
                    </li>
                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fas fa-history h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-alunos"></i>
                        <button data-secao="historico" class="h-[55px] w-full font-semibold flex justify-start items-center text-sm option-nav-alunos py-2 px-3 rounded-lg effect-bar"><p>Histórico</p></button>
                    </li>
                    <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="acompanhamento" aria-expanded="true"><span>Acompanhamento</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fa-solid fa-chart-column h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-alunos"></i>
                        <button data-secao="relatorios" class="h-[55px] w-full font-semibold flex justify-start items-center text-sm option-nav-alunos py-2 px-3 rounded-lg effect-bar"><p>Relatórios</p></button>
                    </li>
                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fa-solid fa-clock h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-alunos"></i>
                        <button data-secao="registro" class="h-[55px] w-full font-semibold flex justify-start items-center text-sm option-nav-alunos py-2 px-3 rounded-lg effect-bar"><p>Registro de Ponto</p></button>
                    </li>
                    <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="conta" aria-expanded="true"><span>Conta</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fa-solid fa-user h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-alunos"></i>
                        <button data-secao="perfil" class="h-[55px] w-full font-semibold flex justify-start items-center text-sm option-nav-alunos py-2 px-3 rounded-lg effect-bar"><p>Perfil</p></button>
                    </li>
                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fa-solid fa-right-from-bracket h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-sair"></i>
                        <form class="w-full" action="../controllers/usuario_controller.php?action=logout" method="POST">
                            <button class="h-[55px] w-full font-semibold flex justify-start items-center text-sm option-nav-sair py-2 px-3 rounded-lg effect-bar"><p>Sair</p></button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>


        <!-- Principal dos alunos -->
            <main id="mainAlunos" class="bg-[var(--gray-crede)] min-h-[100vh] overflow-y-auto lg:ml-64 lg:w-[calc(100vw-16rem)] pt-24 lg:pt-0 w-[100%] pb-8">

                <?php if ($podeFiltrarProjetosAluno): ?>
                    <div id="containerFiltroProjetoAluno" class="filtro-global-projeto fixed hidden" aria-label="Filtrar por projeto">
                        <button type="button" id="btnFiltroProjetoAluno" class="flex h-10 w-16 items-center justify-center rounded-b-full bg-[var(--dark-green)] text-white shadow-lg transition hover:h-12" aria-expanded="false" aria-controls="painelFiltroProjetoAluno" title="Filtrar por projeto">
                            <i class="fa-solid fa-chevron-down transition-transform" aria-hidden="true"></i>
                        </button>
                        <div id="painelFiltroProjetoAluno" class="filtro-global-painel absolute right-0 top-11 hidden w-72 rounded-xl border border-gray-100 bg-white p-4 shadow-2xl">
                            <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-[var(--dark-green)]"><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><span>Filtrar por projeto</span></div>
                            <select id="filtroProjetoAluno" class="w-full rounded-lg border border-gray-200 bg-[var(--suave-green)] px-3 py-2.5 text-sm text-gray-700 outline-none focus:border-[var(--dark-green)]" aria-label="Projeto para filtro">
                                <option value="todos">Todos os projetos</option>
                                <?php foreach ($projetosVinculadosAluno as $projeto): ?>
                                    <option value="<?= (int) $projeto['id'] ?>"><?= htmlspecialchars($projeto['nome'] ?? 'Projeto', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-3 flex items-center justify-between gap-2"><span id="filtroActiveBadgeAluno" class="hidden rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700"><span id="filtroActiveTextAluno"></span></span><button type="button" id="btnLimparFiltroAluno" class="ml-auto text-xs font-semibold text-[var(--dark-green)] hover:text-[var(--dark-green2)]">Limpar filtro</button></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="divModalAssumirAluno" class="w-screen top-1/2 -translate-y-1/2 lg:w-[76vw] flex items-center justify-center fixed z-[220] hidden">

                </div>
                <div id="shadowRegistroPonto" class="bg-black/60 inset-0 z-[210] fixed hidden"></div>

                <!-- DashBoard -->
                <div id="secDashboardAluno" class="secs-aluno pt-12 px-1 lg:px-12 lg:-ml-6">

                    <div class="w-[90%] flex mx-auto items-center justify-between mb-6 space-y-2">
                        <div class="flex flex-col space-y-2 text-center lg:text-start">
                            <p class="text-[var(--dark-green)] font-semibold text-2xl">Demandas</p>
                            <p>Bem-vindo ao sistema de gerenciamento de demandas</p>
                        </div>
                        <div class="hidden lg:block">
                            <div class="flex items-center space-x-2">
                                <div class="iconAluno w-12 h-12 rounded-full bg-[var(--dark-green)] flex items-center justify-center text-white font-semibold">
                                    <?php if ($fotoUsuario): ?>
                                        <img class="fotoPerfilVisual w-full h-full rounded-full object-cover" src="../assets/<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <span class="fotoPerfilVisual spanFoto"><?= substr($nomeUsuario, 0, 1) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-semibold"><?= $nomeUsuario ?></p>
                                    <p class="text-xs text-gray-600"><?= htmlspecialchars($perfilUsuarioExibicao, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-black/60 inset-0 z-[190] fixed hidden shadowDashBoard"></div>

                    <div id="divModalConclusao" class="h-full top-1/2 -translate-y-1/2 w-[76vw] flex items-center justify-center fixed z-[200] hidden">
                        <div id="modalConclusao" class="bg-[var(--white)] w-[90%] sm:w-[420px] pb-8 z-[200] rounded-lg p-5 flex flex-col gap-5 items-center">
                            <i class="fa-regular fa-circle-check text-4xl text-[var(--dark-green)]"></i>
                            <p class="text-[var(--gray2)]"></p>
                            <button id="btnConclusao" type="button" onclick="document.getElementById('divModalConclusao')?.classList.add('hidden'); document.querySelectorAll('.shadowDashBoard').forEach((elemento) => elemento.classList.add('hidden')); document.body.classList.remove('no-scroll'); document.getElementById('mainAlunos')?.classList.remove('no-scroll');" class="py-3 bg-[var(--dark-green)] text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center hover:bg-[var(--dark-green2)] transition ease-in-out duration-300">
                                <p>Concluído</p>
                            </button>
                        </div>
                    </div>

                    <!-- Cards - Apenas Em aberto e Em andamento -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-[90%] mx-auto">
                        <div class="dashboard-status-card bg-[var(--white)] rounded-lg flex flex-col w-full items-start space-y-5">
                            <div class="flex justify-between items-center w-full">
                                <div class="flex items-center space-x-3">
                                    <i class="fa-solid fa-inbox text-[var(--white)] bg-[var(--dark-green)] p-3 rounded-lg"></i>
                                    <p class="text-[var(--dark-green)] font-semibold text-sm lg:text-[0.8vw]">Em aberto</p>
                                </div>
                                <p class="quantDisponiveisViewAluno font-semibold text-[var(--dark-green)]"></p>
                            </div>
                        </div>

                        <div class="dashboard-status-card bg-[var(--white)] rounded-lg flex flex-col w-full items-start space-y-5">
                            <div class="flex justify-between items-center w-full">
                                <div class="flex items-center space-x-3">
                                    <i class="fa-solid fa-hourglass text-[var(--white)] bg-[var(--accent-yellow)] p-3 rounded-lg"></i>
                                    <p class="text-[var(--accent-yellow)] font-semibold text-sm lg:text-[0.8vw]">Em Andamento</p>
                                </div>
                                <p class="quantAndamentoViewAluno text-[var(--accent-yellow-light)] font-semibold"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Ver Informações  !!-->
                    <div class="h-full top-1/2 -translate-y-1/2 w-screen lg:w-[76vw] flex items-center justify-center fixed z-[200] divModalInfs hidden">
                        
                    </div>

                    <!-- Demandas em aberto - organizadas por status com scroll interno -->
                    <div class="dashboard-demandas-area relative grid grid-cols-1 lg:grid-cols-2 gap-6 w-[90%] mx-auto mt-6 items-start">
                        <div id="msgNenhumaDemandaDashboardAluno" class="absolute inset-0 z-10 hidden items-center justify-center"></div>
                        <!-- Em Aberto -->
                        <div id="colunaDashboardEmAberto" class="flex flex-col overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="overflow-y-auto flex-1">
                                <div class="divDemandasDisponiveis grid grid-cols-1 gap-5 p-4" data-status="aberta">
                                </div>
                            </div>
                        </div>

                        <!-- Em Andamento -->
                        <div id="colunaDashboardEmAndamento" class="flex flex-col overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="overflow-y-auto flex-1">
                                <div class="divDemandasAndamento grid grid-cols-1 gap-5 p-4" data-status="em_andamento">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                

                <!-- Demandas -->
                <div id="secDemandasAluno" class="secs-aluno px-1 lg:px-12 lg:-ml-6 hidden pt-12">

                    <div class="w-[90%] flex mx-auto items-center justify-between mb-6 space-y-2">
                        <div class="flex flex-col space-y-2 text-center lg:text-start">
                            <p class="text-[var(--dark-green)] font-semibold text-2xl">Histórico</p>
                        <p>Analise as demandas finalizadas e os resultados do sistema</p>
                        </div>
                        <div class="hidden lg:block">
                            <div class="flex items-center space-x-2">
                                <div class="iconAluno w-12 h-12 rounded-full bg-[var(--dark-green)] flex items-center justify-center text-white font-semibold">
                                    <?php if ($fotoUsuario): ?>
                                        <img class="fotoPerfilVisual w-full h-full rounded-full object-cover" src="../assets/<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <span class="fotoPerfilVisual spanFoto"><?= substr($nomeUsuario, 0, 1) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-semibold"><?= $nomeUsuario ?></p>
                                    <p class="text-xs text-gray-600"><?= htmlspecialchars($perfilUsuarioExibicao, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-black/60 inset-0 z-[190] fixed hidden shadowSecDemandas"></div>

                    <div id="divModalConclusaoSecDemandas" class="h-full top-1/2 -translate-y-1/2 w-[76vw] flex items-center justify-center fixed z-[200] hidden">
                        <div id="modalConclusaoSecDemandas" class="bg-[var(--white)] w-[90%] sm:w-[420px] pb-8 z-[200] rounded-lg p-5 flex flex-col gap-5 items-center">
                            <i class="fa-regular fa-circle-check text-4xl text-[var(--dark-green)]"></i>
                            <p class="text-[var(--gray2)]"></p>
                            <button id="btnConclusaoSecDemandas" type="button" class="py-3 bg-[var(--dark-green)] text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center hover:bg-[var(--dark-green2)] transition ease-in-out duration-300">
                                <p>Concluído</p>
                            </button>
                        </div>
                    </div>

                    <div class="w-[90%] mx-auto mt-12">
                        <div class="lg:w-[50%] xl:w-[40%] h-full flex items-center text-gray-500">
                            <button class="bg-[var(--white)] px-8 lg:px-2 py-3 cursor-pointer lg:w-[40%] text-center rounded-t-2xl text-sm flex items-center space-x-2 justify-center hover:text-[var(--dark-green)] transition ease-in-out duration-300 btnDemandasSec btnDemandasSec_select">
                                <i class="fas fa-globe text-md"></i>
                                <p>Geral</p>
                            </button>

                            <button class="bg-[var(--white)] px-8 lg:px-2 py-3 cursor-pointer lg:w-[250px] text-center rounded-t-2xl text-sm flex items-center space-x-2 justify-center hover:text-[var(--dark-green)] transition ease-in-out duration-300 btnDemandasSec">
                                <i class="fas fa-user-check text-md"></i>
                                <p>Minhas Demandas</p>
                            </button>
                        </div>

                        <!-- Parte de Filtramento -->
                        <div class="w-[100%] bg-[var(--white)] rounded-b-lg rounded-r-lg grid lg:grid-cols-4 gap-5 p-4">
                            <div class="bg-[var(--white-gray)] h-[35px] w-full rounded-lg border border-[#121212]/10 flex items-center">
                                <i class="fa-solid fa-magnifying-glass absolute ml-3"></i>
                                <input id="filtroTituloDemandaAluno" class="w-full bg-[var(--white-gray)] h-full outline-none indent-9" type="search" placeholder="">
                                <label class="absolute ml-9 transition ease-in-out duration-300" for="filtroTituloDemandaAluno">Buscar demanda</label>
                            </div>

                            <div class="bg-[var(--white-gray)] h-[35px] w-full rounded-lg border border-[#121212]/10 flex items-center">
                                <i class="fa-solid fa-triangle-exclamation pr-1 absolute ml-3"></i>
                                <select id="filtroPrioridadeDemandaAluno" class="w-full bg-[var(--white-gray)] h-full outline-none px-9">
                                    <option selected value="">Prioridade</option>
                                    <option value="alta">Alta</option>
                                    <option value="media">Média</option>
                                    <option value="baixa">Baixa</option>
                                </select>
                            </div>

                            <div class="bg-[var(--white-gray)] h-[35px] w-full rounded-lg border border-[#121212]/10 flex items-center">
                                <i class="fa-solid fa-user-tie absolute ml-3"></i>
                                <select id="filtroOrientadorDemandaAluno" class="w-full bg-[var(--white-gray)] h-full outline-none px-9">
                                    <option selected value="">Orientador</option>
                                </select>
                            </div>

                            <div class="bg-[var(--white-gray)] h-[35px] w-full rounded-lg border border-[#121212]/10 flex items-center">
                                <i class="fa-solid fa-circle-info absolute ml-3"></i>
                                <select id="filtroStatusDemandaAluno" class="w-full bg-[var(--white-gray)] h-full outline-none pl-9 px-3">
                                    <option selected value="">Status</option>
                                    <option value="finalizada_concluida">Finalizadas - Concluídas</option>
                                    <option value="finalizada_nao_concluida">Finalizadas - Não Concluídas</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Ver informações -->
                    <div class="h-full top-1/2 -translate-y-1/2 w-screen lg:w-[76vw] flex items-center justify-center fixed z-[200] divModalInfsSecDemandas hidden">
                        
                    </div>

                    <!-- Repartição -->
                    <div class="w-[90%] mx-auto">
                        <!-- Geral -->
                        <div class="geralDemandas demandasSecReparti demandas-grid-aluno pt-6">

                        </div>

                        <div class="minhasDemandas demandas-grid-aluno mt-6 demandasSecReparti hidden w-full">
                        
                        </div>
                    </div>
                </div>

                <!-- Registro de Ponto -->
                <div id="secRegistroPontoAluno" class="secs-aluno relative px-1 lg:px-12 lg:-ml-6 pt-12 hidden">
                    <div class="w-[90%] mx-auto">
                        <div class="w-full flex items-center justify-between mb-6 space-y-2">
                            <div class="flex flex-col space-y-2 text-center lg:text-start">
                                <p class="text-[var(--dark-green)] font-semibold text-2xl">Registro de Ponto</p>
                                <p>Acompanhe seus registros e acesse novas demandas através do QR Code.</p>
                            </div>
                            <div class="hidden lg:block">
                            <div class="flex items-center space-x-2">
                                <div class="iconAluno w-12 h-12 rounded-full bg-[var(--dark-green)] flex items-center justify-center text-white font-semibold">
                                    <?php if ($fotoUsuario): ?>
                                        <img class="fotoPerfilVisual w-full h-full rounded-full object-cover" src="../assets/<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <span class="fotoPerfilVisual spanFoto"><?= substr($nomeUsuario, 0, 1) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-semibold"><?= $nomeUsuario ?></p>
                                    <p class="text-xs text-gray-600"><?= htmlspecialchars($perfilUsuarioExibicao, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex items-center gap-3"><i class="fa-solid fa-calendar-day text-[var(--dark-green)] bg-[var(--suave-green)] w-11 h-11 rounded-lg flex items-center justify-center"></i><div><p class="text-xs uppercase tracking-[0.12em] text-gray-500 font-semibold">Agora</p><p id="pontoDataHora" class="text-lg font-semibold text-[var(--gray2)]"></p></div></div>
                            <button id="btnEscanearQrPonto" type="button" class="bg-[var(--dark-green)] text-white px-5 py-3 rounded-lg font-semibold flex items-center justify-center gap-2 hover:bg-[var(--dark-green2)] transition"><i class="fa-solid fa-camera"></i><span>Escanear QR Code</span></button>
                        </div>

                        <div id="pontoAtivo" class="hidden mt-6"></div>

                        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                                <p class="text-[10px] uppercase tracking-[0.12em] text-gray-500 font-semibold">Demandas do ano</p>
                                <p id="resumoDemandasAno" class="mt-2 text-2xl font-bold text-[var(--dark-green)]">0</p>
                            </div>
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                                <p class="text-[10px] uppercase tracking-[0.12em] text-gray-500 font-semibold">Dias com atividade</p>
                                <p id="resumoDiasAtivos" class="mt-2 text-2xl font-bold text-[var(--gray2)]">0</p>
                            </div>
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                                <p class="text-[10px] uppercase tracking-[0.12em] text-gray-500 font-semibold">Horas registradas</p>
                                <p id="resumoHorasAno" class="mt-2 text-2xl font-bold text-[var(--dark-green)]">0h 00m</p>
                            </div>
                        </div>

                        <div class="calendario-ponto-card rounded-2xl mt-8 overflow-hidden">
                            <div class="calendario-ponto-header px-5 py-4 flex flex-col md:flex-row items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <button type="button" id="btnMesAnteriorPonto" class="w-10 h-10 rounded-lg bg-[var(--suave-green)] text-[var(--dark-green)] hover:bg-[var(--dark-green)] hover:text-white transition"><i class="fa-solid fa-chevron-left"></i></button>
                                    <h2 id="tituloMesCalendarioPonto" class="text-lg md:text-xl font-semibold text-[var(--gray2)] uppercase">Agosto 2026</h2>
                                </div>
                                <div class="flex items-center gap-3">
                                    <select id="selectAnoCalendarioPonto" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 outline-none focus:border-[var(--dark-green)]"></select>
                                    <button type="button" id="btnMesProximoPonto" class="w-10 h-10 rounded-lg bg-[var(--suave-green)] text-[var(--dark-green)] hover:bg-[var(--dark-green)] hover:text-white transition"><i class="fa-solid fa-chevron-right"></i></button>
                                </div>
                            </div>
                            <div class="p-4 md:p-5">
                                <div class="grid grid-cols-7 gap-2 text-center text-[11px] md:text-xs font-semibold uppercase tracking-[0.12em] text-gray-500 mb-3">
                                    <span>Dom</span>
                                    <span>Seg</span>
                                    <span>Ter</span>
                                    <span>Qua</span>
                                    <span>Qui</span>
                                    <span>Sex</span>
                                    <span>Sáb</span>
                                </div>
                                <div id="calendarioPontoMes" class="grid grid-cols-7 gap-2"></div>
                            </div>
                        </div>

                        <div id="pontoResumo" class="hidden"></div>
                        <div id="pontoMinhasDemandas" class="hidden"></div>

                    </div>
                </div>

                <div id="divModalCalendarioDetalhes" class="fixed inset-0 bg-black/60 hidden z-[245] p-4">
                    <div class="w-full h-full flex items-center justify-center">
                        <div id="modalCalendarioDetalhes" class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 p-5 md:p-6">
                        </div>
                    </div>
                </div>

                <!-- Relatórios -->
                <div id="secRelatoriosAluno" class="secs-aluno relative px-1 lg:px-12 lg:-ml-6 pt-12 hidden">
                    <div class="w-[90%] flex mx-auto items-center justify-between mb-6 space-y-2">
                        <div class="flex flex-col space-y-2 text-center lg:text-start">
                            <p class="text-[var(--dark-green)] font-semibold text-2xl">Relatórios</p>
                            <p>Acompanhe os principais indicadores e gere relatórios das demandas.</p>
                        </div>
                        <div class="hidden lg:block">
                            <div class="flex items-center space-x-2">
                                <div class="iconAluno w-12 h-12 rounded-full bg-[var(--dark-green)] flex items-center justify-center text-white font-semibold">
                                    <?php if ($fotoUsuario): ?>
                                        <img class="fotoPerfilVisual w-full h-full rounded-full object-cover" src="../assets/<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <span class="fotoPerfilVisual spanFoto"><?= substr($nomeUsuario, 0, 1) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-semibold"><?= $nomeUsuario ?></p>
                                    <p class="text-xs text-gray-600"><?= htmlspecialchars($perfilUsuarioExibicao, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <section class="relatorios-grid w-[90%] mx-auto">
                        <article class="panel">
                            <div class="panel-header">
                                <h3 class="panel-title">Demandas por situação</h3>
                            </div>
                            <div class="donut-panel">
                                <div class="donut-shell">
                                    <canvas id="donutChartAluno" style="max-width: 260px; max-height: 260px;"></canvas>
                                    <div class="donut-center"><strong id="donutTotalAluno">0</strong><span>Total</span></div>
                                </div>
                                <div class="legend" id="donutLegendAluno" aria-label="Legenda do gráfico"></div>
                            </div>
                        </article>

                        <article class="panel">
                            <div class="panel-header"><h3 class="panel-title">Demandas por categoria</h3></div>
                            <div class="progress-list" id="progressListAluno"></div>
                        </article>
                    </section>

                    <section class="relatorios-categorias">
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top"><div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--verde"><i class="fa-solid fa-file-lines"></i></div><span>Geral</span></div>
                            <p>Visão completa das demandas em aberto no sistema.</p>
                            <a href="relatorios/relatorio_geral.php" target="_blank" rel="noopener noreferrer" class="categoria-relatorio-btn">Gerar relatório</a>
                        </article>
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top"><div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--verde"><i class="fa-solid fa-inbox"></i></div><span>Em Aberto</span></div>
                            <p>Demandas abertas aguardando processamento.</p>
                            <button type="button" class="categoria-relatorio-btn btnRelatorioSituacao" data-situacao="aberta">Gerar relatório</button>
                        </article>
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top"><div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--amarelo"><i class="fa-solid fa-clock"></i></div><span>Em Andamento</span></div>
                            <p>Demandas que estão sendo acompanhadas.</p>
                            <button type="button" class="categoria-relatorio-btn btnRelatorioSituacao" data-situacao="em_andamento">Gerar relatório</button>
                        </article>
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top"><div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--verde"><i class="fa-solid fa-circle-check"></i></div><span>Finalizadas</span></div>
                            <p>Demandas concluídas no sistema.</p>
                            <button type="button" class="categoria-relatorio-btn btnRelatorioSituacao" data-situacao="finalizada">Gerar relatório</button>
                        </article>
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top"><div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--azul"><i class="fa-solid fa-sliders"></i></div><span>Personalizado</span></div>
                            <p>Crie um relatório com filtros personalizados.</p>
                            <button type="button" id="btnRelatorioPersonalizado" class="categoria-relatorio-btn">Gerar relatório</button>
                        </article>
                    </section>

                    <div id="shadowRelatorioPersonalizado" class="bg-black inset-0 opacity-60 z-[190] fixed hidden"></div>
                    <div id="divModalRelatorioPersonalizado" class="fixed inset-0 bg-black/50 hidden z-[200] flex items-center justify-center p-4">
                        <div id="modalRelatorioPersonalizado" class="bg-[var(--white)] w-[90%] sm:w-[500px] max-h-[85vh] pb-8 z-[200] rounded-xl p-5 flex flex-col gap-5 overflow-y-auto shadow-2xl border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[var(--suave-green)] text-[var(--dark-green)] flex items-center justify-center"><i class="fa-solid fa-filter text-xl"></i></div>
                                    <div><p class="text-[11px] uppercase tracking-[0.12em] text-[var(--dark-green)] font-semibold">Relatório</p><h3 class="text-xl font-bold text-[var(--gray2)]">Personalizado</h3></div>
                                </div>
                                <button type="button" id="btnFecharRelatorioPersonalizado" class="w-10 h-10 rounded-lg text-[var(--dark-green)] hover:bg-[var(--suave-green)] transition ease-in-out duration-200 flex items-center justify-center"><i class="fa-solid fa-xmark text-lg"></i></button>
                            </div>

                            <form id="formRelatorioPersonalizado" class="flex flex-col w-full gap-5">
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2"><i class="fa-regular fa-flag text-[var(--dark-green)] text-sm"></i><label class="block text-sm font-semibold">Situação</label></div>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200"><input type="checkbox" name="filtroSituacao" value="aberta" class="w-4 h-4 accent-[var(--dark-green)]"><span class="text-sm text-gray-700">Em Aberto</span></label>
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200"><input type="checkbox" name="filtroSituacao" value="em_andamento" class="w-4 h-4 accent-[var(--dark-green)]"><span class="text-sm text-gray-700">Em Andamento</span></label>
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200"><input type="checkbox" name="filtroSituacao" value="finalizada" class="w-4 h-4 accent-[var(--dark-green)]"><span class="text-sm text-gray-700">Finalizada</span></label>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation text-[var(--dark-green)] text-sm"></i><label class="block text-sm font-semibold">Prioridade</label></div>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200"><input type="checkbox" name="filtroPrioridade" value="alta" class="w-4 h-4 accent-[var(--dark-green)]"><span class="text-sm text-gray-700">Alta</span></label>
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200"><input type="checkbox" name="filtroPrioridade" value="media" class="w-4 h-4 accent-[var(--dark-green)]"><span class="text-sm text-gray-700">Média</span></label>
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200"><input type="checkbox" name="filtroPrioridade" value="baixa" class="w-4 h-4 accent-[var(--dark-green)]"><span class="text-sm text-gray-700">Baixa</span></label>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center gap-2"><i class="fa-solid fa-diagram-project text-[var(--dark-green)] text-sm"></i><label class="block text-sm font-semibold">Projeto</label></div>
                                    <select id="filtroProjetoRelatorio" name="filtroProjeto" class="w-full py-2.5 px-3 rounded-lg bg-[var(--suave-green)] border border-transparent text-gray-600 outline-none focus:border-[var(--dark-green)]">
                                        <option value="">Todos os projetos</option>
                                        <?php foreach ($projetosVinculadosAluno as $projeto): ?>
                                            <option value="<?= (int) $projeto['id'] ?>"><?= htmlspecialchars($projeto['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="space-y-3"><div class="flex items-center gap-2"><i class="fa-solid fa-sitemap text-[var(--dark-green)] text-sm"></i><label for="filtroCategoria" class="block text-sm font-semibold">Categoria</label></div><select id="filtroCategoria" name="filtroCategoria" disabled class="w-full py-2.5 px-3 rounded-lg bg-[var(--suave-green)] border border-transparent text-gray-600 outline-none focus:border-[var(--dark-green)] disabled:opacity-60"><option value="">Selecione um projeto primeiro</option></select></div>

                                <div class="space-y-3"><div class="flex items-center gap-2"><i class="fa-regular fa-calendar text-[var(--dark-green)] text-sm"></i><label class="block text-sm font-semibold">Período de criação</label></div><div class="grid grid-cols-2 gap-3"><div class="space-y-1.5"><span class="text-[10px] uppercase tracking-[0.12em] font-medium text-gray-500">De</span><input type="date" name="filtroDataInicio" class="w-full py-2.5 px-3 rounded-lg bg-[var(--suave-green)] border border-transparent text-gray-600 outline-none focus:border-[var(--dark-green)]"></div><div class="space-y-1.5"><span class="text-[10px] uppercase tracking-[0.12em] font-medium text-gray-500">Até</span><input type="date" name="filtroDataFim" class="w-full py-2.5 px-3 rounded-lg bg-[var(--suave-green)] border border-transparent text-gray-600 outline-none focus:border-[var(--dark-green)]"></div></div><p class="text-[11px] leading-relaxed text-gray-500">Use este intervalo para filtrar demandas criadas dentro do período desejado.</p></div>

                                <div class="flex gap-3 pt-2 w-full"><button type="submit" id="btnGerarRelatorioPersonalizado" class="flex-1 bg-[var(--dark-green)] hover:bg-[var(--dark-green2)] text-white font-semibold py-3 px-4 rounded-lg transition ease-in-out duration-300">Gerar Relatório</button><button type="button" id="btnLimparFiltros" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition ease-in-out duration-300">Limpar</button></div>
                            </form>
                        </div>
                    </div>

                    <div id="legacyStudentReportCards" class="hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <div class="h-auto w-[90%] sm:[200px] lg:w-[300px] bg-white flex flex-col items-center justify-between gap-3 p-6 rounded-md border-l-2 border-l-[var(--dark-green)] m-auto">
                            <i class="bi bi-gear-wide-connected text-6xl text-[var(--accent-yellow)]"></i>
                            <div class="w-full flex flex-col items-center justify-center space-y-1">
                                <p class="text-gray-600 font-semibold">Hardware</p>
                                <span class="w-[60%] h-0.5 bg-[var(--accent-yellow)] block"></span>
                            </div>
                            <button class="btn text-white py-2 w-[100%] bg-[var(--dark-green)] rounded-lg text-[1vw] flex items-center space-x-2 justify-center btnSpecial">
                                <i class="fa-solid fa-file-lines"></i>
                                <p>Gerar Relatório</p>
                            </button>
                        </div>

                        <div class="h-auto w-[90%] sm:[200px] lg:w-[300px] bg-white flex flex-col items-center justify-between gap-3 p-6 rounded-md border-l-2 border-l-[var(--dark-green)] m-auto">
                            <i class="bi bi-globe2 text-6xl text-[var(--accent-yellow)]"></i>
                            <div class="w-full flex flex-col items-center justify-center space-y-1">
                                <p class="text-gray-600 font-semibold">Rede</p>
                                <span class="w-[60%] h-0.5 bg-[var(--accent-yellow)] block"></span>
                            </div>
                            <button class="btn text-white py-2 w-[100%] bg-[var(--dark-green)] rounded-lg text-[1vw] flex items-center space-x-2 justify-center btnSpecial">
                                <i class="fa-solid fa-file-lines"></i>
                                <p>Gerar Relatório</p>
                            </button>
                        </div>

                        <div class="h-auto w-[90%] sm:[200px] lg:w-[300px] bg-white flex flex-col items-center justify-between gap-3 p-6 rounded-md border-l-2 border-l-[var(--dark-green)] m-auto">
                            <i class="bi bi-window-stack text-6xl text-[var(--accent-yellow)]"></i>
                            <div class="w-full flex flex-col items-center justify-center space-y-1">
                                <p class="text-gray-600 font-semibold">Software/Sistemas</p>
                                <span class="w-[60%] h-0.5 bg-[var(--accent-yellow)] block"></span>
                            </div>
                            <button class="btn text-white py-2 w-[100%] bg-[var(--dark-green)] rounded-lg text-[1vw] flex items-center space-x-2 justify-center btnSpecial">
                                <i class="fa-solid fa-file-lines"></i>
                                <p>Gerar Relatório</p>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 pt-10 px-32">
                        <div class="h-auto w-[90%] sm:[200px] lg:w-[300px] bg-white flex flex-col items-center justify-between gap-3 p-6 rounded-md border-l-2 border-l-[var(--dark-green)] m-auto">
                            <i class="bi bi-wrench-adjustable-circle text-6xl text-[var(--accent-yellow)]"></i>
                            <div class="w-full flex flex-col items-center justify-center space-y-1">
                                <p class="text-gray-600 font-semibold">Suporte Opreacional</p>
                                <span class="w-[60%] h-0.5 bg-[var(--accent-yellow)] block"></span>
                            </div>
                            <button class="btn text-white py-2 w-[100%] bg-[var(--dark-green)] rounded-lg text-[1vw] flex items-center space-x-2 justify-center btnSpecial">
                                <i class="fa-solid fa-file-lines"></i>
                                <p>Gerar Relatório</p>
                            </button>
                        </div>

                        <div class="h-auto w-[90%] sm:[200px] lg:w-[300px] bg-white flex flex-col items-center justify-between gap-3 p-6 rounded-md border-l-2 border-l-[var(--dark-green)] m-auto">
                            <i class="bi bi-box-seam text-6xl text-[var(--accent-yellow)]"></i>
                            <div class="w-full flex flex-col items-center justify-center space-y-1">
                                <p class="text-gray-600 font-semibold text-center">Empréstimo Equipamentos</p>
                                <span class="w-[60%] h-0.5 bg-[var(--accent-yellow)] block"></span>
                            </div>
                            <button class="btn text-white py-2 w-[100%] bg-[var(--dark-green)] rounded-lg text-[1vw] flex items-center space-x-2 justify-center btnSpecial">
                                <i class="fa-solid fa-file-lines"></i>
                                <p>Gerar Relatório</p>
                            </button>
                        </div>



                        <!-- MODAL USUÁRIOS -->
                        <div id="modalUsuarios" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">

                            <div class="modal-content bg-white w-[90%] max-w-6xl rounded-2xl p-8 shadow-2xl relative">

                                <!-- TOPO -->
                                <div class="flex items-center justify-between mb-6 border-b pb-4">

                                    <div>
                                        <h2 class="text-2xl font-bold text-[var(--dark-green)]">
                                            Relatório de Usuários
                                        </h2>

                                        <p class="text-gray-500 text-sm">
                                            Visualização dos usuários cadastrados
                                        </p>
                                    </div>

                                    <!-- FECHAR -->
                                    <button id="fecharModalUsuarios"
                                        class="w-10 h-10 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition">

                                        <i class="fa-solid fa-xmark"></i>

                                    </button>

                                </div>

                                <!-- TABELA -->
                                <div class="overflow-x-auto rounded-xl border border-gray-200">

                                    <table class="w-full text-sm text-left">

                                        <!-- CABEÇALHO -->
                                        <thead class="bg-[var(--dark-green)] text-white">

                                            <tr>
                                                <th class="px-5 py-4">ID</th>
                                                <th class="px-5 py-4">Nome</th>
                                                <th class="px-5 py-4">Série</th>
                                                <th class="px-5 py-4">Matrícula</th>
                                                <th class="px-5 py-4">Perfil</th>
                                                <th class="px-5 py-4">Email</th>
                                                <th class="px-5 py-4">PTS</th>
                                            </tr>

                                        </thead>

                                        <!-- CORPO -->
                                        <tbody class="divide-y divide-gray-200 bg-white">

                                            <tr class="hover:bg-gray-50 transition">

                                                <td class="px-5 py-4 font-semibold">#001</td>

                                                <td class="px-5 py-4">
                                                    Francisco Otávio
                                                </td>

                                                <td class="px-5 py-4">

                                                </td>

                                                <td class="px-5 py-4">
                                                    2026001
                                                </td>

                                                <td class="px-5 py-4">

                                                    <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs font-semibold">
                                                        Administrador
                                                    </span>

                                                </td>

                                                <td class="px-5 py-4">
                                                    otavio.filho@prof.ce.gov.br
                                                </td>

                                                <td class="px-5 py-4 font-semibold text-[var(--dark-green)]">
                                                    0
                                                </td>

                                            </tr>

                                            <tr class="hover:bg-gray-50 transition">

                                                <td class="px-5 py-4 font-semibold">#002</td>

                                                <td class="px-5 py-4">
                                                    Cássio Holanda
                                                </td>

                                                <td class="px-5 py-4">
                                                    3
                                                </td>

                                                <td class="px-5 py-4">
                                                    2026002
                                                </td>

                                                <td class="px-5 py-4">

                                                    <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-semibold">
                                                        Usuário
                                                    </span>

                                                </td>

                                                <td class="px-5 py-4">
                                                    cassio.severiano@aluno.ce.gov.br
                                                </td>

                                                <td class="px-5 py-4 font-semibold text-[var(--dark-green)]">
                                                    320
                                                </td>

                                            </tr>

                                            <tr class="hover:bg-gray-50 transition">

                                                <td class="px-5 py-4 font-semibold">#003</td>

                                                <td class="px-5 py-4">
                                                    felipe
                                                </td>

                                                <td class="px-5 py-4">
                                                    3
                                                </td>

                                                <td class="px-5 py-4">
                                                    2026003
                                                </td>

                                                <td class="px-5 py-4">

                                                    <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs font-semibold">
                                                        Aluno
                                                    </span>

                                                </td>

                                                <td class="px-5 py-4">
                                                    felipe@email.com
                                                </td>

                                                <td class="px-5 py-4 font-semibold text-[var(--dark-green)]">
                                                    90
                                                </td>

                                            </tr>

                                        </tbody>

                                    </table>

                                </div>
                                <div class="flex justify-end mt-6">

                                    <button type="submit"
                                        class="bg-[var(--dark-green)] hover:opacity-90 transition text-white px-6 py-3 rounded-xl flex items-center gap-2">

                                        <i class="fa-solid fa-file-pdf"></i>
                                        Gerar Relatório

                                    </button>

                                </div>
                            </div>

                        </div>
                    </div>
                    </div>

                </div>


                <!-- Perfil -->
                <div id="secPerfilAluno" class="secs-aluno relative px-1 lg:px-12 lg:-ml-6 pt-12 hidden overflow-y-auto">
                    <div class="w-[90%] mx-auto">
                          <div class="w-[90%] lg:w-full flex mx-auto items-center justify-between mb-6 space-y-2">
                            <div class="flex flex-col space-y-2 text-center lg:text-start">
                                <p class="text-[var(--dark-green)] font-semibold text-2xl">Perfil</p>
                                <p>Consulte suas informações no sistema.</p>
                            </div>
                            <div class="hidden lg:block">
                                <div class="flex items-center space-x-2">
                                <div class="iconAluno w-12 h-12 rounded-full bg-[var(--dark-green)] flex items-center justify-center text-white font-semibold overflow-hidden">
                                    <?php if ($fotoUsuario): ?>
                                        <img id="fotoPerfilCabecalho" class="fotoPerfilVisual w-full h-full rounded-full object-cover" src="../assets/<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <span id="fotoPerfilCabecalho" class="fotoPerfilVisual spanFoto"><?= substr($nomeUsuario, 0, 1) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-semibold"><?= $nomeUsuario ?></p>
                                    <p class="text-xs text-gray-600"><?= htmlspecialchars($perfilUsuarioExibicao, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                </div>
                            </div>
                        </div>

                        <div class="perfil-card-usuario bg-[var(--perfil-card-bg)] rounded-xl p-6 sm:p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6 shadow-sm border border-white/10">
                            <div class="flex items-center gap-4 sm:gap-5">
                                <div class="iconAluno perfil-avatar-ring w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-full bg-[var(--perfil-card-bg-strong)] flex items-center justify-center overflow-hidden text-white text-3xl font-semibold">
                                    <?php if ($fotoUsuario): ?>
                                        <img id="fotoPerfilPrincipal" class="fotoPerfilVisual w-full h-full rounded-full object-cover" src="../assets/<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <span id="fotoPerfilPrincipal" class="fotoPerfilVisual spanFoto"><?= substr($nomeUsuario, 0, 1) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="space-y-1">
                                    <p class="nomeAluno text-[var(--perfil-text-on-accent)] text-xl sm:text-2xl font-semibold"><?= $nomeUsuario ?></p>
                                    <p class="text-white/80 text-sm"><?= htmlspecialchars($perfilUsuarioExibicao, ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="text-white/60 text-xs">Perfil ativo</p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <button type="button" id="btnAlterarFoto" onclick="document.getElementById('inputFotoPerfil')?.click()" class="bg-[var(--perfil-button-bg)] text-[var(--perfil-button-text)] text-sm font-semibold py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 hover:bg-[var(--perfil-button-hover)] transition ease-in-out duration-300">
                                    <i class="fa-solid fa-camera"></i><span>Alterar foto</span>
                                </button>
                                <button type="button" id="btnRemoverFoto" class="border border-red-200 bg-red-50 text-red-700 text-sm font-semibold py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 hover:bg-red-100 transition ease-in-out duration-300" <?= $fotoUsuario ? '' : 'disabled' ?>>
                                    <i class="fa-solid fa-trash-can"></i><span>Remover foto</span>
                                </button>
                            </div>
                            <input type="file" id="inputFotoPerfil" accept="image/*" class="hidden">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                                <div class="flex items-center gap-3 mb-4"><i class="bi bi-envelope-fill text-[var(--dark-green)] bg-[var(--suave-green)] h-11 w-11 flex items-center justify-center rounded-lg"></i><p class="text-xs uppercase tracking-[0.12em] text-gray-500 font-semibold">E-mail</p></div>
                                <p class="emailValue text-gray-800 font-medium break-words"><?= $emailUsuario ?></p>
                            </div>
                            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                                <div class="flex items-center gap-3 mb-4"><i class="fa-solid fa-sitemap text-[var(--dark-green)] bg-[var(--suave-green)] h-11 w-11 flex items-center justify-center rounded-lg"></i><p class="text-xs uppercase tracking-[0.12em] text-gray-500 font-semibold">Perfil</p></div>
                                <p class="setorValue text-gray-800 font-medium"><?= htmlspecialchars($perfilUsuarioExibicao, ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <?php /* Card de rendimento temporariamente oculto, como na tela do admin. */ ?>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm mt-6 overflow-hidden">
                            <div class="p-5 flex items-center gap-3 border-b border-gray-100">
                                <i class="fa-solid fa-ranking-star text-[var(--dark-green)] bg-[var(--suave-green)] h-11 w-11 flex items-center justify-center rounded-lg"></i>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.12em] text-gray-500 font-semibold">Ranking</p>
                                    <p class="text-gray-800 font-semibold">Pontuação da equipe</p>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-[var(--dark-green2)] text-white">
                                        <tr>
                                            <th class="px-5 py-3">#</th>
                                            <th class="px-5 py-3">
                                                <i class="fa-solid fa-user text-white mr-2"></i>
                                                Usuário
                                            </th>
                                            <th class="px-5 py-3">
                                                <i class="fa-solid fa-sitemap text-white mr-2"></i>
                                                Tipo
                                            </th>
                                            <th class="px-5 py-3 text-right">
                                                <i class="fa-solid fa-star text-white mr-2"></i>
                                                Pontuação
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="rankingPontuacaoBody" class="divide-y divide-gray-100">
                                        <?php foreach ($rankingPontuacao as $posicao => $usuarioRanking): ?>
                                            <tr class="hover:bg-[var(--gray-crede)] transition">
                                                <td class="px-5 py-3 font-semibold text-[var(--accent-yellow)]"><?= $posicao + 1 ?></td>
                                                <td class="px-5 py-3 font-medium text-gray-800">
                                                    <div class="flex items-center gap-3 min-h-10">
                                                        <div class="w-10 h-10 rounded-full bg-[var(--dark-green)] text-white flex items-center justify-center font-semibold text-sm shrink-0 overflow-hidden">
                                                            <?php if (!empty($usuarioRanking['foto'])): ?>
                                                                <img class="w-full h-full object-cover" src="../assets/<?= htmlspecialchars($usuarioRanking['foto'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($usuarioRanking['nome'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <?php else: ?>
                                                                <?= htmlspecialchars(substr($usuarioRanking['nome'], 0, 1), ENT_QUOTES, 'UTF-8') ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span><?= htmlspecialchars($usuarioRanking['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3 text-gray-600"><?= htmlspecialchars($usuarioRanking['tipo_exibicao'] ?? 'Tipo não definido', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="px-5 py-3 text-right font-semibold text-[var(--dark-green)]"><?= (int) $usuarioRanking['pontuacao'] ?> pts</td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (!$rankingPontuacao): ?>
                                            <tr><td colspan="4" class="px-5 py-6 text-center text-gray-500">Nenhuma pontuação registrada.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="modalCorteFoto" class="fixed inset-0 bg-black/60 hidden z-[240] flex items-center justify-center p-4">
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl p-5">
                            <div class="flex items-center justify-between mb-4"><div><p class="text-xs uppercase tracking-[0.14em] text-gray-500">Imagem do perfil</p><h3 class="text-xl font-bold text-[var(--dark-green)]">Ajustar foto</h3></div><button type="button" id="btnFecharCorteFoto" class="w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100"><i class="fa-solid fa-xmark"></i></button></div>
                            <div class="h-72 sm:h-96 bg-gray-100 rounded-lg overflow-hidden"><img id="imagemParaCorte" class="block max-w-full" alt="Pré-visualização da foto"></div>
                            <div class="flex justify-end gap-3 mt-5"><button type="button" id="btnCancelarCorteFoto" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold">Cancelar</button><button type="button" id="btnSalvarCorteFoto" class="bg-[var(--dark-green)] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[var(--dark-green2)]">Salvar foto</button></div>
                        </div>
                    </div>
                </div>
                <div id="modalLeitorQrPonto" class="fixed inset-0 bg-black/60 hidden z-[240] items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-5">
                        <div class="flex items-center justify-between mb-4"><div><p class="text-xs uppercase tracking-[0.12em] text-gray-500">Registro de Ponto</p><h2 class="text-xl font-semibold text-[var(--dark-green)]">Ler QR Code</h2></div><button id="btnFecharLeitorQr" type="button" class="w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Fechar leitor"><i class="fa-solid fa-xmark"></i></button></div>
                        <div class="relative aspect-video bg-gray-900 rounded-lg overflow-hidden"><video id="videoLeitorQr" class="w-full h-full object-cover" autoplay muted playsinline></video><div class="absolute inset-[18%] border-2 border-[var(--accent-yellow)] rounded-lg"></div></div>
                        <p class="text-center text-sm text-gray-600 mt-4">Aponte a câmera para o QR Code da demanda</p><p id="qrStatusPonto" class="text-center text-xs text-red-600 mt-2"></p>
                        <button id="btnCancelarLeitorQr" type="button" class="mt-4 w-full py-3 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition">Cancelar</button>
                    </div>
                </div>


                <div id="divModalDemandaEncontrada" class="fixed top-0 inset-0 bg-black/60 hidden z-[245] items-center justify-center p-4">
                    
                </div>


                <div id="divModalAviso" class="fixed top-4 hidden z-[260] w-screen lg:w-[76vw] pointer-events-none" aria-live="polite"></div>

                <div id="modalConfirmacaoFoto" class="fixed inset-0 bg-black/60 hidden z-[265] items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-5">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-question text-[var(--dark-green)] text-xl"></i>
                            <h2 id="textoConfirmacaoFoto" class="text-lg font-semibold text-[var(--gray2)]"></h2>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" id="btnCancelarConfirmacaoFoto" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300">Cancelar</button>
                            <button type="button" id="btnConfirmarFoto" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700">Remover</button>
                        </div>
                    </div>
                </div>

                <div id="modalJustificativaReabertura" class="fixed inset-0 bg-black/60 hidden z-[265] items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold text-[var(--gray2)]">Justificativa para reabertura</h2>
                            <button type="button" id="btnFecharJustificativaReabertura" class="w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <textarea id="campoJustificativaReabertura" rows="4" class="w-full mt-4 border border-gray-200 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--dark-green)]" placeholder="Digite a justificativa para reabertura"></textarea>
                        <div class="flex justify-end gap-3 mt-5">
                            <button type="button" id="btnCancelarJustificativaReabertura" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300">Cancelar</button>
                            <button type="button" id="btnEnviarJustificativaReabertura" class="px-4 py-2 rounded-lg bg-[var(--dark-green)] text-white font-semibold hover:bg-[var(--dark-green2)]">Enviar</button>
                        </div>
                    </div>
                </div>
            </main>
            <!-- FIM - Principal dos alunos --> 
    </div>

    <script>

        (() => {
            let cortadorFotoAluno = null;
            let arquivoFotoAluno = null;

            const avisar = (tipo, mensagem) => {
                if (typeof window.notify?.[tipo] === 'function') {
                    window.notify[tipo](mensagem);
                } else if (typeof mostrarAvisoPonto === 'function') {
                    mostrarAvisoPonto(mensagem, tipo);
                } else {
                    console.error(mensagem);
                }
            };

            const confirmarRemocaoFoto = () => new Promise((resolve) => {
                const modalConfirmacao = document.getElementById('modalConfirmacaoFoto');
                const textoConfirmacao = document.getElementById('textoConfirmacaoFoto');
                const cancelar = document.getElementById('btnCancelarConfirmacaoFoto');
                const confirmar = document.getElementById('btnConfirmarFoto');
                if (!modalConfirmacao || !textoConfirmacao || !cancelar || !confirmar) {
                    resolve(false);
                    return;
                }

                textoConfirmacao.textContent = 'Deseja remover sua foto de perfil?';
                modalConfirmacao.classList.remove('hidden');
                modalConfirmacao.classList.add('flex');

                const fechar = (resultado) => {
                    modalConfirmacao.classList.add('hidden');
                    modalConfirmacao.classList.remove('flex');
                    cancelar.onclick = null;
                    confirmar.onclick = null;
                    resolve(resultado);
                };

                cancelar.onclick = () => fechar(false);
                confirmar.onclick = () => fechar(true);
            });

            const fecharCorte = () => {
                document.getElementById('modalCorteFoto')?.classList.add('hidden');
                cortadorFotoAluno?.destroy();
                cortadorFotoAluno = null;
                arquivoFotoAluno = null;
                const input = document.getElementById('inputFotoPerfil');
                if (input) input.value = '';
            };

            const input = document.getElementById('inputFotoPerfil');
            const imagem = document.getElementById('imagemParaCorte');
            const modal = document.getElementById('modalCorteFoto');
            const salvar = document.getElementById('btnSalvarCorteFoto');
            const remover = document.getElementById('btnRemoverFoto');

            input?.addEventListener('change', (event) => {
                const arquivo = event.target.files?.[0];
                if (!arquivo || !arquivo.type.startsWith('image/')) {
                    avisar('warning', 'Selecione um arquivo de imagem.');
                    return;
                }
                if (typeof Cropper === 'undefined' || !imagem || !modal) {
                    avisar('error', 'Não foi possível abrir o recorte da imagem.');
                    return;
                }

                arquivoFotoAluno = arquivo;
                imagem.onload = () => {
                    cortadorFotoAluno?.destroy();
                    cortadorFotoAluno = new Cropper(imagem, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        responsive: true,
                        background: false
                    });
                };
                imagem.src = URL.createObjectURL(arquivo);
                modal.classList.remove('hidden');
            });

            salvar?.addEventListener('click', () => {
                if (!cortadorFotoAluno || !arquivoFotoAluno) {
                    avisar('warning', 'Selecione uma foto antes de salvar.');
                    return;
                }

                salvar.disabled = true;
                const canvas = cortadorFotoAluno.getCroppedCanvas({ width: 800, height: 800, imageSmoothingQuality: 'high' });
                canvas.toBlob(async (blob) => {
                    if (!blob) {
                        salvar.disabled = false;
                        avisar('error', 'Não foi possível preparar a imagem.');
                        return;
                    }

                    try {
                        const dados = new FormData();
                        dados.append('foto', blob, 'foto-perfil.jpg');
                        const resposta = await fetch('../controllers/usuario_controller.php?action=upload_foto', {
                            method: 'POST',
                            body: dados,
                            credentials: 'same-origin',
                            cache: 'no-store'
                        });
                        const texto = await resposta.text();
                        let resultado;
                        try {
                            resultado = JSON.parse(texto);
                        } catch (erro) {
                            throw new Error('Resposta inválida do servidor ao salvar a foto.');
                        }
                        if (!resposta.ok || !resultado.sucesso) {
                            throw new Error(resultado.erro || `HTTP ${resposta.status}`);
                        }

                        document.querySelectorAll('.fotoPerfilVisual').forEach((elemento) => {
                            const imagemAtual = document.createElement('img');
                            imagemAtual.className = 'fotoPerfilVisual w-full h-full rounded-full object-cover';
                            imagemAtual.src = '../assets/' + resultado.foto;
                            imagemAtual.alt = 'Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>';
                            elemento.replaceWith(imagemAtual);
                        });
                        remover?.removeAttribute('disabled');
                        fecharCorte();
                        avisar('success', 'Foto atualizada com sucesso!');
                        window.atualizarRankingPontuacao?.();
                    } catch (erro) {
                        console.error('Erro ao salvar foto:', erro);
                        avisar('error', erro.message || 'Erro ao salvar a foto.');
                        salvar.disabled = false;
                    }
                }, 'image/jpeg', 0.9);
            });

            document.getElementById('btnFecharCorteFoto')?.addEventListener('click', fecharCorte);
            document.getElementById('btnCancelarCorteFoto')?.addEventListener('click', fecharCorte);

            remover?.addEventListener('click', async () => {
                if (!await confirmarRemocaoFoto()) return;
                remover.disabled = true;
                try {
                    const resposta = await fetch('../controllers/usuario_controller.php?action=remover_foto', {
                        method: 'POST',
                        credentials: 'same-origin',
                        cache: 'no-store'
                    });
                    const texto = await resposta.text();
                    const resultado = JSON.parse(texto);
                    if (!resposta.ok || !resultado.sucesso) throw new Error(resultado.erro || `HTTP ${resposta.status}`);

                    document.querySelectorAll('.fotoPerfilVisual').forEach((elemento) => {
                        const inicial = document.createElement('span');
                        inicial.className = 'fotoPerfilVisual spanFoto';
                        inicial.textContent = '<?= strtoupper(substr($nomeUsuario, 0, 1)) ?>';
                        elemento.replaceWith(inicial);
                    });
                    avisar('success', 'Foto removida com sucesso!');
                    window.atualizarRankingPontuacao?.();
                } catch (erro) {
                    console.error('Erro ao remover foto:', erro);
                    avisar('error', erro.message || 'Erro ao remover a foto.');
                    remover.disabled = false;
                }
            });
        })();

        window.addEventListener("pageshow", () => {
            const form = document.getElementById("formDemanda");

            if (!form) return;
            
            form.reset();

            form.querySelectorAll("input, select, textarea").forEach(el => {
                if (el.tagName === "SELECT") {
                el.selectedIndex = 0;
            } else {
                el.value = "";
            }
            });  

            document.getElementById("descriDemandaTextArea").value = "";
        });

        const btnOptionsAluno = document.querySelectorAll(".option-nav-alunos");
        const btnOptionSair = document.querySelector(".option-nav-sair");
        const openMenuBtn = document.getElementById("openMenuBtn");
        const menuAlunoMobile = document.getElementById("menuAlunoMobile");
        const closeMenuBtn = document.getElementById("closeMenuBtn");
        const sidebarGroupButtons = document.querySelectorAll(".sidebar-group-toggle");
        const sidebarGroupsStorageKey = "system-suporte-sidebar-groups-aluno";
        const sidebarElement = document.getElementById("sidebar");
        const sidebarCollapseToggle = document.querySelector(".sidebar-collapse-toggle");
        const desktopSidebarQuery = window.matchMedia("(min-width: 1024px)");
        const sidebarCollapseStorageKey = "system-suporte-sidebar-collapsed-aluno";
        let savedSidebarGroups = {};
        let sidebarCollapsedPreference = false;

        try {
            sidebarCollapsedPreference = localStorage.getItem(sidebarCollapseStorageKey) === "true";
        } catch (error) {
            console.warn("Não foi possível restaurar o estado do menu lateral.", error);
        }

        const updateSidebarCollapse = () => {
            const collapsed = desktopSidebarQuery.matches && sidebarCollapsedPreference;
            document.body.classList.toggle("sidebar-collapsed", collapsed);
            sidebarElement.setAttribute("aria-hidden", String(collapsed));
            sidebarElement.inert = collapsed;
            const label = collapsed ? "Mostrar menu lateral" : "Ocultar menu lateral";
            sidebarCollapseToggle.setAttribute("aria-expanded", String(!collapsed));
            sidebarCollapseToggle.setAttribute("aria-label", label);
            sidebarCollapseToggle.setAttribute("title", label);
        };

        updateSidebarCollapse();
        sidebarCollapseToggle.addEventListener("click", () => {
            sidebarCollapsedPreference = !sidebarCollapsedPreference;
            try {
                localStorage.setItem(sidebarCollapseStorageKey, String(sidebarCollapsedPreference));
            } catch (error) {
                console.warn("Não foi possível salvar o estado do menu lateral.", error);
            }
            updateSidebarCollapse();
        });
        desktopSidebarQuery.addEventListener("change", updateSidebarCollapse);

        try {
            const storedSidebarGroups = localStorage.getItem(sidebarGroupsStorageKey);
            if (storedSidebarGroups) {
                savedSidebarGroups = JSON.parse(storedSidebarGroups);
                if (!savedSidebarGroups || typeof savedSidebarGroups !== "object" || Array.isArray(savedSidebarGroups)) {
                    throw new TypeError("Estado dos grupos do menu inválido.");
                }
            }
        } catch (error) {
            console.warn("Não foi possível restaurar os grupos do menu lateral.", error);
            savedSidebarGroups = {};
        }

        const setSidebarGroupExpanded = (groupKey, expanded, animate = false) => {
            sidebarGroupButtons.forEach((button) => {
                if (button.dataset.sidebarGroup !== groupKey) return;
                button.setAttribute("aria-expanded", String(expanded));
                let item = button.closest("li")?.nextElementSibling;
                let itemIndex = 0;
                while (item && !item.classList.contains("sidebar-section-heading")) {
                    item.hidden = !expanded;
                    item.classList.remove("sidebar-item-enter");
                    item.style.removeProperty("--sidebar-item-index");
                    if (expanded && animate) {
                        const animatedItem = item;
                        animatedItem.style.setProperty("--sidebar-item-index", String(itemIndex));
                        animatedItem.classList.add("sidebar-item-enter");
                        animatedItem.addEventListener("animationend", () => {
                            animatedItem.classList.remove("sidebar-item-enter");
                            animatedItem.style.removeProperty("--sidebar-item-index");
                        }, { once: true });
                    }
                    itemIndex += 1;
                    item = item.nextElementSibling;
                }
            });
        };

        sidebarGroupButtons.forEach((button) => {
            const groupKey = button.dataset.sidebarGroup;
            const expanded = Object.prototype.hasOwnProperty.call(savedSidebarGroups, groupKey)
                ? savedSidebarGroups[groupKey] === true
                : true;
            setSidebarGroupExpanded(groupKey, expanded);
            button.addEventListener("click", () => {
                const nextExpanded = button.getAttribute("aria-expanded") !== "true";
                setSidebarGroupExpanded(groupKey, nextExpanded, true);
                savedSidebarGroups[groupKey] = nextExpanded;
                try {
                    localStorage.setItem(sidebarGroupsStorageKey, JSON.stringify(savedSidebarGroups));
                } catch (error) {
                    console.warn("Não foi possível salvar os grupos do menu lateral.", error);
                }
            });
        });

        if (openMenuBtn && menuAlunoMobile) {
            openMenuBtn.onclick = () => {
                menuAlunoMobile.classList.remove("hidden");
                openMenuBtn.setAttribute("aria-expanded", "true");
                requestAnimationFrame(() => menuAlunoMobile.classList.remove("translate-x-[-100%]"));
            };
        }

        if (closeMenuBtn && menuAlunoMobile) {
            closeMenuBtn.onclick = () => {
                menuAlunoMobile.classList.add("translate-x-[-100%]");
                openMenuBtn.setAttribute("aria-expanded", "false");
                setTimeout(() => menuAlunoMobile.classList.add("hidden"), 300);
            };
        }

        const iconsNavAlunos = document.querySelectorAll(".icon-nav-alunos");
        const iconNavSair = document.querySelector(".icon-nav-sair");
        
        const secs_aluno = document.querySelectorAll(".secs-aluno");
        const mainAlunos = document.getElementById("mainAlunos"); 
        const usuario = <?php echo json_encode(mb_strtolower(trim((string) $tipoUsuario), 'UTF-8'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        let donutChartAluno = null;
        let graficosAlunoCarregando = false;

        function mostrarSecaoAluno(secaoAlvo) {
            if (!secaoAlvo) return;

            secs_aluno.forEach((secao) => {
                secao.classList.add("hidden");
                secao.style.display = "none";

                if (secao === secaoAlvo) {
                    secao.classList.remove("hidden");
                    secao.style.display = "block";
                }
            });
        }

        // Navegação dos usuários
        btnOptionsAluno.forEach((btn, index) => {
            btn.onmouseenter = () => {
                iconsNavAlunos.forEach((icon) => {
                    icon.classList.remove("icon-hover");
                });

                iconsNavAlunos[index]?.classList.add("icon-hover");
            };

            btn.onmouseleave = () => {
                iconsNavAlunos.forEach((icon) => {
                    icon.classList.remove("icon-hover");
                });
            };


            btn.onclick = () => {
                const secaoPorNome = {
                    demandas: "secDashboardAluno",
                    historico: "secDemandasAluno",
                    relatorios: "secRelatoriosAluno",
                    registro: "secRegistroPontoAluno",
                    perfil: "secPerfilAluno"
                };
                const textoBotao = btn.textContent.trim().toLowerCase();
                const nomeSecao = String(btn.dataset.secao || (
                    textoBotao.includes("registro") ? "registro" : textoBotao
                )).trim().toLowerCase();
                const idSecao = secaoPorNome[nomeSecao] || "secDashboardAluno";

                btnOptionsAluno.forEach((btn) => {
                    btn.classList.remove("btnSelect");
                });

                btnOptionsAluno[index].classList.remove("text-gray-200");
                btnOptionsAluno[index].classList.add("btnSelect");

                btnOptionsAluno.forEach((btn) => {
                    btn.classList.remove("effect-bar-select");
                });

                btnOptionsAluno[index].classList.add("effect-bar-select");


                iconsNavAlunos.forEach((icon) => {
                    icon.classList.remove("icon-select");
                });

                iconsNavAlunos[index]?.classList.add("icon-select");

                fecharModaisAoTrocarSecaoAluno();
                mostrarSecaoAluno(document.getElementById(idSecao));

                if (idSecao === "secDashboardAluno") {
                    requestAnimationFrame(() => requestAnimationFrame(() => ajustarAlturaColunasDashboard()));
                }

                if (idSecao === "secRelatoriosAluno") {
                    requestAnimationFrame(() => {
                        setTimeout(() => carregarGraficosAluno(), 100);
                    });
                }

                if (idSecao === "secRegistroPontoAluno") {
                    carregarRegistroPonto();
                }

                if (menuAlunoMobile && index >= secs_aluno.length) {
                    menuAlunoMobile.classList.add("translate-x-[-100%]");
                    setTimeout(() => menuAlunoMobile.classList.add("hidden"), 300);
                    openMenuBtn.setAttribute("aria-expanded", "false");
                }
            };
        });

        iconsNavAlunos.forEach((icon) => {
            icon.classList.add('cursor-pointer');
            icon.addEventListener('mouseenter', () => {
                icon.classList.add('icon-hover');
                const botao = icon.closest('li')?.querySelector('.option-nav-alunos');
                botao?.classList.add('icon-hover', 'effect-bar-select');
            });
            icon.addEventListener('mouseleave', () => {
                icon.classList.remove('icon-hover');
                const botao = icon.closest('li')?.querySelector('.option-nav-alunos');
                botao?.classList.remove('icon-hover');
                if (!botao?.classList.contains('btnSelect')) {
                    botao?.classList.remove('effect-bar-select');
                }
            });
            icon.addEventListener('click', () => {
                icon.closest('li')?.querySelector('.option-nav-alunos')?.click();
            });
        });

        const modalLeitorQrPonto = document.getElementById('modalLeitorQrPonto');
        const divModalDemandaEncontrada = document.getElementById('divModalDemandaEncontrada');
        const divModalAviso = document.getElementById('divModalAviso');
        const videoLeitorQr = document.getElementById('videoLeitorQr');
        let cameraQrPonto = null;
        let animacaoQrPonto = null;
        let demandaQrPonto = null;
        let temporizadorAvisoPonto = null;

        function mostrarAvisoPonto(mensagem) {
            if (!divModalAviso) return;

            clearTimeout(temporizadorAvisoPonto);
            divModalAviso.innerHTML = `
                <div class="w-full flex justify-center pointer-events-none" role="alert">
                    <div class="bg-white rounded-xl shadow-lg w-[calc(100%-2rem)] max-w-sm p-5 flex justify-center flex-col items-center pointer-events-auto">
                        <div class="flex items-center justify-center gap-3">
                            <i class="fa-solid fa-circle-info text-[var(--dark-green)] text-xl shrink-0"></i>
                            <p class="textoAvisoPonto text-sm leading-relaxed text-[var(--gray2)] text-center"></p>
                        </div>
                        <div class="h-1 bg-gray-100 rounded-full overflow-hidden mt-5 w-full">
                            <div class="barraAvisoPonto h-full bg-[var(--dark-green)] aviso-ponto-progresso"></div>
                        </div>
                    </div>
                </div>`;
            const texto = divModalAviso.querySelector('.textoAvisoPonto');
            const barra = divModalAviso.querySelector('.barraAvisoPonto');
            texto.textContent = mensagem;
            barra.classList.remove('aviso-ponto-progresso');
            void barra.offsetWidth;
            barra.classList.add('aviso-ponto-progresso');
            divModalAviso.classList.remove('hidden');
            temporizadorAvisoPonto = setTimeout(() => {
                divModalAviso.classList.add('hidden');
                divModalAviso.innerHTML = '';
            }, 2500);
        }

        function atualizarRelogioPonto() {
            const elemento = document.getElementById('pontoDataHora');
            if (elemento) elemento.textContent = new Date().toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
        }

        async function fecharLeitorQr() {
            if (animacaoQrPonto) cancelAnimationFrame(animacaoQrPonto);
            animacaoQrPonto = null;
            cameraQrPonto?.getTracks().forEach((track) => track.stop());
            cameraQrPonto = null;
            modalLeitorQrPonto?.classList.add('hidden');
            if (videoLeitorQr) videoLeitorQr.srcObject = null;
        }

        function abrirConfirmacaoQr(demanda) {
            demandaQrPonto = demanda;
            divModalDemandaEncontrada.innerHTML = `<div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-5"><div class="flex items-center justify-between mb-4"><h2 class="text-xl font-semibold text-[var(--dark-green)]">Demanda encontrada</h2><button type="button" class="btnFecharConfirmacaoQr w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Voltar"><i class="fa-solid fa-arrow-left"></i></button></div><div class="qrDemandaEncontrada space-y-3"><div class="bg-[var(--suave-green)] rounded-lg p-4 space-y-2 text-sm"><p><strong>Título:</strong> ${demanda.titulo || 'Não informado'}</p><p><strong>Descrição:</strong> ${demanda.descricao || 'Sem descrição.'}</p><p><strong>Setor:</strong> ${demanda.projeto || 'Não informado'}</p><p><strong>Prioridade:</strong> ${demanda.prioridade || 'Não informada'}</p><p><strong>Prazo:</strong> ${formatarData(demanda.prazo)}</p></div></div><div class="flex gap-3 mt-5"><button type="button" class="btnVoltarQr flex-1 py-3 rounded-lg bg-gray-200 text-gray-700 font-semibold">Voltar</button><button type="button" class="btnAssumirQr flex-1 py-3 rounded-lg bg-[var(--dark-green)] text-white font-semibold">Assumir demanda</button></div></div>`;
            divModalDemandaEncontrada.classList.remove('hidden');
            divModalDemandaEncontrada.classList.add('flex');
            divModalDemandaEncontrada.querySelector('.btnFecharConfirmacaoQr').onclick = fecharModalDemandaEncontrada;
            divModalDemandaEncontrada.querySelector('.btnVoltarQr').onclick = fecharModalDemandaEncontrada;
            divModalDemandaEncontrada.querySelector('.btnAssumirQr').onclick = assumirDemandaQr;
        }

        function fecharModalDemandaEncontrada() {
            divModalDemandaEncontrada.classList.add('hidden');
            divModalDemandaEncontrada.classList.remove('flex');
            divModalDemandaEncontrada.innerHTML = '';
        }

        async function assumirDemandaQr() {
            if (!demandaQrPonto) return;
            const botao = divModalDemandaEncontrada.querySelector('.btnAssumirQr');
            botao.disabled = true;
            try {
                const resposta = await fetch('../controllers/demanda_controller.php?action=assumir_demanda', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_demanda: demandaQrPonto.id }) });
                const resultado = await resposta.json();
                if (!resultado.sucesso) throw new Error(resultado.erro);
                fecharModalDemandaEncontrada();
                mostrarAvisoPonto('Demanda assumida com sucesso!');
                await carregarDemandas();
                await carregarMinhasDemandas();
                await carregarRegistroPonto();
            } catch (erro) {
                mostrarAvisoPonto(erro.message || 'Não foi possível assumir a demanda.');
            } finally {
                botao.disabled = false;
            }
        }

        async function buscarDemandaQr(id) {
            const resposta = await fetch('../controllers/demanda_controller.php?action=buscar_demanda', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_demanda: id }) });
            const resultado = await resposta.json();
            if (!resultado.sucesso) throw new Error(resultado.erro || 'Não foi possível localizar a demanda.');
            if (resultado.dados.status !== 'aberta') throw new Error('Esta demanda não está disponível para ser assumida.');
            await fecharLeitorQr();
            abrirConfirmacaoQr(resultado.dados);
        }

        function lerQrFrame() {
            if (!videoLeitorQr || videoLeitorQr.readyState < 2 || !window.jsQR) { animacaoQrPonto = requestAnimationFrame(lerQrFrame); return; }
            const canvas = document.createElement('canvas');
            canvas.width = videoLeitorQr.videoWidth; canvas.height = videoLeitorQr.videoHeight;
            const contexto = canvas.getContext('2d'); contexto.drawImage(videoLeitorQr, 0, 0, canvas.width, canvas.height);
            const leitura = window.jsQR(contexto.getImageData(0, 0, canvas.width, canvas.height).data, canvas.width, canvas.height);
            if (leitura?.data) {
                const id = Number((leitura.data.match(/\d+/) || [])[0]);
                if (id > 0) { buscarDemandaQr(id).catch((erro) => { document.getElementById('qrStatusPonto').textContent = erro.message; animacaoQrPonto = requestAnimationFrame(lerQrFrame); }); return; }
            }
            animacaoQrPonto = requestAnimationFrame(lerQrFrame);
        }

        document.getElementById('btnEscanearQrPonto')?.addEventListener('click', async () => {
            document.getElementById('qrStatusPonto').textContent = '';
            modalLeitorQrPonto?.classList.remove('hidden'); modalLeitorQrPonto?.classList.add('flex');
            try { cameraQrPonto = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false }); videoLeitorQr.srcObject = cameraQrPonto; lerQrFrame(); }
            catch (erro) { document.getElementById('qrStatusPonto').textContent = 'Permita o acesso à câmera para ler o QR Code.'; }
        });
        document.getElementById('btnCancelarLeitorQr')?.addEventListener('click', fecharLeitorQr);
        document.getElementById('btnFecharLeitorQr')?.addEventListener('click', fecharLeitorQr);
        document.getElementById('divModalCalendarioDetalhes')?.addEventListener('click', (evento) => {
            if (evento.target === evento.currentTarget) {
                fecharDetalhesDiaCalendario();
            }
        });

        function escaparPonto(valor) {
            return String(valor ?? '').replace(/[&<>'"]/g, (caractere) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[caractere]));
        }

        function formatarDuracaoPonto(inicio, fim = null) {
            if (!inicio) return '-';
            const dataInicio = new Date(String(inicio).replace(' ', 'T'));
            const dataFim = fim ? new Date(String(fim).replace(' ', 'T')) : new Date();
            const segundos = Math.max(0, Math.floor((dataFim - dataInicio) / 1000));
            return `${Math.floor(segundos / 3600)}h ${String(Math.floor((segundos % 3600) / 60)).padStart(2, '0')}min`;
        }

        let estadoCalendarioPonto = null;

        function parseDateValue(valor) {
            if (!valor) return null;
            const texto = String(valor).trim();
            if (!texto) return null;

            const dataBrasileira = texto.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s(\d{2}):(\d{2})(?::(\d{2}))?)?$/);
            if (dataBrasileira) {
                const [, dia, mes, ano, horas = '00', minutos = '00', segundos = '00'] = dataBrasileira;
                return new Date(Number(ano), Number(mes) - 1, Number(dia), Number(horas), Number(minutos), Number(segundos));
            }

            if (/^\d{4}-\d{2}-\d{2}$/.test(texto)) {
                const [ano, mes, dia] = texto.split('-').map(Number);
                return new Date(ano, mes - 1, dia);
            }

            if (/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}(:\d{2})?$/.test(texto)) {
                const [data, hora] = texto.split(' ');
                const [ano, mes, dia] = data.split('-').map(Number);
                const [horas, minutos, segundos = '00'] = hora.split(':').map(Number);
                return new Date(ano, mes - 1, dia, horas, minutos, segundos);
            }

            const dataUsandoHora = texto.includes(' ') ? texto.replace(' ', 'T') : texto;
            const data = new Date(dataUsandoHora);
            if (Number.isNaN(data.getTime())) {
                return null;
            }
            return data;
        }

        function formatarDuracaoMinutos(minutos) {
            if (!Number.isFinite(minutos) || minutos <= 0) return '0h 00m';
            const horas = Math.floor(minutos / 60);
            const resto = minutos % 60;
            return `${horas}h ${String(resto).padStart(2, '0')}m`;
        }

        function obterDuracaoEmMinutos(registro) {
            const inicio = parseDateValue(registro?.atribuicao_inicio || registro?.data_inicio || registro?.data_criacao);
            const fim = parseDateValue(registro?.atribuicao_fim || registro?.data_finalizacao || registro?.data_fim);
            if (!inicio) return 0;
            const finalizacao = fim || new Date();
            const diff = Math.max(0, Math.round((finalizacao.getTime() - inicio.getTime()) / 60000));
            return diff;
        }

        function getDataKeyLocal(data) {
            const ano = data.getFullYear();
            const mes = String(data.getMonth() + 1).padStart(2, '0');
            const dia = String(data.getDate()).padStart(2, '0');
            return `${ano}-${mes}-${dia}`;
        }

        function formatarDataCompleta(dataIso) {
            const valor = parseDateValue(dataIso);
            if (!valor) return 'Data indisponível';
            return valor.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' });
        }

        function resetarEstadoCalendarioPonto() {
            const hoje = new Date();
            const anoMinimo = 2026;
            const anoAtual = Math.max(anoMinimo, hoje.getFullYear());
            estadoCalendarioPonto = {
                ano: anoAtual,
                mes: Math.min(11, hoje.getMonth())
            };

            if (estadoCalendarioPonto.ano === anoMinimo && estadoCalendarioPonto.mes < 0) {
                estadoCalendarioPonto.mes = 0;
            }
        }

        function montarSelectAnoCalendarioPonto() {
            const select = document.getElementById('selectAnoCalendarioPonto');
            if (!select) return;

            const anos = [];
            for (let i = 2026; i <= 2035; i += 1) {
                anos.push(i);
            }

            select.innerHTML = anos.map((ano) => `<option value="${ano}" ${ano === estadoCalendarioPonto?.ano ? 'selected' : ''}>${ano}</option>`).join('');
            select.value = String(estadoCalendarioPonto?.ano ?? 2026);
        }

        function atualizarResumoCalendarioPonto(registros) {
            const anoAtual = estadoCalendarioPonto?.ano ?? new Date().getFullYear();
            const registrosAno = (registros || []).filter((registro) => {
                const data = parseDateValue(registro?.atribuicao_inicio || registro?.data_finalizacao || registro?.data_inicio || registro?.data_criacao);
                if (!data) return false;
                return data.getFullYear() === anoAtual;
            });

            const idsDias = new Set();
            let totalMinutos = 0;

            registrosAno.forEach((registro) => {
                const inicio = parseDateValue(registro?.atribuicao_inicio || registro?.data_inicio || registro?.data_criacao);
                if (inicio) {
                    idsDias.add(getDataKeyLocal(inicio));
                }
                totalMinutos += obterDuracaoEmMinutos(registro);
            });

            const resumoDemandasAno = document.getElementById('resumoDemandasAno');
            const resumoDiasAtivos = document.getElementById('resumoDiasAtivos');
            const resumoHorasAno = document.getElementById('resumoHorasAno');

            if (resumoDemandasAno) resumoDemandasAno.textContent = String(registrosAno.length);
            if (resumoDiasAtivos) resumoDiasAtivos.textContent = String(idsDias.size);
            if (resumoHorasAno) resumoHorasAno.textContent = formatarDuracaoMinutos(totalMinutos);
        }

        function renderizarCalendarioPonto(registros) {
            if (!estadoCalendarioPonto) {
                resetarEstadoCalendarioPonto();
            }

            const container = document.getElementById('calendarioPontoMes');
            const titulo = document.getElementById('tituloMesCalendarioPonto');
            const selectAno = document.getElementById('selectAnoCalendarioPonto');
            const btnMesAnterior = document.getElementById('btnMesAnteriorPonto');
            const btnMesProximo = document.getElementById('btnMesProximoPonto');
            if (!container || !titulo || !selectAno) return;

            const anoAtual = estadoCalendarioPonto.ano;
            const mesAtual = estadoCalendarioPonto.mes;
            const mesNome = new Date(anoAtual, mesAtual, 1).toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
            titulo.textContent = mesNome.toUpperCase();
            selectAno.value = String(anoAtual);

            const primeiroDia = new Date(anoAtual, mesAtual, 1);
            const ultimoDia = new Date(anoAtual, mesAtual + 1, 0);
            const inicioSemana = primeiroDia.getDay();
            const totalDias = ultimoDia.getDate();
            const totalCelulas = Math.ceil((inicioSemana + totalDias) / 7) * 7;
            const hoje = new Date();

            const mapaPorDia = new Map();
            (registros || []).forEach((registro) => {
                const inicio = parseDateValue(registro?.atribuicao_inicio || registro?.data_inicio || registro?.data_criacao);
                if (!inicio) return;
                const chave = getDataKeyLocal(inicio);
                if (!mapaPorDia.has(chave)) {
                    mapaPorDia.set(chave, []);
                }
                mapaPorDia.get(chave).push(registro);
            });

            const dias = [];
            for (let index = 0; index < totalCelulas; index += 1) {
                const numeroDia = index - inicioSemana + 1;
                const dataAtual = new Date(anoAtual, mesAtual, numeroDia);
                const chave = getDataKeyLocal(dataAtual);
                const registrosDia = mapaPorDia.get(chave) || [];
                const foraDoMes = dataAtual.getMonth() !== mesAtual;
                const eHoje = getDataKeyLocal(dataAtual) === getDataKeyLocal(hoje);
                const quantidade = registrosDia.length;

                let classes = 'calendario-dia flex flex-col items-center justify-start w-full p-2 text-left';
                if (foraDoMes) classes += ' calendario-dia--fora-mes';
                if (eHoje) classes += ' calendario-dia--hoje';
                if (quantidade > 0) classes += ' calendario-dia--ativo';

                const totalMinutosDia = registrosDia.reduce((soma, item) => soma + obterDuracaoEmMinutos(item), 0);
                const tooltip = quantidade > 0 ? `
                    <div class="calendario-tooltip text-left">
                        <p class="font-semibold text-white">${formatarDataCompleta(chave)}</p>
                        <p class="mt-1 text-[10px] text-gray-200">${quantidade} ${quantidade === 1 ? 'demanda realizada' : 'demandas realizadas'}</p>
                        ${registrosDia.slice(0, 3).map((registro) => `<p class="mt-1 text-[10px] text-gray-200">• ${escaparPonto(registro.titulo || 'Demanda')}</p>`).join('')}
                        <p class="mt-2 text-[10px] text-[#d7f7e2]">Tempo total: ${formatarDuracaoMinutos(totalMinutosDia)}</p>
                    </div>
                ` : '';

                dias.push(`
                    <div class="relative group">
                        <button type="button" class="${classes} ${quantidade > 0 ? 'cursor-pointer' : 'cursor-default'}" data-data="${chave}" ${quantidade > 0 ? '' : 'disabled'}>
                            <span class="text-sm font-semibold ${foraDoMes ? 'text-gray-400' : 'text-[var(--gray2)]'} ${eHoje ? 'text-[var(--dark-green)]' : ''}">${dataAtual.getDate()}</span>
                            ${quantidade > 0 ? `<span class="calendario-indicador mt-2">${quantidade > 9 ? '9+' : quantidade}</span>` : ''}
                        </button>
                        ${tooltip}
                    </div>
                `);
            }

            container.innerHTML = dias.join('');

            container.querySelectorAll('[data-data]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    const chave = botao.dataset.data;
                    const registrosDia = mapaPorDia.get(chave) || [];
                    if (!registrosDia.length) return;
                    abrirDetalhesDiaCalendario(chave, registrosDia);
                });
            });

            btnMesAnterior.onclick = () => {
                const mesAnterior = new Date(anoAtual, mesAtual - 1, 1);
                estadoCalendarioPonto = {
                    ano: mesAnterior.getFullYear(),
                    mes: mesAnterior.getMonth()
                };
                if (estadoCalendarioPonto.ano < 2026) {
                    estadoCalendarioPonto = { ano: 2026, mes: 0 };
                }
                renderizarCalendarioPonto(registros);
            };

            btnMesProximo.onclick = () => {
                const mesProximo = new Date(anoAtual, mesAtual + 1, 1);
                estadoCalendarioPonto = {
                    ano: mesProximo.getFullYear(),
                    mes: mesProximo.getMonth()
                };
                renderizarCalendarioPonto(registros);
            };

            selectAno.onchange = (event) => {
                const anoSelecionado = Number(event.target.value || 2026);
                estadoCalendarioPonto = { ...estadoCalendarioPonto, ano: anoSelecionado };
                renderizarCalendarioPonto(registros);
            };

            atualizarResumoCalendarioPonto(registros);
        }

        function abrirDetalhesDiaCalendario(chave, registrosDia) {
            const modal = document.getElementById('modalCalendarioDetalhes');
            const overlay = document.getElementById('divModalCalendarioDetalhes');
            if (!modal || !overlay) return;

            const lista = registrosDia
                .slice()
                .sort((a, b) => (parseDateValue(a.atribuicao_inicio || a.data_inicio || a.data_criacao) || new Date()) - (parseDateValue(b.atribuicao_inicio || b.data_inicio || b.data_criacao) || new Date()))
                .map((registro, indice) => {
                    const inicio = parseDateValue(registro.atribuicao_inicio || registro.data_inicio || registro.data_criacao);
                    const fim = parseDateValue(registro.atribuicao_fim || registro.data_finalizacao || registro.data_fim) || new Date();
                    const horas = obterDuracaoEmMinutos(registro);
                    return `
                        <div class="rounded-xl border border-gray-100 bg-[var(--suave-green)] p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-[var(--gray2)]">Demanda ${indice + 1}</p>
                                <span class="px-2 py-1 rounded-full text-[10px] font-semibold ${registro.status === 'finalizada' ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-[var(--dark-green)]'}">${registro.status === 'finalizada' ? 'Finalizada' : 'Em andamento'}</span>
                            </div>
                            <div class="mt-3 space-y-2 text-sm text-gray-600">
                                <p><span class="font-semibold text-[var(--gray2)]">Nome:</span> ${escaparPonto(registro.titulo || 'Sem título')}</p>
                                <p><span class="font-semibold text-[var(--gray2)]">Setor:</span> ${escaparPonto(registro.categoria || 'Não informado')}</p>
                                <p><span class="font-semibold text-[var(--gray2)]">Início:</span> ${inicio ? inicio.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : '-'}</p>
                                <p><span class="font-semibold text-[var(--gray2)]">Final:</span> ${fim ? fim.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : '-'}</p>
                                <p><span class="font-semibold text-[var(--gray2)]">Tempo total:</span> ${formatarDuracaoMinutos(horas)}</p>
                                <p><span class="font-semibold text-[var(--gray2)]">Status:</span> ${escaparPonto(registro.situacao === 'nao_concluida' ? 'Não concluída' : registro.status === 'finalizada' ? 'Finalizada' : 'Em andamento')}</p>
                            </div>
                        </div>
                    `;
                })
                .join('');

            modal.innerHTML = `
                <div class="flex items-center justify-between gap-3 mb-5">
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.14em] text-[var(--dark-green)] font-semibold">Registro de Ponto</p>
                        <h3 class="text-xl font-bold text-[var(--gray2)] mt-1">${formatarDataCompleta(chave)}</h3>
                    </div>
                    <button type="button" class="fecharModalCalendario w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Fechar detalhes"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                    <p class="text-sm font-semibold text-[var(--gray2)]">${registrosDia.length} ${registrosDia.length === 1 ? 'demanda realizada' : 'demandas realizadas'}</p>
                    ${lista}
                </div>
            `;

            overlay.classList.remove('hidden');
            modal.querySelector('.fecharModalCalendario')?.addEventListener('click', () => overlay.classList.add('hidden'));
        }

        function fecharDetalhesDiaCalendario() {
            const overlay = document.getElementById('divModalCalendarioDetalhes');
            if (overlay) overlay.classList.add('hidden');
        }

        function renderizarRegistroPonto(registros) {
            const ativos = (registros || []).filter((registro) => registro.status === 'em_andamento' && !registro.atribuicao_fim);
            const ativo = ativos[0];
            const ativoElemento = document.getElementById('pontoAtivo');
            const listaRegistros = Array.isArray(registros) ? registros : [];

            ativoElemento.innerHTML = ativo ? `<div class="bg-[var(--dark-green2)] text-white rounded-xl p-5 shadow-sm"><div class="flex items-center justify-between gap-3"><div><p class="text-xs uppercase tracking-[0.12em] text-gray-300">Demanda em andamento</p><h3 class="text-xl font-semibold mt-1">${escaparPonto(ativo.titulo)}</h3></div><span class="bg-white/15 px-3 py-1 rounded-full text-sm">Em andamento</span></div><div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5 text-sm"><p><span class="block text-gray-300">Responsável</span>${escaparPonto(ativo.projeto || 'Não informado')}</p><p><span class="block text-gray-300">Início</span>${formatarData(ativo.atribuicao_inicio)}</p><p><span class="block text-gray-300">Tempo decorrido</span><span class="tempoPontoAtivo">${formatarDuracaoPonto(ativo.atribuicao_inicio)}</span></p><p><span class="block text-gray-300">Setor</span>${escaparPonto(ativo.categoria || 'Não informado')}</p></div><button type="button" class="btnFinalizarPonto mt-5 bg-white text-[var(--dark-green2)] px-4 py-2.5 rounded-lg font-semibold hover:bg-gray-100 transition" data-id="${ativo.id}">Finalizar demanda</button></div>` : '';
            ativoElemento.classList.toggle('hidden', !ativo);

            if (estadoCalendarioPonto === null) {
                resetarEstadoCalendarioPonto();
            }
            montarSelectAnoCalendarioPonto();
            renderizarCalendarioPonto(listaRegistros);

            document.getElementById('pontoResumo').textContent = `${listaRegistros.length} registro(s)`;
            ativoElemento.querySelector('.btnFinalizarPonto')?.addEventListener('click', () => executarAcaoDemanda(ativo, 'registro_ponto'));
        }

        function obterParametroProjetoAluno() {
            return filtroProjetoAlunoAtual !== 'todos'
                ? '&id_projeto=' + encodeURIComponent(filtroProjetoAlunoAtual)
                : '';
        }

        async function carregarRegistroPonto() {
            atualizarRelogioPonto();
            try { const resposta = await fetch('../controllers/demanda_controller.php?action=meus_registros_ponto' + obterParametroProjetoAluno(), { method: 'POST' }); const resultado = await resposta.json(); if (!resultado.sucesso) throw new Error(resultado.erro); renderizarRegistroPonto(resultado.dados || []); }
            catch (erro) { document.getElementById('pontoMinhasDemandas').innerHTML = `<p class="text-sm text-red-600 bg-white rounded-xl p-5">${escaparPonto(erro.message || 'Não foi possível carregar os registros.')}</p>`; }
        }

        setInterval(atualizarRelogioPonto, 1000);
        atualizarRelogioPonto();

        // Nav - sair
        btnOptionSair.onmouseenter = () => {     
            iconNavSair.classList.add("icon-hover");
        };
        btnOptionSair.onmouseleave = () => {
            iconNavSair.classList.remove("icon-hover");
        };


        const btnDemandasSec = document.querySelectorAll(".btnDemandasSec");
        const demandasSecReparti = document.querySelectorAll(".demandasSecReparti");

        let abaHistoricoAluno = 'geral';

        btnDemandasSec.forEach((btn, index) => {
            btn.onclick = () => {
                abaHistoricoAluno = index === 1 ? 'minhas' : 'geral';
                btnDemandasSec.forEach((btn) => {
                    btn.classList.remove("btnDemandasSec_select");
                });

                btnDemandasSec[index].classList.add("btnDemandasSec_select");

                demandasSecReparti.forEach((secDemanda) => {
                    secDemanda.classList.add("hidden");
                });

                demandasSecReparti[index].classList.remove("hidden");

                if (abaHistoricoAluno === 'minhas') {
                    carregarMinhasDemandas();
                } else {
                    renderizarDemandas();
                }
            };
        });

        // Contadores
        let quantDisponiveis = 0;
        let quantAndamento = 0;
        let quantFinalizadas = 0;
        let concluidasUsuario = 0;
        let minhasDemandasAtivasIds = new Set();

        const quantDisponiveisViewAluno = document.querySelector(".quantDisponiveisViewAluno");
        const quantAndamentoViewAluno = document.querySelector(".quantAndamentoViewAluno");
        const quantFinalizadasViewAluno = document.querySelector(".quantFinalizadasViewAluno");

        const quantDisponiveisViewAdm = document.querySelector(".quantDisponiveisViewAdm");
        const quantAndamentoViewAdm = document.querySelector(".quantAndamentoViewAdm");
        const quantFinalizadasViewAdm = document.querySelector(".quantFinalizadasViewAdm");

        if (quantDisponiveisViewAluno) quantDisponiveisViewAluno.innerHTML = quantDisponiveis;
        if (quantAndamentoViewAluno) quantAndamentoViewAluno.innerHTML = quantAndamento;
        if (quantFinalizadasViewAluno) quantFinalizadasViewAluno.innerHTML = quantFinalizadas;

        if (quantDisponiveisViewAdm) quantDisponiveisViewAdm.innerHTML = quantDisponiveis;
        if (quantAndamentoViewAdm) quantAndamentoViewAdm.innerHTML = quantAndamento;
        if (quantFinalizadasViewAdm) quantFinalizadasViewAdm.innerHTML = quantFinalizadas;

        // Button NOVA Demanda

        const shadowDashBoard = document.querySelectorAll(".shadowDashBoard");
        const shadowSecDemandas = document.querySelectorAll(".shadowSecDemandas");
        const shadowRegistroPonto = document.getElementById("shadowRegistroPonto");
        const shadowDashBoardADM = document.getElementById("shadowDashBoardADM");

        const divModalCriar = document.getElementById("divModalCriar");
        const divModalDescri = document.getElementById("divModalDescri");
        const divModalInfs = document.querySelectorAll(".divModalInfs");
        const divModalInfsSecDemandas = document.querySelectorAll(".divModalInfsSecDemandas");
        const divModalConclusao = document.getElementById("divModalConclusao");
        const divModalConclusaoSecDemandas = document.getElementById("divModalConclusaoSecDemandas");
        const btnConclusaoSecDemandas = document.getElementById("btnConclusaoSecDemandas");

        btnConclusaoSecDemandas?.addEventListener("click", () => {
            divModalConclusaoSecDemandas.classList.add("hidden");
            shadowSecDemandas.forEach((shadow) => shadow.classList.add("hidden"));
            document.body.classList.remove("no-scroll");
        });
        const divModalAssumirAluno = document.getElementById("divModalAssumirAluno");

        function fecharModaisAoTrocarSecaoAluno() {
            const idsDeAlertas = new Set(['divModalAviso']);
            document.querySelectorAll('[id^="divModal"], .divModalInfs, .divModalInfsSecDemandas, .modal-overlay-animated').forEach((modal) => {
                if (idsDeAlertas.has(modal.id) || modal.dataset.preservarAoTrocar === 'true') return;
                modal.classList.add('hidden');
                if (modal.id === 'divModalAssumirAluno' || modal.classList.contains('modal-overlay-animated')) {
                    modal.innerHTML = '';
                }
            });
            document.querySelectorAll('#modalLeitorQrPonto, #divModalDemandaEncontrada, #modalCorteFoto, #modalConfirmacaoFoto, #modalJustificativaReabertura, #divModalCalendarioDetalhes').forEach((modal) => {
                modal.classList.add('hidden');
            });
            shadowDashBoard.forEach((shadow) => shadow.classList.add("hidden"));
            shadowSecDemandas.forEach((shadow) => shadow.classList.add("hidden"));
            shadowRegistroPonto?.classList.add("hidden");
            document.body.classList.remove("no-scroll");
            mainAlunos?.classList.remove("no-scroll", "h-screen");
        }
        
        const btnNovaDemanda = document.getElementById("btnNovaDemanda");
        const btnFecharModalCriar = document.getElementById("btnFecharModalCriar");
        const btnAddDescri = document.getElementById("btnAddDescri");
        const btnSalvarDescri = document.getElementById("btnSalvarDescri");

        function resetFormDemanda() {
            const form = document.getElementById("formDemanda");

            if (!form) return;

            form.reset();

            form.querySelectorAll("input, select, textarea").forEach(el => {
                if (el.tagName === "SELECT") {
                    el.selectedIndex = 0;
                } else {
                    el.value = "";
                }
            });

            const textarea = document.getElementById("descriDemandaTextArea");
            if (textarea) textarea.value = "";
        };

        if (btnNovaDemanda) btnNovaDemanda.onclick = () => {

            console.log("Botão nova demanda!");

            shadowDashBoardADM.classList.remove("hidden");
            divModalCriar.classList.remove("hidden");

            document.body.classList.add("no-scroll");
            mainAlunos.classList.remove("overflow-y-auto");

            resetFormDemanda();
        };

        if (btnFecharModalCriar) btnFecharModalCriar.onclick = () => {
            shadowDashBoardADM.classList.add("hidden");
            divModalCriar.classList.add("hidden");

            document.body.classList.remove("no-scroll");
            mainAlunos.classList.add("overflow-y-auto");
        };

        const descriDemandaTextArea = document.getElementById("descriDemandaTextArea");

        if (btnAddDescri) btnAddDescri.onclick = () => {
            divModalCriar.classList.add("hidden");
            divModalDescri.classList.remove("hidden");
        };

        if (btnSalvarDescri) btnSalvarDescri.onclick = () => {
            divModalDescri.classList.add("hidden");
            divModalCriar.classList.remove("hidden");

            const valorDescriDemanda = descriDemandaTextArea.value.trim();

            if(valorDescriDemanda) {
                document.getElementById("iconBtnAddDescri").className = "fa-solid fa-pen";
                document.getElementById("textBtnAddDescri").textContent = "Editar Descrição";
            }else{
                document.getElementById("iconBtnAddDescri").className = "fa-solid fa-align-left";
                document.getElementById("textBtnAddDescri").textContent = "Adicionar Descrição";
            }

        };
        

        const btnCriarDemanda = document.getElementById("btnCriarDemanda");
        const btnConclusao = document.getElementById("btnConclusao");
        const msgNenhumaDemandaDashboardAluno = document.getElementById("msgNenhumaDemandaDashboardAluno");

        const divDemandasDisponiveis = document.querySelectorAll(".divDemandasDisponiveis");
        const divDemandasAndamento = document.querySelectorAll(".divDemandasAndamento");
        const divGeralDemandas =  document.querySelectorAll(".geralDemandas");
        const filtroTituloDemandaAluno = document.getElementById("filtroTituloDemandaAluno");
        const filtroPrioridadeDemandaAluno = document.getElementById("filtroPrioridadeDemandaAluno");
        const filtroOrientadorDemandaAluno = document.getElementById("filtroOrientadorDemandaAluno");
        const filtroStatusDemandaAluno = document.getElementById("filtroStatusDemandaAluno");
        const containerFiltroProjetoAluno = document.getElementById("containerFiltroProjetoAluno");
        const filtroProjetoAluno = document.getElementById("filtroProjetoAluno");
        let filtroProjetoAlunoAtual = localStorage.getItem('filtroProjetoAluno') || 'todos';

        function atualizarBadgeFiltroAluno() {
            const badge = document.getElementById('filtroActiveBadgeAluno');
            const textoFiltro = document.getElementById('filtroActiveTextAluno');
            if (!badge || !textoFiltro) return;

            if (filtroProjetoAlunoAtual === 'todos') {
                badge.classList.add('hidden');
                return;
            }

            badge.classList.remove('hidden');
            textoFiltro.textContent = 'Filtro: ' + (filtroProjetoAluno.options[filtroProjetoAluno.selectedIndex]?.text || 'Projeto');
        }

        if (filtroProjetoAluno) {
            if (!Array.from(filtroProjetoAluno.options).some((option) => option.value === filtroProjetoAlunoAtual)) {
                filtroProjetoAlunoAtual = 'todos';
            }
            filtroProjetoAluno.value = filtroProjetoAlunoAtual;
            containerFiltroProjetoAluno?.classList.remove('hidden');
            atualizarBadgeFiltroAluno();
            filtroProjetoAluno.addEventListener('change', () => {
                filtroProjetoAlunoAtual = filtroProjetoAluno.value || 'todos';
                localStorage.setItem('filtroProjetoAluno', filtroProjetoAlunoAtual);
                atualizarBadgeFiltroAluno();
                renderizarDemandas();
                carregarMinhasDemandas();
                carregarMetricasUsuario();
                carregarRegistroPonto();
                carregarGraficosAluno(true);
                window.atualizarRankingPontuacao?.();
                const nomeProjeto = filtroProjetoAlunoAtual === 'todos'
                    ? 'Todas as demandas'
                    : filtroProjetoAluno.options[filtroProjetoAluno.selectedIndex]?.text || 'Projeto';
                mostrarAvisoPonto(filtroProjetoAlunoAtual === 'todos'
                    ? 'Filtro global removido.'
                    : `Filtro global aplicado: ${nomeProjeto}`);
            });
        }

        document.getElementById('btnLimparFiltroAluno')?.addEventListener('click', () => {
            if (!filtroProjetoAluno) return;
            filtroProjetoAluno.value = 'todos';
            filtroProjetoAluno.dispatchEvent(new Event('change'));
        });

        document.getElementById('btnFiltroProjetoAluno')?.addEventListener('click', () => {
            const painel = document.getElementById('painelFiltroProjetoAluno');
            const aberto = !painel?.classList.contains('hidden');
            painel?.classList.toggle('hidden', aberto);
            document.getElementById('btnFiltroProjetoAluno')?.setAttribute('aria-expanded', String(!aberto));
            document.querySelector('#btnFiltroProjetoAluno i')?.classList.toggle('rotate-180', !aberto);
        });

        function obterDemandasFiltradasAluno(listaDemandas = demandas) {
            const titulo = filtroTituloDemandaAluno?.value.trim().toLowerCase() || "";
            const prioridade = filtroPrioridadeDemandaAluno?.value || "";
            const orientador = filtroOrientadorDemandaAluno?.value || "";
            const status = filtroStatusDemandaAluno?.value || "";

            return listaDemandas.filter((demanda) => {
                const tituloNormalizado = String(demanda.titulo || "").toLowerCase();
                const prioridadeNormalizada = String(demanda.prioridade || "").toLowerCase();
                const statusNormalizado = String(demanda.status || "").trim().toLowerCase().normalize('NFD').replace(/[̀-\u036f]/g, '').replace(/\s+/g, '_').replace('disponivel', 'aberta');
                const situacaoNormalizada = String(demanda.resultado || demanda.situacao || "").toLowerCase().normalize('NFD').replace(/[̀-\u036f]/g, '').replace(/\s+/g, '_');
                const statusFinalizada = statusNormalizado === 'finalizada';
                const correspondeStatus = !status
                    ? statusFinalizada
                    : (status === 'finalizada_concluida' && statusFinalizada && situacaoNormalizada === 'concluida')
                    || (status === 'finalizada_nao_concluida' && statusFinalizada && situacaoNormalizada === 'nao_concluida');

                return (!titulo || tituloNormalizado.includes(titulo))
                    && (!prioridade || prioridadeNormalizada === prioridade)
                    && (!orientador || String(demanda.orientador_id) === orientador)
                    && (filtroProjetoAlunoAtual === 'todos' || String(demanda.id_projeto) === filtroProjetoAlunoAtual)
                    && correspondeStatus;
            });
        }

        [filtroTituloDemandaAluno, filtroPrioridadeDemandaAluno, filtroOrientadorDemandaAluno, filtroStatusDemandaAluno]
            .filter(Boolean)
            .forEach((filtro) => {
                const atualizarHistorico = () => {
                    if (abaHistoricoAluno === 'minhas') {
                        carregarMinhasDemandas();
                    } else {
                        renderizarDemandas();
                    }
                };
                filtro.addEventListener("input", atualizarHistorico);
                filtro.addEventListener("change", atualizarHistorico);
            });

        // Inputs
        const tituloDemandaInput = document.getElementById("tituloDemandaInput");
        const prioridadeDemandaSelect = document.getElementById("prioridadeDemandaSelect");
        const orientadorDemandaSelect = document.getElementById("orientadorDemandaSelect");
        const categoriaDemandaSelect = document.getElementById("categoriaDemandaSelect");
        const prazoDemanda = document.getElementById("prazoDemanda");

        // Radios
        const divRadio = document.getElementById("divRadio");

        // Labels
        const prioridadeLabel = {
            alta: "Alta",
            media: "Média",
            baixa: "Baixa"
        };
        const categoriaLabel = {
            hardware: "Hardware",
            rede: "Rede",
            software_sistemas: "Software/Sistemas",
            emprestimo_equipamentos: "Empréstimo Equipamentos",
            suporte_operacional: "Suporte Operacional"
        }
        const statusLabel = {
            aberta: "Em aberto",
            em_andamento: "Em andamento",
            finalizada: "Finalizada"
        }

        function criarDemanda(titulo, prioridade, orientador, categoria, prazo, descricao, status, projeto, dataCriacao = null) {
            return {
                titulo: titulo,
                prioridade: prioridade.toLowerCase(),
                orientador: orientador,
                categoria: categoria,
                prazo: prazo,
                data_criacao: dataCriacao ? formatarData(dataCriacao) : formatarData(new Date()),
                descricao: descricao,
                status: status,
                projeto: projeto
            };
        };

        function getCorPrioridade(prioridade) {
            if(prioridade === "alta") return "text-[var(--red1)]";
            if(prioridade === "media") return "text-[var(--accent-yellow)]";
            if(prioridade === "baixa") return "text-[]";
        };

        function getCorPrioridadeBg(prioridade) {
            if(prioridade === "alta") return "bg-[var(--red-transparent)]";
            if(prioridade === "media") return "bg-[var(--yellow-transparent)]";
            if(prioridade === "baixa") return "bg-[var(--blue-transparent)]";
        };


        function getPrimeiroNome(nomeCompleto) {
            if (!nomeCompleto) return "";
            if (nomeCompleto === "Francisco Otávio de Menezes Filho") {
                return nomeCompleto.split(" ")[1];
            }else{
                return nomeCompleto.split(" ")[0];
            }
        }

        function formatarData(data) {
            if (!data) return "";

            const valor = String(data).trim();
            const dataBrasileira = valor.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
            if (dataBrasileira) {
                const [, dia, mes, ano] = dataBrasileira;
                const d = new Date(Number(ano), Number(mes) - 1, Number(dia));
                return d.toLocaleDateString("pt-BR", {
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric"
                });
            }

            if (/^\d{4}-\d{2}-\d{2}$/.test(valor)) {
                const [ano, mes, dia] = valor.split('-').map(Number);
                const d = new Date(ano, mes - 1, dia);
                return d.toLocaleDateString("pt-BR", {
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric"
                });
            }

            const dataNormalizada = valor.includes(' ') ? valor.replace(' ', 'T') : valor;
            const d = new Date(dataNormalizada);

            if (Number.isNaN(d.getTime())) {
                return valor;
            }

            return d.toLocaleDateString("pt-BR", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric"
            });
        }

        function getNomeOrientador(demanda) {
            const nome = demanda?.orientador_nome || demanda?.orientador || "";
            if (!nome || nome === "N/A" || nome === "null") return "Não Informado";
            return nome;
        }

        function getSituacaoDemanda(demanda) {
            const situacao = String(demanda?.situacao || 'pendente').trim().toLowerCase();
            const situacoes = {
                pendente: 'Pendente',
                concluida: 'Concluída',
                nao_concluida: 'Não concluída'
            };

            return situacoes[situacao] || 'Pendente';
        }

        function getClasseStatus(status) {
            const statusNormalizado = String(status || '').trim().toLowerCase();

            if (statusNormalizado === 'aberta' || statusNormalizado === 'disponível') {
                return 'text-[var(--primary-dark-green)] font-semibold';
            }

            if (statusNormalizado === 'em_andamento' || statusNormalizado === 'em andamento') {
                return 'text-[var(--accent-yellow)] font-semibold';
            }

            return 'text-gray-400';
        }

        function getStatus(status, dataFinalizacao = null) {
            if(status === "aberta") {
                console.log("Demanda - Em aberto");
                return `<div class="flex items-center space-x-2 text-[var(--dark-green)] text-sm lg:text-[0.8vw] w-full">
                    <i class="fa-solid fa-hand"></i>
                    <p>Em aberto</p>
                </div>`;
            };

            if(status === "em_andamento") {
                console.log("Demanda - Em andamento");
                return `<div class="flex items-center space-x-2 text-[var(--accent-yellow)] text-sm lg:text-[0.8vw] w-full">
                    <i class="fa-solid fa-spinner"></i>
                    <p>Em andamento</p>
                </div>`;
            };

            if(status === "finalizada") {
                const dataFormatada = dataFinalizacao ? formatarData(dataFinalizacao) : "";
                return `<div class="flex items-center space-x-2 text-gray-400 text-sm lg:text-[0.8vw] w-full">
                    <i class="fa-solid fa-circle-check"></i>
                    <p>${dataFormatada ? `Finalizada em ${dataFormatada}` : "Finalizada"}</p>
                </div>`;
            };

            return `<div class="flex items-center space-x-2 text-gray-400 text-sm lg:text-[0.8vw] w-full">
                <i class="fa-solid fa-circle-check"></i>
                <p>${status === "aberta" ? "Em aberto" : (statusLabel[status] || "Finalizada")}</p>
            </div>`;
        };

        function demandaFinalizada(demanda) {
            return String(demanda?.status || '').trim().toLowerCase() === 'finalizada';
        }

        // Esqueleto Modal Informações
        function criarModalInfs(demanda) {
            const modalInfs = document.createElement("div");

            const corPrioridade = getCorPrioridadeBg(demanda.prioridade);
            const descriDemanda = demanda.descricao?.trim() || "Sem descrição.";
            const nomeOrientador = getPrimeiroNome(getNomeOrientador(demanda));
            const criacaoFormatada = formatarData(demanda.data_criacao);
            const prazoFormatado = formatarData(demanda.prazo);
            const responsavelNome = String(demanda.responsavel_nome || '').trim();
            const solucao = String(demanda.solucao || '').trim();
            const justificativa = String(demanda.justificativa_nao_conclusao || '').trim();
            const responsavelMarkup = responsavelNome && responsavelNome !== 'N/A' && responsavelNome !== 'null'
                ? `
                            <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                <i class="fa-solid fa-user-check text-[var(--gray2)] w-4"></i>
                                <p class="text-[var(--gray2)]">Responsável:</p>
                                <span class="responsavelSpan">${responsavelNome}</span>
                            </li>`
                : '';
            const solucaoMarkup = demanda.situacao === 'concluida' && solucao
                ? `<li class="text-sm flex items-start gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full"><i class="fa-solid fa-check-circle text-[var(--gray2)] w-4"></i><p class="text-[var(--gray2)]">Solução:</p><span class="text-[var(--gray1)]">${solucao}</span></li>`
                : '';
            const justificativaMarkup = demanda.situacao === 'nao_concluida'
                ? `<li class="text-sm flex items-start gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full"><i class="fa-solid fa-circle-exclamation text-[var(--gray2)] w-4"></i><p class="text-[var(--gray2)]">Justificativa:</p><span class="text-[var(--gray1)]">${justificativa || 'Não informada.'}</span></li>`
                : '';

            modalInfs.innerHTML = `
                <div class="w-screen lg:w-full">
                    <div id="modalInfs" class="bg-[var(--white)] w-[90%] lg:w-[22vw] pb-8 z-[200] rounded-lg flex flex-col gap-3 items-center overflow-hidden mx-auto justify-center shadow-2xl">
                        <div class="w-full flex items-center justify-between to-[var(--dark-green)] py-3 px-5">
                            <div class="flex items-center space-x-2 text-white">
                                <i class="fa-solid fa-globe text-gray-500"></i>
                                <p class="font-semibold"><span class="text-[var(--dark-green)]">Informações</span> <span class="text-[var(--accent-yellow)]">Gerais</span></p>
                            </div>
                            <button class="btnFecharModalInfs">
                                <i class="fa-solid fa-arrow-right text-xl text-[var(--dark-green)] hover:text-[var(--dark-green)] hover:translate-x-1 transition ease-in-out duration-300"></i>
                            </button>
                        </div>
                        <div class="grid lg:grid-cols-1 px-5 gap-10 w-full">
                            <ul class="gap-3 grid lg:grid-cols-1 w-full">
                                <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                    <i class="fa-solid fa-clipboard-list text-[var(--gray2)] w-4"></i>
                                    <p class="text-[var(--gray2)]">Título:</p>
                                    <span class="tituloDemanda tituloEdicao truncate">${demanda.titulo}</span>
                                </li>
                                <li class="text-sm flex items-center gap-2 ${corPrioridade} p-2.5 rounded-lg w-full">
                                    <i class="fa-solid fa-triangle-exclamation w-4"></i>
                                    <p class="text-[var(--gray2)]">Prioridade:</p>
                                    <span class="prioridadeSpan prioridadeEdicao font-semibold">${prioridadeLabel[demanda.prioridade]}</span>
                                </li>
                                <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                    <i class="fa-solid fa-user-tie text-[var(--gray2)] w-4"></i>
                                    <p class="text-[var(--gray2)]">Orientador:</p>
                                    <span class="orientadorSpan">${nomeOrientador || "Não Informado"}</span>
                                </li>
                                <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                    <i class="fa-solid fa-sitemap text-[var(--gray2)] w-4"></i>
                                    <p class="text-[var(--gray2)]">Categoria:</p>
                                    <span class="categoriaSpan categoriaEdicao">${categoriaLabel[demanda.categoria] || demanda.categoria || "Não informada"}</span>
                                </li>
                                <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                    <i class="fa-solid fa-circle-info text-[var(--gray2)] w-4"></i>
                                    <p class="text-[var(--gray2)]">Situação:</p>
                                    <span class="situacaoSpan">${getSituacaoDemanda(demanda)}</span>
                                </li>
                                <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                    <i class="fa-solid fa-hammer text-[var(--gray2)] w-4"></i>
                                    <p class="text-[var(--gray2)]">Criação:</p>
                                    <span class="criacaoSpan">${criacaoFormatada}</span>
                                </li>
                                <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                    <i class="fa-solid fa-calendar-days text-[var(--gray2)] w-4"></i>
                                    <p class="text-[var(--gray2)]">Prazo:</p>
                                    <span class="prazoSpan prazoEdicao">${prazoFormatado}</span>
                                </li>
                                ${responsavelMarkup}
                                ${solucaoMarkup}
                                ${justificativaMarkup}
                            </ul>
                            <div class="w-full border-t-2 border-[var(--dark-green)] rounded-xl bg-[var(--suave-green)] overflow-hidden">
                                <p class="descriDemandaSpan descricaoEdicao py-3 px-3 text-sm leading-relaxed">${descriDemanda}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            return modalInfs;
        }


        function obterEstadoRealDemanda(demanda) {
            if (!demanda) return 'aberta';
            if (demandaFinalizada(demanda)) return 'finalizada';

            const totalAssumidos = Number(demanda.total_assumidos || 0);
            const quantidadeNecessaria = Number(demanda.quantidade_alunos || 1);

            return totalAssumidos >= quantidadeNecessaria ? 'em_andamento' : 'aberta';
        }

        function usuarioEmEsperaNaDemanda(demanda) {
            if (!demanda || !demanda.id) return false;

            const idDemanda = Number(demanda.id);
            const totalAssumidos = Number(demanda.total_assumidos || 0);
            const quantidadeNecessaria = Number(demanda.quantidade_alunos || 1);

            return Number.isFinite(idDemanda)
                && minhasDemandasAtivasIds.has(idDemanda)
                && totalAssumidos < quantidadeNecessaria;
        }

        function aplicarEstadoBotaoAguardando(btnAssumir, card, mensagem = 'Aguardando') {
            if (!btnAssumir) return;

            btnAssumir.disabled = true;
            btnAssumir.classList.remove('bg-[var(--dark-green)]', 'hover:bg-[var(--dark-green2)]', 'border', 'border-[var(--dark-green)]', 'border-transparent');
            btnAssumir.classList.add('bg-gray-400', 'opacity-75', 'cursor-not-allowed', 'border-0');
            btnAssumir.classList.remove('hidden');
            btnAssumir.title = 'Aguardando mais pessoas para iniciar a demanda';
            btnAssumir.innerHTML = card.classList.contains('dashboard-card')
                ? '<i class="fa-solid fa-clock"></i>'
                : `<p>${mensagem}</p><i class="fa-solid fa-clock"></i>`;
        }

        // ==================== FUNÇÃO PARA MOSTRAR BOTÕES CORRETOS ====================
        function configurarBotoesCard(card, usuario, status, demanda = null) {
            const btnAssumir = card.querySelector(".btnAssumir");
            const btnEditar  = card.querySelector(".btnEditar");

            if (!btnAssumir) return;

            if (usuario === "administrador") {
                btnAssumir.classList.add("hidden");
                btnEditar?.classList.remove("hidden");
                return;
            }

            btnEditar?.classList.add("hidden");

            if (demandaFinalizada(demanda || { status })) {
                btnAssumir.classList.add("hidden");
                btnAssumir.disabled = true;
                btnAssumir.title = 'Demanda finalizada';
                return;
            }

            const estadoReal = obterEstadoRealDemanda(demanda || { status, total_assumidos: 0, quantidade_alunos: 1 });
            const aguardandoInicio = usuarioEmEsperaNaDemanda(demanda);

            if (estadoReal === 'em_andamento' && !demanda?.usuario_atribuido) {
                btnAssumir.classList.add("hidden");
                btnAssumir.disabled = true;
                btnAssumir.title = 'Você não está atribuído a esta demanda';
                return;
            }

            if (estadoReal === 'em_andamento') {
                btnAssumir.classList.remove("hidden");
                btnAssumir.disabled = false;
                btnAssumir.classList.remove("bg-gray-400", "opacity-75", "cursor-not-allowed", "border", "border-[var(--dark-green)]", "border-transparent");
                btnAssumir.classList.remove("bg-[var(--dark-green)]");
                btnAssumir.classList.add("bg-[var(--accent-yellow)]", "border-0");
                btnAssumir.classList.remove("hover:bg-[var(--dark-green2)]");
                btnAssumir.classList.add("hover:bg-[var(--dark-accent-yellow2)]");

                btnAssumir.innerHTML = card.classList.contains("dashboard-card")
                    ? '<i class="fa-solid fa-check"></i>'
                    : '<p>Finalizar</p><i class="fa-solid fa-check"></i>';
                return;
            }

            if (aguardandoInicio) {
                aplicarEstadoBotaoAguardando(btnAssumir, card, 'Aguardando');
                return;
            }

            btnAssumir.classList.remove("hidden");
            btnAssumir.disabled = false;
            btnAssumir.classList.remove("bg-gray-400", "opacity-75", "cursor-not-allowed", "border", "border-[var(--dark-green)]", "border-transparent");
            btnAssumir.classList.add("bg-[var(--dark-green)]", "hover:bg-[var(--dark-green2)]", "border-0");

            btnAssumir.innerHTML = card.classList.contains("dashboard-card")
                ? '<i class="fa-solid fa-hand"></i>'
                : '<p>Assumir</p><i class="fa-solid fa-hand"></i>';
        }; 

        // Esqueleto Demanda (Dashboard) - CORRIGIDO
        function criarCardDemandaDashboard(demanda) {
            const cardDemandaDashboard = document.createElement("div");
            cardDemandaDashboard.classList.add("dashboard-card");
            const inicioFormatado = formatarData(demanda.data_criacao);
            const prazoFormatado = formatarData(demanda.prazo);
            const nomeOrientador = getPrimeiroNome(getNomeOrientador(demanda));
            const classePrioridadeCard = `card-prioridade-${demanda.prioridade}`;
            const classeLinhaPrioridade = `linha-prioridade-${demanda.prioridade}`;

            cardDemandaDashboard.innerHTML = `
                <div class="${classePrioridadeCard} border-l-4 w-full rounded-xl px-5 py-4 flex flex-col gap-3 hover:-translate-y-0.5 hover:shadow-md transition ease-in-out duration-300">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 text-[var(--gray2)] min-w-0">
                            <i class="fa-solid fa-clipboard-list text-sm"></i>
                            <p class="tituloDemanda text-sm lg:text-[0.9vw] font-semibold">${demanda.titulo}</p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button class="btnVer ${classeLinhaPrioridade} w-10 h-10 flex items-center justify-center border border-[#121212]/10 rounded-md hover:brightness-95 transition" aria-label="Ver informações da demanda" title="Ver informações">
                                <i class="fa-solid fa-eye text-[#111827]"></i>
                            </button>
                            <button class="btnAssumir w-10 h-10 hidden flex items-center justify-center text-white rounded-md bg-[var(--dark-green)] hover:bg-[var(--dark-green2)] transition border-0" aria-label="Assumir demanda" title="Assumir demanda">
                                <i class="fa-solid fa-hand"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-2.5 text-sm">
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-triangle-exclamation pr-1"></i> Prioridade: <span class="prioridadeSpan text-[var(--gray1)] font-semibold">${prioridadeLabel[demanda.prioridade]}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-calendar-check pr-1"></i> Prazo: <span class="prazoSpan">${prazoFormatado}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-circle-info pr-1"></i> Status: <span class="font-semibold">${demanda.status === "aberta" ? "Em aberto" : (statusLabel[demanda.status] || demanda.status)}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-calendar-days pr-1"></i> Início: <span class="criacaoSpan">${inicioFormatado}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md col-span-2"><i class="fa-solid fa-user-tie pr-1"></i> Orientador: <span class="orientadorSpan">${nomeOrientador || "Não Informado"}</span></p>
                    </div>
                    <div class="flex justify-start items-center space-x-3 pt-1.5 w-full"></div>
                </div>
            `;

            configurarBotoesCard(cardDemandaDashboard, usuario, demanda.status, demanda);

            return cardDemandaDashboard;
        }

        // Esqueleto Demanda (Seção Demandas) - CORRIGIDO
        function criarCardDemandaDemandas(demanda) {
            const cardDemandaDemandas = document.createElement("div");
            const corPrioridade = getCorPrioridade(demanda.prioridade);
            const prazoFormatado = formatarData(demanda.prazo);
            const inicioFormatado = formatarData(demanda.data_criacao);
            const nomeOrientador = getPrimeiroNome(getNomeOrientador(demanda));
            const statusNormalizado = String(demanda.status || '').trim().toLowerCase();
            const statusTexto = statusNormalizado === 'em_andamento' ? 'Em andamento' : statusNormalizado === 'finalizada' ? 'Finalizada' : 'Em aberto';
            const statusIcone = statusNormalizado === 'em_andamento' ? 'fa-spinner' : statusNormalizado === 'finalizada' ? 'fa-circle-check' : 'fa-hand';
            const classePrioridadeCard = `card-prioridade-${demanda.prioridade}`;
            const classeLinhaPrioridade = `linha-prioridade-${demanda.prioridade}`;

            cardDemandaDemandas.innerHTML = `
                <div class="h-full min-h-[300px] w-full ${classePrioridadeCard} rounded-xl p-5 shadow-sm hover:-translate-y-0.5 hover:shadow-md transition ease-in-out duration-300 border-l-4 flex flex-col">
                    <div class="demanda-card-layout flex items-start gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <i class="fa-solid fa-clipboard-list shrink-0"></i>
                            <p class="tituloDemanda font-semibold leading-snug line-clamp-2">${demanda.titulo}</p>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-2.5 text-sm">
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-triangle-exclamation pr-1"></i> Prioridade: <span class="prioridadeSpan text-[var(--gray1)] font-semibold">${prioridadeLabel[demanda.prioridade]}</span></p>
                        <p class="statusDemanda text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid ${statusIcone} pr-1"></i> Status: <span class="text-[var(--gray1)] font-semibold">${statusTexto}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-calendar-check pr-1"></i> Prazo: <span class="prazoSpan">${prazoFormatado}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-calendar-days pr-1"></i> Início: <span class="criacaoSpan">${inicioFormatado}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-user-tie pr-1"></i> Orientador: <span class="orientadorSpan">${nomeOrientador || "Não Informado"}</span></p>
                    </div>
                    <div class="flex items-center gap-2 pt-5 mt-auto w-full">
                        <button class="${classeLinhaPrioridade} font-semibold h-10 flex-1 justify-center rounded-lg text-[#111827] text-sm flex items-center gap-2 hover:brightness-95 transition btnVer"><p>Ver</p><i class="fa-solid fa-eye"></i></button>
                        <button class="bg-[var(--dark-green)] text-white font-semibold h-10 flex-1 justify-center rounded-lg text-sm flex items-center gap-2 hover:bg-[var(--dark-green2)] transition border-0 btnAssumir hidden"><p>Assumir</p><i class="fa-solid fa-hand"></i></button>
                        <button class="bg-[var(--dark-green)] text-white font-semibold h-10 flex-1 justify-center rounded-lg text-sm flex items-center gap-2 hover:bg-[var(--dark-green2)] transition border-0 btnEditar hidden"><p>Editar</p><i class="fa-solid fa-pen"></i></button>
                    </div>
                </div>
            `;

            configurarBotoesCard(cardDemandaDemandas, usuario, demanda.status, demanda);

            return cardDemandaDemandas;
        }
        function viewDemandaUserProject(usuario, projeto) {
            return true;
        };

        let demandas = [];
        let requisicaoDemandasAluno = 0;

        async function buscarJsonAluno(url, options = {}, tentativas = 3) {
            let ultimoErro;
            for (let tentativa = 0; tentativa < tentativas; tentativa += 1) {
                try {
                    const response = await fetch(url, { cache: 'no-store', ...options });
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return await response.json();
                } catch (error) {
                    ultimoErro = error;
                    if (tentativa < tentativas - 1) {
                        await new Promise((resolve) => setTimeout(resolve, 350 * (tentativa + 1)));
                    }
                }
            }
            throw ultimoErro;
        }

        async function carregarMetricasUsuario() {
            try {
                const response = await fetch('../controllers/demanda_controller.php?action=minhas_metricas' + obterParametroProjetoAluno(), { method: 'POST' });
                const data = await response.json();

                if (data.sucesso) {
                    concluidasUsuario = Number(data.dados?.concluidas || 0);
                    if (quantFinalizadasViewAluno) quantFinalizadasViewAluno.innerHTML = concluidasUsuario;
                    /*
                    // Indicador de rendimento temporariamente desativado.
                    // const rendimento = Number(data.dados?.rendimento || 0);
                    // const rendimentoElemento = document.getElementById('rendimentoUsuario');
                    // if (rendimentoElemento) {
                    //     rendimentoElemento.textContent = `${rendimento.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%`;
                    // }
                    */
                }
            } catch (error) {
                console.error('Erro ao carregar métricas do usuário:', error);
            }
        }

        function mostrarModalConclusao(mensagem) {
            if (!divModalConclusao) return;

            const mensagemModal = divModalConclusao.querySelector('p');
            if (mensagemModal) mensagemModal.textContent = mensagem;

            divModalConclusao.classList.remove('hidden');
            shadowDashBoard.forEach((shadow) => shadow.classList.remove('hidden'));
            document.body.classList.add('no-scroll');
            mainAlunos?.classList.add('no-scroll');
        }

        function fecharModalConclusao() {
            divModalConclusao?.classList.add('hidden');
            shadowDashBoard.forEach((shadow) => shadow.classList.add('hidden'));
            document.body.classList.remove('no-scroll');
            mainAlunos?.classList.remove('no-scroll');
        }

        function confirmarAssumirDemanda(secao = "dashboard") {
            divModalAssumirAluno.innerHTML = `
                    <div id="modalAssumirAluno"class="bg-[var(--white)] w-[90%] sm:w-[420px] pb-8 z-[200] rounded-lg p-5 flex flex-col gap-5 justify-center mx-auto items-center">
                        <i class="fa-regular fa-circle-question text-4xl text-[var(--dark-green)]"></i>
                        <p class="text-[var(--gray2)] text-center">Deseja assumir esta demanda?</p>
                        <div class="flex items-center w-full space-x-2">
                            <button type="button" class="btnCancelarAssumirAluno py-3 text-[#121212] w-full rounded-lg font-semibold text-sm flex items-center justify-center bg-[var(--white-gray)] hover:bg-gray-200 transition">Cancelar</button>
                            <button type="button" class="btnConfirmarAssumirAluno py-3 text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center bg-[var(--dark-green)] hover:bg-[var(--dark-green2)] transition"><i class="fa-solid fa-hand"></i><p>Assumir</p></button>
                        </div>
                    </div>`;

            divModalAssumirAluno.classList.remove("hidden");
            const sombras = secao === "demandas" ? shadowSecDemandas : secao === "registro_ponto" ? [shadowRegistroPonto] : shadowDashBoard;
            sombras.forEach((shadow) => shadow.classList.remove("hidden"));
            document.body.classList.add("no-scroll");

            return new Promise((resolve) => {
                const fechar = (resultado) => {
                    divModalAssumirAluno.classList.add("hidden");
                    divModalAssumirAluno.innerHTML = "";
                    sombras.forEach((shadow) => shadow.classList.add("hidden"));
                    document.body.classList.remove("no-scroll");
                    resolve(resultado);
                };

                divModalAssumirAluno.querySelector(".btnCancelarAssumirAluno").onclick = () => fechar(false);
                divModalAssumirAluno.querySelector(".btnConfirmarAssumirAluno").onclick = () => fechar(true);
            });
        }

        function escolherResultadoDemanda(secao = "dashboard") {
            divModalAssumirAluno.innerHTML = `
                <div id="modalConcluirAluno" class="bg-[var(--white)] w-[90%] sm:w-[480px] max-h-[90vh] overflow-y-auto pb-8 z-[200] rounded-lg p-5 flex flex-col gap-5">
                    <i class="fa-solid fa-circle-check self-center text-4xl text-[var(--dark-green)]"></i>
                    <div>
                        <p class="text-[var(--gray2)] font-semibold">Finalizar demanda</p>
                        <p class="text-sm text-gray-500 mt-1">Informe como ficou a situação desta demanda.</p>
                    </div>
                    <div class="w-full space-y-3">
                        <label class="resultado-opcao flex items-center gap-3 bg-green-50 border border-green-200 rounded-lg p-3 cursor-pointer">
                            <input type="radio" name="resultadoConclusaoAluno" value="concluida" checked>
                            <span class="text-sm text-green-800 font-semibold">Finalizada e concluída</span>
                        </label>
                        <label class="resultado-opcao flex items-center gap-3 bg-red-50 border border-red-200 rounded-lg p-3 cursor-pointer">
                            <input type="radio" class="radio-nao-concluida" name="resultadoConclusaoAluno" value="nao_concluida">
                            <span class="text-sm text-red-800 font-semibold">Finalizada e não concluída</span>
                        </label>
                    </div>
                    <div class="w-full">
                        <label for="textoResultadoAluno" class="block text-sm font-semibold text-[var(--gray2)] mb-2" id="labelTextoResultadoAluno">Solução (opcional)</label>
                        <textarea id="textoResultadoAluno" rows="4" class="w-full border border-gray-200 rounded-lg p-3 text-sm outline-none focus:border-[var(--dark-green)] resize-y" placeholder="Descreva a solução aplicada, se desejar."></textarea>
                        <p id="avisoJustificativaAluno" class="hidden text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 mt-2">Informe obrigatoriamente o motivo pelo qual a demanda não foi concluída. Sua justificativa será analisada pelo responsável pela demanda.</p>
                        <p id="erroTextoResultadoAluno" class="hidden text-sm text-red-600 mt-2">A justificativa é obrigatória para uma demanda não concluída.</p>
                    </div>
                    <div class="flex items-center w-full space-x-2">
                        <button type="button" class="btnCancelarConcluirAluno py-3 text-[#121212] w-full rounded-lg font-semibold text-sm flex items-center justify-center bg-[var(--white-gray)] hover:bg-gray-200 transition">Cancelar</button>
                        <button type="button" class="btnConfirmarConcluirAluno py-3 text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center bg-[var(--dark-green)] hover:bg-[var(--dark-green2)] transition"><i class="fa-solid fa-circle-check"></i><p>Finalizar</p></button>
                    </div>
                </div>`;

            divModalAssumirAluno.classList.remove("hidden");
            const sombras = secao === "demandas" ? shadowSecDemandas : secao === "registro_ponto" ? [shadowRegistroPonto] : shadowDashBoard;
            sombras.forEach((shadow) => shadow.classList.remove("hidden"));
            document.body.classList.add("no-scroll");

            return new Promise((resolve) => {
                const fechar = (resultado) => {
                    divModalAssumirAluno.classList.add("hidden");
                    divModalAssumirAluno.innerHTML = "";
                    sombras.forEach((shadow) => shadow.classList.add("hidden"));
                    document.body.classList.remove("no-scroll");
                    resolve(resultado);
                };

                const radios = divModalAssumirAluno.querySelectorAll('input[name="resultadoConclusaoAluno"]');
                const label = divModalAssumirAluno.querySelector('#labelTextoResultadoAluno');
                const textarea = divModalAssumirAluno.querySelector('#textoResultadoAluno');
                const aviso = divModalAssumirAluno.querySelector('#avisoJustificativaAluno');
                const erro = divModalAssumirAluno.querySelector('#erroTextoResultadoAluno');

                const atualizarCampo = () => {
                    const naoConcluida = divModalAssumirAluno.querySelector('input[name="resultadoConclusaoAluno"]:checked')?.value === 'nao_concluida';
                    label.textContent = naoConcluida ? 'Justificativa da não conclusão (obrigatória)' : 'Solução (opcional)';
                    textarea.placeholder = naoConcluida ? 'Explique por que a demanda não foi concluída.' : 'Descreva a solução aplicada, se desejar.';
                    aviso?.classList.toggle('hidden', !naoConcluida);
                    erro.classList.add('hidden');
                };

                radios.forEach((radio) => radio.addEventListener('change', atualizarCampo));
                divModalAssumirAluno.querySelector(".btnCancelarConcluirAluno").onclick = () => fechar(false);
                divModalAssumirAluno.querySelector(".btnConfirmarConcluirAluno").onclick = () => {
                    const situacao = divModalAssumirAluno.querySelector('input[name="resultadoConclusaoAluno"]:checked').value;
                    const texto = textarea.value.trim();
                    if (situacao === 'nao_concluida' && !texto) {
                        erro.classList.remove('hidden');
                        textarea.focus();
                        return;
                    }

                    fechar({
                        situacao,
                        solucao: situacao === 'concluida' ? texto : '',
                        justificativa: situacao === 'nao_concluida' ? texto : ''
                    });
                };
            });
        }

        async function executarAcaoDemanda(demanda, secao = "dashboard") {
            const statusAtual = String(demanda?.status || '').trim().toLowerCase();

            if (demandaFinalizada(demanda)) {
                mostrarAvisoPonto('Esta demanda já foi finalizada e não pode ser assumida ou concluída novamente.');
                return;
            }

            const eAssumir = statusAtual === 'aberta';
            const action = eAssumir ? 'assumir_demanda' : 'concluir_demanda';

            if (eAssumir && !await confirmarAssumirDemanda(secao)) return;
            const dadosConclusao = !eAssumir ? await escolherResultadoDemanda(secao) : null;
            if (!eAssumir && !dadosConclusao) return;

            const payload = eAssumir ? {
                id_demanda: demanda.id
            } : {
                id_demanda: demanda.id,
                situacao: dadosConclusao.situacao,
                solucao: dadosConclusao.solucao,
                justificativa: dadosConclusao.justificativa
            };

            try {
                const response = await fetch('../controllers/demanda_controller.php?action=' + action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!data.sucesso) {
                    const mensagem = data.erro || data.message || 'Erro na ação da demanda.';
                    if (mensagem.includes('Finalize sua demanda atual')) {
                        mostrarAvisoPonto(mensagem);
                    } else {
                        mostrarAvisoPonto(mensagem);
                    }
                    return;
                }

                await carregarDemandas();
                await carregarMinhasDemandas();
                await carregarMetricasUsuario();

                const mensagem = data.message || (eAssumir ? 'Demanda assumida com sucesso!' : 'Demanda finalizada com sucesso!');
                if (secao === "registro_ponto") {
                    mostrarAvisoPonto(mensagem);
                    await carregarRegistroPonto();
                    return;
                }
                mostrarAvisoPonto(mensagem);
            } catch (error) {
                console.error('Erro ao executar ação da demanda:', error);
                mostrarAvisoPonto('Erro ao processar a ação da demanda.');
            }
        }

        window.addEventListener("DOMContentLoaded", () => {
            carregarDemandas();
            carregarMinhasDemandas();
            carregarMetricasUsuario();
            carregarOrientadoresAluno();
        });

        async function carregarOrientadoresAluno() {
            if (!filtroOrientadorDemandaAluno) return;

            try {
                const response = await fetch('../controllers/demanda_controller.php?action=listar_orientadores', {
                    method: 'POST'
                });
                const data = await response.json();

                if (!data.sucesso) return;

                data.dados.forEach((orientador) => {
                    const option = document.createElement('option');
                    option.value = orientador.id;
                    option.textContent = orientador.nome;
                    filtroOrientadorDemandaAluno.appendChild(option);
                });
            } catch (error) {
                console.error('Erro ao carregar orientadores para filtro:', error);
            }
        }

        function atualizarVisibilidadeSecoesDashboard() {
            const secaoAberto = document.getElementById('colunaDashboardEmAberto');
            const secaoAndamento = document.getElementById('colunaDashboardEmAndamento');

            const existeAberto = Array.from(divDemandasDisponiveis || []).some((container) => container.children.length > 0);
            const existeAndamento = Array.from(divDemandasAndamento || []).some((container) => container.children.length > 0);

            if (secaoAberto) {
                secaoAberto.style.visibility = existeAberto ? 'visible' : 'hidden';
            }

            if (secaoAndamento) {
                secaoAndamento.style.visibility = existeAndamento ? 'visible' : 'hidden';
            }
        }

        function ajustarAlturaColunasDashboard() {
            [
                document.getElementById('colunaDashboardEmAberto'),
                document.getElementById('colunaDashboardEmAndamento')
            ].forEach((coluna) => {
                if (!coluna) return;

                const secaoDashboard = coluna.closest('.secs-aluno');
                if (secaoDashboard && getComputedStyle(secaoDashboard).display === 'none') return;

                const lista = coluna.querySelector(':scope > div > div');
                const quantidade = lista?.children.length || 0;
                if (!lista || quantidade === 0) {
                    coluna.style.height = '';
                    lista?.parentElement?.style.removeProperty('height');
                    lista?.parentElement?.style.removeProperty('max-height');
                    lista?.style.removeProperty('max-height');
                    lista?.style.removeProperty('overflow-y');
                    return;
                }

                const viewport = lista.parentElement;
                if (viewport) {
                    viewport.style.flex = 'none';
                    viewport.style.height = 'auto';
                }

                coluna.style.removeProperty('height');
                lista.style.removeProperty('max-height');
                lista.style.removeProperty('overflow-y');

                if (quantidade <= 2) {
                    coluna.style.height = 'auto';
                    lista.style.maxHeight = 'none';
                } else {
                    const cardsVisiveis = Array.from(lista.children).slice(0, 2);
                    const estilosLista = getComputedStyle(lista);
                    const paddingVertical = parseFloat(estilosLista.paddingTop) + parseFloat(estilosLista.paddingBottom);
                    const espacamento = parseFloat(estilosLista.rowGap || estilosLista.gap) || 0;
                    const altura = paddingVertical + cardsVisiveis.reduce((total, card) => total + card.getBoundingClientRect().height, 0) + espacamento + 4;
                    coluna.style.height = `${Math.ceil(altura)}px`;
                    lista.style.maxHeight = `${Math.ceil(altura)}px`;
                }
                lista.style.overflowY = quantidade > 2 ? 'auto' : 'hidden';
            });
        }

        function cardSemDemandas() {
            return '<div class="w-full col-span-full text-center text-gray-500 text-sm bg-white rounded-xl border border-gray-100 shadow-sm p-8"><i class="fa-solid fa-magnifying-glass text-[var(--dark-green)] text-xl mb-3"></i><p class="font-semibold text-[var(--gray2)]">Nenhuma demanda disponível no momento.</p><p class="mt-1">Aguarde a disponibilização de novas demandas.</p></div>';
        }

        function renderizarDemandas() {
            let quantDisponiveis = 0;
            let quantAndamento = 0;
            let quantFinalizadas = 0;
            const demandasFiltradas = obterDemandasFiltradasAluno();
            const demandasDashboard = demandas.filter((demanda) => filtroProjetoAlunoAtual === 'todos'
                || String(demanda.id_projeto) === filtroProjetoAlunoAtual);

            divDemandasDisponiveis.forEach((divDisponiveis) => divDisponiveis.innerHTML = "");
            divDemandasAndamento.forEach((divAndamento) => divAndamento.innerHTML = "");
            divGeralDemandas.forEach((divGeral) => divGeral.innerHTML = "");
            atualizarVisibilidadeSecoesDashboard();
            divGeralDemandas.forEach((divGeral) => {
                divGeral.classList.toggle('px-4', demandasFiltradas.length > 0);
            });

            demandasDashboard.forEach((demanda) => {
                const statusNormalizado = String(demanda.status || '').trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '_').replace('disponivel', 'aberta');

                if (statusNormalizado === "aberta") {
                    quantDisponiveis++;
                } else if (statusNormalizado === "em_andamento") {
                    quantAndamento++;
                } else if (demandaFinalizada(demanda)) {
                    quantFinalizadas++;
                }

                if (statusNormalizado === "aberta") {
                    divDemandasDisponiveis.forEach((divDisponiveis) => {
                        const cardDemanda = criarCardDemandaDashboard(demanda);
                        const btnAssumir = cardDemanda.querySelector('.btnAssumir');

                        if (btnAssumir) {
                            btnAssumir.onclick = async () => executarAcaoDemanda(demanda, "dashboard");
                        }

                        const btnVer = cardDemanda.querySelector('.btnVer');
                        if (btnVer) {
                            btnVer.onclick = () => {
                                divModalInfs.forEach((divModal) => {
                                    divModal.innerHTML = '';
                                    const cardInf = criarModalInfs(demanda);
                                    divModal.appendChild(cardInf);
                                    divModal.classList.remove('hidden');

                                    const btnFecharModalInfs = cardInf.querySelector('.btnFecharModalInfs');
                                    btnFecharModalInfs.onclick = () => {
                                        divModal.classList.add('hidden');
                                        shadowDashBoard.forEach((shadow) => shadow.classList.add('hidden'));
                                    };
                                });

                                shadowDashBoard.forEach((shadow) => shadow.classList.remove('hidden'));
                            };
                        }

                        divDisponiveis.appendChild(cardDemanda);
                    });
                }

                if (statusNormalizado === "em_andamento") {
                    divDemandasAndamento.forEach((divAndamento) => {
                        const cardDemanda = criarCardDemandaDashboard(demanda);
                        const btnAssumir = cardDemanda.querySelector('.btnAssumir');

                        if (btnAssumir) {
                            btnAssumir.onclick = async () => executarAcaoDemanda(demanda, "dashboard");
                        }

                        const btnVer = cardDemanda.querySelector('.btnVer');
                        if (btnVer) {
                            btnVer.onclick = () => {
                                divModalInfs.forEach((divModal) => {
                                    divModal.innerHTML = '';
                                    const cardInf = criarModalInfs(demanda);
                                    divModal.appendChild(cardInf);
                                    divModal.classList.remove('hidden');

                                    const btnFecharModalInfs = cardInf.querySelector('.btnFecharModalInfs');
                                    btnFecharModalInfs.onclick = () => {
                                        divModal.classList.add('hidden');
                                        shadowDashBoard.forEach((shadow) => shadow.classList.add('hidden'));
                                    };
                                });

                                shadowDashBoard.forEach((shadow) => shadow.classList.remove('hidden'));
                            };
                        }

                        divAndamento.appendChild(cardDemanda);
                    });
                }

                if (!demandasFiltradas.includes(demanda)) {
                    return;
                }

                divGeralDemandas.forEach((divGeral) => {
                    const cardDemanda = criarCardDemandaDemandas(demanda);
                    const btnAssumir = cardDemanda.querySelector('.btnAssumir');
                    if (btnAssumir) {
                        btnAssumir.onclick = async () => executarAcaoDemanda(demanda, "demandas");
                    }

                    const btnVer = cardDemanda.querySelector('.btnVer');
                    if (btnVer) {
                        btnVer.onclick = () => {
                            divModalInfsSecDemandas.forEach((divModal) => {
                                divModal.innerHTML = '';
                                const cardInf = criarModalInfs(demanda);
                                divModal.appendChild(cardInf);
                                divModal.classList.remove('hidden');

                                const btnFecharModalInfs = cardInf.querySelector('.btnFecharModalInfs');
                                btnFecharModalInfs.onclick = () => {
                                    divModal.classList.add('hidden');
                                    shadowSecDemandas.forEach((shadow) => shadow.classList.add('hidden'));
                                };
                            });

                            shadowSecDemandas.forEach((shadow) => shadow.classList.remove('hidden'));
                        };
                    }

                    divGeral.appendChild(cardDemanda);
                });
            });

            if (quantDisponiveisViewAdm) quantDisponiveisViewAdm.innerHTML = quantDisponiveis;
            if (quantAndamentoViewAdm) quantAndamentoViewAdm.innerHTML = quantAndamento;
            if (quantFinalizadasViewAdm) quantFinalizadasViewAdm.innerHTML = quantFinalizadas;

            atualizarVisibilidadeSecoesDashboard();

            if (quantDisponiveisViewAluno) quantDisponiveisViewAluno.innerHTML = quantDisponiveis;
            if (quantAndamentoViewAluno) quantAndamentoViewAluno.innerHTML = quantAndamento;
            if (quantFinalizadasViewAluno) quantFinalizadasViewAluno.innerHTML = concluidasUsuario;

            if (msgNenhumaDemandaDashboardAluno) {
                const totalCardsDashboardAluno = Array.from(document.querySelectorAll('.divDemandasDisponiveis, .divDemandasAndamento')).reduce((total, container) => total + (container ? container.children.length : 0), 0);

                msgNenhumaDemandaDashboardAluno.innerHTML = totalCardsDashboardAluno === 0 ? cardSemDemandas() : '';
                msgNenhumaDemandaDashboardAluno.classList.toggle('hidden', totalCardsDashboardAluno > 0);
                msgNenhumaDemandaDashboardAluno.style.display = totalCardsDashboardAluno === 0 ? 'block' : 'none';
            }

            ajustarAlturaColunasDashboard();

            if (demandasFiltradas.length === 0) {
                divGeralDemandas.forEach((divGeral) => {
                    divGeral.innerHTML = cardSemDemandas();
                });
            }
        };

        async function carregarMinhasDemandas() {
            const container = document.querySelector('.minhasDemandas');
            if (!container) return;

            try {
                const data = await buscarJsonAluno('../controllers/demanda_controller.php?action=minhas_demandas', { method: 'POST' });
                if (!data.sucesso) {
                    minhasDemandasAtivasIds = new Set();
                    container.classList.remove('px-4');
                    container.innerHTML = '<p class="col-span-full text-gray-500 text-sm bg-white rounded-lg p-5 w-full">Nenhuma demanda atribuída.</p>';
                    return;
                }

                container.innerHTML = '';

                const demandasUsuario = data.dados || [];
                const demandasVisiveisUsuario = demandasUsuario.filter((demanda) => filtroProjetoAlunoAtual === 'todos'
                    || String(demanda.id_projeto) === filtroProjetoAlunoAtual);
                const demandasFinalizadasUsuario = obterDemandasFiltradasAluno(demandasVisiveisUsuario);
                container.classList.toggle('px-4', demandasFinalizadasUsuario.length > 0);
                minhasDemandasAtivasIds = new Set(demandasUsuario
                    .filter((demanda) => Number(demanda.usuario_atribuido) === 1)
                    .map((demanda) => Number(demanda.id))
                    .filter((id) => Number.isFinite(id)));

                if (!demandasFinalizadasUsuario.length) {
                    container.innerHTML = cardSemDemandas();
                container.classList.remove('px-4');
                    renderizarDemandas();
                    return;
                }

                demandasFinalizadasUsuario.forEach((demanda) => {
                    const card = criarCardDemandaDemandas({
                        ...demanda,
                        status: demanda.status || 'em_andamento',
                        projeto: demanda.projeto,
                        prioridade: demanda.prioridade,
                        categoria: demanda.categoria,
                        orientador: demanda.orientador_nome,
                        data_criacao: demanda.data_criacao,
                        prazo: demanda.prazo,
                        titulo: demanda.titulo,
                        descricao: demanda.descricao,
                        usuario_atribuido: Number(demanda.usuario_atribuido) === 1
                    });

                    const btnAssumir = card.querySelector('.btnAssumir');
                    if (btnAssumir) {
                        if (demandaFinalizada(demanda)) {
                            btnAssumir.classList.add('hidden');
                        } else if (Number(demanda.usuario_atribuido) !== 1) {
                            btnAssumir.classList.add('hidden');
                            btnAssumir.disabled = true;
                            btnAssumir.title = 'Você não está atribuído a esta demanda';
                        } else if (Number(demanda.total_assumidos || 0) < Number(demanda.quantidade_alunos || 1)) {
                            aplicarEstadoBotaoAguardando(btnAssumir, card, 'Aguardando');
                        } else {
                            btnAssumir.disabled = false;
                            btnAssumir.classList.remove('hidden');
                            btnAssumir.classList.remove('bg-gray-400', 'opacity-75', 'cursor-not-allowed');
                            btnAssumir.classList.add('bg-[var(--accent-yellow)]', 'hover:bg-[var(--dark-accent-yellow2)]');
                            btnAssumir.innerHTML = '<p>Finalizar</p><i class="fa-solid fa-check"></i>';
                            btnAssumir.onclick = async () => executarAcaoDemanda(demanda, "demandas");
                        }
                    }

                    const btnVer = card.querySelector('.btnVer');
                    if (btnVer) {
                        btnVer.onclick = () => {
                            divModalInfsSecDemandas.forEach((divModal) => {
                                divModal.innerHTML = '';
                                const cardInf = criarModalInfs(demanda);
                                divModal.appendChild(cardInf);
                                divModal.classList.remove('hidden');

                                const btnFecharModalInfs = cardInf.querySelector('.btnFecharModalInfs');
                                btnFecharModalInfs.onclick = () => {
                                    divModal.classList.add('hidden');
                                    shadowSecDemandas.forEach((shadow) => shadow.classList.add('hidden'));
                                };
                            });

                            shadowSecDemandas.forEach((shadow) => shadow.classList.remove('hidden'));
                        };
                    }

                    container.appendChild(card);
                });

                renderizarDemandas();
            } catch (error) {
                console.error('Erro ao carregar minhas demandas:', error);
                minhasDemandasAtivasIds = new Set();
                container.classList.remove('px-4');
                container.innerHTML = cardSemDemandas();
            }
        }

        async function carregarDemandas() {
            const requisicaoAtual = ++requisicaoDemandasAluno;
            try {
                const data = await buscarJsonAluno('../controllers/demanda_controller.php?action=listar_demandas', { method: 'POST' });
                if (requisicaoAtual !== requisicaoDemandasAluno) return;

                if (!data.sucesso) {
                    console.error(data.erro);
                    demandas = [];
                    renderizarDemandas();
                    return;
                }

                demandas = [];

                (Array.isArray(data.dados) ? data.dados : []).forEach(d => {
                    const modelo = criarDemanda(
                        d.titulo,
                        d.prioridade,
                        d.orientador_nome,
                        d.categoria,
                        d.prazo,
                        d.descricao,
                        d.status,
                        d.projeto,
                        d.data_criacao
                    );

                    modelo.id = d.id;
                        modelo.id_projeto = d.id_projeto;
                    modelo.categoria = d.categoria;
                    modelo.orientador_id = d.orientador_id;
                    modelo.situacao = d.situacao;
                    modelo.usuario_atribuido = Number(d.usuario_atribuido) === 1;
                    modelo.responsavel_nome = d.responsavel_nome;
                    modelo.solucao = d.solucao;
                    modelo.justificativa_nao_conclusao = d.justificativa_nao_conclusao;
                    modelo.quantidade_alunos = Number(d.quantidade_alunos || 1);
                    modelo.total_assumidos = Number(d.total_assumidos || 0);
                    demandas.push(modelo);
                });

                demandas.sort((a, b) => {
                    const ordem = {
                        alta: 3,
                        media: 2,
                        baixa: 1
                    };

                    return ordem[b.prioridade] - ordem[a.prioridade];
                });

                renderizarDemandas();

            } catch (error) {
                if (requisicaoAtual !== requisicaoDemandasAluno) return;
                console.error("Erro ao carregar demandas:", error);
                if (!demandas.length) renderizarDemandas();
            }
        };


        if (btnCriarDemanda) btnCriarDemanda.onclick = async (e) => {

            const titulo = tituloDemandaInput.value.trim();
            const prioridade = prioridadeDemandaSelect.value.trim();
            const orientadorId = orientadorDemandaSelect.value.trim();
            const orientadorNome = orientadorDemandaSelect.options[orientadorDemandaSelect.selectedIndex].text;
            const categoria = categoriaDemandaSelect.value.trim();
            const prazo = prazoDemanda.value.trim();
            const descricao = descriDemandaTextArea.value.trim();
            if(!titulo || !prioridade || !orientadorId || !prazo || !categoria) {
                mostrarAvisoPonto("Preencha todos os campos obrigatórios!");
                return;
            }

            try {
                const response = await fetch('../controllers/demanda_controller.php?action=nova_demanda', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        titulo,
                        descricao,
                        prioridade,
                        categoria,
                        prazo,
                        projeto,
                        orientador_id: orientadorId
                    })
                });

                const data = await response.json();
                console.log(data);

                if (!data.sucesso) {
                    mostrarAvisoPonto(data.erro);
                    return;
                }

                await carregarDemandas();

                divModalCriar.classList.add("hidden");
                shadowDashBoard.forEach((shadow) => shadow.classList.add("hidden"));
                document.getElementById("formDemanda")?.reset();
                document.getElementById("descriDemandaTextArea").value = "";

            } catch (error) {
                console.error("Erro:", error);
            }
        };

        if (btnConclusao) btnConclusao.onclick = () => {
            fecharModalConclusao();
            document.querySelector("form").reset();
            document.getElementById("descriDemandaTextArea").value = "";
        };

        // ===== SOLICITAR REABERTURA =====
        window.abrirModalReabertura = function(idDemanda, titulo, resultado) {
            if (resultado !== 'nao_concluida') {
                mostrarAvisoPonto('Apenas demandas não concluídas podem ser reabertas!');
                return;
            }

            abrirModalJustificativaReabertura(idDemanda);
        };

        function abrirModalJustificativaReabertura(idDemanda) {
            const modal = document.getElementById('modalJustificativaReabertura');
            const campo = document.getElementById('campoJustificativaReabertura');
            const fechar = () => {
                modal?.classList.add('hidden');
                modal?.classList.remove('flex');
                if (campo) campo.value = '';
            };

            if (!modal || !campo) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            campo.focus();

            document.getElementById('btnFecharJustificativaReabertura').onclick = fechar;
            document.getElementById('btnCancelarJustificativaReabertura').onclick = fechar;
            document.getElementById('btnEnviarJustificativaReabertura').onclick = () => {
                const justificativa = campo.value.trim();
                if (!justificativa) {
                    mostrarAvisoPonto('Justificativa é obrigatória!');
                    campo.focus();
                    return;
                }
                fechar();
                solicitarReabertura(idDemanda, justificativa);
            };
        }

        async function solicitarReabertura(idDemanda, justificativa) {
            try {
                const response = await fetch('../controllers/demanda_controller.php?action=solicitar_reabertura', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        id_demanda: idDemanda,
                        justificativa: justificativa
                    })
                });

                const data = await response.json();

                if (data.sucesso) {
                    mostrarAvisoPonto('Solicitação de reabertura enviada com sucesso!');
                } else {
                    mostrarAvisoPonto('Erro: ' + (data.erro || 'Erro ao solicitar reabertura'));
                }
            } catch (error) {
                console.error('Erro:', error);
                mostrarAvisoPonto('Erro ao solicitar reabertura');
            }
        }

        const shadowRelatorioPersonalizado = document.getElementById('shadowRelatorioPersonalizado');
        const divModalRelatorioPersonalizado = document.getElementById('divModalRelatorioPersonalizado');
        const btnRelatorioPersonalizado = document.getElementById('btnRelatorioPersonalizado');
        const btnFecharRelatorioPersonalizado = document.getElementById('btnFecharRelatorioPersonalizado');
        const btnLimparFiltros = document.getElementById('btnLimparFiltros');
        const formRelatorioPersonalizado = document.getElementById('formRelatorioPersonalizado');
        const filtroProjetoRelatorio = document.getElementById('filtroProjetoRelatorio');
        const filtroCategoriaRelatorio = document.getElementById('filtroCategoria');
        let requisicaoCategoriasRelatorioAluno = 0;

        async function carregarCategoriasFiltroRelatorioAluno(idProjeto = '') {
            if (!filtroCategoriaRelatorio) return;
            const requisicaoAtual = ++requisicaoCategoriasRelatorioAluno;
            filtroCategoriaRelatorio.disabled = true;
            filtroCategoriaRelatorio.innerHTML = idProjeto
                ? '<option value="">Carregando categorias...</option>'
                : '<option value="">Selecione um projeto primeiro</option>';
            if (!idProjeto) return;

            try {
                const response = await fetch(`../controllers/projeto_controller.php?action=listar_categorias&id_projeto=${encodeURIComponent(idProjeto)}`, { cache: 'no-store' });
                const data = await response.json();
                if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Erro ao carregar categorias.');
                if (requisicaoAtual !== requisicaoCategoriasRelatorioAluno) return;

                const categorias = Array.isArray(data.dados) ? data.dados : [];
                filtroCategoriaRelatorio.innerHTML = '<option value="">Todas as categorias</option>' + categorias
                    .sort((a, b) => String(a.nome).localeCompare(String(b.nome), 'pt-BR'))
                    .map((categoria) => `<option value="${String(categoria.nome).replace(/[&<>"']/g, (caractere) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[caractere])}">${String(categoria.nome).replace(/[&<>"']/g, (caractere) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[caractere])}</option>`)
                    .join('');
                filtroCategoriaRelatorio.disabled = false;
            } catch (error) {
                if (requisicaoAtual !== requisicaoCategoriasRelatorioAluno) return;
                console.error('Erro ao carregar categorias do relatório:', error);
                filtroCategoriaRelatorio.innerHTML = '<option value="">Não foi possível carregar</option>';
            }
        }

        function fecharRelatorioPersonalizado() {
            divModalRelatorioPersonalizado?.classList.add('hidden');
            shadowRelatorioPersonalizado?.classList.add('hidden');
            document.body.classList.remove('no-scroll');
        }

        btnRelatorioPersonalizado?.addEventListener('click', () => {
            divModalRelatorioPersonalizado?.classList.remove('hidden');
            shadowRelatorioPersonalizado?.classList.remove('hidden');
            document.body.classList.add('no-scroll');
            carregarCategoriasFiltroRelatorioAluno(filtroProjetoRelatorio?.value || '');
        });

        btnFecharRelatorioPersonalizado?.addEventListener('click', fecharRelatorioPersonalizado);
        shadowRelatorioPersonalizado?.addEventListener('click', fecharRelatorioPersonalizado);
        btnLimparFiltros?.addEventListener('click', () => {
            formRelatorioPersonalizado?.reset();
            carregarCategoriasFiltroRelatorioAluno(filtroProjetoRelatorio?.value || '');
        });

        filtroProjetoRelatorio?.addEventListener('change', (event) => {
            carregarCategoriasFiltroRelatorioAluno(event.target.value || '');
        });

        document.querySelectorAll('.btnRelatorioSituacao').forEach((botao) => {
            botao.addEventListener('click', () => {
                const situacao = botao.dataset.situacao;
                window.open('relatorios/relatorio_situacao.php?situacao=' + encodeURIComponent(situacao), '_blank', 'noopener,noreferrer');
            });
        });

        formRelatorioPersonalizado?.addEventListener('submit', (event) => {
            event.preventDefault();
            const formulario = new FormData(formRelatorioPersonalizado);
            const situacoes = formulario.getAll('filtroSituacao');
            const prioridades = formulario.getAll('filtroPrioridade');
            const projeto = formulario.get('filtroProjeto');
            const categoria = formulario.get('filtroCategoria');
            const inicio = formulario.get('filtroDataInicio');
            const fim = formulario.get('filtroDataFim');

            if (!situacoes.length && !prioridades.length && !projeto && !categoria && !inicio && !fim) {
                mostrarAvisoPonto('Selecione pelo menos um filtro para gerar o relatório.');
                return;
            }

            const parametros = new URLSearchParams();
            if (situacoes.length) parametros.set('situacao', situacoes.join(','));
            if (prioridades.length) parametros.set('prioridade', prioridades.join(','));
            if (projeto) parametros.set('projeto', projeto);
            if (filtroProjetoAlunoAtual !== 'todos' && !projeto) parametros.set('id_projeto', filtroProjetoAlunoAtual);
            if (categoria) parametros.set('categoria', categoria);
            if (inicio) parametros.set('data_inicio', inicio);
            if (fim) parametros.set('data_fim', fim);

            fecharRelatorioPersonalizado();
            window.open('relatorios/relatorio_personalizado.php?' + parametros.toString(), '_blank', 'noopener,noreferrer');
        });

        async function carregarGraficosAluno() {
            if (graficosAlunoCarregando) {
                donutChartAluno?.resize();
                return;
            }

            if (typeof Chart === "undefined") return;
            graficosAlunoCarregando = true;

            try {
                const [respostaSituacao, respostaCategoria] = await Promise.all([
                    fetch('../controllers/relatorios_api.php?action=demandas_por_situacao' + obterParametroProjetoAluno()),
                    fetch('../controllers/relatorios_api.php?action=demandas_por_categoria' + obterParametroProjetoAluno())
                ]);

                if (!respostaSituacao.ok || !respostaCategoria.ok) {
                    throw new Error(`Erro ao consultar relatórios: ${respostaSituacao.status}/${respostaCategoria.status}`);
                }

                const dadosSituacao = await respostaSituacao.json();
                const dadosCategoria = await respostaCategoria.json();

                if (!dadosSituacao.sucesso || !dadosCategoria.sucesso) {
                    throw new Error('A API de relatórios retornou uma resposta inválida.');
                }

                renderizarDonutAluno(dadosSituacao);
                renderizarCategoriasAluno(dadosCategoria);
            } catch (erro) {
                console.error('Erro ao carregar gráficos dos relatórios:', erro);
            } finally {
                graficosAlunoCarregando = false;
            }
        }

        function getCorBordaDonutAluno() {
            return document.documentElement.getAttribute('data-theme') === 'dark' ? '#171717' : '#ffffff';
        }

        function renderizarDonutAluno(dados) {
            const canvas = document.getElementById('donutChartAluno');
            const total = document.getElementById('donutTotalAluno');
            const legenda = document.getElementById('donutLegendAluno');
            if (!canvas || !total || !legenda) return;

            if (donutChartAluno) donutChartAluno.destroy();

            total.textContent = Number(dados.total || 0).toLocaleString('pt-BR');
            legenda.innerHTML = '';

            dados.dados.forEach((item) => {
                const percentual = dados.total > 0 ? ((item.total / dados.total) * 100).toFixed(1) : '0.0';
                const itemLegenda = document.createElement('div');
                itemLegenda.className = 'legend-item';
                itemLegenda.innerHTML = `<span class="legend-label"><span class="legend-dot"></span>${item.label}</span><span class="legend-value">${item.total.toLocaleString('pt-BR')} (${percentual}%)</span>`;
                itemLegenda.querySelector('.legend-dot').style.backgroundColor = item.cor;
                legenda.appendChild(itemLegenda);
            });

            donutChartAluno = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: dados.dados.map(item => item.label),
                    datasets: [{
                        data: dados.dados.map(item => item.total),
                        backgroundColor: dados.dados.map(item => item.cor),
                        borderColor: getCorBordaDonutAluno(),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } }
                }
            });
        }

        const observerTemaDonutAluno = new MutationObserver((mutations) => {
            const alterouTema = mutations.some((mutation) => mutation.attributeName === 'data-theme');
            if (!alterouTema || !donutChartAluno) return;

            donutChartAluno.data.datasets[0].borderColor = getCorBordaDonutAluno();
            donutChartAluno.update();
        });

        observerTemaDonutAluno.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });

        function renderizarCategoriasAluno(dados) {
            const lista = document.getElementById('progressListAluno');
            if (!lista) return;

            lista.innerHTML = '';
            dados.dados.forEach((item, index) => {
                const linha = document.createElement('div');
                linha.className = 'progress-item';
                linha.innerHTML = `<div><div class="progress-head"><span>${item.categoria}</span><span>${item.total} (${item.percentual}%)</span></div><div class="progress-bar"><div class="progress-fill" style="width: 0%"></div></div></div>`;
                lista.appendChild(linha);

                setTimeout(() => {
                    const barra = linha.querySelector('.progress-fill');
                    if (barra) {
                        barra.style.transition = 'width .8s ease-out';
                        barra.style.width = `${item.percentual}%`;
                    }
                }, 50 + index * 100);
            });
        }

        window.addEventListener('resize', () => {
            if (donutChartAluno) donutChartAluno.resize();
        });

    </script>

    <script>
        (() => {
            const corpoRanking = document.getElementById('rankingPontuacaoBody');
            if (!corpoRanking) return;

            function criarCelula(classe, texto) {
                const celula = document.createElement('td');
                celula.className = classe;
                celula.textContent = texto;
                return celula;
            }

            function renderizarRanking(dados) {
                corpoRanking.replaceChildren();
                if (!dados.length) {
                    const linha = document.createElement('tr');
                    linha.appendChild(criarCelula('px-5 py-6 text-center text-gray-500', 'Nenhuma pontuação registrada.'));
                    linha.firstChild.colSpan = 4;
                    corpoRanking.appendChild(linha);
                    return;
                }

                dados.forEach((usuario, indice) => {
                    const linha = document.createElement('tr');
                    linha.className = 'hover:bg-[var(--gray-crede)] transition';
                    linha.appendChild(criarCelula('px-5 py-3 font-semibold text-[var(--accent-yellow)]', indice + 1));
                    const celulaUsuario = document.createElement('td');
                    celulaUsuario.className = 'px-5 py-3 font-medium text-gray-800';
                    const grupoUsuario = document.createElement('div');
                    grupoUsuario.className = 'flex items-center gap-3 min-h-10';
                    const avatar = document.createElement('div');
                    avatar.className = 'w-10 h-10 shrink-0 rounded-full bg-[var(--dark-green)] text-white flex items-center justify-center font-semibold overflow-hidden';
                    if (usuario.foto) {
                        const imagem = document.createElement('img');
                        imagem.className = 'w-full h-full object-cover';
                        imagem.src = '../assets/' + usuario.foto;
                        imagem.alt = 'Foto de ' + usuario.nome;
                        avatar.appendChild(imagem);
                    } else {
                        avatar.textContent = String(usuario.nome || '').slice(0, 1);
                    }
                    const nome = document.createElement('span');
                    nome.textContent = usuario.nome || '';
                    grupoUsuario.append(avatar, nome);
                    celulaUsuario.appendChild(grupoUsuario);
                    linha.appendChild(celulaUsuario);
                    const tiposExibicao = String(usuario.tipo_exibicao || 'Tipo não definido').split(',').map(tipo => tipo.trim().replace(/^./, letra => letra.toUpperCase())).join(', ');
                    linha.appendChild(criarCelula('px-5 py-3 text-gray-600', tiposExibicao));
                    linha.appendChild(criarCelula('px-5 py-3 text-right font-semibold text-[var(--dark-green)]', `${Number(usuario.pontuacao) || 0} pts`));
                    corpoRanking.appendChild(linha);
                });
            }

            async function atualizarRankingPontuacao() {
                try {
                    const resposta = await fetch('../controllers/usuario_controller.php?action=listar_ranking_pontuacao&_=' + Date.now() + obterParametroProjetoAluno(), {
                        credentials: 'same-origin',
                        cache: 'no-store'
                    });
                    if (!resposta.ok) return;
                    const resultado = await resposta.json();
                    if (resultado.sucesso && Array.isArray(resultado.dados)) renderizarRanking(resultado.dados);
                } catch (erro) {
                    console.error('Erro ao atualizar ranking:', erro);
                }
            }

            window.atualizarRankingPontuacao = atualizarRankingPontuacao;
            atualizarRankingPontuacao();
            setInterval(() => {
                if (!document.hidden) atualizarRankingPontuacao();
            }, 2000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) atualizarRankingPontuacao();
            });
        })();
    </script>

    <script>
        (() => {
            document.addEventListener('click', (event) => {
                const modal = event.target.closest('[id^="divModal"], .modal-overlay-animated, .divModalInfs, .divModalInfsSecDemandas');
                if (!modal || event.target !== modal) return;

                if (modal.classList.contains('modal-overlay-animated')) {
                    modal.remove();
                } else {
                    modal.classList.add('hidden');
                    if (modal.id !== 'divModalAviso') modal.innerHTML = '';
                }

                document.querySelectorAll('[id^="shadow"], .shadowDashBoard').forEach((shadow) => shadow.classList.add('hidden'));
                document.body.classList.remove('no-scroll');
                document.getElementById('mainAlunos')?.classList.remove('no-scroll', 'h-screen');
            });
        })();
    </script>
</body>
</html>