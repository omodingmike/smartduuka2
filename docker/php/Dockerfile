FROM php:8.4-fpm

ENV HOME=/var/www
ENV CHROME_CRASHPAD_DATABASE_DIR=/tmp/crashpad
ENV PUPPETEER_CACHE_DIR=/app/.cache/puppeteer

# 1. System Dependencies
RUN apt-get update && apt-get install -y libicu-dev libuv1-dev \
    curl nano gnupg ca-certificates supervisor git unzip libpq-dev \
    jpegoptim optipng pngquant gifsicle libavif-bin libmagickwand-dev \
    libzip-dev libx11-xcb1 libxcomposite1 libxshmfence1 libglu1 libnss3 \
    libasound2 libatk1.0-0 libatk-bridge2.0-0 libcairo2 libcups2 \
    libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc-s1 libglib2.0-0 \
    libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 \
    libx11-6 libxcb1 libxcursor1 libxdamage1 libxext6 libxfixes3 \
    libxi6 libxrandr2 libxrender1 libxss1 libxtst6 \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. PHP Extensions
RUN docker-php-ext-install pdo_pgsql pgsql gd exif zip intl opcache pcntl \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick opcache

# 3. Node & Puppeteer + Chrome
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g puppeteer --unsafe-perm=true \
    && npx puppeteer browsers install chrome-headless-shell

# 4. Fix cache ownership right after Chrome install
RUN mkdir -p /app/.cache/puppeteer /tmp/crashpad \
    && chown -R www-data:www-data /app/.cache /tmp/crashpad

# 5. Composer & Supercronic
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.33/supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=71b0d58cc53f6bd72cf2f293e09e294b79c666d8
RUN curl -fsSLO "$SUPERCRONIC_URL" && \
    echo "${SUPERCRONIC_SHA1SUM}  supercronic-linux-amd64" | sha1sum -c - && \
    chmod +x "supercronic-linux-amd64" && \
    mv "supercronic-linux-amd64" "/usr/local/bin/supercronic"

# 6. Dependency Caching
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --no-dev --ignore-platform-reqs

# 7. Permissions & Environment Fix
RUN mkdir -p /var/www/.cache/composer /app/storage /app/bootstrap/cache/filament /var/run/php /app/public/media \
    && chown -R www-data:www-data /var/www /app /var/run/php \
    && chmod -R 775 /app/storage /app/bootstrap/cache /var/run/php

# 8. Copy Code
COPY --chown=www-data:www-data . .

# 9. Finalize Laravel
RUN git config --global --add safe.directory /app && \
    composer install --no-dev --optimize-autoloader --no-interaction && \
    composer dump-autoload --optimize --no-scripts && \
    php artisan package:discover --ansi && \
    php artisan storage:link || true && \
    chown -R www-data:www-data /app/bootstrap/cache

# 10. Configurations
RUN rm -f /usr/local/etc/php-fpm.d/www.conf.default && \
    rm -f /usr/local/etc/php-fpm.d/zz-docker.conf

COPY docker/php/supervisord/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/preload.php /app/preload.php
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# 11. Chrome symlink with validation
RUN CHROME_BIN=$(find /app/.cache/puppeteer -name 'chrome' -o -name 'chrome-headless-shell' | grep -v '\.zip' | head -n 1) \
    && echo "Found Chrome at: $CHROME_BIN" \
    && ln -sf "$CHROME_BIN" /usr/local/bin/chrome-browser \
    && chmod +x /usr/local/bin/chrome-browser

USER www-data
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]