#!/usr/bin/env bash
# Exit on error
set -o errexit

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Install NPM dependencies and build assets
npm ci
npm run build

# Debug: List build directory contents
echo "Contents of public/build directory:"
ls -la public/build

# Ensure manifest.json exists
if [ ! -f "public/build/manifest.json" ]; then
    echo "Error: manifest.json not found!"
    # Copy it from the expected location if it exists elsewhere
    if [ -f "public/build/assets/manifest.json" ]; then
        echo "Found manifest.json in assets directory, copying..."
        cp public/build/assets/manifest.json public/build/
    fi
fi

# Laravel optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create SQLite database if using SQLite
touch database/database.sqlite
php artisan migrate --force
