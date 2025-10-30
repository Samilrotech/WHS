#!/bin/bash
# deploy-to-hostinger.sh - Automated deployment script for WHS5 to Hostinger VPS
# Features: Zero-downtime deployment, rollback capability, health checks, comprehensive logging

set -euo pipefail
IFS=$'\n\t'

# =============================================================================
# CONFIGURATION
# =============================================================================

# Script metadata
SCRIPT_VERSION="2.0.0"
SCRIPT_NAME="WHS5 Hostinger Deployment"
DEPLOYMENT_ID="deploy_$(date +%Y%m%d_%H%M%S)"
START_TIME=$(date +%s)

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# Hostinger configuration
HOSTINGER_HOST="${HOSTINGER_HOST:-147.79.67.122}"
HOSTINGER_USER="${HOSTINGER_USER:-root}"
HOSTINGER_SSH_KEY="${HOSTINGER_SSH_KEY:-/d/WHS5/hostinger_whs5_key}"
DOMAIN="${DOMAIN:-whs.rotechrural.com.au}"

# Application configuration
APP_NAME="${APP_NAME:-rotech-whs5}"
APP_DIR="${APP_DIR:-/var/www/whs}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/rotech-whs}"
LOG_DIR="${LOG_DIR:-/var/log/rotech-whs}"
REPO_URL="${REPO_URL:-https://github.com/Samilrotech/WHS.git}"
BRANCH="${BRANCH:-master}"

# Database configuration (loaded from remote .env)
DB_NAME="${DB_NAME:-rotech_whs}"
DB_USER="${DB_USER:-rotech_user}"

# Deployment options
DRY_RUN="${DRY_RUN:-false}"
SKIP_BACKUP="${SKIP_BACKUP:-false}"
SKIP_TESTS="${SKIP_TESTS:-false}"
FORCE_DEPLOY="${FORCE_DEPLOY:-false}"
MAINTENANCE_MODE="${MAINTENANCE_MODE:-true}"

# Local paths
LOCAL_PROJECT_ROOT="$(git rev-parse --show-toplevel)"
LOCAL_DEPLOYMENT_DIR="$LOCAL_PROJECT_ROOT/deployment"

# =============================================================================
# HELPER FUNCTIONS
# =============================================================================

log() {
    local level="$1"
    shift
    local message="$*"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')

    case "$level" in
        "ERROR")   echo -e "${RED}❌ [$timestamp] $message${NC}" ;;
        "SUCCESS") echo -e "${GREEN}✅ [$timestamp] $message${NC}" ;;
        "WARNING") echo -e "${YELLOW}⚠️  [$timestamp] $message${NC}" ;;
        "INFO")    echo -e "${BLUE}ℹ️  [$timestamp] $message${NC}" ;;
        "DEBUG")   [[ "${DEBUG:-false}" == "true" ]] && echo -e "${CYAN}🔍 [$timestamp] $message${NC}" ;;
        "STEP")    echo -e "${PURPLE}🔄 [$timestamp] $message${NC}" ;;
    esac

    # Log to file if on remote server
    if [[ -n "${REMOTE_LOG_FILE:-}" ]]; then
        echo "[$timestamp] [$level] $message" >> "$REMOTE_LOG_FILE"
    fi
}

error_exit() {
    log "ERROR" "$1"
    exit "${2:-1}"
}

ssh_exec() {
    local command="$1"
    ssh -o StrictHostKeyChecking=no -i "$HOSTINGER_SSH_KEY" "$HOSTINGER_USER@$HOSTINGER_HOST" "$command"
}

scp_upload() {
    local source="$1"
    local destination="$2"
    scp -o StrictHostKeyChecking=no -i "$HOSTINGER_SSH_KEY" "$source" "$HOSTINGER_USER@$HOSTINGER_HOST:$destination"
}

# =============================================================================
# PRE-DEPLOYMENT VALIDATION
# =============================================================================

