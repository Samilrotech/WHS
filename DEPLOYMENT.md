# DEPLOYMENT.md - WHS5 Deployment Configuration

Deployment procedures and configuration for WHS5 application.

## SSH Configuration

### Hostinger SSH Key

```yaml
---
ssh_config:
  host: "whs.rotechrural.com.au"
  user: "root"
  key_type: "ssh-ed25519"
  public_key: "AAAAC3NzaC1lZDI1NTE5AAAAII6Vb2Bkf41/t0aEoWcusbMnQHECM0H6tp4DgppTqCrz"
  key_identifier: "whs5-hostinger@rotechrural.com.au"
  key_location: "~/.ssh/whs5_hostinger"
---
```

**Full SSH Key**:
```
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAII6Vb2Bkf41/t0aEoWcusbMnQHECM0H6tp4DgppTqCrz whs5-hostinger@rotechrural.com.au
```

### SSH Connection

```bash
# Connect to Hostinger VPS
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au

# Or add to ~/.ssh/config
Host whs5-hostinger
    HostName whs.rotechrural.com.au
    User root
    IdentityFile ~/.ssh/whs5_hostinger
    IdentitiesOnly yes
```

## GitHub Repository

```yaml
repository:
  url: "https://github.com/Samilrotech/WHS.git"
  branch: "master"
  remote: "origin"
```

## Deployment Workflow

### Phase 1: GitHub Update

```yaml
---
workflow: "github_deployment"
steps:
  1_status_check:
    command: "git status"
    purpose: "Review uncommitted changes"

  2_add_changes:
    command: "git add ."
    purpose: "Stage all changes"

  3_commit:
    command: "git commit -m 'feat: Complete Priority 1 Dense Table migration (6/6 modules)'"
    purpose: "Commit with descriptive message"

  4_push:
    command: "git push -u origin master"
    purpose: "Push to GitHub repository"
---
```

**Commands**:
```bash
# Check current status
git status

# Stage all changes
git add .

# Commit with descriptive message
git commit -m "$(cat <<'EOF'
feat: Complete Priority 1 Dense Table migration (6/6 modules)

- Migrated Inspection Management to dense table view
- Migrated Risk Assessment to dense table view
- Migrated Branch Management to dense table view
- Updated all pagination to 25 items per page
- Integrated Quick View modals (5/6 modules)
- Full technical verification complete
- Comprehensive documentation suite created

Modules Complete:
✅ Team Management
✅ Incident Management
✅ Vehicle Management
✅ Inspection Management
✅ Risk Assessment
✅ Branch Management

Files Created: 6 new _table-view.blade.php partials
Files Modified: 12 files (6 views + 6 controllers)
Documentation: 5 comprehensive .md files

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>
EOF
)"

# Push to GitHub
git push -u origin master
```

### Phase 2: Hostinger Deployment

```yaml
---
workflow: "hostinger_deployment"
environment:
  server: "whs.rotechrural.com.au"
  user: "root"
  app_path: "/home/whs5"
  php_version: "8.3"
  web_server: "Apache/Nginx"

steps:
  1_ssh_connect:
    command: "ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au"
    purpose: "Connect to Hostinger VPS"

  2_navigate_to_app:
    command: "cd /home/whs5"
    purpose: "Navigate to application directory"

  3_git_pull:
    command: "git pull origin master"
    purpose: "Pull latest changes from GitHub"

  4_composer_install:
    command: "composer install --no-dev --optimize-autoloader"
    purpose: "Install PHP dependencies (production mode)"

  5_clear_cache:
    command: "php artisan cache:clear && php artisan config:clear && php artisan view:clear"
    purpose: "Clear all Laravel caches"

  6_optimize:
    command: "php artisan config:cache && php artisan route:cache && php artisan view:cache"
    purpose: "Optimize Laravel for production"

  7_migrations:
    command: "php artisan migrate --force"
    purpose: "Run database migrations (if any)"

  8_permissions:
    command: "chmod -R 775 storage bootstrap/cache"
    purpose: "Set correct permissions"

  9_restart_services:
    command: "systemctl restart apache2 || systemctl restart nginx"
    purpose: "Restart web server"
---
```

