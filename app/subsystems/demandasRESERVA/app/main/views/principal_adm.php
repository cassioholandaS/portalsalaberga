<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once(__DIR__ . '/../models/usuario_model.php');
require_once(__DIR__ . '/../models/autorizacao.php');

// Verificar sessão e tipo de usuário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirecionar se não autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php?erro=nao_autenticado");
    exit;
}

if (!in_array($_SESSION['tipo'] ?? '', ['master', 'administrador'], true)) {
    header("Location: ../index.php?erro=acesso_negado");
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
$projetosPerfil = [];
$rankingPontuacao = [];
$podeCriarProjeto = false;
$podeGerenciarVinculos = false;

try {
    $autorizacaoUsuarioLogado = new autorizacao();
    $projetosPerfil = $autorizacaoUsuarioLogado->listarProjetosDoUsuario($idUsuarioLogado);
    $rankingPontuacao = (new usuario_model())->listar_ranking_pontuacao($idUsuarioLogado);
    $podeCriarProjeto = $autorizacaoUsuarioLogado->usuarioPodeGerenciarProjetos($idUsuarioLogado);
    $podeGerenciarVinculos = $autorizacaoUsuarioLogado->usuarioPodeGerenciarVinculos($idUsuarioLogado);
} catch (Throwable $exception) {
    error_log('Erro ao carregar dados iniciais da área administrativa: ' . $exception->getMessage());
    $autorizacaoUsuarioLogado = null;
}

$perfilUsuarioExibicao = $tipoSessao === 'master'
    ? 'Master'
    : ($tipoSessao === 'administrador' ? 'Administrador' : 'Usuário');
?>
<!DOCTYPE html>
<script>
        async function abrirModalCategorias(projeto) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay-animated fixed inset-y-0 left-0 z-[300] flex h-full w-screen items-center justify-center bg-black/60 p-4 lg:left-64 lg:w-[calc(100vw-16rem)]';
            modal.innerHTML = `
                <div class="modal-content-animated w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
                        <div><p class="text-xs uppercase tracking-[0.14em] text-gray-500">Projeto</p><h3 class="text-xl font-bold text-[var(--dark-green)]">Categorias de ${escaparTextoAdm(projeto.nome)}</h3></div>
                        <button type="button" class="btnFecharCategorias h-9 w-9 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Fechar categorias"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form class="formNovaCategoria flex gap-2 mb-5">
                        <input name="nome" type="text" maxlength="100" required placeholder="Nome da nova categoria" class="min-w-0 flex-1 rounded-lg border border-gray-200 bg-[var(--suave-green)] px-3 py-2.5 outline-none focus:border-[var(--dark-green)]">
                        <button type="submit" class="btnSalvarCategoria inline-flex items-center gap-2 rounded-lg bg-[var(--dark-green)] px-4 py-2.5 font-semibold text-white hover:bg-[var(--dark-green2)]"><i class="fa-solid fa-plus"></i><span>Criar</span></button>
                    </form>
                    <div><p class="mb-2 text-sm font-semibold text-gray-700">Categorias cadastradas</p><div class="listaCategoriasProjeto space-y-2"></div></div>
                </div>`;
            document.body.appendChild(modal);
            const lista = modal.querySelector('.listaCategoriasProjeto');
            const fechar = () => modal.remove();
            modal.querySelector('.btnFecharCategorias').addEventListener('click', fechar);
            modal.addEventListener('click', (event) => { if (event.target === modal) fechar(); });

            const carregar = async () => {
                lista.innerHTML = '<p class="text-sm text-gray-500">Carregando...</p>';
                try {
                    const response = await fetch(`../controllers/projeto_controller.php?action=listar_categorias&id_projeto=${Number(projeto.id)}`);
                    const data = await response.json();
                    if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Erro ao carregar categorias.');
                    lista.innerHTML = data.dados?.length
                        ? data.dados.map((categoria) => `<div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-[var(--suave-green)]/40 px-3 py-2.5"><span class="flex min-w-0 items-center gap-2 text-sm font-medium text-gray-700"><i class="fa-solid fa-tag text-[var(--dark-green)]"></i><span class="truncate">${escaparTextoAdm(categoria.nome)}</span></span><button type="button" data-acao="excluir-categoria" data-id="${Number(categoria.id)}" data-nome="${escaparTextoAdm(categoria.nome)}" class="botao-excluir flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-700 transition hover:bg-red-100" title="Excluir categoria" aria-label="Excluir categoria ${escaparTextoAdm(categoria.nome)}"><i class="fa-solid fa-trash-can"></i></button></div>`).join('')
                        : '<p class="text-sm text-gray-500">Nenhuma categoria cadastrada.</p>';
                    lista.querySelectorAll('[data-acao="excluir-categoria"]').forEach((botaoExcluir) => {
                        botaoExcluir.addEventListener('click', async () => {
                            const nomeCategoria = botaoExcluir.dataset.nome || 'esta categoria';
                            const confirmar = await confirmarAcaoSistema(`Excluir a categoria "${nomeCategoria}"?`, 'projeto');
                            if (!confirmar) return;

                            botaoExcluir.disabled = true;
                            try {
                                const response = await fetch('../controllers/projeto_controller.php?action=excluir_categoria', {
                                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ id_categoria: Number(botaoExcluir.dataset.id), id_projeto: Number(projeto.id) })
                                });
                                const resultado = await response.json();
                                if (!response.ok || !resultado.sucesso) throw new Error(resultado.erro || 'Erro ao excluir categoria.');
                                notify.success(resultado.message || 'Categoria excluída com sucesso!');
                                await carregar();
                                await carregarCategoriasDemanda();
                            } catch (error) {
                                botaoExcluir.disabled = false;
                                notify.error(' ' + error.message);
                            }
                        });
                    });
                } catch (error) {
                    lista.innerHTML = `<p class="text-sm text-red-600">${escaparTextoAdm(error.message)}</p>`;
                }
            };

            modal.querySelector('.formNovaCategoria').addEventListener('submit', async (event) => {
                event.preventDefault();
                const botao = modal.querySelector('.btnSalvarCategoria');
                const nome = modal.querySelector('[name="nome"]').value.trim();
                botao.disabled = true;
                try {
                    const response = await fetch('../controllers/projeto_controller.php?action=criar_categoria', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_projeto: Number(projeto.id), nome })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Erro ao criar categoria.');
                    modal.querySelector('[name="nome"]').value = '';
                    notify.success(data.message || 'Categoria criada com sucesso!');
                    await carregar();
                    await carregarCategoriasDemanda();
                } catch (error) {
                    notify.error(' ' + error.message);
                } finally {
                    botao.disabled = false;
                }
            });
            await carregar();
        }

    function abrirModalEditarProjeto(projeto) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay-animated modal-editar-projeto-overlay fixed inset-y-0 left-0 z-[300] flex h-full w-screen items-center justify-center bg-black/60 p-4 lg:left-64 lg:w-[calc(100vw-16rem)]';
            modal.innerHTML = `
                    <div class="modal-content-animated modal-editar-projeto-content w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl">
                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3"><i class="fa-solid fa-pen-to-square text-lg text-[var(--dark-green)]"></i><div><p class="text-xs uppercase tracking-[0.14em] text-gray-500">Projeto</p><h3 class="text-xl font-bold text-[var(--dark-green)]">Editar projeto</h3></div></div>
                        <button type="button" class="btnFecharEditarProjeto h-9 w-9 rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-800" aria-label="Fechar edição do projeto"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form class="formEditarProjeto space-y-4">
                        <div><label class="mb-2 block text-sm font-semibold text-gray-700" for="nomeEditarProjeto">Nome do projeto</label><input id="nomeEditarProjeto" name="nome" type="text" maxlength="100" required value="${escaparTextoAdm(projeto.nome || '')}" class="w-full rounded-lg border border-gray-200 bg-[var(--suave-green)] px-3 py-2.5 outline-none focus:border-[var(--dark-green)]"></div>
                        <div><label class="mb-2 block text-sm font-semibold text-gray-700" for="descricaoEditarProjeto">Descrição</label><textarea id="descricaoEditarProjeto" name="descricao" rows="4" class="w-full resize-none rounded-lg border border-gray-200 bg-[var(--suave-green)] px-3 py-2.5 outline-none focus:border-[var(--dark-green)]">${escaparTextoAdm(projeto.descricao || '')}</textarea></div>
                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4"><button type="button" class="btnCancelarEditarProjeto rounded-lg bg-gray-200 px-4 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-300">Cancelar</button><button type="submit" class="btnSalvarEditarProjeto inline-flex items-center gap-2 rounded-lg bg-[var(--dark-green)] px-4 py-2.5 font-semibold text-white transition hover:bg-[var(--dark-green2)]"><i class="fa-solid fa-floppy-disk"></i>Salvar alterações</button></div>
                    </form>
                </div>`;
            document.body.appendChild(modal);
            const fechar = () => modal.remove();
            modal.querySelector('.btnFecharEditarProjeto')?.addEventListener('click', fechar);
            modal.querySelector('.btnCancelarEditarProjeto')?.addEventListener('click', fechar);
            modal.addEventListener('click', (event) => { if (event.target === modal) fechar(); });
            modal.querySelector('.formEditarProjeto')?.addEventListener('submit', async (event) => {
                event.preventDefault();
                const botao = modal.querySelector('.btnSalvarEditarProjeto');
                const nome = modal.querySelector('[name="nome"]')?.value.trim() || '';
                const descricao = modal.querySelector('[name="descricao"]')?.value.trim() || '';
                if (!nome) return;
                botao.disabled = true;
                try {
                    const response = await fetch('../controllers/projeto_controller.php?action=atualizar_projeto', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_projeto: Number(projeto.id), nome, descricao }) });
                    const result = await response.json();
                    if (!response.ok || !result.sucesso) throw new Error(result.erro || 'Erro ao atualizar projeto.');
                    fechar();
                    notify.success(result.message || 'Projeto atualizado com sucesso!');
                    await carregarProjetos();
                    await carregarSolicitacoesProjeto();
                } catch (error) {
                    botao.disabled = false;
                    notify.error(error.message || 'Erro ao atualizar projeto.');
                }
            });
        }
    </script>
    <html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
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
            --perfil-button-alt-hover: #fecaca;
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

        html[data-theme="lula"] #mainAdm {
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
        html[data-theme="lula"] #menuAdmMobile {
            background: linear-gradient(180deg, #9f0b22 0%, #760817 100%) !important;
            border-right-color: rgba(255, 255, 255, 0.14);
        }

        html[data-theme="lula"] #sidebar nav li:has(> button.btnSelect),
        html[data-theme="lula"] #menuAdmMobile nav li:has(> button.btnSelect) {
            background-color: rgba(255, 255, 255, 0.15);
        }

        html[data-theme="lula"] #sidebar nav li:has(> button.btnSelect)::before,
        html[data-theme="lula"] #menuAdmMobile nav li:has(> button.btnSelect)::before {
            background: #fff;
        }

        html[data-theme="lula"] #sidebar .icon-nav-adm.icon-select,
        html[data-theme="lula"] #menuAdmMobile .icon-nav-adm.icon-select,
        html[data-theme="lula"] #sidebar .option-nav-adm.btnSelect,
        html[data-theme="lula"] #menuAdmMobile .option-nav-adm.btnSelect {
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

        .campo-somente-leitura {
            background-color: #d1d5db !important;
            color: #374151;
            cursor: not-allowed;
            opacity: .9;
        }

        .projeto-somente-leitura {
            background-color: #d1d5db;
            color: #4b5563;
            cursor: not-allowed;
            opacity: .9;
            pointer-events: none;
        }

        [data-theme="dark"] #divModalCriar [aria-label="Orientador da demanda"] {
            background-color: var(--suave-green) !important;
            color: var(--theme-text-soft) !important;
            border: 1px solid var(--theme-border);
        }

        [data-theme="dark"] #divModalCriar .fa-solid.fa-user-tie.absolute {
            color: var(--green-icon-crede) !important;
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

        #modalInfs {
            max-height: calc(100vh - 2rem);
            max-height: calc(100dvh - 2rem);
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        @media (max-width: 1023px) {
            .filtro-global-projeto { top: 85px; right: 1rem; }
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

        [data-theme="dark"] .dashboard-status-card,
        [data-theme="dark"] .bg-white,
        [data-theme="dark"] .categoria-relatorio-card,
        [data-theme="dark"] .calendario-ponto-card,
        [data-theme="dark"] .panel,
        [data-theme="dark"] #modalCriarDemanda,
        [data-theme="dark"] #modalAddDescri,
        [data-theme="dark"] #modalConclusao,
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
        [data-theme="dark"] #menuAlunoMobile {
            background: linear-gradient(180deg, #1a1a1a 0%, #121212 100%) !important;
            border-right: 1px solid rgba(255,255,255,0.06);
            color: var(--theme-text) !important;
        }

        [data-theme="dark"] #menuAdm,
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
        [data-theme="dark"] .bg-\[var\(--white\)\],
        [data-theme="dark"] .usuarios-admin-section .bg-\[\#F9FCF8\],
        [data-theme="dark"] .usuarios-admin-section .bg-white,
        [data-theme="dark"] .usuarios-admin-section .panel,
        [data-theme="dark"] .usuarios-admin-section .usuarios-admin-table,
        [data-theme="dark"] .usuarios-admin-section .usuarios-admin-table thead tr,
        [data-theme="dark"] .usuarios-admin-section .usuarios-admin-table tbody tr,
        [data-theme="dark"] .usuarios-admin-section .usuarios-admin-table th,
        [data-theme="dark"] .usuarios-admin-section .usuarios-admin-table td {
            background-color: var(--theme-surface) !important;
            border-color: var(--theme-border) !important;
            color: var(--theme-text) !important;
        }

        [data-theme="dark"] .usuarios-admin-section .usuarios-admin-table thead tr {
            background: linear-gradient(90deg, rgba(31, 174, 90, 0.98), rgba(22, 143, 73, 0.95)) !important;
        }

        [data-theme="dark"] .usuarios-admin-section .usuarios-admin-table thead th,
        [data-theme="dark"] .usuarios-admin-section .usuarios-admin-table thead th * {
            color: #ffffff !important;
        }

        [data-theme="dark"] .usuarios-admin-section .usuarios-admin-table tbody tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        [data-theme="dark"] .categoria-relatorio-card .text-gray-500,
        [data-theme="dark"] .categoria-relatorio-card .text-gray-600,
        [data-theme="dark"] .categoria-relatorio-card .text-gray-700,
        [data-theme="dark"] .categoria-relatorio-card .text-gray-400,
        [data-theme="dark"] .relatorios-grid .text-gray-500,
        [data-theme="dark"] .relatorios-grid .text-gray-600,
        [data-theme="dark"] .relatorios-grid .text-gray-700,
        [data-theme="dark"] .relatorios-grid .text-gray-400,
        [data-theme="dark"] .panel-title,
        [data-theme="dark"] .panel .panel-title,
        [data-theme="dark"] .legend-item,
        [data-theme="dark"] .legend-value,
        [data-theme="dark"] .legend-label,
        [data-theme="dark"] .progress-head,
        [data-theme="dark"] .donut-center span,
        [data-theme="dark"] .donut-center strong,
        [data-theme="dark"] .panel-title,
        [data-theme="dark"] .panel .panel-title {
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

        [data-theme="dark"] .dashboard-status-card:nth-of-type(2) i.fa-hourglass {
            color: #ffffff !important;
            background-color: var(--accent-yellow) !important;
        }

        [data-theme="dark"] .dashboard-status-card:nth-of-type(3) i.fa-check {
            background-color: #6b7280 !important;
            color: #ffffff !important;
        }

        [data-theme="dark"] .dashboard-status-card:nth-of-type(3) .quantFinalizadasViewAdm,
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

        [data-theme="dark"] .demanda-card-layout .btnVer,
        [data-theme="dark"] .demanda-card-layout .btnExcluir,
        [data-theme="dark"] .demanda-card-layout .btnVer p,
        [data-theme="dark"] .demanda-card-layout .btnExcluir p {
            color: #121212 !important;
        }

        [data-theme="dark"] .botao-excluir,
        [data-theme="dark"] .btnExcluir,
        [data-theme="dark"] .btnExcluirHistorico,
        [data-theme="dark"] #btnRemoverFoto {
            background-color: #fee2e2 !important;
            border-color: #fecaca !important;
            color: #b91c1c !important;
        }

        [data-theme="dark"] .botao-excluir *,
        [data-theme="dark"] .btnExcluir *,
        [data-theme="dark"] .btnExcluirHistorico *,
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

        [data-theme="dark"] #listaValidacoesJustificativas .linha-prioridade-alta,
        [data-theme="dark"] #listaValidacoesJustificativas .linha-prioridade-media,
        [data-theme="dark"] #listaValidacoesJustificativas .linha-prioridade-baixa {
            color: #1f2937 !important;
        }

        [data-theme="dark"] #listaValidacoesJustificativas .linha-prioridade-alta *,
        [data-theme="dark"] #listaValidacoesJustificativas .linha-prioridade-media *,
        [data-theme="dark"] #listaValidacoesJustificativas .linha-prioridade-baixa * {
            color: #1f2937 !important;
        }

        [data-theme="dark"] .validacao-justificativa {
            background-color: #fffbea !important;
            border-color: #f2cf64 !important;
            color: #1f2937 !important;
        }

        [data-theme="dark"] .validacao-justificativa .text-gray-700,
        [data-theme="dark"] .validacao-justificativa .text-yellow-700,
        [data-theme="dark"] .validacao-justificativa strong {
            color: #713f12 !important;
        }

        [data-theme="dark"] .btnScale,
        [data-theme="dark"] button,
        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            transition: background-color 0.25s ease, color 0.25s ease, border-color 0.25s ease;
        }

        /* Estilos para o Filtro de Projeto */
        #btnFiltroProject {
            position: relative;
        }

        [data-theme="dark"] #btnFiltroProject {
            background: linear-gradient(135deg, rgba(31, 174, 90, 0.9), rgba(47, 217, 115, 0.8)) !important;
            color: #fff !important;
        }

        [data-theme="dark"] .group:hover > div {
            background: var(--theme-surface) !important;
            border-color: var(--theme-border) !important;
        }

        [data-theme="dark"] .filtroProjectItem {
            color: var(--theme-text) !important;
        }

        [data-theme="dark"] .filtroProjectItem:hover {
            background-color: rgba(31, 174, 90, 0.15) !important;
        }

        [data-theme="dark"] #filtroActiveBadge {
            background: rgba(59, 130, 246, 0.2) !important;
            color: #60a5fa !important;
            border: 1px solid rgba(59, 130, 246, 0.5);
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

        [data-theme="dark"] .categoria-relatorio-card__top,
        [data-theme="dark"] .categoria-relatorio-card p {
            color: #ffffff !important;
        }
        .categoria-relatorio-btn:hover { background: var(--dark-green2); }

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

        .usuarios-admin-table {
            min-width: 760px;
        }

        .usuarios-admin-section {
            font-family: "Poppins", sans-serif;
        }

        .usuarios-admin-pagination label {
            pointer-events: auto;
        }

        .usuarios-admin-pagination select {
            pointer-events: auto;
            cursor: pointer;
        }

        #painelProjetosUsuarioModal label,
        #painelProjetosUsuarioModal input[type="checkbox"] {
            pointer-events: auto;
            cursor: pointer;
        }

        #usuarioPodeCriarProjetoModal,
        label.permissao-criar-projeto {
            pointer-events: auto;
            cursor: pointer;
        }

        .demandas-grid-admin {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
            align-items: stretch;
        }

        @media (max-width: 1400px) {
            .demandas-grid-admin { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        }

        @media (max-width: 900px) {
            .demandas-grid-admin { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        }

        @media (max-width: 700px) {
            .demandas-grid-admin { grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        }

        @media (max-width: 640px) {
            .usuarios-admin-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .usuarios-admin-filter-actions {
                justify-content: stretch;
            }

            .usuarios-admin-filter-actions button {
                flex: 1;
            }
        }

        @media (max-width: 1023px) {
            [id^="divModal"]:not(#divModalAvisoSistema),
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
            #modalEditarDemanda,
            #modalCriarDemanda,
            #modalAddDescri,
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

        @keyframes modalOverlayIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalContentIn {
            from { opacity: 0; transform: translateY(-16px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-overlay-animated {
            animation: modalOverlayIn 0.2s ease-out both;
        }

        .modal-content-animated {
            animation: modalContentIn 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        [data-theme="dark"] .project-card {
            border-color: rgba(118, 216, 158, 0.28) !important;
        }

        [data-theme="dark"] .project-metric-badge {
            background-color: #168f49 !important;
            color: #ffffff !important;
        }

        .modal-editar-projeto-overlay {
            animation: modalOverlayEffect 0.2s ease-out;
        }

        .modal-editar-projeto-content {
            animation: modalEffect 0.45s cubic-bezier(0.22, 1, 0.36, 1);
        }

        @keyframes modalOverlayEffect {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        [id^="shadow"] {
            left: 16rem !important;
            background-color: rgba(0, 0, 0, 0.6) !important;
            opacity: 1 !important;
            animation: modalOverlayEffect 0.2s ease-out both;
        }

        @media (max-width: 1023px) {
            [id^="shadow"] {
                left: 0 !important;
            }

            #modalCorteFoto,
            #modalConfirmacaoFoto {
                left: 0 !important;
            }
        }

        .modal-overlay-animated {
            background-color: rgba(0, 0, 0, 0.6) !important;
        }

        #modalCorteFoto,
        #modalConfirmacaoFoto {
            left: 16rem !important;
        }

        .aviso-ponto-progresso {
            animation: avisoPontoProgresso 2.5s linear forwards;
        }

        @keyframes avisoPontoProgresso {
            from { width: 100%; }
            to { width: 0; }
        }

        [id^="modal"],
        [id^="divModal"] > div {
            animation: modalEffect 0.45s cubic-bezier(0.22, 1, 0.36, 1);
        }

        #modalCriarDemanda,
        #modalConclusao,
        #modalConclusaoSecDemandas,
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
            #modalCriarDemanda {
                height: fit-content;
                min-height: 628px;
                max-height: calc(100dvh - 32px);
                overflow-x: hidden;
                overflow-y: auto;
            }
        }

            @media (min-width: 1024px) {
                #sidebar .sidebar-brand-title { font-size: 0.9rem; }
                #sidebar .fraseMenu { font-size: 0.65rem; line-height: 1.25; }
                #sidebar .option-nav-adm,
                #sidebar .option-nav-sair { font-size: 0.75rem; }
                #sidebar .icon-nav-adm,
                #sidebar .icon-nav-sair { font-size: 0.75rem; }
            }

        #sidebar,
        #menuAdmMobile {
            background: #064c2b !important;
            border-right: 1px solid rgba(209, 231, 219, 0.18);
            color: #e8f2ed;
            scrollbar-color: rgba(209, 231, 219, 0.35) transparent;
            scrollbar-width: thin;
        }

        #sidebar nav ul,
        #menuAdmMobile nav ul {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100%;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        #sidebar nav [hidden],
        #menuAdmMobile nav [hidden] {
            display: none !important;
        }

        #sidebar nav li:not(.sidebar-section-heading),
        #menuAdmMobile nav li:not(.sidebar-section-heading),
        #sidebar > div:last-child > div > li {
            display: grid;
            grid-template-columns: 26px minmax(0, 1fr);
            align-items: center;
            gap: 8px;
            width: 100%;
            margin-top: 0 !important;
        }

        #sidebar nav li > i,
        #menuAdmMobile nav li > i,
        #sidebar > div:last-child > div > li > i {
            width: 26px !important;
            height: 40px !important;
            margin-left: 0 !important;
            border-radius: 6px;
            color: #c5dfce;
            font-size: 0.9rem !important;
        }

        #sidebar .option-nav-adm,
        #sidebar .option-nav-sair,
        #menuAdmMobile .option-nav-adm,
        #menuAdmMobile .option-nav-sair {
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

        #sidebar .option-nav-adm:hover,
        #sidebar .option-nav-sair:hover,
        #menuAdmMobile .option-nav-adm:hover,
        #menuAdmMobile .option-nav-sair:hover,
        #sidebar .option-nav-adm.btnSelect,
        #sidebar .option-nav-sair.btnSelect,
        #menuAdmMobile .option-nav-adm.btnSelect,
        #menuAdmMobile .option-nav-sair.btnSelect {
            background-color: transparent !important;
        }

        #sidebar .option-nav-adm:hover:not(.btnSelect),
        #sidebar .option-nav-sair:hover,
        #menuAdmMobile .option-nav-adm:hover:not(.btnSelect),
        #menuAdmMobile .option-nav-sair:hover,
        #sidebar .option-nav-adm.icon-hover:not(.btnSelect),
        #menuAdmMobile .option-nav-adm.icon-hover:not(.btnSelect) {
            color: #fff !important;
            padding-left: 16px !important;
        }

        #sidebar .option-nav-adm.btnSelect,
        #menuAdmMobile .option-nav-adm.btnSelect,
        #sidebar .option-nav-sair.btnSelect,
        #menuAdmMobile .option-nav-sair.btnSelect {
            color: #ffc04d !important;
            padding-left: 16px !important;
        }

        #sidebar nav .sidebar-item-enter,
        #menuAdmMobile nav .sidebar-item-enter {
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
            #menuAdmMobile nav .sidebar-item-enter {
                animation: none;
            }

            #sidebar .option-nav-adm,
            #sidebar .option-nav-sair,
            #menuAdmMobile .option-nav-adm,
            #menuAdmMobile .option-nav-sair {
                transition-duration: 0.01ms;
            }
        }

        #sidebar .effect-bar::after,
        #menuAdmMobile .effect-bar::after {
            display: none;
        }

        #sidebar .icon-hover,
        #menuAdmMobile .icon-hover {
            background: transparent;
            color: #c5dfce !important;
        }

        #sidebar .icon-select,
        #menuAdmMobile .icon-select {
            background: transparent;
            color: #ffc04d !important;
        }

        #sidebar nav li:has(> button.btnSelect),
        #menuAdmMobile nav li:has(> button.btnSelect) {
            position: relative;
            overflow: hidden;
            border-radius: 7px;
            background-color: #276448;
            padding-left: 5px;
            transition: padding-left 180ms ease;
        }

        #sidebar nav li:has(> button.btnSelect)::before,
        #menuAdmMobile nav li:has(> button.btnSelect)::before {
            position: absolute;
            inset: 8px auto 8px 0;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: #f3a11a;
            content: "";
        }

        #sidebar nav li:has(> button.btnSelect) > button,
        #menuAdmMobile nav li:has(> button.btnSelect) > button {
            background: transparent !important;
        }

        #sidebar nav li:has(> button:hover):not(:has(> button.btnSelect)),
        #menuAdmMobile nav li:has(> button:hover):not(:has(> button.btnSelect)),
        #sidebar nav li:focus-within:not(:has(> button.btnSelect)),
        #menuAdmMobile nav li:focus-within:not(:has(> button.btnSelect)),
        #sidebar > div:last-child > div > li:has(button:hover),
        #menuAdmMobile nav li:has(form > button:hover) {
            border-radius: 7px;
            background-color: rgba(255, 255, 255, 0.1);
        }

        #sidebar nav li:has(> button:hover) > button,
        #menuAdmMobile nav li:has(> button:hover) > button,
        #sidebar nav li:focus-within > button,
        #menuAdmMobile nav li:focus-within > button,
        #sidebar > div:last-child > div > li:has(button:hover) > form > button,
        #menuAdmMobile nav li:has(form > button:hover) > form > button {
            background: transparent !important;
        }

        #sidebar .sidebar-section-heading,
        #menuAdmMobile .sidebar-section-heading {
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
        #menuAdmMobile .sidebar-section-heading:first-child {
            margin-top: 0 !important;
        }

        #sidebar .sidebar-section-heading i,
        #menuAdmMobile .sidebar-section-heading i {
            color: #8db7a1;
            font-size: 0.65rem;
        }

        #sidebar .sidebar-group-toggle,
        #menuAdmMobile .sidebar-group-toggle {
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
        #menuAdmMobile .sidebar-group-toggle:hover {
            color: #fff;
        }

        #sidebar .sidebar-group-toggle i,
        #menuAdmMobile .sidebar-group-toggle i {
            transition: transform 180ms ease, color 180ms ease;
        }

        #sidebar .sidebar-group-toggle[aria-expanded="true"] i,
        #menuAdmMobile .sidebar-group-toggle[aria-expanded="true"] i {
            transform: rotate(180deg);
        }

        #sidebar .sidebar-group-toggle:focus-visible,
        #menuAdmMobile .sidebar-group-toggle:focus-visible {
            outline: 2px solid #f3a11a;
            outline-offset: 3px;
        }

        #menuAdmMobile {
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

            body.sidebar-collapsed #mainAdm {
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
        #menuAdmMobile nav li form {
            width: 100%;
            min-width: 0;
        }

        [data-theme="dark"] #sidebar,
        [data-theme="dark"] #menuAdmMobile {
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
        [data-theme="dark"] #menuAdmMobile .sidebar-section-heading {
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--theme-text-soft);
        }

        [data-theme="dark"] #sidebar .sidebar-section-heading i,
        [data-theme="dark"] #menuAdmMobile .sidebar-section-heading i,
        [data-theme="dark"] #sidebar nav li > i,
        [data-theme="dark"] #menuAdmMobile nav li > i {
            color: var(--theme-text);
        }

        [data-theme="dark"] #sidebar .icon-nav-adm.icon-select,
        [data-theme="dark"] #menuAdmMobile .icon-nav-adm.icon-select,
        [data-theme="dark"] #sidebar .option-nav-adm.btnSelect,
        [data-theme="dark"] #menuAdmMobile .option-nav-adm.btnSelect {
            color: #10c157 !important;
        }

        [data-theme="dark"] #sidebar nav li:has(> button.btnSelect)::before,
        [data-theme="dark"] #menuAdmMobile nav li:has(> button.btnSelect)::before {
            background: #10c157;
        }

        [data-theme="dark"] #sidebar .icon-nav-adm.icon-hover,
        [data-theme="dark"] #menuAdmMobile .icon-nav-adm.icon-hover,
        [data-theme="dark"] #sidebar .option-nav-adm.icon-hover:not(.btnSelect),
        [data-theme="dark"] #menuAdmMobile .option-nav-adm.icon-hover:not(.btnSelect) {
            color: #fff !important;
        }

        [data-theme="dark"] #sidebar nav li:has(> button.btnSelect),
        [data-theme="dark"] #menuAdmMobile nav li:has(> button.btnSelect) {
            background-color: #292929;
        }

        [data-theme="dark"] #sidebar nav li:has(> button:hover):not(:has(> button.btnSelect)),
        [data-theme="dark"] #menuAdmMobile nav li:has(> button:hover):not(:has(> button.btnSelect)),
        [data-theme="dark"] #sidebar nav li:focus-within:not(:has(> button.btnSelect)),
        [data-theme="dark"] #menuAdmMobile nav li:focus-within:not(:has(> button.btnSelect)),
        [data-theme="dark"] #sidebar > div:last-child > div > li:has(button:hover),
        [data-theme="dark"] #menuAdmMobile nav li:has(form > button:hover) {
            background-color: rgba(255, 255, 255, 0.08);
        }

        @media (min-width: 1024px) {
            #sidebar {
                padding: 24px 18px !important;
            }

            #sidebar #menuAdm {
                padding-top: 8px !important;
            }

            #sidebar #menuAdm nav {
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
    <script src="../js/notifications.js"></script>
    <script src="../js/modals.js"></script>
    <script src="../js/theme.js"></script>
</head>

