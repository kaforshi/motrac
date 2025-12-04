#!/bin/bash

# Script untuk fix IP address configuration
# Usage: sudo ./fix-ip-config.sh

set -e

echo "========================================="
echo "  Fix IP Address Configuration"
echo "========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Important: 127.0.0.0 is NOT a valid host IP!${NC}"
echo -e "${YELLOW}Use 127.0.0.1 for localhost${NC}"
echo ""

echo -e "${YELLOW}Step 1: Testing with correct IP...${NC}"
if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001 | grep -qE "200|301|302|500"; then
    echo -e "${GREEN}✓ Connection to 127.0.0.1:8001 works${NC}"
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001)
    echo -e "${GREEN}   HTTP Status: $HTTP_CODE${NC}"
else
    echo -e "${RED}✗ Connection to 127.0.0.1:8001 failed${NC}"
    echo -e "${YELLOW}   Check if Nginx is running and listening on port 8001${NC}"
fi
echo ""

echo -e "${YELLOW}Step 2: Checking Nginx configuration...${NC}"
if [ -f "/etc/nginx/sites-available/motrac" ]; then
    if grep -q "127.0.0.0" /etc/nginx/sites-available/motrac; then
        echo -e "${RED}✗ Found invalid IP 127.0.0.0 in Nginx config!${NC}"
        echo -e "${YELLOW}   Nginx config should use 'listen 8001;' not 'listen 127.0.0.0:8001;'${NC}"
        read -p "Fix Nginx config? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            sed -i 's/127\.0\.0\.0/127.0.0.1/g' /etc/nginx/sites-available/motrac
            # Better: remove IP from listen directive
            sed -i 's/listen 127\.0\.0\.1:8001;/listen 8001;/g' /etc/nginx/sites-available/motrac
            sed -i 's/listen 127\.0\.0\.0:8001;/listen 8001;/g' /etc/nginx/sites-available/motrac
            nginx -t && systemctl reload nginx
            echo -e "${GREEN}✓ Nginx config fixed${NC}"
        fi
    else
        echo -e "${GREEN}✓ Nginx config looks good${NC}"
    fi
else
    echo -e "${YELLOW}⚠ Nginx config file not found${NC}"
fi
echo ""

echo -e "${YELLOW}Step 3: Checking Cloudflare Tunnel config (if on Server 1)...${NC}"
if [ -f "/etc/cloudflared/config.yml" ]; then
    if grep -q "127.0.0.0" /etc/cloudflared/config.yml; then
        echo -e "${RED}✗ Found invalid IP 127.0.0.0 in Cloudflare Tunnel config!${NC}"
        echo -e "${YELLOW}   This needs to be fixed manually:${NC}"
        echo "   sudo nano /etc/cloudflared/config.yml"
        echo "   Change: http://127.0.0.0:8001"
        echo "   To:     http://127.0.0.1:8001"
        echo ""
        read -p "Fix Cloudflare Tunnel config automatically? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            sed -i 's/127\.0\.0\.0/127.0.0.1/g' /etc/cloudflared/config.yml
            cloudflared tunnel --config /etc/cloudflared/config.yml ingress validate
            systemctl restart cloudflared
            echo -e "${GREEN}✓ Cloudflare Tunnel config fixed${NC}"
        fi
    else
        echo -e "${GREEN}✓ Cloudflare Tunnel config looks good${NC}"
    fi
else
    echo -e "${YELLOW}⚠ Cloudflare Tunnel config not found (this is Server 2)${NC}"
fi
echo ""

echo -e "${YELLOW}Step 4: Testing connections...${NC}"
echo -e "${YELLOW}   Testing 127.0.0.1:8001...${NC}"
if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8001 | grep -qE "200|301|302|500"; then
    echo -e "${GREEN}✓ 127.0.0.1:8001 is accessible${NC}"
else
    echo -e "${RED}✗ 127.0.0.1:8001 is NOT accessible${NC}"
fi

echo -e "${YELLOW}   Testing localhost:8001...${NC}"
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8001 | grep -qE "200|301|302|500"; then
    echo -e "${GREEN}✓ localhost:8001 is accessible${NC}"
else
    echo -e "${RED}✗ localhost:8001 is NOT accessible${NC}"
fi
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  IP Configuration Check Completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "Summary:"
echo "  - Use 127.0.0.1 (NOT 127.0.0.0) for localhost"
echo "  - Use SERVER2_IP for direct connection from Server 1"
echo ""
echo "Next steps:"
echo "1. If on Server 1, update Cloudflare Tunnel config:"
echo "   sudo nano /etc/cloudflared/config.yml"
echo "   Change: http://127.0.0.0:8001 → http://127.0.0.1:8001"
echo "   Or use: http://SERVER2_IP:8001"
echo ""
echo "2. Restart Cloudflare Tunnel:"
echo "   sudo systemctl restart cloudflared"
echo ""

