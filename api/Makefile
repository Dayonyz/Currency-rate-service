projectPath=$(shell pwd)
serviceList=php nginx mysql redis
laradockFolder=.docker
sshContainer=php
mysqlContainer=mysql
user=501
group=20
dbName=currency

.DEFAULT_GOAL:=help

help: ## Displayes help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Docker

build: ## Builds docker-compose
	cd $(laradockFolder) && cp .env.example .env && docker-compose build --no-cache $(serviceList)

kill: ## Stops all docker containers
	docker stop $(shell docker ps -aq)

start: ## Starts docker-compose
	cd $(laradockFolder) && docker-compose up -d $(serviceList)

stop: ## Stops docker-compose
	cd $(laradockFolder) && docker-compose down --volumes

restart: ## Stops docker-compose and starts docker-compose
	make stop && make start

ssh: ## SSH to docker-compose
	cd $(laradockFolder) cd && docker-compose exec $(sshContainer) bash

mysql-create: ## Connection to mysql
	cd $(laradockFolder) cd && docker-compose exec $(mysqlContainer) bash -c "mysql -uroot -p12345 -e 'create database $(dbName)'"

##@ Laravel

ihm: ## Creates models hints
	make laradock-exec CMD="php artisan ide-helper:models $(MODEL) --nowrite"

mfs: ## Migrate fresh + seed
	make laradock-exec CMD="php artisan migrate:fresh --seed"

cache: ## Caches configs and routes
	make laradock-exec CMD="php artisan config:cache && php artisan route:cache"

cacheClear: ## Clears cache of configs and routes
	make laradock-exec CMD="php artisan config:clear && php artisan route:clear"

phpstan-server: ## Static code analyzer for remote
	make laradock-exec CMD="composer phpstan-server"

phpstan: ## Static code analyzer for local
	make laradock-exec CMD="composer phpstan"

cs-server: ## PHPcs server run
	make laradock-exec CMD="composer phpcs-server"

cs: ## PHPcs
	make laradock-exec CMD="composer phpcs"

cs-fixer: ## PHPcs fixer
	make laradock-exec CMD="./vendor/bin/phpcbf --standard=phpcs.xml"

##@ System

chmod: ## Make required chmoding for Linux after creating resources from-inside the container
	chmod 777 -R src/app/* src/database/* src/tests/* src/storage/* src/config/* src/vendor/* src/.env src/bootstrap/* src/resources/*

laradock-exec:
	cd $(laradockFolder) && docker-compose exec $(sshContainer) bash -c "$(CMD)"

conf-setup: ## Conf.d setup
	cd $(dockerFolder) && \
	cp cron/local/* cron/ && \
	cp nginx/local/* nginx && \
	cp nginx/local/.htpasswd nginx && \
	cp -r php/local/* php

env: ##Copy .env
	cp src/.env.example src/.env