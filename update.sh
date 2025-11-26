#!/bin/bash
set -e

# ----------------------------
# CONFIG
# ----------------------------
APP_DIR=~/smartduuka
BACKEND_DIR="$APP_DIR/backend"

echo "🚀 Starting backend deployment..."

# ----------------------------
# PULL LATEST CHANGES
# ----------------------------
if [ -d "$BACKEND_DIR" ]; then
  echo "Pulling latest changes for backend..."

  cd "$BACKEND_DIR"
  # Ensure media directory exists

  git reset --hard origin/main
else
  echo "Error: Backend directory not found at $BACKEND_DIR. Exiting."
  exit 1
fi

# Rebuild and recreate API container
sudo docker-compose up -d --no-deps --force-recreate --build

# ----------------------------
# WAIT FOR API CONTAINER
# ----------------------------
echo "⏳ Waiting for API container to be ready..."
until sudo docker-compose exec -T api php artisan up; do
  echo "Waiting for API..."
  sleep 5
done

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

echo "✅ Backend deployment complete!"
