# Ninubo

Two parts in one PHP repo (XAMPP locally at `http://localhost/ninubo`, `NINUBO_BASE` in `includes/config.php`):

1. **Public site** (Spanish, `?lang=en` via `includes/i18n.php` string replacement) — `*.php` in the root + `includes/`.
   `brief.php` + `api/brief-guardar.php` are the client-facing website brief (bilingual on its own; excluded from i18n).
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
- Website brief questions live only in `includes/brief-schema.php`; the CRM shows answers with `ninubo_brief_schema('en')`.

## Safety

- Public GitHub repo (`origin`). Don't commit secrets, `admin/storage/*`, DB dumps (`C:\xampp\backups\`).
- Back up the `ninubo` DB (`mysqldump`) before destructive DB steps.
