#!/bin/bash

# Script untuk melihat error lengkap dari Laravel log
# Usage: ./view-full-error.sh

echo "========================================="
echo "  Laravel Error Viewer"
echo "========================================="
echo ""

APP_DIR="/var/www/motrac"

if [ ! -d "$APP_DIR" ]; then
    echo "Application directory not found!"
    exit 1
fi

cd $APP_DIR

if [ ! -f "storage/logs/laravel.log" ]; then
    echo "Log file not found!"
    exit 1
fi

echo "Last error in Laravel log:"
echo "========================================="
echo ""

# Get last error block - show more context
echo "Last 100 lines of log:"
echo ""
tail -100 storage/logs/laravel.log

echo ""
echo "========================================="
echo "Searching for errors/exceptions:"
echo ""
tail -200 storage/logs/laravel.log | grep -A 10 -B 5 -i "error\|exception\|fatal\|invalid" | tail -50

echo ""
echo "========================================="
echo ""
echo "To see real-time logs:"
echo "  tail -f storage/logs/laravel.log"
echo ""
echo "To see last 50 lines:"
echo "  tail -50 storage/logs/laravel.log"
echo ""

