build: ## Builds docker-compose
	docker-compose build --no-cache

start: ## Starts docker-compose
	docker-compose up app -d

stop: ## Stops docker-compose
	docker-compose down --volumes

ssh: ## SSH to docker-compose
	docker-compose run --rm app sh
