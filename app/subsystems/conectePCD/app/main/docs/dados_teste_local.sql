-- Dados ficticios para teste local do ConectePCD.
-- Execute somente no banco local "sistema_pcd"; em outros bancos, os INSERTs nao fazem alteracoes.
-- Contas criadas: AEE, profissional de apoio e responsavel.
-- Senha comum das contas de teste: TesteLocal@2026

SET @conectepcd_banco_local := (DATABASE() = 'sistema_pcd');

SELECT IF(
    @conectepcd_banco_local,
    'Banco local confirmado: os dados de teste serao inseridos.',
    'Banco diferente de sistema_pcd: nenhum dado de teste sera inserido.'
) AS resultado;

INSERT INTO usuarios (nome, cpf, telefone, email, senha, tipo, status_cadastro, ativo)
SELECT 'AEE Demonstracao Local', '111.111.111-11', '(85) 90000-0001', 'aee.teste@sistemapcd.local',
       '$2y$10$gO3.xvqb1bm5xEq.yi5w0udVjjumnbqj0DV79h8X9HWRVDgrNSrvK', 'aee', 'aprovado', 1
WHERE @conectepcd_banco_local
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'aee.teste@sistemapcd.local');

INSERT INTO usuarios (nome, cpf, telefone, email, senha, tipo, status_cadastro, ativo)
SELECT 'Apoio Demonstracao Local', '222.222.222-22', '(85) 90000-0002', 'apoio.teste@sistemapcd.local',
       '$2y$10$gO3.xvqb1bm5xEq.yi5w0udVjjumnbqj0DV79h8X9HWRVDgrNSrvK', 'apoio', 'aprovado', 1
WHERE @conectepcd_banco_local
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'apoio.teste@sistemapcd.local');

INSERT INTO usuarios (nome, cpf, telefone, email, senha, tipo, status_cadastro, ativo)
SELECT 'Responsavel Demonstracao Local', '333.333.333-33', '(85) 90000-0003', 'responsavel.teste@sistemapcd.local',
       '$2y$10$gO3.xvqb1bm5xEq.yi5w0udVjjumnbqj0DV79h8X9HWRVDgrNSrvK', 'responsavel', 'aprovado', 1
WHERE @conectepcd_banco_local
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'responsavel.teste@sistemapcd.local');

INSERT INTO cursos (nome)
SELECT 'Informatica'
WHERE @conectepcd_banco_local
  AND NOT EXISTS (SELECT 1 FROM cursos WHERE nome = 'Informatica');

INSERT INTO diagnosticos (nome, sigla, descricao)
SELECT 'Transtorno do Espectro Autista', 'TEA', 'Condicao geral ficticia para testes locais.'
WHERE @conectepcd_banco_local
  AND NOT EXISTS (
      SELECT 1 FROM diagnosticos
      WHERE nome = 'Transtorno do Espectro Autista' OR sigla = 'TEA'
  );

INSERT INTO alunos (nome, matricula, cpf, data_nascimento, sexo, turma, curso, diagnostico, id_apoio)
SELECT 'Aluno Demonstracao Local', '7000001', '444.444.444-44', '2010-05-18', 'Masculino',
       '3 Ano', 'Informatica', 'TEA', u.id
FROM usuarios u
WHERE @conectepcd_banco_local
  AND u.email = 'apoio.teste@sistemapcd.local'
  AND u.tipo = 'apoio'
  AND NOT EXISTS (
      SELECT 1 FROM alunos
      WHERE matricula = '7000001' OR id_apoio = u.id
  );

INSERT INTO quesitos_avaliacao (titulo, descricao)
SELECT 'Comunicação funcional', 'Expressa necessidades, preferências e ideias durante as atividades escolares.'
WHERE @conectepcd_banco_local
  AND NOT EXISTS (SELECT 1 FROM quesitos_avaliacao WHERE titulo = 'Comunicação funcional');

INSERT INTO quesitos_avaliacao (titulo, descricao)
SELECT 'Participação nas atividades', 'Participa das propostas individuais e coletivas com os apoios necessários.'
WHERE @conectepcd_banco_local
  AND NOT EXISTS (SELECT 1 FROM quesitos_avaliacao WHERE titulo = 'Participação nas atividades');

INSERT INTO quesitos_avaliacao (titulo, descricao)
SELECT 'Autonomia escolar', 'Realiza rotinas escolares e solicita apoio quando necessário.'
WHERE @conectepcd_banco_local
  AND NOT EXISTS (SELECT 1 FROM quesitos_avaliacao WHERE titulo = 'Autonomia escolar');

INSERT INTO alunos_quesitos (aluno_id, quesito_id, ativo, peso)
SELECT a.id, q.id, 1, 1
FROM alunos a
INNER JOIN usuarios u ON u.id = a.id_apoio
INNER JOIN quesitos_avaliacao q
    ON q.titulo IN ('Comunicação funcional', 'Participação nas atividades', 'Autonomia escolar')
