#!/bin/bash

# Fix storage permissions
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Set APP_URL from environment or use default
if [ -z "$APP_URL" ]; then
    export APP_URL="https://housekeeping-mth6.onrender.com"
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force 2>&1 || echo "Migrations may have already run or encountered an error"

# Clear all caches and rebuild
echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Rebuild caches
echo "Building caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✓ Application ready"

# Start Apache
echo "Starting Apache on port 10000..."
apache2-foreground
