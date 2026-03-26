#!/usr/bin/env bash

set -eu

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$APP_DIR"

PHP_BIN="${PHP_BIN:-}"
if [ -z "$PHP_BIN" ]; then
    PHP_BIN="$(command -v php || true)"
fi

if [ -z "$PHP_BIN" ]; then
    echo "php binary not found" >&2
    exit 1
fi

mkdir -p storage/logs

"$PHP_BIN" artisan schedule:run --no-interaction >> storage/logs/cron-schedule.log 2>&1
"$PHP_BIN" artisan queue:work --once --queue=default --tries=3 --no-interaction >> storage/logs/cron-queue.log 2>&1
