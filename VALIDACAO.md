# Validação realizada

Data: 27/10/2026.

## Ambiente

- PHP CLI 8.3.6 com PDO MySQL, mbstring e ZipArchive.
- MariaDB 10.11.14 em banco de teste isolado, usando o protocolo MySQL.
- Servidor HTTP embutido do PHP.
- Leitura dos documentos com parser XML; leitura dos XLSX com openpyxl.

## Resultados

- Sintaxe de todos os arquivos PHP: aprovada.
- 14 verificações de validação: aprovadas (maiúsculas, limpeza de campos de celular para notebooks, IMEI, telefone, patrimônio Vivo, limites, obrigatoriedade, números positivos e escape HTML).
- 9 verificações de integração: aprovadas (cadastro de ativo disponível, empréstimo, bloqueio de segunda entrega, exclusão bloqueada em aberto, atualização da chave estrangeira ao editar serial, devolução gravada, ativo novamente disponível, bloqueio de devolução repetida e preservação do histórico encerrado).
- Rotas HTTP de painel, ativos, colaboradores, empréstimos, relatórios e formulários: respostas corretas.
- Cadastros de pessoa, notebook e celular por formulários HTTP: aprovados.
- Empréstimo e devolução por HTTP: aprovados.
- POST sem token de sessão: rejeitado com HTTP 422.
- Texto contendo caracteres HTML: exibido com escape, sem interpretação de tags.
- Busca sem resultados: estado vazio exibido.
- Termos DOCX de responsabilidade e devolução para notebook e celular: os quatro arquivos gerados são ZIPs íntegros, com XML válido, campos substituídos e escape de caracteres especiais; IMEIs presentes nos modelos de celular.
- Exportações XLSX de ativos, empréstimos e departamentos: abertas por openpyxl; filtro de ativo respeitado.
- Caminho de página arbitrário e acesso HTTP à configuração fora de public: bloqueados.

## Limites desta verificação

Não houve teste no Windows ou no MySQL 8.0 instalado pelo usuário. O banco de integração foi MariaDB, não uma instância do MySQL 8.0. Não foi feita inspeção visual em navegador nem no Word/Excel; os arquivos foram conferidos estruturalmente. O .bat foi preparado para Windows, sem execução neste ambiente Linux.

Os bloqueios e as transações foram exercitados no fluxo sequencial e em tentativas repetidas; não houve teste de carga com múltiplos usuários simultâneos.

O ZIP original não traz o DDL inicial. As mesmas três tabelas e os mesmos nomes de colunas referenciados no código foram mantidos; os tipos foram reconstruídos a partir das validações e migrações disponíveis. Para comparar todos os detalhes com o banco Python real, seria necessário um export de estrutura desse banco.

Nenhum dado real, senha, .env ou termo já preenchido do projeto Python foi incluído no pacote de entrega. A configuração de teste também não faz parte do ZIP.
