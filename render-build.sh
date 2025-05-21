#!/usr/bin/env bash
# Exit on error
set -o errexit

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Install NPM dependencies and build assets
npm ci
npm run build

# Ensure the build directory exists and is writable
mkdir -p public/build
chmod -R 755 public/build

# Copy the built assets to the public directory
cp -R public/build/assets/* public/build/

# Laravel optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create SQLite database if using SQLite
touch database/database.sqlite
php artisan migrate --force
