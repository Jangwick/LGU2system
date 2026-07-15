const { test, expect } = require('@playwright/test');

const TEST_PAGE = '/tests/e2e/helpers/session-timeout-test.php';

test.describe('Session timeout', () => {
    test('idle timeout modal appears and shows stay/logout buttons', async ({ page }) => {
        await page.goto(TEST_PAGE);
        await page.waitForTimeout(6000); // Wait slightly longer than the 5-second JS timeout
        await expect(page.locator('#session-timeout-modal')).toBeVisible();
        await expect(page.locator('#session-stay-btn')).toBeVisible();
        await expect(page.locator('#session-logout-btn')).toBeVisible();
    });

    test('clicking stay button hides the timeout modal', async ({ page }) => {
        await page.goto(TEST_PAGE);
        await page.waitForTimeout(6000);
        await page.click('#session-stay-btn');
        await expect(page.locator('#session-timeout-modal')).toBeHidden();
    });

    test('clicking logout button redirects to login', async ({ page }) => {
        await page.goto(TEST_PAGE);
        await page.waitForTimeout(6000);
        await page.click('#session-logout-btn');
        await expect(page).toHaveURL(/login/);
    });
});
