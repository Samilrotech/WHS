#!/bin/bash

#############################################################################
# WHS5 Hostinger VPS Deployment Script
#
# This script automates the deployment of WHS5 to Hostinger VPS
# Run this script ON THE SERVER after SSH connection
#
# Usage: bash deploy-to-hostinger.sh
#############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/whs"
REPO_URL="https://github.com/Samilrotech/WHS.git"
DOMAIN="whs.rotechrural.com.au"
DB_NAME="whs5_production"
DB_USER="whs5_user"
WEB_USER="www-data"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}WHS5 Hostinger Deployment Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

# Step 1: Check system requirements
echo -e "${YELLOW}Step 1: Checking system requirements...${NC}"

# Check PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP 8.2+${NC}"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${GREEN}✓ PHP version: $PHP_VERSION${NC}"

# Check Composer
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi
echo -e "${GREEN}✓ Composer installed${NC}"

# Check Node.js
if ! command -v node &> /dev/null; then
    echo -e "${RED}Node.js is not installed. Please install Node.js 18+${NC}"
    exit 1
fi
NODE_VERSION=$(node --version)
echo -e "${GREEN}✓ Node.js version: $NODE_VERSION${NC}"

# Check MySQL
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}MySQL is not installed. Please install MySQL 8.0+${NC}"
    exit 1
fi
echo -e "${GREEN}✓ MySQL installed${NC}"

# Step 2: Database setup
echo -e "${YELLOW}Step 2: Database setup...${NC}"

read -p "Enter MySQL root password: " -s MYSQL_ROOT_PASS
echo ""

read -p "Enter password for database user '$DB_USER': " -s DB_PASS
echo ""

# Create database and user
mysql -u root -p"$MYSQL_ROOT_PASS" <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database created successfully${NC}"
else
    echo -e "${RED}Database creation failed${NC}"
    exit 1
fi

# Step 3: Clone repository
echo -e "${YELLOW}Step 3: Cloning repository...${NC}"

if [ -d "$PROJECT_DIR" ]; then
    echo -e "${YELLOW}Project directory exists. Pulling latest changes...${NC}"
    cd $PROJECT_DIR
    git pull origin master
else
    echo -e "${YELLOW}Cloning repository...${NC}"
    git clone $REPO_URL $PROJECT_DIR
    cd $PROJECT_DIR
fi

echo -e "${GREEN}✓ Repository ready${NC}"

# Step 4: Environment configuration
echo -e "${YELLOW}Step 4: Configuring environment...${NC}"

if [ ! -f ".env" ]; then
    cp .env.example .env

    # Update .env file
    sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
    sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env
    sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

    echo -e "${GREEN}✓ Environment file created${NC}"
else
    echo -e "${YELLOW}Environment file already exists. Skipping...${NC}"
fi

# Step 5: Install dependencies
echo -e "${YELLOW}Step 5: Installing dependencies...${NC}"

# Composer
echo "Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# NPM
echo "Installing Node.js dependencies..."
npm install --production=false

# Build assets
echo "Building production assets..."
npm run build

echo -e "${GREEN}✓ Dependencies installed${NC}"

# Step 6: Set permissions
echo -e "${YELLOW}Step 6: Setting permissions...${NC}"

chown -R $WEB_USER:$WEB_USER $PROJECT_DIR

find $PROJECT_DIR -type d -exec chmod 755 {} \;
find $PROJECT_DIR -type f -exec chmod 644 {} \;

chmod -R 775 $PROJECT_DIR/storage
chmod -R 775 $PROJECT_DIR/bootstrap/cache

chown -R $WEB_USER:$WEB_USER $PROJECT_DIR/storage
chown -R $WEB_USER:$WEB_USER $PROJECT_DIR/bootstrap/cache

echo -e "${GREEN}✓ Permissions set${NC}"

# Step 7: Run migrations
echo -e "${YELLOW}Step 7: Running database migrations...${NC}"

php artisan migrate --force