**Full Deployment Script**:
```bash
#!/bin/bash
# deploy-to-hostinger.sh

echo "🚀 Deploying WHS5 to Hostinger..."

# Connect and deploy
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au << 'ENDSSH'
  # Navigate to app
  cd /home/whs5 || exit 1

  # Pull latest changes
  echo "📥 Pulling latest changes from GitHub..."
  git pull origin master

  # Install dependencies
  echo "📦 Installing dependencies..."
  composer install --no-dev --optimize-autoloader

  # Clear caches
  echo "🧹 Clearing caches..."
  php artisan cache:clear
  php artisan config:clear
  php artisan view:clear

  # Optimize
  echo "⚡ Optimizing application..."
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache

  # Run migrations
  echo "🗄️ Running migrations..."
  php artisan migrate --force

  # Set permissions
  echo "🔒 Setting permissions..."
  chmod -R 775 storage bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache

  # Restart services
  echo "🔄 Restarting web server..."
  systemctl restart apache2 || systemctl restart nginx

  echo "✅ Deployment complete!"
ENDSSH

echo "🎉 WHS5 successfully deployed to Hostinger!"
```

## Environment Configuration

### Production Environment (.env)

```yaml
production_env:
  app_name: "WHS5 - Rotech Rural"
  app_env: "production"
  app_debug: false
  app_url: "https://whs.rotechrural.com.au"

  database:
    connection: "mysql"
    host: "localhost"
    port: 3306
    database: "whs5_production"
    username: "whs5_user"
    # password stored securely on server

  cache:
    driver: "redis"

  session:
    driver: "redis"

  queue:
    connection: "redis"

  mail:
    mailer: "smtp"
    # SMTP configuration on server
```

### Server Requirements

```yaml
server_requirements:
  php: ">=8.2"
  mysql: ">=8.0"
  redis: ">=6.0"
  composer: ">=2.0"
  node: ">=18.0"
  npm: ">=9.0"

  php_extensions:
    - "mbstring"
    - "xml"
    - "bcmath"
    - "pdo"
    - "pdo_mysql"
    - "redis"
    - "gd"
    - "curl"
    - "zip"
    - "intl"
```

## Rollback Procedure

```yaml
---
rollback:
  purpose: "Revert to previous deployment if issues occur"

  steps:
    1_ssh_connect:
      command: "ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au"

    2_navigate:
      command: "cd /home/whs5"

    3_git_log:
      command: "git log --oneline -5"
      purpose: "Find commit to rollback to"

    4_git_reset:
      command: "git reset --hard <commit-hash>"
      purpose: "Reset to previous commit"

    5_reinstall:
      command: "composer install --no-dev --optimize-autoloader"

    6_clear_optimize:
      command: "php artisan cache:clear && php artisan config:cache && php artisan view:cache"

    7_restart:
      command: "systemctl restart apache2 || systemctl restart nginx"
---
```

**Rollback Commands**:
```bash
# SSH to server
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au

# Navigate to app
cd /home/whs5

# View recent commits
git log --oneline -5

# Rollback to previous commit
git reset --hard HEAD~1

# Reinstall dependencies
composer install --no-dev --optimize-autoloader

# Clear and optimize
php artisan cache:clear
php artisan config:cache
php artisan view:cache

# Restart server
systemctl restart apache2 || systemctl restart nginx
```

## Monitoring & Validation

### Post-Deployment Checks

```yaml
validation_checks:
  1_application_status:
    url: "https://whs.rotechrural.com.au"
    expected: "200 OK"

  2_database_connection:
    command: "php artisan tinker --execute='DB::connection()->getPdo();'"
    expected: "PDO object"

  3_cache_status:
    command: "php artisan cache:clear"
    expected: "Application cache cleared!"

  4_queue_status:
    command: "php artisan queue:work --once"
    expected: "No errors"

  5_logs:
    path: "storage/logs/laravel.log"
    check: "Review for errors"
```

