-- Executar uma vez para armazenar os campos equivalentes ao PEI separadamente nos documentos PAEE.
ALTER TABLE paee
  MODIFY data_atendimento DATE NULL,
  ADD COLUMN data_elaboracao DATE NULL AFTER nome_documento,
  ADD COLUMN responsavel_preenchimento VARCHAR(150) NULL AFTER data_elaboracao,
  ADD COLUMN objetivos_pedagogicos TEXT NULL AFTER responsavel_preenchimento,
  ADD COLUMN habilidades_desenvolver TEXT NULL AFTER objetivos_pedagogicos,
  ADD COLUMN estrategias_ensino TEXT NULL AFTER habilidades_desenvolver,
  ADD COLUMN recursos_acessibilidade TEXT NULL AFTER estrategias_ensino,
  ADD COLUMN adaptacoes_curriculares TEXT NULL AFTER recursos_acessibilidade,
  ADD COLUMN criterios_avaliacao TEXT NULL AFTER adaptacoes_curriculares,
  ADD COLUMN observacoes TEXT NULL AFTER criterios_avaliacao;
