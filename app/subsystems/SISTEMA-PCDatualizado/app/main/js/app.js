// Variáveis iniciais e carregamento de alunos
let students = [], events = [], filtered = [];
let abrirPerfilApoioAoCarregar = false;
let avaliacaoDados = { aluno: null, quesitos: [], avaliacoes: [] };
let avaliacaoModalAtual = null;
function carregarAlunos() {
  fetch('controllers/listar_alunos.php')
    .then(async response => {
      const payload = await response.json().catch(() => null);
      if (!response.ok || !Array.isArray(payload)) {
        const message = payload && payload.message ? payload.message : 'Não foi possível carregar os alunos.';
        console.error(message);
        students = [];
        filtered = [];
        refreshDash();
        renderTable(filtered);
        return;
      }
      return payload;
    })
    .then(data => {
      if (!Array.isArray(data)) return;
      students = data;
      filtered = students;
      atualizarFiltrosPerfis();
      refreshDash();
      renderTable(filtered);
      if (abrirPerfilApoioAoCarregar && window.usuarioTipo === 'apoio' && students[0]) {
        abrirPerfilApoioAoCarregar = false;
        go('aluno', students[0].id);
      }
      const alunoAtual = students.find(item => Number(item.id) === Number(curSid));
      if (alunoAtual) {
        const acompanhante = document.getElementById('a-acompanhante');
        if (acompanhante) acompanhante.textContent = alunoAtual.apoio_nome || '—';
        prepararVinculoApoio(alunoAtual);
      }
    })
    .catch(error => { console.error(error); students = []; filtered = []; refreshDash(); renderTable(filtered); });
}

let curSid = null, relatorios = {}, laudos = {};
let evolucaoRegistros = [];
let evolucaoPagina = 1;
const evolucaoItensPorPagina = 5;
let calDate = new Date();
let notifCount = 0;

const pageMeta = {
  dashboard: ['Dashboard','Visão geral do sistema'],
  perfis:    window.usuarioTipo === 'apoio'
    ? ['Perfil do Aluno','Aluno vinculado ao seu acompanhamento']
    : ['Perfis de Alunos','Gerencie todos os alunos PCD cadastrados'],
  agenda:    ['Agenda','Organize seus eventos e compromissos'],
  cadastrar: ['Cadastrar Aluno','Adicionar novo aluno ao sistema'],
  aluno:     ['Perfil do Aluno','Informações detalhadas e documentos'],
  usuarios:  ['Gerenciar Usuários','Controle os acessos e cadastros do sistema'],
  quesitos:  ['Quesitos avaliação','Configure os critérios observados por condição'],
  condicoes: ['Condições gerais','Gerencie as condições disponíveis nos cadastros'],
  relatorios:['Relatórios de evolução','Acompanhe o progresso dos quesitos avaliados'],
  avaliacao: ['Avaliação','Registre e acompanhe as avaliações do aluno'],
  perfil:    ['Perfil','Gerencie suas informações e foto de perfil']
  ,paee:     ['PAEE','Planos por aluno e disciplina']
  ,pei:      ['PEI','Planos Educacionais Individualizados']
};

function abrirMenu() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.overlay');
  if (sidebar) {
    sidebar.classList.add('open');
  }
  if (overlay) {
    overlay.classList.add('show');
  }
}

function fecharMenu() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.overlay');
  if (sidebar) {
    sidebar.classList.remove('open');
  }
  if (overlay) {
    overlay.classList.remove('show');
  }
}

window.abrirMenu = abrirMenu;
window.fecharMenu = fecharMenu;

const sidebarElement = document.querySelector('.sidebar');
const sidebarCollapseToggle = document.querySelector('.sidebar-collapse-toggle');
const desktopSidebarQuery = window.matchMedia('(min-width: 769px)');
const sidebarCollapseStorageKey = 'sistema-pcd-sidebar-collapsed';
let sidebarCollapsedPreference = false;
try {
  sidebarCollapsedPreference = localStorage.getItem(sidebarCollapseStorageKey) === 'true';
} catch {}

function atualizarSidebarRecolhido() {
  const recolhido = desktopSidebarQuery.matches && sidebarCollapsedPreference;
  document.body.classList.toggle('sidebar-collapsed', recolhido);
  sidebarElement?.setAttribute('aria-hidden', String(recolhido));
  if (sidebarElement) sidebarElement.inert = recolhido;
  const label = recolhido ? 'Mostrar menu lateral' : 'Ocultar menu lateral';
  sidebarCollapseToggle?.setAttribute('aria-expanded', String(!recolhido));
  sidebarCollapseToggle?.setAttribute('aria-label', label);
  sidebarCollapseToggle?.setAttribute('title', label);
}

atualizarSidebarRecolhido();
sidebarCollapseToggle?.addEventListener('click', () => {
  sidebarCollapsedPreference = !sidebarCollapsedPreference;
  try {
    localStorage.setItem(sidebarCollapseStorageKey, String(sidebarCollapsedPreference));
  } catch {}
  atualizarSidebarRecolhido();
});
desktopSidebarQuery.addEventListener('change', atualizarSidebarRecolhido);

function alternarGrupoMenu(botao) {
  const grupo = document.getElementById(botao.getAttribute('aria-controls'));
  if (!grupo) return;
  const expandido = botao.getAttribute('aria-expanded') === 'true';
  botao.setAttribute('aria-expanded', String(!expandido));
  grupo.hidden = expandido;
}

window.alternarGrupoMenu = alternarGrupoMenu;

window.addEventListener('resize', () => {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.overlay');
  atualizarSidebarRecolhido();
  if (!sidebar || window.innerWidth <= 768) return;
  sidebar.classList.remove('open');
  overlay?.classList.remove('show');
});

function go(page, sid) {
  if (page === 'perfis' && window.usuarioTipo === 'apoio') {
    if (students[0]) {
      go('aluno', students[0].id);
    } else {
      abrirPerfilApoioAoCarregar = true;
      carregarAlunos();
    }
    return;
  }
  if (page === 'cadastrar') {
    go('perfis');
    abrirModalCadastro();
    return;
  }

  document.querySelectorAll('.modal-bg.open').forEach(modal => modal.classList.remove('open'));
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => { n.classList.remove('active'); if(n.querySelector('.ni')) n.querySelector('.ni').style.color = ''; });
  document.getElementById('page-' + page).classList.add('active');
  const content = document.querySelector('.content');
  if (content) content.scrollTo({ top: 0, left: 0, behavior: 'auto' });
  window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
  const nav = document.getElementById('nav-' + page);
  const grupoMenu = nav?.closest('.sidebar-section-items');
  if (grupoMenu?.hidden) {
    const botaoGrupo = document.querySelector(`[aria-controls="${grupoMenu.id}"]`);
    if (botaoGrupo) alternarGrupoMenu(botaoGrupo);
  }
  if (nav) { nav.classList.add('active'); if(nav.querySelector('.ni')) nav.querySelector('.ni').style.color = 'var(--gold)'; }
  const [t,s] = pageMeta[page] || ['',''];
  const pageTitle = document.getElementById('pg-title');
  const pageSubtitle = document.getElementById('pg-sub');
  if (pageTitle) pageTitle.textContent = t;
  if (pageSubtitle) pageSubtitle.textContent = s;
  document.getElementById('notif-dd').style.display = 'none';
  document.getElementById('bell-btn').setAttribute('aria-expanded', 'false');
  if (window.innerWidth <= 768) fecharMenu();
  if (page === 'dashboard') refreshDash();
  if (page === 'perfis')    { filtered = [...students]; renderTable(filtered); if(document.getElementById('srch-inp')) document.getElementById('srch-inp').value=''; if(document.getElementById('fil-turma')) document.getElementById('fil-turma').value=''; if(document.getElementById('fil-diag')) document.getElementById('fil-diag').value=''; }
  if (page === 'agenda')    loadEventos();
  if (page === 'usuarios')  carregarUsuarios();
  if (page === 'quesitos')  carregarQuesitos();
  if (page === 'condicoes') carregarCondicoes();
  if (page === 'relatorios') carregarRelatoriosEvolucao();
  if (page === 'avaliacao') carregarPaginaAvaliacao();
  if (page === 'cadastrar') carregarOpcoesAluno();
  if (page === 'registro-apoio') renderAlunosRegistroApoio();
  if (page === 'aluno' && sid != null) openProfile(sid);
  if (page === 'paee' || page === 'pei') abrirModuloPlano(page, sid);
}

