import { test, expect } from '@playwright/test'

test('Import Theme Data from Appearance page', async ({ page }) => {
  // Login
  await page.goto('http://cartxis.test/admin/login')
  await page.fill('input[name="email"]', 'admin@wontonee.com')
  await page.fill('input[name="password"]', 'admin123')
  await page.click('button[type="submit"]')
  await page.waitForURL('**/admin/dashboard')

  // Navigate to Appearance
  await page.goto('http://cartxis.test/admin/appearance')
  await page.waitForSelector('h1:text("Appearance")')

  // Verify "Import Demo Data" button is visible
  const importBtn = page.locator('button:has-text("Import Demo Data")')
  await expect(importBtn).toBeVisible()

  // Click it — modal should appear
  await importBtn.click()
  await expect(page.locator('h3:text("Import Theme Data")')).toBeVisible()

  // Verify modal content
  await expect(page.locator('text=CMS Blocks')).toBeVisible()
  await expect(page.locator('text=Navigation Menus')).toBeVisible()
  await expect(page.locator('text=Theme Settings')).toBeVisible()

  // Fresh Import checkbox should be unchecked by default
  const freshCheckbox = page.locator('#import-fresh')
  await expect(freshCheckbox).not.toBeChecked()

  // Click Import Data button (merge mode)
  const confirmBtn = page.locator('button:has-text("Import Data")')
  await expect(confirmBtn).toBeVisible()
  await confirmBtn.click()

  // Wait for import to complete and modal to close
  await page.waitForTimeout(3000)

  // Should show success flash message
  const flash = page.locator('text=imported successfully')
  await expect(flash).toBeVisible({ timeout: 10000 })

  console.log('✅ Import Theme Data test passed!')
})
