# Deployment Guide - Motrac

Panduan lengkap untuk deploy aplikasi Motrac ke server Linux production.

## 📋 Daftar Isi

1. [Persiapan Server](#persiapan-server)
2. [Setup Aplikasi](#setup-aplikasi)
3. [Konfigurasi Database](#konfigurasi-database)
4. [Konfigurasi Web Server](#konfigurasi-web-server)
5. [Setup SSL](#setup-ssl)
6. [Optimasi & Maintenance](#optimasi--maintenance)

## 🚀 Persiapan Server

### Opsi 1: Setup Otomatis (Recommended)

Jalankan script setup otomatis:
```bash
# Upload setup-server.sh ke server
scp setup-server.sh user@your-server:/tmp/

# SSH ke server
ssh user@your-server

# Jalankan script
sudo chmod +x /tmp/setup-server.sh
sudo /tmp/setup-server.sh
```

### Opsi 2: Setup Manual

Ikuti langkah-langkah di `DEPLOYMENT.md` bagian "1. Persiapan Server"

## 📦 Setup Aplikasi

### 1. Upload Files

**Via Git (Recommended):**
```bash
ssh user@your-server
cd /var/www
sudo git clone https://github.com/your-repo/motrac.git
sudo chown -R www-data:www-data motrac
```

**Via SCP:**
```bash
# Dari local machine
scp -r * user@your-server:/tmp/motrac/
# Kemudian di server:
sudo mv /tmp/motrac /var/www/
sudo chown -R www-data:www-data /var/www/motrac
```

### 2. Jalankan Deployment Script

```bash
cd /var/www/motrac
sudo chmod +x deploy.sh
sudo ./deploy.sh
```

Script akan:
- Install dependencies
- Build assets
- Setup environment
- Run migrations
- Optimize application

## 🗄️ Konfigurasi Database

### 1. Buat Database

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE motrac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'motrac_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON motrac.* TO 'motrac_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Konfigurasi .env

Edit file `.env`:
```bash
sudo nano /var/www/motrac/.env
```

Pastikan konfigurasi berikut:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motrac
DB_USERNAME=motrac_user
DB_PASSWORD=strong_password_here

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Motrac"
```

## 🌐 Konfigurasi Web Server

### 1. Setup Nginx

```bash
# Copy konfigurasi
sudo cp /var/www/motrac/nginx.conf.example /etc/nginx/sites-available/motrac

# Edit dengan domain Anda
sudo nano /etc/nginx/sites-available/motrac
```

Ganti `yourdomain.com` dengan domain Anda.

### 2. Aktifkan Konfigurasi

```bash
sudo ln -s /etc/nginx/sites-available/motrac /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 🔒 Setup SSL

### Install Let's Encrypt

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Certbot akan otomatis:
- Install SSL certificate
- Konfigurasi auto-renewal
- Redirect HTTP ke HTTPS

## ⚙️ Optimasi & Maintenance

### Setup Cron Job

```bash
sudo crontab -e -u www-data
```

Tambahkan:
```
* * * * * cd /var/www/motrac && php artisan schedule:run >> /dev/null 2>&1
```

### Setup Queue Worker (Jika menggunakan Queue)

```bash
sudo nano /etc/supervisor/conf.d/motrac-worker.conf
```

Isi dengan:
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

Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start motrac-worker:*
```

### Setup Permissions

```bash
cd /var/www/motrac
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 🔄 Update Aplikasi

```bash
cd /var/www/motrac
sudo -u www-data git pull origin main
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install
sudo -u www-data npm run build
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## 📊 Monitoring

### View Logs

```bash
# Application logs
tail -f /var/www/motrac/storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

### Backup Database

```bash
mysqldump -u motrac_user -p motrac > backup_$(date +%Y%m%d_%H%M%S).sql
```

## 🐛 Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/motrac
sudo chmod -R 775 /var/www/motrac/storage
sudo chmod -R 775 /var/www/motrac/bootstrap/cache
```

### Clear Cache
```bash
cd /var/www/motrac
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 500 Internal Server Error
1. Check Nginx error logs: `sudo tail -f /var/log/nginx/error.log`
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Verify `.env` configuration
4. Check file permissions
5. Run `php artisan config:clear`

### Database Connection Error
1. Verify database credentials in `.env`
2. Check MySQL service: `sudo systemctl status mysql`
3. Test connection: `mysql -u motrac_user -p motrac`

## ✅ Security Checklist

- [ ] `APP_DEBUG=false` di production
- [ ] Strong database password
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Regular backups scheduled
- [ ] File permissions set correctly
- [ ] `.env` file not accessible via web
- [ ] Update system packages regularly
- [ ] Use strong passwords for all services
- [ ] Enable fail2ban (optional but recommended)

## 📞 Support

Jika mengalami masalah:
1. Check log files
2. Verify configuration
3. Test database connection
4. Check service status: `sudo systemctl status nginx php8.2-fpm mysql`

Untuk bantuan lebih lanjut, lihat dokumentasi Laravel: https://laravel.com/docs

