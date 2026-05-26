import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/E2E',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: 'html',
  use: {
    baseURL: 'http://localhost:8080',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: {
    command: 'php -S localhost:8080 -t vendor/orchestra/testbench-core/laravel public',
    url: 'http://localhost:8080',
    reuseExistingServer: !process.env.CI,
    timeout: 30000,
  },
});
