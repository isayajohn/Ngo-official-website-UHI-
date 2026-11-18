# UGHI website and CMS

One Laravel 12 application with Blade, Tailwind CSS 4, Alpine.js, Vite, Filament 5 and Spatie Permission 6. Laravel 12 is the latest Laravel major supporting PHP 8.2. Composer resolves dependencies against PHP 8.2, and lockfiles are included for reproducible installations.

See the [Phase 2 implementation and upgrade guide](docs/PHASE-2.md) for current modules, publication workflows and validation. The [Phase 1 handoff](docs/PHASE-1.md) records the initial foundation.
The [Department Management guide](docs/DEPARTMENTS.md) documents the internal organizational structure, team records, routing and permissions.

## Scope

Phase 1 provides the public design system, accessible responsive navigation, homepage, seven database-backed programmes, programme details, basic information pages, homepage and site settings, admin authentication, first-login password change, users, roles and permissions. Public pages include metadata, canonical links, social cards and a sitemap. No fake statistics, news, projects, beneficiaries or partners are seeded.

The application now includes projects, verified impact, relational operating areas, an editorial hub for news, events, publications and gallery images, donation pledge tracking, and volunteer, membership and partnership enquiry workflows. Donation submissions are pledges that staff follow up and confirm; there is no online payment gateway. Safeguarding reports and newsletters are not implemented. The labeled Tanzania regional map accompanies published countries, regions and districts managed in the CMS; its colors distinguish regions and do not represent coverage or statistics. Content Pages provide editable copy, bilingual fields, publishing controls, SEO metadata and hero/social images. Partners and a reusable Media Library are also available in the CMS.

The internal Departments module adds editable departments, positions, team members, programme/project support relationships and routed contact messages. Departments are administrative units and are kept separate from Programmes.

## Requirements

- PHP 8.2+ with PDO MySQL, mbstring, intl, XML/DOM, fileinfo, OpenSSL, ctype, tokenizer, GD (WebP support), curl and zip. SQLite is used for isolated automated tests.
- Composer 2; MySQL 8+ or a compatible supported MariaDB.
- Node.js 20.19+ or 22.12+ and npm, only to compile assets.
- Writable `storage/` and `bootstrap/cache/`.

## Local installation

```sh
composer install
cp .env.example .env
php artisan key:generate
npm ci
```

Set the database values **only in `.env`** using your local MySQL account. A standard local MySQL setup typically uses port `3306`. On this Mac, the project-local MariaDB instance uses `127.0.0.1:33307` because the existing service on `3306` rejects the supplied account. The database must exist and the account must have migration privileges. `.env.example` contains placeholders, and `.env` is ignored. Do not commit database passwords.

```sh
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`; admin: `http://127.0.0.1:8000/admin`.
For asset hot reload, run `npm run dev` in a second terminal. No queue worker, Redis, Docker or long-running Node process is required for normal operation. File cache and file sessions plus synchronous queues are configured by default.

## Project-local database on this Mac

The approved isolated database now lives in `.local/mysql`, which is ignored by Git and survives temporary-directory cleanup. `.env` points to port `33307`. The existing database service on port `3306` is unchanged. This helper uses the installed Homebrew MariaDB and is only for local development, not Hostinger.

After restarting your Mac or stopping the project database, start both the database and Laravel with:

```sh
npm start
```

If Laravel is already serving the website, run only `npm run db:start`, then refresh your browser. A `Connection refused` error for port `33307` means the project database may be stopped; `php artisan serve` alone does not start it. Migrations and seeders are not needed on every restart.

On macOS the helper submits a project-specific job to the current user's launchd session, so the database survives closing the startup terminal. It does not install a login service; run `npm start` again after a Mac restart.

`npm run db:status` checks it; `npm run db:stop` shuts down only this project's database. The helper checks connectivity using application credentials read from `.env`. Stop verifies the process owner and project data directory before sending a graceful termination signal. The local data folder is not included in a fresh clone; other machines should provision their own MySQL database and configure `.env` normally. `npm start` is for this Mac's project-local database; use `php artisan serve` directly with another configured MySQL service.

If `php artisan storage:link` says the link already exists, it is already configured and does not need to be recreated. After changing `.env`, run `php artisan config:clear`; restart an already-running `php artisan serve` process if necessary.

## Development administrator

In `APP_ENV=local` or `testing`, the seeder creates:

- Email: `admin@ughi.local`
- Password: `Admin@1234`

