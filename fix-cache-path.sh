#!/bin/bash

# Script untuk fix cache path error
# Usage: sudo ./fix-cache-path.sh

set -e

echo "========================================="
echo "  Fix Cache Path Error"
echo "========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

APP_DIR="/var/www/motrac"
APP_USER="www-data"

if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}Application directory $APP_DIR does not exist!${NC}"
    exit 1
fi

cd $APP_DIR

echo -e "${YELLOW}Step 1: Creating cache directories...${NC}"

# Create all necessary cache directories
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p bootstrap/cache

echo -e "${GREEN}✓ Cache directories created${NC}"
echo ""

echo -e "${YELLOW}Step 2: Fixing ownership...${NC}"
chown -R $APP_USER:$APP_USER storage
chown -R $APP_USER:$APP_USER bootstrap/cache
echo -e "${GREEN}✓ Ownership fixed${NC}"
echo ""

echo -e "${YELLOW}Step 3: Fixing permissions...${NC}"
chmod -R 775 storage
chmod -R 775 bootstrap/cache
find storage -type f -exec chmod 664 {} \;
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}Step 4: Clearing all caches...${NC}"
sudo -u $APP_USER php artisan config:clear 2>/dev/null || true
sudo -u $APP_USER php artisan cache:clear 2>/dev/null || true
sudo -u $APP_USER php artisan route:clear 2>/dev/null || true
sudo -u $APP_USER php artisan view:clear 2>/dev/null || true
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

echo -e "${YELLOW}Step 5: Verifying cache directories...${NC}"
if [ -d "storage/framework/views" ] && [ -w "storage/framework/views" ]; then
    echo -e "${GREEN}✓ storage/framework/views exists and is writable${NC}"
else
    echo -e "${RED}✗ storage/framework/views is not writable!${NC}"
    chmod -R 775 storage/framework/views
    chown -R $APP_USER:$APP_USER storage/framework/views
fi

if [ -d "storage/framework/cache" ] && [ -w "storage/framework/cache" ]; then
    echo -e "${GREEN}✓ storage/framework/cache exists and is writable${NC}"
else
    echo -e "${RED}✗ storage/framework/cache is not writable!${NC}"
    chmod -R 775 storage/framework/cache
    chown -R $APP_USER:$APP_USER storage/framework/cache
fi

if [ -d "storage/framework/sessions" ] && [ -w "storage/framework/sessions" ]; then
    echo -e "${GREEN}✓ storage/framework/sessions exists and is writable${NC}"
else
    echo -e "${RED}✗ storage/framework/sessions is not writable!${NC}"
    chmod -R 775 storage/framework/sessions
    chown -R $APP_USER:$APP_USER storage/framework/sessions
fi
echo ""

echo -e "${YELLOW}Step 6: Testing cache write...${NC}"
if sudo -u $APP_USER touch storage/framework/views/test.txt 2>/dev/null; then
    rm -f storage/framework/views/test.txt
    echo -e "${GREEN}✓ Cache directory is writable${NC}"
else
    echo -e "${RED}✗ Cache directory is NOT writable!${NC}"
    echo -e "${YELLOW}Fixing again...${NC}"
    chmod -R 777 storage/framework/views
    chown -R $APP_USER:$APP_USER storage/framework/views
fi
echo ""

echo -e "${YELLOW}Step 7: Optimizing application...${NC}"
sudo -u $APP_USER php artisan config:cache 2>/dev/null || true
sudo -u $APP_USER php artisan route:cache 2>/dev/null || true
sudo -u $APP_USER php artisan view:cache 2>/dev/null || true
echo -e "${GREEN}✓ Application optimized${NC}"
echo ""

echo -e "${YELLOW}Step 8: Testing application...${NC}"
if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001 | grep -qE "200|301|302"; then
    echo -e "${GREEN}✓ Application is working!${NC}"
else
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001)
    echo -e "${YELLOW}HTTP Status: $HTTP_CODE${NC}"
    if [ "$HTTP_CODE" = "500" ]; then
        echo -e "${YELLOW}Still getting 500. Check if TrustProxies is fixed:${NC}"
        echo "  grep 'protected \$proxies' app/Http/Middleware/TrustProxies.php"
    fi
fi
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Cache path fix completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "If still error, check:"
echo "  tail -f storage/logs/laravel.log"
echo ""

