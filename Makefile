COMPOSE = docker compose -f compose.dev.yaml
SERVICE = workspace

.PHONY: up down build restart logs shell setup migrate test vite build-assets horizon horizon-logs

up:
	$(COMPOSE) up -d --build

down:
	$(COMPOSE) down

build:
	$(COMPOSE) build

restart:
	$(COMPOSE) restart

logs:
	$(COMPOSE) logs -f

shell:
	$(COMPOSE) exec $(SERVICE) bash

setup:
	$(COMPOSE) up -d --build
	$(COMPOSE) exec $(SERVICE) composer install
	$(COMPOSE) exec $(SERVICE) npm ci
	$(COMPOSE) exec $(SERVICE) php artisan key:generate --force
	$(COMPOSE) exec $(SERVICE) php artisan migrate --force

migrate:
	$(COMPOSE) exec $(SERVICE) php artisan migrate

test:
	$(COMPOSE) exec $(SERVICE) php artisan test

vite:
	$(COMPOSE) exec $(SERVICE) npm run dev

build-assets:
	$(COMPOSE) exec $(SERVICE) npm run build

horizon:
	$(COMPOSE) up -d horizon

horizon-logs:
	$(COMPOSE) logs -f horizon
