ALTER TABLE alunos
    ADD COLUMN IF NOT EXISTS status_aluno ENUM('ativo', 'concluinte') NOT NULL DEFAULT 'ativo',
    ADD COLUMN IF NOT EXISTS ano_conclusao SMALLINT NULL DEFAULT NULL;

ALTER TABLE turmas
    ADD COLUMN IF NOT EXISTS turma_origem_id INT NULL DEFAULT NULL,
    ADD UNIQUE INDEX IF NOT EXISTS uq_turmas_origem (turma_origem_id);

CREATE TABLE IF NOT EXISTS aluno_turma_historico (
    id_aluno INT NOT NULL,
    id_turma INT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_aluno, id_turma),
    INDEX idx_aluno_turma_historico_turma (id_turma),
    CONSTRAINT fk_ath_aluno FOREIGN KEY (id_aluno) REFERENCES alunos(id_aluno) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ath_turma FOREIGN KEY (id_turma) REFERENCES turmas(id_turma) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT IGNORE INTO aluno_turma_historico (id_aluno, id_turma)
SELECT id_aluno, id_turma FROM alunos;

ALTER TABLE media_final
    DROP INDEX uq_media_final_aluno_bimestre,
    ADD UNIQUE INDEX uq_media_final_aluno_turma_bimestre (id_aluno, id_turma, bimestre, disciplina);

CREATE TABLE IF NOT EXISTS progressao_ano_letivo (
    ano_destino SMALLINT NOT NULL PRIMARY KEY,
    ano_origem SMALLINT NOT NULL,
    tipo_processamento ENUM('baseline', 'concluido') NOT NULL,
    total_promovidos INT NOT NULL DEFAULT 0,
    total_concluintes INT NOT NULL DEFAULT 0,
    executado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO progressao_ano_letivo (ano_destino, ano_origem, tipo_processamento)
VALUES (YEAR(CURRENT_DATE), YEAR(CURRENT_DATE) - 1, 'baseline')
ON DUPLICATE KEY UPDATE ano_destino = VALUES(ano_destino);