read -p "Run database seeders? (y/n): " RUN_SEEDERS
if [ "$RUN_SEEDERS" = "y" ]; then
    php artisan db:seed --force
    echo -e "${GREEN}✓ Seeders executed${NC}"
fi

# Step 8: Optimize application
echo -e "${YELLOW}Step 8: Optimizing application...${NC}"

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo -e "${GREEN}✓ Application optimized${NC}"

# Step 9: Web server configuration
echo -e "${YELLOW}Step 9: Web server configuration...${NC}"

# Detect web server
if systemctl is-active --quiet apache2; then
    WEB_SERVER="apache"
    echo "Detected Apache"
elif systemctl is-active --quiet nginx; then
    WEB_SERVER="nginx"
    echo "Detected Nginx"
else
    echo -e "${YELLOW}No web server detected. Please configure manually.${NC}"
    WEB_SERVER="none"
fi

if [ "$WEB_SERVER" = "apache" ]; then
    # Apache configuration
    VHOST_FILE="/etc/apache2/sites-available/$DOMAIN.conf"

    if [ ! -f "$VHOST_FILE" ]; then
        cat > $VHOST_FILE <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN

    DocumentRoot $PROJECT_DIR/public

    <Directory $PROJECT_DIR/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/$DOMAIN-error.log
    CustomLog \${APACHE_LOG_DIR}/$DOMAIN-access.log combined

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
EOF

        a2ensite $DOMAIN.conf
        a2enmod rewrite
        a2enmod proxy_fcgi
        systemctl reload apache2

        echo -e "${GREEN}✓ Apache virtual host configured${NC}"
    else
        echo -e "${YELLOW}Apache virtual host already exists${NC}"
    fi

elif [ "$WEB_SERVER" = "nginx" ]; then
    # Nginx configuration
    NGINX_FILE="/etc/nginx/sites-available/$DOMAIN"

    if [ ! -f "$NGINX_FILE" ]; then
        cat > $NGINX_FILE <<EOF
server {
    listen 80;
    listen [::]:80;

    server_name $DOMAIN www.$DOMAIN;
    root $PROJECT_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

        ln -s $NGINX_FILE /etc/nginx/sites-enabled/
        nginx -t && systemctl reload nginx

        echo -e "${GREEN}✓ Nginx server block configured${NC}"
    else
        echo -e "${YELLOW}Nginx server block already exists${NC}"
    fi
fi

# Step 10: SSL Certificate
echo -e "${YELLOW}Step 10: SSL Certificate...${NC}"

if command -v certbot &> /dev/null; then
    read -p "Install SSL certificate with Let's Encrypt? (y/n): " INSTALL_SSL

    if [ "$INSTALL_SSL" = "y" ]; then
        if [ "$WEB_SERVER" = "apache" ]; then
            certbot --apache -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN || echo -e "${YELLOW}SSL installation failed. Please configure manually.${NC}"
        elif [ "$WEB_SERVER" = "nginx" ]; then
            certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN || echo -e "${YELLOW}SSL installation failed. Please configure manually.${NC}"
        fi
    fi
else
    echo -e "${YELLOW}Certbot not installed. Skipping SSL...${NC}"
fi

# Final message
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Deployment Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Application URL: ${GREEN}https://$DOMAIN${NC}"
echo -e "Database: ${GREEN}$DB_NAME${NC}"
echo -e "Project Directory: ${GREEN}$PROJECT_DIR${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Update DNS records to point $DOMAIN to this server"
echo "2. Change admin password: php artisan tinker"
echo "3. Configure cron jobs: * * * * * cd $PROJECT_DIR && php artisan schedule:run >> /dev/null 2>&1"
echo "4. Test application at https://$DOMAIN"
echo ""
echo -e "${YELLOW}Default Admin Login:${NC}"
echo "Email: admin@whs4.com.au"
echo "Password: Check database/seeders/InitialDataSeeder.php"
echo ""
echo -e "${GREEN}Deployment log saved to: deployment.log${NC}"
