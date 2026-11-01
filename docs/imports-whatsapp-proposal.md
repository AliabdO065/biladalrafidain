# Three imports and WhatsApp — proposal, 2026-10-26

This records the initial requirements assessment. The implemented import,
notification, inbox and document workflows are documented in `notifications.md`.
No customer records or messages were created during the initial inspection.

## Confirmed intent

- Payments classified as purchases mean money paid to a supplier on a merchant's
  behalf. The notification must describe that payment, not assert that goods were
  purchased, received or shipped.
- The requested customer balance includes purchases and shipping, with separate
  subtotals and an aggregate only within the same confirmed currency.
- Shipment imports should generate individual shipment documents inspired by
  the supplied navy/blue reference. Each merchant receives only their own data.
- The administrator wants template management and two-way WhatsApp conversations.
- Keep storage small; do not retain original uploads indefinitely or duplicate
  the external accounting system.

## Observed spreadsheet structure

| Input | Available fields | Important limits |
| --- | --- | --- |
| Shipment receipt report | Supplier and merchant numbers, merchant phone, trip, parcels, weight, item price, fee, collected, balance, total, notes, receipt/date columns | Sample receipt/date cells are blank; no dedicated goods-type or packaging charge columns. Do not infer business meanings for Total or item price without confirmation. |
| VoucherPayments | Voucher identifiers, date, operation type, supplier, merchant number/name, amount, description, trip | Mixed categories: sample has five purchases and two customs expenses. No phone, currency, parcel count, weight or purchase status. |
| VoucherReceipts | Voucher identifiers, date, operation type, merchant number/name, account, amount, description, trip | Sample has three purchase repayments and one shipping repayment. No phone, currency or remaining balance. Free-text physical receipt references can differ from the exported voucher identifier. |

Use a persistent merchant directory keyed by the exported merchant identifier,
with a confirmed international WhatsApp number and currency settings. Do not
join vouchers to shipments by trip number alone or infer goods details from an
unrelated shipment on the same trip. Repeated headings/totals are not operations.

## Balance recommendation

Establish a verified opening balance per merchant, ledger and currency at an
agreed cutoff. Account for only subsequent, unprocessed movements, using the
confirmed sign convention and including all relevant movements/reversals.
Supplier payments on behalf of a merchant and shipping charges normally increase
the amount owed; receipts normally decrease it. Verify this convention against
an actual merchant statement before enabling balance notifications.

Do not also credit the shipment report's collected amount when the same payment
is imported as a receipt. Opening balances must not overlap imported history.
Compare to an authoritative closing statement; missing files or unresolved
corrections make a computed balance unreliable. Until balances are verified,
omit the remaining-balance field and send only the confirmed payment receipt.
Never substitute zero for an unknown opening balance or a missing currency.

## Proposed workflow

Upload -> validate -> identify operation categories and merchants -> preview
included/excluded/invalid rows -> confirm import. After automatic sending is
explicitly enabled, validated new operations can queue notifications without a
second send action. Initial deployment should default to review mode, with a
pause switch, test recipient and per-type enable/disable controls.

Store stable operation identifiers with source type and import provenance;
re-importing the same operation must neither change balances twice nor resend a
notification. Changed operations require a reviewed correction. Queued messages
must retain the exact operation/template version used, and uncertain deliveries
must not be blindly retried.

Approved WhatsApp templates are needed for business-initiated notifications
outside the customer service window; support a media template for the shipment
image. Editing a dashboard draft does not automatically approve a new WhatsApp
template. Add authenticated webhook processing for inbound messages and delivery
events, deduplicated by provider message identifier. Support replies according
to the current service window and approved-template rules. A public HTTPS
deployment and official WhatsApp account are prerequisites; localhost and manual
wa.me links alone cannot provide automatic sending and an inbox.

## Storage approach

- Parse and validate uploads, then discard original spreadsheets after processing.
- Store merchant records once and minimal structured financial/source metadata.
- Generate shipment images on demand; keep transient media only for a short,
  configurable retry window and remove it only when no queued send needs it.
- Keep recent conversation content for a configurable period, with essential
  message identifiers/statuses retained separately.
- Archive reconciled historical detail with verified closing/opening balances
  before pruning it; retain compact processed-operation identifiers to prevent
  old imports from duplicating balances or messages.
- If merchants should view historical documents in a portal, retention must
  cover that promise. The current product has public shipment tracking, not a
  merchant login/financial portal; remove that promise from messages until built.

## Still needed

- The design PDF was subsequently supplied and all 25 pages reviewed. Public
  website and document styling use it as a visual reference; the existing
  dashboard layout remains intact.
- Verified opening/closing balance source and cutoff, currency confirmation,
  merchant phone directory and intended document grouping for multi-line cargo.
- Meaning/source of goods type, packaging charges, supplier-payment display,
  shipment document date and displayed receipt number.
- Official WhatsApp onboarding, recipient consent, approved templates and a
  reachable deployment. Charges depend on provider and message usage.

Sources checked: https://www.twilio.com/docs/whatsapp/api and
https://www.twilio.com/docs/whatsapp/tutorial/message-template-approvals-statuses

Once implemented, update both client guide languages and its update date as
required by AGENTS.md.
