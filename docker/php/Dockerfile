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
# Install commonly needed packages for Imagick dependencies and clean up
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --- FIX 1: Install Supercronic ---
# Download and install Supercronic for the Supervisor cron program
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.1.12/supercronic-linux-amd64
ENV SUPERCRONIC_SHA256=2b69c4dd7063d8194458f335043a532729a997645f7c3275727976a16087b320

RUN curl -fSL "$SUPERCRONIC_URL" -o /usr/local/bin/supercronic \
    && echo "$SUPERCRONIC_SHA256 /usr/local/bin/supercronic" | sha256sum -c - \
    && chmod +x /usr/local/bin/supercronic
# -----------------------------------

# Install PHP extensions (Redis + Imagick included)
RUN docker-php-ext-install pdo_pgsql pgsql gd exif zip \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick

# --- Potential Fix for Exit Status 78 (PHP-FPM Config) ---
# Ensure PHP-FPM configuration is set up for running with Supervisor
# Copy the default configuration and ensure it listens on port 9000
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
# NOTE: You will need to create a simple www.conf file if you don't have one
# (See note below on what to put in www.conf)
# -----------------------------------------------------------

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