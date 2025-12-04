# Langkah Setelah Menjalankan deploy.sh

Panduan lengkap untuk menyelesaikan setup setelah menjalankan `deploy.sh`.

## ✅ Checklist Setelah deploy.sh

Setelah `deploy.sh` selesai, lakukan langkah-langkah berikut:

## 1. Konfigurasi Database

### Buat Database dan User

```bash
sudo mysql -u root -p
```

Di dalam MySQL:
```sql
CREATE DATABASE motrac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'motrac_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON motrac.* TO 'motrac_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Update .env dengan Database Credentials

```bash
cd /var/www/motrac
sudo nano .env
```

Pastikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motrac
DB_USERNAME=motrac_user
DB_PASSWORD=strong_password_here
```

### Run Migrations

```bash
cd /var/www/motrac
sudo -u www-data php artisan migrate --force
```

## 2. Konfigurasi .env Lengkap

Edit file `.env` dan pastikan semua konfigurasi sudah benar:

```bash
sudo nano /var/www/motrac/.env
```

### Konfigurasi Penting:

```env
APP_NAME=Motrac
APP_ENV=production
APP_KEY=base64:... (sudah di-generate oleh deploy.sh)
APP_DEBUG=false
APP_URL=https://motrac.yourdomain.com

# Database (sudah di-setup di step 1)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motrac
DB_USERNAME=motrac_user
DB_PASSWORD=your_password

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue (jika menggunakan)
QUEUE_CONNECTION=database
```

Setelah edit, clear config cache:
```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
```

## 3. Setup Nginx

### Copy Konfigurasi Nginx

```bash
sudo cp /var/www/motrac/nginx-motrac-port8001.conf /etc/nginx/sites-available/motrac
```

### Edit Konfigurasi

```bash
sudo nano /etc/nginx/sites-available/motrac
```

**Penting:** Ganti `yourdomain.com` dengan domain Anda:
- `server_name motrac.yourdomain.com;` → `server_name motrac.domain-anda.com;`

### Aktifkan Konfigurasi

```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/motrac /etc/nginx/sites-enabled/

# Test konfigurasi
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx

# Check status
sudo systemctl status nginx
```

## 4. Setup Firewall

```bash
# Allow port 8001
sudo ufw allow 8001/tcp

# Check status
sudo ufw status
```

## 5. Setup Cloudflare Tunnel (Jika menggunakan)

### Update Tunnel Config di Server 1

```bash
# Di Server 1
sudo nano /etc/cloudflared/config.yml
```

Tambahkan route Motrac (jika belum):
```yaml
ingress:
  # Website 1 yang sudah ada
  - hostname: website1.yourdomain.com
    service: http://localhost:8000
  
  # Motrac - Server 2
  - hostname: motrac.yourdomain.com
    service: http://SERVER2_IP:8001
  
  # Catch-all
  - service: http_status:404
```

```bash
# Validasi
sudo cloudflared tunnel --config /etc/cloudflared/config.yml ingress validate

# Restart
sudo systemctl restart cloudflared
```

### Setup DNS di Cloudflare Dashboard

1. Login ke Cloudflare
2. Pilih domain
3. DNS → Records → Add record

```
Type: CNAME
Name: motrac
Target: YOUR_TUNNEL_ID.cfargotunnel.com
Proxy: Proxied (ON)
```

## 6. Setup Cron Job (Scheduled Tasks)

```bash
sudo crontab -e -u www-data
```

Tambahkan baris ini:
```
* * * * * cd /var/www/motrac && php artisan schedule:run >> /dev/null 2>&1
```

Verify:
```bash
sudo crontab -l -u www-data
```

## 7. Setup Systemd Services

### Setup Queue Worker dan Scheduler

```bash
cd /var/www/motrac
sudo chmod +x setup-systemd-services.sh
sudo ./setup-systemd-services.sh
```

Atau manual:
```bash
# Copy service files
sudo cp motrac-worker.service /etc/systemd/system/
sudo cp motrac-scheduler.service /etc/systemd/system/

# Reload systemd
sudo systemctl daemon-reload

# Enable and start
sudo systemctl enable motrac-worker
sudo systemctl start motrac-worker

# Scheduler (opsional, alternatif cron)
sudo systemctl enable motrac-scheduler
sudo systemctl start motrac-scheduler
```

