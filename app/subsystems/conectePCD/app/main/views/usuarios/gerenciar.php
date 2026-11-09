<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SESSION['tipo'] ?? '') !== 'master') {
    http_response_code(403);
    exit('Acesso permitido somente ao master.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciar usuários | Sistema PCD</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      color-scheme: light;
      --salaberga-dark:#064c2b;
      --salaberga-green:#008c45;
      --salaberga-active:#276448;
      --salaberga-amber:#f3a11a;
      --salaberga-workspace:#eaf3e8;
      --salaberga-ink:#14251c;
      --salaberga-muted:#64736a;
      --salaberga-line:#dce8dd;
      --salaberga-danger:#b42318;
    }
    * { box-sizing:border-box; }
    body {
      min-height:100vh;
      margin:0;
      background:var(--salaberga-workspace);
      color:var(--salaberga-ink);
      font-family:'Inter',sans-serif;
      -webkit-font-smoothing:antialiased;
    }
    body::before {
      display:block;
      height:5px;
      background:linear-gradient(90deg,var(--salaberga-dark),var(--salaberga-green) 72%,var(--salaberga-amber));
      content:'';
    }
    main { width:min(1120px,calc(100% - 48px)); margin:0 auto; padding:48px 0; }
    .top {
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:24px;
      margin-bottom:28px;
    }
    .eyebrow {
      display:inline-flex;
      align-items:center;
      gap:8px;
      margin-bottom:10px;
      color:var(--salaberga-green);
      font-size:11px;
      font-weight:700;
      letter-spacing:.1em;
      text-transform:uppercase;
    }
    .eyebrow::before { width:8px; height:8px; border-radius:50%; background:var(--salaberga-amber); content:''; }
    h1 { margin:0 0 8px; color:var(--salaberga-dark); font-size:clamp(25px,3vw,34px); font-weight:700; letter-spacing:-.045em; }
    .top p { margin:0; color:var(--salaberga-muted); font-size:14px; line-height:1.6; }
    .back-link {
      display:inline-flex;
      flex-shrink:0;
      align-items:center;
      gap:10px;
      min-height:44px;
      padding:0 16px;
      border:1px solid #cbdccf;
      border-radius:9px;
      background:#fff;
      color:var(--salaberga-dark);
      font-size:13px;
      font-weight:600;
      text-decoration:none;
      transition:background .18s ease,border-color .18s ease,transform .18s ease;
    }
    .back-link::before { content:'←'; font-size:17px; line-height:1; }
    .back-link:hover { transform:translateY(-1px); border-color:#9cbea5; background:#f8fbf8; }
    .panel {
      overflow:hidden;
      padding:24px 28px;
      border:1px solid rgba(15,23,42,.08);
      border-radius:16px;
      background:#fff;
      box-shadow:0 12px 32px rgba(7,24,16,.07);
    }
    .panel-heading {
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      padding-bottom:18px;
      border-bottom:1px solid #edf2ee;
    }
    .panel-heading h2 { margin:0; color:var(--salaberga-ink); font-size:15px; font-weight:700; }
    .pending-badge {
      display:inline-flex;
      align-items:center;
      gap:7px;
      padding:6px 10px;
      border:1px solid #f5dfb6;
      border-radius:999px;
      background:#fff8eb;
      color:#87570a;
      font-size:11px;
      font-weight:600;
    }
    .pending-badge::before { width:6px; height:6px; border-radius:50%; background:var(--salaberga-amber); content:''; }
    .request {
      display:grid;
      grid-template-columns:minmax(130px,1.2fr) minmax(170px,1.5fr) minmax(90px,.7fr) minmax(120px,1fr) auto;
      gap:18px;
      align-items:center;
      padding:20px 0;
      border-bottom:1px solid #edf2ee;
    }
    .request:last-child { border-bottom:0; }
    .label { display:block; margin-bottom:5px; color:var(--salaberga-muted); font-size:10px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; }
    .value { display:block; overflow-wrap:anywhere; color:var(--salaberga-ink); font-size:13px; font-weight:600; line-height:1.5; }
    .actions { display:flex; gap:8px; }
    button {
      min-height:36px;
      padding:0 12px;
      border:1px solid transparent;
      border-radius:8px;
      color:#fff;
      cursor:pointer;
      font:600 12px 'Inter',sans-serif;
      transition:background .18s ease,box-shadow .18s ease,transform .18s ease;
    }
    button:hover { transform:translateY(-1px); }
    .approve { background:var(--salaberga-green); box-shadow:0 3px 8px rgba(0,140,69,.16); }
    .approve:hover { background:var(--salaberga-dark); }
    .reject { border-color:#f2d0cc; background:#fff; color:var(--salaberga-danger); }
    .reject:hover { background:#fff5f4; }
    .empty { padding:46px 16px; color:var(--salaberga-muted); text-align:center; font-size:13px; }
    .notice { margin-bottom:16px; padding:12px 14px; border:1px solid #c9e5d1; border-radius:9px; background:#f1faf3; color:var(--salaberga-dark); font-size:13px; }
    [hidden] { display:none !important; }
    :focus-visible { outline:3px solid var(--salaberga-amber); outline-offset:3px; }
    @media (max-width:900px) {
      main { width:min(100% - 36px,720px); padding:32px 0; }
      .request { grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
      .actions { grid-column:1/-1; padding-top:2px; }
    }
    @media (max-width:560px) {
      main { width:calc(100% - 28px); padding:25px 0; }
      .top { align-items:flex-start; flex-direction:column; gap:18px; margin-bottom:20px; }
      .back-link { min-height:40px; }
      .panel { padding:18px 16px; border-radius:13px; }
      .request { grid-template-columns:minmax(0,1fr); gap:12px; padding:17px 0; }
      .actions { grid-column:auto; }
      .actions button { flex:1; }
    }
    @media (prefers-reduced-motion:reduce) {
      *,*::before,*::after { scroll-behavior:auto !important; transition-duration:.01ms !important; }
    }
  </style>
</head>
<body>
  <main>
    <div class="top">
      <div>
        <span class="eyebrow">Área administrativa</span>
        <h1>Solicitações de cadastro</h1>
        <p>Aprove ou recuse os pedidos de primeiro acesso.</p>
      </div>
      <a class="back-link" href="index.php">Voltar ao sistema</a>
    </div>
    <section class="panel">
      <div class="panel-heading">
        <h2>Cadastros aguardando análise</h2>
        <span class="pending-badge">Revisão necessária</span>
      </div>
      <div id="notice" class="notice" hidden></div>
      <div id="requests"><div class="empty">Carregando solicitações...</div></div>
    </section>
  </main>
  <script>
    const requests = document.getElementById('requests');
    const notice = document.getElementById('notice');
    const labels = { aee: 'AEE', apoio: 'Apoio', responsavel: 'Responsável' };

    function mostrarAviso(texto) {
      notice.textContent = texto;
      notice.hidden = false;
      setTimeout(() => { notice.hidden = true; }, 3500);
    }

    function renderizar(lista) {
      if (!lista.length) {
        requests.innerHTML = '<div class="empty">Nenhuma solicitação pendente.</div>';
        return;
      }

      requests.innerHTML = lista.map((item) => `
        <article class="request">
          <div><span class="label">Nome</span><span class="value">${escapar(item.nome)}</span></div>
          <div><span class="label">E-mail</span><span class="value">${escapar(item.email)}</span></div>
          <div><span class="label">Tipo</span><span class="value">${escapar(labels[item.tipo] || item.tipo)}</span></div>
          <div><span class="label">Solicitado em</span><span class="value">${escapar(item.criado_em || '')}</span></div>
          <div class="actions">
            <button class="approve" onclick="decidir(${Number(item.id)}, 'aprovar')">Aprovar</button>
            <button class="reject" onclick="decidir(${Number(item.id)}, 'recusar')">Recusar</button>
          </div>
        </article>
      `).join('');
    }

    function escapar(valor) {
      return String(valor).replace(/[&<>'"]/g, (caractere) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;' })[caractere]);
    }

    async function carregar() {
      const resposta = await fetch('controllers/usuario_controller.php?action=listar_solicitacoes');
      const resultado = await resposta.json();
      if (!resultado.sucesso) throw new Error(resultado.erro || 'Não foi possível carregar as solicitações.');
      renderizar(resultado.dados || []);
    }

    async function decidir(usuarioId, decisao) {
      const dados = new FormData();
      dados.append('usuario_id', usuarioId);
      dados.append('decisao', decisao);
      const resposta = await fetch('controllers/usuario_controller.php?action=decidir_solicitacao', { method:'POST', body:dados });
      const resultado = await resposta.json();
      mostrarAviso(resultado.mensagem || resultado.erro || 'Solicitação processada.');
      if (resultado.sucesso) carregar();
    }

    carregar().catch((erro) => { requests.innerHTML = `<div class="empty">${escapar(erro.message)}</div>`; });
  </script>
</body>
</html>
