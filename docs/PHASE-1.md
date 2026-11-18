# Phase 1 handoff — 17 October 2026

## Delivered

Laravel 12.70.0, Filament 5.10.2 and Spatie Permission 6.25.0; dependency resolution targets PHP 8.2. Runtime verification used PHP 8.4.12. Public frontend is Blade + Tailwind 4 + Alpine 3 with Vite and self-hosted Inter/Manrope.

The workspace was empty: no existing application architecture was replaced. All application files were newly created. The Laravel scaffold was configured through Composer/npm manifests and lockfiles, `.env` / `.env.example`, bootstrap/providers, application settings, routes, User model, seeders, tests and Vite source files. `.env` contains only local development credentials and is ignored; it is excluded from the source manifest below.

Implemented: responsive homepage and navigation; about/vision information; seven published programmes with English/Kiswahili JSON translation fields; programme create/edit/hide/order/soft-delete/restore; homepage and global CMS settings; active-user admin access; first-login password change; users and roles with policies; SEO defaults, sitemap and branded errors; server-side WebP image optimization and thumbnails. Photos are clearly identified stock placeholders.

Future modules have honest informational destinations, not simulated data or nonfunctional submission forms. Homepage statistics, project features, stories, publications, news, partners and newsletter remain hidden until their later modules have verified published records. The location illustration uses supplied organization information; relational locations are deferred to Phase 2.

## Database tables

Added `programmes`, `site_settings`, `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, and `role_has_permissions`.

Standard Laravel migrations add `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, and `failed_jobs`, plus Laravel's `migrations` tracking table. Queue/cache/session infrastructure tables are available but basic site operation uses synchronous execution and file cache/sessions. User fields added: `is_active`, `must_change_password`.

## Verification results

- `php artisan migrate` and `php artisan db:seed`: passed with `DB_PORT=33307` against an isolated MariaDB 12.0.2 instance through Laravel's MySQL driver. Seven programmes and eight roles seeded. MariaDB is a MySQL-compatible server; Oracle MySQL itself was not separately tested.
- `php artisan route:list`: passed, 54 total framework/application routes.
- `php artisan test`: **22 passed / 139 assertions** using in-memory SQLite.
- Same feature suite against a separate isolated MySQL-driver schema `ughi_test`: **22 passed / 139 assertions**.
- `npm run test:responsive`: **10 passed**, covering 320/375/430/768/1024/1280/1440px, homepage, programmes, about, contact, admin login, JavaScript errors, loaded images, horizontal overflow, menus, keyboard dismissal and language selection.
- `npm ci --offline` and `npm run build`: passed. Frontend production CSS ~34.7 KB and JS ~55.1 KB before gzip; fonts/images are separate assets.
- Laravel Pint formatting: passed using a standalone formatter. Pint remains an optional development tool because its download timed out; no runtime dependency relies on it.
- `composer validate --strict` and `composer check-platform-reqs`: passed. Composer and npm reported no known dependency advisories during installation.
- `php artisan optimize` and `php artisan filament:optimize`: passed. Generated caches were subsequently cleared so temporary environment overrides are not retained.
- Desktop/mobile screenshots reviewed locally in `.qa/` (ignored, not deployment source).

## Local MySQL setup — updated after handoff

The original connection limitation described below is resolved locally by keeping the approved isolated database in `.local/mysql` (ignored), pointing `.env` to port `33307`, and providing `npm run db:start`, `db:stop`, and `db:status`. The existing service on `3306` remains unchanged. See the README for current start commands. The following records the original validation setup.

### Original validation environment

The existing server at `127.0.0.1:3306` rejected the supplied development account. It was not modified. With user approval, validation used a separate temporary database server on port **33307**, with its own `ughi_db` and `ughi_test`. Both temporary web/database servers were stopped after validation. `.env` retains the requested `127.0.0.1:3306` configuration.

To run normally, first make the development account work on the existing MySQL service and ensure `ughi_db` exists, then follow the README's migration, seeding, storage-link, asset-build and serve commands. Do not copy temporary test data into production.

For this machine only, the validation data directory remains at `/private/tmp/ughi-mysql-phase1-20261017`. Until the operating system clears temporary files, it can be restarted in a terminal with:

