-- Atualizacao consolidada do banco PCD para instalacoes existentes.
-- Execute no banco do sistema PCD. Pode ser reaplicada com seguranca.
-- Para uma instalacao nova, importe sistema_pcd.sql e execute este arquivo.

SET @pcd_schema = DATABASE();

-- Frequencia do profissional de apoio.
CREATE TABLE IF NOT EXISTS `frequencia_apoio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aluno_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `presente` tinyint(1) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_frequencia_apoio_aluno_data` (`aluno_id`,`data`),
  KEY `idx_frequencia_apoio_data` (`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Campos adicionais dos documentos PAEE.
ALTER TABLE `paee` MODIFY `data_atendimento` DATE NULL;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'data_elaboracao') = 0,
  'ALTER TABLE `paee` ADD COLUMN `data_elaboracao` DATE NULL AFTER `nome_documento`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'responsavel_preenchimento') = 0,
  'ALTER TABLE `paee` ADD COLUMN `responsavel_preenchimento` VARCHAR(150) NULL AFTER `data_elaboracao`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'objetivos_pedagogicos') = 0,
  'ALTER TABLE `paee` ADD COLUMN `objetivos_pedagogicos` TEXT NULL AFTER `responsavel_preenchimento`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'habilidades_desenvolver') = 0,
  'ALTER TABLE `paee` ADD COLUMN `habilidades_desenvolver` TEXT NULL AFTER `objetivos_pedagogicos`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'estrategias_ensino') = 0,
  'ALTER TABLE `paee` ADD COLUMN `estrategias_ensino` TEXT NULL AFTER `habilidades_desenvolver`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'recursos_acessibilidade') = 0,
  'ALTER TABLE `paee` ADD COLUMN `recursos_acessibilidade` TEXT NULL AFTER `estrategias_ensino`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'adaptacoes_curriculares') = 0,
  'ALTER TABLE `paee` ADD COLUMN `adaptacoes_curriculares` TEXT NULL AFTER `recursos_acessibilidade`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'criterios_avaliacao') = 0,
  'ALTER TABLE `paee` ADD COLUMN `criterios_avaliacao` TEXT NULL AFTER `adaptacoes_curriculares`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'observacoes') = 0,
  'ALTER TABLE `paee` ADD COLUMN `observacoes` TEXT NULL AFTER `criterios_avaliacao`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

-- Disciplinas e associacao de PEI/PAEE a uma disciplina.
CREATE TABLE IF NOT EXISTS `disciplinas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `ativa` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_disciplinas_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `alunos_disciplinas` (
  `aluno_id` int NOT NULL,
  `disciplina_id` int NOT NULL,
  `ativa` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`aluno_id`,`disciplina_id`),
  KEY `idx_alunos_disciplinas_disciplina` (`disciplina_id`),
  CONSTRAINT `fk_alunos_disciplinas_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_alunos_disciplinas_disciplina` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'pei' AND COLUMN_NAME = 'disciplina_id') = 0,
  'ALTER TABLE `pei` ADD COLUMN `disciplina_id` INT NULL AFTER `aluno_id`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'disciplina_id') = 0,
  'ALTER TABLE `paee` ADD COLUMN `disciplina_id` INT NULL AFTER `aluno_id`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'pei_id') = 0,
  'ALTER TABLE `paee` ADD COLUMN `pei_id` INT NULL AFTER `disciplina_id`',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'pei' AND INDEX_NAME = 'idx_pei_aluno_disciplina') = 0,
  'ALTER TABLE `pei` ADD KEY `idx_pei_aluno_disciplina` (`aluno_id`, `disciplina_id`, `data_elaboracao`, `id`)',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND INDEX_NAME = 'idx_paee_aluno_disciplina') = 0,
  'ALTER TABLE `paee` ADD KEY `idx_paee_aluno_disciplina` (`aluno_id`, `disciplina_id`, `data_atendimento`, `id`)',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND INDEX_NAME = 'idx_paee_pei') = 0,
  'ALTER TABLE `paee` ADD KEY `idx_paee_pei` (`pei_id`)',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = @pcd_schema AND TABLE_NAME = 'pei' AND CONSTRAINT_NAME = 'fk_pei_aluno_disciplina') = 0,
  'ALTER TABLE `pei` ADD CONSTRAINT `fk_pei_aluno_disciplina` FOREIGN KEY (`aluno_id`, `disciplina_id`) REFERENCES `alunos_disciplinas` (`aluno_id`, `disciplina_id`) ON DELETE RESTRICT ON UPDATE CASCADE',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND CONSTRAINT_NAME = 'fk_paee_aluno_disciplina') = 0,
  'ALTER TABLE `paee` ADD CONSTRAINT `fk_paee_aluno_disciplina` FOREIGN KEY (`aluno_id`, `disciplina_id`) REFERENCES `alunos_disciplinas` (`aluno_id`, `disciplina_id`) ON DELETE RESTRICT ON UPDATE CASCADE',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

SET @pcd_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee' AND CONSTRAINT_NAME = 'fk_paee_pei') = 0,
  'ALTER TABLE `paee` ADD CONSTRAINT `fk_paee_pei` FOREIGN KEY (`pei_id`) REFERENCES `pei` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'SET @pcd_noop = 1'
);
PREPARE pcd_stmt FROM @pcd_ddl;
EXECUTE pcd_stmt;
DEALLOCATE PREPARE pcd_stmt;

-- Converte a antiga materia do PEI em disciplina sem sobrescrever dados existentes.
INSERT INTO `disciplinas` (`nome`)
SELECT DISTINCT TRIM(`nome_documento`)
FROM `pei`
WHERE `disciplina_id` IS NULL
  AND `nome_documento` IS NOT NULL
  AND TRIM(`nome_documento`) <> ''
ON DUPLICATE KEY UPDATE `ativa` = 1;

INSERT INTO `alunos_disciplinas` (`aluno_id`, `disciplina_id`, `ativa`)
SELECT DISTINCT p.`aluno_id`, d.`id`, 1
FROM `pei` p
INNER JOIN `disciplinas` d ON d.`nome` = TRIM(p.`nome_documento`)
WHERE p.`disciplina_id` IS NULL
  AND p.`nome_documento` IS NOT NULL
  AND TRIM(p.`nome_documento`) <> ''
ON DUPLICATE KEY UPDATE `ativa` = 1;

UPDATE `pei` p
INNER JOIN `disciplinas` d ON d.`nome` = TRIM(p.`nome_documento`)
SET p.`disciplina_id` = d.`id`
WHERE p.`disciplina_id` IS NULL
  AND p.`nome_documento` IS NOT NULL
  AND TRIM(p.`nome_documento`) <> '';

-- Conferencia rapida do resultado.
SELECT 'frequencia_apoio' AS tabela, COUNT(*) AS colunas
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'frequencia_apoio'
UNION ALL
SELECT 'paee', COUNT(*)
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME = 'paee'
  AND COLUMN_NAME IN ('data_elaboracao', 'responsavel_preenchimento', 'objetivos_pedagogicos',
                      'habilidades_desenvolver', 'estrategias_ensino', 'recursos_acessibilidade',
                      'adaptacoes_curriculares', 'criterios_avaliacao', 'observacoes')
UNION ALL
SELECT 'disciplinas', COUNT(*)
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = @pcd_schema AND TABLE_NAME IN ('disciplinas', 'alunos_disciplinas');