WHERE @conectepcd_banco_local
  AND a.matricula = '7000001'
  AND u.email = 'apoio.teste@sistemapcd.local'
  AND NOT EXISTS (
      SELECT 1 FROM alunos_quesitos aq
      WHERE aq.aluno_id = a.id AND aq.quesito_id = q.id
  );

INSERT INTO pei (
    aluno_id, nome_documento, data_elaboracao, responsavel_preenchimento,
    objetivos_pedagogicos, habilidades_desenvolver, estrategias_ensino,
    recursos_acessibilidade, adaptacoes_curriculares, criterios_avaliacao, observacoes
)
SELECT a.id, 'PEI - Demonstração local', '2026-11-17', 'Equipe pedagógica de demonstração',
       'Ampliar a participação nas atividades de leitura e resolução de problemas.',
       'Comunicação funcional, organização da rotina e autonomia nas tarefas.',
       'Apresentar instruções em etapas, antecipar mudanças e oferecer tempo ampliado.',
       'Agenda visual, materiais com contraste e recursos digitais acessíveis.',
       'Oferecer alternativas de resposta e dividir tarefas extensas em etapas.',
       'Acompanhar participação, estratégias utilizadas e progressos individuais.',
       'Registro inteiramente fictício para demonstração local do sistema.'
FROM alunos a
INNER JOIN usuarios u ON u.id = a.id_apoio
WHERE @conectepcd_banco_local
  AND a.matricula = '7000001'
  AND u.email = 'apoio.teste@sistemapcd.local'
  AND NOT EXISTS (
      SELECT 1 FROM pei p
      WHERE p.aluno_id = a.id AND p.nome_documento = 'PEI - Demonstração local'
  );

INSERT INTO paee (
    aluno_id, nome_documento, data_atendimento, profissional_responsavel,
    necessidades_identificadas, objetivos_atendimento, recursos_utilizados,
    estrategias_aplicadas, tecnologias_assistivas, resultados_observados,
    encaminhamentos, observacoes_gerais
)
SELECT a.id, 'PAEE - Demonstração local', '2026-11-17', 'Profissional de demonstração',
       'Apoio para organizar a rotina e compreender instruções com várias etapas.',
       'Fortalecer a autonomia, a comunicação e a participação nas atividades.',
       'Agenda visual, cartões de rotina e materiais acessíveis.',
       'Modelagem, instruções curtas e retirada gradual de apoio.',
       'Recursos digitais com leitura ampliada, quando necessários.',
       'Participou das atividades com apoio visual e concluiu as etapas propostas.',
       'Manter o acompanhamento e compartilhar estratégias com a equipe pedagógica.',
       'Registro inteiramente fictício para demonstração local do sistema.'
FROM alunos a
INNER JOIN usuarios u ON u.id = a.id_apoio
WHERE @conectepcd_banco_local
  AND a.matricula = '7000001'
  AND u.email = 'apoio.teste@sistemapcd.local'
  AND NOT EXISTS (
      SELECT 1 FROM paee p
      WHERE p.aluno_id = a.id AND p.nome_documento = 'PAEE - Demonstração local'
  );

INSERT INTO relatorios_acompanhamento (
    aluno_id, data_atendimento, responsavel_registro, desenvolvimento_observado,
    dificuldades_observadas, potencialidades, recomendacoes, conclusao
)
SELECT a.id, '2026-11-17', 'Profissional de demonstração',
       'Participou das atividades propostas e utilizou a agenda visual para acompanhar a rotina.',
       'Necessitou de apoio para iniciar tarefas com várias etapas.',
       'Demonstrou interesse por atividades práticas e boa resposta a instruções curtas.',
       'Manter instruções segmentadas e ampliar gradualmente as oportunidades de autonomia.',
       'Registro fictício para ilustrar relatórios e gráficos do acompanhamento.'
FROM alunos a
INNER JOIN usuarios u ON u.id = a.id_apoio
WHERE @conectepcd_banco_local
  AND a.matricula = '7000001'
  AND u.email = 'apoio.teste@sistemapcd.local'
  AND NOT EXISTS (
      SELECT 1 FROM relatorios_acompanhamento r
      WHERE r.aluno_id = a.id AND r.data_atendimento = '2026-11-17'
        AND r.responsavel_registro = 'Profissional de demonstração'
  );

INSERT INTO frequencia_apoio (aluno_id, usuario_id, data, presente)
SELECT a.id, u.id, '2026-11-17', 1
FROM alunos a
INNER JOIN usuarios u ON u.id = a.id_apoio
WHERE @conectepcd_banco_local
  AND a.matricula = '7000001'
  AND u.email = 'apoio.teste@sistemapcd.local'
  AND NOT EXISTS (
      SELECT 1 FROM frequencia_apoio f
      WHERE f.aluno_id = a.id AND f.data = '2026-11-17'
  );

SELECT email, tipo, 'TesteLocal@2026' AS senha
FROM usuarios
WHERE @conectepcd_banco_local
  AND email IN (
      'aee.teste@sistemapcd.local',
      'apoio.teste@sistemapcd.local',
      'responsavel.teste@sistemapcd.local'
  )
ORDER BY tipo;