function escapeEvolucao(value) {
  return String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

function gerarMapaCoresQuesitos(quesitos) {
  const palette = [
    '#2f8f5b', '#d8634d', '#3a6fe0', '#a15fd9', '#c9761a', '#1f9d8f',
    '#d7a21a', '#d3395a', '#5a8f2a', '#4f7de8', '#b34ca2', '#f28c28'
  ];

  const map = new Map();
  quesitos.forEach(([id, titulo], index) => {
    const baseColor = palette[index % palette.length];
    const hueStep = 360 / Math.max(quesitos.length, 1);
    const color = quesitos.length <= palette.length
      ? baseColor
      : `hsl(${Math.round(hueStep * index)} 62% 48%)`;
    map.set(String(id), color);
  });
  return map;
}

function obterCorQuesito(quesitoId, titulo, mapaCores) {
  if (mapaCores && mapaCores.has(String(quesitoId))) {
    return mapaCores.get(String(quesitoId));
  }

  const nome = String(quesitoId ?? titulo ?? 'quesito')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();

  if (nome.includes('comunic')) return '#2f8f5b';
  if (nome.includes('sociab') || nome.includes('social')) return '#d8634d';

  const seed = String(quesitoId ?? titulo ?? 'quesito');
  let hash = 0;
  for (let i = 0; i < seed.length; i += 1) {
    hash = (hash << 5) - hash + seed.charCodeAt(i);
    hash |= 0;
  }
  const hue = Math.abs(hash) % 360;
  return `hsl(${hue} 62% 48%)`;
}

function carregarRelatoriosEvolucao() {
  const alunoSelect = document.getElementById('evolucao-aluno');
  if (!alunoSelect) return;
  fetch('controllers/listar_evolucao.php')
    .then(response => response.json())
    .then(data => {
      evolucaoRegistros = Array.isArray(data) ? data : [];
      const alunosVisiveis = Array.isArray(students) ? students : [];
      if (alunoSelect.tagName === 'SELECT') {
        alunoSelect.innerHTML = alunosVisiveis.length
          ? alunosVisiveis.map(student => `<option value="${student.id}">${escapeEvolucao(student.nome)}</option>`).join('')
          : '<option value="">Nenhum aluno disponível</option>';
      } else {
        alunoSelect.value = alunosVisiveis[0]?.id || '';
        const nomeFiltro = document.getElementById('evolucao-aluno-nome-filtro');
        if (nomeFiltro) nomeFiltro.textContent = alunosVisiveis[0]?.nome || 'Nenhum aluno vinculado';
      }
      atualizarQuesitosEvolucao();
      renderRelatorioEvolucao();
    })
    .catch(() => {
      evolucaoRegistros = [];
      alunoSelect.innerHTML = '<option value="">Não foi possível carregar</option>';
      renderRelatorioEvolucao();
    });
}

function atualizarQuesitosEvolucao() {
  const alunoId = document.getElementById('evolucao-aluno')?.value;
  const quesitoSelect = document.getElementById('evolucao-quesito');
  if (!quesitoSelect) return;
  const quesitos = [...new Map(evolucaoRegistros.filter(item => String(item.aluno_id) === String(alunoId)).map(item => [item.quesito_id, item.quesito])).entries()];
  quesitoSelect.innerHTML = '<option value="__todos">Todos os quesitos</option>' + quesitos.map(([id, titulo]) => `<option value="${id}">${escapeEvolucao(titulo)}</option>`).join('');
}

function renderRelatorioEvolucao() {
  evolucaoPagina = 1;
  const alunoId = document.getElementById('evolucao-aluno')?.value;
  const quesitoId = document.getElementById('evolucao-quesito')?.value || '__todos';
  const periodo = document.getElementById('evolucao-periodo')?.value || '__todos';
  const inicio = document.getElementById('evolucao-inicio')?.value || '';
  const fim = document.getElementById('evolucao-fim')?.value || '';
  const aluno = students.find(student => String(student.id) === String(alunoId));
  const registros = evolucaoRegistros.filter(item => String(item.aluno_id) === String(alunoId) && (quesitoId === '__todos' || String(item.quesito_id) === String(quesitoId)) && (periodo === '__todos' || String(item.periodo) === periodo) && (!inicio || item.data >= inicio) && (!fim || item.data <= fim));
  const nomeEl = document.getElementById('evolucao-aluno-nome');
  const metaEl = document.getElementById('evolucao-aluno-meta');
  if (nomeEl) nomeEl.textContent = aluno?.nome || 'Selecione um aluno';
  if (metaEl) metaEl.textContent = aluno ? `Matrícula ${aluno.matricula || '—'} · Turma ${aluno.turma || '—'} · ${aluno.curso || '—'}` : '';
  const resumo = document.getElementById('evolucao-resumo');
  if (resumo) resumo.textContent = `${registros.length} registro${registros.length === 1 ? '' : 's'}`;
  desenharGraficoEvolucao(registros);
  renderTabelaEvolucao(registros);
}

function desenharGraficoEvolucao(registros) {
  const svg = document.getElementById('evolucao-grafico');
  const legenda = document.getElementById('evolucao-legenda');
  const tendenciaEl = document.getElementById('evolucao-tendencia');
  if (!svg || !legenda || !tendenciaEl) return;
  const niveis = { triste: 0, neutro: 1, feliz: 2 };
  const rotulos = { triste: 'Ruim', neutro: 'Neutro', feliz: 'Bom' };
  const quesitos = [...new Map(registros.map(item => [String(item.quesito_id), item.quesito])).entries()]
    .sort(([idA, tituloA], [idB, tituloB]) => String(tituloA).localeCompare(String(tituloB), 'pt-BR') || idA.localeCompare(idB));
  const quesitosParaCores = [...new Map(evolucaoRegistros.map(item => [String(item.quesito_id), item.quesito])).entries()]
    .sort(([idA, tituloA], [idB, tituloB]) => String(tituloA).localeCompare(String(tituloB), 'pt-BR') || idA.localeCompare(idB));
  const mapaCores = gerarMapaCoresQuesitos(quesitosParaCores);
  legenda.innerHTML = quesitos.map(([id, titulo]) => `<div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;font-size:12px;line-height:1.2"><span style="display:inline-block;width:26px;height:3px;border-radius:2px;background:${obterCorQuesito(id, titulo, mapaCores)}; margin-left:4px;"></span><span style="padding-left:2px">${escapeEvolucao(titulo)}</span></div>`).join('') || '<span style="font-size:11px;color:var(--text-muted)">Sem dados para o filtro.</span>';
  if (!registros.length) {
    svg.innerHTML = '<text x="350" y="140" text-anchor="middle" fill="#7b8b81" font-size="13">Sem dados para o filtro selecionado</text>';
    tendenciaEl.textContent = 'Nenhuma tendência disponível.';
    return;
  }
  const W = 760, H = 320, left = 64, right = 18, top = 20, bottom = 46, plotW = W - left - right, plotH = H - top - bottom;
  const chaveEvento = item => `${item.data}|${item.data_hora_registro || ''}|${item.periodo}`;
  const eventos = [...new Map(registros.map(item => [chaveEvento(item), { data: item.data, hora: item.data_hora_registro, periodo: item.periodo, chave: chaveEvento(item) }])).values()]
    .sort((a, b) => String(a.data).localeCompare(String(b.data)) || String(a.hora).localeCompare(String(b.hora)) || String(a.periodo).localeCompare(String(b.periodo)));
  const posicaoEvento = index => {
    if (eventos.length === 1) return left;
    return left + (index / (eventos.length - 1)) * plotW;
  };
  const quesitosPorEventoNivel = new Map();
  registros.forEach(item => {
    const chave = `${chaveEvento(item)}|${niveis[item.avaliacao]}`;
    if (!quesitosPorEventoNivel.has(chave)) quesitosPorEventoNivel.set(chave, new Set());
    quesitosPorEventoNivel.get(chave).add(String(item.quesito_id));
  });
  const visualY = item => {
    const originalY = niveis[item.avaliacao];
    const chave = `${chaveEvento(item)}|${originalY}`;
    const ids = [...(quesitosPorEventoNivel.get(chave) || new Set())]
      .sort((idA, idB) => quesitos.findIndex(([id]) => id === idA) - quesitos.findIndex(([id]) => id === idB) || idA.localeCompare(idB));
    if (ids.length <= 1) return originalY;
    const indice = ids.indexOf(String(item.quesito_id));
    const offsetStep = Math.min(0.1, 0.4 / (ids.length - 1));
    return originalY + (indice - (ids.length - 1) / 2) * offsetStep;
  };
  const horaFormatada = valor => {
    const match = String(valor || '').match(/(?:\d{4}-\d{2}-\d{2}[ T])?(\d{2}:\d{2})/);
    return match ? match[1] : '--:--';
  };
  const periodoFormatado = periodo => periodo === 'tarde' ? 'Tarde' : 'Manhã';
  const y = level => top + plotH - level / 3 * plotH;
  let parts = '';
  [3, 2, 1, 0].forEach(level => { parts += `<line x1="${left}" y1="${y(level)}" x2="${W - right}" y2="${y(level)}" stroke="#dfe6df" stroke-width="1"/>`; });
  [['Bom', 2.5], ['Neutro', 1.5], ['Ruim', 0.5]].forEach(([label, level]) => { parts += `<text x="${left - 18}" y="${y(level) + 4}" text-anchor="end" fill="#718077" font-size="11">${label}</text>`; });
  eventos.forEach((evento, index) => { const posicao = posicaoEvento(index); const dataLabel = `${evento.data.slice(8, 10)}/${evento.data.slice(5, 7)}`; const ancoragem = index === 0 ? 'start' : index === eventos.length - 1 ? 'end' : 'middle'; const tamanhoFonte = eventos.length > 8 ? 8 : 9; parts += `<line x1="${posicao}" y1="${H - bottom + 2}" x2="${posicao}" y2="${H - bottom + 7}" stroke="#cbd8ce" stroke-width="1"/><text x="${posicao}" y="${H - 22}" text-anchor="${ancoragem}" fill="#718077" font-size="${tamanhoFonte}"><tspan x="${posicao}" dy="0">${dataLabel} ${horaFormatada(evento.hora)}</tspan><tspan x="${posicao}" dy="11">${periodoFormatado(evento.periodo)}</tspan></text>`; });
  quesitos.forEach(([id, titulo]) => {
    const cor = obterCorQuesito(id, titulo, mapaCores);
    const serie = registros.filter(item => String(item.quesito_id) === String(id))
      .sort((a, b) => eventos.findIndex(evento => evento.chave === chaveEvento(a)) - eventos.findIndex(evento => evento.chave === chaveEvento(b)));
    const pontos = serie.map(item => {
      const evento = eventos.findIndex(itemEvento => itemEvento.chave === chaveEvento(item));
      return `${posicaoEvento(evento)},${y(visualY(item) + 0.5)}`;
    });
    if (pontos.length === 1) {
      const [pontoX, pontoY] = pontos[0].split(',').map(Number);
      parts += `<line x1="${pontoX - 8}" y1="${pontoY}" x2="${pontoX + 8}" y2="${pontoY}" stroke="${cor}" stroke-width="3" stroke-linecap="round"/>`;
    } else {
      parts += `<polyline points="${pontos.join(' ')}" fill="none" stroke="${cor}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>`;
    }
  });
  svg.innerHTML = parts;
  const linhas = quesitos.map(([id, titulo]) => { const serie = registros.filter(item => String(item.quesito_id) === String(id)); const diff = niveis[serie[serie.length - 1].avaliacao] - niveis[serie[0].avaliacao]; return `${escapeEvolucao(titulo)}: ${diff > 0 ? 'melhora' : diff < 0 ? 'piora' : 'estável'}`; });
  tendenciaEl.innerHTML = `<strong>Tendência geral:</strong><br>${linhas.join('<br>')}`;
}

function renderTabelaEvolucao(registros) {
  const tbody = document.getElementById('evolucao-tabela');
  if (!tbody) return;
  const rotulos = { triste: 'Ruim', neutro: 'Neutro', feliz: 'Bom' };
  const registrosOrdenados = [...registros].reverse();
  const totalPaginas = Math.max(1, Math.ceil(registrosOrdenados.length / evolucaoItensPorPagina));
  evolucaoPagina = Math.min(Math.max(1, evolucaoPagina), totalPaginas);
  const inicio = (evolucaoPagina - 1) * evolucaoItensPorPagina;
  const registrosPagina = registrosOrdenados.slice(inicio, inicio + evolucaoItensPorPagina);
  tbody.innerHTML = registrosPagina.length ? registrosPagina.map(item => `<tr><td style="padding:10px;border-bottom:1px solid var(--border)">${item.data.split('-').reverse().join('/')}</td><td style="padding:10px;border-bottom:1px solid var(--border)">${escapeEvolucao(item.periodo)}</td><td style="padding:10px;border-bottom:1px solid var(--border)">${escapeEvolucao(item.quesito)}</td><td style="padding:10px;border-bottom:1px solid var(--border)"><span class="tag ${item.avaliacao === 'feliz' ? 't-ok' : item.avaliacao === 'neutro' ? 't-warn' : 't-danger'}">${rotulos[item.avaliacao] || item.avaliacao}</span></td><td style="padding:10px;border-bottom:1px solid var(--border);color:var(--text-muted)">${escapeEvolucao(item.observacao || '—')}</td></tr>`).join('') : '<tr><td colspan="5" style="padding:22px;text-align:center;color:var(--text-muted)">Nenhum registro encontrado para este filtro.</td></tr>';
  const paginacao = document.getElementById('evolucao-paginacao');
  if (paginacao) {
    paginacao.innerHTML = registrosOrdenados.length > evolucaoItensPorPagina ? `<button type="button" class="btn-g" ${evolucaoPagina === 1 ? 'disabled' : ''} onclick="mudarPaginaEvolucao(-1)"><i class="bi bi-chevron-left"></i> Anterior</button><span style="font-size:12px;color:var(--text-muted)">Página ${evolucaoPagina} de ${totalPaginas}</span><button type="button" class="btn-g" ${evolucaoPagina === totalPaginas ? 'disabled' : ''} onclick="mudarPaginaEvolucao(1)">Próxima <i class="bi bi-chevron-right"></i></button>` : '';
  }
}

function mudarPaginaEvolucao(direcao) {
  evolucaoPagina += direcao;
  const alunoId = document.getElementById('evolucao-aluno')?.value;
  const quesitoId = document.getElementById('evolucao-quesito')?.value || '__todos';
  const periodo = document.getElementById('evolucao-periodo')?.value || '__todos';
  const inicio = document.getElementById('evolucao-inicio')?.value || '';
  const fim = document.getElementById('evolucao-fim')?.value || '';
  const registros = evolucaoRegistros.filter(item => String(item.aluno_id) === String(alunoId) && (quesitoId === '__todos' || String(item.quesito_id) === String(quesitoId)) && (periodo === '__todos' || String(item.periodo) === periodo) && (!inicio || item.data >= inicio) && (!fim || item.data <= fim));
  renderTabelaEvolucao(registros);
}

function gerarRelatorioEvolucao() {
  const alunoId = document.getElementById('evolucao-aluno')?.value;
  const aluno = students.find(student => String(student.id) === String(alunoId));
  if (!aluno) {
    toast('Selecione um aluno para gerar o relatório.', 'bi-exclamation-triangle', 'amber');
    return;
  }

  const quesitoId = document.getElementById('evolucao-quesito')?.value || '__todos';
  const periodoSelecionado = document.getElementById('evolucao-periodo')?.value || '__todos';
  const inicio = document.getElementById('evolucao-inicio')?.value || '';
  const fim = document.getElementById('evolucao-fim')?.value || '';
  const registros = evolucaoRegistros.filter(item => String(item.aluno_id) === String(alunoId)
    && (quesitoId === '__todos' || String(item.quesito_id) === String(quesitoId))
    && (periodoSelecionado === '__todos' || String(item.periodo) === periodoSelecionado)
    && (!inicio || item.data >= inicio) && (!fim || item.data <= fim));
  const quesito = document.getElementById('evolucao-quesito')?.selectedOptions[0]?.textContent || 'Todos os quesitos';
  const periodoTexto = periodoSelecionado === '__todos' ? 'Todos os períodos' : periodoSelecionado === 'manha' ? 'Manhã' : 'Tarde';
  const periodo = `${periodoTexto} | ${inicio || fim ? `${formatDateBR(inicio)} a ${formatDateBR(fim)}` : 'Todos os registros disponíveis'}`;
  const tabela = registros.length
    ? [...registros].sort((a, b) => String(b.data).localeCompare(String(a.data))).map(item => `<tr><td>${formatDateBR(item.data)}</td><td>${escapeHtmlRelatorio(item.periodo)}</td><td>${escapeHtmlRelatorio(item.quesito)}</td><td><span class="status status-${item.avaliacao}">${escapeHtmlRelatorio({feliz:'Bom', neutro:'Neutro', triste:'Ruim'}[item.avaliacao] || item.avaliacao)}</span></td><td>${escapeHtmlRelatorio(item.observacao || '—')}</td></tr>`).join('')
    : '<tr><td colspan="5" class="vazio">Nenhum registro encontrado para os filtros selecionados.</td></tr>';
  const grafico = document.getElementById('evolucao-grafico')?.outerHTML || '';
  const tendencia = document.getElementById('evolucao-tendencia')?.textContent || 'Nenhuma tendência disponível.';
  const html = `<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Relatório de evolução - ${escapeHtmlRelatorio(aluno.nome)}</title><style>
    @page{size:A4;margin:16mm}*{box-sizing:border-box}body{font-family:Arial,sans-serif;color:#203326;margin:0;font-size:11px;line-height:1.5}header{border-bottom:4px solid #ffb000;padding:0 0 16px;margin-bottom:22px;display:flex;justify-content:space-between;gap:20px;align-items:flex-start}h1{font-size:23px;color:#075b2d;margin:0 0 4px}h2{font-size:13px;color:#075b2d;margin:24px 0 8px;border-bottom:1px solid #d8e7d5;padding-bottom:6px;text-transform:uppercase;letter-spacing:.04em}.marca{font-size:10px;color:#718074;text-align:right}.identificacao{display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;background:#f1f8ef;border:1px solid #d7e8d2;border-radius:8px;padding:14px}.campo b{display:block;color:#718074;font-size:9px;text-transform:uppercase;letter-spacing:.06em}.campo span{font-weight:bold;font-size:12px}.resumo{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.indicador{border-left:3px solid #2f8f5b;background:#f8fbf7;padding:10px}.indicador b{display:block;font-size:18px;color:#075b2d}.indicador span{color:#718074;font-size:10px}.grafico{border:1px solid #d8e7d5;border-radius:8px;padding:8px;margin-top:10px}.grafico svg{width:100%;height:auto;max-height:270px}table{width:100%;border-collapse:collapse;font-size:10px}th{text-align:left;background:#075b2d;color:white;padding:8px}td{padding:8px;border-bottom:1px solid #e3ece1;vertical-align:top}tr:nth-child(even){background:#f7faf6}.status{font-weight:bold}.status-feliz{color:#18794e}.status-neutro{color:#a66d00}.status-triste{color:#b42318}.vazio{text-align:center;color:#718074;padding:20px}footer{margin-top:30px;border-top:1px solid #d8e7d5;padding-top:12px;color:#718074;font-size:10px;display:flex;justify-content:space-between}@media print{.grafico{break-inside:avoid}table{break-inside:auto}tr{break-inside:avoid;break-after:auto}}
  </style></head><body><header><div><h1>Relatório de evolução</h1><div>Acompanhamento pedagógico por registros de quesitos</div></div><div class="marca">Sistema PCD<br>Emitido em ${formatDateBR(new Date().toISOString().slice(0,10))}</div></header>
    <section class="identificacao"><div class="campo"><b>Aluno</b><span>${escapeHtmlRelatorio(aluno.nome)}</span></div><div class="campo"><b>Matrícula</b><span>${escapeHtmlRelatorio(aluno.matricula || '—')}</span></div><div class="campo"><b>Turma</b><span>${escapeHtmlRelatorio(aluno.turma || '—')}</span></div><div class="campo"><b>Curso</b><span>${escapeHtmlRelatorio(aluno.curso || '—')}</span></div><div class="campo"><b>Quesito</b><span>${escapeHtmlRelatorio(quesito)}</span></div><div class="campo"><b>Período</b><span>${escapeHtmlRelatorio(periodo)}</span></div></section>
    <h2>Resumo do acompanhamento</h2><div class="resumo"><div class="indicador"><b>${registros.length}</b><span>registros analisados</span></div><div class="indicador"><b>${new Set(registros.map(item => item.data)).size}</b><span>datas de acompanhamento</span></div><div class="indicador"><b>${new Set(registros.map(item => item.quesito_id)).size}</b><span>quesitos avaliados</span></div></div>
    <h2>Evolução observada</h2><div class="grafico">${grafico}</div><p><strong>Síntese:</strong> ${escapeHtmlRelatorio(tendencia)}</p>
    <h2>Registros detalhados</h2><table><thead><tr><th>Data</th><th>Período</th><th>Quesito</th><th>Avaliação</th><th>Observação</th></tr></thead><tbody>${tabela}</tbody></table>
    <footer><span>Documento gerado pelo Sistema PCD</span><span>Assinatura: ______________________________</span></footer></body></html>`;
  const janela = window.open('', '_blank', 'width=960,height=760');
  if (!janela) { toast('Permita pop-ups para gerar o relatório.', 'bi-x-circle', 'danger'); return; }
  janela.document.write(html); janela.document.close(); janela.focus();
  janela.onload = () => { janela.print(); };
  toast('Relatório preparado para impressão ou PDF.', 'bi-file-earmark-pdf', 'success');
}

function escapeHtmlRelatorio(value) {
  return String(value ?? '—').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

function dataLocalAvaliacao() {
  const agora = new Date();
  return `${agora.getFullYear()}-${String(agora.getMonth() + 1).padStart(2, '0')}-${String(agora.getDate()).padStart(2, '0')}`;
}

function obterAvaliacao(data, periodo) {
  return avaliacaoDados.avaliacoes.find(item => item.data === data && item.periodo === periodo) || null;
}

function podeEditarAvaliacao(data, periodo = '') {
  const hoje = dataLocalAvaliacao();
  const ontem = new Date(`${hoje}T12:00:00`);
  ontem.setDate(ontem.getDate() - 1);
  const dataLimite = ontem.toISOString().slice(0, 10);
  if (data < dataLimite || data > hoje) return false;
  if (data !== hoje) return true;
  const horaAtual = new Date().getHours();
  if (periodo === 'manha') return horaAtual >= 7 && horaAtual < 12;
  if (periodo === 'tarde') return horaAtual >= 12 && horaAtual < 17;
  return horaAtual >= 7 && horaAtual < 17;
}

function rotuloPeriodoAvaliacao(periodo) {
  return periodo === 'manha' ? 'Manhã' : 'Tarde';
}

function horarioPermitidoAvaliacao(periodo) {
  return periodo === 'manha' ? '07:00 as 12:00' : '12:00 as 17:00';
}

function avisarAvaliacaoIndisponivel(periodo) {
  toast(`A avaliação da ${rotuloPeriodoAvaliacao(periodo)} está disponível das ${horarioPermitidoAvaliacao(periodo)}.`, 'bi-clock', 'amber');
}

function montarCardAvaliacao(data, periodo, historico = false) {
  const registro = obterAvaliacao(data, periodo);
  const editavel = podeEditarAvaliacao(data, periodo);
  const dataAtual = dataLocalAvaliacao();
  const ontem = new Date(`${dataAtual}T12:00:00`);
  ontem.setDate(ontem.getDate() - 1);
  const dataAnterior = ontem.toISOString().slice(0, 10);
  const podeAvaliarHistorico = historico && !registro && data === dataAnterior && editavel;
  const titulo = rotuloPeriodoAvaliacao(periodo);
  const icone = periodo === 'manha' ? 'bi-sun-fill' : 'bi-sunset-fill';
  const bloqueado = Boolean(registro && !editavel);
  const classe = registro ? (bloqueado ? 'avaliacao-dia-card avaliacao-dia-card--bloqueado' : 'avaliacao-dia-card avaliacao-dia-card--avaliado') : 'avaliacao-dia-card avaliacao-dia-card--pendente';
  const status = registro
    ? `<span class="avaliacao-status ${bloqueado ? 'avaliacao-status--bloqueado' : 'avaliacao-status--ok'}"><i class="bi ${bloqueado ? 'bi-eye' : 'bi-check-circle-fill'}"></i>${bloqueado ? 'Avaliado · somente leitura' : 'Avaliado'}</span>`
    : '<span class="avaliacao-status"><i class="bi bi-dash-circle"></i>Não avaliado</span>';
  const acao = registro ? (bloqueado ? 'Visualizar' : 'Editar') : (editavel ? 'Avaliar' : 'Indisponível');
  const acaoIcone = registro ? (bloqueado ? 'bi-eye' : 'bi-pencil-square') : (editavel ? 'bi-clipboard2-check' : 'bi-lock');
  const podeExcluir = Boolean(registro && !historico && data === dataAtual && !bloqueado);
  let botao = '';
  if (registro) {
    const botaoExcluir = podeExcluir ? `<button type="button" class="btn-g avaliacao-excluir-btn" title="Excluir avaliação do dia" aria-label="Excluir avaliação de ${rotuloPeriodoAvaliacao(periodo)}" onclick="excluirAvaliacaoAtual(${registro.id}, '${periodo}')"><i class="bi bi-trash3"></i></button>` : '';
    botao = `${botaoExcluir}<div class="avaliacao-acoes"><button type="button" class="avaliacao-acao-principal ${bloqueado ? 'btn-g' : 'btn-g avaliacao-editar-btn'}" onclick="abrirAvaliacao('${data}','${periodo}', '${bloqueado ? 'visualizar' : 'editar'}')"><i class="bi ${acaoIcone}"></i> ${acao}</button></div>`;
  } else if (!historico || podeAvaliarHistorico) {
    botao = editavel
      ? `<button type="button" class="btn-p avaliacao-acao-principal" onclick="abrirAvaliacao('${data}','${periodo}', 'avaliar')"><i class="bi bi-clipboard2-check"></i> Avaliar</button>`
      : `<button type="button" class="btn-p avaliacao-acao-principal avaliacao-acao-indisponivel" aria-disabled="true" onclick="avisarAvaliacaoIndisponivel('${periodo}')" title="Avaliação disponível das ${horarioPermitidoAvaliacao(periodo)}"><i class="bi bi-lock"></i> Indisponível</button>`;
  }
  const cliqueHistorico = historico && registro ? ` onclick="if (!event.target.closest('button')) abrirAvaliacao('${data}', '${periodo}', 'visualizar')"` : '';
  return `<article class="${classe}${historico ? ' avaliacao-historico-card' : ''}"${cliqueHistorico} style="border-left-color:${registro ? (bloqueado ? '#6b7280' : '#16a34a') : '#f4b400'};">
    <div class="avaliacao-card-cabecalho">
      <div class="avaliacao-card-identidade">
        <span class="avaliacao-periodo-icone"><i class="bi ${icone}"></i></span>
        <div class="avaliacao-card-periodo">
          <div class="avaliacao-card-titulo">${titulo}</div>
          <div class="avaliacao-card-data"><i class="bi bi-clock"></i>${horarioPermitidoAvaliacao(periodo)}</div>
        </div>
      </div>
    </div>
    <div class="avaliacao-card-rodape">${status}${botao}</div>
  </article>`;
}

function inicializarCalendarioAvaliacao() {
  const picker = document.getElementById('avaliacao-data-picker');
  const trigger = document.getElementById('avaliacao-historico-data-filtro');
  const popup = document.getElementById('avaliacao-data-picker-popup');
  const input = document.getElementById('avaliacao-historico-data-valor');
  if (!picker || !trigger || !popup || !input || picker.dataset.inicializado === 'true') return;
  picker.dataset.inicializado = 'true';

  const atualizarTextoBotao = () => {
    const label = trigger.querySelector('span');
    if (label) label.textContent = input.value ? formatDateBR(input.value) : 'Selecionar data';
  };

  const renderizarPopup = (dataBase = input.value || dataLocalAvaliacao()) => {
    const [anoBase, mesBase, diaBase] = dataBase.split('-').map(Number);
    const dataReferencia = new Date(anoBase || new Date().getFullYear(), (mesBase || 1) - 1, diaBase || 1);
    const mesAtual = new Date(dataReferencia.getFullYear(), dataReferencia.getMonth(), 1);
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);
    const primeiroDia = new Date(mesAtual.getFullYear(), mesAtual.getMonth(), 1);
    const inicio = (primeiroDia.getDay() + 6) % 7;
    const totalDias = new Date(mesAtual.getFullYear(), mesAtual.getMonth() + 1, 0).getDate();
    const dias = [];
    for (let indice = 0; indice < inicio; indice += 1) dias.push('<span class="avaliacao-data-picker-dia is-outside"></span>');
    for (let dia = 1; dia <= totalDias; dia += 1) {
      const data = `${mesAtual.getFullYear()}-${String(mesAtual.getMonth() + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
      const classes = ['avaliacao-data-picker-dia'];
      if (data === input.value) classes.push('is-selected');
      if (data === dataLocalAvaliacao()) classes.push('is-today');
      if (new Date(`${data}T12:00:00`).getTime() > hoje.getTime()) classes.push('is-outside');
      dias.push(`<button type="button" class="${classes.join(' ')}" data-avaliacao-selecionar="${data}" ${new Date(`${data}T12:00:00`).getTime() > hoje.getTime() ? 'disabled' : ''}>${dia}</button>`);
    }
    popup.innerHTML = `
      <div class="avaliacao-data-picker-cabecalho">
        <button type="button" class="btn-g avaliacao-data-picker-nav" data-avaliacao-mes="-1" aria-label="Mês anterior"><i class="bi bi-chevron-left"></i></button>
        <span class="avaliacao-data-picker-mes">${mesAtual.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })}</span>
        <button type="button" class="btn-g avaliacao-data-picker-nav" data-avaliacao-mes="1" aria-label="Próximo mês"><i class="bi bi-chevron-right"></i></button>
      </div>
      <div class="avaliacao-data-picker-semana">
        ${['Seg','Ter','Qua','Qui','Sex','Sáb','Dom'].map(dia => `<span>${dia}</span>`).join('')}
      </div>
      <div class="avaliacao-data-picker-dias">${dias.join('')}</div>
      <button type="button" class="avaliacao-data-picker-limpar" data-avaliacao-limpar>Limpar filtro</button>
    `;
    popup.querySelectorAll('[data-avaliacao-selecionar]').forEach(botao => {
      botao.addEventListener('click', (event) => {
        const dataSelecionada = event.currentTarget.dataset.avaliacaoSelecionar;
        input.value = dataSelecionada;
        atualizarTextoBotao();
        popup.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        renderizarHistoricoAvaliacao();
      });
    });
    popup.querySelectorAll('[data-avaliacao-mes]').forEach(botao => {
      botao.addEventListener('click', () => {
        const mesMovimento = Number(botao.dataset.avaliacaoMes || 0);
        const mesAtualPopup = new Date(mesAtual.getFullYear(), mesAtual.getMonth() + mesMovimento, 1);
        renderizarPopup(`${mesAtualPopup.getFullYear()}-${String(mesAtualPopup.getMonth() + 1).padStart(2, '0')}-01`);
      });
    });
    popup.querySelectorAll('[data-avaliacao-limpar]').forEach(botao => {
      botao.addEventListener('click', () => {
        input.value = '';
        atualizarTextoBotao();
        popup.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        renderizarHistoricoAvaliacao();
      });
    });
  };

  trigger.addEventListener('click', (event) => {
    event.stopPropagation();
    const aberto = popup.classList.contains('is-open');
    if (aberto) {
      popup.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
      return;
    }
    renderizarPopup(input.value || dataLocalAvaliacao());
    popup.classList.add('is-open');
    trigger.setAttribute('aria-expanded', 'true');
  });

  document.addEventListener('click', (event) => {
    if (!picker.contains(event.target)) {
      popup.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
    }
  });

  atualizarTextoBotao();
}

function limparFiltroHistoricoAvaliacao() {
  const input = document.getElementById('avaliacao-historico-data-valor');
  const trigger = document.getElementById('avaliacao-historico-data-filtro');
  const popup = document.getElementById('avaliacao-data-picker-popup');
  if (input) input.value = '';
  if (trigger) {
    const label = trigger.querySelector('span');
    if (label) label.textContent = 'Selecionar data';
    trigger.setAttribute('aria-expanded', 'false');
  }
  if (popup) popup.classList.remove('is-open');
  renderizarHistoricoAvaliacao();
}

function renderizarHistoricoAvaliacao() {
  const container = document.getElementById('avaliacao-historico');
  if (!container) return;

  const input = document.getElementById('avaliacao-historico-data-valor');
  const registros = Array.isArray(avaliacaoDados?.avaliacoes) ? avaliacaoDados.avaliacoes : [];
  const filtrados = input && input.value ? registros.filter(item => String(item.data) === String(input.value)) : registros;

  const button = document.getElementById('avaliacao-historico-data-filtro');
  if (button) {
    const label = button.querySelector('span');
    if (label) label.textContent = input && input.value ? formatDateBR(input.value) : 'Selecionar data';
  }

  if (!filtrados.length) {
    container.innerHTML = '<div class="empty" style="padding:30px 0"><i class="bi bi-calendar2-x"></i><span style="font-size:13px">Nenhuma avaliação registrada</span></div>';
    return;
  }

  const datas = [...new Set(filtrados.map(item => item.data))].sort((a, b) => String(b).localeCompare(String(a)));
  container.innerHTML = datas.map(data => {
    const periodos = ['manha', 'tarde'].map(periodo => montarCardAvaliacao(data, periodo, true)).join('');
    return `<div class="avaliacao-historico-dia"><div class="avaliacao-historico-data">${formatDateBR(data)}</div><div class="avaliacao-historico-periodos">${periodos}</div></div>`;
  }).join('');
}

function renderizarPaginaAvaliacao() {
  const dia = document.getElementById('avaliacao-dia');
  const historico = document.getElementById('avaliacao-historico');
  inicializarCalendarioAvaliacao();
  const dataAtual = dataLocalAvaliacao();
  const aluno = avaliacaoDados.aluno;
  const avatar = document.getElementById('avaliacao-aluno-avatar');
  const nome = document.getElementById('avaliacao-aluno-nome');
  const condicao = document.getElementById('avaliacao-aluno-condicao');
  if (avatar) avatar.innerHTML = aluno ? renderAvatarAluno(aluno) : '?';
  if (nome) nome.textContent = aluno?.nome || 'Nenhum aluno';
  if (condicao) condicao.innerHTML = aluno ? renderCondicoes(aluno.diagnostico) : '<span class="tag">Aluno</span>';
  ['matricula', 'turma', 'curso', 'sexo'].forEach(campo => {
    const elemento = document.getElementById(`avaliacao-aluno-${campo}`);
    if (elemento) elemento.textContent = aluno?.[campo] || '—';
  });
  const nascimento = document.getElementById('avaliacao-aluno-nascimento');
  if (nascimento) nascimento.textContent = aluno?.data_nascimento ? formatDateBR(aluno.data_nascimento) : '—';
  const apoio = document.getElementById('avaliacao-aluno-apoio');
  if (apoio) apoio.textContent = aluno?.apoio_nome || '—';
  const dataEl = document.getElementById('avaliacao-data-atual');
  if (dataEl) dataEl.textContent = formatDateBR(dataAtual);
  if (!aluno) {
    if (dia) dia.innerHTML = '<div class="empty" style="grid-column:1/-1;padding:34px 0"><i class="bi bi-person-x"></i><span style="font-size:13px">Nenhum aluno vinculado ao seu perfil.</span></div>';
    if (historico) historico.innerHTML = '';
    return;
  }
  if (!avaliacaoDados.quesitos.length) {
    if (dia) dia.innerHTML = '<div class="empty" style="grid-column:1/-1;padding:34px 0"><i class="bi bi-ui-checks"></i><span style="font-size:13px">Nenhum quesito associado a este aluno.</span></div>';
    if (historico) historico.innerHTML = '';
    return;
  }
  if (dia) dia.innerHTML = ['manha', 'tarde'].map(periodo => montarCardAvaliacao(dataAtual, periodo)).join('');
  if (historico) renderizarHistoricoAvaliacao();
}

window.inicializarCalendarioAvaliacao = inicializarCalendarioAvaliacao;
window.limparFiltroHistoricoAvaliacao = limparFiltroHistoricoAvaliacao;
window.renderizarHistoricoAvaliacao = renderizarHistoricoAvaliacao;
window.renderizarPaginaAvaliacao = renderizarPaginaAvaliacao;
window.carregarPaginaAvaliacao = carregarPaginaAvaliacao;
window.abrirAvaliacao = abrirAvaliacao;
window.fecharModalAvaliacao = fecharModalAvaliacao;
window.salvarAvaliacaoAtual = salvarAvaliacaoAtual;
window.excluirAvaliacaoAtual = excluirAvaliacaoAtual;

function carregarPaginaAvaliacao() {
  const alvo = document.getElementById('avaliacao-dia');
  if (!alvo || window.usuarioTipo !== 'apoio') return;
  alvo.innerHTML = '<div class="empty" style="grid-column:1/-1;padding:34px 0"><i class="bi bi-hourglass-split"></i><span style="font-size:13px">Carregando avaliações...</span></div>';
  fetch('controllers/avaliacao.php')
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => { if (!ok || !data.success) throw new Error(data.message || 'Não foi possível carregar as avaliações.'); avaliacaoDados = data; renderizarPaginaAvaliacao(); })
    .catch(error => { alvo.innerHTML = `<div class="empty" style="grid-column:1/-1;padding:34px 0"><i class="bi bi-exclamation-circle"></i><span style="font-size:13px">${escaparUsuario(error.message)}</span></div>`; });
}

function abrirAvaliacao(data, periodo, modo) {
  const registro = obterAvaliacao(data, periodo);
  if (modo === 'editar' && !podeEditarAvaliacao(data, periodo)) modo = 'visualizar';
  const titulo = document.getElementById('avaliacao-modal-titulo');
  const meta = document.getElementById('avaliacao-modal-meta');
  const quesitos = document.getElementById('avaliacao-modal-quesitos');
  const observacao = document.getElementById('avaliacao-modal-observacao');
  const salvar = document.getElementById('avaliacao-modal-salvar');
  if (!titulo || !meta || !quesitos || !observacao || !salvar) return;
  avaliacaoModalAtual = { data, periodo, modo };
  const somenteLeitura = modo === 'visualizar';
  titulo.textContent = `${somenteLeitura ? 'Visualização' : registro ? 'Editar' : 'Avaliação'} - ${rotuloPeriodoAvaliacao(periodo)}`;
  meta.textContent = `${avaliacaoDados.aluno?.nome || 'Aluno'} · ${formatDateBR(data)} · ${rotuloPeriodoAvaliacao(periodo)}`;
  quesitos.innerHTML = avaliacaoDados.quesitos.map(item => {
    const resposta = registro?.respostas?.[String(item.id)]?.avaliacao || '';
    const nome = `avaliacao-quesito-${item.id}`;
    return `<fieldset style="border:0;padding:0;margin:0 0 16px"><legend style="font-size:13px;font-weight:800;margin-bottom:4px">${escaparUsuario(item.titulo)}</legend><div style="font-size:11px;color:var(--text-muted);margin-bottom:9px">${escaparUsuario(item.descricao || '')}</div><div class="avaliacao-opcoes">${[['feliz','😊','Feliz'],['neutro','😐','Neutro'],['triste','😔','Triste']].map(([valor, emoji, rotulo]) => `<div class="avaliacao-opcao"><input type="radio" id="${nome}-${valor}" name="${nome}" value="${valor}" ${resposta === valor ? 'checked' : ''} ${somenteLeitura ? 'disabled' : ''}><label for="${nome}-${valor}"><span class="avaliacao-opcao-emoji">${emoji}</span><span>${rotulo}</span></label></div>`).join('')}</div></fieldset>`;
  }).join('');
  observacao.value = registro?.observacao_geral || '';
  observacao.disabled = somenteLeitura;
  salvar.style.display = somenteLeitura ? 'none' : 'inline-flex';
  const cancelar = salvar.parentElement.querySelector('.btn-g');
  if (cancelar) cancelar.innerHTML = somenteLeitura ? '<i class="bi bi-x-lg"></i> Fechar' : 'Cancelar';
  openM('avaliacao-modal');
}

function fecharModalAvaliacao() { closeM('avaliacao-modal'); avaliacaoModalAtual = null; }

function salvarAvaliacaoAtual() {
  if (!avaliacaoModalAtual || avaliacaoModalAtual.modo === 'visualizar') return;
  const respostas = avaliacaoDados.quesitos.map(item => ({ quesito_id: Number(item.id), avaliacao: document.querySelector(`input[name="avaliacao-quesito-${item.id}"]:checked`)?.value || '' }));
  if (respostas.some(item => !item.avaliacao)) { toast('Selecione uma opção para cada quesito.', 'bi-exclamation-triangle', 'amber'); return; }
  const botao = document.getElementById('avaliacao-modal-salvar');
  if (botao) botao.disabled = true;
  fetch('controllers/avaliacao.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ aluno_id: avaliacaoDados.aluno?.id, data: avaliacaoModalAtual.data, periodo: avaliacaoModalAtual.periodo, observacao_geral: document.getElementById('avaliacao-modal-observacao')?.value.trim() || '', respostas }) })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => { if (!ok || !data.success) throw new Error(data.message || 'Não foi possível salvar a avaliação.'); fecharModalAvaliacao(); toast(data.message || 'Avaliação salva com sucesso.', 'bi-check-circle', 'success'); carregarPaginaAvaliacao(); })
    .catch(error => toast(error.message || 'Erro ao salvar avaliação.', 'bi-x-circle', 'danger'))
    .finally(() => { if (botao) botao.disabled = false; });
}

function excluirAvaliacaoAtual(registroId, periodo) {
  const registro = obterAvaliacao(dataLocalAvaliacao(), periodo);
  if (!registroId || !registro || Number(registro.id) !== Number(registroId)) return;
  openConfirm(`Excluir a avaliação da ${rotuloPeriodoAvaliacao(periodo)} de hoje? Esta ação não pode ser desfeita.`, function() {
    fetch('controllers/avaliacao.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ registro_id: Number(registroId) })
    })
      .then(response => response.json().then(data => ({ ok: response.ok, data })))
      .then(({ ok, data }) => {
        if (!ok || !data.success) throw new Error(data.message || 'Não foi possível excluir a avaliação.');
        avaliacaoDados.avaliacoes = avaliacaoDados.avaliacoes.filter(item => Number(item.id) !== Number(registroId));
        carregarPaginaAvaliacao();
        toast(data.message || 'Avaliação excluída com sucesso.', 'bi-trash3', 'danger');
      })
      .catch(error => toast(error.message || 'Erro ao excluir avaliação.', 'bi-x-circle', 'danger'));
  });
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('evolucao-aluno')?.addEventListener('change', () => { atualizarQuesitosEvolucao(); renderRelatorioEvolucao(); });
  document.getElementById('evolucao-quesito')?.addEventListener('change', renderRelatorioEvolucao);
  document.querySelectorAll('input[name="evolucao-periodo"]').forEach(radio => {
    radio.addEventListener('change', () => {
      const hiddenPeriodo = document.getElementById('evolucao-periodo');
      if (hiddenPeriodo) hiddenPeriodo.value = radio.value;
      renderRelatorioEvolucao();
    });
  });
  document.getElementById('evolucao-inicio')?.addEventListener('change', renderRelatorioEvolucao);
  document.getElementById('evolucao-fim')?.addEventListener('change', renderRelatorioEvolucao);
});

  function carregarEvolucaoDashboard(alunos) {
    const container = document.getElementById('dash-evolucao');
    if (!container) return;
    if (!alunos.length) {
      container.innerHTML = '<div class="empty" style="padding:30px 0"><i class="bi bi-bar-chart-line"></i><span>Nenhum aluno disponível</span></div>';
      return;
    }

    fetch('controllers/listar_evolucao.php')
      .then(response => response.json())
      .then(registros => {
        if (!Array.isArray(registros) || !registros.length) {
          container.innerHTML = '<div class="empty" style="padding:30px 0"><i class="bi bi-bar-chart-line"></i><span>Nenhuma avaliação registrada</span><span style="font-size:11.5px;margin-top:3px">As pontuações aparecerão após os registros.</span></div>';
          return;
        }

        const quesitosParaCores = [...new Map(registros.map(registro => [String(registro.quesito_id), registro.quesito])).entries()]
          .sort(([idA, tituloA], [idB, tituloB]) => String(tituloA).localeCompare(String(tituloB), 'pt-BR') || idA.localeCompare(idB));
        const mapaCores = gerarMapaCoresQuesitos(quesitosParaCores);
        const alunosMap = new Map(alunos.map(aluno => [String(aluno.id), aluno]));
        const porAluno = new Map();
        registros.forEach(registro => {
          const alunoId = String(registro.aluno_id);
          if (!alunosMap.has(alunoId)) return;
          if (!porAluno.has(alunoId)) porAluno.set(alunoId, new Map());
          const porQuesito = porAluno.get(alunoId);
          const quesitoId = String(registro.quesito_id);
          if (!porQuesito.has(quesitoId)) porQuesito.set(quesitoId, { quesitoId, titulo: registro.quesito, pontos: 0, pesoTotal: 0, avaliacoes: 0 });
          const item = porQuesito.get(quesitoId);
          const peso = Math.max(1, Number(registro.peso) || 1);
          item.pontos += (Number(registro.valencia) || 0) * peso;
          item.pesoTotal += peso;
          item.avaliacoes += 1;
        });

        let html = '<div class="evolucao-dashboard-list">';
        porAluno.forEach((quesitos, alunoId) => {
          const aluno = alunosMap.get(alunoId);
          html += `<div class="evolucao-dashboard-aluno"><div class="evolucao-dashboard-aluno-nome"><i class="bi bi-person-check-fill"></i><span>${escaparUsuario(aluno.nome)}</span></div>`;
          quesitos.forEach(item => {
            const limite = Math.max(item.pesoTotal, 1);
            const percentual = Math.max(0, Math.min(100, ((item.pontos / limite) + 1) * 50));
            const cor = obterCorQuesito(item.quesitoId, item.titulo, mapaCores);
            const classe = `color:${cor}`;
            const pontos = item.pontos > 0 ? `+${item.pontos}` : String(item.pontos);
            html += `<div class="evolucao-dashboard-item"><div class="evolucao-dashboard-item-top"><span class="evolucao-dashboard-item-title">${escaparUsuario(item.titulo)}</span><strong class="evolucao-dashboard-score" style="${classe}">${pontos} pts <small style="font-weight:500;color:var(--text-muted)">${item.avaliacoes} aval.</small></strong></div><div class="evolucao-dashboard-track"><span style="width:${percentual}%;background:${cor}"></span></div></div>`;
          });
          html += '</div>';
        });
        container.innerHTML = porAluno.size ? html + '</div>' : '<div class="empty" style="padding:30px 0"><i class="bi bi-bar-chart-line"></i><span>Nenhuma avaliação dos alunos vinculados</span></div>';
      })
      .catch(() => {
        container.innerHTML = '<div class="empty" style="padding:30px 0"><i class="bi bi-exclamation-circle"></i><span>Não foi possível carregar a evolução</span></div>';
      });
  }

  // ===== DASHBOARD =====
  function refreshDash() {
    const listaAlunos = Array.isArray(students) ? students : [];
    const isApoio = (window.usuarioTipo || 'responsavel') === 'apoio';
    const titleEl = document.getElementById('dash-students-title');
    const subtitleEl = document.getElementById('dash-students-subtitle');
    if (titleEl) titleEl.textContent = isApoio ? 'Alunos Vinculados' : 'Alunos Recentes';
    if (subtitleEl) subtitleEl.textContent = isApoio ? 'Aluno(s) vinculado(s) ao acompanhamento' : 'Últimos cadastros realizados';

    const pcdEl = document.getElementById('s-pcds');
    const acompEl = document.getElementById('s-acomp');
    const turmasEl = document.getElementById('s-turmas');
    if (pcdEl) pcdEl.textContent = String(listaAlunos.length || 0);
    const acompCount = listaAlunos.filter(s => s.apoio_nome && String(s.apoio_nome).trim() !== '').length;
    if (acompEl) acompEl.textContent = String(acompCount || 0);
    if (turmasEl) turmasEl.textContent = String(listaAlunos.length ? [...new Set(listaAlunos.map(s => s.turma))].length : 0);

    carregarEvolucaoDashboard(listaAlunos);

    //  GRID DOS ESTUDANTES
    const sg = document.getElementById('dash-students');
    if (!sg) return;
    if (listaAlunos.length === 0) {
      const emptyLabel = isApoio ? 'Nenhum aluno vinculado' : 'Nenhum aluno cadastrado';
      const emptyHint = isApoio ? 'Ainda não há vínculo de acompanhamento para você.' : 'Cadastre alunos para que apareçam aqui';
      sg.innerHTML = `<div class="empty"><i class="bi bi-person-x"></i><span style="font-size:13.5px;font-weight:600">${emptyLabel}</span><span style="font-size:12px">${emptyHint}</span></div>`;
    } else {
      sg.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px">${listaAlunos.slice(-8).map(s => `
        <div onclick="go('aluno',${s.id})" style="padding:14px;border-radius:12px;background:rgba(255,255,255,0.03);border:1px solid var(--border);cursor:pointer;transition:all 0.18s" onmouseenter="this.style.borderColor='rgba(76,175,80,0.32)';this.style.background='rgba(76,175,80,0.05)'" onmouseleave="this.style.borderColor='var(--border)';this.style.background='rgba(255,255,255,0.03)'">
          <div class="av" style="width:38px;height:38px;font-size:14px;margin-bottom:9px;overflow:hidden">${renderAvatarAluno(s)}</div>
          <div style="font-size:12.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${s.nome}</div>
          <div style="font-size:10.5px;color:var(--text-muted);margin-top:2px">${s.turma}</div>
          <div class="student-dashboard-block__conditions">${renderCondicoes(s.diagnostico)}</div>
        </div>`).join('')}</div>`;
    }
  }

  // ===== TAG CLASS =====
  function tagC(d) { return d==='TEA'?'t-tea':d==='TDAH'?'t-tdah':'t-out'; }

  const coresCondicoes = new Map();
  let proximaCorCondicao = 0;

  function estiloCondicao(valor) {
    const paleta = [
      ['rgba(37, 99, 235, .12)', '#1d4ed8', 'rgba(37, 99, 235, .30)'],
      ['rgba(124, 58, 237, .12)', '#6d28d9', 'rgba(124, 58, 237, .30)'],
      ['rgba(219, 39, 119, .12)', '#be185d', 'rgba(219, 39, 119, .30)'],
      ['rgba(13, 148, 136, .12)', '#0f766e', 'rgba(13, 148, 136, .30)'],
      ['rgba(234, 88, 12, .12)', '#c2410c', 'rgba(234, 88, 12, .30)'],
      ['rgba(101, 163, 13, .14)', '#4d7c0f', 'rgba(101, 163, 13, .32)'],
      ['rgba(2, 132, 199, .12)', '#0369a1', 'rgba(2, 132, 199, .30)'],
      ['rgba(190, 24, 93, .12)', '#9d174d', 'rgba(190, 24, 93, .30)']
    ];
    const texto = String(valor || 'Sem condição').trim().toUpperCase();
    if (!coresCondicoes.has(texto)) {
      const indice = proximaCorCondicao++;
      if (indice < paleta.length) {
        coresCondicoes.set(texto, paleta[indice]);
      } else {
        const hue = (indice * 137.508) % 360;
        coresCondicoes.set(texto, [`hsla(${hue}, 72%, 45%, .12)`, `hsl(${hue}, 72%, 34%)`, `hsla(${hue}, 72%, 45%, .30)`]);
      }
    }
    const [fundo, cor, borda] = coresCondicoes.get(texto);
    return `background:${fundo};color:${cor};border:1px solid ${borda};`;
  }

  function renderCondicoes(valor) {
    const condicoes = String(valor || '').split(',').map(condicao => condicao.trim()).filter(Boolean);
    if (!condicoes.length) return '<span class="student-profile-block__empty-value">—</span>';
    return condicoes.map(condicao => `<span class="tag ${tagC(condicao)}" style="${estiloCondicao(condicao)}">${escaparUsuario(condicao)}</span>`).join('');
  }

  // ===== TABELA =====
  function renderTable(list) {
    const lista = Array.isArray(list) ? list : [];
    document.getElementById('s-count').textContent = `Alunos: ${lista.length}`;
    const tb = document.getElementById('s-table');
    const em = document.getElementById('s-empty');
    if (lista.length === 0) { tb.innerHTML=''; em.style.display='flex'; return; }
    em.style.display = 'none';
    tb.innerHTML = lista.map(s => `
      <div class="student-profile-block" role="button" tabindex="0" onclick="go('aluno',${Number(s.id)})" onkeydown="if(event.key==='Enter'||event.key===' ')go('aluno',${Number(s.id)})">
        <div class="student-profile-block__top">
          <div class="student-profile-block__identity">
            <div class="av student-profile-block__avatar">${renderAvatarAluno(s)}</div>
            <div class="student-profile-block__name">${escaparUsuario(s.nome)}</div>
          </div>
          <i class="bi bi-chevron-right student-profile-block__arrow"></i>
        </div>
        <div class="student-profile-block__condition"><span class="student-profile-block__label">Condição geral</span><div class="student-profile-block__conditions">${renderCondicoes(s.diagnostico)}</div></div>
        <div class="student-profile-block__details">
          <div><span class="student-profile-block__label">Turma</span><strong>${escaparUsuario(s.turma || '—')}</strong></div>
          <div><span class="student-profile-block__label">Curso</span><strong>${escaparUsuario(s.curso || '—')}</strong></div>
          <div><span class="student-profile-block__label">Matrícula</span><strong>${escaparUsuario(s.matricula || '—')}</strong></div>
        </div>
      </div>`).join('');
  }

  function filterS(q) {
    const base = Array.isArray(students) ? students : [];
    const t = document.getElementById('fil-turma').value;
    const d = document.getElementById('fil-diag').value;
    filtered = base.filter(s =>
      (s.nome.toLowerCase().includes(q.toLowerCase()) || s.matricula.includes(q)) &&
      (!t || s.turma === t) && (!d || (s.diagnostico || '').split(',').map(condicao => condicao.trim()).includes(d))
    );
    renderTable(filtered);
  }

  function atualizarFiltrosPerfis() {
    const filtroTurma = document.getElementById('fil-turma');
    const filtroDiagnostico = document.getElementById('fil-diag');
    if (!filtroTurma || !filtroDiagnostico) return;

    const turmaAtual = filtroTurma.value;
    const turmas = [...new Set(students.map(aluno => String(aluno.turma || '').trim()).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'pt-BR', { numeric: true }));
    filtroTurma.innerHTML = '<option value="">Todas as Turmas</option>' + turmas.map(turma => `<option value="${escaparUsuario(turma)}">${escaparUsuario(turma)}</option>`).join('');
    filtroTurma.value = turmas.includes(turmaAtual) ? turmaAtual : '';

    const diagnosticoAtual = filtroDiagnostico.value;
    fetch('controllers/listar_opcoes_alunos.php')
      .then(response => response.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível carregar as condições gerais.');
        const diagnosticos = data.diagnosticos || [];
        filtroDiagnostico.innerHTML = '<option value="">Todas as condições gerais</option>' + diagnosticos.map(item => { const valor = item.sigla || item.nome; return `<option value="${escaparUsuario(valor)}">${escaparUsuario(item.nome)} (${escaparUsuario(valor)})</option>`; }).join('');
        filtroDiagnostico.value = diagnosticos.some(item => (item.sigla || item.nome) === diagnosticoAtual) ? diagnosticoAtual : '';
      })
      .catch(error => {
        console.error(error);
        filtroDiagnostico.innerHTML = '<option value="">Todas as condições gerais</option>';
      });
  }

  function exportCSV() {
    if (!filtered.length) { toast('Nenhum aluno para exportar','bi-exclamation-circle','amber'); return; }
    const h = 'Nome,Matrícula,CPF,Sexo,Turma,Curso,Condição geral,Nascimento\n';
    const r = filtered.map(s=>`${s.nome},${s.matricula},${s.cpf},${s.sexo},${s.turma},${s.curso},${s.diagnostico},${s.data_nascimento || s.nasc || ''}`).join('\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([h+r],{type:'text/csv;charset=utf-8;'}));
    a.download = 'alunos_pcd.csv'; a.click();
    toast('Lista exportada!');
  }

  function renderAvatarAluno(aluno) {
    const nome = (aluno?.nome || 'U').trim();
    const inicial = nome ? nome.charAt(0).toUpperCase() : '?';
    return aluno?.foto ? `<img src="${escaparUsuario(aluno.foto)}?v=${Date.now()}" alt="Foto do aluno" style="width:100%;height:100%;object-fit:cover;border-radius:50%">` : inicial;
  }

  function atualizarFotoAlunoPerfil(foto) {
    const av = document.getElementById('a-av');
    if (av) {
      av.style.backgroundImage = '';
      av.innerHTML = foto ? `<img src="${escaparUsuario(foto)}?v=${Date.now()}" alt="Foto do aluno" style="width:100%;height:100%;object-fit:cover;border-radius:50%">` : '?';
    }
    const alunoAtual = students.find(item => Number(item.id) === Number(curSid));
    if (alunoAtual) alunoAtual.foto = foto || null;
    const btnRemover = document.getElementById('btn-remover-foto-aluno');
    if (btnRemover) btnRemover.disabled = !foto;
  }

  function abrirSeletorFotoAluno() {
    if (!curSid) return;
    document.getElementById('input-foto-aluno')?.click();
  }

  document.getElementById('input-foto-aluno')?.addEventListener('change', event => {
    const arquivo = event.target.files?.[0];
    if (!arquivo || !curSid) return;
    const dados = new FormData();
    dados.append('foto', arquivo);
    fetch(`controllers/salvar_aluno.php?action=upload_foto_aluno&aluno_id=${curSid}`, { method: 'POST', body: dados })
      .then(res => res.json())
      .then(data => {
        if (!data.success) throw new Error(data.erro || 'Não foi possível salvar a foto.');
        atualizarFotoAlunoPerfil(data.foto);
        toast('Foto do aluno atualizada!', 'bi-check-circle', 'success');
        event.target.value = '';
      })
      .catch(error => toast(error.message || 'Erro ao salvar foto do aluno.', 'bi-x-circle', 'danger'));
  });

  function removerFotoAluno() {
    if (!curSid) return;
    fetch(`controllers/salvar_aluno.php?action=remover_foto_aluno&aluno_id=${curSid}`, { method: 'POST' })
      .then(res => res.json())
      .then(data => {
        if (!data.success) throw new Error(data.erro || 'Não foi possível remover a foto.');
        atualizarFotoAlunoPerfil('');
        toast('Foto removida com sucesso!', 'bi-trash3', 'danger');
      })
      .catch(error => toast(error.message || 'Erro ao remover a foto.', 'bi-x-circle', 'danger'));
  }

  // ===== PERFIL =====
  function openProfile(id) {
    const s = students.find(x=>x.id===id); if(!s) return;
    curSid = id;
    const av = document.getElementById('a-av');
    av.style.backgroundImage = '';
    av.innerHTML = renderAvatarAluno(s);
    document.getElementById('a-nome').textContent = s.nome;
    document.getElementById('a-mat').textContent = s.matricula;
    document.getElementById('a-turma').textContent = s.turma;
    document.getElementById('a-curso').textContent = s.curso;
    document.getElementById('a-sexo').textContent = s.sexo;
    const dataNascimento = s.data_nascimento || s.nasc || '';
    document.getElementById('a-nasc').textContent = dataNascimento ? new Date(dataNascimento + 'T12:00:00').toLocaleDateString('pt-BR') : '—';
    const btnRemover = document.getElementById('btn-remover-foto-aluno');
    if (btnRemover) btnRemover.disabled = !s.foto;
    const tag = document.getElementById('a-diag-tag');
    tag.innerHTML = renderCondicoes(s.diagnostico); tag.className = 'student-profile-block__conditions';
    const ac = document.getElementById('a-acompanhante'); if(ac) ac.textContent = s.apoio_nome || '—';
    prepararVinculoApoio(s);
    const ehGestorAvaliacao = ['master', 'aee', 'apoio'].includes(String(window.usuarioTipo || '').trim().toLowerCase());
    const abaInicial = ehGestorAvaliacao ? 'apoio' : 'pei';
    swTab(abaInicial, document.getElementById(`tb-${abaInicial}`));
    renderProfileForms();
    aplicarModoSomenteLeitura();
    loadPEI(id);
    loadPAEE(id);
    renderLaudos(id);
    if (ehGestorAvaliacao) carregarAssociacaoQuesitos(id);
  }

  function aplicarModoSomenteLeitura() {
    if (String(window.usuarioTipo || '').trim().toLowerCase() !== 'apoio') return;
    ['tc-pei', 'tc-paee', 'tc-lau'].forEach(id => {
      const area = document.getElementById(id);
      if (!area) return;
      area.querySelectorAll('input, textarea').forEach(campo => { campo.disabled = true; campo.readOnly = true; });
      area.querySelectorAll('button.btn-p').forEach(botao => { botao.style.display = 'none'; });
    });
  }

  function prepararVinculoApoio(aluno) {
    const area = document.getElementById('apoio-vinculo-area');
    const select = document.getElementById('perfil-apoio-select');
    if (!area || !select) return;
    const controles = document.getElementById('apoio-vincular-controls');
    const desvincular = document.getElementById('apoio-desvincular-btn');
    const label = document.getElementById('apoio-vinculo-label');
    area.style.display = 'block';
    if (aluno.id_apoio) {
      if (controles) controles.style.display = 'none';
      if (desvincular) desvincular.style.display = 'inline-flex';
      if (label) label.textContent = 'Apoio vinculado';
      return;
    }
    if (controles) controles.style.display = 'flex';
    if (desvincular) desvincular.style.display = 'none';
    if (label) label.textContent = 'Definir apoio';
    select.innerHTML = '<option value="">Selecione um apoio</option>';
    fetch('controllers/listar_opcoes_alunos.php')
      .then(response => response.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível carregar os apoios.');
        select.innerHTML = '<option value="">Selecione um apoio</option>' + (data.apoios || []).map(item => `<option value="${Number(item.id)}">${escaparUsuario(item.nome)}</option>`).join('');
      })
      .catch(error => toast(error.message || 'Erro ao carregar apoios.', 'bi-x-circle', 'danger'));
  }

  function vincularApoioAluno() {
    const select = document.getElementById('perfil-apoio-select');
    const apoioId = Number(select?.value || 0);
    if (!curSid || !apoioId) {
      toast('Selecione um apoio antes de vincular.', 'bi-exclamation-triangle', 'amber');
      return;
    }
    fetch('controllers/vincular_apoio.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ aluno_id: curSid, id_apoio: apoioId })
    })
      .then(response => response.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível vincular o apoio.');
        const aluno = students.find(item => Number(item.id) === Number(curSid));
        const nomeApoio = select.options[select.selectedIndex]?.textContent || '—';
        if (aluno) { aluno.id_apoio = apoioId; aluno.apoio_nome = nomeApoio; }
        const acompanhante = document.getElementById('a-acompanhante');
        if (acompanhante) acompanhante.textContent = nomeApoio;
        const area = document.getElementById('apoio-vinculo-area');
        if (area) area.style.display = 'none';
        toast(data.message || 'Apoio vinculado com sucesso.', 'bi-check-circle', 'success');
        carregarAlunos();
      })
      .catch(error => toast(error.message || 'Erro ao vincular apoio.', 'bi-x-circle', 'danger'));
  }

  function desvincularApoioAluno() {
    if (!curSid) return;
    openConfirm('Deseja desvincular o apoio deste aluno?', function() {
      fetch('controllers/vincular_apoio.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ aluno_id: curSid, acao: 'desvincular' })
      })
        .then(response => response.json())
        .then(data => {
          if (!data.success) throw new Error(data.message || 'Não foi possível desvincular o apoio.');
          const aluno = students.find(item => Number(item.id) === Number(curSid));
          if (aluno) { aluno.id_apoio = null; aluno.apoio_nome = ''; }
          const acompanhante = document.getElementById('a-acompanhante');
          if (acompanhante) acompanhante.textContent = '—';
          prepararVinculoApoio(aluno || {});
          toast(data.message || 'Apoio desvinculado com sucesso.', 'bi-check-circle', 'success');
          carregarAlunos();
        })
        .catch(error => toast(error.message || 'Erro ao desvincular apoio.', 'bi-x-circle', 'danger'));
    });
  }

  function onlyDigits(input, maxLength) {
    input.value = (input.value.replace(/\D/g, '') || '').slice(0, maxLength);
  }

  // ===== TABS =====
  function swTab(tab, btn, manterDocumentoAberto = false) {
    ['apoio','pei','paee','lau'].forEach(t => { const e=document.getElementById('tc-'+t); if(e) e.style.display='none'; });
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById('tc-'+tab).style.display='block';
    if(btn) btn.classList.add('active');
    if (tab === 'apoio' && curSid) loadRegistrosApoio(curSid);
    if (window.usuarioTipo === 'apoio' && curSid && !manterDocumentoAberto) {
      if (tab === 'pei') {
        document.querySelector('#tc-pei .document-view-form')?.classList.add('document-view-form--hidden');
        loadPEI(curSid);
      }
      if (tab === 'paee') {
        document.querySelector('#tc-paee .document-view-form')?.classList.add('document-view-form--hidden');
        loadPAEE(curSid);
      }
    }
  }

  function setField(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value || '';
  }

  function renderProfileForms() {
    const ids = [
      'rel-data','rel-responsavel','rel-desenvolvimento','rel-dificuldades',
      'rel-potencialidades','rel-recomendacoes','rel-conclusao',
      'pei-nome','pei-data','pei-responsavel','pei-objetivos','pei-habilidades','pei-estrategias',
      'pei-recursos','pei-adaptacoes','pei-avaliacao','pei-observacoes',
      'paee-nome','paee-data','paee-responsavel','paee-necessidades','paee-objetivos','paee-recursos',
      'paee-estrategias','paee-tecnologias','paee-resultados','paee-encaminhamentos','paee-observacoes',
      'apoio-data','apoio-observacao-geral'
    ];
    ids.forEach(id => setField(id, ''));
    const peiTitulo = document.getElementById('pei-titulo');
    const paeeTitulo = document.getElementById('paee-titulo');
    if (peiTitulo) peiTitulo.textContent = 'PEI - Plano Educacional Individualizado';
    if (paeeTitulo) paeeTitulo.textContent = 'PAEE - Plano de Atendimento Educacional Especializado';
    ['rel-history','pei-history','paee-history','lau-list','lau-viewer','apoio-historico'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.innerHTML = '';
    });
    document.querySelectorAll('.document-view-form').forEach(form => {
      if (String(window.usuarioTipo || '').trim().toLowerCase() === 'apoio') form.classList.add('document-view-form--hidden');
    });
  }

  function mostrarDocumentoParaLeitura(tipo) {
    if (String(window.usuarioTipo || '').trim().toLowerCase() !== 'apoio') return;
    document.querySelector(`#tc-${tipo} .document-view-form`)?.classList.remove('document-view-form--hidden');
  }

  function formatDateBR(value) {
    if (!value) return '—';
    const date = new Date(value + 'T00:00');
    if (isNaN(date)) return value;
    return date.toLocaleDateString('pt-BR');
  }

  // garante estilos e comportamento das áreas de histórico (scroll discreto)
  (function ensureHistoryStyles(){
    try {
      const css = `
        #rel-history, #pei-history, #paee-history, #lau-list { max-height:240px; overflow-y:auto; padding-right:8px; }
        #rel-history::-webkit-scrollbar, #pei-history::-webkit-scrollbar, #paee-history::-webkit-scrollbar, #lau-list::-webkit-scrollbar { width:8px; }
        #rel-history::-webkit-scrollbar-thumb, #pei-history::-webkit-scrollbar-thumb, #paee-history::-webkit-scrollbar-thumb, #lau-list::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.06); border-radius:6px; }
        #rel-history::-webkit-scrollbar-track, #pei-history::-webkit-scrollbar-track, #paee-history::-webkit-scrollbar-track, #lau-list::-webkit-scrollbar-track { background:transparent; }
      `;
      const s=document.createElement('style'); s.appendChild(document.createTextNode(css)); document.head.appendChild(s);
    } catch(e){/* fail silently */}
  })();

  function loadRelatorios(id) {
    const target = document.getElementById('rel-history');
    if (!target) return;
    target.innerHTML = '<div style="padding:14px 0;color:var(--text-muted);font-size:12px">Carregando relatórios...</div>';
    fetch(`controllers/listar_relatorios.php?aluno_id=${id}`)
      .then(r => r.json())
      .then(items => {
        if (!Array.isArray(items) || items.length === 0) {
          target.innerHTML = '<div class="empty" style="padding:20px 0"><i class="bi bi-clipboard2-pulse" style="font-size:32px;margin-bottom:8px"></i><span style="font-size:13px;font-weight:600">Nenhum relatório encontrado</span><span style="font-size:11px;margin-top:3px">Os relatórios salvos aparecerão aqui.</span></div>';
          return;
        }
        target.innerHTML = items.map(item => `
          <div class="doc-item document-summary-block" style="margin-bottom:10px;">
            <div style="display:flex;gap:10px;align-items:center">
              <i class="bi bi-file-earmark-text" style="font-size:18px;color:var(--gold)"></i>
              <div>
                <div style="font-size:13px;font-weight:700;color:#fff">Relatório</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">${formatDateBR(item.data_atendimento)} • ${item.responsavel_registro || '—'}</div>
              </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <button onclick="openRelatorio(${item.id})" class="btn-g" style="padding:6px 12px;font-size:12px">Abrir</button>
              <button onclick="deleteRelatorio(${item.id})" class="btn-g" style="padding:6px 12px;font-size:12px;background:rgba(255,0,0,0.06);border-color:rgba(255,0,0,0.12)">Excluir</button>
            </div>
          </div>
        `).join('');
      })
      .catch(() => {
        target.innerHTML = '<div class="empty" style="padding:20px 0"><span style="font-size:12px">Erro ao carregar relatórios.</span></div>';
      });
  }

  function openRelatorio(id) {
    fetch(`controllers/abrir_relatorio.php?id=${id}`)
      .then(r => r.json())
      .then(data => {
        if (!data || !Object.keys(data).length) return;
        setField('rel-data', data.data_atendimento || '');
        setField('rel-responsavel', data.responsavel_registro || '');
        setField('rel-desenvolvimento', data.desenvolvimento_observado || '');
        setField('rel-dificuldades', data.dificuldades_observadas || '');
        setField('rel-potencialidades', data.potencialidades || '');
        setField('rel-recomendacoes', data.recomendacoes || '');
        setField('rel-conclusao', data.conclusao || '');
        swTab('rel', document.getElementById('tb-rel'));
      })
      .catch(() => toast('Erro ao abrir relatório.','bi-x-circle','danger'));
  }

  function loadPEI(id) {
    const target = document.getElementById('pei-history');
    if (!target) return;
    target.style.display = 'block';
    target.innerHTML = '<div style="padding:14px 0;color:var(--text-muted);font-size:12px">Carregando PEI...</div>';
    fetch(`controllers/listar_pei.php?aluno_id=${id}`)
      .then(r => r.json())
      .then(items => {
        if (!Array.isArray(items) || items.length === 0) {
          target.innerHTML = '<div class="empty" style="padding:20px 0"><i class="bi bi-journal-text" style="font-size:32px;margin-bottom:8px"></i><span style="font-size:13px;font-weight:600">Nenhum PEI encontrado</span><span style="font-size:11px;margin-top:3px">Os registros de PEI aparecerão aqui.</span></div>';
          return;
        }
        target.innerHTML = items.map(item => `
          <div class="doc-item document-summary-block${String(window.usuarioTipo || '').trim().toLowerCase() === 'apoio' ? ' document-summary-block--apoio' : ''}" style="margin-bottom:10px;">
            <div style="display:flex;gap:10px;align-items:center">
              <i class="bi bi-journal-text" style="font-size:18px;color:var(--c3)"></i>
              <div>
                <div style="font-size:13px;font-weight:700;color:#fff">${item.nome_documento || 'PEI'}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">${formatDateBR(item.data_elaboracao)} • ${item.responsavel_preenchimento || '—'}</div>
              </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <button onclick="abrirPEI(${item.id})" class="btn-g" style="padding:6px 12px;font-size:12px">Abrir</button>
              ${String(window.usuarioTipo || '').trim().toLowerCase() !== 'apoio' ? `<button onclick="deletePEI(${item.id})" class="btn-g" style="padding:6px 12px;font-size:12px;background:rgba(255,0,0,0.06);border-color:rgba(255,0,0,0.12)">Excluir</button>` : ''}
            </div>
          </div>
        `).join('');
      })
      .catch(() => {
        target.innerHTML = '<div class="empty" style="padding:20px 0"><span style="font-size:12px">Erro ao carregar PEI.</span></div>';
      });
  }

  function abrirPEI(id) {
    fetch(`controllers/abrir_pei.php?id=${id}`)
      .then(r => r.json())
      .then(data => {
        if (!data || !Object.keys(data).length) return;
        const peiTitulo = document.getElementById('pei-titulo');
        if (peiTitulo) peiTitulo.textContent = data.nome_documento || 'PEI - Plano Educacional Individualizado';
        setField('pei-data', data.data_elaboracao || '');
        setField('pei-nome', data.nome_documento || '');
        setField('pei-responsavel', data.responsavel_preenchimento || '');
        setField('pei-objetivos', data.objetivos_pedagogicos || '');
        setField('pei-habilidades', data.habilidades_desenvolver || '');
        setField('pei-estrategias', data.estrategias_ensino || '');
        setField('pei-recursos', data.recursos_acessibilidade || '');
        setField('pei-adaptacoes', data.adaptacoes_curriculares || '');
        setField('pei-avaliacao', data.criterios_avaliacao || '');
        setField('pei-observacoes', data.observacoes || '');
        mostrarDocumentoParaLeitura('pei');
        const historico = document.getElementById('pei-history');
        if (historico) historico.style.display = 'none';
        swTab('pei', document.getElementById('tb-pei'), true);
      })
      .catch(() => toast('Erro ao abrir PEI.','bi-x-circle','danger'));
  }

  function loadPAEE(id) {
    const target = document.getElementById('paee-history');
    if (!target) return;
    target.style.display = 'block';
    target.innerHTML = '<div style="padding:14px 0;color:var(--text-muted);font-size:12px">Carregando PAEE...</div>';
    fetch(`controllers/listar_paee.php?aluno_id=${id}`)
      .then(r => r.json())
      .then(items => {
        if (!Array.isArray(items) || items.length === 0) {
          target.innerHTML = '<div class="empty" style="padding:20px 0"><i class="bi bi-person-workspace" style="font-size:32px;margin-bottom:8px"></i><span style="font-size:13px;font-weight:600">Nenhum PAEE encontrado</span><span style="font-size:11px;margin-top:3px">Os registros de PAEE aparecerão aqui.</span></div>';
          return;
        }
        target.innerHTML = items.map(item => `
          <div class="doc-item document-summary-block${String(window.usuarioTipo || '').trim().toLowerCase() === 'apoio' ? ' document-summary-block--apoio' : ''}" style="margin-bottom:10px;">
            <div style="display:flex;gap:10px;align-items:center">
              <i class="bi bi-person-workspace" style="font-size:18px;color:var(--c4)"></i>
              <div>
                <div style="font-size:13px;font-weight:700;color:#fff">${item.nome_documento || 'PAEE'}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">${formatDateBR(item.data_atendimento)} • ${item.profissional_responsavel || '—'}</div>
              </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <button onclick="abrirPAEE(${item.id})" class="btn-g" style="padding:6px 12px;font-size:12px">Abrir</button>
              ${String(window.usuarioTipo || '').trim().toLowerCase() !== 'apoio' ? `<button onclick="deletePAEE(${item.id})" class="btn-g" style="padding:6px 12px;font-size:12px;background:rgba(255,0,0,0.06);border-color:rgba(255,0,0,0.12)">Excluir</button>` : ''}
            </div>
          </div>
        `).join('');
      })
      .catch(() => {
        target.innerHTML = '<div class="empty" style="padding:20px 0"><span style="font-size:12px">Erro ao carregar PAEE.</span></div>';
      });
  }

  function abrirPAEE(id) {
    fetch(`controllers/abrir_paee.php?id=${id}`)
      .then(r => r.json())
      .then(data => {
        if (!data || !Object.keys(data).length) return;
        const paeeTitulo = document.getElementById('paee-titulo');
        if (paeeTitulo) paeeTitulo.textContent = data.nome_documento || 'PAEE - Plano de Atendimento Educacional Especializado';
        setField('paee-data', data.data_atendimento || '');
        setField('paee-nome', data.nome_documento || '');
        setField('paee-responsavel', data.profissional_responsavel || '');
        setField('paee-necessidades', data.necessidades_identificadas || '');
        setField('paee-objetivos', data.objetivos_atendimento || '');
        setField('paee-recursos', data.recursos_utilizados || '');
        setField('paee-estrategias', data.estrategias_aplicadas || '');
        setField('paee-tecnologias', data.tecnologias_assistivas || '');
        setField('paee-resultados', data.resultados_observados || '');
        setField('paee-encaminhamentos', data.encaminhamentos || '');
        setField('paee-observacoes', data.observacoes_gerais || '');
        mostrarDocumentoParaLeitura('paee');
        const historico = document.getElementById('paee-history');
        if (historico) historico.style.display = 'none';
        swTab('paee', document.getElementById('tb-paee'), true);
      })
      .catch(() => toast('Erro ao abrir PAEE.','bi-x-circle','danger'));
  }

  // ===== DELETE DOCUMENTS =====
  function deleteRelatorio(id) {
    openConfirm('Confirma exclusão do relatório?', function(){
      fetch('controllers/excluir_relatorio.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({id}) })
        .then(r=>r.json()).then(resp=>{ if(resp.success){ toast('Relatório excluído','bi-trash3','danger'); loadRelatorios(curSid);} else toast(resp.message||'Erro ao excluir relatório','bi-x-circle','danger'); })
        .catch(()=>toast('Erro de conexão','bi-x-circle','danger'));
    });
  }

  function deletePEI(id) {
    openConfirm('Confirma exclusão do PEI?', function(){
      fetch('controllers/excluir_pei.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id}) })
        .then(r=>r.json()).then(resp=>{ if(resp.success){ toast('PEI excluído','bi-trash3','danger'); loadPEI(curSid);} else toast(resp.message||'Erro ao excluir PEI','bi-x-circle','danger'); })
        .catch(()=>toast('Erro de conexão','bi-x-circle','danger'));
    });
  }

  function deletePAEE(id) {
    openConfirm('Confirma exclusão do PAEE?', function(){
      fetch('controllers/excluir_paee.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id}) })
        .then(r=>r.json()).then(resp=>{ if(resp.success){ toast('PAEE excluído','bi-trash3','danger'); loadPAEE(curSid);} else toast(resp.message||'Erro ao excluir PAEE','bi-x-circle','danger'); })
        .catch(()=>toast('Erro de conexão','bi-x-circle','danger'));
    });
  }

  function deleteLaudo(id) {
    openConfirm('Confirma exclusão do laudo?', function(){
      fetch('controllers/excluir_laudo.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id}) })
        .then(r=>r.json()).then(resp=>{ if(resp.success){ toast('Laudo excluído','bi-trash3','danger'); renderLaudos(curSid);} else toast(resp.message||'Erro ao excluir laudo','bi-x-circle','danger'); })
        .catch(()=>toast('Erro de conexão','bi-x-circle','danger'));
    });
  }

  // ===== GENERIC CONFIRM MODAL HANDLER =====
  function openConfirm(msg, callback) {
    window._confirmCb = callback;
    const el = document.getElementById('confirm-msg'); if (el) el.textContent = msg;
    openM('confirm-modal');
  }

  function confirmAction() {
    try { if (window._confirmCb && typeof window._confirmCb === 'function') window._confirmCb(); }
    catch(e){ console.error(e); }
    window._confirmCb = null;
    closeM('confirm-modal');
  }

  function exportarRelatorio() {
    if (!curSid) return;

    const dados = {
      data: document.getElementById('rel-data').value || '—',
      responsavel: document.getElementById('rel-responsavel').value || '—',
      desenvolvimento: document.getElementById('rel-desenvolvimento').value || '—',
      dificuldades: document.getElementById('rel-dificuldades').value || '—',
      potencialidades: document.getElementById('rel-potencialidades').value || '—',
      recomendacoes: document.getElementById('rel-recomendacoes').value || '—',
      conclusao: document.getElementById('rel-conclusao').value || '—'
    };

    const linhas = [
      '<!DOCTYPE html>',
      '<html lang="pt-BR">',
      '<head>',
      '<meta charset="UTF-8" />',
      '<style>',
      'body{font-family: "Segoe UI", Arial, sans-serif; color:#203123; padding:34px 38px; line-height:1.5; background:#fcfdf9;}',
      '.cabecalho{border:2px solid #2f7d32; border-radius:12px; padding:18px 22px; background:linear-gradient(135deg, #f3fbf0 0%, #e8f5e9 100%); margin-bottom:24px;}',
      'h1{font-size:22px; margin:0 0 6px; color:#2e7d32;}',
      '.sub{font-size:12px; color:#4e6b53; margin:0;}',
      'h2{font-size:14px; margin:20px 0 8px; color:#2e7d32; border-bottom:1px solid #c8e6c9; padding-bottom:4px;}',
      '.campo{margin-bottom:14px;}',
      '.label{font-weight:700; color:#2f4f3f; display:block; margin-bottom:5px;}',
      '.valor{white-space:pre-wrap; background:#ffffff; padding:12px 14px; border-radius:8px; border:1px solid #dcedc8; min-height:44px;}',
      '.linha{height:1px; background:#dcedc8; margin:16px 0 12px;}',
      '.assinatura{margin-top:34px; display:flex; justify-content:space-between; gap:20px;}',
      '.bloco{flex:1; border-top:1px solid #9ccc65; padding-top:8px; font-size:12px; color:#4e6b53;}',
      '.marca{font-size:11px; color:#6b8f6f; margin-top:8px;}',
      '</style>',
      '</head>',
      '<body>',
      '<div class="cabecalho">',
      '<h1>Relatório de Acompanhamento</h1>',
      '<p class="sub">Documento institucional para registro pedagógico e acompanhamento</p>',
      '</div>',
      `<p><strong>Aluno:</strong> ${document.getElementById('a-nome').textContent || '—'}</p>`,
      `<div class="campo"><span class="label">Data do atendimento</span><div class="valor">${dados.data}</div></div>`,
      `<div class="campo"><span class="label">Responsável pelo registro</span><div class="valor">${dados.responsavel}</div></div>`,
      `<h2>Desenvolvimento observado</h2><div class="valor">${dados.desenvolvimento}</div>`,
      `<h2>Dificuldades observadas</h2><div class="valor">${dados.dificuldades}</div>`,
      `<h2>Potencialidades</h2><div class="valor">${dados.potencialidades}</div>`,
      `<h2>Recomendações</h2><div class="valor">${dados.recomendacoes}</div>`,
      `<h2>Conclusão</h2><div class="valor">${dados.conclusao}</div>`,
      '<div class="linha"></div>',
      '<div class="assinatura">',
      '<div class="bloco">____________________________<br>Assinatura do responsável</div>',
      '<div class="bloco">____________________________<br>Nome e função</div>',
      '</div>',
      '<div class="marca">Documento gerado automaticamente pelo sistema</div>',
      '</body>',
      '</html>'
    ].join('');

    const printWindow = window.open('', '', 'width=900,height=700');
    if (!printWindow) {
      toast('Não foi possível abrir a janela de impressão.','bi-x-circle','danger');
      return;
    }

    printWindow.document.write(linhas);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    toast('PDF preparado para impressão!','bi-printer','success');
  }

  function saveRelatorio() {
    console.log("saveRelatorio foi chamada");
    if (!curSid) return;
    const payload = {
      aluno_id: curSid,
      dataAtendimento: document.getElementById('rel-data').value,
      responsavel: document.getElementById('rel-responsavel').value.trim(),
      desenvolvimento: document.getElementById('rel-desenvolvimento').value.trim(),
      dificuldades: document.getElementById('rel-dificuldades').value.trim(),
      potencialidades: document.getElementById('rel-potencialidades').value.trim(),
      recomendacoes: document.getElementById('rel-recomendacoes').value.trim(),
      conclusao: document.getElementById('rel-conclusao').value.trim()
    };
    if (!payload.dataAtendimento || !payload.responsavel) {
      toast('Informe a data do atendimento e o responsável pelo registro.','bi-exclamation-triangle','amber');
      return;
    }
    fetch('controllers/salvar_relatorio.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        toast('Relatório salvo!','bi-check-circle','success');
        loadRelatorios(curSid);
        // limpar formulário somente após sucesso
        ['rel-data','rel-responsavel','rel-desenvolvimento','rel-dificuldades','rel-potencialidades','rel-recomendacoes','rel-conclusao']
          .forEach(id=>{const el=document.getElementById(id); if(el) el.value='';});
      } else {
        toast(data.message || 'Erro ao salvar o relatório.','bi-x-circle','danger');
      }
    })
    .catch(() => toast('Erro de conexão ao salvar o relatório.','bi-x-circle','danger'));
  }

  // ===== REGISTRO DE APOIO =====
  let quesitosApoio = [];
  let respostasApoio = {};
  let quesitoModalAtual = 0;

  function atualizarPeriodoApoio() {
    const campoData = document.getElementById('apoio-data');
    const agora = new Date();
    const hoje = `${agora.getFullYear()}-${String(agora.getMonth() + 1).padStart(2, '0')}-${String(agora.getDate()).padStart(2, '0')}`;
    const dataAnterior = campoData?.value && campoData.value < hoje;
    const periodoAtual = agora.getHours() < 12 ? 'manha' : 'tarde';
    document.querySelectorAll('input[name="apoio-periodo"]').forEach(radio => {
      radio.disabled = !dataAnterior && radio.value !== periodoAtual;
      if (!dataAnterior) radio.checked = radio.value === periodoAtual;
    });
  }
  setInterval(atualizarPeriodoApoio, 60000);

  function atualizarLimiteDataApoio() {
    const campoData = document.getElementById('apoio-data');
    if (!campoData) return;
    const agora = new Date();
    const hoje = `${agora.getFullYear()}-${String(agora.getMonth() + 1).padStart(2, '0')}-${String(agora.getDate()).padStart(2, '0')}`;
    campoData.max = hoje;
    if (campoData.value > hoje) campoData.value = hoje;
  }

  function loadRegistrosApoio(id) {
    const lista = document.getElementById('apoio-quesitos');
    if (!lista) return;
    lista.innerHTML = '<div style="padding:12px 0;color:var(--text-muted);font-size:12px">Carregando quesitos...</div>';
    const campoData = document.getElementById('apoio-data');
    campoData.value = '';
    campoData.onchange = atualizarPeriodoApoio;
    atualizarLimiteDataApoio();
    campoData.value = campoData.max;
    atualizarPeriodoApoio();
    document.getElementById('apoio-observacao-geral').value = '';
    fetch(`controllers/registro_apoio.php?aluno_id=${id}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível carregar os quesitos.');
        quesitosApoio = data.quesitos || [];
        renderizarQuesitosLeituraApoio(quesitosApoio);
        respostasApoio = {};
        quesitosApoio.forEach(item => { respostasApoio[item.id] = { avaliacao: null, nota: null, observacao: '' }; });
        renderizarQuesitosApoio();
        renderHistoricoApoio(data.historico || []);
        if (['master', 'aee', 'apoio'].includes(String(window.usuarioTipo || '').trim().toLowerCase())) carregarAssociacaoQuesitos(id);
      })
      .catch(error => { lista.innerHTML = `<div class="empty" style="padding:20px 0"><span style="font-size:12px">${escaparUsuario(error.message)}</span></div>`; });
  }

  function renderizarQuesitosLeituraApoio(lista) {
    const alvo = document.getElementById('apoio-quesitos-leitura-lista');
    if (!alvo) return;
    if (!lista.length) {
      alvo.innerHTML = '<div style="font-size:11px;color:var(--text-muted)">Nenhum quesito associado a este aluno.</div>';
      return;
    }
    alvo.innerHTML = lista.map(item => `<div class="apoio-quesito-leitura-item"><i class="bi bi-check2-circle"></i><span>${escaparUsuario(item.titulo)}</span></div>`).join('');
  }

  function carregarAssociacaoQuesitos(alunoId) {
    const alvo = document.getElementById('apoio-config-quesitos');
    if (!alvo) return;
    alvo.innerHTML = '<div style="padding:10px 0;color:var(--text-muted);font-size:12px">Carregando quesitos disponíveis...</div>';
    fetch(`controllers/associar_quesitos.php?aluno_id=${alunoId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível carregar os quesitos.');
        renderizarAssociacaoQuesitos(data.quesitos || []);
      })
      .catch(error => { alvo.innerHTML = `<div style="font-size:12px;color:#c62828">${escaparUsuario(error.message)}</div>`; });
  }

  function renderizarAssociacaoQuesitos(lista) {
    const alvo = document.getElementById('apoio-config-quesitos');
    if (!alvo) return;
    const isApoio = String(window.usuarioTipo || '').trim().toLowerCase() === 'apoio';
    const podeEditarPeso = String(window.usuarioTipo || '').trim().toLowerCase() === 'aee';
    const listaOrdenada = [...(Array.isArray(lista) ? lista : [])].sort((a, b) => {
      const associadoA = Number(a.associado || 0);
      const associadoB = Number(b.associado || 0);
      if (associadoA !== associadoB) return associadoB - associadoA;
      return String(a.titulo || '').localeCompare(String(b.titulo || ''));
    });
    if (!listaOrdenada.length) {
      alvo.innerHTML = '<div class="empty" style="padding:14px 0"><span style="font-size:12px">Nenhum quesito ativo cadastrado.</span></div>';
      return;
    }
    const titulo = isApoio ? 'Quesitos associados ao aluno' : 'Direcionar requisitos para este PCD';
    const descricao = isApoio ? 'Visualização dos quesitos definidos para este aluno.' : 'Selecione os requisitos que o PCD possui e defina o peso de cada um.';
    const acao = isApoio ? '' : '<button type="button" class="btn-p" style="padding:7px 12px;font-size:11px" onclick="salvarAssociacaoQuesitos()"><i class="bi bi-save2"></i> Salvar</button>';
    alvo.innerHTML = `<div style="border:1px solid var(--border);border-radius:10px;padding:12px;background:rgba(0,166,81,.04)">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px"><div><div style="font-size:12px;font-weight:700">${titulo}</div><div style="font-size:11px;color:var(--text-muted)">${descricao}</div></div>${acao}</div>
      <div style="display:flex;flex-direction:column;gap:7px">${listaOrdenada.map(item => `<label style="display:flex;align-items:center;gap:8px;padding:8px;border:1px solid var(--border);border-radius:8px;background:#fff;font-size:12px">${isApoio ? `<input type="checkbox" class="apoio-quesito-check-leitura" ${Number(item.associado) === 1 ? 'checked' : ''} disabled aria-label="${escaparUsuario(item.titulo)}">` : `<input type="checkbox" class="apoio-quesito-check" data-id="${Number(item.id)}" ${Number(item.associado) === 1 ? 'checked' : ''} style="accent-color:var(--c3)">`}<span style="flex:1">${escaparUsuario(item.titulo)}<small style="display:block;color:var(--text-muted)">${escaparUsuario(item.descricao)}</small></span>${podeEditarPeso ? `<input type="number" class="apoio-quesito-peso" data-id="${Number(item.id)}" min="1" max="10" step="1" value="${Number(item.peso || 1)}" style="width:72px;padding:6px 8px" title="Peso">` : ''}</label>`).join('')}</div>
    </div>`;
  }

  function salvarAssociacaoQuesitos() {
    if (!curSid) return;
    const isApoio = String(window.usuarioTipo || '').trim().toLowerCase() === 'apoio';
    const checks = Array.from(document.querySelectorAll('.apoio-quesito-check')).filter(check => check.checked);
    if (isApoio) {
      toast('Somente o AEE pode definir o peso dos quesitos.', 'bi-exclamation-triangle', 'amber');
      return;
    }
    const quesitos = checks.map(check => {
      const peso = document.querySelector(`.apoio-quesito-peso[data-id="${check.dataset.id}"]`);
      return { quesito_id: Number(check.dataset.id), peso: Number(peso?.value || 1) };
    });
    if (!quesitos.length) {
      toast('Selecione ao menos um quesito para este aluno.', 'bi-exclamation-triangle', 'amber');
      return;
    }
    fetch('controllers/associar_quesitos.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ aluno_id: curSid, quesitos }) })
      .then(res => res.json())
      .then(data => {
        if (!data.success) throw new Error(data.message);
        toast(data.message || 'Quesitos associados.', 'bi-check-circle', 'success');
        if (['master', 'aee'].includes(window.usuarioTipo)) carregarAssociacaoQuesitos(curSid);
        if (window.usuarioTipo === 'apoio') loadRegistrosApoio(curSid);
      })
      .catch(error => toast(error.message || 'Erro ao associar quesitos.', 'bi-x-circle', 'danger'));
  }

  function renderizarQuesitosApoio() {
    const lista = document.getElementById('apoio-quesitos');
    if (!lista) return;
    if (!quesitosApoio.length) {
      lista.innerHTML = '<div class="empty" style="padding:20px 0"><i class="bi bi-ui-checks"></i><span style="font-size:12px">Nenhum quesito associado a este aluno.</span></div>';
      return;
    }
    lista.innerHTML = quesitosApoio.map(item => {
      const resposta = respostasApoio[item.id] || { avaliacao: null, nota: null, observacao: '' };
      const avaliacao = resposta.avaliacao ? `${resposta.avaliacao === 'feliz' ? '😃' : resposta.avaliacao === 'neutro' ? '😐' : '😢'} ${resposta.avaliacao.charAt(0).toUpperCase() + resposta.avaliacao.slice(1)}` : 'Avaliar';
      return `<button type="button" class="apoio-quesito-trigger" data-apoio-quesito-id="${Number(item.id)}">
        <span><strong>${escaparUsuario(item.titulo)}</strong><small>${escaparUsuario(item.descricao)} <em>Peso: ${Number(item.peso || 1)}</em></small></span>
        <span class="apoio-quesito-status${resposta.avaliacao ? ' apoio-quesito-status--filled' : ''}">${avaliacao}<i class="bi bi-chevron-right"></i></span>
      </button>`;
    }).join('');
  }

  function abrirModalQuesito(id) {
    const item = quesitosApoio.find(quesito => Number(quesito.id) === Number(id));
    if (!item) return;
    quesitoModalAtual = Number(item.id);
    const resposta = respostasApoio[item.id] || { avaliacao: null, nota: null, observacao: '' };
    document.getElementById('apoio-modal-quesito-id').value = item.id;
    document.getElementById('apoio-modal-quesito-titulo').textContent = item.titulo || 'Quesito';
    document.getElementById('apoio-modal-quesito-descricao').textContent = item.descricao || '';
    document.getElementById('apoio-modal-quesito-peso').textContent = `Peso: ${Number(item.peso || 1)}`;
    document.getElementById('apoio-modal-observacao').value = resposta.observacao || '';
    document.querySelectorAll('.apoio-avaliacao-btn').forEach(botao => botao.classList.toggle('apoio-avaliacao-btn--active', botao.dataset.apoioAvaliacao === resposta.avaliacao));
    openM('apoio-quesito-modal');
  }

  function fecharModalQuesito() { closeM('apoio-quesito-modal'); }

  function selecionarRespostaApoio(id, valor) {
    if (!respostasApoio[id]) respostasApoio[id] = { avaliacao: null, nota: null, observacao: '' };
    respostasApoio[id].avaliacao = valor;
    document.querySelectorAll('.apoio-avaliacao-btn').forEach(botao => botao.classList.toggle('apoio-avaliacao-btn--active', botao.dataset.apoioAvaliacao === valor));
  }

  function selecionarRespostaModal(valor) {
    if (quesitoModalAtual > 0) selecionarRespostaApoio(quesitoModalAtual, valor);
  }

  document.addEventListener('click', event => {
    const quesito = event.target.closest('.apoio-quesito-trigger');
    if (quesito) {
      event.preventDefault();
      abrirModalQuesito(Number(quesito.dataset.apoioQuesitoId));
      return;
    }
    const botao = event.target.closest('.apoio-avaliacao-btn');
    if (!botao) return;
    event.preventDefault();
    event.stopPropagation();
    selecionarRespostaModal(botao.dataset.apoioAvaliacao);
  });

  function confirmarModalQuesito() {
    const id = quesitoModalAtual;
    if (!id) return;
    if (!respostasApoio[id]?.avaliacao) {
      toast('Selecione Feliz, Neutro ou Triste antes de concluir.', 'bi-exclamation-triangle', 'amber');
      return;
    }
    atualizarObservacaoApoio(id, document.getElementById('apoio-modal-observacao').value);
    fecharModalQuesito();
    renderizarQuesitosApoio();
    quesitoModalAtual = 0;
  }

  function atualizarObservacaoApoio(id, valor) {
    if (!respostasApoio[id]) respostasApoio[id] = { avaliacao: null, nota: null, observacao: '' };
    respostasApoio[id].observacao = valor;
  }

  function salvarRegistroApoio() {
    if (!curSid || !quesitosApoio.length) return;
    const faltando = quesitosApoio.filter(item => !respostasApoio[item.id]?.avaliacao);
    if (faltando.length) { toast('Avalie todos os quesitos antes de salvar.', 'bi-exclamation-triangle', 'amber'); return; }
    atualizarPeriodoApoio();
    atualizarLimiteDataApoio();
    const campoData = document.getElementById('apoio-data');
    if (!campoData.value || campoData.value > campoData.max) {
      toast('A data do registro não pode ser posterior a hoje.', 'bi-exclamation-triangle', 'amber');
      return;
    }
    const payload = {
      aluno_id: curSid,
      periodo: document.querySelector('input[name="apoio-periodo"]:checked')?.value || 'manha',
      data: campoData.value,
      observacao_geral: document.getElementById('apoio-observacao-geral').value.trim(),
      respostas: quesitosApoio.map(item => ({ quesito_id: item.id, avaliacao: respostasApoio[item.id].avaliacao, nota: null, observacao: respostasApoio[item.id].observacao }))
    };
    fetch('controllers/registro_apoio.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) })
      .then(res => res.json()).then(data => { if (!data.success) throw new Error(data.message); toast('Registro de apoio salvo!', 'bi-check-circle', 'success'); loadRegistrosApoio(curSid); })
      .catch(error => toast(error.message || 'Erro ao salvar registro.', 'bi-x-circle', 'danger'));
  }

  function renderHistoricoApoio(lista) {
    const alvo = document.getElementById('apoio-historico');
    if (!alvo) return;
    alvo.innerHTML = lista.length ? `<div style="font-size:12px;font-weight:700;color:var(--text-muted);margin-bottom:8px">Registros anteriores</div>` + lista.map(item => {
      const feliz = Number(item.total_feliz || 0);
      const triste = Number(item.total_triste || 0);
      const neutro = Number(item.total_neutro || 0);
      const pontuacao = Number(item.pontuacao || 0);
      const emoji = pontuacao > 0 ? 'bi-emoji-smile' : pontuacao < 0 ? 'bi-emoji-frown' : 'bi-emoji-neutral';
      const cor = pontuacao > 0 ? 'var(--c3)' : pontuacao < 0 ? '#c62828' : '#757575';
      return `<div class="doc-item" style="margin-bottom:8px"><span><i class="bi ${emoji}" style="color:${cor};margin-right:7px"></i>${formatDateBR(item.data)} • ${item.periodo === 'manha' ? 'Manhã' : 'Tarde'}</span><span style="font-size:11px;color:var(--text-muted)">${Number(item.total_respostas || 0)} quesito(s) • ${pontuacao > 0 ? '+' : ''}${pontuacao} pontos</span></div>`;
    }).join('') : '';
  }
 

  function savePEI() {
    if (!curSid) return;
    const payload = {
      aluno_id: curSid,
      nome: document.getElementById('pei-nome').value.trim(),
      dataElaboracao: document.getElementById('pei-data').value,
      responsavel: document.getElementById('pei-responsavel').value.trim(),
      objetivos: document.getElementById('pei-objetivos').value.trim(),
      habilidades: document.getElementById('pei-habilidades').value.trim(),
      estrategias: document.getElementById('pei-estrategias').value.trim(),
      recursos: document.getElementById('pei-recursos').value.trim(),
      adaptacoes: document.getElementById('pei-adaptacoes').value.trim(),
      avaliacao: document.getElementById('pei-avaliacao').value.trim(),
      observacoes: document.getElementById('pei-observacoes').value.trim()
    };
    if (!payload.nome || !payload.dataElaboracao || !payload.responsavel) {
      toast('Informe a matéria escolar, a data de elaboração e o responsável pelo preenchimento.','bi-exclamation-triangle','amber');
      return;
    }
    fetch('controllers/salvar_pei.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        toast(`${payload.nome} salvo!`,'bi-check-circle','success');
        loadPEI(curSid);
        ['pei-nome','pei-data','pei-responsavel','pei-objetivos','pei-habilidades','pei-estrategias','pei-recursos','pei-adaptacoes','pei-avaliacao','pei-observacoes']
          .forEach(id=>{const el=document.getElementById(id); if(el) el.value='';});
      } else {
        toast(data.message || 'Erro ao salvar o PEI.','bi-x-circle','danger');
      }
    })
    .catch(() => toast('Erro de conexão ao salvar o PEI.','bi-x-circle','danger'));
  }

  function savePAEE() {
  if (!curSid) return;
  const payload = {
    aluno_id: curSid,
    nome: document.getElementById('paee-nome').value.trim(),
    dataAtendimento: document.getElementById('paee-data').value,
    responsavel: document.getElementById('paee-responsavel').value.trim(),
    necessidades: document.getElementById('paee-necessidades').value.trim(),
    objetivos: document.getElementById('paee-objetivos').value.trim(),
    recursos: document.getElementById('paee-recursos').value.trim(),
    estrategias: document.getElementById('paee-estrategias').value.trim(),
    tecnologias: document.getElementById('paee-tecnologias').value.trim(),
    resultados: document.getElementById('paee-resultados').value.trim(),
    encaminhamentos: document.getElementById('paee-encaminhamentos').value.trim(),
    observacoes: document.getElementById('paee-observacoes').value.trim()
  };

  if (!payload.nome || !payload.dataAtendimento || !payload.responsavel) {
    toast('Informe o nome do documento, a data do atendimento e o profissional responsável.','bi-exclamation-triangle','amber');
    return;
  }

  fetch('controllers/salvar_paee.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      toast(`${payload.nome} salvo!`,'bi-check-circle','success');
        loadPAEE(curSid);
        ['paee-nome','paee-data','paee-responsavel','paee-necessidades','paee-objetivos','paee-recursos','paee-estrategias','paee-tecnologias','paee-resultados','paee-encaminhamentos','paee-observacoes']
          .forEach(id=>{const el=document.getElementById(id); if(el) el.value='';});
    } else {
      toast(data.message || 'Erro ao salvar o PAEE.','bi-x-circle','danger');
    }
  })
  .catch(() => toast('Erro de conexão ao salvar o PAEE.','bi-x-circle','danger'));
}
  function handleLaudoUpload() {
    if (!curSid) return;
    const input = document.getElementById('lau-file');
    const file = input && input.files && input.files[0];
    if (!file) {
      toast('Selecione um arquivo PDF para enviar.','bi-file-earmark-pdf','amber');
      return;
    }
    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
    if (!isPdf) {
      toast('Apenas arquivos PDF são aceitos.','bi-file-earmark-pdf','amber');
      return;
    }
    const formData = new FormData();
    formData.append('aluno_id', curSid);
    formData.append('arquivo', file);
    fetch('controllers/salvar_laudo.php', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        input.value = '';
        renderLaudos(curSid);
        toast('Laudo enviado com sucesso!','bi-check-circle','success');
      } else {
        toast(data.message || 'Erro ao salvar o laudo.','bi-x-circle','danger');
      }
    })
    .catch(() => toast('Erro de conexão ao enviar o laudo.','bi-x-circle','danger'));
  }

  function renderLaudos(id) {
    const list = document.getElementById('lau-list');
    if (!list) return;
    fetch(`controllers/listar_laudos.php?aluno_id=${id}`)
      .then(r => r.json())
      .then(docs => {
        if (!Array.isArray(docs) || docs.length === 0) {
          list.innerHTML = `
            <div class="empty" style="padding:26px 0">
              <i class="bi bi-file-earmark-pdf" style="font-size:34px;margin-bottom:8px"></i>
              <span style="font-size:13px;font-weight:600">Nenhum laudo enviado</span>
              <span style="font-size:11.5px;margin-top:3px">Os laudos enviados aparecerão aqui.</span>
            </div>`;
          return;
        }
        list.innerHTML = docs.map(doc => {
          const url = doc.url || `uploads/laudos/${encodeURIComponent(doc.arquivo || '')}`;
          return `
            <div class="doc-item" style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);">
              <div style="display:flex;gap:10px;align-items:center">
                <i class="bi bi-file-earmark-pdf" style="font-size:18px;color:var(--peach)"></i>
                <div>
                  <div style="font-size:13px;font-weight:600">${doc.arquivo || 'Laudo PDF'}</div>
                  <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Salvo no servidor</div>
                </div>
              </div>
              <div style="display:flex;gap:8px;align-items:center">
                <a href="${url}" target="_blank" class="btn-g" style="padding:6px 12px;font-size:12px">Abrir PDF</a>
                ${String(window.usuarioTipo || '').trim().toLowerCase() !== 'apoio' ? `<button onclick="deleteLaudo(${doc.id})" class="btn-g" style="padding:6px 12px;font-size:12px;background:rgba(255,0,0,0.06);border-color:rgba(255,0,0,0.12)">Excluir</button>` : ''}
              </div>
            </div>`;
        }).join('');
      })
      .catch(() => {
        list.innerHTML = '<div class="empty" style="padding:26px 0"><span style="font-size:12px">Não foi possível carregar os laudos.</span></div>';
      });
  }

