#!/bin/bash
set -Eeuo pipefail # Better error handling

# ----------------------------
# CONFIG
# ----------------------------
PROJECT_DIR=~/smartduuka
WEB_DIR="$PROJECT_DIR/web"
WEB_REPO_URL="git@kimdigitary:kimdigitary/smartduukanewfront.git"

BACKEND_DIR="$PROJECT_DIR/api"
BACKEND_REPO_URL="git@omodingmike:omodingmike/smartduuka2.git"

SWAP_SIZE="1G"
NETWORK_NAME="smartduuka_network"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"; }
fail() { echo "❌ $*" >&2; exit 1; }

log "🚀 Starting deployment..."

# ----------------------------
# PRE-FLIGHT SSH CHECK
# ----------------------------
log "🧪 Verifying GitHub SSH Aliases..."
ssh -T git@kimdigitary 2>&1 | grep -q "successfully authenticated" || log "⚠️ Warning: kimdigitary SSH check failed. Git clone might fail."
ssh -T git@omodingmike 2>&1 | grep -q "successfully authenticated" || log "⚠️ Warning: omodingmike SSH check failed. Git clone might fail."

# ----------------------------
# UPDATE SYSTEM & INSTALL TOOLS
# ----------------------------
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get install -y curl git unzip software-properties-common gnupg lsb-release

# ----------------------------
# Add Swap Space (Idempotent)
# ----------------------------
if [ -f /swapfile ]; then
  log "✅ Swapfile already exists."
else
  log "➕ Creating ${SWAP_SIZE} swap space..."
  sudo fallocate -l $SWAP_SIZE /swapfile
  sudo chmod 600 /swapfile
  sudo mkswap /swapfile
  sudo swapon /swapfile
  echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
fi

# ----------------------------
# INSTALL DOCKER (Modern Method)
# ----------------------------
if ! command -v docker &> /dev/null; then
  log "🐳 Installing Docker..."
  sudo install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
  sudo chmod a+r /etc/apt/keyrings/docker.gpg
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | \
    sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
  sudo apt-get update
  sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
fi

sudo systemctl enable docker --now

# ----------------------------
# SETUP NETWORK
# ----------------------------
if ! sudo docker network ls --format '{{.Name}}' | grep -wq "$NETWORK_NAME"; then
  log "🌐 Creating network '$NETWORK_NAME'..."
  sudo docker network create "$NETWORK_NAME"
fi

# ----------------------------
# CLONE / UPDATE REPOS
# ----------------------------
mkdir -p "$PROJECT_DIR"

clone_or_pull() {
  local url=$1
  local dir=$2
  if [ ! -d "$dir/.git" ]; then
    log "📥 Cloning $url..."
    git clone "$url" "$dir"
  else
    log "🔄 Updating $dir..."
    cd "$dir" && git pull
  fi
}

clone_or_pull "$WEB_REPO_URL" "$WEB_DIR"
clone_or_pull "$BACKEND_REPO_URL" "$BACKEND_DIR"

# ----------------------------
# FIX PERMISSIONS & SYMLINKS
# ----------------------------
log "🔐 Fixing Laravel permissions..."
# Added .cache for Puppeteer/Chrome
WRITABLE_DIRS=(
  "$BACKEND_DIR/storage"
  "$BACKEND_DIR/bootstrap/cache"
  "$BACKEND_DIR/public/media"
  "$BACKEND_DIR/public/static"
  "$BACKEND_DIR/.cache"
)

for DIR in "${WRITABLE_DIRS[@]}"; do
  mkdir -p "$DIR"
  sudo chown -R 33:33 "$DIR"
  sudo chmod -R 775 "$DIR"
done

log "🔗 Ensuring storage symlink..."
# Using --relative is best practice for Docker volumes
cd "$BACKEND_DIR"
# Remove if exists (to avoid nested links) and recreate
rm -f public/storage
ln -s ../storage/app/public public/storage

# ----------------------------
# DOCKER BUILD & START
# ----------------------------
log "🔨 Building and starting containers..."
# Use 'docker compose' (plugin version) instead of 'docker-compose'
sudo docker compose up -d --build --force-recreate --remove-orphans

# ----------------------------
# LARAVEL POST-DEPLOY
# ----------------------------
log "📦 Running Laravel optimizations..."
sudo docker compose exec -T api composer install --no-dev --optimize-autoloader
sudo docker compose exec -T api php artisan migrate --force
sudo docker compose exec -T api php artisan optimize:clear
sudo docker compose exec -T api php artisan optimize

log "🧹 Cleaning up old images..."
sudo docker image prune -f

log "✅ Deployment complete!"