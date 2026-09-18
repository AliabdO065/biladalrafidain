# Biladalrafidain — Shipping & Customs Clearance Landing Page

A marketing + lead-gen site for Biladalrafidain (بلاد الرافدين), a shipping and customs-clearance logistics company, built on Laravel 9. All content is editable from a custom admin dashboard — no code changes needed to update text, images, prices, reviews, or FAQs.

## What this project is

- A **landing site** (hero, trust stats, services, 3-step process, about, comparison table, reviews, FAQ, callback form, footer, sticky bottom bar) plus two standalone pages: **shipment tracking** (`/tracking`) and **company policies** (`/policies`).
- A **content-managed backend**: every section above is backed by a database table and an admin screen, not hardcoded HTML.
- **Multilingual**: Arabic (default), English, Turkish, and Hebrew. Visitors can switch language; the site falls back gracefully if a language is disabled. Arabic and Hebrew render right-to-left automatically.
- A **lead-capture form**: the "Request a Callback" form stores submissions in the dashboard under *Callback Leads*.
- A **shipment tracking system**: staff record shipments and status timeline events in the dashboard; visitors look up a shipment by its tracking code on the public `/tracking` page.

This project was originally a German emergency-electrician landing site and was rebuilt into the current shipping/logistics business; old unrelated routes still resolve as redirects to the homepage so no old bookmarked/indexed links break.

## Tech stack

- **PHP 8.x / Laravel 9**, Blade templates, Eloquent ORM
- **MySQL** database
- **Bootstrap 5** (dashboard), custom CSS (public landing page)
- **Vite** for asset bundling
- Custom JSON-column based translation system (`app/Traits/HasTranslations.php`) — no third-party i18n package

## Requirements

- PHP 8.0.2+
- Composer
- MySQL (or MariaDB)
- Node.js + npm (only needed if you plan to rebuild/change frontend assets via Vite)

## Getting started (local setup, e.g. with XAMPP)

1. **Clone and install dependencies**
   ```bash
   git clone https://github.com/AliabdO065/elec.git
   cd elec
   composer install
   npm install
   ```

2. **Environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `.env` and set your database credentials:
   ```
   DB_DATABASE=Biladalrafidain
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Set an admin password before seeding**
   Add this line to `.env` (pick your own strong password):
   ```
   SEED_ADMIN_PASSWORD=choose-a-strong-password-here
   ```
   The seeder creates an admin account using this value. If it's left empty, no admin user is created and you won't be able to log in.

4. **Create the database, then migrate and seed**
   ```bash
   php artisan migrate --seed
   ```
   This creates all tables and fills the landing page with placeholder content (stats, services, steps, comparison rows, sample reviews, FAQs, policies, sample shipments) in all 4 languages, ready to be edited from the dashboard.

5. **Build frontend assets** (only required if you change CSS/JS sources under `resources/`)
   ```bash
   npm run dev     # for local development
   npm run build   # for production
   ```

6. **Serve the app**
   - Via XAMPP: point a vhost/alias at the project's `public/` folder, or
   - Via the built-in server:
     ```bash
     php artisan serve
     ```

7. **Log in to the dashboard** at `/login` using the admin email set up by the seeder and the password you set in step 3.

## Using the dashboard

All content lives under **Admin → Landing Page** in the sidebar:

| Section | Controls |
|---|---|
| **Languages** | Enable/disable Arabic, English, Turkish, Hebrew for visitors. At least one language, and the default language, must always stay enabled. |
| **Settings** | Logo, phone/WhatsApp, hero headline/subheadline/image, alert banner, owner name/photo, company story, trust points, footer company info, legal page links, and social media links (only filled-in links show in the footer). |
| **Trust Stats** | The small stat badges near the top (e.g. "+8 years experience", icon + value + label). |
| **Services** | The service cards (title, description, icon, image). |
| **3-Step Process** | The "how it works" steps. |
| **Comparison Table** | Rows comparing "us" vs. unreliable shipping brokers. |
| **Reviews** | Customer reviews (name, photo, star rating, date, text). Reviews can be flagged as an example until real reviews are collected. |
| **FAQ** | Question/answer pairs shown in the FAQ accordion. |
| **Callback Leads** | Read-only inbox of every callback request submitted through the site's form. |
| **Shipments** | Create/edit shipments and their tracking-code, route, and status; add timeline events shown on the public tracking page. |
| **Policies** | The policy pages (privacy, shipping, refund, terms) shown on the public `/policies` page. |

Every text field that appears on the public site is translatable: content forms accept Arabic, English, Turkish, and Hebrew values, and the site displays whichever the visitor has selected (falling back to Arabic if a translation is missing).

The dashboard's own interface (menus, buttons, labels) has a separate language switcher (Arabic/English) next to the admin's account menu — it does **not** affect what visitors see, only how the dashboard itself is displayed to whoever is logged in.

## Project structure notes

- `app/Models/Landing*.php` — one model per content-managed section (`LandingSetting`, `LandingStat`, `LandingService`, `LandingStep`, `LandingComparison`, `LandingReview`, `LandingFaq`, `LandingLead`, `LandingPolicy`, `LandingShipment`, `LandingShipmentEvent`), plus `Language`.
- `app/Http/Controllers/LandingController.php` — public site: renders the homepage, tracking page, policies page, handles callback submissions, handles the visitor language switch.
- `app/Http/Controllers/LandingDashboardController.php` — all admin CRUD for the sections above.
- `app/Traits/HasTranslations.php` — makes a model field transparently read/write per-locale JSON (`{"ar":"...","en":"...","tr":"...","he":"..."}`) without changing how views access it (`$service->title` just works).
- `app/Http/Middleware/SetLocale.php` / `SetAdminLocale.php` — separate locale resolution for visitors vs. logged-in admins.
- `resources/views/fronted/landing/` — the public pages, split into one partial per section (`partials/_hero.blade.php`, `_services.blade.php`, etc.), plus `tracking.blade.php` and `policies.blade.php`.
- `resources/views/dashboard/landing/` — the corresponding admin screens.
- `lang/{ar,en,tr,he}.json` and `lang/{ar,en}/{validation,auth,pagination,passwords}.php` — translation strings for the static site chrome and Laravel's built-in validation/auth messages.

## Security note

Never commit a real `.env` file. `SEED_ADMIN_PASSWORD` should only exist in your local/production `.env`, never in source control — the seeder reads it from the environment specifically so no password is ever hardcoded in the repository.
