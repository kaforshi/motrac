# Setup Cloudflare Tunnel untuk Multiple Servers

Panduan untuk menjalankan 2 website di 2 server Linux menggunakan Cloudflare Tunnel.

## Arsitektur

```
Internet → Cloudflare → Cloudflare Tunnel → Server 1 (Port 8000 - Website 1)
                                    └──→ Server 2 (Port 8001 - Motrac)
```

## Prasyarat

- Akun Cloudflare dengan domain yang sudah terhubung
- Akses ke 2 server Linux
- Port yang berbeda untuk setiap aplikasi

## 1. Setup Cloudflare Tunnel

### Install cloudflared di Server 1 (Main Server)

```bash
# Download cloudflared
wget https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb

# Install
sudo dpkg -i cloudflared-linux-amd64.deb

# Verify installation
cloudflared --version
```

### Login ke Cloudflare

```bash
cloudflared tunnel login
```

Ini akan membuka browser untuk autentikasi. Setelah login, file `~/.cloudflared/cert.pem` akan dibuat.

### Buat Tunnel

```bash
# Buat tunnel baru
cloudflared tunnel create motrac-tunnel

# List tunnels
cloudflared tunnel list
```

Catat Tunnel ID yang dibuat.

## 2. Konfigurasi Tunnel

### Buat file konfigurasi

```bash
sudo mkdir -p /etc/cloudflared
sudo nano /etc/cloudflared/config.yml
```

### Konfigurasi untuk Multiple Backends

```yaml
tunnel: YOUR_TUNNEL_ID
credentials-file: /root/.cloudflared/YOUR_TUNNEL_ID.json

ingress:
  # Website 1 - Server 1 (Port 8000)
  - hostname: website1.yourdomain.com
    service: http://localhost:8000
  
  # Motrac - Server 2 (Port 8001 via SSH tunnel atau direct)
  - hostname: motrac.yourdomain.com
    service: ssh://user@server2-ip:22
    originRequest:
      noHappyEyeballs: true
      tcpKeepAlive: 30
      keepAliveTimeout: 90
      keepAliveConnections: 100
      httpHostHeader: motrac.yourdomain.com
      originServerName: motrac.yourdomain.com
      connectTimeout: 10s
      tcpKeepAlive: 30s
      noHappyEyeballs: false
      disableChunkedEncoding: false
      http2Origin: true
      proxyAddress: 127.0.0.1
      proxyPort: 0
      proxyType: ""
      ipRules: []
      access:
        required: false
        teamName: ""
        audTag: []
        tags: []
  
  # Catch-all rule (harus di akhir)
  - service: http_status:404
```

**Atau jika Server 2 bisa diakses langsung:**

```yaml
tunnel: YOUR_TUNNEL_ID
credentials-file: /root/.cloudflared/YOUR_TUNNEL_ID.json

ingress:
  # Website 1 - Server 1
  - hostname: website1.yourdomain.com
    service: http://localhost:8000
  
  # Motrac - Server 2 (Direct connection)
  - hostname: motrac.yourdomain.com
    service: http://server2-ip:8001
  
  # Catch-all
  - service: http_status:404
```

## 3. Setup Motrac di Server 2

### Install dan Setup Aplikasi

Ikuti langkah-langkah di `DEPLOYMENT.md` untuk setup Motrac di Server 2.

### Konfigurasi Nginx untuk Port 8001

```bash
sudo nano /etc/nginx/sites-available/motrac
```

```nginx
server {
    listen 8001;
    listen [::]:8001;
    server_name motrac.yourdomain.com;
    root /var/www/motrac/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/motrac /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Konfigurasi Firewall

```bash
# Allow port 8001 (jika menggunakan direct connection)
sudo ufw allow 8001/tcp

# Atau allow SSH (jika menggunakan SSH tunnel)
sudo ufw allow 22/tcp
```

### Update .env

```bash
sudo nano /var/www/motrac/.env
```

```env
APP_URL=https://motrac.yourdomain.com
```

## 4. Setup SSH Tunnel (Opsional - untuk keamanan lebih)

Jika Server 2 tidak bisa diakses langsung dari internet, gunakan SSH tunnel.

### Di Server 1, buat SSH tunnel

```bash
# Install autossh untuk auto-reconnect
sudo apt install autossh

