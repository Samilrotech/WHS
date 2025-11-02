#!/bin/bash
# deploy-to-hostinger.sh
# WHS5 Deployment Script for Hostinger VPS

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SSH_KEY="$HOME/.ssh/whs5_hostinger"
SERVER="whs.rotechrural.com.au"
USER="root"
APP_PATH="/home/whs5"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}🚀 WHS5 Hostinger Deployment${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if SSH key exists
if [ ! -f "$SSH_KEY" ]; then
    echo -e "${RED}❌ SSH key not found at: $SSH_KEY${NC}"
    echo -e "${YELLOW}Please ensure your SSH key is properly configured.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ SSH key found${NC}"
echo -e "${BLUE}Connecting to: ${USER}@${SERVER}${NC}"
echo ""

# Execute deployment on remote server
ssh -i "$SSH_KEY" "${USER}@${SERVER}" bash << 'ENDSSH'
    set -e  # Exit on any error

    echo "📂 Navigating to application directory..."
    cd /home/whs5 || { echo "❌ Failed to navigate to /home/whs5"; exit 1; }

    echo "📥 Pulling latest changes from GitHub..."
    git pull origin master || { echo "❌ Git pull failed"; exit 1; }

    echo "📦 Installing PHP dependencies..."
    composer install --no-dev --optimize-autoloader || { echo "❌ Composer install failed"; exit 1; }

    echo "🧹 Clearing application caches..."
    php artisan cache:clear
    php artisan config:clear
    php artisan view:clear
    php artisan route:clear

    echo "⚡ Optimizing application..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo "🗄️ Running database migrations..."
    php artisan migrate --force || { echo "⚠️ Migration failed (may be no new migrations)"; }

    echo "🔒 Setting proper permissions..."
    chmod -R 775 storage bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R apache:apache storage bootstrap/cache

    echo "🔄 Restarting web server..."
    systemctl restart apache2 2>/dev/null || systemctl restart httpd 2>/dev/null || systemctl restart nginx 2>/dev/null || echo "⚠️ Could not restart web server (may need manual restart)"

    echo ""
    echo "✅ Deployment complete!"
    echo ""
    echo "📊 Deployment Summary:"
    echo "  • Application Path: /home/whs5"
    echo "  • Git Branch: $(git branch --show-current)"
    echo "  • Latest Commit: $(git log -1 --pretty=format:'%h - %s')"
    echo "  • PHP Version: $(php -v | head -n 1)"
    echo "  • Laravel Version: $(php artisan --version)"
    echo ""
ENDSSH

DEPLOY_EXIT=$?

echo ""
if [ $DEPLOY_EXIT -eq 0 ]; then
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}🎉 Deployment Successful!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo -e "${BLUE}🌐 Application URL: https://${SERVER}${NC}"
    echo ""
    echo -e "${YELLOW}📋 Post-Deployment Checklist:${NC}"
    echo "  1. Visit https://${SERVER} to verify application loads"
    echo "  2. Test Priority 1 modules:"
    echo "     • Team Management: /teams"
    echo "     • Incident Management: /incidents"
    echo "     • Vehicle Management: /vehicles"
    echo "     • Inspection Management: /inspections"
    echo "     • Risk Assessment: /risk"
    echo "     • Branch Management: /branches"
    echo "  3. Check error logs if any issues occur"
    echo "  4. Verify dense table views are displaying correctly"
    echo "  5. Test Quick View modals"
    echo ""
else
    echo -e "${RED}========================================${NC}"
    echo -e "${RED}❌ Deployment Failed!${NC}"
    echo -e "${RED}========================================${NC}"
    echo ""
    echo -e "${YELLOW}Check the error messages above for details.${NC}"
    echo -e "${YELLOW}You may need to manually SSH to the server to investigate.${NC}"
    echo ""
    echo -e "${BLUE}Manual SSH command:${NC}"
    echo "  ssh -i $SSH_KEY ${USER}@${SERVER}"
    echo ""
    exit 1
fi
