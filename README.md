# Kanban (PHP) — WAMP

API e backend em PHP (sem framework) com roteamento próprio, PDO e testes com PHPUnit.

## Requisitos

- Windows + WampServer (Apache + MySQL + PHP)
- PHP 8.3+ (recomendado 8.3 ou superior)
- Extensões PHP:
  - `pdo` (obrigatório)
  - `pdo_mysql` (obrigatório para rodar com MySQL)
  - `pdo_sqlite` (opcional; usado em alguns testes/migrations SQLite)
- Composer 2+

## Instalação (passo a passo)

### 1) Clonar / colocar o projeto no `www`

Coloque este projeto em:

- `c:\wamp64\www\kanban`

### 2) Instalar dependências PHP

No diretório do projeto:

```bash
composer install
```

### 3) Criar banco MySQL

No MySQL do WAMP (phpMyAdmin ou cliente de sua preferência), crie o banco:

- `kanban`

Se quiser outro nome, ajuste via variáveis de ambiente (ver seção “Configuração”).

### 4) Configurar conexão com banco (MySQL)

O arquivo de configuração lê variáveis de ambiente:

- [config/database.php](file:///c:/wamp64/www/kanban/config/database.php)

Variáveis suportadas:

- `DB_DRIVER` (default: `mysql`)
- `DB_HOST` (default: `127.0.0.1`)
- `DB_PORT` (default: `3306`)
- `DB_DATABASE` (default: `kanban`)
- `DB_USERNAME` (default: `root`)
- `DB_PASSWORD` (default: vazio)
- `DB_CHARSET` (default: `utf8mb4`)

Formas comuns de definir no Windows/WAMP:

- Variáveis de ambiente do Windows (reinicie o WAMP depois)
- `SetEnv` no VirtualHost do Apache (reinicie o Apache depois)

### 5) Rodar migrations

As migrations ficam em:

- MySQL: `database/migrations/mysql/001_mvp.sql`
- SQLite: `database/migrations/sqlite/001_mvp.sql`

Para aplicar no driver configurado (`DB_DRIVER`):

```bash
php database/migrate.php
```

## Executar o projeto

Você pode executar de duas formas.

### Opção A) Servidor embutido do PHP (mais simples)

```bash
php -S localhost:8000 -t public
```

A aplicação fica em:

- `http://localhost:8000`

### Opção B) Apache do WAMP (VirtualHost)

1. Configure um VirtualHost apontando o `DocumentRoot` para:
   - `c:/wamp64/www/kanban/public`

2. Garanta que o Apache encaminhe todas as rotas para `public/index.php`.

Este projeto não vem com `.htaccess` por padrão. Você pode fazer isso no VirtualHost via `mod_rewrite` (ou alternativa equivalente).

Exemplo de VirtualHost (ajuste o `ServerName` e caminhos):

```apache
<VirtualHost *:80>
  ServerName kanban.local
  DocumentRoot "c:/wamp64/www/kanban/public"

  <Directory "c:/wamp64/www/kanban/public">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

Se preferir usar `.htaccess`, crie `public/.htaccess` com:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

## Endpoints úteis

### Healthcheck

- `GET /health`

Retorna:

```json
{"ok":true}
```

### Autenticação (sessão + CSRF + rate limit)

As rotas da API estão em:

- [routes/api.php](file:///c:/wamp64/www/kanban/routes/api.php)

#### Login

- `POST /api/auth/login`
- `Content-Type: application/json`

Body:

```json
{"email":"a@a.com","password":"secret"}
```

Resposta (exemplo):

```json
{"ok":true,"csrf_token":"..."}
```

Observações:

- O login cria sessão no servidor (cookies).
- `csrf_token` deve ser usado nas requisições mutáveis (POST/PUT/PATCH/DELETE), exceto login.

#### Me

- `GET /api/auth/me`

Retorna 200 quando autenticado, ou 401 quando não autenticado.

#### Logout (protegido por CSRF)

- `POST /api/auth/logout`
- Header obrigatório: `X-CSRF-Token: <token retornado no login>`

## Segurança e comportamento

### Timeout de sessão (idle)

Config em:

- [config/session.php](file:///c:/wamp64/www/kanban/config/session.php)

Variável:

- `SESSION_IDLE_TIMEOUT_SECONDS` (default: `1800`)

### Rate limit do login

Config em:

- [config/rate_limit.php](file:///c:/wamp64/www/kanban/config/rate_limit.php)

Variáveis:

- `LOGIN_RATE_LIMIT_MAX_ATTEMPTS` (default: `5`)
- `LOGIN_RATE_LIMIT_WINDOW_SECONDS` (default: `60`)

Se exceder, retorna `429` com header `Retry-After`.

## Testes

Rodar todos os testes:

```bash
vendor\bin\phpunit
```

Observação:

- Alguns testes podem ficar “skipped” se `pdo_sqlite` não estiver habilitado no PHP que executa o PHPUnit.

## Estrutura (alto nível)

- `public/index.php`: front controller
- `bootstrap/app.php`: autoload + registro de rotas
- `routes/web.php` e `routes/api.php`: rotas
- `app/`: código da aplicação (Controllers, Services, Middleware, Helpers, Repositories)
- `database/migrations/*`: migrations por driver
- `tests/`: Unit/Functional/Integration (conforme `phpunit.xml`)
