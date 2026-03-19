# Organizations (Workspace) - Developer Notes

This starter kit includes a lightweight organization (workspace) foundation. It is not full multi-tenancy (no separate DB per tenant), but it provides the data model and middleware required to scope data per organization.

## Data Model

Tables:
- `organizations`
  - `owner_user_id`
  - `plan_id`
  - `billing_provider`
- `organization_users` (membership)
  - `organization_id`
  - `user_id`
  - `org_role` (owner/member)
  - `status` (active/invited/disabled)
- `users.current_organization_id`

Models:
- `App\Models\Organization`
- `App\Models\OrganizationUser`

## Middleware

`ensure.org` (registered in `bootstrap/app.php`) ensures `current_organization_id` exists.

Behavior:
- If missing, it selects the first active organization for that user.
- If no organization is available, it returns a `403` (the user needs a workspace).

## Registration Behavior

Fortify action `CreateNewUser` creates:
- a new `Organization` for the user
- an `OrganizationUser` membership row
- sets `users.current_organization_id`

## Switching Organizations

Endpoint:
- `POST /organizations/switch`

Expected payload:

```json
{ "organization_id": 123 }
```

Security notes:
- The controller must ensure the user is a member of that organization.
- All scoped queries should use `auth()->user()->current_organization_id`.

## Scoping Guidance

Recommended pattern:
- For any business table, add `organization_id` and index it.
- Always filter by `organization_id = current_organization_id`.

Examples:
- `support_tickets.organization_id`
- `activity_logs.organization_id`
- `login_activity_logs.organization_id`
- `subscriptions.organization_id`

## Future Expansion (Optional)

Common next steps:
- Invitations (email invite flows)
- Organization roles (org-level roles separate from global roles)
- Per-organization RBAC policies (best when you introduce multi-tenant admin roles)

