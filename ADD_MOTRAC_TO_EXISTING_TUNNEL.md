# Menambahkan Motrac ke Cloudflare Tunnel yang Sudah Ada

Panduan untuk menambahkan website Motrac ke Cloudflare Tunnel yang sudah digunakan untuk website pertama.

## Prasyarat

- Cloudflare Tunnel sudah terinstall dan berjalan di Server 1
- Website pertama sudah berjalan di port 8000
- Tunnel ID sudah diketahui

## Langkah-langkah

### 1. Cek Konfigurasi Tunnel yang Ada

```bash
# Di Server 1
sudo cat /etc/cloudflared/config.yml
```

Atau jika menggunakan lokasi lain:
```bash
# Cek lokasi config
sudo systemctl status cloudflared
# Lihat di ExecStart untuk path config

# List tunnels
cloudflared tunnel list
```

### 2. Backup Konfigurasi

```bash
sudo cp /etc/cloudflared/config.yml /etc/cloudflared/config.yml.backup
```

### 3. Update Konfigurasi Tunnel

Edit file konfigurasi:
```bash
sudo nano /etc/cloudflared/config.yml
```

**Sebelum (contoh):**
```yaml
tunnel: YOUR_EXISTING_TUNNEL_ID
credentials-file: /root/.cloudflared/YOUR_TUNNEL_ID.json

ingress:
  - hostname: website1.yourdomain.com
    service: http://localhost:8000
  
  - service: http_status:404
```

**Sesudah (tambahkan Motrac):**
```yaml
tunnel: YOUR_EXISTING_TUNNEL_ID
credentials-file: /root/.cloudflared/YOUR_TUNNEL_ID.json

ingress:
  # Website 1 - Server 1 (Port 8000)
  - hostname: website1.yourdomain.com
    service: http://localhost:8000
  
  # Motrac - Server 2
  # Opsi 1: Direct connection (jika Server 2 bisa diakses langsung)
  - hostname: motrac.yourdomain.com
    service: http://SERVER2_IP:8001
  
  # Opsi 2: Via SSH Tunnel (lebih aman, recommended)
  # - hostname: motrac.yourdomain.com
  #   service: http://127.0.0.1:8001  # Gunakan 127.0.0.1, BUKAN 127.0.0.0
  #   originRequest:
  #     httpHostHeader: motrac.yourdomain.com
  #     connectTimeout: 10s
  #     tcpKeepAlive: 30s
  #     http2Origin: true
  
  # Catch-all rule (HARUS di akhir)
  - service: http_status:404
```

**Penting:** Catch-all rule (`http_status:404`) harus selalu di akhir!

### 4. Validasi Konfigurasi

```bash
sudo cloudflared tunnel --config /etc/cloudflared/config.yml ingress validate
```

Jika ada error, perbaiki sebelum lanjut.

### 5. Restart Tunnel Service

```bash
sudo systemctl restart cloudflared
sudo systemctl status cloudflared
```

### 6. Setup Motrac di Server 2

#### Install Aplikasi

```bash
# Upload files ke Server 2
cd /var/www
sudo git clone your-repo-url motrac
# atau upload via SCP

# Setup
cd motrac
sudo chmod +x deploy.sh
sudo ./deploy.sh
```

#### Setup Nginx untuk Port 8001

```bash
sudo cp nginx-motrac-port8001.conf /etc/nginx/sites-available/motrac
sudo nano /etc/nginx/sites-available/motrac
# Edit: ganti yourdomain.com dengan domain Anda

sudo ln -s /etc/nginx/sites-available/motrac /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Update .env

```bash
sudo nano /var/www/motrac/.env
```

Pastikan:
```env
APP_URL=https://motrac.yourdomain.com
APP_ENV=production
APP_DEBUG=false
```

#### Firewall

```bash
sudo ufw allow 8001/tcp
```

### 7. Setup DNS di Cloudflare

1. Login ke Cloudflare Dashboard
2. Pilih domain Anda
3. DNS → Records → Add record

**Tambah record baru:**
```
Type: CNAME
Name: motrac
Target: YOUR_EXISTING_TUNNEL_ID.cfargotunnel.com
Proxy: Proxied (ON - orange cloud)
TTL: Auto
```

**Catatan:** Gunakan Tunnel ID yang sama dengan website pertama!

### 8. Opsi: Setup SSH Tunnel (Recommended untuk Security)

Jika Server 2 tidak bisa diakses langsung dari internet, gunakan SSH tunnel.

#### Di Server 1

```bash
# Install autossh
sudo apt install autossh

