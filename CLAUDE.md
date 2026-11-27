# Ninubo

Two parts in one PHP repo (XAMPP locally at `http://localhost/ninubo`, `NINUBO_BASE` in `includes/config.php`):

1. **Public site** (Spanish, `?lang=en` via `includes/i18n.php` string replacement) — `*.php` in the root + `includes/`.
   `brief.php` is the client-facing website brief (bilingual on its own; excluded from i18n; runs on the CRM code —
   see below). `api/brief-guardar.php` is only a temporary forwarder to it.
2. **CRM** in `admin/` (English only) — mini-MVC with Tabler 1.4, vertical sidebar. Modeled on `../Shipping`.

## CRM conventions (`admin/`)

- Front controller `admin/index.php`; `admin/.htaccess` rewrites everything that isn't a real file. Code in `admin/app/`
  (namespace `Crm\`, autoloaded from `admin/app/`); `app/`, `database/`, `scripts/`, `storage/` are denied by URL.
- Routes in `admin/app/routes.php`. Every route requires sign-in unless `['public' => true]`; `['role' => 'admin']`
  restricts to administrators. All POSTs are CSRF-checked centrally in `Core\App::dispatch` — forms need `csrf_field()`.
- Views: `View::start('actions') … View::end()` puts buttons in the page header. Escape with `e()`. Links with `url('/path')`.
- Validation errors: `$this->back('/path', $errors, $input)`; views read `old()`, `field_error()`, `invalid()`.
- Schema changes: new file in `admin/database/migrations/` (plain SQL, each statement ends with `;` at end of line).
  Admins apply pending migrations from the banner in the UI, or `php admin/scripts/migrate.php`. Never edit an applied migration.
- Roles: `admin` (everything + Users, Settings, catalog) and `sales` (sees **all** clients). First admin is created at
  `/admin/setup`, which only works while `users` is empty and requires the old panel password (`NINUBO_ADMIN_PASSWORD_HASH`).
- Business info for documents lives in Settings (`Core\Settings::DEFAULTS` lists every key) — the US business name is not
  final, never hard-code it.
- Market: Rio Grande Valley, Texas. USD only. Texas sales tax: data processing services (web design, hosting,
  maintenance) taxed on 80% of the charge (Rule 3.330) — rate and taxable % are settings; confirm with the CPA.
- Contracts are in English, Texas law, venue county from Settings. E-signature must capture ESIGN/UETA consent + audit trail.
- **Client-first (owner's rule):** a new sale always starts by creating the client. Briefs, invoices and contracts are
  created *from* a client (client page → "New ▾"); never add a flow that creates them without a client.
- Every meaningful change on a client writes a timeline entry (`Activity::log`) — status/assignment changes, briefs
  created/started/submitted/reopened (the public `brief.php` logs those too). Keep doing this for invoices/contracts.
- Catalog (`packages`): price, billing (one-time/monthly/yearly), `tax_type` (data_processing = taxed on the taxable %
  setting, taxable = 100%, exempt) and `brief_profile`. Items already used can't be deleted, only deactivated.
- Invoices (`Models\Invoice`): draft (editable, no number) → issue (number from Settings `invoice_next_number`,
  locked with FOR UPDATE; bill-to snapshot) → partial / paid from payments. Overdue is computed, never stored.
  Tax: `Invoice::computeTotals()` is the single formula (the form's JS only previews it). Totals, tax rate and
  taxable % are snapshotted on the invoice — never recompute an issued invoice from current settings.
  Issued invoices are never edited or deleted; admins void them (number kept). Clients with invoices can't be deleted.
- Recurring services (`client_subscriptions`): created when an invoice with monthly/yearly lines is issued; a
  website package also schedules the yearly domain & hosting renewal (Settings `website_renewal_package_id`) one year
  later — **except while the client has a care plan** (any `maintenance`-category service), which includes hosting:
  starting one cancels the renewal, cancelling/voiding the last one reschedules it (`Subscription::restoreRenewal`).
  Voiding undoes this (cancels services it started, moves billed periods back).
- Issuing an invoice also creates a website brief per invoiced package with a `brief_profile` (skipped if the client
  has an open brief for that package); `client_briefs.invoice_id` links them, the link prints on the invoice PDF, and
  voiding deletes those the client never opened (keeps started/submitted ones). Admins can delete briefs anywhere.
- PDF: Dompdf in `admin/vendor/` (installed with Composer, committed so hosting needs no Composer); `Core\Pdf`
  disables remote fetches and PHP; logo is embedded as a data: URI. Business identity on documents comes from Settings.
- Contracts (`Models\Contract`): created from a client, based on one of its issued invoices. draft (live preview
  rendered from `Views/contracts/agreement.php`) → finalize (number AGR-####, text frozen in `body_html` + SHA-256
  `body_hash`, secret `token`) → client signs at the public `/sign.php?token=…` (ESIGN/UETA consent, typed name,
  drawn signature, IP, user agent; must match `body_hash`) → admin countersigns, or a pen-signed scan is uploaded.
  Every step goes to `contract_events` (audit trail printed as the PDF's "Certificate of Electronic Signature").
  Never edit a finalized contract's text — cancel and create a new one. Editing the template only affects new drafts.
  Day limits (plan cancellation, late suspension, materials pause, warranty) and venue county are Settings.
- `/sign.php` lives at the site root (not under /admin) so the client's link doesn't expose the CRM; it reuses
  `admin/app` code without a CRM session and is excluded from the i18n translation.
- Deploying new code: while migrations are pending, every CRM page shows "Database update required" (admins get the
  Apply button) instead of failing on missing tables.
- Dashboard KPIs come from `Models\Stats` ("sales" = issued non-void invoices by issue date; "collected" = payments
  by payment date). Charts are server-rendered inline SVG/HTML partials (`Views/partials/viz_*.php`) — no chart
  library; hover tooltips via `data-tip-title` + `data-tip` JSON and `assets/app/viz.js`.
  Chart colors are the CSS variables in `app.css` (`--viz-1..3`, `--seq-1..4`), validated (CVD + contrast) for
  Tabler's light #ffffff and dark #1f2937 surfaces — reuse them in order, never invent new series colors.
- Website brief questions live only in `includes/brief-schema.php`. Profiles (`ninubo_brief_profiles()`: landing,
  business, premium, ecommerce) filter steps/fields with `'only' => [...]` / `'except' => [...]`, options with
  `'options_only'`; `'prefill'` fills a field from the client record until the first save. The brief stores the profile
  at creation. Always call `ninubo_brief_schema($lang, $brief['brief_profile'])`. **Never rename an answer key**
  (saved briefs use them); removed questions still show in the CRM as "earlier version" answers
  (`ninubo_brief_legacy_labels()`).
- Public brief = `/brief.php` (like `/sign.php`: CRM code, no session, token in the link). GET renders
  `Views/brief/page.php` (Tabler, light by default + dark toggle, ES/EN), POST saves JSON (draft/submit) via
  `Brief::saveFromClient`. `Core\BriefForm` draws/collects/displays fields for both the public form and the CRM.
  `admin/assets/app/brief.js` saves only after the client really changed something (opening the link changes nothing).
  Contact details on it come from Settings (`business_phone`, `business_whatsapp`, `business_email`).
  Submitted briefs are locked; staff can reopen them from the CRM brief page.

## Configuration & deployment

- Per-server values live in `includes/config.local.php` (git-ignored; template `includes/config.local.example.php`):
  `NINUBO_BASE` ('/ninubo' in XAMPP, '' in production), `NINUBO_SECRET`, `NINUBO_ADMIN_PASSWORD_HASH`,
  `NINUBO_DB_*`, optional `NINUBO_GA_MEASUREMENT_ID`. Env vars with the same names also work.
  Never hard-code any of these in tracked files again.
- Production deploys with `git pull` on the hosting (see `DEPLOY.md`); Dompdf is committed in `admin/vendor`.

## Safety

- Public GitHub repo (`origin`). Don't commit secrets, partner/production costs, `admin/storage/*`, DB dumps
  (`C:\xampp\backups\`). History was scrubbed of the old secret/hash/partner prices on 2026-11-25.
- Back up the `ninubo` DB (`mysqldump`) before destructive DB steps.
