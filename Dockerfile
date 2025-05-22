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
    npm \
    postgresql-client \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo pdo_pgsql

# Configure PHP
RUN echo "memory_limit=2G" > /usr/local/etc/php/conf.d/memory-limit.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Create a simple .env file with basic settings
RUN echo "APP_NAME=MangaView\n\
APP_ENV=production\n\
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx\n\
APP_DEBUG=true\n\
APP_URL=http://localhost\n\
USERS_DB_HOST=\${USERS_DB_HOST}\n\
USERS_DB_PORT=\${USERS_DB_PORT}\n\
USERS_DB_DATABASE=\${USERS_DB_DATABASE}\n\
USERS_DB_USERNAME=\${USERS_DB_USERNAME}\n\
USERS_DB_PASSWORD=\${USERS_DB_PASSWORD}\n\
MANGA_DB_HOST=\${MANGA_DB_HOST}\n\
MANGA_DB_PORT=\${MANGA_DB_PORT}\n\
MANGA_DB_DATABASE=\${MANGA_DB_DATABASE}\n\
MANGA_DB_USERNAME=\${MANGA_DB_USERNAME}\n\
MANGA_DB_PASSWORD=\${MANGA_DB_PASSWORD}\n\
CACHE_DRIVER=database\n\
QUEUE_CONNECTION=sync\n\
SESSION_DRIVER=database\n\
SESSION_LIFETIME=120" > .env

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Install Composer dependencies with --no-scripts to avoid package discovery
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-scripts

# Run package discovery manually after all dependencies are installed
RUN php -r "class env { public function __toString() { return ''; } } function env(\$key, \$default = null) { return \$default; }" && \
    php artisan package:discover --ansi

# Install Node.js dependencies and build assets
RUN npm ci && npm run build

# Configure Apache
RUN a2enmod rewrite
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Generate application key
RUN php artisan key:generate --force

# Make the database setup script executable
RUN chmod +x setup_database.sh

# Cache configuration
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Expose port 80
EXPOSE 80

# Start Apache and run database setup script
CMD bash -c "./setup_database.sh && apache2-foreground"
