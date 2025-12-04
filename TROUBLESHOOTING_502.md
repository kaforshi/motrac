# Troubleshooting 502 Bad Gateway Error

Error 502 menunjukkan Cloudflare Tunnel bisa connect, tapi server backend tidak merespons.

## Diagnosa Cepat

### 1. Check Nginx Status di Server 2

```bash
# SSH ke Server 2
ssh user@server2-ip

# Check Nginx status
sudo systemctl status nginx

# Jika tidak running, start
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 2. Check Nginx Listen di Port 8001

```bash
# Check apakah Nginx listen di port 8001
sudo netstat -tlnp | grep 8001
# atau
sudo ss -tlnp | grep 8001

# Test dari localhost
curl http://localhost:8001
```

### 3. Check Nginx Configuration

```bash
# Test konfigurasi
sudo nginx -t

# Check apakah site enabled
ls -la /etc/nginx/sites-enabled/ | grep motrac

# View config
sudo cat /etc/nginx/sites-available/motrac
```

**Pastikan:**
- `listen 8001;` ada di config
- `server_name motrac.aryaintaran.dev;` sesuai domain
- `root /var/www/motrac/public;` path benar

### 4. Check PHP-FPM Status

```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm
# atau
sudo systemctl status php-fpm

# Jika tidak running, start
sudo systemctl start php8.2-fpm
sudo systemctl enable php8.2-fpm

# Check socket
ls -la /var/run/php/php8.2-fpm.sock
```

### 5. Check Nginx Error Logs

```bash
# View error logs
sudo tail -f /var/log/nginx/error.log

# View access logs
sudo tail -f /var/log/nginx/access.log
```

### 6. Check Laravel Application

```bash
cd /var/www/motrac

# Check .env exists
ls -la .env

# Check Laravel logs
tail -f storage/logs/laravel.log

# Test artisan
sudo -u www-data php artisan --version
```

### 7. Check Firewall

```bash
# Check firewall status
sudo ufw status

# Allow port 8001
sudo ufw allow 8001/tcp

# Check jika port terbuka
sudo ufw status numbered
```

### 8. Test dari Server 1 (Tunnel Server)

```bash
# SSH ke Server 1
ssh user@server1-ip

# Test connection ke Server 2
curl http://SERVER2_IP:8001

# Atau jika menggunakan SSH tunnel
curl http://localhost:8001
```

### 9. Check Cloudflare Tunnel Config

```bash
# Di Server 1
sudo cat /etc/cloudflared/config.yml

# Pastikan route Motrac benar:
# - hostname: motrac.aryaintaran.dev
# - service: http://SERVER2_IP:8001 (atau localhost:8001 jika SSH tunnel)

# Validate config
sudo cloudflared tunnel --config /etc/cloudflared/config.yml ingress validate

# Check tunnel status
sudo systemctl status cloudflared

# View tunnel logs
sudo journalctl -u cloudflared -f
```

## Solusi Berdasarkan Error

### Error: "Connection refused"

**Penyebab:** Nginx tidak running atau tidak listen di port 8001

**Solusi:**
```bash
# Start Nginx
sudo systemctl start nginx

# Check config
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx

# Verify port listening
sudo netstat -tlnp | grep 8001
```

### Error: "502 Bad Gateway" di Nginx logs

**Penyebab:** PHP-FPM tidak running atau socket tidak ada

**Solusi:**
```bash
# Start PHP-FPM
sudo systemctl start php8.2-fpm
sudo systemctl enable php8.2-fpm

# Check socket path di Nginx config
sudo grep "fastcgi_pass" /etc/nginx/sites-available/motrac

# Verify socket exists
ls -la /var/run/php/php8.2-fpm.sock

# Fix permissions jika perlu
sudo chown www-data:www-data /var/run/php/php8.2-fpm.sock
```

### Error: Laravel Application Error

**Penyebab:** .env tidak dikonfigurasi atau database error

**Solusi:**
```bash
cd /var/www/motrac

# Check .env
cat .env | grep -E "APP_|DB_"

# Clear cache
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear

# Test database connection
sudo -u www-data php artisan tinker
# Di tinker: DB::connection()->getPdo();
```

### Error: Permission Denied

**Penyebab:** File permissions salah

**Solusi:**
```bash
cd /var/www/motrac

# Fix ownership
sudo chown -R www-data:www-data .

# Fix permissions
sudo chmod -R 755 .
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

