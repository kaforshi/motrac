#!/bin/bash

# Script Deployment Otomatis untuk Motrac
# Usage: sudo ./deploy.sh

set -e

echo "========================================="
echo "  Motrac Deployment Script"
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

# Configuration
APP_DIR="/var/www/motrac"
APP_USER="www-data"
PHP_VERSION="8.2"

echo -e "${YELLOW}Step 1: Checking prerequisites...${NC}"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP first.${NC}"
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Composer is not installed. Please install Composer first.${NC}"
    exit 1
fi

# Check if Nginx is installed
if ! command -v nginx &> /dev/null; then
    echo -e "${RED}Nginx is not installed. Please install Nginx first.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Prerequisites check passed${NC}"
echo ""

# Check if app directory exists
if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}Application directory $APP_DIR does not exist!${NC}"
    echo "Please create the directory and upload your files first."
    exit 1
fi

cd $APP_DIR

echo -e "${YELLOW}Step 2: Installing/Updating dependencies...${NC}"
sudo -u $APP_USER composer install --optimize-autoloader --no-dev --no-interaction
echo -e "${GREEN}✓ Dependencies installed${NC}"
echo ""

echo -e "${YELLOW}Step 3: Building assets...${NC}"
if [ -f "package.json" ]; then
    sudo -u $APP_USER npm install --production
    sudo -u $APP_USER npm run build
    echo -e "${GREEN}✓ Assets built${NC}"
else
    echo -e "${YELLOW}⚠ No package.json found, skipping asset build${NC}"
fi
echo ""

echo -e "${YELLOW}Step 4: Setting up environment...${NC}"
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ .env file created from .env.example${NC}"
        echo -e "${YELLOW}⚠ Please edit .env file with your configuration!${NC}"
    else
        echo -e "${RED}.env.example not found!${NC}"
        exit 1
    fi
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    sudo -u $APP_USER php artisan key:generate --force
    echo -e "${GREEN}✓ Application key generated${NC}"
fi
echo ""

echo -e "${YELLOW}Step 5: Running migrations...${NC}"
read -p "Run database migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    sudo -u $APP_USER php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${YELLOW}⚠ Migrations skipped${NC}"
fi
echo ""

echo -e "${YELLOW}Step 6: Creating storage link...${NC}"
sudo -u $APP_USER php artisan storage:link
echo -e "${GREEN}✓ Storage link created${NC}"
echo ""

echo -e "${YELLOW}Step 7: Setting permissions...${NC}"
chown -R $APP_USER:$APP_USER $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

echo -e "${YELLOW}Step 8: Optimizing application...${NC}"
sudo -u $APP_USER php artisan config:cache
sudo -u $APP_USER php artisan route:cache
sudo -u $APP_USER php artisan view:cache
echo -e "${GREEN}✓ Application optimized${NC}"
echo ""

echo -e "${YELLOW}Step 9: Restarting services...${NC}"
systemctl restart php${PHP_VERSION}-fpm
systemctl restart nginx
echo -e "${GREEN}✓ Services restarted${NC}"
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Deployment completed successfully!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Configure your domain in Nginx"
echo "2. Setup SSL certificate (Let's Encrypt)"
echo "3. Setup cron job for scheduled tasks"
echo "4. Configure queue worker (if using queues)"
echo ""
echo "See DEPLOYMENT.md for detailed instructions."

