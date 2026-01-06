# Use official PHP 8.4 FPM image
FROM php:8.4-fpm

# Set working directory
WORKDIR /app

ENV HOME=/var/www
ENV CHROME_CRASHPAD_DATABASE_DIR=/tmp/crashpad

# --------------------------
# Install system dependencies (as root)
# --------------------------
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
    libx11-xcb1 libxcomposite1 libxshmfence1 libglu1 libnss3 libasound2 libatk1.0-0 libatk-bridge2.0-0 libcairo2 libcups2 \
    libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc1 libglib2.0-0 libgtk-3-0 libnspr4 \
    libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libxcb1 libxcursor1 libxdamage1 \
    libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --------------------------
# Install PHP extensions
# --------------------------
RUN docker-php-ext-install pdo_pgsql pgsql gd exif zip \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick

# --------------------------
# Install Node.js and NPM (NodeSource)
# --------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs


# 4. Set the cache dir so bunx/puppeteer knows where to put Chrome
ENV PUPPETEER_CACHE_DIR=/app/.cache/puppeteer

# --------------------------
# Install Puppeteer & Chrome via NPM
# --------------------------
ENV PUPPETEER_CACHE_DIR=/app/.cache/puppeteer

RUN npm install -g puppeteer --unsafe-perm=true \
    && npx puppeteer install chrome

# Create the stable symlink for your PHP code
RUN ln -s $(find /app/.cache/puppeteer -name chrome -type f -executable | head -n 1) /usr/local/bin/chrome-browser

# --------------------------
# Install Supercronic
# --------------------------
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.33/supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=71b0d58cc53f6bd72cf2f293e09e294b79c666d8 \
    SUPERCRONIC=supercronic-linux-amd64

RUN curl -fsSLO "$SUPERCRONIC_URL" && \
    echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - && \
    chmod +x "$SUPERCRONIC" && \
    mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" && \
    ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic

# --------------------------
# Install Composer
# --------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --------------------------
# Copy application code
# --------------------------
COPY . /app

# --------------------------
# Install composer dependencies
# --------------------------
RUN composer install --no-interaction --no-progress --no-dev --prefer-dist --optimize-autoloader --ignore-platform-reqs --no-scripts

# --------------------------
# Laravel artisan optimizations
# --------------------------
RUN #php artisan key:generate --force && php artisan storage:link && php artisan config:cache && php artisan route:cache && php artisan view:cache
RUN  #php artisan storage:link && php artisan config:cache && php artisan route:cache && php artisan view:cache

# --------------------------
# Copy Supervisor config
# --------------------------
COPY docker/php/supervisord/supervisord.conf /etc/supervisor/supervisord.conf

# --------------------------
# Add OPcache Configuration (Crucial for Production Performance)
# --------------------------
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini

# Preload script
COPY docker/php/preload.php /app/preload.php

# --------------------------
# Switch to non-root user
# --------------------------

RUN chmod -R 1777 /tmp


# --------------------------
# Permissions fix for www-data (storage + cache)
# --------------------------
# Make storage and bootstrap/cache writable by container user
RUN mkdir -p /app/storage /app/bootstrap/cache /app/.cache /tmp/crashpad /app/public/media \
    && chmod -R 777 /app/storage /app/bootstrap/cache /app/.cache /tmp/crashpad /app/public/media \
    && chmod -R 1777 /tmp


# --------------------------
# Set PHP to "Unlimited" Mode
# --------------------------
RUN touch /usr/local/etc/php/conf.d/unlimited.ini \
    && echo "upload_max_filesize = 0" >> /usr/local/etc/php/conf.d/unlimited.ini \
    && echo "post_max_size = 0" >> /usr/local/etc/php/conf.d/unlimited.ini \
    && echo "memory_limit = -1" >> /usr/local/etc/php/conf.d/unlimited.ini \
    && echo "max_execution_time = 0" >> /usr/local/etc/php/conf.d/unlimited.ini \
    && echo "max_input_time = -1" >> /usr/local/etc/php/conf.d/unlimited.ini \
    && echo "request_terminate_timeout = 0" >> /usr/local/etc/php/conf.d/unlimited.ini


# Ensure www-data owns the app directory so it can create symlinks in /public
RUN chown -R www-data:www-data /app

USER www-data

# Expose PHP-FPM port
EXPOSE 9000

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
