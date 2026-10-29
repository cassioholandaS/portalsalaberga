<?php

	session_start();

	if (!isset($_SESSION['id_usuario'])) {

		header("Location: ../index.php");
		exit;
	}


	require_once("../models/turmas_model.php");

	function formatarNomeExibicaoTurma(array $turma): string
	{
		$serie = trim((string) ($turma['serie_turma'] ?? ''));
		$nome = trim((string) ($turma['nome_turma'] ?? ''));
		if ($serie === '') {
			return $nome;
		}

		$serieRegex = preg_quote($serie, '/');
		$nome = preg_replace('/^(?:' . $serieRegex . '\s*(?:º|°)?\s*)+/iu', '', $nome);

		return trim($serie . 'º ' . $nome);
	}

	$modelTurma = new TurmasModel();
	$csrfToken = bin2hex(random_bytes(32));
	$_SESSION['csrf_token'] = $csrfToken;

	// Keep all classes visible as cards; retain the current teacher's class IDs for PDT access.
	$turmas = $modelTurma->listarTurmas($_SESSION['id_usuario']);
	$todasTurmas = $modelTurma->listarTurmas();
	$isPdtUsuario = !empty($turmas);
	$turmasPdtIds = [];
	foreach ($turmas as $turmaPdt) {
		$turmasPdtIds[(int) $turmaPdt['id_turma']] = true;
	}

	// Se houver exatamente uma turma, expor seu id para inicialização client-side
	$initialTurmaId = (is_array($turmas) && count($turmas) === 1) ? $turmas[0]['id_turma'] : '';

?>
<script>
	window.isPdtUsuario = <?= $isPdtUsuario ? 'true' : 'false' ?>;
	window.csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
	window.professorTurmas = <?= json_encode($turmas) ?>;
	window.initialTurmaId = <?= json_encode($initialTurmaId) ?>;
