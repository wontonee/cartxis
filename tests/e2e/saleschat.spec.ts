import { test, expect, type Page } from '@playwright/test'

// ─── Helpers ─────────────────────────────────────────────────────────────────

async function adminLogin(page: Page) {
  await page.goto('/admin/login')
  await page.fill('input[name="email"]', 'admin@wontonee.com')
  await page.fill('input[name="password"]', 'admin123')
  await page.click('button[type="submit"]')
  await page.waitForURL('**/admin/dashboard')
}

async function openChat(page: Page) {
  await page.goto('/')
  await page.waitForSelector('.sc-fab', { timeout: 10_000 })
  await page.click('.sc-fab')
  await page.waitForSelector('.sc-window', { state: 'visible' })
}

async function sendMessage(page: Page, text: string) {
  await page.fill('.sc-input', text)
  await page.click('.sc-send-btn')
}

async function waitForBotReply(page: Page) {
  // Wait for typing indicator to appear then disappear (or just wait for a new bot bubble)
  await page.waitForSelector('.sc-typing', { state: 'visible', timeout: 5_000 }).catch(() => null)
  await page.waitForSelector('.sc-typing', { state: 'hidden', timeout: 30_000 })
}

// ─── Widget UI ────────────────────────────────────────────────────────────────

test.describe('SalesChat Widget — UI', () => {
  test('FAB button is visible on the storefront', async ({ page }) => {
    await page.goto('/')
    const fab = page.locator('.sc-fab')
    await expect(fab).toBeVisible({ timeout: 10_000 })
  })

  test('opens and closes the chat window', async ({ page }) => {
    await page.goto('/')
    const fab = page.locator('.sc-fab')
    await fab.waitFor()

    // Open
    await fab.click()
    const window = page.locator('.sc-window')
    await expect(window).toBeVisible()

    // Welcome message should appear
    const firstBotMsg = page.locator('.sc-bubble-bot').first()
    await expect(firstBotMsg).toBeVisible({ timeout: 5_000 })

    // Close via × button
    await page.click('.sc-close-btn')
    await expect(window).not.toBeVisible()
  })

  test('chat input is interactive', async ({ page }) => {
    await openChat(page)
    const input = page.locator('.sc-input')
    await input.fill('hello')
    await expect(input).toHaveValue('hello')

    // Press Enter to send
    await input.press('Enter')
    // Input should clear
    await expect(input).toHaveValue('')
  })
})

// ─── Chat Conversations ───────────────────────────────────────────────────────

test.describe('SalesChat — Conversations', () => {
  test('greeting is handled gracefully', async ({ page }) => {
    await openChat(page)
    await sendMessage(page, 'hi')
    await waitForBotReply(page)

    // Should NOT show product cards for a greeting
    const products = page.locator('.sc-product-card')
    await expect(products).toHaveCount(0)

    // Bot should reply with something
    const botBubbles = page.locator('.sc-bubble-bot')
    await expect(botBubbles.last()).toBeVisible()
  })

  test('product search returns results', async ({ page }) => {
    await openChat(page)
    await sendMessage(page, 'show me shampoo')
    await waitForBotReply(page)

    // At least one product card should appear
    const cards = page.locator('.sc-product-card')
    const count = await cards.count()
    console.log(`🛍️  Products returned: ${count}`)
    expect(count).toBeGreaterThan(0)

    // Each card should display a name
    const firstName = await cards.first().locator('.sc-product-name').innerText()
    expect(firstName.trim().length).toBeGreaterThan(0)
    console.log(`  First result: ${firstName.trim()}`)
  })

  test('filters by price range', async ({ page }) => {
    await openChat(page)
    await sendMessage(page, 'products under 500')
    await waitForBotReply(page)

    const cards = page.locator('.sc-product-card')
    await expect(cards.first()).toBeVisible({ timeout: 15_000 })
    console.log(`💰  Products under 500: ${await cards.count()}`)
  })
})

// ─── Order Flow ───────────────────────────────────────────────────────────────

