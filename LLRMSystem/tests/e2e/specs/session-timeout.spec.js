const { test, expect } = require('@playwright/test');

test.describe('Session timeout', () => {
    test.setTimeout(600 * 1000);

    test('idle timeout modal appears after 5 minutes', async ({ page }) => {
        await page.goto('/modules/dashboard/views/index.php');
        await page.waitForTimeout(300 * 1000); // Wait 5 minutes
        await expect(page.locator('#session-timeout-modal')).toBeVisible();
    });

    test('modal has logout and stay buttons', async ({ page }) => {
        await page.goto('/modules/dashboard/views/index.php');
        await page.waitForTimeout(300 * 1000);
        await expect(page.locator('#session-stay-btn')).toBeVisible();
        await expect(page.locator('#session-logout-btn')).toBeVisible();
    });
});
