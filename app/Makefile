build: ## Builds docker-compose
	docker-compose build --no-cache

init: ## Start and init modules
	docker-compose up frontend -d \
	 && docker cp vue_app_container:/usr/local/tmp/node_modules ./ \
     && docker cp vue_app_container:/usr/local/tmp/package-lock.json ./ \
     && docker cp vue_app_container:/usr/local/tmp/package.json ./
#    && docker cp vue_app_container:/usr/local/app/node_modules ./ \
#    && docker cp vue_app_container:/usr/local/app/package-lock.json ./
#    && docker cp vue_app_container:/usr/local/app/package.json ./

start: ## Start container
	docker-compose up frontend -d

stop: ## Stops docker-compose
	docker-compose down --volumes

restart: ## Restarts container
	docker-compose down --volumes && make start

ssh: ## SSH to docker-compose
	docker-compose run --rm frontend sh

prune: ## Clear build cache
	sudo docker system prune -af