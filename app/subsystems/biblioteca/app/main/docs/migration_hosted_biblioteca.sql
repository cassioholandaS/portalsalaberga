-- Migration do banco u750204740_sistBiblioteca para a estrutura da biblioteca local.
-- Executar com permissoes administrativas suficientes para acessar u750204740_sysPDT.

USE `u750204740_sistBiblioteca`;
SET FOREIGN_KEY_CHECKS = 0;

-- tabelas legadas que nao pertencem mais ao sistema
DROP TABLE IF EXISTS `aluno`;
DROP TABLE IF EXISTS `turma`;
DROP TABLE IF EXISTS `curso`;

CREATE TABLE IF NOT EXISTS `categoria` (
  `id` int(11) NOT NULL,
  `categorias` char(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `categoria` ADD PRIMARY KEY IF NOT EXISTS (`id`);
ALTER TABLE `categoria` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `categoria` AUTO_INCREMENT = 5;

INSERT IGNORE INTO `categoria` (`id`, `categorias`) VALUES
  (1, 'geral'),
  (2, 'didatico'),
  (3, 'paradidatico'),
  (4, 'tecnico');

-- adiciona temporariamente a nova referencia e converte os valores antigos do ENUM
ALTER TABLE `catalogo` ADD COLUMN IF NOT EXISTS `id_categoria` int(11) NOT NULL DEFAULT 1 AFTER `prateleiras`;

UPDATE `catalogo`
SET `id_categoria` = CASE LOWER(TRIM(`categoria`))
  WHEN 'didático' THEN 2
  WHEN 'didatico' THEN 2
  WHEN 'paradidático' THEN 3
  WHEN 'paradidatico' THEN 3
  WHEN 'técnico' THEN 4
  WHEN 'tecnico' THEN 4
  ELSE 1
END;

ALTER TABLE `catalogo` DROP COLUMN IF EXISTS `categoria`;
ALTER TABLE `catalogo` ADD KEY IF NOT EXISTS `id_categoria` (`id_categoria`);

-- estrutura que o genero possui no schema local
ALTER TABLE `genero` ADD COLUMN IF NOT EXISTS `id_categoria` int(11) NOT NULL DEFAULT 1;
ALTER TABLE `genero` ADD KEY IF NOT EXISTS `id_categoria` (`id_categoria`);

SET FOREIGN_KEY_CHECKS = 1;

-- retira, de forma segura, constraints com os mesmos nomes antes de recria-las
ALTER TABLE `catalogo` DROP FOREIGN KEY IF EXISTS `catalogo_ibfk_3`;
ALTER TABLE `catalogo` ADD CONSTRAINT `catalogo_ibfk_3`
  FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id`);

ALTER TABLE `genero` DROP FOREIGN KEY IF EXISTS `genero_ibfk_1`;
ALTER TABLE `genero` ADD CONSTRAINT `genero_ibfk_1`
  FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id`);

ALTER TABLE `devolucao` DROP FOREIGN KEY IF EXISTS `devolucao_ibfk_1`;
ALTER TABLE `devolucao` ADD CONSTRAINT `devolucao_ibfk_1`
  FOREIGN KEY (`id_emprestimo`) REFERENCES `emprestimo` (`id`);

ALTER TABLE `emprestimo` DROP FOREIGN KEY IF EXISTS `emprestimo_ibfk_1`;
ALTER TABLE `emprestimo` ADD CONSTRAINT `emprestimo_ibfk_1`
  FOREIGN KEY (`id_aluno`) REFERENCES `u750204740_sysPDT`.`alunos` (`id_aluno`);

ALTER TABLE `emprestimo` DROP FOREIGN KEY IF EXISTS `emprestimo_ibfk_2`;
ALTER TABLE `emprestimo` ADD CONSTRAINT `emprestimo_ibfk_2`
  FOREIGN KEY (`id_catalogo`) REFERENCES `catalogo` (`id`);
