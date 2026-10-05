# MPP — Maria Papel Papelaria

Sistema web de gestão de encomendas de manuais escolares, desenvolvido para a papelaria **Maria Papel Papelaria**, no âmbito da PAP (Prova de Aptidão Profissional) do curso profissional de [curso], na escola [nome da escola].

## O que é

A MPP automatiza o processo de encomenda de manuais escolares entre a papelaria, as escolas/agrupamentos e as editoras: desde a gestão da estrutura escolar (anos letivos, anos escolares, disciplinas, agrupamentos) e do catálogo de manuais, até ao registo de encomendas, acompanhamento de estado, reposição de stock, expedição via VASP e comunicação automática por email com os encarregados de educação.

## Funcionalidades

- **Autenticação** — login com bloqueio após tentativas falhadas, alteração de password, gestão de utilizadores e permissões de administrador
- **Estrutura escolar** — gestão de anos letivos, anos escolares, disciplinas, agrupamentos e localizações
- **Manuais e editoras** — catálogo de manuais, gestão de editoras, importação de manuais por ficheiro Excel
- **Encomendas** — criação, pesquisa, detalhe, tratamento e cancelamento de encomendas; controlo de caução
- **Reposição de stock** — registo de reposições e alertas de manuais a encomendar
- **Importação de artigos VASP** — leitura do PDF recebido da distribuidora VASP (jornais e revistas), extração automática da tabela de artigos (EAN, preço, IVA, etc.) e conversão para Excel, pronto a importar no sistema de faturação
- **Importação de manuais via Excel** — carregamento do catálogo de manuais por ficheiro Excel
- **Emails automáticos** — confirmação de encomenda e avisos de levantamento, com PDF em anexo, via PHPMailer
- **Dashboard** — gráficos de atividade (Chart.js)

## Tecnologias

- **Backend:** PHP 8.0+ (procedural), MySQL (mysqli, prepared statements)
- **Frontend:** HTML5, CSS (tema [Editorial, da HTML5 UP](https://html5up.net/editorial)), jQuery, Chart.js
- **Gestão de dependências:** Composer
  - `phpmailer/phpmailer` — envio de emails
  - `vlucas/phpdotenv` — variáveis de ambiente
  - `tecnickcom/tcpdf` — geração de PDF
  - `smalot/pdfparser` — leitura de PDF
  - `shuchkin/simplexlsx` / `simplexlsxgen` — leitura e geração de Excel

## Estrutura do projeto

```
├── bd/                     # Scripts SQL (schema e dados de teste)
├── assets/                 # CSS, JS e webfonts do tema
├── images/                 # Imagens e logótipo
├── avisos/                 # PDFs de avisos de encomenda gerados
├── db_connect.php          # Ligação à base de dados
├── login.php / logout.php  # Autenticação
├── header.php / footer.php / menu.php   # Elementos comuns de layout
├── gestao_*.php            # Páginas de gestão (manuais, utilizadores, editoras, estrutura escolar...)
├── encomendar_manuais.php  # Criação de encomendas
├── detalhe_encomenda.php / editar_encomenda.php / pesquisar_encomendas.php
├── tratar_encomendas.php / estado_encomendas.php
├── reposicao.php / manuais_a_encomendar.php / verificar_caucoes.php
├── expedicao_vasp.php      # Página de importação de artigos VASP (upload do PDF, revisão e exportação)
├── extrairTabela.php       # Extração da tabela de artigos a partir do PDF da VASP
├── exportarExcel.php       # Geração do ficheiro Excel com os artigos extraídos
├── carregar_manuais.php    # Importação do catálogo de manuais via Excel
├── enviar_email.php        # Envio de emails (PHPMailer)
└── index.php                # Página inicial / dashboard
```

## Base de dados

O schema está em `bd/mpp3_bd.sql`. Principais tabelas: `utilizador`, `ano_letivo`, `ano_escolar`, `agrupamento`, `disciplina`, `editora`, `manual`, `manual_agrupamento`, `manual_ano_escolar`, `encomenda`, `encomenda_editora`, `encomenda_manual`, `observacao_encomenda`, `reposicao`.

`bd/encomendas_teste.sql` contém dados de exemplo para testes.

## Pré-requisitos

- PHP 8.0 ou superior, com extensão `mysqli`
- MySQL
- Composer
- Servidor local (ex.: XAMPP) ou equivalente com Apache + PHP + MySQL

## Instalação

1. Clonar o repositório e colocar a pasta no servidor local (ex.: `htdocs/`, no caso do XAMPP).
2. Instalar as dependências:
   ```
   composer install
   ```
3. Criar a base de dados e importar o schema:
   ```
   mysql -u root -p -e "CREATE DATABASE mpp3_bd"
   mysql -u root -p mpp3_bd < bd/mpp3_bd.sql
   ```
   (opcional) importar `bd/encomendas_teste.sql` para dados de exemplo.
4. Configurar a ligação à base de dados em `db_connect.php` (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`) conforme o ambiente local.
5. Criar um ficheiro `.env` na raiz do projeto com as credenciais de envio de email:
   ```
   SMTP_USER=exemplo@gmail.com
   SMTP_PASS=xxxxxxxxxxxxxxxx
   ```
6. Aceder a `login.php` através do servidor local (ex.: `http://localhost/MPP/login.php`).

## Autor

João Castro — projeto desenvolvido individualmente no âmbito da PAP, ano letivo 2025/2026.

## Licença

O código da aplicação é de autoria própria. O tema visual (`assets/`, layout base) é o **Editorial**, da [HTML5 UP](https://html5up.net), disponível sob licença [CCA 3.0](https://html5up.net/license) — ver `LICENSE.txt`.