# Client usage guide maintenance

The dashboard client guide is an ongoing part of the product. When changing a
customer-visible workflow, action, field, calculation, validation, import format,
sending behavior, or available feature:

- Update the relevant instructions and FAQs in both `lang/ar/guide.php` and
  `lang/en/guide.php` as part of the same change.
- Keep the guide accurate about prerequisites and limitations. Distinguish
  preparing a statement, manual WhatsApp sending, provider acceptance and delivery.
- Keep chapter IDs stable for bookmarked links. Adjust links if routes change.
- Update `config/guide.php`'s `updated_at` to the date of the content change.
- Reuse application constants for changing lists where practical. The guide
  already reads shipment stages and spreadsheet column headings from source.
- Verify the guide in Arabic and English, including search when its UI changes.

Guide UI: `resources/views/dashboard/help.blade.php`,
`public/dashboard/assets/stylesheets/guide.css`, and
`public/dashboard/assets/javascript/guide.js`. It is available at `/admin/help`
behind dashboard authentication. Keep it free of credentials and internal setup
commands; developer setup belongs in `docs/operations.md`.