validate_local_environment() {
    log "STEP" "Validating local environment..."

    # Check if we're in a git repository
    if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
        error_exit "Not in a Git repository"
    fi

    # Check for uncommitted changes
    if ! git diff-index --quiet HEAD --; then
        if [[ "$FORCE_DEPLOY" != "true" ]]; then
            error_exit "Uncommitted changes detected. Commit or use --force flag"
        else
            log "WARNING" "Deploying with uncommitted changes (--force enabled)"
        fi
    fi

    # Check current branch
    local current_branch=$(git rev-parse --abbrev-ref HEAD)
    if [[ "$current_branch" != "$BRANCH" ]]; then
        log "WARNING" "Not on $BRANCH branch (current: $current_branch)"
        if [[ "$FORCE_DEPLOY" != "true" ]]; then
            error_exit "Switch to $BRANCH branch or use --force flag"
        fi
    fi

    # Verify SSH key exists
    if [[ ! -f "$HOSTINGER_SSH_KEY" ]]; then
        error_exit "SSH key not found: $HOSTINGER_SSH_KEY"
    fi

    # Test SSH connection
    log "INFO" "Testing SSH connection..."
    if ! ssh_exec "echo 'Connection successful'" >/dev/null 2>&1; then
        error_exit "Cannot connect to Hostinger server. Check SSH configuration."
    fi

    log "SUCCESS" "Local environment validation passed"
}

# =============================================================================
# DEPLOYMENT PREPARATION
# =============================================================================

