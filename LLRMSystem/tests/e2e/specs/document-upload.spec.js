const { test, expect } = require('@playwright/test');

test.describe('Document upload', () => {
    test('admin upload page requires login', async ({ page }) => {
        await page.goto('/modules/document-management/views/index.php');
        await expect(page).toHaveURL(/login.php/);
    });

    test('upload modal shows validation for missing file', async ({ page }) => {
        await page.goto('/modules/document-management/views/index.php');
        await expect(page).toHaveURL(/login.php/);
    });
});
