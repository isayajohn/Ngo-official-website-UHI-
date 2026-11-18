# Hostinger shared hosting deployment

This application needs standard PHP and MySQL, no root access, Redis, Docker, WebSockets or background queue worker. Node is needed only on the build machine.

1. In hPanel select PHP 8.2 or newer (8.3/8.4 recommended) and enable the extensions in the README, including `intl`, `fileinfo`, `pdo_mysql`, `gd` and WebP support. Use an appropriate upload limit (at least 5 MB) and PHP memory limit (256 MB or more for image processing). Check the extensions in both CLI and web PHP when both are available.
2. Create a dedicated MySQL database and database user in hPanel. Use the complete Hostinger-prefixed names and supplied host. Give the user access only to this application's database. Put credentials in the private production `.env`; never in PHP files or `.env.example`.
3. Build locally with `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build`. Upload application code, vendor and `public/build` through SFTP or File Manager. Do not upload `node_modules`, `.git`, tests, a local `.env`, SQLite files or private development data. Keep a local development checkout separate from the production package.
4. Prefer a document root pointing to `<application>/public`. Keep the application outside `public_html`. If your hosting plan cannot customize the root, put only the **contents of public/** inside `public_html`, with the rest of Laravel in a sibling private directory. Adjust the two paths in `public_html/index.php` (maintenance file, autoloader and bootstrap) to that private directory and call `$app->usePublicPath(__DIR__)` on the bootstrapped application before `handleRequest`. This ensures public assets and the storage link use the actual web root. Never copy the whole repository into a publicly served directory. Include Laravel's `public/.htaccess` so clean URLs resolve.
5. Create `.env` outside the public root. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`, `APP_TIMEZONE=Africa/Dar_es_Salaam`, `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`, `SESSION_DRIVER=file`, `SESSION_LIFETIME=60`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`, and production MySQL values. Set `APP_KEY` using a securely generated Laravel key; preserve it on future deployments. No payment or mail API keys are required in Phase 1.
6. Grant your hosting user write access to `storage` and `bootstrap/cache`. Normally use 755 for directories and 644 for files, with stricter permissions for `.env` as supported; do not use 777. Ensure `storage/framework/cache/data`, `sessions`, `views` and `storage/logs` exist.
7. With hPanel Terminal/SSH, run:

   ```sh
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   php artisan optimize
   php artisan filament:optimize
   ```

   Production seeding creates reference roles, settings and programmes only, never the development administrator. Run `php artisan ughi:admin` to securely provision an administrator before opening the CMS. Change any imported development password and verify `must_change_password` is cleared only after changing it.
8. Without Terminal: prepare and migrate a dedicated release database locally against the same PHP/MySQL versions and export its schema/reference data; import it using hPanel phpMyAdmin. Alternatively use an authorized deployment machine to run migrations against a reachable database. Never publish a web-based Artisan runner. Upload prebuilt Composer dependencies and Vite assets. Leave framework optimization caches unbuilt rather than uploading caches with local paths/secrets. Clear `bootstrap/cache/config.php` and generated route/view caches from any uploaded release. The unoptimized app still works normally.
9. For public media without symlink support, configure a public disk root outside the private application files in `config/filesystems.php` to point to `public_path('storage')`, preserving the disk URL of `APP_URL/storage`. Create that directory using File Manager. This is public imagery only; keep private uploads separate. Do not duplicate stale media folders as an ongoing synchronization strategy.
10. Enable HTTPS and validate all public routes, `/sitemap.xml`, `/admin/login`, first-login password change and role restrictions. Verify uploaded image URLs, header/footer settings and mobile menu. Verify `.env` and private paths return 403/404, production errors show branded pages, and no development credentials remain.

No cron is required for Phase 1. If scheduled features are added, configure hPanel cron with `php /private/application/artisan schedule:run` once a minute using the correct PHP binary. Basic website operation must remain synchronous.

Before each update: back up the database and uploads, prepare a release, enable maintenance mode if terminal access is available, upload code/assets, run migrations and rebuild caches. Without terminal, use a maintenance response at the web-server level during a File Manager deployment. Keep a rollback package; database changes may require a tested restore rather than code rollback alone.
