serviceList=php nginx mysql redis
sshContainer=php
mysqlContainer=mysql

build: ## Builds Docker file
	docker-compose build --no-cache $(sshContainer)

set-app-slug: ## Converts APP_NAME to DOCKER_APP_SLUG
	@APP_NAME=$$(grep '^APP_NAME=' .env | cut -d '=' -f2-); \
	SLUG=$$(echo $$APP_NAME | tr '[:upper:]' '[:lower:]' | sed 's/ /-/g'); \
	sed -i.bak -e "s/^DOCKER_APP_SLUG=.*/DOCKER_APP_SLUG=$$SLUG/" .env && rm -f .env.bak

set-user-group: ## Set user and group IDs in .env
	@sed -i.bak -e "s/^DOCKER_USER_ID=.*/DOCKER_USER_ID=$(shell id -u)/" .env && rm -f .env.bak
	@sed -i.bak -e "s/^DOCKER_GROUP_ID=.*/DOCKER_GROUP_ID=$(shell id -g)/" .env && rm -f .env.bak

generate-db-password: ## Generate and set a random DB_PASSWORD in .env
	@PASS=$$(cat /dev/urandom | tr -dc 'A-Za-z0-9' | head -c 10); \
	sed -i.bak -e "s/^DB_PASSWORD=.*/DB_PASSWORD=$$PASS/" .env && rm -f .env.bak

set-nginx-port: ## Sets the first free port starting from 8080 to .env as DOCKER_NGINX_PORT
	@PLATFORM=$$(uname); \
	PORT=8080; \
	while true; do \
		if [ "$$PLATFORM" = "Darwin" ]; then \
			if ! lsof -iTCP:$$PORT -sTCP:LISTEN >/dev/null 2>&1; then break; fi; \
		else \
			if ! ss -tuln | grep -q ":$$PORT "; then break; fi; \
		fi; \
		PORT=$$((PORT + 1)); \
	done; \
	echo "Free nginx port: $$PORT"; \
	sed -i.bak -e "s/^DOCKER_NGINX_PORT=.*/DOCKER_NGINX_PORT=$$PORT/" .env && rm -f .env.bak; \
	echo "DOCKER_NGINX_PORT set into .env"

set-nginx-port-stress: ## Sets the first free port starting from 8080 to .env as DOCKER_NGINX_PORT
	@PLATFORM=$$(uname); \
	PORT=8081; \
	while true; do \
		if [ "$$PLATFORM" = "Darwin" ]; then \
			if ! lsof -iTCP:$$PORT -sTCP:LISTEN >/dev/null 2>&1; then break; fi; \
		else \
			if ! ss -tuln | grep -q ":$$PORT "; then break; fi; \
		fi; \
		PORT=$$((PORT + 1)); \
	done; \
	echo "Free nginx port: $$PORT"; \
	sed -i.bak -e "s/^DOCKER_NGINX_PORT=.*/DOCKER_NGINX_PORT=$$PORT/" .env && rm -f .env.bak; \
	echo "DOCKER_NGINX_PORT set into .env"


set-nginx-ssl-port: ## Sets the first free port starting from 8443 to .env as DOCKER_NGINX_SSL_PORT
	@PLATFORM=$$(uname); \
	PORT=8443; \
	while true; do \
		if [ "$$PLATFORM" = "Darwin" ]; then \
			if ! lsof -iTCP:$$PORT -sTCP:LISTEN >/dev/null 2>&1; then break; fi; \
		else \
			if ! ss -tuln | grep -q ":$$PORT "; then break; fi; \
		fi; \
		PORT=$$((PORT + 1)); \
	done; \
	echo "Free nginx ssl port: $$PORT"; \
	sed -i.bak -e "s/^DOCKER_NGINX_SSL_PORT=.*/DOCKER_NGINX_SSL_PORT=$$PORT/" .env && rm -f .env.bak; \
	echo "DOCKER_NGINX_SSL_PORT set into .env"

set-mysql-port: ## Sets the first free port starting from 3307 to .env as DOCKER_MYSQL_PORT
	@PLATFORM=$$(uname); \
	PORT=3307; \
	while true; do \
		if [ "$$PLATFORM" = "Darwin" ]; then \
			if ! lsof -iTCP:$$PORT -sTCP:LISTEN >/dev/null 2>&1; then break; fi; \
		else \
			if ! ss -tuln | grep -q ":$$PORT "; then break; fi; \
		fi; \
		PORT=$$((PORT + 1)); \
	done; \
	echo "Free MySql port: $$PORT"; \
	sed -i.bak -e "s/^DOCKER_MYSQL_PORT=.*/DOCKER_MYSQL_PORT=$$PORT/" .env && rm -f .env.bak; \
	echo "DOCKER_MYSQL_PORT set into .env"

set-redis-port: ## Sets the first free port starting from 6380 to .env as DOCKER_REDIS_PORT
	@PLATFORM=$$(uname); \
	PORT=6380; \
	while true; do \
		if [ "$$PLATFORM" = "Darwin" ]; then \
			if ! lsof -iTCP:$$PORT -sTCP:LISTEN >/dev/null 2>&1; then break; fi; \
		else \
			if ! ss -tuln | grep -q ":$$PORT "; then break; fi; \
		fi; \
		PORT=$$((PORT + 1)); \
	done; \
	echo "Free Redis port: $$PORT"; \
	sed -i.bak -e "s/^DOCKER_REDIS_PORT=.*/DOCKER_REDIS_PORT=$$PORT/" .env && rm -f .env.bak; \
	echo "DOCKER_REDIS_PORT set into .env"

