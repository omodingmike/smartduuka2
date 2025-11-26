#!/bin/bash
set -e

# ----------------------------
# CONFIG
# ----------------------------
PROJECT_DIR=~/smartduuka
FRONTEND_DIR="$PROJECT_DIR/frontend"
FRONTEND_REPO_URL="git@github-frontend:omodingmike/smartduuka-nextjs.git"
BACKEND_REPO_URL="git@github-backend:omodingmike/smartduuka2.git"
BACKEND_DIR="$PROJECT_DIR/backend"

DOMAIN_FRONTEND="piwie.smartduuka.com"
DOMAIN_BACKEND="api.smartduuka.com"

SWAP_SIZE="1G"

echo "🚀 Starting deployment..."

# ----------------------------
# UPDATE SYSTEM
# ----------------------------
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get install -y curl git unzip software-properties-common bash wget gnupg lsb-release

# ----------------------------
# Add Swap Space (if missing)
# ----------------------------
echo "🛠 Checking swap space..."
if swapon --show | grep -q '/swapfile'; then
  echo "✅ Swapfile already exists. Skipping..."
else
  echo "➕ Creating swap space..."
  sudo fallocate -l $SWAP_SIZE /swapfile
  sudo chmod 600 /swapfile
  sudo mkswap /swapfile
  sudo swapon /swapfile
  echo "/swapfile none swap sw 0 0" | sudo tee -a /etc/fstab
  echo "✅ Swapfile created and enabled."
fi

# Make swap permanent
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# ----------------------------
# INSTALL DOCKER
# ----------------------------

sudo apt install apt-transport-https ca-certificates curl software-properties-common -y
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" -y
sudo apt update
sudo apt install docker-ce -y

# ----------------------------
# INSTALL DOCKER COMPOSE
# ----------------------------
sudo rm -f /usr/local/bin/docker-compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose


# Wait for the file to be fully downloaded before proceeding
if [ ! -f /usr/local/bin/docker-compose ]; then
  echo "Docker Compose download failed. Exiting."
  exit 1
fi

sudo chmod +x /usr/local/bin/docker-compose
# Ensure Docker Compose is executable and in path
sudo ln -sf /usr/local/bin/docker-compose /usr/bin/docker-compose

# Ensure Docker starts on boot and start Docker service
sudo systemctl enable docker
sudo systemctl start docker

# Verify Docker Compose installation
docker-compose --version
if [ $? -ne 0 ]; then
  echo "Docker Compose installation failed. Exiting."
  exit 1
fi

# ----------------------------
# CREATE PROJECT DIRECTORIES
# ----------------------------
mkdir -p "$PROJECT_DIR"
cd "$PROJECT_DIR"

# ----------------------------
# CLONE REPOSITORIES
# ----------------------------
if [ ! -d "$FRONTEND_DIR" ]; then
  git clone "$FRONTEND_REPO_URL" frontend
else
  cd "$FRONTEND_DIR" && git pull
fi

if [ ! -d "$BACKEND_DIR" ]; then
  git clone "$BACKEND_REPO_URL" backend
else
  cd "$BACKEND_DIR" && git pull
fi

# ----------------------------
# INSTALL & CONFIGURE NGINX
# ----------------------------
echo "🌐 Installing Nginx..."
sudo apt install nginx -y

# ----------------------------
# REMOVE UNUSED DOCKER IMAGES
# ----------------------------
echo "🧹 Removing dangling Docker images..."
sudo docker-compose up -d --remove-orphans
docker image prune -f

# ----------------------------
# BUILD AND START CONTAINERS
# ----------------------------
cd "$PROJECT_DIR"
echo "🐳 Building and starting containers..."
sudo docker compose  up -d --build

# Check if Docker Compose started correctly
if ! sudo docker compose ps | grep "Up"; then
  echo "Docker containers failed to start. Check logs with 'docker-compose logs'."
  exit 1
fi

# ----------------------------
# WAIT FOR POSTGRES TO BE READY
# ----------------------------
echo "⏳ Waiting for Postgres to be ready..."
"$WAIT_SCRIPT" db:5432 --timeout=60 --strict -- echo "✅ Postgres is ready"

# ----------------------------
# INSTALL LARAVEL PACKAGES
# ----------------------------
echo "📦 Installing PHP dependencies with Composer..."
docker-compose exec -T api composer install --no-dev --optimize-autoloader

# ----------------------------
# RUN LARAVEL MIGRATIONS & CLEAR CACHE
# ----------------------------
echo "🗄️ Running migrations..."
docker-compose exec -T api php artisan migrate --force

echo "🧹 Clearing caches..."
docker-compose exec -T api php artisan config:clear
docker-compose exec -T api php artisan cache:clear
docker-compose exec -T api php artisan route:clear

# ----------------------------
# SETUP LARAVEL SCHEDULER IN CRON
# ----------------------------
# Define the full path to your project directory.
# Example: /var/www/erudite-app
echo "⏰ Setting up Laravel scheduler..."
( crontab -l 2>/dev/null; echo "* * * * * cd $PROJECT_DIR && docker-compose exec -T api php artisan schedule:run >> /dev/null 2>&1" ) | crontab -
echo "✅ Cron job added successfully."

# Check if the cron job already exists. If not, add it.
if ! (crontab -l 2>/dev/null | grep -F "$CRON_JOB"); then
  (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
  echo "✅ Cron job added successfully."
else
  echo "✅ Cron job already exists."
fi


# ----------------------------
# DONE
# ----------------------------
echo "✅ Deployment complete!"
echo "Frontend: https://$DOMAIN_FRONTEND"
echo "Backend: https://$DOMAIN_BACKEND"
echo "PostgreSQL credentials are stored in $BACKEND_DIR/.env"
