# Licensing (Optional) - Protecting Distributed Source

If you distribute this starter kit (CodeCanyon, Gumroad, private sales), you may want basic license enforcement.

This repo includes an optional licensing layer that can be enabled in production.

## What It Does (MVP)

- Stores a license key in `settings` (key: `license.key`) or `.env`
- Tracks license "instances" (host/app fingerprint) in the database
- Blocks protected routes when:
  - `LICENSE_ENFORCE=true`
  - `APP_ENV=production`
  - the license key is missing/invalid

## Enabling Enforcement

In `.env`:

```env
APP_LICENSE_KEY=your-license-key
LICENSE_ENFORCE=true
APP_ENV=production
APP_DEBUG=false
```

## Middleware

Middleware alias:
- `ensure.license`

Applied to:
- dashboard/admin route groups
- account settings
- admin management routes

## Issuing Keys

For a starter kit product you can:
- generate keys in your own admin tool
- email keys to customers
- optionally validate against your licensing server (future)

Current MVP is designed to support:
- local key format validation
- instance tracking (to detect abuse)

## Recommended Improvements (If You Sell at Scale)

- Use a remote licensing API with signed responses
- Add offline grace period (e.g., 7 days)
- Add license revocation
- Show admin UX explaining the enforcement failure

## Notes for SaaS (Hosted)

If you deploy as SaaS, you usually do not need license enforcement.
Instead, enforce access via your subscription system (plans, entitlements).

