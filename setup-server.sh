#!/bin/bash

# Script untuk setup awal server Linux untuk Motrac
# Usage: sudo ./setup-server.sh

set -e

echo "========================================="
echo "  Motrac Server Setup Script"
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

echo -e "${YELLOW}Step 1: Updating system packages...${NC}"
apt update && apt upgrade -y
echo -e "${GREEN}✓ System updated${NC}"
echo ""

echo -e "${YELLOW}Step 2: Installing PHP and extensions...${NC}"
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-mysql \
    php8.2-zip \
    php8.2-gd \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-xml \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-readline

echo -e "${GREEN}✓ PHP installed${NC}"
echo ""

echo -e "${YELLOW}Step 3: Installing Composer...${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    echo -e "${GREEN}✓ Composer installed${NC}"
else
    echo -e "${YELLOW}⚠ Composer already installed${NC}"
fi
echo ""

echo -e "${YELLOW}Step 4: Installing Nginx...${NC}"
apt install -y nginx
echo -e "${GREEN}✓ Nginx installed${NC}"
echo ""

echo -e "${YELLOW}Step 5: Installing MySQL...${NC}"
apt install -y mysql-server
echo -e "${GREEN}✓ MySQL installed${NC}"
echo -e "${YELLOW}⚠ Please run 'sudo mysql_secure_installation' after this script${NC}"
echo ""

echo -e "${YELLOW}Step 6: Installing Git...${NC}"
apt install -y git
echo -e "${GREEN}✓ Git installed${NC}"
echo ""

echo -e "${YELLOW}Step 7: Installing Node.js and NPM...${NC}"
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs
echo -e "${GREEN}✓ Node.js installed${NC}"
echo ""

echo -e "${YELLOW}Step 8: Installing Certbot (for SSL)...${NC}"
apt install -y certbot python3-certbot-nginx
echo -e "${GREEN}✓ Certbot installed${NC}"
echo ""

echo -e "${YELLOW}Step 9: Configuring firewall...${NC}"
ufw allow 'Nginx Full'
ufw allow OpenSSH
echo -e "${YELLOW}⚠ Firewall will be enabled. Make sure SSH is allowed!${NC}"
read -p "Enable firewall now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    ufw --force enable
    echo -e "${GREEN}✓ Firewall enabled${NC}"
else
    echo -e "${YELLOW}⚠ Firewall not enabled. Run 'sudo ufw enable' later.${NC}"
fi
echo ""

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Server setup completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Run: sudo mysql_secure_installation"
echo "2. Create database and user:"
echo "   sudo mysql -u root -p"
echo "   CREATE DATABASE motrac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "   CREATE USER 'motrac_user'@'localhost' IDENTIFIED BY 'your_password';"
echo "   GRANT ALL PRIVILEGES ON motrac.* TO 'motrac_user'@'localhost';"
echo "   FLUSH PRIVILEGES;"
echo "3. Upload your application files to /var/www/motrac"
echo "4. Run: sudo ./deploy.sh"
echo ""

