# Billing (Razorpay) - Setup and Operations

This project includes a minimal subscription billing MVP using Razorpay.

It uses:
- Server-side Razorpay API calls via Laravel HTTP client (no SDK requirement)
- Webhook signature verification
- Local `subscriptions` and `payment_events` tables for state sync and idempotency

## Settings / Configuration

Environment variables (recommended for production):

```env
RAZORPAY_KEY_ID=rzp_test_xxx
RAZORPAY_KEY_SECRET=xxx
RAZORPAY_WEBHOOK_SECRET=xxx
```

Admin UI (settings table):
- `/admin/integrations`
  - `payment.razorpay_key_id`
  - `payment.razorpay_key_secret`
  - `payment.razorpay_webhook_secret`

Preference order:
1. Settings (`payment.*`)
2. Env vars (`RAZORPAY_*`)

## Plans

Plans live in the `plans` table.

Recommended fields:
- `monthly_price` / `yearly_price`
- `currency` (default `INR`)
- `razorpay_plan_id_monthly` / `razorpay_plan_id_yearly`

Important:
- Razorpay requires you to create plans on Razorpay first and store the plan IDs in your app.

## Checkout Flow

Route:
- `POST /admin/billing/checkout/{plan}`

Behavior:
- Creates a Razorpay subscription
- Creates a local `subscriptions` record
- Returns an Inertia page that triggers Razorpay Checkout on the client

## Webhooks

Endpoint:
- `POST /webhooks/razorpay`

Signature header:
- `X-Razorpay-Signature`

The app:
- verifies signature using `payment.razorpay_webhook_secret` (or `RAZORPAY_WEBHOOK_SECRET`)
- stores each webhook in `payment_events` (idempotent by `provider_event_id`)
- syncs local subscription status
- upgrades/downgrades the organization plan on subscription activation

## Events Supported (MVP)

The webhook handler is designed to accept common subscription lifecycle events such as:
- subscription created / authenticated / activated
- payment captured / failed
- subscription cancelled

If you add new event types:
- keep event handling idempotent
- always store raw payload into `payment_events` for later debugging

## Security Notes

- Never log secrets (`RAZORPAY_KEY_SECRET`, webhook secrets) to application logs.
- Use HTTPS for webhook endpoints.
- Set `APP_ENV=production` and `APP_DEBUG=false` in production.

## Troubleshooting

1. Webhook signature errors:
- Ensure you used the correct webhook secret (Razorpay dashboard)
- Confirm the raw body is not modified before verification

2. Subscription not updating:
- Check `payment_events` table for incoming webhooks
- Confirm the organization has a valid `subscriptions` row