This password is unrelated to the database password. On first login you must set a new password (12+ characters, mixed case, number and symbol) before accessing the dashboard. Re-seeding does not reset a changed password, overwrite editorial content, or replace customized permissions on existing roles. The development admin is never seeded in production. Create the first production administrator with `php artisan ughi:admin` after seeding; it securely prompts for a unique email and strong password and assigns the Super Admin role. Alternatively provision a changed-password local administrator in a private deployment database before launch. Never deploy a database containing the default development password.

## CMS

- **Content → Homepage**: hero copy, both CTA labels and URLs, optimized image and alt text, About content, programme introduction, location introduction and get-involved CTA.
- **Our Work → Programmes**: add, translate, publish/hide, order, edit, soft-delete and restore programmes. Empty slugs are generated uniquely on creation. Keep existing slugs stable. Text is rendered safely with Blade escaping.
- **Our Work → Projects**: draft, publish/schedule, feature, filter, archive/restore projects; edit narratives, location and gallery. Publication requires project.publish.
- **Our Work → Impact Statistics**: enter actual counts, evidence and reporting dates; verify saved indicators, then publish. M&E verifies; Administrator/Super Admin can publish. Material changes revoke verification and unpublish the record.
- **Organization → Countries / Regions / Districts**: manage the operating hierarchy and public visibility. Dependent projects are hidden when a referenced operating area is hidden.
- **System → Settings**: branding, mission, vision, goal, verified contact details, social links, footer and default SEO.
- **System → Policy Pages**: edit Privacy Policy and Terms with `settings.manage`; safeguarding content requires `safeguarding.update`.
- **Content → Content Pages**: edit the copy, translations, status, order, SEO metadata and images for the informational pages served from the public navigation.
- **Content → News, Events & Media**: draft, schedule and publish news, events, publications and gallery images. Publications can link to a PDF or an external URL.
- **Content → Partners**: manage partner profiles, logos, featured images, contact links, ordering and homepage visibility. Public partner listing and detail pages are available at `/about/partners`.
- **Content → Media Library**: upload optimized public images once, add accessibility metadata and organize them into collections for reuse across editorial content.
- **Organization → Donations**: track pending pledges, payment methods, confirmed receipts and internal follow-up. No card or mobile money processor is connected.
- **Organization → Get Involved Applications**: review volunteer, membership and partnership enquiries, assign staff and update their workflow status.
- **System → Users**: filter accounts by role and status, view last login, deactivate/reactivate accounts and require a password change.
- **System → Users / Roles**: user activation and granular permissions. Permissions are selected by name in the role editor; there is no need to hand-create permission names.

Seeded roles include Super Admin, Administrator, Executive Secretary, Finance Officer, Content Manager, Programme Officer, M&E Officer, Communications Officer, Membership Officer and Safeguarding Officer. All panel users require `admin.access` and an active account. Policies enforce action-level authorization. Administrator does not receive safeguarding access or role administration. Only the safeguarding role and Super Admin receive safeguarding permissions. RP1 adds performance and report permissions; report authoring and submission are planned for RP2. Super Admin role permissions cannot be edited or deleted. Users cannot delete themselves or modify their own assigned roles through the CMS.

## Department leadership and workflow

The organization module supports multiple department memberships per account, historical leadership appointments, department-scoped tasks, comments and private attachments, reusable approval workflows, approval action history, database notifications and activity entries. The Department Control Panel includes department leadership, active projects, staff workload, task and project status, task completion by month, pending approvals, recent activity, and upcoming task, milestone and project deadlines. Users can switch only among departments in their active scope. The Filament workspace also includes Organization Overview, My Work, Leadership Appointments, Department Tasks and My Approvals. Existing `head_staff_id` data remains intact; appointments provide the longer-term leadership history. Task overdue state is calculated from its due date. Default approval route definitions are seeded without creating employee records.

Department access uses `DepartmentAccessService` together with Spatie permissions. Project visibility includes both lead and supporting departments, while project editing is limited to the lead department unless a user has explicit organization-wide access. `organization.full_access` is a permission grant; names and job titles do not grant global access.

## Finance foundation (F1)

Finance F1 provides financial years, a hierarchical starter chart of accounts, bank account references that store only a masked account identifier, department-scoped cash accounts, finance permissions and a basic Finance Dashboard. Opening balances and dashboard totals remain grouped by currency. Starter year/account records are idempotent and do not overwrite later edits. The `Finance Officer` role receives the F1 configuration permissions when first created; existing role customizations are preserved. The technical Super Admin receives finance foundation access, but this migration grants no finance approval, payment execution or journal posting authority.

F1 does not implement budgeting, income/expense requests, payments, double-entry journals, advances, petty cash transactions, grants, reconciliation or period closing. Those remain later finance phases. Do not treat the displayed opening balances as a posted trial balance.

