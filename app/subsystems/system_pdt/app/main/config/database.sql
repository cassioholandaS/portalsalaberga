-- ============================================================
-- BANCO DE DADOS: SISTEMA PDT
-- Esquema completo do banco de dados do Sistema PDT.
-- Para bancos existentes, use os scripts em config/migrations/.
-- Descrição: Banco de dados para Sistema de Gestão Escolar
-- ============================================================

CREATE DATABASE IF NOT EXISTS sistema_pdt;
USE sistema_pdt;

-- ============================================================
-- TABELA: USUÁRIOS
-- Descrição: Cadastro de usuários (gestor, coordenador, professor, pdt)
-- ============================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    matricula VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('gestor', 'coordenador', 'professor') NOT NULL,
    atribuicao ENUM('pdt') DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CONTAS EXCLUSIVAS DE DESENVOLVIMENTO LOCAL.
-- Senha inicial das três contas: 123456. Remova ou altere este bloco antes de usar o SQL em produção.
-- Os hashes abaixo usam o formato gerado por password_hash(..., PASSWORD_DEFAULT) no PHP.
INSERT INTO usuarios (nome, matricula, email, senha, tipo)
VALUES
    ('Otávio', 'PDT-LOCAL-OTAVIO', 'otavio.ce@gmail.com', '$2y$10$JxY1kq4JbrdhnCfQkoXC2ualO0haU0qo6cSRUzmGPig5FDAhwYtby', 'gestor'),
    ('Kauan', 'PDT-LOCAL-KAUAN', 'kauan@gmail.com', '$2y$10$ZYdOdUPLsjemZkCDsPDq9eBN7EgG3Q/QkWuk2Zs8.F4iKTmS53JZ6', 'coordenador'),
    ('Cassio', 'PDT-LOCAL-CASSIO', 'cassio@gmail.com', '$2y$10$leCO0Bv3hUk/GCgHW6kRQe9zhYweJpaL2Veb0pEOqfijRFSd/gYcC', 'professor')
ON DUPLICATE KEY UPDATE id = id;

-- ============================================================
-- TABELA: NOTIFICAÇÕES
-- Descrição: Notificações de atividade do sistema
-- ============================================================
CREATE TABLE notificacoes (
    id_notificacao INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
    lida TINYINT(1) NOT NULL DEFAULT 0,
    link VARCHAR(255) DEFAULT NULL,
    entidade_tipo VARCHAR(50) DEFAULT NULL,
    entidade_id INT DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificacoes_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_notificacoes_usuario (id_usuario),
    INDEX idx_notificacoes_lida (lida),
    INDEX idx_notificacoes_data (criado_em),
    INDEX idx_notificacoes_entidade (entidade_tipo, entidade_id)
);

-- ============================================================
-- TABELA: CURSOS
-- Descrição: Cursos disponíveis (A, B, C, D)
-- ============================================================
CREATE TABLE cursos (
    id_curso INT PRIMARY KEY AUTO_INCREMENT,
    letra VARCHAR(1) NOT NULL,
    nome_curso VARCHAR(100) NOT NULL,
    descricao TEXT,
    UNIQUE KEY uk_letra_nome (letra, nome_curso)
);

-- Inserir dados padrão dos cursos
INSERT INTO cursos (letra, nome_curso, descricao) VALUES
('A', 'Enfermagem', 'Curso Técnico em Enfermagem'),
('B', 'Informática', 'Curso Técnico em Informática'),
('C', 'Administração', 'Curso Técnico em Administração'),
('C', 'Meio Ambiente', 'Curso Técnico em Meio Ambiente'),
('D', 'Edificações', 'Curso Técnico em Edificações');

