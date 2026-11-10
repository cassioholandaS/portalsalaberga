<!-- 
********************************************************************************
*                                                                              *
*   AREA DEV 001 — FIZEMOS HISTÓRIA!                                           *
*                                                                              *
*   @mth_fl  @lvnas._  @rogercavalcantee  @p_.uchoa  @_jefferson.castro        *
*   @otavio.ce                                                                 *
*                                                                              *
*   A primeira área de desenvolvimento não foi apenas o início de uma jornada  *
*   técnica — foi o nascimento de uma cultura, de uma comunidade, de um sonho  *
*   que hoje pulsa em cada linha de código que escrevemos.                     *
*                                                                              *
*   Foi ali que as primeiras ideias ganharam forma, que os primeiros desafios  *
*   foram enfrentados com criatividade e coragem. Em meio a erros, testes e    *
*   conquistas, formou-se o alicerce de tudo o que viria depois. Cada tela     *
*   branca preenchida, cada console.log exibido, cada deploy feito… todos      *
*   carregam um pouco da história dessa área pioneira.                         *
*                                                                              *
*   Essa primeira área não apenas abriu caminhos — ela construiu pontes.       *
*   Conectou pessoas com propósitos, inspirou gerações de desenvolvedores e    *
*   acendeu uma chama que continua a brilhar em cada nova aplicação criada.    *
*                                                                              *
*   Hoje, com gratidão e orgulho, celebramos esse marco. Que a memória dessa   *
*   primeira área nos lembre sempre de onde viemos e nos inspire a continuar   *
*   construindo o futuro com o mesmo entusiasmo, curiosidade e vontade de      *
*   transformar o mundo com tecnologia.                                        *
*                                                                              *
*   Vlw amigos.                                                                *
*******************************************************************************
-->

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="content-language" content="pt-BR">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#7FB069">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>EEEP Salaberga Torquato Gomes de Matos | Hub Educacional Mágico</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="EEEP Salaberga Torquato Gomes de Matos: Hub Educacional em Maranguape. Oferecemos educação profissional de qualidade com ensino médio integrado ao técnico.">
    <meta name="author" content="EEEP Salaberga Torquato Gomes de Matos">
    <meta name="keywords" content="educação profissional, ensino médio técnico, Maranguape educação, cursos técnicos, EEEP Salaberga Torquato, EEEP Salaberga, Salaberga, Escola Salaberga, Salaberga Torquato">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="./assets/img/S.png">

    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Comfortaa:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Link para o CSS do Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Script do Swiper -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Paleta Studio Ghibli baseada na imagem
                        'ghibli-sky': '#5BA3D24',
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

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        @keyframes sway {

            0%,
            100% {
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

        @keyframes cloud-drift {
            0% {
                transform: translateX(-200px);
            }

            100% {
                transform: translateX(calc(100vw + 200px));
            }
        }

        @keyframes sparkle {

            0%,
            100% {
                opacity: 0.3;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        @keyframes bounce-gentle {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
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

        .ghibli-cloud.medium {
            width: 100px;
            height: 50px;
        }

        .ghibli-cloud.medium::before {
            width: 60px;
            height: 60px;
            top: -25px;
            left: 15px;
        }

        .ghibli-cloud.medium::after {
            width: 50px;
            height: 50px;
            top: -20px;
            right: 15px;
        }

        /* Hero Section com background da imagem */
        .hero-section {
            background-image: url('https://hebbkx1anhila5yf.public.blob.vercel-storage.com/img_index3-9IdYq3BVf9Sebk6O6b0ZJtyMde0HUW.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(91, 163, 212, 0.1) 0%, rgba(248, 248, 248, 0.2) 50%, rgba(127, 176, 105, 0.1) 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        /* Header estilo Ghibli */
        .ghibli-header {
            background: linear-gradient(135deg, rgba(248, 248, 248, 0.95) 0%, rgba(164, 195, 162, 0.95) 100%);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid rgba(127, 176, 105, 0.3);
            box-shadow: 0 4px 20px rgba(127, 176, 105, 0.2);
        }

        .ghibli-header.scrolled {
            background: linear-gradient(135deg, rgba(248, 248, 248, 0.98) 0%, rgba(164, 195, 162, 0.98) 100%);
            box-shadow: 0 8px 30px rgba(127, 176, 105, 0.3);
        }

        /* Navegação */
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
            color: #2D5016;
            font-weight: 500;
            font-family: 'Comfortaa', cursive;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #F4A261, #E76F51);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #E76F51;
            transform: translateY(-2px);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 80%;
        }

        /* Cards estilo Ghibli */
        .ghibli-card {
            background: linear-gradient(135deg, rgba(247, 243, 233, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
            border: 2px solid rgba(127, 176, 105, 0.2);
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(5px);
        }

        .ghibli-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #7FB069 0%, #F4A261 50%, #E76F51 100%);
        }

        .ghibli-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(127, 176, 105, 0.3);
            border-color: rgba(127, 176, 105, 0.4);
        }

        /* Botões estilo Ghibli */
        .ghibli-btn {
            background: linear-gradient(135deg, #7FB069 0%, #A4C3A2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-family: 'Comfortaa', cursive;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(127, 176, 105, 0.3);
            position: relative;
            overflow: hidden;
        }

        .ghibli-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .ghibli-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(127, 176, 105, 0.4);
        }

        .ghibli-btn:hover::before {
            left: 100%;
        }

        .ghibli-btn-secondary {
            background: linear-gradient(135deg, #F4A261 0%, #E76F51 100%);
            box-shadow: 0 8px 20px rgba(244, 162, 97, 0.3);
        }

        .ghibli-btn-secondary:hover {
            box-shadow: 0 15px 30px rgba(244, 162, 97, 0.4);
        }

        /* Seções */
        .ghibli-section {
            position: relative;
            padding: 100px 0;
            overflow: hidden;
        }

        .ghibli-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #7FB069 0%, #F4A261 50%, #E76F51 100%);
        }

        /* Títulos */
        .ghibli-title {
            font-family: 'Comfortaa', cursive;
            font-weight: 700;
            background: linear-gradient(135deg, #7FB069 0%, #2D5016 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            display: inline-block;
        }

        .ghibli-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 3px;
            background: linear-gradient(90deg, #F4A261 0%, #E76F51 100%);
            border-radius: 2px;
        }

        /* Carrossel 3D estilo Ghibli */
        .ghibli-carousel {
            perspective: 2000px;
            width: 100%;
            height: 600px;
            position: relative;
            margin: 0 auto 80px auto;
            overflow: hidden;
            border-radius: 30px;
            padding: 40px 20px;
            background: linear-gradient(135deg, rgba(247, 243, 233, 0.3) 0%, rgba(232, 244, 248, 0.3) 100%);
            backdrop-filter: blur(10px);
        }

        .ghibli-carousel-container {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 1.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ghibli-carousel-item {
            position: absolute;
            width: 350px;
            height: 450px;
            transition: all 1.5s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, rgba(247, 243, 233, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
            border-radius: 25px;
            padding: 40px;
            text-align: center;
            opacity: 0;
            border: 3px solid transparent;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            backface-visibility: hidden;
            transform-origin: center center;
            box-shadow: 0 15px 35px rgba(127, 176, 105, 0.2);
            backdrop-filter: blur(5px);
        }

        .ghibli-carousel-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #7FB069, #F4A261, #E76F51);
            border-radius: 25px;
            padding: 3px;
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }

        .ghibli-carousel-item.active {
            opacity: 1;
            border-color: rgba(127, 176, 105, 0.3);
        }

        .ghibli-carousel-item.active::before {
            opacity: 1;
        }

        .ghibli-carousel-item h2 {
            color: #2D5016;
            font-size: 3.5em;
            margin-bottom: 30px;
            font-weight: 800;
            font-family: 'Comfortaa', cursive;
            background: linear-gradient(135deg, #7FB069 0%, #2D5016 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        .ghibli-carousel-item p {
            color: #2D5016;
            font-size: 1.2em;
            line-height: 1.7;
            font-weight: 400;
            text-align: justify;
            font-family: 'Nunito', sans-serif;
        }

        /* Indicadores */
        .ghibli-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 10;
        }

        .ghibli-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(127, 176, 105, 0.3);
            cursor: pointer;
            transition: all 0.4s ease;
            border: 2px solid transparent;
        }

        .ghibli-indicator.active {
            background: #7FB069;
            transform: scale(1.3);
            box-shadow: 0 0 15px rgba(127, 176, 105, 0.5);
        }

        .ghibli-indicator:hover {
            background: #A4C3A2;
        }

        /* Galeria */
        .ghibli-gallery-img {
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(127, 176, 105, 0.2);
        }

        .ghibli-gallery-img:hover {
            transform: scale(1.05) translateY(-5px);
            box-shadow: 0 20px 40px rgba(127, 176, 105, 0.3);
        }

        /* Footer estilo Ghibli */
        .ghibli-footer {
            background: linear-gradient(135deg, #2D5016 0%, #1a1a1a 50%, #2D5016 100%);
            position: relative;
        }

        .ghibli-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #7FB069 0%, #F4A261 50%, #E76F51 100%);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .ghibli-carousel {
                height: 500px;
                padding: 20px 15px;
            }

            .ghibli-carousel-item {
                width: 300px;
                height: 400px;
                padding: 30px;
            }

            .ghibli-carousel-item h2 {
                font-size: 2.5em;
            }

            .ghibli-section {
                padding: 60px 0;
            }
        }

        /* Menu mobile estilo Ghibli */
        .ghibli-mobile-menu {
            background: linear-gradient(135deg, rgba(248, 248, 248, 0.98) 0%, rgba(164, 195, 162, 0.98) 100%);
            backdrop-filter: blur(15px);
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

        /* Loading spinner estilo Ghibli */
        .ghibli-spinner {
            border: 4px solid rgba(127, 176, 105, 0.3);
            border-top: 4px solid #7FB069;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Efeitos de hover suaves */
        .ghibli-hover {
            transition: all 0.3s ease;
        }

        .ghibli-hover:hover {
            transform: translateY(-3px);
            filter: brightness(1.1);
        }

        /* Scroll suave */
        html {
            scroll-behavior: smooth;
        }

        /* Focus styles */
        *:focus {
            outline: 2px solid #F4A261;
            outline-offset: 2px;
            border-radius: 4px;
        }

        /* Elementos ocultos */
        [x-cloak] {
            display: none !important;
        }

        /* Animação de entrada */
        .fade-in {
            animation: fadeInUp 1s ease-out forwards;
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

        /* Seções com opacidade inicial */
        section {
            opacity: 0;
        }

        /* Texto com sombra suave estilo Ghibli */
        .ghibli-text-shadow {
            text-shadow: 2px 2px 4px rgba(45, 80, 22, 0.3);
        }

        /* Elementos com brilho mágico */
        .magical-glow {
            box-shadow: 0 0 20px rgba(127, 176, 105, 0.4), 0 0 40px rgba(244, 162, 97, 0.2);
        }

        /* Estilos do Carrossel de Cursos */
        .course-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Removendo o efeito de hover dos cards de curso */
        /*
        .course-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(127, 176, 105, 0.3);
        }
        */

        .content-overlay {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
        }

        .carousel {
            scroll-behavior: smooth;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .carousel::-webkit-scrollbar {
            display: none;
        }

        .text-ceara-green {
            color: #7FB069;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #7FB069 0%, #A4C3A2 100%);
        }

        /* Estilos da Seção de Eventos */
        .card-enhanced {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hover-scale {
            transition: transform 0.3s ease;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        .bg-gradient-secondary {
            background: linear-gradient(135deg, #F4A261 0%, #E76F51 100%);
            /* Usando cores Ghibli pêssego/coral */
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #7FB069 0%, #A4C3A2 100%);
            /* Cor Ghibli green */
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(127, 176, 105, 0.4);
        }

        /* Estilos personalizados para o Swiper */
        .swiper-button-next,
        .swiper-button-prev {
            background-color: rgba(164, 195, 162, 0.8);
            color: #2D5016;
            /* ghibli-forest */
            width: 45px;
            height: 45px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(45, 80, 22, 0.2);
            transition: all 0.3s ease;
            opacity: 0.9;
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 20px;
            color: white;
            /* Cor das setas alterada para branco */
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background-color: rgba(244, 162, 97, 0.9);
            color: white;
            /* Contraste com a cor de hover */
            box-shadow: 0 6px 20px rgba(244, 162, 97, 0.3);
            opacity: 1;
            transform: scale(1.1);
        }

        .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: #A4C3A2;
            /* ghibli-sage */
            opacity: 0.6;
            transition: all 0.3s ease;
        }

        .swiper-pagination-bullet-active {
            background: #7FB069;
            /* ghibli-green */
            opacity: 1;
            transform: scale(1.2);
        }

        /* Efeito de hover nos slides */
        .swiper-slide {
            transition: transform 0.3s ease;
        }

        .swiper-slide:hover {
            transform: translateY(-5px);
        }

        /* Efeito 3D aprimorado */
        .parceiros-swiper {
            padding: 50px 0;
            overflow: visible;
        }

        .swiper-slide {
            transition: transform 0.3s ease;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .swiper-slide-active {
            transform: scale(1.05);
            z-index: 2;
        }

        .swiper-slide img {
            transition: all 0.3s ease;
            backface-visibility: hidden;
        }

        .swiper-slide-active img {
            transform: translateZ(10px);
        }

        /* Ajuste para o efeito de sombra */
        .swiper-slide-shadow-left,
        .swiper-slide-shadow-right {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.05));
        }

        @media (min-width: 640px) {
            .developers-title {
                position: relative;
                left: -50px;
            }
        }
    </style>
</head>

<body class="font-ghibli-text">

    <div class="floating-elements">
        <div class="leaf" style="top: -10%; left: 10%; animation-delay: 0s;"></div>
        <div class="leaf" style="top: -10%; left: 80%; animation-delay: -3s;"></div>
        <div class="leaf" style="top: -10%; left: 20%; animation-delay: -6s;"></div>
        <div class="leaf" style="top: -10%; left: 70%; animation-delay: -9s;"></div>
        <div class="leaf" style="top: -10%; left: 90%; animation-delay: -12s;"></div>
        <div class="leaf" style="top: -10%; left: 40%; animation-delay: -15s;"></div>
        <div class="leaf" style="top: -10%; left: 60%; animation-delay: -18s;"></div>
        <div class="leaf" style="top: -10%; left: 30%; animation-delay: -21s;"></div>

    </div>

    <header x-data="{ 
        mobileMenuOpen: false, 
        isScrolled: false,
        activeSection: 'home',
        accessibilityOpen: false,
        fontSize: 'normal',
        
        init() {
            window.addEventListener('scroll', () => {
                this.isScrolled = window.scrollY > 20;
            });
            
            window.addEventListener('scroll', () => {
                const sections = document.querySelectorAll('section[id]');
                const scrollPos = window.scrollY + 100;
                
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.offsetHeight;
                    const sectionId = section.getAttribute('id');
                    
                    if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                        this.activeSection = sectionId;
                    }
                });
            });
            
            this.$watch('mobileMenuOpen', value => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        },
        
        increaseFontSize() {
            if (this.fontSize === 'normal') this.fontSize = 'large';
            else if (this.fontSize === 'small') this.fontSize = 'normal';
            document.documentElement.style.fontSize = this.fontSize === 'small' ? '14px' : this.fontSize === 'normal' ? '16px' : '18px';
        },
        
        decreaseFontSize() {
            if (this.fontSize === 'normal') this.fontSize = 'small';
            else if (this.fontSize === 'large') this.fontSize = 'normal';
            document.documentElement.style.fontSize = this.fontSize === 'small' ? '14px' : this.fontSize === 'normal' ? '16px' : '18px';
        },
        
        resetFontSize() {
            this.fontSize = 'normal';
            document.documentElement.style.fontSize = '16px';
        }
    }"
        class="ghibli-header sticky top-0 z-50 w-full transition-all duration-300"
        :class="{ 'scrolled': isScrolled }">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center">
                    <a href="#home" class="flex items-center space-x-3">
                        <img src="./assets/img/S.png" alt="Logo Salaberga" class="h-8 w-8">
                        <span class="font-ghibli text-xl font-bold text-ghibli-forest ghibli-text-shadow">Salaberga </span>
                    </a>
                </div>

                <nav class="hidden lg:flex items-center space-x-1">
                    <a href="#home"
                        class="nav-link transition duration-300 font-medium px-4 py-2 rounded-md hover:bg-white/20"
                        :class="{ 'active': activeSection === 'home' }">
                        <i class="fas fa-home mr-2"></i> Início
                    </a>
                    <a href="#sobre"
                        class="nav-link transition duration-300 font-medium px-4 py-2 rounded-md hover:bg-white/20"
                        :class="{ 'active': activeSection === 'sobre' }">
                        <i class="fas fa-book-open mr-2"></i> História
                    </a>
                    <a href="#cursos"
                        class="nav-link transition duration-300 font-medium px-4 py-2 rounded-md hover:bg-white/20"
                        :class="{ 'active': activeSection === 'cursos' }">
                        <i class="fas fa-graduation-cap mr-2"></i> Cursos
                    </a>
                    <a href="#eventos"
                        class="nav-link transition duration-300 font-medium px-4 py-2 rounded-md hover:bg-white/20"
                        :class="{ 'active': activeSection === 'eventos' }">
                        <i class="fas fa-calendar-alt mr-2"></i> Eventos
                    </a>
                    <a href="#galeria"
                        class="nav-link transition duration-300 font-medium px-4 py-2 rounded-md hover:bg-white/20"
                        :class="{ 'active': activeSection === 'galeria' }">
                        <i class="fas fa-images mr-2"></i> Galeria
                    </a>
                    <a href="#parceiros"
                        class="nav-link transition duration-300 font-medium px-4 py-2 rounded-md hover:bg-white/20"
                        :class="{ 'active': activeSection === 'parceiros' }">
                        <i class="fas fa-handshake mr-2"></i> Parceiros
                    </a>
                    
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" 
                            class="nav-link text-ghibli-forest hover:text-ghibli-coral transition duration-300 flex items-center font-medium px-4 py-2 rounded-md hover:bg-white/10">
                            <i class="fas fa-sign-in-alt mr-2"></i> Entrar
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-300"
                               :class="{ 'transform rotate-180': open }"></i>
                        </button>
                        <div x-show="open" 
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="dropdown-menu absolute right-0 mt-2 w-56 bg-ghibli-cream rounded-xl shadow-xl py-3 z-50 border border-ghibli-forest/20">
                            <a href="./views/autenticacao/login.php" 
                               class="block px-4 py-3 text-ghibli-forest hover:text-ghibli-coral hover:bg-white/20 transition duration-300 rounded-lg mx-2 font-ghibli">
                                <i class="fas fa-user mr-3"></i> Login Principal
                            </a>
                            <a href="../main/views/autenticacao/login_sesmated.php" 
                               class="block px-4 py-3 text-ghibli-forest hover:text-ghibli-coral hover:bg-white/20 transition duration-300 rounded-lg mx-2 font-ghibli">
                                <i class="fas fa-users mr-3"></i> Login Sesmated
                            </a>
                            <a href="https://crede1.salaberga.com/app/subsystems/gerenciador_escolas/index.php" 
                               class="block px-4 py-3 text-ghibli-forest hover:text-ghibli-coral hover:bg-white/20 transition duration-300 rounded-lg mx-2 font-ghibli">
                                <i class="fas fa-school mr-3"></i> Seleção EEEP's
                            </a>
                            
                        </div>
                    </div>
                </nav>

                <div class="hidden lg:flex items-center space-x-2 ml-6">
                    
                </div>

                <div class="lg:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="flex items-center px-3 py-2 border-2 rounded-lg text-ghibli-forest border-ghibli-forest hover:bg-white/20 transition duration-300"
                        aria-label="Toggle menu"
                        :aria-expanded="mobileMenuOpen">
                        <svg class="fill-current h-4 w-4" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path x-show="!mobileMenuOpen" d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z" />
                            <path x-show="mobileMenuOpen" d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu Mobile -->
        <div x-show="mobileMenuOpen"
            x-cloak
            @click.away="mobileMenuOpen = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-4"
            class="ghibli-mobile-menu lg:hidden absolute top-full left-0 right-0 z-50">
            <div class="px-6 py-8">
                <nav class="space-y-4">
                    <a href="#home"
                        @click="mobileMenuOpen = false"
                        class="block text-ghibli-forest hover:text-ghibli-coral transition-all duration-300 font-ghibli font-medium text-lg"
                        :class="{ 'text-ghibli-coral': activeSection === 'home' }">
                        <i class="fas fa-home mr-3"></i>Inicio
                    </a>
                    <a href="#sobre"
                        @click="mobileMenuOpen = false"
                        class="block text-ghibli-forest hover:text-ghibli-coral transition-all duration-300 font-ghibli font-medium text-lg"
                        :class="{ 'text-ghibli-coral': activeSection === 'sobre' }">
                        <i class="fas fa-book-open mr-3"></i> História
                    </a>
                    <a href="#cursos"
                        @click="mobileMenuOpen = false"
                        class="block text-ghibli-forest hover:text-ghibli-coral transition-all duration-300 font-ghibli font-medium text-lg"
                        :class="{ 'text-ghibli-coral': activeSection === 'cursos' }">
                        <i class="fas fa-graduation-cap mr-3"></i>Cursos
                    </a>
                    <a href="#eventos"
                        @click="mobileMenuOpen = false"
                        class="block text-ghibli-forest hover:text-ghibli-coral transition-all duration-300 font-ghibli font-medium text-lg"
                        :class="{ 'text-ghibli-coral': activeSection === 'eventos' }">
                        <i class="fas fa-calendar-alt mr-3"></i>Eventos
                    </a>
                    <a href="#galeria"
                        @click="mobileMenuOpen = false"
                        class="block text-ghibli-forest hover:text-ghibli-coral transition-all duration-300 font-ghibli font-medium text-lg"
                        :class="{ 'text-ghibli-coral': activeSection === 'galeria' }">
                        <i class="fas fa-images mr-3"></i>Galeria
                    </a>
                    <a href="#parceiros"
                        @click="mobileMenuOpen = false"
                        class="block text-ghibli-forest hover:text-ghibli-coral transition-all duration-300 font-ghibli font-medium text-lg"
                        :class="{ 'text-ghibli-coral': activeSection === 'parceiros' }">
                        <i class="fas fa-handshake mr-3"></i>Parceiros
                    </a>
                    <div x-data="{ loginExpanded: false }" class="mobile-menu-item">
                        <button @click="loginExpanded = !loginExpanded" 
                            class="w-full text-left text-ghibli-forest hover:text-ghibli-coral transition-all duration-300 font-ghibli font-medium text-lg flex items-center">
                            <i class="fas fa-user mr-3"></i>Login
                            <i class="fas fa-chevron-down ml-auto transition-transform duration-300" 
                               :class="{ 'transform rotate-180': loginExpanded }"></i>
                        </button>
                        <div x-show="loginExpanded" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="mt-2 pl-8 space-y-2">
                            <a href="../main/views/autenticacao/login.php" 
                               class="block text-ghibli-forest hover:text-ghibli-coral transition-all duration-300 font-ghibli">
                                <i class="fas fa-sign-in-alt mr-3"></i>Login Principal
                            </a>
                            <a href="../main/views/autenticacao/login_sesmated.php" 
                               class="block text-ghibli-forest hover:text-ghibli-coral transition-all duration-300 font-ghibli">
                                <i class="fas fa-user-plus mr-3"></i>Login sesmated
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <!-- Seção Hero com background da imagem -->
        <section id="home" class="hero-section min-h-screen flex flex-col items-center justify-center overflow-hidden">
            <div class="hero-content container mx-auto px-4 text-center mt-20 md:mt-0">

                <div class="flex flex-col sm:flex-row justify-center items-center space-y-6 sm:space-y-0 sm:space-x-8 mt-[400px]">
                    <a href="#cursos" class="ghibli-btn text-lg px-8 py-4 magical-glow">
                        <i class="fas fa-graduation-cap mr-3"></i> Conheça Nossos Cursos
                    </a>
                </div>
            </div>

            <div class="absolute bottom-8 left-0 right-0 text-center">
                <a href="#sobre" class="text-white hover:text-ghibli-peach transition duration-300">
                    <i class="fas fa-chevron-down text-4xl ghibli-text-shadow"></i>
                </a>
            </div>
        </section>

        <!-- Seção Nossa História com Carrossel 3D -->
        <section class="ghibli-section bg-ghibli-cream" id="sobre">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="ghibli-title text-5xl mb-6">Nossa História</h2>
                    <p class="text-xl text-ghibli-forest max-w-3xl mx-auto font-light">Uma trajetória de excelência e dedicação à educação profissional</p>
                </div>

                <div class="ghibli-carousel">
                    <div class="ghibli-carousel-container">
                        <div class="ghibli-carousel-item" data-index="0">
                            <h2>2009</h2>
                            <p>Iniciamos nossa jornada quando a escola Santa Rita se transformou na primeira escola profissionalizante do município. Nasceu a EEEP Santa Rita, oferecendo cursos técnicos em Enfermagem, Informática e Meio Ambiente.</p>
                        </div>
                        <div class="ghibli-carousel-item" data-index="1">
                            <h2>2011</h2>
                            <p>Formamos nossas primeiras turmas. Técnicos em Enfermagem, Informática e Meio Ambiente concluíram seus cursos, prontos para ingressar no mercado de trabalho.</p>
                        </div>
                        <div class="ghibli-carousel-item" data-index="2">
                            <h2>2014</h2>
                            <p>Mudamos do prédio da EEEP Santa Rita na Guabiraba para ocupar nossa atual sede, renascendo como EEEP Salaberga Torquato Gomes de Matos.</p>
                        </div>
                        <div class="ghibli-carousel-item" data-index="3">
                            <h2>2016</h2>
                            <p>Nossos estudantes José Carlos, Ana Byatriz e Gabriella Vital representaram o Brasil na 13ª Olimpíada Internacional de Geografia em Pequim, China, após uma competição nacional com 35.000 participantes.</p>
                        </div>
                        <div class="ghibli-carousel-item" data-index="4">
                            <h2>2019</h2>
                            <p>Conquistamos o selo de escola sustentável, reconhecendo nosso compromisso com a preservação ambiental e práticas sustentáveis.</p>
                        </div>
                        <div class="ghibli-carousel-item" data-index="5">
                            <h2>2021</h2>
                            <p>Alcançamos o seleto grupo das 100 melhores escolas públicas do país, ocupando o 20º lugar no estado do Ceará.</p>
                        </div>
                        <div class="ghibli-carousel-item" data-index="6">
                            <h2>2022</h2>
                            <p>Nossos estudantes Kaiky Diniz e Leonardo de Sousa conquistaram vaga na final presencial da ONHB na Unicamp em Campinas.</p>
                        </div>
                        <div class="ghibli-carousel-item" data-index="7">
                            <h2>2023</h2>
                            <p>Expandimos nossa oferta educacional com o curso técnico em Administração, ampliando as oportunidades para nossos estudantes.</p>
                        </div>
                        <div class="ghibli-carousel-item" data-index="8">
                            <h2>2024</h2>
                            <p>Nossas estudantes Natassa Uchôa e Gabriele Ferreira, orientadas pela professora Diva, representaram nossa escola na primeira Expociência internacional na Costa Rica.</p>
                        </div>
                    </div>

                    <!-- Adicionando botões de navegação -->
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>

                    <!-- Removendo os indicadores de bolinha -->
                    <!--
                    <div class="ghibli-indicators">
                        <div class="ghibli-indicator active" data-index="0"></div>
                        <div class="ghibli-indicator" data-index="1"></div>
                        <div class="ghibli-indicator" data-index="2"></div>
                        <div class="ghibli-indicator" data-index="3"></div>
                        <div class="ghibli-indicator" data-index="4"></div>
                        <div class="ghibli-indicator" data-index="5"></div>
                        <div class="ghibli-indicator" data-index="6"></div>
                        <div class="ghibli-indicator" data-index="7"></div>
                        <div class="ghibli-indicator" data-index="8"></div>
                    </div>
                    -->

                </div>
            </div>
        </section>

        <!-- Seção Cursos -->
        <section id="cursos" class="bg-gradient-to-br from-gray-50 to-white py-24 fade-in" x-data="{ activeSlide: 0 }">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="ghibli-title text-5xl font-bold mb-6">Nossos Cursos</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">Formação técnica de excelência para preparar você para o mercado de trabalho</p>
                </div>
                <div class="overflow-x-auto">
                    <div class="flex space-x-8 pb-6 carousel"
                        x-init="setInterval(() => { activeSlide = (activeSlide + 1) % 5; document.querySelector('.carousel').scrollLeft += 320; if(activeSlide === 0) { document.querySelector('.carousel').scrollLeft = 0; } }, 5000)">

                        <div class="course-card min-w-[320px] relative group"
                            :class="{ 'active': activeSlide === 0 }">
                            <img src="./assets/img/img-logoscursos/enfer.png" alt="Curso de Enfermagem"
                                class="w-full h-56 object-contain">
                            <div class="p-6">
                                <h3 class="font-bold text-2xl mb-4 text-ceara-green text-center border-b-2 border-ceara-green pb-2">
                                    Enfermagem
                                </h3>
                            </div>
                        </div>

                        <div class="course-card min-w-[310px] relative"
                            :class="{ 'active': activeSlide === 1 }">
                            <img src="./assets/img/img-logoscursos/info.png" alt="Curso de Informática"
                                class="w-full h-56 object-contain">
                            <div class="p-6">
                                <h3 class="font-bold text-2xl mb-4 text-ceara-green text-center border-b-2 border-ceara-green pb-2">
                                    Informática
                                </h3>
                            </div>
                        </div>

                        <div class="course-card min-w-[320px] relative"
                            :class="{ 'active': activeSlide === 2 }">
                            <img src="./assets/img/img-logoscursos/ma.png" alt="Curso de Meio Ambiente"
                                class="w-full h-56 object-contain">
                            <div class="p-6">
                                <h3 class="font-bold text-2xl mb-4 text-ceara-green text-center border-b-2 border-ceara-green pb-2">
                                    Meio Ambiente
                                </h3>
                            </div>
                        </div>

                        <div class="course-card min-w-[320px] relative"
                            :class="{ 'active': activeSlide === 3 }">
                            <img src="./assets/img/img-logoscursos/edf.png" alt="Curso de Edificações"
                                class="w-full h-56 object-contain">
                            <div class="p-6">
                                <h3 class="font-bold text-2xl mb-4 text-ceara-green text-center border-b-2 border-ceara-green pb-2">
                                    Edificações
                                </h3>
                            </div>
                        </div>

                        <div class="course-card min-w-[320px] relative"
                            :class="{ 'active': activeSlide === 4 }">
                            <img src="./assets/img/img-logoscursos/adm.png" alt="Curso de Administração"
                                class="w-full h-56 object-contain">
                            <div class="p-6">
                                <h3 class="font-bold text-2xl mb-4 text-ceara-green text-center border-b-2 border-ceara-green pb-2">
                                    Administração
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Enhanced Events Section -->
        <section id="eventos" class="py-24 fade-in bg-gradient-to-br from-white to-gray-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="ghibli-title text-5xl font-bold mb-6">Próximos Eventos</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">Fique por dentro das oportunidades e eventos da nossa escola</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">                    

                    
                    <div class="card-enhanced p-8 hover-scale">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-gradient-secondary rounded-full flex items-center justify-center mr-6">
                                <i class="fas fa-cloud text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-2xl mb-2 text-ceara-green">Formulário de Seleção Área Dev</h3>
                                <p class="text-gray-600 font-medium">Junho, 2026</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-6 leading-relaxed">Previsão de edital de seleção: 08/2026</p>
                        <!--Para ativar o link, retirar o "#" na frente do endereço-->
                        <a href="#views/form/form_areaDev.php" target="_blank" class="btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Em breve
                        </a>
                    </div>
                    
                    
                    
                    
                    <div class="card-enhanced p-8 hover-scale">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-gradient-secondary rounded-full flex items-center justify-center mr-6">
                                <i class="fas fa-cloud text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-2xl mb-2 text-ceara-green">Sistema de seleção</h3>
                                <p class="text-gray-600 font-medium">Previsão: Dezembro de 2026</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-6 leading-relaxed">Sistema de seleção da EEEP Salaberga Torquato Gomes de Matos pra futuros alunos!</p>
                        <a href="https://crede1.salaberga.com/app/subsystems/gerenciador_escolas/index.php" target="_blank" class="btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Acesse aqui
                        </a>
                    </div>


                    
                    <div class="card-enhanced p-8 hover-scale">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-gradient-secondary rounded-full flex items-center justify-center mr-6">
                                <i class="fas fa-cloud text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-2xl mb-2 text-ceara-green">Ficha de Cadastro de Concedentes</h3>
                                <p class="text-gray-600 font-medium">20 de dezembro, 2025</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-6 leading-relaxed">Sistema de seleção da EEEP Salaberga Torquato Gomes de Matos pra futuros alunos!</p>
                        <a href="https://salaberga.com/salaberga/portalsalaberga/app/subsystems/estagio/sub_fichas_cadastrais/view/cadastro_concedente.php" target="_blank" class="btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Acesse aqui
                        </a>
                    </div>     
                    
                    
                    
                    
                    <div class="card-enhanced p-8 hover-scale">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-gradient-secondary rounded-full flex items-center justify-center mr-6">
                                <i class="fas fa-cloud text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-2xl mb-2 text-ceara-green"> Cadastro de Vagas</h3>
                                <p class="text-gray-600 font-medium">20 de dezembro, 2025</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-6 leading-relaxed">Sistema de seleção da EEEP Salaberga Torquato Gomes de Matos pra futuros alunos!</p>
                        <a href="https://salaberga.com/salaberga/portalsalaberga/app/subsystems/estagio/sub_fichas_cadastrais/view/form_perfil_vagas.php" target="_blank" class="btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Acesse aqui
                        </a>
                    </div>     
                    
<!--                      
                    
                    
                    <div class="card-enhanced p-8 hover-scale">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-gradient-secondary rounded-full flex items-center justify-center mr-6">
                                <i class="fas fa-cloud text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-2xl mb-2 text-ceara-green">Perfil Estágio</h3>
                                <p class="text-gray-600 font-medium">04 de fevereiro 2026</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-6 leading-relaxed">Informe até duas opções de perfil prioritário para o seu estágio.</p>
                        <a href="https://forms.gle/q7Mc7JkMPaSfbeRr5" target="_blank" class="btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Acesse aqui
                        </a>
                    </div>
                    


                    <div class="card-enhanced p-8 hover-scale">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-gradient-secondary rounded-full flex items-center justify-center mr-6">
                                <i class="fas fa-cloud text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-2xl mb-2 text-ceara-green">Autorização Estágio - Maracanaú</h3>
                                <p class="text-gray-600 font-medium">04 de fevereiro 2026</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-6 leading-relaxed">Por meio desta autorização, o estudante declara possuir condições de estagiar em Maracanaú, assumindo a responsabilidade por seu custeio até o efetivo recebimento da bolsa de estágio.</p>
                        <a href="https://forms.gle/ZNcj6E6s5nAekQ4QA" target="_blank" class="btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Acesse aqui
                        </a>
                    </div>
                    

                    <div class="card-enhanced p-8 hover-scale">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-gradient-secondary rounded-full flex items-center justify-center mr-6">
                                <i class="fas fa-file-alt text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-2xl mb-2 text-ceara-green">Documentos Importantes</h3>
                                <p class="text-gray-600 font-medium">Atualizado em 04 de fevereiro, 2026</p>
                            </div>
                        </div>
                    
                    
                        <ul class="space-y-4 text-gray-700">
                            <li>
                                <a href="" target="_blank" class="hover:underline font-medium text-ceara-green">
                                    Formulário de Vagas
                                </a>
                            </li>
                            <li>
                                <a href="" target="_blank" class="hover:underline font-medium text-ceara-green">
                                    Formulário de Autorização de Estágio - Maracanaú
                                </a>
                            </li>
                            <li>
                                <a href="" target="_blank" class="hover:underline font-medium text-ceara-green">
                                    Formação Google
                                </a>
                            </li>
                            <li>
                                <a href="assets/docs/PLANO DE RECUPERAÇÃO DE COMPETÊNCIAS - ESTÁGIO 2026 - TRILHA DE SUPORTE.docx.pdf" target="_blank" class="hover:underline font-medium text-ceara-green">
                                    Plano de Recuperação de Competências
                                </a>
                            </li>
                        </ul>
                    </div>



                  
                    <div class="card-enhanced p-8 hover-scale">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-gradient-secondary rounded-full flex items-center justify-center mr-6">
                                <i class="fas fa-cloud text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-2xl mb-2 text-ceara-green">Edital Lab. Software</h3>
                                <p class="text-gray-600 font-medium">05 de março 2026</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-6 leading-relaxed">Leia o Edital</p>
                        <a href="https://docs.google.com/document/d/1XbN9l9V6BWLfxEksFtQUhLcLPDSVBshk/edit?usp=sharing&ouid=114858872030980914619&rtpof=true&sd=true" target="_blank" class="btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Edital
                        </a>
                        <a href="https://drive.google.com/drive/folders/16Ve0RZUB1RKmhL387HGJiHl67Pf4iF0J" target="_blank" class="btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Pasta de envio
                        </a>
                    </div>           
-->                    
                    
                    

                </div>
            </div>
        </section>

        <!-- Seção Galeria -->
        <section id="galeria" class="ghibli-section bg-ghibli-cream">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="ghibli-title text-5xl mb-6">Galeria de Fotos</h2>
                    <p class="text-xl text-ghibli-forest max-w-3xl mx-auto">Registros de momentos importantes da nossa instituição</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <img src="./assets/img/galeria/img1.png" alt="Momento mágico 1"
                        class="ghibli-gallery-img w-full h-64 object-cover">
                    <img src="./assets/img/galeria/img2.png" alt="Momento mágico 2"
                        class="ghibli-gallery-img w-full h-64 object-cover">
                    <img src="./assets/img/galeria/img3.png" alt="Momento mágico 3"
                        class="ghibli-gallery-img w-full h-64 object-cover">
                    <img src="./assets/img/galeria/img4.png" alt="Momento mágico 4"
                        class="ghibli-gallery-img w-full h-64 object-cover">
                    <img src="./assets/img/galeria/img5.png" alt="Momento mágico 5"
                        class="ghibli-gallery-img w-full h-64 object-cover">
                    <img src="./assets/img/galeria/img6.png" alt="Momento mágico 6"
                        class="ghibli-gallery-img w-full h-64 object-cover">
                    <img src="./assets/img/galeria/img7.png" alt="Momento mágico 7"
                        class="ghibli-gallery-img w-full h-64 object-cover">
                    <img src="./assets/img/galeria/img8.png" alt="Momento mágico 8"
                        class="ghibli-gallery-img w-full h-64 object-cover">
                </div>
            </div>
        </section>

        <!-- Modal da Galeria -->
        <div id="imageModal"
            class="fixed inset-0 bg-black/95 hidden items-center justify-center z-50 transition-all duration-300 ease-in-out opacity-0">
            <div class="relative max-w-6xl w-full mx-4">
                <button id="closeModal"
                    class="absolute top-6 right-6 text-white hover:text-ghibli-peach transition-colors duration-200 z-50 bg-black/50 rounded-full p-3 backdrop-blur-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <div class="relative">
                    <img id="modalImage" src="/placeholder.svg" alt="Imagem ampliada"
                        class="w-full h-auto max-h-[85vh] object-contain rounded-xl shadow-2xl">
                    <button id="prevButton"
                        class="absolute left-6 top-1/2 transform -translate-y-1/2 text-white p-4 rounded-full transition-all duration-200 bg-ghibli-gradient magical-glow">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button id="nextButton"
                        class="absolute right-6 top-1/2 transform -translate-y-1/2 text-white p-4 rounded-full transition-all duration-200 bg-ghibli-gradient magical-glow">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Seção Parceiros -->
        <section id="parceiros" class="ghibli-section bg-gradient-to-br from-white to-gray-50 py-24">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="ghibli-title text-5xl mb-6">Nossos Parceiros</h2>
                    <p class="text-xl text-ghibli-forest max-w-3xl mx-auto">Empresas e instituições que confiam no nosso trabalho e contribuem para o sucesso dos nossos alunos</p>
                </div>

                <div class="relative w-full overflow-hidden">
                    <div class="swiper-container parceiros-swiper w-full">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/fenix Soluções.png" alt="Fenix"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                            
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/netway cursoss.png" alt="netway"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/r2_telecom.jpeg" alt="R2"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/softway_fibra.webp" alt="software fibra"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/TRE_CE.png" alt="tre"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/vp23telecomm.png" alt="vp3"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/ifce.png" alt="ifce"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/prefeitura_municial.png" alt="prefeitura"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                           
                            
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/mallory.png" alt="mallory"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/inovax (2).png" alt="inovax"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="flex items-center justify-center p-8">
                                    <img src="./assets/img/logo empresas/cl_delecom.jpeg" alt="Cl-telecom"
                                        class="h-28 md:h-36 w-auto object-contain transition-all duration-300">
                                </div>
                            </div>
                        </div>

                        <!-- Navegação do Swiper -->
                        <div class="swiper-button-next text-ghibli-sage hover:text-ghibli-coral transition-colors duration-300"></div>
                        <div class="swiper-button-prev text-ghibli-sage hover:text-ghibli-coral transition-colors duration-300"></div>

                        <!-- Paginação do Swiper -->

                    </div>
                </div>
            </div>
        </section>

        <!-- Seção Localização -->
        <section id="location" class="ghibli-section bg-ghibli-mist">
            <div class="container mx-auto px-4 text-center">
                <h2 class="ghibli-title text-5xl mb-12">Nossa Localização</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                    <div class="ghibli-card p-0 overflow-hidden">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4727.003608944703!2d-38.67658031627441!3d-3.888242185992357!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x7c0aca1962c7027%3A0xe5da63c87e731b04!2sEscola%20Estadual%20de%20Educa%C3%A7%C3%A3o%20Profissional%20Salaberga%20Torquato%20Gomes%20de%20Matos!5e1!3m2!1spt-BR!2sbr!4v1728757517751!5m2!1spt-BR!2sbr"
                            width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade" class="rounded-xl">
                        </iframe>
                    </div>
                    <div class="ghibli-card p-8">
                        <h3 class="font-ghibli text-2xl font-bold text-ghibli-forest mb-6">Informações de Contato</h3>
                        <ul class="space-y-4">
                            <li class="flex items-center text-ghibli-forest">
                                <i class="fas fa-map-marker-alt text-ghibli-peach mr-4 w-5"></i>
                                <span>Av. Marta Maria Carvalho Nojoza, sn - Outra Banda, Maranguape - CE</span>
                            </li>
                            <li class="flex items-center text-ghibli-forest">
                                <i class="fas fa-phone text-ghibli-peach mr-4 w-5"></i>
                                <span>(85) 3101-2100</span>
                            </li>
                            <li class="flex items-center text-ghibli-forest">
                                <i class="fas fa-envelope text-ghibli-peach mr-4 w-5"></i>
                                <span>eeepsalaberga@escola.ce.gov.br</span>
                            </li>
                        </ul>
                        <div class="mt-8">
                            <a href="https://www.google.com/maps/place/Escola+Estadual+de+Educa%C3%A7%C3%A3o+Profissional+Salaberga+Torquato+Gomes+de+Matos/@-3.888242,-38.6765803,17z/"
                                target="_blank" class="ghibli-btn magical-glow">
                                <i class="fas fa-directions mr-2"></i> Como Chegar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="ghibli-footer text-white py-20">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <div class="md:col-span-4 space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-ghibli-gradient rounded-full flex items-center justify-center magical-glow">
                            <i class="fas fa-seedling text-white text-xl"></i>
                        </div>
                        <span class="font-ghibli text-xl font-bold text-ghibli-sage">Salaberga Mágica</span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        © 2024 EEEP Salaberga Torquato Gomes de Matos. Todos os direitos reservados.
                        Uma escola a educação transforma vidas e constrói futuros brilhantes.
                        Av. Marta Maria Carvalho Nojoza, sn - Outra Banda, Maranguape - CE
                    </p>
                </div>

                <div class="md:col-span-3 space-y-4">
                    <h3 class="text-xl font-semibold font-ghibli text-ghibli-sage">Links </h3>
                    <ul class="space-y-3">
                        <li><a href="#sobre" class="text-gray-400 hover:text-ghibli-peach transition-colors duration-300 flex items-center">
                                <i class="fas fa-book-open mr-3"></i>Nossa História</a>
                        </li>
                        <li><a href="#cursos" class="text-gray-400 hover:text-ghibli-peach transition-colors duration-300 flex items-center">
                                <i class="fas fa-graduation-cap mr-3"></i>Cursos</a>
                        </li>
                        <li><a href="#galeria" class="text-gray-400 hover:text-ghibli-peach transition-colors duration-300 flex items-center">
                                <i class="fas fa-images mr-3"></i>Galeria</a>
                        </li>
                        <li><a href="#location" class="text-gray-400 hover:text-ghibli-peach transition-colors duration-300 flex items-center">
                                <i class="fas fa-map-marker-alt mr-3"></i>Localização</a>
                        </li>
                    </ul>
                </div>

                <div class="md:col-span-4 grid grid-cols-1 sm:grid-cols-2 gap-x-16 gap-y-8">
                    <h3 class="developers-title sm:col-span-2 text-center text-xl font-semibold font-ghibli text-ghibli-sage">Desenvolvedores</h3>
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold font-ghibli text-gray-400">AreaDev 1</h4>
                        <ul class="space-y-3">
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-code text-sm mr-3 text-ghibli-peach"></i>Otavio Menezes</span>
                            </li>
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-code text-sm mr-3 text-ghibli-peach"></i>Matheus Felix</span>
                            </li>
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-code text-sm mr-3 text-ghibli-peach"></i>Lavosier Nascimento</span>
                            </li>
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-code text-sm mr-3 text-ghibli-peach"></i>Roger Cavalcante</span>
                            </li>
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-code text-sm mr-3 text-ghibli-peach"></i>Pedro Uchôa</span>
                            </li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold font-ghibli text-gray-400">AreaDev 2</h4>
                        <ul class="space-y-3">
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-code text-sm mr-3 text-ghibli-peach"></i>Kauan Cristian</span>
                            </li>
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-code text-sm mr-3 text-ghibli-peach"></i>Cassio Holanda</span>
                            </li>
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-code text-sm mr-3 text-ghibli-peach"></i>Abraão Lyncoln</span>
                            </li>
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-code text-sm mr-3 text-ghibli-peach"></i>Armando Filho</span>
                            </li>
                        </ul>
                    </div>

                    <div class="sm:col-span-2 space-y-4">
                        <h3 class="text-xl font-semibold font-ghibli text-ghibli-sage">Colaboradores</h3>
                        <ul class="space-y-3">
                            <li><span class="text-gray-400 flex items-center whitespace-nowrap">
                                    <i class="fas fa-paint-brush text-sm mr-3 text-ghibli-peach"></i>Jefferson Castro</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="md:col-span-1 flex flex-col items-center justify-center space-y-6">
                    <div class="w-20 h-20 bg-ghibli-gradient rounded-full flex items-center justify-center animate-float magical-glow">
                        <i class="fas fa-magic text-white text-2xl"></i>
                    </div>

                </div>
            </div>

            <div class="mt-16 pt-8 border-t border-gray-800 text-center">
                <p class="text-gray-400 text-sm font-ghibli">&copy; 2024 Escola Mágica Salaberga. Onde os sonhos ganham asas.</p>
            </div>
        </div>
    </footer>

    <!-- Botão Fixo de Acessibilidade -->
    <div class="fixed bottom-6 right-6 z-50" x-data="{ accessibilityOpen: false }">
        <!-- O botão circular -->
        <button @click="accessibilityOpen = !accessibilityOpen"
                class="w-14 h-14 bg-ghibli-green rounded-full shadow-lg flex items-center justify-center text-white hover:bg-ghibli-sage transition-colors duration-300 magical-glow"
                aria-label="Opções de acessibilidade"
                :aria-expanded="accessibilityOpen">
            <i class="fas fa-universal-access text-xl"></i>
        </button>

        <!-- O menu de acessibilidade -->
        <div x-show="accessibilityOpen"
             @click.away="accessibilityOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-4"
             class="absolute bottom-full right-0 mb-3 w-72 bg-white rounded-xl shadow-xl py-3 z-50 border border-ghibli-sage/20 origin-bottom-right" style="display: none;" x-cloak>
            <div class="px-4 py-2 border-b border-ghibli-sage/20">
                <span class="block text-sm font-semibold mb-2 text-ghibli-forest">Tamanho do Texto</span>
                <div class="flex gap-2">
                    <button @click="decreaseFontSize()" class="p-2 hover:bg-ghibli-sage/10 rounded-lg transition-colors duration-200" aria-label="Diminuir tamanho do texto">
                        <i class="fas fa-a text-sm"></i><b class="text-xs">-</b>
                    </button>
                    <button @click="resetFontSize()" class="p-2 hover:bg-ghibli-sage/10 rounded-lg transition-colors duration-200" aria-label="Tamanho padrão do texto">
                        <i class="fas fa-a"></i>
                    </button>
                    <button @click="increaseFontSize()" class="p-2 hover:bg-ghibli-sage/10 rounded-lg transition-colors duration-200" aria-label="Aumentar tamanho do texto">
                        <i class="fas fa-a text-lg"></i><b class="text-xs">+</b>
                    </button>
                </div>
            </div>

            <button class="w-full px-4 py-2 text-left hover:bg-ghibli-sage/10 flex items-center gap-2 text-ghibli-forest transition-colors duration-200">
                <i class="fas fa-ear-listen"></i>
                <span>Leitor de Tela</span>
            </button>

            <button class="w-full px-4 py-2 text-left hover:bg-ghibli-sage/10 flex items-center justify-between text-ghibli-forest transition-colors duration-200">
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-half-stroke"></i>
                    <span>Temas de Contraste</span>
                </div>
                <i class="fas fa-chevron-right text-xs"></i>
            </button>
             <!-- Opções de tema de contraste adicionadas aqui -->
             <div x-data="{ contrastThemeOpen: false }" class="relative">
                <button @click="contrastThemeOpen = !contrastThemeOpen" class="w-full px-4 py-2 text-left hover:bg-ghibli-sage/10 flex items-center justify-between text-ghibli-forest transition-colors duration-200" aria-expanded="contrastThemeOpen">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-circle-half-stroke"></i>
                        <span>Temas de Contraste</span>
                    </div>
                    <i class="fas fa-chevron-right text-xs transition-transform duration-300" :class="{ 'rotate-90': contrastThemeOpen }"></i>
                </button>
                <div x-show="contrastThemeOpen" class="absolute right-full bottom-0 mr-2 w-48 bg-white rounded-xl shadow-xl py-2 z-50 border border-ghibli-sage/20 origin-bottom-right" style="display: none;" x-cloak>
                    <button @click="applyTheme('original'); contrastThemeOpen = false" class="w-full px-4 py-2 text-left hover:bg-ghibli-sage/10 text-ghibli-forest transition-colors duration-200">Original</button>
                    <button @click="applyTheme('monochrome'); contrastThemeOpen = false" class="w-full px-4 py-2 text-left hover:bg-ghibli-sage/10 text-ghibli-forest transition-colors duration-200">Monocromático</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Carrossel 3D
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.ghibli-carousel-container');
            const items = document.querySelectorAll('.ghibli-carousel-item');
            const carousel = document.querySelector('.ghibli-carousel');

            let currentIndex = 0;
            const totalItems = items.length;
            const angleStep = 360 / totalItems;
            let autoRotateInterval;
            let isAnimating = false;

            function updateCarousel() {
                if (isAnimating) return;
                isAnimating = true;

                let radius = 500;
                if (window.innerWidth <= 768) {
                    radius = 250;
                } else if (window.innerWidth <= 480) {
                    radius = 180;
                }

                items.forEach((item, index) => {
                    const angle = (index - currentIndex) * angleStep;
                    const x = Math.sin(angle * Math.PI / 180) * radius;
                    const z = Math.cos(angle * Math.PI / 180) * radius - radius;
                    const scale = index === currentIndex ? 1 : Math.max(0.7, 1 - Math.abs(angle) / 180);
                    const opacity = index === currentIndex ? 1 : Math.max(0.3, 1 - Math.abs(angle) / 180);

                    item.style.transform = `translate3d(${x}px, 0, ${z}px) rotateY(${-angle}deg) scale(${scale})`;
                    item.style.opacity = opacity;

                    if (index === currentIndex) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });

                // Removendo atualização de indicadores
                /*
                indicators.forEach((indicator, index) => {
                    if (index === currentIndex) {
                        indicator.classList.add('active');
                    } else {
                        indicator.classList.remove('active');
                    }
                });
                */

                setTimeout(() => {
                    isAnimating = false;
                }, 1500);
            }

            function showNext() {
                if (currentIndex < totalItems - 1) {
                    currentIndex++;
                } else {
                    currentIndex = 0;
                }
                updateCarousel();
            }

            // Adicionando função para voltar
            function showPrev() {
                if (currentIndex > 0) {
                    currentIndex--;
                } else {
                    currentIndex = totalItems - 1;
                }
                updateCarousel();
            }

            function startAutoRotate() {
                autoRotateInterval = setInterval(showNext, 4000);
            }

            // Removendo indicadores clicáveis
            /*
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    currentIndex = index;
                    updateCarousel();
                });
            });
            */

            // Adicionando listeners para os botões de navegação
            const prevButton = carousel.querySelector('.swiper-button-prev');
            const nextButton = carousel.querySelector('.swiper-button-next');

            if (prevButton) {
                prevButton.addEventListener('click', showPrev);
            }

            if (nextButton) {
                nextButton.addEventListener('click', showNext);
            }

            // Suporte para gestos de toque
            let touchStartX = 0;
            let touchEndX = 0;

            carousel.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            });

            carousel.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });

            function handleSwipe() {
                const swipeThreshold = 50;
                const diff = touchStartX - touchEndX;

                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        showNext();
                    } else {
                        showPrev();
                    }
                }
            }

            // Inicializar
            updateCarousel();
            startAutoRotate();

            // Pausar auto-rotação no hover
            carousel.addEventListener('mouseenter', () => {
                clearInterval(autoRotateInterval);
            });

            carousel.addEventListener('mouseleave', () => {
                startAutoRotate();
            });

            // Atualizar carrossel ao redimensionar a janela
            window.addEventListener('resize', updateCarousel);
        });

        // Modal da galeria
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const closeButton = document.getElementById('closeModal');
            const prevButton = document.getElementById('prevButton');
            const nextButton = document.getElementById('nextButton');
            const galleryImages = document.querySelectorAll('.ghibli-gallery-img');
            let currentIndex = 0;

            function openModal(index) {
                if (modal && modalImage && galleryImages[index]) {
                    currentIndex = index;
                    updateModalImage();
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.style.overflow = 'hidden';

                    setTimeout(() => {
                        modal.classList.add('opacity-100');
                        modal.classList.remove('opacity-0');
                    }, 10);
                }
            }

            function closeModal() {
                if (modal) {
                    modal.classList.add('opacity-0');
                    modal.classList.remove('opacity-100');

                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        document.body.style.overflow = '';
                    }, 300);
                }
            }

            function updateModalImage() {
                if (modalImage && galleryImages[currentIndex]) {
                    const newSrc = galleryImages[currentIndex].src;
                    const newAlt = galleryImages[currentIndex].alt;

                    modalImage.style.opacity = '0';
                    setTimeout(() => {
                        modalImage.src = newSrc;
                        modalImage.alt = newAlt;
                        modalImage.style.opacity = '1';
                    }, 200);
                }
            }

            function nextImage() {
                currentIndex = (currentIndex + 1) % galleryImages.length;
                updateModalImage();
            }

            function prevImage() {
                currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
                updateModalImage();
            }

            galleryImages.forEach((img, index) => {
                img.addEventListener('click', () => openModal(index));
            });

            if (closeButton) {
                closeButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    closeModal();
                });
            }

            if (prevButton) {
                prevButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    prevImage();
                });
            }

            if (nextButton) {
                nextButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    nextImage();
                });
            }

            modal?.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (!modal?.classList.contains('hidden')) {
                    switch (e.key) {
                        case 'Escape':
                            closeModal();
                            break;
                        case 'ArrowLeft':
                            prevImage();
                            break;
                        case 'ArrowRight':
                            nextImage();
                            break;
                    }
                }
            });
        });

        // Animações de scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observar todas as seções
        document.querySelectorAll('section').forEach(section => {
            observer.observe(section);
        });

        // Scroll suave para links âncora
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Destacar link ativo na navegação
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            const scrollPos = window.scrollY + 100;

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');
                const navLink = document.querySelector(`a[href="#${sectionId}"]`);

                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                    });
                    if (navLink) {
                        navLink.classList.add('active');
                    }
                }
            });
        });

        // Aplicar temas de acessibilidade
        function applyTheme(theme) {
            const html = document.documentElement;

            html.classList.remove('theme-monochrome');

            switch (theme) {
                case 'monochrome':
                    html.classList.add('theme-monochrome');
                    document.body.style.filter = 'grayscale(100%)';
                    break;
                case 'original':
                default:
                    document.body.style.filter = 'none';
                    break;
            }
        }

        // Inicializar botões de tema
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-theme]').forEach(button => {
                button.addEventListener('click', function() {
                    const theme = this.getAttribute('data-theme');
                    applyTheme(theme);
                });
            });
        });

        // Animação de loading
        window.addEventListener('load', () => {
            document.body.classList.add('loaded');
        });

        // Inicialização do Swiper para parceiros
        document.addEventListener('DOMContentLoaded', function() {
            const parceirosSwiper = new Swiper('.parceiros-swiper', {
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                slidesPerView: 'auto',
                spaceBetween: 50,
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                coverflowEffect: {
                    rotate: 15,
                    stretch: 0,
                    depth: 150,
                    modifier: 1,
                    slideShadows: false,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 3,
                        spaceBetween: 50,
                    },
                    768: {
                        slidesPerView: 4,
                        spaceBetween: 50,
                    },
                    1024: {
                        slidesPerView: 5,
                        spaceBetween: 50,
                    },
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
        });
    </script>

    <!-- Link para o JS do Swiper -->
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

    <script>
        // Inicialização do Swiper para parceiros
        const parceirosSwiper = new Swiper('.parceiros-swiper', {
            slidesPerView: 2,
            spaceBetween: 50,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            breakpoints: {
                640: {
                    slidesPerView: 3,
                    spaceBetween: 50,
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: 50,
                },
                1024: {
                    slidesPerView: 5,
                    spaceBetween: 50,
                },
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'coverflow',
            coverflowEffect: {
                rotate: 0,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: false,
            },
        });
    </script>
</body>

</html>