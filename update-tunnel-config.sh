#!/bin/bash

# Script untuk menambahkan Motrac ke Cloudflare Tunnel yang sudah ada
# Usage: sudo ./update-tunnel-config.sh

set -e

echo "========================================="
echo "  Update Cloudflare Tunnel Config"
echo "  Add Motrac to Existing Tunnel"
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

# Find config file
CONFIG_FILE="/etc/cloudflared/config.yml"
if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${YELLOW}Config file not found at $CONFIG_FILE${NC}"
    read -p "Enter config file path: " CONFIG_FILE
    if [ ! -f "$CONFIG_FILE" ]; then
        echo -e "${RED}Config file not found!${NC}"
        exit 1
    fi
fi

echo -e "${YELLOW}Found config file: $CONFIG_FILE${NC}"

# Backup config
BACKUP_FILE="${CONFIG_FILE}.backup.$(date +%Y%m%d_%H%M%S)"
cp "$CONFIG_FILE" "$BACKUP_FILE"
echo -e "${GREEN}✓ Backup created: $BACKUP_FILE${NC}"
echo ""

# Get current tunnel ID
TUNNEL_ID=$(grep -E "^tunnel:" "$CONFIG_FILE" | awk '{print $2}' | tr -d '"')
if [ -z "$TUNNEL_ID" ]; then
    echo -e "${RED}Could not find tunnel ID in config!${NC}"
    exit 1
fi

echo -e "${GREEN}Current Tunnel ID: $TUNNEL_ID${NC}"
echo ""

# Get information
read -p "Enter domain for Motrac (e.g., motrac.yourdomain.com): " MOTRAC_DOMAIN
read -p "Enter Server 2 IP address: " SERVER2_IP
read -p "Use SSH tunnel? (y/n) " -n 1 -r
echo
USE_SSH=$REPLY

# Check if Motrac route already exists
if grep -q "hostname: $MOTRAC_DOMAIN" "$CONFIG_FILE"; then
    echo -e "${YELLOW}⚠ Route for $MOTRAC_DOMAIN already exists!${NC}"
    read -p "Overwrite? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Cancelled${NC}"
        exit 0
    fi
    # Remove existing route
    sed -i "/hostname: $MOTRAC_DOMAIN/,/service:/d" "$CONFIG_FILE"
fi

# Create temp file
TEMP_FILE=$(mktemp)

# Read config and add Motrac route before catch-all
while IFS= read -r line; do
    if [[ "$line" =~ ^[[:space:]]*-[[:space:]]*service:[[:space:]]*http_status:404 ]]; then
        # Add Motrac route before catch-all
        if [[ $USE_SSH =~ ^[Yy]$ ]]; then
            read -p "Enter SSH user for Server 2: " SSH_USER
            read -p "Enter local port for SSH tunnel (default: 8001): " LOCAL_PORT
            LOCAL_PORT=${LOCAL_PORT:-8001}
            
            cat >> "$TEMP_FILE" <<EOF
  # Motrac - Server 2 (via SSH tunnel)
  - hostname: $MOTRAC_DOMAIN
    service: http://localhost:$LOCAL_PORT
    originRequest:
      httpHostHeader: $MOTRAC_DOMAIN
      connectTimeout: 10s
      tcpKeepAlive: 30s
      http2Origin: true

EOF
        else
            cat >> "$TEMP_FILE" <<EOF
  # Motrac - Server 2 (direct)
  - hostname: $MOTRAC_DOMAIN
    service: http://$SERVER2_IP:8001

EOF
        fi
    fi
    echo "$line" >> "$TEMP_FILE"
done < "$CONFIG_FILE"

# Replace original with temp
mv "$TEMP_FILE" "$CONFIG_FILE"

echo -e "${GREEN}✓ Config updated${NC}"
echo ""

# Validate config
echo -e "${YELLOW}Validating configuration...${NC}"
if cloudflared tunnel --config "$CONFIG_FILE" ingress validate; then
    echo -e "${GREEN}✓ Configuration is valid${NC}"
else
    echo -e "${RED}✗ Configuration validation failed!${NC}"
    echo -e "${YELLOW}Restoring backup...${NC}"
    cp "$BACKUP_FILE" "$CONFIG_FILE"
    exit 1
fi

echo ""

# Ask to restart
read -p "Restart cloudflared service now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    systemctl restart cloudflared
    sleep 2
    systemctl status cloudflared --no-pager -l
    echo -e "${GREEN}✓ Service restarted${NC}"
else
    echo -e "${YELLOW}⚠ Remember to restart: sudo systemctl restart cloudflared${NC}"
fi

echo ""
echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Config update completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Setup DNS record in Cloudflare:"
echo "   Type: CNAME"
echo "   Name: motrac"
echo "   Target: $TUNNEL_ID.cfargotunnel.com"
echo "   Proxy: Proxied (ON)"
echo ""
if [[ $USE_SSH =~ ^[Yy]$ ]]; then
    echo "2. Setup SSH tunnel service (see ADD_MOTRAC_TO_EXISTING_TUNNEL.md)"
fi
echo "3. Setup Motrac on Server 2 (see DEPLOYMENT.md)"
echo "4. Test: https://$MOTRAC_DOMAIN"
echo ""

