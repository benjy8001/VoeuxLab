ifndef VERBOSE
.SILENT:
endif

DOCKER_COMPOSE  = docker compose
EXEC_PHP        = $(DOCKER_COMPOSE) exec phpfpm
ARTISAN         = $(EXEC_PHP) php artisan
COMPOSER        = $(EXEC_PHP) composer

# Déploiement — copier .env.make.example en .env.make et remplir les valeurs
-include .env.make
DEPLOY_SSH = ssh -p $(DEPLOY_PORT) $(DEPLOY_USER)@$(DEPLOY_HOST)

.DEFAULT_GOAL := help
help: ## This help
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/  \x1b\[32m##/\x1b\[33m/'
.PHONY: help

##
## Projet
## ------
build: .env ## Build des images Docker
	$(DOCKER_COMPOSE) build

run: build ## Démarrer les conteneurs
	$(DOCKER_COMPOSE) up -d

stop: ## Arrêter les conteneurs
	$(DOCKER_COMPOSE) stop

down: ## Arrêter et supprimer les conteneurs + volumes
	$(DOCKER_COMPOSE) down --volumes

clean: ## Arrêter et supprimer les fichiers générés (demande confirmation)
	@echo -n "Are you sure? [y/N] " && read ans && [ $${ans:-N} = y ]
	$(DOCKER_COMPOSE) down -v
	rm -rf vendor public/build storage/logs/laravel.log

start: .env run vendor assets init-storage key init-database ## Première installation complète

.env:
	cp .env.example .env

##
## Assets
## ------
assets: ## Installer npm et compiler les assets
	$(EXEC_PHP) npm install --legacy-peer-deps
	$(EXEC_PHP) npm run build

assets-watch: ## Compiler les assets en mode watch
	$(EXEC_PHP) npm run dev

##
## Composer
## --------
vendor: ## Installer les dépendances Composer
	$(COMPOSER) install

composer-update: ## Mettre à jour les dépendances Composer
	$(COMPOSER) update

##
## Base de données
## ---------------
init-database: ## Migration fraîche + seed
	$(ARTISAN) migrate:fresh --seed

migrate: ## Lancer les migrations en attente
	$(ARTISAN) migrate

seed: ## Lancer les seeders
	$(ARTISAN) db:seed

##
## Laravel
## -------
key: ## Générer APP_KEY dans .env
	$(ARTISAN) key:generate

init-storage: ## Créer le lien symbolique public/storage
	$(DOCKER_COMPOSE) exec -u root phpfpm php artisan storage:link

optimize: ## Mettre en cache config, routes et vues
	$(ARTISAN) optimize

optimize-clear: ## Vider tous les caches
	$(ARTISAN) optimize:clear

tinker: ## Lancer Laravel Tinker
	$(ARTISAN) tinker

connect: ## Ouvrir un shell dans le conteneur PHP
	$(EXEC_PHP) bash

##
## Tests
## -----
tests: ## Lancer les tests Pest
	$(ARTISAN) test

##
## Déploiement (production)
## ------------------------
deploy: deploy-assets deploy-sync deploy-env deploy-composer deploy-artisan ## Déploiement complet en production

deploy-assets: ## Compiler les assets pour la production
	printf "\033[32m Compilation des assets... \033[0m\n"
	$(EXEC_PHP) npm install --legacy-peer-deps
	$(EXEC_PHP) npm run build

deploy-sync: ## Synchroniser les fichiers vers le serveur via rsync
	printf "\033[32m Synchronisation vers $(DEPLOY_USER)@$(DEPLOY_HOST):$(DEPLOY_PATH) ... \033[0m\n"
	rsync -az --delete -p \
		--exclude='.git' \
		--exclude='.env' \
		--exclude='.env.make' \
		--exclude='.claude' \
		--exclude='node_modules' \
		--exclude='vendor' \
		--exclude='storage/logs' \
		--exclude='storage/app/public' \
		--exclude='public/hot' \
		--exclude='docker' \
		--exclude='docker-compose.yml' \
		--exclude='Makefile' \
		--exclude='CLAUDE.md' \
		-e "ssh -p $(DEPLOY_PORT)" \
		./ \
		$(DEPLOY_USER)@$(DEPLOY_HOST):$(DEPLOY_PATH)/

deploy-env: ## Copier .env.production en .env sur le serveur
	printf "\033[32m Copie du .env.production... \033[0m\n"
	$(DEPLOY_SSH) "cp $(DEPLOY_PATH)/.env.production $(DEPLOY_PATH)/.env"

deploy-composer: ## Installer les dépendances sans --dev sur le serveur
	printf "\033[32m Installation des dépendances... \033[0m\n"
	$(DEPLOY_SSH) "cd $(DEPLOY_PATH) && composer install --no-dev --optimize-autoloader --no-interaction"

deploy-artisan: ## Lancer les migrations et caches sur le serveur
	printf "\033[32m Commandes artisan... \033[0m\n"
	$(DEPLOY_SSH) "cd $(DEPLOY_PATH) && php artisan migrate --force && php artisan storage:link --force && php artisan optimize"

deploy-ssh: ## Ouvrir une session SSH sur le serveur
	$(DEPLOY_SSH)

.PHONY: build run stop down clean start
.PHONY: assets assets-watch
.PHONY: vendor composer-update
.PHONY: init-database migrate seed
.PHONY: key init-storage optimize optimize-clear tinker connect
.PHONY: tests
.PHONY: deploy deploy-assets deploy-sync deploy-env deploy-composer deploy-artisan deploy-ssh
