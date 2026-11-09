-- Aplicar no banco de dados da hospedagem usado pelo Conecte PCD.
-- A migracao pode ser executada novamente: colunas existentes nao sao recriadas
-- e a tabela de frequencia e criada somente se ainda nao existir.

-- A tela de avaliacao e o registro de frequencia usam esta tabela.
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

-- Permite cadastrar o cartao PAEE antes de preencher o documento.
ALTER TABLE `paee` MODIFY `data_atendimento` DATE NULL;

-- Adiciona os campos de documento PAEE somente quando ainda nao existem.
SET @paee_schema = DATABASE();

SET @paee_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @paee_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'data_elaboracao') = 0,
  'ALTER TABLE `paee` ADD COLUMN `data_elaboracao` DATE NULL AFTER `nome_documento`',
  'SELECT 1'
);
PREPARE paee_stmt FROM @paee_ddl;
EXECUTE paee_stmt;
DEALLOCATE PREPARE paee_stmt;

SET @paee_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @paee_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'responsavel_preenchimento') = 0,
  'ALTER TABLE `paee` ADD COLUMN `responsavel_preenchimento` VARCHAR(150) NULL AFTER `data_elaboracao`',
  'SELECT 1'
);
PREPARE paee_stmt FROM @paee_ddl;
EXECUTE paee_stmt;
DEALLOCATE PREPARE paee_stmt;

SET @paee_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @paee_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'objetivos_pedagogicos') = 0,
  'ALTER TABLE `paee` ADD COLUMN `objetivos_pedagogicos` TEXT NULL AFTER `responsavel_preenchimento`',
  'SELECT 1'
);
PREPARE paee_stmt FROM @paee_ddl;
EXECUTE paee_stmt;
DEALLOCATE PREPARE paee_stmt;

SET @paee_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @paee_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'habilidades_desenvolver') = 0,
  'ALTER TABLE `paee` ADD COLUMN `habilidades_desenvolver` TEXT NULL AFTER `objetivos_pedagogicos`',
  'SELECT 1'
);
PREPARE paee_stmt FROM @paee_ddl;
EXECUTE paee_stmt;
DEALLOCATE PREPARE paee_stmt;

SET @paee_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @paee_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'estrategias_ensino') = 0,
  'ALTER TABLE `paee` ADD COLUMN `estrategias_ensino` TEXT NULL AFTER `habilidades_desenvolver`',
  'SELECT 1'
);
PREPARE paee_stmt FROM @paee_ddl;
EXECUTE paee_stmt;
DEALLOCATE PREPARE paee_stmt;

SET @paee_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @paee_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'recursos_acessibilidade') = 0,
  'ALTER TABLE `paee` ADD COLUMN `recursos_acessibilidade` TEXT NULL AFTER `estrategias_ensino`',
  'SELECT 1'
);
PREPARE paee_stmt FROM @paee_ddl;
EXECUTE paee_stmt;
DEALLOCATE PREPARE paee_stmt;

SET @paee_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @paee_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'adaptacoes_curriculares') = 0,
  'ALTER TABLE `paee` ADD COLUMN `adaptacoes_curriculares` TEXT NULL AFTER `recursos_acessibilidade`',
  'SELECT 1'
);
PREPARE paee_stmt FROM @paee_ddl;
EXECUTE paee_stmt;
DEALLOCATE PREPARE paee_stmt;

SET @paee_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @paee_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'criterios_avaliacao') = 0,
  'ALTER TABLE `paee` ADD COLUMN `criterios_avaliacao` TEXT NULL AFTER `adaptacoes_curriculares`',
  'SELECT 1'
);
PREPARE paee_stmt FROM @paee_ddl;
EXECUTE paee_stmt;
DEALLOCATE PREPARE paee_stmt;

SET @paee_ddl = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @paee_schema AND TABLE_NAME = 'paee' AND COLUMN_NAME = 'observacoes') = 0,
  'ALTER TABLE `paee` ADD COLUMN `observacoes` TEXT NULL AFTER `criterios_avaliacao`',
  'SELECT 1'
);
PREPARE paee_stmt FROM @paee_ddl;
EXECUTE paee_stmt;
DEALLOCATE PREPARE paee_stmt;

-- Resultado esperado: todas as colunas acima e frequencia_apoio aparecem.
SELECT TABLE_NAME, COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
    (TABLE_NAME = 'paee' AND COLUMN_NAME IN (
      'data_atendimento',
      'data_elaboracao',
      'responsavel_preenchimento',
      'objetivos_pedagogicos',
      'habilidades_desenvolver',
      'estrategias_ensino',
      'recursos_acessibilidade',
      'adaptacoes_curriculares',
      'criterios_avaliacao',
      'observacoes'
    ))
    OR TABLE_NAME = 'frequencia_apoio'
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;
