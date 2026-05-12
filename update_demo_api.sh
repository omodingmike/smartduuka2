#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="$HOME/smartduuka"
BACKEND_DIR="$APP_DIR/api_demo"
REPO_URL="git@github-api:omodingmike/smartduuka2.git"
BRANCH="dev"
# Explicitly using the demo compose file
COMPOSE="docker compose -f docker-compose.demo.yml"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"; }
fail() { echo "❌ $*" >&2; exit 1; }

# Clone or pull
if [ ! -d "$BACKEND_DIR/.git" ]; then
  log "📥 Cloning dev branch only..."
  # Single branch clone to keep the demo environment lightweight
  git clone --branch "$BRANCH" --single-branch "$REPO_URL" "$BACKEND_DIR"
else
  log "⬇ Pulling latest dev changes..."
  sudo chown -R "$(whoami):$(whoami)" "$BACKEND_DIR"

  # Fetch specifically from the dev branch
  git -C "$BACKEND_DIR" fetch origin "$BRANCH"

  # Force switch to dev branch and reset to match origin/dev[cite: 5]
  git -C "$BACKEND_DIR" checkout -B "$BRANCH"
  git -C "$BACKEND_DIR" reset --hard origin/"$BRANCH"
fi

cd "$BACKEND_DIR"

# Fix permissions for the demo directory[cite: 5]
for DIR in storage bootstrap/cache public public/media public/static; do
  sudo mkdir -p "$DIR"
  sudo chown -R 33:33 "$DIR"
  sudo chmod -R 775 "$DIR"
done

# Install dependencies using the demo compose configuration[cite: 5]
$COMPOSE run --rm api_demo bash -c "composer remove maatwebsite/excel --no-dev --no-interaction"
$COMPOSE run --rm api_demo bash -c "composer update --no-dev --no-interaction"

# Build containers using the demo compose configuration[cite: 5]
log "🐳 Building demo containers..."
$COMPOSE up -d --build --force-recreate api_demo

# Wait for api_demo to be healthy[cite: 5]
log "⏳ Waiting for api_demo to be healthy..."
RETRY=0
until $COMPOSE exec -T api_demo php artisan --version >/dev/null 2>&1; do
  RETRY=$((RETRY+1))
  [ "$RETRY" -ge 20 ] && fail "api_demo did not become ready"
  sleep 3
done

# Run demo tenant migrations[cite: 5]
log "🗄 Running demo tenant migrations..."
$COMPOSE exec -T api_demo php artisan migrate --force
$COMPOSE exec -T api_demo php artisan tenants:migrate --force --tenants=demoshop

# Optimize Laravel for the demo environment
$COMPOSE exec -T api_demo php artisan reset-notification-settings
$COMPOSE exec -T api_demo php artisan optimize:clear
$COMPOSE exec -T api_demo php artisan config:cache
$COMPOSE exec -T api_demo php artisan db:seed --force --class=BillingCycleSeeder
$COMPOSE exec -T api_demo php artisan db:seed --force --class=SubscriptionPlanSeeder
$COMPOSE exec -T api_demo php artisan db:seed --force --class=BusinessOnBoardSeeder
#$COMPOSE exec -T api_demo php artisan tenants:seed --class=BillingCycleSeeder
#$COMPOSE exec -T api_demo php artisan tenants:seed --class=SubscriptionPlanSeeder
$COMPOSE exec -T api_demo php artisan route:cache
$COMPOSE exec -T api_demo php artisan view:cache

log "✅ Demo API deployment complete using docker-compose.demo.yml!"