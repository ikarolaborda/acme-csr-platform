.PHONY: help setup up down restart shell composer artisan npm test migrate fresh install clean build build-prod dev create-admin

help:
	@echo "Available commands:"
	@echo "  make setup      - Initial project setup (one-time)"
	@echo "  make up         - Start all containers in development mode"
	@echo "  make up-prod    - Start all containers in production mode"
	@echo "  make down       - Stop all containers"
	@echo "  make restart    - Restart all containers"
	@echo "  make shell      - Access app container shell"
	@echo "  make composer   - Run composer commands"
	@echo "  make artisan    - Run artisan commands"
	@echo "  make npm        - Run npm commands"
	@echo "  make test       - Run all tests"
	@echo "  make migrate    - Run database migrations"
	@echo "  make fresh      - Fresh migrate with seeders"
	@echo "  make clean      - Clean cache and logs"
	@echo "  make build      - Build assets for development"
	@echo "  make build-prod - Build assets for production"
	@echo "  make dev        - Start development mode with hot reload"
	@echo "  make create-admin - Create an admin user interactively"

setup:
	@echo "Setting up ACME CSR Platform..."
	@cp .env.example .env
	@docker compose build --no-cache
	@docker compose up -d
	@sleep 5
	@docker compose exec app composer install
	@docker compose exec app php artisan key:generate
	@docker compose exec app php artisan jwt:secret
	@docker compose exec app php artisan migrate --seed
	@docker compose exec app php artisan storage:link
	@docker compose exec node npm install
	@docker compose exec node npm run build
	@echo "Setup complete! Application is running at http://localhost:8080"

up:
	@echo "Starting containers in development mode..."
	@docker compose up -d

up-prod:
	@echo "Starting containers in production mode..."
	@docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
	@echo "Waiting for assets to build..."
	@sleep 10
	@echo "Production setup complete! Application running at http://localhost:8080"

down:
	@docker compose down

restart:
	@docker compose restart

shell:
	@docker compose exec app bash

composer:
	@docker compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

artisan:
	@docker compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

npm:
	@docker compose exec node npm $(filter-out $@,$(MAKECMDGOALS))

test:
	@docker compose exec app php artisan test

migrate:
	@docker compose exec app php artisan migrate

fresh:
	@docker compose exec app php artisan migrate:fresh --seed

install:
	@docker compose exec app composer install
	@docker compose exec node npm install

clean:
	@docker compose exec app php artisan cache:clear
	@docker compose exec app php artisan config:clear
	@docker compose exec app php artisan route:clear
	@docker compose exec app php artisan view:clear

build:
	@echo "Building assets for development..."
	@docker compose exec node npm run build

build-prod:
	@echo "Building assets for production..."
	@docker compose exec node npm run build

dev:
	@echo "Starting development mode with hot reload..."
	@docker compose up

create-admin:
	@echo "Creating admin user..."
	@docker compose exec app php artisan user:create-admin

%:
	@: 