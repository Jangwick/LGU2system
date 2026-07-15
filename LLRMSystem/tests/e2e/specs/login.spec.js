const { test, expect } = require('@playwright/test');

test.describe('Login flow', () => {
    test('login page loads', async ({ page }) => {
        await page.goto('/modules/authentication/views/login.php');
        await expect(page.locator('#login-form')).toBeVisible();
    });

    test('invalid credentials show error', async ({ page }) => {
        await page.goto('/modules/authentication/views/login.php');
        await page.fill('#login-form input[name="email"]', 'invalid@example.com');
        await page.fill('#login-form input[name="password"]', 'wrongpassword');
        await page.click('#login-form button[type="submit"]');
        await expect(page.locator('text=Invalid')).toBeVisible();
    });
});