### Error: Nginx Config Error

**Penyebab:** Konfigurasi Nginx salah

**Solusi:**
```bash
# Test config
sudo nginx -t

# Jika error, check config
sudo nano /etc/nginx/sites-available/motrac

# Pastikan:
# - listen 8001;
# - server_name motrac.aryaintaran.dev;
# - root /var/www/motrac/public;
# - fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;

# Reload Nginx
sudo nginx -t && sudo systemctl reload nginx
```

## Checklist Lengkap

Jalankan semua check ini:

```bash
# 1. Services running
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql

# 2. Port listening
sudo netstat -tlnp | grep 8001
sudo netstat -tlnp | grep php-fpm

# 3. Nginx config
sudo nginx -t
ls -la /etc/nginx/sites-enabled/ | grep motrac

# 4. Application
cd /var/www/motrac
ls -la .env
sudo -u www-data php artisan --version

# 5. Firewall
sudo ufw status | grep 8001

# 6. Test local
curl http://localhost:8001

# 7. Test from Server 1
# curl http://SERVER2_IP:8001
```

## Quick Fix Script

Buat script untuk fix semua masalah umum:

```bash
#!/bin/bash
# quick-fix-502.sh

echo "Fixing 502 Error..."

# 1. Start services
sudo systemctl start nginx
sudo systemctl start php8.2-fpm
sudo systemctl start mysql

# 2. Fix permissions
cd /var/www/motrac
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache

# 3. Clear cache
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear

# 4. Test Nginx
sudo nginx -t && sudo systemctl restart nginx

# 5. Test PHP-FPM
sudo systemctl restart php8.2-fpm

# 6. Check port
echo "Checking port 8001..."
sudo netstat -tlnp | grep 8001

# 7. Test local
echo "Testing local connection..."
curl -I http://localhost:8001

echo "Done! Check logs if still error:"
echo "  sudo tail -f /var/log/nginx/error.log"
echo "  tail -f /var/www/motrac/storage/logs/laravel.log"
```

## Testing Step by Step

### Step 1: Test dari Server 2 (Local)

```bash
# Test Nginx langsung
curl http://localhost:8001

# Harus return HTML atau redirect, bukan error
```

### Step 2: Test dari Server 1

```bash
# Test connection ke Server 2
curl http://SERVER2_IP:8001

# Harus return HTML
```

### Step 3: Test dari Cloudflare Tunnel

```bash
# Di Server 1, check tunnel logs
sudo journalctl -u cloudflared -f

# Test dari browser
# https://motrac.aryaintaran.dev
```

## Common Issues & Solutions

### Issue 1: Nginx tidak listen di 8001

**Fix:**
```bash
sudo nano /etc/nginx/sites-available/motrac
# Pastikan: listen 8001;
sudo systemctl restart nginx
```

### Issue 2: PHP-FPM socket tidak ditemukan

**Fix:**
```bash
# Check PHP version
php -v

# Find correct socket
ls -la /var/run/php/

# Update Nginx config dengan socket yang benar
sudo nano /etc/nginx/sites-available/motrac
# Update: fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
```

### Issue 3: Laravel .env error

**Fix:**
```bash
cd /var/www/motrac
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
```

### Issue 4: Database connection error

**Fix:**
```bash
# Test connection
mysql -u motrac_user -p motrac

# Check .env
cat .env | grep DB_

# Run migrations
sudo -u www-data php artisan migrate --force
```

## Debug Commands

```bash
# View all relevant logs
sudo tail -f /var/log/nginx/error.log &
tail -f /var/www/motrac/storage/logs/laravel.log &
sudo journalctl -u cloudflared -f &
sudo journalctl -u php8.2-fpm -f &
```

## Still Not Working?

1. **Check semua services running:**
   ```bash
   sudo systemctl status nginx php8.2-fpm mysql cloudflared
   ```

2. **Check semua ports listening:**
   ```bash
   sudo netstat -tlnp | grep -E "8001|php-fpm|mysql"
   ```

3. **Test manual:**
   ```bash
   cd /var/www/motrac
   sudo -u www-data php artisan serve --host=0.0.0.0 --port=8001
   # Test: curl http://localhost:8001
   ```

4. **Check Cloudflare Tunnel routing:**
   ```bash
   # Di Server 1
   sudo cat /etc/cloudflared/config.yml
   # Pastikan route ke Server 2 benar
   ```

