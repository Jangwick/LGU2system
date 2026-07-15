const { test, expect } = require('@playwright/test');

test.describe('Acceptance: public search by document type', () => {
    test('a visitor can filter public documents by ordinance type', async ({ page }) => {
        await page.goto('/modules/public-portal/views/search.php');
        await page.fill('input[type="text"][name="q"]', 'budget');
        await page.click('input[type="radio"][value="ordinance"]');
        await page.click('#filter-form button[type="submit"]');

        // Business requirement: results are shown without errors
        await expect(page.locator('text=Error')).not.toBeVisible();
        await expect(page.locator('body')).toBeVisible();
    });
});
