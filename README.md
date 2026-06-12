# Laravue

Template **Laravel + Inertia/Vue 3** com Docker, Fortify, Spatie Permission, Horizon, Redis e auditoria.

## Pré-requisitos

- Docker e Docker Compose
- Node.js **20.19+** ou **22.12+** no host (apenas para build do frontend sem Vite)

## Setup inicial

```bash
cp .env.example .env
docker compose -f compose.dev.yaml up -d --build
docker compose -f compose.dev.yaml exec workspace composer install
docker compose -f compose.dev.yaml exec workspace npm ci
docker compose -f compose.dev.yaml exec workspace php artisan key:generate --force
docker compose -f compose.dev.yaml exec workspace php artisan migrate --force
docker compose -f compose.dev.yaml exec workspace bash
php artisan db:seed
exit
npm run build
```

1. Os comandos `docker compose` — sobem containers, instalam dependências PHP/Node no container, geram `APP_KEY` e rodam migrations.
2. `db:seed` — cria perfis, permissões e usuário admin (rode **dentro** do container).
3. `npm run build` — gera assets em `public/build/` (rode no **host**).

App: [http://localhost:8080](http://localhost:8080)

> **Importante:** com Docker, use `docker compose -f compose.dev.yaml exec workspace bash` para Artisan, Composer e seeds. Comandos PHP no host falham (Redis/Postgres usam hostnames dos containers).

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
npm run build
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
| `docker compose -f compose.dev.yaml up -d horizon` | Sobe o Horizon |
| `docker compose -f compose.dev.yaml logs -f horizon` | Logs do Horizon |
| `docker compose -f compose.dev.yaml logs -f` | Logs de todos os serviços |

| Container | Função | Porta (host) |
|-----------|--------|--------------|
| `nginx` | HTTP | 8080 |
| `php-fpm` | PHP-FPM | — |
| `workspace` | CLI (Artisan, Composer, npm) | 5173 |
| `postgres` | PostgreSQL | 5432 |
| `redis` | Cache/filas | — |
| `horizon` | Laravel Horizon | — |

Código montado em `/var/www` (volume do diretório do projeto).

### Rebuild completo (sem cache)

```bash
docker compose -f compose.dev.yaml down
docker compose -f compose.dev.yaml build --no-cache
docker compose -f compose.dev.yaml up -d
docker compose -f compose.dev.yaml exec workspace bash
composer install
php artisan migrate --force --seed
exit
npm run build
```

### Recriar banco do zero

```bash
docker compose -f compose.dev.yaml down -v
docker compose -f compose.dev.yaml up -d --build
docker compose -f compose.dev.yaml exec workspace composer install
docker compose -f compose.dev.yaml exec workspace npm ci
docker compose -f compose.dev.yaml exec workspace php artisan key:generate --force
docker compose -f compose.dev.yaml exec workspace php artisan migrate --force
docker compose -f compose.dev.yaml exec workspace php artisan db:seed
npm run build
```

### Conflito de nome de container

Se `docker compose up` falhar com *container name already in use*:

```bash
docker rm -f nginx php-fpm workspace postgres redis horizon
docker compose -f compose.dev.yaml up -d --build
```

## Banco de dados

| Variável | Padrão |
|----------|--------|
| `DB_DATABASE` / `POSTGRES_DATABASE` | `laravue` |
| `DB_USERNAME` / `POSTGRES_USERNAME` | `laravel` |
| `DB_PASSWORD` / `POSTGRES_PASSWORD` | `secret` |

Host dentro dos containers: `postgres`. Redis: `redis`.

## Frontend

| Modo | Comando | Hot reload |
|------|---------|------------|
| Desenvolvimento | `docker compose -f compose.dev.yaml exec workspace npm run dev` | Sim |
| Estático | `npm run build` (host) | Não |

Título e logo usam `APP_NAME` do `.env` (`VITE_APP_NAME="${APP_NAME}"`).

## Testes

```bash
docker compose -f compose.dev.yaml exec workspace php artisan test
# ou filtrado:
docker compose -f compose.dev.yaml exec workspace bash
php artisan test --compact --filter=NomeDoTeste
```

## Personalizar

- **Nome da app:** `APP_NAME` no `.env`
- **Porta web:** `NGINX_PORT` (padrão `8080`)
- **Porta Vite:** `VITE_PORT` (padrão `5173`)
- **UID/GID:** `UID` e `GID` no `.env` (permissões dos volumes)
