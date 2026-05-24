import { test, expect } from '@playwright/test';

test.describe('BREAD CRUD Operations', () => {
  test('browse page redirects to login when unauthenticated', async ({ page }) => {
    await page.goto('/admin/users');
    await expect(page).toHaveURL(/login/);
  });

  test('browse roles page redirects to login when unauthenticated', async ({ page }) => {
    await page.goto('/admin/roles');
    await expect(page).toHaveURL(/login/);
  });

  test('browse media page redirects to login when unauthenticated', async ({ page }) => {
    await page.goto('/admin/media');
    await expect(page).toHaveURL(/login/);
  });

  test('settings page redirects to login when unauthenticated', async ({ page }) => {
    await page.goto('/admin/settings');
    await expect(page).toHaveURL(/login/);
  });

  test('compass page redirects to login when unauthenticated', async ({ page }) => {
    await page.goto('/admin/compass');
    await expect(page).toHaveURL(/login/);
  });
});
