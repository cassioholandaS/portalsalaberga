<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistema PCD</title>

  <!-- Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    :root {
      --c1: #075b2d;
      --c2: #008f45;
      --c3: #00a651;
      --c4: #31b86a;
      --gold: #ffb000;
      --amber: #f59e0b;
      --peach: #f6a623;
      --bg: #eaf4e7;
      --surface: #ffffff;
      --surface2: #ffffff;
      --border: rgba(7,91,45,0.10);
      --text-muted: #718074;
      --text: #14251c;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--c2); border-radius: 4px; }

    /* SIDEBAR */
    .sidebar {
      width: 258px; min-width: 258px;
      background: #064f2d;
      border-right: 1px solid rgba(255,255,255,0.06);
      display: flex; flex-direction: column;
      position: relative; z-index: 60; overflow: hidden;
      transition:width .22s ease,min-width .22s ease,border-color .22s ease;
    }
    .sidebar-collapse-toggle {
      position:fixed;
      top:50%;
      left:257px;
      z-index:1001;
      display:grid;
      width:30px;
      height:72px;
      place-items:center;
      padding:0;
      border:1px solid rgba(255,255,255,.34);
      border-left:0;
      border-radius:0 10px 10px 0;
      background:linear-gradient(145deg,#0b7042,#064c2b);
      color:#ffd06a;
      box-shadow:4px 0 12px rgba(5,45,27,.25),inset -1px 0 rgba(255,255,255,.34);
      transform:translateY(-50%);
      cursor:pointer;
      transition:left .22s ease,width .18s ease,background .18s ease,box-shadow .18s ease;
    }
    .sidebar-collapse-toggle::before {
      position:absolute;
      top:8px;
      bottom:8px;
      left:3px;
      width:1px;
      background:rgba(255,255,255,.3);
      content:'';
    }
    .sidebar-collapse-toggle:hover {
      width:35px;
      background:linear-gradient(145deg,#0d7a49,#075b34);
      box-shadow:5px 0 15px rgba(5,45,27,.34),inset -1px 0 rgba(255,255,255,.44);
    }
    .sidebar-collapse-toggle:focus-visible {
      outline:2px solid var(--gold);
      outline-offset:2px;
    }
    .sidebar-collapse-toggle i { transition:transform .22s ease; }
    .sidebar-collapse-toggle:hover i { transform:rotate(180deg); }
    body.sidebar-collapsed .sidebar {
      width:0;
      min-width:0;
      border-right-color:transparent;
    }
    body.sidebar-collapsed .sidebar-collapse-toggle { left:0; }
    body.sidebar-collapsed .sidebar-collapse-toggle i { transform:rotate(180deg); }
    body.sidebar-collapsed .sidebar-collapse-toggle:hover i { transform:rotate(0deg); }
    @media (max-width:768px) {
      .sidebar { transition:transform .3s ease; }
      .sidebar-collapse-toggle { display:none; }
    }
    .mobile-menu-btn,
    .sidebar-close-btn {
      display: none;
    }
    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      z-index: 9998;
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
      transition: all 0.3s ease;
    }
    .overlay.show {
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
    }
    .sidebar-brand {
      display:flex; align-items:center; gap:12px; padding:25px 22px 18px;
    }
    .sidebar-brand-mark { width:40px; height:40px; display:flex; align-items:center; justify-content:center; flex:0 0 40px; border:1px solid rgba(255,255,255,0.22); border-radius:10px; background:rgba(255,255,255,0.04); }
    .sidebar-brand img { width:28px; height:28px; object-fit:contain; }
    .sidebar-brand-title { color:#fff; font-size:15px; font-weight:700; line-height:1.1; white-space:nowrap; }
    .sidebar-brand-title span { color:#ffad00; }
    .sidebar-brand-subtitle { color:rgba(255,255,255,0.68); font-size:9.5px; line-height:1.35; margin-top:5px; }
    .sidebar nav { flex:1; min-height:0; padding:4px 0 12px; overflow-y:auto; scrollbar-width:thin; scrollbar-color:rgba(255,255,255,0.22) transparent; }
    .sidebar nav::-webkit-scrollbar { width:4px; }
    .sidebar nav::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.22); border-radius:4px; }
    .sidebar-section {
      width:calc(100% - 36px); text-align:left; font-family:inherit; cursor:pointer;
      display:flex; align-items:center; justify-content:space-between; gap:9px;
      margin:10px 18px 7px; padding:9px 10px;
      color:rgba(255,255,255,0.78); font-size:9.5px; font-weight:700;
      letter-spacing:.75px; text-transform:uppercase;
      border:1px solid rgba(255,255,255,0.14); border-radius:7px;
      background:rgba(255,255,255,0.025);
    }
    .sidebar-section:hover { color:#fff; background:rgba(255,255,255,0.07); }
    .sidebar-section:focus-visible { outline:2px solid var(--gold); outline-offset:2px; }
    .sidebar-section i { color:rgba(255,255,255,0.58); font-size:11px; transition:transform .2s ease; }
    .sidebar-section[aria-expanded="false"] i { transform:rotate(180deg); }
    .sidebar-section-items[hidden] { display:none !important; }
    .nav-item {
      display:flex; align-items:center; gap:10px;
      min-height:41px; padding:6px 10px 6px 12px; border-radius:7px; cursor:pointer;
      transition: all 0.2s ease;
      color: rgba(255,255,255,0.82); font-size:12.5px; font-weight:600;
      border:1px solid transparent; border-left:3px solid transparent; margin:2px 16px;
    }
    .nav-item:hover { color:#fff; background:rgba(255,255,255,0.09); }
    .nav-item.active {
      color:#ffb000; background:rgba(145,184,157,0.30);
      border-color:transparent; border-left-color:#ffb000;
      box-shadow:none;
    }
    .nav-item.active .ni { color: var(--gold); }
    .ni {
      width:25px; height:25px; display:flex; align-items:center; justify-content:center;
      border-radius:5px; font-size:14px; color:rgba(255,255,255,0.76);
      background:rgba(255,255,255,0.08); transition:color 0.2s, background 0.2s; flex-shrink:0;
    }
    .nav-item.active .ni { background:rgba(255,176,0,0.13); }
    .sidebar-footer { padding:12px 0; border-top:1px solid rgba(255,255,255,0.10); }
    .sidebar-account-tools { display:flex; align-items:center; gap:8px; position:relative; padding:0 16px 10px; }
    .sidebar-account-user {
      display:flex; align-items:center; gap:9px; flex:1; min-width:0; padding:7px 8px;
      border:1px solid rgba(255,255,255,0.12); border-radius:9px; background:rgba(255,255,255,0.05);
      color:#fff; text-align:left; cursor:pointer;
    }
    .sidebar-account-user > span:last-child { display:block; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .sidebar-account-avatar { width:30px; height:30px; flex:0 0 30px; overflow:hidden; font-size:12px; }
    .sidebar-notification-button {
      display:flex; width:40px; height:40px; flex:0 0 40px; align-items:center; justify-content:center;
      position:relative; border:1px solid rgba(255,255,255,0.16); border-radius:9px;
      background:rgba(255,255,255,0.07); color:#f5fff8; cursor:pointer;
    }
    .sidebar-notification-button:hover { background:rgba(255,255,255,0.14); }
    .sidebar-logout { color:#ff927f; }
    .sidebar-logout .ni { color:inherit; }
    .sidebar-logout:hover { color:#ffad9e; background:rgba(255,255,255,0.08); }

    /* MAIN */
    .main { flex:1; display:flex; flex-direction:column; overflow:hidden; }

    /* CONTENT */
    .content { flex:1; overflow-y:auto; padding:26px 30px; }

    /* PAGES */
    .page { display:none; animation: pgIn 0.28s ease; }
    .page.active { display:block; }
    @keyframes pgIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

    /* CARD */
    .card {
      background:var(--surface2); border:1px solid var(--border);
      border-radius:16px; transition:border-color 0.2s;
    }
    .card:hover { border-color:rgba(76,175,80,0.26); }
      .student-profile-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(245px,300px)); justify-content:start; gap:14px; padding:18px; }
      .student-profile-block { background:var(--surface2); border:1px solid var(--border); border-radius:14px; padding:16px; cursor:pointer; transition:transform .18s, border-color .18s, box-shadow .18s; }
      .student-profile-block:hover, .student-profile-block:focus-visible { border-color:rgba(0,166,81,.5); box-shadow:0 8px 20px rgba(7,91,45,.08); transform:translateY(-2px); outline:none; }
      .student-profile-block__top, .student-profile-block__identity, .student-profile-block__condition, .student-profile-block__details { display:flex; align-items:center; }
      .student-profile-block__top { justify-content:space-between; gap:10px; margin-bottom:16px; }
      .student-profile-block__identity { gap:10px; min-width:0; }
      .student-profile-block__avatar { width:42px; height:42px; flex:0 0 42px; font-size:15px; overflow:hidden; }
      .student-profile-block__name { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:14px; font-weight:700; color:var(--text); }
      .student-profile-block__arrow { color:var(--c3); font-size:15px; }
      .student-profile-block__condition { justify-content:space-between; gap:10px; padding-bottom:13px; border-bottom:1px solid var(--border); }
      .student-profile-block__conditions { display:flex; flex-wrap:wrap; justify-content:center; gap:5px; }
      .student-dashboard-block__conditions { display:flex; flex-wrap:wrap; gap:5px; margin-top:7px; }
      .student-profile-block__details { align-items:stretch; gap:10px; padding-top:13px; }
      .student-profile-block__details > div { display:flex; flex:1; flex-direction:column; align-items:center; gap:4px; min-width:0; text-align:center; }
      .student-profile-block__label { color:var(--text-muted); font-size:10px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
      .student-profile-block__details strong { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text); font-size:12px; }
      .student-profile-block__empty-value { color:var(--text-muted); font-size:12px; }
      .document-view-form--hidden { display:none !important; }
      .document-summary-block { display:flex; align-items:center; justify-content:space-between; gap:14px; padding:14px 16px; border:1px solid var(--border); border-radius:12px; background:rgba(255,255,255,.02); }
      .document-summary-block:hover { border-color:rgba(0,166,81,.35); }
      .apoio-quesito-trigger { display:flex; align-items:center; justify-content:space-between; gap:14px; width:100%; padding:14px 16px; border:1px solid var(--border); border-radius:10px; background:rgba(255,255,255,.02); color:var(--text); text-align:left; cursor:pointer; font-family:inherit; }
      .apoio-quesito-trigger:hover { border-color:rgba(0,166,81,.45); background:rgba(0,166,81,.04); }
      .apoio-quesito-trigger strong, .apoio-quesito-trigger small { display:block; }
      .apoio-quesito-trigger strong { font-size:13px; }
      .apoio-quesito-trigger small { margin-top:4px; color:var(--text-muted); font-size:11px; }
      .apoio-quesito-trigger em { margin-left:6px; color:var(--gold); font-style:normal; }
      .apoio-quesito-status { display:inline-flex; align-items:center; gap:7px; flex-shrink:0; color:var(--text-muted); font-size:12px; }
      .apoio-quesito-status--filled { color:var(--c3); font-weight:700; }
      .apoio-quesitos-leitura { margin-bottom:18px; padding:14px; border:1px solid var(--border); border-radius:12px; background:rgba(0,166,81,.035); }
      .apoio-quesitos-leitura__cabecalho { display:flex; align-items:center; gap:8px; margin-bottom:10px; color:var(--c1); font-size:13px; font-weight:800; }
      .apoio-quesitos-leitura__cabecalho i { color:var(--c3); }
      .apoio-quesitos-leitura__lista { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:7px; }
      .apoio-quesito-leitura-item { display:flex; align-items:center; gap:8px; min-width:0; padding:8px 10px; border:1px solid rgba(7,91,45,.10); border-radius:9px; background:#fff; color:var(--text); font-size:11px; }
      .apoio-quesito-leitura-item i { color:var(--c3); font-size:12px; }
      .apoio-quesito-leitura-item span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
      .apoio-quesito-check-leitura { width:14px; height:14px; flex:0 0 14px; accent-color:#00a651; cursor:not-allowed; }
      .apoio-quesito-check-leitura:checked { accent-color:#00a651; }
      .apoio-avaliacao-btn { position:relative; z-index:2; flex:1; justify-content:center; pointer-events:auto; }
      .apoio-avaliacao-btn--active[data-apoio-avaliacao="feliz"] { background:#dcfce7; border-color:#16a34a; color:#166534; }
      .apoio-avaliacao-btn--active[data-apoio-avaliacao="neutro"] { background:#e5e7eb; border-color:#6b7280; color:#374151; }
      .apoio-avaliacao-btn--active[data-apoio-avaliacao="triste"] { background:#fee2e2; border-color:#dc2626; color:#991b1b; }
      .apoio-avaliacao-btn[data-apoio-avaliacao="feliz"]:hover { background:#dcfce7; border-color:#16a34a; color:#166534; }
      .apoio-avaliacao-btn[data-apoio-avaliacao="neutro"]:hover { background:#e5e7eb; border-color:#6b7280; color:#374151; }
      .apoio-avaliacao-btn[data-apoio-avaliacao="triste"]:hover { background:#fee2e2; border-color:#dc2626; color:#991b1b; }
      .document-summary-block--apoio { display:block; width:min(100%,360px); }
      .document-summary-block--apoio > div:first-child { margin-bottom:10px; }
      .avaliacao-layout { display:grid; grid-template-columns:270px minmax(0,1fr); gap:18px; align-items:start; }
      .avaliacao-aluno-card { padding:22px; text-align:center; position:sticky; top:0; }
      .avaliacao-aluno-avatar { width:70px; height:70px; margin:0 auto 14px; font-size:26px; overflow:hidden; background-size:cover; background-position:center; box-shadow:0 0 0 3px rgba(255,215,0,.28),0 0 0 6px rgba(255,215,0,.08); }
      .avaliacao-aluno-info { text-align:left; font-size:12px; margin-top:14px; }
      .avaliacao-aluno-info > div { display:flex; justify-content:space-between; gap:10px; padding:8px 0; border-bottom:1px solid var(--border); }
      .avaliacao-aluno-info > div:last-child { border-bottom:0; }
      .avaliacao-aluno-info span { color:var(--text-muted); }
      .avaliacao-aluno-info strong { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
      .avaliacao-conteudo { min-width:0; }
      .avaliacao-cabecalho { padding:0 0 8px; margin:0; border-left:0; }
      .avaliacao-cabecalho-data { flex-shrink:0; }
      .avaliacao-dia-secao {
        overflow:hidden;
        padding:20px;
        background:var(--surface2);
        border:1px solid rgba(0,143,69,.18);
        border-radius:16px;
        box-shadow:0 8px 24px rgba(7,91,45,.06);
      }
      .avaliacao-dia-secao .avaliacao-cabecalho { margin:0; padding:0 0 8px; border:0; border-bottom:0; border-radius:0; background:transparent; box-shadow:none; }
      .avaliacao-cabecalho-titulo {
        display:flex;
        align-items:center;
        gap:8px;
        color:#1f2a23;
        font-size:14px;
        font-weight:700;
        line-height:1.3;
      }
      .avaliacao-cabecalho-titulo i { color:var(--c3); font-size:14px; }
      .avaliacao-cabecalho-subtitle {
        display:flex;
        align-items:center;
        gap:8px;
        margin-top:10px;
        padding-top:0;
        color:var(--c1);
        font-size:13px;
        font-weight:800;
        line-height:1.2;
        min-height:16px;
      }
      .avaliacao-cabecalho-subtitle i { font-size:13px; color:inherit; }
      .avaliacao-cabecalho-subtitle::after {
        content:'';
        height:1px;
        flex:1;
        background:var(--border);
        margin-left:8px;
      }
      .avaliacao-cabecalho-titulo + .avaliacao-cabecalho-subtitle {
        display:flex;
      }
      .avaliacao-dia-lista { padding:0 12px 8px; gap:12px; }
      .avaliacao-periodos { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
      .avaliacao-dia-lista > .avaliacao-dia-card + .avaliacao-dia-card { border-left:4px solid var(--gold); }
      .avaliacao-dia-card {
        min-height:116px;
        position:relative;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
        gap:12px;
        border:1px solid var(--border);
        border-left:4px solid #f4b400 !important;
        border-radius:12px;
        padding:14px 16px 12px;
        background:#fbfdfb;
        box-shadow:0 3px 12px rgba(7,91,45,.035);
        transition:background .18s, border-color .18s;
      }
      .avaliacao-dia-card > div:first-child {
        min-height:42px;
        display:flex;
        align-items:center;
      }
      .avaliacao-card-cabecalho { display:flex; align-items:center; min-height:54px; padding-right:38px; }
      .avaliacao-card-identidade { display:flex; align-items:center; gap:11px; min-width:0; }
      .avaliacao-card-periodo { display:flex; flex-direction:column; justify-content:center; gap:5px; min-width:0; }
      .avaliacao-card-titulo { color:#102a1b; font-size:14px; font-weight:800; line-height:1.15; letter-spacing:.02em; text-transform:uppercase; }
      .avaliacao-card-data { display:flex; align-items:center; gap:5px; color:var(--text-muted); font-size:11px; font-weight:600; line-height:1; }
      .avaliacao-card-data i { color:var(--c3); font-size:11px; }
      .avaliacao-card-rodape { display:flex; align-items:center; justify-content:space-between; gap:12px; width:100%; min-height:32px; margin-top:10px; }
      .avaliacao-card-rodape > .avaliacao-acao-principal,
      .avaliacao-card-rodape > .avaliacao-acoes { margin-left:auto; }
      .avaliacao-dia-card--pendente { background:#f8f9f8; border-left-color:#f4b400 !important; }
      .avaliacao-dia-card--pendente:hover { background:#f2f5f3; }
      .avaliacao-dia-card--avaliado { background:#edf9f0; border-left-color:#16a34a !important; }
      .avaliacao-dia-card--avaliado:hover { background:#e5f5e9; }
      .avaliacao-dia-card--bloqueado { background:#f7f8f7; border-left-color:#6b7280 !important; }
      .avaliacao-editar-btn { background:#c62828 !important; border-color:#b71c1c !important; color:#fff !important; }
      .avaliacao-editar-btn:hover { background:#a61b1b !important; border-color:#8f1717 !important; color:#fff !important; }
      .avaliacao-acao-principal { min-height:36px !important; padding:9px 16px !important; border:1px solid transparent !important; border-radius:10px !important; font-size:12px !important; font-weight:700 !important; line-height:1.2; transition:transform .2s, box-shadow .2s, background .2s, border-color .2s !important; }
      .avaliacao-acao-principal:hover { transform:translateY(-1px); box-shadow:0 5px 12px rgba(7,91,45,.22); }
      .avaliacao-acao-indisponivel,
      .avaliacao-acao-indisponivel:hover,
      .avaliacao-acao-indisponivel:disabled { background:#e8eeea !important; border-color:#cbd8ce !important; color:#718074 !important; cursor:not-allowed; opacity:.82; transform:none !important; box-shadow:none !important; }
      .avaliacao-dia-card .btn-p.avaliacao-acao-indisponivel,
      .avaliacao-dia-card .btn-p.avaliacao-acao-indisponivel:hover { background:#e8eeea !important; border-color:#cbd8ce !important; color:#718074 !important; }
      .avaliacao-dia-card .btn-p.avaliacao-acao-principal.avaliacao-acao-indisponivel,
      .avaliacao-dia-card .btn-p.avaliacao-acao-principal.avaliacao-acao-indisponivel:hover { background:#e8eeea !important; border-color:#cbd8ce !important; color:#718074 !important; cursor:not-allowed; transform:none !important; box-shadow:none !important; }
      .avaliacao-dia-card .btn-g.avaliacao-acao-principal:hover { background:#a61b1b !important; border-color:#8f1717 !important; color:#fff !important; }
      .avaliacao-dia-card .btn-p.avaliacao-acao-principal { background:linear-gradient(135deg,var(--c2),var(--c3)) !important; border-color:var(--c3) !important; color:#fff !important; }
      .avaliacao-dia-card .btn-p.avaliacao-acao-principal:hover { background:linear-gradient(135deg,var(--c3),var(--c2)) !important; border-color:var(--c3) !important; color:#fff !important; }
      .avaliacao-historico-periodos {
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        gap:12px;
      }
      .avaliacao-historico-periodos .avaliacao-dia-card { border:1px solid var(--border); border-left:4px solid #f4b400; border-radius:12px; background:#fbfdfb; box-shadow:0 3px 12px rgba(7,91,45,.035); }
      .avaliacao-historico-periodos .avaliacao-dia-card:hover { background:#f5fbf6; }
      .avaliacao-historico-card { cursor:pointer; }
      .avaliacao-historico-card button { cursor:pointer; }
      .avaliacao-historico-periodos .avaliacao-dia-card + .avaliacao-dia-card { border-left:4px solid var(--gold); }
      .avaliacao-historico-periodos .avaliacao-dia-card.avaliacao-dia-card--avaliado { border-left-color:#16a34a; }
      .avaliacao-periodo-icone {
        width:30px;
        height:30px;
        display:flex;
        align-items:center;
        justify-content:center;
        border-radius:10px;
        background:rgba(255,176,0,.12);
        color:#a66d00;
        font-size:14px;
        flex-shrink:0;
      }
      .avaliacao-dia-card .tag { white-space:nowrap; }
      .avaliacao-status {
        display:inline-flex;
        align-items:center;
        gap:7px;
        font-size:11px;
        font-weight:700;
        color:var(--text-muted);
        margin-top:auto;
      }
      .avaliacao-status--ok { color:#08743b; }
      .avaliacao-status--bloqueado { color:#718074; }
      .avaliacao-dia-card .avaliacao-acoes {
        display:flex;
        align-items:center;
        justify-content:flex-end;
        gap:8px;
        margin:0 0 0 auto;
        padding:0;
      }
      .avaliacao-dia-card .btn-p,
      .avaliacao-dia-card .btn-g,
      .avaliacao-dia-card .avaliacao-editar-btn,
      .avaliacao-dia-card .avaliacao-excluir-btn {
        min-height:32px;
        padding:8px 14px;
        font-size:12px;
        border-radius:10px;
      }
      .avaliacao-dia-card .avaliacao-excluir-btn { position:absolute; top:10px; right:10px; z-index:3; width:34px; height:34px; min-height:34px; padding:0 !important; display:inline-flex; align-items:center; justify-content:center; border:1px solid rgba(198,40,40,.32) !important; border-radius:10px; background:#fff5f5 !important; box-shadow:0 2px 6px rgba(198,40,40,.08); }
      .avaliacao-dia-card .avaliacao-excluir-btn:hover { background:#fee2e2 !important; border-color:#c62828 !important; transform:translateY(-1px); box-shadow:0 4px 10px rgba(198,40,40,.16); }
      .avaliacao-dia-card .avaliacao-excluir-btn i { color:#c62828; font-size:14px; }
      .avaliacao-dia-card .avaliacao-acao-principal { margin-left:auto; min-width:92px; justify-content:center; }
      .avaliacao-card-rodape > .avaliacao-acoes > .avaliacao-acao-principal { margin-right:-4px; }
      .avaliacao-opcoes { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:8px; }
      .avaliacao-opcao { position:relative; }
      .avaliacao-opcao input { position:absolute; opacity:0; pointer-events:none; }
      .avaliacao-opcao label { min-height:76px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:5px; padding:8px 5px; border:1px solid var(--border); border-radius:10px; background:#fbfdfb; color:#52675a; font-size:11px; font-weight:700; cursor:pointer; transition:all .16s; }
      .avaliacao-opcao label:hover { border-color:rgba(0,166,81,.42); background:#f1faf3; }
      .avaliacao-opcao input:focus-visible + label { outline:3px solid rgba(0,166,81,.22); outline-offset:2px; }
      .avaliacao-opcao input:checked + label { border-color:var(--c3); background:#e8f8ed; color:#075b2d; box-shadow:0 0 0 2px rgba(0,166,81,.12); }
      .avaliacao-opcao-emoji { font-size:23px; line-height:1; }
      .avaliacao-historico-dia { margin-top:20px; }
      .avaliacao-historico-data { display:flex; align-items:center; gap:10px; margin-bottom:10px; color:var(--c1); font-size:13px; font-weight:800; }
      .avaliacao-historico-data::after { content:''; height:1px; flex:1; background:var(--border); }
      .avaliacao-historico-topo { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:16px; }
      .avaliacao-historico-titulo { display:flex; align-items:center; gap:8px; min-width:0; }
      .avaliacao-historico-filtro { display:flex; align-items:center; gap:6px; flex-shrink:0; }
      .avaliacao-historico-filtro-campo { width:154px; }
      .avaliacao-historico-filtro .lbl { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0,0,0,0); }
      .avaliacao-historico-filtro .inp { min-height:34px; padding:6px 8px; font-size:11px; }
      .avaliacao-historico-filtro .btn-g { width:34px; min-height:34px; padding:6px; justify-content:center; }
      .avaliacao-historico-filtro .btn-g { font-size:0; }
      .avaliacao-historico-filtro .btn-g i { font-size:13px; }
      .avaliacao-data-picker { position:relative; }
      .avaliacao-data-picker-trigger { display:flex; align-items:center; justify-content:space-between; gap:7px; width:154px; min-height:34px; padding:6px 9px !important; color:#365342; background:#fbfdfb; border:1px solid rgba(7,91,45,.16); border-radius:10px; font:600 11px 'Poppins',sans-serif; cursor:pointer; text-align:left; }
      .avaliacao-data-picker-trigger:hover, .avaliacao-data-picker-trigger:focus-visible { border-color:var(--c3); box-shadow:0 0 0 3px rgba(0,166,81,.14); outline:0; }
      .avaliacao-data-picker-trigger i { color:var(--c3); font-size:13px; }
      .avaliacao-data-picker-popup { position:absolute; top:calc(100% + 8px); right:0; z-index:20; display:none; width:270px; padding:13px; border:1px solid rgba(7,91,45,.16); border-radius:12px; background:#fff; box-shadow:0 12px 30px rgba(7,91,45,.18); }
      .avaliacao-data-picker-popup.is-open { display:block; animation:mslide .16s ease; }
      .avaliacao-data-picker-cabecalho { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px; }
      .avaliacao-data-picker-mes { color:var(--c1); font-size:12px; font-weight:800; text-transform:capitalize; }
      .avaliacao-data-picker-nav { width:27px; height:27px; display:inline-flex; align-items:center; justify-content:center; padding:0; border:1px solid var(--border); border-radius:8px; background:#f7faf6; color:var(--c1); cursor:pointer; }
      .avaliacao-data-picker-nav:hover { border-color:var(--c3); background:#eef6ec; }
      .avaliacao-data-picker-semana, .avaliacao-data-picker-dias { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; text-align:center; }
      .avaliacao-data-picker-semana { margin-bottom:4px; color:var(--text-muted); font-size:9px; font-weight:700; text-transform:uppercase; }
      .avaliacao-data-picker-dia { min-height:28px; display:flex; align-items:center; justify-content:center; border:1px solid transparent; border-radius:7px; background:transparent; color:var(--text); font:600 10px 'Poppins',sans-serif; cursor:pointer; }
      .avaliacao-data-picker-dia:hover:not(:disabled) { border-color:rgba(0,166,81,.35); background:#eef9f0; color:var(--c1); }
      .avaliacao-data-picker-dia.is-selected { background:var(--c3); color:#fff; }
      .avaliacao-data-picker-dia.is-today { border-color:var(--gold); }
      .avaliacao-data-picker-dia.is-outside { color:#b5c0b8; }
      .avaliacao-data-picker-dia:disabled { cursor:not-allowed; opacity:.32; }
      .avaliacao-data-picker-limpar { width:100%; margin-top:9px; padding:5px; border:0; border-top:1px solid var(--border); background:transparent; color:var(--c1); font:600 10px 'Poppins',sans-serif; cursor:pointer; }
      .avaliacao-data-picker-limpar:hover { color:var(--c3); }
      .avaliacao-historico-card .avaliacao-periodo-icone,
      .avaliacao-dia-card .avaliacao-periodo-icone { width:30px; height:30px; font-size:14px; }
      .avaliacao-historico-card .avaliacao-status,
      .avaliacao-dia-card .avaliacao-status { font-size:11px; }
      .avaliacao-acoes { display:flex; align-items:center; justify-content:flex-end; gap:7px; flex-wrap:wrap; }
      .avaliacao-excluir-btn { background:#fff1f1 !important; border-color:rgba(198,40,40,.28) !important; color:#b42318 !important; }
      .avaliacao-excluir-btn:hover { background:#fee2e2 !important; border-color:#c62828 !important; }
      .avaliacao-modal-conteudo { overflow:visible; padding-right:0; }
      .avaliacao-modal-box { max-width:600px !important; max-height:none !important; overflow:visible !important; padding:16px 18px !important; border-top:4px solid var(--c3); box-shadow:0 24px 70px rgba(0,45,22,.28); }
      .avaliacao-modal-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding-bottom:9px; margin-bottom:4px; border-bottom:1px solid var(--border); }
      .avaliacao-modal-heading { display:flex; align-items:center; gap:9px; min-width:0; }
      .avaliacao-modal-heading-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; flex:0 0 32px; border-radius:10px; background:rgba(0,166,81,.1); color:var(--c3); font-size:15px; }
      .avaliacao-modal-title { color:#102a1b; font-size:15px; font-weight:800; line-height:1.2; }
      .avaliacao-modal-meta { margin-top:3px; color:var(--text-muted); font-size:10px; font-weight:600; }
      .avaliacao-modal-footer { display:flex; gap:8px; margin-top:10px; }
      .avaliacao-modal-footer > button { min-height:34px; padding:7px 12px !important; justify-content:center; font-size:12px; }
      .avaliacao-modal-footer .btn-p { flex:1.4; }
      .avaliacao-modal-footer .btn-g { flex:1; }
      .avaliacao-opcoes { grid-template-columns:repeat(3,minmax(0,1fr)); width:min(100%,460px); margin:0 auto; gap:6px; }
      .avaliacao-opcao label { min-height:54px; padding:5px 4px; gap:2px; font-size:10px; }
      .avaliacao-opcao-emoji { font-size:18px; }
      .avaliacao-modal-conteudo fieldset { padding:7px 10px 8px !important; margin:0 0 3px !important; border:1px solid rgba(7,91,45,.08) !important; border-radius:10px; background:rgba(0,166,81,.018); }
      .avaliacao-modal-conteudo fieldset:last-child { border-bottom:0 !important; }
      .avaliacao-modal-conteudo legend { color:#173b25; font-size:12px !important; margin-bottom:2px !important; }
      .avaliacao-modal-conteudo fieldset > div:first-of-type { margin-bottom:5px !important; font-size:10px !important; }
      .avaliacao-modal-conteudo > label { display:block; margin-top:7px !important; margin-bottom:4px; font-size:10px; }
      .avaliacao-modal-conteudo textarea:disabled { opacity:.82; background:#f2f7f3; color:var(--text); }
      #confirm-modal .modal-box { border-top:4px solid #d39a00; box-shadow:0 24px 70px rgba(65,45,0,.2); }
      #confirm-modal .confirm-modal-icon { width:58px; height:58px; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; border-radius:16px; background:rgba(255,176,0,.12); border:1px solid rgba(255,176,0,.28); }
      #confirm-modal .confirm-modal-icon i { color:#b97800 !important; font-size:22px !important; }
      #confirm-modal .confirm-modal-title { color:#173b25; font-size:17px !important; }
      #confirm-modal #confirm-msg { line-height:1.55; }
      #confirm-modal #confirm-ok { min-height:38px; background:#c62828 !important; }
      #confirm-modal #confirm-ok:hover { background:#a61b1b !important; }
      @media (max-width:600px) {
        .apoio-quesitos-leitura__lista { grid-template-columns:1fr; }
        .avaliacao-dia-card { padding:13px 14px 12px; }
        .avaliacao-historico-topo { align-items:flex-start; flex-direction:column; }
        .avaliacao-historico-filtro { width:100%; }
        .avaliacao-historico-filtro-campo { flex:1; width:auto; }
        .avaliacao-data-picker-trigger { width:100%; }
        .avaliacao-data-picker-popup { left:0; right:auto; width:min(270px,calc(100vw - 52px)); }
        .avaliacao-historico-filtro .btn-g { flex:0 0 34px; }
        .avaliacao-card-rodape { align-items:flex-start; flex-direction:column; }
        .avaliacao-card-rodape .avaliacao-acoes { width:auto; margin-left:auto; }
        .avaliacao-card-rodape .avaliacao-acoes button { flex:0 0 auto; justify-content:center; }
        .avaliacao-card-rodape > .avaliacao-acao-principal { margin-left:auto; }
        .avaliacao-modal-box { max-width:calc(100vw - 20px) !important; padding:14px !important; overflow:visible !important; }
        .avaliacao-opcoes { width:100%; }
        .avaliacao-modal-footer { flex-direction:column-reverse; }
        .avaliacao-modal-footer > button { width:100%; }
      }

    /* STAT CARD */
    .stat-card {
      background:var(--surface2); border:1px solid rgba(7,91,45,0.10);
      border-left:5px solid var(--c3); border-radius:16px; padding:22px 24px;
      transition:transform 0.2s, box-shadow 0.2s, border-color 0.2s;
      position:relative; overflow:hidden; box-shadow:0 5px 18px rgba(7,91,45,0.04);
    }
    .stat-card::after { content:''; position:absolute; right:-24px; bottom:-38px; width:110px; height:110px; border-radius:50%; background:rgba(0,166,81,0.045); pointer-events:none; }
    .stat-card:hover { transform:translateY(-3px); box-shadow:0 14px 30px rgba(7,91,45,0.10); }
    .stat-card--gold { border-left-color:var(--gold); }
    .stat-card--gold::after { background:rgba(255,176,0,0.08); }
    .stat-card--green { border-left-color:var(--c3); }
    .stat-card--orange { border-left-color:var(--peach); }
    .stat-card--orange::after { background:rgba(246,166,35,0.08); }
    .dashboard-secondary-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; margin-bottom:18px; }
    .dashboard-panel { position:relative; overflow:hidden; min-height:244px; border-left:5px solid var(--gold); box-shadow:0 5px 18px rgba(7,91,45,0.04); }
    .dashboard-panel::after { content:''; position:absolute; right:-32px; bottom:-48px; width:142px; height:142px; border-radius:50%; background:rgba(255,176,0,0.07); pointer-events:none; }
    .dashboard-panel > * { position:relative; z-index:1; }
    .dashboard-panel--green { border-left-color:var(--c3); }
    .dashboard-panel--green::after { background:rgba(0,166,81,0.06); }
    .diag-list { display:flex; flex-direction:column; gap:15px; padding-top:2px; }
    .diag-item { position:relative; }
    .diag-item-top { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:8px; }
    .diag-item-label { display:flex; align-items:center; gap:9px; min-width:0; font-size:13px; font-weight:600; color:#294235; }
    .diag-item-label > span:last-child { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .diag-item-icon { width:27px; height:27px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; border-radius:8px; background:rgba(255,176,0,0.12); color:#b97800; font-size:12px; }
    .diag-item-count { display:flex; align-items:baseline; gap:3px; color:var(--c1); font-size:17px; line-height:1; }
    .diag-item-count small { color:var(--text-muted); font-size:10px; font-weight:500; }
    .diag-item-track { height:7px; overflow:hidden; border-radius:99px; background:rgba(7,91,45,0.08); }
    .diag-item-track span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--gold),#ffd66b); }
    .evolucao-dashboard-list { display:flex; flex-direction:column; gap:13px; max-height:205px; overflow-y:auto; padding-right:3px; }
    .evolucao-dashboard-aluno { padding-bottom:10px; border-bottom:1px solid var(--border); }
    .evolucao-dashboard-aluno:last-child { padding-bottom:0; border-bottom:0; }
    .evolucao-dashboard-aluno-nome { display:flex; align-items:center; gap:7px; margin-bottom:9px; color:#294235; font-size:12px; font-weight:700; }
    .evolucao-dashboard-aluno-nome i { color:var(--c2); font-size:13px; }
    .evolucao-dashboard-item { margin-top:8px; }
    .evolucao-dashboard-item-top { display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:11px; }
    .evolucao-dashboard-item-title { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#52675a; }
    .evolucao-dashboard-score { flex-shrink:0; font-size:11px; font-weight:800; }
    .evolucao-dashboard-track { height:7px; margin-top:5px; overflow:hidden; border-radius:99px; background:linear-gradient(90deg,#f3c4bd 0 50%,#d7eddf 50% 100%); }
    .evolucao-dashboard-track span { display:block; height:100%; border-radius:99px; background:var(--c2); transition:width .25s ease; }

    /* BUTTONS */
    .btn-p {
      background:linear-gradient(135deg, var(--c2), var(--c3));
      color:#fff; border:none; border-radius:10px;
      padding:9px 20px; font-size:13.5px; font-weight:600;
      cursor:pointer; transition:all 0.2s; font-family:'Poppins',sans-serif;
      display:inline-flex; align-items:center; gap:7px;
    }
    .btn-p:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,122,51,0.38); filter:brightness(1.08); }
    .btn-p:active { transform:none; filter:brightness(0.97); }
    .btn-g {
      background:#f7faf6; border:1px solid rgba(7,91,45,0.14);
      color:#365342; border-radius:10px;
      padding:8px 16px; font-size:13px; font-weight:500;
      cursor:pointer; transition:all 0.2s; font-family:'Poppins',sans-serif;
      display:inline-flex; align-items:center; gap:7px;
    }
    .btn-g:hover { background:#eef6ec; color:var(--c1); border-color:rgba(0,166,81,0.28); }

    /* INPUTS */
    .inp {
      background:#fbfdfb; border:1px solid rgba(7,91,45,0.16);
      color:var(--text); border-radius:10px;
      padding:10px 15px; font-size:13.5px; font-family:'Poppins',sans-serif;
      width:100%; outline:none; transition:all 0.2s;
    }
    input[type="date"].inp { color-scheme:light; color:#365342; }
    input[type="date"].inp::-webkit-calendar-picker-indicator { opacity:.72; cursor:pointer; filter:none; }
    .inp::placeholder { color:#9aa99d; }
    .inp:focus { border-color:var(--c3); box-shadow:0 0 0 3px rgba(0,166,81,0.14); background:#fff; }
    .apoio-periodo-radio { accent-color:var(--c3); }
    .apoio-periodo-radio:disabled { cursor:not-allowed; opacity:.45; }
    select.inp option { background:#fff; color:var(--text); }
    textarea.inp { resize:vertical; min-height:80px; }
    .lbl { font-size:11.5px; font-weight:600; color:#5f7465; margin-bottom:5px; display:block; letter-spacing:0.3px; }
    .multi-select { position:relative; }
    .multi-select-toggle { min-height:42px; display:flex; align-items:center; justify-content:space-between; gap:8px; cursor:pointer; text-align:left; }
    .multi-select-toggle:focus { border-color:var(--c3); box-shadow:0 0 0 3px rgba(0,166,81,0.14); }
    .multi-select-values { display:flex; align-items:center; gap:5px; flex-wrap:wrap; min-width:0; }
    .multi-select-placeholder { color:#718074; }
    .multi-select-menu { position:absolute; top:calc(100% + 5px); left:0; right:0; z-index:10; display:none; max-height:190px; overflow-y:auto; padding:6px; background:#fff; border:1px solid rgba(0,166,81,0.3); border-radius:10px; box-shadow:0 10px 24px rgba(7,91,45,0.16); }
    .multi-select.open .multi-select-menu { display:block; }
    .multi-select-option { display:flex; align-items:center; gap:8px; width:100%; padding:8px 9px; border-radius:7px; color:#294235; font-size:12px; cursor:pointer; }
    .multi-select-option:hover { background:#f0f8f1; }
    .multi-select-option input { accent-color:var(--c3); }
    .multi-select-option.selected { background:rgba(0,166,81,0.09); font-weight:600; }
    .multi-select .tag { padding:3px 8px; }

    /* TAGS */
    .tag { display:inline-flex; align-items:center; gap:5px; font-size:10.5px; font-weight:700; padding:3px 10px; border-radius:20px; }
    .t-tea  { background:rgba(255,176,0,0.12); color:#a66d00; border:1px solid rgba(255,176,0,0.30); }
    .t-tdah { background:rgba(0,166,81,0.10); color:#08743b; border:1px solid rgba(0,166,81,0.24); }
    .t-out  { background:rgba(245,158,11,0.12); color:#ad6500; border:1px solid rgba(245,158,11,0.28); }

    /* TABLE */
    .trow { border-top:1px solid var(--border); transition:background 0.14s; cursor:pointer; }
    .trow:hover { background:rgba(76,175,80,0.05); }

    /* TABS */
    .tab-btn {
      padding:7px 16px; border-radius:8px; font-size:13px; font-weight:500;
      cursor:pointer; transition:all 0.18s; color:#708276;
      border:1px solid transparent; font-family:'Poppins',sans-serif; flex:0 0 auto;
    }
    .tab-btn:hover { color:var(--c1); background:#f0f7ef; }
    .tab-btn.active { background:rgba(0,166,81,0.10); color:var(--c1); border-color:rgba(0,166,81,0.24); }
    .tab-row { overflow-x:auto; white-space:nowrap; scrollbar-width:thin; }
    .tab-row::-webkit-scrollbar { height:6px; }

    /* CALENDAR */
    .cal-cell {
      min-height:82px; border-right:1px solid var(--border); border-bottom:1px solid var(--border);
      padding:7px; transition:background 0.13s; cursor:pointer;
    }
    .cal-cell:hover { background:rgba(76,175,80,0.05); }
    .cal-day {
      width:25px; height:25px; display:flex; align-items:center; justify-content:center;
      border-radius:50%; font-size:11.5px; font-weight:600; color:#1f382b; opacity:1;
    }
    .cal-day.today { background:var(--c2); color:#fff; font-weight:700; }
    .ev-chip {
      font-size:10px; font-weight:500; padding:2px 6px; border-radius:5px;
      margin-top:3px; display:block; cursor:pointer;
      border-left:2px solid var(--gold); background:rgba(255,215,0,0.09); color:#1d2f26;
      white-space:nowrap; overflow:hidden; text-overflow:ellipsis; transition:background 0.13s;
    }
    .ev-chip:hover { background:rgba(255,215,0,0.17); }

    /* MODAL */
    .modal-bg {
      position:fixed; inset:0; background:rgba(3,31,16,0.48); backdrop-filter:blur(6px);
      z-index:50; display:none; align-items:center; justify-content:center;
    }
    .modal-bg.open { display:flex; animation:mfade 0.2s ease; }
    @keyframes mfade { from{opacity:0} to{opacity:1} }
    .modal-box {
      background:var(--surface2); border:1px solid rgba(76,175,80,0.22);
      border-radius:20px; padding:26px; width:100%; max-width:450px; margin:16px;
      animation:mslide 0.22s cubic-bezier(.4,0,.2,1);
    }
    @keyframes mslide { from{transform:scale(0.95);opacity:0} to{transform:scale(1);opacity:1} }

    /* TOAST */
    .toast {
      position:fixed; bottom:26px; right:26px; z-index:100;
      background:var(--surface2); border:1px solid rgba(76,175,80,0.3);
      border-left:3px solid var(--c3);
      color:var(--text); padding:13px 18px; border-radius:12px;
      font-size:13px; font-weight:500;
      display:flex; align-items:center; gap:10px;
      transform:translateX(120%); opacity:0;
      transition:all 0.3s cubic-bezier(.4,0,.2,1);
      box-shadow:0 8px 32px rgba(0,0,0,0.45);
      max-width:320px;
    }
    .toast.show { transform:translateX(0); opacity:1; }

    /* DOC ITEM */
    .doc-item {
      display:flex; align-items:center; justify-content:space-between;
      background:rgba(255,255,255,0.03); border:1px solid var(--border);
      border-radius:10px; padding:11px 15px; transition:all 0.16s;
    }
    .doc-item:hover { background:rgba(76,175,80,0.05); border-color:rgba(76,175,80,0.22); }

    /* AVATAR */
    .av {
      border-radius:50%; display:flex; align-items:center; justify-content:center;
      font-weight:700; background:linear-gradient(135deg,var(--c1),var(--c3)); color:#fff; flex-shrink:0;
    }

    /* EMPTY */
    .empty {
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      padding:52px 20px; color:var(--text-muted); text-align:center; gap:4px;
    }
    .evolucao-relatorio-grid { display:grid; grid-template-columns:260px minmax(0,1fr); gap:18px; align-items:start; }
    .evolucao-filtros { display:grid; gap:12px; }
    .evolucao-grafico-layout { display:grid; grid-template-columns:minmax(0,1fr) 250px; gap:22px; align-items:stretch; }
    .evolucao-grafico-box { width:100%; max-width:900px; padding:14px 16px 8px; border:1px solid rgba(7,91,45,.16); border-radius:8px; background:#fff; }
    .evolucao-grafico-titulo { color:#59675e; font-size:19px; font-weight:500; text-align:center; margin-bottom:4px; }
    .evolucao-grafico-box svg { width:100%; height:auto; aspect-ratio:760 / 320; display:block; }
    .evolucao-grafico-eixo { color:#718077; font-size:10px; text-align:center; margin-top:2px; }
    .evolucao-grafico-legenda {
      padding:12px 14px 10px;
      border:1px solid rgba(7,91,45,0.08);
      border-radius:12px;
      background:rgba(132, 176, 145, 0.08);
      display:flex;
      flex-direction:column;
      min-height:290px;
      justify-content:flex-start;
    }
    #evolucao-legenda {
      display:flex;
      flex-direction:column;
      gap:8px;
      margin-bottom:10px;
      flex:1;
    }
    #evolucao-tendencia {
      margin-top:auto;
      padding:12px 14px;
      border-radius:10px;
      background:rgba(72, 151, 98, 0.10);
      border:1px solid rgba(72, 151, 98, 0.16);
      font-size:11px;
      line-height:1.5;
      color:var(--text);
    }
    .perfil-aluno-relatorio { height:calc(100vh - 156px); max-height:calc(100vh - 156px); overflow-y:auto; scrollbar-gutter:stable; }
    .perfil-aluno-relatorio > .tab-row { position:sticky; top:0; z-index:12; background:#f7faf6 !important; box-shadow:0 2px 8px rgba(7,91,45,.08); }

    @media (max-width: 768px) {
      html, body { height:auto; overflow:auto; }
      body { display:block !important; }
      .mobile-menu-btn {
        display:inline-flex; align-items:center; justify-content:center;
        width:40px; height:40px; border-radius:10px; background:rgba(255,255,255,0.05);
        border:1px solid rgba(7,91,45,0.14); color:var(--c1); cursor:pointer;
      }
      .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 280px;
        min-width: 280px;
        height: 100vh;
        z-index: 9999;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        border-right: 1px solid rgba(76,175,80,0.18);
        border-bottom: none;
      }
      .sidebar.open {
        transform: translateX(0) !important;
      }
      .sidebar.open ~ .main .mobile-menu-btn {
        display: none !important;
      }
      .sidebar-close-btn {
        display:flex; align-items:center; justify-content:center;
        width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.05);
        border:1px solid rgba(255,255,255,0.1); color:#fff; cursor:pointer;
        margin-left:auto;
      }
      .main { min-height:100vh; }
      .content { padding:16px; overflow:visible; }
      #page-dashboard > div[style*="grid-template-columns:repeat(3,1fr)"] {
        grid-template-columns:1fr !important;
      }
      #page-dashboard > div[style*="grid-template-columns:3fr 2fr"] {
        grid-template-columns:1fr !important;
      }
      #page-relatorios .card > div[style*="grid-template-columns:repeat(4"],
      #page-relatorios .card > div[style*="grid-template-columns:repeat(5"] {
        grid-template-columns:1fr !important;
      }
      .dashboard-secondary-grid { grid-template-columns:1fr !important; }
      #page-aluno > div[style*="display:grid;grid-template-columns:270px 1fr;gap:18px"] {
        grid-template-columns:1fr !important;
      }
      [style*="grid-template-columns:repeat(2,minmax(0,1fr));gap:12px"],
      [style*="grid-template-columns:1fr 1fr;gap:10px"],
      [style*="grid-template-columns:1fr 1fr;gap:14px"] {
        grid-template-columns:1fr !important;
      }
      [style*="display:flex;gap:8px"],
      [style*="display:flex;gap:9px"],
      [style*="display:flex;gap:10px"] {
        flex-direction:column !important;
      }
      .btn-p, .btn-g {
        width:100%; justify-content:center;
      }
      .tab-row { overflow-x:auto; white-space:nowrap; }
      .table-scroll { overflow-x:auto; }
      .table-scroll table { min-width:620px; display:block; }
      .evolucao-relatorio-grid { grid-template-columns:1fr; }
      .evolucao-filtros { grid-template-columns:repeat(2,minmax(0,1fr)); }
      .evolucao-grafico-layout { grid-template-columns:1fr; gap:14px; }
      .evolucao-grafico-box { max-width:none; }
      .evolucao-grafico-titulo { font-size:16px; }
      .perfil-aluno-relatorio { height:auto; max-height:none; overflow:visible; }
      .perfil-aluno-relatorio > .tab-row { top:0; }
      .modal-box { margin:12px; padding:20px; }
      .avaliacao-periodos { grid-template-columns:1fr; }
      .avaliacao-dia-lista { padding:8px; }
      .avaliacao-dia-lista > .avaliacao-dia-card + .avaliacao-dia-card { border-left:4px solid var(--gold); border-top:1px solid var(--border); }
      .avaliacao-historico-periodos .avaliacao-dia-card + .avaliacao-dia-card { border-left:4px solid var(--gold); border-top:1px solid var(--border); }
      .avaliacao-layout { grid-template-columns:1fr; gap:12px; }
      .avaliacao-aluno-card { position:static; padding:18px; }
      .avaliacao-aluno-info { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0 16px; text-align:left; }
      .avaliacao-cabecalho { padding:16px; }
      .avaliacao-modal-conteudo { max-height:none; overflow:visible; }
      .toast { left:16px; right:16px; bottom:16px; max-width:none; }
    }
    .empty i { font-size:42px; margin-bottom:10px; color:rgba(0,143,69,0.38); }

    /* SEARCH */
    .srch { position:relative; }
    .srch i { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.33); font-size:14px; }
    .srch input { padding-left:40px; }

    /* NOTIF DROPDOWN */
    #notif-dd {
      position:absolute; right:0; bottom:calc(100% + 8px); width:min(260px, calc(100% - 32px)); max-height:min(60vh, 420px); overflow-y:auto;
      background:#073b26; border:1px solid rgba(255,255,255,0.16); color:#f5fff8;
      border-radius:14px; padding:14px; z-index:30;
      box-shadow:0 16px 48px rgba(0,0,0,0.55);
      display:none;
    }

    /* PROGRESS */
    .pb { background:rgba(255,255,255,0.07); border-radius:9999px; overflow:hidden; }
    .pf { height:100%; border-radius:9999px; transition:width 0.7s ease; }

    /* Harmonize legacy inline styles with the light visual language. */
    .main [style*="color:#fff"]:not(i):not(.bi):not(.fa) { color:var(--text) !important; }
    .main [style*="color: #fff"]:not(i):not(.bi):not(.fa) { color:var(--text) !important; }
    .main [style*="color:rgba(255,255,255"] { color:#64766a !important; }
    .main [style*="background:rgba(255,255,255"] { background:#f7faf6 !important; }
    .main [style*="border:1px solid rgba(255,255,255"] { border-color:rgba(7,91,45,0.14) !important; }
    .main [style*="background:rgba(76,175,80"] { background:rgba(0,166,81,0.10) !important; }
    .main [style*="color:var(--gold)"] { color:#a66d00 !important; }
    .main [style*="color:var(--c3)"] { color:var(--c2) !important; }
    .sidebar [style*="color:rgba(255,255,255"] { color:rgba(255,255,255,0.70) !important; }
    .sidebar [style*="color:#fff"] { color:#fff !important; }
    .sidebar .nav-item.active { color:#ffb000 !important; }
    .sidebar .nav-item.active .ni { color:#ffb000 !important; }
    .profile-banner .profile-copy { color:#fff !important; }

    /* Mobile layout: keep every workflow usable without horizontal page overflow. */
    @media (max-width: 768px) {
      html, body { width:100%; min-width:0; height:auto !important; min-height:100%; overflow-x:hidden !important; }
      body { display:block !important; }
      .main { width:100%; min-width:0; min-height:100vh; overflow:visible; }
      .content { width:100%; min-width:0; padding:64px 12px 28px !important; overflow:visible; }
      .mobile-menu-btn { position:fixed; top:12px; left:12px; z-index:100; box-shadow:0 2px 12px rgba(7,91,45,.10); }
      .sidebar-brand { padding:22px 22px 15px; }
      .page { width:100%; min-width:0; }
      .card, .stat-card, .dashboard-panel { max-width:100%; min-width:0; }
      .stat-card { padding:17px 18px; }
      .stat-card [style*="font-size:36px"] { font-size:30px !important; }
      .profile-banner { padding:24px 18px !important; align-items:flex-start !important; }
      .profile-banner > div:first-child { width:100%; }
      .profile-banner > div:last-child { width:100%; }
      .profile-banner .btn-g { flex:1 1 140px; min-width:0; }
      .profile-copy { min-width:0; overflow-wrap:anywhere; }
      .profile-copy > div:first-child { font-size:18px !important; }

      #page-dashboard > div[style*="grid-template-columns:repeat(3,1fr)"],
      #page-dashboard > div[style*="grid-template-columns:3fr 2fr"],
      #page-relatorios .card > div[style*="grid-template-columns:repeat(4"],
      #page-relatorios .card > div[style*="grid-template-columns:repeat(5"],
      #page-relatorios .card > div[style*="grid-template-columns:minmax(0,1fr) 210px"],
      #page-usuarios [style*="grid-template-columns:1fr 1fr"],
      #page-quesitos [style*="grid-template-columns:1fr 1fr"],
      #modal-cadastrar [style*="grid-template-columns:1fr 1fr"],
      #page-aluno [style*="grid-template-columns:270px 1fr"],
      #ev-modal [style*="grid-template-columns:1fr 1fr"] { grid-template-columns:1fr !important; }
      #page-relatorios .card, #page-usuarios .card, #page-quesitos .card, #page-perfis .card { padding-left:14px !important; padding-right:14px !important; }
      #page-perfis > div:first-child { display:grid !important; grid-template-columns:1fr !important; gap:8px !important; width:100%; }
      #page-perfis > div:first-child .srch,
      #page-perfis > div:first-child .inp { width:100% !important; min-width:0 !important; }
      #page-perfis > .card > div:first-child { flex-wrap:wrap; gap:10px; padding:13px 14px !important; }
      #page-perfis > .card > div:first-child > * { margin-right:0 !important; }
      #page-perfis > .card > div:first-child .btn-g { width:auto; }
      #page-aluno > div[style*="display:flex;gap:10px"] .btn-g { flex:1 1 150px; }
      #page-aluno .card { overflow:visible !important; }
      #page-aluno [style*="justify-content:space-between;padding:8px 0"] { gap:12px; align-items:flex-start; }
      #page-aluno [style*="justify-content:space-between;padding:8px 0"] > span:last-child { text-align:right; overflow-wrap:anywhere; }
      .dashboard-secondary-grid { gap:12px; }
      .table-scroll { width:100%; max-width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
      .table-scroll table { display:table; min-width:620px; }
      #page-perfis .table-scroll { overflow:visible; }
      #page-perfis .table-scroll table { display:block; width:100%; min-width:0; }
      #page-perfis .table-scroll thead { display:none; }
      #page-perfis .table-scroll tbody,
      #page-perfis .table-scroll tr { display:block; width:100%; }
      #page-perfis .table-scroll tr { position:relative; padding:12px 42px 12px 0; border-bottom:1px solid var(--border); }
      #page-perfis .table-scroll td { display:flex; align-items:center; justify-content:space-between; gap:12px; width:100%; padding:7px 0 !important; font-size:12px !important; color:var(--text) !important; }
      #page-perfis .table-scroll td::before { flex:0 0 82px; color:var(--text-muted); font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
      #page-perfis .table-scroll td:nth-child(1)::before { content:'Aluno'; }
      #page-perfis .table-scroll td:nth-child(2)::before { content:'Turma'; }
      #page-perfis .table-scroll td:nth-child(3)::before { content:'Curso'; }
      #page-perfis .table-scroll td:nth-child(4)::before { content:'Condição'; }
      #page-perfis .table-scroll td:nth-child(5)::before { content:'Matrícula'; }
      #page-perfis .table-scroll td:nth-child(6) { position:absolute; top:50%; right:0; width:auto; transform:translateY(-50%); }
      #page-perfis .table-scroll td:nth-child(6)::before { display:none; }
      #page-perfis .table-scroll td:nth-child(1) > div { min-width:0; }
      #page-perfis .table-scroll td:nth-child(1) span { overflow-wrap:anywhere; text-align:right; }
      #page-quesitos .table-scroll { overflow:visible; }
      #page-quesitos .table-scroll table { display:block; width:100%; min-width:0; }
      #page-quesitos .table-scroll thead { display:none; }
      #page-quesitos .table-scroll tbody,
      #page-quesitos .table-scroll tr { display:block; width:100%; }
      #page-quesitos .table-scroll tr { padding:12px 0; border-bottom:1px solid var(--border); }
      #page-quesitos .table-scroll td { display:block; width:100%; padding:6px 0 !important; }
      #page-quesitos .table-scroll td:nth-child(3) { display:flex; gap:6px; justify-content:flex-start; }
      #page-quesitos .table-scroll td:nth-child(3) .btn-g { flex:1; min-width:0; justify-content:center; }
      #page-usuarios .table-scroll table { min-width:720px; }
      .tab-row { max-width:100%; padding-bottom:7px !important; }
      .modal-bg { padding:10px; overflow-y:auto; align-items:center !important; justify-content:center; z-index:300 !important; }
      .modal-box { width:100%; max-width:none !important; max-height:calc(100dvh - 20px) !important; margin:0; padding:18px !important; border-radius:14px; overflow-y:auto; }
      .modal-box button[onclick*="fecharEditorUsuario"],
      .modal-box button[onclick*="fecharQuesitoEditor"],
      .modal-box button[onclick*="fecharModalOpcaoAluno"],
      .modal-box button[aria-label="Fechar cadastro"] {
        width:42px !important;
        min-width:42px !important;
        max-width:42px !important;
        height:42px !important;
        min-height:42px !important;
        padding:0 !important;
        flex:0 0 42px !important;
        justify-content:center !important;
      }
      #modal-cadastrar > div { width:100%; max-width:none !important; }
      #modal-cadastrar .modal-box {
        height:auto !important;
        max-height:82dvh !important;
        overflow-y:auto !important;
        overscroll-behavior:contain;
        scrollbar-width:thin;
      }
      #modal-cadastrar .multi-select { flex:1 1 auto; min-width:0; }
      #modal-cadastrar .multi-select-toggle { width:100%; min-width:0; }
      #modal-cadastrar .btn-g[title="Adicionar curso"],
      #modal-cadastrar .btn-g[title="Adicionar condição geral"] {
        width:42px !important;
        min-width:42px !important;
        max-width:42px !important;
        height:42px !important;
        min-height:42px !important;
        padding:0 !important;
        flex:0 0 42px !important;
        justify-content:center !important;
      }
      .btn-p, .btn-g { min-height:42px; }
      .btn-p { white-space:normal; }
      .toast { left:12px; right:12px; bottom:12px; }
      .cal-cell { min-height:64px; padding:4px; }
      .cal-cell > * { max-width:100%; }
      .ev-chip { font-size:9px; }
    }

    @media (max-width: 420px) {
      .content { padding:64px 8px 28px !important; }
      .sidebar { width: min(280px, 88vw); min-width: min(280px, 88vw); }
      .profile-banner { padding:18px 14px !important; }
      .profile-banner > div:first-child { gap:12px; }
      .perfil-avatar-ring { width:68px !important; height:68px !important; font-size:25px !important; }
      .tc-apoio, #tc-pei, #tc-paee, #tc-lau { padding:14px !important; }
      .modal-box { padding:15px !important; }
      .tab-btn { padding-left:12px; padding-right:12px; font-size:12px; }
    }

    @media (max-width: 768px) {
      /* Usuários: filtros em coluna e registros como cartões legíveis. */
      #page-usuarios > .card > div:first-child {
        display:grid !important;
        grid-template-columns:1fr !important;
        gap:10px !important;
        padding:14px !important;
      }
      #page-usuarios > .card > div:first-child > div:first-child { margin-right:0 !important; }
      #usuarios-busca, #usuarios-status { width:100% !important; min-width:0 !important; }

      #page-usuarios .table-scroll { overflow:visible; }
      #page-usuarios .table-scroll table { display:block; width:100%; min-width:0 !important; }
      #page-usuarios .table-scroll thead { display:none; }
      #page-usuarios .table-scroll tbody,
      #page-usuarios .table-scroll tr { display:block; width:100%; }
      #page-usuarios .table-scroll tr { padding:10px 0; border-bottom:1px solid var(--border); }
      #page-usuarios .table-scroll td { display:flex; align-items:center; justify-content:space-between; gap:12px; width:100%; padding:6px 0 !important; font-size:12px !important; color:var(--text) !important; }
      #page-usuarios .table-scroll td::before { flex:0 0 76px; color:var(--text-muted); font-size:9px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
      #page-usuarios .table-scroll td:nth-child(1)::before { content:'Usuário'; }
      #page-usuarios .table-scroll td:nth-child(2)::before { content:'CPF'; }
      #page-usuarios .table-scroll td:nth-child(3)::before { content:'Telefone'; }
      #page-usuarios .table-scroll td:nth-child(4)::before { content:'E-mail'; }
      #page-usuarios .table-scroll td:nth-child(5)::before { content:'Perfil'; }
      #page-usuarios .table-scroll td:nth-child(6)::before { content:'Status'; }
      #page-usuarios .table-scroll td:nth-child(7)::before,
      #page-usuarios .table-scroll td:nth-child(6):last-child::before { content:'Ações'; }
      #page-usuarios .table-scroll td:last-child { display:flex; justify-content:flex-end; gap:5px; flex-wrap:nowrap; }
      #page-usuarios .table-scroll td:last-child::before { margin-right:auto; }
      #page-usuarios .table-scroll td > div { min-width:0; }
      #page-usuarios .table-scroll td > div span,
      #page-usuarios .table-scroll td { overflow-wrap:anywhere; }

      /* Solicitações vazias não reservam uma faixa de cabeçalho desktop. */
      #page-usuarios > .card:first-child .table-scroll:has(tbody:empty) { display:none; }

    }
  </style>
</head>
<body style="display:flex;height:100vh">
<?php
$nomeUsuario = $_SESSION['nome'] ?? 'Usuário';
$tipoUsuario = strtolower(trim($_SESSION['tipo'] ?? 'responsavel'));
$rotulosUsuario = [
  'master' => 'Master do Sistema',
  'aee' => 'AEE',
  'apoio' => 'Apoio',
  'responsavel' => 'Responsável',
];
$rotuloUsuario = $rotulosUsuario[$tipoUsuario] ?? 'Usuário';
$inicialUsuario = strtoupper(substr($nomeUsuario, 0, 1));
$fotoUsuario = $_SESSION['foto'] ?? null;
?>
<script>
  window.usuarioTipo = <?php echo json_encode($tipoUsuario ?? 'responsavel'); ?>;
</script>
<div class="overlay" onclick="fecharMenu()"></div>

<!-- ======================== SIDEBAR ======================== -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-brand-mark"><img src="../../../../../entradasaida/app/main/assets/img/logo.png" alt="Símbolo do Sistema PCD"></div>
    <div>
      <div class="sidebar-brand-title">Sistema <span>PCD</span></div>
      <div class="sidebar-brand-subtitle">Gestão de alunos e<br>acompanhamento escolar</div>
    </div>
  </div>

  <nav>
    <div class="sidebar-section-group">
      <button type="button" class="sidebar-section" aria-expanded="true" aria-controls="sidebar-gerenciamento" onclick="alternarGrupoMenu(this)"><span>Gerenciamento</span><i class="bi bi-chevron-up" aria-hidden="true"></i></button>
      <div class="sidebar-section-items" id="sidebar-gerenciamento">
        <div class="nav-item active" id="nav-dashboard" onclick="go('dashboard')">
          <i class="bi bi-house-fill ni"></i><span>Início</span>
        </div>
        <div class="nav-item" id="nav-perfis" onclick="go('perfis')">
          <i class="bi bi-people-fill ni"></i><span><?= $tipoUsuario === 'apoio' ? 'Perfil do Aluno' : 'Perfis' ?></span>
        </div>
      </div>
    </div>

    <div class="sidebar-section-group">
      <button type="button" class="sidebar-section" aria-expanded="true" aria-controls="sidebar-acompanhamento" onclick="alternarGrupoMenu(this)"><span>Acompanhamento</span><i class="bi bi-chevron-up" aria-hidden="true"></i></button>
      <div class="sidebar-section-items" id="sidebar-acompanhamento">
        <div class="nav-item" id="nav-pei" onclick="go('pei')">
          <i class="bi bi-journal-text ni"></i><span>PEI</span>
        </div>
        <div class="nav-item" id="nav-paee" onclick="go('paee')">
          <i class="bi bi-person-workspace ni"></i><span>PAEE</span>
        </div>
        <?php if ($tipoUsuario === 'apoio'): ?>
        <div class="nav-item" id="nav-avaliacao" onclick="go('avaliacao')">
          <i class="bi bi-clipboard2-check-fill ni"></i><span>Avaliação</span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="sidebar-section-group">
      <button type="button" class="sidebar-section" aria-expanded="true" aria-controls="sidebar-relatorios" onclick="alternarGrupoMenu(this)"><span>Relatórios</span><i class="bi bi-chevron-up" aria-hidden="true"></i></button>
      <div class="sidebar-section-items" id="sidebar-relatorios">
        <div class="nav-item" id="nav-relatorios" onclick="go('relatorios')">
          <i class="bi bi-graph-up-arrow ni"></i><span>Relatórios</span>
        </div>
        <!-- Agenda temporariamente indisponível no menu. -->
        <!-- <div class="nav-item" id="nav-agenda" onclick="go('agenda')">
          <i class="bi bi-calendar3 ni"></i><span>Agenda</span>
        </div> -->
      </div>
    </div>
    <?php if (in_array($tipoUsuario, ['master', 'aee'], true)): ?>
    <div class="sidebar-section-group">
      <button type="button" class="sidebar-section" aria-expanded="true" aria-controls="sidebar-administracao" onclick="alternarGrupoMenu(this)"><span>Administração</span><i class="bi bi-chevron-up" aria-hidden="true"></i></button>
      <div class="sidebar-section-items" id="sidebar-administracao">
        <div class="nav-item" id="nav-usuarios" onclick="go('usuarios')">
          <i class="bi bi-person-gear ni"></i><span>Gerenciar Usuários</span>
        </div>
        <div class="nav-item" id="nav-quesitos" onclick="go('quesitos')">
          <i class="bi bi-ui-checks-grid ni"></i><span>Quesitos avaliação</span>
        </div>
        <div class="nav-item" id="nav-condicoes" onclick="go('condicoes')">
          <i class="bi bi-clipboard2-pulse ni"></i><span>Condições gerais</span>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </nav>
  <div class="sidebar-footer">
      <div class="sidebar-account-tools">
        <button type="button" class="sidebar-account-user" onclick="go('perfil')" title="Abrir perfil">
          <span class="av sidebar-account-avatar" id="topbar-avatar">
            <?php if ($fotoUsuario): ?><img src="<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
            <?php else: ?><?= htmlspecialchars($inicialUsuario, ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
          </span>
          <span>
            <span style="display:block;font-size:12px;font-weight:600;line-height:1.2"><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></span>
            <span style="display:block;margin-top:3px;color:rgba(255,255,255,0.68);font-size:10px"><?= htmlspecialchars($rotuloUsuario, ENT_QUOTES, 'UTF-8') ?></span>
          </span>
        </button>
        <button id="bell-btn" class="sidebar-notification-button" type="button" onclick="toggleDD()" aria-label="Notificações" aria-controls="notif-dd" aria-expanded="false">
          <i class="bi bi-bell-fill" aria-hidden="true"></i>
          <span id="notif-badge" style="position:absolute;top:-5px;right:-5px;min-width:16px;height:16px;padding:0 4px;border-radius:50%;background:var(--amber);color:#000;font-size:9px;font-weight:800;display:none;align-items:center;justify-content:center">0</span>
        </button>
        <div id="notif-dd">
          <div style="font-size:12.5px;font-weight:700;margin-bottom:10px;color:rgba(255,255,255,0.76)"><i class="bi bi-bell me-1"></i> Notificações</div>
          <div id="notif-list">
            <div class="empty" style="padding:20px 0">
              <i class="bi bi-bell-slash" style="font-size:28px;margin-bottom:6px"></i>
              <span style="font-size:12px">Nenhuma notificação</span>
            </div>
          </div>
        </div>
      </div>
      <div class="nav-item sidebar-logout"
        onclick="window.location.href='controllers/usuario_controller.php?action=logout'">
        <i class="bi bi-box-arrow-right ni"></i><span>Sair</span>
    </div>
  </div>
</aside>
<button type="button" class="sidebar-collapse-toggle" aria-label="Ocultar menu lateral" aria-expanded="true" title="Ocultar menu lateral">
  <i class="bi bi-chevron-left" aria-hidden="true"></i>
</button>

<!-- ======================== MAIN ======================== -->
<div class="main">
  <button class="mobile-menu-btn" type="button" onclick="abrirMenu()" aria-label="Abrir menu">
    <i class="bi bi-list" style="font-size:20px" aria-hidden="true"></i>
  </button>

  <!-- CONTENT -->
  <div class="content">

    <!-- ============= MODULOS DE PLANOS ============= -->
    <div class="page" id="page-paee">
      <div class="card" style="padding:20px">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap">
          <div><div id="plano-paee-titulo" style="font-size:17px;font-weight:700"><i class="bi bi-person-workspace" style="color:var(--gold);margin-right:7px"></i>PAEE</div><div id="plano-paee-contexto" style="font-size:11px;color:var(--text-muted);margin-top:5px">Selecione um aluno para consultar os PEIs.</div></div>
          <button type="button" class="btn-g" onclick="planoVoltar('paee')" id="plano-paee-voltar" style="display:none"><i class="bi bi-arrow-left"></i> Voltar</button>
        </div>
        <div id="plano-paee-conteudo" style="margin-top:20px"></div>
      </div>
    </div>

    <div class="page" id="page-pei">
      <div class="card" style="padding:20px">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap">
          <div><div id="plano-pei-titulo" style="font-size:17px;font-weight:700"><i class="bi bi-journal-text" style="color:var(--gold);margin-right:7px"></i>PEI</div><div id="plano-pei-contexto" style="font-size:11px;color:var(--text-muted);margin-top:5px">Selecione um aluno para consultar as disciplinas.</div></div>
          <button type="button" class="btn-g" onclick="planoVoltar('pei')" id="plano-pei-voltar" style="display:none"><i class="bi bi-arrow-left"></i> Voltar</button>
        </div>
        <div id="plano-pei-conteudo" style="margin-top:20px"></div>
      </div>
    </div>

    <!-- ============= PERFIL ============= -->
    <div class="page" id="page-perfil">
      <div class="card" style="max-width:1120px;margin:0 auto;overflow:hidden;background:#f7fbf8">
        <div class="profile-banner" style="background:linear-gradient(135deg,var(--c1),var(--c2));padding:34px 38px;display:flex;align-items:center;justify-content:space-between;gap:22px;flex-wrap:wrap;color:#fff">
          <div style="display:flex;align-items:center;gap:18px">
            <div class="perfil-avatar-ring" id="perfil-avatar" style="width:88px;height:88px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.16);border:4px solid rgba(255,255,255,.35);font-size:32px;font-weight:700;background-size:cover;background-position:center">
              <?php if ($fotoUsuario): ?><img src="<?= htmlspecialchars($fotoUsuario, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
              <?php else: ?><?= htmlspecialchars($inicialUsuario, ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
            </div>
            <div class="profile-copy"><div style="font-size:22px;font-weight:700" id="perfil-nome"><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></div><div style="font-size:13px;opacity:.82"><?= htmlspecialchars($rotuloUsuario, ENT_QUOTES, 'UTF-8') ?></div><div style="font-size:11px;opacity:.65;margin-top:5px">Perfil ativo</div></div>
          </div>
          <div style="display:flex;gap:9px;flex-wrap:wrap">
            <button class="btn-g" style="background:#ecfdf5;color:#14532d;border:none" onclick="abrirSeletorFoto()"><i class="bi bi-camera"></i> Alterar foto</button>
            <button class="btn-g" style="background:rgba(255,220,220,.95);color:#a61b1b;border:none" id="btn-remover-foto" onclick="abrirModalRemoverFoto()" <?= $fotoUsuario ? '' : 'disabled' ?>><i class="bi bi-trash3"></i> Remover foto</button>
            <input type="file" id="input-foto-perfil" accept="image/jpeg,image/png,image/webp" hidden>
          </div>
        </div>
        <div style="padding:34px 38px;background:#f7fbf8">
          <div style="font-size:14px;font-weight:700;margin-bottom:16px;color:var(--c1)">Informações da conta</div>
          <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px">
            <div><span class="lbl">Nome completo</span><div class="card" style="padding:11px 14px;font-size:13px" id="perfil-info-nome"><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></div></div>
            <div><span class="lbl">Perfil de acesso</span><div class="card" style="padding:11px 14px;font-size:13px"><?= htmlspecialchars($rotuloUsuario, ENT_QUOTES, 'UTF-8') ?></div></div>
            <div><span class="lbl">E-mail</span><div class="card" style="padding:11px 14px;font-size:13px"><?= htmlspecialchars($_SESSION['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div></div>
          </div>
        </div>
      </div>
      <div class="modal-bg" id="modal-remover-foto" onclick="if(event.target===this)fecharModalRemoverFoto()">
        <div class="modal-box" style="max-width:380px;text-align:center"><div style="width:54px;height:54px;border-radius:50%;background:rgba(198,40,40,.1);border:1px solid rgba(198,40,40,.35);display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i class="bi bi-trash3-fill" style="font-size:20px;color:#c62828"></i></div><div style="font-size:16px;font-weight:700;margin-bottom:7px">Remover foto de perfil?</div><div style="font-size:12.5px;color:var(--text-muted);margin-bottom:20px">A foto será removida e a inicial do seu nome será exibida novamente.</div><div style="display:flex;gap:9px"><button onclick="fecharModalRemoverFoto()" class="btn-g" style="flex:1;justify-content:center">Cancelar</button><button onclick="removerFotoPerfil()" style="flex:1;padding:9px;border-radius:10px;background:#c62828;border:none;color:#fff;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif"><i class="bi bi-trash3"></i> Remover</button></div></div>
      </div>
    </div>

    <!-- ============= DASHBOARD ============= -->
    <div class="page active" id="page-dashboard">
      <!-- Stats -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:22px">
        <div class="stat-card stat-card--gold">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <span style="font-size:10.5px;font-weight:700;letter-spacing:1px;color:rgba(255,255,255,0.38)">PCDs CADASTRADOS</span>
            <div style="width:34px;height:34px;border-radius:9px;background:rgba(255,215,0,0.1);border:1px solid rgba(255,215,0,0.2);display:flex;align-items:center;justify-content:center">
              <i class="bi bi-person-wheelchair" style="font-size:15px;color:var(--gold)"></i>
            </div>
          </div>
          <div id="s-pcds" style="font-size:36px;font-weight:800;color:var(--gold);line-height:1">0</div>
          
        </div>
        <div class="stat-card stat-card--green">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <span style="font-size:10.5px;font-weight:700;letter-spacing:1px;color:rgba(255,255,255,0.38)">ACOMPANHANTES</span>
            <div style="width:34px;height:34px;border-radius:9px;background:rgba(76,175,80,0.1);border:1px solid rgba(76,175,80,0.2);display:flex;align-items:center;justify-content:center">
              <i class="bi bi-person-heart" style="font-size:15px;color:var(--c3)"></i>
            </div>
          </div>
          <div id="s-acomp" style="font-size:36px;font-weight:800;color:var(--c3);line-height:1">0</div>
          
        </div>
        <div class="stat-card stat-card--orange">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <span style="font-size:10.5px;font-weight:700;letter-spacing:1px;color:rgba(255,255,255,0.38)">TURMAS ATENDIDAS</span>
            <div style="width:34px;height:34px;border-radius:9px;background:rgba(255,140,0,0.1);border:1px solid rgba(255,140,0,0.2);display:flex;align-items:center;justify-content:center">
              <i class="bi bi-mortarboard-fill" style="font-size:15px;color:var(--amber)"></i>
            </div>
          </div>
          <div id="s-turmas" style="font-size:36px;font-weight:800;color:var(--peach);line-height:1">0</div>
          
        </div>
      </div>

      <!-- Row 2 -->
      <div class="dashboard-secondary-grid">
        <!-- Evolução por quesito -->
        <div class="card dashboard-panel" style="padding:22px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
            <div>
              <div style="font-size:14.5px;font-weight:700">Evolução por quesito</div>
              <div style="font-size:11px;color:var(--text-muted)">Pontuação acumulada com o peso de cada avaliação</div>
            </div>
          </div>
          <div id="dash-evolucao">
            <div class="empty" style="padding:30px 0">
              <i class="bi bi-bar-chart-line" style="font-size:38px;margin-bottom:10px"></i>
              <span style="font-size:13px;font-weight:500">Carregando evolução</span>
            </div>
          </div>
        </div>

      <!-- Alunos vinculados -->
      <div class="card dashboard-panel dashboard-panel--green" style="padding:22px">
        <div style="margin-bottom:16px">
          <div>
            <div id="dash-students-title" style="font-size:14.5px;font-weight:700">Alunos Vinculados</div>
            <div id="dash-students-subtitle" style="font-size:11px;color:var(--text-muted)">Aluno(s) vinculado(s) ao acompanhamento</div>
          </div>
        </div>
        <div id="dash-students">
          <div class="empty">
            <i class="bi bi-person-x"></i>
            <span style="font-size:13.5px;font-weight:600">Nenhum aluno vinculado</span>
          </div>
        </div>
      </div>
      </div>
    </div>

    <!-- ============= AVALIAÇÃO ============= -->
    <?php if ($tipoUsuario === 'apoio'): ?>
    <div class="page" id="page-avaliacao">
      <div class="avaliacao-layout">
        <aside class="card avaliacao-aluno-card">
          <div class="av avaliacao-aluno-avatar" id="avaliacao-aluno-avatar">?</div>
          <div id="avaliacao-aluno-nome" style="font-size:17px;font-weight:700;margin-bottom:5px">Carregando...</div>
          <div id="avaliacao-aluno-condicao" class="student-profile-block__conditions">Aluno</div>
          <hr style="border:none;border-top:1px solid var(--border);margin:14px 0">
          <div class="avaliacao-aluno-info">
            <div><span>Matrícula</span><strong id="avaliacao-aluno-matricula">—</strong></div>
            <div><span>Turma</span><strong id="avaliacao-aluno-turma">—</strong></div>
            <div><span>Curso</span><strong id="avaliacao-aluno-curso">—</strong></div>
            <div><span>Sexo</span><strong id="avaliacao-aluno-sexo">—</strong></div>
            <div><span>Nascimento</span><strong id="avaliacao-aluno-nascimento">—</strong></div>
            <div><span>Apoio</span><strong id="avaliacao-aluno-apoio">—</strong></div>
          </div>
        </aside>
        <main class="avaliacao-conteudo">
        <div class="card avaliacao-dia-secao">
          <div class="avaliacao-cabecalho">
            <div class="avaliacao-cabecalho-titulo"><i class="bi bi-clipboard2-check"></i><span>Avaliação do dia</span></div>
            <div class="avaliacao-cabecalho-data avaliacao-cabecalho-subtitle"><i class="bi bi-calendar3"></i><span id="avaliacao-data-atual">Hoje</span></div>
          </div>

          <div id="avaliacao-dia" class="avaliacao-dia-lista avaliacao-periodos">
            <div class="empty" style="grid-column:1/-1;padding:34px 0"><i class="bi bi-hourglass-split"></i><span style="font-size:13px">Carregando avaliações...</span></div>
          </div>
        </div>

          <div class="card" style="padding:20px;margin-top:24px">
            <div class="avaliacao-historico-topo">
              <div class="avaliacao-historico-titulo">
                <i class="bi bi-clock-history" style="color:var(--c3)"></i>
                <div style="font-size:14px;font-weight:700">Histórico de avaliações</div>
              </div>
              <div class="avaliacao-historico-filtro">
                <div class="avaliacao-historico-filtro-campo">
                  <label class="lbl" for="avaliacao-historico-data-filtro">Filtrar por data</label>
                  <div class="avaliacao-data-picker" id="avaliacao-data-picker">
                    <button type="button" id="avaliacao-historico-data-filtro" class="avaliacao-data-picker-trigger" aria-haspopup="dialog" aria-expanded="false"><span>Selecionar data</span><i class="bi bi-calendar3"></i></button>
                    <div class="avaliacao-data-picker-popup" id="avaliacao-data-picker-popup" role="dialog" aria-label="Calendário para filtrar avaliações"></div>
                    <input type="hidden" id="avaliacao-historico-data-valor">
                  </div>
                </div>
                <button type="button" class="btn-g" onclick="limparFiltroHistoricoAvaliacao()" title="Limpar filtro" aria-label="Limpar filtro"><i class="bi bi-x-circle"></i> Limpar filtro</button>
              </div>
            </div>
            <div id="avaliacao-historico">
              <div class="empty" style="padding:30px 0"><i class="bi bi-calendar2-x"></i><span style="font-size:13px">Nenhuma avaliação registrada</span></div>
            </div>
          </div>
        </main>
      </div>
    </div>
    <?php endif; ?>

    <!-- ============= RELATÓRIOS ============= -->
    <div class="page" id="page-relatorios">
      <div class="card" style="padding:20px;margin-bottom:18px;border-left:4px solid var(--gold)">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
          <div>
            <div style="font-size:17px;font-weight:700"><i class="bi bi-graph-up-arrow" style="color:var(--gold);margin-right:7px"></i>Relatórios de evolução</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:5px">Acompanhe a evolução dos quesitos avaliados ao longo dos registros.</div>
          </div>
          <button type="button" class="btn-p" onclick="gerarRelatorioEvolucao()" title="Gerar relatório dos filtros atuais">
            <i class="bi bi-file-earmark-pdf"></i> Gerar relatório
          </button>
        </div>
      </div>
      <div class="evolucao-relatorio-grid">
      <div class="card evolucao-filtros" style="padding:18px;margin-bottom:18px">
          <?php if ($tipoUsuario === 'apoio'): ?>
          <div><label class="lbl">Aluno acompanhado</label><div id="evolucao-aluno-nome-filtro" class="inp" style="background:#f7faf6">Carregando aluno...</div><input id="evolucao-aluno" type="hidden"></div>
          <?php else: ?>
          <div><label class="lbl" for="evolucao-aluno">Aluno</label><select id="evolucao-aluno" class="inp"></select></div>
          <?php endif; ?>
          <div><label class="lbl" for="evolucao-quesito">Quesito</label><select id="evolucao-quesito" class="inp"><option value="__todos">Todos os quesitos</option></select></div>
          <div>
            <span class="lbl">Período</span>
            <div style="display:flex;gap:18px;align-items:center;min-height:42px;padding:0 2px">
              <label style="display:flex;align-items:center;gap:7px;font-size:13px;color:var(--text);cursor:pointer">
                <input type="radio" name="evolucao-periodo" value="manha" class="apoio-periodo-radio">
                <span>Manhã</span>
              </label>
              <label style="display:flex;align-items:center;gap:7px;font-size:13px;color:var(--text);cursor:pointer">
                <input type="radio" name="evolucao-periodo" value="tarde" class="apoio-periodo-radio" checked>
                <span>Tarde</span>
              </label>
            </div>
            <input type="hidden" id="evolucao-periodo" value="tarde">
          </div>
          <div><label class="lbl" for="evolucao-inicio">De</label><input id="evolucao-inicio" type="date" class="inp"></div>
          <div><label class="lbl" for="evolucao-fim">Até</label><input id="evolucao-fim" type="date" class="inp"></div>
      </div>
      <div class="card" style="padding:20px">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap">
          <div>
            <div id="evolucao-aluno-nome" style="font-size:16px;font-weight:700">Selecione um aluno</div>
            <div id="evolucao-aluno-meta" style="font-size:11px;color:var(--text-muted);margin-top:4px"></div>
          </div>
          <div id="evolucao-resumo" class="tag t-tea">0 registros</div>
        </div>
        <hr style="border:none;border-top:1px solid var(--border);margin:16px 0">
        <div class="evolucao-grafico-layout">
          <div>
            <div class="evolucao-grafico-box">
              <div class="evolucao-grafico-titulo">Evolução por período</div>
              <div style="font-size:11px;color:var(--text-muted);text-align:center;margin-bottom:9px">Feliz corresponde a Bom, neutro a Neutro e triste a Ruim.</div>
              <svg id="evolucao-grafico" viewBox="0 0 760 320"></svg>
              <div class="evolucao-grafico-eixo">Registros: data, hora e período</div>
            </div>
          </div>
          <div class="evolucao-grafico-legenda">
            <div style="font-size:12px;font-weight:700;color:var(--text-muted);margin-bottom:10px">Linhas por quesito</div>
            <div id="evolucao-legenda"></div>
            <div id="evolucao-tendencia" style="margin-top:14px;padding:10px 12px;border-radius:10px;background:rgba(0,166,81,.08);font-size:11px;line-height:1.5"></div>
          </div>
        </div>
        <div class="table-scroll" style="margin-top:20px">
          <table style="width:100%;border-collapse:collapse;min-width:650px">
            <thead><tr style="background:rgba(7,91,45,.06)"><th style="text-align:left;padding:11px;font-size:10px">DATA</th><th style="text-align:left;padding:11px;font-size:10px">PERÍODO</th><th style="text-align:left;padding:11px;font-size:10px">QUESITO</th><th style="text-align:left;padding:11px;font-size:10px">AVALIAÇÃO</th><th style="text-align:left;padding:11px;font-size:10px">OBSERVAÇÃO</th></tr></thead>
            <tbody id="evolucao-tabela"></tbody>
          </table>
        </div>
        <div id="evolucao-paginacao" style="display:flex;align-items:center;justify-content:center;gap:12px;margin-top:14px"></div>
      </div>
      </div>
    </div>

    <!-- ============= USUÁRIOS ============= -->
    <?php if (in_array($tipoUsuario, ['master', 'aee'], true)): ?>
    <div class="page" id="page-usuarios">
      <div class="card" style="padding:20px;margin-bottom:18px;border-left:4px solid var(--gold)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
          <div>
            <div style="font-size:14px;font-weight:700"><i class="bi bi-person-check" style="color:var(--gold);margin-right:7px"></i>Solicitações de Cadastro</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:3px">Aprove ou recuse novos acessos antes do primeiro login.</div>
          </div>
          <span id="usuarios-pendentes" class="tag t-tea">0 pendente(s)</span>
        </div>
        <div class="table-scroll">
          <table style="width:100%;border-collapse:collapse;min-width:900px">
            <thead><tr style="background:rgba(255,176,0,.08)">
              <th style="text-align:left;padding:10px;font-size:10px">NOME</th><th style="text-align:left;padding:10px;font-size:10px">CPF</th><th style="text-align:left;padding:10px;font-size:10px">TELEFONE</th><th style="text-align:left;padding:10px;font-size:10px">E-MAIL</th><th style="text-align:left;padding:10px;font-size:10px">PERFIL</th><th style="text-align:right;padding:10px;font-size:10px">AÇÕES</th>
            </tr></thead>
            <tbody id="usuarios-solicitacoes"></tbody>
          </table>
        </div>
        <div id="usuarios-solicitacoes-vazio" class="empty" style="padding:20px 0"><i class="bi bi-person-check"></i><span style="font-size:12px">Nenhuma solicitação pendente</span></div>
      </div>

      <div class="card" style="overflow:hidden">
        <div style="display:flex;align-items:center;gap:10px;padding:16px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap">
          <div style="display:flex;align-items:center;gap:8px;margin-right:auto"><i class="bi bi-people" style="color:var(--c3)"></i><span style="font-size:14px;font-weight:700">Usuários cadastrados</span><span id="usuarios-total" style="font-size:11px;color:var(--text-muted)"></span></div>
          <input id="usuarios-busca" class="inp" style="width:250px" placeholder="Buscar nome ou e-mail" oninput="carregarUsuarios()">
          <select id="usuarios-status" class="inp" style="width:135px" onchange="carregarUsuarios()"><option value="">Todos</option><option value="1">Ativos</option><option value="0">Inativos</option></select>
        </div>
        <div class="table-scroll">
          <table style="width:100%;border-collapse:collapse;min-width:1050px">
            <thead><tr style="background:rgba(7,91,45,.06)"><th style="text-align:left;padding:11px 20px;font-size:10px">USUÁRIO</th><th style="text-align:left;padding:11px 20px;font-size:10px">CPF</th><th style="text-align:left;padding:11px 20px;font-size:10px">TELEFONE</th><th style="text-align:left;padding:11px 20px;font-size:10px">E-MAIL</th><th style="text-align:left;padding:11px 20px;font-size:10px">PERFIL</th><th style="text-align:left;padding:11px 20px;font-size:10px">STATUS</th><th style="text-align:right;padding:11px 20px;font-size:10px">AÇÕES</th></tr></thead>
            <tbody id="usuarios-tabela"></tbody>
          </table>
        </div>
        <div id="usuarios-vazio" class="empty" style="display:none"><i class="bi bi-people"></i><span style="font-size:13px">Nenhum usuário encontrado</span></div>
      </div>

      <div class="modal-bg" id="usuario-editor" onclick="if(event.target===this)fecharEditorUsuario()">
        <div class="modal-box" style="max-width:520px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px"><div><div id="usuario-editor-titulo" style="font-size:16px;font-weight:700">Novo Usuário</div><div style="font-size:11px;color:var(--text-muted)">Preencha os dados de acesso</div></div><button onclick="fecharEditorUsuario()" class="btn-g" style="padding:6px 10px"><i class="bi bi-x-lg"></i></button></div>
          <input type="hidden" id="usuario-id">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div style="grid-column:1/-1"><label class="lbl">Nome completo *</label><input id="usuario-nome" class="inp"></div>
            <div><label class="lbl">E-mail *</label><input id="usuario-email" type="email" class="inp"></div>
            <div><label class="lbl">Perfil *</label><select id="usuario-tipo" class="inp"><option value="aee">AEE</option><option value="apoio">Apoio</option><option value="responsavel">Responsável</option><option value="master">Master</option></select></div>
            <div><label class="lbl">Senha <span id="usuario-senha-ajuda">*</span></label><input id="usuario-senha" type="password" class="inp" placeholder="Mínimo de 6 caracteres"></div>
          </div>
          <div style="display:flex;gap:9px;margin-top:20px"><button onclick="fecharEditorUsuario()" class="btn-g" style="flex:1;justify-content:center">Cancelar</button><button onclick="salvarUsuario()" class="btn-p" style="flex:1;justify-content:center"><i class="bi bi-save2"></i> Salvar</button></div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ============= QUESITOS DE AVALIAÇÃO ============= -->
    <?php if (in_array($tipoUsuario, ['master', 'aee'], true)): ?>
    <div class="page" id="page-quesitos">
      <div class="card" style="padding:20px;margin-bottom:18px;border-left:4px solid var(--gold)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
          <div>
            <div style="font-size:17px;font-weight:700"><i class="bi bi-ui-checks-grid" style="color:var(--gold);margin-right:7px"></i>Quesitos avaliação</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:5px">Defina os critérios observados nas avaliações dos alunos por condição geral.</div>
          </div>
          <button onclick="abrirQuesitoEditor()" class="btn-p"><i class="bi bi-plus-lg"></i> Novo quesito</button>
        </div>
      </div>

      <div class="card" style="overflow:hidden">
        <div style="display:flex;align-items:center;gap:10px;padding:16px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap">
          <div style="display:flex;align-items:center;gap:8px;margin-right:auto"><i class="bi bi-list-check" style="color:var(--c3)"></i><span style="font-size:14px;font-weight:700">Critérios cadastrados</span><span id="quesitos-total" style="font-size:11px;color:var(--text-muted)"></span></div>
        </div>
        <div class="table-scroll">
          <table style="width:100%;border-collapse:collapse;min-width:680px">
            <thead><tr style="background:rgba(7,91,45,.06)"><th style="text-align:left;padding:11px 20px;font-size:10px">QUESITO</th><th style="text-align:left;padding:11px 20px;font-size:10px">STATUS</th><th style="text-align:right;padding:11px 20px;font-size:10px">AÇÕES</th></tr></thead>
            <tbody id="quesitos-tabela"></tbody>
          </table>
        </div>
        <div id="quesitos-vazio" class="empty" style="display:none"><i class="bi bi-ui-checks"></i><span style="font-size:13px">Nenhum quesito encontrado</span></div>
      </div>

      <div class="modal-bg" id="quesito-editor" onclick="if(event.target===this)fecharQuesitoEditor()">
        <div class="modal-box" style="max-width:560px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px"><div><div id="quesito-editor-titulo" style="font-size:16px;font-weight:700">Novo quesito</div><div style="font-size:11px;color:var(--text-muted)">O quesito ficará disponível para vinculação aos alunos.</div></div><button onclick="fecharQuesitoEditor()" class="btn-g" style="padding:6px 10px"><i class="bi bi-x-lg"></i></button></div>
          <input type="hidden" id="quesito-id">
          <label class="lbl">Título *</label><input id="quesito-titulo" class="inp" maxlength="150" placeholder="Ex.: Comunicação verbal">
          <label class="lbl" style="margin-top:12px">Descrição *</label><textarea id="quesito-descricao" class="inp" rows="4" maxlength="1000" placeholder="O que este quesito avalia no aluno"></textarea>
          <div style="display:flex;gap:9px;margin-top:20px"><button onclick="fecharQuesitoEditor()" class="btn-g" style="flex:1;justify-content:center">Cancelar</button><button onclick="salvarQuesito()" class="btn-p" style="flex:1;justify-content:center"><i class="bi bi-save2"></i> Salvar</button></div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ============= CONDIÇÕES GERAIS ============= -->
    <?php if (in_array($tipoUsuario, ['master', 'aee'], true)): ?>
    <div class="page" id="page-condicoes">
      <div class="card" style="padding:20px;margin-bottom:18px;border-left:4px solid var(--gold)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
          <div>
            <div style="font-size:17px;font-weight:700"><i class="bi bi-clipboard2-pulse" style="color:var(--gold);margin-right:7px"></i>Condições gerais</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:5px">Cadastre as condições gerais disponíveis nos cadastros dos alunos.</div>
          </div>
          <button onclick="abrirCondicaoEditor()" class="btn-p"><i class="bi bi-plus-lg"></i> Nova condição</button>
        </div>
      </div>

      <div class="card" style="overflow:hidden">
        <div style="display:flex;align-items:center;gap:10px;padding:16px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap">
          <div style="display:flex;align-items:center;gap:8px;margin-right:auto"><i class="bi bi-list-ul" style="color:var(--c3)"></i><span style="font-size:14px;font-weight:700">Condições cadastradas</span><span id="condicoes-total" style="font-size:11px;color:var(--text-muted)"></span></div>
        </div>
        <div class="table-scroll">
          <table style="width:100%;border-collapse:collapse;min-width:780px">
            <thead><tr style="background:rgba(7,91,45,.06)"><th style="text-align:left;padding:11px 20px;font-size:10px">CONDIÇÃO GERAL</th><th style="text-align:left;padding:11px 20px;font-size:10px">SIGLA</th><th style="text-align:left;padding:11px 20px;font-size:10px">STATUS</th><th style="text-align:right;padding:11px 20px;font-size:10px">AÇÕES</th></tr></thead>
            <tbody id="condicoes-tabela"></tbody>
          </table>
        </div>
        <div id="condicoes-vazio" class="empty" style="display:none"><i class="bi bi-clipboard2-x"></i><span style="font-size:13px">Nenhuma condição geral encontrada</span></div>
      </div>

      <div class="modal-bg" id="condicao-editor" onclick="if(event.target===this)fecharCondicaoEditor()">
        <div class="modal-box" style="max-width:560px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px"><div><div id="condicao-editor-titulo" style="font-size:16px;font-weight:700">Adicionar Condição geral</div><div style="font-size:11px;color:var(--text-muted)">A nova opção ficará disponível nos próximos cadastros.</div></div><button onclick="fecharCondicaoEditor()" class="btn-g" style="padding:6px 10px"><i class="bi bi-x-lg"></i></button></div>
          <input type="hidden" id="condicao-id">
          <label class="lbl">Nome *</label><input id="condicao-nome" class="inp" maxlength="150" placeholder="Digite o nome da condição geral">
          <label class="lbl" style="margin-top:12px">Sigla *</label><input id="condicao-sigla" class="inp" maxlength="30" placeholder="Ex.: TEA">
          <div style="font-size:11px;color:var(--text-muted);margin-top:5px">A sigla será usada nos cadastros e relatórios do sistema.</div>
          <label class="lbl" style="margin-top:12px">Descrição *</label><textarea id="condicao-descricao" class="inp" rows="3" maxlength="500" placeholder="Descreva a condição geral"></textarea>
          <div style="display:flex;gap:9px;margin-top:20px"><button onclick="fecharCondicaoEditor()" class="btn-g" style="flex:1;justify-content:center">Cancelar</button><button onclick="salvarCondicao()" class="btn-p" style="flex:1;justify-content:center"><i class="bi bi-check-circle-fill"></i> Salvar</button></div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ============= PERFIS ============= -->
    <div class="page" id="page-perfis">
      <?php if ($tipoUsuario !== 'apoio'): ?>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
        <div class="srch" style="flex:1">
          <i class="bi bi-search"></i>
          <input type="text" class="inp" id="srch-inp" placeholder="Buscar por nome ou matrícula..." oninput="filterS(this.value)" />
        </div>
        <select class="inp" style="width:165px" id="fil-turma" onchange="filterS(document.getElementById('srch-inp').value)">
          <option value="">Todas as Turmas</option>
        </select>
        <select class="inp" style="width:185px" id="fil-diag" onchange="filterS(document.getElementById('srch-inp').value)">
          <option value="">Todas as condições gerais</option>
        </select>
        <button onclick="abrirModalCadastro()" class="btn-p" style="white-space:nowrap">
          <i class="bi bi-person-plus-fill"></i> Novo Aluno
        </button>
      </div>
      <?php endif; ?>

      <div class="card" style="overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 22px;border-bottom:1px solid var(--border)">
          <div style="display:flex;align-items:center;gap:8px">
            <i class="bi bi-people" style="color:var(--c3)"></i>
            <span style="font-size:13px;font-weight:600" id="s-count">Alunos: 0</span>
          </div>
          <?php if ($tipoUsuario !== 'apoio'): ?><button class="btn-g" style="font-size:12px" onclick="exportCSV()">
            <i class="bi bi-download"></i> Exportar CSV
          </button><?php endif; ?>
        </div>
        <div id="s-table" class="student-profile-grid">
        </div>
        <div id="s-empty" class="empty">
          <i class="bi bi-person-x-fill"></i>
          <span style="font-size:14px;font-weight:600">Nenhum aluno encontrado</span>
          
          
        </div>
      </div>
    </div>

    <!-- ============= AGENDA ============= -->
    <div class="page" id="page-agenda">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:10px">
          <button onclick="prevM()" class="btn-g" style="padding:8px 12px"><i class="bi bi-chevron-left"></i></button>
          <div style="font-size:17px;font-weight:700;min-width:190px;text-align:center" id="cal-title"></div>
          <button onclick="nextM()" class="btn-g" style="padding:8px 12px"><i class="bi bi-chevron-right"></i></button>
          <button onclick="goToday()" class="btn-g" style="font-size:12.5px">Hoje</button>
        </div>
        <button onclick="openEvModal()" class="btn-p">
          <i class="bi bi-plus-circle-fill"></i> Novo Evento
        </button>
      </div>
      <div class="card" style="overflow:hidden">
        <div style="display:grid;grid-template-columns:repeat(7,1fr);background:rgba(255,255,255,0.03);border-bottom:1px solid var(--border)">
          <div style="text-align:center;padding:11px;font-size:10.5px;font-weight:700;letter-spacing:0.8px;color:rgba(255,255,255,0.36)">SEG</div>
          <div style="text-align:center;padding:11px;font-size:10.5px;font-weight:700;letter-spacing:0.8px;color:rgba(255,255,255,0.36)">TER</div>
          <div style="text-align:center;padding:11px;font-size:10.5px;font-weight:700;letter-spacing:0.8px;color:rgba(255,255,255,0.36)">QUA</div>
          <div style="text-align:center;padding:11px;font-size:10.5px;font-weight:700;letter-spacing:0.8px;color:rgba(255,255,255,0.36)">QUI</div>
          <div style="text-align:center;padding:11px;font-size:10.5px;font-weight:700;letter-spacing:0.8px;color:rgba(255,255,255,0.36)">SEX</div>
          <div style="text-align:center;padding:11px;font-size:10.5px;font-weight:700;letter-spacing:0.8px;color:rgba(255,255,255,0.36)">SÁB</div>
          <div style="text-align:center;padding:11px;font-size:10.5px;font-weight:700;letter-spacing:0.8px;color:rgba(255,255,255,0.36)">DOM</div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(7,1fr)" id="cal-grid"></div>
      </div>
    </div>

    <!-- ============= CADASTRAR ============= -->
    <div class="modal-bg" id="modal-cadastrar" onclick="if(event.target===this)fecharModalCadastro()">
      <div style="max-width:660px;margin:0 auto">
        <div class="modal-box" style="position:relative;max-width:660px;max-height:92vh;overflow-y:auto;padding:32px">
          <div style="text-align:center;margin-bottom:26px">
            <button type="button" onclick="fecharModalCadastro()" class="btn-g" style="position:absolute;top:20px;right:20px;padding:6px 10px" aria-label="Fechar cadastro"><i class="bi bi-x-lg"></i></button>
            <div style="width:58px;height:58px;border-radius:15px;background:linear-gradient(135deg,var(--c1),var(--c3));display:flex;align-items:center;justify-content:center;margin:0 auto 12px;box-shadow:0 8px 22px rgba(0,122,51,0.32)">
              <div style="display:flex;align-items:center;justify-content:center;gap:6px;line-height:1">
                <i class="bi bi-person-fill" style="font-size:22px;color:#fff !important"></i>
                <i class="bi bi-plus-circle-fill" style="font-size:13px;color:#fff !important;margin-left:-2px"></i>
              </div>
            </div>
            <div style="font-size:19px;font-weight:700">Cadastrar Novo Aluno</div>
            <div style="font-size:12.5px;color:var(--text-muted);margin-top:4px">Preencha todos os campos obrigatórios</div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div style="grid-column:1/-1">
              <label class="lbl">Nome Completo *</label>
              <input type="text" id="f-nome" class="inp" placeholder="Nome completo do aluno" />
            </div>
            <div>
              <label class="lbl">Matrícula *</label>
              <input type="text" id="f-mat" class="inp" placeholder="Ex: 2025001" maxlength="7" oninput="onlyDigits(this, 7)" />
            </div>
            <div>
              <label class="lbl">CPF *</label>
              <input type="text" id="f-cpf" class="inp" placeholder="000.000.000-00" oninput="mCPF(this)" maxlength="14" />
            </div>
            <div>
              <label class="lbl">Data de Nascimento *</label>
              <input type="date" id="f-nasc" class="inp" />
            </div>
            <div>
              <label class="lbl">Sexo *</label>
              <select id="f-sexo" class="inp">
                <option value="">Selecionar</option>
                <option>Masculino</option><option>Feminino</option>
                <option>Outro / Prefiro não informar</option>
              </select>
            </div>
            <div>
              <label class="lbl">Turma *</label>
              <select id="f-turma" class="inp">
                <option value="">Selecionar</option>
                <option value="1 Ano">1º Ano</option>
                <option value="2 Ano">2º Ano</option>
                <option value="3 Ano">3º Ano</option>
              </select>
            </div>
            <div>
              <label class="lbl">Curso *</label>
              <div style="display:flex;gap:7px;align-items:center">
                <select id="f-curso" class="inp">
                  <option value="">Selecione um curso</option>
                </select>
                <button type="button" class="btn-g" style="padding:9px 11px;flex-shrink:0" title="Adicionar curso" onclick="abrirModalOpcaoAluno('curso')"><i class="bi bi-plus-lg"></i></button>
              </div>
            </div>
            <div>
              <label class="lbl">Condição geral *</label>
              <div style="display:flex;gap:7px;align-items:center">
                <select id="f-diag" class="inp" multiple hidden></select>
                <div class="multi-select" id="diag-multiselect">
                  <button type="button" class="inp multi-select-toggle" onclick="toggleDiagnosticoMenu()" aria-haspopup="listbox" aria-expanded="false">
                    <span class="multi-select-values" id="diag-selected-values"><span class="multi-select-placeholder">Selecione uma condição geral</span></span>
                    <i class="bi bi-chevron-down"></i>
                  </button>
                  <div class="multi-select-menu" id="diag-options" role="listbox" aria-multiselectable="true"></div>
                </div>
                <button type="button" class="btn-g" style="padding:9px 11px;flex-shrink:0" title="Adicionar condição geral" onclick="abrirModalOpcaoAluno('diagnostico')"><i class="bi bi-plus-lg"></i></button>
              </div>
            </div>
            <div>
              <label class="lbl">Apoio <span style="font-weight:400;color:var(--text-muted)">(opcional)</span></label>
              <select id="f-apoio" class="inp"><option value="">Sem apoio definido</option></select>
            </div>
            
          </div>
          <div class="modal-bg" id="modal-opcao-aluno" onclick="if(event.target===this)fecharModalOpcaoAluno()">
            <div class="modal-box" style="max-width:520px">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px"><div><div id="modal-opcao-titulo" style="font-size:16px;font-weight:700">Adicionar opção</div><div style="font-size:11px;color:var(--text-muted)">A nova opção ficará disponível nos próximos cadastros.</div></div><button type="button" onclick="fecharModalOpcaoAluno()" class="btn-g" style="padding:6px 10px"><i class="bi bi-x-lg"></i></button></div>
              <div id="modal-opcao-nome-wrap">
                <label class="lbl" for="modal-opcao-nome">Nome *</label>
                <input id="modal-opcao-nome" class="inp" placeholder="Digite o nome da opção" maxlength="150">
              </div>
              <div id="modal-opcao-sigla-wrap" style="display:none;margin-top:12px">
                <label class="lbl" for="modal-opcao-sigla">Sigla *</label>
                <input id="modal-opcao-sigla" class="inp" placeholder="Ex.: TEA" maxlength="30">
                <div style="font-size:11px;color:var(--text-muted);margin-top:5px">A sigla será usada nos cadastros e relatórios do sistema.</div>
              </div>
              <div id="modal-opcao-descricao-wrap" style="display:none;margin-top:12px">
                <label class="lbl" for="modal-opcao-descricao">Descrição *</label>
                <textarea id="modal-opcao-descricao" class="inp" rows="3" maxlength="500" placeholder="Descreva a condição geral"></textarea>
              </div>
              <div style="display:flex;gap:9px;margin-top:20px"><button type="button" onclick="fecharModalOpcaoAluno()" class="btn-g" style="flex:1;justify-content:center">Cancelar</button><button type="button" onclick="salvarOpcaoAluno()" class="btn-p" style="flex:1;justify-content:center"><i class="bi bi-check-circle-fill"></i> Salvar</button></div>
              <div style="margin-top:18px; border-top:1px solid var(--border); padding-top:16px;">
                <div id="modal-opcao-subtitulo" style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px">Cursos cadastrados</div>
                <div id="modal-opcao-lista" style="display:flex;flex-direction:column;gap:8px;max-height:220px;overflow:auto"></div>
              </div>
            </div>
          </div>
          <div style="display:flex;gap:10px;margin-top:22px">
            <button onclick="clearF()" class="btn-g" style="flex:1;justify-content:center">
              <i class="bi bi-arrow-counterclockwise"></i> Limpar
            </button>
            <button onclick="addStudent()" class="btn-p" style="flex:2.5;justify-content:center;font-size:14.5px">
              <i class="bi bi-check-circle-fill"></i> Cadastrar Aluno
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============= PERFIL ALUNO ============= -->
    <div class="page" id="page-aluno">
      <button onclick="go('<?= $tipoUsuario === 'apoio' ? 'dashboard' : 'perfis' ?>')" class="btn-g" style="margin-bottom:18px;font-size:12.5px">
        <i class="bi bi-arrow-left"></i> <?= $tipoUsuario === 'apoio' ? 'Voltar para Início' : 'Voltar para Perfis' ?>
      </button>
      <?php if ($tipoUsuario !== 'apoio'): ?>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
        <button type="button" class="btn-g" onclick="abrirSeletorFotoAluno()"><i class="bi bi-camera"></i> Alterar foto do PCD</button>
        <button type="button" class="btn-g" id="btn-remover-foto-aluno" onclick="removerFotoAluno()" disabled><i class="bi bi-trash3"></i> Remover foto</button>
        <input type="file" id="input-foto-aluno" accept="image/jpeg,image/png,image/webp" hidden>
      </div>
      <?php endif; ?>
      <div style="display:grid;grid-template-columns:270px 1fr;gap:18px">
        <!-- Informaçoes -->
        <div>
          <div class="card" style="padding:22px;text-align:center;margin-bottom:12px">
            <div class="av" id="a-av" style="width:70px;height:70px;font-size:26px;margin:0 auto 14px;box-shadow:0 0 0 3px rgba(255,215,0,0.28),0 0 0 6px rgba(255,215,0,0.08);background-size:cover;background-position:center">?</div>
            <div style="font-size:17px;font-weight:700;margin-bottom:5px" id="a-nome">—</div>
            <span class="tag" id="a-diag-tag">—</span>
            <hr style="border:none;border-top:1px solid var(--border);margin:14px 0" />
            <div style="text-align:left;font-size:12.5px">
              <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted)">Matrícula</span><span style="font-weight:600" id="a-mat">—</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted)">Turma</span><span style="font-weight:600" id="a-turma">—</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted)">Curso</span><span style="font-weight:600" id="a-curso">—</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted)">Sexo</span><span style="font-weight:600" id="a-sexo">—</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:8px 0">
                <span style="color:var(--text-muted)">Nascimento</span><span style="font-weight:600" id="a-nasc">—</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:8px 0">
                <span style="color:var(--text-muted)">Apoio</span><span style="font-weight:600" id="a-acompanhante">—</span>
              </div>
              <?php if (in_array($tipoUsuario, ['master', 'aee'], true)): ?>
              <div id="apoio-vinculo-area" style="display:none;margin-top:10px;padding-top:12px;border-top:1px solid var(--border);text-align:left">
                <label class="lbl" id="apoio-vinculo-label">Definir apoio</label>
                <div id="apoio-vincular-controls" style="display:flex;gap:7px;align-items:center">
                  <select id="perfil-apoio-select" class="inp"><option value="">Sem apoio definido</option></select>
                  <button type="button" class="btn-p" style="padding:9px 12px;flex-shrink:0" onclick="vincularApoioAluno()" title="Vincular apoio"><i class="bi bi-link-45deg"></i></button>
                </div>
                <button type="button" id="apoio-desvincular-btn" class="btn-g" style="display:none;width:100%;justify-content:center;color:#c62828;border-color:rgba(198,40,40,.28)" onclick="desvincularApoioAluno()"><i class="bi bi-link-45deg"></i> Desvincular apoio</button>
              </div>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($tipoUsuario !== 'apoio'): ?><button onclick="openDelModal()" style="width:100%;padding:9px;border-radius:10px;background:rgba(198,40,40,0.10);border:1px solid rgba(198,40,40,0.35);color:#c62828;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif;font-size:13px;display:flex;align-items:center;justify-content:center;gap:7px;transition:all 0.18s" onmouseenter="this.style.background='rgba(198,40,40,0.18)'" onmouseleave="this.style.background='rgba(198,40,40,0.10)'">
            <i class="bi bi-trash3"></i> Remover Aluno
          </button><?php endif; ?>
        </div>

        <!-- Conteúdo dos acompanhantes -->
        <div class="card perfil-aluno-relatorio" style="overflow-x:hidden">
          <div class="tab-row" style="display:flex;gap:4px;padding:10px;border-bottom:1px solid var(--border);background:rgba(255,255,255,0.02);flex-wrap:nowrap">
            <?php if (in_array($tipoUsuario, ['master', 'aee', 'apoio'], true)): ?><button class="tab-btn active" id="tb-apoio" onclick="swTab('apoio',this)"><i class="bi bi-clipboard2-pulse"></i> Quesitos do aluno</button><?php endif; ?>
            <button class="tab-btn" id="tb-pei" onclick="swTab('pei',this)"><i class="bi bi-journal-text"></i> PEI</button>
            <button class="tab-btn" id="tb-paee" onclick="swTab('paee',this)"><i class="bi bi-person-workspace"></i> PAEE</button>
            <button class="tab-btn" id="tb-lau" onclick="swTab('lau',this)"><i class="bi bi-file-earmark-medical"></i> Laudo</button>
          </div>
          <!-- Registro de apoio -->
          <div id="tc-apoio" style="padding:18px">
            <?php if (in_array($tipoUsuario, ['master', 'aee', 'apoio'], true)): ?>
            <div id="apoio-config-quesitos" style="margin-bottom:16px">
              <div style="padding:14px;border:1px solid var(--border);border-radius:10px;background:rgba(0,166,81,.04)">
                <div style="font-size:13px;font-weight:700">Quesitos do aluno</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px">Carregando os quesitos associados...</div>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <!-- PEI -->
          <div id="tc-pei" style="padding:18px;display:none">
            <div id="pei-titulo" style="font-size:13px;font-weight:700;color:rgba(255,255,255,0.55);margin-bottom:14px">PEI - Plano Educacional Individualizado</div>
            <div class="document-view-form<?= $tipoUsuario === 'apoio' ? ' document-view-form--hidden' : '' ?>" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
              <div style="grid-column:1/-1">
                <label class="lbl" for="pei-nome">Matéria escolar</label>
                <input type="text" id="pei-nome" class="inp" maxlength="150" placeholder="Ex.: Matemática" />
              </div>
              <div>
                <label class="lbl">Data de elaboração</label>
                <input type="date" id="pei-data" class="inp" />
              </div>
              <div>
                <label class="lbl">Responsável pelo preenchimento</label>
                <input type="text" id="pei-responsavel" class="inp" placeholder="Nome do responsável" />
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Objetivos pedagógicos</label>
                <textarea id="pei-objetivos" class="inp" rows="4" placeholder="Descreva os objetivos pedagógicos..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Habilidades a desenvolver</label>
                <textarea id="pei-habilidades" class="inp" rows="4" placeholder="Descreva as habilidades..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Estratégias de ensino</label>
                <textarea id="pei-estrategias" class="inp" rows="4" placeholder="Descreva as estratégias..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Recursos de acessibilidade</label>
                <textarea id="pei-recursos" class="inp" rows="4" placeholder="Liste os recursos de acessibilidade..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Adaptações curriculares</label>
                <textarea id="pei-adaptacoes" class="inp" rows="4" placeholder="Descreva as adaptações..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Critérios de avaliação</label>
                <textarea id="pei-avaliacao" class="inp" rows="4" placeholder="Defina os critérios de avaliação..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Observações</label>
                <textarea id="pei-observacoes" class="inp" rows="4" placeholder="Observações adicionais..."></textarea>
              </div>
            </div>
            <?php if ($tipoUsuario !== 'apoio'): ?><button onclick="savePEI()" class="btn-p" style="width:100%;justify-content:center;font-size:13px;margin-top:16px">
              <i class="bi bi-save2"></i> Salvar PEI
            </button><?php endif; ?>
            <div id="pei-history" style="margin-top:24px"></div>
          </div>

          <!-- PAEE -->
          <div id="tc-paee" style="padding:18px;display:none">
            <div id="paee-titulo" style="font-size:13px;font-weight:700;color:rgba(255,255,255,0.55);margin-bottom:14px">PAEE - Plano de Atendimento Educacional Especializado</div>
            <div class="document-view-form<?= $tipoUsuario === 'apoio' ? ' document-view-form--hidden' : '' ?>" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
              <div style="grid-column:1/-1">
                <label class="lbl" for="paee-nome">Nome do documento</label>
                <input type="text" id="paee-nome" class="inp" maxlength="150" placeholder="Ex.: PAEE - João Silva" />
              </div>
              <div>
                <label class="lbl">Data do atendimento</label>
                <input type="date" id="paee-data" class="inp" />
              </div>
              <div>
                <label class="lbl">Profissional responsável</label>
                <input type="text" id="paee-responsavel" class="inp" placeholder="Nome do profissional" />
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Necessidades identificadas</label>
                <textarea id="paee-necessidades" class="inp" rows="4" placeholder="Descreva as necessidades..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Objetivos do atendimento</label>
                <textarea id="paee-objetivos" class="inp" rows="4" placeholder="Descreva os objetivos..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Recursos utilizados</label>
                <textarea id="paee-recursos" class="inp" rows="4" placeholder="Liste os recursos utilizados..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Estratégias aplicadas</label>
                <textarea id="paee-estrategias" class="inp" rows="4" placeholder="Descreva as estratégias..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Tecnologias assistivas</label>
                <textarea id="paee-tecnologias" class="inp" rows="4" placeholder="Liste as tecnologias..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Resultados observados</label>
                <textarea id="paee-resultados" class="inp" rows="4" placeholder="Descreva os resultados..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Encaminhamentos</label>
                <textarea id="paee-encaminhamentos" class="inp" rows="4" placeholder="Descreva os encaminhamentos..."></textarea>
              </div>
              <div style="grid-column:1/-1">
                <label class="lbl">Observações gerais</label>
                <textarea id="paee-observacoes" class="inp" rows="4" placeholder="Observações gerais..."></textarea>
              </div>
            </div>
            <?php if ($tipoUsuario !== 'apoio'): ?><button onclick="savePAEE()" class="btn-p" style="width:100%;justify-content:center;font-size:13px;margin-top:16px">
              <i class="bi bi-save2"></i> Salvar PAEE
            </button><?php endif; ?>
            <div id="paee-history" style="margin-top:24px"></div>
          </div>

          <!-- Laudo -->
          <div id="tc-lau" style="padding:18px;display:none">
            <div style="font-size:13px;font-weight:700;color:rgba(255,255,255,0.55);margin-bottom:14px">Laudo externo</div>
            <?php if ($tipoUsuario !== 'apoio'): ?><div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:12px;padding:15px">
              <label class="lbl">Selecionar arquivo PDF</label>
              <input type="file" id="lau-file" accept="application/pdf" class="inp" />
              <button onclick="handleLaudoUpload()" class="btn-p" style="width:100%;justify-content:center;font-size:13px;margin-top:12px">
                <i class="bi bi-upload"></i> Enviar arquivo
              </button>
            </div><?php endif; ?>
            <div style="font-size:12.5px;font-weight:600;color:rgba(255,255,255,0.5);margin:16px 0 8px">Laudos enviados</div>
            <div id="lau-list"></div>
            <div id="lau-viewer" style="margin-top:20px"></div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<!-- ============= MODAL EVENTO ============= -->
<div class="modal-bg" id="ev-modal" onclick="if(event.target===this)closeM('ev-modal')">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
      <div>
        <div style="font-size:16px;font-weight:700">Novo Evento</div>
        <div style="font-size:11.5px;color:var(--text-muted)">Adicione à agenda</div>
      </div>
      <button onclick="closeM('ev-modal')" style="width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,0.06);border:none;cursor:pointer;color:rgba(255,255,255,0.48);font-size:15px;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div style="display:flex;flex-direction:column;gap:12px">
      <div><label class="lbl">Título *</label><input type="text" id="ev-tit" class="inp" placeholder="Ex: Reunião de Pais" /></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div><label class="lbl">Data *</label><input type="date" id="ev-dat" class="inp" /></div>
        <div><label class="lbl">Horário</label><input type="time" id="ev-hor" class="inp" /></div>
      </div>
      <div><label class="lbl">Descrição</label><textarea id="ev-desc" class="inp" rows="2" placeholder="Opcional..."></textarea></div>
    </div>
    <div style="display:flex;gap:9px;margin-top:18px">
      <button onclick="closeM('ev-modal')" class="btn-g" style="flex:1;justify-content:center">Cancelar</button>
      <button onclick="saveEv()" class="btn-p" style="flex:2;justify-content:center">
        <i class="bi bi-check-circle-fill"></i> Salvar Evento
      </button>
    </div>
  </div>
</div>

<?php if ($tipoUsuario === 'apoio'): ?>
<!-- ============= MODAL AVALIAÇÃO ============= -->
<div class="modal-bg" id="avaliacao-modal" onclick="if(event.target===this)fecharModalAvaliacao()">
  <div class="modal-box avaliacao-modal-box">
    <div class="avaliacao-modal-header">
      <div class="avaliacao-modal-heading">
        <div class="avaliacao-modal-heading-icon"><i class="bi bi-clipboard2-check"></i></div>
        <div>
          <div id="avaliacao-modal-titulo" class="avaliacao-modal-title">Avaliação</div>
          <div id="avaliacao-modal-meta" class="avaliacao-modal-meta"></div>
        </div>
      </div>
      <button type="button" onclick="fecharModalAvaliacao()" class="btn-g" style="padding:6px 10px" aria-label="Fechar avaliação"><i class="bi bi-x-lg"></i></button>
    </div>
    <div id="avaliacao-modal-conteudo" class="avaliacao-modal-conteudo">
      <div id="avaliacao-modal-quesitos"></div>
      <label class="lbl" for="avaliacao-modal-observacao" style="margin-top:18px">Observações</label>
      <textarea id="avaliacao-modal-observacao" class="inp" rows="2" maxlength="2000" placeholder="Registre observações gerais sobre esta avaliação..."></textarea>
    </div>
    <div class="avaliacao-modal-footer">
      <button type="button" onclick="fecharModalAvaliacao()" class="btn-g">Cancelar</button>
      <button type="button" id="avaliacao-modal-salvar" onclick="salvarAvaliacaoAtual()" class="btn-p"><i class="bi bi-check-circle-fill"></i> Salvar avaliação</button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ============= MODAL DELETE ============= -->
<div class="modal-bg" id="del-modal" onclick="if(event.target===this)closeM('del-modal')">
  <div class="modal-box" style="max-width:370px;text-align:center">
    <div style="width:54px;height:54px;border-radius:50%;background:rgba(198,40,40,0.10);border:1px solid rgba(198,40,40,0.35);display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
      <i class="bi bi-trash3-fill" style="font-size:20px;color:#c62828"></i>
    </div>
    <div style="font-size:16px;font-weight:700;margin-bottom:6px">Remover Aluno</div>
    <div style="font-size:12.5px;color:var(--text-muted);margin-bottom:20px">Tem certeza que deseja remover <strong id="del-nm" style="color:#fff"></strong>? Esta ação não pode ser desfeita.</div>
    <div style="display:flex;gap:9px">
      <button onclick="closeM('del-modal')" class="btn-g" style="flex:1;justify-content:center">Cancelar</button>
      <button onclick="confirmDel()" style="flex:1;padding:9px;border-radius:10px;background:#c62828;border:none;color:#fff;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif;font-size:13.5px;transition:all 0.18s" onmouseenter="this.style.background='#a61b1b'" onmouseleave="this.style.background='#c62828'">
        Sim, remover
      </button>
    </div>
  </div>
</div>

<!-- ============= MODAL AVALIACAO DO QUESITO ============= -->
<div class="modal-bg" id="apoio-quesito-modal" onclick="if(event.target===this)fecharModalQuesito()">
  <div class="modal-box" style="max-width:520px">
    <input type="hidden" id="apoio-modal-quesito-id">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px">
      <div>
        <div id="apoio-modal-quesito-titulo" style="font-size:16px;font-weight:700">Quesito</div>
        <div id="apoio-modal-quesito-descricao" style="font-size:12px;color:var(--text-muted);margin-top:4px"></div>
        <div id="apoio-modal-quesito-peso" style="font-size:11px;color:var(--gold);margin-top:5px"></div>
      </div>
      <button type="button" onclick="fecharModalQuesito()" class="btn-g" style="padding:6px 10px"><i class="bi bi-x-lg"></i></button>
    </div>
    <div style="font-size:12px;font-weight:700;margin-bottom:8px">Avaliação</div>
    <div style="display:flex;gap:8px;margin-bottom:16px">
      <button type="button" class="btn-g apoio-avaliacao-btn" data-apoio-avaliacao="feliz"><span style="font-size:20px">😃</span> Feliz</button>
      <button type="button" class="btn-g apoio-avaliacao-btn" data-apoio-avaliacao="neutro"><span style="font-size:20px">😐</span> Neutro</button>
      <button type="button" class="btn-g apoio-avaliacao-btn" data-apoio-avaliacao="triste"><span style="font-size:20px">😢</span> Triste</button>
    </div>
    <label class="lbl" for="apoio-modal-observacao">Observação sobre este aspecto (opcional)</label>
    <textarea id="apoio-modal-observacao" class="inp" rows="3" placeholder="Descreva algo sobre este quesito..."></textarea>
    <div style="display:flex;gap:9px;margin-top:18px">
      <button type="button" onclick="fecharModalQuesito()" class="btn-g" style="flex:1;justify-content:center">Cancelar</button>
      <button type="button" onclick="confirmarModalQuesito()" class="btn-p" style="flex:1;justify-content:center"><i class="bi bi-check-circle-fill"></i> Concluir avaliação</button>
    </div>
  </div>
</div>

<!-- ============= MODAL CONFIRM GENÉRICO ============= -->
<div class="modal-bg" id="confirm-modal" onclick="if(event.target===this)closeM('confirm-modal')">
  <div class="modal-box" style="max-width:370px;text-align:center">
    <div class="confirm-modal-icon">
      <i class="bi bi-exclamation-triangle"></i>
    </div>
    <div class="confirm-modal-title" style="margin-bottom:6px">Confirmação</div>
    <div style="font-size:12.5px;color:var(--text-muted);margin-bottom:20px" id="confirm-msg">Tem certeza?</div>
    <div style="display:flex;gap:9px">
      <button onclick="closeM('confirm-modal')" class="btn-g" style="flex:1;justify-content:center">Cancelar</button>
      <button onclick="confirmAction()" id="confirm-ok" style="flex:1;padding:9px;border-radius:10px;background:rgba(76,175,80,0.9);border:none;color:#fff;font-weight:600;cursor:pointer;font-family:'Poppins',sans-serif;font-size:13.5px;transition:all 0.18s" onmouseenter="this.style.background='var(--c3)'" onmouseleave="this.style.background='rgba(76,175,80,0.9)'">
        Confirmar
      </button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast-el">
  <i class="bi bi-check-circle-fill" id="toast-ic" style="font-size:16px;color:var(--c3);flex-shrink:0"></i>
  <span id="toast-tx">Operação realizada!</span>
</div>


<script src="js/app.js?v=20261020-sidebar-collapse-2">

</script>
</body>
</html>