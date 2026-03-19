# RBAC SaaS Starter Kit (Laravel + Vue) - Product Documentation

This repository is a production-ready starter kit for building RBAC-based SaaS applications with:
- Laravel + Fortify auth
- Inertia + Vue 3 frontend
- Role/permission RBAC middleware
- Enterprise-grade audit logging (append-only with hash chain)
- MFA (TOTP), trusted devices, IP allowlists
- Organization (workspace) foundation
- Billing (Razorpay subscription MVP)
- Licensing (optional, for distributing the starter kit)
- Support tickets module

## Architecture Overview

Backend:
- Laravel controllers return Inertia pages (`Inertia::render(...)`)
- Permission checks use `permission:<slug>` middleware
- Activity logs are written via `App\Services\ActivityLogger`
- Settings are stored in the `settings` table and exposed to the frontend via Inertia shared props

Frontend:
- Vue 3 pages live in `resources/js/pages`
- Shared UI/layouts live in `resources/js/components` and `resources/js/layouts`
- Theme uses CSS variables (primary color) and dark mode via `class="dark"`

## Key Concepts

### Roles and Permissions
- Roles are stored in `roles` and permissions in `permissions`
- Many-to-many role-permissions: `permission_role`
- `RoleSeeder` is idempotent: it can be run multiple times safely

### Organization (Workspace)
- Every user can belong to one or more organizations (workspaces)
- `users.current_organization_id` controls scoping for most queries
- Middleware `ensure.org` ensures an organization is selected

### Plans and Subscriptions
- Plans are stored in `plans`
- Subscriptions are stored in `subscriptions` and linked to `organizations`
- Razorpay webhook events sync subscription state and update organization plan

### Security Baseline
- MFA can be required per role
- Role-based IP allowlists can restrict access
- Security headers middleware is enabled for common hardening

## First Run (Local)

1. Copy env:

```bash
cp .env.example .env
php artisan key:generate
```

2. Install and seed:

```bash
php artisan app:install --seed
```

Optional demo admin for local/dev:

```env
SEED_DEMO_ADMIN=true
DEMO_ADMIN_EMAIL=admin@admin.com
DEMO_ADMIN_PASSWORD=1234567890
```

## Admin Areas

- Admin dashboard: `/admin/dashboard`
- Roles & permissions: `/admin/management/roles`
- Security dashboard: `/admin/security`
- Support inbox: `/admin/support`
- Billing: `/admin/billing`
- Integrations: `/admin/integrations`
- App settings: `/admin/settings`

## Account Areas

- Profile: `/account/settings/profile`
- Security: `/account/settings/security`
- Sessions: `/account/settings/sessions`
- Activity: `/account/settings/activity`
- API tokens: `/account/settings/api-tokens`
- Support tickets: `/account/settings/support`

## Documentation Map

- Installation / Apache / Docker / DNS: `docs/INSTALLATION.md`
- Organizations: `docs/ORG_TENANCY.md`
- Razorpay billing: `docs/BILLING_RAZORPAY.md`
- Licensing: `docs/LICENSING.md`
- Support: `docs/SUPPORT.md`
- Product-to-market plan: `docs/PRODUCT_TO_MARKET_PLAN.md`

