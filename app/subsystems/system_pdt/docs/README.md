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

## Instalação e configuração

Para uma instalação nova, importe `app/main/config/database.sql` e configure o acesso ao MySQL/MariaDB no ambiente do PHP:

- `PDT_DB_HOST` (padrão local: `localhost`)
- `PDT_DB_PORT` (opcional)
- `PDT_DB_NAME` (padrão: `sistema_pdt`)
- `PDT_DB_USER` (padrão local: `root`)
- `PDT_DB_PASS` (padrão local: vazio)

Em ambiente escolar, configure usuário e senha próprios no servidor; não use a conta `root` nem grave credenciais no repositório. O SQL de instalação não cria contas padrão. O primeiro cadastro pelo login cria o Gestor principal; depois disso, o cadastro público fica restrito a professores. Faça a criação inicial do Gestor em ambiente controlado, antes de disponibilizar o sistema na rede. Em bancos já instalados com contas de desenvolvimento, revise e troque/remova essas contas antes de abrir o acesso escolar.

Atualmente não há tela administrativa para provisionar contas de Coordenador. Esse perfil precisa ser provisionado por um procedimento administrativo confiável; não habilite cadastro público para esse nível de acesso.

Para bancos existentes, aplique as migrações em `app/main/config/migrations/` na ordem numérica, verificando a compatibilidade da versão do MySQL/MariaDB e fazendo backup antes da execução. Em especial, as telas pedagógicas dependem das migrações `002` e `003`, e a progressão anual depende da `004`. Não execute migrações diretamente em produção sem validar primeiro em uma cópia do banco.
O antigo `app/main/config/migrate.php` foi desativado: ele alterava estruturas legadas (`notas`) que não são usadas pelo fluxo atual, baseado em `media_final`.

Para ativar acompanhamento pedagógico e conselho em bancos existentes, aplique manualmente `app/main/config/migrations/002_acompanhamento_conselho.sql` no banco `sistema_pdt` antes de abrir a tela do professor. A migração usa `CREATE TABLE IF NOT EXISTS` e pode ser reaplicada.
Para habilitar os metadados de entidade já usados pelo modelo de notificações, aplique também `app/main/config/migrations/003_notificacoes_entidade.sql`.

Para ativar a progressão automática, aplique `app/main/config/migrations/004_progressao_anual.sql` no banco `sistema_pdt`. A partir de 1º de janeiro, na primeira abertura autenticada do sistema, os alunos ativos do 1º e 2º anos avançam para novas turmas do mesmo curso, sala e PDT; os do 3º ano são arquivados como concluintes. As turmas antigas, seus vínculos históricos de alunos e os registros acadêmicos são mantidos. O sistema registra cada ano de destino para impedir a execução duplicada. As novas turmas de 1º ano continuam sendo cadastradas pelo gestor.

A migração registra o ano de instalação como referência inicial para evitar progressão retroativa. Se aplicada durante o ano letivo, a automação começa na virada seguinte; aplique e valide a migração antes do próximo 1º de janeiro.
