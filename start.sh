#!/bin/bash

set -e  # Exit immediately if any command fails
echo "Starting Laravel container setup..."

# Debugging: Print the current directory
echo "Current directory: $(pwd)"

# Debugging: List files in the /app directory
echo "Files in /app:"
ls -la /app

# Ensure we are in the correct directory
cd /app || exit

# Ensure the .env file has an APP_KEY
if ! grep -q "^APP_KEY=" .env; then
    echo "APP_KEY is missing. Generating a new one..."
    php artisan key:generate --force
fi

# Clear and cache configurations
echo "Clearing and caching configurations..."
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# Ensure correct permissions for storage and bootstrap/cache
echo "Setting permissions for storage and cache..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Ensure Laravel storage is linked
echo "Creating symbolic link for storage..."
php artisan storage:link || echo "Storage link already exists."

# Run migrations safely
echo "Running database migrations..."
php artisan migrate --force >> storage/logs/migration.log 2>&1
echo "Migrations completed. Check logs at storage/logs/migration.log."

# Set up cron job for Laravel scheduler
echo "* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root
crond &

#!/bin/bash

# Alias to add
ALIAS_CMD="alias api='docker exec -it api /bin/bash'"
BASHRC="$HOME/.bashrc"

# Check if alias already exists
if ! grep -Fxq "$ALIAS_CMD" "$BASHRC"; then
    echo "" >> "$BASHRC"           # optional blank line
    echo "# Alias for entering API container" >> "$BASHRC"
    echo "$ALIAS_CMD" >> "$BASHRC"
    echo "Added 'api' alias to $BASHRC"
fi

# Optional: source the .bashrc so the alias is immediately available in this shell
# shellcheck disable=SC1090
source "$BASHRC"

# Start Laravel application
echo "Starting Laravel application on port 8000..."
exec php artisan serve --host=0.0.0.0 --port=8000