## Reports & Performance (RP1)

RP1 adds reporting periods, a KPI library, scoped targets, manually entered and verified results, editable performance rating bands, score settings and an executive dashboard with department/project filters. KPI calculations are centralized in `PerformanceCalculationService`: higher-is-better uses actual ÷ target, lower-is-better uses target ÷ actual once performance falls short, and range KPIs compare actual values with configured bounds. Scores use weighted averages and an editable cap. Results become immutable after verification; changes require a returned/revised entry. Department and project/task measures use existing operational records where possible.

The existing application does not yet contain an internal MEL indicator catalogue or finance transaction/budget data model, so those KPI sources are marked for integration and do not fabricate actuals. Report Builder, approval submissions, compliance calculations, staff appraisal cycles, trend charts and exports remain in RP2–RP5.

## Project operations (Phase 3)

Project records now support an optional code and project manager, a single lead department and multiple supporting departments. Lead and support assignments are checked against each user's active department scope. Supporting departments may view project details; project operations remain editable by the lead department's authorized users or organization-wide users. The project record page provides Activities, Milestones and Risks tabs. Activity completion percentage is bounded in the form, and risk level is calculated from likelihood × impact (1–5 scale). Grant references and activity budget-line references are nullable placeholders for the later finance phases; no grant or budget tables are created here. Project statuses include Planned, Active, On Hold, Completed and Cancelled, while legacy Suspended records remain supported.

## Content and language

English is the fallback. Programme title, summary and description use structured JSON keyed by `en` and `sw`; administrators enter Kiswahili manually. The language switch persists the selected locale in the session. Untranslated fields fall back to English and the website explains this. Automatic translation is not enabled. Full translated pages and settings remain in Phase 6.

Unknown contacts are blank. The public website displays only configured contact details. Replace illustrative stock photographs with organization-approved images before launch. See [image credits](docs/IMAGE-CREDITS.md). The logo is a provisional SVG heart mark with a text wordmark, configurable in settings.

Uploads accept JPEG, PNG or WebP only, with MIME and extension validation, a 5 MB limit and a 16-megapixel decoding limit. Images are assigned UUID filenames, re-encoded as WebP and resized server-side to at most 1920px, with 960px and 480px thumbnails. Programme cards use thumbnails. Files are public website imagery; never upload private records to this disk. Future CVs and safeguarding files require private storage.

## Verification

```sh
php artisan migrate
php artisan db:seed
php artisan route:list
php artisan test
npm run build
composer validate --strict
```

PHP source is formatted with Laravel Pint. To format future changes, optionally install it with `composer require --dev laravel/pint` and run `vendor/bin/pint` (it is not a runtime dependency).

Tests use an in-memory SQLite database, never your development database. They cover page rendering, settings and role policies, project department scope and assignment, project operations, department tasks and approvals, finance foundation, content management, and public/private separation. For browser checks, serve a migrated development/isolated test database, then run `npx playwright install chromium` once and `npm run test:responsive`. To use an installed Chrome instead, set `PLAYWRIGHT_CHROME_PATH` to its executable. `PLAYWRIGHT_BASE_URL` defaults to `http://127.0.0.1:8000`. The browser suite checks 320, 375, 430, 768, 1024, 1280 and 1440px, image loads, JavaScript errors, menus, language selection and public routes. Test traces/screenshots are ignored by Git.

Database migration and seeding checks against the configured MySQL account are separate from these automated tests.

## Production and shared hosting

See [Hostinger deployment](docs/HOSTINGER.md). Build assets before uploading. Set `APP_ENV=production`, `APP_DEBUG=false`, a valid HTTPS `APP_URL`, secure cookies, and private production MySQL credentials. The only web document root must be Laravel's `public/` directory. Do not expose the repository root, `.env`, `storage/logs/`, tests or vendor source as downloadable files.

## Backups

Schedule encrypted MySQL exports and backups of `storage/app/public`, plus a securely stored copy of the application key. Store backups outside the web root and outside this repository. Define retention/access controls and periodically test restoration. Never change `APP_KEY` on an established deployment without a migration plan.

## Upgrade notes

Existing installations: run `php artisan migrate`, `php artisan db:seed`, `php artisan permission:cache-reset`, and `npm run build`. Migrations are additive. See [Phase 3](docs/PHASE-3.md) for project operations. Next for Reports & Performance is RP2: report types, report sections and approval-backed submissions. Internal MEL indicators and transactional finance data will be integrated when those data models are available.

Useful next additions include connecting a verified payment provider, automated acknowledgement and staff notifications, private safeguarding report intake, and fuller Kiswahili versions of the new editorial and application workflows.
