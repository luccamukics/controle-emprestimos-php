# Controle de Empréstimos de Ativos

Aplicação web desenvolvida em **PHP e MySQL** para gerenciar empréstimos e devoluções de equipamentos, acompanhar a disponibilidade dos ativos e emitir termos de responsabilidade e relatórios.

O projeto nasceu de uma necessidade prática de organização do atendimento de TI e foi desenvolvido a partir de um sistema anterior em Python. A versão web permite realizar as operações pelo navegador e centralizar os registros em um banco de dados.

## Sumário

- [Sobre o projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias utilizadas](#tecnologias-utilizadas)
- [Requisitos](#requisitos)
- [Instalação e configuração](#instalação-e-configuração)
- [Executando a aplicação](#executando-a-aplicação)
- [Fluxo de utilização](#fluxo-de-utilização)
- [Regras de negócio](#regras-de-negócio)
- [Documentos e relatórios](#documentos-e-relatórios)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Banco de dados](#banco-de-dados)
- [Validações e testes](#validações-e-testes)
- [Segurança e implantação](#segurança-e-implantação)
- [Backup](#backup)
- [Solução de problemas](#solução-de-problemas)
- [Possíveis evoluções](#possíveis-evoluções)
- [Autor](#autor)

## Sobre o projeto

Controlar equipamentos emprestados exige identificar quem recebeu cada ativo, quando a entrega ocorreu e em quais condições o equipamento foi devolvido.

Este sistema reúne essas informações em uma aplicação com:

- Cadastro de equipamentos e colaboradores.
- Registro das movimentações.
- Atualização automática da disponibilidade dos ativos.
- Emissão de documentos de entrega e devolução.
- Consulta e exportação de relatórios.

A aplicação utiliza PHP com orientação a objetos, acesso ao MySQL por PDO e separação entre entrada das requisições, regras de negócio, geração de documentos e telas.

Não utiliza framework PHP nem exige instalação de dependências pelo Composer.

## Funcionalidades

### Visão geral

- Quantidade de ativos cadastrados.
- Quantidade de equipamentos disponíveis e emprestados.
- Total de colaboradores cadastrados.
- Consulta dos empréstimos em aberto.
- Atalhos para as principais operações.

### Gestão de ativos

- Cadastro de notebooks, celulares e outros tipos de equipamento.
- Identificação por número de série.
- Seleção da empresa responsável: **Arklok** ou **Vivo**.
- Patrimônio opcional para equipamentos da Arklok.
- Registro de marca, modelo, acessórios e chamado GLPI.
- Campos de IMEI e número de telefone para celulares.
- Busca por serial, modelo, patrimônio ou colaborador.
- Filtro por disponibilidade.
- Edição de cadastros.
- Exclusão de ativos sem histórico de empréstimos.

### Gestão de colaboradores

- Cadastro de login, nome, CPF, departamento, cargo e campus.
- Busca por nome ou login.
- Edição dos dados cadastrados.
- Reutilização do cadastro em novas movimentações.

### Empréstimos e devoluções

- Seleção de colaborador e equipamento disponível.
- Registro da condição de saída e de observações.
- Identificação de cada empréstimo por um número próprio.
- Registro da condição de retorno e do chamado GLPI de devolução.
- Atualização automática do status do equipamento.
- Consulta de empréstimos em aberto, devolvidos ou de todo o histórico.

### Documentos e relatórios

- Termos de responsabilidade em **DOCX**.
- Termos de devolução em **DOCX**.
- Modelos específicos para notebooks e celulares.
- Reemissão dos documentos.
- Exportação de ativos e empréstimos em **XLSX**.
- Relatório de ativos emprestados por departamento.
- Consulta do tempo de empréstimo.

## Tecnologias utilizadas

| Tecnologia | Aplicação |
| --- | --- |
| PHP 8.2+ | Processamento das requisições e regras de negócio |
| PDO | Conexão e execução de consultas no MySQL |
| MySQL 8.0+ | Persistência dos cadastros e das movimentações |
| HTML5 | Estrutura das páginas |
| CSS3 | Estilização e adaptação das telas |
| JavaScript | Confirmações e exibição condicional de campos |
| ZipArchive | Manipulação dos arquivos DOCX e XLSX |
| Git e GitHub | Versionamento do código |

As páginas utilizam arquivos CSS e JavaScript locais, sem depender de CDN para o funcionamento básico da interface.

## Requisitos

Para executar o projeto:

- PHP **8.2 ou superior** disponível no terminal.
- MySQL **8.0 ou superior** em execução.
- Extensões PHP:
  - `pdo_mysql`
  - `mbstring`
  - `zip`
- Navegador atualizado.

Para abrir os arquivos exportados:

- Word ou LibreOffice Writer para documentos DOCX.
- Excel ou LibreOffice Calc para planilhas XLSX.

## Instalação e configuração

### 1. Obter o projeto

Clone o repositório:

```powershell
git clone https://github.com/luccamukics/controle-emprestimos-php.git
cd controle-emprestimos-php
```

Também é possível baixar o ZIP pelo GitHub e extrair a pasta inteira.

### 2. Verificar o PHP

Execute:

```powershell
php -v
php --ini
php -m
```

Esses comandos mostram, respectivamente:

- A versão do PHP.
- O arquivo de configuração carregado.
- As extensões disponíveis.

No `php.ini` indicado em **Loaded Configuration File**, habilite as extensões necessárias:

```ini
extension=pdo_mysql
extension=mbstring
extension=zip
```

Se a linha começar com `;`, remova esse caractere.

Após alterar o `php.ini`, reinicie o servidor PHP.

### 3. Configurar a conexão com o MySQL

Crie uma cópia do arquivo de exemplo:

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
    'username' => 'SEU_USUARIO_MYSQL',
    'password' => 'SUA_SENHA_MYSQL',
];
```

Preencha os dados de acesso do seu ambiente.

Mantenha o nome do banco como **`controle_emprestimos_php`**, conforme a configuração esperada pela aplicação.

> O arquivo `config/database.php` contém credenciais e deve permanecer fora do versionamento. O repositório deve incluir apenas `config/database.example.php`.

### 4. Criar o banco e as tabelas

Com o MySQL iniciado, execute na pasta principal do projeto:

```powershell
php bin\install.php
```

Resultado esperado:

```text
Banco controle_emprestimos_php e tabelas preparados com sucesso.
```

Alternativamente, abra `database/schema.sql` no MySQL Workbench e execute o script completo.

A conta utilizada na instalação precisa ter permissão para criar o banco e as tabelas.

> A instalação cria um banco vazio e separado do sistema Python. Não há importação automática dos dados da aplicação anterior.

## Executando a aplicação

### Acesso local pelo terminal

Na pasta principal do projeto:

```powershell
php -S localhost:8000 -t public
```

Abra no navegador:

```text
http://localhost:8000
```

Mantenha o terminal aberto durante o uso. Para encerrar o servidor, pressione **Ctrl+C**.

### Acesso local pelo Windows

Após configurar o PHP e instalar o banco, execute:

```text
iniciar.bat
```

O arquivo inicia o servidor local e informa o endereço de acesso.

Ele não inicia o serviço MySQL nem cria o banco automaticamente.

### Teste de acesso pela rede interna

Para permitir conexões de outros computadores durante um teste:

```powershell
php -S 0.0.0.0:8000 -t public
```

Descubra o IPv4 do computador servidor:

```powershell
ipconfig
```

Nos demais computadores, utilize:

```text
http://IP_DO_SERVIDOR:8000
```

O acesso depende da conectividade da rede e da liberação da porta TCP 8000 no firewall, restrita aos computadores ou à rede autorizada.

Nesse modelo:

- O programa e o MySQL permanecem no computador servidor.
- Os colaboradores utilizam apenas o navegador.
- Todos acessam os mesmos registros.
- O servidor precisa permanecer ligado e sem entrar em suspensão.
- O MySQL pode continuar configurado em `127.0.0.1`; não é necessário expor a porta do banco aos usuários.

> O `iniciar.bat` fornecido inicia o servidor em `localhost`. Para testar acesso pela rede, utilize o comando com `0.0.0.0`.

## Fluxo de utilização

### 1. Cadastrar o equipamento

Em **Ativos → + Novo ativo**:

1. Selecione a empresa.
2. Informe número de série, tipo, marca e modelo.
3. Preencha o chamado GLPI.
4. Informe patrimônio e acessórios, quando aplicável.
5. Para celulares, preencha os dados adicionais desejados.
6. Clique em **Salvar ativo**.

O equipamento será cadastrado como **Disponível**.

### 2. Cadastrar o colaborador

Em **Colaboradores → + Novo colaborador**:

1. Informe login, nome e CPF.
2. Preencha departamento, cargo e campus.
3. Clique em **Salvar colaborador**.

Antes de criar um cadastro, utilize a busca para verificar se a pessoa já existe.

### 3. Registrar um empréstimo

Em **Empréstimos → + Novo empréstimo**:

1. Selecione o colaborador.
2. Selecione um equipamento disponível.
3. Descreva a condição de saída.
4. Preencha observações, se necessário.
5. Clique em **Confirmar empréstimo**.

Após a confirmação:

- A movimentação aparecerá em **Em aberto**.
- O equipamento ficará **Emprestado**.
- O termo de responsabilidade poderá ser baixado na coluna **Ações**.

### 4. Registrar a devolução

Na lista de empréstimos em aberto:

1. Localize o registro pelo nome, login ou serial.
2. Clique em **Registrar devolução**.
3. Confira o equipamento e o colaborador.
4. Informe a condição de retorno e o chamado GLPI de devolução.
5. Clique em **Confirmar devolução**.

O equipamento voltará a ficar **Disponível**.

Para baixar o documento de retorno, utilize o filtro **Devolvidos** e clique em **Termo de devolução**.

## Regras de negócio

| Regra | Comportamento |
| --- | --- |
| Número de série | Obrigatório e único |
| Login do colaborador | Obrigatório e único |
| Patrimônio | Opcional, único e permitido somente para Arklok |
| Disponibilidade | Um ativo pode ter apenas um empréstimo aberto |
| Status do equipamento | Atualizado pelo fluxo de empréstimo e devolução |
| Exclusão de ativo | Permitida somente quando não há histórico de empréstimos |
| IMEI | Cada campo preenchido deve conter exatamente 15 dígitos |
| Telefone | Aceita de 8 a 15 dígitos, com pontuação usual |
| Mudança de tipo | Ao salvar um tipo diferente de CELULAR, os dados de celular são limpos |
| Chamado GLPI | Deve ser um número inteiro positivo |
| Datas | Preenchidas na operação, com o PHP configurado para São Paulo |
| Edição de serial ou login | Preserva os vínculos por atualização em cascata |

Serial, patrimônio, tipo, marca, modelo e itens entregues são salvos em maiúsculas. Nome, departamento e cargo utilizam iniciais maiúsculas.

Os limites de tamanho e as regras são validados no servidor.

As movimentações utilizam transações e bloqueios no banco para manter o registro do empréstimo ou devolução consistente com o status do equipamento.

## Documentos e relatórios

### Termos Word

Os arquivos são gerados a partir dos modelos da pasta `templates`.

Há modelos de responsabilidade e devolução para notebooks e celulares. O download é realizado pelo navegador, em formato DOCX.

**Pontos importantes:**

- Baixar um termo não cria outra movimentação.
- A reemissão utiliza os dados atuais dos cadastros.
- Documentos emitidos anteriormente devem ser guardados para preservar a versão da operação.
- A aplicação não realiza assinatura digital.

### Relatórios Excel

As exportações geram arquivos XLSX:

| Local | Exportação |
| --- | --- |
| Ativos | Equipamentos e informações associadas |
| Empréstimos | Movimentações conforme a busca e a situação selecionadas |
| Relatórios | Ativos emprestados por departamento e empréstimos em aberto |

Aplique os filtros e clique em **Filtrar** antes de exportar.

A coluna de tempo representa dias corridos de empréstimo. O sistema não possui prazo previsto de devolução nem cálculo de atraso.

## Estrutura do projeto

```text
controle-emprestimos-php/
├── bin/
│   └── install.php
├── config/
│   └── database.example.php
├── database/
│   └── schema.sql
├── public/
│   ├── assets/
│   │   ├── app.css
│   │   └── app.js
│   └── index.php
├── src/
│   ├── bootstrap.php
│   ├── Database.php
│   ├── Documents.php
│   └── LoanService.php
├── templates/
├── tests/
│   ├── integration.php
│   └── validation.php
├── views/
├── .gitignore
├── iniciar.bat
├── LEIA_PRIMEIRO.md
├── VALIDACAO.md
└── README.md
```

O arquivo `config/database.php` é criado localmente durante a configuração e não deve ser enviado ao repositório.

Os manuais em PDF e Word podem ser organizados em uma pasta `documentacao`, fora de `public`.

### Responsabilidade dos componentes

| Componente | Responsabilidade |
| --- | --- |
| `public/index.php` | Entrada das requisições e encaminhamento das ações |
| `src/Database.php` | Conexão PDO com o MySQL |
| `src/LoanService.php` | Consultas, validações e regras de negócio |
| `src/Documents.php` | Geração de DOCX e XLSX |
| `src/bootstrap.php` | Inicialização e funções auxiliares |
| `views/` | Telas da aplicação |
| `templates/` | Modelos dos termos Word |
| `database/schema.sql` | Criação do banco e das tabelas |
| `bin/install.php` | Execução da instalação pelo terminal |

## Banco de dados

**Nome do banco:** `controle_emprestimos_php`

| Tabela | Finalidade | Identificador |
| --- | --- | --- |
| `colaboradores` | Pessoas que recebem os equipamentos | `login` |
| `ativos` | Equipamentos disponíveis para movimentação | `serial_number` |
| `emprestimos` | Histórico de entregas e devoluções | `id` |

Cada empréstimo referencia um colaborador e um ativo. Um colaborador pode receber diferentes equipamentos, e um ativo pode acumular várias movimentações ao longo do tempo, respeitando o limite de um empréstimo aberto.

Na lista de ativos, **Setor** corresponde ao departamento do colaborador associado ao empréstimo em aberto.

A estrutura foi reconstruída a partir das consultas, validações e migrações do projeto Python, preservando os nomes de tabelas e colunas utilizados pelo código.

## Validações e testes

### Testes de validação

Execute:

```powershell
php tests\validation.php
```

São verificadas regras como:

- Normalização dos dados.
- Limites dos campos.
- Preenchimento obrigatório.
- IMEIs e telefone.
- Patrimônio por empresa.
- Números positivos.
- Escape de conteúdo HTML.

### Testes de integração

Com o banco configurado, execute em um ambiente de desenvolvimento:

```powershell
php tests\integration.php
```

O teste cria registros temporários e verifica o ciclo de empréstimo e devolução, a atualização de vínculos e os bloqueios previstos pelas regras de negócio. Ao terminar, remove os registros que criou.

### Ambiente de validação inicial

A validação inicial foi realizada com:

- PHP 8.3.6.
- MariaDB 10.11.14, utilizando o protocolo MySQL.
- Requisições HTTP aos formulários e às páginas.
- Verificação estrutural dos quatro modelos de documentos gerados.
- Leitura das exportações XLSX.

Os detalhes e os limites dessa verificação estão em `VALIDACAO.md`.

## Segurança e implantação

A aplicação inclui:

- Consultas preparadas com PDO.
- Escape de conteúdo exibido nas páginas.
- Tokens de proteção contra CSRF nos formulários.
- Transações nas movimentações de equipamentos.
- Configuração de conexão fora da pasta pública.
- Cabeçalhos HTTP de proteção.

### Limitações atuais

- Não há autenticação ou perfis de acesso.
- Quem consegue acessar a aplicação pode consultar e alterar os registros disponíveis nas telas.
- Não há cancelamento ou edição de empréstimos e devoluções pela interface.
- Não há importação automática dos dados do sistema Python.
- As listas ainda não possuem paginação.

O servidor embutido do PHP é destinado a desenvolvimento e testes. Para uso compartilhado permanente, a implantação deve incluir autenticação, autorização, HTTPS e um servidor como Apache, Nginx com PHP-FPM ou IIS com FastCGI.

A raiz do servidor web deve apontar para **`public`**, preservando as configurações e os demais arquivos internos fora do acesso direto.

Não publique no repositório:

- Senhas ou o arquivo `config/database.php`.
- Arquivos `.env` com credenciais.
- Dumps do banco com dados reais.
- Termos preenchidos ou assinados.
- Relatórios com informações pessoais.

## Backup

Os cadastros e as movimentações ficam armazenados no MySQL.

Uma rotina de backup deve contemplar:

1. Exportação da estrutura e dos dados do banco `controle_emprestimos_php`.
2. Cópia dos termos emitidos e assinados.
3. Preservação dos modelos e da configuração necessária à recuperação.
4. Teste de restauração em ambiente separado.

Copiar apenas a pasta do projeto ou exportar uma planilha não substitui um backup completo do banco.

O instalador cria a estrutura inicial; ele não recupera dados perdidos.

## Solução de problemas

| Problema | Verificação |
| --- | --- |
| `php` não é reconhecido | Confira a instalação do PHP e sua inclusão no PATH |
| Configuração de conexão ausente | Copie `database.example.php` para `database.php` e preencha os dados |
| `could not find driver` | Habilite `pdo_mysql` no `php.ini` carregado |
| Extensão ZIP ausente | Habilite `extension=zip` e reinicie o servidor |
| Erro ao conectar ao banco | Confira serviço MySQL, host, porta, usuário, senha e banco instalado |
| Porta 8000 ocupada | Encerre outra execução ou utilize uma porta diferente |
| Registro duplicado | Confira login, serial e patrimônio; use Editar quando o cadastro já existir |
| Ativo indisponível | Verifique se ele possui empréstimo em aberto |
| Sessão expirada | Atualize o formulário e confira se a operação já foi salva antes de repetir |
| Acesso pela rede não funciona | Confira endereço IP, conectividade, firewall e inicialização com `0.0.0.0` |

### HTTP ERROR 500

Consulte a mensagem exibida no terminal em que o servidor PHP está sendo executado. O código 500, isoladamente, não identifica a causa.

Para verificar a sintaxe de um arquivo:

```powershell
php -l src\LoanService.php
```

### Erro de declaração strict_types

Se aparecer:

```text
strict_types declaration must be the very first statement in the script
```

Confira se o arquivo começa diretamente com:

```php
<?php
declare(strict_types=1);
```

Remova conteúdo anterior à abertura PHP e salve o arquivo em **UTF-8 sem BOM**.

### Confirmar a extensão ZIP no Windows

```powershell
php -m | findstr /i zip
```

Se a extensão foi habilitada durante a execução da aplicação, pare o servidor e inicie novamente.

## Possíveis evoluções

Melhorias que podem ser implementadas em versões futuras:

- Autenticação e perfis de acesso.
- Registro de auditoria das operações.
- Prazo previsto de devolução e acompanhamento de atrasos.
- Cancelamento de movimentações com justificativa.
- Armazenamento da versão dos dados utilizada em cada termo.
- Paginação das consultas.
- Importação de dados.
- Automação de testes no GitHub Actions.

Esses itens representam possibilidades de evolução e não funcionalidades disponíveis na versão atual.

## Autor

Desenvolvido por **Lucca da Silva Mukics**.

Projeto criado para aplicar conhecimentos de desenvolvimento backend em uma necessidade prática de gestão de ativos e atendimento de TI.

[Perfil no GitHub](https://github.com/luccamukics)
