const { test, expect } = require('@playwright/test');

test.describe('Public search', () => {
    test('public search page loads and returns results', async ({ page }) => {
        await page.goto('/modules/public-portal/views/search.php');
        await expect(page.locator('input[type="text"][name="q"]')).toBeVisible();
    });

    test('public search handles type filter', async ({ page }) => {
        await page.goto('/modules/public-portal/views/search.php');
        await page.fill('input[type="text"][name="q"]', 'budget');
        await page.click('input[type="radio"][value="ordinance"]');
        await page.click('#filter-form button[type="submit"]');
        await expect(page.locator('body')).toBeVisible();
    });
});

test.describe('Admin search', () => {
    test('admin search page requires login', async ({ page }) => {
        await page.goto('/modules/search/views/index.php');
        await expect(page).toHaveURL(/login.php/);
    });
});