<body>
    <div class="grid lg:grid-cols-[16rem_minmax(0,1fr)] h-full">
        <div id="containerFiltroProjetoAdm" class="filtro-global-projeto fixed hidden" aria-label="Filtro global de projeto">
            <button type="button" id="btnFiltroGlobalProjeto" class="flex h-10 w-16 items-center justify-center rounded-b-full bg-[var(--dark-green)] text-white shadow-lg transition hover:h-12" aria-expanded="false" aria-controls="painelFiltroGlobalProjeto" title="Filtrar por projeto">
                <i class="fa-solid fa-chevron-down transition-transform" aria-hidden="true"></i>
            </button>
            <div id="painelFiltroGlobalProjeto" class="filtro-global-painel absolute right-0 top-11 hidden w-72 rounded-xl border border-gray-100 bg-white p-4 shadow-2xl">
                <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-[var(--dark-green)]"><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><span>Filtrar sistema por projeto</span></div>
                <select id="filtroGlobalProjeto" class="w-full rounded-lg border border-gray-200 bg-[var(--suave-green)] px-3 py-2.5 text-sm text-gray-700 outline-none focus:border-[var(--dark-green)]" aria-label="Projeto para filtro global">
                    <option value="todos">Todos os projetos</option>
                </select>
                <div class="mt-3 flex items-center justify-between gap-2"><span id="filtroActiveBadge" class="hidden rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700"><span id="filtroActiveText"></span></span><button type="button" id="btnLimparFiltro" class="ml-auto text-xs font-semibold text-[var(--dark-green)] hover:text-[var(--dark-green2)]">Limpar filtro</button></div>
            </div>
        </div>
        <!-- Menu Lateral -->
        <div id="sidebar" class="fixed left-0 top-0 w-full lg:w-64 h-[85px] lg:h-screen bg-[var(--dark-green2)] lg:py-10 flex lg:flex-col items-center lg:items-center justify-between px-3 xl:px-6 z-50 overflow-y-auto">
            <div class="flex lg:flex-col justify-between lg:justify-center items-center lg:items-start w-full">
                <div class="flex items-center lg:justify-start ">
                    <img class="system-brand-logo object-cover size-12 lg:size-16" src="../assets/s_simbol.png" data-default-src="../assets/s_simbol.png" data-lula-src="../assets/pt-removebg-preview.png" alt="">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <p class="sidebar-brand-title font-semibold lg:text-[1.2vw] xl:text-[1vw]"><span class="text-white">Sistema</span> <span class="text-[var(--accent-yellow)]">STGM</span></p>
                        </div>
                        <p class="fraseMenu text-gray-400 text-[10px] sm:text-xs">Administrando processos com máxima eficiência.</p>
                    </div>
                </div>
                <button type="button" class="theme-toggle hidden lg:inline-flex" aria-label="Alterar tema">
                    <i class="theme-toggle__icon fa-solid fa-moon" aria-hidden="true"></i>
                    <span class="theme-toggle__label">Dark</span>
                </button>

                <!-- Menu btn Mobile -->
                <div class="block lg:hidden translate-x-[-12px]">
                    <button id="openMenuBtn" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="menuAdmMobile">
                        <i class="fa-solid fa-bars text-xl text-gray-400"></i>
                    </button>
                </div>

                <!-- MENU ADMINISTRADORES PC-->
                <div id="menuAdm" class="pt-6 w-full hidden lg:block">
                    <nav class="pt-4 w-full">
                        <ul class="space-y-6 w-full">
                            <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="demandas" aria-expanded="true"><span>Demandas</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fas fa-tasks h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1.2vw] xl:text-[1vw] icon-nav-adm icon-select"></i>
                                <button data-secao="demandas" class="h-[50px] w-[100%] font-semibold flex justify-start items-center lg:text-[0.8vw] xl:text-[0.7vw] option-nav-adm hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar btnSelect effect-bar-select">
                                    <p>Demandas</p>
                                </button>
                            </li>

                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fas fa-history h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1.2vw] xl:text-[1vw] icon-nav-adm"></i>
                                <button data-secao="historico" class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-adm hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Histórico</p>
                                </button>
                            </li>


                            <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="administracao" aria-expanded="true"><span>Administração</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fa-solid fa-chart-column h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1.2vw] xl:text-[1vw] icon-nav-adm"></i>
                                <button data-secao="relatorios" class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-adm hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Relatórios</p>
                                </button>
                            </li>

                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="bi bi-person-lines-fill h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1.2vw] xl:text-[1vw] icon-nav-adm"></i>
                                <button data-secao="gerenciar usuarios" class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-adm hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Gerenciar Usuários</p>
                                </button>
                            </li>

                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fa-solid fa-diagram-project h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1.2vw] xl:text-[1vw] icon-nav-adm"></i>
                                <button data-secao="projetos" class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-adm hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Projetos</p>
                                </button>
                            </li>

                            <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="conta" aria-expanded="true"><span>Conta</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                            <li class="flex items-center space-x-2.5 text-gray-200">
                                <i class="fa-solid fa-user h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1.2vw] xl:text-[1vw] icon-nav-adm"></i>
                                <button data-secao="perfil" class="h-[50px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 lg:text-[0.8vw] xl:text-[0.7vw] option-nav-adm hover:bg-[var(--gray-btnActive)] lg:px-2 xl:px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Perfil</p>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>

            <div class="flex lg:flex-col justify-between lg:justify-center items-center lg:items-start w-full hidden lg:block">
                <li class="flex items-center space-x-2.5 text-gray-200 w-full">
                    <i class="fa-solid fa-right-from-bracket h-[50px] w-[65px] flex items-center justify-center rounded-lg lg:text-[1vw] xl:text-[1.2vw] xl:text-[1vw] icon-nav-sair"></i>
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

        <!-- MENU ADMINISTRADORES MOBILE-->
        <div id="menuAdmMobile" class="pt-6 w-[100%] h-[100vh] z-[9999] bg-[var(--dark-green2)] p-6 translate-x-[-100%] transition ease-in-out duration-300 hidden fixed lg:hidden">

            <div class="pt-4 flex justify-between items-center">
                <div class="flex items-center lg:justify-start ">
                    <img class="system-brand-logo object-cover size-16" src="../assets/s_simbol.png" data-default-src="../assets/s_simbol.png" data-lula-src="../assets/pt-removebg-preview.png" alt="">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <p class="font-semibold lg:text-[1.2vw] xl:text-[1vw]"><span class="text-white">Sistema</span> <span class="text-[var(--accent-yellow)]">STGM</span></p>
                        </div>
                        <p class="fraseMenu text-gray-400 text-xs">Administrando processos com máxima eficiência.</p>
                    </div>
                </div>

                <button type="button" class="theme-toggle lg:hidden" aria-label="Alterar tema">
                    <i class="theme-toggle__icon fa-solid fa-moon" aria-hidden="true"></i>
                    <span class="theme-toggle__label">Dark</span>
                </button>

                <div class="flex justify-end translate-y-[-25px]">
                    <button id="closeMenuBtn" type="button" aria-label="Fechar menu"><i class="fa-solid fa-xmark text-xl text-white"></i></button>
                </div>
            </div>

            <nav class="pt-8 w-full">
                <ul class="space-y-6 w-full">
                    <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="demandas" aria-expanded="true"><span>Demandas</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fas fa-tasks h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-adm icon-select"></i>
                        <button data-secao="demandas" class="h-[55px] w-[100%] font-semibold flex justify-start items-center text-sm option-nav-adm hover:bg-[var(--gray-btnActive)] py-2 px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar btnSelect effect-bar-select">
                            <p>Demandas</p>
                        </button>
                    </li>

                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fas fa-history h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-adm"></i>
                        <button data-secao="historico" class="h-[55px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 text-sm option-nav-adm hover:bg-[var(--gray-btnActive)] py-2 px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                            <p>Histórico</p>
                        </button>
                    </li>

                    <!-- <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fa-solid fa-chart-column h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-adm"></i>
                        <button data-secao="projetos" class="h-[55px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 text-sm option-nav-adm hover:bg-[var(--gray-btnActive)] py-2 px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                            <p>Relatórios</p>
                        </button>
                    </li> -->

                    <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="administracao" aria-expanded="true"><span>Administração</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fa-solid fa-chart-column h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-adm"></i>
                        <button data-secao="relatorios" class="h-[55px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 text-sm option-nav-adm hover:bg-[var(--gray-btnActive)] py-2 px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                            <p>Relatórios</p>
                        </button>
                    </li>

                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="bi bi-person-lines-fill h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-adm"></i>
                        <button data-secao="gerenciar usuarios" class="h-[55px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 text-sm option-nav-adm hover:bg-[var(--gray-btnActive)] py-2 px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                            <p>Gerenciar Usuários</p>
                        </button>
                    </li>

                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fa-solid fa-diagram-project h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-adm"></i>
                        <button data-secao="projetos" class="h-[55px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 text-sm option-nav-adm hover:bg-[var(--gray-btnActive)] py-2 px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                            <p>Projetos</p>
                        </button>
                    </li>

                    <li class="sidebar-section-heading"><button type="button" class="sidebar-group-toggle" data-sidebar-group="conta" aria-expanded="true"><span>Conta</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button></li>
                    <li class="flex items-center space-x-2.5 text-gray-200">
                        <i class="fa-solid fa-user h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-adm"></i>
                        <button data-secao="perfil" class="h-[55px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 text-sm option-nav-adm hover:bg-[var(--gray-btnActive)] py-2 px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                            <p>Perfil</p>
                        </button>
                    </li>

                    <div class="flex lg:flex-col justify-between lg:justify-center items-center lg:items-start w-full">
                        <li class="flex items-center space-x-2.5 text-gray-200 w-full">
                            <i class="fa-solid fa-right-from-bracket h-[55px] w-[65px] flex items-center justify-center rounded-lg text-sm icon-nav-sair"></i>
                            <form class="w-full" action="../controllers/usuario_controller.php?action=logout" method="POST">
                                <button class="h-[55px] w-[100%] font-semibold flex justify-start items-center space-x-1.5 text-gray-200 text-sm option-nav-sair hover:bg-[var(--gray-btnActive)] py-2 px-3 rounded-lg hover:translate-x-1 hover:text-[var(--accent-yellow)] transition ease-in-out duration-300 effect-bar">
                                    <p>Sair </p>
                                </button>
                            </form>
                        </li>
                    </div>
                </ul>
            </nav>
        </div>


        <!-- Principal dos Administradores -->
        <main id="mainAdm" class="bg-[var(--gray-crede)] min-h-[100vh] overflow-y-auto lg:ml-64 lg:w-[calc(100vw-16rem)] pt-24 lg:pt-0 w-[100%] pb-8">

            <!-- DashBoard -->
                <div id="secDashboardAdm" class="secs-adm pt-12 px-1 lg:px-12 lg:-ml-6">

                <div class="w-[90%] flex mx-auto items-center justify-center lg:justify-between mb-6 space-y-2">
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

                <div id="shadowDashBoardADM" class="bg-black inset-0 opacity-60 z-[190] fixed hidden"></div>

                <!-- Modal de Exclusão -->
                <div id="divModalExclusao" class="fixed inset-y-0 left-0 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center z-[200] hidden">
                    
                </div>

                <!-- Modal de confirmação para assumir demanda -->
                <div id="divModalAssumir" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[200] hidden">

                </div>

                <!-- Modal de Criar Demanda -->
                <div id="divModalCriar" class="fixed inset-y-0 left-0 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center z-[200] hidden">

                    <div id="modalCriarDemanda" class="bg-[var(--white)] w-[90%] sm:w-[420px] pb-8 z-[200] rounded-lg p-5 flex flex-col gap-5">
                        <div class="flex items-center justify-between">
                            <div></div>
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-clipboard-list text-[var(--green-icon-crede)] bg-[var(--suave-green)] py-3 px-[18.5px] rounded-xl text-2xl"></i>
                            </div>
                            <!-- Fechar Modal -->
                            <button id="btnFecharModalCriar">
                                <i class="fa-solid fa-arrow-right text-xl text-[var(--dark-green)] hover:text-[var(--dark-green)] hover:translate-x-1 transition ease-in-out duration-300"></i>
                            </button>
                        </div>


                        <!-- Inputs Requisitados -->
                        <form id="formDemanda" autocomplete="off" class="flex flex-col w-full gap-7">
                            <div class="w-full flex items-center relative">
                                <i class="fa-solid fa-tag absolute text-[var(--green-icon-crede)] ml-3"></i>
                                <input class="w-full py-2 rounded-md bg-[var(--suave-green)] outline-none text-gray-500 indent-9" type="text" name="titulo" id="tituloDemandaInput" placeholder="Título">
                            </div>

                            <div class="w-full flex items-center relative">
                                <i class="fa-solid fa-triangle-exclamation absolute text-[var(--green-icon-crede)] ml-3"></i>
                                <select name="prioridade" id="prioridadeDemandaSelect" class="w-full py-2 rounded-md bg-[var(--suave-green)] outline-none text-gray-500 pl-9">
                                    <option disabled selected hidden value="">Prioridade</option>
                                    <option value="alta" class="text-[var(--red1)]">Alta</option>
                                    <option value="media" class="text-[var(--accent-yellow)]">Média</option>
                                    <option value="baixa" class="text-[#2b6cb0]">Baixa</option>
                                </select>
                            </div>


                            <div class="w-full flex items-center relative">
                                <i class="fa-solid fa-user-tie absolute text-[var(--green-icon-crede)] ml-3"></i>
                                <div class="w-full py-2 rounded-md bg-gray-200 text-gray-500 pl-9 select-none cursor-not-allowed" aria-label="Orientador da demanda">
                                    Orientador: <?php echo htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>

                            <!-- Projeto e atribuição opcional -->
                            <div id="divRadio" class="flex flex-col justify-start items-start gap-5 relative">
                                <!-- <p class="text-sm text-gray-500">Projeto responsável</p> -->
                                <div id="listaProjetosResponsaveis" class="w-full"></div>

                                <div class="w-full flex items-center relative pt-2">
                                    <i class="fa-solid fa-sitemap absolute text-[var(--green-icon-crede)] ml-3"></i>
                                    <select name="categoria" id="categoriaDemandaSelect" class="w-full py-2 rounded-md bg-[var(--suave-green)] outline-none text-gray-500 pl-9">
                                        <option disabled selected hidden value="">Categoria</option>
                                    </select>
                                </div>

                                <!-- TAMBÉM SE PUXA A DATA DE CRIAÇÃO, MAS ISSO O SISTEMA FAZ AUTOMATICAMENTE!  -->

                                <div class="w-full flex items-center relative pt-2">
                                    <i class="fa-solid fa-clock absolute text-[var(--green-icon-crede)] ml-3"></i>
                                    <input class="w-full py-2 rounded-md bg-[var(--suave-green)] outline-none text-gray-500 indent-9" type="date" name="prazo" id="prazoDemanda" min="<?php echo date('Y-m-d'); ?>">
                                </div>

                                <div class="w-full flex items-center relative pt-2">
                                    <i class="fa-solid fa-users absolute text-[var(--green-icon-crede)] ml-3"></i>
                                    <input id="quantidadeAlunosDemanda" type="number" min="1" max="10" value="1" class="w-full py-2 rounded-md bg-[var(--suave-green)] outline-none text-gray-500 pl-9 pr-3" placeholder="Alunos necessários" aria-label="Quantidade de alunos para iniciar a demanda">
                                </div>

                                <label for="usuariosAtribuidosBusca" class="text-sm text-gray-500 pt-2">Responsáveis (opcional)</label>
                                <div class="w-full relative">
                                    <i class="fa-solid fa-magnifying-glass absolute text-[var(--green-icon-crede)] ml-3 mt-3"></i>
                                    <input id="usuariosAtribuidosBusca" type="search" placeholder="Pesquisar usuário..." class="w-full py-2 rounded-md bg-[var(--suave-green)] outline-none text-gray-500 pl-9 pr-3" aria-describedby="usuariosAtribuidosAjuda">
                                    <div id="usuariosAtribuidosResultados" class="absolute z-10 hidden w-full mt-1 rounded-md bg-white border border-gray-200 shadow-lg max-h-40 overflow-y-auto"></div>
                                </div>
                                <div id="usuariosAtribuidosSelecionados" class="flex flex-wrap gap-2 w-full"></div>
                                <p id="usuariosAtribuidosAjuda" class="text-xs text-gray-400">Opcional: selecione um ou mais responsáveis. Sem seleção, a demanda ficará aberta para o projeto escolhido.</p>
                                <select id="usuariosAtribuidosSelect" name="usuarios_atribuidos[]" multiple class="hidden" aria-hidden="true"></select>
                            </div>

                            <div class="w-full flex flex-col gap-4">
                                <button id="btnAddDescri" class="py-3 bg-[var(--accent-yellow)] text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center hover:bg-[var(--dark-accent-yellow2)] hover:text-[var(--white)] transition ease-in-out duration-300" type="button">
                                    <i id="iconBtnAddDescri" class="fa-solid fa-align-left"></i>
                                    <p id="textBtnAddDescri">Adicionar Descrição</p>
                                </button>

                                <button id="btnCriarDemanda" class="py-3 bg-[var(--dark-green)] text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center hover:bg-[var(--dark-green2)]  transition ease-in-out duration-300" type="button">
                                    <i class="bi bi-plus-circle"></i>
                                    <p>Criar Demanda</p>
                                </button>
                            </div>

                        </form>
                    </div>

                </div>

                <!-- Modal de Criar Descrição -->
                <div id="divModalDescri" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[200] hidden">
                    <div id="modalAddDescri" class="bg-[var(--white)] w-[90%] sm:w-[420px] pb-8 z-[200] rounded-lg p-5 flex flex-col gap-5">

                        <div class="flex items-center justify-between">
                            <div></div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-align-left text-[var(--green-icon-crede)]"></i>
                                <p class="text-gray-200 font-semibold"><span class="text-[var(--dark-green)]">Modal</span> <span class="text-[var(--accent-yellow)]">Descrição</span></p>
                            </div>
                            <button id="btnFecharModalDescri" type="button" aria-label="Fechar modal de descrição">
                                <i class="fa-solid fa-arrow-right text-xl text-[var(--dark-green)] hover:text-[var(--dark-green)] hover:translate-x-1 transition ease-in-out duration-300"></i>
                            </button>
                        </div>

                        <textarea class="w-full bg-[var(--suave-green)] outline-none text-gray-500 overflow-y-auto resize-none rounded-lg p-2" name="descricao" id="descriDemandaTextArea" rows="4" placeholder="Descreva detalhes necessários para a realização da demanda."></textarea>

                        <button id="btnSalvarDescri" class="py-3 bg-[var(--dark-green)] text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center hover:bg-[var(--dark-green)] transition ease-in-out duration-300">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <p>Salvar</p>
                        </button>
                    </div>
                </div>

                <!-- Modal de Conclusão -->
                <div id="divModalConclusao" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[200] hidden">
                    <div id="modalConclusao" class="bg-[var(--white)] w-[90%] sm:w-[420px] pb-8 z-[200] rounded-lg p-5 flex flex-col gap-5 items-center">
                        <i class="fa-regular fa-circle-check text-4xl text-[var(--dark-green)]"></i>

                        <p class="text-[var(--gray2)]">Demanda criada com sucesso!</p>

                        <button id="btnConclusao" class="py-3 bg-[var(--dark-green)] text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center hover:bg-[VAR(--dark-green2)] transition ease-in-out duration-300">
                            <p>Concluído</p>
                        </button>
                    </div>
                </div>

                <!-- Modal de informações do dashboard -->
                <div class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[200] divModalInfs hidden">

                </div>