# Setup SSH key (jika belum)
ssh-keygen -t rsa -b 4096
ssh-copy-id user@SERVER2_IP

# Test connection
ssh user@SERVER2_IP
```

#### Buat SSH Tunnel Service

```bash
sudo nano /etc/systemd/system/cloudflare-tunnel-ssh.service
```

Paste ini (ganti `user` dan `SERVER2_IP`):
```ini
[Unit]
Description=SSH Tunnel to Server 2 for Motrac
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/bin/autossh -M 0 -o "ServerAliveInterval 30" -o "ServerAliveCountMax 3" -N -L 127.0.0.1:8001:127.0.0.1:8001 user@SERVER2_IP
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable cloudflare-tunnel-ssh
sudo systemctl start cloudflare-tunnel-ssh
sudo systemctl status cloudflare-tunnel-ssh
```

#### Update config.yml untuk menggunakan localhost

```yaml
  - hostname: motrac.yourdomain.com
    service: http://localhost:8001
    originRequest:
      httpHostHeader: motrac.yourdomain.com
      connectTimeout: 10s
      tcpKeepAlive: 30s
      http2Origin: true
```

Restart tunnel:
```bash
sudo systemctl restart cloudflared
```

### 9. Testing

```bash
# Check tunnel status
sudo systemctl status cloudflared

# View tunnel logs
sudo journalctl -u cloudflared -f

# Test dari browser
# https://website1.yourdomain.com (harus tetap bekerja)
# https://motrac.yourdomain.com (harus bisa diakses)
```

### 10. Troubleshooting

#### Website pertama tidak bisa diakses setelah update

```bash
# Rollback config
sudo cp /etc/cloudflared/config.yml.backup /etc/cloudflared/config.yml
sudo systemctl restart cloudflared

# Periksa config
sudo cloudflared tunnel --config /etc/cloudflared/config.yml ingress validate
```

#### Motrac tidak bisa diakses

1. **Check DNS:**
   - Pastikan record CNAME sudah dibuat
   - Pastikan Proxy status: Proxied (ON)
   - Pastikan menggunakan Tunnel ID yang benar

2. **Check Tunnel:**
   ```bash
   sudo systemctl status cloudflared
   sudo journalctl -u cloudflared -f
   ```

3. **Check Server 2:**
   ```bash
   # Test Nginx
   sudo systemctl status nginx
   curl http://localhost:8001
   
   # Test dari Server 1 (jika direct)
   curl http://SERVER2_IP:8001
   
   # Test SSH tunnel (jika menggunakan)
   curl http://localhost:8001
   ```

4. **Check Firewall:**
   ```bash
   # Di Server 2
   sudo ufw status
   sudo ufw allow 8001/tcp
   ```

5. **Check Nginx logs:**
   ```bash
   sudo tail -f /var/log/nginx/error.log
   sudo tail -f /var/log/nginx/access.log
   ```

#### SSH Tunnel tidak connect

```bash
# Test SSH connection
ssh user@SERVER2_IP

# Check service
sudo systemctl status cloudflare-tunnel-ssh

# View logs
sudo journalctl -u cloudflare-tunnel-ssh -f

# Restart service
sudo systemctl restart cloudflare-tunnel-ssh
```

## Checklist

- [ ] Backup config tunnel yang ada
- [ ] Update config.yml dengan route Motrac
- [ ] Validasi config
- [ ] Restart cloudflared service
- [ ] Setup Motrac di Server 2
- [ ] Setup Nginx di Server 2 (port 8001)
- [ ] Update .env di Server 2
- [ ] Setup firewall di Server 2
- [ ] Setup DNS record di Cloudflare
- [ ] (Opsional) Setup SSH tunnel
- [ ] Test kedua website

## Catatan Penting

1. **Catch-all rule harus di akhir** - Route `http_status:404` harus selalu menjadi rule terakhir
2. **Gunakan Tunnel ID yang sama** - Untuk website pertama dan Motrac
3. **DNS harus Proxied** - Pastikan status Proxy: ON (orange cloud) di Cloudflare
4. **Order matters** - Cloudflare Tunnel akan match route dari atas ke bawah, jadi pastikan urutan benar
5. **Backup config** - Selalu backup sebelum mengubah config

## Update Aplikasi Motrac

Setelah setup, untuk update Motrac:

```bash
# Di Server 2
cd /var/www/motrac
sudo -u www-data git pull
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install && npm run build
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

Tunnel tidak perlu di-restart karena hanya routing traffic.

