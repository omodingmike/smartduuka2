#!/bin/bash
set -Eeuo pipefail # Better error handling

# ----------------------------
# CONFIG
# ----------------------------
PROJECT_DIR=~/smartduuka
WEB_DIR="$PROJECT_DIR/web"
WEB_REPO_URL="git@github-web:kimdigitary/smartduukanewfront.git"

BACKEND_DIR="$PROJECT_DIR/api"
BACKEND_REPO_URL="git@github-api:omodingmike/smartduuka2.git"

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
# ENSURE DOCKER REPOSITORY IS ADDED
# ----------------------------
# This must run even if Docker is installed, so apt can find buildx-plugin
if [ ! -f /etc/apt/sources.list.d/docker.list ]; then
  log "🔑 Adding official Docker repository..."
  sudo install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor --yes -o /etc/apt/keyrings/docker.gpg
  sudo chmod a+r /etc/apt/keyrings/docker.gpg
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | \
    sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
  sudo apt-get update
fi

# ----------------------------
# INSTALL DOCKER ENGINE
# ----------------------------
if ! command -v docker &> /dev/null; then
  log "🐳 Installing Docker Engine..."
  sudo apt-get install -y docker-ce docker-ce-cli containerd.io
fi

sudo systemctl enable docker --now

# ----------------------------
# ENSURE BUILDX IS INSTALLED
# ----------------------------
if ! docker buildx version &> /dev/null; then
  log "📦 Installing Docker Buildx plugin..."
  sudo apt-get update
  sudo apt-get install -y docker-buildx-plugin
fi

# ----------------------------
# FETCH & INSTALL LATEST DOCKER COMPOSE
# ----------------------------
log "🐳 Fetching the absolute latest Docker Compose V2..."
COMPOSE_VERSION=$(curl -s https://api.github.com/repos/docker/compose/releases/latest | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')
sudo mkdir -p /usr/local/lib/docker/cli-plugins
sudo curl -SL "https://github.com/docker/compose/releases/download/${COMPOSE_VERSION}/docker-compose-linux-$(uname -m)" -o /usr/local/lib/docker/cli-plugins/docker-compose
sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
log "✅ Docker Compose dynamically updated to ${COMPOSE_VERSION}"

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
cd "$BACKEND_DIR"
rm -f public/storage
ln -s ../storage/app/public public/storage

# ----------------------------
# DOCKER BUILD & START
# ----------------------------
log "🔨 Building and starting containers..."
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