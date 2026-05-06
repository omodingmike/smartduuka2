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
  log "📥 Cloning dev branch only..."
  # Use --single-branch to fetch only the dev branch
  git clone --branch "$BRANCH" --single-branch "$REPO_URL" "$BACKEND_DIR"
else
  log "⬇ Pulling latest dev changes..."
  sudo chown -R "$(whoami):$(whoami)" "$BACKEND_DIR"

  # 1. Fetch specifically from the dev branch on the remote
  git -C "$BACKEND_DIR" fetch origin "$BRANCH"

  # 2. Switch to the dev branch (create if missing, reset to tracking branch)
  git -C "$BACKEND_DIR" checkout -B "$BRANCH"

  # 3. Perform the hard reset to the remote-tracking state[cite: 5]
  git -C "$BACKEND_DIR" reset --hard origin/"$BRANCH"
fi

cd "$BACKEND_DIR"

# Fix permissions[cite: 5]
for DIR in storage bootstrap/cache public public/media public/static; do
  sudo mkdir -p "$DIR"
  sudo chown -R 33:33 "$DIR"
  sudo chmod -R 775 "$DIR"
done

# Install dependencies[cite: 5]
$COMPOSE run --rm api_demo bash -c "composer install --no-dev --no-interaction"

# Build containers[cite: 5]
log "🐳 Building demo containers..."
$COMPOSE up -d --build --force-recreate api_demo

# Wait for api_demo[cite: 5]
log "⏳ Waiting for api_demo to be healthy..."
RETRY=0
until $COMPOSE exec -T api_demo php artisan --version >/dev/null 2>&1; do
  RETRY=$((RETRY+1))
  [ "$RETRY" -ge 20 ] && fail "api_demo did not become ready"
  sleep 3
done

# Run demo tenant migrations only[cite: 5]
log "🗄 Running demo tenant migrations..."
$COMPOSE exec -T api_demo php artisan migrate --force
$COMPOSE exec -T api_demo php artisan tenants:migrate --force --tenants=demoshop

# Optimize[cite: 5]
$COMPOSE exec -T api_demo php artisan optimize:clear
$COMPOSE exec -T api_demo php artisan config:cache
$COMPOSE exec -T api_demo php artisan route:cache
$COMPOSE exec -T api_demo php artisan view:cache

log "✅ Demo API deployment complete!"