# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project aims to follow Semantic Versioning.

## [Unreleased]

## [1.0.0] - 2026-03-21

Initial commercial release.

### Added
- RBAC (roles, permissions, permission middleware)
- Super Admin bootstrap role + idempotent seeders
- Admin dashboard + settings + integrations
- Activity logs (append-only hash chain) + login activity
- MFA (TOTP), trusted devices, session management, IP allowlist
- Organizations (workspace) foundation + org switching
- Billing MVP (Razorpay subscriptions + webhook sync)
- Support tickets (account tickets + admin inbox)
- Optional license enforcement middleware
- Modules manager with zip-based add-on installer (base hub ships with no modules preinstalled)
- Admin/user listings with global query filtering and list/grid views
- Global top search that filters the sidebar menu
- Documentation set in `docs/`
