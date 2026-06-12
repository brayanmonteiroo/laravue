# Laravue

Template **Laravel + Inertia/Vue 3** com Docker, Fortify, Spatie Permission, Horizon, Redis e auditoria.

## Pré-requisitos

- Docker e Docker Compose
- Make (opcional, recomendado)
- Node.js **20.19+** ou **22.12+** no host (apenas para build do frontend sem Vite)

## Setup inicial

```bash
cp .env.example .env
make setup
make shell
php artisan db:seed
exit
npm run build
```

1. `make setup` — sobe containers, instala dependências PHP/Node no container, gera `APP_KEY` e roda migrations.
2. `db:seed` — cria perfis, permissões e usuário admin (rode **dentro** do container).
3. `npm run build` — gera assets em `public/build/` (rode no **host**).

App: [http://localhost:8080](http://localhost:8080)

> **Importante:** com Docker, use `make shell` para Artisan, Composer e seeds. Comandos PHP no host falham (Redis/Postgres usam hostnames dos containers).

## Uso diário

**Terminal 1 — stack**

```bash
make up
```

**Terminal 2 — hot reload (opcional, para editar Vue/CSS)**

```bash
make vite
```

Sem `make vite`, após mudanças no frontend:

```bash
npm run build
```

## Comandos Make

| Comando | Descrição |
|---------|-----------|
| `make up` | Sobe/rebuilda containers em background |
| `make down` | Para containers |
| `make build` | Build das imagens Docker |
| `make shell` | Bash no container `workspace` |
| `make setup` | Setup completo (primeira vez) |
| `make migrate` | Migrations |
| `make test` | Testes Pest |
| `make vite` | Vite dev server (porta 5173) |
| `make build-assets` | Build via container *(pode falhar — prefira `npm run build` no host)* |
| `make horizon-logs` | Logs do Horizon |
| `make logs` | Logs de todos os serviços |

## Docker

Arquivo: `compose.dev.yaml` · Projeto Compose: `laravue`

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
make down
docker compose -f compose.dev.yaml build --no-cache
docker compose -f compose.dev.yaml up -d
make shell
composer install
php artisan migrate --force --seed
exit
npm run build
```

### Recriar banco do zero

```bash
make down -v
make setup
make shell && php artisan db:seed && exit
npm run build
```

### Conflito de nome de container

Se `docker compose up` falhar com *container name already in use*:

```bash
docker rm -f nginx php-fpm workspace postgres redis horizon
make up
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
| Desenvolvimento | `make vite` | Sim |
| Estático | `npm run build` (host) | Não |

Título e logo usam `APP_NAME` do `.env` (`VITE_APP_NAME="${APP_NAME}"`).

## Testes

```bash
make test
# ou filtrado:
make shell
php artisan test --compact --filter=NomeDoTeste
```

## Personalizar

- **Nome da app:** `APP_NAME` no `.env`
- **Porta web:** `NGINX_PORT` (padrão `8080`)
- **Porta Vite:** `VITE_PORT` (padrão `5173`)
- **UID/GID:** `UID` e `GID` no `.env` (permissões dos volumes)
