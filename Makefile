.PHONY: help build up down restart logs shell migrate fresh seed test

# Default target
help:
	@echo "Available commands:"
	@echo "  build    - Build Docker images"
	@echo "  up       - Start all containers"
	@echo "  down     - Stop all containers"
	@echo "  restart  - Restart all containers"
	@echo "  logs     - Show container logs"
	@echo "  shell    - Open shell in app container"
	@echo "  migrate  - Run database migrations"
	@echo "  fresh    - Fresh database migration and seeding"
	@echo "  seed     - Run database seeders"
	@echo "  test     - Run tests in container"

# Build Docker images
build:
	docker compose build

# Start all containers
up:
	docker compose up -d

# Stop all containers
down:
	docker compose down

# Restart all containers
restart:
	docker compose restart

# Show container logs
logs:
	docker compose logs -f

# Open shell in app container
shell:
	docker compose exec app bash

# Run database migrations
migrate:
	docker compose exec app php artisan migrate

# Fresh database migration and seeding
fresh:
	docker compose exec app php artisan migrate:fresh --seed

# Run database seeders
seed:
	docker compose exec app php artisan db:seed

# Run tests in container
test:
	docker compose exec app php artisan test

# Setup project (first time)
setup:
	@echo "Setting up BookVerse project..."
	cp docker.env .env
	docker compose build
	docker compose up -d
	@echo "Waiting for containers to be ready..."
	sleep 30
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan jwt:secret
	docker compose exec app php artisan migrate --seed
	@echo "Setup complete! Visit http://localhost:8000"

# Clean up everything
clean:
	docker compose down -v
	docker system prune -f 