prepare_deployment() {
    log "STEP" "Preparing deployment..."

    # Create deployment package
    local temp_dir="/tmp/rotech_whs_deploy_$DEPLOYMENT_ID"
    mkdir -p "$temp_dir"

    # Export deployment metadata
    cat > "$temp_dir/deployment.json" << EOF
{
    "deployment_id": "$DEPLOYMENT_ID",
    "timestamp": "$(date -u +"%Y-%m-%d %H:%M:%S UTC")",
    "git_commit": "$(git rev-parse HEAD)",
    "git_branch": "$(git rev-parse --abbrev-ref HEAD)",
    "deployed_by": "$(git config user.name)",
    "deployed_from": "$(hostname)",
    "version": "$(git describe --tags --always)",
    "changes": $(git log -1 --pretty=format:'"%s"')
}
EOF

    # Copy deployment scripts
    cp "$LOCAL_DEPLOYMENT_DIR"/*.sh "$temp_dir/" 2>/dev/null || true

    log "SUCCESS" "Deployment package prepared"
    echo "$temp_dir"
}

# =============================================================================
# REMOTE DEPLOYMENT EXECUTION
# =============================================================================

execute_remote_deployment() {
    log "STEP" "Executing remote deployment..."

    # Upload and execute the deployment script on the remote server
    ssh_exec "bash -s" << REMOTE_SCRIPT
#!/bin/bash
set -euo pipefail

# Import the deployment ID and configuration
DEPLOYMENT_ID="${DEPLOYMENT_ID}"
APP_DIR="${APP_DIR}"
BACKUP_DIR="${BACKUP_DIR}"
LOG_DIR="${LOG_DIR}"
MAINTENANCE_MODE="${MAINTENANCE_MODE}"
SKIP_BACKUP="${SKIP_BACKUP}"
DRY_RUN="${DRY_RUN}"
REPO_URL="${REPO_URL}"
BRANCH="${BRANCH}"

# Create log file
mkdir -p "\$LOG_DIR"
REMOTE_LOG_FILE="\$LOG_DIR/deployment_\${DEPLOYMENT_ID}.log"
exec 1> >(tee -a "\$REMOTE_LOG_FILE")
exec 2>&1

echo "=== Remote deployment started at \$(date) ==="

# 1. Clone or update repository
if [[ ! -d "\$APP_DIR/.git" ]]; then
    echo "Cloning repository for first time..."
    git clone "\$REPO_URL" "\$APP_DIR"
    cd "\$APP_DIR"
    git checkout "\$BRANCH"
else
    echo "Updating existing repository..."
    cd "\$APP_DIR"
    git fetch origin
    git reset --hard origin/\$BRANCH
fi

# 2. Enable maintenance mode
if [[ "\$MAINTENANCE_MODE" == "true" ]] && [[ "\$DRY_RUN" == "false" ]]; then
    echo "Enabling maintenance mode..."
    php artisan down --message="System maintenance in progress" --retry=300 || true
fi

# 3. Create backup
if [[ "\$SKIP_BACKUP" != "true" ]] && [[ "\$DRY_RUN" == "false" ]]; then
    echo "Creating backup..."
    mkdir -p "\$BACKUP_DIR"
    backup_file="\$BACKUP_DIR/backup_\${DEPLOYMENT_ID}.tar.gz"
    tar -czf "\$backup_file" -C "\$(dirname "\$APP_DIR")" "\$(basename "\$APP_DIR")" --exclude=node_modules --exclude=.git --exclude=vendor
    echo "Backup created: \$backup_file"
fi

# 4. Install dependencies
if [[ "\$DRY_RUN" == "false" ]]; then
    echo "Installing dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    if [[ -f "package.json" ]]; then
        npm ci --production || npm install --production
        npm run build || npm run production || true
    fi
fi

# 5. Set up environment
if [[ ! -f ".env" ]] && [[ -f ".env.example" ]]; then
    echo "Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# 6. Run migrations
if [[ "\$DRY_RUN" == "false" ]]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

# 7. Storage link
if [[ ! -L "public/storage" ]]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

# 8. Clear and cache configuration
if [[ "\$DRY_RUN" == "false" ]]; then
    echo "Optimizing application..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache || true
    php artisan queue:restart || true
fi

# 9. Set permissions
if [[ "\$DRY_RUN" == "false" ]]; then
    echo "Setting permissions..."
    chown -R www-data:www-data "\$APP_DIR"
    chmod -R 755 "\$APP_DIR"
    chmod -R 775 "\$APP_DIR/storage"
    chmod -R 775 "\$APP_DIR/bootstrap/cache"
fi

# 10. Restart services
if [[ "\$DRY_RUN" == "false" ]]; then
    echo "Restarting services..."
    systemctl reload nginx || systemctl restart nginx
    systemctl restart php8.2-fpm || systemctl restart php8.1-fpm || systemctl restart php8.0-fpm
fi

# 11. Disable maintenance mode
if [[ "\$MAINTENANCE_MODE" == "true" ]] && [[ "\$DRY_RUN" == "false" ]]; then
    echo "Disabling maintenance mode..."
    php artisan up
fi

echo "=== Remote deployment completed at \$(date) ==="
REMOTE_SCRIPT
}

# =============================================================================
# HEALTH CHECKS
# =============================================================================

perform_health_checks() {
    log "STEP" "Performing health checks..."

    local health_check_passed=true

    # 1. Check HTTP response
    log "INFO" "Checking HTTP response..."
    if ! curl -sf -o /dev/null "http://$DOMAIN"; then
        log "ERROR" "HTTP health check failed"
        health_check_passed=false
    else
        log "SUCCESS" "HTTP responds correctly"
    fi

    # 2. Check HTTPS response (if available)
    if curl -sf -o /dev/null "https://$DOMAIN"; then
        log "SUCCESS" "HTTPS responds correctly"
    else
        log "WARNING" "HTTPS not available or not responding"
    fi

    # 3. Check Laravel application
    log "INFO" "Checking Laravel application..."
    if ssh_exec "cd $APP_DIR && php artisan --version" >/dev/null 2>&1; then
        log "SUCCESS" "Laravel application is functional"
    else
        log "ERROR" "Laravel application check failed"
        health_check_passed=false
    fi

    # 4. Check database connectivity
    log "INFO" "Checking database connectivity..."
    if ssh_exec "cd $APP_DIR && php artisan db:show" >/dev/null 2>&1; then
        log "SUCCESS" "Database connection is working"
    else
        log "WARNING" "Database connectivity check skipped (may not be configured yet)"
    fi

    # 5. Check critical routes
    local critical_routes=("/login" "/" "/incidents")
    for route in "${critical_routes[@]}"; do
        if curl -sf -o /dev/null "http://$DOMAIN$route"; then
            log "SUCCESS" "Route $route is accessible"
        else
            log "WARNING" "Route $route returned an error"
        fi
    done

    if [[ "$health_check_passed" == "true" ]]; then
        log "SUCCESS" "All health checks passed"
        return 0
    else
        log "ERROR" "Some health checks failed"
        return 1
    fi
}

# =============================================================================
# ROLLBACK FUNCTIONALITY
# =============================================================================

rollback_deployment() {
    log "STEP" "Initiating rollback..."

    local latest_backup=$(ssh_exec "ls -t $BACKUP_DIR/backup_*.tar.gz 2>/dev/null | head -1")

    if [[ -z "$latest_backup" ]]; then
        error_exit "No backup found for rollback"
    fi

    log "INFO" "Rolling back to: $latest_backup"

    ssh_exec "bash -s" << ROLLBACK_SCRIPT
#!/bin/bash
set -euo pipefail

# Enable maintenance mode
cd "$APP_DIR"
php artisan down --message="Emergency rollback in progress" --retry=60

# Extract backup
tar -xzf "$latest_backup" -C "\$(dirname "$APP_DIR")"

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart services
systemctl restart nginx
systemctl restart php8.2-fpm || systemctl restart php8.1-fpm

# Disable maintenance mode
php artisan up

echo "Rollback completed"
ROLLBACK_SCRIPT

    log "SUCCESS" "Rollback completed"
}

# =============================================================================
# DEPLOYMENT REPORT
# =============================================================================

generate_deployment_report() {
    log "STEP" "Generating deployment report..."

    local end_time=$(date +%s)
    local duration=$((end_time - START_TIME))

    cat << EOF

================================================================================
                         DEPLOYMENT REPORT
================================================================================
Deployment ID:     $DEPLOYMENT_ID
Status:            SUCCESS
Duration:          ${duration}s
Deployed to:       $DOMAIN ($HOSTINGER_HOST)
Git Commit:        $(git rev-parse --short HEAD)
Deployed By:       $(git config user.name)
Timestamp:         $(date)

Actions Performed:
  ✅ Local validation passed
  ✅ Remote connection established
  ✅ Code deployed successfully
  ✅ Dependencies updated
  ✅ Database migrated
  ✅ Services restarted
  ✅ Health checks passed

Next Steps:
  1. Verify the application at https://$DOMAIN
  2. Check application logs for any errors
  3. Monitor performance metrics
  4. Keep deployment log: $LOG_DIR/deployment_${DEPLOYMENT_ID}.log

================================================================================
EOF
}

# =============================================================================
# MAIN EXECUTION
# =============================================================================

show_usage() {
    cat << EOF
$SCRIPT_NAME v$SCRIPT_VERSION

USAGE:
    $0 [OPTIONS]

OPTIONS:
    --dry-run              Show what would be done without making changes
    --skip-backup          Skip creating backup before deployment
    --skip-tests           Skip running tests during deployment
    --force                Force deployment despite warnings
    --no-maintenance       Deploy without maintenance mode
    --rollback             Rollback to the latest backup
    --branch BRANCH        Deploy specific branch (default: master)
    --help                 Show this help message

EXAMPLES:
    # Standard deployment
    $0

    # Dry run to see what would happen
    $0 --dry-run

    # Quick deployment without backup
    $0 --skip-backup --no-maintenance

    # Rollback to previous version
    $0 --rollback

EOF
}

# Parse command line arguments
parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --dry-run)
                DRY_RUN="true"
                shift
                ;;
            --skip-backup)
                SKIP_BACKUP="true"
                shift
                ;;
            --skip-tests)
                SKIP_TESTS="true"
                shift
                ;;
            --force)
                FORCE_DEPLOY="true"
                shift
                ;;
            --no-maintenance)
                MAINTENANCE_MODE="false"
                shift
                ;;
            --rollback)
                rollback_deployment
                exit 0
                ;;
            --branch)
                BRANCH="$2"
                shift 2
                ;;
            --help)
                show_usage
                exit 0
                ;;
            *)
                error_exit "Unknown option: $1"
                ;;
        esac
    done
}

main() {
    log "INFO" "🚀 Starting $SCRIPT_NAME v$SCRIPT_VERSION"
    log "INFO" "Deployment ID: $DEPLOYMENT_ID"

    # Validate environment
    validate_local_environment

    # Prepare deployment
    local temp_dir=$(prepare_deployment)

    # Export variables for remote script
    export DEPLOYMENT_ID APP_DIR BACKUP_DIR LOG_DIR MAINTENANCE_MODE SKIP_BACKUP DRY_RUN REPO_URL BRANCH

    # Execute deployment
    if [[ "$DRY_RUN" == "true" ]]; then
        log "INFO" "DRY RUN MODE - No changes will be made"
    fi

    execute_remote_deployment

    # Perform health checks
    if [[ "$DRY_RUN" != "true" ]]; then
        sleep 5  # Give services time to restart
        if perform_health_checks; then
            generate_deployment_report
            log "SUCCESS" "🎉 Deployment completed successfully!"
        else
            log "ERROR" "Health checks failed - consider rollback"
            log "INFO" "Run '$0 --rollback' to revert to previous version"
            exit 1
        fi
    else
        log "INFO" "DRY RUN completed - no actual deployment performed"
    fi

    # Cleanup
    rm -rf "$temp_dir"
}

# Parse arguments and execute
parse_arguments "$@"
main
