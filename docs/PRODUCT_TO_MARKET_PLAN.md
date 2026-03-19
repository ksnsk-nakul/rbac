# Product-to-Market Plan (Developer SaaS Starter Kit)

Goal: ship this codebase as a sellable asset that other developers can install quickly, brand easily, and extend safely.

This plan is written for a solo developer and assumes you want to sell:
- as a starter kit (source download)
- and optionally as a hosted SaaS later

## Phase 1: Code Audit for Portability

Outcome: someone new can clone, configure, and run the app in < 30 minutes.

Technical checklist:
- `.env.example` includes all required keys with safe defaults
- `php artisan app:install --seed` works on a fresh DB
- seeders are idempotent (`firstOrCreate`, `sync`, no duplicates)
- demo credentials are gated behind `SEED_DEMO_ADMIN=true`
- no hardcoded local paths, URLs, or hostnames (especially Docker DB host)
- error pages exist and are consistent
- `npm run build` succeeds

Deliverables to add for sales:
- `docs/` complete documentation set
- post-install “first login” instructions
- quick branding settings (name/logo/favicon/theme)

## Phase 2: Account & Organization Logic

Outcome: clean onboarding for real customers.

Recommendation:
- Keep Fortify + Inertia Vue auth flow (already integrated)
- Use Organization (workspace) as the “tenant” primitive

Technical checklist:
- registration creates org + membership
- org switching endpoint exists (membership-validated)
- middleware ensures org is selected
- all sensitive tables are scoped by `organization_id`

## Phase 3: Pricing & Subscriptions

Outcome: paid plans with feature gating.

Provider recommendation (given your preference):
- Razorpay subscriptions for India-first customers
- keep Stripe as future optional provider

Technical checklist:
- plans seeded with flags: retention, api tokens, ip allowlist, etc.
- webhooks verify signature and are idempotent
- subscription state sync updates organization plan
- PlanGate enforces features (UI + backend)

## Phase 4: Security & Licensing

Outcome: safer product and a defensible “enterprise-ready” value proposition.

Technical checklist:
- MFA required for privileged roles
- trusted device prompts and revocation
- session management and revoke
- IP allowlist support
- audit logs append-only with tamper detection (hash chain)
- audit retention policy per plan
- optional license enforcement for distributed source builds

Notes on selling on marketplaces:
- Source protection is limited. Best defense is strong docs, support, and updates.
- License enforcement should be optional and non-destructive.

## Phase 5: Support & Maintenance

Outcome: reduce support load and increase perceived product quality.

Technical checklist:
- in-app support tickets (user + admin inbox)
- activity logs and export for debugging
- integrations settings page for email/sms/payment keys
- “Support dashboard” view: open tickets, recent security events, health checks

## Pricing Suggestions (Starter Kit)

Suggested tiers for a developer product:

1. Starter (source only)
- $49 to $99
- RBAC + dashboard + theme + audit logs basic

2. Pro (source + modules)
- $149 to $299
- MFA, sessions, IP allowlist, billing module, support module

3. Enterprise (license + priority support)
- $499+
- audit retention, advanced exports, webhook integrations, custom onboarding

If you sell as SaaS later, translate these to monthly tiers.

## Release Checklist (Before Selling)

- add screenshots / demo video
- add seed script to generate demo data
- write “common issues” troubleshooting guide
- add upgrade guide and changelog
- add automated tests for critical flows (auth, RBAC, org switch, billing webhook)

