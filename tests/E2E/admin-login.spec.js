import { test, expect } from '@playwright/test';

test.describe('Admin Login', () => {
  test('shows login page', async ({ page }) => {
    await page.goto('/admin/login');
    await expect(page.locator('h1')).toContainText(/login|giriş|voyager/i);
  });

  test('fails with invalid credentials', async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'wrong@example.com');
    await page.fill('input[name="password"]', 'wrong');
    await page.click('button[type="submit"]');
    await expect(page.locator('.text-red-500, .alert-danger')).toBeVisible();
  });
});

test.describe('Admin Dashboard', () => {
  test('redirects unauthenticated users to login', async ({ page }) => {
    await page.goto('/admin');
    await expect(page).toHaveURL(/login/);
  });
});

test.describe('BREAD Browse', () => {
  test('redirects unauthenticated users to login for BREAD pages', async ({ page }) => {
    await page.goto('/admin/users');
    await expect(page).toHaveURL(/login/);
  });
});