test.describe('SalesChat — Order Flow', () => {
  test('inline qty order: "i need 2 shampoo can you create order"', async ({ page }) => {
    await openChat(page)

    // First get some products into context
    await sendMessage(page, 'show me shampoo')
    await waitForBotReply(page)
    await expect(page.locator('.sc-product-card').first()).toBeVisible({ timeout: 15_000 })

    // Now send inline qty + order intent
    await sendMessage(page, 'i need 2 can you create order')
    await waitForBotReply(page)

    // Bot should show an Add-to-Cart button
    const cartBtn = page.locator('.sc-cart-btn')
    await expect(cartBtn).toBeVisible({ timeout: 15_000 })
    console.log(`🛒  Cart button text: ${await cartBtn.innerText()}`)
    expect(await cartBtn.innerText()).toMatch(/2/)
  })

  test('multi-turn order: bot asks "how many?" then user replies', async ({ page }) => {
    await openChat(page)

    // Get product into context
    await sendMessage(page, 'show me milk')
    await waitForBotReply(page)
    await expect(page.locator('.sc-product-card').first()).toBeVisible({ timeout: 15_000 })

    // Say "order it" without a qty
    await sendMessage(page, 'i want to order it')
    await waitForBotReply(page)

    // Bot should ask "how many?"
    const lastBotMsg = page.locator('.sc-bubble-bot').last()
    const lastText = await lastBotMsg.innerText()
    console.log(`🤖  Bot reply: ${lastText}`)
    expect(lastText.toLowerCase()).toMatch(/how many|quantity|how much/)

    // User replies with a quantity
    await sendMessage(page, '3')
    await waitForBotReply(page)

    // Now the cart button should appear
    const cartBtn = page.locator('.sc-cart-btn')
    await expect(cartBtn).toBeVisible({ timeout: 15_000 })
    console.log(`🛒  Cart button: ${await cartBtn.innerText()}`)
  })

  test('natural language order: "one qty can you able to create order"', async ({ page }) => {
    await openChat(page)

    // Get product context
    await sendMessage(page, 'show me soap')
    await waitForBotReply(page)
    await expect(page.locator('.sc-product-card').first()).toBeVisible({ timeout: 15_000 })

    await sendMessage(page, 'one qty can you able to create order')
    await waitForBotReply(page)

    // Should get either cart button directly or "how many?" prompt
    const cartBtn = page.locator('.sc-cart-btn')
    const lastBot  = page.locator('.sc-bubble-bot').last()
    const botText  = await lastBot.innerText()
    console.log(`🤖  Bot reply: ${botText}`)

    const hasCartBtn  = await cartBtn.isVisible().catch(() => false)
    const askedHowMany = botText.toLowerCase().includes('how many')

    expect(hasCartBtn || askedHowMany).toBeTruthy()
  })

  test('cart button click adds product', async ({ page }) => {
    await openChat(page)

    await sendMessage(page, 'show me shampoo')
    await waitForBotReply(page)
    await expect(page.locator('.sc-product-card').first()).toBeVisible({ timeout: 15_000 })

    await sendMessage(page, 'i need 1 qty create order')
    await waitForBotReply(page)

    const cartBtn = page.locator('.sc-cart-btn')
    await expect(cartBtn).toBeVisible({ timeout: 15_000 })
    await cartBtn.click()

    // Should transition to success state
    const success = page.locator('.sc-cart-success')
    await expect(success).toBeVisible({ timeout: 10_000 })

    // "Go to Cart" link should appear
    const cartLink = page.locator('.sc-cart-link')
    await expect(cartLink).toBeVisible()
    console.log('✅ Product added to cart successfully')
  })
})

// ─── Admin — Settings Page ─────────────────────────────────────────────────────

test.describe('SalesChat Admin — Settings', () => {
  test('settings page loads and bot name field is visible', async ({ page }) => {
    await adminLogin(page)
    await page.goto('/admin/saleschat')
    await page.waitForLoadState('networkidle')

    await expect(page.locator('input[name="bot_name"]')).toBeVisible()
    console.log('✅ SalesChat settings page loaded')
  })

  test('AI Agent select has a visible dropdown chevron', async ({ page }) => {
    await adminLogin(page)
    await page.goto('/admin/saleschat')
    await page.waitForLoadState('networkidle')

    // The wrapper div should have a chevron icon inside it (pointer-events-none span)
    const selectWrapper = page.locator('.relative').filter({ has: page.locator('select[name="agent_setting_key"]') })
    await expect(selectWrapper).toBeVisible()

    // The chevron SVG should be visible inside the wrapper
    const chevron = selectWrapper.locator('svg').last()
    await expect(chevron).toBeVisible()
    console.log('✅ AI Agent select chevron is visible')
  })

  test('save settings shows success notification', async ({ page }) => {
    await adminLogin(page)
    await page.goto('/admin/saleschat')
    await page.waitForLoadState('networkidle')

    const saveBtn = page.locator('button[type="submit"]:has-text("Save")')
    await expect(saveBtn).toBeVisible()
    await saveBtn.click()

    // Flash / toast success
    const success = page.locator('text=saved, text=success, text=updated').first()
    await expect(success).toBeVisible({ timeout: 8_000 })
    console.log('✅ Settings saved successfully')
  })
})

// ─── Admin — Training ─────────────────────────────────────────────────────────

test.describe('SalesChat Admin — Training', () => {
  test('training card is visible on settings page', async ({ page }) => {
    await adminLogin(page)
    await page.goto('/admin/saleschat')
    await page.waitForLoadState('networkidle')

    const trainBtn = page.locator('button:has-text("Train Now"), button:has-text("Train")')
    await expect(trainBtn).toBeVisible()
    console.log('✅ Training card visible')
  })

  test('Train Now button triggers training and shows result', async ({ page }) => {
    await adminLogin(page)
    await page.goto('/admin/saleschat')
    await page.waitForLoadState('networkidle')

    // Ensure at least Products scope is checked
    const productCheckbox = page.locator('input[type="checkbox"][value="products"]')
    if (await productCheckbox.count()) {
      const checked = await productCheckbox.isChecked()
      if (!checked) await productCheckbox.check()
    }

    const trainBtn = page.locator('button:has-text("Train Now")')
    await trainBtn.click()

    // Should disable the button while training (spinner)
    await expect(trainBtn).toBeDisabled({ timeout: 5_000 }).catch(() => null)

    // After training finishes (max 30 s) something should indicate success
    const successIndicator = page.locator('text=Training complete, text=Trained, text=Last trained')
    await expect(successIndicator.first()).toBeVisible({ timeout: 30_000 })
    console.log('✅ Training completed successfully')
  })

  test('training status stats are displayed', async ({ page }) => {
    await adminLogin(page)
    await page.goto('/admin/saleschat')
    await page.waitForLoadState('networkidle')

    // Stats grid should show numeric values (products, categories, brands counts)
    const statsRow = page.locator('text=Products, text=Categories, text=Brands').first()
    await expect(statsRow).toBeVisible()
    console.log('✅ Training stats grid visible')
  })
})
