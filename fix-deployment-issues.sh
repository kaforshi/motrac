#!/bin/bash

# Script untuk fix masalah deployment
# Usage: sudo ./fix-deployment-issues.sh

set -e

echo "========================================="
echo "  Fix Deployment Issues"
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

echo -e "${YELLOW}Step 1: Fixing Git ownership...${NC}"
if [ -d ".git" ]; then
    # Add safe directory
    git config --global --add safe.directory $APP_DIR
    
    # Fix ownership
    chown -R $APP_USER:$APP_USER .git
    chown -R $APP_USER:$APP_USER .
    
    echo -e "${GREEN}✓ Git ownership fixed${NC}"
else
    echo -e "${YELLOW}⚠ No .git directory found${NC}"
fi
echo ""

echo -e "${YELLOW}Step 2: Fixing Composer lock file...${NC}"
if [ -f "composer.lock" ]; then
    echo -e "${YELLOW}Backing up composer.lock...${NC}"
    cp composer.lock composer.lock.backup.$(date +%Y%m%d_%H%M%S) || true
    
    echo -e "${YELLOW}Removing outdated composer.lock...${NC}"
    rm -f composer.lock
    
    echo -e "${YELLOW}Regenerating composer.lock from composer.json...${NC}"
    # Clear composer cache first
    sudo -u $APP_USER composer clear-cache
    
    # Update to regenerate lock file
    sudo -u $APP_USER composer update --no-dev --no-interaction
    
    echo -e "${GREEN}✓ Composer lock file regenerated${NC}"
else
    echo -e "${YELLOW}⚠ No composer.lock found, will be created on install${NC}"
fi
echo ""

echo -e "${YELLOW}Step 3: Installing dependencies...${NC}"
sudo -u $APP_USER composer install --optimize-autoloader --no-dev --no-interaction
echo -e "${GREEN}✓ Dependencies installed${NC}"
echo ""

echo -e "${YELLOW}Step 4: Fixing file permissions...${NC}"
chown -R $APP_USER:$APP_USER $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Issues fixed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "You can now run: sudo ./deploy.sh"
echo ""

