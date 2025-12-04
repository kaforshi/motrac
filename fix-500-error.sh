#!/bin/bash

# Script untuk fix 500 Internal Server Error
# Usage: sudo ./fix-500-error.sh

set -e

echo "========================================="
echo "  Fix 500 Internal Server Error"
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

echo -e "${YELLOW}Step 1: Checking .env file...${NC}"
if [ ! -f ".env" ]; then
    echo -e "${RED}✗ .env file not found!${NC}"
    if [ -f ".env.example" ]; then
        echo -e "${YELLOW}Creating .env from .env.example...${NC}"
        cp .env.example .env
        echo -e "${GREEN}✓ .env created${NC}"
        echo -e "${YELLOW}⚠ Please configure .env file!${NC}"
    else
        echo -e "${RED}✗ .env.example also not found!${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✓ .env file exists${NC}"
fi
echo ""

echo -e "${YELLOW}Step 2: Checking APP_KEY...${NC}"
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo -e "${YELLOW}APP_KEY not set, generating...${NC}"
    sudo -u $APP_USER php artisan key:generate --force
    echo -e "${GREEN}✓ APP_KEY generated${NC}"
else
    echo -e "${GREEN}✓ APP_KEY exists${NC}"
fi
echo ""

echo -e "${YELLOW}Step 3: Checking database configuration...${NC}"
if grep -q "DB_DATABASE=" .env; then
    DB_NAME=$(grep "DB_DATABASE=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    DB_USER=$(grep "DB_USERNAME=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    DB_PASS=$(grep "DB_PASSWORD=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    
    if [ -n "$DB_NAME" ] && [ -n "$DB_USER" ]; then
        echo -e "${GREEN}✓ Database config found: $DB_NAME / $DB_USER${NC}"
        
        # Test database connection
        echo -e "${YELLOW}Testing database connection...${NC}"
        if mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>/dev/null; then
            echo -e "${GREEN}✓ Database connection successful${NC}"
        else
            echo -e "${RED}✗ Database connection failed!${NC}"
            echo -e "${YELLOW}Please check database credentials in .env${NC}"
        fi
    else
        echo -e "${RED}✗ Database credentials incomplete in .env${NC}"
    fi
else
    echo -e "${RED}✗ Database configuration not found in .env${NC}"
fi
echo ""

echo -e "${YELLOW}Step 4: Fixing permissions...${NC}"
chown -R $APP_USER:$APP_USER .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${YELLOW}Step 5: Clearing all caches...${NC}"
sudo -u $APP_USER php artisan config:clear 2>/dev/null || true
sudo -u $APP_USER php artisan cache:clear 2>/dev/null || true
sudo -u $APP_USER php artisan route:clear 2>/dev/null || true
sudo -u $APP_USER php artisan view:clear 2>/dev/null || true
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

echo -e "${YELLOW}Step 6: Running migrations...${NC}"
read -p "Run database migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    if sudo -u $APP_USER php artisan migrate --force 2>&1 | grep -q "Nothing to migrate"; then
        echo -e "${GREEN}✓ Migrations up to date${NC}"
    else
        sudo -u $APP_USER php artisan migrate --force
        echo -e "${GREEN}✓ Migrations completed${NC}"
    fi
else
    echo -e "${YELLOW}⚠ Migrations skipped${NC}"
fi
echo ""

echo -e "${YELLOW}Step 7: Creating storage link...${NC}"
sudo -u $APP_USER php artisan storage:link 2>/dev/null || echo -e "${YELLOW}⚠ Storage link already exists${NC}"
echo ""

echo -e "${YELLOW}Step 8: Optimizing application...${NC}"
sudo -u $APP_USER php artisan config:cache 2>/dev/null || true
sudo -u $APP_USER php artisan route:cache 2>/dev/null || true
sudo -u $APP_USER php artisan view:cache 2>/dev/null || true
echo -e "${GREEN}✓ Application optimized${NC}"
echo ""

echo -e "${YELLOW}Step 9: Testing application...${NC}"
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8001 | grep -qE "200|301|302"; then
    echo -e "${GREEN}✓ Application is working!${NC}"
else
    echo -e "${RED}✗ Application still has errors${NC}"
    echo -e "${YELLOW}Check Laravel logs:${NC}"
    echo "  tail -f storage/logs/laravel.log"
    echo ""
    echo -e "${YELLOW}Common issues:${NC}"
    echo "1. Database connection error - Check .env DB_* settings"
    echo "2. Missing APP_KEY - Run: php artisan key:generate"
    echo "3. Permission issues - Check storage and cache folders"
    echo "4. Missing dependencies - Run: composer install"
fi
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Fix completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "If still error, check:"
echo "  tail -f /var/www/motrac/storage/logs/laravel.log"
echo "  sudo tail -f /var/log/nginx/error.log"
echo ""