</script>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sistema Professor - Minha Turma</title>

	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

		.bg-white .text-gray-800,
		.bg-white .text-gray-700,
		.bg-white .text-gray-600,
		.bg-white .text-gray-500,
		.bg-white .text-gray-400 {
			color: var(--text) !important;
		}

		.card-dashboard,
		.card-aluno{
			transition: box-shadow .25s ease, background-color .18s ease, transform .18s ease;
			border-radius: 22px;
			padding: 24px;
			height: 160px;
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
			background: rgba(255,255,255,0.04) !important;
			border-color: var(--border) !important;
			color: var(--text) !important;
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
	<link rel="stylesheet" href="../assets/css/system-pdt.css?v=39">
	<script src="../assets/js/system-pdt-ui.js?v=2" defer></script>
</head>
<body class="pdt-interface">

<div class="flex min-h-screen">

	<div id="toastAluno" class="fixed top-6 left-1/2 z-50 hidden w-[calc(100%-2rem)] max-w-xl -translate-x-1/2 rounded-2xl border border-green-200 bg-white shadow-2xl px-5 py-4">
		<div class="flex items-start gap-3">
			<div id="toastAlunoDot" class="mt-1 h-3 w-3 rounded-full bg-green-600"></div>
			<div class="flex-1">
				<div class="flex items-center justify-between gap-4">
					<div>
						<p id="toastAlunoTitulo" class="text-sm font-semibold text-green-700">Mensagem</p>
										<p id="toastAlunoMensagem" class="text-xs text-gray-500">Operação concluída.</p>
					</div>
					<button id="fecharToastAluno" class="text-gray-400 hover:text-gray-700">Fechar</button>
				</div>
				<div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-green-100">
					<div id="toastAlunoBar" class="h-full w-full rounded-full bg-green-600 transition-all duration-200"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- SIDEBAR -->
	<aside id="pdtSidebar" class="w-[280px] sidebar-panel border-r fixed h-screen px-6 py-8 hidden lg:flex flex-col justify-between">

		<div>
			<div class="pdt-brand flex items-center gap-3">
				<span class="pdt-brand-mark"><img src="../../../../entradasaida/app/main/assets/img/logo.png" alt="" class="pdt-brand-logo"></span>

				<div>
					<h1 class="font-bold text-[18px]">Sistema <span>Salaberga</span></h1>
					<p>Professor Diretor de Turma</p>
				</div>
			</div>

			<nav class="flex flex-col gap-2">

					<button data-section="sec-inicio" class="menu-item active flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar effect-bar-select">
						<i class="bi bi-house-door"></i>
						<span>Início</span>
					</button>


					<button data-section="sec-turmas" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
						<i class="bi bi-journal-bookmark"></i>
						<span>Turmas</span>
					</button>

					<button data-section="sec-notas" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
						<i class="bi bi-journal-plus"></i>
						<span>Rendimento</span>
					</button>
					<?php if ($isPdtUsuario): ?>
						<button data-section="sec-pedagogico" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
							<i class="bi bi-clipboard2-pulse"></i>
							<span>Acompanhamento e conselho</span>
						</button>
						<button data-section="sec-calendario" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
							<i class="bi bi-calendar-event"></i>
							<span>Calendário</span>
						</button>
					<?php endif; ?>

					<button data-section="sec-ocorrencias" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
					<i class="bi bi-exclamation-triangle"></i>
					<span>Ocorrências</span>
				</button>

					<?php if ($isPdtUsuario): ?>
						<button data-section="sec-relatorios" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
							<i class="bi bi-bar-chart"></i>
							<span>Relatórios</span>
						</button>
					<?php endif; ?>

       

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
					<span class="pdt-account-role"><?= htmlspecialchars(ucfirst((string) ($_SESSION['tipo'] ?? 'professor')), ENT_QUOTES, 'UTF-8') ?></span>
				</span>
			</div>
			<button class="pdt-notifications-toggle" type="button" aria-label="Notificações" aria-controls="notificacoesPanel" aria-expanded="false">
				<i class="bi bi-bell" aria-hidden="true"></i>
				<span class="contadorNotificacoes hidden absolute -right-2 -top-2 min-w-5 rounded-full bg-red-600 px-1 text-xs text-white"></span>
			</button>
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

		<div id="menuPdtMobile" class="sidebar-panel fixed inset-y-0 left-0 z-50 w-[280px] hidden -translate-x-full transform border-r bg-[#001a0c] p-6 overflow-y-auto lg:hidden transition-transform duration-200 ease-out">
			<div class="flex items-center justify-between mb-8">
				<div class="pdt-brand flex items-center gap-3">
					<span class="pdt-brand-mark"><img src="../../../../entradasaida/app/main/assets/img/logo.png" alt="" class="pdt-brand-logo"></span>
					<div>
						<h1 class="font-bold text-[18px]">Sistema <span>Salaberga</span></h1>
						<p>Professor Diretor de Turma</p>
					</div>
				</div>
				<button id="closeMenuBtn" class="text-2xl text-white focus:outline-none focus:ring-2 focus:ring-green-500 rounded-full" aria-label="Fechar menu">
					<i class="bi bi-x-lg"></i>
				</button>
			</div>

			<nav class="flex flex-col gap-2">
				<button data-section="sec-inicio" class="menu-item active flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar effect-bar-select">
					<i class="bi bi-house-door"></i>
					<span>Início</span>
				</button>
				<button data-section="sec-turmas" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
					<i class="bi bi-journal-bookmark"></i>
					<span>Turmas</span>
				</button>
				<button data-section="sec-notas" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
					<i class="bi bi-journal-plus"></i>
					<span>Rendimento</span>
				</button>
				<?php if ($isPdtUsuario): ?>
					<button data-section="sec-pedagogico" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
						<i class="bi bi-clipboard2-pulse"></i>
						<span>Acompanhamento e conselho</span>
					</button>
					<button data-section="sec-calendario" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
						<i class="bi bi-calendar-event"></i>
						<span>Calendário</span>
					</button>
				<?php endif; ?>
				<button data-section="sec-ocorrencias" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
					<i class="bi bi-exclamation-triangle"></i>
					<span>Ocorrências</span>
				</button>
				<?php if ($isPdtUsuario): ?>
					<button data-section="sec-relatorios" class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl transition effect-bar">
						<i class="bi bi-bar-chart"></i>
						<span>Relatórios</span>
					</button>
				<?php endif; ?>
			</nav>

			<div class="pdt-account-tools mt-8">
				<div class="pdt-account-user">
					<span class="pdt-account-avatar"><?= htmlspecialchars(substr((string) ($_SESSION['nome'] ?? 'U'), 0, 1), ENT_QUOTES, 'UTF-8') ?></span>
					<span class="pdt-account-details">
						<span><?= htmlspecialchars((string) ($_SESSION['nome'] ?? 'Usuário'), ENT_QUOTES, 'UTF-8') ?></span>
						<span class="pdt-account-role"><?= htmlspecialchars(ucfirst((string) ($_SESSION['tipo'] ?? 'professor')), ENT_QUOTES, 'UTF-8') ?></span>
					</span>
				</div>
				<button class="pdt-notifications-toggle" type="button" aria-label="Notificações" aria-controls="notificacoesPanel" aria-expanded="false">
					<i class="bi bi-bell" aria-hidden="true"></i>
					<span class="contadorNotificacoes hidden absolute -right-2 -top-2 min-w-5 rounded-full bg-red-600 px-1 text-xs text-white"></span>
				</button>
			</div>
			<form action="../controllers/usuario_controller.php?action=logout" method="POST" class="mt-3">
				<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
				<button type="submit" class="pdt-logout-button">
					<i class="bi bi-box-arrow-right"></i>
					<span>Sair</span>
				</button>
			</form>
		</div>

		<div id="mobileMenuBackdrop" class="fixed inset-0 hidden bg-black/50 z-40 lg:hidden"></div>
		<div id="notificacoesPanel" class="pdt-notifications-panel hidden rounded-3xl border border-slate-700 bg-slate-950 p-4 shadow-2xl text-slate-200">
			<div class="flex items-center justify-between mb-3">
				<span class="text-sm font-semibold">Notificações</span>
				<button id="fecharNotificacoes" class="text-slate-400 hover:text-white text-sm" type="button">Fechar</button>
			</div>
			<div id="listaNotificacoes" class="max-h-96 space-y-3 overflow-y-auto text-sm text-slate-300"></div>
		</div>
	<!-- CONTEÚDO -->
	<div class="pdt-main-offset flex-1 lg:ml-[280px]">

		<button id="openMenuBtn" class="pdt-mobile-menu-button lg:hidden" type="button" aria-label="Abrir menu" aria-expanded="false">
			<i class="bi bi-list" aria-hidden="true"></i>
		</button>

		<!-- MAIN -->
		<main class="pdt-content p-8 main-shell">

			<div id="sec-inicio" class="secs">
				<!-- BOAS VINDAS -->
				<div class="mb-8">
					<h1 class="text-3xl font-bold text-gray-800 mb-2">
						Bem-vindo(a), <?= $_SESSION['nome'] ?>
					</h1>
					<p class="text-gray-500">
						Consulte as turmas cadastradas. O acesso completo é exclusivo ao PDT responsável.
					</p>
				</div>
				<!-- CARDS -->
				<div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start mb-8">
					<div class="space-y-6 xl:col-span-3">
						<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
							<div class="card-dashboard">
								<div class="flex items-center justify-between">
									<div>
										<p class="text-gray-500 text-sm mb-2">Alunos</p>
										<h2 id="contadorAlunos" class="text-4xl font-bold text-gray-800">0</h2>
										<span class="text-gray-400 text-sm">Total de alunos</span>
									</div>
									<div class="w-14 h-14 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center text-2xl">
										<i class="bi bi-people-fill"></i>
									</div>
								</div>
							</div>
							<div class="card-dashboard">
								<div class="flex items-center justify-between">
									<div>
										<p class="text-gray-500 text-sm mb-2">Ocorrências</p>
										<h2 id="contadorOcorrencias" class="text-4xl font-bold text-gray-800">0</h2>
										<span class="text-gray-400 text-sm">Este mês</span>
									</div>
									<div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl">
										<i class="bi bi-exclamation-circle-fill"></i>
									</div>
								</div>
							</div>
							<div class="card-dashboard">
								<div class="flex items-center justify-between">
									<div>
										<p class="text-gray-500 text-sm mb-2">Frequência Média</p>
										<div class="flex items-center space-x-0 text-4xl font-bold text-gray-800">
											<h2 id="contadorFrequencia" class="">0</h2>
											<span>%</span>
										</div>
										<span class="text-gray-400 text-sm">Este mês</span>
									</div>
									<div class="w-14 h-14 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center text-2xl">
										<i class="bi bi-person-check-fill"></i>
									</div>
								</div>
							</div>
						</div>
						<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
							<div class="bg-white border border-gray-200 rounded-2xl p-6">
								<div class="flex items-center justify-between mb-6">
									<h2 class="text-xl font-bold text-gray-800">Últimas ocorrências</h2>
									<button id="btnDashboardVerTodasOcorrencias" class="text-green-700 font-medium text-sm hover:underline">
										Ver todas
									</button>
								</div>
								<div id="listaUltimasOcorrencias" class="flex flex-col gap-5"></div>
							</div>
							<div class="bg-white border border-gray-200 rounded-2xl p-6">
								<div class="flex items-center justify-between mb-8">
									<h2 class="text-xl font-bold text-gray-800">Frequência da turma</h2>
									<select class="border border-gray-200 rounded-xl px-4 py-2 outline-none text-sm">
										<option>Este mês</option>
										<option>Últimos 3 meses</option>
									</select>
								</div>
								<div id="graficoFrequencia" class="w-full h-[300px] flex items-end justify-between gap-2"></div>
							</div>
						</div>
					</div>
					<div class="bg-white border border-gray-200 rounded-2xl p-6 self-start">
						<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
							<div>
								<p class="text-gray-500 text-sm mb-2">Painel de desempenho</p>
							</div>
							<div class="flex items-center gap-3">
								<select id="selectTurmaDesempenhoProfessor" class="border border-gray-200 rounded-xl px-4 py-2 outline-none text-sm hidden"></select>
							</div>
						</div>
						<div id="desempenhoProfessorMensagem" class="rounded-2xl border border-gray-200 p-4 text-gray-700 hidden"></div>
						<div id="desempenhoProfessorCard" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 hidden">
							<div class="bg-white border border-gray-200 rounded-2xl p-4">
								<p class="text-gray-500 text-sm mb-2">Turma</p>
								<h3 id="desempenhoProfessorNomeTurma" class="text-xl font-semibold text-gray-800">-</h3>
							</div>
							<div class="bg-white border border-gray-200 rounded-2xl p-4">
								<p class="text-gray-500 text-sm mb-2">Média geral</p>
								<h3 id="desempenhoProfessorMediaGeral" class="text-3xl font-bold text-gray-800">0,00</h3>
							</div>
							<div class="bg-white border border-gray-200 rounded-2xl p-4">
								<p class="text-gray-500 text-sm mb-2">Classificação</p>
								<h3 id="desempenhoProfessorClassificacao" class="text-xl font-semibold">-</h3>
							</div>
							<div class="bg-white border border-gray-200 rounded-2xl p-4">
								<p class="text-gray-500 text-sm mb-2">Total de alunos</p>
								<h3 id="desempenhoProfessorTotalAlunos" class="text-xl font-semibold text-gray-800">0</h3>
							</div>
							<div class="bg-white border border-gray-200 rounded-2xl p-4">
								<p class="text-gray-500 text-sm mb-2">Total de notas</p>
								<h3 id="desempenhoProfessorTotalNotas" class="text-xl font-semibold text-gray-800">0</h3>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="sec-turmas" class="secs hidden">

				<!-- GRID DE TURMAS -->
				<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
					<?php if(empty($todasTurmas)): ?>
						<div class="col-span-full bg-white border border-gray-200 rounded-xl px-6 hover:shadow-xl transition duration-300 h-full w-full flex flex-col justify-between">
							Nenhuma turma cadastrada no sistema.
						</div>
					<?php endif; ?>

					<?php foreach($todasTurmas as $turma): ?>
						<?php $souPdtDaTurma = isset($turmasPdtIds[(int) $turma['id_turma']]); ?>

						<div class="pdt-class-card h-full w-full">
							<div class="pdt-class-card-heading">
								<div class="pdt-class-icon">
									<i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
								</div>
								<div class="min-w-0">
									<div class="flex flex-wrap items-center gap-2">
										<h2 class="pdt-class-title">
											<?= htmlspecialchars((string) $turma['serie_turma'], ENT_QUOTES, 'UTF-8') ?>º <?= htmlspecialchars((string) $turma['nome_turma'], ENT_QUOTES, 'UTF-8') ?>
										</h2>
										<?php if($souPdtDaTurma): ?>
											<span class="inline-flex rounded-full bg-amber-500 px-2 py-1 text-xs font-semibold text-white">PDT</span>
										<?php endif; ?>
									</div>
									<p class="pdt-class-course">
										<?= htmlspecialchars((string) ($turma['nome_curso'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
									</p>
								</div>
							</div>

							<div class="pdt-class-details">
								<div class="pdt-class-detail">
									<span class="pdt-class-detail-label"><i class="bi bi-geo-alt" aria-hidden="true"></i>Sala</span>
									<span class="pdt-class-detail-value"><?= htmlspecialchars((string) $turma['sala_turma'], ENT_QUOTES, 'UTF-8') ?></span>
								</div>
								<div class="pdt-class-detail">
									<span class="pdt-class-detail-label"><i class="bi bi-calendar3" aria-hidden="true"></i>Ano letivo</span>
									<span class="pdt-class-detail-value"><?= htmlspecialchars((string) $turma['ano_letivo'], ENT_QUOTES, 'UTF-8') ?></span>
								</div>
								<div class="pdt-class-detail">
									<span class="pdt-class-detail-label"><i class="bi bi-person" aria-hidden="true"></i>Professor</span>
									<span class="pdt-class-detail-value"><?= htmlspecialchars((string) ($turma['professor_nome'] ?? 'Não informado'), ENT_QUOTES, 'UTF-8') ?></span>
								</div>
							</div>

							<?php if($souPdtDaTurma): ?>
								<div class="pdt-class-card-actions">
									<button data-turma-id="<?= (int) $turma['id_turma'] ?>" class="pdt-class-access btn-entrar-turma">
										<i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
										<span>Acessar turma</span>
									</button>
								</div>
							<?php else: ?>
								<p class="pdt-class-access-note">
									Acesso completo exclusivo ao PDT responsável.
								</p>
							<?php endif; ?>
						</div>

					<?php endforeach; ?>

				</div>

			</div>

			<?php if ($isPdtUsuario): ?>
			<div id="sec-pedagogico" class="secs hidden">
				<div class="mb-8">
					<h1 class="text-3xl font-bold text-gray-800">Acompanhamento pedagógico</h1>
					<p class="mt-1 text-gray-500">Registre intervenções individuais e atas de conselho das turmas pelas quais você responde como PDT.</p>
				</div>
				<div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5">
					<input id="turmaPedagogicoId" type="hidden" value="<?= (int) ($turmas[0]['id_turma'] ?? 0) ?>">
					<p class="mb-2 text-sm font-medium text-gray-700">Sua turma como PDT</p>
					<p class="text-lg font-semibold text-gray-800">
						<?= htmlspecialchars(
							$turmas
								? ($turmas[0]['serie_turma'] . 'º ' . $turmas[0]['nome_turma'] . ' · Sala ' . $turmas[0]['sala_turma'])
								: 'Nenhuma turma PDT vinculada',
							ENT_QUOTES,
							'UTF-8'
						) ?>
					</p>
				</div>

				<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
					<section class="rounded-2xl border border-gray-200 bg-white p-6">
						<h2 class="mb-4 text-xl font-bold text-gray-800">Novo acompanhamento individual</h2>
						<form id="formAcompanhamentoPedagogico" class="space-y-4">
							<div>
								<label for="alunoAcompanhamento" class="mb-1 block text-sm text-gray-700">Aluno</label>
								<select id="alunoAcompanhamento" required class="w-full rounded-xl border border-gray-200 px-3 py-3">
									<option value="">Selecione um aluno</option>
								</select>
							</div>
							<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
								<div>
									<label for="dataAcompanhamento" class="mb-1 block text-sm text-gray-700">Data do registro</label>
									<input id="dataAcompanhamento" type="date" required class="w-full rounded-xl border border-gray-200 px-3 py-3">
								</div>
								<div>
									<label for="motivoAcompanhamento" class="mb-1 block text-sm text-gray-700">Motivo</label>
									<input id="motivoAcompanhamento" maxlength="150" required class="w-full rounded-xl border border-gray-200 px-3 py-3" placeholder="Ex.: frequência, aprendizagem">
								</div>
							</div>
							<div>
								<label for="observacaoAcompanhamento" class="mb-1 block text-sm text-gray-700">Registro pedagógico</label>
								<textarea id="observacaoAcompanhamento" maxlength="10000" required rows="4" class="w-full rounded-xl border border-gray-200 px-3 py-3"></textarea>
							</div>
							<div>
								<label for="encaminhamentoAcompanhamento" class="mb-1 block text-sm text-gray-700">Encaminhamento</label>
								<textarea id="encaminhamentoAcompanhamento" maxlength="10000" rows="3" class="w-full rounded-xl border border-gray-200 px-3 py-3"></textarea>
							</div>
							<div>
								<label for="dataRetornoAcompanhamento" class="mb-1 block text-sm text-gray-700">Data prevista para retorno (opcional)</label>
								<input id="dataRetornoAcompanhamento" type="date" class="w-full rounded-xl border border-gray-200 px-3 py-3 md:max-w-xs">
							</div>
							<button type="submit" class="rounded-xl bg-green-700 px-5 py-3 font-medium text-white hover:bg-green-800">Salvar acompanhamento</button>
						</form>
					</section>

					<section class="rounded-2xl border border-gray-200 bg-white p-6">
						<h2 class="mb-4 text-xl font-bold text-gray-800">Registrar conselho de classe</h2>
						<form id="formConselhoClasse" class="space-y-4">
							<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
								<div>
									<label for="dataConselhoClasse" class="mb-1 block text-sm text-gray-700">Data</label>
									<input id="dataConselhoClasse" type="date" required class="w-full rounded-xl border border-gray-200 px-3 py-3">
								</div>
								<div>
									<label for="bimestreConselhoClasse" class="mb-1 block text-sm text-gray-700">Bimestre</label>
									<select id="bimestreConselhoClasse" class="w-full rounded-xl border border-gray-200 px-3 py-3">
										<option value="1">1º bimestre</option><option value="2">2º bimestre</option>
										<option value="3">3º bimestre</option><option value="4">4º bimestre</option>
									</select>
								</div>
							</div>
							<div>
								<label for="pautaConselhoClasse" class="mb-1 block text-sm text-gray-700">Pauta</label>
								<input id="pautaConselhoClasse" maxlength="200" required class="w-full rounded-xl border border-gray-200 px-3 py-3">
							</div>
							<div>
								<label for="ataConselhoClasse" class="mb-1 block text-sm text-gray-700">Ata / síntese das deliberações</label>
								<textarea id="ataConselhoClasse" maxlength="20000" required rows="5" class="w-full rounded-xl border border-gray-200 px-3 py-3"></textarea>
							</div>
							<button type="submit" class="rounded-xl bg-green-700 px-5 py-3 font-medium text-white hover:bg-green-800">Salvar conselho como rascunho</button>
						</form>
					</section>
				</div>

				<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
					<section class="rounded-2xl border border-gray-200 bg-white p-6">
						<h2 class="mb-4 text-xl font-bold text-gray-800">Acompanhamentos da turma</h2>
						<div id="listaAcompanhamentosPedagogicos" class="space-y-3 text-gray-500" aria-live="polite">Carregando...</div>
					</section>
					<section class="rounded-2xl border border-gray-200 bg-white p-6">
						<h2 class="mb-4 text-xl font-bold text-gray-800">Conselhos de classe</h2>
						<div id="listaConselhosClasse" class="space-y-4 text-gray-500" aria-live="polite">Carregando...</div>
					</section>
				</div>

				<section class="mt-6 rounded-2xl border border-gray-200 bg-white p-6">
					<h2 class="mb-4 text-xl font-bold text-gray-800">Deliberação individual do conselho</h2>
					<form id="formItemConselhoClasse" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
						<div>
							<label for="conselhoItemId" class="mb-1 block text-sm text-gray-700">Conselho em rascunho</label>
							<select id="conselhoItemId" required class="w-full rounded-xl border border-gray-200 px-3 py-3"><option value="">Selecione um conselho</option></select>
						</div>
						<div>
							<label for="alunoItemConselho" class="mb-1 block text-sm text-gray-700">Aluno</label>
							<select id="alunoItemConselho" required class="w-full rounded-xl border border-gray-200 px-3 py-3"><option value="">Selecione um aluno</option></select>
						</div>
						<div>
							<label for="observacaoItemConselho" class="mb-1 block text-sm text-gray-700">Observação / deliberação</label>
							<textarea id="observacaoItemConselho" maxlength="10000" required rows="3" class="w-full rounded-xl border border-gray-200 px-3 py-3"></textarea>
						</div>
						<div>
							<label for="encaminhamentoItemConselho" class="mb-1 block text-sm text-gray-700">Encaminhamento</label>
							<textarea id="encaminhamentoItemConselho" maxlength="10000" rows="3" class="w-full rounded-xl border border-gray-200 px-3 py-3"></textarea>
						</div>
						<div class="lg:col-span-2">
							<button type="submit" class="rounded-xl bg-green-700 px-5 py-3 font-medium text-white hover:bg-green-800">Salvar deliberação</button>
						</div>
					</form>
				</section>
			</div>
			<?php endif; ?>

			<!-- DETALHE TURMA -->
			<div id="sec-turma-detalhe" class="secs hidden">
				<div class="flex items-center gap-3 mb-8">
					<button id="btnVoltar" class="flex items-center gap-2 text-green-700 hover:text-green-800 transition font-medium">
						<i class="bi bi-arrow-left"></i>
						Voltar
					</button>
				</div>
				<div id="conteudoDetalhe" class="bg-white border border-gray-200 rounded-2xl p-8">
					<!-- Conteúdo da turma será preenchido aqui -->
				</div>
			</div>
			
			<!-- Notas -->
			<div id="sec-notas" class="secs hidden">
				<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
					<div>
						<h1 class="text-3xl font-bold text-gray-800">Lançamento de Notas</h1>
						<p class="text-gray-500 mt-1">Insira a média final dos alunos por bimestre. O rendimento será calculado a partir da média final.</p>
					</div>
				</div>

				<div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
					<div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:max-w-2xl">
						<select id="selectTurmaNotas" class="border border-gray-200 rounded-xl px-4 py-2 outline-none text-sm w-full sm:w-1/2">
							<option value="">Selecione uma turma</option>
							<?php foreach($turmas as $t): ?>
								<option value="<?= (int) $t['id_turma'] ?>"><?= htmlspecialchars(formatarNomeExibicaoTurma($t) . ' - ' . (string) $t['sala_turma'], ENT_QUOTES, 'UTF-8') ?></option>
							<?php endforeach; ?>
						</select>
						<select id="selectDisciplinaNotas" class="border border-gray-200 rounded-xl px-4 py-2 outline-none text-sm w-full sm:w-1/3">
							<option value="">Selecione a disciplina</option>
							<option value="Geografia">Geografia</option>
							<option value="Historia">História</option>
							<option value="Filosofia">Filosofia</option>
							<option value="Sociologia">Sociologia</option>
							<option value="Lingua Portuguesa">Língua Portuguesa</option>
							<option value="Educacao Fisica">Educação Física</option>
							<option value="Ingles">Inglês</option>
							<option value="Espanhol">Espanhol</option>
							<option value="Literatura">Literatura</option>
							<option value="Artes">Artes</option>
							<option value="Matematica I">Matemática I</option>
							<option value="Matematica II">Matemática II</option>
							<option value="Biologia">Biologia</option>
							<option value="Quimica">Química</option>
							<option value="Fisica">Física</option>
						</select>
					</div>
					<div class="flex items-center gap-3 w-full sm:w-auto">
						<select id="selectBimestreNotas" class="border border-gray-200 rounded-xl px-4 py-2 outline-none text-sm w-full sm:w-40">
							<option value="1">1º Bimestre</option>
							<option value="2">2º Bimestre</option>
							<option value="3">3º Bimestre</option>
							<option value="4">4º Bimestre</option>
						</select>
						<button id="btnCarregarAlunosNotas" class="h-11 px-4 w-full sm:w-[200px] rounded-xl bg-green-700 text-white hover:bg-green-800 transition flex items-center justify-center space-x-2">
							<i class="fa-solid fa-spinner"></i>
							<p>Carregar alunos</p>
						</button>
					</div>
				</div>

				<div class="bg-white border border-gray-200 rounded-2xl p-6">
					<div class="flex items-center justify-between mb-4">
						<h2 class="text-xl font-bold text-gray-800">Notas da turma</h2>
						<div>
							<button id="btnSalvarTodasNotas" class="h-10 px-4 rounded-xl bg-orange-500 text-white hover:bg-orange-600 transition">Salvar todas</button>
						</div>
					</div>
					<div id="areaNotas">
						<table id="notasTable" class="w-full text-left border-collapse">
							<thead>
								<tr class="text-sm text-gray-600">
									<th class="p-3 border-b">Matrícula</th>
									<th class="p-3 border-b">Aluno</th>
										    <th class="p-3 border-b">Média final</th>
									<th class="p-3 border-b">Ações</th>
								</tr>
							</thead>
							<tbody>
								<tr><td colspan="6" class="p-6 text-gray-500">Selecione uma turma e clique em "Carregar alunos".</td></tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Ocorrências -->
			<div id="sec-ocorrencias" class="secs hidden">
				<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
					<div>
						<h1 class="text-3xl font-bold text-gray-800">Ocorrências</h1>
						<p class="text-gray-500 mt-1">Gerencie e filtre as ocorrências registradas.</p>
					</div>
					<!-- <button id="btnNovaOcorrenciaSec" type="button" class="h-12 px-5 rounded-xl bg-red-600 text-white hover:bg-red-700 transition flex items-center gap-2">
						<i class="bi bi-plus-lg"></i>
						Nova ocorrência
					</button> -->
				</div>

				<div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
					<div class="relative w-full lg:max-w-md">
						<i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
						<input id="buscarOcorrenciasSec" type="text" placeholder="Buscar ocorrência, aluno ou turma" class="w-full border border-gray-200 rounded-xl h-12 pl-12 pr-4 outline-none">
					</div>
					<div class="flex flex-col sm:flex-row gap-3 w-full lg:max-w-xs">
						<select id="filtroOcorrenciasSec" class="w-full h-12 border border-gray-200 rounded-xl px-3 outline-none">
							<option value="mes" selected>Este mês</option>
							<option value="trimestre">Últimos 3 meses</option>
							<option value="semestre">Últimos 6 meses</option>
							<option value="ano">Este ano</option>
						</select>
						<button id="btnAtualizarOcorrenciasSec" type="button" class="h-12 px-4 rounded-full bg-[#DCFCE7] text-[#166534] hover:bg-[#f0fdf4] transition "><i class="bi bi-arrow-clockwise"></i></button>
					</div>
				</div>

				<div id="listaOcorrenciasSec" class="grid grid-cols-1 gap-5"></div>
			</div>

			<div id="sec-relatorios" class="secs hidden">
				<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
					<div>
						<h1 class="text-3xl font-bold text-gray-800">Relatórios</h1>
						<p class="text-gray-500 mt-1">Painel analítico com resumos de turmas, alunos, ocorrências e frequência.</p>
					</div>
					<button id="btnProfessorRelatoriosAtualizar" type="button" class="h-12 px-5 rounded-xl bg-green-700 text-white hover:bg-green-800 transition">Atualizar</button>
				</div>
				<div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
					<div class="flex flex-col sm:flex-row sm:items-center gap-3">
						<label for="profRelPeriodo" class="text-sm font-medium text-gray-700">Período</label>
						<select id="profRelPeriodo" class="h-12 border border-gray-200 rounded-xl px-4 outline-none">
							<option value="mes" selected>Último mês</option>
							<option value="trimestre">Últimos 3 meses</option>
							<option value="semestre">Últimos 6 meses</option>
							<option value="ano">Este ano</option>
						</select>
					</div>
					<div class="flex flex-wrap gap-3 items-center">
						<button id="btnProfessorRelatoriosExportarCSV" type="button" class="h-12 px-5 rounded-xl bg-slate-800 text-white hover:bg-slate-900 transition">Exportar CSV</button>
						<button id="btnProfessorRelatoriosExportarXLS" type="button" class="h-12 px-5 rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition">Exportar XLS</button>
						<button id="btnProfessorRelatoriosExportarOcorrenciasCSV" type="button" class="h-12 px-5 rounded-xl bg-cyan-700 text-white hover:bg-cyan-800 transition">Ocorrências CSV</button>
						<button id="btnProfessorRelatoriosExportarOcorrenciasXLS" type="button" class="h-12 px-5 rounded-xl bg-cyan-600 text-white hover:bg-cyan-700 transition">Ocorrências XLS</button>
						<button id="btnProfessorRelatoriosImprimir" type="button" class="h-12 px-5 rounded-xl bg-slate-200 text-gray-800 hover:bg-slate-300 transition">Imprimir</button>
					</div>
				</div>
				<div class="mb-4 text-sm text-gray-500">Resumo do período: <span id="profRelResumoPeriodo">último mês</span></div>
				<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
					<div class="card-dashboard">
						<p class="text-gray-500 text-sm mb-2">Turmas</p>
						<h2 id="profRelTotalTurmas" class="text-4xl font-bold text-gray-800">0</h2>
						<span class="text-gray-400 text-sm">Total de turmas</span>
					</div>
					<div class="card-dashboard">
						<p class="text-gray-500 text-sm mb-2">Alunos</p>
						<h2 id="profRelTotalAlunos" class="text-4xl font-bold text-gray-800">0</h2>
						<span class="text-gray-400 text-sm">Total de alunos</span>
					</div>
					<div class="card-dashboard">
						<p class="text-gray-500 text-sm mb-2">Ocorrências</p>
						<h2 id="profRelTotalOcorrencias" class="text-4xl font-bold text-gray-800">0</h2>
						<span class="text-gray-400 text-sm">Este mês</span>
					</div>
					<div class="card-dashboard">
						<p class="text-gray-500 text-sm mb-2">Frequência média</p>
						<h2 id="profRelMediaFrequencia" class="text-4xl font-bold text-gray-800">0%</h2>
						<span class="text-gray-400 text-sm">Este mês</span>
					</div>
					<div class="card-dashboard">
						<p class="text-gray-500 text-sm mb-2">Acompanhamentos</p>
						<h2 id="profRelTotalAcompanhamentos" class="text-4xl font-bold text-gray-800">0</h2>
						<span class="text-gray-400 text-sm">Criados no período</span>
					</div>
					<div class="card-dashboard">
						<p class="text-gray-500 text-sm mb-2">Em aberto</p>
						<h2 id="profRelAcompanhamentosAbertos" class="text-4xl font-bold text-gray-800">0</h2>
						<span class="text-gray-400 text-sm">Do período selecionado</span>
					</div>
					<div class="card-dashboard">
						<p class="text-gray-500 text-sm mb-2">Conselhos</p>
						<h2 id="profRelTotalConselhos" class="text-4xl font-bold text-gray-800">0</h2>
						<span class="text-gray-400 text-sm">Registrados no período</span>
					</div>
				</div>
				<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
					<div class="bg-white border border-gray-200 rounded-2xl p-6">
						<h2 class="text-xl font-bold text-gray-800 mb-4">Ocorrências recentes</h2>
						<div id="profRelOcorrenciasRecentes" class="space-y-4 text-gray-500">Carregando...</div>
					</div>
					<div class="bg-white border border-gray-200 rounded-2xl p-6">
						<h2 class="text-xl font-bold text-gray-800 mb-4">Top turmas (ocorrências)</h2>
						<div id="profRelTopTurmasOcorrencias" class="space-y-4 text-gray-500">Carregando...</div>
					</div>
					<div class="bg-white border border-gray-200 rounded-2xl p-6">
						<h2 class="text-xl font-bold text-gray-800 mb-4">Ocorrências por tipo</h2>
						<div id="profRelOcorrenciasPorTipo" class="space-y-4 text-gray-500">Carregando...</div>
					</div>
				</div>
				<div class="bg-white border border-gray-200 rounded-2xl p-6">
					<h2 class="text-xl font-bold text-gray-800 mb-4">Turmas por curso</h2>
					<div id="profRelTurmasPorCurso" class="grid gap-3 md:grid-cols-2"></div>
				</div>
			</div>

			<?php if ($isPdtUsuario): ?>
			<div id="sec-calendario" class="secs hidden">
				<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
					<div>
						<h1 class="text-3xl font-bold text-gray-800">Calendário</h1>
						<p class="text-gray-500 mt-1">Eventos com base em ocorrências e compromissos.</p>
					</div>
					<div class="flex gap-3">
						<button id="profCalendarioPrev" type="button" class="h-12 px-5 rounded-xl bg-slate-200 text-gray-800 hover:bg-slate-300 transition">Anterior</button>
						<button id="profCalendarioNext" type="button" class="h-12 px-5 rounded-xl bg-slate-200 text-gray-800 hover:bg-slate-300 transition">Próximo</button>
					</div>
				</div>
				<div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6">
					<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
						<div>
							<p id="profCalendarioMesAno" class="text-xl font-semibold text-gray-800"></p>
							<p class="text-gray-500 text-sm">Selecione um dia para ver os eventos.</p>
						</div>
						<p id="profCalendarioContagem" class="text-gray-500 text-sm"></p>
					</div>
					<div id="profCalendarioGrid" class="pdt-calendar-grid grid grid-cols-7 gap-2 text-center text-sm"></div>
				</div>
				<div class="bg-white border border-gray-200 rounded-2xl p-5">
					<h2 class="text-xl font-bold text-gray-800 mb-4">Eventos do mês</h2>
					<div id="profCalendarioEventos" class="space-y-4 text-gray-500">Carregando...</div>
				</div>
			</div>
			<?php endif; ?>
		</main>
	</div>

</div>

<div id="perfilPedagogicoModal" class="fixed inset-0 z-[320] hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm" aria-hidden="true">
	<section class="modal-card max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="perfilPedagogicoTitulo">
		<div class="mb-5 flex items-start justify-between gap-4">
			<div>
				<h2 id="perfilPedagogicoTitulo" class="text-2xl font-bold text-gray-800">Perfil pedagógico</h2>
				<p id="perfilPedagogicoIdentificacao" class="mt-1 text-sm text-gray-500">Carregando...</p>
			</div>
			<button id="fecharPerfilPedagogico" type="button" class="rounded-lg px-3 py-2 text-gray-600 hover:bg-gray-100" aria-label="Fechar perfil">Fechar</button>
		</div>
		<div id="perfilPedagogicoConteudo" class="space-y-5" aria-live="polite"></div>
	</section>
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
		const contadores = document.querySelectorAll('.contadorNotificacoes');
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
			const total = data.nao_lidas || 0;
			contadores.forEach(contador => {
				contador.textContent = total > 0 ? total : '';
				contador.classList.toggle('hidden', total <= 0);
			});
			lista.innerHTML = itens.length ? itens.map((item) => `
					<div class="rounded-2xl border ${item.lida ? 'border-slate-800 bg-slate-900/70' : 'border-emerald-500/30 bg-emerald-500/10'} p-3">
						<p class="font-semibold text-white">${item.titulo}</p>
						<p class="mt-1 text-slate-300">${item.mensagem}</p>
						<p class="mt-2 text-[11px] text-slate-500">${item.criado_em || ''}</p>
					</div>
				`).join('') : '<div class="text-slate-400">Nenhuma notificação</div>';
		} catch (e) {
			console.error('Erro ao carregar notificações:', e);
		}
	}

	const notificacoesPanelProfessor = document.getElementById('notificacoesPanel');
	const fecharNotificacoesProfessor = document.getElementById('fecharNotificacoes');
	const botoesNotificacoesProfessor = document.querySelectorAll('.pdt-notifications-toggle');

	botoesNotificacoesProfessor.forEach(botao => {
		botao.addEventListener('click', event => {
			event.stopPropagation();
			const abrir = notificacoesPanelProfessor.classList.contains('hidden');
			notificacoesPanelProfessor.classList.toggle('hidden', !abrir);
			botoesNotificacoesProfessor.forEach(item => item.setAttribute('aria-expanded', String(abrir)));
			if (abrir) carregarNotificacoes();
		});
	});

	fecharNotificacoesProfessor?.addEventListener('click', () => {
		notificacoesPanelProfessor.classList.add('hidden');
		botoesNotificacoesProfessor.forEach(item => item.setAttribute('aria-expanded', 'false'));
	});

	document.addEventListener('click', event => {
		if (
			notificacoesPanelProfessor
			&& !notificacoesPanelProfessor.classList.contains('hidden')
			&& !notificacoesPanelProfessor.contains(event.target)
			&& !Array.from(botoesNotificacoesProfessor).some(botao => botao.contains(event.target))
		) {
			notificacoesPanelProfessor.classList.add('hidden');
			botoesNotificacoesProfessor.forEach(item => item.setAttribute('aria-expanded', 'false'));
		}
	});

	// MENU MOBILE
	const openMenuBtn = document.getElementById("openMenuBtn");
	const closeMenuBtn = document.getElementById("closeMenuBtn");
	const menuPdtMobile = document.getElementById("menuPdtMobile");
	const mobileMenuBackdrop = document.getElementById("mobileMenuBackdrop");

	function openMobileMenu() {
		if (!menuPdtMobile || !mobileMenuBackdrop) return;
		menuPdtMobile.classList.remove("hidden");
		mobileMenuBackdrop.classList.remove("hidden");
		setTimeout(() => {
			menuPdtMobile.classList.remove("-translate-x-full");
		}, 10);
	}

	function closeMobileMenu() {
		if (!menuPdtMobile || !mobileMenuBackdrop) return;
		menuPdtMobile.classList.add("-translate-x-full");
		mobileMenuBackdrop.classList.add("hidden");
		setTimeout(() => {
			menuPdtMobile.classList.add("hidden");
		}, 220);
	}

	if (openMenuBtn) {
		openMenuBtn.onclick = openMobileMenu;
	}

	if (closeMenuBtn) {
		closeMenuBtn.onclick = closeMobileMenu;
	}

	if (mobileMenuBackdrop) {
		mobileMenuBackdrop.onclick = closeMobileMenu;
	}

	// MENU LATERAL PDT
	const btnOptionsPdt = document.querySelectorAll(".menu-item[data-section]");
	const secsPdt = document.querySelectorAll(".secs");

	btnOptionsPdt.forEach((btn) => {
		btn.addEventListener("click", () => {
			btnOptionsPdt.forEach(item => item.classList.remove("active"));
			btn.classList.add("active");

			const sectionId = btn.dataset.section;
			if (!sectionId) return;

			const sectionTarget = document.getElementById(sectionId);
			if (!sectionTarget) return;

			secsPdt.forEach(sec => sec.classList.add("hidden"));
			sectionTarget.classList.remove("hidden");

			if (sectionId === 'sec-ocorrencias') carregarOcorrenciasSec();
			if (sectionId === 'sec-relatorios') carregarRelatoriosSec();
			if (sectionId === 'sec-calendario') carregarCalendarioSec();
			if (sectionId === 'sec-pedagogico') carregarModuloPedagogico();
		});
	});

	const turmaPedagogicoId = document.getElementById('turmaPedagogicoId');
	const formAcompanhamentoPedagogico = document.getElementById('formAcompanhamentoPedagogico');
	const formConselhoClasse = document.getElementById('formConselhoClasse');
	const formItemConselhoClasse = document.getElementById('formItemConselhoClasse');
	let alunosTurmaPedagogico = [];

	function dataLocalISO() {
		const hoje = new Date();
		hoje.setMinutes(hoje.getMinutes() - hoje.getTimezoneOffset());
		return hoje.toISOString().slice(0, 10);
	}

	async function requisicaoModuloPedagogico(action, options = {}, queryParams = {}) {
		const query = new URLSearchParams({ action, ...queryParams });
		const resposta = await fetch(`../controllers/pdt_pedagogico_controller.php?${query.toString()}`, options);
		const resultado = await resposta.json();
		if (!resposta.ok || !resultado.sucesso) {
			throw new Error(resultado.erro || 'Não foi possível concluir a operação.');
		}
		return resultado;
	}

	function preencherAlunosPedagogico(alunos) {
		alunosTurmaPedagogico = Array.isArray(alunos) ? alunos : [];
		['alunoAcompanhamento', 'alunoItemConselho'].forEach(id => {
			const select = document.getElementById(id);
			if (!select) return;
			select.replaceChildren(new Option('Selecione um aluno', ''));
			alunosTurmaPedagogico.forEach(aluno => {
				select.add(new Option(aluno.nome, aluno.id_aluno));
			});
		});
	}

	function elementoTexto(tag, className, text) {
		const element = document.createElement(tag);
		element.className = className;
		element.textContent = text;
		return element;
	}

	function renderizarAcompanhamentosPedagogicos(registros) {
		const lista = document.getElementById('listaAcompanhamentosPedagogicos');
		if (!lista) return;
		lista.replaceChildren();
		if (!registros.length) {
			lista.append(elementoTexto('p', 'py-4 text-gray-500', 'Nenhum acompanhamento registrado nesta turma.'));
			return;
		}
		registros.forEach(registro => {
			const card = document.createElement('article');
			card.className = 'rounded-xl border border-gray-200 p-4';
			card.append(
				elementoTexto('h3', 'font-semibold text-gray-800', `${registro.aluno_nome} · ${registro.motivo}`),
				elementoTexto('p', 'mt-1 text-xs text-gray-500', `Registro: ${registro.data_registro} · Matrícula: ${registro.matricula}`),
				elementoTexto('p', 'mt-3 whitespace-pre-wrap text-sm text-gray-700', registro.observacao)
			);
			if (registro.encaminhamento) card.append(elementoTexto('p', 'mt-2 whitespace-pre-wrap text-sm text-gray-600', `Encaminhamento: ${registro.encaminhamento}`));
			if (registro.data_retorno) card.append(elementoTexto('p', 'mt-2 text-sm text-gray-600', `Retorno previsto: ${registro.data_retorno}`));
			const status = document.createElement('select');
			status.className = 'mt-3 rounded-lg border border-gray-200 px-3 py-2 text-sm';
			[
				['aberto', 'Aberto'],
				['em_acompanhamento', 'Em acompanhamento'],
				['concluido', 'Concluído']
			].forEach(([value, label]) => status.add(new Option(label, value)));
			status.value = registro.status;
			status.addEventListener('change', async () => {
				try {
					await requisicaoModuloPedagogico('atualizar_status_acompanhamento', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify({ id_acompanhamento: registro.id_acompanhamento, status: status.value })
					});
					await carregarModuloPedagogico();
				} catch (erro) {
					alert(erro.message);
				}
			});
			card.append(status);
			lista.append(card);
		});
	}

	function renderizarConselhosClasse(conselhos) {
		const lista = document.getElementById('listaConselhosClasse');
		const selectConselho = document.getElementById('conselhoItemId');
		if (!lista || !selectConselho) return;
		lista.replaceChildren();
		selectConselho.replaceChildren(new Option('Selecione um conselho em rascunho', ''));
		conselhos.forEach(conselho => {
			const card = document.createElement('article');
			card.className = 'rounded-xl border border-gray-200 p-4';
			card.append(
				elementoTexto('h3', 'font-semibold text-gray-800', `Conselho · ${conselho.bimestre}º bimestre · ${conselho.data_conselho}`),
				elementoTexto('p', 'mt-1 text-sm font-medium text-gray-700', conselho.pauta),
				elementoTexto('p', 'mt-2 whitespace-pre-wrap text-sm text-gray-600', conselho.ata),
				elementoTexto('p', 'mt-2 text-xs font-semibold uppercase text-gray-500', conselho.status === 'finalizado' ? 'Finalizado' : 'Rascunho')
			);
			(conselho.alunos || []).forEach(item => {
				card.append(elementoTexto('p', 'mt-3 border-t border-gray-100 pt-3 text-sm text-gray-700', `${item.aluno_nome}: ${item.observacao}`));
				if (item.encaminhamento) card.append(elementoTexto('p', 'pl-3 text-sm text-gray-500', `Encaminhamento: ${item.encaminhamento}`));
			});
			if (conselho.status === 'rascunho') {
				selectConselho.add(new Option(`${conselho.data_conselho} · ${conselho.pauta}`, conselho.id_conselho));
				const finalizar = document.createElement('button');
				finalizar.type = 'button';
				finalizar.className = 'mt-3 rounded-lg border border-green-700 px-3 py-2 text-sm font-medium text-green-800 hover:bg-green-50';
				finalizar.textContent = 'Finalizar conselho';
				finalizar.addEventListener('click', async () => {
					if (!confirm('Finalizar este conselho? Os registros individuais ficarão bloqueados para edição.')) return;
					try {
						await requisicaoModuloPedagogico('finalizar_conselho', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json' },
							body: JSON.stringify({ id_conselho: conselho.id_conselho })
						});
						await carregarModuloPedagogico();
					} catch (erro) {
						alert(erro.message);
					}
				});
				card.append(finalizar);
			}
			lista.append(card);
		});
		if (!conselhos.length) lista.append(elementoTexto('p', 'py-4 text-gray-500', 'Nenhum conselho registrado nesta turma.'));
	}

	async function carregarModuloPedagogico() {
		if (!turmaPedagogicoId || !turmaPedagogicoId.value) return;
		const idTurma = turmaPedagogicoId.value;
		try {
			const [alunosResposta, dados] = await Promise.all([
				fetch(`../controllers/aluno_controller.php?action=listar_alunos&id_turma=${encodeURIComponent(idTurma)}`).then(resposta => resposta.json()),
				requisicaoModuloPedagogico('listar', {}, { id_turma: idTurma })
			]);
			if (!alunosResposta.sucesso) throw new Error(alunosResposta.erro || 'Não foi possível carregar os alunos.');
			preencherAlunosPedagogico(alunosResposta.dados);
			renderizarAcompanhamentosPedagogicos(dados.acompanhamentos || []);
			renderizarConselhosClasse(dados.conselhos || []);
		} catch (erro) {
			['listaAcompanhamentosPedagogicos', 'listaConselhosClasse'].forEach(id => {
				const list = document.getElementById(id);
				if (list) {
					list.textContent = `Erro ao carregar módulo pedagógico: ${erro.message}`;
					list.classList.add('text-red-600');
				}
			});
			console.error('Erro ao carregar módulo pedagógico:', erro);
		}
	}

	if (turmaPedagogicoId?.value) {
		['dataAcompanhamento', 'dataConselhoClasse'].forEach(id => {
			const input = document.getElementById(id);
			if (input) input.value = dataLocalISO();
		});
		carregarModuloPedagogico();
	}

	formAcompanhamentoPedagogico?.addEventListener('submit', async event => {
		event.preventDefault();
		try {
			await requisicaoModuloPedagogico('criar_acompanhamento', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					id_turma: turmaPedagogicoId.value,
					id_aluno: document.getElementById('alunoAcompanhamento').value,
					data_registro: document.getElementById('dataAcompanhamento').value,
					motivo: document.getElementById('motivoAcompanhamento').value,
					observacao: document.getElementById('observacaoAcompanhamento').value,
					encaminhamento: document.getElementById('encaminhamentoAcompanhamento').value,
					data_retorno: document.getElementById('dataRetornoAcompanhamento').value
				})
			});
			formAcompanhamentoPedagogico.reset();
			document.getElementById('dataAcompanhamento').value = dataLocalISO();
			await carregarModuloPedagogico();
		} catch (erro) {
			alert(erro.message);
		}
	});

	formConselhoClasse?.addEventListener('submit', async event => {
		event.preventDefault();
		try {
			await requisicaoModuloPedagogico('criar_conselho', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					id_turma: turmaPedagogicoId.value,
					data_conselho: document.getElementById('dataConselhoClasse').value,
					bimestre: document.getElementById('bimestreConselhoClasse').value,
					pauta: document.getElementById('pautaConselhoClasse').value,
					ata: document.getElementById('ataConselhoClasse').value
				})
			});
			formConselhoClasse.reset();
			document.getElementById('dataConselhoClasse').value = dataLocalISO();
			await carregarModuloPedagogico();
		} catch (erro) {
			alert(erro.message);
		}
	});

	formItemConselhoClasse?.addEventListener('submit', async event => {
		event.preventDefault();
		try {
			await requisicaoModuloPedagogico('adicionar_item_conselho', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					id_conselho: document.getElementById('conselhoItemId').value,
					id_aluno: document.getElementById('alunoItemConselho').value,
					observacao: document.getElementById('observacaoItemConselho').value,
					encaminhamento: document.getElementById('encaminhamentoItemConselho').value
				})
			});
			formItemConselhoClasse.reset();
			await carregarModuloPedagogico();
		} catch (erro) {
			alert(erro.message);
		}
	});

	// NAVEGAÇÃO TURMA DETALHE
	const btnVoltar = document.getElementById('btnVoltar');
	const secTurmaDetalhe = document.getElementById('sec-turma-detalhe');
	const secTurmas = document.getElementById('sec-turmas');
	let turmaIdModal = null;
	let turmaAtualId = null;

	function getTurmaIdModal() {
		if (!turmaIdModal) {
			turmaIdModal = document.getElementById('turmaIdModal');
		}
		return turmaIdModal;
	}

	document.addEventListener('click', (e) => {
		const btnEntrar = e.target.closest('.btn-entrar-turma');
		if (!btnEntrar) return;

		turmaAtualId = btnEntrar.getAttribute('data-turma-id');
		console.log('? Botão "Entrar" clicado - Turma ID:', turmaAtualId);
		
		// Encontrar os dados da turma
		const turmaCard = btnEntrar.closest('.bg-white');
		let nomeTurma = 'Turma';
		let sala = '-';
		let ano = '2026';
		
		if (turmaCard) {
			const h2 = turmaCard.querySelector('h2');
			if (h2) nomeTurma = h2.textContent;
			
			const rows = turmaCard.querySelectorAll('.flex.items-center.justify-between');
			if (rows[0]) sala = rows[0].querySelector('.font-semibold')?.textContent || '-';
			if (rows[1]) ano = rows[1].querySelector('.font-semibold')?.textContent || '2026';
		}

		// Definir turma no modal
		const modalInput = getTurmaIdModal();
		console.log('Antes de setar turmaIdModal - element:', modalInput, 'value:', modalInput?.value);
		if (modalInput) {
			modalInput.value = turmaAtualId;
			console.log('turmaIdModal.value setado para:', modalInput.value);
		} else {
			console.error('? turmaIdModal é NULL!');
		}

		// Preencher detalhe com informações da turma
		const conteudo = document.getElementById('conteudoDetalhe');
		if (conteudo) {
			conteudo.innerHTML = `
				<div class="mb-8">
					<h1 class="text-4xl font-bold text-gray-800 mb-2">${nomeTurma}</h1>
					<p class="text-gray-500">Sala ${sala} - Ano letivo ${ano}</p>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
					<div class="card-dashboard">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-gray-500 text-sm mb-2">Alunos</p>
								<h2 id="contadorAlunosDetalhe" class="text-4xl font-bold text-gray-800">0</h2>
								<span class="text-gray-400 text-sm">Registrados</span>
							</div>
							<div class="w-14 h-14 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center text-2xl">
								<i class="bi bi-people-fill"></i>
							</div>
						</div>
					</div>
					<div class="card-dashboard">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-gray-500 text-sm mb-2">Sala</p>
								<h2 class="text-4xl font-bold text-gray-800">${sala}</h2>
								<span class="text-gray-400 text-sm">Localização</span>
							</div>
							<div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl">
								<i class="bi bi-door-closed"></i>
							</div>
						</div>
					</div>
					<div class="card-dashboard">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-gray-500 text-sm mb-2">Ano letivo</p>
								<h2 class="text-4xl font-bold text-gray-800">${ano}</h2>
								<span class="text-gray-400 text-sm">Período</span>
							</div>
							<div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl">
								<i class="bi bi-calendar2"></i>
							</div>
						</div>
					</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
					<button id="btnGerenciarAlunos" class="h-12 px-4 rounded-xl bg-green-700 text-white hover:bg-green-800 transition font-medium flex items-center justify-center gap-2">
						<i class="bi bi-people"></i>
						<span class="hidden sm:inline">Gerenciar alunos</span>
						<span class="sm:hidden">Alunos</span>
					</button>
					<button id="btnRealizarFrequenciaTurma" class="h-12 px-4 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition font-medium flex items-center justify-center gap-2">
						<i class="bi bi-check-circle"></i>
						<span class="hidden sm:inline">Registrar frequência</span>
						<span class="sm:hidden">Frequência</span>
					</button>
					<button id="btnAplicarOcorrenciaTurma" class="h-12 px-4 rounded-xl bg-red-600 text-white hover:bg-red-700 transition font-medium flex items-center justify-center gap-2">
						<i class="bi bi-exclamation-triangle-fill"></i>
						<span class="hidden sm:inline">Nova Ocorrência</span>
						<span class="sm:hidden">Ocorrência</span>
					</button>
				</div>
			`;
			atualizarContadorAlunosDetalhe(turmaAtualId);

			// Adicionar evento ao botão de gerenciar alunos
			const btnGerenciar = conteudo.querySelector('#btnGerenciarAlunos');
			if (btnGerenciar) {
				btnGerenciar.addEventListener('click', () => {
					abrirGerenciarAlunos(turmaAtualId, nomeTurma);
				});
			}

				// Adicionar evento ao botão de aplicar ocorrência dentro do card (criado dinamicamente)
				const btnAplicarOcorrenciaTurma = conteudo.querySelector('#btnAplicarOcorrenciaTurma');
				if (btnAplicarOcorrenciaTurma) {
					btnAplicarOcorrenciaTurma.addEventListener('click', () => {
						// Abrir modal de ocorrência e selecionar a turma atual
						abrirModalOcorrencia();
						if (typeof selectTurmaOcorrencia !== 'undefined' && selectTurmaOcorrencia) {
							selectTurmaOcorrencia.value = turmaAtualId;
							// Disparar change para carregar alunos da turma
							selectTurmaOcorrencia.dispatchEvent(new Event('change'));
						}
					});
				}

				const btnRealizarFrequenciaTurma = conteudo.querySelector('#btnRealizarFrequenciaTurma');
				if (btnRealizarFrequenciaTurma) {
					btnRealizarFrequenciaTurma.addEventListener('click', async () => {
						if (dataFrequenciaInput) {
							dataFrequenciaInput.value = new Date().toISOString().slice(0, 10);
						}
						await abrirModalAlunos(turmaAtualId, nomeTurma);
						try {
							await abrirRealizarFrequencia();
						} catch (err) {
							console.error('Erro ao abrir realizar frequência:', err);
						}
					});
				}
		}

		// Esconder sec-turmas e mostrar detalhe
		if (secTurmas) secTurmas.classList.add('hidden');
		if (secTurmaDetalhe) secTurmaDetalhe.classList.remove('hidden');
	});

	if (btnVoltar) {
		btnVoltar.addEventListener('click', () => {
			if (secTurmaDetalhe) secTurmaDetalhe.classList.add('hidden');
			if (secTurmas) secTurmas.classList.remove('hidden');
			turmaAtualId = null;
		});
	}

	const listaUltimasOcorrencias = document.getElementById('listaUltimasOcorrencias');
	const btnDashboardVerTodasOcorrencias = document.getElementById('btnDashboardVerTodasOcorrencias');
	let listaOcorrenciasSec = null;
	let btnNovaOcorrenciaSec = null;
	let buscarOcorrenciasSec = null;
	let filtroOcorrenciasSec = null;
	let btnAtualizarOcorrenciasSec = null;
	let overlayConfirmExcluirOcorrencia = null;
	let fecharConfirmExcluirOcorrenciaBtn = null;
	let cancelarExcluirOcorrenciaBtn = null;
	let confirmarExcluirOcorrenciaBtn = null;
	let confirmTipoOcorrencia = null;
	let ocorrenciasSecCache = [];
	let ocorrenciaExcluirId = null;
	document.addEventListener('DOMContentLoaded', () => {
		listaOcorrenciasSec = document.getElementById('listaOcorrenciasSec');
		btnNovaOcorrenciaSec = document.getElementById('btnNovaOcorrenciaSec');
		buscarOcorrenciasSec = document.getElementById('buscarOcorrenciasSec');
		filtroOcorrenciasSec = document.getElementById('filtroOcorrenciasSec');
		btnAtualizarOcorrenciasSec = document.getElementById('btnAtualizarOcorrenciasSec');
		overlayConfirmExcluirOcorrencia = document.getElementById('overlayConfirmExcluirOcorrencia');
		fecharConfirmExcluirOcorrenciaBtn = document.getElementById('fecharConfirmExcluirOcorrencia');
		cancelarExcluirOcorrenciaBtn = document.getElementById('cancelarExcluirOcorrencia');
		confirmarExcluirOcorrenciaBtn = document.getElementById('confirmarExcluirOcorrencia');
		confirmTipoOcorrencia = document.getElementById('confirmTipoOcorrencia');

		if (listaOcorrenciasSec) {
			listaOcorrenciasSec.addEventListener('click', (e) => {
				const btn = e.target.closest('.btn-excluir-ocorrencia');
				if (!btn) return;
				const id = btn.getAttribute('data-id');
				const tipo = btn.getAttribute('data-tipo') || 'Ocorrência';
				abrirModalExcluirOcorrencia(id, tipo);
			});
		}

		if (btnAtualizarOcorrenciasSec) {
			btnAtualizarOcorrenciasSec.addEventListener('click', carregarOcorrenciasSec);
		}

		if (buscarOcorrenciasSec) {
			buscarOcorrenciasSec.addEventListener('input', () => renderizarCardsOcorrenciasSec(ocorrenciasSecCache));
		}

		if (filtroOcorrenciasSec) {
			filtroOcorrenciasSec.addEventListener('change', carregarOcorrenciasSec);
		}

		if (fecharConfirmExcluirOcorrenciaBtn) {
			fecharConfirmExcluirOcorrenciaBtn.addEventListener('click', fecharModalExcluirOcorrencia);
		}

		if (cancelarExcluirOcorrenciaBtn) {
			cancelarExcluirOcorrenciaBtn.addEventListener('click', fecharModalExcluirOcorrencia);
		}

		if (confirmarExcluirOcorrenciaBtn) {
			confirmarExcluirOcorrenciaBtn.addEventListener('click', async () => {
				if (!ocorrenciaExcluirId) return;
				try {
					const res = await fetch('../controllers/ocorrencia_controller.php?action=excluir_ocorrencia', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-CSRF-Token': window.csrfToken
						},
						body: JSON.stringify({
							id_ocorrencia: ocorrenciaExcluirId,
							csrf_token: window.csrfToken
						})
					});
					const json = await res.json();
					if (json.sucesso) {
						fecharModalExcluirOcorrencia();
						carregarOcorrenciasSec();
						carregarUltimasOcorrencias();
						mostrarToastAluno('Ocorrência cancelada com sucesso!', 'ocorrencia_cancelada');
					} else {
						alert('Erro ao cancelar ocorrência: ' + (json.erro || 'Erro desconhecido'));
					}
				} catch (err) {
					console.error(err);
					alert('Erro de conexão');
				}
			});
		}
	});

	async function carregarUltimasOcorrencias(){
		if (!listaUltimasOcorrencias) return;
		listaUltimasOcorrencias.innerHTML = '<div class="text-gray-500">Carregando...</div>';
		try {
			const res = await fetch('../controllers/ocorrencia_controller.php?action=listar_ocorrencias&filtro=mes');
			const json = await res.json();
			if (!json.sucesso) {
				listaUltimasOcorrencias.innerHTML = '<div class="text-red-500">Erro ao carregar ocorrências</div>';
				return;
			}
			renderizarCardsOcorrenciasDashboard(json.dados || []);
		} catch (err) {
			console.error(err);
			listaUltimasOcorrencias.innerHTML = '<div class="text-red-500">Erro de conexão</div>';
		}
	}

	async function carregarOcorrenciasSec(){
		if (!listaOcorrenciasSec) return;
		listaOcorrenciasSec.innerHTML = '<div class="text-gray-500">Carregando ocorrências...</div>';
		try {
			const filtro = filtroOcorrenciasSec ? filtroOcorrenciasSec.value : 'mes';
			const res = await fetch('../controllers/ocorrencia_controller.php?action=listar_ocorrencias&filtro=' + encodeURIComponent(filtro));
			const json = await res.json();
			if (!json.sucesso) {
				listaOcorrenciasSec.innerHTML = '<div class="text-red-500">Erro ao carregar ocorrências</div>';
				return;
			}
			ocorrenciasSecCache = json.dados || [];
			renderizarCardsOcorrenciasSec(ocorrenciasSecCache);
		} catch (err) {
			console.error(err);
			listaOcorrenciasSec.innerHTML = '<div class="text-red-500">Erro de conexão</div>';
		}
	}

	function renderizarGraficoRosca(container, dados, chaveRotulo, chaveValor, descricao) {
		if (!container) return;
		container.replaceChildren();
		const itens = dados
			.map(item => ({
				rotulo: String(item[chaveRotulo] ?? 'Sem identificação'),
				valor: Number(item[chaveValor])
			}))
			.filter(item => Number.isFinite(item.valor) && item.valor > 0);
		const total = itens.reduce((soma, item) => soma + item.valor, 0);

		if (total <= 0) {
			container.append(elementoTexto('p', 'py-4 text-gray-500', 'Sem dados para exibir neste período.'));
			return;
		}

		const cores = ['#18b5d5', '#e84e43', '#06bf8f', '#a84fa3', '#f18700', '#f2cf20', '#5267c9', '#ec6b9b'];
		let anguloAtual = 0;
		const segmentos = itens.map((item, indice) => {
			const inicio = anguloAtual;
			anguloAtual += item.valor / total * 360;
			return `${cores[indice % cores.length]} ${inicio}deg ${anguloAtual}deg`;
		});

		const grafico = document.createElement('div');
		grafico.className = 'relative h-40 w-40 shrink-0 rounded-full';
		grafico.style.background = `conic-gradient(${segmentos.join(', ')})`;
		grafico.setAttribute('role', 'img');
		grafico.setAttribute(
			'aria-label',
			`${descricao}: ${itens.map(item => `${item.rotulo}, ${item.valor}`).join('; ')}`
		);

		const centro = document.createElement('div');
		centro.className = 'absolute inset-[24%] flex flex-col items-center justify-center rounded-full bg-white text-center shadow-inner';
		centro.append(
			elementoTexto('strong', 'text-2xl font-bold text-gray-800', String(total)),
			elementoTexto('span', 'mt-1 text-[10px] leading-tight text-gray-500', 'Total')
		);
		grafico.append(centro);

		const legenda = document.createElement('ul');
		legenda.className = 'w-full space-y-2';
		itens.forEach((item, indice) => {
			const linha = document.createElement('li');
			linha.className = 'flex items-center justify-between gap-3 text-sm';
			const identificacao = document.createElement('span');
			identificacao.className = 'flex min-w-0 items-center gap-2 text-gray-600';
			const marcador = document.createElement('span');
			marcador.className = 'h-3 w-3 shrink-0 rounded-sm';
			marcador.style.backgroundColor = cores[indice % cores.length];
			const rotulo = elementoTexto('span', 'truncate', item.rotulo);
			identificacao.append(marcador, rotulo);
			const valor = elementoTexto(
				'strong',
				'shrink-0 text-gray-800',
				`${item.valor} · ${((item.valor / total) * 100).toFixed(1).replace('.', ',')}%`
			);
			linha.append(identificacao, valor);
			legenda.append(linha);
		});

		container.className = 'flex flex-col items-center gap-5';
		container.append(grafico, legenda);
	}

	async function carregarRelatoriosSec(force = false) {
		const periodo = document.getElementById('profRelPeriodo')?.value || 'mes';
		const totalTurmasEl = document.getElementById('profRelTotalTurmas');
		const totalAlunosEl = document.getElementById('profRelTotalAlunos');
		const totalOcorrenciasEl = document.getElementById('profRelTotalOcorrencias');
		const mediaFrequenciaEl = document.getElementById('profRelMediaFrequencia');
		const totalAcompanhamentosEl = document.getElementById('profRelTotalAcompanhamentos');
		const acompanhamentosAbertosEl = document.getElementById('profRelAcompanhamentosAbertos');
		const totalConselhosEl = document.getElementById('profRelTotalConselhos');
		const recentesEl = document.getElementById('profRelOcorrenciasRecentes');
		const topEl = document.getElementById('profRelTopTurmasOcorrencias');
		const tipoEl = document.getElementById('profRelOcorrenciasPorTipo');
		const porCursoEl = document.getElementById('profRelTurmasPorCurso');
		const detalhesTurmasEl = document.getElementById('profRelDetalhesTurmas');
		const detalhesOcorrenciasEl = document.getElementById('profRelDetalhesOcorrencias');
		const resumoPeriodoEl = document.getElementById('profRelResumoPeriodo');
		const ocorrenciasPeriodoLabelEl = document.getElementById('profRelOcorrenciasPeriodoLabel');

		if (!totalTurmasEl || !totalAlunosEl || !totalOcorrenciasEl || !mediaFrequenciaEl) return;
		if (recentesEl) recentesEl.innerHTML = 'Carregando...';
		if (topEl) topEl.innerHTML = 'Carregando...';
		if (tipoEl) tipoEl.innerHTML = 'Carregando...';
		if (porCursoEl) porCursoEl.innerHTML = 'Carregando...';
		if (detalhesTurmasEl) detalhesTurmasEl.innerHTML = 'Carregando...';
		if (detalhesOcorrenciasEl) detalhesOcorrenciasEl.innerHTML = 'Carregando...';
		if (resumoPeriodoEl) resumoPeriodoEl.textContent = document.getElementById('profRelPeriodo')?.selectedOptions?.[0]?.textContent || 'último mês';
		if (ocorrenciasPeriodoLabelEl) ocorrenciasPeriodoLabelEl.textContent = document.getElementById('profRelPeriodo')?.selectedOptions?.[0]?.textContent || 'último mês';

		try {
			const [res, turmasRes, ocorrenciasRes] = await Promise.all([
				fetch(`../controllers/relatorio_controller.php?action=resumo_geral&periodo=${encodeURIComponent(periodo)}`),
				fetch(`../controllers/relatorio_controller.php?action=listar_turmas_relatorio&periodo=${encodeURIComponent(periodo)}`),
				fetch(`../controllers/relatorio_controller.php?action=listar_ocorrencias_relatorio&periodo=${encodeURIComponent(periodo)}`)
			]);

			const data = await res.json();
			const turmasData = await turmasRes.json();
			const ocorrenciasData = await ocorrenciasRes.json();

			if (!data.sucesso || !turmasData.sucesso || !ocorrenciasData.sucesso) {
				const errorHtml = '<div class="text-red-500">Erro ao carregar relatórios.</div>';
				if (recentesEl) recentesEl.innerHTML = errorHtml;
				if (topEl) topEl.innerHTML = errorHtml;
				if (tipoEl) tipoEl.innerHTML = errorHtml;
				if (porCursoEl) porCursoEl.innerHTML = errorHtml;
				if (detalhesTurmasEl) detalhesTurmasEl.innerHTML = errorHtml;
				if (detalhesOcorrenciasEl) detalhesOcorrenciasEl.innerHTML = errorHtml;
				return;
			}

			totalTurmasEl.textContent = data.total_turmas ?? 0;
			totalAlunosEl.textContent = data.total_alunos ?? 0;
			totalOcorrenciasEl.textContent = data.total_ocorrencias_periodo ?? 0;
			mediaFrequenciaEl.textContent = `${data.media_frequencia_periodo ?? 0}%`;
			if (totalAcompanhamentosEl) totalAcompanhamentosEl.textContent = data.acompanhamentos_periodo ?? 0;
			if (acompanhamentosAbertosEl) acompanhamentosAbertosEl.textContent = data.acompanhamentos_abertos ?? 0;
			if (totalConselhosEl) totalConselhosEl.textContent = data.conselhos_periodo ?? 0;

			if (recentesEl) {
				recentesEl.innerHTML = (data.ocorrencias_recentes || []).length ? (data.ocorrencias_recentes || []).map(item => `
					<div class="rounded-2xl border border-gray-200 p-4">
						<p class="text-gray-500 text-sm">${item.data_ocorrencia || ''}</p>
						<h3 class="text-gray-800 font-semibold mt-2">${item.tipo_ocorrencia || ''}</h3>
						<p class="text-gray-500 text-sm mt-1">${item.nome_aluno || ''}</p>
					</div>
				`).join('') : '<div class="text-gray-500">Nenhuma ocorrência recente.</div>';
			}

			renderizarGraficoRosca(topEl, data.top_turmas_ocorrencias || [], 'turma', 'total', 'Ocorrências nas principais turmas');
			renderizarGraficoRosca(tipoEl, data.ocorrencias_por_tipo || [], 'tipo_ocorrencia', 'total', 'Ocorrências por tipo');
			renderizarGraficoRosca(porCursoEl, data.turmas_por_curso || [], 'nome_curso', 'total', 'Turmas por curso');

			if (detalhesTurmasEl) {
				detalhesTurmasEl.innerHTML = (turmasData.dados || []).length ? (turmasData.dados || []).map(item => `
					<div class="rounded-2xl border border-gray-200 p-4">
						<div class="flex items-center justify-between gap-4">
							<div>
								<p class="text-gray-500 text-sm">${item.turma}</p>
								<p class="text-gray-800 font-semibold mt-2">Alunos: ${item.total_alunos} · Ocorrências: ${item.ocorrencias_periodo}</p>
								<p class="mt-1 text-sm text-gray-500">Acompanhamentos: ${item.acompanhamentos_periodo} · Em aberto: ${item.acompanhamentos_abertos} · Conselhos: ${item.conselhos_periodo}</p>
							</div>
							<div class="text-slate-700 font-semibold">${item.frequencia_media_periodo ?? 0}%</div>
						</div>
					</div>
				`).join('') : '<div class="text-gray-500">Sem dados de turmas para o período.</div>';
			}

			if (detalhesOcorrenciasEl) {
				detalhesOcorrenciasEl.innerHTML = (ocorrenciasData.dados || []).length ? (ocorrenciasData.dados || []).map(item => `
					<div class="rounded-2xl border border-gray-200 p-4">
						<p class="text-gray-500 text-sm">${item.data_ocorrencia || ''} | ${item.tipo_ocorrencia || ''}</p>
						<h3 class="text-gray-800 font-semibold mt-2">${item.nome_aluno || ''}</h3>
						<p class="text-gray-500 text-sm mt-1">${item.turma || ''}  Sala ${item.sala || ''}</p>
						<p class="text-gray-500 text-sm mt-2">${item.descricao || ''}</p>
					</div>
				`).join('') : '<div class="text-gray-500">Nenhuma ocorrência registrada no período.</div>';
			}
		} catch (err) {
			console.error(err);
			const errorHtml = '<div class="text-red-500">Erro ao carregar relatórios.</div>';
			if (recentesEl) recentesEl.innerHTML = errorHtml;
			if (topEl) topEl.innerHTML = errorHtml;
			if (tipoEl) tipoEl.innerHTML = errorHtml;
			if (porCursoEl) porCursoEl.innerHTML = errorHtml;
			if (detalhesTurmasEl) detalhesTurmasEl.innerHTML = errorHtml;
			if (detalhesOcorrenciasEl) detalhesOcorrenciasEl.innerHTML = errorHtml;
		}
	}

	async function carregarCalendarioSec(force = false) {
		if (typeof window.professorCalendarYear === 'undefined') {
			window.professorCalendarYear = new Date().getFullYear();
			window.professorCalendarMonth = new Date().getMonth();
		}
		const response = await fetch('../controllers/relatorio_controller.php?action=calendario_eventos&ano=' + window.professorCalendarYear + '&mes=' + (window.professorCalendarMonth + 1));
		const data = await response.json();
		if (!response.ok || !data.sucesso) {
			window.professorCalendarEvents = [];
			const eventsEl = document.getElementById('profCalendarioEventos');
			if (eventsEl) eventsEl.textContent = data.erro || 'Não foi possível carregar o calendário.';
			console.error('Erro ao carregar calendário:', data.erro || response.statusText);
			return;
		}
		window.professorCalendarEvents = data.dados || [];
		renderProfessorCalendar();
	}

	function renderProfessorCalendar(selectedDate = null) {
		const grid = document.getElementById('profCalendarioGrid');
		const label = document.getElementById('profCalendarioMesAno');
		const counter = document.getElementById('profCalendarioContagem');
		const eventsEl = document.getElementById('profCalendarioEventos');
		if (!grid || !label || !counter || !eventsEl) return;

		const monthName = new Date(window.professorCalendarYear, window.professorCalendarMonth, 1).toLocaleString('pt-BR', { month: 'long' });
		label.textContent = `${monthName.charAt(0).toUpperCase() + monthName.slice(1)} ${window.professorCalendarYear}`;
		const firstDay = new Date(window.professorCalendarYear, window.professorCalendarMonth, 1).getDay();
		const totalDays = new Date(window.professorCalendarYear, window.professorCalendarMonth + 1, 0).getDate();
		const eventsByDate = (window.professorCalendarEvents || []).reduce((acc, item) => {
			if (!item.evento_data) return acc;
			acc[item.evento_data] = acc[item.evento_data] || [];
			acc[item.evento_data].push(item);
			return acc;
		}, {});

		grid.innerHTML = '';
		['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'].forEach(day => {
			const header = document.createElement('div');
			header.className = 'pdt-calendar-weekday text-sm font-semibold text-gray-500';
			header.textContent = day;
			grid.appendChild(header);
		});

		for (let blank = 0; blank < firstDay; blank++) {
			const cell = document.createElement('div');
			cell.className = 'pdt-calendar-empty h-20 rounded-2xl border';
			grid.appendChild(cell);
		}

		for (let day = 1; day <= totalDays; day++) {
			const dateKey = `${window.professorCalendarYear}-${String(window.professorCalendarMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
			const events = eventsByDate[dateKey] || [];
			const cell = document.createElement('button');
			cell.type = 'button';
			cell.className = `pdt-calendar-day group h-20 rounded-2xl border p-3 text-left transition${events.length ? ' has-events' : ''}`;
			cell.innerHTML = `
				<div class="flex items-center justify-between">
					<span class="pdt-calendar-date font-semibold">${day}</span>
					${events.length ? `<span class="pdt-calendar-event-count rounded-full px-2 py-1 text-[11px]">${events.length}</span>` : ''}
				</div>
			`;
			const title = document.createElement('p');
			title.className = 'pdt-calendar-day-title mt-2 text-xs';
			title.textContent = events[0]?.titulo || '';
			cell.appendChild(title);
			cell.addEventListener('click', () => renderProfessorCalendarEvents(dateKey));
			grid.appendChild(cell);
		}

		counter.textContent = `${(window.professorCalendarEvents || []).length} evento(s) no período`;
		renderProfessorCalendarEvents(selectedDate);
	}

	function renderProfessorCalendarEvents(dateKey = null) {
		const eventsEl = document.getElementById('profCalendarioEventos');
		if (!eventsEl) return;
		const filtered = dateKey ? (window.professorCalendarEvents || []).filter(item => item.evento_data === dateKey) : (window.professorCalendarEvents || []);
		if (!filtered.length) {
			eventsEl.innerHTML = '<div class="rounded-2xl border border-gray-200 bg-white p-5 text-gray-500">Nenhum evento disponível.</div>';
			return;
		}
		eventsEl.replaceChildren();
		filtered.forEach(item => {
			const card = document.createElement('article');
			card.className = 'pdt-calendar-event rounded-2xl border border-gray-200 bg-white p-5';
			const header = document.createElement('div');
			header.className = 'mb-2 flex items-center justify-between gap-3';
			const details = document.createElement('div');
			details.append(
				elementoTexto('p', 'text-gray-500 text-sm', item.evento_data || ''),
				elementoTexto('h3', 'mt-2 text-gray-800 text-lg font-semibold', item.titulo || '')
			);
			header.append(details, elementoTexto('span', 'pdt-calendar-event-type rounded-full px-3 py-1 text-xs', item.tipo || ''));
			card.append(header);
			if (item.descricao) card.append(elementoTexto('p', 'text-gray-600 text-sm', item.descricao));
			eventsEl.append(card);
		});
	}

	document.getElementById('btnProfessorRelatoriosAtualizar')?.addEventListener('click', carregarRelatoriosSec);
	document.getElementById('profRelPeriodo')?.addEventListener('change', carregarRelatoriosSec);
	document.getElementById('btnProfessorRelatoriosExportarCSV')?.addEventListener('click', () => exportarRelatorioProfessor('turmas', 'csv'));
	document.getElementById('btnProfessorRelatoriosExportarXLS')?.addEventListener('click', () => exportarRelatorioProfessor('turmas', 'xls'));
	document.getElementById('btnProfessorRelatoriosExportarOcorrenciasCSV')?.addEventListener('click', () => exportarRelatorioProfessor('ocorrencias', 'csv'));
	document.getElementById('btnProfessorRelatoriosExportarOcorrenciasXLS')?.addEventListener('click', () => exportarRelatorioProfessor('ocorrencias', 'xls'));
	document.getElementById('btnProfessorRelatoriosImprimir')?.addEventListener('click', () => window.print());

	function exportarRelatorioProfessor(report, type) {
		const periodo = document.getElementById('profRelPeriodo')?.value || 'mes';
		const url = `../controllers/relatorio_controller.php?action=exportar_relatorio&report=${encodeURIComponent(report)}&type=${encodeURIComponent(type)}&periodo=${encodeURIComponent(periodo)}`;
		window.open(url, '_blank');
	}
	document.getElementById('profCalendarioPrev')?.addEventListener('click', () => {
		window.professorCalendarMonth = window.professorCalendarMonth === 0 ? 11 : window.professorCalendarMonth - 1;
		if (window.professorCalendarMonth === 11) window.professorCalendarYear -= 1;
		carregarCalendarioSec(true);
	});
	document.getElementById('profCalendarioNext')?.addEventListener('click', () => {
		window.professorCalendarMonth = window.professorCalendarMonth === 11 ? 0 : window.professorCalendarMonth + 1;
		if (window.professorCalendarMonth === 0) window.professorCalendarYear += 1;
		carregarCalendarioSec(true);
	});

	function formatarDataHora(value){
		if (!value) return '';
		const dt = new Date(value);
		return dt.toLocaleDateString('pt-BR') + ' ' + dt.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
	}

	function renderizarCardsOcorrenciasDashboard(ocorrencias){
		if (!listaUltimasOcorrencias) return;
		if (!ocorrencias || ocorrencias.length === 0) {
			listaUltimasOcorrencias.innerHTML = '<div class="text-gray-500">Nenhuma ocorrência registrada.</div>';
			return;
		}
		listaUltimasOcorrencias.innerHTML = '';
		ocorrencias.forEach(o => {
			const card = document.createElement('div');
			card.className = 'bg-white border border-gray-100 rounded-2xl p-4 shadow-sm';
			card.innerHTML = `
				<div class="flex items-start justify-between gap-3">
					<div>
						<div class="font-semibold text-gray-800">${o.tipo_ocorrencia || 'Ocorrência'}</div>
						<div class="text-sm text-gray-500 mt-1">${o.nome_aluno || ''} - ${o.matricula || ''} - ${o.nome_turma || ''}</div>
					</div>
					<div class="text-xs text-gray-400">${formatarDataHora(o.data_ocorrencia)}</div>
				</div>
				${o.descricao ? `<p class="text-sm text-gray-500 mt-3">${o.descricao}</p>` : ''}
			`;
			listaUltimasOcorrencias.appendChild(card);
		});
	}

	function renderizarCardsOcorrenciasSec(ocorrencias){
		if (!listaOcorrenciasSec) return;
		const termo = buscarOcorrenciasSec ? buscarOcorrenciasSec.value.trim().toLowerCase() : '';
		const filtradas = ocorrencias.filter(o => {
			if (!termo) return true;
			return (o.tipo_ocorrencia || '').toLowerCase().includes(termo)
				|| (o.nome_aluno || '').toLowerCase().includes(termo)
				|| (o.matricula || '').toLowerCase().includes(termo)
				|| (o.nome_turma || '').toLowerCase().includes(termo)
				|| (o.descricao || '').toLowerCase().includes(termo);
		});
		if (filtradas.length === 0) {
			listaOcorrenciasSec.innerHTML = '<div class="text-gray-500">Nenhuma ocorrência encontrada.</div>';
			return;
		}
		listaOcorrenciasSec.innerHTML = '';
		filtradas.forEach(o => {
			const card = document.createElement('div');
			card.className = 'bg-white border border-gray-100 rounded-2xl p-6 shadow-sm';
			card.innerHTML = `
				<div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
					<div>
						<div class="font-semibold text-gray-800 text-lg">${o.tipo_ocorrencia || 'Ocorrência'}</div>
						<div class="text-sm text-gray-500 mt-2">${o.nome_aluno || ''} (${o.matricula || ''}) - ${o.nome_turma || ''}</div>
						<div class="text-xs text-gray-400 mt-3">${o.descricao || 'Sem descrição'}</div>
					</div>
				<div class="flex flex-col items-end gap-3">
					<div class="text-right text-xs text-gray-400">${formatarDataHora(o.data_ocorrencia)}</div>
					<button type="button" class="btn-excluir-ocorrencia inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-2 text-sm font-medium hover:bg-red-100 transition"
						data-id="${o.id_ocorrencia}" data-tipo="${o.tipo_ocorrencia || 'Ocorrência'}">
						<i class="bi bi-x-circle"></i> Cancelar
					</button>
				</div>
			</div>
		`;
		listaOcorrenciasSec.appendChild(card);
	});
}

if (listaOcorrenciasSec) {
	listaOcorrenciasSec.addEventListener('click', (e) => {
		const btn = e.target.closest('.btn-excluir-ocorrencia');
		if (!btn) return;
		const id = btn.getAttribute('data-id');
		const tipo = btn.getAttribute('data-tipo') || 'Ocorrência';
		abrirModalExcluirOcorrencia(id, tipo);
	});
}

if (btnAtualizarOcorrenciasSec) {
		btnAtualizarOcorrenciasSec.addEventListener('click', carregarOcorrenciasSec);
	}

	if (buscarOcorrenciasSec) {
		buscarOcorrenciasSec.addEventListener('input', () => renderizarCardsOcorrenciasSec(ocorrenciasSecCache));
	}

	if (filtroOcorrenciasSec) {
		filtroOcorrenciasSec.addEventListener('change', carregarOcorrenciasSec);
	}

	function abrirModalExcluirOcorrencia(id, tipo) {
		if (!overlayConfirmExcluirOcorrencia) return;
		ocorrenciaExcluirId = id;
		if (confirmTipoOcorrencia) confirmTipoOcorrencia.textContent = tipo;
		overlayConfirmExcluirOcorrencia.classList.remove('hidden');
		overlayConfirmExcluirOcorrencia.classList.add('flex');
	}

	function fecharModalExcluirOcorrencia() {
		if (!overlayConfirmExcluirOcorrencia) return;
		overlayConfirmExcluirOcorrencia.classList.add('hidden');
		overlayConfirmExcluirOcorrencia.classList.remove('flex');
		ocorrenciaExcluirId = null;
	}

	if (fecharConfirmExcluirOcorrenciaBtn) {
		fecharConfirmExcluirOcorrenciaBtn.addEventListener('click', fecharModalExcluirOcorrencia);
	}

	if (cancelarExcluirOcorrenciaBtn) {
		cancelarExcluirOcorrenciaBtn.addEventListener('click', fecharModalExcluirOcorrencia);
	}

	if (confirmarExcluirOcorrenciaBtn) {
		confirmarExcluirOcorrenciaBtn.addEventListener('click', async () => {
			if (!ocorrenciaExcluirId) return;
			try {
				const res = await fetch('../controllers/ocorrencia_controller.php?action=excluir_ocorrencia', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-Token': window.csrfToken
					},
					body: JSON.stringify({
						id_ocorrencia: ocorrenciaExcluirId,
						csrf_token: window.csrfToken
					})
				});
				const json = await res.json();
				if (json.sucesso) {
					fecharModalExcluirOcorrencia();
					carregarOcorrenciasSec();
					carregarUltimasOcorrencias();
					mostrarToastAluno('Ocorrência cancelada com sucesso!', 'ocorrencia_cancelada');
				} else {
					alert('Erro ao cancelar ocorrência: ' + (json.erro || 'Erro desconhecido'));
				}
			} catch (err) {
				console.error(err);
				alert('Erro de conexão');
			}
		});
	}

		// --- Funções da seção Notas (front-end) ---

		const selectTurmaNotas = document.getElementById('selectTurmaNotas');
		const selectDisciplinaNotas = document.getElementById('selectDisciplinaNotas');
		const selectBimestreNotas = document.getElementById('selectBimestreNotas');
		const btnCarregarAlunosNotas = document.getElementById('btnCarregarAlunosNotas');
		const notasTable = document.getElementById('notasTable');
		const btnSalvarTodasNotas = document.getElementById('btnSalvarTodasNotas');
		const notasCache = {};
		let turmaNotasAtual = null;
		let bimestreNotasAtual = selectBimestreNotas ? selectBimestreNotas.value : '1';

		if (btnCarregarAlunosNotas && selectTurmaNotas) {
			btnCarregarAlunosNotas.addEventListener('click', () => {
				const turmaId = selectTurmaNotas.value;
				const disciplina = selectDisciplinaNotas ? selectDisciplinaNotas.value : '';
				if (!turmaId) return mostrarToastAluno('Selecione uma turma antes de carregar alunos', 'erro');
				if (!disciplina) return mostrarToastAluno('Selecione a disciplina antes de carregar alunos', 'erro');
				carregarAlunosTurmaNotas(turmaId, selectBimestreNotas ? selectBimestreNotas.value : '1');
			});
		}

		if (btnSalvarTodasNotas) {
			btnSalvarTodasNotas.addEventListener('click', salvarNotas);
		}

		async function carregarAlunosTurmaNotas(turmaId, bimestre = selectBimestreNotas ? selectBimestreNotas.value : '1'){
			const disciplina = selectDisciplinaNotas ? selectDisciplinaNotas.value : '';
			const tbody = notasTable.querySelector('tbody');
			tbody.innerHTML = '<tr><td colspan="4" class="p-6 text-gray-500">Carregando alunos...</td></tr>';
			turmaNotasAtual = turmaId;
			bimestreNotasAtual = bimestre;
			try {
				const [alunosRes, notasRes] = await Promise.all([
					fetch('../controllers/aluno_controller.php?action=listar_alunos&id_turma=' + encodeURIComponent(turmaId)),
					fetch('../controllers/aluno_controller.php?action=listar_notas&id_turma=' + encodeURIComponent(turmaId) + '&bimestre=' + encodeURIComponent(bimestre) + '&disciplina=' + encodeURIComponent(disciplina))
				]);

				const alunosJson = await alunosRes.json();
				const notasJson = await notasRes.json();

				if (!alunosJson.sucesso) {
					tbody.innerHTML = '<tr><td colspan="4" class="p-6 text-red-500">Erro ao carregar alunos</td></tr>';
					return;
				}

				const notasMap = notasJson.sucesso ? notasJson.dados || {} : {};
				if (!notasJson.sucesso) {
					mostrarToastAluno('Erro ao carregar notas: ' + (notasJson.erro || 'Erro desconhecido'), 'erro');
				}

				notasCache[turmaId] = notasCache[turmaId] || {};
				notasCache[turmaId][bimestre] = notasCache[turmaId][bimestre] || {};
				notasCache[turmaId][bimestre][disciplina] = notasMap;

				const rows = alunosJson.dados.map(a => {
					const alunoId = a.id_aluno || a.id;
					const nota = notasMap[alunoId] || {};
					const media_final = nota.media_final !== null && nota.media_final !== undefined ? nota.media_final : '';
					const temNotasSalvas = media_final !== '';

					return `
						<tr class="border-b">
							<td class="p-3">${a.matricula || ''}</td>
							<td class="p-3">${a.nome || ''}</td>
							<td class="p-3">
								<div class="display-val text-gray-700" data-aluno="${alunoId}" data-field="media_final" style="display:${temNotasSalvas ? '' : 'none'}">${media_final === '' ? '-' : media_final}</div>
								<input data-aluno="${alunoId}" data-field="media_final" class="w-28 h-9 border rounded px-2 notas-input" type="number" step="0.01" min="0" max="10" value="${media_final}" style="display:${temNotasSalvas ? 'none' : ''}">
							</td>
							<td class="p-3"><button type="button" data-aluno="${alunoId}" class="btn-salvar-individual h-9 px-3 rounded ${temNotasSalvas ? 'bg-green-700 text-white' : 'bg-green-700 text-white'} transition">${temNotasSalvas ? 'Editar' : 'Salvar'}</button></td>
						</tr>
					`;
				}).join('');

				tbody.innerHTML = rows || '<tr><td colspan="4" class="p-6 text-gray-500">Nenhum aluno encontrado nesta turma.</td></tr>';

				document.querySelectorAll('.btn-salvar-individual').forEach(btn => {
					btn.addEventListener('click', async () => {
						const id = btn.getAttribute('data-aluno');
						const label = (btn.textContent || '').trim().toLowerCase();
						if (label === 'editar') {
							btn.textContent = 'Salvar';
							btn.classList.remove('bg-white','border','border-green-700','text-green-700');
							btn.classList.add('bg-green-700','text-white');
							const inputs = Array.from(document.querySelectorAll(`.notas-input[data-aluno="${id}"]`));
							const divs = Array.from(document.querySelectorAll(`.display-val[data-aluno="${id}"]`));
							inputs.forEach(input => input.style.display = '');
							divs.forEach(div => div.style.display = 'none');
							if (inputs[0]) inputs[0].focus();
							return;
						}
						await salvarNotasIndividual(id);
					});
				});

				atualizarContadorDesempenho();
			} catch (err) {
				console.error(err);
				tbody.innerHTML = '<tr><td colspan="4" class="p-6 text-red-500">Erro ao carregar dados da turma.</td></tr>';
			}
		}

		async function salvarNotasIndividual(alunoId){
			const bimestre = selectBimestreNotas ? selectBimestreNotas.value : '1';
			const disciplina = selectDisciplinaNotas ? selectDisciplinaNotas.value : '';
			const turmaId = selectTurmaNotas ? selectTurmaNotas.value : null;
			if (!disciplina) {
				mostrarToastAluno('Selecione a disciplina antes de salvar a nota individual', 'erro');
				return;
			}
			const input = document.querySelector(`.notas-input[data-aluno="${alunoId}"][data-field="media_final"]`);
			const payload = {
				id_aluno: alunoId,
				bimestre,
				disciplina,
				media_final: input ? (input.value === '' ? null : input.value) : null,
				id_turma: turmaId
			};
			try {
				const res = await fetch('../controllers/aluno_controller.php?action=salvar_notas', {
					method: 'POST',
					headers: {'Content-Type':'application/json'},
					body: JSON.stringify(payload)
				});
				const json = await res.json();
				if (json.sucesso) {
					mostrarToastAluno('Nota salva', 'sucesso');
					atualizarContadorDesempenho();
					const div = document.querySelector(`.display-val[data-aluno="${alunoId}"][data-field="media_final"]`);
					const value = input ? input.value : '';
					if (div) {
						div.textContent = value === '' ? '-' : value;
						div.style.display = '';
					}
					if (input) {
						input.style.display = 'none';
					}
					const btn = document.querySelector(`.btn-salvar-individual[data-aluno="${alunoId}"]`);
					if (btn) {
						btn.textContent = 'Editar';
						btn.classList.remove('bg-green-700');
						btn.classList.add('bg-white','border','border-green-700','text-green-700');
						btn.dataset.saved = '1';
					}
				} else {
					mostrarToastAluno('Erro ao salvar nota', 'erro');
				}
			} catch (err) {
				console.error(err);
				mostrarToastAluno('Erro de conexão', 'erro');
			}
		}

		async function salvarNotas(){
			const bimestre = selectBimestreNotas ? selectBimestreNotas.value : '1';
			const disciplina = selectDisciplinaNotas ? selectDisciplinaNotas.value : '';
			const turmaId = selectTurmaNotas ? selectTurmaNotas.value : null;
			if (!disciplina) {
				mostrarToastAluno('Selecione a disciplina antes de salvar as notas em lote', 'erro');
				return;
			}
			const allInputs = Array.from(document.querySelectorAll('.notas-input[data-field="media_final"]'));
			const byAluno = {};
			allInputs.forEach(input => {
				const id = input.getAttribute('data-aluno');
				if (!byAluno[id]) byAluno[id] = {id_aluno: id};
				byAluno[id].media_final = input.value === '' ? null : input.value;
			});
			const payload = {bimestre, disciplina, notas: Object.values(byAluno), id_turma: turmaId};
			try {
				const res = await fetch('../controllers/aluno_controller.php?action=salvar_notas_lote', {
					method: 'POST',
					headers: {'Content-Type':'application/json'},
					body: JSON.stringify(payload)
				});
				const json = await res.json();
				if (json.sucesso) {
					mostrarToastAluno('Todas as notas foram salvas', 'sucesso');
					atualizarContadorDesempenho();
					Object.values(byAluno).forEach(nota => {
						const id = String(nota.id_aluno);
						const input = document.querySelector(`.notas-input[data-aluno="${id}"][data-field="media_final"]`);
						const div = document.querySelector(`.display-val[data-aluno="${id}"][data-field="media_final"]`);
						const value = nota.media_final || '';
						if (div) {
							div.textContent = value === '' ? '-' : value;
							div.style.display = '';
						}
						if (input) {
							input.style.display = 'none';
						}
					});
					document.querySelectorAll('.btn-salvar-individual').forEach(btn => {
						btn.textContent = 'Editar';
						btn.classList.remove('bg-green-700');
						btn.classList.add('bg-white','border','border-green-700','text-green-700');
						btn.dataset.saved = '1';
					});
				} else {
					mostrarToastAluno('Erro ao salvar notas em lote', 'erro');
				}
			} catch (err) {
				console.error(err);
				mostrarToastAluno('Erro de conexão', 'erro');
			}
		}

		// Ao mudar de bimestre, recarregar notas salvas para a turma atual
		if (selectBimestreNotas) {
			selectBimestreNotas.addEventListener('change', () => {
				const turmaId = selectTurmaNotas ? selectTurmaNotas.value : null;
				const disciplina = selectDisciplinaNotas ? selectDisciplinaNotas.value : '';
				const novoBimestre = selectBimestreNotas.value;
				if (!disciplina) {
					mostrarToastAluno('Selecione a disciplina antes de carregar notas', 'erro');
					return;
				}
				if (turmaId) {
					carregarAlunosTurmaNotas(turmaId, novoBimestre);
				}
			});
		}
		if (selectDisciplinaNotas) {
			selectDisciplinaNotas.addEventListener('change', () => {
				const turmaId = selectTurmaNotas ? selectTurmaNotas.value : null;
				const novoBimestre = selectBimestreNotas ? selectBimestreNotas.value : '1';
				if (turmaId) {
					carregarAlunosTurmaNotas(turmaId, novoBimestre);
				}
			});
		}

		carregarUltimasOcorrencias();

</script>

<!-- MODAL ALUNOS -->
<div id="overlayModalAluno" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[300] p-4 backdrop-blur-[2px]">
	<div id="modalAlunos" class="bg-white w-full max-w-2xl rounded-[16px] p-6 relative shadow-2xl modal-card">
		<input type="hidden" id="turmaIdModal" value="<?= htmlspecialchars($initialTurmaId) ?>">
		
		<div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
			<div class="flex-1">
				<div class="absolute opacity-0">
					<div class="text-sm text-gray-500">Total</div>
					<div id="totalAlunosModal" class="font-bold text-lg">0</div>
				</div>
				<h2 id="tituloModalTurma" class="text-2xl font-bold text-gray-800 mt-3">Turma</h2>
				<p class="text-sm text-gray-500">Lista de alunos da turma selecionada</p>
			</div>
			<button id="fecharModalAluno" class="w-10 h-10 rounded-full hover:bg-gray-100 transition text-gray-600">
				<i class="bi bi-x-lg"></i>
			</button>
		</div>

		<!-- GERENCIAR ALUNOS (FULLSCREEN) -->
		<div id="sec-gerenciar-alunos" class="secs hidden">
			<div class="mb-6 flex items-center gap-4">
				<button id="btnVoltarGerenciar" class="flex items-center gap-2 text-green-700 hover:text-green-800 transition font-medium">
					<i class="bi bi-arrow-left"></i>
					Voltar
				</button>
				<div class="flex-1">
					<h2 id="gerenciarTituloTurma" class="text-2xl font-bold text-gray-800">Gerenciar alunos</h2>
					<p class="text-sm text-gray-500">Lista completa de alunos - Total: <span id="totalAlunosFull" class="font-semibold">0</span></p>
				</div>
			</div>

			<div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center justify-between gap-4">
				<div class="flex-1">
					<p class="text-sm text-green-800"><strong>Dicas:</strong> Use os botões abaixo para registrar novos alunos, realizar/consultar frequência ou aplicar ocorrências.</p>
				</div>
			</div>



			<div class="mb-4 rounded-xl p-4">
				<h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
					<i class="bi bi-search text-green-600"></i>
					Filtrar Alunos
				</h3>
				<div class="flex flex-col sm:flex-row gap-3">
					<input id="buscarAlunoFull" type="text" placeholder="Nome, matrícula ou e-mail" class="flex-1 h-11 border border-blue-300 rounded-lg px-4 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition text-sm">
					<button id="btnRegistrarAlunoFull" type="button" class="h-11 px-5 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition flex items-center justify-center gap-2 whitespace-nowrap">
						<i class="bi bi-person-plus"></i>
						<span class="hidden sm:inline">Registrar Aluno</span>
						<span class="sm:hidden">Adicionar</span>
					</button>
				</div>
			</div>

			<div class="mb-4 bg-white border border-gray-200 rounded-xl p-4">
				<h3 class="font-semibold text-gray-800 mb-2">Lista de Alunos</h3>
				<div id="listaAlunosFull" class="space-y-2 max-h-[calc(100vh-380px)] overflow-y-auto"></div>
			</div>
		</div>

		<div id="sec-alunos-modal-base">
			<!-- PESQUISA -->
			<div class="mb-6">
				<div class="flex flex-col sm:flex-row sm:items-end gap-3 mb-4">
					<div class="flex-1">
						<label for="buscarAlunoModal" class="text-sm font-semibold text-gray-700 block mb-2">Buscar aluno</label>
						<div class="relative">
							<i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
							<input id="buscarAlunoModal" type="text" placeholder="Nome, matrícula ou e-mail" class="w-full border border-gray-200 rounded-xl h-12 pl-12 pr-4 outline-none focus:border-green-500 transition">
						</div>
					</div>
					<button id="btnRegistrarAlunoModal" type="button" class="h-12 px-4 rounded-xl bg-green-700 text-white text-sm font-medium hover:bg-green-800 transition flex items-center justify-center gap-2 whitespace-nowrap">
						<i class="bi bi-plus-lg"></i>
						<span class="hidden sm:inline">Registrar aluno</span>
						<span class="sm:hidden">Registrar</span>
					</button>
				</div>
			</div>

			<div class="mb-6 grid gap-3 grid-cols-1 sm:grid-cols-[1fr_auto_auto_auto] items-end">
				<div>
					<label for="dataFrequencia" class="text-sm font-semibold text-gray-700 block mb-2">Data para frequência</label>
					<input id="dataFrequencia" type="date" class="w-full h-12 border border-gray-200 rounded-xl px-3 outline-none focus:border-green-500 transition">
				</div>
				<button id="btnRealizarFrequencia" type="button" class="h-12 px-3 rounded-xl bg-green-700 text-white text-xs font-medium hover:bg-green-800 transition whitespace-nowrap">
					<i class="bi bi-check-circle"></i> Realizar
				</button>
				<button id="btnConsultarFrequencia" type="button" class="h-12 px-3 rounded-xl bg-blue-700 text-white text-xs font-medium hover:bg-blue-800 transition whitespace-nowrap">
					<i class="bi bi-binoculars"></i> Consultar
				</button>
				<button id="btnSalvarFrequencia" type="button" class="h-12 px-4 rounded-xl bg-orange-500 text-white text-xs font-medium hover:bg-orange-600 transition hidden whitespace-nowrap">
					<i class="bi bi-floppy"></i> Salvar
				</button>
			</div>

			<div id="areaFrequenciaAviso" class="mb-6 hidden rounded-2xl border border-green-100 bg-green-50 p-4 text-sm text-green-800">
				<strong>Modo:</strong> <span id="textoModoFrequencia"></span>
			</div>

			<!-- LISTA DE ALUNOS -->
			<div id="listaAlunosModal" class="space-y-3 max-h-[400px] overflow-y-auto">
			</div>
		</div>
	</div>
</div>

<!-- MODAL REGISTRAR ALUNO -->
<div id="overlayModalRegistrarAluno" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[310] p-4 backdrop-blur-[2px]">
	<div class="bg-white w-full max-w-md rounded-[20px] p-6 relative shadow-2xl modal-card">
		<div class="mb-6 flex items-start justify-between gap-4">
			<div class="flex items-start gap-3">
				<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-100 text-green-700">
					<i class="bi bi-person-plus-fill text-xl"></i>
				</div>
				<div>
					<h2 class="text-2xl font-bold text-gray-800">Registrar aluno</h2>
					<p class="text-sm text-gray-500 mt-1">Cadastre um novo aluno na turma selecionada</p>
				</div>
			</div>
			<button id="fecharModalRegistrarAluno" class="h-10 w-10 rounded-full hover:bg-gray-100 transition text-gray-600">
				<i class="bi bi-x-lg"></i>
			</button>
		</div>

		<form id="formRegistrarAluno" class="space-y-4">
			<input type="hidden" id="turmaIdRegistrar" name="turma_id" value="">

			<div class="space-y-4">
				<div>
					<label class="text-sm text-gray-700 font-medium">Nome completo</label>
					<input id="nomeAlunoRegistrar" name="nome" type="text" class="w-full h-11 border border-gray-200 rounded-xl px-3 outline-none focus:border-green-500 transition" required>
				</div>

				<div class="grid gap-4 sm:grid-cols-2">
					<div>
						<label class="text-sm text-gray-700 font-medium">Matrícula</label>
						<input id="matriculaAlunoRegistrar" name="matricula" type="text" pattern="\d{7}" maxlength="7" inputmode="numeric" class="w-full h-11 border border-gray-200 rounded-xl px-3 outline-none focus:border-green-500 transition" required>
					</div>
					<div>
						<label class="text-sm text-gray-700 font-medium">PCD</label>
						<select id="pcdAlunoRegistrar" name="pcd" class="w-full h-11 border border-gray-200 rounded-xl px-3 outline-none focus:border-green-500 transition">
							<option value="nao">Não</option>
							<option value="sim">Sim</option>
						</select>
					</div>
				</div>

				<div>
					<label class="text-sm text-gray-700 font-medium">Email</label>
					<input id="emailAlunoRegistrar" name="email" type="email" class="w-full h-11 border border-gray-200 rounded-xl px-3 outline-none focus:border-green-500 transition" required>
				</div>
			</div>

			<div class="flex items-center justify-end gap-3 pt-2">
				<button type="button" id="cancelarRegistrarAluno" class="h-10 px-4 rounded-xl border border-gray-200 hover:bg-gray-100 transition">Cancelar</button>
				<button type="submit" class="h-10 px-4 rounded-xl bg-green-700 text-white hover:bg-green-800 transition">Registrar</button>
			</div>
		</form>
	</div>
</div>

<!-- MODAL APLICAR OCORRÊNCIA -->
<div id="overlayModalOcorrencia" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[305] p-4 backdrop-blur-[2px]">
	<div class="bg-white w-full max-w-2xl rounded-[16px] p-6 relative shadow-2xl modal-card">
		<div class="mb-4 flex items-start justify-between gap-4">
			<div>
				<h2 class="text-2xl font-bold text-gray-800">Aplicar ocorrência</h2>
				<p class="text-sm text-gray-500">Selecione turma, aluno e tipo de ocorrência.</p>
			</div>
			<button id="fecharModalOcorrencia" class="w-10 h-10 rounded-full hover:bg-gray-100 transition text-gray-600">
				<i class="bi bi-x-lg"></i>
			</button>
		</div>

		<form id="formOcorrencia" class="space-y-4">
			<div>
				<label for="selectTurmaOcorrencia" class="text-sm text-gray-700">Turma</label>
				<select id="selectTurmaOcorrencia" class="w-full h-12 border border-gray-200 rounded-xl px-3 outline-none">
					<option value="">Selecione a turma</option>
					<?php foreach($turmas as $turma): ?>
						<option value="<?= (int) $turma['id_turma'] ?>"><?= htmlspecialchars(formatarNomeExibicaoTurma($turma), ENT_QUOTES, 'UTF-8') ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<label for="selectAlunoOcorrencia" class="text-sm text-gray-700">Aluno</label>
				<select id="selectAlunoOcorrencia" class="w-full h-12 border border-gray-200 rounded-xl px-3 outline-none" disabled>
					<option value="">Selecione a turma primeiro</option>
				</select>
			</div>

			<div>
				<label for="selectTipoOcorrencia" class="text-sm text-gray-700">Tipo de ocorrência</label>
				<select id="selectTipoOcorrencia" class="w-full h-12 border border-gray-200 rounded-xl px-3 outline-none">
					<option value="">Selecione o tipo de ocorrência</option>
					<option>Chegou atrasado</option>
					<option>Falta sem justificativa</option>
					<option>Falta justificada</option>
					<option>Saiu mais cedo</option>
					<option>Não trouxe material</option>
					<option>Conversa excessiva em sala</option>
					<option>Desrespeito ao professor</option>
					<option>Desrespeito aos colegas</option>
					<option>Uso indevido de celular</option>
					<option>Não realizou atividade</option>
					<option>Dormiu em sala</option>
					<option>Briga entre alunos</option>
					<option>Bullying</option>
					<option>Danificao de patrimnio escolar</option>
					<option>Uniforme incompleto</option>
					<option>Excelente comportamento</option>
					<option>Participao positiva</option>
					<option>Destaque acadmico</option>
					<option>Advertncia verbal</option>
					<option>Advertncia escrita</option>
					<option>Suspensão</option>
					<option>Encaminhamento à coordenação</option>
					<option>Responsável convocado</option>
					<option>Uso de linguagem inadequada</option>
					<option>Cola em avaliação</option>
					<option>Entrada sem autorização</option>
					<option>Saída sem autorização</option>
					<option>Atraso na entrega de trabalhos</option>
					<option>Comportamento inadequado no intervalo</option>
				</select>
			</div>

			<div>
				<label for="descricaoOcorrencia" class="text-sm text-gray-700">Descrição (opcional)</label>
				<textarea id="descricaoOcorrencia" rows="4" class="w-full border border-gray-200 rounded-xl px-3 py-3 outline-none" placeholder="Descreva a ocorrência se necessário"></textarea>
			</div>

			<div class="flex items-center justify-end gap-2 pt-2">
				<button type="button" id="cancelarOcorrencia" class="h-11 px-5 rounded-xl border border-gray-200 hover:bg-gray-100 transition">Cancelar</button>
				<button type="submit" class="h-11 px-5 rounded-xl bg-red-600 text-white hover:bg-red-700 transition">Salvar ocorrência</button>
			</div>
		</form>
	</div>
</div>

<!-- MODAL CONFIRMAÇÃO EXCLUSÃO OCORRÊNCIA -->
<div id="overlayConfirmExcluirOcorrencia" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[310] p-4 backdrop-blur-[2px]">
	<div class="bg-white w-full max-w-md rounded-[16px] p-6 relative shadow-2xl modal-card">
		<div class="mb-4 flex items-start justify-between gap-4">
			<div>
				<h2 class="text-2xl font-bold text-gray-800">Cancelar ocorrência</h2>
				<p class="text-sm text-gray-500 mt-1">Tem certeza que deseja cancelar esta ocorrência?</p>
			</div>
			<button id="fecharConfirmExcluirOcorrencia" class="w-10 h-10 rounded-full hover:bg-gray-100 transition text-gray-600">
				<i class="bi bi-x-lg"></i>
			</button>
		</div>

		<p class="text-sm text-gray-500">Ocorrência:</p>
		<p id="confirmTipoOcorrencia" class="text-lg font-semibold text-gray-800 mb-6"></p>

		<div class="flex items-center justify-end gap-3">
			<button id="cancelarExcluirOcorrencia" class="h-10 px-4 rounded-xl border border-gray-200 hover:bg-gray-100">Cancelar</button>
			<button id="confirmarExcluirOcorrencia" class="h-10 px-4 rounded-xl bg-red-600 text-white">Cancelar ocorrência</button>
		</div>
	</div>
</div>

<!-- MODAL CONFIRMAO EXCLUSO -->
<div id="overlayConfirmExcluirAluno" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[310] p-4 backdrop-blur-[2px]">
	<div class="bg-white w-full max-w-md rounded-[16px] p-6 relative shadow-2xl modal-card">
		<div class="mb-4 flex items-start justify-between gap-4">
			<div>
				<h2 class="text-2xl font-bold text-gray-800">Confirmar exclusão</h2>
				<p class="text-sm text-gray-500 mt-1">Tem certeza que deseja excluir este aluno?</p>
			</div>
			<button id="fecharConfirmExcluirAluno" class="w-10 h-10 rounded-full hover:bg-gray-100 transition text-gray-600">
				<i class="bi bi-x-lg"></i>
			</button>
		</div>

		<p class="text-sm text-gray-500">Aluno:</p>
		<p id="confirmNomeAluno" class="text-lg font-semibold text-gray-800 mb-6"></p>

		<div class="flex items-center justify-end gap-3">
			<button id="cancelarExcluirAluno" class="h-10 px-4 rounded-xl border border-gray-200 hover:bg-gray-100">Cancelar</button>
			<button id="confirmarExcluirAluno" class="h-10 px-4 rounded-xl bg-red-600 text-white">Excluir</button>
		</div>
	</div>
</div>

<!-- MODAL EDITAR ALUNO -->
<div id="overlayModalEditarAluno" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[310] p-4 backdrop-blur-[2px]">
	<div class="bg-white w-full max-w-md rounded-[20px] p-6 relative shadow-2xl modal-card">
		<div class="mb-6 flex items-start justify-between gap-4">
			<div class="flex items-start gap-3">
				<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
					<i class="bi bi-person-lines-fill text-xl"></i>
				</div>
				<div>
					<h2 class="text-2xl font-bold text-gray-800">Editar aluno</h2>
					<p class="text-sm text-gray-500 mt-1">Atualize os dados do perfil do aluno</p>
				</div>
			</div>
			<button id="fecharModalEditarAluno" class="h-10 w-10 rounded-full hover:bg-gray-100 transition text-gray-600">
				<i class="bi bi-x-lg"></i>
			</button>
		</div>

		<form id="formEditarAluno" class="space-y-4">
			<input type="hidden" id="editarAlunoId" name="id_aluno" value="">

			<div class="space-y-4">
				<div>
					<label class="text-sm text-gray-700 font-medium">Nome completo</label>
					<input id="editarNomeAluno" name="nome" type="text" class="w-full h-11 border border-gray-200 rounded-xl px-3 outline-none focus:border-[#FFA500] transition" required>
				</div>

				<div class="grid gap-4 sm:grid-cols-2">
					<div>
						<label class="text-sm text-gray-700 font-medium">Matrícula</label>
						<input id="editarMatriculaAluno" name="matricula" type="text" pattern="\d{7}" maxlength="7" inputmode="numeric" class="w-full h-11 border border-gray-200 rounded-xl px-3 outline-none focus:border-[#FFA500] transition" required>
					</div>
					<div>
						<label class="text-sm text-gray-700 font-medium">PCD</label>
						<select id="editarPcdAluno" name="pcd" class="w-full h-11 border border-gray-200 rounded-xl px-3 outline-none focus:border-[#FFA500] transition">
							<option value="nao">Não</option>
							<option value="sim">Sim</option>
						</select>
					</div>
				</div>

				<div>
					<label class="text-sm text-gray-700 font-medium">Email</label>
					<input id="editarEmailAluno" name="email" type="email" class="w-full h-11 border border-gray-200 rounded-xl px-3 outline-none focus:border-[#FFA500] transition" required>
				</div>

				<div>
					<label class="text-sm text-gray-700 font-medium">Rendimento (%)</label>
					<input id="editarRendimentoAluno" name="rendimento" type="number" step="0.01" min="0" max="100" readonly class="w-full h-11 border border-gray-200 rounded-xl px-3 bg-gray-50 text-gray-600 cursor-not-allowed focus:border-[#FFA500] transition">
					<p class="mt-1 text-xs text-gray-400">Valor calculado com base nas notas registradas, não editável manualmente.</p>
				</div>
			</div>

			<div class="flex items-center justify-end gap-3 pt-2">
				<button type="button" id="cancelarEditarAluno" class="h-10 px-4 rounded-xl border border-gray-200 hover:bg-gray-100 transition">Cancelar</button>
				<button type="submit" class="h-10 px-4 rounded-xl btn-salvar-editar hover:bg-[#e69500] transition">Salvar</button>
			</div>
		</form>
	</div>
</div>

<script>
// MODAL E FUNÇÕES DE ALUNOS
const overlayModalAluno = document.getElementById('overlayModalAluno');
const fecharModalAlunoBtn = document.getElementById('fecharModalAluno');
const listaAlunosModal = document.getElementById('listaAlunosModal');
const totalAlunosModal = document.getElementById('totalAlunosModal');
const contadorAlunosCard = document.getElementById('contadorAlunos');
const tituloModalTurma = document.getElementById('tituloModalTurma');
const campoBuscarAlunoModal = document.getElementById('buscarAlunoModal');
const campoBuscarAlunoFull = document.getElementById('buscarAlunoFull');
const btnRegistrarAlunoModal = document.getElementById('btnRegistrarAlunoModal');
const btnRealizarFrequencia = document.getElementById('btnRealizarFrequencia');
const btnConsultarFrequencia = document.getElementById('btnConsultarFrequencia');
const btnSalvarFrequencia = document.getElementById('btnSalvarFrequencia');
const dataFrequenciaInput = document.getElementById('dataFrequencia');
const areaFrequenciaAviso = document.getElementById('areaFrequenciaAviso');
const textoModoFrequencia = document.getElementById('textoModoFrequencia');
let frequenciaMode = null; // 'realizar' | 'consultar' | null
let frequenciaRegistros = {};
let turmaNomeModal = '';

// MODAL REGISTRAR ALUNO
const overlayModalRegistrarAluno = document.getElementById('overlayModalRegistrarAluno');
const fecharModalRegistrarAlunoBtn = document.getElementById('fecharModalRegistrarAluno');
const cancelarRegistrarAlunoBtn = document.getElementById('cancelarRegistrarAluno');
const formRegistrarAluno = document.getElementById('formRegistrarAluno');
const turmaIdRegistrar = document.getElementById('turmaIdRegistrar');

const btnAplicarOcorrencia = document.getElementById('btnAplicarOcorrenciaTurma');
const overlayModalOcorrencia = document.getElementById('overlayModalOcorrencia');
const fecharModalOcorrenciaBtn = document.getElementById('fecharModalOcorrencia');
const cancelarOcorrenciaBtn = document.getElementById('cancelarOcorrencia');
const selectTurmaOcorrencia = document.getElementById('selectTurmaOcorrencia');
const selectAlunoOcorrencia = document.getElementById('selectAlunoOcorrencia');
const selectTipoOcorrencia = document.getElementById('selectTipoOcorrencia');
const descricaoOcorrencia = document.getElementById('descricaoOcorrencia');
const formOcorrencia = document.getElementById('formOcorrencia');
let alunosModalCache = [];
const overlayConfirmExcluirAluno = document.getElementById('overlayConfirmExcluirAluno');
const fecharConfirmExcluirAlunoBtn = document.getElementById('fecharConfirmExcluirAluno');
const cancelarExcluirAlunoBtn = document.getElementById('cancelarExcluirAluno');
const confirmarExcluirAlunoBtn = document.getElementById('confirmarExcluirAluno');
const confirmNomeAluno = document.getElementById('confirmNomeAluno');
let alunoExcluirId = null;

function abrirModalAlunos(turmaId, turmaNome){
	const modalInput = getTurmaIdModal();
	if (modalInput) {
		modalInput.value = turmaId;
	}
	turmaAtualId = turmaId;
	turmaNomeModal = turmaNome || 'Turma';
	tituloModalTurma.textContent = turmaNome || 'Turma';
	dataFrequenciaInput.value = new Date().toISOString().slice(0, 10);
	frequenciaMode = null;
	frequenciaRegistros = {};
	if (btnRegistrarAlunoModal) btnRegistrarAlunoModal.classList.remove('hidden');
	areaFrequenciaAviso.classList.add('hidden');
	const secBase = document.getElementById('sec-alunos-modal-base');
	const secGerenciar = document.getElementById('sec-gerenciar-alunos');
	if (secBase) secBase.classList.remove('hidden');
	if (secGerenciar) secGerenciar.classList.add('hidden');
	overlayModalAluno.classList.remove('hidden');
	overlayModalAluno.classList.add('flex');
	carregarAlunosTurma(turmaId);
}

// Abrir a tela completa de gerenciamento de alunos
function abrirGerenciarAlunos(turmaId, turmaNome){
	if (!turmaId) return;
	turmaAtualId = turmaId;
	const modalInput = getTurmaIdModal();
	if (modalInput) modalInput.value = turmaId;
	const secGerenciar = document.getElementById('sec-gerenciar-alunos');
	const secBase = document.getElementById('sec-alunos-modal-base');
	const secTurmaDetalhe = document.getElementById('sec-turma-detalhe');
	const secTurmas = document.getElementById('sec-turmas');
	if (secBase) secBase.classList.add('hidden');
	if (secGerenciar) secGerenciar.classList.remove('hidden');
	overlayModalAluno.classList.remove('hidden');
	overlayModalAluno.classList.add('flex');

	const titulo = document.getElementById('gerenciarTituloTurma');
	if (titulo) titulo.textContent = turmaNome || 'Gerenciar alunos';

	const dataInput = document.getElementById('dataFrequenciaFull');
	if (dataInput) dataInput.value = new Date().toISOString().slice(0,10);

	carregarAlunosTurma(turmaId).catch(err => console.error(err));
}

function renderizarListaAlunosFull(alunos = []){
	const listaFull = document.getElementById('listaAlunosFull');
	const totalFull = document.getElementById('totalAlunosFull');
	if (!listaFull) return;
	listaFull.innerHTML = '';
	if (totalFull) totalFull.textContent = String((alunos || []).length);
	if (!alunos || !alunos.length) {
		listaFull.innerHTML = '<div class="text-gray-500 text-center py-6">Nenhum aluno cadastrado nesta turma.</div>';
		return;
	}
	const canEdit = window.isPdtUsuario === true;
	alunos.forEach(a => {
		const div = document.createElement('div');
		div.className = 'bg-white border border-gray-100 rounded-xl p-4 flex items-center justify-between gap-3 hover:border-gray-200 transition';
		
		let acoes = '';
		if (canEdit) {
			acoes = `
				<div class="flex items-center gap-2">
					<button data-id="${a.id_aluno}" data-nome="${a.nome}" data-matricula="${a.matricula}" data-email="${a.email}" data-pcd="${a.pcd || 'nao'}" data-rendimento="${a.rendimento || 0}" class="btn-editar-aluno h-9 px-3 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 text-xs font-medium hover:bg-amber-100 transition">Editar</button>
					<button data-id="${a.id_aluno}" data-nome="${a.nome}" class="btn-excluir-aluno h-9 px-3 rounded-lg bg-red-50 text-red-700 border border-red-100 text-xs font-medium hover:bg-red-100 transition">Excluir</button>
					<button type="button" data-id="${a.id_aluno}" class="btn-perfil-pedagogico h-9 px-3 rounded-lg bg-green-50 text-green-800 border border-green-100 text-xs font-medium hover:bg-green-100 transition">Perfil</button>
				</div>
			`;
		} else {
			acoes = `<button type="button" data-id="${a.id_aluno}" class="btn-perfil-pedagogico h-9 px-3 rounded-lg bg-green-50 text-green-800 border border-green-100 text-xs font-medium hover:bg-green-100 transition">Perfil pedagógico</button>`;
		}
		
		div.innerHTML = `
			<div class="flex-1">
				<div class="font-semibold text-gray-800">${a.nome || ''}</div>
				<div class="text-xs text-gray-500 mt-1">
					<span class="inline-block mr-3">Matrícula: ${a.matricula || 'N/A'}</span>
					<span class="inline-block mr-3">Email: ${a.email || 'N/A'}</span>
					<span class="inline-block">PCD: ${(a.pcd === 'sim' ? 'Sim' : 'Não')}</span>
				</div>
			</div>
			${acoes}
		`;
		listaFull.appendChild(div);
	});
	
	// Attach event listeners após criar os elementos
	listaFull.querySelectorAll('.btn-editar-aluno').forEach(btn => {
		btn.addEventListener('click', () => {
			abrirModalEditarAluno(btn.getAttribute('data-id'), btn.getAttribute('data-nome'), btn.getAttribute('data-matricula'), btn.getAttribute('data-email'), btn.getAttribute('data-pcd'), btn.getAttribute('data-rendimento'));
		});
	});
	
	listaFull.querySelectorAll('.btn-excluir-aluno').forEach(btn => {
		btn.addEventListener('click', () => {
			abrirModalExcluirAluno(btn.getAttribute('data-nome'), btn.getAttribute('data-id'));
		});
	});

	listaFull.querySelectorAll('.btn-perfil-pedagogico').forEach(btn => {
		btn.addEventListener('click', () => abrirPerfilPedagogico(btn.dataset.id));
	});
}

function escaparTextoPerfil(valor){
	return String(valor ?? '').replace(/[&<>"']/g, caractere => ({
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	}[caractere]));
}

function classeClassificacaoDesempenho(classificacao){
	return {
		'Muito Crítico': 'pdt-classification--very-critical',
		'Crítico': 'pdt-classification--critical',
		'Intermediário': 'pdt-classification--intermediate',
		'Adequado': 'pdt-classification--adequate'
	}[classificacao] || '';
}

async function abrirPerfilPedagogico(idAluno){
	const modal = document.getElementById('perfilPedagogicoModal');
	const identificacao = document.getElementById('perfilPedagogicoIdentificacao');
	const conteudo = document.getElementById('perfilPedagogicoConteudo');
	if (!modal || !identificacao || !conteudo) return;

	modal.classList.remove('hidden');
	modal.classList.add('flex');
	modal.setAttribute('aria-hidden', 'false');
	identificacao.textContent = 'Carregando dados pedagógicos...';
	conteudo.textContent = '';
	document.getElementById('fecharPerfilPedagogico')?.focus();

	try {
		const response = await fetch('../controllers/aluno_controller.php?action=perfil_pedagogico&id_aluno=' + encodeURIComponent(idAluno));
		const result = await response.json();
		if (!response.ok || !result.sucesso || !result.dados) {
			throw new Error(result.erro || 'Não foi possível carregar o perfil.');
		}

		const aluno = result.dados;
		const frequencia = aluno.frequencia || {};
		const conclusao = aluno.status_aluno === 'concluinte'
			? ` · Concluinte ${aluno.ano_conclusao || ''}`
			: '';
		identificacao.textContent = `${aluno.turma || 'Turma não informada'} · Ano letivo ${aluno.ano_letivo || 'não informado'}${conclusao}`;

		const linhasNotas = (aluno.notas || []).map(nota => `
			<tr class="border-t border-gray-100">
				<td class="px-3 py-2">${escaparTextoPerfil(nota.ano_letivo || '—')}</td>
				<td class="px-3 py-2">${escaparTextoPerfil(nota.bimestre)}º</td>
				<td class="px-3 py-2">${escaparTextoPerfil(nota.disciplina)}</td>
				<td class="px-3 py-2">${nota.nota === null ? '—' : Number(nota.nota).toFixed(2).replace('.', ',')}</td>
				<td class="px-3 py-2"><span class="pdt-classification-label ${classeClassificacaoDesempenho(nota.classificacao)}">${escaparTextoPerfil(nota.classificacao || (nota.situacao === 'sem_nota' ? 'Sem nota lançada' : 'Nota inválida'))}</span></td>
			</tr>
		`).join('');

		const ocorrencias = (aluno.ocorrencias || []).map(ocorrencia => `
			<li class="rounded-xl border border-gray-100 bg-gray-50 p-3">
				<p class="font-semibold text-gray-800">${escaparTextoPerfil(ocorrencia.tipo_ocorrencia)}</p>
				<p class="mt-1 text-sm text-gray-600">${escaparTextoPerfil(ocorrencia.descricao || 'Sem descrição')}</p>
				<p class="mt-2 text-xs text-gray-500">${escaparTextoPerfil(ocorrencia.data_ocorrencia)} · ${escaparTextoPerfil(ocorrencia.serie_turma)}º ano / ${escaparTextoPerfil(ocorrencia.ano_letivo)}</p>
			</li>
		`).join('');
		const acompanhamentos = (aluno.acompanhamentos || []).map(acompanhamento => `
			<li class="rounded-xl border border-gray-100 bg-gray-50 p-3">
				<p class="font-semibold text-gray-800">${escaparTextoPerfil(acompanhamento.motivo)} · ${escaparTextoPerfil(acompanhamento.status)}</p>
				<p class="mt-1 whitespace-pre-wrap text-sm text-gray-600">${escaparTextoPerfil(acompanhamento.observacao)}</p>
				${acompanhamento.encaminhamento ? `<p class="mt-1 whitespace-pre-wrap text-sm text-gray-600">Encaminhamento: ${escaparTextoPerfil(acompanhamento.encaminhamento)}</p>` : ''}
				<p class="mt-2 text-xs text-gray-500">${escaparTextoPerfil(acompanhamento.serie_turma)}º ano / ${escaparTextoPerfil(acompanhamento.ano_letivo)} · Registro: ${escaparTextoPerfil(acompanhamento.data_registro)}${acompanhamento.data_retorno ? ` · Retorno previsto: ${escaparTextoPerfil(acompanhamento.data_retorno)}` : ''}</p>
			</li>
		`).join('');
		const deliberacoesConselho = (aluno.deliberacoes_conselho || []).map(deliberacao => `
			<li class="rounded-xl border border-gray-100 bg-gray-50 p-3">
				<p class="font-semibold text-gray-800">${escaparTextoPerfil(deliberacao.bimestre)}º bimestre · ${escaparTextoPerfil(deliberacao.data_conselho)} · ${escaparTextoPerfil(deliberacao.serie_turma)}º ano / ${escaparTextoPerfil(deliberacao.ano_letivo)}</p>
				<p class="mt-1 text-sm text-gray-600">${escaparTextoPerfil(deliberacao.pauta)}</p>
				<p class="mt-1 whitespace-pre-wrap text-sm text-gray-600">${escaparTextoPerfil(deliberacao.observacao)}</p>
				${deliberacao.encaminhamento ? `<p class="mt-1 whitespace-pre-wrap text-sm text-gray-600">Encaminhamento: ${escaparTextoPerfil(deliberacao.encaminhamento)}</p>` : ''}
			</li>
		`).join('');

		conteudo.innerHTML = `
			<div class="grid gap-3 sm:grid-cols-3">
				<div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><p class="text-xs text-gray-500">Aluno</p><p class="mt-1 font-semibold text-gray-800">${escaparTextoPerfil(aluno.nome)}</p><p class="text-sm text-gray-600">Matrícula: ${escaparTextoPerfil(aluno.matricula)}</p></div>
				<div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><p class="text-xs text-gray-500">Frequência registrada</p><p class="mt-1 font-semibold text-gray-800">${frequencia.percentual_presenca === null ? 'Sem dados registrados' : `${Number(frequencia.percentual_presenca).toFixed(1).replace('.', ',')}% de presença`}</p><p class="text-sm text-gray-600">${escaparTextoPerfil(frequencia.presencas || 0)} presenças · ${escaparTextoPerfil(frequencia.faltas || 0)} faltas · ${escaparTextoPerfil(frequencia.total_registros || 0)} registros</p></div>
				<div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><p class="text-xs text-gray-500">Período de frequência</p><p class="mt-1 font-semibold text-gray-800">${frequencia.primeira_data ? escaparTextoPerfil(frequencia.primeira_data) : 'Sem dados registrados'}</p><p class="text-sm text-gray-600">${frequencia.ultima_data ? `Último registro: ${escaparTextoPerfil(frequencia.ultima_data)}` : ''}</p></div>
			</div>
			<section><h3 class="mb-2 text-lg font-semibold text-gray-800">Notas por disciplina e bimestre</h3>
				${linhasNotas ? `<div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="text-gray-500"><th class="px-3 py-2">Ano</th><th class="px-3 py-2">Bimestre</th><th class="px-3 py-2">Disciplina</th><th class="px-3 py-2">Nota</th><th class="px-3 py-2">Classificação</th></tr></thead><tbody>${linhasNotas}</tbody></table></div>` : '<p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">Sem notas registradas.</p>'}
			</section>
			<section><h3 class="mb-2 text-lg font-semibold text-gray-800">Ocorrências</h3>
				${ocorrencias ? `<ul class="space-y-2">${ocorrencias}</ul>` : '<p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">Sem ocorrências registradas.</p>'}
			</section>
			<section><h3 class="mb-2 text-lg font-semibold text-gray-800">Acompanhamento pedagógico</h3>
				${acompanhamentos ? `<ul class="space-y-2">${acompanhamentos}</ul>` : '<p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">Sem acompanhamentos registrados.</p>'}
			</section>
			<section><h3 class="mb-2 text-lg font-semibold text-gray-800">Deliberações de conselho</h3>
				${deliberacoesConselho ? `<ul class="space-y-2">${deliberacoesConselho}</ul>` : '<p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">Sem deliberações registradas.</p>'}
			</section>
		`;
	} catch (error) {
		identificacao.textContent = error.message || 'Erro ao carregar o perfil pedagógico.';
		conteudo.innerHTML = '<p class="rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">Não foi possível carregar os dados pedagógicos deste aluno.</p>';
	}
}

function fecharPerfilPedagogico(){
	const modal = document.getElementById('perfilPedagogicoModal');
	if (!modal) return;
	modal.classList.add('hidden');
	modal.classList.remove('flex');
	modal.setAttribute('aria-hidden', 'true');
}

document.getElementById('fecharPerfilPedagogico')?.addEventListener('click', fecharPerfilPedagogico);
document.getElementById('perfilPedagogicoModal')?.addEventListener('click', event => {
	if (event.target.id === 'perfilPedagogicoModal') fecharPerfilPedagogico();
});

// voltar da tela de gerenciar para detalhe
const btnVoltarGerenciar = document.getElementById('btnVoltarGerenciar');
if (btnVoltarGerenciar) {
	btnVoltarGerenciar.addEventListener('click', () => {
		const secGerenciar = document.getElementById('sec-gerenciar-alunos');
		const secBase = document.getElementById('sec-alunos-modal-base');
		if (secGerenciar) secGerenciar.classList.add('hidden');
		if (secBase) secBase.classList.remove('hidden');
		if (overlayModalAluno) {
			overlayModalAluno.classList.remove('hidden');
			overlayModalAluno.classList.add('flex');
		}
	});
}

function fecharModalAlunos(){
	overlayModalAluno.classList.add('hidden');
	overlayModalAluno.classList.remove('flex');
	listaAlunosModal.innerHTML = '';
	const secBase = document.getElementById('sec-alunos-modal-base');
	const secGerenciar = document.getElementById('sec-gerenciar-alunos');
	if (secBase) secBase.classList.remove('hidden');
	if (secGerenciar) secGerenciar.classList.add('hidden');
	frequenciaMode = null;
	frequenciaRegistros = {};
}

fecharModalAlunoBtn.addEventListener('click', fecharModalAlunos);

overlayModalAluno.addEventListener('click', (e) => {
	if (e.target === overlayModalAluno) fecharModalAlunos();
});

function abrirModalRegistrarAluno(turmaIdParam = null){
	if (!overlayModalRegistrarAluno) return;
	
	formRegistrarAluno.reset();
	
	// Prioridade de cópia: parâmetro > turmaAtualId > turmaIdModal.value > turmaIdRegistrar.value
	let turmaFinal = null;
	
	if (turmaIdParam) {
		turmaFinal = turmaIdParam;
	} else if (turmaAtualId) {
		turmaFinal = turmaAtualId;
	} else if (getTurmaIdModal()?.value) {
		turmaFinal = getTurmaIdModal().value;
	}
	
	if (turmaFinal) {
		turmaIdRegistrar.value = turmaFinal;
	} else {
		alert('Erro: Nenhuma turma foi selecionada. Volte e tente novamente.');
		return;
	}
	
	overlayModalRegistrarAluno.classList.remove('hidden');
	overlayModalRegistrarAluno.classList.add('flex');
}

function fecharModalRegistrarAluno(){
	if (!overlayModalRegistrarAluno) return;
	overlayModalRegistrarAluno.classList.add('hidden');
	overlayModalRegistrarAluno.classList.remove('flex');
	formRegistrarAluno.reset();
}

if (btnRegistrarAlunoModal) {
	btnRegistrarAlunoModal.addEventListener('click', () => {
		// Passar turmaIdModal.value como parâmetro
		abrirModalRegistrarAluno(getTurmaIdModal()?.value);
	});
}

// registrar a partir da tela full
const btnRegistrarAlunoFull = document.getElementById('btnRegistrarAlunoFull');
if (btnRegistrarAlunoFull) {
	btnRegistrarAlunoFull.addEventListener('click', () => {
		abrirModalRegistrarAluno(turmaAtualId || getTurmaIdModal()?.value);
	});
}

const btnAplicarOcorrenciaFull = document.getElementById('btnAplicarOcorrenciaFull');
if (btnAplicarOcorrenciaFull) {
	btnAplicarOcorrenciaFull.addEventListener('click', () => {
		abrirModalOcorrencia();
		if (selectTurmaOcorrencia) {
			selectTurmaOcorrencia.value = turmaAtualId || getTurmaIdModal()?.value;
			selectTurmaOcorrencia.dispatchEvent(new Event('change'));
		}
	});
}

const btnRealizarFrequenciaFull = document.getElementById('btnRealizarFrequenciaFull');
if (btnRealizarFrequenciaFull) btnRealizarFrequenciaFull.addEventListener('click', async () => {
	// sincronizar data
	const data = document.getElementById('dataFrequenciaFull')?.value;
	if (data) document.getElementById('dataFrequencia').value = data;
	abrirRealizarFrequencia();
});

const btnConsultarFrequenciaFull = document.getElementById('btnConsultarFrequenciaFull');
if (btnConsultarFrequenciaFull) btnConsultarFrequenciaFull.addEventListener('click', async () => {
	const data = document.getElementById('dataFrequenciaFull')?.value;
	if (data) document.getElementById('dataFrequencia').value = data;
	abrirConsultarFrequencia();
});

if (fecharModalRegistrarAlunoBtn) {
	fecharModalRegistrarAlunoBtn.addEventListener('click', fecharModalRegistrarAluno);
}

if (cancelarRegistrarAlunoBtn) {
	cancelarRegistrarAlunoBtn.addEventListener('click', fecharModalRegistrarAluno);
}

if (overlayModalRegistrarAluno) {
	overlayModalRegistrarAluno.addEventListener('click', (e) => {
		if (e.target === overlayModalRegistrarAluno) fecharModalRegistrarAluno();
	});
}

function filtrarAlunosModal(){
	if (!campoBuscarAlunoModal) return;
	const filtro = campoBuscarAlunoModal.value.trim().toLowerCase();
	const alunosFiltrados = alunosModalCache.filter(a => {
		return (a.nome || '').toLowerCase().includes(filtro)
			|| (a.matricula || '').toLowerCase().includes(filtro)
			|| (a.email || '').toLowerCase().includes(filtro);
	});
	renderizarListaAlunos(alunosFiltrados);
}

if (campoBuscarAlunoModal) {
	campoBuscarAlunoModal.addEventListener('input', filtrarAlunosModal);
}

if (campoBuscarAlunoFull) {
	campoBuscarAlunoFull.addEventListener('input', () => {
		const filtro = campoBuscarAlunoFull.value.trim().toLowerCase();
		const alunosFiltrados = (alunosModalCache || []).filter(a => {
			return (a.nome || '').toLowerCase().includes(filtro)
				|| (a.matricula || '').toLowerCase().includes(filtro)
				|| (a.email || '').toLowerCase().includes(filtro);
		});
		renderizarListaAlunosFull(alunosFiltrados);
	});
}

// Quando a data da frequência mudar e o modal estiver aberto, recarregar registros
if (dataFrequenciaInput) {
	dataFrequenciaInput.addEventListener('change', async () => {
		try {
			if (!overlayModalAluno || overlayModalAluno.classList.contains('hidden')) return;
			if (frequenciaMode === 'realizar') {
				await abrirRealizarFrequencia();
			} else if (frequenciaMode === 'consultar') {
				await abrirConsultarFrequencia();
			}
		} catch (e) {
			console.error('Erro ao recarregar frequência após mudança de data:', e);
		}
	});
}

if (btnAplicarOcorrencia) {
	btnAplicarOcorrencia.addEventListener('click', abrirModalOcorrencia);
}

function abrirModalOcorrencia(){
	if (!overlayModalOcorrencia) return;
	overlayModalOcorrencia.classList.remove('hidden');
	overlayModalOcorrencia.classList.add('flex');
	if (selectTurmaOcorrencia) selectTurmaOcorrencia.value = '';
	if (selectAlunoOcorrencia) {
		selectAlunoOcorrencia.innerHTML = '<option value="">Selecione a turma primeiro</option>';
		selectAlunoOcorrencia.disabled = true;
	}
	if (selectTipoOcorrencia) selectTipoOcorrencia.value = '';
	if (descricaoOcorrencia) descricaoOcorrencia.value = '';
}

function fecharModalOcorrencia(){
	if (!overlayModalOcorrencia) return;
	overlayModalOcorrencia.classList.add('hidden');
	overlayModalOcorrencia.classList.remove('flex');
}

if (fecharModalOcorrenciaBtn) fecharModalOcorrenciaBtn.addEventListener('click', fecharModalOcorrencia);
if (cancelarOcorrenciaBtn) cancelarOcorrenciaBtn.addEventListener('click', fecharModalOcorrencia);

if (overlayModalOcorrencia) {
	overlayModalOcorrencia.addEventListener('click', (e) => {
		if (e.target === overlayModalOcorrencia) fecharModalOcorrencia();
	});
}

if (selectTurmaOcorrencia) {
	selectTurmaOcorrencia.addEventListener('change', async () => {
		const turmaId = selectTurmaOcorrencia.value;
		if (!turmaId) {
			selectAlunoOcorrencia.innerHTML = '<option value="">Selecione a turma primeiro</option>';
			selectAlunoOcorrencia.disabled = true;
			return;
		}

		try {
			const res = await fetch('../controllers/aluno_controller.php?action=listar_alunos&id_turma=' + encodeURIComponent(turmaId));
			const json = await res.json();
			if (!json.sucesso) {
				selectAlunoOcorrencia.innerHTML = '<option value="">Erro ao carregar alunos</option>';
				selectAlunoOcorrencia.disabled = true;
				return;
			}

			selectAlunoOcorrencia.disabled = false;
			selectAlunoOcorrencia.innerHTML = '<option value="">Selecione um aluno</option>';
			json.dados.forEach(a => {
				const option = document.createElement('option');
				option.value = a.id_aluno;
				option.textContent = `${a.nome} (${a.matricula})`;
				selectAlunoOcorrencia.appendChild(option);
			});
		} catch (err) {
			console.error(err);
			selectAlunoOcorrencia.innerHTML = '<option value="">Erro de conexão</option>';
			selectAlunoOcorrencia.disabled = true;
		}
	});
}

if (formOcorrencia) {
	formOcorrencia.addEventListener('submit', async (e) => {
		e.preventDefault();
		const turmaId = selectTurmaOcorrencia ? selectTurmaOcorrencia.value : '';
		const alunoId = selectAlunoOcorrencia ? selectAlunoOcorrencia.value : '';
		const tipo = selectTipoOcorrencia ? selectTipoOcorrencia.value : '';
		const descricao = descricaoOcorrencia ? descricaoOcorrencia.value.trim() : '';

		if (!turmaId) { alert('Selecione a turma.'); return; }
		if (!alunoId) { alert('Selecione o aluno.'); return; }
		if (!tipo) { alert('Selecione o tipo de ocorrência.'); return; }

		try {
			const res = await fetch('../controllers/ocorrencia_controller.php?action=criar_ocorrencia', {
				method: 'POST',
				headers: {'Content-Type':'application/json'},
				body: JSON.stringify({turma_id: turmaId, aluno_id: alunoId, tipo: tipo, descricao: descricao})
			});
			const j = await res.json();
			if (j.sucesso) {
				formOcorrencia.reset();
				fecharModalOcorrencia();
				mostrarToastAluno('Ocorrência registrada com sucesso!', 'ocorrencia');
				atualizarContadorOcorrencias();
			} else {
				alert('Erro ao registrar: ' + (j.erro || ''));
			}
		} catch (err) {
			console.error(err);
			alert('Erro de conexão');
		}
	});
}

const toastAluno = document.getElementById('toastAluno');
const toastAlunoTitulo = document.getElementById('toastAlunoTitulo');
const toastAlunoDot = document.getElementById('toastAlunoDot');
const toastAlunoBar = document.getElementById('toastAlunoBar');
const fecharToastAlunoBtn = document.getElementById('fecharToastAluno');

function esconderToastAluno(){
	if (!toastAluno) return;
	toastAluno.classList.add('hidden');
	toastAlunoBar.style.width = '100%';
}

function configurarToast(tipo = 'success'){
	if (!toastAluno) return;
	const isError = tipo === 'error' || tipo === 'erro' || tipo === 'ocorrencia' || tipo === 'ocorrencia_cancelada';
	toastAluno.classList.toggle('border-green-200', tipo === 'success' || tipo === 'sucesso');
    toastAluno.classList.toggle('border-red-200', isError);
    toastAluno.classList.toggle('border-yellow-200', tipo === 'edit');
    toastAlunoDot.style.backgroundColor = isError ? '#dc2626' : tipo === 'edit' ? '#FFA500' : '#16a34a';
    toastAlunoTitulo.classList.toggle('text-green-700', tipo === 'success');
    toastAlunoTitulo.classList.toggle('text-red-700', isError);
    toastAlunoTitulo.classList.toggle('text-yellow-700', tipo === 'edit');
    toastAlunoBar.style.backgroundColor = isError ? '#dc2626' : tipo === 'edit' ? '#FFA500' : '#16a34a';
}

function mostrarToastAluno(mensagem, tipo = 'sucesso'){
	if (!toastAluno) return;
	const toastMensagem = document.getElementById('toastAlunoMensagem');
	if (toastMensagem) toastMensagem.textContent = mensagem;
	if (toastAlunoTitulo) {
		if (tipo === 'ocorrencia') toastAlunoTitulo.textContent = 'Ocorrência registrada';
		else if (tipo === 'ocorrencia_cancelada') toastAlunoTitulo.textContent = 'Ocorrência cancelada';
		else if (tipo === 'error' || tipo === 'erro') toastAlunoTitulo.textContent = 'Erro';
		else if (tipo === 'edit') toastAlunoTitulo.textContent = 'Aluno editado';
		else if (tipo === 'sucesso' || tipo === 'success') toastAlunoTitulo.textContent = 'Sucesso';
		else toastAlunoTitulo.textContent = 'Notificação';
	}
	configurarToast(tipo);
	toastAluno.classList.remove('hidden');
	toastAlunoBar.style.transition = 'width 2s linear';
	toastAlunoBar.style.width = '100%';
	setTimeout(() => {
		toastAlunoBar.style.width = '0%';
	}, 50);
	clearTimeout(window.toastAlunoTimeout);
	window.toastAlunoTimeout = setTimeout(() => {
		esconderToastAluno();
	}, 2050);
}

if (fecharToastAlunoBtn) {
	fecharToastAlunoBtn.addEventListener('click', esconderToastAluno);
}

async function carregarAlunosTurma(turmaId){
	try {
		const res = await fetch('../controllers/aluno_controller.php?action=listar_alunos&id_turma=' + encodeURIComponent(turmaId));
		const json = await res.json();
		if (!json.sucesso) {
			listaAlunosModal.innerHTML = '<div class="text-red-500">Erro ao carregar alunos</div>';
			alunosModalCache = [];
			renderizarListaAlunosFull([]);
			return;
		}

		alunosModalCache = json.dados || [];
		renderizarListaAlunos(alunosModalCache);
		renderizarListaAlunosFull(alunosModalCache);

		// Atualizar contador na visualização detalhada da turma (quando presente)
		try {
			const contadorDetalhe = document.getElementById('contadorAlunosDetalhe');
			if (contadorDetalhe) {
				contadorDetalhe.textContent = String((alunosModalCache || []).length);
			}
		} catch (e) {
			console.error('Erro ao atualizar contadorAlunosDetalhe:', e);
		}
	} catch (err) {
		console.error(err);
		listaAlunosModal.innerHTML = '<div class="text-red-500">Erro de conexão</div>';
	}
}

async function carregarFrequenciaTurma(turmaId, data){
	try {
		const res = await fetch(`../controllers/frequencia_controller.php?action=listar_frequencia&id_turma=${encodeURIComponent(turmaId)}&data=${encodeURIComponent(data)}`);
		const json = await res.json();
		if (!json.sucesso) {
			throw new Error(json.erro || 'Erro ao carregar frequência');
		}
		return json.dados || [];
	} catch (err) {
		console.error(err);
		return [];
	}
}

function definirModoFrequencia(modo, data){
	frequenciaMode = modo;
	btnRegistrarAlunoModal?.classList.toggle('hidden', modo !== null);
	if (modo === 'realizar') {
		textoModoFrequencia.textContent = `Registrando frequência para ${data}`;
		btnSalvarFrequencia.classList.remove('hidden');
		areaFrequenciaAviso.classList.remove('hidden');
		btnRealizarFrequencia.classList.add('opacity-80');
		btnConsultarFrequencia.classList.remove('opacity-80');
	} else if (modo === 'consultar') {
		textoModoFrequencia.textContent = `Consultando frequência para ${data}`;
		btnSalvarFrequencia.classList.add('hidden');
		areaFrequenciaAviso.classList.remove('hidden');
		btnConsultarFrequencia.classList.add('opacity-80');
		btnRealizarFrequencia.classList.remove('opacity-80');
	} else {
		textoModoFrequencia.textContent = '';
		btnSalvarFrequencia.classList.add('hidden');
		areaFrequenciaAviso.classList.add('hidden');
		btnRealizarFrequencia.classList.remove('opacity-80');
		btnConsultarFrequencia.classList.remove('opacity-80');
	}
}

async function abrirRealizarFrequencia(){
	if (!dataFrequenciaInput.value) {
		alert('Selecione a data da frequência primeiro');
		return;
	}
	const turmaId = getTurmaIdModal()?.value;
	if (!turmaId) {
		alert('Turma inválida');
		return;
	}
	const data = dataFrequenciaInput.value;
	const frequencias = await carregarFrequenciaTurma(turmaId, data);
	frequenciaRegistros = {};
	frequencias.forEach(item => {
		frequenciaRegistros[item.id_aluno] = item.status;
	});
	alunosModalCache.forEach(a => {
		if (!frequenciaRegistros[a.id_aluno]) {
			frequenciaRegistros[a.id_aluno] = 'falta';
		}
	});
	frequenciaRegistros = frequenciaRegistros || {};
	frequenciaMode = 'realizar';
	definirModoFrequencia('realizar', data);
	renderizarListaAlunos(alunosModalCache, frequenciaRegistros, 'frequencia', true);
}

async function abrirConsultarFrequencia(){
	if (!dataFrequenciaInput.value) {
		alert('Selecione a data para consulta');
		return;
	}
	const turmaId = getTurmaIdModal()?.value;
	if (!turmaId) {
		alert('Turma inválida');
		return;
	}
	const data = dataFrequenciaInput.value;
	const frequencias = await carregarFrequenciaTurma(turmaId, data);
	const registros = {};
	frequencias.forEach(item => {
		registros[item.id_aluno] = item.status;
	});
	definirModoFrequencia('consultar', data);
	renderizarListaAlunos(alunosModalCache, registros, 'frequencia', false);
}

function alternarPresencaAluno(alunoId){
	if (frequenciaMode !== 'realizar') return;
	const presente = frequenciaRegistros[alunoId] === 'presente';
	frequenciaRegistros[alunoId] = presente ? 'falta' : 'presente';
	renderizarListaAlunos(alunosModalCache, frequenciaRegistros, 'frequencia', true);
}

async function salvarFrequencia(){
	if (frequenciaMode !== 'realizar') return;
	const modalInput = getTurmaIdModal();
	const turmaId = (modalInput && modalInput.value ? modalInput.value : turmaAtualId);
	const data = dataFrequenciaInput.value;
	const registros = Object.keys(frequenciaRegistros).map(id_aluno => ({
		id_aluno: parseInt(id_aluno, 10),
		status: frequenciaRegistros[id_aluno]
	}));
	if (!registros.length) {
		alert('Não há registros para salvar');
		return;
	}
	try {
		const res = await fetch('../controllers/frequencia_controller.php?action=registrar_frequencia', {
			method: 'POST',
			headers: {'Content-Type':'application/json'},
			body: JSON.stringify({id_turma: turmaId, data: data, registros: registros})
		});
		const json = await res.json();
		if (json.sucesso) {
			alert('frequência salva com sucesso!');
			frequenciaMode = null;
			definirModoFrequencia(null);
			carregarAlunosTurma(turmaId);
			atualizarContadorFrequencia();
		} else {
			alert('Erro ao salvar frequência: ' + (json.erro || 'Erro desconhecido'));
		}
	} catch (err) {
		console.error(err);
		alert('Erro de conexão');
	}
}

async function atualizarContadorAlunos(turmaIdParam = null){
	if (!contadorAlunosCard) return;

	try {
		// Prefer explicit param > turmaAtualId > modal value
		const turmaId = turmaIdParam || (typeof turmaAtualId !== 'undefined' && turmaAtualId ? turmaAtualId : (getTurmaIdModal()?.value || null));
		console.debug('[atualizarContadorAlunos] usando turmaId=', turmaId, 'turmaIdParam=', turmaIdParam, 'turmaAtualId=', typeof turmaAtualId !== 'undefined' ? turmaAtualId : null);
		if (turmaId) {
			const url = '../controllers/aluno_controller.php?action=listar_alunos&id_turma=' + encodeURIComponent(turmaId);
			console.debug('[atualizarContadorAlunos] fetch listar_alunos ->', url);
			const res = await fetch(url);
			const json = await res.json();
			console.debug('[atualizarContadorAlunos] resposta listar_alunos', json);
			if (json.sucesso) {
				contadorAlunosCard.textContent = String((json.dados || []).length);
				return;
			}
		}

		// Fallback: contar alunos do professor (endpoint já existente)
		console.debug('[atualizarContadorAlunos] fazendo fallback contar_alunos');
		const res2 = await fetch('../controllers/aluno_controller.php?action=contar_alunos');
		const json2 = await res2.json();
		console.debug('[atualizarContadorAlunos] resposta contar_alunos', json2);
		if (json2.sucesso) {
			contadorAlunosCard.textContent = json2.total;
		}
	} catch (err) {
		console.error('Erro ao atualizar contador de alunos:', err);
	}
}

async function atualizarContadorAlunosDetalhe(turmaId){
	const contadorDetalhe = document.getElementById('contadorAlunosDetalhe');
	if (!contadorDetalhe || !turmaId) return;

	contadorDetalhe.textContent = '…';
	try {
		const res = await fetch('../controllers/aluno_controller.php?action=listar_alunos&id_turma=' + encodeURIComponent(turmaId));
		const json = await res.json();
		if (!res.ok || !json.sucesso) {
			throw new Error(json.erro || 'Não foi possível carregar a quantidade de alunos.');
		}
		contadorDetalhe.textContent = String((json.dados || []).length);
	} catch (err) {
		console.error('Erro ao atualizar contador de alunos da turma:', err);
		contadorDetalhe.textContent = '—';
	}
}

async function atualizarContadorOcorrencias(){
	const contadorOcorrencias = document.getElementById('contadorOcorrencias');
	if (!contadorOcorrencias) return;

	try {
		const res = await fetch('../controllers/ocorrencia_controller.php?action=contar_ocorrencias&filtro=mes');
		const json = await res.json();
		if (json.sucesso) {
			contadorOcorrencias.textContent = json.total;
		}
	} catch (err) {
		console.error('Erro ao atualizar contador de ocorrências:', err);
	}
}

async function atualizarContadorFrequencia(){
	const contadorFrequencia = document.getElementById('contadorFrequencia');
	if (!contadorFrequencia) return;
	try {
		const res = await fetch('../controllers/frequencia_controller.php?action=contar_frequencia');
		const json = await res.json();
		if (json.sucesso) {
			contadorFrequencia.textContent = json.media || 0;
		}
	} catch (err) {
		console.error('Erro ao atualizar contador de frequência:', err);
	}
}

async function atualizarContadorDesempenho(){
	const contadorDesempenho = document.getElementById('contadorDesempenho');
	if (!contadorDesempenho) return;
	try {
		const turmaSelect = document.getElementById('selectTurmaDesempenhoProfessor');
		const turmaId = turmaSelect && turmaSelect.value ? turmaSelect.value : window.initialTurmaId || (window.professorTurmas?.[0]?.id_turma ?? null);
		if (!turmaId) {
			contadorDesempenho.textContent = 'Sem turma';
			return;
		}
		const url = '../controllers/aluno_controller.php?action=desempenho_turma&id_turma=' + encodeURIComponent(turmaId);
		const res = await fetch(url);
		const json = await res.json();
		if (json.sucesso && json.dados) {
			contadorDesempenho.textContent = String(json.dados.media_geral ?? 0).replace('.', ',');
		} else {
			contadorDesempenho.textContent = 'N/A';
		}
	} catch (err) {
		console.error('Erro ao atualizar contador de desempenho:', err);
	}
}

async function carregarDesempenhoProfessor(turmaId = null) {
	const mensagemEl = document.getElementById('desempenhoProfessorMensagem');
	const cardEl = document.getElementById('desempenhoProfessorCard');
	const turmaNomeEl = document.getElementById('desempenhoProfessorNomeTurma');
	const mediaEl = document.getElementById('desempenhoProfessorMediaGeral');
	const classificacaoEl = document.getElementById('desempenhoProfessorClassificacao');
	const alunosEl = document.getElementById('desempenhoProfessorTotalAlunos');
	const notasEl = document.getElementById('desempenhoProfessorTotalNotas');
	const selectEl = document.getElementById('selectTurmaDesempenhoProfessor');

	if (!turmaNomeEl || !mediaEl || !classificacaoEl || !alunosEl || !notasEl || !selectEl) return;

	const turmas = window.professorTurmas || [];
	const isPdt = window.isPdtUsuario === true;

	if (!isPdt || !turmas.length) {
		if (mensagemEl) {
			mensagemEl.textContent = 'Você não é Professor Diretor de Turma (PDT) de nenhuma turma. O painel de desempenho está disponível apenas para professores PDT.';
			mensagemEl.classList.remove('hidden');
		}
		if (cardEl) cardEl.classList.add('hidden');
		return;
	}

	if (mensagemEl) mensagemEl.classList.add('hidden');
	if (cardEl) cardEl.classList.remove('hidden');

	selectEl.innerHTML = '';
	turmas.forEach((turma) => {
		const option = document.createElement('option');
		option.value = turma.id_turma;
		option.textContent = `${turma.serie_turma}º ${turma.letra} - ${turma.nome_curso}`;
		selectEl.appendChild(option);
	});

	const selectedId = turmaId || window.initialTurmaId || turmas[0].id_turma;
	selectEl.value = selectedId;

	if (turmas.length > 1) {
		selectEl.classList.remove('hidden');
	} else {
		selectEl.classList.add('hidden');
	}

	await carregarDesempenhoProfessorDados(selectedId);
}

async function carregarDesempenhoProfessorDados(turmaId) {
	const mensagemEl = document.getElementById('desempenhoProfessorMensagem');
	const cardEl = document.getElementById('desempenhoProfessorCard');
	const turmaNomeEl = document.getElementById('desempenhoProfessorNomeTurma');
	const mediaEl = document.getElementById('desempenhoProfessorMediaGeral');
	const classificacaoEl = document.getElementById('desempenhoProfessorClassificacao');
	const alunosEl = document.getElementById('desempenhoProfessorTotalAlunos');
	const notasEl = document.getElementById('desempenhoProfessorTotalNotas');

	if (mensagemEl) mensagemEl.classList.add('hidden');
	if (cardEl) cardEl.classList.remove('hidden');

	turmaNomeEl.textContent = 'Carregando...';
	mediaEl.textContent = '0.00';
	classificacaoEl.textContent = '...';
	alunosEl.textContent = '0';
	notasEl.textContent = '0';

	try {
		const res = await fetch('../controllers/aluno_controller.php?action=desempenho_turma&id_turma=' + encodeURIComponent(turmaId));
		const json = await res.json();
		if (!json.sucesso || !json.dados) {
			throw new Error(json.erro || 'Erro ao carregar desempenho');
		}

		const dados = json.dados;
		turmaNomeEl.textContent = dados.turma_descricao || dados.nome_turma || 'Turma';
		mediaEl.textContent = dados.media_geral === null || dados.media_geral === undefined
			? '—'
			: Number(dados.media_geral).toFixed(2).replace('.', ',');
		const classificacao = dados.classificacao
			|| (dados.situacao_desempenho === 'dados_invalidos' ? 'Dados inválidos' : 'Sem dados registrados');
		classificacaoEl.textContent = classificacao;
		classificacaoEl.classList.remove(
			'pdt-classification-label',
			'pdt-classification--very-critical',
			'pdt-classification--critical',
			'pdt-classification--intermediate',
			'pdt-classification--adequate'
		);
		const classeClassificacao = classeClassificacaoDesempenho(classificacao);
		if (classeClassificacao) {
			classificacaoEl.classList.add('pdt-classification-label', classeClassificacao);
		}
		alunosEl.textContent = String(dados.total_alunos ?? 0);
		notasEl.textContent = String(dados.total_notas ?? 0);
		if (document.getElementById('contadorDesempenho')) {
			document.getElementById('contadorDesempenho').textContent = dados.media_geral === null || dados.media_geral === undefined
				? '—'
				: Number(dados.media_geral).toFixed(2).replace('.', ',');
		}
	} catch (err) {
		console.error('Erro ao carregar desempenho da turma:', err);
		if (mensagemEl) {
			mensagemEl.textContent = 'Não foi possível carregar o desempenho desta turma.';
			mensagemEl.classList.remove('hidden');
		}
		if (cardEl) cardEl.classList.add('hidden');
	}
}

const selectTurmaDesempenhoProfessor = document.getElementById('selectTurmaDesempenhoProfessor');
if (selectTurmaDesempenhoProfessor) {
	selectTurmaDesempenhoProfessor.addEventListener('change', (event) => {
		carregarDesempenhoProfessorDados(event.target.value);
	});
}

// Carregar gráfico de frequência: últimos N dias (por turma)
async function carregarGraficoFrequencia(days = 8){
	const container = document.getElementById('graficoFrequencia');
	if (!container) return;
	container.innerHTML = '';

	// Determina turma
	const turmaId = (typeof turmaAtualId !== 'undefined' && turmaAtualId) ? turmaAtualId : (getTurmaIdModal()?.value || null);
	if (!turmaId) {
		container.innerHTML = '<div class="text-sm text-gray-500">Nenhuma turma selecionada</div>';
		return;
	}

	// Montar lista de datas (dias atrás)
	const dates = [];
	const today = new Date();
	for (let i = days - 1; i >= 0; i--) {
		const d = new Date(today);
		d.setDate(d.getDate() - i);
		dates.push(d.toISOString().slice(0,10));
	}

	// Buscar frequência para cada data e calcular percentual
	const results = [];
	for (const data of dates) {
		try {
			const url = `../controllers/frequencia_controller.php?action=listar_frequencia&id_turma=${encodeURIComponent(turmaId)}&data=${encodeURIComponent(data)}`;
			const res = await fetch(url);
			const json = await res.json();
			if (!json.sucesso) {
				results.push({data, percent: 0});
				continue;
			}
			const registros = json.dados || [];
			const total = registros.length;
			const presentes = registros.filter(r => r.status === 'presente').length;
			const percent = total === 0 ? 0 : Math.round((presentes / total) * 100);
			results.push({data, percent});
		} catch (e) {
			console.error('Erro ao carregar frequência para', data, e);
			results.push({data, percent: 0});
		}
	}

	// Renderizar barras
	results.forEach(item => {
		const wrapper = document.createElement('div');
		wrapper.className = 'flex flex-col items-center gap-2 w-full';
		wrapper.style.maxWidth = '60px';

		const bar = document.createElement('div');
		bar.className = 'w-full rounded-t-xl bg-green-200 transition-all';
		bar.style.height = (Math.max(6, item.percent)) + '%';
		bar.title = `${item.data}: ${item.percent}%`;

		const label = document.createElement('div');
		label.className = 'text-xs text-gray-500 mt-2';
		const dt = new Date(item.data);
		label.textContent = `${dt.getDate()}/${dt.getMonth()+1}`;

		wrapper.appendChild(bar);
		wrapper.appendChild(label);
		container.appendChild(wrapper);
	});
}

// Atualizar gráfico na inicialização e quando mudar turma
document.addEventListener('click', (e) => {
	const btnEntrar = e.target.closest('.btn-entrar-turma');
	if (btnEntrar) {
		// pequena espera para que turmaAtualId seja definido
		setTimeout(() => carregarGraficoFrequencia().catch(console.error), 150);
	}
});


function renderizarListaAlunos(alunos, registros = {}, mode = 'alunos', edit = false){
	listaAlunosModal.innerHTML = '';
	totalAlunosModal.textContent = alunos.length;

	if (alunos.length === 0) {
		listaAlunosModal.innerHTML = '<div class="text-gray-500">Nenhum aluno cadastrado nesta turma.</div>';
		return;
	}

	if (mode === 'frequencia') {
		alunos.forEach(a => {
			const status = registros?.[a.id_aluno] ?? null;
			const statusClasse = status === 'presente' ? 'bg-green-100 text-green-700' : status === 'falta' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-black';
			const statusTexto = status === 'presente' ? 'Presente' : status === 'falta' ? 'Falta' : 'Sem registro';
			const item = document.createElement('div');
			item.className = 'bg-white border border-gray-100 rounded-xl p-3 flex items-center justify-between gap-3';
			item.innerHTML = `
				<div>
					<div class="font-semibold">${a.nome || ''} <span class="text-sm text-gray-500">(${a.matricula})</span></div>
					<div class="text-sm text-gray-500">${a.email}</div>
				</div>
				<div class="flex items-center gap-2">
					<span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ${statusClasse}">${statusTexto}</span>
					${edit ? `<button type="button" data-id="${a.id_aluno}" class="btn-toggle-presenca h-9 px-3 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition">Alterar</button>` : ''}
				</div>
			`;
			listaAlunosModal.appendChild(item);
		});

		document.querySelectorAll('.btn-toggle-presenca').forEach(btn => {
			btn.addEventListener('click', () => alternarPresencaAluno(btn.getAttribute('data-id')));
		});
		return;
	}

	alunos.forEach(a => {
		const div = document.createElement('div');
		div.className = 'bg-white border border-gray-100 rounded-xl p-3 flex items-center justify-between';
		const canEdit = window.isPdtUsuario === true;
		div.innerHTML = `
			<div>
				<div class="font-semibold">${a.nome || ''} <span class="text-sm text-gray-500">(${a.matricula})</span></div>
				<div class="text-sm text-gray-500">${a.email} - PCD: ${a.pcd || 'não'}</div>
			</div>
			<div class="flex items-center gap-2">
				${canEdit ? `<button data-id="${a.id_aluno}" data-nome="${a.nome}" data-matricula="${a.matricula}" data-email="${a.email}" data-pcd="${a.pcd}" data-rendimento="${a.rendimento}" class="btn-editar-aluno h-8 px-3 rounded-xl border border-[#FBBF24]">Editar</button>` : ''}
				${canEdit ? `<button data-id="${a.id_aluno}" data-nome="${a.nome}" class="btn-excluir-aluno h-8 px-3 rounded-xl bg-red-50 text-red-700 border border-red-100">Excluir</button>` : ''}
			</div>
		`;
		listaAlunosModal.appendChild(div);
	});

	// attach edit handlers
	document.querySelectorAll('.btn-editar-aluno').forEach(btn => {
		btn.addEventListener('click', (e) => {
			const id = btn.getAttribute('data-id');
			const nome = btn.getAttribute('data-nome');
			const matricula = btn.getAttribute('data-matricula');
			const email = btn.getAttribute('data-email');
			const pcd = btn.getAttribute('data-pcd');
			const rendimento = btn.getAttribute('data-rendimento');
			abrirModalEditarAluno(id, nome, matricula, email, pcd, rendimento);
		});
	});

	// attach delete handlers
	document.querySelectorAll('.btn-excluir-aluno').forEach(btn => {
		btn.addEventListener('click', (e) => {
			const id = btn.getAttribute('data-id');
			const nome = btn.getAttribute('data-nome') || 'este aluno';
			abrirModalExcluirAluno(nome, id);
		});
	});
}

function abrirModalExcluirAluno(nome, id){
	alunoExcluirId = id;
	confirmNomeAluno.textContent = nome;
	overlayConfirmExcluirAluno.classList.remove('hidden');
	overlayConfirmExcluirAluno.classList.add('flex');
}

function fecharModalExcluirAluno(){
	overlayConfirmExcluirAluno.classList.add('hidden');
	overlayConfirmExcluirAluno.classList.remove('flex');
	alunoExcluirId = null;
	confirmNomeAluno.textContent = '';
}

if (fecharConfirmExcluirAlunoBtn) fecharConfirmExcluirAlunoBtn.addEventListener('click', fecharModalExcluirAluno);
if (cancelarExcluirAlunoBtn) cancelarExcluirAlunoBtn.addEventListener('click', fecharModalExcluirAluno);
overlayConfirmExcluirAluno.addEventListener('click', (e) => {
	if (e.target === overlayConfirmExcluirAluno) fecharModalExcluirAluno();
});

if (confirmarExcluirAlunoBtn) {
	confirmarExcluirAlunoBtn.addEventListener('click', async () => {
		if (!alunoExcluirId) return;
		const turmaId = turmaAtualId || getTurmaIdModal()?.value || null;
		try {
			const res = await fetch('../controllers/aluno_controller.php?action=excluir_aluno', {
				method: 'POST',
				headers: {'Content-Type':'application/json'},
				body: JSON.stringify({id_aluno: alunoExcluirId, turma_id: turmaId, csrf_token: window.csrfToken})
			});
			const j = await res.json();
			if (j.sucesso) {
				fecharModalExcluirAluno();
				fecharModalAlunos();
				mostrarToastAluno('Aluno excluído com sucesso!', '');
				atualizarContadorAlunos();
			} else {
				alert('Erro ao excluir: ' + (j.erro || ''));
			}
		} catch (err) {
			console.error(err);
			alert('Erro de conexão');
		}
	});
}


if (btnRealizarFrequencia) {
	btnRealizarFrequencia.addEventListener('click', abrirRealizarFrequencia);
}

if (btnConsultarFrequencia) {
	btnConsultarFrequencia.addEventListener('click', abrirConsultarFrequencia);
}

if (btnSalvarFrequencia) {
	btnSalvarFrequencia.addEventListener('click', salvarFrequencia);
}
// cadastrar
const formAlunoModal = document.getElementById('formAlunoModal');
if (formAlunoModal) {
	formAlunoModal.addEventListener('submit', async (e) => {
		e.preventDefault();

		const nome = document.getElementById('nomeAlunoModal').value.trim();
		const matricula = document.getElementById('matriculaAlunoModal').value.trim();
		const email = document.getElementById('emailAlunoModal').value.trim();
		const pcd = document.getElementById('pcdAlunoModal').value;
		const rendimento = document.getElementById('rendimentoAlunoModal').value || 0;
		const turmaId = getTurmaIdModal()?.value || null;

		if (!nome) { alert('Preencha o nome do aluno'); return; }

		if (!/^[0-9]{7}$/.test(matricula)) { alert('Matrícula deve ter exatamente 7 dígitos numéricos'); return; }

		if (!email) { alert('Preencha o email do aluno'); return; }

		try {
			const res = await fetch('../controllers/aluno_controller.php?action=cadastrar_aluno', {
				method: 'POST',
				headers: {'Content-Type':'application/json'},
				body: JSON.stringify({nome, matricula, email, pcd, rendimento, turma_id: turmaId})
			});
			const j = await res.json();
			if (j.sucesso) {
				formAlunoModal.reset();
				fecharModalAlunos();
				mostrarToastAluno('Aluno cadastrado com sucesso!');
				carregarAlunosTurma(turmaId);
				atualizarContadorAlunos(turmaId);
			} else {
				alert('Erro ao cadastrar: ' + (j.erro || ''));
			}
		} catch (err) {
			console.error(err);
			alert('Erro de conexão');
		}
	});
}

// registrar aluno (novo modal)
if (formRegistrarAluno) {
	formRegistrarAluno.addEventListener('submit', async (e) => {
		e.preventDefault();
		console.log('Form registrar aluno submetido');

		const nome = document.getElementById('nomeAlunoRegistrar').value.trim();
		const matricula = document.getElementById('matriculaAlunoRegistrar').value.trim();
		const email = document.getElementById('emailAlunoRegistrar').value.trim();
		const pcd = document.getElementById('pcdAlunoRegistrar').value;
		const rendimento = 0;
		
		// Converter turmaId para número inteiro
		const turmaIdValue = (turmaIdRegistrar.value || '').trim();
		const turmaId = turmaIdValue ? parseInt(turmaIdValue, 10) : null;

		console.log('Dados:', {nome, matricula, email, pcd, turmaId, turmaIdRegistrar: turmaIdRegistrar.value});

		if (!nome) { alert('Preencha o nome do aluno'); return; }

		if (!/^[0-9]{7}$/.test(matricula)) { alert('Matrícula deve ter exatamente 7 dígitos numéricos'); return; }

		if (!email) { alert('Preencha o email do aluno'); return; }

		if (!turmaId || turmaId === 0) { 
			console.error('Turma não foi selecionada. turmaIdRegistrar.value:', turmaIdRegistrar.value, 'turmaId:', turmaId);
			alert('Turma não foi selecionada. Tente novamente.');
			return; 
		}

		try {
			console.log('Enviando dados para registrar aluno...');
			console.log({nome, matricula, email, pcd, rendimento, turma_id: turmaId});
			
			const res = await fetch('../controllers/aluno_controller.php?action=cadastrar_aluno', {
				method: 'POST',
				headers: {'Content-Type':'application/json'},
				body: JSON.stringify({nome, matricula, email, pcd, rendimento, turma_id: turmaId, csrf_token: window.csrfToken})
			});
			
			if (!res.ok) {
				const texto = await res.text();
				throw new Error(`HTTP Error: ${res.status} - ${texto}`);
			}
			
			const texto = await res.text();
			let j;
			try {
				j = JSON.parse(texto);
			} catch (parseErr) {
				console.error('Falha ao decodificar JSON do servidor:', texto);
				throw new Error('Resposta inválida do servidor');
			}
			console.log('Resposta do servidor:', j);
			
			if (j.sucesso) {
				console.log('Aluno registrado com sucesso!');
				formRegistrarAluno.reset();
				fecharModalRegistrarAluno();
				mostrarToastAluno('Aluno registrado com sucesso!', 'sucesso');
				await carregarAlunosTurma(turmaId);
				atualizarContadorAlunos(turmaId);
			} else {
				const erro = j.erro || 'Erro desconhecido ao registrar aluno';
				console.error('? Erro do servidor:', erro);
				// NÃO fecha o modal para user corrigir e tentar de novo
				mostrarToastAluno('? ' + erro, 'erro');
				alert('Erro ao registrar: ' + erro);
			}
		} catch (err) {
			console.error('? Erro na requisição:', err);
			mostrarToastAluno('? Erro de conexão: ' + err.message, 'erro');
			alert('Erro de conexão: ' + err.message);
		}
	});
} else {
	console.warn('formRegistrarAluno não encontrado!');
}

// MODAL EDITAR
const overlayModalEditarAluno = document.getElementById('overlayModalEditarAluno');
const fecharModalEditarAlunoBtn = document.getElementById('fecharModalEditarAluno');
const cancelarEditarAlunoBtn = document.getElementById('cancelarEditarAluno');
const formEditarAluno = document.getElementById('formEditarAluno');
const editarAlunoId = document.getElementById('editarAlunoId');
const editarNomeAluno = document.getElementById('editarNomeAluno');
const editarMatriculaAluno = document.getElementById('editarMatriculaAluno');
const editarEmailAluno = document.getElementById('editarEmailAluno');
const editarPcdAluno = document.getElementById('editarPcdAluno');
const editarRendimentoAluno = document.getElementById('editarRendimentoAluno');

function abrirModalEditarAluno(id, nome, matricula, email, pcd, rendimento){
	editarAlunoId.value = id;
	editarNomeAluno.value = nome;
	editarMatriculaAluno.value = matricula;
	editarEmailAluno.value = email;
	editarPcdAluno.value = pcd || 'nao';
	editarRendimentoAluno.value = rendimento || 0;
	
	overlayModalEditarAluno.classList.remove('hidden');
	overlayModalEditarAluno.classList.add('flex');
}

function fecharModalEditarAluno(){
	overlayModalEditarAluno.classList.add('hidden');
	overlayModalEditarAluno.classList.remove('flex');
	formEditarAluno.reset();
}

fecharModalEditarAlunoBtn.addEventListener('click', fecharModalEditarAluno);
cancelarEditarAlunoBtn.addEventListener('click', fecharModalEditarAluno);

overlayModalEditarAluno.addEventListener('click', (e) => {
	if (e.target === overlayModalEditarAluno) fecharModalEditarAluno();
});

formEditarAluno.addEventListener('submit', async (e) => {
	e.preventDefault();

	const id_aluno = editarAlunoId.value;
	const nome = editarNomeAluno.value.trim();
	const matricula = editarMatriculaAluno.value.trim();
	const email = editarEmailAluno.value.trim();
	const pcd = editarPcdAluno.value;
	const rendimento = editarRendimentoAluno.value || 0;
	const turmaId = turmaAtualId || getTurmaIdModal()?.value || null;

	if (!id_aluno) {
		alert('ID do aluno não encontrado');
		return;
	}

	if (!nome) {
		alert('Preencha o nome do aluno');
		return;
	}

	if (!/^[0-9]{7}$/.test(matricula)) {
		alert('Matrícula deve ter exatamente 7 dígitos numéricos');
		return;
	}

	if (!email) {
		alert('Preencha o email do aluno');
		return;
	}

	try {
		const res = await fetch('../controllers/aluno_controller.php?action=editar_aluno', {
			method: 'POST',
			headers: {'Content-Type':'application/json'},
			body: JSON.stringify({id_aluno, nome, matricula, email, pcd, rendimento, turma_id: turmaId, csrf_token: window.csrfToken})
		});
		const j = await res.json();
		if (j.sucesso) {
			fecharModalEditarAluno();
			mostrarToastAluno(`${nome} foi editado com sucesso!`, 'edit');
			const turmaIdAtual = turmaAtualId || getTurmaIdModal()?.value;
			if (turmaIdAtual) {
				await carregarAlunosTurma(turmaIdAtual);
				atualizarContadorAlunos(turmaIdAtual);
			}
		} else {
			alert('Erro ao editar: ' + (j.erro || ''));
		}
	} catch (err) {
		console.error(err);
		alert('Erro de conexão');
	}
});

// delegação para botões Consultar
document.querySelectorAll('.btn-consultar').forEach(btn => {
	btn.addEventListener('click', (e) => {
		const id = btn.getAttribute('data-turma-id');
		const nome = btn.closest('.bg-white')?.querySelector('h2')?.textContent || '';
		abrirModalAlunos(id, nome);
	});
});

const campoBuscarTurma = document.getElementById('buscarTurma');
if (campoBuscarTurma) {
	campoBuscarTurma.addEventListener('input', () => {
		const valor = campoBuscarTurma.value.trim().toLowerCase();
		const cards = document.querySelectorAll('#listaTurmas > div');

		cards.forEach(card => {
			const texto = card.innerText.toLowerCase();
			card.style.display = texto.includes(valor) ? 'block' : 'none';
		});
	});
}

atualizarContadorAlunos();
atualizarContadorOcorrencias();
atualizarContadorFrequencia();
atualizarContadorDesempenho();
	carregarDesempenhoProfessor();
	try {
		// Tenta diversas fontes para obter um id de turma ao carregar a página
		console.debug('[autoLoadTurmaDetail] estado inicial turmaAtualId=', turmaAtualId);
		if (typeof turmaAtualId === 'undefined' || !turmaAtualId) {
			const modalInput = getTurmaIdModal();
			let initialTurmaId = modalInput && modalInput.value ? modalInput.value : null;
			console.debug('[autoLoadTurmaDetail] modalInput value=', modalInput ? modalInput.value : null);
			if (!initialTurmaId) {
				const primeiroBtnEntrar = document.querySelector('.btn-entrar-turma');
				if (primeiroBtnEntrar) initialTurmaId = primeiroBtnEntrar.getAttribute('data-turma-id');
				console.debug('[autoLoadTurmaDetail] primeiroBtnEntrar data-turma-id=', primeiroBtnEntrar ? primeiroBtnEntrar.getAttribute('data-turma-id') : null);
			}
			if (!initialTurmaId) {
				const stn = document.getElementById('selectTurmaNotas');
				if (stn && stn.value) initialTurmaId = stn.value;
				console.debug('[autoLoadTurmaDetail] selectTurmaNotas value=', stn ? stn.value : null);
			}
			if (!initialTurmaId) {
				const sto = document.getElementById('selectTurmaOcorrencia');
				if (sto && sto.value) initialTurmaId = sto.value;
				console.debug('[autoLoadTurmaDetail] selectTurmaOcorrencia value=', sto ? sto.value : null);
			}
			if (!initialTurmaId) {
				const reg = document.getElementById('turmaIdRegistrar');
				if (reg && reg.value) initialTurmaId = reg.value;
				console.debug('[autoLoadTurmaDetail] turmaIdRegistrar value=', reg ? reg.value : null);
			}

			if (initialTurmaId) {
				turmaAtualId = initialTurmaId;
				if (modalInput) modalInput.value = turmaAtualId;
				// carregar alunos e atualizar contadores imediatamente
				carregarAlunosTurma(turmaAtualId).catch(e => console.error(e));
				atualizarContadorAlunos(turmaAtualId);
			}
		} else {
			// já temos turmaAtualId definido: garantir que os contadores estejam corretos
			atualizarContadorAlunos(turmaAtualId);
		}
	} catch (e) {
		console.error('Erro no autoLoadTurmaDetail:', e);
	}

</script>

</body>
</html>
