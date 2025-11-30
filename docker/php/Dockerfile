#!/bin/bash
set -e

# ----------------------------
# CONFIG
# ----------------------------
APP_DIR=~/smartduuka
APP_NAME=smartduuka
BACKEND_DIR="$APP_DIR/backend"

echo "🚀 Starting deployment for $APP_NAME..."

# ----------------------------
# PULL LATEST CHANGES
# ----------------------------
if [ -d "$BACKEND_DIR" ]; then
  echo "Pulling latest changes for backend..."

  cd "$BACKEND_DIR"
  git pull origin main
else
  echo "Error: Backend directory not found at $BACKEND_DIR. Exiting."
  exit 1
fi

# ----------------------------
# BUILD & RECREATE CONTAINERS
# ----------------------------
echo "🔨 Building and recreating all web services (api, nginx, frontend)..."

# Rebuilds API (Laravel), Nginx (config files), and Frontend (Next.js) if their source/config changed.
# --force-recreate ensures a fresh start, especially after file structure changes.
sudo docker-compose up -d --build --force-recreate api nginx frontend

# ----------------------------
# WAIT FOR API CONTAINER
# ----------------------------
echo "⏳ Waiting for API container to be ready..."
# Wait for the API container to accept Artisan commands (app is ready to handle traffic)
until sudo docker-compose exec -T api php artisan up; do
  echo "Waiting for API..."
  sleep 3
done

# ----------------------------
# RUN MIGRATIONS & CLEAR CACHE
# ----------------------------
echo "🗄️ Running migrations..."
sudo docker-compose exec -T api php artisan migrate --force

echo "🔗 Relinking storage (if needed)..."
sudo docker-compose exec -T api php artisan storage:link

echo "🧹 Clearing and optimizing caches..."
# Clear all caches (safer during deployment)
sudo docker-compose exec -T api php artisan optimize:clear

# Re-optimize/re-cache for production performance
sudo docker-compose exec -T api php artisan optimize
sudo docker-compose exec -T api php artisan config:cache
sudo docker-compose exec -T api php artisan event:cache
sudo docker-compose exec -T api php artisan route:cache
sudo docker-compose exec -T api php artisan view:cache

# ----------------------------
# CHECK STATUS
# ----------------------------
echo "🔎 Checking container status..."
if ! sudo docker-compose ps | grep "api" | grep "Up" && ! sudo docker-compose ps | grep "nginx" | grep "Up"; then
  echo "❌ One or more essential containers failed to start. Check logs."
  exit 1
fi

echo "✅ Deployment complete! New containers are running."