# Laravue

Template **Laravel + Inertia/Vue 3** com Docker, Fortify, Spatie Permission, Horizon, Redis e auditoria.

## Pré-requisitos

- Docker e Docker Compose

## Setup inicial

```bash
cp .env.example .env
cp .env.testing.example .env.testing
docker compose -f compose.dev.yaml up -d --build
docker compose -f compose.dev.yaml exec workspace composer install
docker compose -f compose.dev.yaml exec workspace npm ci
docker compose -f compose.dev.yaml exec workspace php artisan key:generate --force
docker compose -f compose.dev.yaml exec workspace bash -lc 'KEY=$(grep -E "^APP_KEY=" .env | cut -d= -f2-) && sed -i "s|^APP_KEY=.*|APP_KEY=${KEY}|" .env.testing'
docker compose -f compose.dev.yaml exec workspace php artisan migrate --force
docker compose -f compose.dev.yaml exec workspace php artisan db:seed
docker compose -f compose.dev.yaml exec workspace npm run build
```

1. Copia `.env` (app) e `.env.testing` (Pest) a partir dos examples.
2. Sobe os containers; o Postgres cria os bancos `laravue` e `laravue_testing` no volume novo.
3. Instala dependências, gera `APP_KEY` e replica no `.env.testing`.
4. Migrations + seed no banco da aplicação; build dos assets.

