# Default target
.PHONY: help

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-20s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Start all services
up:
	docker-compose up -d

# Stop all services  
down:
	docker-compose down

# Build and start services
build:
	docker-compose up -d --build

# View logs
logs:
	docker-compose logs -f

# Access PHP container shell
shell:
	docker exec -it dev_php bash

# Run Composer commands
composer:
	docker-compose run --rm composer $(filter-out $@,$(MAKECMDGOALS))

# Run NPM commands  
npm:
	docker-compose run --rm node npm $(filter-out $@,$(MAKECMDGOALS))

# Fresh Laravel installation
fresh:
	docker-compose run --rm composer create-project laravel/laravel .
	cp .env.example .env
	docker-compose run --rm php php artisan key:generate

# run artisan commands
artisan:
	docker-compose run --rm php php artisan $(filter-out $@,$(MAKECMDGOALS))

# Run tests
test:
	docker exec -it dev_php php artisan test

# Clear all caches
clear:
	docker exec -it dev_php php artisan cache:clear
	docker exec -it dev_php php artisan config:clear
	docker exec -it dev_php php artisan route:clear
	docker exec -it dev_php php artisan view:clear

%:
	@: