# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project aims to follow Semantic Versioning.

## [Unreleased]

## [1.0.0] - 2026-03-19

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
- Documentation set in `docs/`