App: [http://localhost:8080](http://localhost:8080)

> **Importante:** com Docker, use `docker compose -f compose.dev.yaml exec workspace …` (ou `exec workspace bash`) para Artisan, Composer, npm e seeds. Comandos PHP no host falham (Redis/Postgres usam hostnames dos containers).

## Uso diário

**Terminal 1 — stack**

```bash
docker compose -f compose.dev.yaml up -d --build
```

**Terminal 2 — hot reload (opcional, para editar Vue/CSS)**

```bash
docker compose -f compose.dev.yaml exec workspace npm run dev
```

Sem Vite em dev, após mudanças no frontend:

```bash
docker compose -f compose.dev.yaml exec workspace npm run build
```

## Comandos Docker

Arquivo: `compose.dev.yaml` · Projeto Compose: `laravue`

| Comando | Descrição |
|---------|-----------|
| `docker compose -f compose.dev.yaml up -d --build` | Sobe/rebuilda containers em background |
| `docker compose -f compose.dev.yaml down` | Para containers |
| `docker compose -f compose.dev.yaml build` | Build das imagens Docker |
| `docker compose -f compose.dev.yaml exec workspace bash` | Bash no container `workspace` |
| `docker compose -f compose.dev.yaml exec workspace php artisan migrate` | Migrations |
| `docker compose -f compose.dev.yaml exec workspace php artisan test` | Testes Pest |
| `docker compose -f compose.dev.yaml exec workspace npm run dev` | Vite dev server (porta 5173) |
| `docker compose -f compose.dev.yaml exec workspace npm run build` | Build dos assets em `public/build/` |
| `docker compose -f compose.dev.yaml up -d horizon` | Sobe o Horizon |
| `docker compose -f compose.dev.yaml logs -f horizon` | Logs do Horizon |
| `docker compose -f compose.dev.yaml up -d scheduler` | Sobe o Scheduler |
| `docker compose -f compose.dev.yaml logs -f scheduler` | Logs do Scheduler |
| `docker compose -f compose.dev.yaml logs -f` | Logs de todos os serviços |

| Container | Função | Porta (host) |
|-----------|--------|--------------|
| `laravue-nginx` | HTTP | 8080 |
| `laravue-php-fpm` | PHP-FPM | — |
| `laravue-workspace` | CLI (Artisan, Composer, npm) | 5173 |
| `laravue-postgres` | PostgreSQL | 5432 |
| `laravue-redis` | Cache/filas | — |
| `laravue-horizon` | Laravel Horizon | — |
| `laravue-scheduler` | Laravel Scheduler (`schedule:work`) | — |

Código montado em `/var/www` (volume do diretório do projeto).

O container `scheduler` executa tarefas agendadas em [`routes/console.php`](routes/console.php), incluindo `horizon:snapshot` a cada 5 minutos. Sem ele, as métricas do dashboard `/horizon` permanecem vazias.

### Rebuild completo (sem cache)

```bash
docker compose -f compose.dev.yaml down
docker compose -f compose.dev.yaml build --no-cache
docker compose -f compose.dev.yaml up -d
docker compose -f compose.dev.yaml exec workspace bash
composer install
php artisan migrate --force --seed
exit
docker compose -f compose.dev.yaml exec workspace npm run build
```

### Recriar banco do zero

Apaga o volume do Postgres (bancos `laravue` e `laravue_testing` nascem de novo no init):

```bash
docker compose -f compose.dev.yaml down -v
docker compose -f compose.dev.yaml up -d --build
docker compose -f compose.dev.yaml exec workspace composer install
docker compose -f compose.dev.yaml exec workspace npm ci
docker compose -f compose.dev.yaml exec workspace php artisan key:generate --force
docker compose -f compose.dev.yaml exec workspace bash -lc 'KEY=$(grep -E "^APP_KEY=" .env | cut -d= -f2-) && sed -i "s|^APP_KEY=.*|APP_KEY=${KEY}|" .env.testing'
docker compose -f compose.dev.yaml exec workspace php artisan migrate --force
docker compose -f compose.dev.yaml exec workspace php artisan db:seed
docker compose -f compose.dev.yaml exec workspace npm run build
```

### Conflito de nome de container

Se `docker compose up` falhar com *container name already in use*:

```bash
docker rm -f laravue-nginx laravue-php-fpm laravue-workspace laravue-postgres laravue-redis laravue-horizon laravue-scheduler
docker compose -f compose.dev.yaml up -d --build
```

## Banco de dados

Mesmo servidor Postgres, **dois bancos**:

| Uso | Arquivo | Banco |
|-----|---------|-------|
| Aplicação | `.env` | `laravue` |
| Testes (Pest) | `.env.testing` | `laravue_testing` |

| Variável | Padrão |
|----------|--------|
| `DB_USERNAME` / `POSTGRES_USERNAME` | `laravel` |
| `DB_PASSWORD` / `POSTGRES_PASSWORD` | `secret` |

Host dentro dos containers: `postgres`. Redis: `redis`.

Na **primeira** inicialização do volume, o script [`docker/development/postgres/init-testing-database.sql`](docker/development/postgres/init-testing-database.sql) cria `laravue_testing` automaticamente (o `POSTGRES_DB` já cria `laravue`).

Se o volume já existia **antes** desse init, recrie com `down -v` (seção acima) ou, só como recovery:

```bash
docker compose -f compose.dev.yaml exec postgres \
  psql -U laravel -d postgres -c "CREATE DATABASE laravue_testing OWNER laravel;"
```

## Frontend

| Modo | Comando | Hot reload |
|------|---------|------------|
| Desenvolvimento | `docker compose -f compose.dev.yaml exec workspace npm run dev` | Sim |
| Estático | `docker compose -f compose.dev.yaml exec workspace npm run build` | Não |

Título e logo usam `APP_NAME` do `.env` (`VITE_APP_NAME="${APP_NAME}"`).

## Testes

Requisitos: `.env.testing` presente (veja setup) e banco `laravue_testing` no Postgres.

```bash
docker compose -f compose.dev.yaml exec workspace php artisan test --compact
# ou filtrado:
docker compose -f compose.dev.yaml exec workspace php artisan test --compact --filter=NomeDoTeste
```

O bootstrap em `tests/bootstrap.php` carrega `.env.testing` e sobrescreve variáveis injetadas pelo Compose a partir do `.env` da app (cache Redis, session database, etc.).

### Verificação fresh (do zero)

```bash
docker compose -f compose.dev.yaml down -v
# seguir o “Setup inicial” completo (incluindo .env.testing)
docker compose -f compose.dev.yaml exec postgres \
  psql -U laravel -d postgres -c "\l"   # deve listar laravue e laravue_testing
docker compose -f compose.dev.yaml exec workspace php artisan test --compact
```

## Personalizar

- **Nome da app:** `APP_NAME` no `.env`
- **Porta web:** `NGINX_PORT` (padrão `8080`)
- **Porta Vite:** `VITE_PORT` (padrão `5173`)
- **UID/GID:** `UID` e `GID` no `.env` (permissões dos volumes)