set-xdebug-port: ## Sets the first free port starting from 9001 to .env as DOCKER_XDEBUG_PORT
	@PLATFORM=$$(uname); \
	PORT=9001; \
	while true; do \
		if [ "$$PLATFORM" = "Darwin" ]; then \
			if ! lsof -iTCP:$$PORT -sTCP:LISTEN >/dev/null 2>&1; then break; fi; \
		else \
			if ! ss -tuln | grep -q ":$$PORT "; then break; fi; \
		fi; \
		PORT=$$((PORT + 1)); \
	done; \
	echo "Free Xdebug port: $$PORT"; \
	sed -i.bak -e "s/^DOCKER_XDEBUG_PORT=.*/DOCKER_XDEBUG_PORT=$$PORT/" .env && rm -f .env.bak; \
	echo "DOCKER_XDEBUG_PORT set into .env"

set-docker-remote-host: ## Set DOCKER_REMOTE_HOST in .env
	@HOST_IP=""; \
	if command -v ip >/dev/null 2>&1; then \
		HOST_IP=$$(ip route | grep default | awk '{print $$3}'); \
	elif command -v route >/dev/null 2>&1; then \
		HOST_IP=$$(route -n get default | grep 'gateway' | awk '{print $$2}'); \
	else \
		echo "⚠️  Could not auto-detect host IP. Using fallback 172.17.0.1"; \
		HOST_IP="172.17.0.1"; \
	fi; \
	echo "✅ Detected or fallback HOST_IP=$$HOST_IP"; \
	sed -i.bak -e "s/^DOCKER_REMOTE_HOST=.*/DOCKER_REMOTE_HOST=$$HOST_IP/" .env && rm -f .env.bak

set-docker-server-name: ## Set DOCKER_SERVER_NAME in .env
	@APP_NAME=$$(grep '^APP_NAME=' .env | cut -d '=' -f2- | xargs); \
	SLUG=$$(echo "$$APP_NAME" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]/-/g'); \
	FINAL_NAME="\"Docker-$$SLUG\""; \
	if grep -q '^DOCKER_SERVER_NAME=' .env; then \
		sed -i.bak -e "s/^DOCKER_SERVER_NAME=.*/DOCKER_SERVER_NAME=$$FINAL_NAME/" .env; \
	else \
		echo "DOCKER_SERVER_NAME=$$FINAL_NAME" >> .env; \
	fi; \
	rm -f .env.bak; \
	echo "Set DOCKER_SERVER_NAME=$$FINAL_NAME"

generate-env: ## Creates .env from .env.example
	cp .env.example .env
	@make set-app-slug
	@make set-user-group
	@make generate-db-password
	@make set-nginx-port
	@make set-nginx-ssl-port
	@make set-mysql-port
	@make set-redis-port
	@make set-xdebug-port
	@make set-docker-server-name
	@make set-docker-remote-host

install: ## Install for dev work
	@make stop && \
	rm -rf .docker/mysql/volumes/*
	rm -rf .docker/redis/data/*
	@make start && \
	docker-compose exec $(sshContainer) bash -c "\
		composer install --optimize-autoloader && \
		php artisan key:generate && \
		php artisan config:clear && \
		php artisan route:clear && \
		php artisan view:clear && \
		php artisan config:cache && \
		php artisan route:cache && \
		php artisan view:cache && \
		php artisan migrate:fresh --seed && \
		php artisan queue:flush && \
		php artisan app:fetch-currency-rates-since --currency=EUR --base=USD --M=01 --Y=2024 && \
		php artisan app:generate-stress-test-files"

load-test: ## Install for load test
	@make stop
	rm -rf .docker/mysql/volumes/*
	rm -rf .docker/redis/data/*
	docker-compose -f docker-compose.yml -f docker-compose.override.loadtest.yml up -d --build

	docker-compose exec $(sshContainer) bash -c "\
		composer install --no-dev --no-scripts && \
		composer dump-autoload --optimize && \
		php artisan key:generate && \
		php artisan config:cache && \
		php artisan route:cache && \
		php artisan view:cache && \
		php artisan migrate:fresh --seed && \
		php artisan app:fetch-currency-rates-since --currency=EUR --base=USD --M=01 --Y=2024"

kill: ## Stops all docker containers
	docker stop $(shell docker ps -aq)

start-load-test: ## Starts docker-compose for stress testing
	docker-compose -f docker-compose.yml -f docker-compose.override.loadtest.yml up -d $(serviceList)

start: ## Starts docker-compose
	docker-compose up -d $(serviceList)

stop: ## Stops docker-compose
	docker-compose down

restart: ## Stops docker-compose and starts docker-compose
	make stop && make start

ssh: ## SSH to docker-compose
	docker-compose exec $(sshContainer) bash

db-create: ## Create MySQL database
	DB_PASSWORD=$$(grep '^DB_PASSWORD=' .env | cut -d '=' -f2); \
	DB_USERNAME=$$(grep '^DB_USERNAME=' .env | cut -d '=' -f2); \
	DB_DATABASE=$$(grep '^DB_DATABASE=' .env | cut -d '=' -f2); \
	docker-compose exec $(mysqlContainer) bash -c "mysql -h db -u$$DB_USERNAME -p'$$DB_PASSWORD' -e 'CREATE DATABASE IF NOT EXISTS $$DB_DATABASE;'"

prune: ## Clear build cache
	sudo docker system prune -af
