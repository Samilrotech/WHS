# DEPLOYMENT_QUICK_START.md - Quick Deployment Reference

Quick reference guide for deploying WHS5 to Hostinger VPS.

## ✅ Completed Steps

```yaml
completed:
  github_deployment:
    status: "COMPLETE ✅"
    commit: "e55e634"
    message: "feat: Complete Priority 1 Dense Table UI migration (6/6 modules)"
    files_changed: 61
    insertions: 14300
    repository: "https://github.com/Samilrotech/WHS.git"
    branch: "master"
```

## 🚀 Next Steps: Deploy to Hostinger

### Option 1: Automated Deployment (Recommended)

```bash
# Run the deployment script
bash deploy-to-hostinger.sh
```

**What the script does**:
1. ✅ Checks SSH key exists
2. ✅ Connects to Hostinger VPS
3. ✅ Pulls latest code from GitHub
4. ✅ Installs dependencies
5. ✅ Runs migrations
6. ✅ Clears and optimizes caches
7. ✅ Sets permissions
8. ✅ Restarts web server
9. ✅ Shows deployment summary

### Option 2: Manual Deployment

```bash
# 1. SSH to Hostinger
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au

# 2. Navigate to app directory
cd /home/whs5

# 3. Pull latest changes
git pull origin master

# 4. Install dependencies
composer install --no-dev --optimize-autoloader

# 5. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 6. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Run migrations
php artisan migrate --force

# 8. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 9. Restart web server
systemctl restart apache2  # or nginx
```

## 📋 Pre-Deployment Checklist

```yaml
pre_deployment:
  - "✅ Code pushed to GitHub (commit e55e634)"
  - "✅ Documentation created (DEPLOYMENT.md)"
  - "✅ Deployment script created (deploy-to-hostinger.sh)"
  - "⬜ SSH key configured (~/.ssh/whs5_hostinger)"
  - "⬜ Server access verified"
  - "⬜ Database backup created (recommended)"
```

## 🔑 SSH Key Setup

### If SSH key doesn't exist yet:

```bash
# 1. Create SSH directory if needed
mkdir -p ~/.ssh

# 2. Create SSH key file
cat > ~/.ssh/whs5_hostinger << 'EOF'
[PASTE YOUR PRIVATE KEY HERE]
EOF

# 3. Create public key file
cat > ~/.ssh/whs5_hostinger.pub << 'EOF'
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAII6Vb2Bkf41/t0aEoWcusbMnQHECM0H6tp4DgppTqCrz whs5-hostinger@rotechrural.com.au
EOF

# 4. Set correct permissions
chmod 600 ~/.ssh/whs5_hostinger
chmod 644 ~/.ssh/whs5_hostinger.pub

# 5. Test SSH connection
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au
```

### Optional: Add to SSH config for easier access

```bash
# Add to ~/.ssh/config
cat >> ~/.ssh/config << 'EOF'

Host whs5-hostinger
    HostName whs.rotechrural.com.au
    User root
    IdentityFile ~/.ssh/whs5_hostinger
    IdentitiesOnly yes
EOF

# Then you can connect with just:
ssh whs5-hostinger
```

## 📊 Post-Deployment Validation

### Automated Validation

```bash
# Quick validation script
curl -I https://whs.rotechrural.com.au | grep "200 OK"
```

### Manual Testing Checklist

```yaml
validation_tests:
  application:
    - "⬜ Visit https://whs.rotechrural.com.au"
    - "⬜ Verify login page loads"
    - "⬜ Login with valid credentials"

  priority_1_modules:
    - "⬜ Team Management: https://whs.rotechrural.com.au/teams"
    - "⬜ Incident Management: https://whs.rotechrural.com.au/incidents"
    - "⬜ Vehicle Management: https://whs.rotechrural.com.au/vehicles"
    - "⬜ Inspection Management: https://whs.rotechrural.com.au/inspections"
    - "⬜ Risk Assessment: https://whs.rotechrural.com.au/risk"
    - "⬜ Branch Management: https://whs.rotechrural.com.au/branches"

  dense_table_features:
    - "⬜ Tables display with 25 items per page"
    - "⬜ Search functionality works"
    - "⬜ Filter pills display correctly"
    - "⬜ Quick View modals open (5 modules)"
    - "⬜ Pagination works"
    - "⬜ Responsive design on mobile"

  performance:
    - "⬜ Page loads within 3 seconds"
    - "⬜ No errors in browser console"
    - "⬜ No PHP errors in logs"
```

## 🐛 Troubleshooting

### Common Issues

**Issue**: SSH connection refused
```bash
# Solution: Verify SSH key permissions
ls -la ~/.ssh/whs5_hostinger
# Should show: -rw------- (600)

# Fix if needed
chmod 600 ~/.ssh/whs5_hostinger
```

**Issue**: Git pull fails
```bash
# Solution: Check if repository is clean
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au
cd /home/whs5
git status
git stash  # If there are uncommitted changes
git pull origin master
```

**Issue**: Composer install fails
```bash
# Solution: Clear composer cache
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au
cd /home/whs5
composer clear-cache
composer install --no-dev --optimize-autoloader
```

**Issue**: Permission errors
```bash
# Solution: Reset permissions
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au
cd /home/whs5
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Issue**: 500 Internal Server Error
```bash
# Solution: Check Laravel logs
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au
cd /home/whs5
tail -f storage/logs/laravel.log
```

## 🔄 Rollback Procedure

If deployment causes issues, rollback to previous version:

```bash
# 1. SSH to server
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au

# 2. Navigate to app
cd /home/whs5

# 3. View recent commits
git log --oneline -5

# 4. Rollback to previous commit (before e55e634)
git reset --hard 25d53b6  # Previous commit

# 5. Reinstall dependencies
composer install --no-dev --optimize-autoloader

# 6. Clear and optimize
php artisan cache:clear
php artisan config:cache
php artisan view:cache

# 7. Restart server
systemctl restart apache2
```

## 📞 Support

**Documentation**:
- Full deployment guide: `DEPLOYMENT.md`
- Migration summary: `DENSE_TABLE_MIGRATION_SUMMARY.md`
- Testing report: `PRIORITY_1_TESTING_REPORT.md`

**Server Details**:
- URL: https://whs.rotechrural.com.au
- SSH: root@whs.rotechrural.com.au
- App Path: /home/whs5
- PHP Version: 8.3+
- Laravel Version: 12

---

**Last Updated**: November 3, 2025
**Status**: Ready for deployment
**Next Action**: Run `bash deploy-to-hostinger.sh`
