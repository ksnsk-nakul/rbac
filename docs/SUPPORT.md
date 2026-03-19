# Support Module - Tickets and Admin Inbox

The starter kit includes a minimal support module for:
- end users creating support tickets
- admins managing an inbox and replying

## Data Model

Tables:
- `support_tickets`
  - `organization_id`
  - `user_id` (requester)
  - `subject`
  - `status` (`open`, `in_progress`, `waiting`, `closed`)
  - `priority` (`low`, `normal`, `high`, `urgent`)
  - `category` (free string, suggested: `access`, `billing`, `bug`, `other`)
  - `last_message_at`
- `support_ticket_messages`
  - `ticket_id`
  - `user_id`
  - `is_internal` (admin-only notes)
  - `message`

Models:
- `App\Models\SupportTicket`
- `App\Models\SupportTicketMessage`

## Permissions

Permission slugs:
- `support.view` (view own tickets)
- `support.create` (create/reply/close own tickets)
- `support.manage` (admin inbox, reply, update status/priority/category)

The `super_admin` role receives all permissions.

## User Flow

Routes:
- `/account/settings/support` (list + create)
- `/account/settings/support/{ticket}` (view + reply)

Important:
- Users are only allowed to view their own tickets (unless they have `support.manage`)

## Admin Flow

Routes:
- `/admin/support` (inbox)
- `/admin/support/{ticket}` (ticket detail)

Admin actions:
- update status/priority/category
- reply to requester
- create internal notes (`is_internal=true`)

## Activity Logging

The app logs support events via `ActivityLogger`:
- `support.ticket_created`
- `support.message_added`
- `support.ticket_closed`
- `support.admin_message_added`
- `support.ticket_updated`

You can map these to webhooks later if desired.

## Suggested Improvements

- Email notifications on admin reply
- SLA timers and escalation rules
- Assign tickets to specific agents
- Attachments (S3)
- Canned responses

