# Cartxis - User Guide

**Version 1.0.4** | **Last Updated: February 2026**

A comprehensive guide for managing your Cartxis e-commerce store through the admin panel.

---

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Getting Started](#getting-started)
4. [Dashboard](#dashboard)
5. [Catalog Management](#catalog-management)
6. [Sales Management](#sales-management)
7. [Customer Management](#customer-management)
8. [Marketing](#marketing)
9. [Content Management](#content-management)
10. [Reports](#reports)
11. [Settings](#settings)
12. [System Administration](#system-administration)
13. [Troubleshooting](#troubleshooting)

---

## Introduction

Cartxis is a modern, AI-powered e-commerce platform built with Laravel, Inertia.js, and Vue 3. It provides a complete solution for building and managing online stores with a powerful admin panel, flexible theme system, and modular architecture.

### Key Features

#### 🛍️ Shop Features
- **Product Management**: Complete product catalog with variants, attributes, and inventory tracking
- **Shopping Cart**: Real-time cart with session persistence and guest checkout support
- **Multi-Payment Gateway**: Support for Stripe, Razorpay, PayPal, PhonePe, and more
- **Order Management**: Comprehensive order tracking and lifecycle management
- **Customer Accounts**: User registration, authentication, and order history
- **Theme System**: Flexible theme architecture with easy customization

#### 🎛️ Admin Features
- **Dashboard**: Analytics and insights at a glance
- **Product Management**: Create and manage products, categories, brands, and attributes
- **Order Processing**: Complete order lifecycle management
- **Customer Management**: Customer profiles, addresses, and notes
- **Settings**: Store configuration, payment methods, shipping rates, tax rules
- **Email Templates**: Customizable transactional email templates
- **CMS**: Content management for pages and blocks
- **Reports**: Sales, customer, and product reporting

---

## Installation

### System Requirements

Before installing Cartxis, ensure your system meets these requirements:

| Requirement | Version |
|-------------|---------|
| PHP | 8.2 or higher |
| Composer | 2.x |
| Node.js | 18.x or higher |
| NPM | 9.x or higher |
| MySQL | 8.0+ |
| PHP Extensions | OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath |

### Installation Steps

#### Step 1: Clone the Repository

```bash
git clone https://github.com/wontonee/cartxis.git
cd cartxis
```

#### Step 2: Install PHP Dependencies

```bash
composer install
```

#### Step 3: Install JavaScript Dependencies

```bash
npm install
```

#### Step 4: Environment Configuration

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

#### Step 5: Database Setup

1. Create a MySQL database:

```sql
CREATE DATABASE cartxis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update your `.env` file with database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cartxis
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Step 6: Run Migrations and Seeders

```bash
php artisan migrate --seed
```

This creates all necessary tables and seeds initial data including:
- Default admin user
- Sample products and categories
- Default settings and configurations
- Payment methods
- Email templates

#### Step 7: Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

#### Step 8: Create Storage Link

```bash
php artisan storage:link
```

#### Step 9: Start the Development Server

```bash
php artisan serve
```

Your Cartxis installation is now accessible at `http://localhost:8000`

---

## Getting Started

### Accessing the Admin Panel

1. Navigate to: `http://your-domain.com/admin/login`
2. Enter the default credentials:
   - **Email**: `admin@wontonee.com`
   - **Password**: `password`

> ⚠️ **Security Notice**: Change these default credentials immediately after your first login!

### Admin Panel Layout

The admin panel consists of three main areas:

1. **Sidebar Navigation** (Left): Contains links to all admin modules
2. **Top Header**: Includes search, notifications, theme toggle, and user profile
3. **Main Content Area**: Displays the active page content

---

## Dashboard

The Dashboard provides an at-a-glance overview of your store's performance.

### Dashboard Components

#### Welcome Banner
Displays a personalized greeting with the current date.

#### Statistics Cards
Quick metrics showing:
- **Completed Revenue**: Total revenue from completed orders
- **Paid Revenue**: Total revenue from paid orders
- **Orders**: Total number of orders
- **Customers**: Total registered customers
- **Products**: Total products in catalog

#### Sales Overview
A visual chart showing revenue performance over the last 7 days with daily sales breakdown.

---

## Catalog Management

The Catalog module is where you manage all your store products and related entities.

### Products

**Navigation**: Catalog → Products

#### Product Listing Page

The products page displays all your products in a data table with:

| Column | Description |
|--------|-------------|
| Image | Product thumbnail |
| Product Details | Name, category, and tags (Featured, New) |
| SKU & Type | Unique identifier and product type |
| Price | Product price with currency |
| Stock | Inventory count and status |
| Status | Enable/Disable toggle |
| Actions | View, Edit, Delete buttons |

#### Filters & Search
- **Search**: Find products by name or SKU
- **Status Filter**: Filter by Enabled/Disabled
- **Category Filter**: Filter by product category
- **Stock Status**: Filter by In Stock/Out of Stock

#### Creating a New Product

1. Click the **"+ Add Product"** button
2. Fill in the product details across tabs:

##### General Tab
- **Name**: Product title
- **SKU**: Stock Keeping Unit (unique identifier)
- **URL Slug**: URL-friendly name (auto-generated)
- **Short Description**: Brief product summary
- **Description**: Full product details (rich text editor)
- **Price**: Regular selling price
- **Special Price**: Discounted price (optional)
- **Cost**: Product cost for margin tracking
- **Tax Class**: Applicable tax category
- **Status**: Enable or disable the product
- **Featured**: Mark as featured product
- **New**: Mark as new arrival

##### Images Tab
Upload product images using drag-and-drop or file browser. The first image becomes the main product image.

##### Attributes Tab
Assign product attributes:
1. Select attributes to add (Size, Color, Material, etc.)
2. Choose values for each attribute
3. Supports single-select and multi-select attributes

##### Inventory Tab
- **Stock Quantity**: Available units
- **Stock Status**: In Stock/Out of Stock
- **Manage Stock**: Enable automatic stock tracking
- **Low Stock Threshold**: Alert when stock reaches this level

##### SEO Tab
- **Meta Title**: Search engine title
- **Meta Description**: Search engine description
- **Meta Keywords**: SEO keywords

3. Click **"Create Product"** to save

### Categories

**Navigation**: Catalog → Categories

Organize your products into categories:

- Create hierarchical category structures (parent/child)
- Assign images to categories
- Set category descriptions
- Configure SEO settings per category

#### Creating a Category

1. Click **"+ Add Category"**
2. Enter category details:
   - Name
   - Parent Category (optional)
   - Description
   - Image
   - URL Slug
   - SEO fields
3. Save the category

### Attributes

**Navigation**: Catalog → Attributes

Define product characteristics that customers can filter by:

- **Text attributes**: Brand, Material
- **Select attributes**: Size, Color
- **Multi-select attributes**: Features, Compatibility

#### Creating an Attribute

1. Click **"+ Add Attribute"**
2. Fill in:
   - Attribute Name
   - Attribute Code (unique identifier)
   - Input Type (Text, Select, Multiselect)
   - Is Required
   - Is Filterable (appears in shop filters)
3. Add attribute options (for select types)
4. Save attribute

### Brands

**Navigation**: Catalog → Brands

Manage product brands/manufacturers:

- Add brand logo
- Set brand description
- Configure URL slug for brand pages

### Reviews

**Navigation**: Catalog → Reviews

Moderate customer product reviews:

- View pending reviews
- Approve or reject reviews
- Reply to customer feedback
- Filter reviews by status, rating, or product

---

## Sales Management

The Sales module handles all order-related operations.

### Orders

**Navigation**: Sales → Orders

#### Order Listing
View all orders with:
- Order Number
- Customer Name
- Order Date
- Status (Pending, Processing, Completed, Cancelled)
- Payment Status
- Total Amount

#### Order Details Page

Clicking on an order shows:

##### Overview Tab
- Customer information
- Order date and payment method
- Order notes

##### Items Tab
- Product details (image, name, SKU)
- Quantity and price
- Item total
- Order subtotal, tax, shipping, and grand total

##### Addresses Tab
- Shipping address
- Billing address

##### Shipments Tab
- Shipment tracking information
- Carrier details

##### History Tab
- Order status changes timeline
- Comments and notes

#### Order Actions

- **Update Status**: Change order status (Pending → Processing → Completed)
- **Update Payment Status**: Mark as Paid/Unpaid
- **Cancel Order**: Cancel with stock restoration option
- **Create Shipment**: Add shipping information
- **Create Invoice**: Generate order invoice
- **Create Credit Memo**: Process refunds

### Invoices

**Navigation**: Sales → Invoices

Manage and view all generated invoices:
- Invoice number and date
- Associated order
- Invoice total
- Print/Download options

### Shipments

**Navigation**: Sales → Shipments

Track order shipments:
- Add tracking numbers
- Select shipping carriers
- Update shipment status
- Send shipping notifications to customers

### Credit Memos

**Navigation**: Sales → Credit Memos

Handle refunds and returns:
- Create credit memos for partial/full refunds
- Restore inventory automatically
- Track refund reasons

### Transactions

**Navigation**: Sales → Transactions

View payment transaction history:
- Transaction ID
- Payment method
- Amount
- Status
- Associated order

---

## Customer Management

### All Customers

**Navigation**: Customers → All Customers

View and manage customer accounts:

#### Customer Listing
- Customer name and email
- Registration date
- Total orders
- Total spent
- Account status

#### Customer Actions
- **View/Edit**: Access full customer profile
- **Delete**: Remove customer account

#### Customer Profile

View detailed customer information:
- Personal information (name, email, phone)
- Addresses (shipping and billing)
- Order history
- Customer notes
- Account status

### Customer Groups

**Navigation**: Customers → Customer Groups

Create customer segments for targeted pricing and promotions:

- **General**: Default customer group
- **Wholesale**: For B2B customers
- **VIP**: For loyalty program members

Each group can have:
- Special pricing rules
- Exclusive discounts
- Different tax rates

---

## Marketing

### Coupons

**Navigation**: Marketing → Coupons

Create and manage discount coupons:

#### Creating a Coupon

1. Click **"+ Create Coupon"**
2. Configure coupon settings:

| Field | Description |
|-------|-------------|
| Coupon Code | Unique code customers enter (e.g., SAVE20) |
| Description | Internal description |
| Discount Type | Percentage or Fixed Amount |
| Discount Value | Amount or percentage off |
| Minimum Order Amount | Required cart minimum |
| Maximum Discount | Cap on discount amount |
| Valid From/To | Coupon validity period |
| Usage Limit | Maximum total uses |
| Per Customer | Uses allowed per customer |
| Status | Active/Inactive |

3. Save the coupon

#### Coupon Conditions
Set conditions for coupon application:
- Specific products
- Specific categories
- Customer groups
- First-time customers only

### Promotions

**Navigation**: Marketing → Promotions

Create store-wide promotions:

- **Cart Price Rules**: Automatic discounts based on cart conditions
- **Catalog Price Rules**: Product-level discounts

---

## Content Management

### Pages

**Navigation**: Content → Pages

Create and manage static pages:

- About Us
- Contact
- Privacy Policy
- Terms & Conditions
- Custom landing pages

#### Creating a Page

1. Click **"+ Add Page"**
2. Enter page details:
   - Title
   - URL Slug
   - Content (rich text editor)
   - SEO settings
   - Status (Published/Draft)
3. Save the page

### Storefront Menus

**Navigation**: Content → Storefront Menus

Configure navigation menus:

- Header menu
- Footer menu
- Mobile menu

Add menu items linking to:
- Categories
- Pages
- Products
- Custom URLs

### Blocks

**Navigation**: Content → Blocks

Create reusable content blocks for:
- Homepage sections
- Promotional banners
- Footer widgets
- Sidebar content

### Media Library

**Navigation**: Content → Media Library

Centralized media management:
- Upload images and files
- Organize in folders
- Search and filter media
- Get shareable URLs

---

## Reports

### Sales Reports

**Navigation**: Reports → Sales Reports

Analyze sales performance:

#### Available Metrics
- Total Revenue
- Number of Orders
- Average Order Value
- Revenue by Payment Method
- Revenue by Day/Week/Month

#### Date Range Filters
- Today
- Last 7 Days
- Last 30 Days
- This Month
- Custom Range

#### Export Options
- Download as CSV
- Download as PDF

### Product Reports

**Navigation**: Reports → Product Reports

Track product performance:
- Best-selling products
- Low stock alerts
- Products by revenue
- Products by quantity sold

### Customer Reports

**Navigation**: Reports → Customer Reports

Understand your customers:
- New vs. returning customers
- Customer acquisition trends
- Customers by orders
- Customers by revenue

---

## Settings

### General Settings

**Navigation**: Settings → General Settings

Configure basic store settings:
- Store Name
- Store Email
- Store Phone
- Store Address
- Default Currency
- Timezone
- Weight Unit
- Dimension Unit

### Store Configuration

**Navigation**: Settings → Store Configuration

Configure store-specific options:
- Logo and Favicon
- Store Description
- Operating Hours
- Social Media Links

### Locales

**Navigation**: Settings → Locales

Manage store languages and regional settings:
- Add/Edit locales
- Set default locale
- Configure date/number formats

### Channels

**Navigation**: Settings → Channels

For multi-channel selling:
- Default channel configuration
- Channel-specific settings

### Payment Methods

**Navigation**: Settings → Payment Methods

Configure how customers pay:

#### Available Payment Methods

| Method | Description |
|--------|-------------|
| **Stripe** | Credit/Debit card payments |
| **Razorpay** | Popular in India |
| **PayPal** | Global payment platform |
| **PhonePe** | UPI payments (India) |
| **PayUMoney** | Indian payment gateway |
| **Bank Transfer** | Manual bank payments |
| **Cash on Delivery** | Pay when delivered |

#### Configuring a Payment Method

1. Click on the payment method card
2. Click **"Configure"**
3. Enter API credentials:
   - API Key / Publishable Key
   - Secret Key
   - Webhook Secret (if applicable)
4. Set as Default (optional)
5. Toggle Enable/Disable
6. Save settings

### Shipping Methods

**Navigation**: Settings → Shipping Methods

Configure shipping options:

#### Free Shipping
- Set minimum order amount for free shipping
- Apply to specific regions

#### Flat Rate Shipping
- Fixed shipping cost
- Per item or per order

#### Table Rates
- Rate by weight
- Rate by price
- Rate by destination

### Tax Rules

**Navigation**: Settings → Tax Rules

Configure tax calculations:

1. Create Tax Classes (Standard, Reduced, Zero)
2. Create Tax Zones (countries/states)
3. Set Tax Rates per zone
4. Assign Tax Classes to products

### Email Settings

**Navigation**: Settings → Email Settings

Configure email delivery:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourstore.com
MAIL_FROM_NAME="Your Store Name"
```

### AI Settings

**Navigation**: Settings → AI Settings

Configure AI-powered features:
- AI content generation
- Product recommendations
- Smart search

---

## System Administration

### Cache Management

**Navigation**: System → Cache Management

Clear various caches:
- **Application Cache**: General app cache
- **Configuration Cache**: Config values
- **Route Cache**: Route definitions
- **View Cache**: Compiled templates
- **Clear All**: All caches at once

### Menu Configuration

**Navigation**: System → Menu Configuration

Customize admin navigation:
- Reorder menu items
- Enable/Disable menu items
- Add custom menu entries

### Extensions

**Navigation**: System → Extensions

Manage installed extensions:
- View installed extensions
- Enable/Disable extensions
- Configure extension settings

### Permissions

**Navigation**: System → Permissions

Manage admin access control:

#### Roles
Create roles with specific permissions:
- Administrator (full access)
- Sales Manager
- Content Editor
- Customer Support

#### Users
Assign roles to admin users:
1. Create admin user
2. Assign role
3. Set department/area (optional)

### Maintenance

**Navigation**: System → Maintenance

Put store in maintenance mode:
1. Enable Maintenance Mode
2. Set allowed IP addresses
3. Customize maintenance message

### Data Migration

**Navigation**: System → Data Migration

Import/Export data:
- Import products from CSV
- Export orders
- Migrate from other platforms

### API Sync

**Navigation**: System → API Sync

Configure external API integrations:
- Inventory sync
- Order sync
- Product feeds

### Backups

**Navigation**: System → Backups

Manage database backups:
- Create manual backup
- Schedule automatic backups
- Download backup files
- Restore from backup

---

## Troubleshooting

### Common Issues

#### 419 Page Expired Error

This usually indicates a session/CSRF issue.

**Solution**:
1. Clear browser cache and cookies
2. Clear application cache: `php artisan cache:clear`
3. Verify session configuration in `.env`

#### Images Not Loading

**Solution**:
1. Run `php artisan storage:link`
2. Check file permissions on `storage/` directory
3. Verify `APP_URL` in `.env` is correct

#### Payment Gateway Not Working

**Solution**:
1. Verify API credentials are correct
2. Check if test/live mode matches your keys
3. Ensure SSL is configured for production

#### Login Credentials Not Working

**Solution**:
1. Reset password via "Forgot Password"
2. Re-seed admin user: `php artisan db:seed --class=AdminUserSeeder`
3. Check database connection

### Getting Help

- **GitHub Issues**: [Report bugs](https://github.com/wontonee/cartxis/issues)
- **GitHub Discussions**: [Ask questions](https://github.com/wontonee/cartxis/discussions)

---

## Quick Reference

### Default Admin Credentials
| Field | Value |
|-------|-------|
| URL | `/admin/login` |
| Email | `admin@wontonee.com` |
| Password | `password` |

### Common Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Create storage link
php artisan storage:link

# Start queue worker
php artisan queue:work
```

### Environment Variables

```env
# App
APP_NAME="Your Store"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourstore.com

# Database
DB_CONNECTION=mysql
DB_DATABASE=cartxis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com

# Payment (Stripe)
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx

# Payment (Razorpay)
RAZORPAY_KEY=rzp_live_xxx
RAZORPAY_SECRET=xxx
```

---

**© 2026 Wontonee Team** | Built with ❤️ using Laravel, Vue.js, and Tailwind CSS
