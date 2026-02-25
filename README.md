# Cartxis — Open Source Laravel eCommerce Platform

A modern, extensible eCommerce platform built with **Laravel 12**, **Inertia.js**, **Vue 3.5**, and **TypeScript**. Cartxis provides a complete solution for building online stores with a powerful admin panel, flexible theme system, and modular architecture.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5.2%2B-3178C6?logo=typescript)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.1-38B2AC?logo=tailwind-css)

---

## ✨ Features

### 🛍️ Shop Features
- **Product Management** — Complete product catalog with variants, attributes, and inventory tracking
- **Shopping Cart** — Real-time cart with session persistence and guest checkout support
- **Multi-Payment Gateway** — Stripe, Razorpay, PayPal, PhonePe, and PayUMoney support
- **Order Management** — Comprehensive order tracking and lifecycle management
- **Customer Accounts** — Registration, authentication, profile, and order history
- **Theme System** — Flexible theme architecture with easy customization

### 🎛️ Admin Features
- **Dashboard** — Analytics and key insights at a glance
- **Product Management** — Products, categories, brands, attributes, and variants
- **Order Processing** — Complete order lifecycle with status management
- **Customer Management** — Profiles, addresses, and notes
- **Settings** — Store configuration, payment methods, shipping rates, and tax rules
- **AI Agents** — Create and manage custom AI agents for commerce workflows
- **Email Templates** — Customizable transactional email templates
- **CMS** — Content management for pages and blocks
- **Reports** — Sales, customer, and inventory reporting
- **Maintenance Mode** — Built-in maintenance banner and system controls

### 💳 Payment Gateways

| Gateway | Status |
|---------|--------|
| Stripe | ✅ Supported |
| Razorpay | ✅ Supported |
| PayPal | ✅ Supported |
| PhonePe | ✅ Supported |
| PayUMoney | ✅ Supported |

### 📱 Mobile App

A full **REST API** is available for building a mobile app (iOS & Android):
- Authentication and customer accounts
- Product catalog, search, and categories
- Cart, checkout, and order management
- Wishlist and reviews
- Store sync and banners

Native iOS & Android apps are coming soon.

### 🔧 Technical Features
- **Modular Architecture** — Package-based structure for easy extension
- **MySQL 8.0+** — Optimised relational database layer
- **Modern Frontend** — Vue 3 with TypeScript and Tailwind CSS
- **Inertia.js** — SPA-like experience without API complexity
- **Two-Factor Authentication** — Enhanced security with 2FA
- **Email Verification** — Built-in email verification workflow
- **Fortify Integration** — Laravel Fortify for authentication

---

## 📋 Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.2 or higher |
| Composer | 2.x |
| Node.js | 18.x or higher |
| NPM | 9.x or higher |
| MySQL | 8.0 or higher |

**Required PHP extensions:** OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath

---

## 🚀 Quick Install via Composer

The fastest way to get Cartxis running:

```bash
composer create-project cartxis/cartxis my-store
cd my-store
php artisan cartxis:install
```

The interactive installer will guide you through:
- Database configuration (with live connection test)
- Admin account creation (name, email, and password)
- Migrations and seeders
- Building frontend assets automatically

Once complete, the installer displays your admin panel URL, email, and password.

> **If you see blank pages or asset errors after the setup wizard**, the frontend build may not have completed yet. Fix it by running:
> ```bash
> npm run build
> # or if you use Yarn:
> yarn build
> ```

---

## 🛠️ Installation (Development / Git Clone)

Use this method if you are contributing to or developing on Cartxis.

### 1. Clone the Repository

```bash
git clone https://github.com/cartxis/cartxis.git
cd cartxis
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup

Create a MySQL database:
```sql
CREATE DATABASE cartxis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cartxis
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run the Installer

```bash
php artisan cartxis:install
```

Or manually run migrations and seed:
```bash
php artisan migrate --seed
```

> **Note:** When seeding manually, provide admin credentials via environment variables:
> ```bash
> CARTXIS_ADMIN_NAME="Admin" \
> CARTXIS_ADMIN_EMAIL="you@example.com" \
> CARTXIS_ADMIN_PASSWORD="yourpassword" \
> php artisan db:seed
> ```

### 6. Build Frontend Assets

Development (watch mode):
```bash
npm run dev
```

Production build:
```bash
npm run build
```

### 7. Start the Server

```bash
php artisan serve
```

Visit `http://localhost:8000` for the storefront and `http://localhost:8000/admin/login` for the admin panel.

---

## ⚙️ Configuration

### Email Configuration

Email (SMTP) settings are configured directly in the **Admin Panel** — no `.env` changes required.

1. Log in to **Admin → Settings → Email Settings**
2. Enter your SMTP host, port, username, password, and encryption type
3. Set your "From" name and address
4. Send a test email to verify the configuration

### Payment Gateway Setup

All payment gateway credentials are configured directly in the **Admin Panel** — no `.env` changes required.

1. Log in to **Admin → Settings → Payment Methods**
2. Select the gateway you want to enable (Stripe, Razorpay, PayPal, PhonePe, or PayUMoney)
3. Enter your API keys and credentials in the provided fields
4. Toggle the gateway to **Active**

Your credentials are stored securely in the database and managed entirely through the admin interface.

### Store Settings

