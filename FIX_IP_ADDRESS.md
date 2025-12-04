# Fix IP Address Configuration

## Masalah: IP 127.0.0.0 tidak valid

**127.0.0.0** adalah network address, bukan host address. Untuk localhost, gunakan **127.0.0.1**.

## Perbaikan

### 1. Test dengan IP yang Benar

```bash
# Test dengan 127.0.0.1 (benar)
curl http://127.0.0.1:8001

# Test dengan localhost
curl http://localhost:8001
```

### 2. Update Cloudflare Tunnel Config (Server 1)

Jika menggunakan SSH tunnel atau localhost connection:

```bash
# Di Server 1
sudo nano /etc/cloudflared/config.yml
```

**Jika menggunakan SSH tunnel:**
```yaml
  - hostname: motrac.aryaintaran.dev
    service: http://127.0.0.1:8001  # Bukan 127.0.0.0
```

**Jika direct connection ke Server 2:**
```yaml
  - hostname: motrac.aryaintaran.dev
    service: http://SERVER2_IP:8001  # Gunakan IP Server 2 yang benar
```

Restart tunnel:
```bash
sudo systemctl restart cloudflared
```

### 3. Check Nginx Config (Server 2)

```bash
# Di Server 2
sudo nano /etc/nginx/sites-available/motrac
```

Pastikan tidak ada referensi ke 127.0.0.0. Nginx config biasanya tidak perlu IP di listen, cukup:
```nginx
listen 8001;
listen [::]:8001;
```

### 4. Test Connection

```bash
# Di Server 2 - Test local
curl http://127.0.0.1:8001
curl http://localhost:8001

# Di Server 1 - Test ke Server 2
curl http://SERVER2_IP:8001
```

## IP Address yang Benar

- **127.0.0.1** = localhost (benar)
- **127.0.0.0** = network address (salah untuk host)
- **localhost** = alias untuk 127.0.0.1

## Quick Fix

```bash
# Di Server 2
curl http://127.0.0.1:8001  # Test dengan IP yang benar

# Jika berhasil, update Cloudflare Tunnel config di Server 1
# Ganti 127.0.0.0 dengan 127.0.0.1 (jika SSH tunnel)
# Atau gunakan SERVER2_IP (jika direct)
```

