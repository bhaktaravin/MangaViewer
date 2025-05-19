#!/usr/bin/env bash
# Exit on error
set -o errexit

npm ci
npm run build
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create SQLite database if using SQLite
if [ "$DB_CONNECTION" = "sqlite" ]; then
    touch database/database.sqlite
    php artisan migrate --force
fi
