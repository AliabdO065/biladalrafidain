# Factory catalog

Implemented 2026-10-27: public browsing, factory self-registration with explicit
administrator approval, private factory management and item moderation. This is
a catalog and portfolio, not an ordering or payments application.

## Entry points

- `/catalog`: published products/work with keyword, category, type and city filters.
- `/catalog/factories`: approved factory directory and public factory profiles.
- `/factory/register`, `/factory/login`, `/factory`: supplier registration, login,
  own content, profile and password management.
- `/admin/catalog`: factory/item review and two-level bilingual categories.

Factories use a separate `factory` session guard and `factory_accounts` provider.
They never become administrative `users`. Admin routes explicitly use `auth:web`.
Owner-scoped item queries reject cross-factory edits, deletes and private images.
Registration/login are rate-limited; public registration cannot set approval.
There is no third-party authentication service or email recovery flow configured.
Before launch, define an operator procedure for account recovery; the site does
not claim to send password-reset emails.

New factories are pending and cannot upload until approved. Each new/edited post
is pending. Editing an approved factory profile hides the profile and all its
content until review; rejected profiles can resubmit through edits. Suspension
cannot be lifted by the factory. Public queries always require approved item and
factory plus an active category and parent. Protected media routes apply the same
publication rules, with no-store responses for moderation changes.

Approval checks a content hash against the version seen on the review form,
using row locks. Concurrent edits cannot silently publish unreviewed content.
Profile writes and item writes lock the factory record consistently.

## Images and capacity

GD with WebP support is required. Only decoded JPEG/PNG/WebP images are accepted;
SVGs and raw uploads are never served. Images are re-encoded, stripped of metadata,
resized and JPEG orientation is applied. Stored paths are UUID WebP files under
private `storage/app/catalog/<factory-id>`; originals remain only in PHP upload
temporary storage for the request.

Defaults in `config/catalog.php`: 3 images per item, 5 MB source/image, 12 MP
source dimensions, 1400-pixel maximum output dimension, 350 KiB maximum encoded
output, 100 posts and 40 MiB stored images per factory. Quotas are checked under
the factory row lock, including replacements. PHP upload/post limits must allow
the intended request size (for example, three 5 MB files require a post limit
above 15 MB). Smaller server limits still apply before Laravel validation.

Replacements and deletions remove old files only after database commit. Failed
writes remove new files. `catalog:prune-images`, scheduled daily, removes only
unreferenced UUID image paths older than 24 hours. It retains every referenced
image, including pending/rejected factory content. Run the Laravel scheduler on
deployment to execute cleanup.

The catalog does not join factory login identities to ERP factory numbers or
expose merchant finances or shipments. It contains no demo factories or products;
only five editable starter category names are seeded by the migration.

Validation: 82 application tests passed, with one optional legacy external-file
test skipped. Factory-specific tests cover registration, guard separation,
publication gates, private images, cross-factory access, stale review hashes,
quota rollback, image replacement/deletion, category filtering and orphan
cleanup. Ten screens were also checked in Arabic and English at desktop/mobile
sizes, including the dark administrator review page. Demo preview records were
not inserted into the live database.
