# Quick Deployment Guide - Motrac

Panduan cepat untuk deploy Motrac ke server Linux.

## Langkah Cepat

### 1. Upload Files ke Server
```bash
# Via SCP
scp -r * user@your-server-ip:/var/www/motrac/

# Atau via Git
ssh user@your-server-ip
cd /var/www
git clone https://github.com/your-repo/motrac.git
```

### 2. Jalankan Script Deployment
```bash
cd /var/www/motrac
sudo chmod +x deploy.sh
sudo ./deploy.sh
```

### 3. Setup Nginx
```bash
# Copy konfigurasi Nginx
sudo cp nginx.conf.example /etc/nginx/sites-available/motrac

# Edit dengan domain Anda
sudo nano /etc/nginx/sites-available/motrac

# Aktifkan
sudo ln -s /etc/nginx/sites-available/motrac /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 4. Setup Database
```bash
# Login ke MySQL
sudo mysql -u root -p

# Buat database
CREATE DATABASE motrac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'motrac_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON motrac.* TO 'motrac_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Konfigurasi .env
```bash
cd /var/www/motrac
sudo nano .env
```

Pastikan konfigurasi berikut:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_DATABASE=motrac
DB_USERNAME=motrac_user
DB_PASSWORD=strong_password_here

# Email configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### 6. Setup SSL (Let's Encrypt)
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 7. Setup Cron Job
```bash
sudo crontab -e -u www-data
```

Tambahkan:
```
* * * * * cd /var/www/motrac && php artisan schedule:run >> /dev/null 2>&1
```

## Checklist Deployment

- [ ] Files uploaded ke `/var/www/motrac`
- [ ] Dependencies installed (`composer install`)
- [ ] Assets built (`npm run build`)
- [ ] `.env` file configured
- [ ] Database created and configured
- [ ] Migrations run (`php artisan migrate`)
- [ ] Storage link created (`php artisan storage:link`)
- [ ] Permissions set correctly
- [ ] Nginx configured and restarted
- [ ] SSL certificate installed
- [ ] Cron job configured
- [ ] Application tested

## Troubleshooting

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

### Check Logs
```bash
# Laravel logs
tail -f /var/www/motrac/storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
```

## Update Aplikasi

```bash
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

