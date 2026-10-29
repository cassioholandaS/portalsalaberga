<?php
session_start();
require_once('../config/security.php');
require_once('../models/usuario_model.php');

iniciarSessaoSegura();
exigirAutenticacao(false);

if (normalizarTipoUsuario($_SESSION['tipo'] ?? '') !== 'coordenador' && normalizarTipoUsuario($_SESSION['tipo'] ?? '') !== 'gestor') {
    header('Location: ../index.php');
    exit;
}

$usuarioModel = new usuario_model();
$usuarioAtual = $usuarioModel->buscar_por_id($_SESSION['id_usuario']);
$csrfToken = $_SESSION['csrf_token'] ?? gerarCsrfToken();
?>
<script>window.csrfToken = <?= json_encode($csrfToken) ?>;</script>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordenador | Sistema PDT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .coord-section {
            transition: opacity .25s ease;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/system-pdt.css?v=35">
    <script src="../assets/js/system-pdt-ui.js?v=2" defer></script>
</head>
<body class="pdt-interface min-h-screen">
<div class="flex min-h-screen">
    <aside id="pdtSidebar" class="pdt-sidebar sidebar-panel border-r border-slate-800 bg-slate-950 flex flex-col justify-between">
        <div>
            <div class="pdt-brand flex items-center gap-3">
                <span class="pdt-brand-mark"><img src="../../../../entradasaida/app/main/assets/img/logo.png" alt="" class="pdt-brand-logo"></span>
                <div>
                    <h1 class="font-bold text-[18px]">Sistema <span>Salaberga</span></h1>
                    <p>Professor Diretor de Turma</p>
                </div>
            </div>
            <nav>
                <button data-section="sec-dashboard" class="coord-nav active effect-bar effect-bar-select"><i class="bi bi-house-door" aria-hidden="true"></i>Dashboard</button>
                <button data-section="sec-turmas" class="coord-nav effect-bar"><i class="bi bi-journal-bookmark" aria-hidden="true"></i>Turmas</button>
                <button data-section="sec-ocorrencias" class="coord-nav effect-bar"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Ocorrências</button>
                <!-- <button data-section="sec-relatorios" class="coord-nav w-full rounded-3xl border border-slate-700 bg-slate-900 px-4 py-4 text-left text-slate-100">Relatórios</button> -->
                <button data-section="sec-calendario" class="coord-nav effect-bar"><i class="bi bi-calendar-event" aria-hidden="true"></i>Calendário</button>
                <button data-section="sec-notificacoes" class="coord-nav effect-bar"><i class="bi bi-bell" aria-hidden="true"></i>Notificações</button>
            </nav>
        </div>
        <div class="w-full">
        <div class="pdt-account-tools">
            <div class="pdt-account-user">
                <span class="pdt-account-avatar"><?= htmlspecialchars(substr((string) ($usuarioAtual['nome'] ?? $_SESSION['nome'] ?? 'U'), 0, 1), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="pdt-account-details">
                    <span><?= htmlspecialchars((string) ($usuarioAtual['nome'] ?? $_SESSION['nome'] ?? 'Usuário'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="pdt-account-role"><?= htmlspecialchars((string) ($usuarioAtual['tipo'] ?? $_SESSION['tipo'] ?? 'Coordenador'), ENT_QUOTES, 'UTF-8') ?></span>
                </span>
            </div>
            <button class="pdt-notifications-toggle" type="button" aria-label="Abrir notificações" title="Notificações" onclick="document.querySelector('[data-section=&quot;sec-notificacoes&quot;]').click()">
                <i class="bi bi-bell" aria-hidden="true"></i>
            </button>
        </div>
        <form action="../controllers/usuario_controller.php?action=logout" method="POST" class="m-0 p-0">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <button type="submit" class="pdt-logout-button"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Sair</span></button>
        </form>
        </div>
    </aside>
    <button class="pdt-sidebar-collapse-toggle" type="button" aria-controls="pdtSidebar" aria-label="Recolher barra lateral" aria-expanded="true" title="Recolher barra lateral">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
    </button>

    <div class="pdt-main-offset flex-1 lg:ml-[280px]">
        <div class="pdt-mobile-nav lg:hidden border-b border-slate-800 bg-slate-950 px-6 py-4">
            <div class="grid grid-cols-3 gap-2">
                <button class="coord-nav w-full rounded-3xl border border-slate-700 bg-slate-900 px-3 py-3 text-sm text-slate-100" data-section="sec-dashboard">Dashboard</button>
                <button class="coord-nav w-full rounded-3xl border border-slate-700 bg-slate-900 px-3 py-3 text-sm text-slate-100" data-section="sec-turmas">Turmas</button>
                <!-- <button class="coord-nav w-full rounded-3xl border border-slate-700 bg-slate-900 px-3 py-3 text-sm text-slate-100" data-section="sec-relatorios">Relatórios</button> -->
            </div>
        </div>

        <main class="pdt-content p-6 space-y-8">
            <section id="sec-dashboard" class="coord-section rounded-3xl border border-slate-800 bg-slate-950 p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-white">Dashboard</h3>
                        <p class="text-slate-400">Resumo de atividades, frequência e ocorrências.</p>
                    </div>
                    <button id="btnAtualizarDashboard" class="rounded-full border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-200 hover:bg-slate-800 transition">Atualizar</button>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
                    <div class="rounded-3xl border border-slate-800 bg-slate-950 p-5">
                        <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Turmas</p>
                        <h4 id="dashTotalTurmas" class="mt-4 text-4xl font-bold text-white">0</h4>
                        <p class="text-slate-400 mt-2 text-sm">Turmas cadastradas no sistema.</p>
                    </div>
                    <div class="rounded-3xl border border-slate-800 bg-slate-950 p-5">
                        <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Alunos</p>
                        <h4 id="dashTotalAlunos" class="mt-4 text-4xl font-bold text-white">0</h4>
                        <p class="text-slate-400 mt-2 text-sm">Alunos ativos registrados.</p>
                    </div>
                    <div class="rounded-3xl border border-slate-800 bg-slate-950 p-5">
                        <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Ocorrências</p>
                        <h4 id="dashOcorrenciasMes" class="mt-4 text-4xl font-bold text-white">0</h4>
                        <p class="text-slate-400 mt-2 text-sm">Ocorrências no último mês.</p>
                    </div>
                    <div class="rounded-3xl border border-slate-800 bg-slate-950 p-5">
                        <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Frequência</p>
                        <h4 id="dashMediaFrequencia" class="mt-4 text-4xl font-bold text-white">0%</h4>
                        <p class="text-slate-400 mt-2 text-sm">Média de presença mensal.</p>
                    </div>
                </div>
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-3xl border border-slate-800 bg-slate-950 p-5">
                        <h4 class="text-lg font-semibold text-white mb-4">Últimas ocorrências</h4>
                        <div id="dashOcorrenciasRecentes" class="space-y-4 text-slate-300">Carregando...</div>
                    </div>
                    <div class="rounded-3xl border border-slate-800 bg-slate-950 p-5">
                        <h4 class="text-lg font-semibold text-white mb-4">Turmas com mais ocorrências</h4>
                        <div id="dashTopTurmasOcorrencias" class="space-y-4 text-slate-300">Carregando...</div>
                    </div>
                </div>
            </section>

            <section id="sec-turmas" class="coord-section hidden rounded-3xl border border-slate-800 bg-slate-950 p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-white">Turmas</h3>
                        <p class="text-slate-400">Lista completa de turmas e professores vinculados.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-4">
                            <p class="text-slate-400 text-sm">Total de turmas</p>
                            <p id="coordTurmasTotal" class="text-white text-3xl font-semibold mt-2">0</p>
                        </div>
                    </div>
                </div>
                <div class="mb-6 grid gap-4 lg:grid-cols-[1fr_280px]">
                    <input id="buscarTurmaCoord" type="text" placeholder="Buscar turma, curso ou professor" class="w-full rounded-3xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 outline-none focus:border-emerald-500" />
                </div>
                <div id="coordTurmasLista" class="space-y-4">Carregando...</div>
            </section>

            <section id="sec-ocorrencias" class="coord-section hidden rounded-3xl border border-slate-800 bg-slate-950 p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-white">Ocorrências</h3>
                        <p class="text-slate-400">Filtre e pesquise ocorrências coletivas.</p>
                    </div>
                </div>
                <div class="grid gap-4 lg:grid-cols-3 mb-6">
                    <div>
                        <label class="text-slate-400 text-sm mb-2 block">Período</label>
                        <select id="coordOcorrenciasFiltro" class="w-full rounded-3xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 outline-none">
                            <option value="mes">Último mês</option>
                            <option value="trimestre">Último trimestre</option>
                            <option value="semestre">Último semestre</option>
                            <option value="ano">Último ano</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-slate-400 text-sm mb-2 block">Buscar</label>
                        <input id="coordOcorrenciasBusca" type="text" placeholder="Aluno, tipo ou turma" class="w-full rounded-3xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 outline-none" />
                    </div>
                </div>
                <div id="coordOcorrenciasLista" class="space-y-4">Carregando...</div>
            </section>

            <section id="sec-relatorios" class="coord-section hidden rounded-3xl border border-slate-800 bg-slate-950 p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-white">Relatórios</h3>
                        <p class="text-slate-400">Painel analítico de cursos, turmas e ocorrências.</p>
                    </div>
                </div>
                <div class="grid gap-4 xl:grid-cols-4 mb-6">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5">
                        <p class="text-slate-400 text-sm">Turmas por curso</p>
                        <div id="coordRelTurmasPorCurso" class="mt-4 space-y-3"></div>
                    </div>
                    <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5">
                        <p class="text-slate-400 text-sm">Ocorrências por tipo</p>
                        <div id="coordRelOcorrenciasTipo" class="mt-4 space-y-3"></div>
                    </div>
                    <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5">
                        <p class="text-slate-400 text-sm">Top turmas (ocorrências)</p>
                        <div id="coordTopTurmasOcorrencias" class="mt-4 space-y-3"></div>
                    </div>
                    <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5">
                        <p class="text-slate-400 text-sm">Top turmas (frequência)</p>
                        <div id="coordTopTurmasFrequencia" class="mt-4 space-y-3"></div>
                    </div>
                </div>
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5">
                    <h4 class="text-lg font-semibold text-white mb-4">Resumo geral</h4>
                    <div id="coordResumoRelatorios" class="grid gap-4 lg:grid-cols-2"></div>
                </div>
            </section>

            <section id="sec-calendario" class="coord-section hidden rounded-3xl border border-slate-800 bg-slate-950 p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-white">Calendário</h3>
                        <p class="text-slate-400">Eventos com base em ocorrências e frequência.</p>
                    </div>
                    <div class="flex gap-3">
                        <button id="coordCalendarioPrev" class="rounded-full border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-200 hover:bg-slate-800 transition">Anterior</button>
                        <button id="coordCalendarioNext" class="rounded-full border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-200 hover:bg-slate-800 transition">Próximo</button>
                    </div>
                </div>
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5 mb-6">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <p id="coordCalendarioMesAno" class="text-xl font-semibold text-white"></p>
                            <p class="text-slate-400 text-sm">Selecione um dia para ver eventos detalhados.</p>
                        </div>
                        <div id="coordCalendarioContagem" class="text-slate-400 text-sm"></div>
                    </div>
                    <div id="coordCalendarioGrid" class="grid grid-cols-7 gap-2 text-center text-slate-400 text-xs"></div>
                </div>
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5">
                    <h4 class="text-lg font-semibold text-white mb-4">Eventos do mês</h4>
                    <div id="coordCalendarioEventos" class="space-y-4 text-slate-300">Carregando...</div>
                </div>
            </section>

            <section id="sec-notificacoes" class="coord-section hidden rounded-3xl border border-slate-800 bg-slate-950 p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-white">Notificações</h3>
                        <p class="text-slate-400">Acompanhe mensagens enviadas ao seu usuário.</p>
                    </div>
                    <button id="coordNotificacoesMarcarTodas" class="rounded-full border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-200 hover:bg-slate-800 transition">Marcar todas como lidas</button>
                </div>
                <div id="coordNotificacoesLista" class="space-y-4 text-slate-300">Carregando...</div>
            </section>
        </main>
    </div>
</div>

<script>
    const navButtons = document.querySelectorAll('.coord-nav');
    const sections = document.querySelectorAll('.coord-section');
    const state = {
        loaded: {
            dashboard: false,
            turmas: false,
            ocorrencias: false,
            relatorios: false,
            calendario: false,
            notificacoes: false
        },
        ocorrencias: [],
        events: [],
        calendarYear: new Date().getFullYear(),
        calendarMonth: new Date().getMonth()
    };

    function setActiveSection(sectionId) {
        sections.forEach(sec => sec.classList.add('hidden'));
        document.getElementById(sectionId)?.classList.remove('hidden');
        navButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.section === sectionId));
        switch (sectionId) {
            case 'sec-dashboard': carregarDashboard(); break;
            case 'sec-turmas': carregarTurmas(); break;
            case 'sec-ocorrencias': carregarOcorrencias(); break;
            case 'sec-relatorios': carregarRelatorios(); break;
            case 'sec-calendario': carregarCalendarioEventos(); break;
            case 'sec-notificacoes': carregarNotificacoes(); break;
        }
    }

    navButtons.forEach(button => button.addEventListener('click', () => setActiveSection(button.dataset.section)));
    document.getElementById('btnAtualizarDashboard')?.addEventListener('click', () => carregarDashboard(true));
    document.getElementById('coordOcorrenciasFiltro')?.addEventListener('change', carregarOcorrencias);
    document.getElementById('coordOcorrenciasBusca')?.addEventListener('input', filtrarOcorrencias);
    document.getElementById('coordCalendarioPrev')?.addEventListener('click', () => { state.calendarMonth = state.calendarMonth === 0 ? 11 : state.calendarMonth - 1; if (state.calendarMonth === 11) state.calendarYear -= 1; carregarCalendarioEventos(); });
    document.getElementById('coordCalendarioNext')?.addEventListener('click', () => { state.calendarMonth = state.calendarMonth === 11 ? 0 : state.calendarMonth + 1; if (state.calendarMonth === 0) state.calendarYear += 1; carregarCalendarioEventos(); });
    document.getElementById('coordNotificacoesMarcarTodas')?.addEventListener('click', marcarTodasNotificacoesLidas);

    async function fetchJson(url, options = {}) {
        try {
            const response = await fetch(url, options);
            return await response.json();
        } catch (error) {
            console.error('Fetch error:', error);
            return { sucesso: false, erro: 'Erro de conexão' };
        }
    }

    async function carregarDashboard(force = false) {
        if (state.loaded.dashboard && !force) return;
        state.loaded.dashboard = true;
        const recent = document.getElementById('dashOcorrenciasRecentes');
        const top = document.getElementById('dashTopTurmasOcorrencias');
        recent.innerHTML = '<div class="text-slate-500">Carregando...</div>';
        top.innerHTML = '<div class="text-slate-500">Carregando...</div>';

        const data = await fetchJson('../controllers/relatorio_controller.php?action=resumo_geral');
        if (!data.sucesso) {
            recent.innerHTML = '<div class="text-red-500">Erro ao carregar dados</div>';
            top.innerHTML = '<div class="text-red-500">Erro ao carregar dados</div>';
            return;
        }

        document.getElementById('dashTotalTurmas').textContent = data.total_turmas ?? 0;
        document.getElementById('dashTotalAlunos').textContent = data.total_alunos ?? 0;
        document.getElementById('dashOcorrenciasMes').textContent = data.total_ocorrencias_mes ?? 0;
        document.getElementById('dashMediaFrequencia').textContent = `${data.media_frequencia_mes ?? 0}%`;

        recent.innerHTML = data.ocorrencias_recentes.length ? data.ocorrencias_recentes.map(item => `
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-4">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="text-slate-400 text-xs">${item.data_ocorrencia}</span>
                    <span class="text-emerald-400 text-xs">${item.tipo_ocorrencia}</span>
                </div>
                <p class="text-white font-semibold">${item.nome_aluno}</p>
                <p class="text-slate-500 text-sm mt-2">${item.nome_curso} • ${item.serie_turma}º ${item.letra}</p>
            </div>
        `).join('') : '<div class="text-slate-500">Nenhuma ocorrência recente.</div>';

        top.innerHTML = data.top_turmas_ocorrencias.length ? data.top_turmas_ocorrencias.map(item => `
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-4">
                <p class="text-slate-400 text-sm">${item.turma}</p>
                <p class="text-white mt-3 text-xl font-semibold">${item.total}</p>
            </div>
        `).join('') : '<div class="text-slate-500">Sem turmas com ocorrências.</div>';
    }

    async function carregarTurmas() {
        if (state.loaded.turmas) return;
        state.loaded.turmas = true;
        const lista = document.getElementById('coordTurmasLista');
        const total = document.getElementById('coordTurmasTotal');
        lista.innerHTML = '<div class="text-slate-500">Carregando turmas...</div>';

        const data = await fetchJson('../controllers/turma_controller.php?action=listar_turmas', { method: 'POST' });
        if (!data.sucesso) {
            lista.innerHTML = '<div class="text-red-500">Erro ao carregar turmas</div>';
            return;
        }

        const turmas = data.dados || [];
        if (total) total.textContent = turmas.length;
        lista.innerHTML = turmas.length ? turmas.map(turma => `
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-slate-400 text-sm">${turma.nome_curso}</p>
                        <h4 class="text-white text-xl font-semibold mt-2">${turma.serie_turma}º ${turma.letra}</h4>
                        <p class="text-slate-500 mt-2">Sala ${turma.sala_turma} • ${turma.ano_letivo}</p>
                    </div>
                    <div class="text-right text-slate-400 text-sm">
                        <p class="text-slate-400">Professor</p>
                        <p class="text-white font-semibold mt-2">${turma.professor_nome}</p>
                    </div>
                </div>
            </div>
        `).join('') : '<div class="text-slate-500">Nenhuma turma cadastrada.</div>';

        document.getElementById('buscarTurmaCoord')?.addEventListener('input', () => {
            const termo = document.getElementById('buscarTurmaCoord').value.toLowerCase();
            Array.from(lista.children).forEach(card => {
                card.style.display = card.textContent.toLowerCase().includes(termo) ? '' : 'none';
            });
        });
    }

    async function carregarOcorrencias() {
        state.loaded.ocorrencias = true;
        const lista = document.getElementById('coordOcorrenciasLista');
        if (!lista) return;
        lista.innerHTML = '<div class="text-slate-500">Carregando ocorrências...</div>';
        const filtro = document.getElementById('coordOcorrenciasFiltro')?.value || 'mes';
        const data = await fetchJson(`../controllers/ocorrencia_controller.php?action=listar_ocorrencias&filtro=${encodeURIComponent(filtro)}`);
        if (!data.sucesso) {
            lista.innerHTML = '<div class="text-red-500">Erro ao carregar ocorrências.</div>';
            return;
        }
        state.ocorrencias = data.dados || [];
        renderOcorrencias();
    }

    function filtrarOcorrencias() {
        renderOcorrencias(document.getElementById('coordOcorrenciasBusca')?.value.toLowerCase() || '');
    }

    function renderOcorrencias(termo = '') {
        const lista = document.getElementById('coordOcorrenciasLista');
        if (!lista) return;
        const items = state.ocorrencias.filter(item => {
            if (!termo) return true;
            return [item.tipo_ocorrencia, item.nome_aluno, item.nome_turma, item.matricula, item.professor_nome].some(value => value && value.toLowerCase().includes(termo));
        });
        if (!items.length) {
            lista.innerHTML = '<div class="text-slate-500">Nenhuma ocorrência encontrada.</div>';
            return;
        }
        lista.innerHTML = items.map(item => `
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-slate-400 text-xs">${item.data_ocorrencia}</p>
                        <h4 class="text-white text-xl font-semibold mt-2">${item.tipo_ocorrencia}</h4>
                        <p class="text-slate-500 mt-2">${item.nome_aluno} • ${item.matricula || '---'}</p>
                    </div>
                    <div class="text-right text-slate-400 text-sm">
                        <p>${item.nome_turma}</p>
                        <p class="mt-2">Professor: ${item.professor_nome || 'N/A'}</p>
                    </div>
                </div>
                ${item.descricao ? `<p class="mt-4 text-slate-500 text-sm">${item.descricao}</p>` : ''}
            </div>
        `).join('');
    }

    async function carregarRelatorios() {
        if (state.loaded.relatorios) return;
        state.loaded.relatorios = true;
        const data = await fetchJson('../controllers/relatorio_controller.php?action=resumo_geral');
        if (!data.sucesso) {
            document.getElementById('coordResumoRelatorios').innerHTML = '<div class="text-red-500">Erro ao carregar relatórios.</div>';
            return;
        }
        document.getElementById('coordRelTurmasPorCurso').innerHTML = data.turmas_por_curso.map(item => `
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-3 flex items-center justify-between">
                <span class="text-slate-300">${item.nome_curso}</span>
                <strong class="text-white">${item.total}</strong>
            </div>
        `).join('');
        document.getElementById('coordRelOcorrenciasTipo').innerHTML = data.ocorrencias_por_tipo.map(item => `
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-3 flex items-center justify-between">
                <span class="text-slate-300">${item.tipo_ocorrencia}</span>
                <strong class="text-white">${item.total}</strong>
            </div>
        `).join('');
        document.getElementById('coordTopTurmasOcorrencias').innerHTML = data.top_turmas_ocorrencias.map(item => `
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-3">
                <p class="text-slate-400 text-sm">${item.turma}</p>
                <p class="text-white font-semibold mt-2">${item.total}</p>
            </div>
        `).join('');
        document.getElementById('coordTopTurmasFrequencia').innerHTML = data.top_turmas_frequencia.map(item => `
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-3">
                <p class="text-slate-400 text-sm">${item.turma}</p>
                <p class="text-white font-semibold mt-2">${item.total}</p>
            </div>
        `).join('');
        document.getElementById('coordResumoRelatorios').innerHTML = `
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-4">
                    <p class="text-slate-400 text-sm">Turmas cadastradas</p>
                    <p class="text-white text-2xl font-semibold mt-2">${data.total_turmas}</p>
                </div>
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-4">
                    <p class="text-slate-400 text-sm">Alunos matriculados</p>
                    <p class="text-white text-2xl font-semibold mt-2">${data.total_alunos}</p>
                </div>
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-4">
                    <p class="text-slate-400 text-sm">Ocorrências no mês</p>
                    <p class="text-white text-2xl font-semibold mt-2">${data.total_ocorrencias_mes}</p>
                </div>
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-4">
                    <p class="text-slate-400 text-sm">Presença média</p>
                    <p class="text-white text-2xl font-semibold mt-2">${data.media_frequencia_mes}%</p>
                </div>
            </div>
        `;
    }

    async function carregarCalendarioEventos() {
        const response = await fetchJson(`../controllers/relatorio_controller.php?action=calendario_eventos&ano=${state.calendarYear}&mes=${state.calendarMonth + 1}`);
        if (!response.sucesso) {
            state.events = [];
            const events = document.getElementById('coordCalendarioEventos');
            if (events) events.textContent = response.erro || 'Não foi possível carregar o calendário.';
            return;
        }
        state.events = response.dados || [];
        renderCalendar();
    }

    function renderCalendar() {
        const grid = document.getElementById('coordCalendarioGrid');
        const label = document.getElementById('coordCalendarioMesAno');
        const counter = document.getElementById('coordCalendarioContagem');
        if (!grid || !label || !counter) return;
        const monthName = new Date(state.calendarYear, state.calendarMonth, 1).toLocaleString('pt-BR', { month: 'long' });
        label.textContent = `${monthName.charAt(0).toUpperCase() + monthName.slice(1)} ${state.calendarYear}`;
        const firstDay = new Date(state.calendarYear, state.calendarMonth, 1).getDay();
        const totalDays = new Date(state.calendarYear, state.calendarMonth + 1, 0).getDate();
        const eventsByDate = state.events.reduce((acc, item) => {
            if (!item.evento_data) return acc;
            acc[item.evento_data] = acc[item.evento_data] || [];
            acc[item.evento_data].push(item);
            return acc;
        }, {});
        grid.innerHTML = '';
        ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'].forEach(day => {
            const header = document.createElement('div');
            header.className = 'text-slate-500 font-semibold';
            header.textContent = day;
            grid.appendChild(header);
        });
        for (let blank = 0; blank < firstDay; blank++) {
            const cell = document.createElement('div');
            cell.className = 'h-20 rounded-3xl bg-slate-900 border border-slate-800';
            grid.appendChild(cell);
        }
        for (let day = 1; day <= totalDays; day++) {
            const dateKey = `${state.calendarYear}-${String(state.calendarMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const events = eventsByDate[dateKey] || [];
            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = `group h-20 rounded-3xl border border-slate-800 p-3 text-left transition ${events.length ? 'bg-emerald-950 border-emerald-600 hover:bg-emerald-900' : 'bg-slate-900 hover:bg-slate-800'}`;
            cell.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-white font-semibold">${day}</span>
                    ${events.length ? `<span class="rounded-full bg-emerald-600 px-2 py-1 text-[11px] text-white">${events.length}</span>` : ''}
                </div>
            `;
            const title = document.createElement('p');
            title.className = 'mt-2 text-xs text-slate-400';
            title.textContent = events[0]?.titulo || '';
            cell.appendChild(title);
            cell.addEventListener('click', () => renderCalendarEvents(dateKey));
            grid.appendChild(cell);
        }
        counter.textContent = `${state.events.length} evento(s) no período`;
        renderCalendarEvents();
    }

    function renderCalendarEvents(dateKey = null) {
        const list = document.getElementById('coordCalendarioEventos');
        if (!list) return;
        const filtered = dateKey ? state.events.filter(item => item.evento_data === dateKey) : state.events;
        if (!filtered.length) {
            list.innerHTML = '<div class="rounded-3xl border border-slate-800 bg-slate-900 p-5 text-slate-500">Nenhum evento disponível.</div>';
            return;
        }
        list.replaceChildren();
        filtered.forEach(item => {
            const card = document.createElement('article');
            card.className = 'rounded-3xl border border-slate-800 bg-slate-900 p-5';
            const header = document.createElement('div');
            header.className = 'flex items-center justify-between gap-3';
            const details = document.createElement('div');
            const date = document.createElement('p');
            date.className = 'text-slate-400 text-xs';
            date.textContent = item.evento_data || '';
            const title = document.createElement('h4');
            title.className = 'text-white text-lg font-semibold mt-2';
            title.textContent = item.titulo || '';
            details.append(date, title);
            const type = document.createElement('span');
            type.className = 'rounded-full bg-emerald-600 px-3 py-1 text-xs text-white';
            type.textContent = item.tipo || '';
            header.append(details, type);
            card.append(header);
            if (item.descricao) {
                const description = document.createElement('p');
                description.className = 'mt-3 text-slate-400 text-sm';
                description.textContent = item.descricao;
                card.append(description);
            }
            list.append(card);
        });
    }

    async function carregarNotificacoes() {
        if (state.loaded.notificacoes) return;
        state.loaded.notificacoes = true;
        const lista = document.getElementById('coordNotificacoesLista');
        if (!lista) return;
        lista.innerHTML = '<div class="text-slate-500">Carregando notificações...</div>';
        const payload = new FormData();
        payload.append('csrf_token', window.csrfToken);
        const data = await fetchJson('../controllers/notificacao_controller.php?action=listar', { method: 'POST', body: payload });
        if (!data.sucesso) {
            lista.innerHTML = '<div class="text-red-500">Erro ao carregar notificações.</div>';
            return;
        }
        const notificacoes = data.dados || [];
        if (!notificacoes.length) {
            lista.innerHTML = '<div class="rounded-3xl border border-slate-800 bg-slate-900 p-5 text-slate-500">Nenhuma notificação no momento.</div>';
            return;
        }
        lista.innerHTML = notificacoes.map(item => `
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-5 ${item.lida ? 'opacity-70' : ''}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-white font-semibold">${item.titulo}</p>
                        <p class="text-slate-400 text-sm mt-2">${item.mensagem}</p>
                    </div>
                    <button data-id="${item.id_notificacao}" class="coord-notificacao-lida rounded-full border border-slate-700 px-3 py-2 text-xs text-slate-200 hover:bg-slate-800 transition">Marcar lida</button>
                </div>
                <p class="text-slate-500 text-xs mt-3">${item.criado_em ?? ''}</p>
            </div>
        `).join('');
        document.querySelectorAll('.coord-notificacao-lida').forEach(btn => btn.addEventListener('click', marcarNotificacaoLida));
    }

    async function marcarNotificacaoLida(event) {
        const id = event.currentTarget.dataset.id;
        if (!id) return;
        const payload = new FormData();
        payload.append('csrf_token', window.csrfToken);
        payload.append('id_notificacao', id);
        const data = await fetchJson('../controllers/notificacao_controller.php?action=marcar_lida', { method: 'POST', body: payload });
        if (data.sucesso) carregarNotificacoes();
    }

    async function marcarTodasNotificacoesLidas() {
        const payload = new FormData();
        payload.append('csrf_token', window.csrfToken);
        const data = await fetchJson('../controllers/notificacao_controller.php?action=marcar_todas_lidas', { method: 'POST', body: payload });
        if (data.sucesso) carregarNotificacoes();
    }

    setActiveSection('sec-dashboard');
</script>
</body>
</html>
