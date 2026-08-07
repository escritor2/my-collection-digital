# 📚 Acervo Digital - Sua Biblioteca Inteligente

<p align="center">
  <img src="my-collection-digital/assets/banner.png" width="100%" alt="Acervo Digital Banner">
</p>

<p align="center">
  <b>A plataforma definitiva para organizar, ler e estudar seus livros com o poder da IA.</b>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Nuxt-00DC82?style=for-the-badge&logo=nuxt.js&logoColor=white" />
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Groq_AI-f55036?style=for-the-badge" />
  <img src="https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white" />
</p>

---

## 📑 Sumário

- [Funcionalidades Principais](#-funcionalidades-principais)
- [Tecnologias Utilizadas](#️-tecnologias-utilizadas)
- [Pré-requisitos](#-pré-requisitos)
- [Configurando o PHP (php.ini)](#-configurando-o-php-phpini)
- [Instalação Passo a Passo](#-instalação-passo-a-passo)
- [Variáveis de Ambiente](#-variáveis-de-ambiente)
- [Comandos Úteis](#-comandos-úteis-do-dia-a-dia)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Como usar o Chat com IA](#-como-usar-o-chat-com-ia)
- [Solução de Problemas (Troubleshooting)](#-solução-de-problemas-troubleshooting)
- [Licença](#-licença)
- [Equipe](#-equipe)

---

## ✨ Funcionalidades Principais

- 🔎 **Catálogo Inteligente**: Busque livros via Google Books, Open Library e iTunes com importação em um clique.
- 📖 **E-Reader Premium**: Leitor nativo para arquivos PDF e EPUB com salvamento automático de progresso.
- 🤖 **IA Anti-Spoiler (Groq)**: Converse com o livro! Tire dúvidas, peça resumos ou crie quizzes baseados apenas no que você já leu.
- 🏆 **Gamificação**: Ganhe XP e suba de nível enquanto lê e estuda.
- 🌐 **Social**: Compartilhe reviews, crie clubes de leitura e veja o que seus amigos estão lendo.
- 🌓 **Interface Moderna**: Design inspirado em glassmorphism com suporte total a modo escuro.

## 🛠️ Tecnologias Utilizadas

### Frontend
- **Framework**: [Nuxt 3](https://nuxt.com/)
- **UI Components**: [Shadcn Vue](https://www.shadcn-vue.com/)
- **Styling**: TailwindCSS & Vanilla CSS
- **Icons**: Lucide Vue Next

### Backend
- **Framework**: [Laravel 12](https://laravel.com/)
- **Auth**: Laravel Sanctum (Stateful) + Fortify
- **Permissões**: Spatie Laravel Permission
- **Mídia**: Spatie Media Library
- **Busca**: Laravel Scout + Meilisearch (opcional)
- **Database**: SQLite (Desenvolvimento)
- **AI Engine**: Groq API (Llama 3.1)

---

## ✅ Pré-requisitos

Antes de instalar, confirme que você tem tudo isso na sua máquina:

| Requisito | Versão mínima | Como checar |
|---|---|---|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18+ | `node -v` |
| npm | 9+ | `npm -v` |
| Extensões do PHP | ver lista abaixo | `php -m` |

> 💡 Se qualquer um desses comandos der "command not found" / "não é reconhecido como comando", o programa não está instalado ou não está no `PATH` do sistema. Instale-o antes de continuar.

### Extensões do PHP obrigatórias

O Laravel e as bibliotecas usadas no backend (Sanctum, Scout, Media Library, Guzzle para chamar Google Books/OpenLibrary/Groq) exigem as seguintes extensões **ativas** no seu PHP:

```
ctype, curl, dom, exif, fileinfo, filter, hash, iconv, json,
libxml, mbstring, openssl, pcre, pdo, pdo_sqlite, phar,
reflection, session, simplexml, tokenizer, xml, xmlwriter, zlib
```

- `curl` → obrigatório para consumir a API do Groq e os catálogos externos (Google Books, Open Library, iTunes).
- `pdo_sqlite` → obrigatório porque o projeto usa SQLite por padrão em desenvolvimento.
- `fileinfo` e `exif` → usados para validar/ler metadados de capas de livros e uploads.
- `gd` **ou** `imagick` (recomendado, não obrigatório) → usados pelo Spatie Media Library para gerar thumbnails de capas.

Se você já tem um PHP instalado (via XAMPP, Laragon, WAMP, Homebrew, apt, etc.) é bem provável que a maioria dessas extensões já venha ativada por padrão — a seção abaixo mostra como confirmar e ativar as que faltarem.

---

## ⚙️ Configurando o PHP (php.ini)

Essa é a parte que mais trava quem está começando, então vamos com calma. O `php.ini` é o arquivo de configuração central do PHP: é nele que você ativa extensões e ajusta limites (upload, memória, tempo de execução).

### 1. Descubra qual `php.ini` está sendo usado

Nem sempre existe só um `php.ini` na máquina — pode haver um para CLI (terminal) e outro para o servidor web (Apache/Nginx). O comando abaixo mostra exatamente qual arquivo o PHP do seu terminal está lendo:

```bash
php --ini
```

Você vai ver algo como:

```
Configuration File (php.ini) Path: /etc/php/8.2/cli
Loaded Configuration File:         /etc/php/8.2/cli/php.ini
```

A linha **"Loaded Configuration File"** é o arquivo que você precisa editar. Se aparecer `(none)`, significa que nenhum `php.ini` está sendo carregado — nesse caso, copie o `php.ini-development` que vem junto com o PHP para o local indicado em "Configuration File Path" e renomeie para `php.ini`.

> ⚠️ Como o `php artisan serve` usa o PHP de **linha de comando (CLI)**, é o `php.ini` do CLI que importa para rodar o backend localmente — não o do Apache/XAMPP, mesmo que você tenha os dois instalados.

### 2. Onde encontrar o php.ini em cada instalação comum

| Ambiente | Caminho típico do php.ini |
|---|---|
| XAMPP (Windows) | `C:\xampp\php\php.ini` |
| Laragon (Windows) | `C:\laragon\bin\php\php-8.2.x\php.ini` |
| WAMP (Windows) | `C:\wamp64\bin\php\php8.2.x\php.ini` |
| PHP via Homebrew (macOS) | `/opt/homebrew/etc/php/8.2/php.ini` |
| PHP via apt (Linux/Ubuntu) | `/etc/php/8.2/cli/php.ini` |
| Docker (imagem oficial `php`) | `/usr/local/etc/php/php.ini` |

Se tiver dúvida, sempre confie no resultado de `php --ini` — ele nunca mente.

### 3. Ativando uma extensão

Abra o `php.ini` em um editor de texto e procure a linha da extensão que você precisa. Extensões desativadas aparecem **comentadas**, com um `;` no início:

```ini
;extension=curl
;extension=pdo_sqlite
;extension=fileinfo
;extension=mbstring
;extension=gd
```

Remova o `;` do início da linha para ativá-la:

```ini
extension=curl
extension=pdo_sqlite
extension=fileinfo
extension=mbstring
extension=gd
```

Salve o arquivo e **reinicie o terminal** (ou o Apache, se estiver usando um servidor web) para que a mudança tenha efeito.

### 4. Confirmando que a extensão está ativa

```bash
php -m
```

Isso lista todas as extensões carregadas. Procure pelo nome (sem o `ext-` do composer). Se `curl`, `pdo_sqlite`, `mbstring`, `fileinfo` etc. aparecerem na lista, está tudo certo. Também dá para checar uma extensão específica direto:

```bash
php -m | grep curl        # Linux/macOS
php -m | findstr curl     # Windows (PowerShell/CMD)
```

### 5. Ajustando limites de upload (importante para este projeto!)

Como o Acervo Digital permite subir arquivos **PDF e EPUB** para o leitor, o `php.ini` padrão (que costuma limitar uploads a 2 MB) provavelmente vai bloquear livros maiores. Ajuste estas diretivas:

```ini
upload_max_filesize = 50M
post_max_size = 55M
memory_limit = 256M
max_execution_time = 120
```

> `post_max_size` deve ser **sempre maior** que `upload_max_filesize`, porque ele engloba o upload inteiro (arquivo + outros dados do formulário).

Depois de editar, salve, reinicie o terminal/servidor e confirme com:

```bash
php -i | grep upload_max_filesize
```

### 6. Extension_dir (erro comum no Windows)

Se ao ativar uma extensão você receber um erro do tipo `Unable to load dynamic library`, confira se a diretiva `extension_dir` no `php.ini` aponta para a pasta correta onde ficam os arquivos `.dll` (Windows) ou `.so` (Linux/macOS):

```ini
; Windows (ajuste para o caminho real da sua instalação)
extension_dir = "C:\xampp\php\ext"

; Linux/macOS geralmente já vem configurado corretamente pelo gerenciador de pacotes
```

---

## 🚀 Instalação Passo a Passo

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd my-collection-digital
```

### 2. Configuração do Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Abra o `.env` recém-criado e configure pelo menos:

```env
GROQ_API_KEY=sua_chave_aqui   # opcional, necessário só para o chat com IA
```

Crie o banco SQLite (o Laravel não cria o arquivo sozinho em algumas versões/SOs):

```bash
# Linux/macOS
touch database/database.sqlite

# Windows (PowerShell)
New-Item database/database.sqlite -ItemType File
```

Rode as migrations e crie o link de storage (necessário para exibir capas/arquivos enviados publicamente):

```bash
php artisan migrate
php artisan storage:link
```

Inicie o servidor:

```bash
php artisan serve
```

A API estará disponível em `http://localhost:8000`.

### 3. Configuração do Frontend (Nuxt)

Em outro terminal:

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

O frontend estará disponível em `http://localhost:3000`.

> Deixe `NUXT_PUBLIC_API_BASE_URL` vazio no `.env` do frontend durante o desenvolvimento local — o projeto infere a URL do backend automaticamente.

---

## 🔑 Variáveis de Ambiente

### Backend (`backend/.env`)

| Variável | Para que serve | Valor padrão em dev |
|---|---|---|
| `APP_URL` | URL onde a API roda | `http://localhost:8000` |
| `FRONTEND_URL` | URL do frontend, usada em e-mails/links | `http://localhost:3000` |
| `DB_CONNECTION` | Driver do banco | `sqlite` |
| `SANCTUM_STATEFUL_DOMAINS` | Domínios do frontend autorizados a autenticar via cookie | já vem com localhost configurado |
| `CORS_ALLOWED_ORIGINS` | Domínios liberados no CORS | já vem com localhost configurado |
| `GROQ_API_KEY` | Chave da API do Groq, ativa o chat com IA | vazio (feature desativada) |
| `GROQ_MODEL` | Modelo usado no chat | `llama-3.1-70b-versatile` |

### Frontend (`frontend/.env`)

| Variável | Para que serve | Valor padrão em dev |
|---|---|---|
| `NUXT_PUBLIC_APP_ENV` | Ambiente atual | `development` |
| `NUXT_PUBLIC_API_BASE_URL` | URL base da API Laravel | vazio (inferido automaticamente) |

---

## 🔧 Comandos Úteis do Dia a Dia

```bash
# Depois de alterar o .env do backend, sempre limpe o cache de config:
php artisan optimize:clear

# Recriar o banco do zero (apaga os dados!):
php artisan migrate:fresh

# Rodar a fila (necessário se QUEUE_CONNECTION não for "sync"):
php artisan queue:work

# Rodar os testes do backend:
php artisan test

# Formatar o código PHP (Pint):
./vendor/bin/pint

# Depois de alterar o .env do frontend, reinicie o servidor:
# (Ctrl+C e rode de novo)
npm run dev
```

---

## 📁 Estrutura do Projeto

```
my-collection-digital/
├── backend/          # API em Laravel 12
│   ├── app/           # Controllers, Models, Services, Actions...
│   ├── config/         # Configurações (cors, sanctum, database...)
│   ├── database/       # Migrations, seeders, database.sqlite
│   ├── routes/         # api.php, web.php
│   └── .env.example
├── frontend/         # Aplicação em Nuxt 3
│   ├── components/     # Componentes Vue
│   ├── pages/           # Rotas/páginas
│   ├── composables/     # Lógica reutilizável (useApi, useAuth...)
│   └── .env.example
└── docs/             # Documentação complementar
```

---

## 📖 Como usar o Chat com IA

1. Importe um livro no catálogo.
2. Na sua estante, clique em **Ler**.
3. Faça o upload do arquivo PDF ou EPUB.
4. Abra o painel lateral da IA e comece a estudar!

---

## 🩺 Solução de Problemas (Troubleshooting)

| Erro / Sintoma | Causa provável | Como resolver |
|---|---|---|
| `Class "PDO" not found` ou `could not find driver` | Extensão `pdo_sqlite` desativada no `php.ini` | Ative `extension=pdo_sqlite` (veja a seção [Configurando o PHP](#-configurando-o-php-phpini)) e reinicie o terminal |
| `cURL error 60` ou falha ao buscar livros/usar o chat de IA | Extensão `curl` desativada, ou certificados SSL desatualizados (comum no Windows) | Ative `extension=curl`; se persistir, baixe um `cacert.pem` atualizado e aponte `curl.cainfo` no `php.ini` |
| `SQLSTATE[HY000] [14] unable to open database file` | O arquivo `database/database.sqlite` não existe ou não tem permissão de escrita | Crie o arquivo (`touch database/database.sqlite`) e rode `php artisan migrate` novamente |
| Upload de PDF/EPUB falha silenciosamente ou trava em livros grandes | `upload_max_filesize` / `post_max_size` muito baixos no `php.ini` | Aumente os dois valores (ex.: `50M` / `55M`) e reinicie o terminal/servidor |
| `419 Page Expired` ou erro de CSRF ao logar pelo frontend | `SANCTUM_STATEFUL_DOMAINS` ou `CORS_ALLOWED_ORIGINS` não incluem a porta que o frontend está usando | Adicione a URL exata (com porta) do frontend nessas variáveis no `.env` do backend e rode `php artisan optimize:clear` |
| Capas de livros/imagens não aparecem no frontend | `php artisan storage:link` não foi executado | Rode `php artisan storage:link` dentro da pasta `backend` |
| `Class "Intervention\Image\..." not found` ou capas não são geradas | Nenhuma extensão de imagem (`gd` ou `imagick`) ativa | Ative `extension=gd` no `php.ini` |
| Mudanças no `.env` não têm efeito | O Laravel cacheou a configuração antiga | Rode `php artisan optimize:clear` (ou `php artisan config:clear`) |
| `Allowed memory size exhausted` | `memory_limit` do PHP muito baixo | Aumente `memory_limit` no `php.ini` (ex.: `256M`) |
| `command not found: php` / `php` não reconhecido | PHP não está no `PATH` do sistema | Adicione a pasta do PHP (ex.: `C:\xampp\php`) às variáveis de ambiente do sistema e reabra o terminal |
| `npm install` falha com erros de permissão (Linux/macOS) | Node instalado com permissões de root/sudo | Reinstale o Node via [nvm](https://github.com/nvm-sh/nvm) para evitar precisar de `sudo` |
| Frontend não consegue falar com o backend (erro de rede/CORS no console do navegador) | Backend não está rodando, ou `NUXT_PUBLIC_API_BASE_URL` aponta para a URL errada | Confirme que `php artisan serve` está ativo e revise a variável no `.env` do frontend |
| Chat com IA não responde / erro relacionado ao Groq | `GROQ_API_KEY` vazia ou inválida | Gere uma chave em [console.groq.com](https://console.groq.com/) e adicione ao `.env` do backend, depois rode `php artisan optimize:clear` |

> Ainda com problemas depois de checar a tabela acima? Rode `php artisan about` dentro da pasta `backend` — ele mostra um resumo do ambiente (versão do PHP, extensões, driver do banco, etc.) que ajuda muito a identificar o que está faltando.

---

## 📄 Licença
Distribuído sob a licença MIT. Veja `LICENSE` para mais informações.

---

## 👥 Equipe

Temos orgulho de apresentar os desenvolvedores por trás do **Acervo Digital**:

<p align="center">
  <img src="my-collection-digital/assets/team_banner_v2.png" width="100%" alt="Equipe Acervo Digital">
</p>

<p align="center">
  <a href="https://github.com/escritor2"><b>Gabriel (escritor2)</b></a> • <a href="https://github.com/mgabriela06"><b>Gabriela (mgabriela06)</b></a>
</p>

<p align="center">
  Desenvolvido com ❤️ para amantes de leitura.
</p>
