@echo off
REM WHS5 Deployment to Hostinger
echo ========================================
echo WHS5 Hostinger Deployment
echo ========================================
echo.

set SSH_KEY=%USERPROFILE%\.ssh\rotech_hostinger
set SERVER=147.79.67.122

echo Connecting to %SERVER%...
echo.

ssh -i "%SSH_KEY%" root@%SERVER%" cd /var/www/html && pwd && git pull origin master && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && chmod -R 775 storage bootstrap/cache && systemctl restart apache2"

echo.
echo Deployment complete!
echo Visit: https://whs.rotechrural.com.au
echo.
pause
