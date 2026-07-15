const { test, expect } = require('@playwright/test');

test.describe('Login flow', () => {
    test('login page loads', async ({ page }) => {
        await page.goto('/modules/authentication/views/login.php');
        await expect(page.locator('#login-form')).toBeVisible();
    });

    test('invalid credentials show error', async ({ page }) => {
        const uniqueEmail = `invalid${Date.now()}@example.com`;
        await page.goto('/modules/authentication/views/login.php');
        await page.fill('#login-form input[name="email"]', uniqueEmail);
        await page.fill('#login-form input[name="password"]', 'wrongpassword');
        await page.click('#login-form button[type="submit"]');
        await expect(page.locator('#alert-container')).toContainText('Invalid', { timeout: 15000 });
    });
});
