# Use official PHP 8.4 FPM image
FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    nano \
    gnupg \
    ca-certificates \
    supervisor \
    git \
    unzip \
    libpq-dev \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    libavif-bin \
    libmagickwand-dev \
    libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (Redis + Imagick included)
RUN docker-php-ext-install pdo_pgsql pgsql gd exif zip \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . /var/www

# Ensure .env exists BEFORE artisan commands
COPY .env /var/www/.env

# Install composer dependencies
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# Laravel artisan commands (need vendor + .env)
RUN php artisan key:generate --force \
    && php artisan storage:link \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Set correct permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy Supervisor config
COPY docker/php/supervisord/supervisord.conf /etc/supervisor/supervisord.conf

# Expose PHP-FPM port
EXPOSE 9000

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
