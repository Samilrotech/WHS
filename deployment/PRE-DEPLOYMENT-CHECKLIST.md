# WHS5 Pre-Deployment Checklist

## ✅ Pre-Deployment Requirements

### 1. Hostinger Configuration
- [ ] **Subdomain Created**: `whs.rotechrural.site` pointing to `/var/www/whs`
- [ ] **SSH Key Added**: Public key added to Hostinger authorized_keys
- [ ] **SSH Connection Tested**: ✅ Connection successful (147.79.66.172)
- [ ] **Directory Structure**: Create `/var/www/whs` directory on server

### 2. Database Setup
- [ ] **Database Created**: Create `rotech_whs` database on server
- [ ] **Database User**: Create `rotech_user` with password
- [ ] **Permissions Granted**: Grant all privileges to `rotech_user` on `rotech_whs`
- [ ] **Database Credentials**: Have DB_PASSWORD ready for .env configuration

### 3. Server Requirements
- [ ] **PHP Version**: PHP 8.2 or 8.1 installed
- [ ] **Composer**: Composer installed globally
- [ ] **Node.js/NPM**: Node.js 18+ and NPM installed
- [ ] **Git**: Git installed
- [ ] **Nginx/Apache**: Web server configured
- [ ] **PHP Extensions**: Required extensions installed
  - PDO
  - MySQL
  - mbstring
  - tokenizer
  - XML
  - cURL
  - GD
  - fileinfo
  - OpenSSL

### 4. GitHub Configuration
- [ ] **Repository**: ✅ Code pushed to https://github.com/Samilrotech/rotech-whs.git
- [ ] **SSH Key**: Server has access to pull from GitHub (use HTTPS with token)

### 5. Local Configuration
- [ ] **SSH Key**: ✅ SSH key available at `/d/WHS5/whs5_deployment_key`
- [ ] **Git Clean**: All changes committed and pushed
- [ ] **Branch**: On `master` or `main` branch

## 🚀 Deployment Commands

### First-Time Deployment (Full Setup)
```bash
# SSH into server manually first time to set up directories
ssh -i /d/WHS5/whs5_deployment_key root@147.79.66.172

# On server:
mkdir -p /var/www/whs
mkdir -p /var/backups/rotech-whs
mkdir -p /var/log/rotech-whs
chown -R www-data:www-data /var/www/whs

# Create database
mysql -u root -p
CREATE DATABASE rotech_whs;
CREATE USER 'rotech_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON rotech_whs.* TO 'rotech_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Exit SSH and return to local machine
exit

# Run deployment from local machine
cd /d/WHS5
bash deployment/deploy-to-hostinger.sh
```

### Standard Deployment (After First Setup)
```bash
cd /d/WHS5
bash deployment/deploy-to-hostinger.sh
```

### Test Deployment (Dry Run)
```bash
bash deployment/deploy-to-hostinger.sh --dry-run
```

### Emergency Deployment (Skip Backup)
```bash
bash deployment/deploy-to-hostinger.sh --skip-backup --force
```

### Rollback to Previous Version
```bash
bash deployment/deploy-to-hostinger.sh --rollback
```

## 📝 Post-Deployment Tasks

### 1. Configure Environment Variables
SSH into server and update `.env`:
```bash
ssh -i /d/WHS5/whs5_deployment_key root@147.79.66.172
cd /var/www/whs
nano .env

# Update these values:
APP_NAME="Rotech WHS5"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://whs.rotechrural.site

DB_DATABASE=rotech_whs
DB_USERNAME=rotech_user
DB_PASSWORD=YOUR_SECURE_PASSWORD

# Save and exit
```

### 2. Run Initial Setup
```bash
cd /var/www/whs
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
```

### 3. Configure Nginx
```bash
nano /etc/nginx/sites-available/whs.rotechrural.site

# Add this configuration:
server {
    listen 80;
    server_name whs.rotechrural.site;
    root /var/www/whs/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Enable site
ln -s /etc/nginx/sites-available/whs.rotechrural.site /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### 4. Install SSL Certificate
```bash
# Install Certbot if not already installed
apt-get install certbot python3-certbot-nginx

# Get SSL certificate
certbot --nginx -d whs.rotechrural.site
```

### 5. Verify Deployment
- [ ] Visit http://whs.rotechrural.site - Site loads
- [ ] Visit https://whs.rotechrural.site - SSL works
- [ ] Login page accessible
- [ ] Dashboard loads correctly
- [ ] All 5 modules accessible
- [ ] Database connectivity working
- [ ] Check Laravel logs: `tail -f /var/www/whs/storage/logs/laravel.log`

## 🔧 Troubleshooting

### Permission Issues
```bash
cd /var/www/whs
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
```

### Database Connection Errors
```bash
cd /var/www/whs
php artisan config:clear
php artisan cache:clear
# Check .env database credentials
```

### 500 Internal Server Error
```bash
# Check Laravel logs
tail -f /var/www/whs/storage/logs/laravel.log

# Check Nginx error log
tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

### Composer Install Fails
```bash
cd /var/www/whs
composer install --no-dev --optimize-autoloader --no-interaction
```

### Assets Not Loading
```bash
cd /var/www/whs
npm install
npm run build
php artisan storage:link
```

## 📊 Monitoring

### Application Logs
```bash
tail -f /var/www/whs/storage/logs/laravel.log
```

### Deployment Logs
```bash
tail -f /var/log/rotech-whs/deployment_*.log
```

### Web Server Logs
```bash
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### System Resources
```bash
htop
df -h
free -h
```

## 🔐 Security Checklist

- [ ] `.env` file permissions set to 600
- [ ] Firewall configured (allow only 22, 80, 443)
- [ ] fail2ban installed and configured
- [ ] Regular backups scheduled
- [ ] SSL certificate auto-renewal configured
- [ ] Database backups automated
- [ ] Application logs rotated

## 📞 Support

For issues during deployment:
1. Check deployment logs: `/var/log/rotech-whs/`
2. Check Laravel logs: `/var/www/whs/storage/logs/`
3. Check Nginx logs: `/var/log/nginx/`
4. Run health checks: `cd /var/www/whs && php artisan route:list`

---

**Deployment Script**: `/d/WHS5/deployment/deploy-to-hostinger.sh`
**SSH Key**: `/d/WHS5/whs5_deployment_key`
**GitHub Repo**: https://github.com/Samilrotech/rotech-whs
