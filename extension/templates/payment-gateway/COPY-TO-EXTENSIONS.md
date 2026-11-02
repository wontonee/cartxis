## How to use this template

1. Copy the folder `extension/templates/payment-gateway/` to `extension/<your-extension>/`.
2. Update `extension.json`:
   - `code`, `name`, `author`, `provider`
3. Update namespaces in PHP files:
   - `Acme\\ExampleGateway\\...` → your vendor/extension namespace
4. Rename the UI locale namespace in `resources/locales/en.json` to `extensions.{your_snake_code}` and add more locale files as needed (`ar.json`, `tr.json`, …). See `docs/I18N.md`.
5. Run:
   - `php artisan cartxis:extensions:sync`
   - `php artisan cartxis:extensions:install <your-extension-code>`
   - `php artisan cartxis:extensions:activate <your-extension-code>`

If your extension needs Composer dependencies, you should package it properly or add an autoloader strategy; filesystem extensions rely on `provider_file` for provider loading.

Or scaffold with: `php artisan cartxis:extension:make "My Gateway" --template=payment-gateway` (creates locale stubs for you).
