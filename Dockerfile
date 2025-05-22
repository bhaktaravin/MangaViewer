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

# Make the database setup script executable
RUN chmod +x setup_database.sh

# Create a startup script to properly handle environment variables
RUN echo '#!/bin/bash\n\
# Generate proper .env file with actual environment variables\n\
echo "APP_NAME=MangaView" > .env\n\
echo "APP_ENV=production" >> .env\n\
echo "APP_DEBUG=true" >> .env\n\
echo "APP_URL=${APP_URL}" >> .env\n\
echo "USERS_DB_HOST=${USERS_DB_HOST}" >> .env\n\
echo "USERS_DB_PORT=${USERS_DB_PORT}" >> .env\n\
echo "USERS_DB_DATABASE=${USERS_DB_DATABASE}" >> .env\n\
echo "USERS_DB_USERNAME=${USERS_DB_USERNAME}" >> .env\n\
echo "USERS_DB_PASSWORD=${USERS_DB_PASSWORD}" >> .env\n\
echo "MANGA_DB_HOST=${MANGA_DB_HOST}" >> .env\n\
echo "MANGA_DB_PORT=${MANGA_DB_PORT}" >> .env\n\
echo "MANGA_DB_DATABASE=${MANGA_DB_DATABASE}" >> .env\n\
echo "MANGA_DB_USERNAME=${MANGA_DB_USERNAME}" >> .env\n\
echo "MANGA_DB_PASSWORD=${MANGA_DB_PASSWORD}" >> .env\n\
echo "CACHE_DRIVER=database" >> .env\n\
echo "QUEUE_CONNECTION=sync" >> .env\n\
echo "SESSION_DRIVER=database" >> .env\n\
echo "SESSION_LIFETIME=120" >> .env\n\
echo "LOG_CHANNEL=stderr" >> .env\n\
\n\
# Generate application key if not already set\n\
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env || grep -q "^APP_KEY=base64:$" .env; then\n\
  echo "Generating application key..."\n\
  php artisan key:generate --force\n\
else\n\
  echo "Application key already exists."\n\
fi\n\
\n\
# Clear config cache\n\
php artisan config:clear\n\
\n\
# Run database setup script\n\
./setup_database.sh\n\
\n\
# Start Apache\n\
apache2-foreground\n\
' > /var/www/html/start.sh && chmod +x /var/www/html/start.sh

# Expose port 80
EXPOSE 80

# Use the startup script as the entry point
CMD ["/var/www/html/start.sh"]
