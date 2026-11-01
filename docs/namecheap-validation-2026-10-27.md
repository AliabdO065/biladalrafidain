# Namecheap release verification — 27 October 2026

- Laravel upgraded from 9.52.15 to 12.70.2, Sanctum to 4.x, PHPUnit to 11.5; supported PHP minimum is 8.2. Production guide selects PHP 8.3. Laravel 12 security maintenance ends 24 February 2027; schedule its next major upgrade before then.
- Composer dependency audit completed online: no known security advisories reported for the resolved dependencies. The separate production install also passed its platform requirements and no-dev audit. This is a dependency check, not a guarantee against all application or hosting vulnerabilities.
- Full suite: **97 passed, 1 skipped, 1,001 assertions**. The skip is the optional legacy external shipment fixture; current supplied spreadsheets were included through TEST_VOUCHER_DIRECTORY.
- Native PHP bill renderer tested with Arabic/English, long notes, a missing browser executable, valid PNG dimensions and the WhatsApp upload size bound. Shared-hosting queue checks cover locks, disconnected/paused behavior and an empty queue without real HTTP sends.
- Private pages send no-store/nosniff/frame restrictions. Admin logout and legacy delete actions require mutating requests. Event-history protection identifies the most recently inserted event independently from chronological display ordering.
- Production archive is approximately **10.82 MiB**, has 7,942 entries and includes production vendor, fonts and licenses, a private application directory and a public-only entry point.
- Archive contents checked against current route/layout/document/controller hashes. No .env, database dump, PHPUnit install, private image storage or review artifacts are included.
- Unused upstream Ar-PHP documentation, examples, duplicate PHAR and the large spellchecking dictionary are omitted from the release ZIP. Runtime shaping data and licenses are retained; source Composer installation remains untouched.
- A fresh extraction with production-only vendor passed config:cache, route:cache and view:cache. A guarded temporary SQLite database was used, never the live MySQL database.
- The extracted package served the homepage, catalog, tracking, factory registration and login through its real public entry point over loopback HTTP in both languages, without JavaScript errors or mobile horizontal overflow. Private-path requests did not expose files.
- The trimmed production library generated Arabic and English PNGs without Chrome. No Meta message was sent.
- A separate local database/image snapshot was created under storage/app/private/backups; it is not in the public release archive and does not contain .env. Keep the existing APP_KEY separately and take a fresh cutover snapshot when actually migrating.

## Release checksum
SHA-256 for dist/namecheap-release.zip:
963960109724a0585de0ecc2974fbef351d8388f102ba98d4841f9c4e2790ee4

## Still dependent on the owner's accounts
Domain, cPanel deployment and permissions, real SSL/HTTPS, PHP options, Cron and backup restoration must be verified on Namecheap. Meta business assets, number verification, payment method, approved templates, webhook subscription, consent and a real accepted/delivered/reply test require the owner's account setup.
The prepared release does not mean these external account steps have been performed. APP_URL is configured for https://biladalrafidain.online. APP_PUBLIC_PATH remains a clearly marked placeholder until the cPanel account path is supplied. Domain migration instructions preserve tracking codes and cover Meta webhook re-verification.

Official references and current pricing are in namecheap-deployment.md and whatsapp-setup-and-cost.md.
