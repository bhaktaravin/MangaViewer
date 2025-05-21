FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Configure PHP
RUN echo "memory_limit=2G" > /usr/local/etc/php/conf.d/memory-limit.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy bootstrap directory first with env helper
COPY bootstrap/env_helper.php bootstrap/env_helper.php

# Copy composer files
COPY composer.json composer.lock* ./

# Install Composer dependencies without scripts and autoloader
RUN COMPOSER_MEMORY_LIMIT=-1 COMPOSER_DISABLE_XDEBUG_WARN=1 composer install --no-interaction --prefer-dist --no-dev --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Generate autoloader
RUN COMPOSER_MEMORY_LIMIT=-1 composer dump-autoload --optimize

# Run package discovery separately
RUN php -d memory_limit=-1 artisan package:discover --ansi || true

# Configure Apache
RUN a2enmod rewrite
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Install Node.js dependencies and build assets
RUN npm ci && npm run build

# Copy .env.example to .env if .env doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Generate application key
RUN php artisan key:generate --force

# Cache configuration
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Create SQLite database if using SQLite
RUN mkdir -p database
RUN touch database/database.sqlite
RUN chown -R www-data:www-data database
RUN php artisan migrate --force

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
