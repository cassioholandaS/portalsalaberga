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

SELECT email, tipo, 'TesteLocal@2026' AS senha
FROM usuarios
WHERE @conectepcd_banco_local
  AND email IN (
      'aee.teste@sistemapcd.local',
      'apoio.teste@sistemapcd.local',
      'responsavel.teste@sistemapcd.local'
  )
ORDER BY tipo;
