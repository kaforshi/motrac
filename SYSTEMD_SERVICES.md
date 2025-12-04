# Setup Systemd Services untuk Motrac

Panduan untuk menjalankan Motrac menggunakan systemd services.

## Services yang Tersedia

### 1. Queue Worker Service
Menjalankan Laravel queue worker untuk memproses background jobs.

### 2. Scheduler Service (Opsional)
Alternatif untuk cron job, menjalankan Laravel scheduler secara kontinyu.

## Setup

### Opsi 1: Menggunakan Script (Recommended)

```bash
cd /var/www/motrac
sudo chmod +x setup-systemd-services.sh
sudo ./setup-systemd-services.sh
```

### Opsi 2: Manual Setup

#### 1. Copy Service Files

```bash
cd /var/www/motrac

# Copy queue worker service
sudo cp motrac-worker.service /etc/systemd/system/

# Copy scheduler service (opsional)
sudo cp motrac-scheduler.service /etc/systemd/system/
```

#### 2. Reload Systemd

```bash
sudo systemctl daemon-reload
```

#### 3. Enable dan Start Services

```bash
# Queue Worker
sudo systemctl enable motrac-worker.service
sudo systemctl start motrac-worker.service

# Scheduler (opsional, alternatif cron)
sudo systemctl enable motrac-scheduler.service
sudo systemctl start motrac-scheduler.service
```

## Management Commands

### Queue Worker

```bash
# Check status
sudo systemctl status motrac-worker

# Start
sudo systemctl start motrac-worker

# Stop
sudo systemctl stop motrac-worker

# Restart
sudo systemctl restart motrac-worker

# Enable on boot
sudo systemctl enable motrac-worker

# Disable on boot
sudo systemctl disable motrac-worker

# View logs
sudo journalctl -u motrac-worker -f
sudo journalctl -u motrac-worker --since "1 hour ago"
```

### Scheduler

```bash
# Check status
sudo systemctl status motrac-scheduler

# Start
sudo systemctl start motrac-scheduler

# Stop
sudo systemctl stop motrac-scheduler

# Restart
sudo systemctl restart motrac-scheduler

# View logs
sudo journalctl -u motrac-scheduler -f
```

## Konfigurasi

### Queue Worker

Edit service file jika perlu:
```bash
sudo nano /etc/systemd/system/motrac-worker.service
```

**Konfigurasi yang bisa diubah:**
- `--sleep=3` - Waktu tunggu antara jobs (detik)
- `--tries=3` - Jumlah retry jika gagal
- `--max-time=3600` - Maksimal waktu worker berjalan (detik)
- `numprocs=2` - Jumlah worker process (tambahkan di [Service] section)

**Untuk multiple workers:**
```ini
[Service]
Type=simple
ExecStart=/usr/bin/php /var/www/motrac/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Kemudian buat multiple instances:
```bash
sudo systemctl enable motrac-worker@1.service
sudo systemctl enable motrac-worker@2.service
```

### Scheduler

Jika menggunakan scheduler service, **hapus cron job**:
```bash
sudo crontab -e -u www-data
# Hapus baris: * * * * * cd /var/www/motrac && php artisan schedule:run
```

## Monitoring

### View Real-time Logs

```bash
# Queue worker logs
sudo journalctl -u motrac-worker -f

# Scheduler logs
sudo journalctl -u motrac-scheduler -f

# Both logs
sudo journalctl -u motrac-worker -u motrac-scheduler -f
```

### Check Service Health

```bash
# Status semua services
sudo systemctl status motrac-worker motrac-scheduler

# Check if running
sudo systemctl is-active motrac-worker
sudo systemctl is-active motrac-scheduler

# Check if enabled
sudo systemctl is-enabled motrac-worker
sudo systemctl is-enabled motrac-scheduler
```

## Troubleshooting

### Service tidak start

```bash
# Check status
sudo systemctl status motrac-worker

# Check logs
sudo journalctl -u motrac-worker -n 50

# Check permissions
ls -la /var/www/motrac
sudo chown -R www-data:www-data /var/www/motrac
```

### Service restart terus menerus

```bash
# Check logs untuk error
sudo journalctl -u motrac-worker -f

# Check .env file
cat /var/www/motrac/.env | grep QUEUE_CONNECTION

# Test artisan command manually
cd /var/www/motrac
sudo -u www-data php artisan queue:work --once
```

### Permission Denied

```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/motrac
sudo chmod -R 775 /var/www/motrac/storage
sudo chmod -R 775 /var/www/motrac/bootstrap/cache
```

## Multiple Workers

Untuk menjalankan multiple queue workers:

### Opsi 1: Multiple Service Files

```bash
# Copy service file
sudo cp /etc/systemd/system/motrac-worker.service /etc/systemd/system/motrac-worker-1.service
sudo cp /etc/systemd/system/motrac-worker.service /etc/systemd/system/motrac-worker-2.service

# Edit untuk menggunakan queue berbeda (jika ada)
sudo nano /etc/systemd-system/motrac-worker-1.service
# Change: queue:work --queue=high,default

sudo nano /etc/systemd-system/motrac-worker-2.service
# Change: queue:work --queue=low

# Reload and start
sudo systemctl daemon-reload
sudo systemctl enable motrac-worker-1 motrac-worker-2
sudo systemctl start motrac-worker-1 motrac-worker-2
```

### Opsi 2: Supervisor (Recommended untuk Multiple Workers)

Gunakan Supervisor untuk manage multiple workers:
```bash
sudo apt install supervisor
sudo nano /etc/supervisor/conf.d/motrac-worker.conf
```

```ini
[program:motrac-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/motrac/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/motrac/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start motrac-worker:*
```

## Best Practices

1. **Gunakan Supervisor untuk Multiple Workers** - Lebih mudah manage
2. **Monitor Logs** - Setup log rotation
3. **Set Max Time** - Prevent memory leaks
4. **Use Queue Connection** - Database untuk production
5. **Monitor Resources** - Check CPU dan memory usage

## Log Rotation

Setup log rotation untuk worker logs:

```bash
sudo nano /etc/logrotate.d/motrac-worker
```

```
/var/www/motrac/storage/logs/worker.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        supervisorctl restart motrac-worker:*
    endscript
}
```

## Update Service

Setelah update aplikasi:

```bash
# Restart services
sudo systemctl restart motrac-worker
sudo systemctl restart motrac-scheduler

# Check status
sudo systemctl status motrac-worker
```

## Disable Services

Jika tidak menggunakan:

```bash
# Stop services
sudo systemctl stop motrac-worker
sudo systemctl stop motrac-scheduler

# Disable on boot
sudo systemctl disable motrac-worker
sudo systemctl disable motrac-scheduler

# Remove service files (optional)
sudo rm /etc/systemd/system/motrac-worker.service
sudo rm /etc/systemd/system/motrac-scheduler.service
sudo systemctl daemon-reload
```

