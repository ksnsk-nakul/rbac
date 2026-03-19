# RBAC Starter Kit (Laravel 12 + Vue 3 + Inertia)

Production-ready RBAC + security foundation for Laravel apps:

- Role + permission system (grouped permissions)
- Super Admin bootstrap role
- Activity logs (append-only) + login activity
- MFA (TOTP), trusted devices, session management
- IP allowlist (role-based)
- Admin dashboard + settings module
- API tokens (scoped)
- Webhooks for security events

## Requirements

- PHP 8.2+
- Composer 2+
- Node.js 20+
- MySQL 8+ (recommended) and Redis (optional but supported)

## Quickstart (Local)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
```

Then open: `http://localhost:8080` (or your `APP_URL`)

## Demo Admin (Optional, Local Only)

By default, seeders do **not** create demo credentials.

Enable demo seeding in `.env`:

```env
SEED_DEMO_ADMIN=true
DEMO_ADMIN_EMAIL=admin@admin.com
DEMO_ADMIN_PASSWORD=1234567890
```

Then run:

```bash
php artisan db:seed --class=Database\\Seeders\\RoleSeeder
```

## One-Command Installer

```bash
php artisan app:install --seed
```

To reset database:

```bash
php artisan app:install --fresh --seed
```

## Docker

See `docs/INSTALLATION.md` for Docker + Apache instructions and DNS notes.

## Docs

- `docs/INSTALLATION.md` (Apache/Docker/DNS)
- `docs/PRODUCT.md` (product overview)
- `docs/ORG_TENANCY.md` (organizations/workspaces)
- `docs/BILLING_RAZORPAY.md` (Razorpay billing)
- `docs/LICENSING.md` (optional license enforcement)
- `docs/SUPPORT.md` (support tickets module)
- `docs/PRODUCT_TO_MARKET_PLAN.md` (release and sales plan)

## Notes

- This project uses Laravel Fortify with role-based login routes (`/login/{role}`).
- Theme color is configurable via settings and applied through CSS variables.
