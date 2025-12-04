.PHONY: help build up down restart logs shell composer artisan test migrate seed fresh install

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build Docker containers
	docker compose build

up: ## Start all containers
	docker compose up -d

up-dev: ## Start all containers with dev profile
	docker compose --profile dev up -d

down: ## Stop all containers
	docker compose down

restart: ## Restart all containers
	docker compose restart

logs: ## Show logs from all containers
	docker compose logs -f

shell: ## Access app container shell
	docker compose exec app sh

composer: ## Run composer install
	docker compose exec app composer install

artisan: ## Run artisan command (use: make artisan cmd="migrate")
	docker compose exec app php artisan $(cmd)

test: ## Run tests
	docker compose exec app php artisan test

migrate: ## Run database migrations
	docker compose exec app php artisan migrate

seed: ## Seed database
	docker compose exec app php artisan db:seed

fresh: ## Fresh database with seed data
	docker compose exec app php artisan migrate:fresh --seed

install: ## Initial installation
	@echo "🚀 Installing OpenBiz Suite..."
	@if [ ! -f .env ]; then cp .env.example .env; fi
	@echo "✅ Environment file created"
	docker compose build
	@echo "✅ Containers built"
	docker compose up -d
	@echo "⏳ Waiting for services to be ready..."
	sleep 10
	docker compose exec app composer install
	@echo "✅ Dependencies installed"
	docker compose exec app php artisan key:generate
	@echo "✅ Application key generated"
	docker compose exec app php artisan migrate --seed
	@echo "✅ Database migrated and seeded"
	@echo ""
	@echo "🎉 Installation complete!"
	@echo ""
	@echo "Access the application at: http://localhost"
	@echo "phpMyAdmin: http://localhost:8081"
	@echo "Traefik Dashboard: http://localhost:8080"
