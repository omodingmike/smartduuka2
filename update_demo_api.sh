#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="$HOME/smartduuka"
BACKEND_DIR="$APP_DIR/api_demo"
REPO_URL="git@github-api:omodingmike/smartduuka2.git"
BRANCH="dev"
COMPOSE="docker compose -f docker-compose.demo.yml"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"; }
fail() { echo "❌ $*" >&2; exit 1; }

# Clone or pull
if [ ! -d "$BACKEND_DIR/.git" ]; then
  log "📥 Cloning dev branch..."
  git clone --branch "$BRANCH" "$REPO_URL" "$BACKEND_DIR"
else
  log "⬇ Pulling latest dev changes..."
  sudo chown -R "$(whoami):$(whoami)" "$BACKEND_DIR"
  git -C "$BACKEND_DIR" fetch origin
  git -C "$BACKEND_DIR" reset --hard origin/"$BRANCH"
fi

cd "$BACKEND_DIR"

# Fix permissions
for DIR in storage bootstrap/cache public public/media public/static; do
  sudo mkdir -p "$DIR"
  sudo chown -R 33:33 "$DIR"
  sudo chmod -R 775 "$DIR"
done

# Install dependencies
$COMPOSE run --rm api_demo bash -c "composer install --no-dev --no-interaction"

# Build containers
log "🐳 Building demo containers..."
$COMPOSE up -d --build --force-recreate api_demo

# Wait for api_demo
log "⏳ Waiting for api_demo to be healthy..."
RETRY=0
until $COMPOSE exec -T api_demo php artisan --version >/dev/null 2>&1; do
  RETRY=$((RETRY+1))
  [ "$RETRY" -ge 20 ] && fail "api_demo did not become ready"
  sleep 3
done

# Run demo tenant migrations only — do NOT touch prod tenants
log "🗄 Running demo tenant migrations..."
$COMPOSE exec -T api_demo php artisan migrate --force
$COMPOSE exec -T api_demo php artisan tenants:migrate --force --tenants=demoshop

# Optimize
$COMPOSE exec -T api_demo php artisan optimize:clear
$COMPOSE exec -T api_demo php artisan config:cache
$COMPOSE exec -T api_demo php artisan route:cache
$COMPOSE exec -T api_demo php artisan view:cache

log "✅ Demo API deployment complete!"