build:
	export $$(grep -v '^#' .env | xargs) && docker-compose build --no-cache

init: ## Start and init modules
	export $$(grep -v '^#' .env | xargs) && \
	docker-compose up frontend -d && \
	docker cp vue_app_container:/usr/local/tmp/node_modules ./ && \
	docker cp vue_app_container:/usr/local/tmp/package-lock.json ./ && \
	docker cp vue_app_container:/usr/local/tmp/package.json ./

	@make restart

set-backend-port: ## Sets BACKEND_PORT in app/.env starting from DOCKER_NGINX_PORT from api/.env
	@PORT=$$(grep '^DOCKER_NGINX_PORT=' ../api/.env | cut -d '=' -f2); \
	if [ -z "$$PORT" ]; then \
		echo "Not found DOCKER_NGINX_PORT ../api/.env"; \
		exit 1; \
	fi; \
	echo "BACKEND_PORT is: $$PORT"; \
	if [ ! -f .env ]; then cp .env.example .env; fi; \
	sed -i.bak -e "s/^VUE_APP_BACKEND_PORT=.*/VUE_APP_BACKEND_PORT=$$PORT/" .env || echo "VUE_APP_BACKEND_PORT=$$PORT" >> .env; \
	rm -f .env.bak; \
	echo "VUE_APP_BACKEND_PORT=$$PORT installed into app/.env"

set-backend-host: ## Sets BACKEND_HOST in app/.env starting from BACKEND_HOST from api/.env
	@HOST=$$(grep '^APP_HOST=' ../api/.env | cut -d '=' -f2); \
	if [ -z "$$HOST" ]; then \
		echo "Not found BACKEND_HOST ../api/.env"; \
		exit 1; \
	fi; \
	echo "BACKEND_HOST is: $$HOST"; \
	if [ ! -f .env ]; then cp .env.example .env; fi; \
	sed -i.bak -e "s|^VUE_APP_BACKEND_HOST=.*|VUE_APP_BACKEND_HOST=$$HOST|" .env || echo "VUE_APP_BACKEND_HOST=$$HOST" >> .env; \
	rm -f .env.bak; \
	echo "VUE_APP_BACKEND_HOST=$$HOST installed into app/.env"

set-frontend-port: ## Sets the first free port starting from 8081 to .env as VUE_APP_FRONTEND_PORT
	@PLATFORM=$$(uname); \
	START_PORT=$$(grep -E '^VUE_APP_BACKEND_PORT=' .env | cut -d '=' -f2 | tr -d '\r'); \
    	if [ -z "$$START_PORT" ]; then \
    		echo "Not found VUE_APP_BACKEND_PORT in .env"; \
    		exit 1; \
    	fi; \
	PORT=$$START_PORT; \
	while true; do \
		if [ "$$PLATFORM" = "Darwin" ]; then \
			if ! lsof -iTCP:$$PORT -sTCP:LISTEN >/dev/null 2>&1; then break; fi; \
		else \
			if ! ss -tuln | grep -q ":$$PORT "; then break; fi; \
		fi; \
		PORT=$$((PORT + 1)); \
	done; \
	echo "Free port is: $$PORT"; \
	sed -i.bak -e "s/^VUE_APP_FRONTEND_PORT=.*/VUE_APP_FRONTEND_PORT=$$PORT/" .env && rm -f .env.bak; \
	echo "VUE_APP_FRONTEND_PORT=$$PORT installed into app/.env"

set-api-root: ## Set VUE_APP_API_ROOT_URL in .env
	@HOST=$$(grep '^VUE_APP_BACKEND_HOST=' .env | cut -d '=' -f2); \
	PORT=$$(grep '^VUE_APP_BACKEND_PORT=' .env | cut -d '=' -f2); \
	if [ -z "$$HOST" ] || [ -z "$$PORT" ]; then \
		echo "VUE_APP_BACKEND_HOST or VUE_APP_BACKEND_PORT not found in .env"; \
		exit 1; \
	fi; \
	API_URL="$$HOST:$$PORT/api"; \
	echo "Setting VUE_APP_API_ROOT_URL=$$API_URL"; \
	if grep -q '^VUE_APP_API_ROOT_URL=' .env; then \
		sed -i.bak "s|^VUE_APP_API_ROOT_URL=.*|VUE_APP_API_ROOT_URL=$$API_URL|" .env; \
	else \
		echo "VUE_APP_API_ROOT_URL=$$API_URL" >> .env; \
	fi; \
	rm -f .env.bak

generate-env:
	cp .env.example .env
	@make set-backend-host
	@make set-backend-port
	@make set-frontend-port
	@make set-api-root

start: ## Start container
	@PORT=$$(grep -E '^VUE_APP_FRONTEND_PORT=' .env | cut -d '=' -f2 | tr -d '\r'); \
	export $$(grep -v '^#' .env | xargs) && docker-compose up frontend -d; \
	echo "Application run: http://127.0.0.1:$$PORT/"

stop: ## Stops docker-compose
	export $$(grep -v '^#' .env | xargs) && docker-compose down --remove-orphans

restart: ## Restarts container
	make stop && make start

ssh: ## SSH to docker-compose
	export $$(grep -v '^#' .env | xargs) && docker-compose run --rm frontend sh

prune: ## Clear build cache
	sudo docker system prune -af