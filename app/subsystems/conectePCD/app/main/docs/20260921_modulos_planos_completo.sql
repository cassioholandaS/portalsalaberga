-- Dump incremental unico: 20260921_modulos_planos_completo
-- Aplicar uma vez, apos o dump sistema_pcd.sql, em ambiente de desenvolvimento.
-- Ordem interna: estruturas de disciplinas e planos, depois vinculo PAEE-PEI.
-- Nao remove nem recria tabelas existentes.
-- Planos antigos permanecem com disciplina_id NULL e PAEE antigos com pei_id NULL.

START TRANSACTION;

-- 1. Catalogo e associacao de disciplinas
CREATE TABLE IF NOT EXISTS disciplinas (
  id INT NOT NULL AUTO_INCREMENT,
  nome VARCHAR(150) NOT NULL,
  ativa TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_disciplinas_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS alunos_disciplinas (
  aluno_id INT NOT NULL,
  disciplina_id INT NOT NULL,
  ativa TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (aluno_id, disciplina_id),
  KEY idx_alunos_disciplinas_disciplina (disciplina_id),
  CONSTRAINT fk_alunos_disciplinas_aluno FOREIGN KEY (aluno_id) REFERENCES alunos (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_alunos_disciplinas_disciplina FOREIGN KEY (disciplina_id) REFERENCES disciplinas (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Associacao dos planos com aluno e disciplina
ALTER TABLE pei ADD COLUMN disciplina_id INT NULL AFTER aluno_id;
ALTER TABLE paee ADD COLUMN disciplina_id INT NULL AFTER aluno_id;
ALTER TABLE pei ADD KEY idx_pei_aluno_disciplina (aluno_id, disciplina_id, data_elaboracao, id);
ALTER TABLE paee ADD KEY idx_paee_aluno_disciplina (aluno_id, disciplina_id, data_atendimento, id);
ALTER TABLE pei ADD CONSTRAINT fk_pei_aluno_disciplina FOREIGN KEY (aluno_id, disciplina_id) REFERENCES alunos_disciplinas (aluno_id, disciplina_id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE paee ADD CONSTRAINT fk_paee_aluno_disciplina FOREIGN KEY (aluno_id, disciplina_id) REFERENCES alunos_disciplinas (aluno_id, disciplina_id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- 3. Vinculo do PAEE ao PEI especifico da disciplina
ALTER TABLE paee ADD COLUMN pei_id INT NULL AFTER disciplina_id;
ALTER TABLE paee ADD KEY idx_paee_pei (pei_id);
ALTER TABLE paee ADD CONSTRAINT fk_paee_pei FOREIGN KEY (pei_id) REFERENCES pei (id) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;
