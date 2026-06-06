#!/usr/bin/env bash

# Exit immediately if a command exits with a non-zero status
set -e

# Configuration variables
PROJECT_DIR="/var/www/status.roleplaymeets.com/backend"
SUPERVISOR_SERVICE="scheduler_statuscheck"
PHP_BIN="/usr/bin/php"

echo "=========================================="
echo "🚀 Starting Production Deployment"
echo "=========================================="

# 1. Navigate to project directory
cd "$PROJECT_DIR"
echo "📂 Navigated to $PROJECT_DIR"

# 2. Safety stash and pull updates via rebase
echo "📥 Fetching latest changes from Git..."
git stash --include-untracked
git pull https://github.com/wolfricoz/status-dashboard-backend.git --rebase --force --autostash
echo "✅ Git repository updated successfully."

# 3. Install/Update dependencies (Crucial for backend changes)
if [ -f "composer.json" ]; then
    echo "📦 Optimizing Composer dependencies..."
    # --no-dev: Skips dev packages
    # -o: Optimizes autoloader for faster class lookups in production
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# 4. Run database migrations
echo "🗄️ Checking for pending database migrations..."
$PHP_BIN bin/console doctrine:migrations:migrate --no-interaction
echo "✅ Database schema updated."

# 5. Clear and warm up application cache
echo "🧹 Clearing production cache..."
# Clearing app pools explicitly ensures your scheduler cache keys don't get stuck
$PHP_BIN bin/console cache:pool:clear cache.app --no-interaction
$PHP_BIN bin/console cache:clear --env=prod --no-warmup
$PHP_BIN bin/console cache:warmup --env=prod
echo "✅ Production cache rebuilt."

# 6. Restart Supervisor worker processes
echo "🔄 Restarting Supervisor worker services..."
# We use 'restart' so the old code currently in worker memory is flushed out completely
sudo supervisorctl restart "$SUPERVISOR_SERVICE"
echo "✅ Supervisor services restarted successfully."

echo "=========================================="
echo "🎉 Deployment Completed Successfully!"
echo "=========================================="