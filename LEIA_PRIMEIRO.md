# Controle de Empréstimos — PHP + MySQL

Versão web baseada no projeto Python enviado, sem frameworks e sem dependências Composer. Backend PHP com PDO, páginas HTML/CSS e JavaScript apenas para confirmação de exclusão e exibição condicional dos campos de celular. As validações e regras são executadas também no servidor.

## 1. Requisitos

- PHP 8.2 ou superior (compatível com o PHP 8.5 usado no seu Windows).
- Extensões PHP: `pdo_mysql`, `mbstring` e `zip`.
- MySQL 8.0 ou superior ativo.
- Word ou LibreOffice para abrir os termos; Excel ou LibreOffice para as planilhas.

No PowerShell, confira:

```powershell
php -v
php --ini
php -m
```

No `php.ini` carregado, habilite, se necessário:

```ini
extension=pdo_mysql
extension=mbstring
extension=zip
```

Salve o arquivo e reinicie o servidor PHP se ele estiver aberto.

## 2. Configurar a conexão

Extraia o ZIP inteiro. Abra a pasta `controle-emprestimos-php` no VS Code e o terminal nessa pasta.

```powershell
Copy-Item config\database.example.php config\database.php
```

Edite `config/database.php`:

```php
<?php
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'controle_emprestimos_php',
    'username' => 'root',
    'password' => 'SUA_SENHA_DO_MYSQL',
];
```

Use suas próprias credenciais. Nenhuma senha ou documento gerado do projeto Python foi incluído neste pacote. A configuração real está ignorada pelo Git.

## 3. Criar o novo banco

Com o MySQL iniciado e a conexão preenchida, execute:

```powershell
php bin\install.php
```

Alternativa: abra `database/schema.sql` no MySQL Workbench e execute o script completo com o botão de raio.

O script cria **controle_emprestimos_php**, com as tabelas **colaboradores**, **ativos** e **emprestimos**. Ele não acessa nem altera o banco `controle_emprestimos` do Python. O banco novo começa vazio. Não é necessário executar as migrações do Python.

A conta usada na instalação precisa poder criar o banco e as tabelas. Após a instalação, a aplicação precisa de SELECT, INSERT, UPDATE e DELETE nas três tabelas; pode usar uma conta MySQL restrita a esse banco.

O instalador usa CREATE IF NOT EXISTS: não apaga dados, mas também não corrige uma estrutura previamente criada com colunas diferentes.

## 4. Iniciar a aplicação

No terminal, dentro da pasta do projeto:

```powershell
php -S localhost:8000 -t public
```

Abra **http://localhost:8000** no navegador. Mantenha o terminal aberto enquanto usa o sistema. Para encerrar: `Ctrl+C`.

Também é possível abrir `iniciar.bat` no Windows, depois de configurar e instalar o banco.

**Importante:** a raiz pública é `public`, nunca a pasta inteira do projeto. O servidor embutido é para execução local/desenvolvimento. Esta versão não inclui autenticação nem perfis; qualquer pessoa com acesso à aplicação pode consultar e alterar registros. Para uso em rede ou publicação, implemente autenticação, autorização e HTTPS e utilize um servidor PHP de produção.

## 5. Usar o sistema

1. Em **Ativos**, cadastre o equipamento, selecione Arklok ou Vivo e informe o chamado GLPI.
2. O patrimônio é opcional e exclusivo de Arklok. Os campos IMEI e número são opcionais para tipo `CELULAR`.
3. Em **Colaboradores**, cadastre login, nome, CPF, departamento, cargo e campus.
4. Em **Empréstimos → Novo empréstimo**, selecione a pessoa, um ativo disponível e informe a condição de saída. O registro usa a data atual de São Paulo.
5. Após salvar, baixe o **Termo de responsabilidade** na lista de empréstimos.
6. Use **Registrar devolução**, informe condição de retorno e chamado GLPI. O ativo volta a ficar disponível.
7. No filtro **Devolvidos**, baixe o **Termo de devolução**.
8. Use **Relatórios** para consultar tempo emprestado e distribuição por departamento. Os botões **Exportar Excel** geram arquivos XLSX reais; os filtros da lista são aplicados à exportação.

