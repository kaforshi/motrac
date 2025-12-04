# Quick Guide - Setelah deploy.sh

Panduan cepat langkah-langkah setelah menjalankan `deploy.sh`.

## 🚀 Langkah Cepat

### 1. Setup Database

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE motrac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'motrac_user'@'localhost' IDENTIFIED BY 'password_kuat';
GRANT ALL PRIVILEGES ON motrac.* TO 'motrac_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Update .env

```bash
cd /var/www/motrac
sudo nano .env
```

**Penting:**
- `DB_DATABASE=motrac`
- `DB_USERNAME=motrac_user`
- `DB_PASSWORD=password_kuat`
- `APP_URL=https://motrac.yourdomain.com`
- `APP_DEBUG=false`

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan migrate --force
```

### 3. Setup Nginx

```bash
sudo cp /var/www/motrac/nginx-motrac-port8001.conf /etc/nginx/sites-available/motrac
sudo nano /etc/nginx/sites-available/motrac
# Edit: ganti yourdomain.com dengan domain Anda

sudo ln -s /etc/nginx/sites-available/motrac /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
sudo ufw allow 8001/tcp
```

### 4. Setup Cloudflare Tunnel (Jika menggunakan)

**Di Server 1:**
```bash
sudo nano /etc/cloudflared/config.yml
```

Tambahkan:
```yaml
  - hostname: motrac.yourdomain.com
    service: http://SERVER2_IP:8001
```

```bash
sudo systemctl restart cloudflared
```

**Di Cloudflare Dashboard:**
- DNS → Add record
- Type: CNAME
- Name: motrac
- Target: YOUR_TUNNEL_ID.cfargotunnel.com
- Proxy: ON

### 5. Setup Systemd Services

```bash
cd /var/www/motrac
sudo chmod +x setup-systemd-services.sh
sudo ./setup-systemd-services.sh
```

**Atau manual:**
```bash
sudo cp motrac-worker.service /etc/systemd/system/
sudo cp motrac-scheduler.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable motrac-worker motrac-scheduler
sudo systemctl start motrac-worker motrac-scheduler
```

**Catatan:** Jika menggunakan scheduler service, tidak perlu setup cron job.

### 6. Setup Cron Job (Jika tidak menggunakan scheduler service)

```bash
sudo crontab -e -u www-data
```

Tambahkan:
```
* * * * * cd /var/www/motrac && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Testing

```bash
# Test Nginx
curl http://localhost:8001

# Test dari browser
# https://motrac.yourdomain.com
```

## ✅ Checklist

- [ ] Database dibuat dan user dibuat
- [ ] .env dikonfigurasi dengan benar
- [ ] Migrations dijalankan
- [ ] Nginx dikonfigurasi dan diaktifkan
- [ ] Firewall port 8001 dibuka
- [ ] Cloudflare Tunnel dikonfigurasi (jika menggunakan)
- [ ] DNS record dibuat di Cloudflare
- [ ] Systemd services di-setup (queue worker & scheduler)
- [ ] Cron job di-setup (jika tidak menggunakan scheduler service)
- [ ] Aplikasi bisa diakses dari browser
- [ ] Test registrasi dan login

## 🐛 Troubleshooting Cepat

### 500 Error
```bash
tail -f /var/www/motrac/storage/logs/laravel.log
sudo tail -f /var/log/nginx/error.log
```

### Database Error
```bash
mysql -u motrac_user -p motrac
```

### Permission Error
```bash
sudo chown -R www-data:www-data /var/www/motrac
sudo chmod -R 775 /var/www/motrac/storage
```

## 📝 Catatan

Setelah semua selesai, aplikasi seharusnya sudah bisa diakses di:
- `https://motrac.yourdomain.com` (jika menggunakan Cloudflare Tunnel)
- Atau `http://SERVER_IP:8001` (jika akses langsung)

Lihat `POST_DEPLOYMENT.md` untuk panduan lengkap.