**Validation Script**:
```bash
#!/bin/bash
# validate-deployment.sh

echo "🔍 Validating WHS5 deployment..."

# Check application is accessible
curl -I https://whs.rotechrural.com.au | grep "200 OK"

# SSH to server for internal checks
ssh -i ~/.ssh/whs5_hostinger root@whs.rotechrural.com.au << 'ENDSSH'
  cd /home/whs5

  # Check database connection
  php artisan tinker --execute='DB::connection()->getPdo();'

  # Check cache
  php artisan cache:clear

  # Check recent logs
  tail -n 50 storage/logs/laravel.log
ENDSSH

echo "✅ Validation complete!"
```

## Backup Procedures

### Pre-Deployment Backup

```yaml
backup_workflow:
  database_backup:
    command: "mysqldump -u whs5_user -p whs5_production > backup_$(date +%Y%m%d_%H%M%S).sql"
    location: "/home/backups/database/"

  files_backup:
    command: "tar -czf whs5_backup_$(date +%Y%m%d_%H%M%S).tar.gz /home/whs5"
    location: "/home/backups/files/"
    exclude:
      - "node_modules/"
      - "vendor/"
      - "storage/logs/"
      - ".git/"
```

**Backup Script**:
```bash
#!/bin/bash
# backup-before-deploy.sh

BACKUP_DIR="/home/backups"
DATE=$(date +%Y%m%d_%H%M%S)

echo "💾 Creating backup before deployment..."

# Database backup
mysqldump -u whs5_user -p whs5_production > "$BACKUP_DIR/database/whs5_db_$DATE.sql"

# Files backup (excluding node_modules, vendor, logs)
tar -czf "$BACKUP_DIR/files/whs5_files_$DATE.tar.gz" \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='storage/logs' \
  --exclude='.git' \
  /home/whs5

echo "✅ Backup complete!"
echo "📁 Database: $BACKUP_DIR/database/whs5_db_$DATE.sql"
echo "📁 Files: $BACKUP_DIR/files/whs5_files_$DATE.tar.gz"
```

## Deployment Checklist

```yaml
---
pre_deployment:
  - "✅ All Priority 1 modules tested locally"
  - "✅ Git status clean (all changes committed)"
  - "✅ Documentation updated"
  - "✅ Database migrations tested"
  - "✅ Environment variables verified"
  - "✅ Backup created"

deployment:
  - "⬜ Push to GitHub"
  - "⬜ SSH to Hostinger"
  - "⬜ Pull latest changes"
  - "⬜ Install dependencies"
  - "⬜ Run migrations"
  - "⬜ Clear and optimize caches"
  - "⬜ Set permissions"
  - "⬜ Restart services"

post_deployment:
  - "⬜ Verify application loads"
  - "⬜ Test Priority 1 modules"
  - "⬜ Check error logs"
  - "⬜ Verify database connection"
  - "⬜ Test authentication"
  - "⬜ Validate dense table views"
---
```

## Troubleshooting

### Common Issues

```yaml
issues:
  permission_denied:
    symptom: "Permission denied errors"
    solution: "chmod -R 775 storage bootstrap/cache"

  cache_issues:
    symptom: "Old views or config persisting"
    solution: "php artisan cache:clear && php artisan config:clear && php artisan view:clear"

  composer_errors:
    symptom: "Composer install fails"
    solution: "composer clear-cache && composer install --no-dev"

  database_connection:
    symptom: "Could not connect to database"
    solution: "Check .env credentials and MySQL service status"

  500_errors:
    symptom: "500 Internal Server Error"
    solution: "Check storage/logs/laravel.log for details"
```

## Security Notes

```yaml
security:
  ssh_key:
    storage: "Local ~/.ssh directory only"
    permissions: "600 (read/write owner only)"
    backup: "Store securely in password manager"

  env_file:
    location: "Server only (not in git)"
    permissions: "600"
    contains: "Database credentials, API keys, app secrets"

  database:
    access: "Localhost only"
    credentials: "Strong passwords, rotated quarterly"
    backups: "Daily automated backups"
```

---

**Last Updated**: November 3, 2025
**Deployment Target**: Hostinger VPS - whs.rotechrural.com.au
**Repository**: https://github.com/Samilrotech/WHS.git
