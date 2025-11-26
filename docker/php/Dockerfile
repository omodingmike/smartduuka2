# Use official PHP 8.4 FPM image
FROM php:8.4-fpm

# Set working directory
WORKDIR /app

# Install system dependencies + Spatie recommended tools + Imagick
RUN apt-get update && apt-get install -y \
    curl \
    nano \
    gnupg \
    ca-certificates \
    nodejs \
    npm \
    supervisor \
    git \
    unzip \
    libpq-dev \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    libavif-bin \
    libmagickwand-dev --no-install-recommends \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install SVGO (for SVG optimization)
RUN npm install -g svgo

# Install PHP extensions including Redis and Imagick
RUN apt-get update && apt-get install -y \
        libmagickwand-dev --no-install-recommends \
        libzip-dev \
    && docker-php-ext-install pdo_pgsql pgsql gd exif zip \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Supercronic (for cron tasks)
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.33/supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=71b0d58cc53f6bd72cf2f293e09e294b79c666d8 \
    SUPERCRONIC=supercronic-linux-amd64

RUN curl -fsSLO "$SUPERCRONIC_URL" && \
    echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - && \
    chmod +x "$SUPERCRONIC" && \
    mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" && \
    ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic

# Copy Laravel app source
COPY . /app

# Install Composer dependencies
RUN composer install --no-interaction --optimize-autoloader

# Generate application key and cache configs/routes/views
RUN php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Set correct permissions for Laravel storage
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Optional: install chokidar for file watching
RUN npm install --save-dev chokidar

# Copy Supervisor configuration
COPY docker/php/supervisord/supervisord.conf /etc/supervisor/supervisord.conf

# Expose PHP-FPM port
EXPOSE 9000

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
