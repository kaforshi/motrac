# Fix TrustProxies untuk Cloudflare

Error 500 yang muncul dari TrustProxies middleware biasanya karena aplikasi tidak mempercayai proxy dari Cloudflare.

## Masalah

Laravel's TrustProxies middleware perlu dikonfigurasi untuk mempercayai Cloudflare sebagai proxy. Jika tidak dikonfigurasi, aplikasi akan menolak request dari Cloudflare.

## Solusi

### Opsi 1: Menggunakan Script (Recommended)

```bash
cd /var/www/motrac
sudo chmod +x fix-trust-proxies.sh
sudo ./fix-trust-proxies.sh
```

### Opsi 2: Manual Fix

Edit file `app/Http/Middleware/TrustProxies.php`:

```bash
cd /var/www/motrac
sudo nano app/Http/Middleware/TrustProxies.php
```

**Sebelum:**
```php
protected $proxies;
```

**Sesudah:**
```php
protected $proxies = '*'; // Trust all proxies (for Cloudflare)
```

Kemudian:
```bash
# Clear cache
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear

# Optimize
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache

# Test
curl http://127.0.0.1:8001
```

## Penjelasan

- **`$proxies = '*'`** - Mempercayai semua proxy (cocok untuk Cloudflare)
- **`$proxies = ['*']`** - Alternatif syntax
- **`$proxies = null`** - Tidak mempercayai proxy (default, menyebabkan error dengan Cloudflare)

## Verifikasi

Setelah fix, test aplikasi:

```bash
# Test local
curl http://127.0.0.1:8001

# Test dari browser
# https://motrac.aryaintaran.dev
```

Jika masih error, check log:
```bash
tail -f storage/logs/laravel.log
```

## Alternative: Trust Specific Cloudflare IPs

Jika ingin lebih spesifik (tidak recommended, karena Cloudflare IPs berubah):

```php
protected $proxies = [
    '173.245.48.0/20',
    '103.21.244.0/22',
    '103.22.200.0/22',
    '103.31.4.0/22',
    '141.101.64.0/18',
    '108.162.192.0/18',
    '190.93.240.0/20',
    '188.114.96.0/20',
    '197.234.240.0/22',
    '198.41.128.0/17',
    '162.158.0.0/15',
    '104.16.0.0/13',
    '104.24.0.0/14',
    '172.64.0.0/13',
    '131.0.72.0/22',
];
```

Tapi lebih mudah dan aman menggunakan `'*'` untuk production dengan Cloudflare.

