#!/bin/bash

# Deployment script for Murugan Admin Panel
# This script pulls the latest code from GitHub and runs necessary deployment steps

set -e  # Exit on error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
REPO_PATH="/home/u459609675/domains/app.muruganflowersuae.com/public_html"
LOG_FILE="$REPO_PATH/deployment.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# Function to log messages
log_message() {
    echo -e "${GREEN}[${TIMESTAMP}]${NC} $1" | tee -a "$LOG_FILE"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

# Start deployment
log_message "=========================================="
log_message "Starting deployment process..."
log_message "=========================================="

# Step 1: Pull latest code from GitHub
log_message "Step 1: Pulling latest code from GitHub..."
cd "$REPO_PATH"
git fetch origin master
git reset --hard origin/master
log_message "✓ Code pulled successfully"

# Step 2: Verify we're in the correct directory
log_message "Step 2: Verifying directory..."
cd "$REPO_PATH"
log_message "✓ In directory: $(pwd)"

# Step 3: Install/Update PHP dependencies
log_message "Step 3: Installing PHP dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
    log_message "✓ Composer dependencies installed"
else
    log_error "composer.json not found!"
    exit 1
fi

# Step 4: Clear Laravel cache
log_message "Step 4: Clearing Laravel cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
log_message "✓ Cache cleared"

# Step 5: Run database migrations (if needed)
log_message "Step 5: Running database migrations..."
php artisan migrate --force
log_message "✓ Migrations completed"

# Step 6: Seed database (optional - uncomment if needed)
# log_message "Step 6: Seeding database..."
# php artisan db:seed --force
# log_message "✓ Database seeded"

# Step 7: Set proper permissions
log_message "Step 6: Setting proper permissions..."
chmod -R 755 "$REPO_PATH/storage"
chmod -R 755 "$REPO_PATH/bootstrap/cache"
log_message "✓ Permissions set"

# Step 8: Optimize Laravel
log_message "Step 7: Optimizing Laravel..."
php artisan optimize
log_message "✓ Laravel optimized"

# Step 9: Restart queue workers (if using queues)
# log_message "Step 8: Restarting queue workers..."
# php artisan queue:restart
# log_message "✓ Queue workers restarted"

# Deployment complete
log_message "=========================================="
log_message "✅ Deployment completed successfully!"
log_message "=========================================="
log_message "Deployment timestamp: $TIMESTAMP"
log_message "Repository: $REPO_PATH"
log_message "Domain: app.muruganflowersuae.com"

exit 0

