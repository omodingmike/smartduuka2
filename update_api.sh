#!/usr/bin/env bash
set -Eeuo pipefail

# --------------------------------------------------
# CONFIG
# --------------------------------------------------
APP_DIR="$HOME/smartduuka"
APP_NAME="api"
BACKEND_DIR="$APP_DIR/$APP_NAME"
COMPOSE="docker compose"
BRANCH="main"

# --------------------------------------------------
# HELPERS
# --------------------------------------------------
log() {
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"
}

fail() {
  echo "❌ $*" >&2
  exit 1
}

require_cmd() {
  command -v "$1" >/dev/null 2>&1 || fail "Required command not found: $1"
}

# --------------------------------------------------
# PRE-FLIGHT CHECKS
# --------------------------------------------------
require_cmd git
require_cmd docker
require_cmd docker-compose || true

[ -d "$BACKEND_DIR" ] || fail "Backend directory not found: $BACKEND_DIR"

log "🚀 Starting deployment for ${APP_NAME}"

cd "$BACKEND_DIR"

# --------------------------------------------------
# PULL LATEST CODE
# --------------------------------------------------
log "📥 Fetching latest changes from Git..."
git fetch origin "$BRANCH"

LOCAL_HASH=$(git rev-parse HEAD)
REMOTE_HASH=$(git rev-parse "origin/$BRANCH")

if [ "$LOCAL_HASH" != "$REMOTE_HASH" ]; then
  log "🔄 Updating codebase..."
  git pull --ff-only origin "$BRANCH"
else
  log "✔ Code already up to date."
fi

# ----------------------------
# FIX LARAVEL PERMISSIONS (HOST)
# ----------------------------
echo "🔐 Fixing Laravel permissions for Docker..."

LARAVEL_PATH="$BACKEND_DIR"

# Writable directories
WRITABLE_DIRS=(
  "$LARAVEL_PATH/storage"
  "$LARAVEL_PATH/bootstrap/cache"
  "$LARAVEL_PATH/public"
  "$LARAVEL_PATH/public/media"
  "$LARAVEL_PATH/public/static"
)

for DIR in "${WRITABLE_DIRS[@]}"; do
  if [ -d "$DIR" ]; then
    echo "  → Fixing $DIR"
    sudo chown -R 33:33 "$DIR"
    sudo chmod -R 775 "$DIR"
  else
    echo "  ⚠️  $DIR does not exist, skipping"
  fi
done

echo "✅ Laravel permissions fixed"


# --------------------------------------------------
# BUILD & RECREATE CONTAINERS
# --------------------------------------------------
log "🔨 Building and recreating api & nginx containers..."
$COMPOSE up -d --build --force-recreate api nginx

# --------------------------------------------------
# NGINX CONFIG TEST (HARD FAIL)
# --------------------------------------------------
log "🧪 Validating Nginx configuration..."
if ! $COMPOSE exec -T nginx nginx -t; then
  $COMPOSE logs --tail=100 nginx
  fail "Nginx configuration test failed"
fi
log "✔ Nginx configuration is valid."

# --------------------------------------------------
# WAIT FOR API TO BE READY
# --------------------------------------------------
log "⏳ Waiting for API container to become healthy..."

MAX_RETRIES=20
RETRY=0

until $COMPOSE exec -T api php artisan --version >/dev/null 2>&1; do
  RETRY=$((RETRY + 1))
  [ "$RETRY" -ge "$MAX_RETRIES" ] && fail "API container did not become ready"
  sleep 3
done

log "✔ API container is ready."

# --------------------------------------------------
# DATABASE & APP TASKS
# --------------------------------------------------

log "🚚 Running post-deployment Composer hooks..."
$COMPOSE exec -T api composer dump-autoload --optimize
$COMPOSE exec -T api php artisan package:discover --ansi

log "🗄 Running database migrations..."
$COMPOSE exec -T api php artisan migrate --force
#$COMPOSE exec -T api php artisan migrate:fresh --force
#$COMPOSE exec -T api php artisan db:seed --force

log "🔗 Ensuring storage symlink..."
$COMPOSE exec -T api php artisan storage:link || true

log "🧹 Optimizing application..."
$COMPOSE exec -T api php artisan optimize:clear
$COMPOSE exec -T api php artisan optimize

# --------------------------------------------------
# VERIFY CONTAINERS
# --------------------------------------------------
log "🔎 Verifying container status..."

for service in api nginx; do
  if ! $COMPOSE ps --services --filter "status=running" | grep -q "^${service}$"; then
    fail "Service not running: ${service}"
  fi
done

log "✅ Deployment completed successfully."
