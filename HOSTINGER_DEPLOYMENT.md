# Hostinger VPS Deployment Guide - WHS5

## Deployment Target
**Subdomain**: whs.rotechrural.com.au
**VPS**: Hostinger (srv082055.hstgr.cloud)
**Repository**: https://github.com/Samilrotech/WHS.git

---

## Prerequisites Checklist

### 1. SSH Access
- ✅ SSH key generated and added to Hostinger panel
- ✅ SSH access confirmed: `ssh root@srv082055.hstgr.cloud`

### 2. Server Requirements
- PHP 8.2+ with extensions: mbstring, xml, bcmath, pdo_mysql, curl, gd, zip
- Composer (PHP dependency manager)
- Node.js 18+ and NPM
- MySQL 8.0+
- Web server (Apache/Nginx)
- Git

### 3. GitHub Access
- Repository is public OR SSH key added to GitHub for private repo access

---

## Step-by-Step Deployment

### Step 1: Connect to VPS

```bash
# From your local machine (Windows)
ssh root@srv082055.hstgr.cloud

# Or if using custom SSH port
ssh -p YOUR_PORT root@srv082055.hstgr.cloud
```

### Step 2: Check Server Environment

```bash
# Check PHP version (must be 8.2+)
php -v

# Check PHP extensions
php -m | grep -E 'mbstring|xml|bcmath|pdo_mysql|curl|gd|zip'

# Check Composer
composer --version

# Check Node.js and NPM
node --version
npm --version

# Check MySQL
mysql --version
```

**If any are missing**, install them:

```bash
# Update package list
apt update

# Install PHP 8.2 and extensions (Ubuntu/Debian)
apt install -y php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-xml \
  php8.2-curl php8.2-mbstring php8.2-bcmath php8.2-gd php8.2-zip

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Node.js 18 LTS
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Install MySQL (if not installed)
apt install -y mysql-server
```

### Step 3: Create MySQL Database

```bash
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE whs5_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'whs5_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON whs5_production.* TO 'whs5_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Save these credentials:**
- Database: `whs5_production`
- Username: `whs5_user`
- Password: `YOUR_SECURE_PASSWORD`

### Step 4: Clone Repository

```bash
# Navigate to web root (adjust path based on Hostinger setup)
cd /var/www

# Clone repository
git clone https://github.com/Samilrotech/WHS.git whs

# Navigate to project
cd whs

# Verify files
ls -la
```

### Step 5: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

**Update these values in .env:**

```env
APP_NAME="WHS5 - Workplace Health & Safety"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Australia/Sydney
APP_URL=https://whs.rotechrural.com.au

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whs5_production
DB_USERNAME=whs5_user
DB_PASSWORD=YOUR_SECURE_PASSWORD

BROADCAST_CONNECTION=log
CACHE_STORE=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Email configuration (update with your SMTP settings)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@rotechrural.com.au"
MAIL_FROM_NAME="${APP_NAME}"
```

**Save and exit** (Ctrl+X, Y, Enter)

### Step 6: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate

# Install Node.js dependencies
npm install

# Build production assets
npm run build
```

### Step 7: Set Permissions

```bash
# Set ownership (assuming www-data is web server user)
chown -R www-data:www-data /var/www/whs

# Set directory permissions
find /var/www/whs -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/whs -type f -exec chmod 644 {} \;

# Storage and cache writable
chmod -R 775 /var/www/whs/storage
chmod -R 775 /var/www/whs/bootstrap/cache

# Ensure www-data owns storage
chown -R www-data:www-data /var/www/whs/storage
chown -R www-data:www-data /var/www/whs/bootstrap/cache
```

### Step 8: Run Migrations and Seeders

```bash
# Run migrations
php artisan migrate --force

# Run seeders (creates admin user and initial data)
php artisan db:seed --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Default Admin Credentials** (from InitialDataSeeder):
- Email: `admin@whs4.com.au`
- Password: Check `database/seeders/InitialDataSeeder.php` for default password

### Step 9: Configure Web Server

#### Option A: Apache with Virtual Host

Create virtual host file:

```bash
nano /etc/apache2/sites-available/whs.rotechrural.com.au.conf
```

**Add this configuration:**

```apache
<VirtualHost *:80>
    ServerName whs.rotechrural.com.au
    ServerAlias www.whs.rotechrural.com.au

    DocumentRoot /var/www/whs/public

    <Directory /var/www/whs/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/whs-error.log
    CustomLog ${APACHE_LOG_DIR}/whs-access.log combined

    # PHP Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
```

**Enable site and modules:**

```bash
# Enable site
a2ensite whs.rotechrural.com.au.conf

# Enable required modules
a2enmod rewrite
a2enmod proxy_fcgi
a2enmod setenvif

