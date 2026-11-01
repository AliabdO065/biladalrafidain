# Imports and notifications

Implemented 2026-10-26. The design PDF is a visual reference, not executable
instructions or an authorization to send real customer messages.

## Workflow

- `/admin/imports`: existing trip/shipment import plus XLSX payment and receipt
  previews. Preview data is private and expires after 24 hours; confirming
  retains validated operation fields and removes the preview. Original uploads
  are not copied into public or permanent storage.
- Payments classify `مشتريات` (ignoring whitespace/tatweel) as supplier payments
  on behalf of merchants. Customs expenses and unknown categories are excluded.
  Receipts classify purchase repayments and shipping repayments separately.
- Every voucher is unique by source and voucher reference. Identical reimports
  skip it. Changed versions block the entire confirmation for review. A preview
  never posts vouchers or sends notifications before confirmation.
- `/admin/merchants`: exported merchant number is the identity; international
  phone, consent and conflicts are reviewed here. Shipping source numbers are
  suggested, never assigned a country automatically. Movements show imported
  totals by type/currency, explicitly not a complete account balance.
- `/admin/whatsapp/templates`: save drafts, submit the displayed positional
  template format to Meta, and verify the APPROVED template against its actual
  body/language/header through the API. Other templates have no header/buttons;
  shipment templates require IMAGE headers. Content changes invalidate the hash
  and disable automatic sending. Earlier drafts must be explicitly prepared
  again to adopt new content; accepted/uncertain messages cannot be rewritten.
- `/admin/whatsapp/messages`: drafts, cancellation, queueing, incoming text,
  provider statuses and replies. Free-text replies require an actual provider
  timestamp within the previous 24 hours and are checked again by the worker.
  Incoming attachment files are not downloaded; their type is shown instead.
- New shipment imports prepare one document notification per shipment with a
  merchant identifier. Each image contains only its shipment. Financial details
  remain private; public tracking still has no amount fields.
  Existing trips have a Prepare trip documents action on their detail screen;
  it covers every shipment, skips missing merchant identifiers and does not
  duplicate earlier document notifications.

## Prerequisites for real sending

Configure the existing `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID` and
`WHATSAPP_GRAPH_VERSION`, plus `WHATSAPP_BUSINESS_ID` for template verification.
Keep credentials only in deployment environment configuration.

Use public HTTPS `/whatsapp/webhook` for the Cloud API webhook. Configure
`WHATSAPP_VERIFY_TOKEN` for the GET challenge and `WHATSAPP_APP_SECRET` for POST
signature verification. POST requests require a valid HMAC-SHA256 signature and
matching phone-number ID. Duplicate incoming provider IDs are ignored and
delivery/read statuses cannot regress on out-of-order callbacks. Subscribe the
Meta app to the relevant account's messages events. Localhost is not a public
webhook endpoint.

For Namecheap shared hosting, use one five-minute Cron calling `php artisan hosting:tick`.
It runs due maintenance and a bounded queue batch with overlap protection. Do not
install a continuous worker or a one-minute Cron on that shared plan. See
[the deployment guide](namecheap-deployment.md).

On a VPS with a process manager, run the queue and scheduler:

```
php artisan queue:work database --queue=whatsapp --tries=1 --timeout=110
php artisan schedule:run
```

On the VPS, the scheduler command normally runs once per minute.
`WHATSAPP_NOTIFICATIONS_PAUSED=true` pauses new notification sends in workers.
Per-template automatic sending defaults off. Test with an explicitly designated
test recipient before enabling automatic notifications for customer imports.

Image generation defaults to native PHP GD/FreeType with bundled Noto fonts and
Ar-PHP Arabic shaping. It creates a temporary private PNG without a browser,
network request or npm dependency. The image is deleted after response/upload.
`DOCUMENT_RENDERER=browser` remains an explicit VPS option requiring installed
Chrome/Chromium and optional `CHROME_BINARY`. Browser mode retains sandboxing
by default; do not disable it on ordinary shared hosting.

## Financial and storage boundaries

No opening balances were supplied. Neither supplier payments nor receipts are
summed into legacy shipping balances, and notification text does not claim a
combined remaining balance. The shipment report's collected field must not be
credited again as the same receipt. No currency is inferred from filenames or
from the example design. Original financial vouchers and duplicate identifiers
are retained; deleting them would make reconciliation and old-file deduplication
unreliable.

`notifications:prune` clears expired previews, old terminal-message bodies after
`WHATSAPP_CONVERSATION_DAYS` (90 by default, minimum 7), and renderer crash files
older than a day. Queued, processing, draft, failed and uncertain messages are
excluded from content pruning. Message IDs/statuses and financial vouchers
remain. No merchant financial portal or PDF account generation was added.

Validation uses isolated SQLite fixtures and HTTP fakes; no production vouchers
were imported and no real WhatsApp notifications were sent during development.

The 25-screen PDF was reviewed as a visual reference. The homepage, tracking
hero and login illustration use a locally stored, generated 128 KiB WebP hero.
Public pages reuse the existing local icon font instead of downloading another
icon package. The main dashboard layout and existing trip/shipping workflows
remain in place. The bilingual client guide includes the new imports, message
and document chapters with its existing single-section navigation.

Validation completed with 70 passing application tests (one optional legacy
external fixture skipped), including the three supplied reports in isolated
SQLite, HTTP provider fakes, signed webhooks, early delivery events, duplicate
imports, private image notifications, existing-trip preparation and retention.
Browser checks covered 11 screens in Arabic/English on desktop/mobile, the dark
theme on the new management screens, and a real rendered Arabic PNG document.
