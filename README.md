# Cartxis - Open Source Laravel E-Commerce Platform

A modern, extensible e-commerce platform built with Laravel 12, Inertia.js, Vue 3.5, and TypeScript. Cartxis provides a complete solution for building online stores with a powerful admin panel, flexible theme system, and modular architecture.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5.2%2B-3178C6?logo=typescript)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.1-38B2AC?logo=tailwind-css)

## ✨ Features

### 🛍️ Shop Features
- **Product Management**: Complete product catalog with variants, attributes, and inventory tracking
- **Shopping Cart**: Real-time cart with session persistence and guest checkout support
- **Multi-Payment Gateway**: Support for Stripe, Razorpay, and extensible payment methods
- **Order Management**: Comprehensive order tracking and management
- **Customer Accounts**: User registration, authentication, and order history
- **Theme System**: Flexible theme architecture with easy customization

### 🎛️ Admin Features
- **Dashboard**: Analytics and insights at a glance
- **Product Management**: Create and manage products, categories, brands, and attributes
- **Order Processing**: Complete order lifecycle management
- **Customer Management**: Customer profiles, addresses, and notes
- **Settings**: Store configuration, payment methods, shipping rates, tax rules
- **AI Agents**: Create and manage custom AI agents for commerce workflows
- **Email Templates**: Customizable transactional email templates
- **CMS**: Content management for pages and blocks
- **Reports**: Sales, customer, and inventory reporting

### 🔧 Technical Features
- **Modular Architecture**: Package-based structure for easy extension
- **MySQL Database**: Optimized for MySQL 8.0+
- **Modern Frontend**: Vue 3 with TypeScript and Tailwind CSS
- **Inertia.js**: SPA-like experience without API complexity
- **Two-Factor Authentication**: Enhanced security with 2FA support
- **Email Verification**: Built-in email verification workflow
- **Fortify Integration**: Laravel Fortify for authentication

## 📋 Requirements

- **PHP**: 8.2 or higher
- **Composer**: 2.x
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher
- **Database**: MySQL 8.0+
- **Extensions**: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath

## 🚀 Quick Install via Composer

The fastest way to get Cartxis running:

```bash
composer create-project cartxis/cartxis my-store
cd my-store
php artisan cartxis:install
```

The interactive installer will guide you through:
- Database configuration (MySQL 8.0+)
- Admin account setup
- Running migrations and seeders
- Building frontend assets (optional)

---

## 🛠️ Installation (Development / Git Clone)

Use this method if you are contributing or developing on Cartxis.

### 1. Clone the Repository

```bash
git clone https://github.com/cartxis/cartxis.git
cd cartxis
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE cartxis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cartxis
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

This will create all necessary tables and seed initial data including:
- Admin user (credentials set during `cartxis:install`)
- Default settings and configurations
- Payment methods
- Email templates

### 7. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 8. Storage Link

Create a symbolic link for storage:
```bash
php artisan storage:link
```

### 9. Start the Development Server

```bash
php artisan serve
```

Your Cartxis installation should now be accessible at `http://localhost:8000`

## 🔐 Default Credentials

### Admin Panel
- URL: `http://localhost:8000/admin/login`
- Email: `admin@wontonee.com`
- Password: `password`

**⚠️ Important**: Change these credentials immediately after installation!

### Customer Account
You can register a new customer account from the frontend or use the seeded test account if available.

## ⚙️ Configuration

### Store Settings

Configure your store through the admin panel:

1. Navigate to **Settings > Store Configuration**
2. Set your store name, currency, timezone, and other preferences
3. Configure payment methods in **Settings > Payment Methods**
4. Set up shipping rates in **Settings > Shipping**
5. Configure tax rules in **Settings > Tax Rules**

### Email Configuration

Update your `.env` file with your email provider settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourstore.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Payment Gateway Setup

#### Stripe
```env
STRIPE_KEY=your_publishable_key
STRIPE_SECRET=your_secret_key
STRIPE_WEBHOOK_SECRET=your_webhook_secret
```

#### Razorpay
```env
RAZORPAY_KEY=your_key_id
RAZORPAY_SECRET=your_secret_key
```

Enable payment methods in **Settings > Payment Methods** in the admin panel.

## 🧪 Running Tests

Cartxis includes a comprehensive test suite using Pest PHP:

```bash
# Run all tests
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Unit

# Run with coverage
./vendor/bin/pest --coverage
```

## 🎨 Theme Development

Cartxis uses a flexible theme system. The default theme is located in `themes/cartxis-default/`.

### Creating a Custom Theme

1. Copy the default theme:
```bash
cp -r themes/cartxis-default themes/your-theme-name
```

2. Update theme configuration in `config/theme.php`:
```php
'active' => 'your-theme-name',
```

3. Customize the theme files:
- **Pages**: `themes/your-theme-name/resources/views/pages/`
- **Components**: `themes/your-theme-name/resources/views/components/`
- **Layouts**: `themes/your-theme-name/resources/views/layouts/`

## 📦 Package Structure

Cartxis is organized into modular packages:

```
packages/Cartxis/
├── Admin/          # Admin panel functionality
├── Cart/           # Shopping cart management
├── CMS/            # Content management
├── Core/           # Core functionality and utilities
├── Customer/       # Customer management
├── Marketing/      # Marketing tools
├── Product/        # Product management
├── Reports/        # Reporting and analytics
├── Sales/          # Order and sales management
├── Settings/       # System settings
├── Shop/           # Frontend shop functionality
├── Stripe/         # Stripe payment integration
├── Razorpay/       # Razorpay payment integration
└── System/         # System utilities
```

## 🛠️ Development

### Code Style

Cartxis follows PSR-12 coding standards for PHP and ESLint rules for JavaScript/TypeScript.

Run linters:
```bash
# PHP
./vendor/bin/phpcs

# JavaScript/TypeScript
npm run lint
```

### Building Assets in Watch Mode

```bash
npm run dev
```

### Clearing Caches

```bash
php artisan optimize:clear
```

## 🚢 Deployment

### Production Checklist

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `npm run build`
4. Run `php artisan config:cache`
5. Run `php artisan route:cache`
6. Run `php artisan view:cache`
7. Set up proper file permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```
8. Configure your web server (Nginx/Apache)
9. Set up SSL certificate
10. Configure job queue and scheduler

### Queue Workers

Cartxis uses queues for background jobs. Set up a queue worker:

```bash
php artisan queue:work
```

For production, use Supervisor to manage queue workers.

### Scheduler

Add this cron entry to run scheduled tasks:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 for PHP code
- Use meaningful variable and method names
- Write tests for new features
- Update documentation as needed

## 📝 License

Cartxis is open-source software licensed under the [MIT license](LICENSE).

## 🙏 Credits

Built with:
- [Laravel](https://laravel.com)
- [Inertia.js](https://inertiajs.com)
- [Vue.js](https://vuejs.org)
- [Tailwind CSS](https://tailwindcss.com)
- [shadcn-vue](https://www.shadcn-vue.com)

## 📞 Support

- **Documentation**: Coming soon
- **Issues**: [GitHub Issues](https://github.com/wontonee/cartxis/issues)
- **Discussions**: [GitHub Discussions](https://github.com/wontonee/cartxis/discussions)

## 🗺️ Roadmap

- [ ] Multi-vendor marketplace support
- [ ] Advanced inventory management
- [ ] Subscription products
- [ ] Advanced SEO tools
- [ ] Mobile app
- [ ] Multi-language support (i18n)
- [ ] Advanced analytics dashboard
- [ ] Integration marketplace

---

Made with ❤️ by the Wontonee Team
