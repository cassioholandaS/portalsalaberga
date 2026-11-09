-- Executar uma vez para permitir o cadastro do cartão PAEE antes do preenchimento do documento.
ALTER TABLE paee MODIFY data_atendimento DATE NULL;
