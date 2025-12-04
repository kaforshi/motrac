#!/bin/bash

# Script untuk fix TrustProxies middleware untuk Cloudflare
# Usage: sudo ./fix-trust-proxies.sh

set -e

echo "========================================="
echo "  Fix TrustProxies for Cloudflare"
echo "========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

APP_DIR="/var/www/motrac"
APP_USER="www-data"

if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}Application directory $APP_DIR does not exist!${NC}"
    exit 1
fi

cd $APP_DIR

TRUST_PROXIES_FILE="app/Http/Middleware/TrustProxies.php"

if [ ! -f "$TRUST_PROXIES_FILE" ]; then
    echo -e "${RED}TrustProxies.php not found!${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Checking current TrustProxies configuration...${NC}"
if grep -q "protected \$proxies = '\*';" "$TRUST_PROXIES_FILE"; then
    echo -e "${GREEN}✓ TrustProxies already configured for Cloudflare${NC}"
else
    echo -e "${YELLOW}Updating TrustProxies configuration...${NC}"
    
    # Backup
    cp "$TRUST_PROXIES_FILE" "${TRUST_PROXIES_FILE}.backup"
    
    # Update proxies to trust all (for Cloudflare)
    sed -i "s/protected \$proxies;/protected \$proxies = '*'; \/\/ Trust all proxies (for Cloudflare)/g" "$TRUST_PROXIES_FILE"
    
    echo -e "${GREEN}✓ TrustProxies updated${NC}"
fi
echo ""

echo -e "${YELLOW}Step 2: Clearing Laravel cache...${NC}"
sudo -u $APP_USER php artisan config:clear 2>/dev/null || true
sudo -u $APP_USER php artisan cache:clear 2>/dev/null || true
sudo -u $APP_USER php artisan route:clear 2>/dev/null || true
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}Step 3: Optimizing application...${NC}"
sudo -u $APP_USER php artisan config:cache 2>/dev/null || true
sudo -u $APP_USER php artisan route:cache 2>/dev/null || true
echo -e "${GREEN}✓ Application optimized${NC}"
echo ""

echo -e "${YELLOW}Step 4: Testing application...${NC}"
if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001 | grep -qE "200|301|302"; then
    echo -e "${GREEN}✓ Application is working!${NC}"
else
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001)
    echo -e "${YELLOW}HTTP Status: $HTTP_CODE${NC}"
    if [ "$HTTP_CODE" = "500" ]; then
        echo -e "${YELLOW}Still getting 500 error. Check logs:${NC}"
        echo "  tail -f storage/logs/laravel.log"
    fi
fi
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  TrustProxies fix completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "The TrustProxies middleware is now configured to trust all proxies,"
echo "which is required for Cloudflare Tunnel to work correctly."
echo ""

