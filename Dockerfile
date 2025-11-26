FROM php:8.4-alpine

# Set working directory
WORKDIR /app

# Install system dependencies & build tools
RUN apk add --no-cache \
    bash curl nano zip unzip dos2unix \
# === IMAGE MANIPULATION DEPENDENCIES ===
    # Add imagemagick-dev for the imagick PHP extension
    libpng-dev libjpeg-turbo-dev freetype-dev imagemagick-dev \
    # =======================================
    postgresql-dev mysql-client mariadb-connector-c-dev \
    # Install Spatie Media Library Optimizers (Alpine)
    jpegoptim optipng pngquant gifsicle libavif-apps \
    # =======================================
    nodejs npm \
    $PHPIZE_DEPS \
    # Install Redis extension
    && pecl install redis \
    && docker-php-ext-enable redis \
    \
    # Install Imagick extension (Requires imagemagick-dev)
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Install PHP extensions
# We keep gd configuration for legacy/fallback, but Imagick is now enabled
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql pdo_pgsql mysqli exif \
    && docker-php-ext-enable exif

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install svgo globally (via npm)
RUN npm install -g svgo

# Copy project files
COPY . /app
COPY .env /var/www/.env

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Fix permissions for Laravel storage, cache, and media
RUN mkdir -p /var/www/public/media \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public/media \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/public/media

# Expose port
EXPOSE 9000

# Copy Supervisor configuration
COPY docker/php/supervisord.conf /etc/supervisord.conf

# Run Laravel app
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]