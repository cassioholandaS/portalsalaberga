# entradasaida

## Configuração do banco de dados

O sistema lê a conexão MySQL destas variáveis de ambiente:

- `DB_HOST` (padrão: `localhost`)
- `DB_PORT` (opcional)
- `DB_NAME` (padrão: `entradasaida`)
- `DB_USER` (padrão: `root`)
- `DB_PASSWORD` (padrão: vazio)

No ambiente de hospedagem, configure `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASSWORD` com os dados fornecidos pelo provedor; defina `DB_PORT` se ele não usar a porta padrão. Não coloque senhas no código-fonte nem no repositório. O PHP do servidor precisa ter a extensão `pdo_mysql` habilitada e permissão de conexão ao banco.

Defina também `APP_ENV=production`. Em requisições hospedadas, a aplicação exige que as variáveis `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASSWORD` estejam configuradas; ela não tenta conectar usando as credenciais locais do XAMPP.

## Perfil da recepção

O tipo de usuário `usuario_recepcao`, atribuído à pessoa para o sistema Entrada/saída no gerenciador de permissões, ativa a interface simplificada. Os usuários com outros tipos, como `Admin_entrada_saida`, continuam usando a interface padrão. O tipo é carregado na sessão durante o login; não é necessário configurar e-mails nem usar parâmetros de URL para selecionar a interface.

O perfil da recepção terá acesso a registrar atrasos, registrar saídas antecipadas, registrar saídas para estágio, consultar ausências registradas e excluir atrasos pendentes ou do histórico, assim como o administrador.

Os atrasos podem ser validados até as 17h do mesmo dia; depois desse horário, a validação fica desabilitada. Após a validação, a opção `Impressão` reúne o registro do atraso (ocorrência e justificativa) e a autorização para entrar em sala (horário e aula autorizada), sem criar um segundo atraso.

## Banco de dados

Para uma instalação nova, crie o banco configurado em `DB_NAME` e importe `app/main/docs/entradasaida.sql`. O dump já contém toda a estrutura necessária para o sistema, incluindo restrições por turma e validação de atrasos via QR Code. Não é necessário importar outros arquivos SQL.

O dump também contém os dados presentes na exportação. Confira se deseja copiá-los para a nova máquina antes de importá-lo. Não importe esse arquivo sobre um banco em uso: ele é destinado à criação de uma instalação nova.

Para atualizar uma instalação existente que ainda não possui a coluna `justificativa_atraso` em `registro_entrada`, execute `app/main/docs/migrations/2026-10-23-justificativa-atraso-manual.sql` no banco configurado em `DB_NAME`. A migração verifica se a coluna já existe antes de alterá-la.

// Leia toda a pasta do subsistema de `entradasaida` e, analisando todo o seu funcionamento e estrutura - obs: somente a tela mais simples com os cards, correspondente do usuário com o tipo `usuario_recepcao` - produza um manual de instruções tipo storyboard, com prints de cada interface, trazendo setas que apontem para cada botão, campo, interface, etc com explicação detalhada. Se baseie no modelo de manual presente no subsistema de `biblioteca` que pertence a mesma hierarquia de pastas aqui no workspace, com a diferença de que deve agora ter a aparencia de um storyboard com prints das telas
