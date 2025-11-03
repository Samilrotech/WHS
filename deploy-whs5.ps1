# WHS5 Deployment to Hostinger - PowerShell Script

Write-Host "========================================" -ForegroundColor Blue
Write-Host "WHS5 Hostinger Deployment" -ForegroundColor Blue  
Write-Host "========================================" -ForegroundColor Blue
Write-Host ""

$SSHKey = "$env:USERPROFILE\.ssh\whs5_deployment_key"
$Server = "147.79.67.122"

Write-Host "Testing SSH connection..." -ForegroundColor Yellow
$testCmd = "echo 'Connected'"
ssh -i $SSHKey root@$Server $testCmd

if ($LASTEXITCODE -eq 0) {
    Write-Host "SSH connection successful!" -ForegroundColor Green
    Write-Host ""
    
    Write-Host "Deploying WHS5..." -ForegroundColor Yellow
    
    $deployScript = @"
cd /var/www/html && \
git pull origin master && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan cache:clear && \
php artisan config:clear && \
php artisan view:clear && \
php artisan route:clear && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
chmod -R 775 storage bootstrap/cache && \
chown -R www-data:www-data storage bootstrap/cache && \
systemctl restart apache2 && \
echo 'Deployment complete!'
"@

    ssh -i $SSHKey root@$Server $deployScript
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "" 
        Write-Host "========================================" -ForegroundColor Green
        Write-Host "Deployment Successful!" -ForegroundColor Green
        Write-Host "========================================" -ForegroundColor Green
        Write-Host ""
        Write-Host "Application URL: https://whs.rotechrural.com.au" -ForegroundColor Cyan
        Write-Host ""
    } else {
        Write-Host "Deployment failed!" -ForegroundColor Red
    }
} else {
    Write-Host "SSH connection failed!" -ForegroundColor Red
}
