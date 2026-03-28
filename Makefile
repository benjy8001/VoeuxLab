ifndef VERBOSE
.SILENT:
endif

DOCKER_COMPOSE  = docker compose
EXEC_PHP        = $(DOCKER_COMPOSE) exec phpfpm
ARTISAN         = $(EXEC_PHP) php artisan
COMPOSER        = $(EXEC_PHP) composer

.DEFAULT_GOAL := help
help: ## This help
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/  \x1b\[32m##/\x1b\[33m/'
.PHONY: help

## Project
build: .env ## Build docker images
	$(DOCKER_COMPOSE) build

run: build ## Start containers
	$(DOCKER_COMPOSE) up -d

stop: ## Stop containers
	$(DOCKER_COMPOSE) stop

start: .env run vendor assets init-storage key init-database ## First install

.env:
	cp .env.example .env

## Assets
assets: ## Install npm deps and compile assets
	$(EXEC_PHP) npm install
	$(EXEC_PHP) npm run build

assets-watch: ## Compile assets in watch mode
	$(EXEC_PHP) npm run dev

## Composer
vendor: ## Run composer install
	$(COMPOSER) install

## Database
init-database: ## Fresh migration + seed
	$(ARTISAN) migrate:fresh --seed

migrate: ## Run pending migrations
	$(ARTISAN) migrate

seed: ## Run seeders
	$(ARTISAN) db:seed

## Laravel
key: ## Generate APP_KEY
	$(ARTISAN) key:generate

init-storage: ## Create storage symlink
	$(ARTISAN) storage:link

tinker: ## Run Tinker
	$(ARTISAN) tinker

connect: ## Shell in PHP container
	$(EXEC_PHP) bash

## Tests
tests: ## Run Pest tests
	$(ARTISAN) test

.PHONY: build run stop start assets assets-watch vendor init-database migrate seed key init-storage tinker connect tests
