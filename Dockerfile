FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Copy .env.example to .env if .env doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Create a custom bootstrap file to fix the env() issue
RUN echo "<?php \
if (!function_exists('env')) { \
    function env(\$key, \$default = null) { \
        return \$default; \
    } \
}" > /var/www/html/bootstrap/env_helper.php

# Modify composer.json to include our helper before autoloading
RUN sed -i 's/"autoload": {/"autoload": { "files": ["bootstrap\/env_helper.php"],/g' composer.json

# Install Composer dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install Node.js dependencies and build assets
RUN npm ci && npm run build

# Configure Apache
RUN a2enmod rewrite
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Generate application key
RUN php artisan key:generate --force

# Cache configuration
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Create SQLite database if using SQLite
RUN touch database/database.sqlite
RUN php artisan migrate --force

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
