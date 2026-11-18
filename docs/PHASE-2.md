# Phase 2 — 18 October 2026

## Delivered

Phase 2 extends the existing Laravel/Blade/Filament application. It adds project publishing and delivery details, verified impact indicators, and database-managed operating areas. No new runtime packages or production infrastructure are required.

### Projects

- English/Kiswahili JSON content with English fallback, unique stable slugs, programme association, region/district foreign keys, optional location text, dates, enum statuses, optional internal budget/reach, featured image, ordered gallery, metadata and scheduled publication.
- Public listing supports keyword, programme, status, region and start-year filters, validation, pagination and retained query parameters.
- Details show programme, location, duration, status, overview, challenge, objectives, activities, target groups, published verified indicators, a funding-partner label, gallery and CTA. Empty sections stay hidden. Internal budgets and operational reach are not exposed publicly.
- Draft, future-scheduled, archived, inactive-programme and hidden-location projects are excluded from public details, listings, homepage and sitemap.
- Programme pages include their published projects and verified programme-level indicators.
- Homepage supports a selected published project, fallback to the latest featured published project, and up to three additional featured projects. Editors select the preferred project in Content → Homepage.
- Programme Officers and Content Managers can prepare/edit drafts. Only project.publish holders (seeded Super Admin and Administrator) can publish or schedule. Editing a published project without publication permission returns it to draft.

### Verified impact

- Indicators store the actual integer count, label translations, metric, display suffix/icon/order, optional programme/project scope, reporting date, private evidence reference, verifier and verification timestamp.
- Workflow: save a draft with source and reporting date → choose **Verify saved indicator** → enable publication and save. Verification requires impact.verify plus impact.update; publication requires impact.publish. The seeded M&E Officer can verify but cannot publish. Administrator and Super Admin can do both.
- Changes to the figure, label, source, reporting date, suffix or scope revoke verification and unpublish it. Moving a project to another programme similarly invalidates its project-scoped indicators.
- Select Other indicator for a custom count; different custom labels remain separate on the homepage.
- K+/M+ are display formats: enter 10000 as the actual value, then choose K+ to display 10K+. No figures are fabricated or summed across potentially overlapping scopes.
- Homepage uses the latest published organization-wide record per metric. Project/programme pages show appropriately scoped indicators; /impact has programme/metric filters and pagination.
- Public counters respect reduced motion and keep a static screen-reader value. Evidence references and verifier identifiers are not rendered publicly.
- Dashboard charts use actual records: project counts by status and the latest published verified programme-level people-reached figures. Charts have informative empty states and screen-reader summaries; no background polling is required.

### Locations

- Countries → Regions → Districts, with foreign keys, unique public slugs, active flags, ordering, soft deletes and restoration.
- Initial reference data: Tanzania (TZ), Ruvuma, Namtumbo. Seeders preserve existing edits, archived records and custom homepage introductions.
- Country/region/district hiding cascades through public operating-area displays and projects. Project forms validate that the selected district belongs to its region. Referenced districts cannot be moved in ways that would invalidate project geography.
- Where We Work, homepage, footer and contact-area summaries read published location records. The Tanzania regional map is a locally served SVG with named regions, reference-inspired colors and a larger-map link. Colors are decorative, and operating areas remain defined by published CMS records. It requires no map API; source and license are recorded in IMAGE-CREDITS.md.

## Database and files

Migration: `database/migrations/2026_10_17_000002_create_projects_impact_and_locations.php`.

New tables: `countries`, `regions`, `districts`, `projects`, `project_images`, `impact_statistics`.

Added models, policies, Filament resources/pages, controllers, project-filter Form Request, status enum, real-data widgets, Blade cards/pages and location/indicator seeders. Extended Programme relations, homepage settings, sitemap, route definitions, existing programme/homepage views, responsive tests and README. See the source tree for the implementation; the application remains one Laravel repository.

