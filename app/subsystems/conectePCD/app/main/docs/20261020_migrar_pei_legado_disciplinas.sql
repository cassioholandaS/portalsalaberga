-- Vincula os PEIs antigos usando nome_documento, que era o campo "Matéria escolar".
-- Seguro para reaplicar: não altera PEIs que já tenham uma disciplina associada.

START TRANSACTION;

INSERT INTO disciplinas (nome)
SELECT DISTINCT TRIM(nome_documento)
FROM pei
WHERE disciplina_id IS NULL
  AND nome_documento IS NOT NULL
  AND TRIM(nome_documento) <> ''
ON DUPLICATE KEY UPDATE ativa = 1;

INSERT INTO alunos_disciplinas (aluno_id, disciplina_id, ativa)
SELECT DISTINCT p.aluno_id, d.id, 1
FROM pei p
INNER JOIN disciplinas d ON d.nome = TRIM(p.nome_documento)
WHERE p.disciplina_id IS NULL
  AND p.nome_documento IS NOT NULL
  AND TRIM(p.nome_documento) <> ''
ON DUPLICATE KEY UPDATE ativa = 1;

UPDATE pei p
INNER JOIN disciplinas d ON d.nome = TRIM(p.nome_documento)
SET p.disciplina_id = d.id
WHERE p.disciplina_id IS NULL
  AND p.nome_documento IS NOT NULL
  AND TRIM(p.nome_documento) <> '';

COMMIT;