# Buat SSH key pair (jika belum ada)
ssh-keygen -t rsa -b 4096

# Copy public key ke Server 2
ssh-copy-id user@server2-ip

# Test connection
ssh user@server2-ip

# Buat systemd service untuk SSH tunnel
sudo nano /etc/systemd/system/cloudflare-tunnel-ssh.service
```

```ini
[Unit]
Description=SSH Tunnel to Server 2 for Cloudflare
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
sudo systemctl enable cloudflare-tunnel-ssh.service
sudo systemctl start cloudflare-tunnel-ssh.service
```

### Update config.yml untuk menggunakan localhost

```yaml
  - hostname: motrac.yourdomain.com
    service: http://localhost:8001
```

## 5. Setup Cloudflare Tunnel Service

### Install sebagai systemd service

```bash
sudo cloudflared service install
```

### Edit service file

```bash
sudo nano /etc/systemd/system/cloudflared.service
```

```ini
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
```

### Start service

```bash
sudo systemctl daemon-reload
sudo systemctl enable cloudflared
sudo systemctl start cloudflared
sudo systemctl status cloudflared
```

## 6. Konfigurasi DNS di Cloudflare

### Setup DNS Records

1. Login ke Cloudflare Dashboard
2. Pilih domain Anda
3. Go to DNS → Records
4. Tambahkan records:

```
Type: CNAME
Name: website1
Target: YOUR_TUNNEL_ID.cfargotunnel.com
Proxy: Proxied (orange cloud)

Type: CNAME
Name: motrac
Target: YOUR_TUNNEL_ID.cfargotunnel.com
Proxy: Proxied (orange cloud)
```

## 7. Testing

### Test Tunnel

```bash
# Check tunnel status
sudo systemctl status cloudflared

# Check logs
sudo journalctl -u cloudflared -f

# Test connection
curl -I https://motrac.yourdomain.com
curl -I https://website1.yourdomain.com
```

### Test dari Browser

1. Buka `https://motrac.yourdomain.com`
2. Buka `https://website1.yourdomain.com`
3. Pastikan kedua website bisa diakses

## 8. Monitoring

### View Logs

```bash
# Cloudflare Tunnel logs
sudo journalctl -u cloudflared -f

# Nginx logs (Server 2)
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# Application logs (Server 2)
tail -f /var/www/motrac/storage/logs/laravel.log
```

## Troubleshooting

### Tunnel tidak connect

```bash
# Check config
sudo cloudflared tunnel --config /etc/cloudflared/config.yml ingress validate

# Test tunnel
sudo cloudflared tunnel --config /etc/cloudflared/config.yml run
```

### Website tidak bisa diakses

1. Check DNS records di Cloudflare
2. Check tunnel status: `sudo systemctl status cloudflared`
3. Check Nginx di Server 2: `sudo systemctl status nginx`
4. Check firewall rules
5. Check logs untuk error messages

### SSH Tunnel tidak connect

```bash
# Test SSH connection
ssh user@server2-ip

# Check SSH tunnel service
sudo systemctl status cloudflare-tunnel-ssh.service

# View SSH tunnel logs
sudo journalctl -u cloudflare-tunnel-ssh.service -f
```

## Security Best Practices

1. **Gunakan SSH Tunnel** untuk Server 2 jika memungkinkan
2. **Firewall Rules**: Hanya allow port yang diperlukan
3. **SSH Key Authentication**: Disable password authentication
4. **Regular Updates**: Update cloudflared dan sistem secara berkala
5. **Monitor Logs**: Regularly check logs untuk suspicious activity

## Update Tunnel

```bash
# Update cloudflared
sudo cloudflared update

# Restart service
sudo systemctl restart cloudflared
```

## Backup Configuration

```bash
# Backup config
sudo cp /etc/cloudflared/config.yml /etc/cloudflared/config.yml.backup

# Backup tunnel credentials
sudo cp ~/.cloudflared/*.json /backup/location/
```