Configure your store through the admin panel:
1. **Settings → Store Configuration** — name, currency, timezone
2. **Settings → Payment Methods** — enable/disable gateways
3. **Settings → Shipping** — shipping rates and zones
4. **Settings → Tax Rules** — tax configuration

---

## 📦 Package Structure

```
packages/Cartxis/
├── Admin/          # Admin panel — controllers, views, assets
├── Cart/           # Shopping cart management
├── CMS/            # Content pages and blocks
├── Core/           # Core utilities, installer command, service providers
├── Customer/       # Customer profiles and authentication
├── Marketing/      # Promotions, coupons, and tools
├── PayPal/         # PayPal payment integration
├── PayUMoney/      # PayUMoney payment integration
├── PhonePe/        # PhonePe payment integration (SDK bundled)
├── Product/        # Product catalog, variants, attributes
├── Razorpay/       # Razorpay payment integration
├── Reports/        # Sales, inventory, and customer reports
├── Sales/          # Orders and sales management
├── Settings/       # System and store settings
├── Setup/          # First-run setup wizard
├── Shop/           # Frontend storefront
├── Stripe/         # Stripe payment integration
└── System/         # System health and utilities
```

---

## 🎨 Theme Development

The default theme is located in `themes/cartxis-default/`.

### Creating a Custom Theme

```bash
cp -r themes/cartxis-default themes/your-theme-name
```

Update `config/theme.php`:
```php
'active' => 'your-theme-name',
```

Customise:
- **Pages**: `themes/your-theme-name/resources/views/pages/`
- **Components**: `themes/your-theme-name/resources/views/components/`
- **Layouts**: `themes/your-theme-name/resources/views/layouts/`

---

## 🧪 Running Tests

```bash
# Run all tests
./vendor/bin/pest

# Feature tests only
./vendor/bin/pest tests/Feature

# With coverage
./vendor/bin/pest --coverage
```

---

## 🚢 Deployment

### Production Checklist

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. `composer install --optimize-autoloader --no-dev`
3. `npm run build`
4. `php artisan config:cache`
5. `php artisan route:cache`
6. `php artisan view:cache`
7. Set file permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```
8. Configure your web server (Nginx/Apache) with document root pointing to `public/`
9. Set up an SSL certificate
10. Configure queue workers and the task scheduler

### Queue Workers

```bash
php artisan queue:work
```

Use **Supervisor** in production to keep queue workers running.

### Scheduler

Add to crontab:
```bash
* * * * * cd /path-to-cartxis && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🛠️ Developer Notes

### Code Style

- **PHP**: PSR-12 standard — `./vendor/bin/phpcs`
- **TypeScript/JavaScript**: ESLint — `npm run lint`

### Clearing Caches

```bash
php artisan optimize:clear
```

### Creating an Extension

See [EXTENSION-DEVELOPMENT-GUIDE.md](specificationandtask/EXTENSION-DEVELOPMENT-GUIDE.md) for the full guide on building Cartxis extension packages.

---

## 🤝 Contributing

Contributions are welcome!

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push: `git push origin feature/amazing-feature`
5. Open a Pull Request

Please follow PSR-12 for PHP, write tests for new features, and update documentation as needed.

---

## 📞 Support

| Channel | Link |
|---------|------|
| Website | [https://www.cartxis.com](https://www.cartxis.com) |
| Email | [dev@wontonee.com](mailto:dev@wontonee.com) |
| Issues | [GitHub Issues](https://github.com/cartxis/cartxis/issues) |
| Discussions | [GitHub Discussions](https://github.com/cartxis/cartxis/discussions) |

For billing, licensing, or partnership enquiries, contact us at **dev@wontonee.com**.

---

## 🗺️ Roadmap

### ✅ Completed
- [x] Product management (variants, attributes, inventory)
- [x] Shopping cart with guest checkout
- [x] Order management — invoices, shipments, credit memos, transactions
- [x] Customer accounts and profiles
- [x] Wishlist
- [x] Reviews
- [x] Newsletter
- [x] Multi-payment gateway (Stripe, Razorpay, PayPal, PhonePe, PayUMoney)
- [x] CMS — pages and content blocks
- [x] Media management
- [x] Email templates (customizable transactional emails)
- [x] Reports — sales, customer, and inventory
- [x] Flexible theme system
- [x] Maintenance mode
- [x] Extensions system
- [x] Backup and cache management
- [x] Tax management (classes, rates, rules, zones)
- [x] Shipping methods and delivery settings
- [x] Shiprocket integration
- [x] AI settings and product AI
- [x] Demo data (Electronics, Fashion, Kirana, Retail)
- [x] Data migration (WooCommerce, Bagisto)
- [x] REST API for mobile app
- [x] Interactive CLI installer (`php artisan cartxis:install`)
- [x] Composer create-project support

### 🔜 Upcoming
- [ ] Mobile app (iOS & Android)
- [ ] Two-factor authentication (2FA)
- [ ] Multi-vendor marketplace
- [ ] Subscription products
- [ ] Advanced SEO tools
- [ ] Multi-language support (i18n)
- [ ] Advanced analytics dashboard
- [ ] Integration marketplace

---

## 📝 License

Cartxis is open-source software licensed under the [MIT License](LICENSE).

---

Made with ❤️ by the [Wontonee Team](https://www.cartxis.com)
