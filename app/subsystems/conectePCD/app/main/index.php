<?php
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    $tipoUsuario = $_SESSION['tipo'] ?? 'responsavel';
    $interfaces = [
        'master' => __DIR__ . '/views/usuarios/master.php',
        'aee' => __DIR__ . '/views/usuarios/coordenador_aee.php',
        'apoio' => __DIR__ . '/views/usuarios/profissional_apoio.php',
        'responsavel' => __DIR__ . '/views/usuarios/aluno_pcd.php',
    ];

    if (isset($interfaces[$tipoUsuario])) {
        require $interfaces[$tipoUsuario];
        exit;
    }

    session_unset();
    session_destroy();
    header('Location: index.php?erro=perfil_invalido');
    exit;
}

$mensagensAcesso = [
    'primeiro_acesso' => ['success', 'Cadastro realizado com sucesso! Sua solicitação foi enviada para análise. Aguarde a aprovação de um administrador para realizar o primeiro acesso.'],
    '6' => ['warning', 'Seu cadastro ainda aguarda aprovação do master.'],
    '7' => ['error', 'Seu cadastro foi recusado pelo master.'],
    'campos_vazios' => ['error', 'Preencha todos os campos obrigatórios para concluir o primeiro acesso.'],
    'email_invalido' => ['error', 'O e-mail informado não é válido.'],
    'senha_fraca' => ['error', 'A senha deve ter no mínimo 6 caracteres.'],
    'tipo_invalido' => ['error', 'Tipo de usuário inválido.'],
    'usuario_ja_existe' => ['error', 'Já existe um cadastro com este e-mail.'],
    'erro_servidor' => ['error', 'Não foi possível concluir o primeiro acesso neste momento. Tente novamente.'],
    'usuario_nao_encontrado' => ['error', 'Usuário não encontrado. Verifique os dados informados.'],
    'senha_incorreta' => ['error', 'Senha incorreta. Verifique sua senha e tente novamente.'],
    'login_erro' => ['error', 'Credenciais inválidas. Verifique seu e-mail e senha.']
];
$keyMensagem = $_GET['sucesso'] ?? $_GET['erro'] ?? '';
$mensagemAcesso = $mensagensAcesso[$keyMensagem] ?? null;
if ($mensagemAcesso === null && !empty($_GET['erro'])) {
    $mensagemAcesso = ['error', 'Não foi possível concluir a operação. Verifique os dados e tente novamente.'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="content-language" content="pt-BR">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#064c2b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>Login | EEEP Salaberga Torquato Gomes de Matos</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Acesso ao sistema da EEEP Salaberga Torquato Gomes de Matos - Hub Educacional em Maranguape">
    <meta name="author" content="EEEP Salaberga Torquato Gomes de Matos">
    <meta name="keywords" content="login, acesso, EEEP Salaberga, sistema educacional, Maranguape">

    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="./assets/s_simbol.png.webp">

    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700;800&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Paleta Studio Ghibli baseada na imagem
                        'ghibli-sky': '#5BA3D4',
                        'ghibli-cloud': '#F8F8F8',
                        'ghibli-building': '#8B9DC3',
                        'ghibli-accent': '#D4A574',
                        'ghibli-green': '#7FB069',
                        'ghibli-sage': '#A4C3A2',
                        'ghibli-cream': '#F7F3E9',
                        'ghibli-peach': '#F4A261',
                        'ghibli-coral': '#E76F51',
                        'ghibli-lavender': '#B19CD9',
                        'ghibli-gold': '#F1C40F',
                        'ghibli-brown': '#8B4513',
                        'ghibli-forest': '#2D5016',
                        'ghibli-mist': '#E8F4F8',
                        'ghibli-sunset': '#FF6B6B'
                    },
                    backgroundImage: {
                        'ghibli-gradient': 'linear-gradient(135deg, #7FB069 0%, #A4C3A2 100%)',
                        'ghibli-sunset': 'linear-gradient(135deg, #F4A261 0%, #E76F51 100%)',
                        'ghibli-sky-gradient': 'linear-gradient(135deg, #5BA3D4 0%, #87CEEB 100%)',
                        'ghibli-forest': 'linear-gradient(135deg, #2D5016 0%, #7FB069 100%)',
                        'hero-ghibli': 'url("https://hebbkx1anhila5yf.public.blob.vercel-storage.com/img_index3-9IdYq3BVf9Sebk6O6b0ZJtyMde0HUW.png")'
                    },
                    fontFamily: {
                        'ghibli': ['Comfortaa', 'cursive'],
                        'ghibli-text': ['Nunito', 'sans-serif']
                    },
                    boxShadow: {
                        'ghibli': '0 10px 25px -5px rgba(127, 176, 105, 0.3)',
                        'ghibli-warm': '0 10px 25px -5px rgba(244, 162, 97, 0.3)',
                        'ghibli-soft': '0 5px 15px -3px rgba(164, 195, 162, 0.2)',
                        'ghibli-cloud': '0 8px 32px rgba(248, 248, 248, 0.4)'
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'sway': 'sway 4s ease-in-out infinite',
                        'drift': 'drift 8s linear infinite',
                        'sparkle': 'sparkle 2s ease-in-out infinite',
                        'bounce-gentle': 'bounce-gentle 3s ease-in-out infinite'
                    }
                }
            }
        }
    </script>

    <style>
        /* Fonte principal */
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #F7F3E9 0%, #E8F4F8 100%);
        }

        /* Animações Studio Ghibli */
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        @keyframes sway {
            0%, 100% {
                transform: translateX(0px) rotate(0deg);
            }
            50% {
                transform: translateX(10px) rotate(2deg);
            }
        }

        @keyframes drift {
            0% {
                transform: translateY(-100px) translateX(0px) rotate(0deg);
            }
            100% {
                transform: translateY(calc(100vh + 100px)) translateX(50px) rotate(360deg);
            }
        }

        @keyframes sparkle {
            0%, 100% {
                opacity: 0.3;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(127, 176, 105, 0.4);
            }
            50% {
                box-shadow: 0 0 30px rgba(127, 176, 105, 0.6);
            }
        }

        /* Elementos flutuantes */
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .leaf {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #7FB069;
            border-radius: 0 100% 0 100%;
            animation: drift 15s linear infinite;
            opacity: 0.6;
        }

        .leaf:nth-child(2n) {
            background: #A4C3A2;
            animation-duration: 20s;
            animation-delay: -5s;
        }

        .leaf:nth-child(3n) {
            background: #F4A261;
            animation-duration: 18s;
            animation-delay: -10s;
        }

        .ghibli-cloud {
            position: absolute;
            background: rgba(248, 248, 248, 0.9);
            border-radius: 50px;
            animation: cloud-drift 30s linear infinite;
            opacity: 0.8;
            box-shadow: 0 4px 20px rgba(248, 248, 248, 0.3);
        }

        @keyframes cloud-drift {
            0% {
                transform: translateX(-200px);
            }
            100% {
                transform: translateX(calc(100vw + 200px));
            }
        }

        .ghibli-cloud::before,
        .ghibli-cloud::after {
            content: '';
            position: absolute;
            background: rgba(248, 248, 248, 0.9);
            border-radius: 50px;
        }

        .ghibli-cloud.small {
            width: 60px;
            height: 30px;
        }

        .ghibli-cloud.small::before {
            width: 40px;
            height: 40px;
            top: -15px;
            left: 10px;
        }

        .ghibli-cloud.small::after {
            width: 30px;
            height: 30px;
            top: -10px;
            right: 10px;
        }

        /* Enhanced input styles */
        .input-group {
            position: relative;
        }

        .input-group input,
        .input-group select {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, rgba(247, 243, 233, 0.8) 0%, rgba(255, 255, 255, 0.9) 100%);
            border: 2px solid rgba(127, 176, 105, 0.3);
            backdrop-filter: blur(5px);
        }

        .input-group input:focus,
        .input-group select:focus {
            transform: translateY(-2px);
            outline: none;
            border-color: #F4A261;
            box-shadow: 0 8px 25px rgba(244, 162, 97, 0.3);
            background: rgba(255, 255, 255, 0.95);
        }

        .input-group label {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(45, 80, 22, 0.6);
            font-size: 1rem;
            transition: all 0.3s ease;
            pointer-events: none;
            padding: 0 5px;
            background-color: transparent;
            z-index: 1;
            font-family: 'Nunito', sans-serif;
        }

        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label,
        .input-group select:focus + label,
        .input-group select:valid + label {
            top: -12px;
            left: 10px;
            font-size: 0.85rem;
            color: #E76F51;
            transform: translateY(0);
            background: linear-gradient(135deg, #F7F3E9 0%, #E8F4F8 100%);
            padding: 0 8px;
            font-weight: 600;
            border-radius: 4px;
        }

        /* Enhanced button styles */
        .btn-enhanced {
            background: linear-gradient(135deg, #7FB069 0%, #A4C3A2 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-family: 'Comfortaa', cursive;
            box-shadow: 0 8px 20px rgba(127, 176, 105, 0.3);
        }

        .btn-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-enhanced:hover::before {
            left: 100%;
        }

        .btn-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(127, 176, 105, 0.4);
        }

        /* Password strength indicator */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
            background: linear-gradient(90deg, #ff4444 0%, #ffaa00 50%, #7FB069 100%);
        }

        /* Error message styling */
        .error-message {
            background: linear-gradient(135deg, #E76F51 0%, #F4A261 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            margin: 16px 0;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(231, 111, 81, 0.3);
            animation: fadeInUp 0.5s ease-out;
            font-family: 'Nunito', sans-serif;
        }

        /* Loading state */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Efeitos especiais */
        .sparkle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #F1C40F;
            border-radius: 50%;
            animation: sparkle 2s ease-in-out infinite;
        }

        /* Elementos com brilho mágico */
        .magical-glow {
            box-shadow: 0 0 20px rgba(127, 176, 105, 0.4), 0 0 40px rgba(244, 162, 97, 0.2);
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .animate-slide-in-left {
            animation: slideInLeft 0.8s ease-out forwards;
        }

        .animate-slide-in-right {
            animation: slideInRight 0.8s ease-out forwards;
        }

        .pulse-glow {
            animation: pulse-glow 2s infinite;
        }

        /* Password toggle animation */
        input[type="password"]::-ms-reveal {
            display: none;
        }

        .toggle-password.animate {
            animation: toggle-bounce 0.2s ease-out;
        }

        @keyframes toggle-bounce {
            0% { transform: translateY(-50%) scale(1); }
            50% { transform: translateY(-50%) scale(1.2) rotate(10deg); }
            100% { transform: translateY(-50%) scale(1) rotate(0deg); }
        }

        /* Focus styles */
        *:focus {
            outline: 2px solid #F4A261;
            outline-offset: 2px;
            border-radius: 4px;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #7FB069 0%, #F4A261 100%);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #2D5016 0%, #E76F51 100%);
        }
        
        /* Enhanced responsive styles */
        @media (max-width: 640px) {
            .main-container {
                width: 95%;
                margin: 1rem auto;
            }
            
            .input-group label {
                font-size: 0.9rem;
            }
            
            .btn-enhanced {
                padding: 0.75rem 1rem;
            }
        }
        
        @media (max-width: 475px) {
            body {
                display: block;
                min-height: 100dvh;
                overflow-y: auto;
                overflow-x: hidden;
            }

            .main-container {
                width: 100%;
                margin: 0;
                border-radius: 0;
                min-height: 100dvh;
                height: auto;
                overflow: visible;
            }

            .main-container > .grid {
                height: auto;
                min-height: 100dvh;
            }

            .rightDivForm {
                height: auto;
                min-height: 100dvh;
                overflow: visible;
                justify-content: flex-start;
                padding-bottom: 3rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }
        }

        @media (max-width: 1023px) {
            html,
            body {
                min-height: 100%;
                height: auto !important;
                overflow-x: hidden !important;
                overflow-y: auto !important;
            }

            body {
                display: block !important;
                padding-bottom: env(safe-area-inset-bottom);
            }

            .main-container,
            .main-container > .grid,
            .rightDivForm {
                height: auto !important;
                min-height: 100svh !important;
            }

            .main-container {
                overflow: visible !important;
            }

            .rightDivForm {
                justify-content: flex-start !important;
                overflow: visible !important;
                padding-bottom: calc(3rem + env(safe-area-inset-bottom)) !important;
            }

            #primeiroAcessoForm {
                padding-bottom: 1rem;
            }
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #eaf3e8 !important;
        }

        .floating-elements {
            display: none;
        }

        .main-container {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 24px 68px rgba(7, 24, 16, 0.16);
        }

        .main-container > .grid > div:first-child {
            background:
                radial-gradient(circle at 18% 20%, rgba(243, 161, 26, 0.18), transparent 32%),
                linear-gradient(145deg, #064c2b 0%, #008c45 100%);
        }

        .main-container > .grid > div:first-child > .absolute.inset-0:first-child {
            background: linear-gradient(145deg, rgba(6, 76, 43, 0.14), rgba(0, 140, 69, 0.18));
        }

        .main-container > .grid > div:first-child > .absolute.inset-0:nth-child(2) {
            background-image: linear-gradient(135deg, rgba(6, 76, 43, 0.34), rgba(6, 76, 43, 0.04) 55%, rgba(243, 161, 26, 0.18));
        }

        .main-container > .grid > div:first-child h1 span,
        .main-container > .grid > div:first-child .text-ghibli-peach,
        .main-container > .grid > div:first-child .text-ghibli-gold {
            color: #f3a11a !important;
        }

        .rightDivForm {
            background: #fff;
        }

        .rightDivForm form {
            padding: 1.25rem;
            border: 1px solid #e4ece6;
            border-radius: 16px;
            background: #fff;
        }

        .rightDivForm h2 {
            color: #064c2b !important;
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.04em;
        }

        .rightDivForm > .text-center p {
            color: #64736a !important;
        }

        .input-group input,
        .input-group select {
            color: #14251c;
            background: #fff;
            border: 1px solid #d7e5da;
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(20, 37, 28, 0.03);
            backdrop-filter: none;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: #008c45;
            box-shadow: 0 0 0 3px rgba(0, 140, 69, 0.12);
            background: #fff;
            transform: none;
        }

        .input-group label {
            color: #64736a;
            font-family: 'Inter', sans-serif;
        }

        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label,
        .input-group select:focus + label,
        .input-group select:valid + label {
            color: #276448;
            background: #fff;
        }

        .rightDivForm .text-ghibli-green {
            color: #008c45 !important;
        }

        .rightDivForm button[id$="Option"] {
            font-family: 'Inter', sans-serif;
        }

        .rightDivForm button[id$="Option"].bg-white {
            color: #064c2b !important;
            box-shadow: 0 2px 8px rgba(6, 76, 43, 0.1);
        }

        .rightDivForm button[id$="Option"]:not(.bg-white) {
            color: #64736a !important;
        }

        .btn-enhanced {
            background: linear-gradient(135deg, #008c45 0%, #276448 100%) !important;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(0, 140, 69, 0.2);
            font-family: 'Inter', sans-serif;
        }

        .btn-enhanced:hover {
            background: linear-gradient(135deg, #064c2b 0%, #008c45 100%) !important;
            box-shadow: 0 14px 28px rgba(6, 76, 43, 0.24);
        }

        *:focus {
            outline: none;
        }

        :focus-visible {
            outline: 3px solid #f3a11a;
            outline-offset: 3px;
        }

        @media (max-width: 639px) {
            .main-container {
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .rightDivForm {
                background: #eaf3e8;
            }

            .rightDivForm > .text-center,
            .rightDivForm > div:has(button[id$="Option"]),
            .rightDivForm form,
            .rightDivForm > div[role="status"] {
                background: #fff;
            }

            .rightDivForm > .text-center,
            .rightDivForm > div:has(button[id$="Option"]) {
                padding: 1rem;
                border: 1px solid #e4ece6;
                border-radius: 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="font-ghibli-text bg-gradient-to-br from-ghibli-mist via-ghibli-cloud to-ghibli-sky min-h-screen flex items-center justify-center p-0 sm:p-4">
    
    <!-- Elementos flutuantes Studio Ghibli -->
    <div class="floating-elements">
        <div class="leaf" style="top: -10%; left: 10%; animation-delay: 0s;"></div>
        <div class="leaf" style="top: -10%; left: 80%; animation-delay: -3s;"></div>
        <div class="leaf" style="top: -10%; left: 20%; animation-delay: -6s;"></div>
        <div class="leaf" style="top: -10%; left: 70%; animation-delay: -9s;"></div>
        <div class="leaf" style="top: -10%; left: 90%; animation-delay: -12s;"></div>
        <div class="leaf" style="top: -10%; left: 40%; animation-delay: -15s;"></div>
        <div class="leaf" style="top: -10%; left: 60%; animation-delay: -18s;"></div>
        <div class="leaf" style="top: -10%; left: 30%; animation-delay: -21s;"></div>

        <!-- Nuvens flutuantes -->
        <div class="ghibli-cloud small" style="top: 20%; animation-delay: 0s;"></div>
        <div class="ghibli-cloud small" style="top: 60%; animation-delay: -10s;"></div>
        <div class="ghibli-cloud small" style="top: 40%; animation-delay: -20s;"></div>
    </div>

    <div class="main-container w-full max-w-6xl bg-ghibli-cream rounded-none sm:rounded-3xl shadow-ghibli overflow-hidden animate-fade-in-up relative z-10 h-screen lg:h-[728px]">
        <div class="grid grid-cols-1 lg:grid-cols-2 h-screen lg:h-[728px]">
            
            <!-- Enhanced Image Container com traços Ghibli -->
            <div class="hidden md:block lg:flex-1 bg-ghibli-forest relative overflow-hidden animate-slide-in-left h-full">
                <div class="absolute inset-0 bg-black/40"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-ghibli-forest/80 via-transparent to-ghibli-coral/80"></div>
                
                <!-- Elementos decorativos Ghibli -->
                <div class="absolute top-10 left-10 w-20 h-20 border-2 border-white/30 rounded-full animate-float"></div>
                <div class="absolute bottom-20 right-10 w-16 h-16 border-2 border-white/30 rounded-full animate-sway"></div>
                <div class="absolute top-1/3 right-20 w-12 h-12 bg-white/20 rounded-full animate-bounce-gentle"></div>
                
                <!-- Sparkles mágicos -->
                <div class="sparkle" style="top: 15%; left: 20%; animation-delay: 0s;"></div>
                <div class="sparkle" style="top: 70%; left: 80%; animation-delay: 1s;"></div>
                <div class="sparkle" style="top: 40%; left: 15%; animation-delay: 2s;"></div>
                <div class="sparkle" style="top: 80%; left: 30%; animation-delay: 3s;"></div>
                
                <div class="relative z-10 h-full flex flex-col justify-center items-center p-8 lg:p-12 text-center text-white">
                    <div class="mb-8">
                        <i class="fas fa-graduation-cap text-6xl lg:text-8xl mb-6 text-ghibli-peach"></i>
                    </div>
                    
                    <h1 class="text-3xl lg:text-5xl font-bold mb-6 leading-tight font-ghibli">
                        EEEP <span class="text-ghibli-peach">Salaberga</span>
                    </h1>
                    
                    <p class="text-lg lg:text-xl mb-8 max-w-md leading-relaxed opacity-90 font-ghibli-text">
                        Transformando o futuro através da educação e inovação tecnológica
                    </p>
                    
                    <div class="flex space-x-4 text-sm opacity-80">
                        <div class="flex items-center animate-bounce-gentle" style="animation-delay: 0.5s;">
                            <i class="fas fa-users mr-2 text-ghibli-gold"></i>
                            <span>Comunidade</span>
                        </div>
                        <div class="flex items-center animate-bounce-gentle" style="animation-delay: 1s;">
                            <i class="fas fa-award mr-2 text-ghibli-gold"></i>
                            <span>Excelência</span>
                        </div>
                        <div class="flex items-center animate-bounce-gentle" style="animation-delay: 1.5s;">
                            <i class="fas fa-rocket mr-2 text-ghibli-gold"></i>
                            <span>Inovação</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Form Container com traços Ghibli -->
            <div class="rightDivForm w-full lg:flex-1 p-4 xs:p-6 sm:p-8 lg:p-12 flex flex-col justify-start animate-slide-in-right relative overflow-y-auto min-h-0 h-full">
                
                <!-- Sparkles no formulário -->
                <div class="sparkle" style="top: 10%; right: 10%; animation-delay: 0s;"></div>
                <div class="sparkle" style="bottom: 20%; left: 15%; animation-delay: 2s;"></div>
                
                <!-- Logo Container com efeitos Ghibli -->
                <div class="text-center mb-6 sm:mb-8">
                    <div class="inline-block p-3 sm:p-4 rounded-2xl">
                        <img src="./assets/s_simbol.png.webp" 
                             alt="Logo EEEP Salaberga" 
                             class="w-16 h-16 sm:w-20 sm:h-20 object-contain">
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-ghibli-forest mb-2 font-ghibli">
                        Bem-vindo
                    </h2>
                    <p class="text-ghibli-forest/70 text-base sm:text-lg font-ghibli-text">
                        Acesse sua conta institucional
                    </p>
                </div>

                <!-- Tipo de Acesso -->
                <div class="mb-6">
                    <div class="flex bg-ghibli-sage/20 rounded-xl p-1">
                        <button type="button" 
                                id="loginOption" 
                                class="flex-1 py-2 px-4 rounded-lg font-medium transition-all duration-300 bg-white text-ghibli-forest shadow-ghibli-soft">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Login
                        </button>
                        <button type="button" 
                                id="primeiroAcessoOption" 
                                class="flex-1 py-2 px-4 rounded-lg font-medium transition-all duration-300 text-ghibli-forest/60 hover:text-ghibli-forest">
                            <i class="fas fa-key mr-2"></i>
                            Primeiro Acesso
                        </button>
                    </div>
                </div>

                <?php if ($mensagemAcesso): ?>
                    <div class="mb-5 rounded-xl border px-4 py-3 text-sm <?= $mensagemAcesso[0] === 'success' ? 'border-green-200 bg-green-50 text-green-800' : ($mensagemAcesso[0] === 'warning' ? 'border-yellow-200 bg-yellow-50 text-yellow-800' : 'border-red-200 bg-red-50 text-red-800') ?>" role="status">
                        <i class="fas <?= $mensagemAcesso[0] === 'success' ? 'fa-circle-check' : ($mensagemAcesso[0] === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-xmark') ?> mr-2"></i>
                        <?= htmlspecialchars($mensagemAcesso[1], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <!-- Formulário de Login -->
                <form id="loginForm" 
                      action="./controllers/usuario_controller.php?action=login"
                      method="POST" 
                      class="space-y-4 sm:space-y-6">
                    
                    <!-- Email Input -->
                    <div class="input-group">
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   id="loginEmail" 
                                   placeholder=" " 
                                   required 
                                   class="w-full px-4 py-3 sm:py-4 pl-10 sm:pl-12 rounded-xl text-ghibli-forest focus:shadow-ghibli-soft transition-all duration-300 peer text-sm sm:text-base">
                            <label for="loginEmail" 
                                   class="absolute left-10 mx-10 sm:left-12 top-3 sm:top-4 transition-all duration-300 peer-focus:text-ghibli-coral peer-focus:text-xs sm:peer-focus:text-sm peer-focus:-translate-y--1 peer-focus:font-semibold peer-[:not(:placeholder-shown)]:text-xs sm:peer-[:not(:placeholder-shown)]:text-sm peer-[:not(:placeholder-shown)]: peer-[:not(:placeholder-shown)]:text-ghibli-coral peer-[:not(:placeholder-shown)]:font-semibold text-sm sm:text-base">
                                E-mail
                            </label>
                            <i class="fas fa-envelope absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-ghibli-green text-base sm:text-lg"></i>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="input-group">
                        <div class="relative">
                            <input type="password" 
                                   name="senha" 
                                   id="loginPassword" 
                                   placeholder=" " 
                                   required
                                   class="w-full px-4 py-3 sm:py-4 pl-10 sm:pl-12 pr-10 sm:pr-12 rounded-xl text-ghibli-forest focus:shadow-ghibli-soft transition-all duration-300 peer text-sm sm:text-base">
                            <label for="loginPassword" 
                                   class="absolute left-10 mx-10 sm:left-12 top-3 sm:top-4 transition-all duration-300 peer-focus:text-ghibli-coral peer-focus:text-xs sm:peer-focus:text-sm peer-focus:-translate-y--1 peer-focus:font-semibold peer-[:not(:placeholder-shown)]:text-xs sm:peer-[:not(:placeholder-shown)]:text-sm peer-[:not(:placeholder-shown)]: peer-[:not(:placeholder-shown)]:text-ghibli-coral peer-[:not(:placeholder-shown)]:font-semibold text-sm sm:text-base">
                                Senha
                            </label>
                            <i class="fas fa-lock absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-ghibli-green text-base sm:text-lg"></i>
                            <button type="button"
                                    class="toggle-password absolute right-3 sm:right-4 top-1/2 transform -translate-y-1/2 text-ghibli-forest/40 hover:text-ghibli-coral cursor-pointer transition-colors duration-300 text-base sm:text-lg"
                                    aria-label="Mostrar senha"
                                    title="Mostrar senha">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Error Message -->  
                    <?php if (isset($_GET['login']) && $_GET['login'] == 'erro'): ?>
                        <div class="error-message flex items-center text-sm sm:text-base">
                            <i class="fas fa-exclamation-triangle mr-2 sm:mr-3"></i>
                            <span>Dados de login incorretos. Verifique suas credenciais e tente novamente.</span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <p class="text-sm text-right"><a href="./views/recuperar-senha.php" class="text-ghibli-coral hover:text-ghibli-peach transition-colors duration-300">Esqueci a senha?</a></p>
                    </div>

                    <!-- Submit Button Login -->
                    <div class="mt-4 sm:mt-6">
                        <button type="submit" 
                                class="btn-enhanced w-full px-6 py-3 sm:py-4 text-white rounded-xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-ghibli-sage focus:ring-opacity-50 text-sm sm:text-base font-semibold bg-gradient-to-r from-ghibli-green to-ghibli-sage hover:from-ghibli-forest hover:to-ghibli-green">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            <span>Acessar Sistema</span>
                        </button>
                    </div>
                </form>

                <!-- Formulário de Primeiro Acesso -->
                <form id="primeiroAcessoForm" 
                      action="./controllers/usuario_controller.php?action=primeiro_acesso" 
                      method="POST" 
                      class="space-y-4 sm:space-y-6 hidden">
                    
                    <!-- Nome Input -->
                    <div class="input-group">
                        <div class="relative">
                            <input type="text" 
                                   name="nome" 
                                   id="primeiroAcessoNome" 
                                   placeholder=" " 
                                   required 
                                   class="w-full px-4 py-3 sm:py-4 pl-10 sm:pl-12 rounded-xl text-ghibli-forest focus:shadow-ghibli-soft transition-all duration-300 peer text-sm sm:text-base">
                            <label for="primeiroAcessoNome" 
                                   class="absolute left-10 mx-10 sm:left-12 top-3 sm:top-4 transition-all duration-300 peer-focus:text-ghibli-coral peer-focus:text-xs sm:peer-focus:text-sm peer-focus:-translate-y--1 peer-focus:font-semibold peer-[:not(:placeholder-shown)]:text-xs sm:peer-[:not(:placeholder-shown)]:text-sm peer-[:not(:placeholder-shown)]: peer-[:not(:placeholder-shown)]:text-ghibli-coral peer-[:not(:placeholder-shown)]:font-semibold text-sm sm:text-base">
                                Nome
                            </label>
                            <i class="fas fa-user absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-ghibli-green text-base sm:text-lg"></i>
                        </div>
                    </div>

                    <!-- Email Input -->
                    <div class="input-group">
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   id="primeiroAcessoEmail" 
                                   placeholder=" " 
                                   required 
                                   class="w-full px-4 py-3 sm:py-4 pl-10 sm:pl-12 rounded-xl text-ghibli-forest focus:shadow-ghibli-soft transition-all duration-300 peer text-sm sm:text-base">
                                   <label for="primeiroAcessoEmail" 
                                   class="absolute left-10 mx-10 sm:left-12 top-3 sm:top-4 transition-all duration-300 peer-focus:text-ghibli-coral peer-focus:text-xs sm:peer-focus:text-sm peer-focus:-translate-y--1 peer-focus:font-semibold peer-[:not(:placeholder-shown)]:text-xs sm:peer-[:not(:placeholder-shown)]:text-sm peer-[:not(:placeholder-shown)]: peer-[:not(:placeholder-shown)]:text-ghibli-coral peer-[:not(:placeholder-shown)]:font-semibold text-sm sm:text-base">
                                   E-mail
                                </label>
                            <i class="fas fa-envelope absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-ghibli-green text-base sm:text-lg"></i>
                        </div>
                    </div>

                    <!-- Senha Input -->
                    <div class="input-group">
                        <div class="relative">
                            <input type="password" 
                                   name="senha" 
                                   id="primeiroAcessoSenha" 
                                   placeholder=" " 
                                   required 
                                   class="w-full px-4 py-3 sm:py-4 pl-10 sm:pl-12 rounded-xl text-ghibli-forest focus:shadow-ghibli-soft transition-all duration-300 peer text-sm sm:text-base">
                                   <label for="primeiroAcessoSenha" 
                                   class="absolute left-10 mx-10 sm:left-12 top-3 sm:top-4 transition-all duration-300 peer-focus:text-ghibli-coral peer-focus:text-xs sm:peer-focus:text-sm peer-focus:-translate-y--1 peer-focus:font-semibold peer-[:not(:placeholder-shown)]:text-xs sm:peer-[:not(:placeholder-shown)]:text-sm peer-[:not(:placeholder-shown)]: peer-[:not(:placeholder-shown)]:text-ghibli-coral peer-[:not(:placeholder-shown)]:font-semibold text-sm sm:text-base">
                                Senha
                            </label>
                            <i class="fas fa-lock absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-ghibli-green text-base sm:text-lg"></i>
                        </div>
                    </div>

                    <!-- CPF Input -->
                    <div class="input-group">
                        <div class="relative">
                            <input type="text" name="cpf" id="primeiroAcessoCpf" placeholder=" " maxlength="14" inputmode="numeric" oninput="mascaraCPF(this)" required class="w-full px-4 py-3 sm:py-4 pl-10 sm:pl-12 rounded-xl text-ghibli-forest focus:shadow-ghibli-soft transition-all duration-300 peer text-sm sm:text-base">
                            <label for="primeiroAcessoCpf" class="absolute left-10 mx-10 sm:left-12 top-3 sm:top-4 transition-all duration-300 peer-focus:text-ghibli-coral peer-focus:text-xs sm:peer-focus:text-sm peer-focus:-translate-y--1 peer-focus:font-semibold peer-[:not(:placeholder-shown)]:text-xs sm:peer-[:not(:placeholder-shown)]:text-sm peer-[:not(:placeholder-shown)]:text-ghibli-coral peer-[:not(:placeholder-shown)]:font-semibold text-sm sm:text-base">CPF</label>
                            <i class="fas fa-id-card absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-ghibli-green text-base sm:text-lg"></i>
                        </div>
                    </div>

                    <!-- Telefone Input -->
                    <div class="input-group">
                        <div class="relative">
                            <input type="text" name="telefone" id="primeiroAcessoTelefone" placeholder=" " maxlength="15" inputmode="tel" oninput="mascaraTelefone(this)" required class="w-full px-4 py-3 sm:py-4 pl-10 sm:pl-12 rounded-xl text-ghibli-forest focus:shadow-ghibli-soft transition-all duration-300 peer text-sm sm:text-base">
                            <label for="primeiroAcessoTelefone" class="absolute left-10 mx-10 sm:left-12 top-3 sm:top-4 transition-all duration-300 peer-focus:text-ghibli-coral peer-focus:text-xs sm:peer-focus:text-sm peer-focus:-translate-y--1 peer-focus:font-semibold peer-[:not(:placeholder-shown)]:text-xs sm:peer-[:not(:placeholder-shown)]:text-sm peer-[:not(:placeholder-shown)]:text-ghibli-coral peer-[:not(:placeholder-shown)]:font-semibold text-sm sm:text-base">Telefone</label>
                            <i class="fas fa-phone absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-ghibli-green text-base sm:text-lg"></i>
                        </div>
                    </div>
                    
                    <!-- Tipo de acesso -->
                    <div class="input-group">
                        <div class="relative">
                            <select name="tipo" id="primeiroAcessoTipo" required class="w-full px-4 py-3 sm:py-4 pl-10 sm:pl-12 pr-10 sm:pr-12 rounded-xl text-ghibli-forest focus:shadow-ghibli-soft transition-all duration-300 peer text-sm sm:text-base appearance-none bg-white">
                                <option value="" disabled selected></option>
                                <option value="aee">AEE</option>
                                <option value="apoio">Apoio</option>
                                <option value="responsavel">Responsável</option>
                            </select>
                            <label for="primeiroAcessoTipo" class="absolute left-10 mx-10 sm:left-12 top-3 sm:top-4 transition-all duration-300 peer-focus:text-ghibli-coral peer-focus:text-xs sm:peer-focus:text-sm peer-focus:-translate-y--1 peer-focus:font-semibold peer-[:not(:placeholder-shown)]:text-xs sm:peer-[:not(:placeholder-shown)]:text-sm peer-[:not(:placeholder-shown)]:text-ghibli-coral peer-[:not(:placeholder-shown)]:font-semibold text-sm sm:text-base">Tipo de acesso</label>
                            <i class="fas fa-user-shield absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-ghibli-green text-base sm:text-lg"></i>
                            <i class="fas fa-chevron-down absolute right-3 sm:right-4 top-1/2 transform -translate-y-1/2 text-ghibli-forest/40 pointer-events-none text-base sm:text-lg"></i>
                        </div>
                    </div>
                    
                    <!-- Submit Button Primeiro Acesso -->
                    <div class="mt-4 sm:mt-6">
                        <button type="submit"
                        class="btn-enhanced w-full px-6 py-3 sm:py-4 text-white rounded-xl transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-ghibli-sage focus:ring-opacity-50 text-sm sm:text-base font-semibold bg-gradient-to-r from-ghibli-green to-ghibli-sage hover:from-ghibli-forest hover:to-ghibli-green">
                        <i class="fas fa-key mr-2"></i>
                        <span>Verificar Primeiro Acesso</span>
                        </button>
                    </div>

                </form>

                <!-- Additional Links -->
                <div class="mt-6 sm:mt-8 text-center space-y-3 sm:space-y-4">
                    <div class="pt-3 sm:pt-4 border-t border-ghibli-sage/20">
                        <p class="text-ghibli-forest/60 text-xs sm:text-sm font-ghibli-text">
                            <a href="#" class="text-ghibli-coral hover:text-ghibli-peach transition-colors duration-300">
                                <i class="fas fa-arrow-left mr-1"></i>Voltar ao portal
                            </a>
                            <span class="mx-2">•</span>
                            <a href="#" class="text-ghibli-coral hover:text-ghibli-peach transition-colors duration-300">
                                <i class="fas fa-question-circle mr-1"></i>Precisa de ajuda?
                            </a>
                            <span class="mx-2">•</span>
                            <a href="mailto:suporte@eeepsalaberga.edu.br" 
                               class="text-ghibli-coral hover:text-ghibli-peach transition-colors duration-300">
                                Contate o suporte
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script>
        function mascaraCPF(input) {
            input.value = input.value.replace(/\D/g, '').slice(0, 11)
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        }

        function mascaraTelefone(input) {
            const digits = input.value.replace(/\D/g, '').slice(0, 11);
            input.value = digits.length <= 10
                ? digits.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2')
                : digits.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Elementos dos formulários
            const loginForm = document.getElementById('loginForm');
            const primeiroAcessoForm = document.getElementById('primeiroAcessoForm');
            const togglePassword = document.querySelector('.toggle-password');
            const loginPasswordInput = document.getElementById('loginPassword');
            const primeiroAcessoNomeInput = document.getElementById('primeiroAcessoNome');
            const primeiroAcessoEmailInput = document.getElementById('primeiroAcessoEmail');
            const primeiroAcessoSenhaInput = document.getElementById('primeiroAcessoSenha');
            const loginEmailInput = document.getElementById('loginEmail');
            const primeiroAcessoTipo = document.getElementById('primeiroAcessoTipo');
            
            // Elementos de controle
            const loginOption = document.getElementById('loginOption');
            const primeiroAcessoOption = document.getElementById('primeiroAcessoOption');

            const mainContainer = document.querySelector('.main-container');
            const rightDivForm = document.querySelector(".rightDivForm");

            togglePassword.addEventListener('click', function() {
                const senhaVisivel = loginPasswordInput.type === 'text';
                loginPasswordInput.type = senhaVisivel ? 'password' : 'text';
                togglePassword.setAttribute('aria-label', senhaVisivel ? 'Mostrar senha' : 'Ocultar senha');
                togglePassword.setAttribute('title', senhaVisivel ? 'Mostrar senha' : 'Ocultar senha');
                togglePassword.innerHTML = `<i class="fas ${senhaVisivel ? 'fa-eye' : 'fa-eye-slash'}" aria-hidden="true"></i>`;
                togglePassword.classList.remove('animate');
                void togglePassword.offsetWidth;
                togglePassword.classList.add('animate');
            });

            loginOption.addEventListener('click', function() {
                toggleMode(false);
            });

            primeiroAcessoOption.addEventListener('click', function() {
                toggleMode(true);
            });

            // Função para alternar entre login e primeiro acesso
            function toggleMode(isPrimeiroAcessoMode) {
                if (isPrimeiroAcessoMode) {
                    loginOption.classList.remove('bg-white', 'text-ghibli-forest', 'shadow-ghibli-soft');
                    loginOption.classList.add('text-ghibli-forest/60');
                    primeiroAcessoOption.classList.remove('text-ghibli-forest/60');
                    primeiroAcessoOption.classList.add('bg-white', 'text-ghibli-forest', 'shadow-ghibli-soft');

                    loginForm.classList.add('hidden');
                    primeiroAcessoForm.classList.remove('hidden');
                } else {
                    primeiroAcessoOption.classList.remove('bg-white', 'text-ghibli-forest', 'shadow-ghibli-soft');
                    primeiroAcessoOption.classList.add('text-ghibli-forest/60');
                    loginOption.classList.remove('text-ghibli-forest/60');
                    loginOption.classList.add('bg-white', 'text-ghibli-forest', 'shadow-ghibli-soft');

                    primeiroAcessoForm.classList.add('hidden');
                    loginForm.classList.remove('hidden');
                }
            }

            // Enhanced form submission for login
            loginForm.addEventListener('submit', function(e) {
                const email = loginEmailInput.value.trim();
                const password = loginPasswordInput.value;

                if (!email) {
                    alert('Por favor, insira seu e-mail.');
                    loginEmailInput.focus();
                    return;
                }

                if (!password) {
                    alert('Por favor, insira sua senha.');
                    loginPasswordInput.focus();
                    return;
                }

                const submitButton = loginForm.querySelector('button[type="submit"]');
                submitButton.classList.add('loading');
                submitButton.disabled = true;

                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<span><i class="fas fa-spinner fa-spin mr-2"></i>Entrando...</span>';

                setTimeout(() => {
                    submitButton.classList.remove('loading');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                    alert('Login realizado com sucesso!');
                }, 2000);
            });

            // Enhanced form submission for primeiro acesso
            primeiroAcessoForm.addEventListener('submit', function(e) {
                const nome = primeiroAcessoNomeInput.value.trim();
                const tipo = document.getElementById('primeiroAcessoTipo').value;
                const email = primeiroAcessoEmailInput.value.trim();
                const senha = primeiroAcessoSenhaInput.value.trim();

                if (!nome) {
                    e.preventDefault();
                    alert('Por favor, insira seu nome.');
                    primeiroAcessoNomeInput.focus();
                    return;
                }

                if (!tipo) {
                    e.preventDefault();
                    alert('Por favor, selecione seu tipo.');
                    document.getElementById('primeiroAcessoTipo').focus();
                    return;
                }

                if (!email) {
                    e.preventDefault();
                    alert('Por favor, insira seu e-mail.');
                    primeiroAcessoEmailInput.focus();
                    return;
                }

                if (!senha) {
                    e.preventDefault();
                    alert('Por favor, insira sua senha.');
                    primeiroAcessoSenhaInput.focus();
                    return;
                }

                const submitButton = primeiroAcessoForm.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.classList.add('loading');
                    submitButton.disabled = true;
                    const originalText = submitButton.innerHTML;
                    submitButton.innerHTML = '<span><i class="fas fa-spinner fa-spin mr-2"></i>Verificando...</span>';
                    setTimeout(() => {
                        submitButton.classList.remove('loading');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                    }, 1500);
                }
            });

            // Enhanced input and select animations
            const focusables = document.querySelectorAll('input, select');
            focusables.forEach(field => {
                field.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 0 20px rgba(127, 176, 105, 0.3)';
                });
                
                field.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                    this.style.boxShadow = '';
                });
            });

            // Add ripple effect to button
            const submitButtons = document.querySelectorAll('.btn-enhanced');
            submitButtons.forEach(submitButton => {
                submitButton.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(244, 162, 97, 0.4);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s ease-out;
                        pointer-events: none;
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);

            // Criar sparkles dinamicamente
            function createSparkle() {
                const sparkle = document.createElement('div');
                sparkle.className = 'sparkle';
                sparkle.style.top = Math.random() * 100 + '%';
                sparkle.style.left = Math.random() * 100 + '%';
                sparkle.style.animationDelay = Math.random() * 2 + 's';
                
                document.querySelector('.animate-slide-in-right').appendChild(sparkle);
                
                setTimeout(() => {
                    sparkle.remove();
                }, 2000);
            }

            // Criar sparkles periodicamente
            setInterval(createSparkle, 4000);
        });

        // Enhanced accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-navigation');
        });
        
        // Handle orientation changes on mobile
        window.addEventListener('orientationchange', function() {
            // Small delay to allow the browser to update dimensions
            setTimeout(() => {
                const vh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--vh', `${vh}px`);
            }, 100);
        });
        
        // Set initial viewport height variable
        (function() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        })();
    </script>
</body>

</html>