<!-- Botões de Ação -->
                    <div class="bg-white flex justify-start items-center h-full p-4 w-[90%] mx-auto rounded-lg">
                        <div class="flex items-center justify-center space-x-4">
                            <button id="btnNovaDemanda" class="bg-[var(--dark-green)] py-3 px-4 rounded-lg text-[10px] lg:text-[0.7vw] flex items-center space-x-3 btnScale text-white hover:scale-105 transition ease-in-out duration-150">
                                <i class="bi bi-plus-circle"></i>
                                <p>Nova Demanda</p>
                            </button>

                            <button id="btnGerarRelatorio" class="bg-[var(--white-gray)] py-3 px-4 rounded-lg text-[10px] lg:text-[0.7vw] flex items-center space-x-3 btnScale text-[#121212] border border-[#121212]/10 hover:scale-105 transition ease-in-out duration-150">
                                <i class="fa-solid fa-file-lines"></i>
                                <p>Gerar Relatório</p>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Cards Contadores - Apenas Em aberto e Em andamento -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-[90%] mx-auto mt-8">
                        <div class="dashboard-status-card bg-[var(--white)] rounded-lg flex flex-col w-full items-start space-y-5">
                            <div class="flex justify-between items-center w-full">
                                <div class="flex items-center space-x-3">
                                    <i class="fa-solid fa-inbox text-[var(--white)] bg-[var(--dark-green)] p-3 rounded-lg"></i>
                                    <p class="text-[var(--dark-green)] font-semibold text-sm lg:text-[0.8vw]">Em aberto</p>
                                </div>
                                <p class="quantDisponiveisViewAdm font-semibold text-[var(--dark-green)]"></p>
                            </div>
                        </div>

                        <div class="dashboard-status-card bg-[var(--white)] rounded-lg flex flex-col w-full items-start space-y-5">
                            <div class="flex justify-between items-center w-full">
                                <div class="flex items-center space-x-3">
                                    <i class="fa-solid fa-hourglass text-[var(--white)] bg-[var(--accent-yellow)] p-3 rounded-lg"></i>
                                    <p class="text-[var(--accent-yellow)] font-semibold text-sm lg:text-[0.8vw]">Em Andamento</p>
                                </div>
                                <p class="quantAndamentoViewAdm text-[var(--accent-yellow-light)] font-semibold"></p>
                            </div>
                        </div>
                    </div>

                <!-- Demandas Organizadas por Status - 2 Colunas com Scroll Interno -->
                <div class="dashboard-demandas-area relative grid grid-cols-1 lg:grid-cols-2 gap-6 w-[90%] mx-auto mt-8 items-start">
                    <!-- Mensagem quando não houver demandas no Dashboard -->
                    <div id="msgNenhumaDemandaDashboard" class="absolute inset-0 z-10 hidden items-center justify-center"></div>
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
                <div id="secDemandasAdm" class="secs-adm px-1 lg:px-12 lg:-ml-6 hidden pt-12">

                <div id="shadowSecDemandas" class="bg-black inset-0 opacity-60 z-[190] fixed hidden"></div>

                <div class="w-[90%] flex mx-auto items-center justify-center lg:justify-between mb-6 space-y-2">
                    <div class="flex flex-col space-y-2 text-start lg:text-start">
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

                <div class="w-[90%] mx-auto mt-8">

                    <!-- Parte de Filtramento -->
                    <div class="w-[100%] bg-[var(--white)] rounded-lg grid lg:grid-cols-4 gap-5 p-4">

                        <div class="bg-[var(--white-gray)] h-[35px] w-full rounded-lg border border-[#121212]/10 flex items-center">
                            <i class="fa-solid fa-magnifying-glass absolute ml-3"></i>
                            <input id="filtroTituloDemanda" class="w-full bg-[var(--white-gray)] h-full outline-none indent-9" type="search" placeholder="">
                            <label class="absolute ml-9 transition ease-in-out duration-300" for="filtroTituloDemanda">Buscar demanda</label>
                        </div>

                        <div class="bg-[var(--white-gray)] h-[35px] w-full rounded-lg border border-[#121212]/10 flex items-center">
                            <i class="fa-solid fa-triangle-exclamation pr-1 absolute ml-3"></i>
                            <select id="filtroPrioridadeDemanda" class="w-full bg-[var(--white-gray)] w-full h-full outline-none px-9">
                                <option selected value="">Prioridade</option>
                                <option value="alta">Alta</option>
                                <option value="media">Média</option>
                                <option value="baixa">Baixa</option>
                            </select>
                        </div>

                        <div class="bg-[var(--white-gray)] h-[35px] w-full rounded-lg border border-[#121212]/10 flex items-center">
                            <i class="fa-solid fa-user-tie absolute ml-3"></i>
                            <select id="filtroOrientadorDemanda" class="w-full bg-[var(--white-gray)] w-full h-full outline-none px-9">
                                <option selected value="">Orientador</option>
                            </select>                
                        </div>

                        <div class="bg-[var(--white-gray)] h-[35px] w-full rounded-lg border border-[#121212]/10 flex items-center">
                            <i class="fa-solid fa-circle-info absolute ml-3"></i>
                            <select id="filtroStatusDemanda" class="w-full bg-[var(--white-gray)] h-full outline-none pl-9 pr-3">
                                <option selected value="">Status</option>
                                <option value="finalizada_concluida">Concluídas</option>
                                <option value="finalizada_nao_concluida">Não concluídas</option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Modal de edição da seção Demandas -->
                <div class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[200] divModalInfsSecDemandas hidden">

                </div>
                <div id="divModalValidarJustificativaDemandas" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[210] hidden"></div>
                <div id="divModalInvalidarJustificativaDemandas" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[210] hidden"></div>

                <!-- Repartição -->
                <div class="w-[90%] mx-auto">
                    <!-- Geral -->
                    <div class="geralDemandas demandasSecReparti demandas-grid-admin pt-6">
                                
                    </div>

                    <div class="minhasDemandas demandas-grid-admin mt-6 demandasSecReparti hidden">

                    </div>

                </div>
            </div>

            <!-- Relatórios -->
                <div id="secRelatoriosAdm" class="secs-adm relative px-1 lg:px-12 lg:-ml-6 pt-12 hidden">
                <div class="relatorios-wrapper">
                    <div class="w-[90%] flex mx-auto items-center justify-center lg:justify-between mb-6 space-y-2">
                        <div class="flex flex-col space-y-2 text-center lg:text-start">
                            <p class="text-[var(--dark-green)] font-semibold text-2xl">Relatórios</p>
                            <p>Acompanhe os principais indicadores e gere relatórios das demandas do sistema.</p>
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

                    <!-- <section class="metrics-grid w-[90%] mx-auto">
                        <article class="metric-card metric-card--total">
                            <div class="metric-header">
                                <div class="metric-label">Total de demandas</div>
                                <div class="metric-icon"><i class="fa-solid fa-file-lines"></i></div>
                            </div>
                            <div class="metric-value" data-target="1248">0</div>
                            <div class="metric-footer">100% do período</div>
                        </article>

                        <article class="metric-card metric-card--concluidas">
                            <div class="metric-header">
                                <div class="metric-label">Finalizadas</div>
                                <div class="metric-icon"><i class="fa-solid fa-check"></i></div>
                            </div>
                            <div class="metric-value" data-target="892">0</div>
                            <div class="metric-footer">71,5% do total</div>
                        </article>

                        <article class="metric-card metric-card--andamento">
                            <div class="metric-header">
                                <div class="metric-label">Em andamento</div>
                                <div class="metric-icon"><i class="fa-solid fa-clock"></i></div>
                            </div>
                            <div class="metric-value" data-target="256">0</div>
                            <div class="metric-footer">20,5% do total</div>
                        </article>

                        <article class="metric-card metric-card--pendentes">
                            <div class="metric-header">
                                <div class="metric-label">Pendentes</div>
                                <div class="metric-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            </div>
                            <div class="metric-value" data-target="100">0</div>
                            <div class="metric-footer">8,0% do total</div>
                        </article>
                    </section> -->

                    <section class="relatorios-grid w-[90%] mx-auto">
                        <article class="panel">
                            <div class="panel-header">
                                <h3 class="panel-title">Demandas por situação</h3>
                            </div>

                            <div class="donut-panel">
                                <div class="donut-shell">
                                    <canvas id="donutChart" style="max-width: 260px; max-height: 260px;"></canvas>
                                    <div class="donut-center">
                                        <strong id="donutTotal">0</strong>
                                        <span>Total</span>
                                    </div>
                                </div>

                                <div class="legend" id="donutLegend" aria-label="Legenda do gráfico">
                                    <!-- Preenchido dinamicamente pelo JavaScript -->
                                </div>
                            </div>
                        </article>

                        <article class="panel">
                            <div class="panel-header">
                                <h3 class="panel-title">Demandas por categoria</h3>
                            </div>

                            <div class="progress-list" id="progressList">
                                <!-- Preenchido dinamicamente pelo JavaScript -->
                            </div>
                        </article>
                    </section>

                    <section class="relatorios-categorias">
                        <!-- Card: Relatório Geral -->
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top">
                                <div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--verde"><i class="fa-solid fa-file-lines"></i></div>
                                <span>Geral</span>
                            </div>
                            <p>Visão completa de todas as demandas cadastradas no sistema.</p>
                            <a href="relatorios/relatorio_geral.php" target="_blank" rel="noopener noreferrer" class="categoria-relatorio-btn">Gerar relatório</a>
                        </article>

                        <!-- Card: Demandas em Aberto -->
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top">
                                <div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--verde"><i class="fa-solid fa-inbox"></i></div>
                                <span>Em Aberto</span>
                            </div>
                            <p>Demandas em aberto aguardando atribuição ou processamento.</p>
                            <button type="button" class="categoria-relatorio-btn btnRelatorioSituacao" data-situacao="aberta">Gerar relatório</button>
                        </article>

                        <!-- Card: Demandas em Andamento -->
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top">
                                <div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--amarelo"><i class="fa-solid fa-clock"></i></div>
                                <span>Em Andamento</span>
                            </div>
                            <p>Demandas que estão sendo processadas e acompanhadas.</p>
                            <button type="button" class="categoria-relatorio-btn btnRelatorioSituacao" data-situacao="em_andamento">Gerar relatório</button>
                        </article>

                        <!-- Card: Demandas Finalizadas -->
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top">
                                <div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--verde"><i class="fa-solid fa-circle-check"></i></div>
                                <span>Finalizadas</span>
                            </div>
                            <p>Demandas já concluídas e finalizadas no sistema.</p>
                            <button type="button" class="categoria-relatorio-btn btnRelatorioSituacao" data-situacao="finalizada">Gerar relatório</button>
                        </article>

                        <!-- Card: Relatório Personalizado -->
                        <article class="categoria-relatorio-card">
                            <div class="categoria-relatorio-card__top">
                                <div class="categoria-relatorio-card__icon categoria-relatorio-card__icon--azul"><i class="fa-solid fa-sliders"></i></div>
                                <span>Personalizado</span>
                            </div>
                            <p>Crie um relatório com filtros personalizados de acordo com sua necessidade.</p>
                            <button type="button" id="btnRelatorioPersonalizado" class="categoria-relatorio-btn">Gerar relatório</button>
                        </article>
                    </section>

                    <!-- Modal Shadow para Relatório Personalizado -->
                    <div id="shadowRelatorioPersonalizado" class="bg-black inset-0 opacity-60 z-[190] fixed hidden"></div>

                    <!-- Modal para Relatório Personalizado -->
                    <div id="divModalRelatorioPersonalizado" class="fixed inset-0 bg-black/50 hidden z-[200] flex items-center justify-center p-4">
                        <div id="modalRelatorioPersonalizado" class="bg-[var(--white)] w-[90%] sm:w-[500px] max-h-[85vh] pb-8 z-[200] rounded-xl p-5 flex flex-col gap-5 overflow-y-auto shadow-2xl border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[var(--suave-green)] text-[var(--dark-green)] flex items-center justify-center">
                                        <i class="fa-solid fa-filter text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] uppercase tracking-[0.12em] text-[var(--dark-green)] font-semibold">Relatório</p>
                                        <h3 class="text-xl font-bold text-[var(--gray2)]">Personalizado</h3>
                                    </div>
                                </div>

                                <button type="button" id="btnFecharRelatorioPersonalizado" class="w-10 h-10 rounded-lg text-[var(--dark-green)] hover:bg-[var(--suave-green)] transition ease-in-out duration-200 flex items-center justify-center">
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </button>
                            </div>

                            <form id="formRelatorioPersonalizado" class="flex flex-col w-full gap-5">
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-flag text-[var(--dark-green)] text-sm"></i>
                                        <label class="block text-sm font-semibold">Situação</label>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200">
                                            <input type="checkbox" name="filtroSituacao" value="aberta" class="w-4 h-4 accent-[var(--dark-green)]">
                                            <span class="text-sm text-gray-700">Em Aberto</span>
                                        </label>
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200">
                                            <input type="checkbox" name="filtroSituacao" value="em_andamento" class="w-4 h-4 accent-[var(--dark-green)]">
                                            <span class="text-sm text-gray-700">Em Andamento</span>
                                        </label>
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200">
                                            <input type="checkbox" name="filtroSituacao" value="finalizada" class="w-4 h-4 accent-[var(--dark-green)]">
                                            <span class="text-sm text-gray-700">Finalizada</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-triangle-exclamation text-[var(--dark-green)] text-sm"></i>
                                        <label class="block text-sm font-semibold">Prioridade</label>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200">
                                            <input type="checkbox" name="filtroPrioridade" value="alta" class="w-4 h-4 accent-[var(--dark-green)]">
                                            <span class="text-sm text-gray-700">Alta</span>
                                        </label>
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200">
                                            <input type="checkbox" name="filtroPrioridade" value="media" class="w-4 h-4 accent-[var(--dark-green)]">
                                            <span class="text-sm text-gray-700">Média</span>
                                        </label>
                                        <label class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition border border-transparent hover:border-gray-200">
                                            <input type="checkbox" name="filtroPrioridade" value="baixa" class="w-4 h-4 accent-[var(--dark-green)]">
                                            <span class="text-sm text-gray-700">Baixa</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-diagram-project text-[var(--dark-green)] text-sm"></i>
                                        <label for="filtroProjetoRelatorio" class="block text-sm font-semibold">Projeto</label>
                                    </div>
                                    <select id="filtroProjetoRelatorio" name="filtroProjeto" class="w-full py-2.5 px-3 rounded-lg bg-[var(--suave-green)] border border-transparent text-gray-600 outline-none focus:border-[var(--dark-green)]">
                                        <option value="">Todos os projetos</option>
                                    </select>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-sitemap text-[var(--dark-green)] text-sm"></i>
                                        <label for="filtroCategoria" class="block text-sm font-semibold">Categoria</label>
                                    </div>
                                    <select id="filtroCategoria" name="filtroCategoria" disabled class="w-full py-2.5 px-3 rounded-lg bg-[var(--suave-green)] border border-transparent text-gray-600 outline-none focus:border-[var(--dark-green)] disabled:opacity-60">
                                        <option value="">Selecione um projeto primeiro</option>
                                    </select>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-calendar text-[var(--dark-green)] text-sm"></i>
                                        <label class="block text-sm font-semibold">Período de criação</label>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <span class="text-[10px] uppercase tracking-[0.12em] font-medium text-gray-500">De</span>
                                            <input type="date" name="filtroDataInicio" class="w-full py-2.5 px-3 rounded-lg bg-[var(--suave-green)] border border-transparent text-gray-600 outline-none focus:border-[var(--dark-green)]">
                                        </div>
                                        <div class="space-y-1.5">
                                            <span class="text-[10px] uppercase tracking-[0.12em] font-medium text-gray-500">Até</span>
                                            <input type="date" name="filtroDataFim" class="w-full py-2.5 px-3 rounded-lg bg-[var(--suave-green)] border border-transparent text-gray-600 outline-none focus:border-[var(--dark-green)]">
                                        </div>
                                    </div>

                                    <p class="text-[11px] leading-relaxed text-gray-500">Use este intervalo para filtrar demandas criadas dentro do período desejado.</p>
                                </div>

                                <div class="flex gap-3 pt-2 w-full">
                                    <button type="submit" id="btnGerarRelatorioPersonalizado" class="flex-1 bg-[var(--dark-green)] hover:bg-[var(--dark-green2)] text-white font-semibold py-3 px-4 rounded-lg transition ease-in-out duration-300">
                                        Gerar Relatório
                                    </button>
                                    <button type="button" id="btnLimparFiltros" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition ease-in-out duration-300">
                                        Limpar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


                <!-- Gerenciar Usuarios -->
                <div id="secProjetosAdm" class="secs-adm projetos-admin-section relative px-1 lg:px-12 lg:-ml-6 pt-12 hidden">
                    <div class="w-[90%] mx-auto">
                        <div class="w-full flex items-center justify-between gap-5 mb-6">
                            <div class="flex flex-col space-y-2 text-center lg:text-start">
                                <p class="text-[var(--dark-green)] font-semibold text-2xl">Projetos</p>
                                <p>Crie e acompanhe os projetos do sistema para organizar demandas por área.</p>
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

                        <div class="grid grid-cols-1 xl:grid-cols-[0.9fr_1.6fr] items-start gap-6 mb-6">
                            <div class="self-start h-fit bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <i class="fa-solid fa-folder-plus text-[var(--dark-green)]"></i>
                                    <h3 class="text-lg font-bold text-[var(--dark-green)]">Novo projeto</h3>
                                </div>
                                <?php if ($podeCriarProjeto): ?>
                                <form id="formProjeto" class="space-y-4">
                                    <div>
                                        <label for="nomeProjeto" class="block text-sm font-semibold text-gray-700 mb-2">Nome</label>
                                        <input id="nomeProjeto" type="text" maxlength="100" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 bg-[var(--suave-green)] outline-none focus:border-[var(--dark-green)]" placeholder="Ex.: Desenvolvimento, Suporte...">
                                    </div>
                                    <div>
                                        <label for="descricaoProjeto" class="block text-sm font-semibold text-gray-700 mb-2">Descrição</label>
                                        <textarea id="descricaoProjeto" rows="4" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 bg-[var(--suave-green)] outline-none focus:border-[var(--dark-green)]" placeholder="Descreva o objetivo deste projeto."></textarea>
                                    </div>
                                    <button type="submit" class="w-full bg-[var(--dark-green)] hover:bg-[var(--dark-green2)] text-white font-semibold py-2.5 px-4 rounded-lg transition flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-plus mr-2"></i>Criar projeto
                                    </button>
                                </form>
                                <?php else: ?>
                                <div class="rounded-lg border border-dashed border-gray-200 bg-[var(--suave-green)] p-4 text-sm text-gray-600">
                                    Você não possui permissão para criar projetos.
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-list text-[var(--dark-green)]"></i>
                                        <h3 class="text-lg font-bold text-[var(--dark-green)]">Projetos cadastrados</h3>
                                    </div>
                                    <span id="contadorProjetos" class="text-xs font-semibold bg-[var(--suave-green)] text-[var(--dark-green)] rounded-full px-2 py-1">0</span>
                                </div>
                                <div id="listaProjetos" class="space-y-3"></div>
                                <div id="listaProjetosAdmin" class="space-y-3 hidden"></div>
                                <div id="listaProjetosVinculadosAdmin" class="mt-4 space-y-3 hidden"></div>

                                <div id="painelSolicitacoesProjeto" class="mt-6 border-t border-gray-200 pt-5 hidden">
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-user-clock text-[var(--dark-green)]"></i>
                                            <h3 class="text-lg font-bold text-[var(--dark-green)]">Solicitações de participação</h3>
                                        </div>
                                        <span id="contadorSolicitacoesProjeto" class="text-xs font-semibold bg-[var(--suave-green)] text-[var(--dark-green)] rounded-full px-2 py-1">0</span>
                                    </div>
                                    <div id="listaSolicitacoesProjeto" class="space-y-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="secUsuariosAdm" class="secs-adm usuarios-admin-section relative px-1 lg:px-12 lg:-ml-6 pt-12 hidden">

                    <div id="shadowSecUsuarios" class="bg-black inset-0 opacity-60 z-[190] fixed hidden"></div>

                    <div class="w-[90%] mx-auto">
                        <div class="usuarios-admin-header w-full flex items-center justify-between gap-5 mb-6">
                            <div class="flex flex-col space-y-2 text-center lg:text-start">
                                <p class="text-[var(--dark-green)] font-semibold text-2xl">Gerenciar Usuários</p>
                                <p>Cadastre, pesquise, edite, visualize e gerencie acessos do sistema.</p>
                            </div>
                            <div class="flex items-center gap-5 w-full sm:w-auto lg:translate-y-1">
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
                        </div>
                            <div class="bg-[#F9FCF8] rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6 p-5 border-l-4 border-l-[#F9A304]">
                                <div class="p-3 sm:p-5 flex items-center justify-between gap-3 sm:gap-4 border-b border-gray-100">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-user-plus text-[#f9a304]"></i>
                                            <h3 class="text-base sm:text-lg font-bold text-[#121212]">Solicitações de Cadastro</h3>
                                            <span id="contadorSolicitacoesCadastro" class="text-[10px] sm:text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full px-2 sm:px-2.5 py-1 whitespace-nowrap">0 pendente(s)</span>
                                        </div>
                                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Analise e aprove novos usuários antes do primeiro acesso.</p>
                                    </div>
                                </div>
                                <div class="overflow-auto">
                                    <table class="usuarios-admin-table w-full text-sm text-left rounded-lg overflow-hidden">
                                        <thead><tr class="bg-[#FEF6E9] border-b border-gray-100">
                                            <th class="px-4 py-3 text-xs uppercase tracking-[0.08em] text-[#121212]">Nome</th>
                                            <th class="px-4 py-3 text-xs uppercase tracking-[0.08em] text-[#121212]">E-mail</th>
                                            <th class="px-4 py-3 text-xs uppercase tracking-[0.08em] text-[#121212]">Matrícula</th>
                                            <th class="px-4 py-3 text-xs uppercase tracking-[0.08em] text-[#121212]">Perfil</th>
                                            <th class="px-4 py-3 text-xs uppercase tracking-[0.08em] text-[#121212]">Projeto</th>
                                            <th class="px-4 py-3 text-xs uppercase tracking-[0.08em] text-[#121212]">Solicitado em</th>
                                            <th class="px-4 py-3 text-center text-xs uppercase tracking-[0.08em] text-[#121212]">Ações</th>
                                        </tr></thead>
                                        <tbody id="solicitacoesCadastroTableBody"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="validacoesJustificativas" class="hidden bg-[#F9FCF8] rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6 p-5 border-l-4 border-l-[#F9A304]">
                                <div class="p-3 sm:p-5 flex items-center justify-between gap-3 sm:gap-4 border-b border-gray-100">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-clipboard-check text-[#f9a304]"></i>
                                            <h3 class="text-base sm:text-lg font-bold text-[#121212]">Validação de justificativas</h3>
                                            <span id="contadorValidacoesJustificativas" class="text-[10px] sm:text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full px-2 sm:px-2.5 py-1 whitespace-nowrap">0 pendente(s)</span>
                                        </div>
                                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Analise as justificativas das demandas finalizadas como não concluídas.</p>
                                    </div>
                                    <i class="fa-solid fa-scale-balanced text-[var(--dark-green)]" aria-hidden="true"></i>
                                </div>
                                <div class="p-5">
                                    <div id="listaValidacoesJustificativas" class="grid grid-cols-1 lg:grid-cols-2 gap-5"></div>
                                </div>
                            </div>

                            <div id="modalDetalhesValidacao" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[200] hidden"></div>
                            <div id="divModalValidarJustificativaUsuarios" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[210] hidden"></div>
                            <div id="divModalInvalidarJustificativaUsuarios" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[210] hidden"></div>
                            <div id="divModalVerUsuario" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[215] hidden"></div>
                            <div id="divModalCadastrarUsuario" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[215] hidden"></div>
                            <div id="divModalEditarUsuario" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[215] hidden"></div>
                            <div id="divModalStatusUsuario" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[215] hidden"></div>
                            <div id="divModalAprovarCadastro" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[215] hidden"></div>
                            <div id="divModalRecusarCadastro" class="h-full top-1/2 left-0 -translate-y-1/2 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] flex items-center justify-center fixed z-[215] hidden"></div>

                        <div class="bg-[#F9FCF8] rounded-xl border border-gray-100 shadow-sm overflow-auto p-5 border-l-4 border-l-[var(--dark-green)]">
                            <div class="p-5 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 border-b border-gray-100">
                                <div class="flex items-center gap-2 shrink-0">
                                    <i class="fa-solid fa-users text-[var(--dark-green)]"></i>
                                    <h3 class="text-lg font-bold text-[var(--dark-green)]">Usuários cadastrados</h3>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-end gap-3 w-full xl:w-auto">
                                    <div class="w-full sm:min-w-64">
                                        <input id="usuarioPesquisa" type="text" placeholder="Buscar por nome, e-mail ou matrícula" aria-label="Buscar usuário por nome, e-mail ou matrícula" class="w-full px-3 py-2.5 rounded-lg bg-[var(--suave-green)] border border-transparent outline-none focus:border-[var(--dark-green)]">
                                    </div>
                                    <div class="w-full sm:w-40">
                                        <select id="usuarioFiltroStatus" aria-label="Filtrar usuários por status" class="w-full px-3 py-2.5 rounded-lg bg-[var(--suave-green)] border border-transparent outline-none focus:border-[var(--dark-green)]">
                                            <option value="">Todos</option>
                                            <option value="1">Ativos</option>
                                            <option value="0">Inativos</option>
                                        </select>
                                    </div>
                                    <button id="btnLimparUsuarios" type="button" class="h-11 shrink-0 bg-[var(--gray-crede)] text-[var(--dark-green)] px-4 rounded-lg font-semibold hover:bg-[var(--suave-green)] transition" title="Limpar filtros" aria-label="Limpar filtros"><i class="fa-solid fa-rotate-left"></i></button>
                                </div>
                                <span id="totalUsuariosAdmin" class="text-xs font-semibold text-[var(--dark-green)] xl:ml-auto"></span>
                            </div>
                            <table class="usuarios-admin-table w-full text-sm text-left rounded-lg overflow-hidden" id="tabelaUsuarios">
                                <thead>
                                    <tr class="bg-[var(--dark-green)] border-b border-gray-100">
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-[0.08em] font-semibold text-white">Nome</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-[0.08em] font-semibold text-white">Matrícula</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-[0.08em] font-semibold text-white">Email</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-[0.08em] font-semibold text-white">Perfil / Projetos</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-[0.08em] font-semibold text-white">Status</th>
                                        <th class="px-4 py-3 text-center text-xs uppercase tracking-[0.08em] font-semibold text-white">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="usuariosTableBody"></tbody>
                            </table>
                            <div class="usuarios-admin-pagination flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 text-xs text-gray-500">
                                <span id="resumoUsuariosAdmin">Exibindo 0 usuários</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="btnPaginaUsuariosAnterior" class="w-8 h-8 rounded-md border border-gray-200 text-gray-400" aria-label="Página anterior"><i class="fa-solid fa-chevron-left"></i></button>
                                    <span id="paginaUsuariosAtual" class="min-w-8 h-8 px-1 rounded-md bg-[var(--dark-green)] text-white flex items-center justify-center font-semibold">1</span>
                                    <button type="button" id="btnPaginaUsuariosProxima" class="w-8 h-8 rounded-md border border-gray-200 text-gray-400" aria-label="Próxima página"><i class="fa-solid fa-chevron-right"></i></button>
                                </div>
                                <label class="flex items-center gap-2">Itens por página:
                                    <select id="usuariosItensPorPagina" aria-label="Quantidade de usuários por página" class="border border-gray-200 rounded-md px-2 py-1.5 bg-white">
                                        <option value="5" selected>5</option>
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="modalUsuario" class="fixed inset-0 bg-black/50 hidden z-[220] flex items-center justify-center p-4">
                        <div class="bg-white rounded-xl shadow-xl w-[90%] sm:w-[500px] p-6">
                            <div class="flex justify-between items-center mb-5">
                                <h3 id="modalUsuarioTitulo" class="text-xl font-bold text-[var(--dark-green)]">Cadastrar usuário</h3>
                                <button id="btnFecharModalUsuario" class="text-gray-500 hover:text-gray-800 text-2xl">×</button>
                            </div>

                            <form id="formUsuario" class="space-y-4">
                                <input type="hidden" id="usuarioId" value="">

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nome</label>
                                    <input id="usuarioNome" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none" required>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                        <input id="usuarioEmail" type="email" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Matrícula</label>
                                        <input id="usuarioMatricula" type="text" maxlength="7" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none" required>
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Perfil de acesso</label>
                                        <select id="usuarioTipo" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none">
                                            <option value="administrador">Administrador</option>
                                            <option value="master">Master</option>
                                            <option value="usuario">Usuário</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Série</label>
                                        <select id="usuarioSerie" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none">
                                            <option value="">Selecione</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="painelSenha">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Senha</label>
                                    <input id="usuarioSenha" type="password" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none">
                                    <small id="usuarioSenhaHelp" class="text-gray-500">Senha obrigatória para cadastro.</small>
                                </div>

                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" id="btnCancelarModalUsuario" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md font-semibold">Cancelar</button>
                                    <button type="submit" class="bg-[var(--dark-green)] text-white px-4 py-2 rounded-md font-semibold">Salvar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="modalDetalhesUsuario" class="fixed inset-0 bg-black/50 hidden z-[230] flex items-center justify-center p-4">
                        <div class="bg-white rounded-xl shadow-xl w-[90%] sm:w-[560px] p-6">
                            <div class="flex justify-between items-center mb-5">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.2em] text-gray-500">Usuário</p>
                                    <h3 id="detalheUsuarioTitulo" class="text-2xl font-bold text-[var(--dark-green)]">Perfil do usuário</h3>
                                </div>
                                <button id="btnFecharDetalhesUsuario" class="text-gray-500 hover:text-gray-800 text-2xl">×</button>
                            </div>

                            <div id="detalheUsuarioConteudo" class="space-y-5"></div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" id="btnCancelarDetalhesUsuario" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md font-semibold">Fechar</button>
                                <button type="button" id="btnEditarUsuarioDetalhe" class="bg-[var(--dark-green)] text-white px-4 py-2 rounded-md font-semibold hover:bg-[var(--dark-green2)]">Editar usuário</button>
                            </div>
                        </div>
                    </div>

                    <div id="modalStatusUsuario" class="fixed inset-0 bg-black/50 hidden z-[240] flex items-center justify-center p-4">
                        <div class="bg-white w-[90%] sm:w-[420px] rounded-2xl shadow-2xl p-6 animate-[modalEffect_1s_cubic-bezier(0.22,1,0.36,1)]">
                            <div class="flex items-start gap-4">
                                <div id="modalStatusIcon" class="w-12 h-12 shrink-0 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                                    <i class="fa-solid fa-user-slash text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.14em] text-gray-500">Confirmação</p>
                                    <h3 id="modalStatusTitulo" class="text-xl font-bold text-red-600">Desativar usuário?</h3>
                                    <p id="modalStatusMensagem" class="mt-2 text-sm leading-relaxed text-gray-600"></p>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" id="btnCancelarStatusUsuario" class="px-4 py-2.5 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition">Cancelar</button>
                                <button type="button" id="btnConfirmarStatusUsuario" class="px-4 py-2.5 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">Desativar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Perfil -->
                <div id="secPerfilAdm" class="secs-adm relative px-1 lg:px-12 lg:-ml-6 hidden overflow-y-auto pt-12">
                    <div class="w-[90%] mx-auto">
                        <div class="w-full flex items-center justify-center lg:justify-between mb-6 space-y-2">
                            <div class="flex flex-col space-y-2 text-center lg:text-start">
                                <p class="text-[var(--dark-green)] font-semibold text-2xl">Perfil</p>
                                <p class="">Consulte suas informações no sistema.</p>
                            </div>

                            <div class="hidden lg:block">
                                <div class="flex items-center space-x-2">
                                <div class="iconAluno w-12 h-12 rounded-full bg-[var(--dark-green)] flex items-center justify-center text-white font-semibold">
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
                                <div class="iconAluno perfil-avatar-ring w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-full bg-[var(--perfil-card-bg-strong)] flex items-center justify-center text-white text-3xl font-semibold">
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
                                <button type="button" id="btnAlterarFoto" class="bg-[var(--perfil-button-bg)] text-[var(--perfil-button-text)] text-sm font-semibold py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 hover:bg-[var(--perfil-button-hover)] transition ease-in-out duration-300">
                                    <i class="fa-solid fa-camera"></i>
                                    <span>Alterar foto</span>
                                </button>
                                <button type="button" id="btnRemoverFoto" class="border border-red-200 bg-red-50 text-red-700 text-sm font-semibold py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 hover:bg-red-100 transition ease-in-out duration-300" <?= $fotoUsuario ? '' : 'disabled' ?>>
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Remover foto</span>
                                </button>
                            </div>
                            <input type="file" id="inputFotoPerfil" accept="image/*" class="hidden">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                                <div class="flex items-center gap-3 mb-4">
                                    <i class="bi bi-envelope-fill text-[var(--dark-green)] bg-[var(--suave-green)] h-11 w-11 flex items-center justify-center rounded-lg"></i>
                                    <p class="text-xs uppercase tracking-[0.12em] text-gray-500 font-semibold">E-mail</p>
                                </div>
                                <p class="emailValue text-gray-800 font-medium break-words"><?= $emailUsuario ?></p>
                            </div>

                            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                                <div class="flex items-center gap-3 mb-4">
                                    <i class="fa-solid fa-sitemap text-[var(--dark-green)] bg-[var(--suave-green)] h-11 w-11 flex items-center justify-center rounded-lg"></i>
                                    <p class="text-xs uppercase tracking-[0.12em] text-gray-500 font-semibold">Perfil</p>
                                </div>
                                <p class="setorValue text-gray-800 font-medium"><?= htmlspecialchars($perfilUsuarioExibicao, ENT_QUOTES, 'UTF-8') ?></p>
                            </div>

                        </div>

                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm mt-6 overflow-hidden">
                            <div class="p-5 flex items-center gap-3 border-b border-gray-100">
                                <i class="fa-solid fa-ranking-star text-[var(--dark-green)] bg-[var(--suave-green)] h-11 w-11 flex items-center justify-center rounded-lg"></i>
                                <div class="flex-1">
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
                                                Perfil
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
                                                        <div class="w-10 h-10 shrink-0 rounded-full bg-[var(--dark-green)] text-white flex items-center justify-center font-semibold text- overflow-hidden">
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
                        <div class="bg-white rounded-xl shadow-2xl w-[90%] sm:w-[500px] p-5">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.14em] text-gray-500">Imagem do perfil</p>
                                    <h3 class="text-xl font-bold text-[var(--dark-green)]">Ajustar foto</h3>
                                </div>
                                <button type="button" id="btnFecharCorteFoto" class="w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Fechar recorte">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <div class="h-72 sm:h-96 bg-gray-100 rounded-lg overflow-hidden">
                                <img id="imagemParaCorte" class="block max-w-full" alt="Pré-visualização da foto">
                            </div>
                            <div class="flex justify-end gap-3 mt-5">
                                <button type="button" id="btnCancelarCorteFoto" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold">Cancelar</button>
                                <button type="button" id="btnSalvarCorteFoto" class="bg-[var(--dark-green)] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[var(--dark-green2)]">Salvar foto</button>
                            </div>
                        </div>
                    </div>
                    <div id="modalConfirmacaoFoto" class="fixed inset-0 bg-black/60 hidden z-[265] items-center justify-center p-4">
                        <div class="bg-white rounded-xl shadow-2xl w-[90%] sm:w-[420px] p-5">
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
                    <div id="modalAvisoSistema" class="fixed top-4 left-0 hidden z-[260] w-screen lg:left-64 lg:w-[calc(100vw-16rem)] pointer-events-none" aria-live="polite">
                        <div class="w-full flex justify-center pointer-events-none">
                            <div class="bg-white rounded-xl shadow-lg w-[calc(100%-2rem)] max-w-sm p-5 flex justify-center flex-col items-center pointer-events-auto">
                                <div class="flex items-center justify-center gap-3">
                                    <i id="iconeAvisoSistema" class="fa-solid fa-circle-info text-[var(--dark-green)] text-xl shrink-0"></i>
                                    <p id="textoAvisoSistema" class="text-sm leading-relaxed text-[var(--gray2)] text-center"></p>
                                </div>
                                <div class="h-1 bg-gray-100 rounded-full overflow-hidden mt-5 w-full">
                                    <div id="barraAvisoSistema" class="h-full bg-[var(--dark-green)]"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- FIM PRINCIPAL ADMINISTRADORES -->
        </main>
    </div>

    <script>
        let temporizadorAvisoSistema = null;
        const modalAvisoSistema = document.getElementById('modalAvisoSistema');
        if (modalAvisoSistema) {
            document.body.appendChild(modalAvisoSistema);
        }

        function mostrarAvisoSistema(mensagem, tipo = 'info') {
            const modal = document.getElementById('modalAvisoSistema');
            const texto = document.getElementById('textoAvisoSistema');
            const icone = document.getElementById('iconeAvisoSistema');
            const barra = document.getElementById('barraAvisoSistema');
            if (!modal || !texto || !icone || !barra) return;

            clearTimeout(temporizadorAvisoSistema);
            texto.textContent = mensagem;
            icone.className = tipo === 'error'
                ? 'fa-solid fa-circle-xmark text-red-600 text-2xl'
                : tipo === 'warning'
                    ? 'fa-solid fa-triangle-exclamation text-yellow-500 text-2xl'
                    : 'fa-solid fa-circle-info text-[var(--dark-green)] text-2xl';
            barra.className = tipo === 'error'
                ? 'h-full bg-red-600 aviso-ponto-progresso'
                : tipo === 'warning'
                    ? 'h-full bg-yellow-500 aviso-ponto-progresso'
                    : 'h-full bg-[var(--dark-green)] aviso-ponto-progresso';
            barra.classList.remove('aviso-ponto-progresso');
            void barra.offsetWidth;
            barra.classList.add('aviso-ponto-progresso');
            modal.classList.remove('hidden');
            temporizadorAvisoSistema = setTimeout(() => {
                modal.classList.add('hidden');
            }, 2500);
        }

        function confirmarAcaoSistema(mensagem, escopo = 'tela') {
            return new Promise((resolve) => {
                const modal = document.createElement('div');
                modal.className = escopo === 'demanda' || escopo === 'projeto'
                        ? 'modal-overlay-animated fixed inset-y-0 left-0 w-screen lg:left-64 lg:w-[calc(100vw-16rem)] bg-black/60 z-[300] flex items-center justify-center p-4'
                        : 'modal-overlay-animated fixed inset-0 bg-black/60 z-[300] flex items-center justify-center p-4';
                modal.innerHTML = `
                    <div class="modal-content-animated bg-white rounded-xl shadow-2xl w-[90%] sm:w-[420px] p-5">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-question text-[var(--dark-green)] text-xl"></i>
                            <h2 class="textoConfirmacaoSistema text-lg font-semibold text-[var(--gray2)]"></h2>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" class="btnCancelarConfirmacaoSistema px-4 py-2 rounded-lg bg-gray-200 font-semibold hover:bg-gray-300">Cancelar</button>
                            <button type="button" class="btnConfirmarAcaoSistema px-4 py-2 rounded-lg bg-[var(--dark-green)] text-white font-semibold hover:bg-[var(--dark-green2)]">Confirmar</button>
                        </div>
                    </div>`;
                document.body.appendChild(modal);
                const texto = modal.querySelector('.textoConfirmacaoSistema');
                const cancelar = modal.querySelector('.btnCancelarConfirmacaoSistema');
                const confirmar = modal.querySelector('.btnConfirmarAcaoSistema');
                texto.textContent = mensagem;
                modal.classList.remove('hidden');
                const fechar = (resultado) => {
                    modal.remove();
                    resolve(resultado);
                };
                cancelar.onclick = () => fechar(false);
                confirmar.onclick = () => fechar(true);
            });
        }

        window.notify = window.notify || {};
        window.notify.success = window.notify.success || ((mensagem) => mostrarAvisoSistema(mensagem, 'success'));
        window.notify.error = window.notify.error || ((mensagem) => mostrarAvisoSistema(mensagem, 'error'));
        window.notify.warning = window.notify.warning || ((mensagem) => mostrarAvisoSistema(mensagem, 'warning'));
        window.notify.info = window.notify.info || ((mensagem) => mostrarAvisoSistema(mensagem, 'info'));
        const notify = window.notify;

        window.addEventListener("pageshow", () => {
            const form = document.getElementById("formDemanda");

            form.reset();

            form.querySelectorAll("input, select, textarea").forEach(el => {
                if (el.tagName === "SELECT") {
                    el.selectedIndex = 0;
                } else if (el.type !== "radio" && el.type !== "checkbox") {
                    el.value = "";
                }
            });                        

            document.getElementById("descriDemandaTextArea").value = "";
        });

        const openMenuBtn = document.getElementById("openMenuBtn");
        const menuAdmMobile = document.getElementById("menuAdmMobile");
        const closeMenuBtn = document.getElementById("closeMenuBtn");
        const sidebarGroupButtons = document.querySelectorAll(".sidebar-group-toggle");
        const sidebarGroupsStorageKey = "system-suporte-sidebar-groups-adm";
        const sidebarElement = document.getElementById("sidebar");
        const sidebarCollapseToggle = document.querySelector(".sidebar-collapse-toggle");
        const desktopSidebarQuery = window.matchMedia("(min-width: 1024px)");
        const sidebarCollapseStorageKey = "system-suporte-sidebar-collapsed-adm";
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

        openMenuBtn.onclick = () => {
            menuAdmMobile.classList.remove("hidden");
            openMenuBtn.setAttribute("aria-expanded", "true");
            setTimeout(() => {
                menuAdmMobile.classList.remove("translate-x-[-100%]");
            }, 10);
        };

        closeMenuBtn.onclick = () => {
            menuAdmMobile.classList.add("translate-x-[-100%]");
            openMenuBtn.setAttribute("aria-expanded", "false");
            setTimeout(() => {
                menuAdmMobile.classList.add("hidden");
            }, 300);
        }

        const btnOptionsAluno = document.querySelectorAll(".option-nav-alunos");
        const btnOptionsAdm = document.querySelectorAll(".option-nav-adm");
        const btnOptionSair = document.querySelector(".option-nav-sair");

        const iconsNavAlunos = document.querySelectorAll(".icon-nav-alunos");
        const iconsNavAdm = document.querySelectorAll(".icon-nav-adm");
        const iconNavSair = document.querySelector(".icon-nav-sair");

        const secs_aluno = document.querySelectorAll(".secs-aluno");
        const secs_adm = document.querySelectorAll(".secs-adm");

        const menuAlunos = document.getElementById("menuAlunos");
        const menuAdm = document.getElementById("menuAdm");

        const mainAlunos = document.getElementById("mainAlunos");
        const mainAdm = document.getElementById("mainAdm");

        let currentIndex = 0;

        const secoesAdmPorNome = {
            demandas: "secDashboardAdm",
            historico: "secDemandasAdm",
            relatorios: "secRelatoriosAdm",
            projetos: "secProjetosAdm",
            "gerenciar usuarios": "secUsuariosAdm",
            perfil: "secPerfilAdm"
        };

        function fecharModaisAoTrocarSecao() {
            document.querySelectorAll('.modal-overlay-animated').forEach((modal) => {
                if (modal.dataset.preservarAoTrocar === 'true' || modal.id === 'modalAvisoSistema') return;
                modal.remove();
            });
            document.querySelectorAll('[id^="divModal"], .divModalInfs, .divModalInfsSecDemandas').forEach((modal) => {
                if (modal.id === 'modalAvisoSistema' || modal.id === 'divModalAviso') return;
                modal.style.removeProperty('display');
                modal.style.removeProperty('z-index');
                modal.style.removeProperty('visibility');
                modal.style.removeProperty('opacity');
                modal.classList.add('hidden');
                if (modal.classList.contains('divModalInfs') || modal.classList.contains('divModalInfsSecDemandas')) {
                    modal.innerHTML = '';
                }
            });
            document.querySelectorAll('[id^="shadow"], .shadowDashBoard').forEach((shadow) => shadow.classList.add('hidden'));
            document.body.classList.remove('no-scroll');
            mainAdm?.classList.remove('no-scroll', 'h-screen');
        }

        btnOptionsAdm.forEach((btn) => {
            btn.onmouseenter = () => {
                btn.closest("li")?.querySelector(".icon-nav-adm")?.classList.add("icon-hover");
            };

            btn.onmouseleave = () => {
                btn.closest("li")?.querySelector(".icon-nav-adm")?.classList.remove("icon-hover");
            };

            btn.onclick = () => {
                const nomeSecao = String(btn.dataset.secao || '').trim().toLowerCase();
                const idSecao = secoesAdmPorNome[nomeSecao];
                const secaoAlvo = idSecao ? document.getElementById(idSecao) : null;

                if (!secaoAlvo) return;

                fecharModaisAoTrocarSecao();

                btnOptionsAdm.forEach((opcao) => {
                    const ativa = (opcao.dataset.secao || '').trim().toLowerCase() === nomeSecao;
                    opcao.classList.toggle("btnSelect", ativa);
                    opcao.classList.toggle("text-gray-200", !ativa);
                    opcao.classList.toggle("effect-bar-select", ativa);
                    opcao.closest("li")?.querySelector(".icon-nav-adm")?.classList.toggle("icon-select", ativa);
                });

                secs_adm.forEach((sec) => {
                    sec.classList.add("hidden");
                });

                secaoAlvo.classList.remove("hidden");

                if (idSecao === "secDashboardAdm") {
                    requestAnimationFrame(() => requestAnimationFrame(() => ajustarAlturaColunasDashboard()));
                }

                if (idSecao === "secRelatoriosAdm") {
                    setTimeout(() => carregarDadosGraficos(), 100);
                }

                if (idSecao === "secProjetosAdm") {
                    setTimeout(() => {
                        carregarProjetos();
                        if (usuario === 'master') {
                            carregarSolicitacoesProjeto();
                        }
                    }, 150);
                }

                if (idSecao === "secUsuariosAdm") {
                    setTimeout(() => {
                        carregarFiltroProjetosUsuarios();
                        carregarUsuarios();
                        carregarSolicitacoesCadastro();
                    }, 150);
                }

                if (menuAdmMobile && window.innerWidth < 1024) {
                    menuAdmMobile.classList.add("translate-x-[-100%]");
                    setTimeout(() => menuAdmMobile.classList.add("hidden"), 300);
                    openMenuBtn.setAttribute("aria-expanded", "false");
                }

                currentIndex = Array.from(secs_adm).indexOf(secaoAlvo);
                console.log(currentIndex);
            };
        });

        iconsNavAdm.forEach((icon) => {
            icon.classList.add('cursor-pointer');
            icon.addEventListener('mouseenter', () => {
                icon.classList.add('icon-hover');
                const botao = icon.closest('li')?.querySelector('.option-nav-adm');
                botao?.classList.add('icon-hover', 'effect-bar-select');
            });
            icon.addEventListener('mouseleave', () => {
                icon.classList.remove('icon-hover');
                const botao = icon.closest('li')?.querySelector('.option-nav-adm');
                botao?.classList.remove('icon-hover');
                if (!botao?.classList.contains('btnSelect')) {
                    botao?.classList.remove('effect-bar-select');
                }
            });
            icon.addEventListener('click', () => {
                icon.closest('li')?.querySelector('.option-nav-adm')?.click();
            });
        });

        // Nav - sair
        btnOptionSair.onmouseenter = () => {
            iconNavSair.classList.add("icon-hover");
        };
        btnOptionSair.onmouseleave = () => {
            iconNavSair.classList.remove("icon-hover");
        };


        const btnDemandasSec = document.querySelectorAll(".btnDemandasSec");
        const demandasSecReparti = document.querySelectorAll(".demandasSecReparti");

        btnDemandasSec.forEach((btn, index) => {
            btn.onclick = () => {
                btnDemandasSec.forEach((btn) => {
                    btn.classList.remove("btnDemandasSec_select");
                });

                btnDemandasSec[index].classList.add("btnDemandasSec_select");

                demandasSecReparti.forEach((secDemanda) => {
                    secDemanda.classList.add("hidden");
                });

                demandasSecReparti[index].classList.remove("hidden");
            };
        });

        // Contadores
        let quantDisponiveis = 0;
        let quantAndamento = 0;
        let quantFinalizadas = 0;


        const quantDisponiveisViewAdm = document.querySelector(".quantDisponiveisViewAdm");
        const quantAndamentoViewAdm = document.querySelector(".quantAndamentoViewAdm");
        const quantFinalizadasViewAdm = document.querySelector(".quantFinalizadasViewAdm");


        if (quantDisponiveisViewAdm) quantDisponiveisViewAdm.innerHTML = quantDisponiveis;
        if (quantAndamentoViewAdm) quantAndamentoViewAdm.innerHTML = quantAndamento;
        if (quantFinalizadasViewAdm) quantFinalizadasViewAdm.innerHTML = quantFinalizadas;

        // Button NOVA Demanda

        const shadowDashBoard = document.querySelectorAll(".shadowDashBoard");
        const shadowSecDemandas = document.getElementById("shadowSecDemandas");
        const shadowDashBoardADM = document.getElementById("shadowDashBoardADM");
        const shadowSecUsuarios = document.getElementById("shadowSecUsuarios");   

        const divModalCriar = document.getElementById("divModalCriar");
        const divModalExclusao = document.getElementById("divModalExclusao");
        const divModalAssumir = document.getElementById("divModalAssumir");
        const divModalDescri = document.getElementById("divModalDescri");
        const divModalInfs = document.querySelectorAll(".divModalInfs");
        const divModalInfsSecDemandas = document.querySelectorAll(".divModalInfsSecDemandas");
        const divModalConclusao = document.getElementById("divModalConclusao");

        const btnNovaDemanda = document.querySelectorAll("#btnNovaDemanda, #btnNovaDemandaFooter");
        const btnGerarRelatorio = document.querySelectorAll("#btnGerarRelatorio, #btnGerarRelatorioFooter");
        const btnFecharModalCriar = document.getElementById("btnFecharModalCriar");
        const btnFecharModalDescri = document.getElementById("btnFecharModalDescri");
        const btnAddDescri = document.getElementById("btnAddDescri");
        const btnSalvarDescri = document.getElementById("btnSalvarDescri");

        function resetFormDemanda() {
            const form = document.getElementById("formDemanda");

            if (!form) return;

            form.reset();

            form.querySelectorAll("input, select, textarea").forEach(el => {
                if (el.tagName === "SELECT") {
                    el.selectedIndex = 0;
                } else if (el.type !== "radio" && el.type !== "checkbox") {
                    el.value = "";
                }
            });

            const textarea = document.getElementById("descriDemandaTextArea");
            if (textarea) textarea.value = "";

            if (quantidadeAlunosDemanda) {
                quantidadeAlunosDemanda.value = 1;
            }

            if (usuariosAtribuidosSelect) {
                usuariosAtribuidosSelect.innerHTML = '';
                usuariosAtribuidosSelecionados.innerHTML = '';
                usuariosAtribuidosBusca.value = '';
                usuariosAtribuidosResultados.innerHTML = '';
                usuariosAtribuidosResultados.classList.add('hidden');
                const projetoSelect = document.getElementById('projetoDemandaSelect');
                if (projetoSelect) {
                    projetoSelect.disabled = false;
                    projetoSelect.value = '';
                }
                renderizarProjetosResponsaveis();
            }
        };

        function bloquearRolagemModal() {
            document.body.classList.add("no-scroll");
            mainAdm.classList.add("no-scroll");
            mainAdm.classList.add("h-screen");
        }

        function liberarRolagemModal() {
            document.body.classList.remove("no-scroll");
            mainAdm.classList.remove("no-scroll");
            mainAdm.classList.remove("h-screen");
        }

        function fecharModalCriarDemanda() {
            shadowDashBoardADM?.classList.add('hidden');
            divModalCriar?.classList.add('hidden');
            divModalCriar?.style.removeProperty('display');
            divModalCriar?.style.removeProperty('z-index');
            divModalCriar?.style.removeProperty('visibility');
            divModalCriar?.style.removeProperty('opacity');
            liberarRolagemModal();
        }

        // ===== FILTRO DE PROJETO =====
        let filtroProjetoAtual = 'todos';
        const projetosAtivosAdmin = {};

        async function carregarFiltroProjetoSessao() {
            try {
                const response = await fetch('../controllers/demanda_controller.php?action=obter_filtro_projeto', { cache: 'no-store' });
                const data = await response.json();
                if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Não foi possível carregar o filtro.');
                filtroProjetoAtual = data.id_projeto ? String(data.id_projeto) : 'todos';
                localStorage.setItem('filtroProjetoAdmin', filtroProjetoAtual);
            } catch (error) {
                console.error('Erro ao carregar filtro de projeto da sessão:', error);
            }
        }

        function renderizarProjetosResponsaveis() {
            const lista = document.getElementById('listaProjetosResponsaveis');
            if (!lista) return;

            const projetos = Object.entries(projetosAtivosAdmin);
            lista.innerHTML = projetos.length
                ? `<select id="projetoDemandaSelect" name="projeto" required class="w-full py-2 rounded-md bg-[var(--suave-green)] outline-none text-gray-500 pl-3">
                    <option value="" disabled selected>Selecione o projeto responsável</option>
                    ${projetos.map(([id, nome]) => `<option value="${id}">${escaparTextoAdm(nome)}</option>`).join('')}
                </select>`
                : '<p class="text-sm text-red-600">Nenhum projeto vinculado disponível.</p>';

            if (projetos.length === 1) {
                const select = lista.querySelector('#projetoDemandaSelect');
                if (select) select.value = projetos[0][0];
            }

            lista.querySelector('#projetoDemandaSelect')?.addEventListener('change', () => {
                renderizarUsuariosAtribuidos();
                carregarCategoriasDemanda();
            });
            sincronizarProjetoGlobalComFormulario();
            carregarCategoriasDemanda();
        }

        function sincronizarProjetoGlobalComFormulario() {
            const select = document.getElementById('projetoDemandaSelect');
            if (!select) return;

            const possuiFiltro = filtroProjetoAtual !== 'todos';
            if (possuiFiltro && projetosAtivosAdmin[filtroProjetoAtual]) {
                select.value = filtroProjetoAtual;
                select.disabled = true;
            } else {
                select.disabled = false;
                if (Object.keys(projetosAtivosAdmin).length > 1) {
                    select.value = '';
                }
            }

            carregarCategoriasDemanda();
        }

        async function carregarCategoriasDemanda() {
            const select = document.getElementById('categoriaDemandaSelect');
            const projeto = document.getElementById('projetoDemandaSelect');
            if (!select) return;

            select.innerHTML = '<option disabled selected hidden value="">Categoria</option>';
            if (!projeto?.value) return;

            try {
                const response = await fetch(`../controllers/projeto_controller.php?action=listar_categorias&id_projeto=${encodeURIComponent(projeto.value)}`);
                const data = await response.json();
                if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Erro ao carregar categorias.');
                const categorias = Array.isArray(data.dados) ? data.dados : [];
                select.innerHTML = categorias.length
                    ? '<option disabled selected hidden value="">Categoria</option>' + categorias.map((categoria) => `<option value="${escaparTextoAdm(categoria.nome)}">${escaparTextoAdm(categoria.nome)}</option>`).join('')
                    : '<option disabled selected hidden value="">Nenhuma categoria cadastrada</option>';
            } catch (error) {
                console.error('Erro ao carregar categorias:', error);
                select.innerHTML = '<option disabled selected hidden value="">Não foi possível carregar</option>';
            }
        }

        let requisicaoCategoriasRelatorio = 0;

        async function carregarCategoriasFiltroRelatorio(idProjeto = '') {
            const select = document.getElementById('filtroCategoria');
            if (!select) return;
            const requisicaoAtual = ++requisicaoCategoriasRelatorio;
            select.disabled = true;
            select.innerHTML = idProjeto
                ? '<option value="">Carregando categorias...</option>'
                : '<option value="">Selecione um projeto primeiro</option>';
            if (!idProjeto) return;

            try {
                const response = await fetch(`../controllers/projeto_controller.php?action=listar_categorias&id_projeto=${encodeURIComponent(idProjeto)}`, { cache: 'no-store' });
                const data = await response.json();
                if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Erro ao carregar categorias.');
                if (requisicaoAtual !== requisicaoCategoriasRelatorio) return;
                const categorias = Array.isArray(data.dados) ? data.dados : [];
                select.innerHTML = '<option value="">Todas as categorias</option>' + categorias
                    .sort((a, b) => String(a.nome).localeCompare(String(b.nome), 'pt-BR'))
                    .map((categoria) => `<option value="${escaparTextoAdm(categoria.nome)}">${escaparTextoAdm(categoria.nome)}</option>`).join('');
                select.disabled = false;
            } catch (error) {
                if (requisicaoAtual !== requisicaoCategoriasRelatorio) return;
                console.error('Erro ao carregar categoria do filtro:', error);
                select.innerHTML = '<option value="">Não foi possível carregar</option>';
            }
        }

        async function carregarProjetosFiltroRelatorio() {
            const select = document.getElementById('filtroProjetoRelatorio');
            if (!select) return;
            try {
                const response = await fetch('../controllers/projeto_controller.php?action=listar_projetos&apenas_ativos=true');
                const data = await response.json();
                if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Erro ao carregar projetos.');
                const valorAtual = select.value;
                select.innerHTML = '<option value="">Todos os projetos</option>' + (data.dados || []).map((projeto) => `<option value="${Number(projeto.id)}">${escaparTextoAdm(projeto.nome)}</option>`).join('');
                select.value = Array.from(select.options).some((opcao) => opcao.value === valorAtual) ? valorAtual : '';
                await carregarCategoriasFiltroRelatorio(select.value);
            } catch (error) {
                console.error('Erro ao carregar projetos do relatório:', error);
            }
        }

        document.getElementById('filtroProjetoRelatorio')?.addEventListener('change', (event) => {
            carregarCategoriasFiltroRelatorio(event.target.value || '');
        });

        function atualizarBadgeFiltro() {
            const badge = document.getElementById('filtroActiveBadge');
            const textoFiltro = document.getElementById('filtroActiveText');

            if (!badge || !textoFiltro) return;

            if (filtroProjetoAtual === 'todos') {
                badge.classList.add('hidden');
                return;
            }

            badge.classList.remove('hidden');
            const nomeProjeto = projetosAtivosAdmin[filtroProjetoAtual] || 'Projeto';
            textoFiltro.textContent = 'Filtro: ' + nomeProjeto;
        }

        function atualizarVisibilidadeFiltroProjetos() {
            const container = document.getElementById('containerFiltroProjetoAdm');
            if (!container) return;

            const qtdProjetos = Object.keys(projetosAtivosAdmin).length;
            if (qtdProjetos >= 2) {
                container.classList.remove('hidden');
                if (filtroProjetoAtual !== 'todos' && !projetosAtivosAdmin[filtroProjetoAtual]) {
                    filtroProjetoAtual = 'todos';
                    localStorage.setItem('filtroProjetoAdmin', 'todos');
                }
            } else {
                container.classList.add('hidden');
                filtroProjetoAtual = 'todos';
                localStorage.setItem('filtroProjetoAdmin', 'todos');
            }

            const filtroGlobal = document.getElementById('filtroGlobalProjeto');
            if (filtroGlobal) filtroGlobal.value = filtroProjetoAtual;
        }

        function montarFiltroProjetos() {
            const lista = document.getElementById('listaFiltroProjetos');
            const filtroGlobal = document.getElementById('filtroGlobalProjeto');
            if (filtroGlobal) {
                filtroGlobal.innerHTML = '<option value="todos">Todos os projetos</option>' + Object.entries(projetosAtivosAdmin)
                    .map(([id, nome]) => `<option value="${id}">${escaparTextoAdm(nome)}</option>`).join('');
                filtroGlobal.value = filtroProjetoAtual;
            }

            if (!lista) return;

            const itens = [
                `<button class="filtroProjectItem w-full text-left px-3 sm:px-4 py-2 sm:py-2.5 hover:bg-[var(--theme-surface-soft)] transition-colors duration-200 flex items-center space-x-2 sm:space-x-3" data-projeto="todos">
                    <i class="bi bi-collection text-[var(--dark-green)] text-sm"></i>
                    <span class="text-gray-700 font-medium text-xs sm:text-sm">Todas as Demandas</span>
                </button>`
            ];

            Object.entries(projetosAtivosAdmin).forEach(([id, nome]) => {
                itens.push(`
                    <button class="filtroProjectItem w-full text-left px-3 sm:px-4 py-2 sm:py-2.5 hover:bg-[var(--theme-surface-soft)] transition-colors duration-200 flex items-center space-x-2 sm:space-x-3" data-projeto="${id}">
                        <i class="bi bi-diagram-3 text-[var(--dark-green)] text-sm"></i>
                        <span class="text-gray-700 font-medium text-xs sm:text-sm">${nome}</span>
                    </button>
                `);
            });

            lista.innerHTML = itens.join('');

        }

        document.getElementById('btnFiltroGlobalProjeto')?.addEventListener('click', () => {
            const painel = document.getElementById('painelFiltroGlobalProjeto');
            const aberto = !painel?.classList.contains('hidden');
            painel?.classList.toggle('hidden', aberto);
            document.getElementById('btnFiltroGlobalProjeto')?.setAttribute('aria-expanded', String(!aberto));
            document.querySelector('#btnFiltroGlobalProjeto i')?.classList.toggle('rotate-180', !aberto);
        });

        let requisicaoFiltroGlobal = 0;
        let filaPersistenciaFiltroGlobal = Promise.resolve();

        function persistirFiltroGlobalProjeto(idProjeto) {
            const persistencia = filaPersistenciaFiltroGlobal.then(async () => {
                const response = await fetch('../controllers/demanda_controller.php?action=definir_filtro_projeto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ id_projeto: idProjeto })
                });
                const data = await response.json();
                if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Não foi possível salvar o filtro.');
                return data;
            });

            filaPersistenciaFiltroGlobal = persistencia.catch(() => {});
            return persistencia;
        }

        async function aplicarFiltroGlobalProjeto(projeto) {
            const requisicaoAtual = ++requisicaoFiltroGlobal;
            const novoFiltro = projeto || 'todos';
            filtroProjetoAtual = novoFiltro;
            localStorage.setItem('filtroProjetoAdmin', filtroProjetoAtual);
            atualizarBadgeFiltro();
            atualizarVisibilidadeFiltroProjetos();
            sincronizarProjetoGlobalComFormulario();
            mostrarEstadoDemandas('carregando');

            try {
                await persistirFiltroGlobalProjeto(novoFiltro);
            } catch (error) {
                if (requisicaoAtual === requisicaoFiltroGlobal) {
                    console.warn('Não foi possível salvar o filtro de projeto:', error);
                }
            }

            if (requisicaoAtual !== requisicaoFiltroGlobal) return false;

            const select = document.getElementById('filtroGlobalProjeto');
            const botaoFiltro = document.getElementById('btnFiltroGlobalProjeto');
            if (select) select.value = filtroProjetoAtual;

            const resultados = await Promise.allSettled([
                carregarDemandas(),
                carregarDadosGraficos(),
                carregarUsuarios(),
                window.atualizarRankingPontuacao?.()
            ]);

            if (requisicaoAtual !== requisicaoFiltroGlobal) return;
            const demandasFalharam = resultados[0]?.status === 'rejected' || resultados[0]?.value === false;
            if (demandasFalharam) {
                console.error('Não foi possível atualizar as demandas após alterar o filtro.');
                notify.error('Não foi possível concluir a operação agora. Tente novamente.');
                return true;
            }
            const nome = filtroProjetoAtual === 'todos' ? 'Todas as demandas' : (projetosAtivosAdmin[filtroProjetoAtual] || 'Projeto');
            notify.success(filtroProjetoAtual === 'todos' ? 'Filtro global removido.' : `Filtro global aplicado: ${nome}`);
            return true;
        }

        document.getElementById('filtroGlobalProjeto')?.addEventListener('change', (event) => {
            aplicarFiltroGlobalProjeto(event.target.value || 'todos');
        });

        let requisicaoDemandas = 0;

        async function buscarJsonAdm(url, options = {}, tentativas = 3) {
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

        async function carregarDemandas() {
            const requisicaoAtual = ++requisicaoDemandas;
            try {
                console.log('Carregando demandas com filtro:', filtroProjetoAtual);
                
                // Verificar se variáveis globais estão definidas
                if (!divDemandasDisponiveis) {
                    console.error(' divDemandasDisponiveis não encontrado no DOM');
                    return false;
                }

                mostrarEstadoDemandas('carregando');
                
                // Construir URL
                let urlFetch = '../controllers/demanda_controller.php?action=listar_demandas';
                if (filtroProjetoAtual !== 'todos') {
                    urlFetch += '&id_projeto=' + encodeURIComponent(filtroProjetoAtual);
                }

                console.log('URL Fetch:', urlFetch);

                const data = await buscarJsonAdm(urlFetch, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                });

                console.log('Resposta do servidor (JSON parseado):', data);

                if (!data || !data.sucesso) {
                    console.error('Erro do servidor:', data?.erro || 'Resposta sem sucesso');
                    throw new Error(data?.erro || 'Servidor retornou sucesso=false');
                }

                if (requisicaoAtual !== requisicaoDemandas) return;

                // limpa o array antes
                demandas = [];

                if (!data.dados || !Array.isArray(data.dados)) {
                    console.warn('Dados não é um array ou está vazio');
                    data.dados = [];
                }

                console.log('Quantidade de demandas recebidas:', data.dados.length);

                data.dados.forEach((d, index) => {
                    try {
                        console.log(`Processando demanda ${index + 1}:`, d);

                        const modelo = criarDemanda(
                            d.titulo,
                            d.prioridade,
                            d.orientador_nome || 'N/A',
                            d.categoria,
                            d.prazo,
                            d.descricao,
                            d.status,
                            d.projeto,
                            d.data_criacao
                        );

                        modelo.id = d.id;
                        modelo.id_projeto = Number(d.id_projeto || 0);
                        modelo.orientador_id = d.orientador_id;
                        modelo.situacao = d.situacao;
                        modelo.quantidade_alunos = Number(d.quantidade_alunos || 1);
                        modelo.total_assumidos = Number(d.total_assumidos || 0);
                        modelo.responsavel_nome = d.responsavel_nome;
                        modelo.data_finalizacao = d.data_finalizacao;
                        modelo.solucao = d.solucao;
                        modelo.justificativa_nao_conclusao = d.justificativa_nao_conclusao;
                        modelo.justificativa_validacao_status = d.justificativa_validacao_status;
                        modelo.id_admin_decisao_justificativa = d.id_admin_decisao_justificativa;
                        modelo.data_decisao_justificativa = d.data_decisao_justificativa;

                        demandas.push(modelo);
                    } catch (err) {
                        console.error(`Erro ao processar demanda ${index + 1}:`, err);
                    }
                });

                console.log('Total de demandas carregadas no array:', demandas.length);

                demandas.sort((a, b) => {
                    const ordem = {
                        alta: 3,
                        media: 2,
                        baixa: 1
                    };

                    return ordem[b.prioridade] - ordem[a.prioridade];
                });

                console.log('Demandas após ordenação:', demandas.length);
                console.log('Chamando renderizarDemandas...');
                renderizarDemandas();
                console.log('renderizarDemandas concluída');
                return true;

            } catch (error) {
                if (requisicaoAtual !== requisicaoDemandas) return;
                console.error(" ERRO CRÍTICO ao carregar demandas:", error);
                console.error("Stack:", error.stack);
                console.error("Message:", error.message);

                mostrarEstadoDemandas('erro');
                return false;
            }
        };

        const listaFiltroProjetos = document.getElementById('listaFiltroProjetos');
        if (listaFiltroProjetos) {
            listaFiltroProjetos.addEventListener('click', async (e) => {
                const item = e.target.closest('.filtroProjectItem');
                if (!item) return;
                e.preventDefault();
                const projeto = item.getAttribute('data-projeto');

                await aplicarFiltroGlobalProjeto(projeto || 'todos');
            });
        }

        // Botão limpar filtro
        const btnLimparFiltro = document.getElementById('btnLimparFiltro');
        if (btnLimparFiltro) {
            btnLimparFiltro.addEventListener('click', async (e) => {
                e.preventDefault();
                const filtroGlobal = document.getElementById('filtroGlobalProjeto');
                if (filtroGlobal) {
                    filtroGlobal.value = 'todos';
                    filtroGlobal.dispatchEvent(new Event('change'));
                }
            });
        }

        function mostrarConclusaoDemandas() {
            notify.success('Demanda editada com sucesso!');
        }

        function abrirModalCriarDemanda() {
            if (!shadowDashBoardADM || !divModalCriar) {
                console.error('Modal de criação de demanda não encontrado no DOM.');
                return;
            }

            fecharModaisAoTrocarSecao();
            shadowDashBoardADM.classList.remove('hidden');
            divModalCriar.classList.remove('hidden');
            divModalCriar.style.setProperty('display', 'flex', 'important');
            divModalCriar.style.setProperty('z-index', '200', 'important');
            divModalCriar.style.setProperty('visibility', 'visible', 'important');
            divModalCriar.style.setProperty('opacity', '1', 'important');
            document.getElementById('modalCriarDemanda')?.classList.remove('hidden');
            bloquearRolagemModal();
            resetFormDemanda();
        }

        async function verificarProjetosAntesDeAbrir(acao) {
            if (Object.keys(projetosAtivosAdmin).length === 0) {
                await carregarProjetos();
            }

            if (Object.keys(projetosAtivosAdmin).length > 0) return true;

            const mensagem = acao === 'relatorio'
                ? 'Crie um projeto antes de gerar um relatório personalizado.'
                : 'Crie um projeto antes de cadastrar uma nova demanda.';
            if (typeof mostrarAvisoSistema === 'function') {
                mostrarAvisoSistema(mensagem, 'warning');
            } else {
                notify.warning(mensagem);
            }
            return false;
        }

        btnNovaDemanda.forEach((botao) => botao.addEventListener('click', async () => {
            if (await verificarProjetosAntesDeAbrir('demanda')) abrirModalCriarDemanda();
        }));

        btnGerarRelatorio.forEach((botao) => botao.onclick = () => {
            const filtroProjeto = filtroProjetoAtual !== 'todos' ? '?id_projeto=' + encodeURIComponent(filtroProjetoAtual) : '';
            window.open('relatorios/relatorio_geral.php' + filtroProjeto, '_blank', 'noopener,noreferrer');
        });

        btnFecharModalCriar?.addEventListener('click', () => {
            fecharModalCriarDemanda();
        });

        const descriDemandaTextArea = document.getElementById("descriDemandaTextArea");

        btnAddDescri.onclick = () => {
            divModalCriar.classList.add("hidden");
            divModalDescri.classList.remove("hidden");
        };

        if (btnFecharModalDescri) {
            btnFecharModalDescri.onclick = () => {
                divModalDescri.classList.add("hidden");
                divModalCriar.classList.remove("hidden");
            };
        }

        btnSalvarDescri.onclick = () => {
            divModalDescri.classList.add("hidden");
            divModalCriar.classList.remove("hidden");

            const valorDescriDemanda = descriDemandaTextArea.value.trim();

            if (valorDescriDemanda) {
                document.getElementById("iconBtnAddDescri").className = "fa-solid fa-pen";
                document.getElementById("textBtnAddDescri").textContent = "Editar Descrição";
            } else {
                document.getElementById("iconBtnAddDescri").className = "fa-solid fa-align-left";
                document.getElementById("textBtnAddDescri").textContent = "Adicionar Descrição";
            }

        };


        const btnCriarDemanda = document.getElementById("btnCriarDemanda");
        const btnConclusao = document.getElementById("btnConclusao");

        const sinalizacaoSemDemandasDp = document.querySelectorAll(".sinalizacaoSemDemandasDp");
        const divDemandasDisponiveis = document.querySelector(".divDemandasDisponiveis");
        const divDemandasAndamento = document.querySelector(".divDemandasAndamento");
        const divGeralDemandas = document.querySelector(".geralDemandas");
        const filtroTituloDemanda = document.getElementById("filtroTituloDemanda");
        const filtroPrioridadeDemanda = document.getElementById("filtroPrioridadeDemanda");
        const filtroOrientadorDemanda = document.getElementById("filtroOrientadorDemanda");
        const filtroStatusDemanda = document.getElementById("filtroStatusDemanda");

        // Inputs
        const tituloDemandaInput = document.getElementById("tituloDemandaInput");
        const prioridadeDemandaSelect = document.getElementById("prioridadeDemandaSelect");
        const categoriaDemandaSelect = document.getElementById("categoriaDemandaSelect");
        const prazoDemanda = document.getElementById("prazoDemanda");
        const usuariosAtribuidosSelect = document.getElementById("usuariosAtribuidosSelect");
        const usuariosAtribuidosBusca = document.getElementById("usuariosAtribuidosBusca");
        const usuariosAtribuidosResultados = document.getElementById("usuariosAtribuidosResultados");
        const usuariosAtribuidosSelecionados = document.getElementById("usuariosAtribuidosSelecionados");
        const quantidadeAlunosDemanda = document.getElementById("quantidadeAlunosDemanda");
        let usuariosDisponiveis = [];

        function sincronizarProjetoComAtribuicoes() {
            if (!usuariosAtribuidosSelect) return;

            const selecionados = Array.from(usuariosAtribuidosSelect.selectedOptions);
            const projetoSelect = document.getElementById('projetoDemandaSelect');
            const projetoSelecionado = selecionados[0]?.dataset.projetoId || projetoSelect?.value || '';

            if (projetoSelect) {
                projetoSelect.disabled = selecionados.length > 0;
                if (projetoSelecionado) projetoSelect.value = projetoSelecionado;
            }

            renderizarUsuariosAtribuidos();
        }

        function renderizarUsuariosAtribuidos() {
            const idsSelecionados = new Set(Array.from(usuariosAtribuidosSelect.selectedOptions).map((option) => option.value));
            const projetoSelect = document.getElementById('projetoDemandaSelect');
            const projetoSelecionado = usuariosAtribuidosSelect.selectedOptions[0]?.dataset.projetoId || projetoSelect?.value || '';
            const pesquisa = usuariosAtribuidosBusca.value.trim().toLocaleLowerCase();

            usuariosAtribuidosResultados.innerHTML = '';
            const resultados = usuariosDisponiveis.filter((usuario) => {
                const nomeCorresponde = usuario.nome.toLocaleLowerCase().includes(pesquisa);
                const projetosUsuario = String(usuario.projetos_ids || '').split(',').filter(Boolean);
                const tipoPermitido = !projetoSelecionado || projetosUsuario.includes(String(projetoSelecionado));
                return nomeCorresponde && tipoPermitido && !idsSelecionados.has(String(usuario.id));
            });

            if (!pesquisa || resultados.length === 0) {
                usuariosAtribuidosResultados.classList.add('hidden');
                return;
            }

            resultados.forEach((usuario) => {
                const resultado = document.createElement('button');
                resultado.type = 'button';
                resultado.className = 'w-full text-left px-3 py-2 text-sm text-gray-600 hover:bg-gray-100';
                resultado.textContent = `${usuario.nome} (Usuário)`;
                resultado.addEventListener('click', () => adicionarUsuarioAtribuido(usuario));
                usuariosAtribuidosResultados.appendChild(resultado);
            });
            usuariosAtribuidosResultados.classList.remove('hidden');
        }

        function adicionarUsuarioAtribuido(usuario) {
            const option = document.createElement('option');
            option.value = usuario.id;
            option.dataset.projetoId = String(usuario.projetos_ids || '').split(',')[0] || '';
            option.textContent = usuario.nome;
            option.selected = true;
            usuariosAtribuidosSelect.appendChild(option);

            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-2 rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-600';
            chip.innerHTML = `<span>${usuario.nome}</span><button type="button" class="text-gray-500 hover:text-red-600" aria-label="Remover ${usuario.nome}"><i class="fa-solid fa-xmark"></i></button>`;
            chip.querySelector('button').addEventListener('click', () => {
                option.remove();
                chip.remove();
                sincronizarProjetoComAtribuicoes();
            });
            usuariosAtribuidosSelecionados.appendChild(chip);
            usuariosAtribuidosBusca.value = '';
            sincronizarProjetoComAtribuicoes();
        }

        // Adicione isso depois das consts
        const usuario = <?php echo json_encode(mb_strtolower(trim((string) $tipoUsuario), 'UTF-8'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        const usuarioAtualId = <?php echo (int) $_SESSION['id_usuario']; ?>;
        console.log("Usuário logado como:", usuario);

        // Labels
        const prioridadeLabel = {
            alta: "Alta",
            media: "Média",
            baixa: "Baixa"
        };
        // const categoriaLabel = {
        //     hardware: "Hardware",
        //     rede: "Rede",
        //     software_sistemas: "Software/Sistemas",
        //     emprestimo_equipamentos: "Empréstimo Equipamentos",
        //     suporte_operacional: "Suporte Operacional"
        // }
        
        const statusLabel = {
            aberta: "Em aberto",
            em_andamento: "Em andamento",
            finalizada: "Finalizada"
        };

        function getSituacaoDemanda(demanda) {
            const situacao = String(demanda?.situacao || 'pendente').trim().toLowerCase();
            const situacoes = {
                pendente: 'Pendente',
                concluida: 'Concluída',
                nao_concluida: 'Não concluída'
            };

            return situacoes[situacao] || 'Pendente';
        }

        function criarDemanda(titulo, prioridade, orientador, categoria, prazo, descricao, status, projeto, dataCriacao = null) {
            return {
                titulo: titulo,
                prioridade: prioridade.toLowerCase(),
                orientador: orientador,
                categoria: categoria,
                prazo: prazo,
                data_criacao: dataCriacao ? new Date(dataCriacao).toLocaleDateString("pt-BR") : new Date().toLocaleDateString("pt-BR"),
                descricao: descricao,
                status: status,
                projeto: projeto
            };
        };

        function getCorPrioridade(prioridade) {
            if (prioridade === "alta") return "text-[var(--red1)]";
            if (prioridade === "media") return "text-[var(--accent-yellow)]";
            if (prioridade === "baixa") return "text-[]";
        };

        function getCorPrioridadeBg(prioridade) {
            if (prioridade === "alta") return "bg-[var(--red-transparent)]";
            if (prioridade === "media") return "bg-[var(--yellow-transparent)]";
            if (prioridade === "baixa") return "bg-[var(--blue-transparent)]";
        };

        function getPontosPrioridade(prioridade) {
            return { baixa: 1, media: 2, alta: 3 }[String(prioridade || '').toLowerCase()] || 1;
        }


        function getPrimeiroNome(nomeCompleto) {
            if (!nomeCompleto) return "";
            if (nomeCompleto === "Francisco Otávio de Menezes Filho") {
                return nomeCompleto.split(" ")[1];
            } else {
                return nomeCompleto.split(" ")[0];
            }
        }

        function formatarData(data) {
            if (!data) return "";

            const valor = String(data).trim();
            const dataBrasileira = valor.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
            const d = dataBrasileira
                ? new Date(Number(dataBrasileira[3]), Number(dataBrasileira[2]) - 1, Number(dataBrasileira[1]))
                : new Date(valor.includes(' ') ? valor.replace(' ', 'T') : valor);

            return Number.isNaN(d.getTime()) ? valor : d.toLocaleDateString("pt-BR");
        }

        function getStatusColor(status) {

            if (status === "aberta") {
                console.log("Demanda - Aberta");
                return `text-[var(--primary-dark-green)] font-semibold`;
            };

            if (status === "em_andamento") {
                console.log("Demanda - Em andamento");
                return `text-[var(--accent-yellow)] font-semibold`;
            };

        };

        function getStatus(status, dataFinalizacao = null) {
            if (status === "aberta") {
                return `<div class="flex items-center space-x-2 text-[var(--dark-green)] text-sm lg:text-[0.8vw] w-full">
                    <i class="fa-solid fa-hand"></i>
                    <p>Em aberto</p>
                </div>`;
            }

            if (status === "em_andamento") {
                return `<div class="flex items-center space-x-2 text-[var(--accent-yellow)] text-sm lg:text-[0.8vw] w-full">
                    <i class="fa-solid fa-spinner"></i>
                    <p>Em andamento</p>
                </div>`;
            }

            if (status === "finalizada") {
                const dataFormatada = dataFinalizacao ? formatarData(dataFinalizacao) : "";
                return `<div class="flex items-center space-x-2 text-gray-400 text-sm lg:text-[0.8vw] w-full">
                    <i class="fa-solid fa-circle-check"></i>
                    <p>${dataFormatada ? `Finalizada em ${dataFormatada}` : "Finalizada"}</p>
                </div>`;
            }

            return `<div class="flex items-center space-x-2 text-gray-400 text-sm lg:text-[0.8vw] w-full">
                <i class="fa-solid fa-circle-check"></i>
                <p>${statusLabel[status] || "Finalizada"}</p>
            </div>`;
        }

        function obterDemandasFiltradas() {
            const titulo = filtroTituloDemanda.value.trim().toLowerCase();
            const prioridade = filtroPrioridadeDemanda.value;
            const orientador = filtroOrientadorDemanda.value;
            const status = String(filtroStatusDemanda.value || '').trim().toLowerCase();

            return demandas.filter((demanda) => {
                const correspondeTitulo = !titulo || demanda.titulo.toLowerCase().includes(titulo);
                const correspondePrioridade = !prioridade || demanda.prioridade === prioridade;
                const correspondeOrientador = !orientador || String(demanda.orientador_id) === orientador;
                const statusDemanda = String(demanda.status || '').trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '_');
                const situacaoDemanda = String(demanda.situacao || '').trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '_');
                const statusFinalizada = statusDemanda === 'finalizada';
                const correspondeStatus = !status
                    ? statusFinalizada
                    : (status === 'finalizada_concluida' && statusFinalizada && situacaoDemanda === 'concluida')
                    || (status === 'finalizada_nao_concluida' && statusFinalizada && situacaoDemanda === 'nao_concluida');

                return correspondeTitulo && correspondePrioridade && correspondeOrientador && correspondeStatus;
            });
        }

        function aplicarFiltrosDemandas() {
            renderizarDemandas();
        }

        [filtroTituloDemanda, filtroPrioridadeDemanda, filtroOrientadorDemanda, filtroStatusDemanda]
            .forEach((filtro) => {
                filtro.addEventListener("input", aplicarFiltrosDemandas);
                filtro.addEventListener("change", aplicarFiltrosDemandas);
            });

        // Esqueleto Modal Informações
        function criarModalInfs(demanda) {
            const modalInfs = document.createElement("div");

            const corPrioridade = getCorPrioridadeBg(demanda.prioridade);

            const descriDemanda = demanda.descricao?.trim() || "Sem descrição.";

            let prazoFormatado = formatarData(demanda.prazo);
            const responsavelNome = String(demanda.responsavel_nome || '').trim();
            const solucao = String(demanda.solucao || '').trim();
            const justificativa = String(demanda.justificativa_nao_conclusao || '').trim();
            const responsavelMarkup = responsavelNome && responsavelNome !== 'N/A' && responsavelNome !== 'null'
                ? `
                            <div class="flex items-center w-full gap-2">
                                <li class="text-sm flex items-center space-x-2 bg-[var(--white-gray)] p-2 rounded-md w-full">
                                    <i class="fa-solid fa-user-check"></i>
                                    <p class="text-[var(--gray2)]">Responsável:</p>
                                    <span class="responsavelSpan">${responsavelNome}</span>
                                </li>
                            </div>`
                : '';
            const solucaoMarkup = demanda.situacao === 'concluida' && solucao
                ? `
                            <div class="flex items-center w-full gap-2">
                                <li class="text-sm flex items-start space-x-2 bg-[var(--white-gray)] p-2 rounded-md w-full">
                                    <i class="fa-solid fa-check-circle"></i>
                                    <p class="text-[var(--gray2)]">Solução:</p>
                                    <span class="text-[var(--gray1)]">${solucao}</span>
                                </li>
                            </div>`
                : '';
            const justificativaMarkup = demanda.situacao === 'nao_concluida'
                ? `
                            <div class="flex items-center w-full gap-2">
                                <li class="text-sm flex items-start space-x-2 bg-[var(--white-gray)] p-2 rounded-md w-full">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    <p class="text-[var(--gray2)]">Justificativa:</p>
                                    <span class="text-[var(--gray1)]">${justificativa || 'Não informada.'}</span>
                                </li>
                            </div>`
                : '';
            const statusValidacao = demanda.justificativa_validacao_status || 'pendente';
            const validacaoMarkup = demanda.situacao === 'nao_concluida' && Number(demanda.orientador_id) === usuarioAtualId
                ? `
                    <div class="validacao-justificativa mt-3 border border-yellow-200 bg-yellow-50 rounded-lg p-3" data-status-validacao="${statusValidacao}">
                        <p class="text-xs uppercase tracking-[0.12em] text-yellow-700 font-semibold">Validação da justificativa</p>
                        <p class="text-sm text-gray-700 mt-2">Pontuação da demanda: <strong>${getPontosPrioridade(demanda.prioridade)} ponto(s)</strong></p>
                        <p class="text-sm text-yellow-700 font-semibold mt-1">Status: ${statusValidacao === 'pendente' ? 'Aguardando validação' : statusValidacao === 'validada' ? 'Justificativa validada' : 'Justificativa invalidada'}</p>
                        ${statusValidacao === 'pendente' ? '<div class="flex gap-2 mt-3"><button type="button" class="btnValidarJustificativa flex-1 py-2 rounded-md bg-[var(--dark-green)] text-white text-xs font-semibold">Validar justificativa</button><button type="button" class="btnInvalidarJustificativa flex-1 py-2 rounded-md bg-[var(--red1)] text-white text-xs font-semibold">Invalidar justificativa</button></div>' : ''}
                    </div>`
                : '';

            modalInfs.innerHTML = `
            
                <div class="w-screen lg:w-full flex justify-center">
                    <div id="modalInfs" class="bg-[var(--white)] w-[90%] lg:w-[22vw] max-h-[calc(100dvh-1rem)] sm:max-h-[90vh] pb-8 z-[200] rounded-lg flex flex-col gap-3 items-center overflow-y-auto overscroll-contain">
                        <div class="w-full flex items-center justify-between to-[var(--dark-green)] py-3 px-5">
                            <div class="flex items-center space-x-2 text-white ">
                                <i class="fa-solid fa-globe text-gray-500"></i>
                                <p class="font-semibold"><span class="text-[var(--dark-green)]">Informações</span>
                                <span class="text-[var(--accent-yellow)]">Gerais</span></p>
                            </div>
                            <!-- Fechar Modal -->
                            <button class="btnFecharModalInfs">
                                <i class="fa-solid fa-arrow-right text-xl text-[var(--dark-green)] hover:text-[var(--dark-green)] hover:translate-x-1 transition ease-in-out duration-300"></i>
                            </button>
                        </div>
                        <div class="grid lg:grid-cols-1 px-5 gap-10 w-full">
                            <ul class="gap-3 grid lg:grid-cols-1 w-full">
                                <div class="flex items-center w-full gap-2">
                                    <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                        <i class="fa-solid fa-clipboard-list"></i>
                                        <p class="text-[var(--gray2)]">Título:</p>
                                        <span class="tituloDemanda tituloEdicao text-[var(--theme-color)]">${demanda.titulo}</span>
                                    </li>
                                </div>
                                <div class="flex items-center w-full gap-2">
                                    <li class="text-sm flex items-center gap-2 ${corPrioridade} p-2.5 rounded-lg w-full">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <p class="text-[var(--gray2)]">Prioridade:</p>
                                        <span class="prioridadeSpan prioridadeEdicao">${prioridadeLabel[demanda.prioridade]}</span>
                                    </li>
                                </div>
                                <div class="flex items-center w-full gap-2">
                                    <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                        <i class="fa-solid fa-user-tie"></i>
                                        <p class="text-[var(--gray2)]">Orientador:</p>
                                        <span class="orientadorSpan text-[var(--theme-color)]">${getPrimeiroNome(demanda.orientador) || "Não Informado"}</span>
                                    </li>
                                </div>
                                <div class="flex items-center w-full gap-2">
                                    <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                        <i class="fa-solid fa-sitemap"></i>
                                        <p class="text-[var(--gray2)]">Categoria:</p>
                                        <span class="categoriaSpan categoriaEdicao text-[var(--theme-color)]">${demanda.categoria || escaparTextoAdm(demanda.categoria)}</span>
                                    </li>
                                </div>
                                <div class="flex items-center w-full gap-2">
                                    <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                        <i class="fa-solid fa-circle-info"></i>
                                        <p class="text-[var(--gray2)]">Situação:</p>
                                        <span class="situacaoSpan text-[var(--theme-color)]">${getSituacaoDemanda(demanda)}</span>
                                    </li>
                                </div>
                                <div class="flex items-center w-full gap-2">
                                    <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                        <i class="fa-solid fa-hammer"></i>
                                        <p class="text-[var(--gray2)]">Criação:</p>
                                        <span class="criacaoSpan text-[var(--theme-color)]">${demanda.data_criacao}</span>
                                    </li>
                                </div>
                                <div class="flex items-center w-full gap-2">
                                    <li class="text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full">
                                        <i class="fa-solid fa-calendar-days pr-1"></i>
                                        <p class="text-[var(--gray2)]">Prazo:</p>
                                        <span class="prazoSpan prazoEdicao text-[var(--theme-color)]">${prazoFormatado}</span>
                                    </li>
                                </div>
                                ${responsavelMarkup}
                                ${solucaoMarkup}
                                ${justificativaMarkup}
                                ${validacaoMarkup}
                            </ul>
                            <div class="w-full border-t-2 border-[var(--dark-green)] rounded-xl bg-[var(--suave-green)] overflow-hidden">
                                <p class="descriDemandaSpan descricaoEdicao text-[var(--theme-color)] py-3 px-3 text-sm leading-relaxed">${descriDemanda}</p>
                            </div>
                        </div>
                    </div>
                </div>

            `

            return modalInfs;
        };


        // ==================== FUNÇÃO PARA MOSTRAR BOTÕES CORRETOS ====================
        function configurarBotoesCard(card, usuario, status, podeGerenciar = false) {
            const btnAssumir = card.querySelector(".btnAssumir");
            const btnEditar = card.querySelector(".btnEditar");
            const statusNormalizado = String(status || '').trim().toLowerCase();

            if (statusNormalizado === 'finalizada') {
                btnAssumir?.classList.add("hidden");
                btnEditar?.classList.add("hidden");
                return;
            }

            if (usuario === "administrador" || usuario === "master") {
                btnAssumir?.classList.add("hidden");
                btnEditar?.classList.toggle("hidden", !podeGerenciar);
                return;
            }

            if (!btnAssumir || !btnEditar) return;

            else {
                // Usuários vinculados assumem ou concluem a demanda.
                if (status === "aberta") {
                    btnAssumir.classList.remove("hidden");
                    btnEditar.classList.add("hidden");

                    btnAssumir.innerHTML = `
                        <p>Assumir</p>
                        <i class="fa-solid fa-hand"></i>
                    `;
                }

                if (status === "em_andamento") {
                    btnAssumir.classList.remove("hidden");
                    btnEditar.classList.add("hidden");

                    btnAssumir.innerHTML = `
                        <p>Concluir</p>
                        <i class="fa-solid fa-check"></i>
                    `;

                    btnAssumir.classList.remove("bg-[var(--dark-green)]");
                    btnAssumir.classList.add("bg-[var(--accent-yellow)]");
                }
            };
        };

        // Esqueleto Demanda (Dashboard) - CORRIGIDO
        function criarCardDemandaDashboard(demanda) {
            const cardDemandaDashboard = document.createElement("div");
            // marca o card com o id da demanda para manipulação direta no DOM
            if (demanda && demanda.id !== undefined) cardDemandaDashboard.setAttribute('data-demanda-id', demanda.id);

            let prazoFormatado = formatarData(demanda.prazo);
            const classePrioridadeCard = `card-prioridade-${demanda.prioridade}`;
            const classeLinhaPrioridade = `linha-prioridade-${demanda.prioridade}`;

            const totalAssumidos = Number(demanda.total_assumidos || 0);
            const quantidadeNecessaria = Number(demanda.quantidade_alunos || 1);

            cardDemandaDashboard.innerHTML = `
                <div class="demanda-card-layout ${classePrioridadeCard} border-l-4 w-full rounded-xl px-5 py-4 flex flex-col gap-3 hover:-translate-y-0.5 hover:shadow-md transition ease-in-out duration-300">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center space-x-2 text-[var(--gray2)] min-w-0">
                            <i class="fa-solid fa-clipboard-list text-sm lg:text-[1vw]"></i>
                            <p class="tituloDemanda text-sm lg:text-[0.9vw] font-semibold">${demanda.titulo}</p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button class="btnVer ${classeLinhaPrioridade} w-10 h-10 flex items-center justify-center border border-[#121212]/10 rounded-md hover:brightness-95 transition" aria-label="Ver informações da demanda" title="Ver informações">
                                <i class="fa-solid fa-eye text-[#121212]"></i>
                            </button>

                            <button class="btnEditar ${classeLinhaPrioridade} w-10 h-10 flex items-center justify-center border border-[#121212]/10 rounded-md hover:brightness-95 transition hidden" aria-label="Editar demanda" title="Editar demanda">
                                <i class="fa-solid fa-pen text-[#111827]"></i>
                            </button>

                            <button class="btnExcluir ${classeLinhaPrioridade} w-10 h-10 flex items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-700 transition hover:bg-red-100" aria-label="Excluir demanda" title="Excluir demanda">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-2.5 text-sm">
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-triangle-exclamation pr-1"></i> Prioridade: <span class="prioridadeSpan text-[var(--gray1)] font-semibold">${prioridadeLabel[demanda.prioridade]}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-calendar-check pr-1"></i> Prazo: <span class="prazoSpan text-[var(--gray2)]">${prazoFormatado}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-circle-info pr-1"></i> Status: <span class="text-[var(--gray1)] font-semibold">${statusLabel[demanda.status] || demanda.status}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-calendar-days pr-1"></i> Início: <span class="criacaoSpan text-[var(--gray2)]">${formatarData(demanda.data_criacao)}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-user-tie pr-1"></i> Orientador: <span class="orientadorSpan text-[var(--gray2)]">${getPrimeiroNome(demanda.orientador) || "Não Informado"}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-users pr-1"></i> Assumidos: <span class="font-semibold text-[var(--gray1)]">${totalAssumidos}/${quantidadeNecessaria}</span></p>
                    </div>
                    <div class="flex justify-start items-center space-x-3 pt-1.5 w-full">
                        <button class="bg-[var(--dark-green)] text-white font-semibold py-2 w-[55%] justify-center rounded-lg text-sm lg text-sm:lg:text-[0.8vw] flex items-center space-x-2 hover:bg-[var(--dark-green2)] transition ease-in-out duration-300 btnAssumir hidden">
                            <p>Assumir</p>
                            <i class="fa-solid fa-hand"></i>
                        </button>
                    </div>
                </div>
            `;

            // Configura os botões corretamente após criar o card
            configurarBotoesCard(cardDemandaDashboard, usuario, demanda.status, usuario === 'master' || Number(demanda.orientador_id) === usuarioAtualId);

            return cardDemandaDashboard;
        }

        // Esqueleto Demanda (Seção Demandas) - CORRIGIDO
        function criarCardDemandaDemandas(demanda) {
            const cardDemandaDemandas = document.createElement("div");
            // marca o card com o id da demanda para manipulação direta no DOM
            if (demanda && demanda.id !== undefined) cardDemandaDemandas.setAttribute('data-demanda-id', demanda.id);
            const corPrioridade = getCorPrioridade(demanda.prioridade);
            const statusDemanda = getStatus(demanda.status, demanda.data_finalizacao || demanda.dataFinalizacao);
            let prazoFormatado = formatarData(demanda.prazo);
            const statusNormalizado = String(demanda.status || '').trim().toLowerCase();
            const statusTexto = statusNormalizado === 'em_andamento' ? 'Em andamento' : statusNormalizado === 'finalizada' ? 'Finalizada' : 'Em aberto';
            const statusIcone = statusNormalizado === 'em_andamento' ? 'fa-spinner' : statusNormalizado === 'finalizada' ? 'fa-circle-check' : 'fa-hand';
            const classePrioridadeCard = `card-prioridade-${demanda.prioridade}`;
            const classeLinhaPrioridade = `linha-prioridade-${demanda.prioridade}`;
            const totalAssumidos = Number(demanda.total_assumidos || 0);
            const quantidadeNecessaria = Number(demanda.quantidade_alunos || 1);

            cardDemandaDemandas.innerHTML = `
                <div class="h-full min-h-[300px] w-full ${classePrioridadeCard} rounded-xl p-5 shadow-sm hover:-translate-y-0.5 hover:shadow-md transition ease-in-out duration-300 border-l-4 flex flex-col">
                    <div class="demanda-card-layout flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <i class="fa-solid fa-clipboard-list shrink-0"></i>
                            <p class="tituloDemanda font-semibold leading-snug line-clamp-2">${demanda.titulo}</p>
                        </div>
                    </div>
                    <div class="mt-5 space-y-2.5 text-sm">
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-triangle-exclamation pr-1"></i> Prioridade: <span class="prioridadeSpan text-[var(--gray1)] font-semibold">${prioridadeLabel[demanda.prioridade]}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-user-tie pr-1"></i> Orientador: <span class="orientadorSpan">${getPrimeiroNome(demanda.orientador) || "Não Informado"}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-calendar-days pr-1"></i> Início: <span class="criacaoSpan">${formatarData(demanda.data_criacao)}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-calendar-check pr-1"></i> Prazo: <span class="prazoSpan">${prazoFormatado}</span></p>
                        <p class="statusDemanda text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid ${statusIcone} pr-1"></i> Status: <span class="text-[var(--gray1)] font-semibold">${statusTexto}</span></p>
                    </div>
                    <div class="flex items-center gap-2 pt-5 mt-auto w-full">
                        <button class="${classeLinhaPrioridade} font-semibold h-10 flex-1 justify-center rounded-lg text-sm flex items-center gap-2 hover:brightness-95 transition btnVer text-[#111827]" aria-label="Ver informações da demanda" title="Ver informações"><p>Ver</p><i class="fa-solid fa-eye"></i></button>
                        <button class="bg-[var(--dark-green)] text-white font-semibold h-10 flex-1 justify-center rounded-lg text-sm flex items-center gap-2 hover:bg-[var(--dark-green2)] transition btnAssumir hidden"><p>Assumir</p><i class="fa-solid fa-hand"></i></button>
                        <button class="bg-[var(--dark-green)] text-white font-semibold h-10 flex-1 justify-center rounded-lg text-sm flex items-center gap-2 hover:bg-[var(--dark-green2)] transition btnEditar hidden"><p>Editar</p><i class="fa-solid fa-pen"></i></button>
                    </div>
                </div>
            `;

            // Configura os botões corretamente
            configurarBotoesCard(cardDemandaDemandas, usuario, demanda.status, usuario === 'master' || Number(demanda.orientador_id) === usuarioAtualId);

            return cardDemandaDemandas;
        }

        function abrirModalEdicao(demanda) {
            const modalContainer = divModalInfsSecDemandas[0];
            if (!modalContainer) return;

            modalContainer.innerHTML = `
                <div id="modalEditarDemanda" class="bg-[var(--white)] w-[90%] lg:w-[22vw] pb-8 rounded-lg flex flex-col gap-3 items-center overflow-hidden z-[200]">
                    <div class="w-full flex items-center justify-between py-3 px-5">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-globe text-gray-500"></i>
                            <p class="font-semibold"><span class="text-[var(--dark-green)]">Informações</span> <span class="text-[var(--accent-yellow)]">Gerais</span></p>
                        </div>
                        <button type="button" class="btnFecharEdicao text-xl text-[var(--dark-green)] hover:translate-x-1 transition">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>

                    <form class="formEdicaoDemanda flex flex-col w-full gap-3 px-5">
                        <div class="w-full flex items-center relative">
                            <i class="fa-solid fa-tag absolute text-[var(--green-icon-crede)] ml-3"></i>
                            <input class="campoEdicaoTitulo w-full py-2 rounded-md bg-[var(--white-gray)] outline-none text-gray-500 indent-9" type="text" placeholder="Título" required>
                        </div>
                        <div class="w-full flex items-center relative">
                            <i class="fa-solid fa-triangle-exclamation absolute text-[var(--green-icon-crede)] ml-3"></i>
                            <select class="campoEdicaoPrioridade w-full py-2 rounded-md bg-[var(--white-gray)] outline-none text-gray-500 pl-9" required>
                            <option value="alta">Alta</option>
                            <option value="media">Média</option>
                            <option value="baixa">Baixa</option>
                            </select>
                        </div>
                        <div class="w-full flex items-center relative">
                            <i class="fa-solid fa-user-tie absolute text-[var(--green-icon-crede)] ml-3"></i>
                            <div class="w-full py-2 rounded-md bg-[var(--white-gray)] text-gray-500 pl-9 select-none cursor-not-allowed">Orientador: ${demanda.orientador || "Não informado"}</div>
                        </div>
                        <div class="w-full flex items-center relative">
                            <i class="fa-solid fa-sitemap absolute text-[var(--green-icon-crede)] ml-3"></i>
                            <select class="campoEdicaoCategoria w-full py-2 rounded-md bg-[var(--white-gray)] outline-none text-gray-500 pl-9" required>
                            <option value="">Categoria</option>
                            </select>
                        </div>
                        <div class="w-full flex items-center relative">
                            <i class="fa-solid fa-clock absolute text-[var(--green-icon-crede)] ml-3"></i>
                            <input class="campoEdicaoPrazo w-full py-2 rounded-md bg-[var(--white-gray)] outline-none text-gray-500 pl-9" type="date" required>
                        </div>
                        <div class="w-full flex items-center relative">
                            <i class="fa-solid fa-users absolute text-[var(--green-icon-crede)] ml-3"></i>
                            <input class="campoEdicaoQuantidadeAlunos w-full py-2 rounded-md bg-[var(--white-gray)] outline-none text-gray-500 pl-9 pr-3" type="number" min="1" max="10" required aria-label="Quantidade de usuários necessários" placeholder="Usuários necessários">
                        </div>
                        <div class="flex flex-col gap-2 text-sm text-gray-600 py-1">
                            <p>Projeto responsável</p>
                            <div class="flex items-center gap-5">
                            <label><input class="campoEdicaoProjeto" type="radio" name="projetoEdicao" value="${Number(demanda.id_projeto)}"> ${escaparTextoAdm(demanda.projeto || 'Projeto')}</label>
                            </div>
                        </div>
                        <div class="w-full flex items-center relative">
                            <i class="fa-solid fa-align-left absolute text-[var(--green-icon-crede)] ml-3"></i>
                            <textarea class="campoEdicaoDescricao w-full bg-[var(--white-gray)] outline-none text-gray-500 resize-none rounded-lg p-2 pl-9" rows="4" placeholder="Descrição"></textarea>
                        </div>
                        <button type="submit" class="btnSalvarEdicao py-3 bg-[var(--dark-green)] text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center hover:bg-[var(--dark-green2)] transition">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Salvar alterações</span>
                        </button>
                    </form>
                </div>
            `;

            const form = modalContainer.querySelector(".formEdicaoDemanda");
            const campoTitulo = modalContainer.querySelector(".campoEdicaoTitulo");
            const campoPrioridade = modalContainer.querySelector(".campoEdicaoPrioridade");
            const campoCategoria = modalContainer.querySelector(".campoEdicaoCategoria");
            const campoPrazo = modalContainer.querySelector(".campoEdicaoPrazo");
            const campoQuantidadeAlunos = modalContainer.querySelector(".campoEdicaoQuantidadeAlunos");
            const campoDescricao = modalContainer.querySelector(".campoEdicaoDescricao");
            const campoProjeto = modalContainer.querySelector(`input[name="projetoEdicao"][value="${Number(demanda.id_projeto)}"]`);

            campoTitulo.value = demanda.titulo || "";
            campoPrioridade.value = (demanda.prioridade || "").toLowerCase();
            campoCategoria.value = demanda.categoria || "";
            campoPrazo.value = demanda.prazo ? String(demanda.prazo).slice(0, 10) : "";
            campoQuantidadeAlunos.value = Number(demanda.quantidade_alunos || 1);
            campoDescricao.value = demanda.descricao || "";
            if (campoProjeto) campoProjeto.checked = true;

            const fechar = () => {
                modalContainer.classList.add("hidden");
                modalContainer.innerHTML = "";
                shadowSecDemandas.classList.add("hidden");
                liberarRolagemModal();
            };

            modalContainer.querySelector(".btnFecharEdicao").onclick = fechar;
            form.onsubmit = async (event) => {
                event.preventDefault();
                const botaoSalvar = modalContainer.querySelector(".btnSalvarEdicao");
                const idProjeto = Number(modalContainer.querySelector('input[name="projetoEdicao"]:checked')?.value || 0);
                botaoSalvar.disabled = true;

                try {
                    const response = await fetch('../controllers/demanda_controller.php?action=atualizar_demanda', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id_demanda: demanda.id,
                            titulo: campoTitulo.value.trim(),
                            prioridade: campoPrioridade.value,
                            categoria: campoCategoria.value,
                            prazo: campoPrazo.value,
                            quantidade_alunos: Number(campoQuantidadeAlunos.value),
                            id_projeto: idProjeto,
                            descricao: campoDescricao.value.trim()
                        })
                    });

                    const data = await response.json();
                    if (!data.sucesso) {
                        notify.error(' ' + (data.erro || "Não foi possível atualizar a demanda."));
                        botaoSalvar.disabled = false;
                        return;
                    }

                    fechar();
                    await carregarDemandas();
                    mostrarConclusaoDemandas();
                } catch (error) {
                    console.error("Erro ao atualizar demanda:", error);
                    notify.error("Erro de conexão com o servidor.");
                    botaoSalvar.disabled = false;
                }
            };

            modalContainer.classList.remove("hidden");
            shadowSecDemandas.classList.remove("hidden");
            bloquearRolagemModal();
        }

        function criarModalExclusao(demanda, shadow = shadowDashBoardADM) {
            const modalExclusao = document.createElement("div");

            modalExclusao.innerHTML = `
                <div class="w-screen flex justify-center">
                    <div id="modalExclusao" class="bg-[var(--white)] w-[90%] sm:w-[420px] pb-8 z-[200] rounded-lg p-5 flex flex-col gap-5 items-center">
                        <i class="fa-regular fa-circle-xmark text-4xl text-[var(--red1)]"></i>
                        <p class="text-[var(--gray2)]">Excluir Demanda?</p>
                        <div class="flex items-center w-full space-x-2">
                            <button type="button" class="btnCancelarExclusao py-3 text-[#121212] w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center bg-[var(--white-gray)] hover:bg-gray-200 transition ease-in-out duration-300">
                                <p>Cancelar</p>
                            </button>
                            <button type="button" class="btnConfirmarExclusao py-3 text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center bg-[var(--red1)] hover:bg-[var(--red2)] transition ease-in-out duration-300">
                                <i class="fa-solid fa-trash"></i>
                                <p>Excluir</p>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            modalExclusao.querySelector(".btnCancelarExclusao").onclick = () => {
                divModalExclusao.classList.add("hidden");
                divModalExclusao.innerHTML = "";
                shadow?.classList.add("hidden");
                liberarRolagemModal();
            };

            modalExclusao.querySelector(".btnConfirmarExclusao").onclick = async () => {
                const botaoExcluir = modalExclusao.querySelector(".btnConfirmarExclusao");
                botaoExcluir.disabled = true;

                try {
                    const response = await fetch('../controllers/demanda_controller.php?action=excluir_demanda', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id_demanda: demanda.id })
                    });

                    const data = await response.json();

                    if (!data.sucesso) {
                        notify.error((data.erro || 'Não foi possível excluir a demanda.'));
                        botaoExcluir.disabled = false;
                        return;
                    }

                    // Remove imediatamente os cards correspondentes no DOM para melhor UX
                    try {
                        const selector = '[data-demanda-id="' + demanda.id + '"]';
                        const elementos = document.querySelectorAll(selector);
                        elementos.forEach(el => el.remove());
                    } catch (e) {
                        console.warn('Não foi possível remover card localmente:', e);
                    }

                    divModalExclusao.classList.add("hidden");
                    divModalExclusao.innerHTML = "";
                    shadow?.classList.add("hidden");
                    liberarRolagemModal();
                    await carregarDemandas();
                    notify.success('Demanda excluída com sucesso!');
                } catch (error) {
                    console.error("Erro ao excluir demanda:", error);
                    notify.error("Erro de conexão com o servidor.");
                    botaoExcluir.disabled = false;
                }
            };

            return modalExclusao;
        }

        function abrirModalExclusaoDemanda(demanda, shadow = shadowDashBoardADM) {
            if (divModalExclusao.parentElement !== document.body) {
                document.body.appendChild(divModalExclusao);
            }
            divModalExclusao.innerHTML = '';
            divModalExclusao.appendChild(criarModalExclusao(demanda, shadow));
            divModalExclusao.classList.remove('hidden');
            shadow?.classList.remove('hidden');
            bloquearRolagemModal();
        }

        function abrirModalSolicitacaoReabertura(demanda) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay-animated fixed inset-0 z-[300] flex items-center justify-center bg-black/60 p-4';
            modal.innerHTML = `
                <div class="modal-content-animated w-full max-w-lg rounded-xl bg-[var(--white)] p-5 shadow-2xl">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-[var(--gray2)]">Solicitar reabertura</h2>
                        <button type="button" class="btnFecharReabertura h-9 w-9 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">Informe por que a demanda <strong>${escaparTextoAdm(demanda.titulo || '')}</strong> deve ser reaberta.</p>
                    <textarea class="campoJustificativaReabertura mt-4 min-h-28 w-full resize-y rounded-lg border border-gray-200 bg-[var(--suave-green)] p-3 text-sm outline-none focus:border-[var(--dark-green)]" maxlength="1000" placeholder="Justificativa da reabertura" required></textarea>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btnCancelarReabertura rounded-lg bg-gray-200 px-4 py-2.5 font-semibold text-gray-700 hover:bg-gray-300">Cancelar</button>
                        <button type="button" class="btnEnviarReabertura rounded-lg bg-[var(--dark-green)] px-4 py-2.5 font-semibold text-white hover:bg-[var(--dark-green2)]">Enviar solicitação</button>
                    </div>
                </div>`;

            const fechar = () => {
                modal.remove();
                liberarRolagemModal();
            };
            const enviar = async () => {
                const campo = modal.querySelector('.campoJustificativaReabertura');
                const botao = modal.querySelector('.btnEnviarReabertura');
                const justificativa = campo.value.trim();
                if (!justificativa) {
                    notify.warning('Informe uma justificativa para solicitar a reabertura.');
                    campo.focus();
                    return;
                }

                botao.disabled = true;
                try {
                    const response = await fetch('../controllers/demanda_controller.php?action=solicitar_reabertura', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ id_demanda: demanda.id, justificativa })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Não foi possível solicitar a reabertura.');
                    fechar();
                    notify.success(data.message || 'Solicitação de reabertura enviada.');
                } catch (error) {
                    notify.error(error.message || 'Erro ao solicitar a reabertura.');
                    botao.disabled = false;
                }
            };

            modal.querySelector('.btnFecharReabertura').onclick = fechar;
            modal.querySelector('.btnCancelarReabertura').onclick = fechar;
            modal.querySelector('.btnEnviarReabertura').onclick = enviar;
            modal.addEventListener('click', (event) => {
                if (event.target === modal) fechar();
            });
            document.body.appendChild(modal);
            bloquearRolagemModal();
            modal.querySelector('.campoJustificativaReabertura').focus();
        }

        function abrirModalReaberturaAdministrativa(demanda) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay-animated fixed inset-0 z-[300] flex items-center justify-center bg-black/60 p-4';
            modal.innerHTML = `
                <div class="modal-content-animated w-full max-w-md rounded-xl bg-[var(--white)] p-5 shadow-2xl">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-[var(--gray2)]">Reabrir demanda</h2>
                        <button type="button" class="btnFecharReaberturaAdmin h-9 w-9 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">Defina o novo prazo para <strong>${escaparTextoAdm(demanda.titulo || '')}</strong>.</p>
                    <input type="date" class="campoPrazoReaberturaAdmin mt-4 w-full rounded-lg border border-gray-200 bg-[var(--suave-green)] p-3 outline-none focus:border-[var(--dark-green)]" required>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btnCancelarReaberturaAdmin rounded-lg bg-gray-200 px-4 py-2.5 font-semibold text-gray-700 hover:bg-gray-300">Cancelar</button>
                        <button type="button" class="btnConfirmarReaberturaAdmin rounded-lg bg-[var(--dark-green)] px-4 py-2.5 font-semibold text-white hover:bg-[var(--dark-green2)]">Reabrir demanda</button>
                    </div>
                </div>`;
            const fechar = () => {
                modal.remove();
                liberarRolagemModal();
            };
            const prazo = modal.querySelector('.campoPrazoReaberturaAdmin');
            const confirmar = modal.querySelector('.btnConfirmarReaberturaAdmin');
            const enviar = async () => {
                if (!prazo.value) {
                    notify.warning('Informe o novo prazo da demanda.');
                    prazo.focus();
                    return;
                }
                confirmar.disabled = true;
                try {
                    const response = await fetch('../controllers/demanda_controller.php?action=reabrir_demanda', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ id_demanda: demanda.id, prazo: prazo.value })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.sucesso) throw new Error(data.erro || 'Não foi possível reabrir a demanda.');
                    fechar();
                    await carregarDemandas();
                    notify.success(data.message || 'Demanda reaberta.');
                } catch (error) {
                    notify.error(error.message || 'Erro ao reabrir a demanda.');
                    confirmar.disabled = false;
                }
            };
            modal.querySelector('.btnFecharReaberturaAdmin').onclick = fechar;
            modal.querySelector('.btnCancelarReaberturaAdmin').onclick = fechar;
            confirmar.onclick = enviar;
            modal.addEventListener('click', (event) => {
                if (event.target === modal) fechar();
            });
            document.body.appendChild(modal);
            bloquearRolagemModal();
            prazo.focus();
        }

        function criarModalAssumir(demanda, confirmar) {
            const modalAssumir = document.createElement("div");

            modalAssumir.innerHTML = `
                <div id="modalAssumir" class="bg-[var(--white)] w-[90%] sm:w-[420px] pb-8 z-[200] rounded-lg p-5 flex flex-col gap-5 items-center">
                    <i class="fa-solid fa-hand text-4xl text-[var(--dark-green)]"></i>
                    <p class="text-[var(--gray2)] text-center">Deseja assumir esta demanda?</p>
                    <div class="flex items-center w-full space-x-2">
                        <button type="button" class="btnCancelarAssumir py-3 text-[#121212] w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center bg-[var(--white-gray)] hover:bg-gray-200 transition ease-in-out duration-300">
                            <p>Cancelar</p>
                        </button>
                        <button type="button" class="btnConfirmarAssumir py-3 text-white w-full rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center bg-[var(--dark-green)] hover:bg-[var(--dark-green2)] transition ease-in-out duration-300">
                            <i class="fa-solid fa-hand"></i>
                            <p>Assumir</p>
                        </button>
                    </div>
                </div>
            `;

            const fechar = () => {
                divModalAssumir.classList.add("hidden");
                divModalAssumir.innerHTML = "";
                shadowDashBoardADM.classList.add("hidden");
                liberarRolagemModal();
            };

            modalAssumir.querySelector(".btnCancelarAssumir").onclick = fechar;
            modalAssumir.querySelector(".btnConfirmarAssumir").onclick = async () => {
                const botaoConfirmar = modalAssumir.querySelector(".btnConfirmarAssumir");
                botaoConfirmar.disabled = true;
                const sucesso = await confirmar();
                if (!sucesso) botaoConfirmar.disabled = false;
            };

            return modalAssumir;
        }

        function viewDemandaUserProject(usuario, projeto) {
            const usuarioNormalizado = String(usuario || '').trim().toLowerCase();
            return usuarioNormalizado === 'administrador' || usuarioNormalizado === 'master';
        };

        let demandas = [];

        // ==================== GRÁFICOS DE RELATÓRIOS ====================
        let donutChart = null;
        let requisicaoGraficos = 0;

        async function carregarDadosGraficos() {
            const requisicaoAtual = ++requisicaoGraficos;
            const projetoDaRequisicao = filtroProjetoAtual;
            try {
                const filtroProjeto = projetoDaRequisicao !== 'todos' ? '&id_projeto=' + encodeURIComponent(projetoDaRequisicao) : '';
                const [dadosDonut, dadosCategoria] = await Promise.all([
                    buscarJsonAdm('../controllers/relatorios_api.php?action=demandas_por_situacao' + filtroProjeto),
                    buscarJsonAdm('../controllers/relatorios_api.php?action=demandas_por_categoria' + filtroProjeto)
                ]);

                if (requisicaoAtual !== requisicaoGraficos || projetoDaRequisicao !== filtroProjetoAtual) return;

                if (dadosDonut.sucesso) {
                    renderizarGraficoDonut(dadosDonut);
                }

                if (dadosCategoria.sucesso) {
                    renderizarGraficoCategoria(dadosCategoria);
                }

                return true;
            } catch (erro) {
                console.error('Erro ao carregar dados dos gráficos:', erro);
                return true;
            }
        }

        function getCorBordaDonut() {
            return document.documentElement.getAttribute('data-theme') === 'dark'
                ? '#171717'
                : '#ffffff';
        }

        function renderizarGraficoDonut(dados) {
            const ctx = document.getElementById('donutChart');
            if (!ctx) return;

            const labels = dados.dados.map(d => d.label);
            const valores = dados.dados.map(d => d.total);
            const cores = dados.dados.map(d => d.cor);

            // Destruir gráfico anterior se existir
            if (donutChart) {
                donutChart.destroy();
            }

            // Atualizar total
            document.getElementById('donutTotal').textContent = dados.total.toLocaleString('pt-BR');

            // Renderizar legenda
            const legendContainer = document.getElementById('donutLegend');
            legendContainer.innerHTML = '';

            dados.dados.forEach((item) => {
                const percentual = dados.total > 0 ? ((item.total / dados.total) * 100).toFixed(1) : 0;
                const li = document.createElement('div');
                li.className = 'legend-item';
                li.innerHTML = `
                    <span class="legend-label">
                        <span class="legend-dot" data-cor="${item.cor}"></span>${item.label}
                    </span>
                    <span class="legend-value">${item.total.toLocaleString('pt-BR')} (${percentual}%)</span>
                `;
                li.querySelector('.legend-dot').style.backgroundColor = item.cor;
                legendContainer.appendChild(li);
            });

            // Criar novo gráfico
            donutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: valores,
                        backgroundColor: cores,
                        borderColor: getCorBordaDonut(),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        const observerTemaDonut = new MutationObserver((mutations) => {
            const alterouTema = mutations.some((mutation) => mutation.attributeName === 'data-theme');
            if (!alterouTema || !donutChart) return;

            donutChart.data.datasets[0].borderColor = getCorBordaDonut();
            donutChart.update();
        });

        observerTemaDonut.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });

        function formatarNomeCategoria(categoria) {
            // Converter underscores em espaços e capitalizar cada palavra
            return categoria
                .replace(/_/g, ' ')
                .toLowerCase()
                .split(' ')
                .map(palavra => palavra.charAt(0).toUpperCase() + palavra.slice(1))
                .join(' ');
        }

        function renderizarGraficoCategoria(dados) {
            const progressList = document.getElementById('progressList');
            if (!progressList) return;

            progressList.innerHTML = '';

            dados.dados.forEach((item, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'progress-item';
                
                // Usar categoria já formatada da API, com fallback para formatação local
                const categoriaNome = item.categoria || formatarNomeCategoria(item.categoria_original || '');
                
                itemDiv.innerHTML = `
                    <div>
                        <div class="progress-head">
                            <span>${categoriaNome}</span>
                            <span>${item.total} (${item.percentual}%)</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 0%"></div>
                        </div>
                    </div>
                `;

                progressList.appendChild(itemDiv);

                // Animar a barra de progresso
                setTimeout(() => {
                    const progressFill = itemDiv.querySelector('.progress-fill');
                    if (progressFill) {
                        progressFill.style.transition = 'width 0.8s ease-out';
                        progressFill.style.width = item.percentual + '%';
                    }
                }, 50 + (index * 100));
            });
        }

        // ==================== FIM GRÁFICOS ====================

        window.addEventListener("DOMContentLoaded", async () => {
            const sections = document.querySelectorAll(".secs-adm");
            sections.forEach((section, index) => {
                section.classList.toggle("hidden", index !== 0);
            });

            // Inicializar filtro
            mostrarEstadoDemandas('carregando');
            await carregarFiltroProjetoSessao();
            atualizarBadgeFiltro();
            await carregarProjetos();
            await Promise.all([
                carregarDemandas(),
                carregarOrientadores(),
                carregarAtendentes()
            ]);
            await carregarDadosGraficos();
        });

        async function carregarOrientadores() {
            try {
                const response = await fetch('../controllers/demanda_controller.php?action=listar_orientadores', {
                    method: 'POST'
                });
                const data = await response.json();

                if (!data.sucesso) {
                    console.error(data.erro || "Não foi possível carregar os orientadores.");
                    return;
                }

                data.dados.forEach((orientador) => {
                    const option = document.createElement("option");
                    option.value = orientador.id;
                    option.textContent = orientador.nome;
                    filtroOrientadorDemanda.appendChild(option);
                });
            } catch (error) {
                console.error("Erro ao carregar orientadores:", error);
            }
        }

        async function carregarAtendentes() {
            if (!usuariosAtribuidosSelect) return;

            try {
                const response = await fetch('../controllers/demanda_controller.php?action=listar_atendentes', { method: 'POST' });
                const data = await response.json();
                if (!data.sucesso) throw new Error(data.erro || 'Não foi possível carregar os usuários.');

                usuariosDisponiveis = data.dados;
                usuariosAtribuidosSelect.innerHTML = '';
                renderizarUsuariosAtribuidos();
            } catch (error) {
                console.error('Erro ao carregar atendentes:', error);
                usuariosAtribuidosSelect.innerHTML = '<option disabled>Não foi possível carregar usuários</option>';
            }
        }

        usuariosAtribuidosBusca?.addEventListener('input', renderizarUsuariosAtribuidos);
        usuariosAtribuidosBusca?.addEventListener('focus', renderizarUsuariosAtribuidos);
        document.addEventListener('click', (event) => {
            if (!usuariosAtribuidosBusca?.parentElement.contains(event.target)) {
                usuariosAtribuidosResultados?.classList.add('hidden');
            }
        });

        function abrirModalInformacoes(demanda, containers = divModalInfs, shadow = shadowDashBoardADM, editavel = false) {
            console.log("Abrindo modal de informações para:", demanda.titulo);

            if (shadow && shadow.parentElement !== document.body) {
                document.body.appendChild(shadow);
            }

            containers.forEach((divModal) => {
                divModal.innerHTML = "";
                const modalContent = criarModalInfs(demanda);
                divModal.appendChild(modalContent);
                divModal.classList.remove("hidden");
            });

            if (editavel) {
                const modal = containers[0].querySelector("#modalInfs");
                modal.classList.add("modo-edicao");
                modal.querySelector(".orientadorSpan")?.closest("li")?.classList.add("campo-somente-leitura");
                modal.querySelector(".situacaoSpan")?.closest("li")?.classList.add("campo-somente-leitura");
                const titulo = modal.querySelector(".tituloEdicao");
                const prioridade = modal.querySelector(".prioridadeEdicao");
                const categoria = modal.querySelector(".categoriaEdicao");
                const prazo = modal.querySelector(".prazoEdicao");
                const descricao = modal.querySelector(".descricaoEdicao");

                titulo.outerHTML = `<input class="campoEdicaoInformacoes w-full bg-transparent outline-none text-[var(--theme-color)]" value="${demanda.titulo.replace(/"/g, '&quot;')}" required>`;
                prioridade.outerHTML = `<select class="campoEdicaoInformacoes w-full bg-transparent outline-none text-[var(--theme-color)]" required>
                    <option value="alta">Alta</option><option value="media">Média</option><option value="baixa">Baixa</option>
                </select>`;
                categoria.outerHTML = `<select class="campoEdicaoInformacoes w-full bg-transparent outline-none text-[var(--theme-color)]" required>
                    <option value="${String(demanda.categoria || '').replace(/"/g, '&quot;')}">${String(demanda.categoria || 'Categoria').replace(/[&<>"']/g, caractere => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[caractere])}</option>
                </select>`;
                const quantidadeAlunos = document.createElement('input');
                quantidadeAlunos.type = 'number';
                quantidadeAlunos.min = '1';
                quantidadeAlunos.max = '10';
                quantidadeAlunos.required = true;
                quantidadeAlunos.className = 'campoEdicaoInformacoes w-full bg-transparent outline-none text-[var(--theme-color)]';
                quantidadeAlunos.value = Number(demanda.quantidade_alunos || 1);
                const quantidadeAlunosItem = document.createElement('li');
                quantidadeAlunosItem.className = 'text-sm flex items-center gap-2 bg-[var(--white-gray)] p-2.5 rounded-lg w-full';
                quantidadeAlunosItem.innerHTML = '<i class="fa-solid fa-users"></i><p class="text-[var(--gray2)]">Usuários necessários:</p>';
                quantidadeAlunosItem.appendChild(quantidadeAlunos);
                modal.querySelector('.prazoEdicao').closest('li').parentElement.appendChild(quantidadeAlunosItem);
                prazo.outerHTML = `<input type="date" class="campoEdicaoInformacoes w-full bg-transparent outline-none text-[var(--theme-color)]" value="${String(demanda.prazo || '').slice(0, 10)}" required>`;
                descricao.outerHTML = `<textarea class="descriDemandaSpan campoEdicaoDescricaoInformacoes w-full bg-transparent outline-none text-[var(--theme-color)] resize-none" rows="8">${demanda.descricao || ''}</textarea>`;

                const campos = modal.querySelectorAll(".campoEdicaoInformacoes");
                campos[1].value = demanda.prioridade;
                campos[2].value = demanda.categoria;
                const carregarCategoriasEdicao = async () => {
                    const categoriaSelect = campos[2];
                    const idProjeto = Number(demanda.id_projeto || 0);

                    if (!idProjeto) {
                        categoriaSelect.innerHTML = '<option value="">Projeto não identificado</option>';
                        categoriaSelect.disabled = true;
                        console.error('Não foi possível carregar categorias: demanda sem id_projeto.', demanda);
                        return;
                    }

                    categoriaSelect.innerHTML = '<option value="">Carregando categorias...</option>';
                    categoriaSelect.disabled = true;

                    try {
                        const response = await fetch(`../controllers/projeto_controller.php?action=listar_categorias&id_projeto=${encodeURIComponent(idProjeto)}`, { cache: 'no-store' });
                        const data = await response.json();
                        if (!response.ok || !data.sucesso) {
                            throw new Error(data.erro || 'Não foi possível carregar as categorias.');
                        }

                        const categorias = Array.isArray(data.dados) ? data.dados : [];
                        categoriaSelect.innerHTML = categorias.length
                            ? categorias.map((item) => `<option value="${escaparTextoAdm(item.nome)}">${escaparTextoAdm(item.nome)}</option>`).join('')
                            : '<option value="">Nenhuma categoria cadastrada</option>';
                        categoriaSelect.value = demanda.categoria || '';
                        categoriaSelect.disabled = false;
                    } catch (error) {
                        categoriaSelect.innerHTML = '<option value="">Não foi possível carregar</option>';
                        console.error('Erro ao carregar categorias da edição:', error);
                    }
                };
                carregarCategoriasEdicao();

                const projeto = document.createElement("div");
                projeto.className = "projeto-somente-leitura flex items-center gap-4 text-sm px-2 py-2 rounded-md";
                projeto.setAttribute("aria-disabled", "true");
                projeto.innerHTML = `<span>Projeto:</span><label><input type="radio" name="projetoEdicaoInformacoes" value="${Number(demanda.id_projeto)}" disabled checked> ${escaparTextoAdm(demanda.projeto || 'Projeto')}</label>`;
                modal.querySelector(".descriDemandaSpan").parentElement.insertAdjacentElement("afterend", projeto);

                const salvar = document.createElement("button");
                salvar.type = "button";
                salvar.className = "btnSalvarInformacoes py-3 bg-[var(--dark-green)] text-white w-[calc(100%-40px)] mx-5 rounded-lg font-semibold text-sm flex items-center space-x-2 justify-center hover:bg-[var(--dark-green2)] transition";
                salvar.innerHTML = '<i class="fa-solid fa-floppy-disk"></i><span>Salvar Alterações</span>';
                modal.appendChild(salvar);
                const projetoSelecionado = projeto.querySelector(`input[value="${demanda.projeto || ''}"]`);
                if (projetoSelecionado) projetoSelecionado.checked = true;

                const atribuicoesAtivas = document.createElement("div");
                atribuicoesAtivas.className = "atribuicoes-ativas-edicao flex flex-col gap-2 text-sm text-gray-600 py-1 border-t border-gray-200 pt-3";
                atribuicoesAtivas.innerHTML = `
                    <p class="font-semibold text-[var(--gray2)]">Usuários vinculados</p>
                    <div class="lista-atribuicoes-ativas flex flex-col gap-2">
                        <p class="text-xs text-gray-500">Carregando usuários...</p>
                    </div>`;
                projeto.insertAdjacentElement("afterend", atribuicoesAtivas);

                const listaAtribuicoes = atribuicoesAtivas.querySelector('.lista-atribuicoes-ativas');
                const carregarAtribuicoesAtivas = async () => {
                    try {
                        const resposta = await fetch(`../controllers/demanda_controller.php?action=listar_atribuicoes_ativas&id_demanda=${encodeURIComponent(demanda.id)}`, {
                            method: 'POST'
                        });
                        const resultado = await resposta.json();
                        if (!resultado.sucesso) throw new Error(resultado.erro || 'Não foi possível carregar os usuários.');

                        listaAtribuicoes.innerHTML = '';
                        if (!resultado.dados.length) {
                            listaAtribuicoes.innerHTML = '<p class="text-xs text-gray-500">Nenhum usuário vinculado.</p>';
                            return;
                        }

                        resultado.dados.forEach((atribuicao) => {
                            const linha = document.createElement('div');
                            linha.className = 'flex items-center justify-between gap-2 rounded-md bg-[var(--white-gray)] px-3 py-2';

                            const nome = document.createElement('span');
                            nome.className = 'truncate';
                            nome.textContent = atribuicao.nome || 'Usuário';

                            const botao = document.createElement('button');
                            botao.type = 'button';
                            botao.className = 'shrink-0 rounded-md border border-red-200 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50';
                            botao.textContent = 'Desvincular';
                            botao.addEventListener('click', async () => {
                                if (!await confirmarAcaoSistema(`Desvincular ${atribuicao.nome || 'este usuário'} da demanda?`, 'demanda')) return;

                                botao.disabled = true;
                                try {
                                    const resposta = await fetch('../controllers/demanda_controller.php?action=desvincular_atribuicao', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({
                                            id_demanda: demanda.id,
                                            id_atribuicao: atribuicao.id
                                        })
                                    });
                                    const resultado = await resposta.json();
                                    if (!resultado.sucesso) throw new Error(resultado.erro || 'Não foi possível desvincular o usuário.');

                                    notify.success(resultado.message || 'Usuário desvinculado da demanda.');
                                    btnFechar?.click();
                                    await carregarDemandas();
                                } catch (erro) {
                                    notify.error(erro.message || 'Erro ao desvincular usuário.');
                                    botao.disabled = false;
                                }
                            });

                            linha.append(nome, botao);
                            listaAtribuicoes.appendChild(linha);
                        });
                    } catch (erro) {
                        listaAtribuicoes.innerHTML = '<p class="text-xs text-red-600">Não foi possível carregar os usuários vinculados.</p>';
                        console.error('Erro ao carregar atribuições ativas:', erro);
                    }
                };
                carregarAtribuicoesAtivas();

                salvar.onclick = async () => {
                    salvar.disabled = true;
                    try {
                        const response = await fetch('../controllers/demanda_controller.php?action=atualizar_demanda', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                id_demanda: demanda.id,
                                titulo: campos[0].value.trim(),
                                prioridade: campos[1].value,
                                categoria: campos[2].value,
                                prazo: campos[3].value,
                                quantidade_alunos: Number(quantidadeAlunos.value),
                                descricao: modal.querySelector('.campoEdicaoDescricaoInformacoes').value.trim(),
                                id_projeto: Number(demanda.id_projeto)
                            })
                        });
                        const data = await response.json();
                        if (!data.sucesso) throw new Error(data.erro || 'Não foi possível salvar as alterações.');
                        btnFechar?.click();
                        mostrarConclusaoDemandas();
                        await carregarDemandas();
                    } catch (error) {
                        notify.error(error.message);
                        salvar.disabled = false;
                    }
                };
            }

            shadow?.classList.remove("hidden");
            bloquearRolagemModal();

            const fechar = () => {
                containers.forEach((divModal) => {
                    divModal.classList.add("hidden");
                    divModal.innerHTML = "";
                    divModal.removeEventListener("click", fecharAoClicarNoFundo);
                });
                shadow?.classList.add("hidden");
                shadow?.removeEventListener("click", fecharAoClicarNaSombra);
                liberarRolagemModal();
            };
            const fecharAoClicarNoFundo = (event) => {
                if (event.target !== event.currentTarget) return;
                event.stopPropagation();
                fechar();
            };
            const fecharAoClicarNaSombra = (event) => {
                event.stopPropagation();
                fechar();
            };

            containers.forEach((divModal) => divModal.addEventListener("click", fecharAoClicarNoFundo));
            shadow?.addEventListener("click", fecharAoClicarNaSombra);

            const btnFechar = containers[0]?.querySelector(".btnFecharModalInfs");
            if (btnFechar) {
                btnFechar.onclick = fechar;
            }

            const contextoValidacao = shadow === shadowSecUsuarios ? 'usuarios' : 'demandas';
            const validacao = containers[0]?.querySelector('.validacao-justificativa');
            validacao?.querySelector('.btnValidarJustificativa')?.addEventListener('click', () => {
                btnFechar?.click();
                abrirModalValidarJustificativa(demanda, contextoValidacao);
            });
            validacao?.querySelector('.btnInvalidarJustificativa')?.addEventListener('click', () => {
                btnFechar?.click();
                abrirModalInvalidarJustificativa(demanda, contextoValidacao);
            });
        };

        function abrirModalConfirmacaoJustificativa(demanda, decisao, modal, shadow) {
            if (!modal) return;
            const pontos = getPontosPrioridade(demanda.prioridade);
            const validar = decisao === 'validada';
            modal.innerHTML = `
                <div class="bg-[var(--white)] w-[90%] sm:w-[440px] rounded-lg p-5 flex flex-col gap-5 items-center shadow-2xl">
                    <i class="fa-solid ${validar ? 'fa-circle-check' : 'fa-triangle-exclamation'} text-4xl ${validar ? 'text-[var(--dark-green)]' : 'text-[var(--red1)]'}"></i>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-[var(--gray2)]">${validar ? 'Validar justificativa?' : 'Invalidar justificativa?'}</h3>
                        <p class="text-sm text-gray-600 mt-2">${validar ? 'O usuário não ganhará nem perderá pontos.' : `O usuário perderá ${pontos} ponto(s).`}</p>
                    </div>
                    <div class="flex items-center w-full gap-2">
                        <button type="button" class="btnCancelarValidacao py-3 text-[#121212] w-full rounded-lg font-semibold text-sm bg-[var(--white-gray)] hover:bg-gray-200 transition">Cancelar</button>
                        <button type="button" class="btnConfirmarValidacao py-3 text-white w-full rounded-lg font-semibold text-sm ${validar ? 'bg-[var(--dark-green)] hover:bg-[var(--dark-green2)]' : 'bg-[var(--red1)] hover:bg-[var(--red2)]'} transition">${validar ? 'Validar' : 'Invalidar'}</button>
                    </div>
                </div>`;
            modal.classList.remove('hidden');
            shadow?.classList.remove('hidden');
            bloquearRolagemModal();

            const fechar = () => {
                modal.classList.add('hidden');
                modal.innerHTML = '';
                shadow?.classList.add('hidden');
                liberarRolagemModal();
            };
            modal.querySelector('.btnCancelarValidacao').onclick = fechar;
            modal.querySelector('.btnConfirmarValidacao').onclick = async () => {
                modal.querySelector('.btnConfirmarValidacao').disabled = true;
                fechar();
                await processarValidacaoJustificativa(demanda, decisao);
            };
        }

        function abrirModalValidarJustificativa(demanda, contexto = 'demandas') {
            const usuarios = contexto === 'usuarios';
            abrirModalConfirmacaoJustificativa(
                demanda,
                'validada',
                document.getElementById(usuarios ? 'divModalValidarJustificativaUsuarios' : 'divModalValidarJustificativaDemandas'),
                usuarios ? shadowSecUsuarios : shadowSecDemandas
            );
        }

        function abrirModalInvalidarJustificativa(demanda, contexto = 'demandas') {
            const usuarios = contexto === 'usuarios';
            abrirModalConfirmacaoJustificativa(
                demanda,
                'invalidada',
                document.getElementById(usuarios ? 'divModalInvalidarJustificativaUsuarios' : 'divModalInvalidarJustificativaDemandas'),
                usuarios ? shadowSecUsuarios : shadowSecDemandas
            );
        }

        async function processarValidacaoJustificativa(demanda, decisao) {
            try {
                const response = await fetch('../controllers/demanda_controller.php?action=validar_justificativa', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_demanda: demanda.id, decisao })
                });
                const data = await response.json();
                if (!data.sucesso) throw new Error(data.erro || 'Não foi possível processar a justificativa.');
                notify.success(data.message || 'Justificativa processada.');
                await carregarDemandas();
            } catch (error) {
                notify.error(error.message || 'Erro ao processar a justificativa.');
            }
        }

        function mostrarModalConclusao(mensagem) {
            if (!divModalConclusao) return;
            const p = divModalConclusao.querySelector("p");
            if (p) p.textContent = mensagem;

            divModalConclusao.classList.remove("hidden");
            shadowDashBoardADM.classList.remove("hidden");
            bloquearRolagemModal();
        }

        function renderizarValidacoesJustificativas(listaDemandas) {
            const secao = document.getElementById('validacoesJustificativas');
            const lista = document.getElementById('listaValidacoesJustificativas');
            const contador = document.getElementById('contadorValidacoesJustificativas');
            if (!secao || !lista) return;

            const pendentes = listaDemandas.filter((demanda) =>
                String(demanda.status || '').toLowerCase() === 'finalizada'
                && String(demanda.situacao || '').toLowerCase() === 'nao_concluida'
                && (demanda.justificativa_validacao_status || 'pendente') === 'pendente'
                && Number(demanda.orientador_id) === usuarioAtualId
            );

            lista.innerHTML = '';
            secao.classList.toggle('hidden', pendentes.length === 0);
            if (contador) contador.textContent = `${pendentes.length} pendente(s)`;
            pendentes.forEach((demanda) => {
                const card = document.createElement('article');
                const classePrioridadeCard = `card-prioridade-${demanda.prioridade}`;
                const classeLinhaPrioridade = `linha-prioridade-${demanda.prioridade}`;
                const corBordaPrioridade = { alta: '#dc3030', media: '#d6900d', baixa: '#2b6cb0' }[demanda.prioridade] || '#F9A304';
                card.className = `h-full min-h-[300px] w-full ${classePrioridadeCard} border border-gray-100 border-l-4 rounded-xl p-5 shadow-sm hover:-translate-y-0.5 hover:shadow-md transition ease-in-out duration-300 flex flex-col`;
                card.style.borderLeftColor = corBordaPrioridade;
                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <i class="fa-solid fa-clipboard-list shrink-0 text-[var(--gray2)]"></i>
                            <div class="min-w-0">
                                <p class="text-[10px] uppercase tracking-[0.12em] text-yellow-700 font-semibold">Justificativa pendente</p>
                                <h4 class="tituloDemanda text-[var(--gray2)] font-semibold leading-snug line-clamp-2 mt-1">${escaparTextoAdm(demanda.titulo)}</h4>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full bg-yellow-100 text-yellow-800 px-2.5 py-1 text-xs font-semibold">Pendente</span>
                    </div>
                    <div class="mt-5 space-y-2.5 text-sm">
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-triangle-exclamation pr-1"></i> Prioridade: <span class="font-semibold">${prioridadeLabel[demanda.prioridade] || demanda.prioridade}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-user pr-1"></i> Assumida por: <span class="font-semibold text-[var(--gray2)]">${escaparTextoAdm(demanda.responsavel_nome || 'Usuário não identificado')}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-comment-dots pr-1"></i> Justificativa: <span class="text-[var(--gray2)]">${escaparTextoAdm(demanda.justificativa_nao_conclusao || 'Sem justificativa informada.')}</span></p>
                        <p class="text-[var(--gray1)] ${classeLinhaPrioridade} p-2 rounded-md"><i class="fa-solid fa-star pr-1"></i> Valor: <span class="font-semibold">${getPontosPrioridade(demanda.prioridade)} ponto(s)</span></p>
                    </div>
                    <div class="flex items-center gap-2 pt-5 mt-auto w-full">
                        <button type="button" class="btnVerJustificativa ${classeLinhaPrioridade} text-[var(--gray1)] font-semibold h-10 flex-1 justify-center rounded-lg text-sm flex items-center gap-2 hover:brightness-95 transition" aria-label="Ver demanda" title="Ver demanda"><span class="hidden sm:inline">Ver</span><i class="fa-solid fa-eye"></i></button>
                        <button type="button" class="btnValidarLista bg-[var(--dark-green)] text-white font-semibold h-10 flex-1 justify-center rounded-lg text-sm flex items-center gap-2 hover:bg-[var(--dark-green2)] transition" aria-label="Validar justificativa" title="Validar justificativa"><span class="hidden sm:inline">Validar</span><i class="fa-solid fa-check"></i></button>
                        <button type="button" class="btnInvalidarLista bg-[var(--red1)] text-white font-semibold h-10 flex-1 justify-center rounded-lg text-sm flex items-center gap-2 hover:bg-[var(--red2)] transition" aria-label="Invalidar justificativa" title="Invalidar justificativa"><span class="hidden sm:inline">Invalidar</span><i class="fa-solid fa-xmark"></i></button>
                    </div>`;
                card.querySelector('.btnVerJustificativa').onclick = () => {
                    const modalValidacao = document.getElementById('modalDetalhesValidacao');
                    if (modalValidacao) abrirModalInformacoes(demanda, [modalValidacao], shadowSecUsuarios);
                };
                card.querySelector('.btnValidarLista').onclick = () => abrirModalValidarJustificativa(demanda, 'usuarios');
                card.querySelector('.btnInvalidarLista').onclick = () => abrirModalInvalidarJustificativa(demanda, 'usuarios');
                lista.appendChild(card);
            });
        }

        
        function atualizarVisibilidadeSecoesDashboard() {
            const secaoAberto = document.getElementById('colunaDashboardEmAberto');
            const secaoAndamento = document.getElementById('colunaDashboardEmAndamento');

            const existeAberto = Boolean(divDemandasDisponiveis?.children.length);
            const existeAndamento = Boolean(divDemandasAndamento?.children.length);

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

                const secaoDashboard = coluna.closest('.secs-adm');
                if (secaoDashboard && getComputedStyle(secaoDashboard).display === 'none') return;

                const lista = coluna.querySelector(':scope > div > div');
                const quantidade = lista?.children.length || 0;
                if (!lista || quantidade === 0) {
                    coluna.style.height = '';
                    lista?.parentElement?.style.removeProperty('height');
                    lista?.parentElement?.style.removeProperty('max-height');
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

        function cardEstadoDemandas(tipo) {
            if (tipo === 'erro') {
                return '<div class="w-full col-span-full text-center text-gray-500 text-sm bg-white rounded-xl border border-gray-100 shadow-sm p-8"><i class="fa-solid fa-triangle-exclamation text-red-500 text-xl mb-3"></i><p class="font-semibold text-[var(--gray2)]">Não foi possível carregar as demandas.</p><p class="mt-1">Tente novamente em alguns instantes.</p></div>';
            }

            return '<div class="w-full col-span-full text-center text-gray-500 text-sm bg-white rounded-xl border border-gray-100 shadow-sm p-8"><i class="fa-solid fa-spinner fa-spin text-[var(--dark-green)] text-xl mb-3"></i><p class="font-semibold text-[var(--gray2)]">Carregando demandas...</p></div>';
        }

        function mostrarEstadoDemandas(tipo) {
            const card = cardEstadoDemandas(tipo);
            if (divDemandasDisponiveis) divDemandasDisponiveis.innerHTML = card;
            if (divDemandasAndamento) divDemandasAndamento.innerHTML = card;
            if (divGeralDemandas) divGeralDemandas.innerHTML = card;

            const msgDashboard = document.getElementById('msgNenhumaDemandaDashboard');
            if (msgDashboard) {
                msgDashboard.innerHTML = '';
                msgDashboard.classList.add('hidden');
                msgDashboard.style.display = 'none';
            }
        }

        function renderizarDemandas() {
            // Atualizar variáveis globais (não declarar com let para usar as globais)
            quantDisponiveis = 0;
            quantAndamento = 0;
            quantFinalizadas = 0;

            if (divDemandasDisponiveis) divDemandasDisponiveis.innerHTML = "";
            if (divDemandasAndamento) divDemandasAndamento.innerHTML = "";
            if (divGeralDemandas) divGeralDemandas.innerHTML = "";
            atualizarVisibilidadeSecoesDashboard();
            renderizarValidacoesJustificativas(demandas);

            demandas.forEach((demanda) => {
                const statusNormalizado = String(demanda.status || '')
                    .trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/\s+/g, '_')
                    .replace('disponivel', 'aberta');

                // CONTAGEM
                if (statusNormalizado === "aberta") quantDisponiveis++;
                else if (statusNormalizado === "em_andamento") quantAndamento++;
                else if (statusNormalizado === "finalizada") quantFinalizadas++;

                if (statusNormalizado === 'finalizada') {
                    return;
                }

                // CARD DASHBOARD
                const cardDashboard = criarCardDemandaDashboard(demanda);

                if (viewDemandaUserProject(usuario, demanda.projeto)) {
                    // Adicionar ao container correto baseado no status
                    if (statusNormalizado === "aberta") {
                        divDemandasDisponiveis.appendChild(cardDashboard);
                    } else if (statusNormalizado === "em_andamento") {
                        divDemandasAndamento.appendChild(cardDashboard);
                    }

                    // Botão Ver
                    cardDashboard.querySelector(".btnVer")?.addEventListener("click", () => {
                        abrirModalInformacoes(demanda);
                    });

                    atualizarVisibilidadeSecoesDashboard();

                    const btnAssumir = cardDashboard.querySelector(".btnAssumir");
                    if (btnAssumir) {
                        btnAssumir.onclick = () => {
                            const modalAssumirContent = criarModalAssumir(demanda, async () => {
                                const action = statusNormalizado === 'aberta' ? 'assumir_demanda' : 'concluir_demanda';

                                try {
                                    const response = await fetch(`../controllers/demanda_controller.php?action=${action}`, {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({
                                            id_demanda: demanda.id,
                                            data_fim: demanda.prazo || new Date().toISOString()
                                        })
                                    });

                                    const data = await response.json();
                                    if (!data.sucesso) {
                                        notify.error(' ' + (data.erro || 'Não foi possível executar a ação na demanda.'));
                                        return false;
                                    }

                                    divModalAssumir.classList.add("hidden");
                                    divModalAssumir.innerHTML = "";
                                    shadowDashBoardADM.classList.add("hidden");
                                    liberarRolagemModal();
                                    await carregarDemandas();
                                    if (action === 'assumir_demanda') {
                                        notify.success('👆 Demanda assumida com sucesso!');
                                    } else {
                                        notify.success(' Demanda concluída com sucesso!');
                                    }
                                    return true;
                                } catch (error) {
                                    console.error('Erro ao processar ação da demanda:', error);
                                    notify.error(' Erro de conexão com o servidor.');
                                    return false;
                                }
                            });

                            divModalAssumir.innerHTML = "";
                            divModalAssumir.appendChild(modalAssumirContent);
                            divModalAssumir.classList.remove("hidden");
                            shadowDashBoardADM.classList.remove("hidden");
                            bloquearRolagemModal();
                        };
                    }

                    const btnEditarDashboard = cardDashboard.querySelector(".btnEditar");
                    if (btnEditarDashboard) {
                        btnEditarDashboard.classList.toggle("hidden", usuario !== 'master' && Number(demanda.orientador_id) !== usuarioAtualId);
                        btnEditarDashboard.onclick = () => {
                            abrirModalInformacoes(demanda, divModalInfs, shadowDashBoardADM, true);
                        };
                    }

                    // Botão Excluir
                    const btnExcluir = cardDashboard.querySelector(".btnExcluir");
                    if (btnExcluir) {
                        if (!['administrador', 'master'].includes(String(usuario || '').toLowerCase())) {
                            btnExcluir.classList.add("hidden");
                        }
                        btnExcluir.onclick = () => {
                            divModalExclusao.innerHTML = "";
                            const modalExclusaoContent = criarModalExclusao(demanda);
                            divModalExclusao.appendChild(modalExclusaoContent);
                            divModalExclusao.classList.remove("hidden");
                            shadowDashBoardADM.classList.remove("hidden");
                            bloquearRolagemModal();
                        };
                    };

                }

            });

            const demandasFiltradas = obterDemandasFiltradas();
            divGeralDemandas.classList.toggle('px-4', demandasFiltradas.length > 0);
            demandasFiltradas.forEach((demanda) => {
                // CARD NA SEÇÃO GERAL
                const cardGeral = criarCardDemandaDemandas(demanda);
                divGeralDemandas.appendChild(cardGeral);

                cardGeral.querySelector(".btnVer")?.addEventListener("click", () => {
                    abrirModalInformacoes(demanda, divModalInfsSecDemandas, shadowSecDemandas);
                });

                cardGeral.querySelector(".btnEditar")?.addEventListener("click", () => {
                    abrirModalInformacoes(demanda, divModalInfsSecDemandas, shadowSecDemandas, true);
                });

                const btnExcluirHistorico = document.createElement('button');
                btnExcluirHistorico.type = 'button';
                btnExcluirHistorico.className = 'btnExcluirHistorico absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-700 transition hover:bg-red-100';
                btnExcluirHistorico.setAttribute('aria-label', 'Excluir demanda');
                btnExcluirHistorico.title = 'Excluir demanda';
                btnExcluirHistorico.innerHTML = '<i class="fa-solid fa-trash"></i>';
                const cardVisual = cardGeral.firstElementChild;
                if (cardVisual) {
                    cardVisual.classList.add('relative');
                    cardVisual.appendChild(btnExcluirHistorico);
                }
                btnExcluirHistorico.classList.toggle('hidden', !['administrador', 'master'].includes(String(usuario || '').toLowerCase()) || Number(demanda.orientador_id) !== usuarioAtualId);
                btnExcluirHistorico.addEventListener('click', () => abrirModalExclusaoDemanda(demanda, shadowSecDemandas));

                if (String(demanda.situacao || '').toLowerCase() === 'nao_concluida') {
                    const btnReabrir = document.createElement('button');
                    btnReabrir.type = 'button';
                    btnReabrir.className = 'btnReabrirDemanda bg-[var(--accent-yellow)] text-[#121212] font-semibold h-10 flex-1 justify-center rounded-lg text-sm flex items-center gap-2 hover:brightness-95 transition';
                    btnReabrir.innerHTML = '<span>Reabrir</span><i class="fa-solid fa-rotate-left"></i>';
                    btnReabrir.title = 'Solicitar reabertura';
                    cardGeral.querySelector('.flex.items-center.gap-2.pt-5')?.appendChild(btnReabrir);
                    btnReabrir.addEventListener('click', () => abrirModalReaberturaAdministrativa(demanda));
                }
            });

            if (demandasFiltradas.length === 0) {
                divGeralDemandas.innerHTML = cardSemDemandas();
            }

            // Mensagem no Dashboard quando não houver demandas visíveis
            const msgDashboard = document.getElementById('msgNenhumaDemandaDashboard');
            if (msgDashboard) {
                const quantidadeCardsDashboard = (divDemandasDisponiveis?.childElementCount || 0)
                    + (divDemandasAndamento?.childElementCount || 0);

                if (quantidadeCardsDashboard === 0) {
                    msgDashboard.innerHTML = cardSemDemandas();
                    msgDashboard.style.display = 'block';
                } else {
                    msgDashboard.innerHTML = '';
                    msgDashboard.style.display = '';
                }
            }

            // Atualiza contadores
            if (quantDisponiveisViewAdm) quantDisponiveisViewAdm.textContent = quantDisponiveis;
            if (quantAndamentoViewAdm) quantAndamentoViewAdm.textContent = quantAndamento;
            if (quantFinalizadasViewAdm) quantFinalizadasViewAdm.textContent = quantFinalizadas;
            ajustarAlturaColunasDashboard();
            requestAnimationFrame(ajustarAlturaColunasDashboard);
        }
    

        btnCriarDemanda.onclick = async () => {
            console.log("Botão 'Criar Demanda' foi clicado!", "color: green; font-weight: bold");

            const titulo = tituloDemandaInput.value.trim();
            const prioridade = prioridadeDemandaSelect.value.trim();
            const categoria = categoriaDemandaSelect.value.trim();
            const prazo = prazoDemanda.value.trim();
            const descricao = descriDemandaTextArea.value.trim();
            const quantidadeAlunos = Number(quantidadeAlunosDemanda?.value || 1);

            const projetoSelecionado = document.getElementById('projetoDemandaSelect');
            const idProjeto = projetoSelecionado ? Number(projetoSelecionado.value) : 0;
            const usuariosAtribuidos = Array.from(usuariosAtribuidosSelect?.selectedOptions || []).map((option) => Number(option.value));

            // Debug completo
            console.table({
                titulo,
                prioridade,
                categoria,
                prazo,
                descricao,
                idProjeto,
                usuariosAtribuidos
            });

            if (!titulo || !prioridade || !categoria || !prazo || !idProjeto) {
                notify.warning('Por favor, preencha todos os campos obrigatórios!');
                return;
            }

            let demandaCriada = false;

            try {
                console.log("Enviando requisição para criar demanda...");

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
                        id_projeto: idProjeto,
                        quantidade_alunos: quantidadeAlunos,
                        usuarios_atribuidos: usuariosAtribuidos
                    })
                });

                console.log("Status HTTP:", response.status);

                const data = await response.json();
                console.log("Resposta do servidor:", data);

                if (data.sucesso) {
                    demandaCriada = true;
                    console.log('Demanda criada com ID:', data.id);
                    
                    try {
                        console.log('Iniciando carregamento de demandas...');
                        
                        await carregarDemandas();
                        console.log('Demandas carregadas com sucesso');
                    } catch (loadError) {
                        if (demandas.length > 0) {
                            console.error('Erro ao carregar demandas após criação:', loadError);
                        }
                        // Mesmo com erro ao carregar, demanda foi criada - continua normalmente
                    }

                    // Fecha modal de criar
                    fecharModalCriarDemanda();
                    resetFormDemanda();

                    notify.success('Demanda criada com sucesso!');

                } else {
                    notify.error(' ' + (data.erro || "Não foi possível criar a demanda"));
                }

            } catch (error) {
                console.error("Erro na requisição:", error);
                notify.error(' Erro ao conectar com o servidor. Por favor tente novamente.');
                
                // Se demanda foi criada mas houve erro ao recarregar, fecha modal mesmo assim
                if (demandaCriada) {
                    fecharModalCriarDemanda();
                }
            }
        };

        btnConclusao.onclick = () => {
            document.getElementById("divModalConclusao").classList.add("hidden");
            shadowDashBoardADM.classList.add("hidden");
            liberarRolagemModal();
            document.querySelector("form").reset();
            document.getElementById("descriDemandaTextArea").value = "";
        };

        // ==================== RELATÓRIO PERSONALIZADO ====================

        const shadowRelatorioPersonalizado = document.getElementById("shadowRelatorioPersonalizado");
        const divModalRelatorioPersonalizado = document.getElementById("divModalRelatorioPersonalizado");
        const btnRelatorioPersonalizado = document.getElementById("btnRelatorioPersonalizado");
        const btnFecharRelatorioPersonalizado = document.getElementById("btnFecharRelatorioPersonalizado");
        const btnLimparFiltros = document.getElementById("btnLimparFiltros");
        const btnGerarRelatorioPersonalizado = document.getElementById("btnGerarRelatorioPersonalizado");
        const formRelatorioPersonalizado = document.getElementById("formRelatorioPersonalizado");
        const btnRelatorioSituacao = document.querySelectorAll(".btnRelatorioSituacao");

        // Abrir modal de relatório personalizado
        btnRelatorioPersonalizado.onclick = async () => {
            if (!await verificarProjetosAntesDeAbrir('relatorio')) return;
            divModalRelatorioPersonalizado.classList.remove("hidden");
            shadowRelatorioPersonalizado.classList.remove("hidden");
            bloquearRolagemModal();
            carregarCategoriasFiltroRelatorio();
            carregarProjetosFiltroRelatorio();
        };

        // Fechar modal de relatório personalizado
        btnFecharRelatorioPersonalizado.onclick = () => {
            divModalRelatorioPersonalizado.classList.add("hidden");
            shadowRelatorioPersonalizado.classList.add("hidden");
            liberarRolagemModal();
        };

        // Fechar ao clicar no shadow
        shadowRelatorioPersonalizado.onclick = () => {
            btnFecharRelatorioPersonalizado.click();
        };

        // Limpar filtros
        btnLimparFiltros.onclick = () => {
            formRelatorioPersonalizado.reset();
            document.querySelectorAll("#formRelatorioPersonalizado input[type='checkbox']").forEach(input => {
                input.checked = false;
            });
            document.getElementById("filtroCategoria").selectedIndex = 0;
            document.getElementById("filtroProjetoRelatorio").selectedIndex = 0;
            carregarCategoriasFiltroRelatorio('');
            document.querySelector("input[name='filtroDataInicio']").value = "";
            document.querySelector("input[name='filtroDataFim']").value = "";
        };

        // Gerar relatório personalizado
        btnGerarRelatorioPersonalizado.onclick = async (e) => {
            e.preventDefault();

            // Coletar filtros selecionados
            const filtrosSituacao = Array.from(document.querySelectorAll("#formRelatorioPersonalizado input[name='filtroSituacao']:checked")).map(el => el.value);
            const filtrosPrioridade = Array.from(document.querySelectorAll("#formRelatorioPersonalizado input[name='filtroPrioridade']:checked")).map(el => el.value);
            const filtroCategoria = document.getElementById("filtroCategoria").value;
            const filtroProjeto = document.getElementById('filtroProjetoRelatorio')?.value || '';
            const filtroDataInicio = document.querySelector("input[name='filtroDataInicio']").value;
            const filtroDataFim = document.querySelector("input[name='filtroDataFim']").value;

            // Validar se pelo menos um filtro foi selecionado
            if (filtrosSituacao.length === 0 && filtrosPrioridade.length === 0 && !filtroCategoria && !filtroProjeto && !filtroDataInicio && !filtroDataFim) {
                notify.warning("Por favor, selecione pelo menos um filtro para gerar o relatório.");
                return;
            }

            try {
                // Criar URL com filtros como query parameters
                let url = 'relatorios/relatorio_personalizado.php?';
                
                if (filtrosSituacao.length > 0) {
                    url += 'situacao=' + encodeURIComponent(filtrosSituacao.join(',')) + '&';
                }
                if (filtrosPrioridade.length > 0) {
                    url += 'prioridade=' + encodeURIComponent(filtrosPrioridade.join(',')) + '&';
                }
                if (filtroCategoria) {
                    url += 'categoria=' + encodeURIComponent(filtroCategoria) + '&';
                }
                if (filtroProjeto) {
                    url += 'projeto=' + encodeURIComponent(filtroProjeto) + '&';
                }
                if (filtroProjetoAtual !== 'todos') {
                    url += 'id_projeto=' + encodeURIComponent(filtroProjetoAtual) + '&';
                }
                if (filtroDataInicio) {
                    url += 'data_inicio=' + encodeURIComponent(filtroDataInicio) + '&';
                }
                if (filtroDataFim) {
                    url += 'data_fim=' + encodeURIComponent(filtroDataFim) + '&';
                }
                
                // Remover último '&' se existir
                url = url.replace(/&$/, '');

                console.log("Redirecionando para:", url);

                // Fechar modal
                divModalRelatorioPersonalizado.classList.add("hidden");
                shadowRelatorioPersonalizado.classList.add("hidden");
                liberarRolagemModal();

                // Abrir em outra página, mantendo a tela atual acessível
                window.open(url, '_blank', 'noopener,noreferrer');

            } catch (error) {
                console.error("Erro ao gerar relatório:", error);
                notify.error("Erro ao gerar o relatório. Verifique o console (F12)");
            }
        };

        // Gerar relatório por situação (Em aberto, Em andamento, Finalizadas)
        btnRelatorioSituacao.forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                const situacao = btn.getAttribute("data-situacao");

                try {
                    // Abrir relatório em outra página
                    const filtroProjeto = filtroProjetoAtual !== 'todos' ? '&id_projeto=' + encodeURIComponent(filtroProjetoAtual) : '';
                    const url = 'relatorios/relatorio_situacao.php?situacao=' + encodeURIComponent(situacao) + filtroProjeto;
                    console.log("Gerando relatório para situação:", situacao);
                    window.open(url, '_blank', 'noopener,noreferrer');

                } catch (error) {
                    console.error("Erro ao gerar relatório:", error);
                    notify.error("Erro ao gerar o relatório. Verifique o console (F12)");
                }
            };
        });

        // FIM RELATÓRIO PERSONALIZADO

        // ===== GERENCIAR USUÁRIOS =====
        let usuariosAdmin = [];
        let paginaUsuariosAtual = 1;
        let usuariosItensPorPagina = 5;
        let requisicaoUsuariosAdmin = 0;

        async function carregarUsuarios() {
            const requisicaoAtual = ++requisicaoUsuariosAdmin;
            const projetoDaRequisicao = filtroProjetoAtual;
            try {
                console.log('Carregando usuários...');
                
                const pesquisa = document.getElementById('usuarioPesquisa')?.value || '';
                const tipo = document.getElementById('usuarioFiltroTipo')?.value || '';
                const idProjeto = projetoDaRequisicao !== 'todos' ? projetoDaRequisicao : (document.getElementById('usuarioFiltroProjeto')?.value || '');
                const status = document.getElementById('usuarioFiltroStatus')?.value ?? '';
                const ativo = status !== '' ? Number(status) : '';

                let url = '../controllers/usuario_controller.php?action=listar_usuarios';
                if (pesquisa) url += '&pesquisa=' + encodeURIComponent(pesquisa);
                if (tipo) url += '&tipo=' + encodeURIComponent(tipo);
                if (idProjeto) url += '&id_projeto=' + encodeURIComponent(idProjeto);
                if (status !== '') url += '&ativo=' + encodeURIComponent(ativo);

                console.log('URL da requisição:', url);

                const response = await fetch(url, { method: 'GET', credentials: 'same-origin', cache: 'no-store' });
                
                console.log('Status HTTP:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();

                console.log('Resposta do servidor (usuários):', data);
                if (requisicaoAtual !== requisicaoUsuariosAdmin || projetoDaRequisicao !== filtroProjetoAtual) return;

                if (data.sucesso) {
                    console.log('Usuários carregados com sucesso:', data.dados?.length || 0);
                    const totalUsuarios = document.getElementById('totalUsuariosAdmin');
                    usuariosAdmin = data.dados || [];
                    paginaUsuariosAtual = 1;
                    const quantidadeUsuarios = usuariosAdmin.length;
                    if (totalUsuarios) totalUsuarios.textContent = `Total de usuários: ${quantidadeUsuarios}`;
                    renderizarUsuarios();
                } else {
                    console.warn('Endpoint de usuários retornou erro:', data.erro || 'Erro não informado.');
                }
            } catch (error) {
                console.error('Erro ao carregar usuários:', error);
            }
        }

        async function carregarFiltroProjetosUsuarios() {
            const filtro = document.getElementById('usuarioFiltroProjeto');
            if (!filtro) return;

            try {
                const response = await fetch('../controllers/projeto_controller.php?action=listar_projetos');
                const data = await response.json();
                const valorAtual = filtro.value;
                const projetos = Array.isArray(data?.dados) ? data.dados : [];
                filtro.innerHTML = '<option value="">Todos</option>' + projetos.map((projeto) => `<option value="${Number(projeto.id)}">${escaparTextoAdm(projeto.nome || 'Projeto')}</option>`).join('');
                filtro.value = valorAtual;
            } catch (error) {
                console.error('Erro ao carregar filtro de projetos:', error);
            }
        }

        async function carregarSolicitacoesCadastro() {
            const tbody = document.getElementById('solicitacoesCadastroTableBody');
            const contador = document.getElementById('contadorSolicitacoesCadastro');
            if (!tbody || !contador) return;

            try {
                const response = await fetch('../controllers/usuario_controller.php?action=listar_solicitacoes_cadastro', {
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                const data = await response.json();
                if (!data.sucesso) throw new Error(data.erro || 'Erro ao carregar solicitações.');

                const solicitacoes = data.dados || [];
                contador.textContent = `${solicitacoes.length} pendente(s)`;
                tbody.innerHTML = solicitacoes.length ? '' : `<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500"><div class="flex flex-col items-center justify-center gap-2"><i class="fa-solid fa-inbox text-3xl text-gray-300" aria-hidden="true"></i><span>Nenhuma solicitação pendente.</span></div></td></tr>`;
                solicitacoes.forEach((solicitacao) => {
                    const tr = document.createElement('tr');
                    const nome = String(solicitacao.nome || 'Usuário');
                    const inicial = escaparTextoAdm(nome.charAt(0).toUpperCase());
                    const tipo = String(solicitacao.tipo || '').toLowerCase();
                    const tipoClasse = tipo === 'administrador' || tipo === 'master'
                        ? 'bg-blue-100 text-blue-700'
                        : 'bg-green-100 text-green-700';
                    const tipoLabel = tipo === 'master' ? 'Master' : (tipo === 'administrador'
                        ? 'Administrador'
                        : `Usuário - ${solicitacao.projeto_nome || 'Projeto não informado'}`);
                    tr.className = 'border-b border-gray-100 hover:bg-[#fbfdfb] transition';
                    tr.innerHTML = `
                        <td class="px-4 py-2.5"><div class="flex items-center gap-3"><span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center font-semibold text-sm shrink-0">${inicial}</span><span class="text-sm font-semibold text-gray-800">${escaparTextoAdm(nome)}</span></div></td>
                        <td class="px-4 py-2.5 text-sm text-gray-600">${escaparTextoAdm(solicitacao.email)}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-600">${escaparTextoAdm(solicitacao.matricula)}</td>
                        <td class="px-4 py-2.5 align-middle text-center"><span title="${escaparTextoAdm(tipoLabel)}" class="inline-flex max-w-[155px] items-center justify-center break-words whitespace-normal rounded-full px-2.5 py-1 text-center text-[11px] font-semibold leading-tight">${escaparTextoAdm(tipoLabel)}</span></td>
                        <td class="px-4 py-2.5 text-sm text-gray-600">${escaparTextoAdm(solicitacao.projeto_nome || 'Projeto não informado')}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-600 whitespace-nowrap">${formatarDataAdm(solicitacao.data_criacao)}</td>
                        <td class="px-4 py-2.5 text-center"><div class="flex flex-wrap justify-center gap-2"><button type="button" class="btnAprovarCadastro border border-green-300 text-green-700 px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-green-50 transition"><i class="fa-solid fa-check mr-1"></i>Aprovar</button><button type="button" class="btnRecusarCadastro border border-red-300 text-red-600 px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-red-50 transition"><i class="fa-solid fa-xmark mr-1"></i>Recusar</button></div></td>`;
                    tr.querySelector('.btnAprovarCadastro').onclick = () => decidirSolicitacaoCadastro(solicitacao.id, 'aprovado');
                    tr.querySelector('.btnRecusarCadastro').onclick = () => decidirSolicitacaoCadastro(solicitacao.id, 'recusado');
                    tbody.appendChild(tr);
                });
            } catch (error) {
                console.error('Erro ao carregar solicitações de cadastro:', error);
            }
        }

        function escaparTextoAdm(valor) {
            return String(valor ?? '').replace(/[&<>"']/g, caractere => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[caractere]);
        }

        function formatarDataAdm(valor) {
            const texto = String(valor || '').trim();
            if (!texto) return '-';

            const partes = texto.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
            return partes ? `${partes[3]}/${partes[2]}/${partes[1]}, ${partes[4]}:${partes[5]}` : '-';
        }

        function abrirModalDecisaoCadastro(id, decisao) {
            const aprovar = decisao === 'aprovado';
            const modal = document.getElementById(aprovar ? 'divModalAprovarCadastro' : 'divModalRecusarCadastro');
            if (!modal) return;

            modal.innerHTML = `
                <div class="bg-white w-[90%] sm:w-[420px] rounded-2xl shadow-2xl p-4 sm:p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 shrink-0 rounded-xl flex items-center justify-center ${aprovar ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'}">
                            <i class="fa-solid ${aprovar ? 'fa-user-check' : 'fa-user-xmark'} text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.14em] text-gray-500">Confirmação</p>
                            <h3 class="text-xl font-bold ${aprovar ? 'text-[var(--dark-green)]' : 'text-red-600'}">${aprovar ? 'Aprovar cadastro?' : 'Recusar cadastro?'}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">${aprovar ? 'O usuário poderá acessar o sistema.' : 'A solicitação será recusada.'}</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" class="btnCancelarDecisaoCadastro px-4 py-2.5 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition">Cancelar</button>
                        <button type="button" class="btnConfirmarDecisaoCadastro px-4 py-2.5 rounded-lg text-white font-semibold ${aprovar ? 'bg-[var(--dark-green)] hover:bg-[var(--dark-green2)]' : 'bg-red-600 hover:bg-red-700'} transition">${aprovar ? 'Aprovar' : 'Recusar'}</button>
                    </div>
                </div>`;
            modal.classList.remove('hidden');
            shadowSecUsuarios?.classList.remove('hidden');
            bloquearRolagemModal();

            const fechar = () => {
                modal.classList.add('hidden');
                modal.innerHTML = '';
                shadowSecUsuarios?.classList.add('hidden');
                liberarRolagemModal();
            };
            modal.querySelector('.btnCancelarDecisaoCadastro')?.addEventListener('click', fechar);
            modal.querySelector('.btnConfirmarDecisaoCadastro')?.addEventListener('click', async () => {
                const botao = modal.querySelector('.btnConfirmarDecisaoCadastro');
                if (botao) botao.disabled = true;
                fechar();
                await processarDecisaoCadastro(id, decisao);
            });
        }

        function decidirSolicitacaoCadastro(id, decisao) {
            abrirModalDecisaoCadastro(id, decisao);
        }

        async function processarDecisaoCadastro(id, decisao) {

            try {
                const response = await fetch('../controllers/usuario_controller.php?action=decidir_solicitacao_cadastro', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, decisao })
                });
                const data = await response.json();
                if (!data.sucesso) throw new Error(data.erro || 'Erro ao atualizar cadastro.');
                notify.success(data.message);
                await Promise.all([carregarSolicitacoesCadastro(), carregarUsuarios()]);
            } catch (error) {
                console.error('Erro ao decidir cadastro:', error);
                notify.error(error.message || 'Erro ao atualizar cadastro.');
            }
        }

        function renderizarUsuarios() {
            const tbody = document.getElementById('usuariosTableBody');
            if (!tbody) return;
            tbody.innerHTML = '';

            const totalUsuarios = usuariosAdmin.length;
            const totalPaginas = Math.max(1, Math.ceil(totalUsuarios / usuariosItensPorPagina));
            paginaUsuariosAtual = Math.min(paginaUsuariosAtual, totalPaginas);
            const inicio = (paginaUsuariosAtual - 1) * usuariosItensPorPagina;
            const usuarios = usuariosAdmin.slice(inicio, inicio + usuariosItensPorPagina);
            const resumoUsuarios = document.getElementById('resumoUsuariosAdmin');
            const paginaAtual = document.getElementById('paginaUsuariosAtual');
            const btnAnterior = document.getElementById('btnPaginaUsuariosAnterior');
            const btnProxima = document.getElementById('btnPaginaUsuariosProxima');

            if (resumoUsuarios) {
                resumoUsuarios.textContent = totalUsuarios
                    ? `Exibindo ${inicio + 1} a ${Math.min(inicio + usuarios.length, totalUsuarios)} de ${totalUsuarios} usuários`
                    : 'Nenhum usuário para exibir';
            }
            if (paginaAtual) paginaAtual.textContent = `${paginaUsuariosAtual} / ${totalPaginas}`;
            if (btnAnterior) btnAnterior.disabled = paginaUsuariosAtual === 1;
            if (btnProxima) btnProxima.disabled = paginaUsuariosAtual === totalPaginas;

            if (!usuarios.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Nenhum usuário encontrado.</td>
                    </tr>       
                `;
                return;
            }

            usuarios.forEach(usuario => {
                const statusCadastro = usuario.status_cadastro || 'aprovado';
                const statusLabel = statusCadastro === 'pendente' ? 'Pendente' : statusCadastro === 'recusado' ? 'Recusado' : Number(usuario.ativo) === 1 ? 'Ativo' : 'Inativo';
                const statusClass = statusCadastro === 'pendente' ? 'bg-yellow-100 text-yellow-800' : statusCadastro === 'recusado' ? 'bg-red-100 text-red-700' : Number(usuario.ativo) === 1 ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700';
                const nomeUsuario = String(usuario.nome || 'Usuário');
                const inicialUsuario = nomeUsuario.charAt(0).toUpperCase();
                const fotoUsuario = String(usuario.foto || '').trim();
                const fotoUsuarioMarkup = fotoUsuario
                    ? `<img src="../assets/${escaparTextoAdm(fotoUsuario)}" alt="Foto de ${escaparTextoAdm(nomeUsuario)}" class="w-full h-full rounded-full object-cover">`
                    : escaparTextoAdm(inicialUsuario);
                const tipoUsuario = String(usuario.tipo || '').toLowerCase();
                const projetosNomes = String(usuario.projetos_nomes || '').trim();
                const tipoLabel = tipoUsuario === 'master'
                    ? 'Master'
                    : tipoUsuario === 'administrador' ? 'Administrador' : `Usuário - ${projetosNomes || 'Projeto não informado'}`;
                const tipoClass = tipoUsuario === 'master' || tipoUsuario === 'administrador' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700';
                const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-100 hover:bg-[#fbfdfb] transition';

                const cellNome = document.createElement('td');
                cellNome.className = 'px-4 py-2.5 text-sm font-medium text-gray-800';
                cellNome.innerHTML = `<div class="flex items-center gap-3"><span class="w-10 h-10 rounded-full bg-[var(--dark-green)] text-white flex items-center justify-center font-semibold text-sm shrink-0 overflow-hidden">${fotoUsuarioMarkup}</span><span>${escaparTextoAdm(nomeUsuario)}</span></div>`;

                const cellMatricula = document.createElement('td');
                cellMatricula.className = 'px-4 py-2.5 text-sm text-gray-600';
                cellMatricula.textContent = usuario.matricula;

                const cellEmail = document.createElement('td');
                cellEmail.className = 'px-4 py-2.5 text-sm text-gray-600';
                cellEmail.textContent = usuario.email;

                const cellTipo = document.createElement('td');
                cellTipo.className = 'px-4 py-2.5 text-sm';
                cellTipo.innerHTML = `<span class="${tipoClass} rounded-full px-2.5 py-1 text-[11px] font-semibold">${tipoLabel}</span>`;

                const cellStatus = document.createElement('td');
                cellStatus.className = 'px-4 py-2.5 text-sm';
                cellStatus.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${statusClass}"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>${statusLabel}</span>`;

                const cellAcoes = document.createElement('td');
                cellAcoes.className = 'px-4 py-2.5 text-center w-[150px] min-w-[150px]';

                const actionsWrap = document.createElement('div');
                actionsWrap.className = 'flex flex-nowrap items-center justify-center gap-2 whitespace-nowrap';

                const tipoUsuarioAtual = String(usuario.tipo || '').toLowerCase();
                const usuarioAdministrativo = tipoUsuarioAtual === 'administrador' || tipoUsuarioAtual === 'master';
                if (!usuarioAdministrativo) {
                    const btnVer = document.createElement('button');
                    btnVer.type = 'button';
                    btnVer.className = 'border border-blue-200 text-blue-700 hover:bg-blue-50 w-9 h-9 shrink-0 rounded-md transition';
                    btnVer.innerHTML = '<i class="fa-solid fa-eye"></i>';
                    btnVer.title = 'Ver informações do usuário';
                    btnVer.setAttribute('aria-label', `Ver informações de ${nomeUsuario}`);
                    btnVer.addEventListener('click', () => abrirDetalhesUsuario(usuario.id));
                    actionsWrap.appendChild(btnVer);
                }

                const btnEditar = document.createElement('button');
                btnEditar.type = 'button';
                btnEditar.className = 'border border-gray-200 text-gray-600 hover:bg-gray-50 w-9 h-9 shrink-0 rounded-md transition';
                btnEditar.innerHTML = '<i class="fa-solid fa-pencil"></i>';
                btnEditar.title = 'Editar usuário';
                btnEditar.addEventListener('click', () => abrirFormularioUsuario('editar', usuario));

                const btnStatus = document.createElement('button');
                btnStatus.type = 'button';
                btnStatus.className = `${Number(usuario.ativo) === 1 ? 'border border-red-200 text-red-600 hover:bg-red-50' : 'border border-emerald-200 text-emerald-700 hover:bg-emerald-50'} w-9 h-9 shrink-0 rounded-md transition`;
                btnStatus.innerHTML = Number(usuario.ativo) === 1 ? '<i class="fa-solid fa-user-slash"></i>' : '<i class="fa-solid fa-user-check"></i>';
                btnStatus.title = Number(usuario.ativo) === 1 ? 'Desativar usuário' : 'Ativar usuário';
                btnStatus.addEventListener('click', () => alterarStatusUsuario(usuario.id, Number(usuario.ativo) === 1 ? 0 : 1, usuario.nome));

                actionsWrap.appendChild(btnEditar);
                actionsWrap.appendChild(btnStatus);
                cellAcoes.appendChild(actionsWrap);

                tr.appendChild(cellNome);
                tr.appendChild(cellMatricula);
                tr.appendChild(cellEmail);
                tr.appendChild(cellTipo);
                tr.appendChild(cellStatus);
                tr.appendChild(cellAcoes);

                tbody.appendChild(tr);
            });
        }

        async function abrirDetalhesUsuario(id) {
            try {
                const response = await fetch('../controllers/usuario_controller.php?action=obter_usuario&id=' + id);
                const data = await response.json();
                if (!data.sucesso || !data.dados) {
                    notify.error(' ' + (data.erro || 'Usuário não encontrado.'));
                    return;
                }

                const u = data.dados;
                const historico = (u.historico || []).map(item => `
                    <li class="border-b border-gray-200 py-2 last:border-b-0">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-semibold text-gray-800">#${item.id}</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">${item.status}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">${item.titulo}</p>
                        <p class="text-xs text-gray-500 mt-1">${item.situacao || 'pendente'}</p>
                    </li>
                `).join('') || '<li class="text-gray-500 py-2">Sem histórico.</li>';

                const modal = document.getElementById('divModalVerUsuario');
                if (!modal) return;
                modal.innerHTML = '';
                const conteudoDiv = document.createElement('div');
                conteudoDiv.className = 'bg-white rounded-xl shadow-xl w-[90%] sm:w-[560px] p-6 max-h-[90vh] overflow-y-auto';
                
                const titulo = document.createElement('div');
                titulo.className = 'flex justify-between items-center mb-5';
                titulo.innerHTML = `
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-gray-500">Usuário</p>
                        <h3 class="text-2xl font-bold text-[var(--dark-green)]">${u.nome}</h3>
                    </div>
                    <button type="button" class="btnFecharVerUsuario text-gray-500 hover:text-gray-800 text-2xl">×</button>
                </div>`;
                conteudoDiv.appendChild(titulo);
                
                const conteudo = document.createElement('div');
                conteudo.className = 'space-y-5';
                const fotoUsuarioDetalhes = String(u.foto || '').trim();
                const fotoUsuarioMarkup = fotoUsuarioDetalhes
                    ? `<img src="../assets/${fotoUsuarioDetalhes.replace(/[&<>\"']/g, caractere => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[caractere])}" alt="Foto de ${String(u.nome || '').replace(/[&<>\"']/g, caractere => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[caractere])}" class="w-full h-full rounded-full object-cover">`
                    : String(u.nome || 'U').charAt(0).toUpperCase();
                conteudo.innerHTML = `
                    <div class="flex items-center gap-4 border-b border-gray-200 pb-4">
                        <div class="w-16 h-16 rounded-full bg-[var(--dark-green)] text-white flex items-center justify-center text-2xl font-bold">
                            ${fotoUsuarioMarkup}
                        </div>
                        <div>
                            <p class="text-xl font-semibold">${u.nome}</p>
                            <p class="text-sm text-gray-600">${escaparTextoAdm(u.tipo === 'master' ? 'Master' : u.tipo === 'administrador' ? 'Administrador' : `Usuário - ${u.projetos_nomes || 'Projeto não informado'}`)}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm lg:text-[13px]">
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <p class="text-[var(--gray1)]">E-mail</p>
                            <p class="font-medium text-gray-900">${u.email}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <p class="text-[var(--gray1)]">Matrícula</p>
                            <p class="font-medium text-gray-900">${u.matricula}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <p class="text-[var(--gray1)]">Perfil</p>
                            <p class="font-medium text-gray-900">${escaparTextoAdm(u.tipo === 'master' ? 'Master' : u.tipo === 'administrador' ? 'Administrador' : `Usuário - ${u.projetos_nomes || 'Projeto não informado'}`)}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <p class="text-[var(--gray1)]">Status</p>
                            <p class="font-medium ${Number(u.ativo) === 1 ? 'text-green-700' : 'text-gray-700'}">${Number(u.ativo) === 1 ? 'Ativo' : 'Inativo'}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <p class="text-[var(--gray1)]">Série</p>
                            <p class="font-medium text-gray-900">${u.serie || 'N/A'}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <p class="text-[var(--gray1)]">Pontuação</p>
                            <p class="font-medium text-gray-900">${Math.max(0, Number(u.pontuacao) || 0)}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 md:col-span-2">
                            <p class="text-[var(--gray1)]">Projetos vinculados</p>
                            <p class="font-medium text-gray-900">${escaparTextoAdm(u.projetos_nomes || 'Nenhum projeto vinculado')}</p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Resumo das demandas</h4>
                        </div>
                        <div class="grid grid-cols-3 gap-3 text-center text-sm">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <p class="text-green-700 font-bold">${u.demandas_concluidas || 0}</p>
                                <p class="text-green-800 text-xs">Concluídas</p>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <p class="text-yellow-700 font-bold">${u.demandas_em_andamento || 0}</p>
                                <p class="text-yellow-800 text-xs">Em andamento</p>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <p class="text-red-700 font-bold">${u.demandas_nao_concluidas || 0}</p>
                                <p class="text-red-800 text-xs">Não concluídas</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <h4 class="font-semibold text-gray-800 mb-2">Histórico recente</h4>
                        <ul class="max-h-52 overflow-y-auto space-y-1 list-none">${historico}</ul>
                    </div>
                `;
                conteudoDiv.appendChild(conteudo);
                
                const acoes = document.createElement('div');
                acoes.className = 'mt-6 flex justify-end gap-3';
                acoes.innerHTML = `
                    <button type="button" class="btnEditarUsuarioDetalhe bg-[var(--dark-green)] text-white px-4 py-2 rounded-md font-semibold hover:bg-[var(--dark-green2)]">Editar usuário</button>
                `;
                conteudoDiv.appendChild(acoes);
                modal.appendChild(conteudoDiv);

                modal.classList.remove('hidden');
                shadowSecUsuarios?.classList.remove('hidden');
                bloquearRolagemModal();
                
                const fechar = () => {
                    modal.classList.add('hidden');
                    modal.innerHTML = '';
                    shadowSecUsuarios?.classList.add('hidden');
                    liberarRolagemModal();
                };
                
                modal.querySelector('.btnFecharVerUsuario')?.addEventListener('click', fechar);
                modal.querySelector('.btnEditarUsuarioDetalhe')?.addEventListener('click', () => {
                    fechar();
                    abrirFormularioUsuario('editar', u);
                }, { once: true });
            } catch (error) {
                console.error('Erro ao obter detalhes:', error);
            }
        }

        async function abrirFormularioUsuario(modo, usuario = null) {
            if (modo === 'editar' && usuario?.id) {
                try {
                    const respostaAtual = await fetch(`../controllers/usuario_controller.php?action=obter_usuario&id=${Number(usuario.id)}`, { cache: 'no-store' });
                    const dadosAtuais = await respostaAtual.json();
                    if (respostaAtual.ok && dadosAtuais.sucesso && dadosAtuais.dados) {
                        usuario = { ...usuario, ...dadosAtuais.dados };
                    }
                } catch (erro) {
                    console.warn('Não foi possível atualizar os dados do usuário antes da edição:', erro);
                }
            }

            const divModal = document.getElementById(modo === 'novo' ? 'divModalCadastrarUsuario' : 'divModalEditarUsuario');
            if (!divModal) return;
            divModal.innerHTML = '';

            const ehAdministradorEditado = String(usuario?.tipo || '').trim().toLowerCase() === 'administrador';
            const podeVincularProjetoUsuario = Number(usuario?.pode_vincular_projeto) === 1;
            
            const modal = document.createElement('div');
            modal.className = 'relative z-[230] pointer-events-auto bg-white rounded-xl shadow-xl w-[90%] sm:w-[500px] p-6';
            modal.innerHTML = `
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-xl font-bold text-[var(--dark-green)]">${modo === 'editar' ? 'Editar usuário' : 'Cadastrar usuário'}</h3>
                    <button class="btnFecharEditarUsuario text-gray-500 hover:text-gray-800 text-2xl">×</button>
                </div>

                <form id="formUsuarioModal" class="space-y-4">
                    <input type="hidden" id="usuarioIdModal" value="">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nome</label>
                        <input id="usuarioNomeModal" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none" required>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input id="usuarioEmailModal" type="email" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Matrícula</label>
                            <input id="usuarioMatriculaModal" type="text" maxlength="7" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none" required>
                        </div>
                    </div>

                    <div>
                        <input type="hidden" id="usuarioTipoModal" value="usuario">
                        <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Série</label>
                            <select id="usuarioSerieModal" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none">
                                <option value="">Selecione</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        </div>
                    </div>

                    <div id="painelSenhaModal">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Senha</label>
                        <input id="usuarioSenhaModal" type="password" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none">
                        <small id="usuarioSenhaHelpModal" class="text-gray-500">Senha obrigatória para cadastro.</small>
                    </div>

                    <div id="painelProjetosUsuarioModal" class="hidden border-t border-gray-200 pt-4 mt-2">
                        <label id="tituloProjetosUsuarioModal" class="block text-sm font-semibold text-gray-700 mb-2">Projetos de atuação</label>
                        <div id="listaProjetosUsuarioModal" class="space-y-2 max-h-52 overflow-y-auto pr-1"></div>
                        <small class="text-gray-500">Selecione os projetos aos quais este usuário terá acesso.</small>
                    </div>

                    ${modo === 'editar' && <?php echo $tipoUsuario === 'master' ? 'true' : 'false'; ?> && ehAdministradorEditado ? `
                    <label class="permissao-criar-projeto relative z-10 flex pointer-events-auto items-center gap-3 border-t border-gray-200 pt-4 mt-6 text-sm font-semibold text-gray-700 cursor-pointer">
                        <input id="usuarioPodeCriarProjetoModal" type="checkbox" class="relative z-20 pointer-events-auto w-4 h-4 shrink-0 accent-[var(--dark-green)]" ${Number(usuario?.pode_criar_projeto) === 1 ? 'checked' : ''}>
                        Pode criar novos projetos
                    </label>` : ''}

                    ${modo === 'editar' && <?php echo $tipoUsuario === 'master' ? 'true' : 'false'; ?> && ehAdministradorEditado ? `
                    <label class="permissao-vincular-projeto relative z-10 flex pointer-events-auto items-center gap-3 border-t border-gray-200 pt-4 mt-2 text-sm font-semibold text-gray-700 cursor-pointer">
                        <input id="usuarioPodeVincularProjetoModal" type="checkbox" class="relative z-20 pointer-events-auto w-4 h-4 shrink-0 accent-[var(--dark-green)]" ${podeVincularProjetoUsuario ? 'checked' : ''}>
                        Pode vincular usuários diretamente a projetos
                    </label>` : ''}

                    ${modo === 'editar' ? `
                    <div class="border-t border-red-100 pt-4 mt-2">
                        <button type="button" id="btnExcluirUsuarioModal" class="w-full border border-red-200 bg-red-50 text-red-700 px-4 py-2.5 rounded-md font-semibold hover:bg-red-100 transition">
                            <i class="fa-solid fa-user-xmark mr-2"></i>Excluir usuário permanentemente
                        </button>
                        <p class="text-xs text-red-600 mt-2">Esta ação é definitiva e não poderá ser desfeita.</p>
                    </div>` : ''}

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="btnCancelarEditarUsuario bg-gray-300 text-gray-700 px-4 py-2 rounded-md font-semibold">Cancelar</button>
                        <button type="submit" class="bg-[var(--dark-green)] text-white px-4 py-2 rounded-md font-semibold">Salvar</button>
                    </div>
                </form>
            `;
            divModal.appendChild(modal);
            
            const idInput = modal.querySelector('#usuarioIdModal');
            const nomeInput = modal.querySelector('#usuarioNomeModal');
            const emailInput = modal.querySelector('#usuarioEmailModal');
            const matriculaInput = modal.querySelector('#usuarioMatriculaModal');
            const tipoInput = modal.querySelector('#usuarioTipoModal');
            const serieInput = modal.querySelector('#usuarioSerieModal');
            const senhaInput = modal.querySelector('#usuarioSenhaModal');
            const senhaHelp = modal.querySelector('#usuarioSenhaHelpModal');
            const permissaoProjetoInput = modal.querySelector('#usuarioPodeCriarProjetoModal');
            const permissaoVinculoInput = modal.querySelector('#usuarioPodeVincularProjetoModal');
            const painelSenha = modal.querySelector('#painelSenhaModal');
            const painelProjetos = modal.querySelector('#painelProjetosUsuarioModal');
            const listaProjetosModal = modal.querySelector('#listaProjetosUsuarioModal');
            const tituloProjetosModal = modal.querySelector('#tituloProjetosUsuarioModal');
            const form = modal.querySelector('#formUsuarioModal');

            const atualizarPainelProjetosUsuario = async () => {
                if (!painelProjetos || !listaProjetosModal) return;

                const tipoAtual = tipoInput.value;
                const podeVincularAdministrador = modo === 'editar'
                    && tipoAtual === 'administrador'
                    && <?php echo $podeGerenciarVinculos ? 'true' : 'false'; ?>;
                const exibirProjetos = modo === 'editar' && (tipoAtual === 'usuario' || podeVincularAdministrador);
                painelProjetos.classList.toggle('hidden', !exibirProjetos);
                if (tituloProjetosModal) {
                    tituloProjetosModal.textContent = podeVincularAdministrador ? 'Projetos do administrador' : 'Projetos de atuação';
                }

                try {
                    const response = await fetch('../controllers/projeto_controller.php?action=listar_projetos');
                    const data = await response.json();
                    const projetos = Array.isArray(data?.dados) ? data.dados : [];


                    let projetosSelecionados = new Set();
                    if (modo === 'editar' && usuario?.id) {
                        const vinculadosResponse = await fetch(`../controllers/projeto_controller.php?action=listar_projetos_usuario&id_usuario=${usuario.id}`);
                        const vinculadosData = await vinculadosResponse.json();
                        const vinculados = Array.isArray(vinculadosData?.dados) ? vinculadosData.dados : [];
                        projetosSelecionados = new Set(vinculados.filter((projeto) => !!projeto.vinculado).map((projeto) => Number(projeto.id)));
                    }

                    listaProjetosModal.innerHTML = projetos.length
                        ? projetos.map((projeto) => `
                            <label class="flex items-center gap-3 p-2 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="projetosUsuarioModal" value="${Number(projeto.id)}" ${projetosSelecionados.has(Number(projeto.id)) ? 'checked' : ''} class="w-4 h-4 accent-[var(--dark-green)]">
                                <span class="text-sm text-gray-700 flex-1">${escaparTextoAdm(projeto.nome || 'Projeto')}</span>
                            </label>
                        `).join('')
                        : '<div class="text-sm text-gray-500">Nenhum projeto ativo cadastrado.</div>';
                } catch (error) {
                    console.error('Erro ao carregar projetos do usuário:', error);
                    listaProjetosModal.innerHTML = '<div class="text-sm text-red-500">Não foi possível carregar os projetos.</div>';
                }
            };

            if (modo === 'editar' && usuario) {
                idInput.value = usuario.id;
                nomeInput.value = usuario.nome || '';
                emailInput.value = usuario.email || '';
                matriculaInput.value = usuario.matricula || '';
                tipoInput.value = usuario.tipo || 'usuario';
                serieInput.value = usuario.serie || '3';
                senhaInput.value = '';
                senhaHelp.textContent = 'Deixe em branco para manter a senha atual.';
            } else {
                idInput.value = '';
                nomeInput.value = '';
                emailInput.value = '';
                matriculaInput.value = '';
                tipoInput.value = 'administrador';
                serieInput.value = '3';
                senhaInput.value = '';
                senhaHelp.textContent = 'Senha obrigatória para cadastro.';
            }

            atualizarPainelProjetosUsuario().finally(() => {
                divModal.classList.remove('hidden');
                shadowSecUsuarios?.classList.remove('hidden');
                bloquearRolagemModal();
            });
            
            const fechar = () => {
                divModal.classList.add('hidden');
                divModal.innerHTML = '';
                shadowSecUsuarios?.classList.add('hidden');
                liberarRolagemModal();
            };
            
            modal.querySelector('.btnFecharEditarUsuario')?.addEventListener('click', fechar);
            modal.querySelector('.btnCancelarEditarUsuario')?.addEventListener('click', fechar);
            modal.querySelector('#btnExcluirUsuarioModal')?.addEventListener('click', () => {
                fechar();
                confirmarExclusaoUsuario(usuario.id, usuario.nome);
            });
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await salvarUsuarioModal(form, fechar);
            });
        }

        function confirmarExclusaoUsuario(id, nomeUsuario = 'este usuário') {
            const divModal = document.getElementById('divModalStatusUsuario');
            if (!divModal) return;
            divModal.innerHTML = `
                <div class="bg-white w-[90%] sm:w-[460px] rounded-2xl shadow-2xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 shrink-0 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                            <i class="fa-solid fa-user-xmark text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.14em] text-gray-500">Ação irreversível</p>
                            <h3 class="text-xl font-bold text-red-600">Excluir usuário permanentemente?</h3>
                            <p class="mt-2 text-sm leading-relaxed text-gray-600">O usuário <strong>${escaparTextoAdm(nomeUsuario)}</strong>, suas atribuições e pontuações serão removidos definitivamente.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" class="btnCancelarStatus px-4 py-2.5 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition">Cancelar</button>
                        <button type="button" class="btnConfirmarStatus px-4 py-2.5 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">Excluir definitivamente</button>
                    </div>
                </div>`;
            divModal.classList.remove('hidden');
            shadowSecUsuarios?.classList.remove('hidden');
            bloquearRolagemModal();

            const fechar = () => {
                divModal.classList.add('hidden');
                divModal.innerHTML = '';
                shadowSecUsuarios?.classList.add('hidden');
                liberarRolagemModal();
            };
            divModal.querySelector('.btnCancelarStatus')?.addEventListener('click', fechar);
            divModal.querySelector('.btnConfirmarStatus')?.addEventListener('click', async () => {
                const botao = divModal.querySelector('.btnConfirmarStatus');
                if (botao) botao.disabled = true;
                fechar();
                try {
                    const resposta = await fetch('../controllers/usuario_controller.php?action=excluir_usuario', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const resultado = await resposta.json();
                    if (!resposta.ok || !resultado.sucesso) throw new Error(resultado.erro || 'Erro ao excluir usuário.');
                    notify.success(resultado.message || 'Usuário excluído permanentemente.');
                    await carregarUsuarios();
                } catch (erro) {
                    console.error('Erro ao excluir usuário:', erro);
                    notify.error(' ' + (erro.message || 'Erro ao excluir usuário.'));
                }
            });
        }
        
        async function salvarUsuarioModal(form, fechar) {
            const id = form.querySelector('#usuarioIdModal')?.value || '';
            const nome = form.querySelector('#usuarioNomeModal')?.value.trim() || '';
            const email = form.querySelector('#usuarioEmailModal')?.value.trim() || '';
            const matricula = form.querySelector('#usuarioMatriculaModal')?.value.trim() || '';
            const tipo = form.querySelector('#usuarioTipoModal')?.value || '';
            const serie = form.querySelector('#usuarioSerieModal')?.value || '';
            const senha = form.querySelector('#usuarioSenhaModal')?.value || '';
            const podeCriarProjeto = form.querySelector('#usuarioPodeCriarProjetoModal')?.checked || false;
            const podeVincularProjeto = form.querySelector('#usuarioPodeVincularProjetoModal')?.checked || false;
            const projetosSelecionados = Array.from(form.querySelectorAll('input[name="projetosUsuarioModal"]:checked')).map((input) => Number(input.value)).filter((id) => Number.isFinite(id) && id > 0);

            if (!nome || !email || !matricula || !tipo) {
                notify.warning('⚠️ Preencha todos os campos obrigatórios!');
                return;
            }

            if (!/^[0-9]{7}$/.test(matricula)) {
                notify.warning('⚠️ Matrícula deve conter exatamente 7 dígitos.');
                return;
            }

            if (tipo === 'usuario' && !serie) {
                notify.warning('⚠️ Selecione a série para usuários não administradores.');
                return;
            }

            const projetosParaSalvar = projetosSelecionados;

            if (tipo === 'usuario' && projetosParaSalvar.length === 0) {
                notify.warning('⚠️ Selecione ao menos um projeto de atuação.');
                return;
            }

            if (!id && senha.length < 6) {
                notify.warning('⚠️ Senha deve ter no mínimo 6 caracteres.');
                return;
            }

            try {
                const payload = { nome, email, matricula, tipo, serie, senha: senha || undefined, pode_criar_projeto: podeCriarProjeto, pode_vincular_projeto: podeVincularProjeto };
                if (tipo === 'usuario' || (tipo === 'administrador' && <?php echo $podeGerenciarVinculos ? 'true' : 'false'; ?>)) {
                    payload.projetos = projetosParaSalvar;
                }
                if (id) payload.id = Number(id);

                const endpoint = id ? '../controllers/usuario_controller.php?action=atualizar_usuario' : '../controllers/usuario_controller.php?action=cadastrar_usuario';
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                window._abrirModalEditarProjeto = function(projeto) {
                    const modal = document.createElement('div');
                    modal.className = 'modal-overlay-animated fixed inset-y-0 left-0 z-[300] flex h-full w-screen items-center justify-center bg-black/60 p-4 lg:left-64 lg:w-[calc(100vw-16rem)]';
                    modal.innerHTML = `
                        <div class="modal-content-animated w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl">
                            <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-pen-to-square text-lg text-[var(--dark-green)]"></i>
                                    <h3 class="text-xl font-bold text-[var(--dark-green)]">Editar projeto</h3>
                                </div>
                                <button type="button" class="btnFecharEditarProjeto h-9 w-9 rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-800" aria-label="Fechar edição do projeto">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <form class="formEditarProjeto space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700" for="nomeEditarProjeto">Nome do projeto</label>
                                    <input id="nomeEditarProjeto" name="nome" type="text" maxlength="100" required value="${escaparTextoAdm(projeto.nome || '')}" class="w-full rounded-lg border border-gray-200 bg-[var(--suave-green)] px-3 py-2.5 outline-none focus:border-[var(--dark-green)]">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700" for="descricaoEditarProjeto">Descrição</label>
                                    <textarea id="descricaoEditarProjeto" name="descricao" rows="4" class="w-full resize-none rounded-lg border border-gray-200 bg-[var(--suave-green)] px-3 py-2.5 outline-none focus:border-[var(--dark-green)]">${escaparTextoAdm(projeto.descricao || '')}</textarea>
                                </div>
                                <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                                    <button type="button" class="btnCancelarEditarProjeto rounded-lg bg-gray-200 px-4 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-300">Cancelar</button>
                                    <button type="submit" class="btnSalvarEditarProjeto inline-flex items-center gap-2 rounded-lg bg-[var(--dark-green)] px-4 py-2.5 font-semibold text-white transition hover:bg-[var(--dark-green2)]">
                                        <i class="fa-solid fa-floppy-disk"></i>Salvar alterações
                                    </button>
                                </div>
                            </form>
                        </div>`;

                    document.body.appendChild(modal);
                    const fechar = () => modal.remove();
                    modal.querySelector('.btnFecharEditarProjeto')?.addEventListener('click', fechar);
                    modal.querySelector('.btnCancelarEditarProjeto')?.addEventListener('click', fechar);
                    modal.addEventListener('click', (event) => {
                        if (event.target === modal) fechar();
                    });
                    modal.querySelector('.formEditarProjeto')?.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        const botao = modal.querySelector('.btnSalvarEditarProjeto');
                        const nome = modal.querySelector('[name="nome"]')?.value.trim() || '';
                        const descricao = modal.querySelector('[name="descricao"]')?.value.trim() || '';
                        if (!nome) return;
                        botao.disabled = true;
                        try {
                            const response = await fetch('../controllers/projeto_controller.php?action=atualizar_projeto', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_projeto: Number(projeto.id), nome, descricao })
                            });
                            const result = await response.json();
                            if (!response.ok || !result.sucesso) throw new Error(result.erro || 'Erro ao atualizar projeto.');
                            fechar();
                            notify.success(result.message || 'Projeto atualizado com sucesso!');
                            await carregarProjetos();
                            await carregarSolicitacoesProjeto();
                        } catch (error) {
                            botao.disabled = false;
                            notify.error(error.message || 'Erro ao atualizar projeto.');
                        }
                    });
                };

                const data = await response.json();

                if (data.sucesso) {
                    notify.success(' ' + (data.message || 'Usuário salvo com sucesso!'));
                    fechar();
                    await carregarUsuarios();
                } else {
                    notify.error(' ' + (data.erro || 'Erro ao salvar usuário.'));
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                notify.error(' Erro ao conectar com o servidor. Por favor tente novamente.');
            }
        }





        async function carregarProjetos() {
            try {
                const data = await buscarJsonAdm('../controllers/projeto_controller.php?action=listar_projetos&apenas_ativos=false');
                const lista = document.getElementById('listaProjetos');
                const listaAdmin = document.getElementById('listaProjetosAdmin');
                const listaVinculos = document.getElementById('listaProjetosVinculadosAdmin');
                const painelSolicitacoesProjeto = document.getElementById('painelSolicitacoesProjeto');
                const contador = document.getElementById('contadorProjetos');
                const ehMaster = String(usuario).toLowerCase() === 'master';

                Object.keys(projetosAtivosAdmin).forEach((key) => delete projetosAtivosAdmin[key]);

                if (painelSolicitacoesProjeto) {
                    painelSolicitacoesProjeto.classList.toggle('hidden', !ehMaster);
                }

                if (lista) {
                    lista.classList.toggle('hidden', !ehMaster);
                }
                if (listaAdmin) {
                    listaAdmin.classList.toggle('hidden', ehMaster);
                }
                if (listaVinculos) {
                    listaVinculos.classList.toggle('hidden', ehMaster);
                }

                const isProjetoAdminView = !ehMaster;
                if (isProjetoAdminView) {
                    const listaProjetosUsuario = document.getElementById('listaProjetosAdmin');
                    const listaProjetosVinculados = document.getElementById('listaProjetosVinculadosAdmin');
                    if (!listaProjetosUsuario || !listaProjetosVinculados) return;

                    if (!data.sucesso || !Array.isArray(data.dados)) {
                        listaProjetosUsuario.innerHTML = '<div class="p-4 rounded-lg border border-dashed border-gray-200 text-gray-500 text-sm">Não foi possível carregar os projetos.</div>';
                        listaProjetosVinculados.innerHTML = '';
                        if (contador) contador.textContent = '0';
                        return;
                    }

                    const projetos = data.dados;
                    const vinculados = projetos.filter((projeto) => projeto.vinculado === true);
                    const pendentes = {};

                    try {
                        const respostaSolicitacoes = await fetch('../controllers/solicitacao_projeto_controller.php?action=listar_minhas_solicitacoes');
                        const solicitacoesData = await respostaSolicitacoes.json();
                        if (respostaSolicitacoes.ok && solicitacoesData.sucesso && Array.isArray(solicitacoesData.dados)) {
                            solicitacoesData.dados.forEach((solicitacao) => {
                                if (solicitacao.id_projeto && solicitacao.status === 'pendente') {
                                    pendentes[Number(solicitacao.id_projeto)] = solicitacao;
                                }
                            });
                        }
                    } catch (erro) {
                        console.warn('Erro ao carregar solicitações do projeto:', erro);
                    }

                    vinculados.forEach((projeto) => {
                        if (projeto && projeto.id && Number(projeto.ativo) === 1) {
                            projetosAtivosAdmin[Number(projeto.id)] = projeto.nome || 'Projeto';
                        }
                    });
                    renderizarProjetosResponsaveis();
                    montarFiltroProjetos();
                    atualizarVisibilidadeFiltroProjetos();
                    atualizarBadgeFiltro();

                    listaProjetosUsuario.innerHTML = projetos.map((projeto) => {
                        const estaVinculado = Boolean(projeto.vinculado);
                        const pendente = pendentes[Number(projeto.id)];
                        const textoBotao = pendente ? 'Solicitação pendente' : (estaVinculado ? 'Vinculado' : 'Solicitar vínculo');
                        const corBotao = pendente ? 'bg-amber-100 text-amber-700' : (estaVinculado ? 'bg-green-100 text-green-700' : 'bg-[var(--dark-green)] text-white');
                        const iconeBotao = pendente ? 'fa-clock' : (estaVinculado ? 'fa-circle-check' : 'fa-link');
                        const botaoDisabled = pendente || estaVinculado ? 'disabled' : '';
                        const statusBadge = pendente ? 'Pendente' : (estaVinculado ? 'Vinculado' : 'Disponível');

                        return `
                            <div class="project-card p-4 rounded-lg border border-gray-200 bg-[var(--suave-green)]/40">
                                <div class="flex flex-col items-stretch justify-between gap-4 sm:flex-row sm:items-start">
                                    <div class="flex-1">
                                        <p class="font-semibold text-[var(--dark-green)]">${projeto.nome || 'Projeto sem nome'}</p>
                                        <p class="text-sm text-gray-600 mt-1">${projeto.descricao ? projeto.descricao : 'Sem descrição cadastrada.'}</p>
                                        <div class="mt-3 flex flex-wrap gap-2 text-[10px] text-gray-600">
                                            <span class="project-metric-badge px-2 py-1 rounded-full bg-white/80">${Number(projeto.total_usuarios || 0)} usuário(s)</span>
                                            <span class="project-metric-badge px-2 py-1 rounded-full bg-white/80">${Number(projeto.total_demandas || 0)} demanda(s)</span>
                                        </div>
                                    </div>
                                    <div class="flex w-full flex-col items-stretch gap-2 sm:w-auto sm:items-end">
                                        <span class="inline-flex w-fit self-start px-2 py-1 text-[10px] font-semibold rounded-full sm:self-auto ${estaVinculado ? 'bg-green-100 text-green-700' : (pendente ? 'bg-amber-100 text-amber-700' : 'bg-gray-200 text-gray-700')}">${statusBadge}</span>
                                        ${estaVinculado ? `<button type="button" data-acao="gerenciar-categorias" data-id="${projeto.id}" class="inline-flex w-full items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border border-[var(--dark-green)] text-[var(--dark-green)] hover:bg-white transition sm:w-auto"><i class="fa-solid fa-tags" aria-hidden="true"></i><span>Categorias</span></button>` : ''}
                                        ${!estaVinculado ? `<button type="button" data-acao="solicitar-vinculo" data-id="${projeto.id}" ${botaoDisabled} class="inline-flex w-full items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg ${corBotao} hover:opacity-90 transition disabled:opacity-60 disabled:cursor-not-allowed sm:w-auto">
                                            <i class="fa-solid ${iconeBotao}" aria-hidden="true"></i>
                                            <span>${textoBotao}</span>
                                        </button>` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');

                    listaProjetosUsuario.querySelectorAll('[data-acao="gerenciar-categorias"]').forEach((btn) => {
                        btn.addEventListener('click', () => {
                            const projeto = projetos.find((item) => Number(item.id) === Number(btn.dataset.id));
                            if (projeto) abrirModalCategorias(projeto);
                        });
                    });

                    listaProjetosUsuario.querySelectorAll('[data-acao="solicitar-vinculo"]').forEach((btn) => {
                        btn.addEventListener('click', async () => {
                            const idProjeto = Number(btn.dataset.id);
                            try {
                                const response = await fetch('../controllers/solicitacao_projeto_controller.php?action=criar_solicitacao', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ id_projeto: idProjeto, mensagem: 'Solicito participação neste projeto.' })
                                });
                                const result = await response.json();
                                if (!response.ok || !result.sucesso) {
                                    throw new Error(result.erro || 'Erro ao solicitar vínculo.');
                                }
                                notify.success(result.message || 'Solicitação criada com sucesso!');
                                await carregarProjetos();
                            } catch (error) {
                                console.error('Erro ao solicitar vínculo:', error);
                                notify.error(' ' + (error.message || 'Erro ao solicitar vínculo.'));
                            }
                        });
                    });

                    if (listaProjetosVinculados) {
                        if (!vinculados.length) {
                            listaProjetosVinculados.innerHTML = '<div class="p-4 rounded-lg border border-dashed border-gray-200 text-gray-500 text-sm">Você ainda não está vinculado a nenhum projeto.</div>';
                        } else {
                            listaProjetosVinculados.innerHTML = `<div class="p-3 rounded-lg bg-white border border-gray-200"><p class="font-semibold text-[var(--dark-green)] mb-2">Projetos vinculados</p><div class="flex flex-wrap gap-2">${vinculados.map((projeto) => `<span class="px-2 py-1 text-[10px] rounded-full bg-green-100 text-green-700">${projeto.nome}</span>`).join('')}</div></div>`;
                        }
                    }

                    if (contador) contador.textContent = String(projetos.length);
                    atualizarVisibilidadeFiltroProjetos();
                    return;
                }

                if (!data.sucesso || !Array.isArray(data.dados)) {
                    lista.innerHTML = '<div class="p-4 rounded-lg border border-dashed border-gray-200 text-gray-500 text-sm">Não foi possível carregar os projetos.</div>';
                    if (contador) contador.textContent = '0';
                    return;
                }

                if (!data.dados.length) {
                    lista.innerHTML = '<div class="p-4 rounded-lg border border-dashed border-gray-200 text-gray-500 text-sm">Nenhum projeto cadastrado.</div>';
                    if (contador) contador.textContent = '0';
                    return;
                }

                data.dados.forEach((projeto) => {
                    if (projeto && projeto.id && Number(projeto.ativo) === 1) {
                        projetosAtivosAdmin[Number(projeto.id)] = projeto.nome || 'Projeto';
                    }
                });
                renderizarProjetosResponsaveis();
                montarFiltroProjetos();
                atualizarVisibilidadeFiltroProjetos();
                atualizarBadgeFiltro();

                lista.innerHTML = data.dados.map((projeto) => `
                    <div class="project-card p-4 rounded-lg border border-gray-200 bg-[var(--suave-green)]/40">
                        <div class="flex flex-col items-stretch justify-between gap-4 sm:flex-row sm:items-start">
                            <div class="flex-1">
                                <p class="font-semibold text-[var(--dark-green)]">${projeto.nome || 'Projeto sem nome'}</p>
                                <p class="text-sm text-gray-600 mt-1">${projeto.descricao ? projeto.descricao : 'Sem descrição cadastrada.'}</p>
                                <div class="mt-3 flex flex-wrap gap-2 text-[10px] text-gray-600">
                                    <span class="project-metric-badge px-2 py-1 rounded-full bg-white/80">${Number(projeto.total_usuarios || 0)} usuário(s)</span>
                                    <span class="project-metric-badge px-2 py-1 rounded-full bg-white/80">${Number(projeto.total_demandas || 0)} demanda(s)</span>
                                </div>
                            </div>
                            <div class="flex w-full flex-col items-stretch gap-2 sm:w-auto sm:items-end">
                                <span class="inline-flex w-fit self-start px-2 py-1 text-[10px] font-semibold rounded-full sm:self-auto ${projeto.ativo == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'}">
                                    ${projeto.ativo == 1 ? 'Ativo' : 'Inativo'}
                                </span>
                                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                                    <button type="button" data-acao="gerenciar-categorias" data-id="${projeto.id}" class="inline-flex w-full items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border border-[var(--dark-green)] text-[var(--dark-green)] hover:bg-white transition sm:w-auto"><i class="fa-solid fa-tags"></i>Categorias</button>
                                    <button type="button" data-acao="editar-projeto" data-id="${projeto.id}" class="inline-flex w-full items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 transition sm:w-auto"><i class="fa-solid fa-pen-to-square"></i>Editar</button>
                                    <button type="button" data-acao="toggle-projeto" data-id="${projeto.id}" data-ativo="${projeto.ativo}" class="inline-flex w-full items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border ${projeto.ativo == 1 ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700'} transition sm:w-auto"><i class="fa-solid ${projeto.ativo == 1 ? 'fa-pause' : 'fa-play'}"></i>${projeto.ativo == 1 ? 'Desativar' : 'Ativar'}</button>
                                    <button type="button" data-acao="excluir-projeto" data-id="${projeto.id}" class="botao-excluir inline-flex w-full items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition sm:w-auto"><i class="fa-solid fa-trash"></i>Excluir</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');

                lista.querySelectorAll('[data-acao="gerenciar-categorias"]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const projeto = data.dados.find((item) => Number(item.id) === Number(btn.dataset.id));
                        if (projeto) abrirModalCategorias(projeto);
                    });
                });

                lista.querySelectorAll('[data-acao="editar-projeto"]').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const id = Number(btn.dataset.id);
                        const projeto = data.dados.find((item) => Number(item.id) === id);
                        if (!projeto) return;

                        abrirModalEditarProjeto(projeto);
                    });
                });

                lista.querySelectorAll('[data-acao="toggle-projeto"]').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const id = Number(btn.dataset.id);
                        const ativo = Number(btn.dataset.ativo) === 1 ? 0 : 1;
                        const projeto = data.dados.find((item) => Number(item.id) === id);
                        if (!projeto) return;

                        const confirmar = await confirmarAcaoSistema(`${ativo === 1 ? 'Ativar' : 'Desativar'} o projeto "${projeto.nome}"?`, 'projeto');
                        if (!confirmar) return;

                        try {
                            const response = await fetch('../controllers/projeto_controller.php?action=atualizar_projeto', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_projeto: id, ativo })
                            });
                            const result = await response.json();
                            if (!response.ok || !result.sucesso) {
                                throw new Error(result.erro || 'Erro ao alterar status do projeto.');
                            }
                            notify.success(result.message || 'Status atualizado com sucesso!');
                            await carregarProjetos();
                            await carregarSolicitacoesProjeto();
                        } catch (error) {
                            console.error('Erro ao alterar status do projeto:', error);
                            notify.error(' ' + (error.message || 'Erro ao alterar status do projeto.'));
                        }
                    });
                });

                lista.querySelectorAll('[data-acao="excluir-projeto"]').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const id = Number(btn.dataset.id);
                        const projeto = data.dados.find((item) => Number(item.id) === id);
                        if (!projeto) return;

                        const confirmar = await confirmarAcaoSistema(`Excluir permanentemente o projeto "${projeto.nome}"?`, 'projeto');
                        if (!confirmar) return;

                        try {
                            const response = await fetch('../controllers/projeto_controller.php?action=excluir_projeto', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_projeto: id })
                            });
                            const result = await response.json();
                            if (!response.ok || !result.sucesso) {
                                throw new Error(result.erro || 'Erro ao excluir projeto.');
                            }
                            notify.success(result.message || 'Projeto excluído com sucesso!');
                            await carregarProjetos();
                            await carregarSolicitacoesProjeto();
                        } catch (error) {
                            console.error('Erro ao excluir projeto:', error);
                            notify.error(' ' + (error.message || 'Erro ao excluir projeto.'));
                        }
                    });
                });

                if (contador) contador.textContent = String(data.dados.length);
            } catch (error) {
                console.error('Erro ao carregar projetos:', error);
                const lista = document.getElementById('listaProjetos');
                const listaAdmin = document.getElementById('listaProjetosAdmin');
                if (lista) {
                    lista.innerHTML = '<div class="p-4 rounded-lg border border-dashed border-red-200 text-red-600 text-sm">Erro ao carregar projetos.</div>';
                }
                if (listaAdmin) {
                    listaAdmin.innerHTML = '<div class="p-4 rounded-lg border border-dashed border-red-200 text-red-600 text-sm">Erro ao carregar projetos.</div>';
                }
            }
        }

        async function carregarSolicitacoesProjeto() {
            const container = document.getElementById('listaSolicitacoesProjeto');
            const contador = document.getElementById('contadorSolicitacoesProjeto');
            if (!container) return;

            try {
                const response = await fetch('../controllers/solicitacao_projeto_controller.php?action=listar_pendentes');
                const data = await response.json();

                if (!response.ok || !data.sucesso || !Array.isArray(data.dados)) {
                    container.innerHTML = '<div class="p-4 rounded-lg border border-dashed border-red-200 text-red-600 text-sm">Não foi possível carregar as solicitações.</div>';
                    if (contador) contador.textContent = '0';
                    return;
                }

                if (!data.dados.length) {
                    container.innerHTML = '<div class="p-4 rounded-lg border border-dashed border-gray-200 text-gray-500 text-sm">Nenhuma solicitação pendente de vínculo para aprovação.</div>';
                    if (contador) contador.textContent = '0';
                    return;
                }

                container.innerHTML = data.dados.map((solicitacao) => {
                    const dataSolicitacao = solicitacao.data_solicitacao ? formatarDataAdm(solicitacao.data_solicitacao) : 'Data indisponível';
                    return `
                        <div class="p-4 rounded-lg border border-gray-200 bg-[var(--suave-green)]/35">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-semibold text-[var(--dark-green)]">${escaparTextoAdm(solicitacao.usuario_nome || 'Usuário')}</p>
                                        <span class="text-[10px] uppercase tracking-wide font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700">Pendente</span>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-1"><strong>Projeto:</strong> ${escaparTextoAdm(solicitacao.projeto_nome || 'Projeto')}</p>
                                    <p class="text-sm text-gray-600 mt-1">${escaparTextoAdm(solicitacao.mensagem || 'Solicitou participação neste projeto.')}</p>
                                    <p class="text-[11px] text-gray-500 mt-2">Solicitado em: ${dataSolicitacao}</p>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button type="button" data-acao="aprovar-solicitacao-projeto" data-id="${solicitacao.id}" class="px-3 py-2 text-xs font-semibold rounded-md border border-green-200 bg-green-50 text-green-700 hover:bg-green-100 transition">Aprovar</button>
                                    <button type="button" data-acao="recusar-solicitacao-projeto" data-id="${solicitacao.id}" class="px-3 py-2 text-xs font-semibold rounded-md border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition">Recusar</button>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                if (contador) contador.textContent = String(data.dados.length);

                container.querySelectorAll('[data-acao="aprovar-solicitacao-projeto"]').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const id = Number(btn.dataset.id);
                        if (!id) return;
                        const confirmar = await confirmarAcaoSistema('Aprovar esta solicitação de vínculo ao projeto?', 'projeto');
                        if (!confirmar) return;

                        try {
                            const response = await fetch('../controllers/solicitacao_projeto_controller.php?action=aprovar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_solicitacao: id })
                            });
                            const result = await response.json();
                            if (!response.ok || !result.sucesso) throw new Error(result.erro || 'Erro ao aprovar solicitação.');
                            notify.success(result.message || 'Solicitação aprovada com sucesso!');
                            await carregarSolicitacoesProjeto();
                            await carregarProjetos();
                        } catch (error) {
                            console.error('Erro ao aprovar solicitação de projeto:', error);
                            notify.error(' ' + (error.message || 'Erro ao aprovar solicitação.'));
                        }
                    });
                });

                container.querySelectorAll('[data-acao="recusar-solicitacao-projeto"]').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const id = Number(btn.dataset.id);
                        if (!id) return;
                        const confirmar = await confirmarAcaoSistema('Recusar esta solicitação de vínculo ao projeto?', 'projeto');
                        if (!confirmar) return;

                        try {
                            const response = await fetch('../controllers/solicitacao_projeto_controller.php?action=recusar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_solicitacao: id, motivo: 'Solicitação recusada pelo administrador master.' })
                            });
                            const result = await response.json();
                            if (!response.ok || !result.sucesso) throw new Error(result.erro || 'Erro ao recusar solicitação.');
                            notify.success(result.message || 'Solicitação recusada com sucesso!');
                            await carregarSolicitacoesProjeto();
                        } catch (error) {
                            console.error('Erro ao recusar solicitação de projeto:', error);
                            notify.error(' ' + (error.message || 'Erro ao recusar solicitação.'));
                        }
                    });
                });
            } catch (error) {
                console.error('Erro ao carregar solicitações de projeto:', error);
                container.innerHTML = '<div class="p-4 rounded-lg border border-dashed border-red-200 text-red-600 text-sm">Erro ao carregar solicitações.</div>';
                if (contador) contador.textContent = '0';
            }
        }

        document.getElementById('formProjeto')?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const nome = document.getElementById('nomeProjeto')?.value.trim() || '';
            const descricao = document.getElementById('descricaoProjeto')?.value.trim() || '';

            if (!nome) {
                notify.warning('⚠️ Informe o nome do projeto.');
                return;
            }

            try {
                const response = await fetch('../controllers/projeto_controller.php?action=criar_projeto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nome, descricao })
                });
                const data = await response.json();

                if (!response.ok || !data.sucesso) {
                    throw new Error(data.erro || 'Erro ao criar projeto.');
                }

                notify.success(data.message || 'Projeto criado com sucesso!');
                document.getElementById('formProjeto')?.reset();
                await carregarProjetos();
                await carregarSolicitacoesProjeto();
            } catch (error) {
                console.error('Erro ao criar projeto:', error);
                notify.error(' ' + (error.message || 'Erro ao criar projeto.'));
            }
        });

        async function alterarStatusUsuario(id, ativo, nomeUsuario = 'usuário') {
            const divModal = document.getElementById('divModalStatusUsuario');
            if (!divModal) return;
            divModal.innerHTML = '';
            
            const desativar = ativo === 0;
            const modal = document.createElement('div');
            modal.className = 'bg-white w-[90%] sm:w-[420px] rounded-2xl shadow-2xl p-6 animate-[modalEffect_1s_cubic-bezier(0.22,1,0.36,1)]';
            modal.innerHTML = `
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 shrink-0 rounded-xl flex items-center justify-center ${desativar ? 'bg-red-100 text-red-600' : 'bg-[var(--suave-green)] text-[var(--dark-green)]'}">
                        <i class="fa-solid ${desativar ? 'fa-user-slash' : 'fa-user-check'} text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.14em] text-gray-500">Confirmação</p>
                        <h3 class="text-xl font-bold ${desativar ? 'text-red-600' : 'text-[var(--dark-green)]'}">${desativar ? 'Desativar usuário?' : 'Ativar usuário?'}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">Você está prestes a ${desativar ? 'desativar' : 'ativar'} ${nomeUsuario}.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" class="btnCancelarStatus px-4 py-2.5 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition">Cancelar</button>
                    <button type="button" class="btnConfirmarStatus px-4 py-2.5 rounded-lg text-white font-semibold transition ${desativar ? 'bg-red-600 hover:bg-red-700' : 'bg-[var(--dark-green)] hover:bg-[var(--dark-green2)]'}">${desativar ? 'Desativar' : 'Ativar'}</button>
                </div>
            `;
            divModal.appendChild(modal);
            
            divModal.classList.remove('hidden');
            shadowSecUsuarios?.classList.remove('hidden');
            bloquearRolagemModal();
            
            const fechar = () => {
                divModal.classList.add('hidden');
                divModal.innerHTML = '';
                shadowSecUsuarios?.classList.add('hidden');
                liberarRolagemModal();
            };
            
            modal.querySelector('.btnCancelarStatus').addEventListener('click', fechar);
            modal.querySelector('.btnConfirmarStatus').addEventListener('click', async () => {
                modal.querySelector('.btnConfirmarStatus').disabled = true;
                fechar();
                
                const action = ativo === 1 ? 'ativar_usuario' : 'desativar_usuario';
                const acaoTexto = ativo === 1 ? 'ativar' : 'desativar';

                try {
                    const response = await fetch('../controllers/usuario_controller.php?action=' + action, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const data = await response.json();

                    if (data.sucesso) {
                        notify[ativo === 1 ? 'success' : 'warning'](`Usuário ${ativo === 1 ? 'ativado' : 'desativado'} com sucesso!`);
                        await carregarUsuarios();
                    } else {
                        notify.error(' ' + (data.erro || `Erro ao ${acaoTexto} usuário.`));
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    notify.error(' Erro de conexão com o servidor.');
                }
            });
        }

        async function alterarTipoUsuario(id, tipo) {
            if (!await confirmarStatusUsuario(1, 'o tipo deste usuário')) return;

            try {
                const response = await fetch('../controllers/usuario_controller.php?action=alterar_tipo_usuario', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, tipo })
                });
                const data = await response.json();
                if (data.sucesso) {
                    notify.info('🔄 ' + (data.message || 'Tipo atualizado com sucesso!'));
                    await carregarUsuarios();
                } else {
                    notify.error(' ' + (data.erro || 'Erro ao alterar tipo.'));
                }
            } catch (error) {
                console.error('Erro:', error);
                notify.error(' Erro de conexão com o servidor.');
            }
        }

        document.getElementById('btnLimparUsuarios')?.addEventListener('click', () => {
            const pesquisa = document.getElementById('usuarioPesquisa');
            const filtroTipo = document.getElementById('usuarioFiltroTipo');
            const filtroProjeto = document.getElementById('usuarioFiltroProjeto');
            const filtroStatus = document.getElementById('usuarioFiltroStatus');
            if (pesquisa) pesquisa.value = '';
            if (filtroTipo) filtroTipo.value = '';
            if (filtroProjeto) filtroProjeto.value = '';
            if (filtroStatus) filtroStatus.value = '';
            carregarUsuarios();
        });
        document.getElementById('btnPaginaUsuariosAnterior')?.addEventListener('click', () => {
            if (paginaUsuariosAtual > 1) {
                paginaUsuariosAtual -= 1;
                renderizarUsuarios();
            }
        });
        document.getElementById('btnPaginaUsuariosProxima')?.addEventListener('click', () => {
            const totalPaginas = Math.max(1, Math.ceil(usuariosAdmin.length / usuariosItensPorPagina));
            if (paginaUsuariosAtual < totalPaginas) {
                paginaUsuariosAtual += 1;
                renderizarUsuarios();
            }
        });
        document.getElementById('usuariosItensPorPagina')?.addEventListener('change', (event) => {
            usuariosItensPorPagina = Number(event.target.value) || 5;
            paginaUsuariosAtual = 1;
            renderizarUsuarios();
        });

        let filtroUsuariosTimeout;
        document.getElementById('usuarioPesquisa')?.addEventListener('input', () => {
            clearTimeout(filtroUsuariosTimeout);
            filtroUsuariosTimeout = setTimeout(carregarUsuarios, 250);
        });
        document.getElementById('usuarioFiltroTipo')?.addEventListener('change', carregarUsuarios);
        document.getElementById('usuarioFiltroProjeto')?.addEventListener('change', carregarUsuarios);
        document.getElementById('usuarioFiltroStatus')?.addEventListener('change', carregarUsuarios);
        document.getElementById('btnAtualizarSolicitacoesCadastro')?.addEventListener('click', carregarSolicitacoesCadastro);

        // ===== FOTO DO PERFIL =====
        let cortadorFoto = null;
        let arquivoFotoSelecionado = null;

        function fecharCorteFoto() {
            document.getElementById('modalCorteFoto')?.classList.add('hidden');
            if (cortadorFoto) {
                cortadorFoto.destroy();
                cortadorFoto = null;
            }
            const input = document.getElementById('inputFotoPerfil');
            if (input) input.value = '';
            arquivoFotoSelecionado = null;
        }

        document.getElementById('btnAlterarFoto')?.addEventListener('click', () => {
            document.getElementById('inputFotoPerfil')?.click();
        });

        document.getElementById('inputFotoPerfil')?.addEventListener('change', (event) => {
            const arquivo = event.target.files?.[0];
            if (!arquivo || !arquivo.type.startsWith('image/')) {
                notify.warning('⚠️ Selecione um arquivo de imagem.');
                return;
            }

            arquivoFotoSelecionado = arquivo;
            const imagem = document.getElementById('imagemParaCorte');
            const modal = document.getElementById('modalCorteFoto');
            if (!imagem || !modal || typeof Cropper === 'undefined') {
                notify.error(' Não foi possível abrir o recorte da imagem.');
                return;
            }

            imagem.onload = () => {
                if (cortadorFoto) cortadorFoto.destroy();
                cortadorFoto = new Cropper(imagem, {
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

        document.getElementById('btnSalvarCorteFoto')?.addEventListener('click', () => {
            if (!cortadorFoto || !arquivoFotoSelecionado) return;

            const canvasRecortado = cortadorFoto.getCroppedCanvas({ width: 800, height: 800, imageSmoothingQuality: 'high' });
            fecharCorteFoto();

            canvasRecortado.toBlob(async (blob) => {
                if (!blob) {
                    notify.error(' Não foi possível preparar a imagem.');
                    return;
                }

                const dados = new FormData();
                dados.append('foto', blob, 'foto-perfil.jpg');
                const botao = document.getElementById('btnSalvarCorteFoto');
                if (botao) botao.disabled = true;

                try {
                    const response = await fetch('../controllers/usuario_controller.php?action=upload_foto', {
                        method: 'POST',
                        body: dados
                    });
                    const resultado = await response.json();

                    if (!resultado.sucesso) {
                        notify.error(' ' + (resultado.erro || 'Erro ao atualizar foto.'));
                        return;
                    }

                    const caminho = '../assets/' + resultado.foto;
                    document.querySelectorAll('.fotoPerfilVisual').forEach((elemento) => {
                        const imagemAtual = document.createElement('img');
                        imagemAtual.className = 'fotoPerfilVisual w-full h-full rounded-full object-cover';
                        imagemAtual.src = caminho;
                        imagemAtual.alt = 'Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>';
                        elemento.replaceWith(imagemAtual);
                    });
                    document.getElementById('btnRemoverFoto')?.removeAttribute('disabled');

                    notify.success(' Foto atualizada com sucesso!');
                    window.atualizarRankingPontuacao?.();
                } catch (error) {
                    console.error('Erro ao enviar foto:', error);
                    notify.error(' Erro de conexão ao atualizar a foto.');
                } finally {
                    if (botao) botao.disabled = false;
                }
            }, 'image/jpeg', 0.9);
        });

        function confirmarRemocaoFoto() {
            return new Promise((resolve) => {
                const modal = document.getElementById('modalConfirmacaoFoto');
                const texto = document.getElementById('textoConfirmacaoFoto');
                const cancelar = document.getElementById('btnCancelarConfirmacaoFoto');
                const confirmar = document.getElementById('btnConfirmarFoto');
                if (!modal || !texto || !cancelar || !confirmar) {
                    resolve(false);
                    return;
                }

                texto.textContent = 'Deseja remover sua foto de perfil?';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                const fechar = (resultado) => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    cancelar.onclick = null;
                    confirmar.onclick = null;
                    resolve(resultado);
                };
                cancelar.onclick = () => fechar(false);
                confirmar.onclick = () => fechar(true);
            });
        }

        document.getElementById('btnRemoverFoto')?.addEventListener('click', async () => {
            if (!await confirmarRemocaoFoto()) return;

            const botao = document.getElementById('btnRemoverFoto');
            let fotoRemovida = false;
            if (botao) botao.disabled = true;

            try {
                const response = await fetch('../controllers/usuario_controller.php?action=remover_foto', {
                    method: 'POST',
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const corpoResposta = await response.text();
                let resultado;
                try {
                    resultado = JSON.parse(corpoResposta);
                } catch (erro) {
                    throw new Error('Resposta inválida do servidor ao remover a foto.');
                }

                if (!resultado.sucesso) {
                    const mensagem = ' ' + (resultado.erro || 'Erro ao remover foto.');
                    notify.error(mensagem);
                    return;
                }

                document.querySelectorAll('.fotoPerfilVisual').forEach((elemento) => {
                    const inicial = document.createElement('span');
                    inicial.className = 'fotoPerfilVisual spanFoto';
                    inicial.textContent = '<?= strtoupper(substr($nomeUsuario, 0, 1)) ?>';
                    elemento.replaceWith(inicial);
                });

                fotoRemovida = true;
                notify.success(' Foto removida com sucesso!');
                window.atualizarRankingPontuacao?.();
            } catch (error) {
                console.error('Erro ao remover foto:', error);
                const mensagem = ' ' + (error.message || 'Erro de conexão ao remover a foto.');
                notify.error(mensagem);
            } finally {
                if (botao) botao.disabled = fotoRemovida;
            }
        });

        document.getElementById('btnFecharCorteFoto')?.addEventListener('click', fecharCorteFoto);
        document.getElementById('btnCancelarCorteFoto')?.addEventListener('click', fecharCorteFoto);

        // ===== SOLICITAÇÕES DE REABERTURA =====

        async function carregarSolicitacoesReabertura() {
            try {
                const response = await fetch('../controllers/demanda_controller.php?action=listar_solicitacoes_reabertura');
                const data = await response.json();

                if (data.sucesso) {
                    renderizarSolicitacoesReabertura(data.dados);
                }
            } catch (error) {
                console.error('Erro ao carregar solicitações:', error);
            }
        }

        function renderizarSolicitacoesReabertura(solicitacoes) {
            const tbody = document.getElementById('reaberturaTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';

            solicitacoes.forEach(s => {
                const tr = document.createElement('tr');
                tr.className = 'border-b hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-sm">#${s.id_demanda}</td>
                    <td class="px-4 py-3 text-sm">${s.demanda_titulo}</td>
                    <td class="px-4 py-3 text-sm">${s.usuario_nome}</td>
                    <td class="px-4 py-3 text-sm">${new Date(s.data_solicitacao).toLocaleDateString('pt-BR')}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 rounded text-xs font-semibold bg-[#fef0c7] text-yellow-800">
                            Pendente
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center space-x-2">
                        <button onclick="abrirDetalhesReabertura(${s.id}, ${s.id_demanda}, '${s.usuario_nome}', '${s.justificativa}')" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                            Ver
                        </button>
                        <button onclick="aprovarReabertura(${s.id})" class="text-green-600 hover:text-green-800 font-semibold text-sm">
                            Aprovar
                        </button>
                        <button onclick="rejeitarReabertura(${s.id})" class="text-red-600 hover:text-red-800 font-semibold text-sm">
                            Rejeitar
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function abrirDetalhesReabertura(id, idDemanda, solicitante, justificativa) {
            notify.info(`ID Solicitação: ${id}\nDemanda: #${idDemanda}\nSolicitante: ${solicitante}\nJustificativa: ${justificativa}`);
        }

        async function aprovarReabertura(id) {
            if (await confirmarAcaoSistema('Tem certeza que deseja aprovar esta solicitação?')) {
                try {
                    const response = await fetch('../controllers/demanda_controller.php?action=aprovar_reabertura', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id_solicitacao: id})
                    });
                    const data = await response.json();

                    if (data.sucesso) {
                        notify.success('Solicitação aprovada!');
                        carregarSolicitacoesReabertura();
                    } else {
                        notify.error('Erro: ' + (data.erro || 'Erro ao aprovar'));
                    }
                } catch (error) {
                    console.error('Erro:', error);
                }
            }
        }

        async function rejeitarReabertura(id) {
            if (await confirmarAcaoSistema('Tem certeza que deseja rejeitar esta solicitação?')) {
                try {
                    const response = await fetch('../controllers/demanda_controller.php?action=rejeitar_reabertura', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id_solicitacao: id})
                    });
                    const data = await response.json();

                    if (data.sucesso) {
                        notify.success('Solicitação rejeitada!');
                        carregarSolicitacoesReabertura();
                    } else {
                        notify.error('Erro: ' + (data.erro || 'Erro ao rejeitar'));
                    }
                } catch (error) {
                    console.error('Erro:', error);
                }
            }
        }

        // Carregar dados na primeira vez
        setTimeout(() => {
            if (document.getElementById('usuariosTableBody')) {
                carregarUsuarios();
                carregarSolicitacoesCadastro();
            }
            if (document.getElementById('reaberturaTableBody')) {
                carregarSolicitacoesReabertura();
            }
        }, 500);
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

            let requisicaoRankingPontuacao = 0;

            async function atualizarRankingPontuacao() {
                const requisicaoAtual = ++requisicaoRankingPontuacao;
                const projetoDaRequisicao = filtroProjetoAtual;
                try {
                    const filtroProjeto = projetoDaRequisicao !== 'todos' ? '&id_projeto=' + encodeURIComponent(projetoDaRequisicao) : '';
                    const resposta = await fetch('../controllers/usuario_controller.php?action=listar_ranking_pontuacao' + filtroProjeto + '&_=' + Date.now(), {
                        credentials: 'same-origin',
                        cache: 'no-store'
                    });
                    if (!resposta.ok) return;
                    const resultado = await resposta.json();
                    if (requisicaoAtual !== requisicaoRankingPontuacao || projetoDaRequisicao !== filtroProjetoAtual) return;
                    if (resultado.sucesso && Array.isArray(resultado.dados)) renderizarRanking(resultado.dados);
                } catch (erro) {
                    console.error('Erro ao atualizar ranking:', erro);
                }
            }

            window.atualizarRankingPontuacao = atualizarRankingPontuacao;
            atualizarRankingPontuacao();
            setInterval(() => {
                if (!document.hidden) atualizarRankingPontuacao();
            }, 15000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) atualizarRankingPontuacao();
            });
        })();
    </script>

    <script>
        (() => {
            document.addEventListener('click', (event) => {
                const modal = event.target.closest('[id^="divModal"], .modal-overlay-animated, .divModalInfs, .divModalInfsSecDemandas');
                const clicouNoShadow = event.target.matches('[id^="shadow"], .shadowDashBoard');
                const clicouForaDoModal = modal && event.target === modal;
                if (!clicouNoShadow && !clicouForaDoModal) return;

                fecharModaisAoTrocarSecao();
            });
        })();
    </script>

</body>

</html>