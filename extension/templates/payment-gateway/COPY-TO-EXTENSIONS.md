## How to use this template

1. Copy the folder `extension/templates/payment-gateway/` to `extensions/<your-extension>/`.
2. Update `extension.json`:
   - `code`, `name`, `author`, `provider`
3. Update namespaces in PHP files:
   - `Acme\\ExampleGateway\\...` → your vendor/extension namespace
4. Run:
   - `php artisan vortex:extensions:sync`
   - `php artisan vortex:extensions:install <your-extension-code>`
   - `php artisan vortex:extensions:activate <your-extension-code>`

If your extension needs Composer dependencies, you should package it properly or add an autoloader strategy; filesystem extensions rely on `provider_file` for provider loading.
