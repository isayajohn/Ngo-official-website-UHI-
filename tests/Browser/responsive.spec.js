import { test, expect } from '@playwright/test';

const widths = [320, 375, 430, 768, 1024, 1280, 1440];
const paths = ['/', '/programmes', '/projects', '/impact', '/where-we-work', '/about', '/contact', '/admin/login', ...(process.env.PLAYWRIGHT_PROJECT_PATH ? [process.env.PLAYWRIGHT_PROJECT_PATH] : [])];

for (const width of widths) {
    test(`public pages and admin login fit ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 900 });
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        for (const path of paths) {
            const response = await page.goto(path);
            expect(response.status(), path).toBe(200);
            await page.evaluate(() => document.fonts.ready);
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), `${path} overflows at ${width}px`).toBe(true);
            if (path !== '/admin/login') {
                await expect(page.locator('main h1')).toHaveCount(1);
                const broken = await page.locator('img').evaluateAll(images => images.filter(image => image.complete && image.naturalWidth === 0).map(image => image.src));
                expect(broken).toEqual([]);
            }
        }
        expect(errors).toEqual([]);
    });
}

test('mobile menu and nested navigation work with keyboard dismissal', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/');
    const toggle = page.getByRole('button', { name: 'Open menu', exact: true });
    await toggle.click();
    const menu = page.getByRole('navigation', { name: 'Mobile navigation' });
    await expect(menu).toBeVisible();
    await menu.locator('summary').filter({ hasText: 'Our Work' }).click();
    await expect(menu.getByRole('link', { name: 'All Programmes', exact: true })).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(menu).toBeHidden();
});

test('desktop dropdowns expose programme links and dismiss with Escape', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.goto('/');
    const nav = page.getByRole('navigation', { name: 'Main navigation', exact: true });
    const toggle = nav.getByRole('button', { name: 'Our Work', exact: true });
    await toggle.click();
    await expect(nav.getByRole('link', { name: 'All Programmes', exact: true })).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(toggle).toBeFocused();
    await expect(nav.getByRole('link', { name: 'All Programmes', exact: true })).toBeHidden();
});

test('English fallback and language switch work', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.goto('/');
    await page.getByRole('button', { name: 'SW', exact: true }).click();
    await expect(page.locator('html')).toHaveAttribute('lang', 'sw');
    await expect(page.getByRole('status')).toContainText('English is shown');
    await page.getByRole('button', { name: 'EN', exact: true }).click();
    await expect(page.locator('html')).toHaveAttribute('lang', 'en');
});


test('project filters preserve the selected status', async ({ page }) => {
    await page.goto('/projects');
    await page.getByLabel('Project status', { exact: true }).selectOption('ACTIVE');
    await page.getByRole('button', { name: 'Apply Filters' }).click();
    await expect(page).toHaveURL(/status=ACTIVE/);
    await expect(page.getByLabel('Project status', { exact: true })).toHaveValue('ACTIVE');
    await page.getByRole('link', { name: 'Clear filters' }).click();
    await expect(page.getByLabel('Project status', { exact: true })).toHaveValue('');
});

test('project page renders verified figures under reduced motion', async ({ page }) => {
    test.skip(!process.env.PLAYWRIGHT_PROJECT_PATH, 'Provide a published fixture project in an isolated test database.');
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto(process.env.PLAYWRIGHT_PROJECT_PATH);
    await expect(page.getByRole('heading', { name: 'Verified Impact', exact: true })).toBeVisible();
    await expect(page.getByText('234', { exact: true }).first()).toBeVisible();
    const gallery = page.getByRole('heading', { name: 'Project Gallery', exact: true });
    await expect(gallery).toBeVisible();
});


test('authenticated Phase 2 admin pages fit mobile and desktop', async ({ page }) => {
    test.setTimeout(90_000);
    test.skip(!process.env.PLAYWRIGHT_ADMIN_EMAIL || !process.env.PLAYWRIGHT_ADMIN_PASSWORD, 'Use only a disposable test-database administrator.');
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('/admin/login');
    await page.locator('[id="form.email"]').fill(process.env.PLAYWRIGHT_ADMIN_EMAIL);
    await page.locator('[id="form.password"]').fill(process.env.PLAYWRIGHT_ADMIN_PASSWORD);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await expect(page).toHaveURL(/\/admin$/);
    for (const width of [375, 1440]) {
        await page.setViewportSize({ width, height: 1000 });
        for (const path of ['/admin', '/admin/projects', '/admin/projects/create', '/admin/impact-statistics', '/admin/impact-statistics/create', '/admin/countries', '/admin/regions', '/admin/districts']) {
            const response = await page.goto(path);
            expect(response.status(), path).toBe(200);
            await page.waitForLoadState('networkidle');
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), `${path} overflows at ${width}px`).toBe(true);
        }
    }
    expect(errors).toEqual([]);
});
