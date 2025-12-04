# Fix Deployment Issues

Panduan untuk memperbaiki masalah yang muncul saat deployment.

## Masalah 1: Git Ownership Error

**Error:**
```
fatal: detected dubious ownership in repository at '/var/www/motrac'
```

**Solusi:**

### Opsi 1: Menggunakan Script Fix (Recommended)

```bash
cd /var/www/motrac
sudo chmod +x fix-deployment-issues.sh
sudo ./fix-deployment-issues.sh
```

### Opsi 2: Manual Fix

```bash
cd /var/www/motrac

# Fix git ownership
sudo git config --global --add safe.directory /var/www/motrac
sudo chown -R www-data:www-data .git
sudo chown -R www-data:www-data .
```

## Masalah 2: Composer Lock File Outdated

**Error:**
```
Warning: The lock file is not up to date with the latest changes in composer.json
- Required package "laravel/framework" is in the lock file as "v10.50.0" but that does not satisfy your constraint "^11.0"
- Required package "laravel/sanctum" is in the lock file as "v3.3.3" but that does not satisfy your constraint "^4.0"
```

**Solusi:**

### Opsi 1: Menggunakan Script Fix (Recommended)

```bash
cd /var/www/motrac
sudo chmod +x fix-deployment-issues.sh
sudo ./fix-deployment-issues.sh
```

### Opsi 2: Manual Fix

```bash
cd /var/www/motrac

# Backup lock file
sudo cp composer.lock composer.lock.backup

# Update lock file
sudo -u www-data composer update --no-dev --no-interaction --lock

# Install dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction
```

### Opsi 3: Regenerate Lock File (Jika masih error)

```bash
cd /var/www/motrac

# Hapus lock file lama
sudo rm composer.lock

# Install ulang (akan generate lock file baru)
sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction
```

## Masalah 3: Permission Denied

**Error:**
```
Permission denied pada storage atau cache
```

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

## Langkah Lengkap Fix Semua Masalah

```bash
cd /var/www/motrac

# 1. Fix Git ownership
sudo git config --global --add safe.directory /var/www/motrac
sudo chown -R www-data:www-data .git

# 2. Fix Composer lock file
sudo cp composer.lock composer.lock.backup
sudo -u www-data composer update --no-dev --no-interaction --lock

# 3. Install dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev --no-interaction

# 4. Fix all permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache

# 5. Continue deployment
sudo ./deploy.sh
```

## Menggunakan Script Otomatis

Script `fix-deployment-issues.sh` akan otomatis:
1. Fix Git ownership
2. Update composer.lock
3. Install dependencies
4. Fix permissions

```bash
cd /var/www/motrac
sudo chmod +x fix-deployment-issues.sh
sudo ./fix-deployment-issues.sh
```

Setelah itu, lanjutkan deployment:
```bash
sudo ./deploy.sh
```

## Troubleshooting

### Composer update masih error

Jika `composer update` masih error, coba:

```bash
# Clear composer cache
sudo -u www-data composer clear-cache

# Update dengan verbose untuk lihat error detail
sudo -u www-data composer update --no-dev --no-interaction -vvv
```

### Masih ada permission issues

```bash
# Pastikan user www-data memiliki akses
sudo chown -R www-data:www-data /var/www/motrac
sudo chmod -R 755 /var/www/motrac
sudo chmod -R 775 /var/www/motrac/storage
sudo chmod -R 775 /var/www/motrac/bootstrap/cache

# Check ownership
ls -la /var/www/motrac
```

### Git masih error setelah fix

```bash
# Check git config
git config --global --get-regexp safe.directory

# Add lagi jika perlu
sudo git config --global --add safe.directory /var/www/motrac

# Check ownership
ls -la /var/www/motrac/.git
```

## Catatan

1. **Selalu backup** sebelum mengubah file penting
2. **Gunakan www-data user** untuk menjalankan composer dan artisan commands
3. **Check permissions** setelah setiap perubahan
4. **Update deploy.sh** sudah include fix untuk masalah ini di versi terbaru

