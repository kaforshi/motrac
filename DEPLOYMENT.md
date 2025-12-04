# Panduan Deployment Motrac ke Server Linux

Dokumen ini menjelaskan langkah-langkah untuk deploy aplikasi Motrac ke server Linux.

## Prasyarat

- Server Linux (Ubuntu 20.04/22.04 atau Debian 11/12 direkomendasikan)
- Akses root atau user dengan sudo privileges
- Domain name yang sudah diarahkan ke IP server (opsional, untuk SSL)

## 1. Persiapan Server

### Update sistem
```bash
sudo apt update && sudo apt upgrade -y
```

### Install dependencies
```bash
# Install PHP 8.1/8.2 dan extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
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

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Nginx
sudo apt install -y nginx

# Install MySQL/MariaDB
sudo apt install -y mysql-server

# Install Git
sudo apt install -y git

# Install Node.js dan NPM (untuk build assets)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### Konfigurasi MySQL
```bash
sudo mysql_secure_installation
# Buat database dan user
sudo mysql -u root -p
```

Di dalam MySQL:
```sql
CREATE DATABASE motrac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'motrac_user'@'localhost' IDENTIFIED BY 'password_kuat_anda';
GRANT ALL PRIVILEGES ON motrac.* TO 'motrac_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 2. Setup Aplikasi

### Clone repository atau upload files
```bash
# Jika menggunakan Git
cd /var/www
sudo git clone https://github.com/username/motrac.git
sudo chown -R www-data:www-data motrac

# Atau upload via SCP/SFTP ke /var/www/motrac
```

### Install dependencies
```bash
cd /var/www/motrac
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install
sudo -u www-data npm run build
```

### Setup environment
```bash
cd /var/www/motrac
sudo cp .env.example .env
sudo nano .env
```

Edit file `.env` dengan konfigurasi berikut:
```env
APP_NAME=Motrac
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motrac
DB_USERNAME=motrac_user
DB_PASSWORD=password_kuat_anda

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

SESSION_DRIVER=file
SESSION_LIFETIME=120

QUEUE_CONNECTION=database
```

### Generate application key
```bash
sudo -u www-data php artisan key:generate
```

### Run migrations
```bash
sudo -u www-data php artisan migrate --force
```

### Setup storage link
```bash
sudo -u www-data php artisan storage:link
```

### Optimize aplikasi
```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

## 3. Konfigurasi Nginx

Buat file konfigurasi Nginx:
```bash
sudo nano /etc/nginx/sites-available/motrac
```

Isi dengan konfigurasi berikut (ganti `yourdomain.com` dengan domain Anda):
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
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

Aktifkan konfigurasi:
```bash
sudo ln -s /etc/nginx/sites-available/motrac /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 4. Setup SSL dengan Let's Encrypt (Opsional tapi Direkomendasikan)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Certbot akan otomatis mengkonfigurasi SSL dan auto-renewal.

## 5. Setup Permissions

```bash
cd /var/www/motrac
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 6. Setup Cron Job (Untuk Scheduled Tasks)

Edit crontab:
```bash
sudo crontab -e -u www-data
```

Tambahkan:
```
* * * * * cd /var/www/motrac && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Setup Supervisor (Jika menggunakan Queue)

Buat file supervisor:
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

## 8. Firewall Configuration

```bash
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable
```

## 9. Testing

1. Akses `http://yourdomain.com` atau `http://your-server-ip`
2. Test registrasi user baru
3. Test login
4. Test fitur-fitur utama

## 10. Monitoring dan Maintenance

### View logs
```bash
# Application logs
tail -f /var/www/motrac/storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

### Update aplikasi
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
```

### Backup database
```bash
mysqldump -u motrac_user -p motrac > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Troubleshooting

### Permission denied errors
```bash
sudo chown -R www-data:www-data /var/www/motrac
sudo chmod -R 775 /var/www/motrac/storage
sudo chmod -R 775 /var/www/motrac/bootstrap/cache
```

### 500 Internal Server Error
- Check Nginx error logs: `sudo tail -f /var/log/nginx/error.log`
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Verify `.env` file configuration
- Run `php artisan config:clear`

### Database connection errors
- Verify database credentials in `.env`
- Check MySQL service: `sudo systemctl status mysql`
- Test connection: `mysql -u motrac_user -p motrac`

## Security Checklist

- [ ] APP_DEBUG=false di production
- [ ] Strong database password
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Regular backups scheduled
- [ ] File permissions set correctly
- [ ] .env file not accessible via web
- [ ] Update system packages regularly

## Support

Jika mengalami masalah, periksa:
1. Log files di `/var/www/motrac/storage/logs/`
2. Nginx error logs
3. PHP-FPM logs
4. System logs: `journalctl -xe`

