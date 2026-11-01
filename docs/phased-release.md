# Current delivery: phases 1 and 2

Enabled: public company website, services, callback requests, policies, Arabic and
English, administrator login, dashboard guide/settings, manual trip and shipment
entry, stage updates and public tracking. Shipment spreadsheet upload stays disabled.

Deferred: merchant accounts and statement history, manual WhatsApp sending UI,
all provider WhatsApp tools and webhooks, voucher uploads, merchant directory,
financial bill documents, public catalogue and factory registration/login/portal.
Administrator authentication remains required. No customer data has been deleted.

The deferred routes in `routes/web.php` are commented out, not merely hidden in
navigation. Direct requests return 404. UI entry points are retained in Blade
comments. Dynamic dashboard/workspace links omit unavailable named routes.
`config/whatsapp.php` forces automatic notification processing to remain paused.
Do not run a separate queue worker for deferred notification jobs in this release.

For phase 3, restore the account/trade routes and their matching commented UI,
then review the saved manual account workflow before exposing it. Automatic API
sending remains a separate decision. For phase 4, restore catalogue/factory
routes, homepage sections, catalogue data queries and navigation together.
Remove the phase-specific CSS include when restoring the marketplace layout.
Restore guide chapters through `config/guide.php`, with bilingual content review.

Before pushing live, take a database/media backup. After deploying source, clear
old Laravel route/config/view caches; rebuild the normal production caches using
the deployment instructions. This change introduces no database migration.
The earlier `dist/namecheap-release.zip` predates this delivery and must not be
used as the current source package. Deployment is performed by the owner.

Active-release checks:

```text
php vendor/phpunit/phpunit/phpunit tests/Feature/PhasedReleaseTest.php tests/Feature/UserGuideTest.php
```

Tests for deferred features remain in the repository for their activation stage;
their routes are deliberately unavailable in this release.
