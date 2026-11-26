# NINUBO project handoff

## Current state

- Local XAMPP base path is configured as `/ninubo` in `includes/config.php`. Set it to an empty string when `ninubo.com` is served from the domain root.
- Spanish is the default language. English is available with `?lang=en` and the header flag selector preserves the selected language across internal links.
- `includes/i18n.php` contains the English copy mapping. The privacy notice uses the `ninubo_t()` helper for its legal copy.

- Client-brief module (added 2026-09-08) uses a MySQL database named `ninubo` (schema in `sql/brief.sql`, table `client_briefs`). Connection settings via `includes/db.php`, overridable with `NINUBO_DB_HOST` / `NINUBO_DB_NAME` / `NINUBO_DB_USER` / `NINUBO_DB_PASS` env vars; defaults match XAMPP's local root/no-password MySQL.

## Completed work

- Added a client-brief intake wizard so Sergio can send each new web client a unique link to fill in their own project info (2026-09-08):
  - `brief.php?token=...` — public multi-step wizard (13 themed steps covering the 24-section intake questionnaire), autosaves each step to the database via `api/brief-guardar.php`, resumes where the client left off, and blocks edits once submitted.
  - `admin/` — password-protected panel (`admin/login.php`, `admin/index.php`, `admin/brief-ver.php`, `admin/logout.php`) to create a client link, see who has completed/is in progress, and read the submitted answers in a formatted view. Password hash lives in `includes/config.php` as `NINUBO_ADMIN_PASSWORD_HASH` (default password `[removed]` — **change this before sharing the panel with anyone or deploying to production**).
  - The full question schema (labels, field types, options) lives in one place: `includes/brief-schema.php`. Edit only that file to add/change/remove questions — `brief.php`, `api/brief-guardar.php`, and `admin/brief-ver.php` all render from it automatically.
  - Bilingual (added same day, per Sergio's request — "habrá clientes que no hablen español"): the public wizard has an ES/EN flag switcher (reuses the site's `?lang=` mechanism, scoped to this page only). Every label/hint/option in `includes/brief-schema.php` is wrapped with `ninubo_brief_bt('Español', 'English')`; `ninubo_brief_schema($lang)` resolves it. Checkbox/radio answers are stored as stable English-ish keys (e.g. `nuevos_clientes`), not the displayed text, so the admin panel (always Spanish) shows the right label regardless of which language the client used — see the big comment at the top of `includes/brief-schema.php`. The `client_briefs.lang` column remembers which language a client is using so reopening their link later resumes in the same language automatically.
  - File/photo sharing uses an external link field (Google Drive/WeTransfer/etc.) rather than direct upload, per Sergio's decision — kept out of scope for v1.
  - On submit, a notification email is sent to `contacto@ninubo.com` with a link to the admin view (not the raw data, to keep the email short) — same `mail()` reliability caveat as the contact form applies here.
- Added the shared favicon from `favicon.ico`.
- Clarified the site around three primary services: custom web systems, data analysis & BI, and professional websites.
- Added SEO foundations: canonical and hreflang tags, Open Graph/Twitter metadata, JSON-LD, `robots.txt`, and `sitemap.xml`.
- Added conversion instrumentation in `assets/js/main.js` for successful contact submissions, phone/email/WhatsApp clicks, contact CTAs, and language selection.
- Google Analytics is conditional: set `NINUBO_GA_MEASUREMENT_ID` to a GA4 ID (`G-...`) in the hosting environment to enable collection.
- Replaced generic mockups at the top of `portafolio.php` with five real project screenshots copied into `assets/img/`:
  - `portfolio-escalaciones.jpg`
  - `portfolio-requisiciones.jpg`
  - `portfolio-erp-manufactura.jpg`
  - `portfolio-despacho.jpg`
  - `portfolio-rh-asistencia.jpg`

