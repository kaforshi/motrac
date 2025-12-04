# Quick Setup - Cloudflare Tunnel untuk 2 Server

Panduan cepat untuk setup Cloudflare Tunnel dengan 2 website di 2 server berbeda.

## 📋 Prasyarat

- Server 1: Website 1 running di port 8000
- Server 2: Motrac akan diinstall
- Domain di Cloudflare
- Akses root ke kedua server

## 🚀 Langkah Cepat

### Server 1 (Main Server - Tunnel)

#### 1. Install Cloudflare Tunnel

```bash
# Download dan install
wget https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared-linux-amd64.deb

# Login
cloudflared tunnel login

# Buat tunnel
cloudflared tunnel create motrac-tunnel
# Catat Tunnel ID yang muncul
```

#### 2. Setup Konfigurasi

```bash
sudo mkdir -p /etc/cloudflared
sudo nano /etc/cloudflared/config.yml
```

Gunakan template dari `cloudflared-config.yml.example` atau jalankan:
```bash
sudo ./setup-cloudflare-tunnel.sh
```

#### 3. Start Service

```bash
sudo systemctl enable cloudflared
sudo systemctl start cloudflared
sudo systemctl status cloudflared
```

### Server 2 (Motrac)

#### 1. Install Aplikasi

Ikuti `DEPLOYMENT.md` atau jalankan:
```bash
sudo ./setup-server.sh
sudo ./deploy.sh
```

#### 2. Setup Nginx untuk Port 8001

```bash
sudo cp nginx-motrac-port8001.conf /etc/nginx/sites-available/motrac
sudo nano /etc/nginx/sites-available/motrac
# Edit domain name

sudo ln -s /etc/nginx/sites-available/motrac /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 3. Update .env

```bash
sudo nano /var/www/motrac/.env
```

```env
APP_URL=https://motrac.yourdomain.com
```

#### 4. Firewall

```bash
sudo ufw allow 8001/tcp
```

### Cloudflare Dashboard

#### Setup DNS Records

1. Login ke Cloudflare
2. Pilih domain
3. DNS → Records → Add record

**Record 1:**
```
Type: CNAME
Name: website1
Target: YOUR_TUNNEL_ID.cfargotunnel.com
Proxy: Proxied (ON)
```

**Record 2:**
```
Type: CNAME
Name: motrac
Target: YOUR_TUNNEL_ID.cfargotunnel.com
Proxy: Proxied (ON)
```

## 🔧 Opsi: SSH Tunnel (Lebih Aman)

Jika Server 2 tidak bisa diakses langsung:

### Di Server 1

```bash
# Install autossh
sudo apt install autossh

# Setup SSH key
ssh-keygen -t rsa
ssh-copy-id user@server2-ip

# Test
ssh user@server2-ip
```

### Buat SSH Tunnel Service

```bash
sudo nano /etc/systemd/system/cloudflare-tunnel-ssh.service
```

```ini
[Unit]
Description=SSH Tunnel to Server 2
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/bin/autossh -M 0 -o "ServerAliveInterval 30" -o "ServerAliveCountMax 3" -N -L 127.0.0.1:8001:127.0.0.1:8001 user@server2-ip
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable cloudflare-tunnel-ssh
sudo systemctl start cloudflare-tunnel-ssh
```

### Update config.yml

```yaml
  - hostname: motrac.yourdomain.com
    service: http://localhost:8001
```

## ✅ Testing

```bash
# Check tunnel status
sudo systemctl status cloudflared

# View logs
sudo journalctl -u cloudflared -f

# Test dari browser
# https://website1.yourdomain.com
# https://motrac.yourdomain.com
```

## 🐛 Troubleshooting

### Tunnel tidak connect
```bash
# Validate config
sudo cloudflared tunnel --config /etc/cloudflared/config.yml ingress validate

# Test run
sudo cloudflared tunnel --config /etc/cloudflared/config.yml run
```

### Website tidak bisa diakses
1. Check DNS di Cloudflare (harus Proxied)
2. Check tunnel status
3. Check Nginx di Server 2
4. Check firewall rules

### SSH Tunnel issues
```bash
# Test SSH
ssh user@server2-ip

# Check service
sudo systemctl status cloudflare-tunnel-ssh

# View logs
sudo journalctl -u cloudflare-tunnel-ssh -f
```

## 📊 Monitoring

```bash
# Tunnel logs
sudo journalctl -u cloudflared -f

# Nginx logs (Server 2)
sudo tail -f /var/log/nginx/error.log

# App logs (Server 2)
tail -f /var/www/motrac/storage/logs/laravel.log
```

## 🔄 Update

```bash
# Update cloudflared
cloudflared update
sudo systemctl restart cloudflared
```

