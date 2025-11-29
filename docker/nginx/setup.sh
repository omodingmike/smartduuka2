# ----------------------------
# INSTALL & CONFIGURE NGINX
# ----------------------------

DOMAIN_FRONTEND="piwie.eruditesuganda.com"
DOMAIN_BACKEND="piwieapi.eruditesuganda.com"
APP_NAME="myapp"

echo "🌐 Installing Nginx..."
sudo apt install nginx -y

# Remove old Nginx config (if it exists)
sudo rm -f /etc/nginx/sites-available/$APP_NAME
sudo rm -f /etc/nginx/sites-enabled/$APP_NAME

# Stop Nginx temporarily to allow Certbot to run in standalone mode
sudo systemctl stop nginx

# Create Nginx config with Cloudflare SSL
sudo cat > /etc/nginx/sites-available/$APP_NAME <<EOL
limit_req_zone $binary_remote_addr zone=mylimit:10m rate=10r/s;

# ----------------------
# HTTP redirect to HTTPS
# ----------------------
server {
    listen 80;
    listen [::]:80;
    server_name piwie.eruditesuganda.com piwieapi.eruditesuganda.com;
    return 301 https://$host$request_uri;
}

# ----------------------
# Frontend (Next.js)
# ----------------------
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name piwie.eruditesuganda.com;

    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/private_key.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    limit_req zone=mylimit burst=20 nodelay;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_buffering off;
        proxy_set_header X-Accel-Buffering no;
    }
}

# ----------------------
# Backend (Laravel API)
# ----------------------
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name piwieapi.eruditesuganda.com;

    root /var/www/public; # Laravel public directory inside Docker
    index index.php index.html;

    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/private_key.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    limit_req zone=mylimit burst=20 nodelay;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass api:9000; # Docker PHP-FPM service
        fastcgi_index index.php;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

EOL


# Create symbolic link if it doesn't already exist
sudo ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/$APP_NAME

# Test Nginx configuration
sudo nginx -t
if [ $? -ne 0 ]; then
  echo "Nginx configuration test failed. Exiting."
  exit 1
fi

# Restart Nginx to apply the new configuration
sudo systemctl restart nginx