Os termos são gerados sob demanda a partir dos quatro modelos Word do projeto Python, com substituição segura dos campos. Os arquivos são baixados pelo navegador; não são gravados nas antigas pastas do Python. Reemissões usam os dados atuais dos cadastros, como no programa original: alterações posteriores no nome ou equipamento aparecem no novo documento. Guarde o DOCX baixado quando precisar conservar a versão da entrega. Baixar um termo não cria nem repete empréstimos.

## 6. Tabelas e colunas preservadas

| Tabela | Colunas |
| --- | --- |
| colaboradores | login, nome, CPF, departamento, cargo, campus |
| ativos | empresa, serial_number, patrimonio, tipo, modelo, marca, at_status, itens_entregues, id_chamado, imei_1, imei_2, numero_celular |
| emprestimos | id, id_colaborador, id_ativo, data_saida, data_devolucao, condicao_saida, condicao_retorno, observacoes, chamado_devolucao |

O ZIP Python não inclui o SQL de criação inicial nem um dump do banco. Os nomes acima foram conferidos nas consultas e migrações; os tipos e limites foram reconstruídos a partir das validações do código. Não é possível afirmar que todas as definições são idênticas ao banco instalado no seu computador sem um export de estrutura desse banco. Em particular, `setor` é exibido a partir de `colaboradores.departamento`, não é uma coluna nova de `ativos`.

As chaves estrangeiras usam ON UPDATE CASCADE para preservar os vínculos ao editar login ou serial. O patrimônio permanece único. Nenhuma tabela ou coluna adicional foi criada.

## 7. Regras e diferenças da versão web

- Status dos ativos controlado pelo fluxo de empréstimo/devolução, sem edição manual.
- Transações e bloqueios no MySQL protegem as movimentações concorrentes.
- IMEIs preenchidos exigem 15 dígitos; número de celular aceita de 8 a 15 dígitos e pontuação usual.
- Ao trocar o tipo para outro que não seja CELULAR, os campos de celular ficam vazios no banco.
- Serial, patrimônio, tipo, marca, modelo e itens ficam em maiúsculas. Nome, departamento e cargo usam iniciais maiúsculas.
- Nesta versão, ativos com qualquer histórico de empréstimos não podem ser excluídos. Isso preserva o histórico; ativos nunca emprestados podem ser excluídos com confirmação.
- Colaboradores têm cadastro e edição próprios; cadastre antes da entrega.
- Consultas preparadas, escape HTML e token de proteção dos formulários estão incluídos.
- As listas são carregadas integralmente; para inventários muito grandes, acrescente paginação.
- Datas não são retroativas; seguem a data da operação no servidor.

## 8. Organização para estudar

- `public/index.php`: entrada, rotas, validação da sessão e despacho das ações.
- `src/Database.php`: conexão PDO com MySQL.
- `src/LoanService.php`: consultas, validações e regras de negócio.
- `src/Documents.php`: geração de DOCX e XLSX sem bibliotecas externas.
- `src/bootstrap.php`: funções auxiliares e fuso horário.
- `views/`: telas HTML/PHP.
- `public/assets/`: CSS e JavaScript locais; não precisa de CDN ou internet.
- `config/database.example.php`: exemplo de conexão.
- `database/schema.sql`: banco e estrutura das três tabelas.
- `templates/`: modelos Word do Python, preparados para substituição dos campos em PHP.
- `tests/`: verificações de validação e teste de integração opcional com MySQL.

## 9. Verificar no seu ambiente

```powershell
php tests\validation.php
```

O teste de integração usa o banco configurado, cria identificadores de teste e remove seus próprios registros ao terminar. Execute em um banco de desenvolvimento:

```powershell
php tests\integration.php
```

O registro de validação entregue em `VALIDACAO.md` informa exatamente o que foi possível verificar no ambiente de criação.

## Problemas comuns

- **could not find driver:** habilite `extension=pdo_mysql` no php.ini indicado por `php --ini`.
- **Erro de conexão:** confira se o serviço MySQL está iniciado, host, porta, usuário e senha.
- **Banco/tabela não existe:** execute `php bin\install.php` ou importe `database/schema.sql` no Workbench.
- **DOCX/XLSX não baixa:** confira se a extensão `zip` aparece em `php -m` e se `templates` foi extraída por inteiro.
- **Porta 8000 em uso:** execute `php -S localhost:8001 -t public` e abra http://localhost:8001.
- **Já existe um registro:** serial, patrimônio e login precisam ser únicos. Use Editar para atualizar o cadastro existente.