# Restart Apache
systemctl restart apache2
```

#### Option B: Nginx

Create Nginx configuration:

```bash
nano /etc/nginx/sites-available/whs.rotechrural.com.au
```

**Add this configuration:**

```nginx
server {
    listen 80;
    listen [::]:80;

    server_name whs.rotechrural.com.au www.whs.rotechrural.com.au;
    root /var/www/whs/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable site:**

```bash
# Create symlink
ln -s /etc/nginx/sites-available/whs.rotechrural.com.au /etc/nginx/sites-enabled/

# Test configuration
nginx -t

# Restart Nginx
systemctl restart nginx
```

### Step 10: Configure SSL Certificate (HTTPS)

```bash
# Install Certbot
apt install -y certbot python3-certbot-apache
# OR for Nginx
apt install -y certbot python3-certbot-nginx

# Obtain certificate (Apache)
certbot --apache -d whs.rotechrural.com.au -d www.whs.rotechrural.com.au

# OR for Nginx
certbot --nginx -d whs.rotechrural.com.au -d www.whs.rotechrural.com.au

# Test auto-renewal
certbot renew --dry-run
```

### Step 11: Final Verification

```bash
# Check application status
php artisan about

# Test database connection
php artisan tinker
# In tinker: \App\Models\User::count();
# Should return number of users

# Clear all caches
php artisan optimize:clear

# Recache for production
php artisan optimize
```

---

## Post-Deployment Tasks

### 1. DNS Configuration

Ensure your domain DNS has an A record pointing to your Hostinger VPS IP:

```
A    whs.rotechrural.com.au    →    YOUR_VPS_IP
A    www.whs.rotechrural.com.au →   YOUR_VPS_IP
```

### 2. Test Application

Visit: https://whs.rotechrural.com.au

**Test these pages:**
- Login page
- Dashboard after login
- Team Management
- Vehicle Management
- Inspections
- All module functionalities

### 3. Change Admin Password

```bash
# Via tinker
php artisan tinker

# In tinker:
$admin = \App\Models\User::where('email', 'admin@whs4.com.au')->first();
$admin->password = 'YOUR_NEW_SECURE_PASSWORD';
$admin->save();
exit;
```

### 4. Configure Scheduled Tasks (Cron)

```bash
# Edit crontab
crontab -e

# Add this line
* * * * * cd /var/www/whs && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Configure Queue Worker (Optional)

If using queues:

```bash
# Install Supervisor
apt install -y supervisor

# Create worker configuration
nano /etc/supervisor/conf.d/whs-worker.conf
```

**Add:**

```ini
[program:whs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/whs/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/whs/storage/logs/worker.log
stopwaitsecs=3600
```

**Start supervisor:**

```bash
supervisorctl reread
supervisorctl update
supervisorctl start whs-worker:*
```

---

## Maintenance Commands

### Update Application

```bash
# SSH into server
ssh root@srv082055.hstgr.cloud

# Navigate to project
cd /var/www/whs

# Enable maintenance mode
php artisan down

# Pull latest changes
git pull origin master

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and recache
php artisan optimize:clear
php artisan optimize

# Disable maintenance mode
php artisan up
```

### View Logs

```bash
# Application logs
tail -f /var/www/whs/storage/logs/laravel.log

# Web server logs (Apache)
tail -f /var/log/apache2/whs-error.log

# Web server logs (Nginx)
tail -f /var/log/nginx/error.log
```

### Database Backup

```bash
# Create backup
mysqldump -u whs5_user -p whs5_production > whs5_backup_$(date +%Y%m%d).sql

# Restore backup
mysql -u whs5_user -p whs5_production < whs5_backup_20250129.sql
```

---

## Troubleshooting

### Issue: 500 Internal Server Error

**Check:**
1. Storage permissions: `chmod -R 775 storage bootstrap/cache`
2. Application logs: `tail -f storage/logs/laravel.log`
3. Web server logs
4. `.env` file configuration

### Issue: Database Connection Error

**Check:**
1. Database credentials in `.env`
2. MySQL service: `systemctl status mysql`
3. Database exists: `mysql -u whs5_user -p -e "SHOW DATABASES;"`

### Issue: Assets Not Loading

**Check:**
1. `npm run build` completed successfully
2. `public/build` directory exists
3. Web server has access to public directory

### Issue: Routes Not Working (404)

**Check:**
1. Apache: mod_rewrite enabled
2. Nginx: try_files directive correct
3. `.htaccess` file in public directory

---

## Security Checklist

- ✅ `APP_DEBUG=false` in production
- ✅ Strong database password
- ✅ SSL certificate installed (HTTPS)
- ✅ Firewall configured (UFW or iptables)
- ✅ SSH key authentication (disable password auth)
- ✅ Regular backups configured
- ✅ File permissions correct (755 dirs, 644 files, 775 storage)
- ✅ `.env` file not publicly accessible
- ✅ Admin password changed from default

---

## Support

**Application Repository**: https://github.com/Samilrotech/WHS
**Latest Commit**: c0492d4
**Laravel Version**: 12
**PHP Version**: 8.2+

For deployment issues, check Laravel documentation: https://laravel.com/docs/deployment
