SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE registro_entrada ADD COLUMN justificativa_atraso TEXT DEFAULT NULL',
        'SELECT ''A coluna justificativa_atraso já existe.'' AS resultado'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'registro_entrada'
      AND COLUMN_NAME = 'justificativa_atraso'
);
PREPARE migration FROM @sql;
EXECUTE migration;
DEALLOCATE PREPARE migration;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE registro_entrada ADD COLUMN status_justificativa ENUM(''pendente'',''aprovada'',''recusada'') NOT NULL DEFAULT ''pendente''',
        'SELECT ''A coluna status_justificativa já existe.'' AS resultado'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'registro_entrada'
      AND COLUMN_NAME = 'status_justificativa'
);
PREPARE migration FROM @sql;
EXECUTE migration;
DEALLOCATE PREPARE migration;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE registro_entrada ADD COLUMN observacao_validacao TEXT DEFAULT NULL',
        'SELECT ''A coluna observacao_validacao já existe.'' AS resultado'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'registro_entrada'
      AND COLUMN_NAME = 'observacao_validacao'
);
PREPARE migration FROM @sql;
EXECUTE migration;
DEALLOCATE PREPARE migration;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE registro_entrada ADD COLUMN validado_por VARCHAR(100) DEFAULT NULL',
        'SELECT ''A coluna validado_por já existe.'' AS resultado'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'registro_entrada'
      AND COLUMN_NAME = 'validado_por'
);
PREPARE migration FROM @sql;
EXECUTE migration;
DEALLOCATE PREPARE migration;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE registro_entrada ADD COLUMN validado_em DATETIME DEFAULT NULL',
        'SELECT ''A coluna validado_em já existe.'' AS resultado'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'registro_entrada'
      AND COLUMN_NAME = 'validado_em'
);
PREPARE migration FROM @sql;
EXECUTE migration;
DEALLOCATE PREPARE migration;