let planosEstado = {
  pei: { etapa: 'alunos', aluno: null, disciplina: null, pei: null, registro: null, token: 0 },
  paee: { etapa: 'alunos', aluno: null, disciplina: null, pei: null, registro: null, token: 0 }
};

function escaparPlano(valor) {
  return String(valor ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[c]));
}

function abrirModuloPlano(modulo, alunoId = null) {
  const estado = planosEstado[modulo];
  estado.etapa = 'alunos'; estado.aluno = null; estado.disciplina = null; estado.registro = null; estado.token += 1;
  document.getElementById(`plano-${modulo}-voltar`)?.style.setProperty('display', 'none');
  if (alunoId != null && Number(alunoId) > 0) {
    selecionarAlunoPlano(modulo, Number(alunoId));
    return;
  }
  carregarAlunosPlano(modulo, estado.token);
}

function carregarAlunosPlano(modulo, token) {
  const alvo = document.getElementById(`plano-${modulo}-conteudo`);
  if (!alvo) return;
  alvo.innerHTML = '<div class="empty" style="padding:30px 0"><i class="bi bi-hourglass-split"></i><span>Carregando alunos...</span></div>';
  fetch('controllers/recurso_planos.php?action=alunos')
    .then(r => r.json())
    .then(data => {
      if (planosEstado[modulo].token !== token) return;
      if (!data.success) throw new Error(data.message || 'Não foi possível carregar os alunos.');
      if (!data.alunos.length) { alvo.innerHTML = '<div class="empty" style="padding:30px 0"><i class="bi bi-person-x"></i><span>Nenhum aluno disponível para este módulo.</span></div>'; return; }
      if (window.usuarioTipo === 'apoio') { selecionarAlunoPlano(modulo, data.alunos[0].id); return; }
      alvo.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px">${data.alunos.map(aluno => `<button type="button" class="card" style="padding:16px;text-align:left;cursor:pointer" onclick="selecionarAlunoPlano('${modulo}',${Number(aluno.id)})"><div style="font-weight:700">${escaparPlano(aluno.nome)}</div><div style="font-size:11px;color:var(--text-muted);margin-top:5px">Matrícula: ${escaparPlano(aluno.matricula || '—')} · Turma: ${escaparPlano(aluno.turma || '—')}</div></button>`).join('')}</div>`;
    })
    .catch(error => { alvo.innerHTML = `<div class="empty" style="padding:30px 0"><i class="bi bi-exclamation-circle"></i><span>${escaparPlano(error.message)}</span></div>`; });
}

