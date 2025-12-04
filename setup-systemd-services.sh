#!/bin/bash

# Script untuk setup systemd services untuk Motrac
# Usage: sudo ./setup-systemd-services.sh

set -e

echo "========================================="
echo "  Setup Systemd Services for Motrac"
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

if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}Application directory $APP_DIR does not exist!${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Installing Queue Worker service...${NC}"

# Copy queue worker service
if [ -f "$APP_DIR/motrac-worker.service" ]; then
    cp "$APP_DIR/motrac-worker.service" /etc/systemd/system/
    echo -e "${GREEN}✓ Queue worker service file copied${NC}"
else
    echo -e "${RED}✗ motrac-worker.service not found in $APP_DIR${NC}"
    exit 1
fi

echo ""

echo -e "${YELLOW}Step 2: Installing Scheduler service...${NC}"

# Copy scheduler service
if [ -f "$APP_DIR/motrac-scheduler.service" ]; then
    cp "$APP_DIR/motrac-scheduler.service" /etc/systemd/system/
    echo -e "${GREEN}✓ Scheduler service file copied${NC}"
else
    echo -e "${YELLOW}⚠ motrac-scheduler.service not found, skipping...${NC}"
fi

echo ""

echo -e "${YELLOW}Step 3: Reloading systemd...${NC}"
systemctl daemon-reload
echo -e "${GREEN}✓ Systemd reloaded${NC}"
echo ""

echo -e "${YELLOW}Step 4: Enabling services...${NC}"

# Enable and start queue worker
if systemctl enable motrac-worker.service; then
    echo -e "${GREEN}✓ Queue worker service enabled${NC}"
    
    read -p "Start queue worker service now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        systemctl start motrac-worker.service
        echo -e "${GREEN}✓ Queue worker service started${NC}"
    fi
else
    echo -e "${RED}✗ Failed to enable queue worker service${NC}"
fi

# Enable and start scheduler (optional)
if [ -f "/etc/systemd/system/motrac-scheduler.service" ]; then
    read -p "Enable scheduler service (alternative to cron)? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        if systemctl enable motrac-scheduler.service; then
            echo -e "${GREEN}✓ Scheduler service enabled${NC}"
            
            read -p "Start scheduler service now? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                systemctl start motrac-scheduler.service
                echo -e "${GREEN}✓ Scheduler service started${NC}"
            fi
        fi
    fi
fi

echo ""

echo -e "${YELLOW}Step 5: Checking service status...${NC}"
echo ""
echo -e "${GREEN}Queue Worker Status:${NC}"
systemctl status motrac-worker.service --no-pager -l || true
echo ""

if systemctl is-enabled motrac-scheduler.service &>/dev/null; then
    echo -e "${GREEN}Scheduler Status:${NC}"
    systemctl status motrac-scheduler.service --no-pager -l || true
    echo ""
fi

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Systemd services setup completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "Useful commands:"
echo "  sudo systemctl status motrac-worker"
echo "  sudo systemctl restart motrac-worker"
echo "  sudo systemctl stop motrac-worker"
echo "  sudo systemctl start motrac-worker"
echo "  sudo journalctl -u motrac-worker -f"
echo ""
if systemctl is-enabled motrac-scheduler.service &>/dev/null; then
    echo "  sudo systemctl status motrac-scheduler"
    echo "  sudo systemctl restart motrac-scheduler"
    echo "  sudo journalctl -u motrac-scheduler -f"
    echo ""
fi
echo "Note: If using scheduler service, you can remove cron job:"
echo "  sudo crontab -e -u www-data"
echo "  (Remove the schedule:run line)"
echo ""

