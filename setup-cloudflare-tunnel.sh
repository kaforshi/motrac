#!/bin/bash

# Script untuk setup Cloudflare Tunnel di Server 1
# Usage: sudo ./setup-cloudflare-tunnel.sh

set -e

echo "========================================="
echo "  Cloudflare Tunnel Setup Script"
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

echo -e "${YELLOW}Step 1: Installing cloudflared...${NC}"

# Check if cloudflared is already installed
if command -v cloudflared &> /dev/null; then
    echo -e "${YELLOW}⚠ cloudflared is already installed${NC}"
    read -p "Update to latest version? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        cloudflared update
    fi
else
    # Download and install cloudflared
    ARCH=$(uname -m)
    if [ "$ARCH" = "x86_64" ]; then
        ARCH="amd64"
    elif [ "$ARCH" = "aarch64" ]; then
        ARCH="arm64"
    fi

    wget -q https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-${ARCH}.deb -O /tmp/cloudflared.deb
    dpkg -i /tmp/cloudflared.deb || apt-get install -f -y
    rm /tmp/cloudflared.deb
    
    echo -e "${GREEN}✓ cloudflared installed${NC}"
fi

echo ""

echo -e "${YELLOW}Step 2: Login to Cloudflare...${NC}"
echo -e "${YELLOW}This will open a browser for authentication${NC}"
read -p "Continue? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    cloudflared tunnel login
    echo -e "${GREEN}✓ Logged in to Cloudflare${NC}"
else
    echo -e "${YELLOW}⚠ Skipping login. Run 'cloudflared tunnel login' manually${NC}"
fi

echo ""

echo -e "${YELLOW}Step 3: Creating tunnel...${NC}"
read -p "Enter tunnel name (default: motrac-tunnel): " TUNNEL_NAME
TUNNEL_NAME=${TUNNEL_NAME:-motrac-tunnel}

TUNNEL_OUTPUT=$(cloudflared tunnel create "$TUNNEL_NAME" 2>&1)
TUNNEL_ID=$(echo "$TUNNEL_OUTPUT" | grep -oP '(?<=Created tunnel )[a-f0-9-]+' || echo "")

if [ -z "$TUNNEL_ID" ]; then
    echo -e "${YELLOW}⚠ Could not extract tunnel ID. Please create manually:${NC}"
    echo "   cloudflared tunnel create $TUNNEL_NAME"
    echo "   Then run: cloudflared tunnel list"
    read -p "Enter tunnel ID: " TUNNEL_ID
else
    echo -e "${GREEN}✓ Tunnel created: $TUNNEL_ID${NC}"
fi

echo ""

echo -e "${YELLOW}Step 4: Setting up configuration...${NC}"

# Create config directory
mkdir -p /etc/cloudflared

# Ask for configuration details
read -p "Enter domain for website 1 (e.g., website1.yourdomain.com): " DOMAIN1
read -p "Enter domain for Motrac (e.g., motrac.yourdomain.com): " DOMAIN2
read -p "Enter Server 2 IP address: " SERVER2_IP
read -p "Use SSH tunnel? (y/n) " -n 1 -r
echo
USE_SSH=$REPLY

# Create config file
cat > /etc/cloudflared/config.yml <<EOF
tunnel: $TUNNEL_ID
credentials-file: /root/.cloudflared/$TUNNEL_ID.json

ingress:
  # Website 1 - Server 1 (Port 8000)
  - hostname: $DOMAIN1
    service: http://localhost:8000
  
  # Motrac - Server 2
EOF

if [[ $USE_SSH =~ ^[Yy]$ ]]; then
    read -p "Enter SSH user for Server 2: " SSH_USER
    read -p "Enter local port for SSH tunnel (default: 8001): " LOCAL_PORT
    LOCAL_PORT=${LOCAL_PORT:-8001}
    
    cat >> /etc/cloudflared/config.yml <<EOF
  - hostname: $DOMAIN2
    service: http://localhost:$LOCAL_PORT
    originRequest:
      httpHostHeader: $DOMAIN2
      connectTimeout: 10s
      tcpKeepAlive: 30s
      http2Origin: true
EOF
else
    cat >> /etc/cloudflared/config.yml <<EOF
  - hostname: $DOMAIN2
    service: http://$SERVER2_IP:8001
EOF
fi

cat >> /etc/cloudflared/config.yml <<EOF
  
  # Catch-all rule
  - service: http_status:404
EOF

echo -e "${GREEN}✓ Configuration created${NC}"
echo ""

# Validate config
echo -e "${YELLOW}Step 5: Validating configuration...${NC}"
cloudflared tunnel --config /etc/cloudflared/config.yml ingress validate
echo -e "${GREEN}✓ Configuration valid${NC}"
echo ""

echo -e "${YELLOW}Step 6: Setting up systemd service...${NC}"

# Create systemd service
cat > /etc/systemd/system/cloudflared.service <<EOF
[Unit]
Description=cloudflared
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/local/bin/cloudflared tunnel --config /etc/cloudflared/config.yml run
Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable cloudflared
systemctl start cloudflared

echo -e "${GREEN}✓ Service started${NC}"
echo ""

# Check status
echo -e "${YELLOW}Step 7: Checking service status...${NC}"
systemctl status cloudflared --no-pager -l
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Cloudflare Tunnel setup completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Configure DNS records in Cloudflare:"
echo "   - $DOMAIN1 → CNAME → $TUNNEL_ID.cfargotunnel.com (Proxied)"
echo "   - $DOMAIN2 → CNAME → $TUNNEL_ID.cfargotunnel.com (Proxied)"
echo ""
if [[ $USE_SSH =~ ^[Yy]$ ]]; then
    echo "2. Setup SSH tunnel (see CLOUDFLARE_TUNNEL_SETUP.md)"
fi
echo "3. Test access:"
echo "   - https://$DOMAIN1"
echo "   - https://$DOMAIN2"
echo ""
echo "View logs: sudo journalctl -u cloudflared -f"
echo ""