function selecionarAlunoPlano(modulo, alunoId) {
  const estado = planosEstado[modulo]; estado.aluno = { id: alunoId }; estado.disciplina = null; estado.pei = null; estado.registro = null; estado.etapa = 'disciplinas'; estado.token += 1;
  const token = estado.token;
  const alvo = document.getElementById(`plano-${modulo}-conteudo`);
  document.getElementById(`plano-${modulo}-voltar`)?.style.setProperty('display', window.usuarioTipo === 'apoio' ? 'none' : 'inline-flex');
  alvo.innerHTML = `<div class="empty" style="padding:30px 0"><i class="bi bi-hourglass-split"></i><span>Carregando ${modulo === 'paee' ? 'PEIs' : 'disciplinas'}...</span></div>`;
  if (modulo === 'paee') { carregarPeisParaPaee(estado, token, alvo); return; }
  fetch(`controllers/recurso_planos.php?action=disciplinas&aluno_id=${Number(alunoId)}`)
    .then(r => r.json()).then(data => {
      if (estado.token !== token) return;
      if (!data.success) throw new Error(data.message || 'Falha ao consultar disciplinas.');
      estado.aluno = data.aluno;
      document.getElementById(`plano-${modulo}-contexto`).textContent = `${data.aluno.nome} · selecione uma disciplina`;
      const gestao = window.usuarioTipo !== 'apoio' ? `<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px"><input class="inp" id="nova-disciplina-${modulo}" placeholder="Nova disciplina"><button class="btn-p" type="button" onclick="cadastrarDisciplinaPlano('${modulo}')"><i class="bi bi-plus-lg"></i> Cadastrar e associar</button></div>` : '';
      const vazio = '<div class="empty" style="padding:26px 0"><i class="bi bi-journal-x"></i><span>Este aluno não possui disciplinas associadas.</span></div>';
      alvo.innerHTML = gestao + (data.disciplinas.length ? `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px">${data.disciplinas.map(d => `<button type="button" class="card" style="padding:16px;text-align:left;cursor:pointer" onclick="selecionarDisciplinaPlano('${modulo}',${Number(d.id)})"><div style="font-weight:700">${escaparPlano(d.nome)}</div><div style="font-size:11px;color:var(--text-muted);margin-top:7px">${modulo === 'pei' ? (Number(d.tem_pei) ? 'PEI cadastrado' : 'PEI ainda não cadastrado') : (Number(d.tem_paee) ? 'PAEE cadastrado' : 'PAEE ainda não cadastrado')}</div></button>`).join('')}</div>` : vazio);
    }).catch(error => { alvo.innerHTML = `<div class="empty" style="padding:30px 0"><i class="bi bi-exclamation-circle"></i><span>${escaparPlano(error.message)}</span></div>`; });
}

