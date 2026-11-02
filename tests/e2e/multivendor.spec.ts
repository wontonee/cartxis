import { test, expect } from '@playwright/test';

test.describe('Multi-Vendor extension', () => {
  test('vendor login page responds without server error when extension present', async ({ page }) => {
    const res = await page.goto('/vendor/login', { waitUntil: 'domcontentloaded' });
    const status = res?.status() ?? 0;
    expect([200, 302, 404]).toContain(status);
  });
});