- Hardening (2026-11-25):
  - `.htaccess` (root) blocks `.md`, `.sql`, `.log`, dotfiles and every `.txt` except `robots.txt`, and disables directory listing; `includes/.htaccess` and `sql/.htaccess` deny all. **Upload these `.htaccess` files to production too.**
  - Admin panel: CSRF token on every POST (login, create, delete, logout), logout is now POST-only, session cookie is HttpOnly + SameSite=Lax + Secure on HTTPS and scoped to `/admin/`, 2h idle timeout, and a per-IP lockout after 5 failed logins in 15 min (stored in PHP's temp dir as `ninubo_login_*.json`; delete that file to unlock early). See `includes/admin-auth.php`.
  - `ninubo_translate_html()` no longer runs on `brief.php`, `admin/` or `api/` (see `ninubo_should_translate_page()` in `includes/config.php`), so client answers are never altered by the EN string replacement.

- CRM rebuild of `/admin` (2026-11-25) — see `CLAUDE.md` for conventions:
  - Phase 0: git repo initialized (remote `origin` = github.com/serge701/Ninubo, **public**); DB backup at
    `C:\xampp\backups\ninubo_2026-11-25_pre-crm.sql`.
  - Phase 1 (done): Tabler 1.4 vertical layout, username+password login with `users` table (roles admin/sales),
    login throttling in `login_attempts`, forced password change for temporary passwords, user management, profile,
    Business settings (identity, logo, invoice numbering, Texas sales tax, contract venue), migrations runner,
    Website briefs module migrated (English view). Old `admin/login.php`, `logout.php`, `brief-ver.php`,
    `includes/admin-auth.php` removed; `/admin/brief-ver.php?id=N` still redirects to `/admin/briefs/N`.
  - To deploy: upload `admin/`, open `/admin/` → setup screen (asks for the old panel password) creates tables + first admin.
  - Phase 2 (done): Clients (list/search/filters, client page with "New ▾" menu, activity timeline with notes, calls,
    WhatsApp, meetings and dated follow-ups), Catalog of packages & services (price, billing, Texas tax type, brief
    profile; 5 starter items at $0.00), briefs created from client + package with per-package questions (landing 9 steps,
    business 13, ecommerce 14 incl. new "Online store" step), dashboard with leads/active/follow-ups. The 2 pre-CRM briefs
    are not linked to a client (shown as "Not linked to a client", full questionnaire).
  - Catalog loaded from the "Ninubo 360" partnership proposal (migration 05): Starter $697, Business $1,397,
    Premium $2,697 (with internal production cost / margin, admin-only), Online Store from $2,500, Website Redesign from
    $700, add-ons (fixed, "from" and range prices) and monthly care plans ($59 / $99 / $199). New brief profile
    `premium` (extra "Premium features" step); Starter asks for up to 5 page sections; per-package hints
    (`hint_by_profile`) for pages included and bilingual.
  - Phase 3 (done): invoices from the client page (quick pickers for website package + monthly care plan, catalog
    add-ons, custom lines), live totals with Texas tax rules, payment terms 50/50 or 40/30/30 (> $2,000) or full,
    draft → issue (INV-####) → payments (partial/paid, first payment turns a Lead into Active) → void (admin).
    Downloadable PDF (Dompdf, committed in admin/vendor). Recurring services per client with "Invoice next period";
    yearly Domain & Hosting Renewal scheduled automatically (catalog item, **price still $0 — owner must set it**).
    Owner decisions: blog & booking only in Premium; Business delivery 10–15 business days; domain + hosting
    included for the first year, renewed yearly.
  - Next phases: 4) contracts with English RGV template + e-signature link + PDF, 5) dashboard KPIs.

## Important follow-ups

- ~~Confirm the canonical contact number~~ — Resolved 2026-09-05: canonical number is 8995-08-3462 (`+528995083462`), matching the visible text and WhatsApp. `tel:` links in `contacto.php`, `includes/footer.php`, and both language strings in `aviso-de-privacidad.php` were pointing to a stale `+528991225076` and have been corrected.
- ~~Site is live in production~~ — Confirmed by Sergio 2026-09-08. He also confirmed `NINUBO_ADMIN_PASSWORD_HASH` and `NINUBO_SECRET` were both rotated away from the source defaults before going live — no further action needed on those two.
- Configure SMTP for the contact form; PHP `mail()` is not a dependable delivery mechanism by itself. Same applies to the brief-completion notification email. (Not confirmed either way — worth checking if either email has been missing in practice.)
- Add an approved 1200×630 social-sharing image when available; the current Open Graph image is the NINUBO logo.
- The workspace is not a Git repository.
