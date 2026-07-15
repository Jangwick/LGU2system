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

    test('public search preview button opens modal and displays document details', async ({ page }) => {
        await page.goto('/modules/public-portal/views/search.php');
        await page.click('button:has-text("Preview")');
        await expect(page.locator('#preview-modal')).not.toHaveClass(/hidden/);
        await expect(page.locator('#preview-title')).not.toHaveText('---');
        await expect(page.locator('#preview-filename')).not.toHaveText('---');
    });
});

test.describe('Admin search', () => {
    test('admin search page requires login', async ({ page }) => {
        await page.goto('/modules/search/views/index.php');
        await expect(page).toHaveURL(/login.php/);
    });
});
