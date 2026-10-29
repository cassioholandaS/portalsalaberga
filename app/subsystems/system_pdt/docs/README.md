# Sistema PDT

O sistema fica organizado por responsabilidade:

- `../app/main/config/`: conexão, segurança e migração do banco.
- `../app/main/config/database.sql`: arquivo único do esquema completo para instalações novas.
- `../app/main/config/migrations/`: alterações incrementais para bancos já existentes.
- `../app/main/controllers/`: endpoints e ações da aplicação.
- `../app/main/models/`: acesso e regras dos dados.
- `../app/main/views/`: telas de login e painéis por perfil.
- `../app/main/assets/images/`: imagens do sistema.
- `../app/main/assets/css/` e `../app/main/assets/js/`: estilos e scripts compartilhados.
- `tests/manual/`: verificações PHP e páginas de teste para desenvolvimento local. Os scripts PHP são bloqueados para acesso web.
- `tests/fixtures/`: referências antigas mantidas fora das rotas ativas.
- `docs/history/`: notas e guias de implementação anteriores.
- `docs/diagnostics/`: saídas de diagnóstico antigas.

O ponto de entrada do subsistema é `../default.php`, que encaminha para `app/main/index.php`.
Execute os testes PHP manuais a partir da raiz do subsistema:

```powershell
cd C:\xampp\htdocs\portalsalaberga\app\subsystems\system_pdt
php tests\manual\test_alunos.php
php tests\manual\test_classificacao_desempenho.php
```

Os testes que criam dados devem ser executados somente em uma base de desenvolvimento.
As interfaces autenticadas usam os temas Claro e Escuro, com preferência isolada em `localStorage`.

Para ativar acompanhamento pedagógico e conselho em bancos existentes, aplique manualmente `app/main/config/migrations/002_acompanhamento_conselho.sql` no banco `sistema_pdt` antes de abrir a tela do professor. A migração usa `CREATE TABLE IF NOT EXISTS` e pode ser reaplicada.
Para habilitar os metadados de entidade já usados pelo modelo de notificações, aplique também `app/main/config/migrations/003_notificacoes_entidade.sql`.

Para ativar a progressão automática, aplique `app/main/config/migrations/004_progressao_anual.sql` no banco `sistema_pdt`. A partir de 1º de janeiro, na primeira abertura autenticada do sistema, os alunos ativos do 1º e 2º anos avançam para novas turmas do mesmo curso, sala e PDT; os do 3º ano são arquivados como concluintes. As turmas antigas, seus vínculos históricos de alunos e os registros acadêmicos são mantidos. O sistema registra cada ano de destino para impedir a execução duplicada. As novas turmas de 1º ano continuam sendo cadastradas pelo gestor.

A migração registra o ano de instalação como referência inicial para evitar progressão retroativa. Se aplicada durante o ano letivo, a automação começa na virada seguinte; aplique e valide a migração antes do próximo 1º de janeiro.
