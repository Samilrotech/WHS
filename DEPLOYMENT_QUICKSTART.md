# WHS5 Hostinger Deployment - Quick Start

## 🚀 Quick Deployment Steps

### 1. SSH into Your Hostinger VPS

From your Windows machine:

```powershell
ssh root@srv082055.hstgr.cloud
```

Or use PuTTY with:
- Host: `srv082055.hstgr.cloud`
- Port: `22` (or custom port)
- Username: `root`
- Authentication: SSH key (you've already set this up in Hostinger panel)

### 2. Run the Automated Deployment Script

Once connected to the server:

```bash
# Download the deployment script
curl -o deploy.sh https://raw.githubusercontent.com/Samilrotech/WHS/master/deploy-to-hostinger.sh

# Make it executable
chmod +x deploy.sh

# Run it
sudo bash deploy.sh
```

**The script will:**
- ✅ Check system requirements (PHP, MySQL, Node.js)
- ✅ Create database and user
- ✅ Clone GitHub repository
- ✅ Install all dependencies
- ✅ Configure environment
- ✅ Run migrations and seeders
- ✅ Set up web server (Apache or Nginx)
- ✅ Optionally install SSL certificate

### 3. Configure DNS

In your domain registrar (where you manage rotechrural.com.au), add these DNS records:

```
Type    Name    Value                   TTL
A       whs     YOUR_VPS_IP_ADDRESS     3600
A       www.whs YOUR_VPS_IP_ADDRESS     3600
```

**To find your VPS IP:**
```bash
curl ifconfig.me
```

### 4. Access Your Application

Wait 10-15 minutes for DNS propagation, then visit:

**URL**: https://whs.rotechrural.com.au

**Default Login:**
- Email: `admin@whs4.com.au`
- Password: Check the seeder file or the terminal output

### 5. Change Admin Password

```bash
ssh root@srv082055.hstgr.cloud
cd /var/www/whs
php artisan tinker
```

In tinker:
```php
$admin = \App\Models\User::where('email', 'admin@whs4.com.au')->first();
$admin->password = 'YOUR_NEW_SECURE_PASSWORD';
$admin->save();
exit;
```

---

## 📋 Manual Deployment (If Script Fails)

If the automated script doesn't work, follow the step-by-step guide in `HOSTINGER_DEPLOYMENT.md`

---

## 🔧 Post-Deployment Configuration

### Set Up Cron Jobs

```bash
crontab -e
```

Add this line:
```
* * * * * cd /var/www/whs && php artisan schedule:run >> /dev/null 2>&1
```

### Configure Email (Optional)

Edit `.env` file:
```bash
nano /var/www/whs/.env
```

Update email settings:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
```

Then:
```bash
php artisan config:cache
```

---

## 🛠️ Troubleshooting

### Issue: Can't connect via SSH

**Solution:** Check SSH key in Hostinger panel:
1. Go to Hostinger → VPS → Settings → SSH Keys
2. Ensure your public key is added
3. Try connecting with verbose mode: `ssh -v root@srv082055.hstgr.cloud`

### Issue: Website shows 404

**Check:**
```bash
# Is web server running?
systemctl status apache2
# OR
systemctl status nginx

# Check virtual host configuration
ls -la /etc/apache2/sites-enabled/
# OR
ls -la /etc/nginx/sites-enabled/
```

### Issue: 500 Internal Server Error

**Check logs:**
```bash
# Application logs
tail -f /var/www/whs/storage/logs/laravel.log

# Web server logs
tail -f /var/log/apache2/error.log
# OR
tail -f /var/log/nginx/error.log
```

**Fix permissions:**
```bash
cd /var/www/whs
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue: Database connection error

**Test database:**
```bash
mysql -u whs5_user -p whs5_production
```

If this fails, check database credentials in `.env`

### Issue: CSS/JS not loading

**Rebuild assets:**
```bash
cd /var/www/whs
npm run build
php artisan view:cache
```

---

## 📞 Need Help?

**Full Documentation**: See `HOSTINGER_DEPLOYMENT.md` for complete step-by-step guide

**Repository**: https://github.com/Samilrotech/WHS

**Server Details**:
- VPS: srv082055.hstgr.cloud
- Subdomain: whs.rotechrural.com.au
- Project Path: /var/www/whs
- Database: whs5_production

---

## ✅ Deployment Checklist

Before going live:

- [ ] DNS records configured
- [ ] SSL certificate installed
- [ ] Admin password changed
- [ ] Email configuration tested
- [ ] Cron jobs configured
- [ ] Backups configured
- [ ] All modules tested (Teams, Vehicles, Inspections, etc.)
- [ ] Performance tested
- [ ] Security scan completed

---

## 🔄 Future Updates

To update the application after changes:

```bash
ssh root@srv082055.hstgr.cloud
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

# Clear caches
php artisan optimize:clear
php artisan optimize

# Disable maintenance mode
php artisan up
```

---

## 🎉 You're Ready!

Your WHS5 application is now deployed to production at:
**https://whs.rotechrural.com.au**
