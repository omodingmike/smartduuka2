#!/bin/bash
set -e

# ----------------------------
# CONFIG
# ----------------------------
APP_DIR=~/smartduuka
APP_NAME=smartduuka
BACKEND_DIR="$APP_DIR/backend"

echo "🚀 Starting backend deployment..."

# ----------------------------
# PULL LATEST CHANGES
# ----------------------------
if [ -d "$BACKEND_DIR" ]; then
  echo "Pulling latest changes for backend..."

  cd "$BACKEND_DIR"
  # Ensure media directory exists

  git pull origin main
else
  echo "Error: Backend directory not found at $BACKEND_DIR. Exiting."
  exit 1
fi

# Rebuild and recreate API container
sudo docker-compose up -d --no-deps --force-recreate --build api

# ----------------------------
# WAIT FOR API CONTAINER
# ----------------------------
echo "⏳ Waiting for API container to be ready..."
until sudo docker-compose exec -T api php artisan up; do
  echo "Waiting for API..."
  sleep 5
done

mkdir -p /var/www/laravel/public/media
chown -R www-data:www-data /var/www/laravel/public/media

# ----------------------------
# RUN MIGRATIONS & CLEAR CACHE
# ----------------------------
echo "🗄️ Running migrations..."
sudo docker-compose exec -T api php artisan migrate --force

sudo docker-compose exec -T api php artisan storage:link

echo "🧹 Clearing caches..."
sudo docker-compose exec -T api php artisan optimize:clear
sudo docker-compose exec -T api php artisan config:clear
sudo docker-compose exec -T api php artisan cache:clear
sudo docker-compose exec -T api php artisan route:clear

echo "🧹 Optimizing..."
sudo docker-compose exec -T api php artisan optimize
sudo docker-compose exec -T api php artisan config:cache
sudo docker-compose exec -T api php artisan event:cache
sudo docker-compose exec -T api php artisan route:cache
sudo docker-compose exec -T api php artisan view:cache

# ----------------------------
# CHECK STATUS
# ----------------------------
if ! sudo docker-compose ps | grep "api" | grep "Up"; then
  echo "❌ API container failed to start. Check logs with 'docker-compose logs api'."
  exit 1
fi

APP_NAME=smartduuka # Set your app name

# 1️⃣ Remove old Nginx config if it exists
sudo rm -f /etc/nginx/sites-enabled/$APP_NAME
sudo rm -f /etc/nginx/sites-available/$APP_NAME

# 2️⃣ Copy new config to sites-available
sudo cp docker/nginx/conf.d/app.conf /etc/nginx/sites-available/$APP_NAME

# 3️⃣ Enable the site by creating a symlink in sites-enabled
sudo ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/$APP_NAME

# 4️⃣ Test Nginx configuration
sudo nginx -t

# 5️⃣ Stop Nginx temporarily to allow Certbot to run in standalone mode
sudo systemctl stop nginx

# 6️⃣ Run Certbot here if needed
# sudo certbot certonly --standalone -d yourdomain.com

# 7️⃣ Restart Nginx to apply the new configuration
sudo systemctl restart nginx

echo "✅ Backend deployment complete!"
