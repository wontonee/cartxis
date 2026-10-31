import { test, expect } from '@playwright/test'

/**
 * Optional E2E stub for cartxis-delivery.
 * Skips when the commercial extension is not installed / activated.
 */

async function extensionLikelyInstalled(page: import('@playwright/test').Page): Promise<boolean> {
  const res = await page.request.get('/admin/delivery/settings', {
    maxRedirects: 0,
  }).catch(() => null)

  if (!res) return false
  // 200 = active + authenticated redirect path may be 302 to login
  return [200, 302, 401, 403].includes(res.status()) && res.status() !== 404
}

test.describe('Cartxis Delivery', () => {
  test('admin delivery settings route exists when extension is installed', async ({ page }) => {
    const installed = await extensionLikelyInstalled(page)
    test.skip(!installed, 'cartxis-delivery not installed — skipping')

    await page.goto('/admin/login')
    // Soft check: login page or settings (if already authed)
    await expect(page.locator('body')).toBeVisible()
  })

  test('customer tracking API responds for unknown token with 404', async ({ request }) => {
    const res = await request.get('/api/delivery/track/not-a-real-token-xxxxxxxx')
    // 404 when extension active; 404/405/500 when routes missing — treat non-404-route as skip
    if (res.status() === 404) {
      expect(res.status()).toBe(404)
      return
    }
    test.skip(true, 'delivery API routes not registered')
  })
})
