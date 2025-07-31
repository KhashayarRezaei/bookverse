# Docker Setup for BookVerse

This document provides instructions for running BookVerse using Docker containers.

## Prerequisites

- Docker
- Docker Compose
- Make (optional, for convenience commands)

## Quick Start

### Option 1: Using Make (Recommended)

```bash
# Setup the project for the first time
make setup

# Or run commands individually
make build
make up
```

### Option 2: Manual Setup

```bash
# 1. Copy Docker environment file
cp docker.env .env

# 2. Build and start containers
docker-compose build
docker-compose up -d

# 3. Wait for containers to be ready (30 seconds)
sleep 30

# 4. Generate application keys
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret

# 5. Run migrations and seeders
docker-compose exec app php artisan migrate --seed
```

## Available Commands

### Using Make
```bash
make help      # Show all available commands
make build     # Build Docker images
make up        # Start all containers
make down      # Stop all containers
make restart   # Restart all containers
make logs      # Show container logs
make shell     # Open shell in app container
make migrate   # Run database migrations
make fresh     # Fresh database migration and seeding
make seed      # Run database seeders
make test      # Run tests in container
make clean     # Clean up everything
```

### Using Docker Compose Directly
```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Execute commands in app container
docker-compose exec app php artisan migrate
docker-compose exec app php artisan test
docker-compose exec app bash
```

## Services

The Docker setup includes the following services:

### 1. App Container (`bookverse_app`)
- **Image**: Custom PHP 8.2 with FPM
- **Port**: 9000 (internal)
- **Features**:
  - PHP 8.2 with FPM
  - Composer for dependency management
  - Redis extension
  - MySQL client
  - Automatic startup script with migrations
  - Health checks

### 2. Nginx Container (`bookverse_nginx`)
- **Image**: nginx:alpine
- **Port**: 8000 (external)
- **Features**:
  - Reverse proxy for PHP-FPM
  - Static file serving
  - Gzip compression

### 3. MySQL Container (`bookverse_db`)
- **Image**: mysql:8.0
- **Port**: 3306 (external)
- **Features**:
  - MySQL 8.0
  - Persistent data storage
  - Health checks
  - Environment-based configuration

### 4. Redis Container (`bookverse_redis`)
- **Image**: redis:alpine
- **Port**: 6379 (external)
- **Features**:
  - Redis for caching and queues
  - Persistent data storage
  - Health checks

### 5. Queue Worker Container (`bookverse_queue`)
- **Image**: Same as app container
- **Features**:
  - Laravel queue worker
  - Redis queue processing
  - Automatic restarts

## Environment Configuration

### Docker Environment File (`docker.env`)
The `docker.env` file contains Docker-specific environment variables:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=bookverse
DB_USERNAME=bookverse_user
DB_PASSWORD=password

# Redis Configuration
REDIS_HOST=redis
REDIS_PORT=6379
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# Application Configuration
APP_URL=http://localhost:8000
APP_ENV=local
APP_DEBUG=true
```

### Override Configuration (`docker-compose.override.yml`)
This file allows you to override specific settings for local development.

## Features

### Automatic Startup
The app container includes a startup script that:
1. Waits for the database to be ready
2. Installs Composer dependencies if needed
3. Generates application key if not set
4. Runs database migrations
5. Creates storage link
6. Caches configuration
7. Starts PHP-FPM

### Health Checks
All services include health checks to ensure proper startup order:
- **MySQL**: Checks if database is accepting connections
- **Redis**: Checks if Redis is responding to ping
- **App**: Checks if PHP-FPM configuration is valid

### Volume Mapping
- Application code is mounted for live development
- Database and Redis data are persisted in named volumes
- Nginx configuration is mounted for easy customization

### Network Configuration
All services are connected via a custom bridge network (`bookverse_network`) for secure inter-service communication.

## Development Workflow

### 1. Starting Development
```bash
make up
```

### 2. Making Changes
- Code changes are immediately reflected due to volume mounting
- No need to rebuild containers for code changes

### 3. Database Changes
```bash
# Create new migration
docker-compose exec app php artisan make:migration create_new_table

# Run migrations
make migrate

# Fresh database with seeders
make fresh
```

### 4. Running Tests
```bash
make test
```

### 5. Viewing Logs
```bash
make logs
```

### 6. Accessing Container Shell
```bash
make shell
```

## Troubleshooting

### Common Issues

#### 1. Port Already in Use
```bash
# Check what's using port 8000
lsof -i :8000

# Kill the process or change port in docker-compose.yml
```

#### 2. Database Connection Issues
```bash
# Check if MySQL is running
docker-compose ps

# Check MySQL logs
docker-compose logs db

# Wait for MySQL to be ready
docker-compose exec app mysqladmin ping -h db -u bookverse_user -ppassword
```

#### 3. Permission Issues
```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

#### 4. Queue Not Processing
```bash
# Check queue worker logs
docker-compose logs queue

# Restart queue worker
docker-compose restart queue
```

### Useful Commands

```bash
# View all container status
docker-compose ps

# View specific service logs
docker-compose logs app
docker-compose logs db
docker-compose logs redis

# Execute commands in specific containers
docker-compose exec db mysql -u root -p
docker-compose exec redis redis-cli

# Clean up everything
make clean
```

## Production Considerations

For production deployment:

1. **Environment Variables**: Use proper production environment variables
2. **Secrets Management**: Use Docker secrets or external secret management
3. **SSL/TLS**: Configure SSL termination in Nginx
4. **Monitoring**: Add monitoring and logging solutions
5. **Backup**: Implement database backup strategies
6. **Scaling**: Consider using Docker Swarm or Kubernetes for scaling

## API Testing in Docker

Once the containers are running, you can test the API:

```bash
# Test the API
curl -X GET http://localhost:8000/api/books

# Test with authentication
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "admin@bookverse.com", "password": "password"}'
```

## Support

For issues related to Docker setup:
1. Check the troubleshooting section above
2. Review container logs: `make logs`
3. Ensure all prerequisites are installed
4. Verify Docker and Docker Compose versions are compatible 