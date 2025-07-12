PHONY: up down build logs shell composer npm

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