function carregarPeisParaPaee(estado, token, alvo) {
  fetch(`controllers/recurso_planos.php?action=peis_paee&aluno_id=${Number(estado.aluno.id)}`)
    .then(r => r.json()).then(data => {
      if (estado.token !== token) return;
      if (!data.success) throw new Error(data.message || 'Falha ao consultar os PEIs.');
      if (!data.peis.length) { alvo.innerHTML = '<div class="empty" style="padding:26px 0"><i class="bi bi-journal-x"></i><span>Nenhum PEI com disciplina foi cadastrado para este aluno.</span></div>'; return; }
      if (data.aluno) estado.aluno = data.aluno;
      document.getElementById('plano-paee-contexto').textContent = `${estado.aluno.nome} · selecione um PEI`;
      alvo.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:12px">${data.peis.map(pei => `<button type="button" class="card" style="padding:16px;text-align:left;cursor:pointer" onclick="selecionarPeiParaPaee(${Number(pei.id)},${Number(pei.aluno_id)},${Number(pei.disciplina_id)})"><div style="font-weight:700">PEI · ${escaparPlano(pei.disciplina_nome)}</div><div style="font-size:11px;color:var(--text-muted);margin-top:7px">Elaborado em: ${escaparPlano(formatDateBR(pei.data_elaboracao))}</div><div style="font-size:11px;color:var(--text-muted);margin-top:4px">${Number(pei.tem_paee) ? 'PAEE cadastrado' : 'Criar PAEE para este PEI'}</div></button>`).join('')}</div>`;
    }).catch(error => { alvo.innerHTML = `<div class="empty" style="padding:30px 0"><i class="bi bi-exclamation-circle"></i><span>${escaparPlano(error.message)}</span></div>`; });
}

function selecionarPeiParaPaee(peiId, alunoId, disciplinaId) {
  const estado = planosEstado.paee;
  estado.aluno = estado.aluno || { id: alunoId }; estado.disciplina = { id: disciplinaId }; estado.pei = { id: peiId }; estado.registro = null; estado.etapa = 'documento'; estado.token += 1;
  const token = estado.token; const alvo = document.getElementById('plano-paee-conteudo');
  fetch(`controllers/recurso_planos.php?action=historico&modulo=paee&aluno_id=${Number(alunoId)}&disciplina_id=${Number(disciplinaId)}&pei_id=${Number(peiId)}`).then(r=>r.json()).then(data => {
    if (estado.token !== token) return;
    if (!data.success) throw new Error(data.message || 'Falha ao consultar o PAEE.');
    const historico = data.registros || []; estado.registro = historico[0] || null;
    document.getElementById('plano-paee-contexto').textContent = `${estado.aluno.nome} · ${historico[0]?.disciplina_nome || 'Disciplina do PEI'}`;
    alvo.innerHTML = renderDocumentoPlano('paee', estado.registro, historico);
  }).catch(error => { alvo.innerHTML = `<div class="empty" style="padding:30px 0"><i class="bi bi-exclamation-circle"></i><span>${escaparPlano(error.message)}</span></div>`; });
}

