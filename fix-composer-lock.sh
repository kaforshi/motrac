#!/bin/bash

# Script khusus untuk fix composer.lock issue
# Usage: sudo ./fix-composer-lock.sh

set -e

echo "========================================="
echo "  Fix Composer Lock File"
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

echo -e "${YELLOW}Step 1: Checking composer.json...${NC}"
if [ ! -f "composer.json" ]; then
    echo -e "${RED}composer.json not found!${NC}"
    exit 1
fi

# Show current requirements
echo -e "${GREEN}Current requirements in composer.json:${NC}"
grep -E "laravel/framework|laravel/sanctum" composer.json || true
echo ""

echo -e "${YELLOW}Step 2: Backing up composer.lock...${NC}"
if [ -f "composer.lock" ]; then
    BACKUP_FILE="composer.lock.backup.$(date +%Y%m%d_%H%M%S)"
    cp composer.lock "$BACKUP_FILE"
    echo -e "${GREEN}✓ Backup created: $BACKUP_FILE${NC}"
    
    # Show what's in lock file
    echo -e "${YELLOW}Current versions in composer.lock:${NC}"
    grep -A 2 '"name": "laravel/framework"' composer.lock | grep '"version"' || true
    grep -A 2 '"name": "laravel/sanctum"' composer.lock | grep '"version"' || true
    echo ""
else
    echo -e "${YELLOW}⚠ No composer.lock found${NC}"
fi

echo -e "${YELLOW}Step 3: Removing outdated composer.lock...${NC}"
rm -f composer.lock
echo -e "${GREEN}✓ Removed${NC}"
echo ""

echo -e "${YELLOW}Step 4: Clearing Composer cache...${NC}"
sudo -u $APP_USER composer clear-cache
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

echo -e "${YELLOW}Step 5: Updating dependencies to regenerate lock file...${NC}"
echo -e "${YELLOW}This may take a few minutes...${NC}"
sudo -u $APP_USER composer update --no-dev --no-interaction
echo -e "${GREEN}✓ Lock file regenerated${NC}"
echo ""

echo -e "${YELLOW}Step 6: Verifying new lock file...${NC}"
if [ -f "composer.lock" ]; then
    echo -e "${GREEN}New versions in composer.lock:${NC}"
    grep -A 2 '"name": "laravel/framework"' composer.lock | grep '"version"' || true
    grep -A 2 '"name": "laravel/sanctum"' composer.lock | grep '"version"' || true
    echo ""
else
    echo -e "${RED}✗ composer.lock was not created!${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 7: Installing dependencies...${NC}"
sudo -u $APP_USER composer install --optimize-autoloader --no-dev --no-interaction
echo -e "${GREEN}✓ Dependencies installed${NC}"
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Composer lock file fixed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "You can now continue with deployment:"
echo "  sudo ./deploy.sh"
echo ""