New public routes: GET `/projects`, `/projects/{slug}`, `/impact`, `/where-we-work` replace their Phase 1 informational destinations. Admin resources are `/admin/projects`, `/admin/impact-statistics`, `/admin/countries`, `/admin/regions`, `/admin/districts` with action-level policies. Public resource URLs use slugs; admin-only record identifiers are internal.

New application permissions: `project.publish`, `impact.verify`, `impact.publish`. Existing project.*, impact.* and location.* permissions govern CRUD/restore. PhaseTwoSeeder adds the new grants without resetting existing roles' other permissions. No database-server account or global SQL privileges are changed.

## Upgrade an existing Phase 1 database

```sh
npm run db:start                 # this Mac's project-local database only
php artisan migrate
php artisan db:seed --class=PhaseTwoSeeder
php artisan permission:cache-reset
npm run build
php artisan serve
```

The upgrade migration adds tables; it does not replace Phase 1 content or reset user passwords. A private database backup was saved under `.local/backups/` before the local upgrade. `.env` retains the working project-local port 33307. The service at port 3306 is untouched.

Fresh installs use the README's standard migrate and DatabaseSeeder instructions. PhaseTwoSeeder intentionally creates no example projects. Five default impact indicators remain zero-valued, unverified and unpublished until actual evidence is entered and approved.

## Validation

Final results: **46 feature tests / 308 assertions** passed on both in-memory SQLite and the isolated MariaDB/MySQL-driver test schema. **13 browser checks** passed (12 public interaction/responsiveness checks and one authenticated mobile/desktop CMS check). The Vite production build, route listing (72 total routes), Pint formatting, Composer validation and JavaScript syntax checks passed. Populated project/homepage/location screenshots were reviewed; five images including lazy-loaded variants were checked after scrolling into view. The browser test server was stopped and its generated image files removed. Synthetic records were removed by the isolated test-schema reset; they were never added to the application database.


Production configuration, routes, views and Filament caches built successfully and were cleared afterward for local development. All application migrations are applied. The working database contains the reference location hierarchy and no public projects or verified public indicators.

Feature tests cover scheduled/draft/archived visibility, XSS-safe content, filters and pagination, CMS project CRUD/publication/restore, forged publication state, location hierarchy validation/visibility, country/region/district forms, scoped permission restrictions, impact evidence/verification/publication/revocation, forged verification state, homepage selection/fallback and chart empty states. Reference-seeder regression checks preserve renamed/archived content.

Browser tests cover the original public routes plus project listings/details, impact and locations at 320, 375, 430, 768, 1024, 1280 and 1440px. They check images, JS errors, horizontal overflow, filters, menu interaction, translations, reduced-motion counters and authenticated CMS screens on mobile/desktop.

Browser fixtures in `tests/Browser/seed.php` refuse a database unless it is local/testing and its name ends in `_test`. They use clearly labelled synthetic content and an array cache. For a disposable test schema already created with migration privileges:

```sh
CACHE_STORE=array DB_DATABASE=ughi_test php artisan migrate --seed
CACHE_STORE=array DB_DATABASE=ughi_test php tests/Browser/seed.php
CACHE_STORE=array DB_DATABASE=ughi_test APP_URL=http://127.0.0.1:8017 php artisan serve --port=8017
```

In another terminal, set PLAYWRIGHT_BASE_URL to that server and PLAYWRIGHT_PROJECT_PATH to `/projects/test-only-community-outreach`, then run `npm run test:responsive`. Optional PLAYWRIGHT_ADMIN_EMAIL/PASSWORD enable authenticated checks; use only the disposable test account. The stock image variants generated for these checks are recorded in `.qa/phase2-fixture-media.json` and removed after local verification. Do not publish test fixtures or run browser seeding against the application database. Stop the temporary web server when finished.

## Phase 3 remaining

News, events, impact stories, publications and gallery albums. Related stories/publications on projects should be wired in when those models and publication scopes exist; they are not simulated by Phase 2. Later phases retain their assigned team, engagement, safeguarding, donations, full bilingual settings/pages, audit-log and deployment-hardening work.
