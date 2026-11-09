-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14/09/2026 às 21:33
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_pcd`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('master','aee','apoio','responsavel') NOT NULL DEFAULT 'responsavel',
  `status_cadastro` enum('aprovado','pendente','recusado') NOT NULL DEFAULT 'aprovado',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `foto` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `agenda`
--

CREATE TABLE `agenda` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `data_evento` date NOT NULL,
  `horario` time DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('ativo','cancelado') NOT NULL DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `matricula` varchar(7) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `sexo` varchar(20) DEFAULT NULL,
  `turma` varchar(100) DEFAULT NULL,
  `curso` varchar(150) DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `id_apoio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aluno_aspecto`
--

CREATE TABLE `aluno_aspecto` (
  `id` int(11) NOT NULL,
  `peso` int(11) NOT NULL,
  `id_aluno` int(11) NOT NULL,
  `id_aspecto` int(11) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aspectos`
--

CREATE TABLE `aspectos` (
  `id` int(11) NOT NULL,
  `aspecto` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacao`
--

CREATE TABLE `avaliacao` (
  `id` int(11) NOT NULL,
  `valencia` int(11) NOT NULL,
  `data` date NOT NULL,
  `periodo` enum('manhã','tarde','ausente') NOT NULL,
  `id_aluno_aspecto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contato`
--

CREATE TABLE `contato` (
  `id` int(11) NOT NULL,
  `tipo` enum('fone','whatsApp','email') NOT NULL,
  `valor` varchar(200) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `diagnosticos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `sigla` varchar(30) DEFAULT NULL,
  `descricao` varchar(500) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`),
  UNIQUE KEY `sigla` (`sigla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `quesitos_avaliacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `descricao` varchar(1000) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `excluido` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `alunos_quesitos` (
  `aluno_id` int(11) NOT NULL,
  `quesito_id` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `peso` int(11) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`aluno_id`,`quesito_id`),
  KEY `idx_alunos_quesitos_quesito` (`quesito_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `registros_apoio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aluno_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `periodo` enum('manha','tarde') NOT NULL,
  `observacao_geral` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_registros_apoio_aluno` (`aluno_id`,`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `frequencia_apoio` (
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

CREATE TABLE `respostas_apoio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registro_id` int(11) NOT NULL,
  `quesito_id` int(11) NOT NULL,
  `avaliacao` enum('feliz','neutro','triste') NOT NULL,
  `nota` tinyint(4) DEFAULT NULL,
  `valencia` tinyint(4) NOT NULL DEFAULT 0,
  `peso` int(11) NOT NULL DEFAULT 1,
  `observacao` varchar(1000) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_respostas_apoio_registro` (`registro_id`),
  KEY `idx_respostas_apoio_quesito` (`quesito_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `laudos`
--

CREATE TABLE `laudos` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `paee`
--

CREATE TABLE `paee` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `nome_documento` varchar(150) DEFAULT NULL,
  `data_atendimento` date NOT NULL,
  `profissional_responsavel` varchar(150) DEFAULT NULL,
  `necessidades_identificadas` text DEFAULT NULL,
  `objetivos_atendimento` text DEFAULT NULL,
  `recursos_utilizados` text DEFAULT NULL,
  `estrategias_aplicadas` text DEFAULT NULL,
  `tecnologias_assistivas` text DEFAULT NULL,
  `resultados_observados` text DEFAULT NULL,
  `encaminhamentos` text DEFAULT NULL,
  `observacoes_gerais` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pei`
--

CREATE TABLE `pei` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `nome_documento` varchar(150) DEFAULT NULL,
  `data_elaboracao` date NOT NULL,
  `responsavel_preenchimento` varchar(150) DEFAULT NULL,
  `objetivos_pedagogicos` text DEFAULT NULL,
  `habilidades_desenvolver` text DEFAULT NULL,
  `estrategias_ensino` text DEFAULT NULL,
  `recursos_acessibilidade` text DEFAULT NULL,
  `adaptacoes_curriculares` text DEFAULT NULL,
  `criterios_avaliacao` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `relatorios_acompanhamento`
--

CREATE TABLE `relatorios_acompanhamento` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `data_atendimento` date NOT NULL,
  `responsavel_registro` varchar(150) DEFAULT NULL,
  `desenvolvimento_observado` text DEFAULT NULL,
  `dificuldades_observadas` text DEFAULT NULL,
  `potencialidades` text DEFAULT NULL,
  `recomendacoes` text DEFAULT NULL,
  `conclusao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_agenda_data` (`data_evento`),
  ADD KEY `idx_agenda_status` (`status`);

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_alunos_matricula` (`matricula`),
  ADD KEY `idx_alunos_apoio` (`id_apoio`),
  ADD UNIQUE KEY `ux_alunos_id_apoio` (`id_apoio`);

--
-- Índices de tabela `aluno_aspecto`
--
ALTER TABLE `aluno_aspecto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_aluno_aspecto` (`id_aluno`,`id_aspecto`),
  ADD KEY `idx_aluno_aspecto_aluno` (`id_aluno`),
  ADD KEY `idx_aluno_aspecto_aspecto` (`id_aspecto`);

--
-- Restrições para tabela `alunos_quesitos`
--
ALTER TABLE `alunos_quesitos`
  ADD CONSTRAINT `fk_alunos_quesitos_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alunos_quesitos_quesito` FOREIGN KEY (`quesito_id`) REFERENCES `quesitos_avaliacao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Índices de tabela `aspectos`
--
ALTER TABLE `aspectos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_aspectos_aspecto` (`aspecto`);

--
-- Índices de tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_avaliacao_periodo` (`id_aluno_aspecto`,`data`,`periodo`),
  ADD KEY `idx_avaliacao_aluno_aspecto` (`id_aluno_aspecto`),
  ADD KEY `idx_avaliacao_usuario` (`id_usuario`),
  ADD KEY `idx_avaliacao_data` (`data`),
  ADD KEY `idx_avaliacao_periodo` (`periodo`);

--
-- Índices de tabela `contato`
--
ALTER TABLE `contato`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contato_usuario` (`id_usuario`);

--
-- Índices de tabela `laudos`
--
ALTER TABLE `laudos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_laudos_aluno` (`aluno_id`);

--
-- Índices de tabela `paee`
--
ALTER TABLE `paee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_paee_aluno` (`aluno_id`),
  ADD KEY `idx_paee_data` (`data_atendimento`);

--
-- Índices de tabela `pei`
--
ALTER TABLE `pei`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pei_aluno` (`aluno_id`),
  ADD KEY `idx_pei_data` (`data_elaboracao`);

--
-- Índices de tabela `relatorios_acompanhamento`
--
ALTER TABLE `relatorios_acompanhamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_relatorios_aluno` (`aluno_id`),
  ADD KEY `idx_relatorios_data` (`data_atendimento`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aluno_aspecto`
--
ALTER TABLE `aluno_aspecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aspectos`
--
ALTER TABLE `aspectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contato`
--
ALTER TABLE `contato`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `laudos`
--
ALTER TABLE `laudos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `paee`
--
ALTER TABLE `paee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pei`
--
ALTER TABLE `pei`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `relatorios_acompanhamento`
--
ALTER TABLE `relatorios_acompanhamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `tipo`, `status_cadastro`, `ativo`)
SELECT 'Master do Sistema', 'master@sistemapcd.local',
   '$2y$10$Yb17AhlPJQwhSf6h76U2qulQWtNBR2NCukRnPT9KbKJNWPn5hiQkW',
       'master', 'aprovado', 1
WHERE NOT EXISTS (
  SELECT 1 FROM `usuarios` WHERE `email` = 'master@sistemapcd.local'
);
--
-- Restrições para tabelas despejadas
--

ALTER TABLE `alunos`
  ADD CONSTRAINT `fk_alunos_apoio` FOREIGN KEY (`id_apoio`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `aluno_aspecto`
--
ALTER TABLE `aluno_aspecto`
  ADD CONSTRAINT `fk_aluno_aspecto_aluno` FOREIGN KEY (`id_aluno`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aluno_aspecto_aspecto` FOREIGN KEY (`id_aspecto`) REFERENCES `aspectos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD CONSTRAINT `fk_avaliacao_aluno_aspecto` FOREIGN KEY (`id_aluno_aspecto`) REFERENCES `aluno_aspecto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avaliacao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `contato`
--
ALTER TABLE `contato`
  ADD CONSTRAINT `fk_contato_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `laudos`
--
ALTER TABLE `laudos`
  ADD CONSTRAINT `fk_laudos_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `paee`
--
ALTER TABLE `paee`
  ADD CONSTRAINT `fk_paee_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `pei`
--
ALTER TABLE `pei`
  ADD CONSTRAINT `fk_pei_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `relatorios_acompanhamento`
--
ALTER TABLE `relatorios_acompanhamento`
  ADD CONSTRAINT `fk_relatorios_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
