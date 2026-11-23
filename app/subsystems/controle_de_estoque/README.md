# Controle de estoque: recuperacao do esquema

Em hospedagem, defina `APP_ENV=production` para priorizar as credenciais de
`hospedagem` em `.env/config.php`. Sem essa variavel, o modulo decide pelo host
da requisicao; `localhost`, `.local` e `.test` priorizam `local`.

O aplicativo espera as tabelas `categorias`, `movimentacao`, `perdas_produtos`,
`produtos` e `ambientes` no banco de estoque. Antes da migracao, a base local
usava uma estrutura antiga para `movimentacao` e `produtos`.

## Aplicacao da migracao

1. Faca um backup completo do banco de estoque.
2. No phpMyAdmin, selecione o banco de estoque configurado para a instalacao.
3. Revise se o esquema da hospedagem corresponde ao esquema legado descrito
   nesta migracao e, somente se corresponder, execute uma unica vez
   [`001_repair_inventory_schema.sql`](./docs/database/migrations/001_repair_inventory_schema.sql).
4. Recarregue o subsistema e confirme as telas de produtos, ambientes,
   movimentacoes e perdas.

A migracao adiciona as colunas usadas pelo aplicativo em `produtos`, cria as
tabelas ausentes, usa os valores existentes de `natureza` para iniciar as
categorias e cria o ambiente padrao de ID 2. A tabela de movimentacoes antiga
e renomeada para `movimentacao_legada`; ela e mantida para preservar dados,
pois sua estrutura nao guarda todos os campos de movimentacao do aplicativo.
Os registros legados nao sao convertidos automaticamente.

A migracao e especifica para a estrutura legada identificada no banco local e
nao deve ser executada novamente. Nao a execute no banco da hospedagem sem
revisar o esquema, fazer backup e confirmar que a estrutura e compativel.