function cadastrarDisciplinaPlano(modulo) {
  const estado = planosEstado[modulo]; const input = document.getElementById(`nova-disciplina-${modulo}`); const nome = input?.value.trim();
  if (!nome || !estado.aluno) return toast('Informe o nome da disciplina.', 'bi-exclamation-circle', 'amber');
  fetch('controllers/recurso_planos.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ action:'cadastrar_disciplina', aluno_id:estado.aluno.id, nome }) }).then(r=>r.json()).then(data => { if (!data.success) throw new Error(data.message); toast(data.message, 'bi-check-circle', 'success'); selecionarAlunoPlano(modulo, estado.aluno.id); }).catch(error => toast(error.message, 'bi-x-circle', 'danger'));
}

function selecionarDisciplinaPlano(modulo, disciplinaId) {
  const estado = planosEstado[modulo]; estado.disciplina = { id: disciplinaId }; estado.registro = null; estado.etapa = 'documento'; estado.token += 1; const token = estado.token; const alvo = document.getElementById(`plano-${modulo}-conteudo`);
  fetch(`controllers/recurso_planos.php?action=historico&modulo=${modulo}&aluno_id=${estado.aluno.id}&disciplina_id=${disciplinaId}`).then(r=>r.json()).then(data => { if (estado.token !== token) return; if (!data.success) throw new Error(data.message); const historico = data.registros || []; const recente = historico[0]; estado.registro = recente || null; document.getElementById(`plano-${modulo}-contexto`).textContent = `${estado.aluno.nome} · ${recente?.disciplina_nome || 'Disciplina selecionada'}`; alvo.innerHTML = renderDocumentoPlano(modulo, recente, historico); }).catch(error => { alvo.innerHTML = `<div class="empty" style="padding:30px 0"><i class="bi bi-exclamation-circle"></i><span>${escaparPlano(error.message)}</span></div>`; });
}

function renderDocumentoPlano(modulo, registro, historico) {
  const apoio = window.usuarioTipo === 'apoio'; const prefixo = modulo === 'pei' ? 'pei' : 'paee';
  const campos = modulo === 'pei' ? [['data_elaboracao','Data de elaboração'],['responsavel_preenchimento','Responsável'],['objetivos_pedagogicos','Objetivos pedagógicos'],['habilidades_desenvolver','Habilidades a desenvolver'],['estrategias_ensino','Estratégias de ensino'],['recursos_acessibilidade','Recursos de acessibilidade'],['adaptacoes_curriculares','Adaptações curriculares'],['criterios_avaliacao','Critérios de avaliação'],['observacoes','Observações']] : [['data_atendimento','Data do atendimento'],['profissional_responsavel','Profissional responsável'],['necessidades_identificadas','Necessidades identificadas'],['objetivos_atendimento','Objetivos do atendimento'],['recursos_utilizados','Recursos utilizados'],['estrategias_aplicadas','Estratégias aplicadas'],['tecnologias_assistivas','Tecnologias assistivas'],['resultados_observados','Resultados observados'],['encaminhamentos','Encaminhamentos'],['observacoes_gerais','Observações gerais']];
  const form = registro ? campos.map(([campo, label]) => `<div style="margin-bottom:12px"><label class="lbl">${label}</label><div class="inp" style="min-height:42px;white-space:pre-wrap">${escaparPlano(registro[campo] || '—')}</div></div>`).join('') : '<div class="empty" style="padding:20px 0"><i class="bi bi-file-earmark-text"></i><span>Este plano ainda não foi cadastrado.</span></div>';
  const historicoHtml = historico.length > 1 ? `<div style="margin-top:18px"><div style="font-weight:700;font-size:13px;margin-bottom:8px">Documentos anteriores</div>${historico.slice(1).map(item => `<button type="button" class="btn-g" style="margin:0 6px 6px 0" onclick="abrirHistoricoPlano('${modulo}',${Number(item.id)})">${escaparPlano(formatDateBR(item.data_elaboracao || item.data_atendimento))} #${Number(item.id)}</button>`).join('')}</div>` : '';
  const estado = planosEstado[modulo];
  return `<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap"><div><div style="font-size:14px;font-weight:700">${prefixo === 'pei' ? 'PEI' : 'PAEE'} · ${escaparPlano(estado.aluno.nome)}</div><div style="font-size:11px;color:var(--text-muted)">${escaparPlano(registro?.disciplina_nome || 'Disciplina do PEI')}</div></div>${!apoio ? `<button type="button" class="btn-p" onclick="editarPlano('${modulo}',${registro ? Number(registro.id) : 0})"><i class="bi bi-pencil"></i> ${registro ? 'Editar' : 'Criar documento'}</button>` : ''}</div>${form}${historicoHtml}`;
}

function editarPlano(modulo, id) {
  const estado = planosEstado[modulo]; const registro = id ? estado.registro : null;
  const campos = modulo === 'pei' ? [['data_elaboracao','Data de elaboração','date'],['responsavel_preenchimento','Responsável','text'],['objetivos_pedagogicos','Objetivos pedagógicos','textarea'],['habilidades_desenvolver','Habilidades a desenvolver','textarea'],['estrategias_ensino','Estratégias de ensino','textarea'],['recursos_acessibilidade','Recursos de acessibilidade','textarea'],['adaptacoes_curriculares','Adaptações curriculares','textarea'],['criterios_avaliacao','Critérios de avaliação','textarea'],['observacoes','Observações','textarea']] : [['data_atendimento','Data do atendimento','date'],['profissional_responsavel','Profissional responsável','text'],['necessidades_identificadas','Necessidades identificadas','textarea'],['objetivos_atendimento','Objetivos do atendimento','textarea'],['recursos_utilizados','Recursos utilizados','textarea'],['estrategias_aplicadas','Estratégias aplicadas','textarea'],['tecnologias_assistivas','Tecnologias assistivas','textarea'],['resultados_observados','Resultados observados','textarea'],['encaminhamentos','Encaminhamentos','textarea'],['observacoes_gerais','Observações gerais','textarea']];
  const camposHtml = campos.map(([campo, label, tipo]) => `<div style="margin-bottom:12px"><label class="lbl">${label}</label>${tipo === 'textarea' ? `<textarea class="inp plano-campo" data-campo="${campo}" rows="3">${escaparPlano(registro?.[campo] || '')}</textarea>` : `<input class="inp plano-campo" data-campo="${campo}" type="${tipo}" value="${escaparPlano(registro?.[campo] || '')}">`}</div>`).join('');
  document.getElementById(`plano-${modulo}-conteudo`).innerHTML = `<div style="font-size:14px;font-weight:700;margin-bottom:14px">${registro ? 'Editar documento' : 'Novo documento'} · ${escaparPlano(estado.aluno.nome)}</div>${camposHtml}<button type="button" class="btn-p" onclick="salvarPlanoModulo('${modulo}',${id})"><i class="bi bi-save2"></i> Salvar</button>`;
}

function salvarPlanoModulo(modulo, id) {
  const estado = planosEstado[modulo]; const payload = { action:'salvar', modulo, id, aluno_id:estado.aluno.id, disciplina_id:estado.disciplina.id };
  if (modulo === 'paee') payload.pei_id = estado.pei?.id || 0;
  document.querySelectorAll(`#plano-${modulo}-conteudo .plano-campo`).forEach(campo => { payload[campo.dataset.campo] = campo.value; });
  fetch('controllers/recurso_planos.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) }).then(r=>r.json()).then(data => { if (!data.success) throw new Error(data.message); toast(data.message, 'bi-check-circle', 'success'); if (modulo === 'paee') selecionarPeiParaPaee(estado.pei.id, estado.aluno.id, estado.disciplina.id); else selecionarDisciplinaPlano(modulo, estado.disciplina.id); }).catch(error => toast(error.message, 'bi-x-circle', 'danger'));
}

function abrirHistoricoPlano(modulo, id) {
  fetch(`controllers/recurso_planos.php?action=abrir&modulo=${modulo}&id=${Number(id)}`).then(r=>r.json()).then(data => { if (!data.success) throw new Error(data.message); const estado = planosEstado[modulo]; estado.registro = data.registro; document.getElementById(`plano-${modulo}-conteudo`).innerHTML = renderDocumentoPlano(modulo, data.registro, [data.registro]); }).catch(error => toast(error.message, 'bi-x-circle', 'danger'));
}

function planoVoltar(modulo) {
  const estado = planosEstado[modulo]; if (estado.etapa === 'documento') { selecionarAlunoPlano(modulo, estado.aluno.id); return; } abrirModuloPlano(modulo);
}

  // ===== DELETE =====
  function openDelModal() {
    if (!curSid) return;
    const s = students.find(x=>x.id===curSid);
    document.getElementById('del-nm').textContent = s?.nome || 'este aluno';
    openM('del-modal');
  }
  function confirmDel() {

  fetch('controllers/excluir_aluno.php', {

    method: 'POST',

    headers: {
      'Content-Type': 'application/json'
    },

    body: JSON.stringify({
      id: curSid
    })

  })

  .then(response => response.json())

  .then(data => {

    if (data.success) {

      closeM('del-modal');

      toast(
        'Aluno removido',
        'bi-trash3',
        'danger'
      );

      carregarAlunos();

      go('perfis');

    } else {

      toast(
        'Erro ao remover aluno',
        'bi-x-circle',
        'danger'
      );

    }

  })

  .catch(error => {

    console.error(error);

    toast(
      'Erro de conexão',
      'bi-x-circle',
      'danger'
    );

  });

}

  // ===== CADASTRAR =====
  function mCPF(inp) {
    let v = inp.value.replace(/\D/g,'').slice(0,11);
    v = v.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2');
    inp.value = v;
  }
  function clearF() {
    ['f-nome','f-mat','f-cpf','f-curso'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';});
    limparDiagnosticosSelecionados();
    document.getElementById('f-nasc').value='';
    ['f-sexo','f-turma','f-apoio'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';});
  }

  function toggleDiagnosticoMenu() {
    const seletor = document.getElementById('diag-multiselect');
    if (!seletor) return;
    const aberto = seletor.classList.toggle('open');
    seletor.querySelector('.multi-select-toggle')?.setAttribute('aria-expanded', String(aberto));
  }

  function renderDiagnosticosSelecionados() {
    const select = document.getElementById('f-diag');
    const valores = document.getElementById('diag-selected-values');
    const menu = document.getElementById('diag-options');
    if (!select || !valores || !menu) return;

    const selecionados = Array.from(select.selectedOptions).map(option => option.value);
    valores.innerHTML = selecionados.length
      ? selecionados.map(nome => `<span class="tag ${tagC(nome)}" style="${estiloCondicao(nome)}" data-value="${escaparUsuario(nome)}" onclick="event.stopPropagation();removerDiagnostico(this.dataset.value)" title="Remover ${escaparUsuario(nome)}">${escaparUsuario(nome)} <i class="bi bi-x"></i></span>`).join('')
      : '<span class="multi-select-placeholder">Selecione uma condição geral</span>';

    menu.querySelectorAll('.multi-select-option').forEach(opcao => {
      const input = opcao.querySelector('input');
      const marcado = selecionados.includes(input.value);
      input.checked = marcado;
      opcao.classList.toggle('selected', marcado);
    });
  }

  function renderOpcoesDiagnostico(opcoes, selecionados = []) {
    const select = document.getElementById('f-diag');
    const menu = document.getElementById('diag-options');
    if (!select || !menu) return;

    select.innerHTML = opcoes.map(item => `<option value="${escaparUsuario(item.sigla || item.nome)}"${selecionados.includes(item.sigla || item.nome) ? ' selected' : ''}>${escaparUsuario(item.nome)} (${escaparUsuario(item.sigla || item.nome)})</option>`).join('');
    menu.innerHTML = opcoes.map(item => { const valor = item.sigla || item.nome; return `<label class="multi-select-option${selecionados.includes(valor) ? ' selected' : ''}" role="option"><input type="checkbox" value="${escaparUsuario(valor)}"${selecionados.includes(valor) ? ' checked' : ''} onchange="selecionarDiagnostico(this)"><span class="tag ${tagC(valor)}" style="${estiloCondicao(valor)}">${escaparUsuario(item.nome)} (${escaparUsuario(valor)})</span></label>`; }).join('');
    renderDiagnosticosSelecionados();
  }

  function selecionarDiagnostico(input) {
    const select = document.getElementById('f-diag');
    const option = Array.from(select?.options || []).find(item => item.value === input.value);
    if (option) option.selected = input.checked;
    renderDiagnosticosSelecionados();
  }

  function removerDiagnostico(nome) {
    const select = document.getElementById('f-diag');
    const option = Array.from(select?.options || []).find(item => item.value === nome);
    if (option) option.selected = false;
    renderDiagnosticosSelecionados();
  }

  function limparDiagnosticosSelecionados() {
    const select = document.getElementById('f-diag');
    if (select) Array.from(select.options).forEach(option => { option.selected = false; });
    renderDiagnosticosSelecionados();
  }

  function abrirModalCadastro() {
    carregarOpcoesAluno();
    openM('modal-cadastrar');
    setTimeout(() => document.getElementById('f-nome')?.focus(), 50);
  }

  function fecharModalCadastro() {
    closeM('modal-cadastrar');
  }

  function carregarOpcoesAluno() {
    const apoio = document.getElementById('f-apoio');
    const cursoSelect = document.getElementById('f-curso');
    const diagnosticoSelect = document.getElementById('f-diag');
    if (!apoio) return;
    return fetch('controllers/listar_opcoes_alunos.php')
      .then(response => response.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível carregar as opções.');

        const cursos = data.cursos || [];
        const diagnosticos = data.diagnosticos || [];

        if (cursoSelect) {
          const cursoAtual = cursoSelect.value;
          cursoSelect.innerHTML = '<option value="">Selecione um curso</option>' + cursos.map(item => `<option value="${escaparUsuario(item.nome)}" ${cursoAtual === item.nome ? 'selected' : ''}>${escaparUsuario(item.nome)}</option>`).join('');
        }

        if (diagnosticoSelect) {
          const diagnosticosAtuais = Array.from(diagnosticoSelect.selectedOptions).map(option => option.value);
          renderOpcoesDiagnostico(diagnosticos, diagnosticosAtuais);
        }

        apoio.innerHTML = '<option value="">Sem apoio definido</option>' + (data.apoios || []).map(item => `<option value="${item.id}">${escaparUsuario(item.nome)}</option>`).join('');
      })
      .catch(error => { apoio.innerHTML = '<option value="">Sem apoio definido</option>'; if (cursoSelect) cursoSelect.innerHTML = '<option value="">Selecione um curso</option>'; if (diagnosticoSelect) { diagnosticoSelect.innerHTML = ''; renderOpcoesDiagnostico([], []); } toast(error.message, 'bi-x-circle', 'danger'); });
  }

  function excluirOpcaoAluno(tipo, id) {
    if (!id) return;

    const nomeExibicao = tipo === 'curso' ? 'curso' : 'condição geral';
    openConfirm(`Deseja realmente excluir este ${nomeExibicao}?`, function() {
      fetch('controllers/listar_opcoes_alunos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tipo, acao: 'excluir', id })
      })
        .then(response => response.json())
        .then(data => {
          if (!data.success) throw new Error(data.message || 'Não foi possível excluir a opção.');
          toast(`${nomeExibicao.charAt(0).toUpperCase() + nomeExibicao.slice(1)} removido com sucesso!`, 'bi-trash3', 'danger');
          renderListaOpcoesAluno(tipo);
          carregarOpcoesAluno();
        })
        .catch(error => {
          console.error(error);
          toast(error.message || 'Erro ao excluir opção.', 'bi-x-circle', 'danger');
        });
    });
  }

  function renderListaOpcoesAluno(tipo) {
    const lista = document.getElementById('modal-opcao-lista');
    const subtitulo = document.getElementById('modal-opcao-subtitulo');
    if (!lista || !subtitulo) return;

    const chave = tipo === 'curso' ? 'cursos' : tipo === 'diagnostico' ? 'diagnosticos' : '';
    if (!chave) {
      lista.innerHTML = '';
      subtitulo.textContent = 'Opções cadastradas';
      return;
    }

    fetch('controllers/listar_opcoes_alunos.php')
      .then(response => response.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível carregar as opções.');

        const itens = data[chave] || [];
        subtitulo.textContent = tipo === 'curso' ? 'Cursos cadastrados' : 'Condições gerais cadastradas';

        if (!itens.length) {
          lista.innerHTML = '<div style="padding:12px 10px;border:1px dashed rgba(7,91,45,0.2);border-radius:10px;color:var(--text-muted);font-size:12px;background:rgba(255,255,255,0.02)">Nenhuma opção cadastrada ainda.</div>';
          return;
        }

        lista.innerHTML = itens.map(item => `
          <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;border:1px solid rgba(7,91,45,0.12);border-radius:10px;background:#f7faf6;box-shadow:0 1px 0 rgba(7,91,45,0.04);font-size:13px;color:#1f2a1f;">
            <span style="display:flex;align-items:center;gap:10px;font-weight:600;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
              <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:7px;background:rgba(24,154,89,0.10);color:var(--c3);flex-shrink:0;">
                ${tipo === 'curso' ? '<i class="bi bi-book-half" style="font-size:12px"></i>' : '<i class="bi bi-file-medical" style="font-size:12px"></i>'}
              </span>
              <span style="overflow:hidden;text-overflow:ellipsis;">${escaparUsuario(item.nome)}${tipo === 'diagnostico' ? ` <small style="color:var(--text-muted);font-weight:700">(${escaparUsuario(item.sigla || item.nome)})</small>` : ''}</span>
              ${tipo === 'diagnostico' && item.descricao ? `<small style="display:block;color:var(--text-muted);font-weight:400;margin-top:3px;white-space:normal">${escaparUsuario(item.descricao)}</small>` : ''}
            </span>
            <button type="button" onclick="excluirOpcaoAluno('${tipo}', ${Number(item.id)})" style="border:1px solid rgba(198,40,40,0.18);background:rgba(198,40,40,0.08);color:#c62828;border-radius:8px;padding:7px 10px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s ease;" title="Excluir ${tipo === 'curso' ? 'curso' : 'condição geral'}" onmouseover="this.style.background='rgba(198,40,40,0.14)';this.style.transform='translateY(-1px)'" onmouseout="this.style.background='rgba(198,40,40,0.08)';this.style.transform='translateY(0)';">
              <i class="bi bi-trash3" style="font-size:13px"></i>
            </button>
          </div>
        `).join('');
      })
      .catch(error => {
        console.error(error);
        lista.innerHTML = '<div style="padding:12px 10px;border:1px dashed rgba(198,40,40,0.32);border-radius:10px;color:#c62828;font-size:12px;background:rgba(198,40,40,0.04)">Não foi possível carregar a lista.</div>';
      });
  }

  function abrirModalOpcaoAluno(tipo) {
    const modal = document.getElementById('modal-opcao-aluno');
    const titulo = document.getElementById('modal-opcao-titulo');
    const input = document.getElementById('modal-opcao-nome');
    const siglaInput = document.getElementById('modal-opcao-sigla');
    const siglaWrap = document.getElementById('modal-opcao-sigla-wrap');
    const descricaoInput = document.getElementById('modal-opcao-descricao');
    const descricaoWrap = document.getElementById('modal-opcao-descricao-wrap');
    if (!modal || !titulo || !input) return;

    const label = tipo === 'curso' ? 'Curso' : tipo === 'diagnostico' ? 'Condição geral' : 'Opção';
    modal.dataset.tipo = tipo;
    titulo.textContent = `Adicionar ${label}`;
    input.value = '';
    if (siglaInput) siglaInput.value = '';
    if (descricaoInput) descricaoInput.value = '';
    if (siglaWrap) siglaWrap.style.display = tipo === 'diagnostico' ? 'block' : 'none';
    if (descricaoWrap) descricaoWrap.style.display = tipo === 'diagnostico' ? 'block' : 'none';
    input.placeholder = `Digite o nome do ${label.toLowerCase()}`;
    renderListaOpcoesAluno(tipo);
    openM('modal-opcao-aluno');
    setTimeout(() => input.focus(), 50);
  }

  function fecharModalOpcaoAluno() {
    const modal = document.getElementById('modal-opcao-aluno');
    const input = document.getElementById('modal-opcao-nome');
    const siglaInput = document.getElementById('modal-opcao-sigla');
    const descricaoInput = document.getElementById('modal-opcao-descricao');
    if (modal) {
      closeM('modal-opcao-aluno');
      delete modal.dataset.tipo;
    }
    if (input) input.value = '';
    if (siglaInput) siglaInput.value = '';
    if (descricaoInput) descricaoInput.value = '';
  }

  function salvarOpcaoAluno() {
    const modal = document.getElementById('modal-opcao-aluno');
    const tipo = modal?.dataset.tipo || '';
    const input = document.getElementById('modal-opcao-nome');
    const siglaInput = document.getElementById('modal-opcao-sigla');
    const descricaoInput = document.getElementById('modal-opcao-descricao');
    const nome = (input?.value || '').trim();
    const sigla = (siglaInput?.value || '').trim().toUpperCase();
    const descricao = (descricaoInput?.value || '').trim();

    if (!tipo || !nome || (tipo === 'diagnostico' && (!sigla || !descricao))) {
      toast('Informe nome, sigla e descrição da condição geral.', 'bi-exclamation-triangle', 'amber');
      return;
    }

    fetch('controllers/listar_opcoes_alunos.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ tipo, nome, sigla, descricao })
    })
      .then(response => response.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível salvar a opção.');

        const nomeExibicao = tipo === 'curso' ? 'Curso' : 'Condição geral';
        toast(`${nomeExibicao} adicionado com sucesso!`, 'bi-check-circle', 'success');

        input.value = '';
        if (siglaInput) siglaInput.value = '';
        if (descricaoInput) descricaoInput.value = '';
        renderListaOpcoesAluno(tipo);
        carregarOpcoesAluno().then(() => {
          const campo = tipo === 'curso' ? document.getElementById('f-curso') : document.getElementById('f-diag');
          if (!campo) return;
          const valor = tipo === 'diagnostico' ? (data.sigla || sigla) : (data.nome || nome);
          if (tipo === 'curso') {
            campo.value = valor;
          } else {
            const opcao = Array.from(campo.options).find(item => item.value === valor);
            if (opcao) opcao.selected = true;
            renderDiagnosticosSelecionados();
          }
          campo.focus();
        });
      })
      .catch(error => {
        console.error(error);
        toast(error.message || 'Erro ao salvar opção.', 'bi-x-circle', 'danger');
      });
  }

  function addStudent() {

  const nome  = document.getElementById('f-nome').value.trim();
  const mat   = document.getElementById('f-mat').value.trim();
  const cpf   = document.getElementById('f-cpf').value.trim();
  const nasc  = document.getElementById('f-nasc').value;
  const sexo  = document.getElementById('f-sexo').value;
  const turma = document.getElementById('f-turma').value;
  const curso = document.getElementById('f-curso').value;
  const diag = Array.from(document.getElementById('f-diag').selectedOptions).map(option => option.value).filter(Boolean);
  const apoioId = document.getElementById('f-apoio').value;

  if (!nome || !mat || !cpf || !nasc || !sexo || !turma || !curso || !diag.length) {
toast(
    'Preencha todos os campos!',
    'bi-exclamation-triangle',
    'amber'
  );

  return;

}
  // enviar JSON simples (foto removida)
  fetch('controllers/salvar_aluno.php', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nome: nome, matricula: mat, cpf: cpf, data_nascimento: nasc, sexo: sexo, turma: turma, curso: curso, diagnostico: diag, id_apoio: apoioId || null })
  }).then(response => response.json()).then(data => handleAlunoSaveResponse(data, nome)).catch(error => { console.error(error); toast('Erro de conexão com o servidor','bi-x-circle','danger'); });

}