-- ============================================================
-- TABELA: TURMAS
-- Descrição: Cadastro de turmas/classes
-- ============================================================
CREATE TABLE turmas (
    id_turma INT PRIMARY KEY AUTO_INCREMENT,
    id_curso INT NOT NULL,
    nome_turma VARCHAR(100) NOT NULL,
    serie_turma INT NOT NULL,
    sala_turma VARCHAR(50) NOT NULL,
    ano_letivo INT NOT NULL,
    descricao_turma TEXT,
    id_professor INT NOT NULL,
    turma_origem_id INT NULL DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_turmas_cursos
        FOREIGN KEY (id_curso)
        REFERENCES cursos(id_curso)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_turmas_usuarios
        FOREIGN KEY (id_professor)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    
    INDEX idx_turmas_professor (id_professor),
    INDEX idx_turmas_curso (id_curso),
    INDEX idx_turmas_ano_letivo (ano_letivo),
    UNIQUE KEY uq_turmas_origem (turma_origem_id)
);

-- ============================================================
-- TABELA: ALUNOS
-- Descrição: Cadastro de alunos das turmas
-- ============================================================
CREATE TABLE alunos (
    id_aluno INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    matricula VARCHAR(7) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    id_turma INT NOT NULL,
    data_insercao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pcd ENUM('sim', 'nao') DEFAULT 'nao',
    rendimento DECIMAL(5,2) DEFAULT 0.00,
    status_aluno ENUM('ativo', 'concluinte') NOT NULL DEFAULT 'ativo',
    ano_conclusao SMALLINT NULL DEFAULT NULL,
    
    CONSTRAINT fk_alunos_turmas
        FOREIGN KEY (id_turma)
        REFERENCES turmas(id_turma)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    
    INDEX idx_alunos_turma (id_turma),
    INDEX idx_alunos_matricula (matricula)
);

CREATE TABLE aluno_turma_historico (
    id_aluno INT NOT NULL,
    id_turma INT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_aluno, id_turma),
    INDEX idx_aluno_turma_historico_turma (id_turma),
    CONSTRAINT fk_ath_aluno FOREIGN KEY (id_aluno) REFERENCES alunos(id_aluno) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ath_turma FOREIGN KEY (id_turma) REFERENCES turmas(id_turma) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ============================================================
-- TABELA: OCORRÊNCIAS
-- Descrição: Registro de ocorrências/comportamentos dos alunos
-- ============================================================
CREATE TABLE ocorrencias (
    id_ocorrencia INT PRIMARY KEY AUTO_INCREMENT,
    id_aluno INT NOT NULL,
    id_turma INT NOT NULL,
    id_professor INT NOT NULL,
    tipo_ocorrencia VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_ocorrencia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deletado_em TIMESTAMP NULL DEFAULT NULL,
    
    CONSTRAINT fk_ocorrencias_alunos
        FOREIGN KEY (id_aluno)
        REFERENCES alunos(id_aluno)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_ocorrencias_turmas
        FOREIGN KEY (id_turma)
        REFERENCES turmas(id_turma)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_ocorrencias_usuarios
        FOREIGN KEY (id_professor)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    
    INDEX idx_ocorrencias_aluno (id_aluno),
    INDEX idx_ocorrencias_turma (id_turma),
    INDEX idx_ocorrencias_professor (id_professor),
    INDEX idx_ocorrencias_data (data_ocorrencia),
    INDEX idx_ocorrencias_deletado_em (deletado_em)
);

