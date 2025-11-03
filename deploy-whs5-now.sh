#!/bin/bash
# Immediate WHS5 deployment to Hostinger

set -e

echo "========================================="
echo "🚀 WHS5 Hostinger Deployment"
echo "========================================="
echo ""

# Try existing SSH key
SSH_KEY="$HOME/.ssh/rotech_hostinger"
SERVER="whs.rotechrural.com.au"
USER="root"

if [ ! -f "$SSH_KEY" ]; then
    echo "❌ SSH key not found at: $SSH_KEY"
    exit 1
fi

echo "✓ Using SSH key: $SSH_KEY"
echo "✓ Deploying to: $SERVER"
echo ""

# Test SSH connection first
echo "🔗 Testing SSH connection..."
ssh -i "$SSH_KEY" -o ConnectTimeout=10 "${USER}@${SERVER}" "echo '✓ SSH connection successful'" || {
    echo "❌ SSH connection failed. Please verify:"
    echo "  - Server is accessible"
    echo "  - SSH key has correct permissions"
    echo "  - Key is authorized on server"
    exit 1
}

echo ""
echo "📦 Starting deployment..."
echo ""

# Deploy
ssh -i "$SSH_KEY" "${USER}@${SERVER}" bash << 'ENDSSH'
    set -e
    
    echo "📂 Navigating to /home/whs5..."
    cd /home/whs5 || { echo "❌ Directory not found"; exit 1; }
    
    echo "📥 Pulling latest changes from GitHub..."
    git pull origin master || { echo "❌ Git pull failed"; exit 1; }
    
    echo "📦 Installing dependencies..."
    composer install --no-dev --optimize-autoloader || { echo "❌ Composer failed"; exit 1; }
    
    echo "🧹 Clearing caches..."
    php artisan cache:clear
    php artisan config:clear
    php artisan view:clear
    php artisan route:clear
    
    echo "⚡ Optimizing..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    echo "🗄️ Running migrations..."
    php artisan migrate --force || echo "⚠️ No new migrations"
    
    echo "🔒 Setting permissions..."
    chmod -R 775 storage bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R apache:apache storage bootstrap/cache 2>/dev/null || true
    
    echo "🔄 Restarting web server..."
    systemctl restart apache2 2>/dev/null || systemctl restart httpd 2>/dev/null || systemctl restart nginx 2>/dev/null || echo "⚠️ Manual restart may be needed"
    
    echo ""
    echo "✅ Deployment complete!"
    echo ""
    echo "📊 Summary:"
    echo "  Branch: $(git branch --show-current)"
    echo "  Commit: $(git log -1 --pretty=format:'%h - %s' | head -c 50)"
    echo "  PHP: $(php -v | head -n 1 | cut -d' ' -f2)"
    echo ""
ENDSSH

echo ""
echo "========================================="
echo "🎉 Deployment Successful!"
echo "========================================="
echo ""
echo "🌐 Application: https://$SERVER"
echo ""
echo "📋 Next Steps:"
echo "  1. Visit https://$SERVER"
echo "  2. Login and test Priority 1 modules"
echo "  3. Verify dense table views"
echo ""