```sh
mariadbd --no-defaults --basedir=/opt/homebrew/opt/mariadb --datadir=/private/tmp/ughi-mysql-phase1-20261017 --socket=/private/tmp/ughi-mysql-phase1-20261017/mysql.sock --port=33307 --bind-address=127.0.0.1 --pid-file=/private/tmp/ughi-mysql-phase1-20261017/mysql.pid --log-error=/private/tmp/ughi-mysql-phase1-20261017/mysql.log --skip-log-bin --performance-schema=OFF --innodb-buffer-pool-size=32M
```

In another terminal, run `APP_URL=http://127.0.0.1:8017 DB_PORT=33307 php artisan serve --port=8017`. This overrides the port for that process without changing `.env`. Use this isolated server only for development validation.

Development administrator: `admin@ughi.local` / `Admin@1234`. First login requires a new strong password. Production seeding does not create this account. `php artisan ughi:admin` securely provisions a new administrator after migrations/seeding.

## Phase 2 remaining

Complete project schema, relationships, statuses, filters, listing and detail pages; verified impact records and programme breakdowns; relational countries/regions/districts; database-driven location content and appropriate tests. Add real-data charts when these modules provide data. Later modules, full bilingual pages/settings and audit logs retain their assigned phases.

## Application routes

- `GET|HEAD /` — `home`
- `GET|HEAD /about` — `about`
- `GET|HEAD /about/governance` — `about.governance`
- `GET|HEAD /about/leadership` — `about.leadership`
- `GET|HEAD /about/vision-mission` — `vision-mission`
- `GET|HEAD /admin` — `filament.admin.pages.dashboard`
- `GET|HEAD /admin/homepage` — `filament.admin.pages.homepage`
- `GET|HEAD /admin/profile` — `filament.admin.auth.profile`
- `GET|HEAD /admin/programmes` — `filament.admin.resources.programmes.index`
- `GET|HEAD /admin/programmes/create` — `filament.admin.resources.programmes.create`
- `GET|HEAD /admin/programmes/{record}/edit` — `filament.admin.resources.programmes.edit`
- `GET|HEAD /admin/roles` — `filament.admin.resources.roles.index`
- `GET|HEAD /admin/roles/create` — `filament.admin.resources.roles.create`
- `GET|HEAD /admin/roles/{record}/edit` — `filament.admin.resources.roles.edit`
- `GET|HEAD /admin/settings` — `filament.admin.pages.settings`
- `GET|HEAD /admin/users` — `filament.admin.resources.users.index`
- `GET|HEAD /admin/users/create` — `filament.admin.resources.users.create`
- `GET|HEAD /admin/users/{record}/edit` — `filament.admin.resources.users.edit`
- `GET|HEAD /contact` — `contact`
- `GET|HEAD /donate` — `donate`
- `GET|HEAD /events` — `events`
- `GET|HEAD /gallery` — `gallery`
- `GET|HEAD /get-involved` — `get-involved`
- `GET|HEAD /get-involved/membership` — `get-involved.membership`
- `GET|HEAD /get-involved/partner` — `get-involved.partner`
- `GET|HEAD /get-involved/volunteer` — `get-involved.volunteer`
- `GET|HEAD /impact` — `impact`
- `POST /language/{locale}` — `locale`
- `GET|HEAD /news` — `news`
- `GET|HEAD /privacy-policy` — `privacy-policy`
- `GET|HEAD /programmes` — `programmes.index`
- `GET|HEAD /programmes/{programme}` — `programmes.show`
- `GET|HEAD /projects` — `projects`
- `GET|HEAD /resources/publications` — `resources.publications`
- `GET|HEAD /safeguarding` — `safeguarding`
- `GET|HEAD /sitemap.xml` — `sitemap`
- `GET|HEAD /terms` — `terms`
- `GET|HEAD /where-we-work` — `where-we-work`

## Source files created/configured

