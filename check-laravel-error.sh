#!/bin/bash

# Script untuk check error Laravel secara detail
# Usage: sudo ./check-laravel-error.sh

set -e

echo "========================================="
echo "  Laravel Error Checker"
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

echo -e "${YELLOW}1. Checking .env file...${NC}"
if [ -f ".env" ]; then
    echo -e "${GREEN}✓ .env exists${NC}"
    
    # Check important settings
    echo -e "${YELLOW}   Important settings:${NC}"
    grep -E "APP_ENV|APP_DEBUG|APP_KEY|APP_URL" .env | sed 's/^/   /'
    
    # Check database
    echo -e "${YELLOW}   Database settings:${NC}"
    grep -E "DB_" .env | sed 's/DB_PASSWORD=.*/DB_PASSWORD=***/' | sed 's/^/   /'
else
    echo -e "${RED}✗ .env not found!${NC}"
fi
echo ""

echo -e "${YELLOW}2. Checking APP_KEY...${NC}"
if grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo -e "${GREEN}✓ APP_KEY is set${NC}"
else
    echo -e "${RED}✗ APP_KEY is NOT set!${NC}"
    echo -e "${YELLOW}   Run: sudo -u www-data php artisan key:generate${NC}"
fi
echo ""

echo -e "${YELLOW}3. Checking database connection...${NC}"
if [ -f ".env" ]; then
    DB_NAME=$(grep "DB_DATABASE=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    DB_USER=$(grep "DB_USERNAME=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    DB_PASS=$(grep "DB_PASSWORD=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    
    if [ -n "$DB_NAME" ] && [ -n "$DB_USER" ] && [ -n "$DB_PASS" ]; then
        if mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>/dev/null; then
            echo -e "${GREEN}✓ Database connection OK${NC}"
            
            # Check tables
            TABLE_COUNT=$(mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME; SHOW TABLES;" 2>/dev/null | wc -l)
            if [ "$TABLE_COUNT" -gt 1 ]; then
                echo -e "${GREEN}✓ Database has tables ($((TABLE_COUNT-1)) tables)${NC}"
            else
                echo -e "${YELLOW}⚠ Database is empty (no tables)${NC}"
                echo -e "${YELLOW}   Run: sudo -u www-data php artisan migrate${NC}"
            fi
        else
            echo -e "${RED}✗ Database connection FAILED!${NC}"
            echo -e "${YELLOW}   Check credentials in .env${NC}"
        fi
    else
        echo -e "${RED}✗ Database credentials incomplete${NC}"
    fi
fi
echo ""

echo -e "${YELLOW}4. Checking permissions...${NC}"
if [ -w "storage" ] && [ -w "bootstrap/cache" ]; then
    echo -e "${GREEN}✓ Storage and cache are writable${NC}"
else
    echo -e "${RED}✗ Permission issues!${NC}"
    echo -e "${YELLOW}   Run: sudo chmod -R 775 storage bootstrap/cache${NC}"
fi
echo ""

echo -e "${YELLOW}5. Checking Laravel logs (last 20 lines)...${NC}"
if [ -f "storage/logs/laravel.log" ]; then
    echo -e "${YELLOW}   Last errors:${NC}"
    tail -20 storage/logs/laravel.log | grep -A 5 -B 5 -i "error\|exception\|fatal" | head -30 | sed 's/^/   /' || echo "   No recent errors found"
else
    echo -e "${YELLOW}⚠ No log file found${NC}"
fi
echo ""

echo -e "${YELLOW}6. Testing artisan commands...${NC}"
if sudo -u $APP_USER php artisan --version 2>&1 | grep -q "Laravel Framework"; then
    echo -e "${GREEN}✓ Artisan is working${NC}"
    VERSION=$(sudo -u $APP_USER php artisan --version 2>&1)
    echo "   $VERSION"
else
    echo -e "${RED}✗ Artisan has errors!${NC}"
    sudo -u $APP_USER php artisan --version 2>&1 | head -5 | sed 's/^/   /'
fi
echo ""

echo -e "${YELLOW}7. Testing route list...${NC}"
if sudo -u $APP_USER php artisan route:list 2>&1 | head -1 | grep -q "Method\|URI"; then
    echo -e "${GREEN}✓ Routes are loaded${NC}"
else
    echo -e "${RED}✗ Routes have errors!${NC}"
    sudo -u $APP_USER php artisan route:list 2>&1 | head -10 | sed 's/^/   /'
fi
echo ""

echo -e "${YELLOW}8. Checking PHP errors...${NC}"
ERROR_OUTPUT=$(sudo -u $APP_USER php artisan config:cache 2>&1 || true)
if echo "$ERROR_OUTPUT" | grep -q "error\|fatal\|exception"; then
    echo -e "${RED}✗ PHP errors found:${NC}"
    echo "$ERROR_OUTPUT" | grep -i "error\|fatal\|exception" | head -5 | sed 's/^/   /'
else
    echo -e "${GREEN}✓ No PHP errors${NC}"
fi
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Check completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "To view full error:"
echo "  tail -f storage/logs/laravel.log"
echo ""
echo "To fix common issues:"
echo "  sudo ./fix-500-error.sh"
echo ""