**Catatan:** Jika menggunakan scheduler service, hapus cron job yang sudah dibuat sebelumnya.

Lihat `SYSTEMD_SERVICES.md` untuk detail lengkap.

## 8. Setup Queue Worker dengan Supervisor (Alternatif)

### Install Supervisor

```bash
sudo apt install supervisor -y
```

### Buat Config Supervisor

```bash
sudo nano /etc/supervisor/conf.d/motrac-worker.conf
```

Paste ini:
```ini
[program:motrac-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/motrac/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/motrac/storage/logs/worker.log
stopwaitsecs=3600
```

### Start Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start motrac-worker:*
sudo supervisorctl status
```

## 9. Final Permissions Check

```bash
cd /var/www/motrac

# Fix ownership
sudo chown -R www-data:www-data .

# Fix permissions
sudo chmod -R 755 .
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

## 10. Testing

### Test Nginx

```bash
# Test dari server
curl http://localhost:8001

# Test dari browser (jika tunnel sudah setup)
# https://motrac.yourdomain.com
```

### Test Database Connection

```bash
cd /var/www/motrac
sudo -u www-data php artisan tinker
```

Di tinker:
```php
DB::connection()->getPdo();
// Harus return PDO object tanpa error
exit
```

### Test Application

1. Buka browser: `https://motrac.yourdomain.com`
2. Test registrasi user baru
3. Test login
4. Test fitur-fitur utama

## 11. Monitoring

### View Logs

```bash
# Application logs
tail -f /var/www/motrac/storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# Queue worker logs (jika menggunakan)
tail -f /var/www/motrac/storage/logs/worker.log
```

### Check Services Status

```bash
# Nginx
sudo systemctl status nginx

# PHP-FPM
sudo systemctl status php8.2-fpm

# MySQL
sudo systemctl status mysql

# Supervisor (jika menggunakan)
sudo supervisorctl status
```

## 12. Security Checklist

- [ ] `APP_DEBUG=false` di `.env`
- [ ] Strong database password
- [ ] SSL certificate (via Cloudflare Tunnel)
- [ ] Firewall configured
- [ ] File permissions correct
- [ ] `.env` file not accessible via web
- [ ] Regular backups scheduled

## 13. Backup Setup

### Backup Database

Buat script backup:
```bash
sudo nano /usr/local/bin/backup-motrac.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/backup/motrac"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u motrac_user -p'your_password' motrac > $BACKUP_DIR/db_$DATE.sql

# Backup files (optional)
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/motrac/storage

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
sudo chmod +x /usr/local/bin/backup-motrac.sh

# Setup cron untuk backup harian
sudo crontab -e
# Tambahkan: 0 2 * * * /usr/local/bin/backup-motrac.sh
```

## Troubleshooting

### 500 Internal Server Error

```bash
# Check logs
tail -f /var/www/motrac/storage/logs/laravel.log
sudo tail -f /var/log/nginx/error.log

# Clear cache
cd /var/www/motrac
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan view:clear
```

### Database Connection Error

```bash
# Test connection
mysql -u motrac_user -p motrac

# Check .env
cat /var/www/motrac/.env | grep DB_

# Check MySQL service
sudo systemctl status mysql
```

### Permission Denied

```bash
cd /var/www/motrac
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache
```

## Next Steps

Setelah semua setup selesai:

1. **Test semua fitur** aplikasi
2. **Setup monitoring** (opsional)
3. **Schedule regular backups**
4. **Document credentials** (simpan dengan aman)
5. **Update aplikasi** secara berkala

## Update Aplikasi (Untuk Masa Depan)

```bash
cd /var/www/motrac

# Backup dulu
sudo -u www-data php artisan backup:run  # jika ada

# Update
sudo -u www-data git pull origin main
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install && npm run build
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## Support

Jika mengalami masalah:
1. Check log files
2. Verify configuration
3. Test database connection
4. Check service status
5. Review `DEPLOYMENT_FIX.md` untuk troubleshooting