- `.editorconfig`
- `.env.example`
- `.gitignore`
- `README.md`
- `app/Filament/Pages/BaseSettings.php`
- `app/Filament/Pages/Dashboard.php`
- `app/Filament/Pages/EditProfile.php`
- `app/Filament/Pages/Homepage.php`
- `app/Filament/Pages/Settings.php`
- `app/Filament/Resources/ProgrammeResource.php`
- `app/Filament/Resources/ProgrammeResource/Pages/CreateProgramme.php`
- `app/Filament/Resources/ProgrammeResource/Pages/EditProgramme.php`
- `app/Filament/Resources/ProgrammeResource/Pages/ListProgrammes.php`
- `app/Filament/Resources/RoleResource.php`
- `app/Filament/Resources/RoleResource/Pages/CreateRole.php`
- `app/Filament/Resources/RoleResource/Pages/EditRole.php`
- `app/Filament/Resources/RoleResource/Pages/ListRoles.php`
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/UserResource/Pages/CreateUser.php`
- `app/Filament/Resources/UserResource/Pages/EditUser.php`
- `app/Filament/Resources/UserResource/Pages/ListUsers.php`
- `app/Filament/Widgets/FoundationOverview.php`
- `app/Http/Controllers/Controller.php`
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/InformationController.php`
- `app/Http/Controllers/ProgrammeController.php`
- `app/Http/Middleware/EnsurePasswordChanged.php`
- `app/Http/Middleware/SetLocale.php`
- `app/Models/Programme.php`
- `app/Models/SiteSetting.php`
- `app/Models/User.php`
- `app/Policies/ProgrammePolicy.php`
- `app/Policies/RolePolicy.php`
- `app/Policies/UserPolicy.php`
- `app/Providers/AppServiceProvider.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Support/CmsFields.php`
- `app/Support/ImageStorage.php`
- `app/Support/SiteSettings.php`
- `artisan`
- `bootstrap/app.php`
- `bootstrap/providers.php`
- `composer.json`
- `composer.lock`
- `config/app.php`
- `config/auth.php`
- `config/cache.php`
- `config/database.php`
- `config/filesystems.php`
- `config/logging.php`
- `config/mail.php`
- `config/permission.php`
- `config/queue.php`
- `config/services.php`
- `config/session.php`
- `config/ughi.php`
- `config/ughi_pages.php`
- `database/.gitignore`
- `database/factories/UserFactory.php`
- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/0001_01_01_000001_create_cache_table.php`
- `database/migrations/0001_01_01_000002_create_jobs_table.php`
- `database/migrations/2026_10_17_000000_create_permission_tables.php`
- `database/migrations/2026_10_17_000001_create_phase_one_tables.php`
- `database/seeders/AdminSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/ProgrammeSeeder.php`
- `database/seeders/RolePermissionSeeder.php`
- `docs/HOSTINGER.md`
- `docs/IMAGE-CREDITS.md`
- `docs/licenses/Inter.txt`
- `docs/licenses/Manrope.txt`
- `package-lock.json`
- `package.json`
- `phpunit.xml`
- `playwright.config.js`
- `public/.htaccess`
- `public/favicon.svg`
- `public/fonts/inter-latin-400-normal.woff2`
- `public/fonts/inter-latin-500-normal.woff2`
- `public/fonts/inter-latin-600-normal.woff2`
- `public/fonts/inter-latin-700-normal.woff2`
- `public/fonts/inter.css`
- `public/images/community-care.jpg`
- `public/images/listening-care.jpg`
- `public/index.php`
- `public/robots.txt`
- `resources/css/app.css`
- `resources/js/app.js`
- `resources/views/components/breadcrumb.blade.php`
- `resources/views/components/button.blade.php`
- `resources/views/components/empty-state.blade.php`
- `resources/views/components/footer.blade.php`
- `resources/views/components/header.blade.php`
- `resources/views/components/layout.blade.php`
- `resources/views/components/logo.blade.php`
- `resources/views/components/page-hero.blade.php`
- `resources/views/components/programme-card.blade.php`
- `resources/views/components/section-heading.blade.php`
- `resources/views/components/tanzania-map.blade.php`
- `resources/views/components/ughi-icon.blade.php`
- `resources/views/errors/403.blade.php`
- `resources/views/errors/404.blade.php`
- `resources/views/errors/419.blade.php`
- `resources/views/errors/500.blade.php`
- `resources/views/filament/dashboard.blade.php`
- `resources/views/filament/settings.blade.php`
- `resources/views/pages/about.blade.php`
- `resources/views/pages/home.blade.php`
- `resources/views/pages/information.blade.php`
- `resources/views/pages/programme.blade.php`
- `resources/views/pages/programmes.blade.php`
- `resources/views/pages/sitemap.blade.php`
- `routes/console.php`
- `routes/web.php`
- `tests/Browser/responsive.spec.js`
- `tests/Feature/FoundationTest.php`
- `tests/TestCase.php`
- `vite.config.js`
- `docs/PHASE-1.md`
