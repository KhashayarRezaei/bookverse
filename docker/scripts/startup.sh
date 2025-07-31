#!/bin/bash

# Wait for database to be ready
echo "Waiting for database to be ready..."
while ! mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent; do
    sleep 1
done
echo "Database is ready!"

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --no-dev --optimize-autoloader
fi

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Create storage link if it doesn't exist
if [ ! -L "public/storage" ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

# Clear and cache config
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm 