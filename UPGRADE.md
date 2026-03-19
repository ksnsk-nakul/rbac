# Upgrade Guide

This document describes how to upgrade between versions of the RBAC Starter Kit.

## General Upgrade Steps

1. Back up database and `.env`
2. Pull the new version of the code
3. Install dependencies:

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
npm install
npm run build
```

4. Run migrations:

```bash
php artisan migrate --force
```

5. Clear caches:

```bash
php artisan optimize:clear
```

6. Restart workers / containers:
- systemd queue worker or supervisor
- or `docker compose restart app queue nginx`

## 1.0.0

Initial release; no upgrade steps.

