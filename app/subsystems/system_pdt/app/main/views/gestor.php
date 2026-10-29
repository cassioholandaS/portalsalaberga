<?php

    session_start();

    if (!isset($_SESSION['id_usuario'])) {
        header("Location: ../index.php");
        exit;
    }

    require_once("../models/turmas_model.php");
    require_once("../models/usuario_model.php");

    $modelTurma = new TurmasModel();
    $modelUsuario = new usuario_model();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'gestor') {
        $turmas = $modelTurma->listarTurmas();
    } else {
        $turmas = $modelTurma->listarTurmas($_SESSION['id_usuario']);
    }

    $usuarios = $modelUsuario->listar_usuarios();
    $professores = [];
    
    foreach($usuarios as $u){
        if(isset($u['tipo'])){
            $tipo = strtolower($u['tipo']);
            if ($tipo === 'professor') {
                $professores[] = $u;
            }
        }
    }

?>
<script>window.csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;</script>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Professor Diretor</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <style>
        :root{
            --c1: #005A24;
            --c2: #007A33;
            --c3: #4CAF50;
            --c4: #8BC34A;
            --gold: #FFD700;
            --amber: #FF8C00;
            --peach: #FFB74D;
            --bg: #020E06;
            --surface: #061409;
            --surface2: #091C0D;
            --border: rgba(76,175,80,0.14);
            --text: #F4FFF4;
            --text-soft: rgba(255,255,255,0.72);
            --text-muted: rgba(255,255,255,0.45);
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Inter', sans-serif;
        }

        body{
            background:
                radial-gradient(circle at top left, rgba(76,175,80,0.18), transparent 28%),
                radial-gradient(circle at bottom right, rgba(255,215,0,0.12), transparent 24%),
                var(--bg);
            overflow-x:hidden;
            color: var(--text);
        }

        ::selection{
            background: rgba(76,175,80,0.32);
            color: #fff;
        }

        .topbar-panel{
            background: rgba(2,14,6,0.86);
            backdrop-filter: blur(16px);
            border-color: var(--border);
        }

        .main-shell{
            background: transparent;
        }

        .card-dashboard,
        .card-aluno,
        .bg-white.border.border-gray-200,
        .bg-white,
        .modal-card{
            background: var(--surface2) !important;
            border-color: var(--border) !important;
            color: var(--text) !important;
            box-shadow: 0 10px 35px rgba(0,0,0,0.16);
        }

        .card-dashboard,
        .card-aluno{
            transition: box-shadow .25s ease, background-color .18s ease, transform .18s ease;
            border-radius: 22px;
            padding: 24px;
        }

        .card-dashboard:hover,
        .card-aluno:hover{
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(0,0,0,0.24);
        }

        button.bg-green-700,
        button.bg-green-700:hover,
        button.bg-green-800,
        button.bg-green-800:hover,
        .bg-green-700,
        .bg-green-800{
            background: linear-gradient(135deg, var(--c2), var(--c3)) !important;
            color: #fff !important;
            border: none !important;
        }

        button.bg-red-600,
        button.bg-red-700,
        .bg-red-600,
        .bg-red-700{
            background: linear-gradient(135deg, #b71c1c, #e53935) !important;
            color: #fff !important;
            border: none !important;
        }

        button.bg-orange-500,
        button.bg-orange-600,
        .bg-orange-500,
        .bg-orange-600{
            background: linear-gradient(135deg, var(--amber), #FFA726) !important;
            color: #fff !important;
            border: none !important;
        }

        .text-gray-500,
        .text-gray-400,
        .text-gray-600{
            color: var(--text-soft) !important;
        }

        .text-gray-800,
        .text-gray-700{
            color: var(--text) !important;
        }

        input,
        select,
        textarea{
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: rgba(255,255,255,0.04) !important;
            border-color: var(--border) !important;
            color: var(--text) !important;
        }

        select{
            color: var(--text) !important;
            background: rgba(255,255,255,0.04) !important;
        }

        select option{
            color: var(--text) !important;
            background: rgba(2,14,6,0.98) !important;
        }

        select::-ms-expand {
            filter: invert(1);
        }

        .bg-gray-50,
        .bg-gray-100,
        .bg-gray-200,
        .bg-gray-300{
            background: rgba(255,255,255,0.04) !important;
        }

        .border-gray-200{
            border-color: var(--border) !important;
        }

        input::placeholder,
        textarea::placeholder{
            color: rgba(255,255,255,0.35) !important;
        }

        input:focus,
        select:focus,
        textarea:focus{
            border-color: var(--c3) !important;
            box-shadow: 0 0 0 3px rgba(76,175,80,0.14) !important;
        }

        .modal-card{
            animation: fade .22s ease-out both;
        }

        .btn-editar-aluno{
            background: rgba(255,215,0,0.14) !important;
            border-color: rgba(255,215,0,0.24) !important;
            color: var(--gold) !important;
        }

        .btn-editar-aluno:hover{
            background: rgba(255,215,0,0.2) !important;
        }

        .btn-salvar-editar{
            background: linear-gradient(135deg, var(--amber), #ffb74d) !important;
            color: white !important;
            border: none !important;
        }

        .btn-salvar-editar:hover{
            filter: brightness(1.06);
        }

        @keyframes fade {
            from{
                opacity: 0;
                transform: translateY(10px) scale(.98);
            }

            to{
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/system-pdt.css?v=35">
    <script src="../assets/js/system-pdt-ui.js?v=2" defer></script>
</head>
<body class="pdt-interface">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside id="pdtSidebar" class="pdt-sidebar sidebar-panel border-r fixed h-screen px-6 py-8 flex flex-col justify-between">

        <div>
            <div class="pdt-brand flex items-center gap-3">
                <span class="pdt-brand-mark"><img src="../../../../entradasaida/app/main/assets/img/logo.png" alt="" class="pdt-brand-logo"></span>
                <div>
                    <h1 class="font-bold text-[18px]">Sistema <span>Salaberga</span></h1>
                    <p>Professor Diretor de Turma</p>
                </div>
            </div>

            <nav>
                <button type="button" data-section="sec-inicio-gestor" class="menu-item active flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar effect-bar-select">
                    <i class="bi bi-house-door"></i>
                    <span>Início</span>
                </button>
                <button type="button" data-section="sec-turmas-gestor" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
                    <i class="bi bi-journal-bookmark"></i>
                    <span>Turmas</span>
                </button>
                <button type="button" data-section="sec-ocorrencias-gestor" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Ocorrências</span>
                </button>
                <button type="button" data-section="sec-relatorios-gestor" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
                    <i class="bi bi-bar-chart"></i>
                    <span>Indicadores</span>
                </button>
                <!-- <button class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition text-gray-600">
                    <i class="bi bi-gear"></i>
                    <span>Configurações</span>
                </button> -->
            </nav>
        </div>

        <div class="w-full">
        <div class="pdt-account-tools">
            <div class="pdt-account-user">
                <span class="pdt-account-avatar"><?= htmlspecialchars(substr((string) ($_SESSION['nome'] ?? 'U'), 0, 1), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="pdt-account-details">
                    <span><?= htmlspecialchars((string) ($_SESSION['nome'] ?? 'Usuário'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="pdt-account-role"><?= htmlspecialchars(ucfirst((string) ($_SESSION['tipo'] ?? 'gestor')), ENT_QUOTES, 'UTF-8') ?></span>
                </span>
            </div>
            <button id="btnNotificacoes" class="pdt-notifications-toggle" type="button" aria-label="Notificações" aria-controls="notificacoesPanel" aria-expanded="false">
                <i class="bi bi-bell" aria-hidden="true"></i>
                <span id="contadorNotificacoes" class="absolute -right-2 -top-2 hidden min-w-5 rounded-full bg-red-600 px-1 text-xs text-white"></span>
            </button>
            <div id="notificacoesPanel" class="pdt-notifications-panel hidden rounded-3xl border border-slate-700 bg-slate-950 p-4 shadow-2xl text-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold">Notificações</span>
                    <button id="fecharNotificacoes" class="text-slate-400 hover:text-white text-sm">Fechar</button>
                </div>
                <div id="listaNotificacoes" class="max-h-96 space-y-3 overflow-y-auto text-sm text-slate-300">
                    <p>Nenhuma notificação no momento.</p>
                </div>
            </div>
        </div>

        <form action="../controllers/usuario_controller.php?action=logout" method="POST" class="m-0 p-0">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <button type="submit" class="pdt-logout-button">
                <i class="bi bi-box-arrow-right icon-nav-sair"></i>
                <span>Sair</span>
            </button>
        </form>
        </div>

    </aside>
    <button class="pdt-sidebar-collapse-toggle" type="button" aria-controls="pdtSidebar" aria-label="Recolher barra lateral" aria-expanded="true" title="Recolher barra lateral">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
    </button>

    <!-- CONTEÚDO -->
    <div class="pdt-main-offset flex-1 lg:ml-[280px]">
        <!-- MAIN -->
        <main class="pdt-content p-8 main-shell">

            <!-- SEÇÃO INÍCIO -->
            <section id="sec-inicio-gestor" class="secs gestor-section">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        Bem-vindo(a), <?= htmlspecialchars((string) $_SESSION['nome'], ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <p class="text-gray-500">
                        Visão geral da gestão escolar e atalhos para as atividades do sistema.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                    <div class="card-dashboard">
                        <p class="text-gray-500 text-sm mb-2">Turmas</p>
                        <h2 id="contadorAlunos" class="text-4xl font-bold text-gray-800">0</h2>
                        <span class="text-gray-400 text-sm">Cadastradas</span>
                    </div>
                    <div class="card-dashboard">
                        <p class="text-gray-500 text-sm mb-2">Alunos</p>
                        <h2 id="gestorTotalAlunos" class="text-4xl font-bold text-gray-800">0</h2>
                        <span class="text-gray-400 text-sm">Matriculados nas turmas</span>
                    </div>
                    <div class="card-dashboard">
                        <p class="text-gray-500 text-sm mb-2">Ocorrências</p>
                        <h2 id="gestorTotalOcorrencias" class="text-4xl font-bold text-gray-800">0</h2>
                        <span class="text-gray-400 text-sm">No período selecionado</span>
                    </div>
                    <div class="card-dashboard">
                        <p class="text-gray-500 text-sm mb-2">Frequência média</p>
                        <h2 id="gestorMediaFrequencia" class="text-4xl font-bold text-gray-800">0%</h2>
                        <span class="text-gray-400 text-sm">No período selecionado</span>
                    </div>
                </div>
                <div class="grid gap-6 xl:grid-cols-2">
                    <article class="card-dashboard">
                        <h2 class="mb-2 text-xl font-bold text-gray-800">Atenção pedagógica</h2>
                        <p class="text-gray-500">Acompanhamentos em aberto: <strong id="gestorAcompanhamentosAbertos" class="text-gray-800">0</strong></p>
                        <p class="mt-2 text-gray-500">Conselhos registrados no período: <strong id="gestorTotalConselhos" class="text-gray-800">0</strong></p>
                    </article>
                    <article class="card-dashboard">
                        <h2 class="mb-2 text-xl font-bold text-gray-800">Acesso rápido</h2>
                        <p class="text-gray-500">Gerencie turmas, consulte ocorrências de toda a escola e acompanhe os indicadores gerais.</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button type="button" data-go-section="sec-turmas-gestor" class="rounded-xl bg-green-700 px-4 py-2 font-medium text-white">Gerenciar turmas</button>
                            <button type="button" data-go-section="sec-ocorrencias-gestor" class="rounded-xl border border-gray-200 px-4 py-2 font-medium text-gray-800">Ver ocorrências</button>
                        </div>
                    </article>
                </div>
            </section>

            <!-- SEÇÃO TURMAS -->
            <section id="sec-turmas-gestor" class="secs gestor-section hidden">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">
                            Gestão de Turmas
                        </h1>
                        <p class="text-gray-500 mt-1">
                            Crie e gerencie as turmas do sistema.
                        </p>
                    </div>
                    <button
                        id="btnAbrirModalTurma"
                        class="bg-green-700 hover:bg-green-800 text-white h-12 px-6 rounded-2xl flex items-center gap-2 font-medium transition"
                    >
                        <i class="bi bi-plus-lg"></i>
                        Criar turma
                    </button>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
                    <div class="relative w-full lg:max-w-md">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            id="buscarTurma"
                            placeholder="Buscar turma..."
                            class="w-full border border-gray-200 rounded-xl h-12 pl-12 pr-4 outline-none"
                        >
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="font-semibold text-lg text-gray-700">
                        Total de turmas:
                        <span id="contadorTurmas" class="text-green-700">0</span>
                    </h2>
                </div>

                <div id="listaTurmas" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"></div>

                <!-- MODAL CRIAR/EDITAR TURMA -->
                <div id="overlayModalTurma" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[300] p-4 backdrop-blur-[2px]">
                    <div id="modalTurma" class="bg-white w-full max-w-2xl rounded-[32px] p-8 relative shadow-2xl animate-[fade_.2s_ease]">
                        <button id="fecharModalTurma" class="absolute top-5 right-5 w-11 h-11 rounded-full hover:bg-gray-100 transition text-gray-600">
                            <i class="bi bi-x-lg"></i>
                        </button>

                        <div class="mb-8">
                            <div class="w-16 h-16 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center text-2xl mb-5">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">
                                Criar turma
                            </h2>
                            <p class="text-gray-500">
                                Cadastre uma nova turma no sistema.
                            </p>
                        </div>

                        <form id="formCadastrarTurma" class="space-y-4">
                            <input type="hidden" id="idTurmaEdicao" value="">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-200 mb-2">
                                        Turma
                                    </label>
                                    <select
                                        name="id_curso"
                                        id="cursoTurma"
                                        class="w-full h-12 border border-slate-700 rounded-xl px-4 outline-none focus:border-emerald-500 bg-slate-900 text-white transition"
                                    >
                                        <option value="">Selecione</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-200 mb-2">
                                        Série
                                    </label>
                                    <select
                                        name="serie_turma"
                                        id="serieTurma"
                                        class="w-full h-12 border border-slate-700 rounded-xl px-4 outline-none focus:border-emerald-500 bg-slate-900 text-white transition"
                                    >
                                        <option value="">Selecione</option>
                                        <option value="1">1º Ano</option>
                                        <option value="2">2º Ano</option>
                                        <option value="3">3º Ano</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-200 mb-2">
                                        Sala
                                    </label>
                                    <input
                                        type="text"
                                        name="sala_turma"
                                        id="salaTurma"
                                        placeholder="Ex: 04"
                                        class="w-full h-12 border border-slate-700 rounded-xl px-4 outline-none focus:border-emerald-500 bg-slate-900 text-white transition"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-200 mb-2">
                                        Ano letivo
                                    </label>
                                    <div class="pdt-year-field w-full h-12 border border-slate-700 rounded-xl px-4 flex items-center font-semibold">
                                        2026
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-2">
                                    Professor responsável
                                </label>
                                <select
                                    name="id_professor"
                                    id="selectProfessor"
                                    class="w-full h-12 border border-slate-700 rounded-xl px-4 outline-none focus:border-emerald-500 bg-slate-900 text-white transition"
                                >
                                    <option value="">Selecione um professor</option>
                                    <?php foreach($professores as $prof): ?>
                                        <option value="<?= $prof['id'] ?>"><?= htmlspecialchars($prof['nome']) ?> (<?= htmlspecialchars($prof['matricula']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-2">
                                    Observações
                                </label>
                                <textarea
                                    name="descricao_turma"
                                    id="descricaoTurma"
                                    rows="4"
                                    placeholder="Informações adicionais sobre a turma..."
                                    class="w-full border border-slate-700 rounded-2xl p-4 outline-none resize-none focus:border-emerald-500 bg-slate-900 text-white transition"
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4">
                                <button
                                    type="button"
                                    id="cancelarModalTurma"
                                    class="h-12 px-6 rounded-xl border border-gray-200 hover:bg-gray-100 transition font-medium"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    type="submit" 
                                    class="px-5 py-2.5 bg-green-700 hover:bg-green-800 transition text-white font-medium rounded-xl text-sm shadow-sm"
                                >
                                    Salvar Turma
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- MODAL CURSO C -->
                <div id="overlayModalSubcursoC" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[400] p-4 backdrop-blur-[2px]">
                    <div class="bg-white w-full max-w-md rounded-[32px] p-8 relative shadow-2xl animate-[fade_.2s_ease]">
                        <button id="fecharModalSubcursoC" class="absolute top-5 right-5 w-11 h-11 rounded-full hover:bg-gray-100 transition text-gray-600">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">Selecione o tipo de curso</h2>
                            <p class="text-gray-500">Curso C possui duas modalidades</p>
                        </div>
                        <div class="flex gap-4">
                            <button
                                type="button"
                                id="btnAdministracao"
                                class="flex-1 py-3 px-4 bg-green-100 hover:bg-green-200 text-green-700 font-semibold rounded-xl transition"
                            >
                                Administração
                            </button>
                            <button
                                type="button"
                                id="btnMeioAmbiente"
                                class="flex-1 py-3 px-4 bg-green-100 hover:bg-green-200 text-green-700 font-semibold rounded-xl transition"
                            >
                                Meio Ambiente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MODAL DESVINCULAÇÃO -->
                <div id="overlayModalDesvinculaTurma" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[400] p-4 backdrop-blur-[2px]">
                    <div class="bg-white w-full max-w-md rounded-[32px] p-8 relative shadow-2xl animate-[fade_.2s_ease]">
                        <button id="fecharModalDesvincula" class="absolute top-5 right-5 w-11 h-11 rounded-full hover:bg-gray-100 transition text-gray-600">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <div class="text-center mb-8">
                            <div class="w-16 h-16 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center text-2xl mx-auto mb-5">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">Desvincular turma</h2>
                            <p class="text-gray-500 text-sm">Esta ação não pode ser desfeita</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 mb-6 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 text-sm">Turma</span>
                                <span id="nomeTurmaDesvincula" class="font-semibold text-gray-800">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 text-sm">Professor</span>
                                <span id="professorDesvincula" class="font-semibold text-gray-800">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 text-sm">Série</span>
                                <span id="serieDesvincula" class="font-semibold text-gray-800">-</span>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button
                                type="button"
                                id="cancelarModalDesvincula"
                                class="flex-1 py-3 px-4 border border-gray-200 hover:bg-gray-100 text-gray-700 font-semibold rounded-xl transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                id="confirmarDesvincula"
                                class="flex-1 py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition"
                            >
                                Desvincular
                            </button>
                        </div>
                    </div>
                </div>

            </section>

            <section id="sec-ocorrencias-gestor" class="secs gestor-section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Ocorrências da escola</h1>
                    <p class="mt-1 text-gray-500">Consulte ocorrências ativas registradas pelos professores em todas as turmas.</p>
                </div>
                <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 lg:flex-row lg:items-center lg:justify-between">
                    <label class="sr-only" for="buscarOcorrenciasGestor">Buscar ocorrência</label>
                    <input id="buscarOcorrenciasGestor" type="search" placeholder="Buscar por aluno, turma, professor ou tipo" class="h-12 w-full rounded-xl border border-gray-200 px-4 outline-none lg:max-w-xl">
                    <div class="flex gap-3">
                        <label class="sr-only" for="periodoOcorrenciasGestor">Período</label>
                        <select id="periodoOcorrenciasGestor" class="h-12 rounded-xl border border-gray-200 px-4">
                            <option value="mes">Este mês</option>
                            <option value="trimestre">Este trimestre</option>
                            <option value="semestre">Este semestre</option>
                            <option value="ano">Este ano</option>
                        </select>
                        <button id="atualizarOcorrenciasGestor" type="button" class="h-12 rounded-xl bg-green-700 px-4 font-medium text-white" aria-label="Atualizar ocorrências">
                            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <p id="totalOcorrenciasGestor" class="mb-4 text-sm text-gray-500" aria-live="polite"></p>
                <div id="listaOcorrenciasGestor" class="grid grid-cols-1 gap-4 xl:grid-cols-2" aria-live="polite">
                    <p class="text-gray-500">Carregando ocorrências...</p>
                </div>
            </section>

            <section id="sec-relatorios-gestor" class="secs gestor-section hidden">
                <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Indicadores gerais</h1>
                        <p class="mt-1 text-gray-500">Resumo escolar e distribuição das ocorrências no período.</p>
                    </div>
                    <div class="flex gap-3">
                        <label class="sr-only" for="periodoRelatoriosGestor">Período</label>
                        <select id="periodoRelatoriosGestor" class="h-12 rounded-xl border border-gray-200 px-4">
                            <option value="mes">Este mês</option>
                            <option value="trimestre">Últimos 3 meses</option>
                            <option value="semestre">Últimos 6 meses</option>
                            <option value="ano">Este ano</option>
                        </select>
                        <button id="atualizarRelatoriosGestor" type="button" class="h-12 rounded-xl bg-green-700 px-4 font-medium text-white">Atualizar</button>
                    </div>
                </div>
                <div class="mb-4 text-sm text-gray-500">Período: <span id="periodoResumoGestor">Este mês</span></div>
                <div class="mb-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="card-dashboard"><p class="text-sm text-gray-500">Turmas</p><strong id="relatorioTurmasGestor" class="mt-2 block text-3xl text-gray-800">0</strong></article>
                    <article class="card-dashboard"><p class="text-sm text-gray-500">Alunos</p><strong id="relatorioAlunosGestor" class="mt-2 block text-3xl text-gray-800">0</strong></article>
                    <article class="card-dashboard"><p class="text-sm text-gray-500">Ocorrências no período</p><strong id="relatorioOcorrenciasGestor" class="mt-2 block text-3xl text-gray-800">0</strong></article>
                    <article class="card-dashboard"><p class="text-sm text-gray-500">Frequência média</p><strong id="relatorioFrequenciaGestor" class="mt-2 block text-3xl text-gray-800">0%</strong></article>
                    <article class="card-dashboard"><p class="text-sm text-gray-500">Acompanhamentos</p><strong id="relatorioAcompanhamentosGestor" class="mt-2 block text-3xl text-gray-800">0</strong></article>
                    <article class="card-dashboard"><p class="text-sm text-gray-500">Acompanhamentos em aberto</p><strong id="relatorioAbertosGestor" class="mt-2 block text-3xl text-gray-800">0</strong></article>
                    <article class="card-dashboard"><p class="text-sm text-gray-500">Conselhos</p><strong id="relatorioConselhosGestor" class="mt-2 block text-3xl text-gray-800">0</strong></article>
                </div>
                <article class="rounded-2xl border border-gray-200 bg-white p-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-800">Ocorrências por tipo</h2>
                    <div id="ocorrenciasPorTipoGestor" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3" aria-live="polite">
                        <p class="text-gray-500">Carregando indicadores...</p>
                    </div>
                </article>
            </section>

        </main>

    </div>

</div>

<script>
const originalFetch = window.fetch.bind(window);
window.fetch = function(url, options = {}) {
    const method = (options?.method || 'GET').toUpperCase();
    const headers = new Headers(options?.headers || {});
    if (window.csrfToken && !headers.has('X-CSRF-Token')) {
        headers.set('X-CSRF-Token', window.csrfToken);
    }
    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method) && options?.body && typeof options.body === 'string' && !options.body.includes('csrf_token')) {
        try {
            const parsed = JSON.parse(options.body);
            if (parsed && typeof parsed === 'object' && !('csrf_token' in parsed)) {
                parsed.csrf_token = window.csrfToken;
                options = { ...options, body: JSON.stringify(parsed) };
            }
        } catch (e) {}
    }
    return originalFetch(url, { ...options, headers });
};

async function carregarNotificacoes() {
    const lista = document.getElementById('listaNotificacoes');
    const contador = document.getElementById('contadorNotificacoes');
    if (!lista) return;
    try {
        const res = await fetch('../controllers/notificacao_controller.php?action=listar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ csrf_token: window.csrfToken })
        });
        const data = await res.json();
        if (!data?.sucesso) return;
        const itens = data.dados || [];
        if (contador) {
            const total = data.nao_lidas || 0;
            contador.textContent = total > 0 ? total : '';
            contador.classList.toggle('hidden', total <= 0);
        }
        lista.replaceChildren();
        if (!itens.length) {
            const vazio = document.createElement('p');
            vazio.className = 'text-slate-400';
            vazio.textContent = 'Nenhuma notificação';
            lista.append(vazio);
            return;
        }
        itens.forEach(item => {
            const card = document.createElement('article');
            card.className = `rounded-2xl border ${item.lida ? 'border-slate-800 bg-slate-900/70' : 'border-emerald-500/30 bg-emerald-500/10'} p-3`;
            const titulo = document.createElement('p');
            titulo.className = 'font-semibold text-white';
            titulo.textContent = item.titulo || '';
            const mensagem = document.createElement('p');
            mensagem.className = 'mt-1 text-slate-300';
            mensagem.textContent = item.mensagem || '';
            const data = document.createElement('p');
            data.className = 'mt-2 text-[11px] text-slate-500';
            data.textContent = item.criado_em || '';
            card.append(titulo, mensagem, data);
            lista.append(card);
        });
    } catch (e) {
        console.error('Erro ao carregar notificações:', e);
    }
}

document.addEventListener("DOMContentLoaded", function() {

    // ================================
    // MENU E NAVEGAÇÃO
    // ================================
    const btnOptionsPdt = document.querySelectorAll(".menu-item[data-section]");
    const secoesGestor = document.querySelectorAll(".gestor-section");
    const sidebarGestor = document.querySelector(".pdt-sidebar");

    function abrirSecaoGestor(sectionId) {
        const secao = document.getElementById(sectionId);
        if (!secao) return;
        secoesGestor.forEach(item => item.classList.toggle("hidden", item !== secao));
        btnOptionsPdt.forEach(item => {
            const ativo = item.dataset.section === sectionId;
            item.classList.toggle("active", ativo);
            item.classList.toggle("effect-bar-select", ativo);
        });
        sidebarGestor?.classList.remove("is-open");
        if (sectionId === "sec-ocorrencias-gestor") carregarOcorrenciasGestor();
        if (sectionId === "sec-relatorios-gestor") carregarRelatoriosGestor();
    }

    function elementoTextoGestor(tag, className, text) {
        const elemento = document.createElement(tag);
        elemento.className = className;
        elemento.textContent = text ?? "";
        return elemento;
    }

    async function carregarOcorrenciasGestor() {
        const lista = document.getElementById("listaOcorrenciasGestor");
        const total = document.getElementById("totalOcorrenciasGestor");
        const filtro = document.getElementById("periodoOcorrenciasGestor")?.value || "mes";
        if (!lista) return;
        lista.replaceChildren(elementoTextoGestor("p", "text-gray-500", "Carregando ocorrências..."));

        try {
            const resposta = await fetch(`../controllers/ocorrencia_controller.php?action=listar_ocorrencias&filtro=${encodeURIComponent(filtro)}`);
            const resultado = await resposta.json();
            if (!resposta.ok || !resultado.sucesso) {
                throw new Error(resultado.erro || "Não foi possível carregar as ocorrências.");
            }

            const ocorrencias = Array.isArray(resultado.dados) ? resultado.dados : [];
            const busca = (document.getElementById("buscarOcorrenciasGestor")?.value || "").trim().toLocaleLowerCase("pt-BR");
            const filtradas = ocorrencias.filter(ocorrencia => [
                ocorrencia.tipo_ocorrencia,
                ocorrencia.nome_aluno,
                ocorrencia.matricula,
                ocorrencia.nome_turma,
                ocorrencia.professor_nome,
                ocorrencia.descricao
            ].some(valor => String(valor ?? "").toLocaleLowerCase("pt-BR").includes(busca)));

            if (total) {
                total.textContent = ocorrencias.length
                    ? `Exibindo ${filtradas.length} de ${ocorrencias.length} ocorrências mais recentes.`
                    : "Nenhuma ocorrência registrada neste período.";
            }
            lista.replaceChildren();
            if (!filtradas.length) {
                lista.append(elementoTextoGestor(
                    "p",
                    "col-span-full rounded-2xl border border-gray-200 bg-white p-6 text-gray-500",
                    busca ? "Nenhuma ocorrência corresponde à busca." : "Nenhuma ocorrência registrada neste período."
                ));
                return;
            }

            filtradas.forEach(ocorrencia => {
                const card = document.createElement("article");
                card.className = "rounded-2xl border border-gray-200 bg-white p-5";
                const topo = document.createElement("div");
                topo.className = "flex flex-wrap items-start justify-between gap-3";
                topo.append(
                    elementoTextoGestor("h2", "text-lg font-semibold text-gray-800", ocorrencia.tipo_ocorrencia || "Ocorrência"),
                    elementoTextoGestor("time", "text-sm text-gray-500", ocorrencia.data_ocorrencia || "Data não informada")
                );
                card.append(
                    topo,
                    elementoTextoGestor("p", "mt-3 font-medium text-gray-800", ocorrencia.nome_aluno || "Aluno não informado"),
                    elementoTextoGestor("p", "mt-1 text-sm text-gray-500", `Turma: ${ocorrencia.serie_turma || ""}º ${ocorrencia.nome_turma || "não informada"} · Matrícula: ${ocorrencia.matricula || "—"}`),
                    elementoTextoGestor("p", "mt-1 text-sm text-gray-500", `Registrada por: ${ocorrencia.professor_nome || "não informado"}`),
                    elementoTextoGestor("p", "mt-3 whitespace-pre-wrap text-sm text-gray-600", ocorrencia.descricao || "Sem descrição.")
                );
                lista.append(card);
            });
        } catch (error) {
            console.error("Erro ao carregar ocorrências do gestor:", error);
            if (total) total.textContent = "Falha ao consultar ocorrências.";
            lista.replaceChildren(elementoTextoGestor("p", "col-span-full rounded-2xl border border-red-200 bg-red-50 p-6 text-red-700", error.message));
        }
    }

    async function carregarRelatoriosGestor() {
        const periodo = document.getElementById("periodoRelatoriosGestor")?.value || "mes";
        const listaTipos = document.getElementById("ocorrenciasPorTipoGestor");
        const resumoPeriodo = document.getElementById("periodoResumoGestor");
        if (resumoPeriodo) {
            resumoPeriodo.textContent = document.getElementById("periodoRelatoriosGestor")?.selectedOptions?.[0]?.textContent || "Este mês";
        }
        if (listaTipos) listaTipos.replaceChildren(elementoTextoGestor("p", "text-gray-500", "Carregando indicadores..."));

        try {
            const resposta = await fetch(`../controllers/relatorio_controller.php?action=resumo_geral&periodo=${encodeURIComponent(periodo)}`);
            const dados = await resposta.json();
            if (!resposta.ok || !dados.sucesso) {
                throw new Error(dados.erro || "Não foi possível carregar os indicadores.");
            }

            const valores = {
                gestorTotalAlunos: dados.total_alunos ?? 0,
                gestorTotalOcorrencias: dados.total_ocorrencias_periodo ?? 0,
                gestorMediaFrequencia: `${dados.media_frequencia_periodo ?? 0}%`,
                gestorAcompanhamentosAbertos: dados.acompanhamentos_abertos ?? 0,
                gestorTotalConselhos: dados.conselhos_periodo ?? 0,
                relatorioTurmasGestor: dados.total_turmas ?? 0,
                relatorioAlunosGestor: dados.total_alunos ?? 0,
                relatorioOcorrenciasGestor: dados.total_ocorrencias_periodo ?? 0,
                relatorioFrequenciaGestor: `${dados.media_frequencia_periodo ?? 0}%`,
                relatorioAcompanhamentosGestor: dados.acompanhamentos_periodo ?? 0,
                relatorioAbertosGestor: dados.acompanhamentos_abertos ?? 0,
                relatorioConselhosGestor: dados.conselhos_periodo ?? 0
            };
            Object.entries(valores).forEach(([id, valor]) => {
                const elemento = document.getElementById(id);
                if (elemento) elemento.textContent = String(valor);
            });

            if (!listaTipos) return;
            listaTipos.replaceChildren();
            const tipos = Array.isArray(dados.ocorrencias_por_tipo) ? dados.ocorrencias_por_tipo : [];
            if (!tipos.length) {
                listaTipos.append(elementoTextoGestor("p", "text-gray-500", "Sem ocorrências para exibir neste período."));
                return;
            }
            const maximo = Math.max(...tipos.map(item => Number(item.total) || 0), 1);
            tipos.forEach(item => {
                const card = document.createElement("div");
                card.className = "rounded-xl border border-gray-200 p-4";
                card.append(
                    elementoTextoGestor("div", "mb-2 flex justify-between gap-3 text-sm text-gray-700", `${item.tipo_ocorrencia} · ${item.total}`),
                    elementoTextoGestor("div", "sr-only", `${item.total} ocorrências`)
                );
                const trilha = document.createElement("div");
                trilha.className = "h-2 overflow-hidden rounded-full bg-gray-100";
                const barra = document.createElement("div");
                barra.className = "h-full rounded-full bg-green-600";
                barra.style.width = `${Math.min(100, ((Number(item.total) || 0) / maximo) * 100)}%`;
                trilha.append(barra);
                card.append(trilha);
                listaTipos.append(card);
            });
        } catch (error) {
            console.error("Erro ao carregar indicadores do gestor:", error);
            if (listaTipos) listaTipos.replaceChildren(elementoTextoGestor("p", "text-red-600", error.message));
        }
    }

    btnOptionsPdt.forEach(btn => {
        btn.addEventListener("click", () => abrirSecaoGestor(btn.dataset.section));
    });
    document.getElementById("periodoOcorrenciasGestor")?.addEventListener("change", carregarOcorrenciasGestor);
    document.getElementById("atualizarOcorrenciasGestor")?.addEventListener("click", carregarOcorrenciasGestor);
    document.getElementById("buscarOcorrenciasGestor")?.addEventListener("input", carregarOcorrenciasGestor);
    document.getElementById("periodoRelatoriosGestor")?.addEventListener("change", carregarRelatoriosGestor);
    document.getElementById("atualizarRelatoriosGestor")?.addEventListener("click", carregarRelatoriosGestor);
    document.querySelectorAll("[data-go-section]").forEach(btn => {
        btn.addEventListener("click", () => abrirSecaoGestor(btn.dataset.goSection));
    });
    // ================================
    // VARIÁVEIS GLOBAIS
    // ================================
    window.cursos = [];
    window.turmas = [];
    let turmaParaDesvinculnarId = null;
    let cursosCAtivos = [];
    
    // Referências para facilitar
    let cursos = window.cursos;
    let turmas = window.turmas;

    // ================================
    // ELEMENTOS DO DOM
    // ================================
    const btnAbrirModalTurma = document.getElementById("btnAbrirModalTurma");
    const overlayModalTurma = document.getElementById("overlayModalTurma");
    const fecharModalTurma = document.getElementById("fecharModalTurma");
    const cancelarModalTurma = document.getElementById("cancelarModalTurma");
    const btnNotificacoes = document.getElementById("btnNotificacoes");
    const notificacoesPanel = document.getElementById("notificacoesPanel");
    const fecharNotificacoes = document.getElementById("fecharNotificacoes");
    const formCadastrarTurma = document.getElementById("formCadastrarTurma");
    const cursoTurmaSelect = document.getElementById("cursoTurma");
    const salaTurmaInput = document.getElementById("salaTurma");
    const idTurmaEdicao = document.getElementById("idTurmaEdicao");
    const overlayModalSubcursoC = document.getElementById("overlayModalSubcursoC");
    const fecharModalSubcursoC = document.getElementById("fecharModalSubcursoC");
    const btnAdministracao = document.getElementById("btnAdministracao");
    const btnMeioAmbiente = document.getElementById("btnMeioAmbiente");
    const overlayModalDesvincula = document.getElementById("overlayModalDesvinculaTurma");
    const fecharModalDesvincula = document.getElementById("fecharModalDesvincula");
    const cancelarModalDesvincula = document.getElementById("cancelarModalDesvincula");
    const confirmarDesvincula = document.getElementById("confirmarDesvincula");
    const buscarTurma = document.getElementById("buscarTurma");

    // ================================
    // MODAL TURMA - ABRIR/FECHAR
    // ================================
    function abrirModalTurma(turmaId = null) {
        if (!overlayModalTurma) return;
        overlayModalTurma.classList.remove("hidden");
        overlayModalTurma.classList.add("flex");
        
        if (turmaId) {
            const turma = turmas.find(t => t.id_turma === turmaId);
            if (turma) {
                document.getElementById("serieTurma").value = turma.serie_turma || "";
                document.getElementById("salaTurma").value = turma.sala_turma || "";
                document.getElementById("selectProfessor").value = turma.id_professor || "";
                document.getElementById("descricaoTurma").value = turma.descricao_turma || "";
                cursoTurmaSelect.value = turma.id_curso || "";
                idTurmaEdicao.value = turmaId;
            }
        } else {
            if (formCadastrarTurma) formCadastrarTurma.reset();
            idTurmaEdicao.value = "";
        }
    }
    
    function fecharModalTurmaFunc() {
        if (!overlayModalTurma) return;
        overlayModalTurma.classList.add("hidden");
        overlayModalTurma.classList.remove("flex");
        if (formCadastrarTurma) formCadastrarTurma.reset();
        idTurmaEdicao.value = "";
    }
    
    if (btnAbrirModalTurma) {
        btnAbrirModalTurma.addEventListener("click", (e) => {
            e.preventDefault();
            abrirModalTurma();
        });
    }
    
    if (fecharModalTurma) fecharModalTurma.addEventListener("click", fecharModalTurmaFunc);
    if (cancelarModalTurma) cancelarModalTurma.addEventListener("click", fecharModalTurmaFunc);
    
    if (overlayModalTurma) {
        overlayModalTurma.addEventListener("click", (e) => {
            if (e.target === overlayModalTurma) fecharModalTurmaFunc();
        });
    }

    if (btnNotificacoes && notificacoesPanel) {
        btnNotificacoes.addEventListener("click", (e) => {
            e.stopPropagation();
            const abrir = notificacoesPanel.classList.contains("hidden");
            notificacoesPanel.classList.toggle("hidden", !abrir);
            btnNotificacoes.setAttribute("aria-expanded", String(abrir));
            if (!notificacoesPanel.classList.contains('hidden')) {
                carregarNotificacoes();
            }
        });
    }

    if (fecharNotificacoes) {
        fecharNotificacoes.addEventListener("click", () => {
            if (notificacoesPanel) notificacoesPanel.classList.add("hidden");
            btnNotificacoes?.setAttribute("aria-expanded", "false");
        });
    }

    document.addEventListener("click", (e) => {
        if (notificacoesPanel && !notificacoesPanel.contains(e.target) && !(btnNotificacoes && btnNotificacoes.contains(e.target))) {
            notificacoesPanel.classList.add("hidden");
            btnNotificacoes?.setAttribute("aria-expanded", "false");
        }
    });

    // Validação sala - apenas números
    if (salaTurmaInput) {
        salaTurmaInput.addEventListener("input", (e) => {
            e.target.value = e.target.value.replace(/[^\d]/g, "");
        });
    }

    // ================================
    // CARREGAR CURSOS
    // ================================
    async function carregarCursos() {
        try {
            const response = await fetch("../controllers/turma_controller.php?action=listar_cursos", {
                method: "GET"
            });
            const data = await response.json();
            
            if (!data.sucesso) {
                console.error("Erro ao carregar cursos:", data.erro);
                return;
            }
            
            window.cursos = data.dados;
            cursos = window.cursos;
            popularSelectCursos();
        } catch (error) {
            console.error("Erro ao carregar cursos:", error);
        }
    }

    // ================================
    // POPULAR SELECT DE CURSOS
    // ================================
    function popularSelectCursos() {
        if (!cursoTurmaSelect) return;
        
        cursoTurmaSelect.innerHTML = '<option value="">Selecione</option>';
        
        const letrasAdicionadas = new Set();
        cursos.forEach(curso => {
            if (curso.letra === 'C') {
                // Para Curso C, mostrar com o nome específico (Administração ou Meio Ambiente)
                const opt = document.createElement('option');
                opt.value = curso.id_curso;
                opt.text = `${curso.letra} - ${curso.nome_curso}`;
                cursoTurmaSelect.appendChild(opt);
            } else {
                // Para outros cursos, mostrar normalmente
                const opt = document.createElement('option');
                opt.value = curso.id_curso;
                opt.text = `${curso.letra} - ${curso.nome_curso}`;
                cursoTurmaSelect.appendChild(opt);
            }
        });
    }

    // ================================
    // MODAL SUBCURSO C
    // ================================
    function abrirModalSubcursoC() {
        if (overlayModalSubcursoC) {
            overlayModalSubcursoC.classList.remove("hidden");
            overlayModalSubcursoC.classList.add("flex");
        }
    }

    function fecharModalSubcursoCFunc() {
        if (overlayModalSubcursoC) {
            overlayModalSubcursoC.classList.add("hidden");
            overlayModalSubcursoC.classList.remove("flex");
        }
    }

    if (fecharModalSubcursoC) {
        fecharModalSubcursoC.addEventListener("click", fecharModalSubcursoCFunc);
    }

    if (overlayModalSubcursoC) {
        overlayModalSubcursoC.addEventListener("click", (e) => {
            if (e.target === overlayModalSubcursoC) fecharModalSubcursoCFunc();
        });
    }

    // Seleção de subcurso C
    if (cursoTurmaSelect) {
        cursoTurmaSelect.addEventListener("change", (e) => {
            // Não abrir modal, apenas permitir seleção direta
            // Se for futuramente necessário abrir modal, descomentar:
            // if (e.target.value === 'C') {
            //     abrirModalSubcursoC();
            // }
        });
    }

    if (btnAdministracao) {
        btnAdministracao.addEventListener("click", () => {
            console.log('Botão Administração clicado');
            console.log('Cursos disponíveis:', cursos);
            
            // Buscar curso C de Administração
            const adminCurso = cursos.find(c => {
                const nome = c.nome_curso.toLowerCase();
                const match = c.letra === 'C' && (nome.includes('administração') || nome.includes('administracao') || nome.includes('admin'));
                console.log(`Verificando: ${c.nome_curso} (${c.letra}) - Match: ${match}, Nome lower: ${nome}`);
                return match;
            });
            
            console.log('Curso encontrado:', adminCurso);
            
            if (adminCurso) {
                console.log('Atribuindo valor:', adminCurso.id_curso);
                cursoTurmaSelect.value = adminCurso.id_curso;
                // Disparar change para atualizar o campo
                cursoTurmaSelect.dispatchEvent(new Event('change'));
                console.log('Valor do select após atribuição:', cursoTurmaSelect.value);
                fecharModalSubcursoCFunc();
            } else {
                console.warn('Curso Administração não encontrado', cursos);
            }
        });
    }

    if (btnMeioAmbiente) {
        btnMeioAmbiente.addEventListener("click", () => {
            console.log('Botão Meio Ambiente clicado');
            console.log('Cursos disponíveis:', cursos);
            
            // Buscar curso C de Meio Ambiente
            const meioAmbienteCurso = cursos.find(c => {
                const nome = c.nome_curso.toLowerCase();
                const match = c.letra === 'C' && (nome.includes('meio ambiente') || nome.includes('meio') || nome.includes('ambiente'));
                console.log(`Verificando: ${c.nome_curso} (${c.letra}) - Match: ${match}, Nome lower: ${nome}`);
                return match;
            });
            
            console.log('Curso encontrado:', meioAmbienteCurso);
            
            if (meioAmbienteCurso) {
                console.log('Atribuindo valor:', meioAmbienteCurso.id_curso);
                cursoTurmaSelect.value = meioAmbienteCurso.id_curso;
                // Disparar change para atualizar o campo
                cursoTurmaSelect.dispatchEvent(new Event('change'));
                console.log('Valor do select após atribuição:', cursoTurmaSelect.value);
                fecharModalSubcursoCFunc();
            } else {
                console.warn('Curso Meio Ambiente não encontrado', cursos);
            }
        });
    }

    // ================================
    // FORM TURMA SUBMIT
    // ================================
    if (formCadastrarTurma) {
        formCadastrarTurma.addEventListener("submit", async (e) => {
            e.preventDefault();

            const idCurso = cursoTurmaSelect.value;
            const serie = document.getElementById("serieTurma").value;
            const sala = document.getElementById("salaTurma").value;
            const idProfessor = document.getElementById("selectProfessor").value;
            const descricao = document.getElementById("descricaoTurma").value;
            const turmaIdEdicao = idTurmaEdicao.value;

            if (!idCurso || !serie || !sala || !idProfessor) {
                alert("Preencha todos os campos obrigatórios!");
                return;
            }

            if (!/^\d+$/.test(sala)) {
                alert("O campo sala deve conter apenas números!");
                return;
            }

            try {
                const action = turmaIdEdicao ? "atualizar_turma" : "cadastrar_turma";
                const payload = {
                    id_curso: parseInt(idCurso),
                    serie_turma: parseInt(serie),
                    sala_turma: sala,
                    ano_letivo: 2026,
                    descricao_turma: descricao,
                    id_professor: parseInt(idProfessor)
                };

                if (turmaIdEdicao) {
                    payload.id_turma = parseInt(turmaIdEdicao);
                }

                const response = await fetch(`../controllers/turma_controller.php?action=${action}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.sucesso) {
                    alert(data.mensagem || "Turma salva com sucesso!");
                    fecharModalTurmaFunc();
                    await carregarTurmas();
                } else {
                    alert("Erro: " + (data.erro || "Algo deu errado"));
                }
            } catch (error) {
                console.error("Erro ao salvar turma:", error);
                alert("Erro ao salvar turma!");
            }
        });
    }

    // ================================
    // CARREGAR TURMAS
    // ================================
    async function carregarTurmas() {
        try {
            const response = await fetch("../controllers/turma_controller.php?action=listar_turmas", {
                method: "POST"
            });

            const data = await response.json();

            if (!data.sucesso) {
                console.error(data.erro);
                return;
            }

            window.turmas = data.dados;
            turmas = window.turmas;
            renderizarTurmas();
        } catch (error) {
            console.error("Erro ao carregar turmas:", error);
        }
    }

    // ================================
    // RENDERIZAR TURMAS
    // ================================
    function renderizarTurmas() {
        const listaTurmas = document.getElementById("listaTurmas");
        const contadorTurmas = document.getElementById("contadorTurmas");
        const contadorTurmasDashboard = document.getElementById("contadorAlunos");

        if (!listaTurmas) return;
        
        listaTurmas.innerHTML = "";

        if (turmas.length === 0) {
            listaTurmas.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-gray-500">Nenhuma turma cadastrada ainda.</p></div>';
        } else {
            turmas.forEach(turma => {
                const card = document.createElement("div");
                card.className = "pdt-class-card h-full w-full";
                card.innerHTML = `
                    <div class="pdt-class-card-heading">
                        <div class="pdt-class-icon">
                            <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="pdt-class-title">
                                ${turma.serie_turma}º ${turma.letra}
                            </h2>
                            <p class="pdt-class-course">${turma.nome_curso}</p>
                        </div>
                    </div>

                    <div class="pdt-class-details">
                        <div class="pdt-class-detail">
                            <span class="pdt-class-detail-label"><i class="bi bi-geo-alt" aria-hidden="true"></i>Sala</span>
                            <span class="pdt-class-detail-value">${turma.sala_turma}</span>
                        </div>
                        <div class="pdt-class-detail">
                            <span class="pdt-class-detail-label"><i class="bi bi-calendar3" aria-hidden="true"></i>Ano letivo</span>
                            <span class="pdt-class-detail-value">${turma.ano_letivo}</span>
                        </div>
                        <div class="pdt-class-detail">
                            <span class="pdt-class-detail-label"><i class="bi bi-person" aria-hidden="true"></i>Professor</span>
                            <span class="pdt-class-detail-value">${turma.professor_nome}</span>
                        </div>
                    </div>

                    <div class="pdt-class-card-actions">
                        <button class="pdt-class-edit btn-editar-turma" data-turma-id="${turma.id_turma}">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                            <span>Editar</span>
                        </button>
                        <button class="pdt-class-unlink btn-desvincula-turma" data-turma-id="${turma.id_turma}">
                            <i class="bi bi-link-45deg" aria-hidden="true"></i>
                            <span>Desvincular</span>
                        </button>
                    </div>
                `;
                listaTurmas.appendChild(card);
            });

            document.querySelectorAll(".btn-editar-turma").forEach(btn => {
                btn.addEventListener("click", (event) => {
                    const idTurma = parseInt(event.currentTarget.dataset.turmaId, 10);
                    abrirModalTurma(idTurma);
                });
            });

            document.querySelectorAll(".btn-desvincula-turma").forEach(btn => {
                btn.addEventListener("click", (event) => {
                    const idTurma = parseInt(event.currentTarget.dataset.turmaId, 10);
                    const turma = turmas.find(t => t.id_turma === idTurma);
                    if (turma) abrirModalDesvinculacao(turma);
                });
            });
        }

        if (contadorTurmas) contadorTurmas.textContent = turmas.length;
        if (contadorTurmasDashboard) contadorTurmasDashboard.textContent = turmas.length;
    }

    // ================================
    // MODAL DESVINCULAÇÃO
    // ================================
    function abrirModalDesvinculacao(turma) {
        document.getElementById("nomeTurmaDesvincula").textContent = `${turma.letra} - ${turma.nome_curso}`;
        document.getElementById("professorDesvincula").textContent = turma.professor_nome;
        document.getElementById("serieDesvincula").textContent = `${turma.serie_turma}º Ano`;
        turmaParaDesvinculnarId = turma.id_turma;
        
        if (overlayModalDesvincula) {
            overlayModalDesvincula.classList.remove("hidden");
            overlayModalDesvincula.classList.add("flex");
        }
    }

    function fecharModalDesvinculacaoFunc() {
        if (overlayModalDesvincula) {
            overlayModalDesvincula.classList.add("hidden");
            overlayModalDesvincula.classList.remove("flex");
        }
        turmaParaDesvinculnarId = null;
    }

    if (fecharModalDesvincula) fecharModalDesvincula.addEventListener("click", fecharModalDesvinculacaoFunc);
    if (cancelarModalDesvincula) cancelarModalDesvincula.addEventListener("click", fecharModalDesvinculacaoFunc);

    if (overlayModalDesvincula) {
        overlayModalDesvincula.addEventListener("click", (e) => {
            if (e.target === overlayModalDesvincula) fecharModalDesvinculacaoFunc();
        });
    }

    // Confirmar desvinculação
    if (confirmarDesvincula) {
        confirmarDesvincula.addEventListener("click", async () => {
            if (!turmaParaDesvinculnarId) return;

            try {
                const response = await fetch("../controllers/turma_controller.php?action=deletar_turma", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id_turma: turmaParaDesvinculnarId
                    })
                });

                const data = await response.json();

                if (data.sucesso) {
                    alert(data.mensagem || "Turma desvinculada com sucesso!");
                    fecharModalDesvinculacaoFunc();
                    await carregarTurmas();
                } else {
                    alert("Erro: " + (data.erro || "Algo deu errado"));
                }
            } catch (error) {
                console.error("Erro ao desvincular turma:", error);
                alert("Erro ao desvincular turma!");
            }
        });
    }

    // ================================
    // BUSCAR TURMA
    // ================================
    if (buscarTurma) {
        buscarTurma.addEventListener("input", () => {
            const valor = buscarTurma.value.toLowerCase();
            const cards = document.querySelectorAll("#listaTurmas > div");
            cards.forEach(card => {
                const texto = card.innerText.toLowerCase();
                if (texto.includes(valor)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        });
    }

    // ================================
    // INICIALIZAR
    // ================================
    carregarCursos();
    carregarTurmas();
    carregarRelatoriosGestor();
});
</script>

</body>
</html>
