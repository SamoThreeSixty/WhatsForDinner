#!/usr/bin/env sh

set -eu

echo "[railway-start] Building frontend assets with runtime environment..."
npm run build

echo "[railway-start] Running database migrations..."
php artisan migrate --force
