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
COPY .env /app/.env

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Fix permissions for Laravel storage, cache, and media
RUN mkdir -p /app/public/media \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/public/media \
    && chmod -R 775 /app/storage /app/bootstrap/cache /app/public/media


# Remove existing start.sh before copying a new one
RUN rm -f /start.sh

# Copy and prepare startup script
COPY start.sh /start.sh
RUN dos2unix /start.sh && chmod +x /start.sh

# Expose port
EXPOSE 8000

# Run Laravel app
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]