-- ============================================================
-- TABELA: FREQUÊNCIAS
-- Descrição: Registro de frequência dos alunos nas aulas
-- ============================================================
CREATE TABLE frequencias (
    id_frequencia INT PRIMARY KEY AUTO_INCREMENT,
    id_turma INT NOT NULL,
    id_aluno INT NOT NULL,
    id_professor INT NOT NULL,
    data_frequencia DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT uq_frequencias_turma_aluno_data 
        UNIQUE (id_turma, id_aluno, data_frequencia),
    
    CONSTRAINT fk_frequencias_turmas 
        FOREIGN KEY (id_turma) 
        REFERENCES turmas(id_turma) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_frequencias_alunos 
        FOREIGN KEY (id_aluno) 
        REFERENCES alunos(id_aluno) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT fk_frequencias_professores 
        FOREIGN KEY (id_professor) 
        REFERENCES usuarios(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    INDEX idx_frequencias_turma (id_turma),
    INDEX idx_frequencias_aluno (id_aluno),
    INDEX idx_frequencias_professor (id_professor),
    INDEX idx_frequencias_data (data_frequencia)
);

-- ============================================================
-- TABELA: MÉDIA FINAL
-- Descrição: Lançamento de médias finais por bimestre
-- ============================================================
CREATE TABLE media_final (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_aluno INT NOT NULL,
    id_turma INT DEFAULT NULL,
    id_professor INT DEFAULT NULL,
    bimestre TINYINT NOT NULL,
    disciplina VARCHAR(100) NOT NULL,
    valor DECIMAL(5,2) DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_media_final_aluno_turma_bimestre UNIQUE (id_aluno, id_turma, bimestre, disciplina),
    CONSTRAINT fk_media_final_alunos FOREIGN KEY (id_aluno) REFERENCES alunos(id_aluno) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_media_final_turmas FOREIGN KEY (id_turma) REFERENCES turmas(id_turma) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_media_final_professores FOREIGN KEY (id_professor) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_media_final_aluno (id_aluno),
    INDEX idx_media_final_turma (id_turma),
    INDEX idx_media_final_professor (id_professor),
    INDEX idx_media_final_disciplina (disciplina)
);
-- ============================================================
-- ACOMPANHAMENTO PEDAGÓGICO E CONSELHO DE CLASSE
-- ============================================================
CREATE TABLE acompanhamentos_pedagogicos (
    id_acompanhamento INT AUTO_INCREMENT PRIMARY KEY,
    id_turma INT NOT NULL,
    id_aluno INT NOT NULL,
    id_responsavel INT NULL,
    data_registro DATE NOT NULL,
    motivo VARCHAR(150) NOT NULL,
    observacao TEXT NOT NULL,
    encaminhamento TEXT NULL,
    data_retorno DATE NULL,
    status ENUM('aberto', 'em_acompanhamento', 'concluido') NOT NULL DEFAULT 'aberto',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ap_turma FOREIGN KEY (id_turma) REFERENCES turmas(id_turma) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ap_aluno FOREIGN KEY (id_aluno) REFERENCES alunos(id_aluno) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ap_responsavel FOREIGN KEY (id_responsavel) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_ap_turma_data (id_turma, data_registro),
    INDEX idx_ap_aluno_status (id_aluno, status)
);

CREATE TABLE conselhos_classe (
    id_conselho INT AUTO_INCREMENT PRIMARY KEY,
    id_turma INT NOT NULL,
    id_responsavel INT NULL,
    data_conselho DATE NOT NULL,
    bimestre TINYINT NOT NULL,
    pauta VARCHAR(200) NOT NULL,
    ata TEXT NOT NULL,
    status ENUM('rascunho', 'finalizado') NOT NULL DEFAULT 'rascunho',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cc_turma FOREIGN KEY (id_turma) REFERENCES turmas(id_turma) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cc_responsavel FOREIGN KEY (id_responsavel) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_cc_turma_data (id_turma, data_conselho)
);

CREATE TABLE conselho_classe_alunos (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_conselho INT NOT NULL,
    id_aluno INT NOT NULL,
    observacao TEXT NOT NULL,
    encaminhamento TEXT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_cc_aluno UNIQUE (id_conselho, id_aluno),
    CONSTRAINT fk_cca_conselho FOREIGN KEY (id_conselho) REFERENCES conselhos_classe(id_conselho) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cca_aluno FOREIGN KEY (id_aluno) REFERENCES alunos(id_aluno) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_cca_aluno (id_aluno)
);

CREATE TABLE progressao_ano_letivo (
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

-- Bancos existentes devem aplicar config/migrations/002_acompanhamento_conselho.sql.
-- ============================================================
-- FIM DO SCRIPT
-- ============================================================
