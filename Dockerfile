FROM php:8.2-apache

# Install system dependencies with explicit PostgreSQL packages
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

# Install PHP extensions - ensure pgsql and pdo_pgsql are installed separately and explicitly
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install pdo_pgsql

# Create custom PHP configuration files to ensure PostgreSQL extensions are enabled
RUN echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/pdo_pgsql.ini
RUN echo "extension=pgsql.so" > /usr/local/etc/php/conf.d/pgsql.ini

# Verify PostgreSQL extensions are installed
RUN php -m | grep pgsql || (echo "PostgreSQL extension not installed!" && exit 1)
RUN php -m | grep pdo_pgsql || (echo "PostgreSQL PDO driver not installed!" && exit 1)

# Configure PHP
RUN echo "memory_limit=2G" > /usr/local/etc/php/conf.d/memory-limit.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Create log directory and set proper permissions
RUN mkdir -p /var/www/html/storage/logs
RUN touch /var/www/html/storage/logs/laravel.log
RUN chown -R www-data:www-data /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/bootstrap/cache

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
\n\
# Explicitly set PostgreSQL as the only database connection\n\
echo "DB_CONNECTION=pgsql" >> .env\n\
\n\
# Users database connection - ensure each parameter is set separately and properly quoted\n\
echo "USERS_DB_HOST=\"${USERS_DB_HOST}\"" >> .env\n\
echo "USERS_DB_PORT=${USERS_DB_PORT:-5432}" >> .env\n\
echo "USERS_DB_DATABASE=\"${USERS_DB_DATABASE}\"" >> .env\n\
echo "USERS_DB_USERNAME=\"${USERS_DB_USERNAME}\"" >> .env\n\
echo "USERS_DB_PASSWORD=\"${USERS_DB_PASSWORD}\"" >> .env\n\
\n\
# Manga database connection - ensure each parameter is set separately and properly quoted\n\
echo "MANGA_DB_HOST=\"${MANGA_DB_HOST}\"" >> .env\n\
echo "MANGA_DB_PORT=${MANGA_DB_PORT:-5432}" >> .env\n\
echo "MANGA_DB_DATABASE=\"${MANGA_DB_DATABASE}\"" >> .env\n\
echo "MANGA_DB_USERNAME=\"${MANGA_DB_USERNAME}\"" >> .env\n\
echo "MANGA_DB_PASSWORD=\"${MANGA_DB_PASSWORD}\"" >> .env\n\
\n\
# Full connection URLs for direct use\n\
echo "USERS_DB_URL=\"${USERS_DB_URL}\"" >> .env\n\
echo "MANGA_DB_URL=\"${MANGA_DB_URL}\"" >> .env\n\
\n\
# Session and cache configuration - explicitly use PostgreSQL\n\
echo "CACHE_DRIVER=database" >> .env\n\
echo "QUEUE_CONNECTION=sync" >> .env\n\
echo "SESSION_DRIVER=database" >> .env\n\
echo "SESSION_CONNECTION=users_db" >> .env\n\
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
# Check if PostgreSQL extension is installed\n\
echo "Checking PHP extensions..."\n\
php -m | grep pdo\n\
php -m | grep pgsql\n\
php -m | grep pdo_pgsql\n\
\n\
# Debug database connection parameters in detail\n\
echo "Database connection parameters (detailed):"\n\
echo "USERS_DB_HOST: \"${USERS_DB_HOST}\""\n\
echo "USERS_DB_PORT: \"${USERS_DB_PORT:-5432}\""\n\
echo "USERS_DB_DATABASE: \"${USERS_DB_DATABASE}\""\n\
echo "USERS_DB_USERNAME: \"${USERS_DB_USERNAME}\""\n\
echo "USERS_DB_URL: \"${USERS_DB_URL}\""\n\
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
