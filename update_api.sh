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
log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"; }
fail() {
  echo "❌ $*" >&2
  exit 1
}
require_cmd() { command -v "$1" >/dev/null 2>&1 || fail "Required command not found: $1"; }

# --------------------------------------------------
# PRE-FLIGHT CHECKS
# --------------------------------------------------
require_cmd git
require_cmd docker
$COMPOSE version >/dev/null 2>&1 || fail "Docker Compose plugin not found"

[ -d "$BACKEND_DIR" ] || fail "Backend directory not found: $BACKEND_DIR"

log "🚀 Starting deployment for ${APP_NAME}"
cd "$BACKEND_DIR"

log "🔐 Reclaiming ownership for the deploy user..."
# This ensures 'deploy' can modify/delete files during git pull
sudo chown -R $(whoami):$(whoami) "$BACKEND_DIR"

# --------------------------------------------------
# PULL LATEST CODE
# --------------------------------------------------
log "📥 Fetching latest changes from Git..."
sudo chown -R $(whoami):$(whoami) .git

git fetch origin "$BRANCH"
git reset --hard origin/"$BRANCH"

# ----------------------------
# FIX LARAVEL PERMISSIONS (HOST)
# ----------------------------
log "🔐 Fixing Laravel permissions for Docker..."
LARAVEL_PATH="$BACKEND_DIR"

WRITABLE_DIRS=(
  "$LARAVEL_PATH/storage"
  "$LARAVEL_PATH/bootstrap/cache"
  "$LARAVEL_PATH/public"
  "$LARAVEL_PATH/public/media"
  "$LARAVEL_PATH/public/static"
  "$LARAVEL_PATH/.cache"
)

for DIR in "${WRITABLE_DIRS[@]}"; do
  if [ ! -d "$DIR" ]; then
    echo "  ⚠️  Creating $DIR"
    mkdir -p "$DIR"
  fi
  # Assign to www-data (33) so the container can write
  sudo chown -R 33:33 "$DIR"
  sudo chmod -R 775 "$DIR"
done

# --------------------------------------------------
# 1. BOOTSTRAP VENDOR FOLDER
# --------------------------------------------------
log "📦 Bootstrapping vendor folder and fixing ownership..."
# We use --user root here because only root can fix the "dubious ownership"
# and create the vendor folder if it was previously owned by 'deploy'.
log "📦 Bootstrapping vendor folder and copying SQL seeds..."

log "📦 Performing clean vendor installation..."
$COMPOSE run --rm api bash -c "
    composer install --no-dev --no-interaction
"

# --------------------------------------------------
# 2. BUILD & RECREATE CONTAINERS
# --------------------------------------------------
log "🔨 Building and recreating api & nginx containers..."
$COMPOSE up -d --build --force-recreate --force-recreate api nginx

# --------------------------------------------------
# 3. NGINX CONFIG TEST
# --------------------------------------------------
log "🧪 Validating Nginx configuration..."
$COMPOSE exec -T nginx nginx -t || fail "Nginx configuration test failed"

# --------------------------------------------------
# 4. WAIT FOR API TO BE READY
# --------------------------------------------------
log "⏳ Waiting for API container to become healthy..."
MAX_RETRIES=20
RETRY=0
until $COMPOSE exec -T api php artisan --version >/dev/null 2>&1; do
  RETRY=$((RETRY + 1))
  [ "$RETRY" -ge "$MAX_RETRIES" ] && fail "API container did not become ready"
  sleep 3
done

# --------------------------------------------------
# 5. DATABASE & POST-DEPLOY TASKS
# --------------------------------------------------
log "🗄 Running database migrations..."
$COMPOSE exec -T api php artisan migrate --force
$COMPOSE exec -T api php artisan tenants:migrate
$COMPOSE exec -T api php artisan db:seed --force
$COMPOSE exec -T api php artisan tenants:seed

log "🔗 Ensuring storage symlink..."
$COMPOSE exec -T api php artisan storage:link --relative || true

log "🧹 Optimizing application..."
$COMPOSE exec -T api php artisan optimize:clear
$COMPOSE exec -T api php artisan optimize
$COMPOSE exec -T api php artisan config:cache
$COMPOSE exec -T api php artisan event:cache
$COMPOSE exec -T api php artisan route:cache
$COMPOSE exec -T api php artisan view:cache

# --------------------------------------------------
# 6. RELOAD PHP-FPM (THE FIX)
# --------------------------------------------------
log "🔄 Reloading PHP-FPM to clear OPcache..."
# This sends the USR2 signal to the master process (PID 1) to reload workers
# This ensures they pick up the 'config.php' changes made in Step 5
$COMPOSE exec -T api kill -USR2 1 || echo "⚠️  Could not reload PHP-FPM automatically, verify manually."

# --------------------------------------------------
# 7. VERIFY CONTAINERS
# --------------------------------------------------
log "🔎 Verifying container status..."
for service in api nginx; do
  if ! $COMPOSE ps --services --filter "status=running" | grep -q "^${service}$"; then
    $COMPOSE logs --tail=20 "$service"
    fail "Service not running: ${service}"
  fi
done

log "🧹 Cleaning up old Docker images..."
docker image prune -f

log "✅ Deployment completed successfully."
