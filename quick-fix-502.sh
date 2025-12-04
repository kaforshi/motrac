#!/bin/bash

# Quick Fix Script untuk 502 Bad Gateway Error
# Usage: sudo ./quick-fix-502.sh

set -e

echo "========================================="
echo "  Quick Fix 502 Bad Gateway Error"
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

echo -e "${YELLOW}Step 1: Starting services...${NC}"
systemctl start nginx || true
systemctl start php8.2-fpm || systemctl start php-fpm || true
systemctl start mysql || true
echo -e "${GREEN}✓ Services started${NC}"
echo ""

echo -e "${YELLOW}Step 2: Checking service status...${NC}"
echo "Nginx:"
systemctl is-active nginx && echo -e "${GREEN}✓ Running${NC}" || echo -e "${RED}✗ Not running${NC}"

PHP_SERVICE="php8.2-fpm"
if ! systemctl is-active $PHP_SERVICE &>/dev/null; then
    PHP_SERVICE="php-fpm"
fi
echo "PHP-FPM:"
systemctl is-active $PHP_SERVICE && echo -e "${GREEN}✓ Running${NC}" || echo -e "${RED}✗ Not running${NC}"

echo "MySQL:"
systemctl is-active mysql && echo -e "${GREEN}✓ Running${NC}" || echo -e "${RED}✗ Not running${NC}"
echo ""

echo -e "${YELLOW}Step 3: Checking Nginx configuration...${NC}"
if nginx -t 2>&1 | grep -q "successful"; then
    echo -e "${GREEN}✓ Nginx config is valid${NC}"
    systemctl reload nginx
else
    echo -e "${RED}✗ Nginx config has errors:${NC}"
    nginx -t
    exit 1
fi
echo ""

echo -e "${YELLOW}Step 4: Checking port 8001...${NC}"
if netstat -tlnp 2>/dev/null | grep -q ":8001" || ss -tlnp 2>/dev/null | grep -q ":8001"; then
    echo -e "${GREEN}✓ Port 8001 is listening${NC}"
    netstat -tlnp 2>/dev/null | grep ":8001" || ss -tlnp 2>/dev/null | grep ":8001"
else
    echo -e "${RED}✗ Port 8001 is NOT listening${NC}"
    echo -e "${YELLOW}Checking Nginx config...${NC}"
    if [ -f "/etc/nginx/sites-available/motrac" ]; then
        grep -q "listen 8001" /etc/nginx/sites-available/motrac && echo -e "${GREEN}✓ Config has listen 8001${NC}" || echo -e "${RED}✗ Config missing listen 8001${NC}"
        if [ ! -L "/etc/nginx/sites-enabled/motrac" ]; then
            echo -e "${YELLOW}⚠ Site not enabled, enabling...${NC}"
            ln -s /etc/nginx/sites-available/motrac /etc/nginx/sites-enabled/
            systemctl reload nginx
        fi
    else
        echo -e "${RED}✗ Nginx config file not found!${NC}"
    fi
fi
echo ""

if [ -d "$APP_DIR" ]; then
    cd $APP_DIR
    
    echo -e "${YELLOW}Step 5: Fixing permissions...${NC}"
    chown -R www-data:www-data .
    chmod -R 755 .
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    echo -e "${GREEN}✓ Permissions fixed${NC}"
    echo ""
    
    echo -e "${YELLOW}Step 6: Clearing Laravel cache...${NC}"
    if [ -f "artisan" ]; then
        sudo -u www-data php artisan config:clear 2>/dev/null || true
        sudo -u www-data php artisan cache:clear 2>/dev/null || true
        sudo -u www-data php artisan view:clear 2>/dev/null || true
        echo -e "${GREEN}✓ Cache cleared${NC}"
    else
        echo -e "${YELLOW}⚠ artisan not found, skipping${NC}"
    fi
    echo ""
    
    echo -e "${YELLOW}Step 7: Testing local connection...${NC}"
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:8001 | grep -qE "200|301|302"; then
        echo -e "${GREEN}✓ Local connection works${NC}"
    else
        echo -e "${RED}✗ Local connection failed${NC}"
        echo -e "${YELLOW}Response:${NC}"
        curl -I http://localhost:8001 2>&1 | head -5
    fi
    echo ""
else
    echo -e "${YELLOW}⚠ Application directory not found, skipping app checks${NC}"
fi

echo -e "${YELLOW}Step 8: Checking PHP-FPM socket...${NC}"
PHP_SOCKET=$(find /var/run/php -name "*.sock" 2>/dev/null | head -1)
if [ -n "$PHP_SOCKET" ]; then
    echo -e "${GREEN}✓ PHP-FPM socket found: $PHP_SOCKET${NC}"
    if [ -f "/etc/nginx/sites-available/motrac" ]; then
        if grep -q "$PHP_SOCKET" /etc/nginx/sites-available/motrac; then
            echo -e "${GREEN}✓ Nginx config uses correct socket${NC}"
        else
            echo -e "${YELLOW}⚠ Nginx config may need socket update${NC}"
            echo -e "${YELLOW}Current socket in config:${NC}"
            grep "fastcgi_pass" /etc/nginx/sites-available/motrac || echo "Not found"
        fi
    fi
else
    echo -e "${RED}✗ PHP-FPM socket not found!${NC}"
    echo -e "${YELLOW}Check PHP-FPM service:${NC}"
    systemctl status $PHP_SERVICE --no-pager -l | head -10
fi
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Quick Fix Completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Test from browser: https://motrac.aryaintaran.dev"
echo "2. Check logs if still error:"
echo "   sudo tail -f /var/log/nginx/error.log"
echo "   tail -f $APP_DIR/storage/logs/laravel.log"
echo "3. Test from Server 1:"
echo "   curl http://SERVER2_IP:8001"
echo ""

