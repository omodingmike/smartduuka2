FROM php:8.5-fpm

ENV HOME=/var/www
ENV CHROME_CRASHPAD_DATABASE_DIR=/tmp/crashpad
ENV PUPPETEER_CACHE_DIR=/app/.cache/puppeteer

# 1. System Dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev libuv1-dev \
    curl gnupg ca-certificates supervisor git unzip libpq-dev \
    jpegoptim optipng pngquant gifsicle libavif-bin libmagickwand-dev \
    libzip-dev libx11-xcb1 libxcomposite1 libxshmfence1 libglu1 libnss3 \
    libasound2 libatk1.0-0 libatk-bridge2.0-0 libcairo2 libcups2 \
    libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc-s1 libglib2.0-0 \
    libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 \
    libx11-6 libxcb1 libxcursor1 libxdamage1 libxext6 libxfixes3 \
    libxi6 libxrandr2 libxrender1 libxss1 libxtst6 \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. PHP Extensions
# opcache is statically compiled into PHP 8.5 — no need to install or enable it
RUN docker-php-ext-install pdo_pgsql pgsql gd exif zip intl pcntl \
    && pecl install redis imagick-3.8.1 \
    && docker-php-ext-enable redis imagick

# 3. Node & Puppeteer + Chrome
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && npm install -g puppeteer --unsafe-perm=true \
    && npx puppeteer browsers install chrome

# 4. Composer & Supercronic
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.33/supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=71b0d58cc53f6bd72cf2f293e09e294b79c666d8
RUN curl -fsSLO "$SUPERCRONIC_URL" && \
    echo "${SUPERCRONIC_SHA1SUM}  supercronic-linux-amd64" | sha1sum -c - && \
    chmod +x "supercronic-linux-amd64" && \
    mv "supercronic-linux-amd64" "/usr/local/bin/supercronic"

# 5. Composer dependency cache layer
# Copy only composer files first so this layer is only invalidated when
# dependencies change, not on every code change.
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer update --no-interaction --no-scripts --no-autoloader --no-dev --ignore-platform-reqs

# 6. Copy full application code
COPY --chown=www-data:www-data . .

# 7. Finalize Laravel
# Must run AFTER full code copy so vendor, app code, and configs are all present.
# composer install here completes autoloader generation on top of the cached packages from step 5.
RUN git config --global --add safe.directory /app && \
    composer install --no-dev --optimize-autoloader --no-interaction && \
    composer dump-autoload --optimize --no-scripts && \
    php artisan package:discover --ansi && \
    php artisan storage:link || true

# 8. Directories, ownership, and permissions — set AFTER all files are in place
RUN mkdir -p \
        /var/www/.cache/composer \
        /app/storage/framework/sessions \
        /app/storage/framework/views \
        /app/storage/framework/cache \
        /app/storage/logs \
        /app/bootstrap/cache/filament \
        /var/run/php \
        /app/public/media \
        /app/.cache/puppeteer \
        /tmp/crashpad \
    && chown -R www-data:www-data /var/www /app /var/run/php /tmp/crashpad \
    && chmod -R 775 /app/storage /app/bootstrap/cache /var/run/php

# 9. PHP-FPM & Supervisor configuration
# Remove defaults — zz-docker.conf only holds "daemonize = no";
# do not re-enable daemonization in www.conf
RUN rm -f /usr/local/etc/php-fpm.d/www.conf.default \
    && rm -f /usr/local/etc/php-fpm.d/zz-docker.conf

COPY docker/php/supervisord/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/php/opcache.ini                  /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/preload.php                  /app/preload.php
COPY docker/php/www.conf                     /usr/local/etc/php-fpm.d/www.conf

# Ensure config files copied above are also owned by www-data
RUN chown www-data:www-data /app/preload.php

# 10. Chrome wrapper
RUN CHROME_BIN=$(find /app/.cache/puppeteer -type f -name 'chrome' | grep -v '\.zip' | head -n 1) \
    && echo "Found Chrome at: $CHROME_BIN" \
    && printf '#!/bin/sh\nexec "%s" --no-sandbox --disable-setuid-sandbox --disable-dev-shm-usage "$@"\n' "$CHROME_BIN" \
        > /usr/local/bin/chrome-browser \
    && chmod +x /usr/local/bin/chrome-browser

USER www-data
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]