FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    libsqlite3-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pdo_sqlite
RUN docker-php-ext-install zip

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create .env file from .env.example
RUN cp .env.example .env

RUN sed -i "s/APP_DEBUG=false/APP_DEBUG=true/g" .env


# Configure database
RUN sed -i "s/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/g" .env && \
    sed -i "s/DB_DATABASE=laravel/DB_DATABASE=\/var\/www\/html\/database\/database.sqlite/g" .env

# Create SQLite database
RUN mkdir -p database
RUN touch database/database.sqlite
RUN chmod 666 database/database.sqlite

RUN sed -i "s/SESSION_DRIVER=database/SESSION_DRIVER=file/g" .env


# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate application key
RUN php artisan key:generate

# Run migrations
RUN php artisan migrate --force

# Set permissions
RUN chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html


# Configure Apache
RUN sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
