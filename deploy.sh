#!/bin/bash
# Deploy script for Netking ISP
# Usage: bash deploy.sh

set -e

APP_DIR="/home/netking.id/release-clean"
BRANCH="main"

echo "==================================="
echo "  NETKING ISP — Deploy Script"
echo "==================================="

cd $APP_DIR

echo ""
echo "📥 Pulling latest code..."
git pull origin $BRANCH

echo ""
echo "🔧 Optimizing Laravel cache..."
php artisan optimize

echo ""
echo "🔄 Restarting web server..."
sudo /usr/local/lsws/bin/lswsctrl restart

echo ""
echo "==================================="
echo "  ✅ Deploy selesai!"
echo "==================================="
