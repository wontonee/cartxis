# Cartxis Admin Quick Reference

A quick reference guide for common admin operations.

---

## 🔐 Admin Access

| Item | Value |
|------|-------|
| **URL** | `http://your-domain.com/admin/login` |
| **Default Email** | `admin@wontonee.com` |
| **Default Password** | `password` |

---

## 📍 Navigation Quick Links

### Catalog
| Page | URL Path |
|------|----------|
| Products | `/admin/catalog/products` |
| Categories | `/admin/catalog/categories` |
| Attributes | `/admin/catalog/attributes` |
| Brands | `/admin/catalog/brands` |
| Reviews | `/admin/catalog/reviews` |

### Sales
| Page | URL Path |
|------|----------|
| Orders | `/admin/sales/orders` |
| Invoices | `/admin/sales/invoices` |
| Shipments | `/admin/sales/shipments` |
| Credit Memos | `/admin/sales/credit-memos` |
| Transactions | `/admin/sales/transactions` |

### Customers
| Page | URL Path |
|------|----------|
| All Customers | `/admin/customers` |
| Customer Groups | `/admin/customers/groups` |

### Marketing
| Page | URL Path |
|------|----------|
| Coupons | `/admin/marketing/coupons` |
| Promotions | `/admin/marketing/promotions` |

### Content
| Page | URL Path |
|------|----------|
| Pages | `/admin/content/pages` |
| Storefront Menus | `/admin/content/storefront-menus` |
| Blocks | `/admin/content/blocks` |
| Media Library | `/admin/content/media` |

### Reports
| Page | URL Path |
|------|----------|
| Sales Reports | `/admin/reports/sales` |
| Product Reports | `/admin/reports/products` |
| Customer Reports | `/admin/reports/customers` |

### Settings
| Page | URL Path |
|------|----------|
| General | `/admin/settings/general` |
| Store Configuration | `/admin/settings/store` |
| Locales | `/admin/settings/locales` |
| Channels | `/admin/settings/channels` |
| Payment Methods | `/admin/settings/payment-methods` |
| Shipping Methods | `/admin/settings/shipping-methods` |
| Tax Rules | `/admin/settings/tax-rules` |
| Email Settings | `/admin/settings/email` |
| AI Settings | `/admin/settings/ai` |

### System
| Page | URL Path |
|------|----------|
| Cache Management | `/admin/system/cache` |
| Menu Configuration | `/admin/system/menus` |
| Extensions | `/admin/system/extensions` |
| Permissions | `/admin/system/permissions` |
| Maintenance | `/admin/system/maintenance` |
| Data Migration | `/admin/system/migration` |
| API Sync | `/admin/system/api-sync` |
| Backups | `/admin/system/backups` |

---

## ⚡ Common Tasks

### Add a New Product
1. Go to **Catalog → Products**
2. Click **"+ Add Product"**
3. Fill in General, Images, Attributes, Inventory, SEO tabs
4. Click **"Create Product"**

### Process an Order
1. Go to **Sales → Orders**
2. Click on the order to view details
3. Update status: Pending → Processing → Completed
4. Create shipment with tracking number
5. Generate invoice

### Create a Coupon
1. Go to **Marketing → Coupons**
2. Click **"+ Create Coupon"**
3. Set code, discount type, and value
4. Configure validity dates and usage limits
5. Save and activate

### Configure Payment Method
1. Go to **Settings → Payment Methods**
2. Click **"Configure"** on desired method
3. Enter API credentials
4. Toggle **"Enable"**
5. Save changes

---

## 💻 CLI Commands

```bash
# Start development server
php artisan serve

# Clear all caches
php artisan optimize:clear

# Run database migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Create storage link
php artisan storage:link

# Build frontend (dev)
npm run dev

# Build frontend (production)
npm run build

# Run queue worker
php artisan queue:work

# Put in maintenance mode
php artisan down

# Exit maintenance mode
php artisan up
```

---

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| 419 Error | Clear cache: `php artisan cache:clear` |
| Images not showing | Run: `php artisan storage:link` |
| Login not working | Re-seed admin: `php artisan db:seed --class=AdminUserSeeder` |
| Page not loading | Clear all: `php artisan optimize:clear` |
| Payment errors | Verify API keys and SSL certificate |

---

## 📧 Email Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourstore.com
```

---

## 💳 Payment Gateway Credentials

### Stripe
```env
STRIPE_KEY=pk_xxx
STRIPE_SECRET=sk_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

### Razorpay
```env
RAZORPAY_KEY=rzp_xxx
RAZORPAY_SECRET=xxx
```

---

## 📦 Package Structure

```
packages/Cartxis/
├── Admin/        # Admin panel
├── Cart/         # Shopping cart
├── CMS/          # Content management
├── Core/         # Core utilities
├── Customer/     # Customer management
├── Marketing/    # Marketing tools
├── Product/      # Product management
├── Reports/      # Reporting
├── Sales/        # Order management
├── Settings/     # Configuration
├── Shop/         # Frontend shop
├── Stripe/       # Stripe integration
├── Razorpay/     # Razorpay integration
├── PhonePe/      # PhonePe integration
└── System/       # System utilities
```

---

**Cartxis v1.0.4** | [Documentation](USER_GUIDE.md) | [GitHub](https://github.com/wontonee/cartxis)
