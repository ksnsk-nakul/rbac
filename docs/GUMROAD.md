# Gumroad Launch Pack (v1.0)

This file contains ready-to-paste content for your Gumroad product page.

## Product Name

RBAC SaaS Starter Kit (Laravel 12 + Vue 3 + Inertia)

## Tagline

Production-ready authentication, RBAC, security, auditing, billing (Razorpay), and add-ons for Laravel SaaS apps.

## Short Description

Build admin portals and SaaS products faster with a clean RBAC foundation: roles & permissions, MFA, audit logs, session management, org/workspaces, Razorpay billing, and a zip-based add-on system.

## What You Get

- Full Laravel + Vue 3 + Inertia source code
- Idempotent seeders and one-command installer (`php artisan app:install --seed`)
- Production-grade audit logs (append-only + hash chain)
- MFA (TOTP), trusted devices, session revoke, IP allowlist
- Organizations/workspaces foundation
- Billing MVP (Razorpay subscriptions + webhooks)
- Settings module (theme, logo/favicon, integrations)
- Support ticket module
- Zip-based add-on installer + module manager UI
- Documentation in `docs/`

## Key Features

- RBAC:
  - Roles & permissions grouped by modules
  - Permission middleware checks (backend + UI hiding)
- Security:
  - MFA enforcement for privileged roles
  - Trusted device prompts
  - Session management + revoke
  - IP allowlist by role
- Audit:
  - Activity logs and login logs
  - Export support
  - Retention policies by plan
- SaaS foundation:
  - Organization/workspace model
  - Plans + feature gating
- Billing:
  - Razorpay subscription checkout + webhook sync
- Add-ons:
  - Upload add-on zip and enable from UI (`/admin/modules`)
  - Optional API key gating for paid add-ons

## Tech Stack

- Laravel 12
- Fortify auth
- Inertia + Vue 3
- Tailwind
- MySQL (recommended) + Redis (optional)

## Installation (Quickstart)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan app:install --seed
npm install
npm run dev
```

Docker + Apache + DNS notes: `docs/INSTALLATION.md`

## Demo Credentials (Optional, Local Only)

Seed a demo super admin:

```env
SEED_DEMO_ADMIN=true
DEMO_ADMIN_EMAIL=admin@admin.com
DEMO_ADMIN_PASSWORD=1234567890
```

Then:

```bash
php artisan db:seed --class=Database\\Seeders\\RoleSeeder
```

## License

See `LICENSE.md` (commercial license).

## Support

Suggested support statement:
- Support includes installation questions and bug reports for the current version.
- Feature requests/customization are not included (available separately).

## Refund Policy (Recommended)

Digital product refunds should be limited. Example:
- Refunds only if the product is fundamentally broken on supported versions and we can’t resolve it.

## FAQ

**Does it support Stripe?**
- Billing MVP is Razorpay-first. Stripe can be added later if needed.

**Is it multi-tenant?**
- It includes an organization/workspace foundation (single database, scoped by `organization_id`).

**Can I sell a SaaS built with this?**
- Yes. The license restricts reselling the starter kit itself, not your end product.