function handleAlunoSaveResponse(data, nome) {
  if (data.success) {
    carregarAlunos();
    clearF();
    fecharModalCadastro();
    toast(`"${nome}" cadastrado com sucesso!`,'bi-check-circle','success');
    addNotif(`Novo aluno: ${nome}`);
    setTimeout(() => go('perfis'), 1100);
  } else {
    toast(data.message || 'Erro ao salvar no banco','bi-x-circle','danger');
  }
}

  // ===== CALENDARIO =====
  const MNS = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
  function renderCal() {
    const y=calDate.getFullYear(), m=calDate.getMonth();
    document.getElementById('cal-title').textContent = `${MNS[m]} ${y}`;
    const fd = new Date(y,m,1).getDay(), dim = new Date(y,m+1,0).getDate();
    const off = fd===0?6:fd-1;
    const today = new Date(); today.setHours(0,0,0,0);
    const grid = document.getElementById('cal-grid'); grid.innerHTML='';
    for(let i=0;i<off;i++){const c=document.createElement('div');c.className='cal-cell';c.style.background='rgba(0,0,0,0.1)';grid.appendChild(c);}
    for(let d=1;d<=dim;d++){
      const ds=`${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const isTd = new Date(ds+'T00:00').getTime()===today.getTime();
      const evs = (events||[]).filter(e=>e.data_evento===ds || e.date===ds);
      const cell=document.createElement('div'); cell.className='cal-cell'+(isTd?' today-cell':'');
      cell.onclick=()=>quickEv(ds);
      const dn=document.createElement('div'); dn.className='cal-day'+(isTd?' today':''); dn.textContent=d; cell.appendChild(dn);
      evs.forEach(ev=>{
        const chip=document.createElement('span');chip.className='ev-chip';
        const label = (ev.horario||ev.time? (ev.horario||ev.time)+' ':'') + (ev.titulo||ev.title||'');
        chip.textContent = label;
        chip.onclick = e=>{ e.stopPropagation(); toast(label); };
        // pequeno botão de excluir dentro do chip
        const xb = document.createElement('button'); xb.textContent='✕'; xb.style.marginLeft='8px'; xb.style.background='transparent'; xb.style.border='none'; xb.style.color='rgba(255,255,255,0.5)'; xb.style.cursor='pointer'; xb.onclick = evv=>{ evv.stopPropagation(); openConfirm('Deseja realmente excluir este evento?', function(){ deleteEvento(ev.id); }); };
        chip.appendChild(xb);
        cell.appendChild(chip);
      });
      grid.appendChild(cell);
    }
  }
  function prevM(){calDate.setMonth(calDate.getMonth()-1);renderCal();}
  function nextM(){calDate.setMonth(calDate.getMonth()+1);renderCal();}
  function goToday(){calDate=new Date();renderCal();}
  function openEvModal(){document.getElementById('ev-dat').value=new Date().toISOString().split('T')[0];openM('ev-modal');setTimeout(()=>document.getElementById('ev-tit').focus(),50);}
  function quickEv(ds){document.getElementById('ev-dat').value=ds;openM('ev-modal');setTimeout(()=>document.getElementById('ev-tit').focus(),50);}
  function saveEv(){
    const t=document.getElementById('ev-tit').value.trim(),d=document.getElementById('ev-dat').value,h=document.getElementById('ev-hor').value,desc=document.getElementById('ev-desc').value.trim();
    if(!t||!d){toast('Título e data são obrigatórios!','bi-exclamation-triangle','amber');return;}
    fetch('controllers/salvar_evento.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ titulo:t, data_evento:d, horario:h || null, descricao: desc }) })
      .then(r=>r.json()).then(res=>{
        if(res.success){ closeM('ev-modal'); loadEventos(); toast('Evento criado!'); addNotif(`Evento: ${t}`); ['ev-tit','ev-hor','ev-desc'].forEach(id=>{const e=document.getElementById(id); if(e) e.value='';}); }
        else toast(res.message||'Erro ao salvar evento','bi-x-circle','danger');
      }).catch(()=>toast('Erro de conexão','bi-x-circle','danger'));
  }

  function loadEventos(){
    fetch('controllers/listar_eventos.php')
      .then(r=>r.json())
      .then(data=>{
        events = Array.isArray(data)?data:[];
        // atualizar views
        renderCal();
        refreshDash();
      })
      .catch(()=>{
        events = [];
        renderCal();
        refreshDash();
      });
  }

  function deleteEvento(id){
    fetch('controllers/excluir_evento.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}) })
      .then(r=>r.json()).then(res=>{ if(res.success){ toast('Evento removido','bi-trash3','danger'); loadEventos(); } else toast(res.message||'Erro ao remover evento','bi-x-circle','danger'); })
      .catch(()=>toast('Erro de conexão','bi-x-circle','danger'));
  }

  // ===== USUÁRIOS =====
  let usuariosGerenciados = [];

  function escaparUsuario(valor) {
    return String(valor ?? '').replace(/[&<>'"]/g, caractere => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[caractere]));
  }

  function carregarUsuarios() {
    const tabela = document.getElementById('usuarios-tabela');
    if (!tabela) return;
    const pesquisa = document.getElementById('usuarios-busca')?.value || '';
    const ativo = document.getElementById('usuarios-status')?.value || '';
    const query = new URLSearchParams({ action: 'listar_usuarios', pesquisa, ativo });
    fetch(`controllers/usuario_controller.php?${query}`)
      .then(res => res.json())
      .then(data => {
        if (!data.sucesso) throw new Error(data.erro || 'Não foi possível carregar os usuários.');
        usuariosGerenciados = data.usuarios || [];
        renderizarUsuarios(usuariosGerenciados);
        renderizarSolicitacoes(data.solicitacoes || []);
      })
      .catch(error => toast(error.message, 'bi-x-circle', 'danger'));
  }

  function renderizarUsuarios(lista) {
    const tabela = document.getElementById('usuarios-tabela');
    const vazio = document.getElementById('usuarios-vazio');
    const total = document.getElementById('usuarios-total');
    if (!tabela) return;
    total.textContent = `(${lista.length})`;
    vazio.style.display = lista.length ? 'none' : 'flex';
    tabela.innerHTML = lista.map(usuario => {
      const ativo = Number(usuario.ativo) === 1;
      const podeAlterar = true;
      return `<tr class="trow">
        <td style="padding:12px 20px"><div style="display:flex;align-items:center;gap:9px"><div class="av" style="width:32px;height:32px;font-size:12px">${escaparUsuario(usuario.nome).charAt(0).toUpperCase()}</div><span style="font-weight:600;font-size:13px">${escaparUsuario(usuario.nome)}</span></div></td>
        <td style="padding:12px 20px;font-size:12px">${escaparUsuario(formatarCpfUsuario(usuario.cpf))}</td>
        <td style="padding:12px 20px;font-size:12px">${escaparUsuario(formatarTelefoneUsuario(usuario.telefone))}</td>
        <td style="padding:12px 20px;font-size:12px">${escaparUsuario(usuario.email)}</td>
        <td style="padding:12px 20px;font-size:12px;color:var(--c2);font-weight:600">${escaparUsuario(rotuloTipoUsuario(usuario.tipo))}</td>
        <td style="padding:12px 20px"><span class="tag ${ativo ? 't-tdah' : 't-out'}">${ativo ? 'Ativo' : 'Inativo'}</span></td>
        <td style="padding:12px 20px;text-align:right;white-space:nowrap">${podeAlterar ? `<button class="btn-g" style="padding:6px 8px" title="Editar" onclick="abrirEditorUsuario(${usuario.id})"><i class="bi bi-pencil"></i></button><button class="btn-g" style="padding:6px 8px" title="Ativar ou desativar" onclick="alternarUsuario(${usuario.id},${ativo ? 0 : 1})"><i class="bi bi-power"></i></button><button class="btn-g" style="padding:6px 8px;color:#c62828" title="Excluir" onclick="excluirUsuario(${usuario.id})"><i class="bi bi-trash3"></i></button>` : '<span style="font-size:11px;color:var(--text-muted)">Conta atual</span>'}</td>
      </tr>`;
    }).join('');
  }

  function rotuloTipoUsuario(tipo) {
    return {master:'Master', aee:'AEE', apoio:'Apoio', responsavel:'Responsável'}[tipo] || tipo;
  }

  function formatarCpfUsuario(valor) {
    const digits = String(valor || '').replace(/\D/g, '');
    return digits.length === 11 ? digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') : (valor || '—');
  }

  function formatarTelefoneUsuario(valor) {
    const digits = String(valor || '').replace(/\D/g, '');
    if (digits.length === 11) return digits.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    if (digits.length === 10) return digits.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    return valor || '—';
  }

  function renderizarSolicitacoes(lista) {
    const tabela = document.getElementById('usuarios-solicitacoes');
    const vazio = document.getElementById('usuarios-solicitacoes-vazio');
    const contador = document.getElementById('usuarios-pendentes');
    if (!tabela) return;
    contador.textContent = `${lista.length} pendente(s)`;
    vazio.style.display = lista.length ? 'none' : 'flex';
    tabela.innerHTML = lista.map(usuario => `<tr class="trow"><td style="padding:10px;font-size:12px;font-weight:600">${escaparUsuario(usuario.nome)}</td><td style="padding:10px;font-size:12px">${escaparUsuario(formatarCpfUsuario(usuario.cpf))}</td><td style="padding:10px;font-size:12px">${escaparUsuario(formatarTelefoneUsuario(usuario.telefone))}</td><td style="padding:10px;font-size:12px">${escaparUsuario(usuario.email)}</td><td style="padding:10px;font-size:12px;color:var(--c2);font-weight:600">${escaparUsuario(rotuloTipoUsuario(usuario.tipo))}</td><td style="padding:10px;text-align:right;white-space:nowrap"><button class="btn-p" style="padding:6px 9px;font-size:11px" onclick="decidirUsuario(${usuario.id},'aprovar')"><i class="bi bi-check"></i></button> <button class="btn-g" style="padding:6px 9px;font-size:11px;color:#c62828" onclick="decidirUsuario(${usuario.id},'recusar')"><i class="bi bi-x"></i></button></td></tr>`).join('');
  }

  function abrirEditorUsuario(id = 0) {
    document.getElementById('usuario-editor').classList.add('open');
    document.getElementById('usuario-editor-titulo').textContent = id ? 'Editar Usuário' : 'Novo Usuário';
    document.getElementById('usuario-id').value = id || '';
    document.getElementById('usuario-senha').value = '';
    document.getElementById('usuario-senha-ajuda').textContent = id ? '(opcional)' : '*';
    const usuario = usuariosGerenciados.find(item => Number(item.id) === Number(id));
    document.getElementById('usuario-nome').value = usuario?.nome || '';
    document.getElementById('usuario-email').value = usuario?.email || '';
    document.getElementById('usuario-tipo').value = usuario?.tipo || 'responsavel';
  }

  function fecharEditorUsuario() { document.getElementById('usuario-editor')?.classList.remove('open'); }

  function atualizarAvataresPerfil(foto) {
    const inicial = (document.getElementById('perfil-nome')?.textContent || 'U').trim().charAt(0).toUpperCase();
    ['topbar-avatar', 'perfil-avatar'].forEach(id => {
      const avatar = document.getElementById(id);
      if (!avatar) return;
      avatar.innerHTML = foto ? `<img src="${escaparUsuario(foto)}?v=${Date.now()}" alt="Foto de perfil" style="width:100%;height:100%;object-fit:cover;border-radius:50%">` : inicial;
    });
    const remover = document.getElementById('btn-remover-foto');
    if (remover) remover.disabled = !foto;
  }

  function abrirSeletorFoto() { document.getElementById('input-foto-perfil')?.click(); }

  document.getElementById('input-foto-perfil')?.addEventListener('change', event => {
    const arquivo = event.target.files?.[0];
    if (!arquivo) return;
    const dados = new FormData();
    dados.append('foto', arquivo);
    fetch('controllers/usuario_controller.php?action=upload_foto', {method:'POST', body:dados})
      .then(res => res.json()).then(data => { if (!data.sucesso) throw new Error(data.erro); atualizarAvataresPerfil(data.foto); toast('Foto atualizada com sucesso!'); event.target.value = ''; }).catch(error => toast(error.message, 'bi-x-circle', 'danger'));
  });

  function abrirModalRemoverFoto() {
    if (document.getElementById('btn-remover-foto')?.disabled) return;
    openM('modal-remover-foto');
  }

  function fecharModalRemoverFoto() { closeM('modal-remover-foto'); }

  function removerFotoPerfil() {
    fetch('controllers/usuario_controller.php?action=remover_foto', {method:'POST'})
      .then(res => res.json()).then(data => { if (!data.sucesso) throw new Error(data.erro); fecharModalRemoverFoto(); atualizarAvataresPerfil(''); toast('Foto removida com sucesso!', 'bi-trash3', 'danger'); }).catch(error => toast(error.message, 'bi-x-circle', 'danger'));
  }

  function salvarUsuario() {
    const id = Number(document.getElementById('usuario-id').value || 0);
    const dados = { id, nome: document.getElementById('usuario-nome').value.trim(), email: document.getElementById('usuario-email').value.trim(), tipo: document.getElementById('usuario-tipo').value, senha: document.getElementById('usuario-senha').value };
    const action = id ? 'atualizar_usuario' : 'cadastrar_usuario';
    fetch(`controllers/usuario_controller.php?action=${action}`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(dados)})
      .then(res => res.json()).then(data => { if (!data.sucesso) throw new Error(data.erro); fecharEditorUsuario(); toast(data.message || 'Operação realizada!'); carregarUsuarios(); }).catch(error => toast(error.message || 'Erro ao salvar usuário.', 'bi-x-circle', 'danger'));
  }

  function decidirUsuario(id, decisao) {
    fetch('controllers/usuario_controller.php?action=decidir_usuario', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id, decisao})})
      .then(res => res.json()).then(data => { if (!data.sucesso) throw new Error(data.erro); toast(data.message || 'Solicitação atualizada!'); carregarUsuarios(); }).catch(error => toast(error.message, 'bi-x-circle', 'danger'));
  }

  function alternarUsuario(id, ativo) {
    openConfirm(`${ativo ? 'Ativar' : 'Desativar'} este usuário?`, function() {
      fetch('controllers/usuario_controller.php?action=alternar_usuario', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id, ativo})})
        .then(res => res.json()).then(data => { if (!data.sucesso) throw new Error(data.erro); toast('Status atualizado!'); carregarUsuarios(); }).catch(error => toast(error.message, 'bi-x-circle', 'danger'));
    });
  }

  function excluirUsuario(id) {
    openConfirm('Excluir este usuário? Esta ação não pode ser desfeita.', function() {
      fetch('controllers/usuario_controller.php?action=excluir_usuario', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id})})
        .then(res => res.json()).then(data => { if (!data.sucesso) throw new Error(data.erro); toast('Usuário excluído!', 'bi-trash3', 'danger'); carregarUsuarios(); }).catch(error => toast(error.message, 'bi-x-circle', 'danger'));
    });
  }

  // ===== QUESITOS DE AVALIAÇÃO =====
  let condicoesQuesitos = [];
  let quesitosGerenciados = [];

  function carregarQuesitos() {
    const tabela = document.getElementById('quesitos-tabela');
    if (!tabela) return;
    fetch('controllers/quesitos_avaliacao.php')
      .then(res => res.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível carregar os quesitos.');
        quesitosGerenciados = data.quesitos || [];
        renderizarQuesitos(quesitosGerenciados);
      })
      .catch(error => toast(error.message, 'bi-x-circle', 'danger'));
  }

  function renderizarQuesitos(lista) {
    const tabela = document.getElementById('quesitos-tabela');
    const vazio = document.getElementById('quesitos-vazio');
    const total = document.getElementById('quesitos-total');
    if (!tabela) return;
    total.textContent = `(${lista.length})`;
    vazio.style.display = lista.length ? 'none' : 'flex';
    tabela.innerHTML = lista.map(quesito => {
      const ativo = Number(quesito.ativo) === 1;
      return `<tr class="trow" style="opacity:${ativo ? '1' : '.58'}">
        <td style="padding:12px 20px"><div style="font-weight:600;font-size:13px">${escaparUsuario(quesito.titulo)}</div><div style="font-size:11px;color:var(--text-muted);margin-top:4px;max-width:500px">${escaparUsuario(quesito.descricao)}</div></td>
        <td style="padding:12px 20px"><span class="tag ${ativo ? 't-tdah' : 't-out'}">${ativo ? 'Ativo' : 'Inativo'}</span></td>
        <td style="padding:12px 20px;text-align:right;white-space:nowrap"><button class="btn-g" style="padding:6px 8px" title="Editar" onclick="abrirQuesitoEditor(${Number(quesito.id)})"><i class="bi bi-pencil"></i></button><button class="btn-g" style="padding:6px 8px" title="Ativar ou desativar" onclick="alternarQuesito(${Number(quesito.id)},${ativo ? 0 : 1})"><i class="bi bi-power"></i></button><button class="btn-g" style="padding:6px 8px;color:#c62828" title="Excluir" onclick="excluirQuesito(${Number(quesito.id)})"><i class="bi bi-trash3"></i></button></td>
      </tr>`;
    }).join('');
  }

  function abrirQuesitoEditor(id = 0) {
    const quesito = quesitosGerenciados.find(item => Number(item.id) === Number(id));
    document.getElementById('quesito-editor-titulo').textContent = id ? 'Editar quesito' : 'Novo quesito';
    document.getElementById('quesito-id').value = id || '';
    document.getElementById('quesito-titulo').value = quesito?.titulo || '';
    document.getElementById('quesito-descricao').value = quesito?.descricao || '';
    openM('quesito-editor');
  }

  function fecharQuesitoEditor() { closeM('quesito-editor'); }

  function salvarQuesito() {
    const id = Number(document.getElementById('quesito-id').value || 0);
    const dados = { titulo: document.getElementById('quesito-titulo').value.trim(), descricao: document.getElementById('quesito-descricao').value.trim() };
    const method = id ? 'PUT' : 'POST';
    const url = id ? `controllers/quesitos_avaliacao.php?id=${id}` : 'controllers/quesitos_avaliacao.php';
    fetch(url, { method, headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dados) })
      .then(res => res.json()).then(data => { if (!data.success) throw new Error(data.message); fecharQuesitoEditor(); toast(data.message || 'Quesito salvo com sucesso!'); carregarQuesitos(); }).catch(error => toast(error.message || 'Erro ao salvar quesito.', 'bi-x-circle', 'danger'));
  }

  function alternarQuesito(id, ativo) {
    openConfirm(`${ativo ? 'Ativar' : 'Inativar'} este quesito?`, function() {
      fetch(`controllers/quesitos_avaliacao.php?id=${id}`, { method:'PATCH', headers:{'Content-Type':'application/json'}, body:JSON.stringify({acao:'status'}) })
        .then(res => res.json()).then(data => { if (!data.success) throw new Error(data.message); toast('Status atualizado!'); carregarQuesitos(); }).catch(error => toast(error.message || 'Erro ao atualizar quesito.', 'bi-x-circle', 'danger'));
    });
  }

  function excluirQuesito(id) {
    openConfirm('Excluir este quesito? Ele será inativado e permanecerá no histórico.', function() {
      fetch(`controllers/quesitos_avaliacao.php?id=${id}`, { method:'DELETE' })
        .then(res => res.json()).then(data => { if (!data.success) throw new Error(data.message); toast('Quesito excluído.', 'bi-trash3', 'danger'); carregarQuesitos(); }).catch(error => toast(error.message || 'Erro ao excluir quesito.', 'bi-x-circle', 'danger'));
    });
  }

  // ===== CONDIÇÕES GERAIS =====
  let condicoesGerenciadas = [];

  function carregarCondicoes() {
    const tabela = document.getElementById('condicoes-tabela');
    if (!tabela) return;
    fetch('controllers/condicoes_gerais.php')
      .then(res => res.json())
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Não foi possível carregar as condições gerais.');
        condicoesGerenciadas = data.condicoes || [];
        renderizarCondicoes(condicoesGerenciadas);
      })
      .catch(error => toast(error.message, 'bi-x-circle', 'danger'));
  }

  function renderizarCondicoes(lista) {
    const tabela = document.getElementById('condicoes-tabela');
    const vazio = document.getElementById('condicoes-vazio');
    const total = document.getElementById('condicoes-total');
    if (!tabela) return;
    total.textContent = `(${lista.length})`;
    vazio.style.display = lista.length ? 'none' : 'flex';
    tabela.innerHTML = lista.map(condicao => {
      const ativo = Number(condicao.ativo) === 1;
      return `<tr class="trow" style="opacity:${ativo ? '1' : '.58'}">
        <td style="padding:12px 20px"><div style="font-weight:600;font-size:13px">${escaparUsuario(condicao.nome)}</div><div style="font-size:11px;color:var(--text-muted);margin-top:4px;max-width:430px">${escaparUsuario(condicao.descricao || '')}</div></td>
        <td style="padding:12px 20px;font-size:12px;font-weight:700">${escaparUsuario(condicao.sigla || '')}</td>
        <td style="padding:12px 20px"><span class="tag ${ativo ? 't-tdah' : 't-out'}">${ativo ? 'Ativa' : 'Inativa'}</span></td>
        <td style="padding:12px 20px;text-align:right;white-space:nowrap"><button class="btn-g" style="padding:6px 8px" title="Editar" onclick="abrirCondicaoEditor(${Number(condicao.id)})"><i class="bi bi-pencil"></i></button><button class="btn-g" style="padding:6px 8px" title="Ativar ou desativar" onclick="alternarCondicao(${Number(condicao.id)},${ativo ? 0 : 1})"><i class="bi bi-power"></i></button><button class="btn-g" style="padding:6px 8px;color:#c62828" title="Excluir" onclick="excluirCondicao(${Number(condicao.id)})"><i class="bi bi-trash3"></i></button></td>
      </tr>`;
    }).join('');
  }

  function abrirCondicaoEditor(id = 0) {
    const condicao = condicoesGerenciadas.find(item => Number(item.id) === Number(id));
    document.getElementById('condicao-editor-titulo').textContent = id ? 'Editar condição geral' : 'Adicionar Condição geral';
    document.getElementById('condicao-id').value = id || '';
    document.getElementById('condicao-nome').value = condicao?.nome || '';
    document.getElementById('condicao-sigla').value = condicao?.sigla || '';
    document.getElementById('condicao-descricao').value = condicao?.descricao || '';
    openM('condicao-editor');
  }

  function fecharCondicaoEditor() { closeM('condicao-editor'); }

  function salvarCondicao() {
    const id = Number(document.getElementById('condicao-id').value || 0);
    const dados = { nome: document.getElementById('condicao-nome').value.trim(), sigla: document.getElementById('condicao-sigla').value.trim(), descricao: document.getElementById('condicao-descricao').value.trim() };
    const method = id ? 'PUT' : 'POST';
    const url = id ? `controllers/condicoes_gerais.php?id=${id}` : 'controllers/condicoes_gerais.php';
    fetch(url, { method, headers: {'Content-Type': 'application/json'}, body: JSON.stringify(dados) })
      .then(res => res.json()).then(data => { if (!data.success) throw new Error(data.message); fecharCondicaoEditor(); toast(data.message || 'Condição geral salva com sucesso!'); carregarCondicoes(); }).catch(error => toast(error.message || 'Erro ao salvar condição geral.', 'bi-x-circle', 'danger'));
  }

  function alternarCondicao(id, ativo) {
    openConfirm(`${ativo ? 'Ativar' : 'Inativar'} esta condição geral?`, function() {
      fetch(`controllers/condicoes_gerais.php?id=${id}`, { method:'PATCH', headers:{'Content-Type':'application/json'}, body:JSON.stringify({acao:'status'}) })
        .then(res => res.json()).then(data => { if (!data.success) throw new Error(data.message); toast('Status atualizado!'); carregarCondicoes(); }).catch(error => toast(error.message || 'Erro ao atualizar condição geral.', 'bi-x-circle', 'danger'));
    });
  }

  function excluirCondicao(id) {
    openConfirm('Excluir esta condição geral definitivamente? Esta ação não poderá ser desfeita.', function() {
      fetch(`controllers/condicoes_gerais.php?id=${id}`, { method:'DELETE' })
        .then(res => res.json()).then(data => { if (!data.success) throw new Error(data.message); toast(data.message, 'bi-trash3', 'danger'); carregarCondicoes(); }).catch(error => toast(error.message || 'Erro ao excluir condição geral.', 'bi-x-circle', 'danger'));
    });
  }

  // ===== MODAL =====
  function openM(id){document.getElementById(id)?.classList.add('open');}
  function closeM(id){document.getElementById(id)?.classList.remove('open');}
  window.abrirModalQuesito = abrirModalQuesito;
  window.fecharModalQuesito = fecharModalQuesito;
  window.selecionarRespostaModal = selecionarRespostaModal;
  window.confirmarModalQuesito = confirmarModalQuesito;

  // ===== TOAST =====
  function toast(msg, ic='bi-check-circle-fill', type='ok') {
    const el=document.getElementById('toast-el'), icEl=document.getElementById('toast-ic'), txEl=document.getElementById('toast-tx');
    icEl.className=`bi ${ic}`;
    const col=type==='amber'?'var(--amber)':type==='danger'?'#f87171':'var(--c3)';
    icEl.style.color=col; el.style.borderLeftColor=col;
    txEl.textContent=msg; el.classList.add('show');
    clearTimeout(el._t); el._t=setTimeout(()=>el.classList.remove('show'),3200);
  }

  // ===== NOTIFICAÇOES =====
  function addNotif(msg) {
    notifCount++;
    const b=document.getElementById('notif-badge'); b.textContent=notifCount; b.style.display='flex';
    const li=document.getElementById('notif-list');
    const em=li.querySelector('.empty'); if(em) em.remove();
    const div=document.createElement('div');
    div.style.cssText='display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:8px;font-size:12px;margin-bottom:4px;background:rgba(76,175,80,0.05);border:1px solid rgba(76,175,80,0.12)';
    div.innerHTML=`<i class="bi bi-bell-fill" style="color:var(--c3);font-size:12px;flex-shrink:0"></i><span>${msg}</span>`;
    li.prepend(div);
  }

  function toggleDD() {
    const dd=document.getElementById('notif-dd');
    const open=dd.style.display==='block';
    dd.style.display=open?'none':'block';
    document.getElementById('bell-btn').setAttribute('aria-expanded', String(!open));
    if(!open){notifCount=0;document.getElementById('notif-badge').style.display='none';}
  }

  // ===== GLOBAL CLICKS =====
  function botaoSalvarVisivel(container) {
    if (!container) return null;
    return Array.from(container.querySelectorAll('button[onclick]')).find(button => {
      const acao = button.getAttribute('onclick') || '';
      const visivel = button.offsetParent !== null && getComputedStyle(button).display !== 'none';
      return visivel && /save|salvar|addStudent|handleLaudoUpload/i.test(acao);
    }) || null;
  }

  document.addEventListener('keydown', e => {
    if (e.key !== 'Enter' || e.isComposing || e.defaultPrevented) return;
    if (e.target.matches('textarea, [contenteditable="true"]')) return;

    const modal = e.target.closest('.modal-bg.open');
    const aba = e.target.closest('[id^="tc-"]');
    const sessao = e.target.closest('.page.active');
    const escopo = modal?.querySelector('.modal-box') || aba || sessao;
    const botao = botaoSalvarVisivel(escopo);
    if (!botao) return;

    e.preventDefault();
    botao.click();
  });

  document.addEventListener('click', e => {
    const dd=document.getElementById('notif-dd');
    const btn=document.getElementById('bell-btn');
    if(!dd.contains(e.target)&&!btn.contains(e.target)) {
      dd.style.display='none';
      btn.setAttribute('aria-expanded', 'false');
    }
    const modal = e.target.closest('.modal-bg');
    if (modal && e.target === modal) modal.classList.remove('open');
    const seletor = document.getElementById('diag-multiselect');
    if (seletor && !seletor.contains(e.target)) {
      seletor.classList.remove('open');
      seletor.querySelector('.multi-select-toggle')?.setAttribute('aria-expanded', 'false');
    }
  });
  document.addEventListener('keydown', e => {
    if(e.key==='Escape'){
      document.querySelectorAll('.modal-bg.open').forEach(modal => modal.classList.remove('open'));
      document.getElementById('notif-dd').style.display='none';
      document.getElementById('bell-btn').setAttribute('aria-expanded', 'false');
    }
  });

  // ===== INIT =====


carregarAlunos();
loadEventos();
go('dashboard');