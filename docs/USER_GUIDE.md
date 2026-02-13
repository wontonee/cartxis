# Cartxis E-Commerce Platform — User Guide

**Version:** 1.0.4
**Last Updated:** February 2026
**Platform:** Cartxis E-Commerce Platform
**Stack:** Laravel 12 · Inertia.js · Vue 3 · TypeScript · Tailwind CSS

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Installation & Setup](#2-installation--setup)
3. [Getting Started](#3-getting-started)
4. [Dashboard](#4-dashboard)
5. [Catalog Management](#5-catalog-management)
   - [Products](#51-products)
   - [Product Types](#52-product-types)
   - [Categories](#53-categories)
   - [Attributes](#54-attributes)
   - [Brands](#55-brands)
   - [Reviews](#56-reviews)
6. [Sales Management](#6-sales-management)
   - [Orders](#61-orders)
   - [Invoices](#62-invoices)
   - [Shipments](#63-shipments)
   - [Credit Memos](#64-credit-memos)
   - [Transactions](#65-transactions)
7. [Customer Management](#7-customer-management)
   - [All Customers](#71-all-customers)
   - [Customer Groups](#72-customer-groups)
8. [Marketing](#8-marketing)
   - [Coupons](#81-coupons)
   - [Promotions](#82-promotions)
9. [Content Management](#9-content-management)
   - [Pages](#91-pages)
   - [Storefront Menus](#92-storefront-menus)
   - [Blocks](#93-blocks)
   - [Media Library](#94-media-library)
10. [Reports](#10-reports)
    - [Sales Reports](#101-sales-reports)
    - [Product Reports](#102-product-reports)
    - [Customer Reports](#103-customer-reports)
11. [Settings](#11-settings)
    - [General Settings](#111-general-settings)
    - [Store Configuration](#112-store-configuration)
    - [Locales & Currencies](#113-locales--currencies)
    - [Channels](#114-channels)
    - [Payment Methods](#115-payment-methods)
    - [Shipping Methods](#116-shipping-methods)
    - [Tax Rules](#117-tax-rules)
    - [Email Settings](#118-email-settings)
    - [AI Settings](#119-ai-settings)
12. [System Administration](#12-system-administration)
    - [Cache Management](#121-cache-management)
    - [Menu Configuration](#122-menu-configuration)
    - [Extensions](#123-extensions)
    - [Permissions](#124-permissions)
    - [Maintenance Mode](#125-maintenance-mode)
    - [Data Migration](#126-data-migration)
    - [API Sync](#127-api-sync)
    - [Backups](#128-backups)
13. [Troubleshooting](#13-troubleshooting)
14. [Quick Reference](#14-quick-reference)

---

## 1. Introduction

Cartxis is a modern, full-featured e-commerce platform built on **Laravel 12** with **Inertia.js**, **Vue 3**, **TypeScript**, and **Tailwind CSS**. It provides a comprehensive admin panel for managing every aspect of an online store — from product catalogs and order processing to marketing campaigns, content management, reporting, and system-level configuration.

### Key Capabilities

- **Catalog Management** — Products (4 types), categories, attributes, brands, and customer reviews
- **Sales Processing** — Orders, invoices, shipments, credit memos, and transaction tracking
- **Customer Management** — Customer profiles, groups with automatic assignment, and segmentation
- **Marketing Tools** — Coupons (5 discount types) and promotions (5 promotion types) with advanced scheduling and restrictions
- **Content Management** — CMS pages, content blocks (5 block types), and media library
- **Reporting** — Sales analytics, product performance, and customer insights with charts and exportable data
- **Flexible Settings** — General config, store details, locales/currencies, channels, payment gateways, shipping methods, tax rules, email templates, and AI integration
- **System Administration** — Cache management, extensions, permissions, maintenance mode, API sync, and backups
- **Extension Architecture** — Modular payment gateway extensions (PayPal, Stripe, Razorpay, PhonePe, PayUMoney) with a sync mechanism

---

## 2. Installation & Setup

### Prerequisites

| Requirement | Minimum Version |
|---|---|
| PHP | 8.2+ |
| Node.js | 18+ |
| Composer | 2.x |
| MySQL / MariaDB | 8.0+ / 10.6+ |
| Redis (optional) | 6+ |

### Installation Steps

```bash
# 1. Clone the repository
git clone <repository-url> cartxis
cd cartxis

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Configure your database in .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=cartxis
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Run migrations and seed data
php artisan migrate --seed

# 7. Create storage symlink
php artisan storage:link

# 8. Build frontend assets
npm run build        # Production build
npm run dev          # Development with HMR

# 9. Start the development server
php artisan serve
```

### Post-Installation

- Access the admin panel at `http://your-domain/admin`
- Log in with the default admin credentials created during seeding
- Navigate to **Settings → General Settings** to configure your site name, branding, and contact details
- Navigate to **Settings → Store Configuration** to set up business information and store policies
- Configure at least one **Payment Method** and one **Shipping Method** before accepting orders

---

## 3. Getting Started

### Accessing the Admin Panel

Navigate to `/admin` on your domain. After logging in, you will land on the **Dashboard** — the central hub for store performance at a glance.

### Admin Panel Navigation

The sidebar provides access to all modules:

| Module | Sections |
|---|---|
| **Dashboard** | Overview statistics and charts |
| **Catalog** | Products, Categories, Attributes, Brands, Reviews |
| **Sales** | Orders, Invoices, Shipments, Credit Memos, Transactions |
| **Customers** | All Customers, Customer Groups |
| **Marketing** | Coupons, Promotions |
| **Content** | Pages, Storefront Menus, Blocks, Media Library |
| **Reports** | Sales Reports, Product Reports, Customer Reports |
| **Settings** | General, Store Config, Locales & Currencies, Channels, Payment Methods, Shipping Methods, Tax Rules, Email Settings, AI Settings |
| **System** | Cache Management, Menu Configuration, Extensions, Permissions, Maintenance Mode, Data Migration, API Sync, Backups |

### Common UI Patterns

- **Tables** — Most listing pages display data in sortable tables with pagination. Use the search bar and filter dropdowns above the table to narrow results.
- **Create/Edit Forms** — Forms are organized into collapsible sections (not tabs, unless stated otherwise). Required fields are marked with an asterisk (*).
- **Actions Column** — Each table row has an Actions column with options such as Edit, Delete, or View.
- **Toggle Switches** — Boolean settings (Active/Inactive, Enabled/Disabled) use toggle switches.
- **Rich Text Editor** — Description fields use an integrated rich-text editor supporting formatting, links, images, and HTML.

---

## 4. Dashboard

The Dashboard provides an at-a-glance summary of store performance and quick access to common tasks.

### Statistics Cards

Five stat cards are displayed at the top of the dashboard:

| Card | Description |
|---|---|
| **Completed Revenue** | Total revenue from completed orders |
| **Paid Revenue** | Total revenue from paid orders |
| **Orders** | Total number of orders |
| **Customers** | Total registered customers |
| **Products** | Total products in the catalog |

### Sales Overview Chart

A **7-day revenue chart** visualizes recent sales trends, helping you identify daily patterns and spikes.

### Quick Actions

Directly from the dashboard, you can:

- **View All** — Jump to the full orders list
- **Add Product** — Open the product creation form
- **View Orders** — Navigate to the orders management page

---

## 5. Catalog Management

The Catalog module is the heart of your store. It manages everything customers browse and buy.

### 5.1 Products

#### Product Listing

The products table displays the following columns:

| Column | Description |
|---|---|
| **Image** | Product thumbnail |
| **Product Details** | Product name and summary info |
| **SKU & Type** | Stock Keeping Unit code and product type |
| **Price** | Current selling price |
| **Stock** | Current inventory quantity |
| **Status** | Enabled or Disabled |
| **Actions** | Edit, Delete, View |

#### Filtering Products

Use the filter dropdowns above the table to narrow the product list:

- **Status** — `Enabled`, `Disabled`
- **Category** — Select from your store categories (e.g., Bakery & Snacks, Beverages, Dairy Products, Fruits & Vegetables, Household Essentials, Personal Care)
- **Stock Status** — `In Stock`, `Out of Stock`, `On Backorder`

Use the **search bar** to find products by name or SKU.

#### Creating / Editing a Product

The product creation form is organized into **sections** (not tabs). Scroll through all sections or use the page outline to jump to a specific one.

**Section 1 — General Information**

| Field | Required | Description |
|---|---|---|
| Product Name | ✅ | The display name of the product |
| Slug | ✅ | URL-friendly identifier (auto-generated from name, editable) |
| SKU | ✅ | Unique Stock Keeping Unit code |
| Product Type | ✅ | Select: Simple, Configurable, Virtual, or Downloadable (see [Product Types](#52-product-types)) |
| Brand | | Select from configured brands |
| Short Description | | Brief description shown in listings (rich text editor) |
| Full Description | | Detailed product description (rich text editor) |

**Section 2 — Product Images**

Upload product images via **drag-and-drop** or click to browse. Multiple images are supported. The first image serves as the primary product thumbnail.

**Section 3 — Product Attributes**

Use the **Add Attribute** dropdown to attach predefined attributes to the product. Attributes define product characteristics such as color, size, material, etc. Attributes must be created first in the [Attributes](#54-attributes) section.

**Section 4 — Pricing**

| Field | Required | Description |
|---|---|---|
| Regular Price | ✅ | The standard selling price |
| Cost | | Product cost/wholesale price (for margin calculations) |
| Tax Class | | Select: `No Tax`, `Digital Goods`, `Reduced Rate`, `Standard Rate`, `Zero Rate` |

**Section 5 — Special Pricing**

| Field | Description |
|---|---|
| Special Price | A temporary discounted price |
| From Date | When the special price begins |
| To Date | When the special price ends |

Special pricing is ideal for time-limited sales without creating a coupon or promotion.

**Section 6 — Inventory**

| Field | Required | Description |
|---|---|---|
| Quantity | ✅ | Number of units in stock |
| Weight (kg) | | Product weight for shipping calculations |

**Section 7 — Search Engine Optimization**

| Field | Description |
|---|---|
| Meta Title | Title tag for search engines |
| Meta Description | Description for search engine result pages |
| Meta Keywords | Comma-separated keywords for SEO |

**Section 8 — Publish**

| Field | Description |
|---|---|
| Status | `Enabled` — visible; `Disabled` — hidden |
| Visibility | `Catalog & Search` — appears everywhere; `Catalog Only` — browsable but not searchable; `Search Only` — searchable but not in category listings; `Not Visible` — hidden from storefront (useful for variants) |
| Featured Product | Check to feature this product on the storefront |
| Mark as New | Check to display a "New" badge on the product |

**Section 9 — Categories**

Assign the product to one or more categories by checking the corresponding checkboxes.

---

### 5.2 Product Types

Cartxis supports four product types, each tailored to a different kind of merchandise:

#### Simple Product

A standard physical product with a fixed price and inventory. Examples: a bag of flour, a bottle of shampoo, a pack of batteries.

- Has its own SKU and stock quantity
- Requires shipping
- Most common product type

#### Configurable Product (Variants)

A product with user-selectable variation options such as size, color, or material. The parent configurable product groups multiple simple product variants.

- Customers choose options (e.g., size: S/M/L, color: Red/Blue) before adding to cart
- Each variant combination can have its own SKU, price, and stock level
- Requires attributes marked as **"Use for Product Variants"** in the [Attributes](#54-attributes) section
- The parent product itself is not purchasable — a specific variant must be selected

#### Virtual Product (No Shipping)

A non-physical product that does not require shipping. Examples: service appointments, consulting hours, memberships, gift cards.

- No weight or shipping calculations
- Fulfilled digitally or via service delivery
- Inventory tracking is optional

#### Downloadable Product (Digital)

A digital product that customers download after purchase. Examples: e-books, software licenses, music files, design templates.

- Downloadable files are attached to the product
- Customers receive download links after successful payment
- No shipping required
- Download limits and expiration can be configured

---

### 5.3 Categories

Categories organize your product catalog into a browsable hierarchy.

#### Category Listing

| Column | Description |
|---|---|
| **Image** | Category image thumbnail |
| **Name & Slug** | Display name and URL slug |
| **Parent** | Parent category (or root) |
| **Status** | Active or Inactive |
| **Order** | Sort order value |
| **Menu** | Whether shown in navigation menu |
| **Actions** | Edit, Delete |

#### Creating / Editing a Category

| Field | Required | Description |
|---|---|---|
| Category Name | ✅ | Display name |
| URL Slug | | URL-friendly identifier (auto-generated if left blank) |
| Description | | Category description (rich text editor) |
| Parent Category | | Select `None` for a root category, or choose an existing category as parent |
| Category Image | | Upload an image for the category |
| Sort Order | | Numeric value to control display order (lower = first) |
| Active Status | | Toggle on/off to show or hide the category |
| Show in Navigation Menu | | Toggle on/off to include in the storefront navigation |

**SEO Section:**

| Field | Description |
|---|---|
| Meta Title | Search engine title |
| Meta Description | Search engine description |
| Meta Keywords | Comma-separated SEO keywords |

---

### 5.4 Attributes

Attributes define the properties of products (e.g., Color, Size, Material). They power filtering on the storefront and variant configuration for configurable products.

#### Attribute Listing

| Column | Description |
|---|---|
| **Name** | Attribute display name |
| **Code** | Unique attribute code (e.g., `color`, `size`) |
| **Type** | Input type (Text, Select, Boolean, etc.) |
| **Options** | Number of options (for Select/Multi-select types) |
| **Required** | Whether the attribute is required on products |
| **Filterable** | Whether it appears in storefront filters |
| **Configurable** | Whether it can be used for product variants |
| **Actions** | Edit, Delete |

#### Creating / Editing an Attribute

| Field | Required | Description |
|---|---|---|
| Attribute Name | ✅ | Display name (e.g., "Color", "Size") |
| Attribute Code | ✅ | Unique system identifier (e.g., `color`, `size`) — lowercase, no spaces |
| Input Type | ✅ | Determines how the attribute value is entered (see below) |
| Sort Order | | Controls the display order among attributes |
| Required | | Toggle — when enabled, this attribute must be filled on every product that uses it |
| Use in Filters | | Toggle — when enabled, this attribute appears as a filter on the storefront |
| Use for Product Variants | | Toggle — when enabled, this attribute can define variants on configurable products |

**Available Input Types:**

| Type | Description |
|---|---|
| **Text** | Single-line text input |
| **Textarea** | Multi-line text input |
| **Select/Dropdown** | Single selection from predefined options |
| **Multi-select** | Multiple selections from predefined options |
| **Boolean (Yes/No)** | True/false toggle |
| **Date** | Date picker |
| **Price** | Numeric input formatted as currency |

> **Tip:** For configurable products, use **Select/Dropdown** attributes with "Use for Product Variants" enabled. For example, create a "Size" attribute with options S, M, L, XL, then assign it to a configurable product to generate variants.

---

### 5.5 Brands

Brands allow you to organize products by manufacturer or brand and provide dedicated brand pages.

#### Brand Listing

| Column | Description |
|---|---|
| **Name** | Brand name |
| **Website** | Brand website URL |
| **Status** | Active or Inactive |
| **Featured** | Whether featured on the storefront |
| **Products** | Number of assigned products |
| **Actions** | Edit, Delete |

#### Creating / Editing a Brand

| Field | Required | Description |
|---|---|---|
| Brand Name | ✅ | Display name of the brand |
| Slug | | URL-friendly identifier |
| Description | | Brand description |
| Website URL | | Official brand website link |
| Brand Logo | | Upload a logo image |
| Meta Title | | SEO title |
| Meta Description | | SEO description |
| Meta Keywords | | SEO keywords |
| Active | | Toggle to enable/disable the brand |
| Featured Brand | | Toggle to feature on the storefront |

---

### 5.6 Reviews

Manage customer-submitted product reviews. Reviews can be moderated before appearing on the storefront.

#### Review Listing

| Column | Description |
|---|---|
| **Product** | Product the review is for |
| **Reviewer** | Customer who wrote the review |
| **Rating** | Star rating (1–5) |
| **Review** | Review text (truncated in listing) |
| **Status** | Pending, Approved, or Rejected |
| **Date** | Submission date |
| **Actions** | Edit, Approve, Reject, Delete |

#### Filtering Reviews

- **Status** — `Pending`, `Approved`, `Rejected`
- **Rating** — `1 Star`, `2 Stars`, `3 Stars`, `4 Stars`, `5 Stars`
- **Product** — Select a specific product from the dropdown

> **Workflow:** New reviews arrive with `Pending` status. Review the content and either **Approve** (makes it visible on the storefront) or **Reject** (keeps it hidden).

---

## 6. Sales Management

The Sales module handles the complete order lifecycle — from initial placement through invoicing, shipment, and refunds.

### 6.1 Orders

#### Order Listing

| Column | Description |
|---|---|
| **Order #** | Unique order identifier |
| **Customer** | Customer name |
| **Status** | Current order status |
| **Payment** | Payment status |
| **Total** | Order total amount |
| **Date** | Order placement date |
| **Actions** | View, Edit, Process |

#### Filtering Orders

- **Status** — `Pending`, `Processing`, `Completed`, `Cancelled`, `Refunded`, `Failed`
- **Payment** — `Paid`

#### Order Statuses

| Status | Description |
|---|---|
| **Pending** | Order received, awaiting processing |
| **Processing** | Order is being prepared/fulfilled |
| **Completed** | Order fulfilled and delivered |
| **Cancelled** | Order cancelled before fulfillment |
| **Refunded** | Payment returned to customer |
| **Failed** | Order failed (payment declined, etc.) |

#### Order Detail View

Clicking an order opens its detail page, which includes:

- **Order summary** — Order number, date, status, and totals
- **Customer information** — Name, email, billing and shipping addresses
- **Items ordered** — Product details, quantities, prices
- **Payment information** — Method, transaction ID, payment status
- **Status history** — Timeline of status changes with timestamps
- **Actions** — Create invoice, create shipment, cancel order, add comments

---

### 6.2 Invoices

Invoices are generated from orders to document payment obligations.

| Column | Description |
|---|---|
| **Invoice #** | Unique invoice identifier |
| **Order #** | Associated order number |
| **Customer** | Customer name |
| **Issue Date** | Date the invoice was created |
| **Status** | Invoice status |
| **Total** | Invoice total |
| **Actions** | View, Print, Download PDF |

To create an invoice, open the associated order and click the invoice generation action.

---

### 6.3 Shipments

Shipments track the physical delivery of ordered products.

| Column | Description |
|---|---|
| **Shipment #** | Unique shipment identifier |
| **Order #** | Associated order number |
| **Customer** | Customer name |
| **Carrier** | Shipping carrier name |
| **Tracking #** | Carrier tracking number |
| **Date Created** | Shipment creation date |
| **Status** | Shipment status |
| **Actions** | View, Track, Update |

To create a shipment, open the associated order and use the shipment creation action. Enter the carrier name and tracking number for customer tracking.

---

### 6.4 Credit Memos

Credit memos document refunds issued against orders.

| Column | Description |
|---|---|
| **Credit Memo** | Unique credit memo identifier |
| **Order** | Associated order number |
| **Customer** | Customer name |
| **Status** | Credit memo status |
| **Method** | Refund method |
| **Amount** | Refund amount |
| **Date** | Credit memo date |
| **Actions** | View, Print |

Credit memos are created from the order detail page when processing a full or partial refund.

---

### 6.5 Transactions

Transactions provide a unified log of all financial activity.

| Column | Description |
|---|---|
| **Transaction #** | Unique transaction identifier |
| **Order #** | Associated order number |
| **Date** | Transaction date and time |
| **Type** | Transaction type (payment, refund, etc.) |
| **Gateway** | Payment gateway used |
| **Amount** | Transaction amount |
| **Status** | Transaction status |
| **Actions** | View details |

Use this section to audit payment activity, verify gateway responses, and reconcile financial records.

---

## 7. Customer Management

### 7.1 All Customers

#### Customer Listing

| Column | Description |
|---|---|
| **Customer** | Customer name |
| **Contact** | Email and/or phone |
| **Group** | Customer group (General, Wholesale, VIP, Retail) |
| **Orders** | Total orders placed |
| **Total Spent** | Lifetime spending |
| **Status** | Active or Inactive |
| **Created** | Registration date |
| **Actions** | View, Edit, Delete |

#### Filtering Customers

- **Group** — `General`, `Wholesale`, `VIP`, `Retail`
- **Status** — `Active`, `Inactive`
- **Type** — `Registered`, `Guest`
- **Verified** — `Verified`, `Not Verified`

#### Creating / Editing a Customer

| Field | Required | Description |
|---|---|---|
| First Name | ✅ | Customer's first name |
| Last Name | ✅ | Customer's last name |
| Email | ✅ | Login email address (must be unique) |
| Phone | | Phone number |
| Date of Birth | | Customer's birthdate |
| Gender | | `Male`, `Female`, `Other` |
| Customer Group | ✅ | Assign to: `General`, `Wholesale`, `VIP`, or `Retail` |
| Company Name | | Business name (for B2B customers) |
| Tax ID | | Tax identification number |
| Active | | Toggle to enable/disable the account |
| Verified | | Toggle to mark email as verified |
| Newsletter | | Toggle to subscribe to newsletter |
| Notes | | Internal notes about the customer |

---

### 7.2 Customer Groups

Customer groups allow you to segment customers for pricing, discounts, and automatic promotion targeting.

#### Group Listing

| Column | Description |
|---|---|
| **Order** | Sort order |
| **Group** | Group name |
| **Discount** | Group-wide discount percentage |
| **Customers** | Number of customers in the group |
| **Auto-Assignment** | Whether customers are auto-assigned based on criteria |
| **Status** | Active or Inactive |
| **Actions** | Edit, Delete |

#### Pre-Configured Groups

| Group | Discount | Auto-Assignment Rule |
|---|---|---|
| **General** | 0% | Default group for all new customers |
| **Wholesale** | 15% | Auto-assigned after 5 orders |
| **VIP** | 20% | Auto-assigned after 10 orders |
| **Retail** | 5% | — |

> **Tip:** Customer groups with auto-assignment rules will automatically upgrade customers when they meet the order threshold. This incentivizes repeat purchases.

---

## 8. Marketing

### 8.1 Coupons

Coupons provide discount codes that customers can apply at checkout.

#### Coupon Listing

| Column | Description |
|---|---|
| **Details** | Coupon code and display name |
| **Type** | Discount type |
| **Discount** | Discount value |
| **Usage** | Number of times used |
| **Valid Until** | Expiration date |
| **Status** | Active or Inactive |
| **Actions** | Edit, Delete, Toggle Status |

#### Creating / Editing a Coupon

The coupon form is organized into five sections:

**Section 1 — Basic Information**

| Field | Required | Description |
|---|---|---|
| Coupon Code | ✅ | The code customers enter at checkout (e.g., `SAVE20`) |
| Display Name | ✅ | Human-readable name (e.g., "20% Summer Sale") |
| Description | | Internal description |
| Discount Type | ✅ | See discount types below |
| Percentage / Value | ✅ | The discount amount (percentage or fixed) |
| Max Discount | | Maximum discount cap (useful for percentage discounts) |
| Start Date | ✅ | When the coupon becomes active |
| End Date | | When the coupon expires (leave blank for no expiry) |
| Active | | Toggle to enable/disable |
| Public | | Toggle — when enabled, the coupon is visible on the storefront |
| Auto Apply | | Toggle — when enabled, the coupon is applied automatically at checkout |

**Discount Types:**

| Type | Description |
|---|---|
| **Percentage** | Discounts by a percentage of the cart total (e.g., 20% off) |
| **Fixed Amount** | Subtracts a fixed dollar amount from the cart (e.g., $10 off) |
| **Free Shipping** | Waives all shipping charges |
| **Buy X Get Y** | Buy a specified quantity, get additional items free or discounted |
| **Fixed Price** | Sets the item price to a specific amount |

**Section 2 — Order Conditions**

| Field | Description |
|---|---|
| Minimum Order Amount | The minimum cart subtotal required to use the coupon |
| Priority | Determines processing order when multiple coupons could apply (lower = higher priority) |
| First Order Only | Toggle — restrict to customers placing their first order |
| Can Stack with Other Coupons | Toggle — allow combining with other coupon codes |

**Section 3 — Usage Limits**

| Field | Description |
|---|---|
| Total Usage Limit | Maximum total uses across all customers (e.g., first 100 uses) |
| Usage Limit Per Customer | Maximum uses per individual customer |

**Section 4 — Time Restrictions**

| Field | Description |
|---|---|
| Days of Week | Checkboxes for Monday through Sunday — restrict the coupon to specific days |
| Start Time | Time of day the coupon becomes active |
| End Time | Time of day the coupon deactivates |

> **Example:** A coupon valid only on weekends from 6 PM to midnight — check Saturday and Sunday, set Start Time to 18:00 and End Time to 00:00.

**Section 5 — Customer Restrictions**

| Field | Description |
|---|---|
| Minimum Account Age (Days) | Require the customer's account to be at least this many days old |

---

### 8.2 Promotions

Promotions are automatic discounts applied based on rules — no coupon code required. They can target catalog prices, cart totals, or specific purchase patterns.

#### Promotion Listing

| Column | Description |
|---|---|
| **Promotion** | Name and description |
| **Discount** | Discount type and value |
| **Badge** | Whether a badge is shown on products |
| **Usage** | Number of times applied |
| **Schedule** | Start and end dates |
| **Status** | Active or Inactive |
| **Actions** | Edit, Delete, Toggle Status |

#### Creating / Editing a Promotion

| Field | Required | Description |
|---|---|---|
| Promotion Name | ✅ | Display name of the promotion |
| Promotion Type | ✅ | See promotion types below |
| Discount Type | ✅ | `Percentage %` or `Fixed Amount $` |
| Discount Value | ✅ | Numeric discount value |
| Maximum Discount Amount | | Cap on the discount (for percentage types) |
| Description | | Customer-facing description |
| Priority | | Processing order (lower = higher priority) |
| Internal Notes | | Admin-only notes |
| Show Badge on Products | | Toggle — displays a promotional badge on qualifying products |
| Show Countdown Timer | | Toggle — displays a countdown to promotion end on the storefront |
| Total Usage Limit | | Maximum total applications |
| Per Customer Usage Limit | | Maximum applications per customer |
| Stackable with Other Promotions | | Toggle — allow combining with other active promotions |
| Stackable with Coupons | | Toggle — allow combining with coupon codes |
| Stop Further Rules Processing | | Toggle — when triggered, prevents lower-priority promotions from applying |
| Start Date & Time | | When the promotion activates |
| End Date & Time | | When the promotion deactivates |
| Active | | Toggle to enable/disable |

**Promotion Types:**

| Type | Description |
|---|---|
| **Catalog Price Rule** | Adjusts the displayed product price in catalog listings and product pages. Discount is applied before the product is added to cart. |
| **Cart Price Rule** | Applies a discount to the cart total during checkout. Works similarly to coupons but is automatic. |
| **Bundle Deal** | Offers a discount when specific products are purchased together (e.g., "Buy shampoo + conditioner, save 15%"). |
| **Flash Sale** | A time-limited promotion with optional countdown timer. Creates urgency for customers. |
| **Tiered Pricing** | Offers increasing discounts based on quantity purchased (e.g., 1–9 units: 0% off; 10–49 units: 10% off; 50+: 20% off). |

---

## 9. Content Management

### 9.1 Pages

Create and manage static CMS pages such as "About Us", "Contact", "FAQ", and custom landing pages.

#### Page Listing

| Column | Description |
|---|---|
| **Title** | Page title |
| **URL Key** | URL path (e.g., `about-us`) |
| **Status** | Draft, Published, or Disabled |
| **Created** | Creation date |
| **Actions** | Edit, Delete, Preview |

#### Creating / Editing a Page

| Field | Required | Description |
|---|---|---|
| Title | ✅ | Page heading and browser title |
| URL Key | ✅ | URL-friendly path (e.g., `about-us` → `/about-us`) |
| Content | ✅ | Page body content (rich text editor with full HTML support) |
| Meta Title | | SEO title tag |
| Meta Description | | SEO meta description |
| Meta Keywords | | SEO keywords |
| Status | ✅ | `Draft` — saved but not public; `Published` — live on storefront; `Disabled` — taken offline |

---

### 9.2 Storefront Menus

> ⚠️ **Note:** The Storefront Menus route currently returns a **404 error**. This feature appears in the sidebar navigation but is not yet implemented. Menu management functionality is planned for a future release.

---

### 9.3 Blocks

Content blocks are reusable content components that can be placed in various positions across the storefront.

#### Block Listing

| Column | Description |
|---|---|
| **Title** | Block title |
| **Identifier** | Unique system identifier |
| **Type** | Block type |
| **Status** | Active or Inactive |
| **Schedule** | Start and end dates (if scheduled) |
| **Actions** | Edit, Delete |

#### Creating / Editing a Block

| Field | Required | Description |
|---|---|---|
| Title | ✅ | Display name of the block |
| Identifier | ✅ | Unique system identifier (e.g., `homepage-banner`, `promo-sidebar`) |
| Block Type | ✅ | See block types below |
| Status | | `Active` or `Inactive` |
| Start Date | | When the block becomes visible (optional scheduling) |
| End Date | | When the block is hidden (optional scheduling) |

**Block Types:**

| Type | Use Case |
|---|---|
| **Text** | Plain or formatted text content |
| **HTML** | Custom HTML markup for advanced layouts |
| **Banner** | Promotional banner images with optional links |
| **Promotion** | Highlight active promotions or deals |
| **Newsletter** | Newsletter subscription form |

> **Tip:** Use the scheduling fields (Start Date / End Date) to automatically show and hide seasonal content without manual intervention.

---

### 9.4 Media Library

The Media Library provides a centralized file management system for all uploaded assets.

**Features:**

- **Upload Files** — Upload images, documents, and other media files via the Upload Files button
- **Folders** — Organize files into folders using the folder panel on the left
- **All Files View** — Browse all uploaded files across all folders
- **Search & Filter** — Locate files by name using the search bar; filter by type or folder

All product images, category images, brand logos, and block assets are stored in and accessible from the Media Library.

---

## 10. Reports

The Reports module provides business intelligence dashboards to track store performance.

### 10.1 Sales Reports

#### Summary Statistics

Four key metrics are displayed at the top:

| Metric | Description |
|---|---|
| **Total Revenue** | Sum of all order revenue in the selected period |
| **Total Orders** | Number of orders placed |
| **Avg Order Value** | Average revenue per order |
| **Total Growth %** | Revenue growth compared to the previous period |

#### Charts and Tables

| Report | Description |
|---|---|
| **Revenue Over Time** | Line/bar chart showing daily revenue trends |
| **Orders by Status** | Breakdown of orders by status (Completed, Processing, Pending, etc.) |
| **Payment Methods** | Distribution of payment methods used |
| **Top 10 Orders** | Table of the highest-value orders |

#### Filters

- **Start Date** — Beginning of the reporting period
- **End Date** — End of the reporting period
- **Order Status** — `All`, `Completed`, `Processing`, `Pending`, `Cancelled`

#### Export

Click **Export Report** to download the current report data (filtered) for offline analysis.

---

### 10.2 Product Reports

| Report | Description |
|---|---|
| **Top 10 Best Sellers** | Products ranked by units sold |
| **Revenue by Category** | Revenue breakdown across product categories |
| **Sales Trend** | Product sales trend over time |
| **Low Stock Products** | Products approaching or at zero inventory |
| **Slow Moving Products** | Products with low sales velocity — candidates for promotions or clearance |

---

### 10.3 Customer Reports

| Report | Description |
|---|---|
| **Customer Acquisition** | New customer registrations over time |
| **Customer Segments** | Breakdown of customers by group (General, Wholesale, VIP, Retail) |
| **Revenue by Country** | Geographic distribution of revenue |
| **LTV Distribution** | Customer Lifetime Value distribution histogram |
| **Top Customers** | Highest-spending customers |
| **Segment Analysis** | Detailed performance metrics per customer segment |

---

## 11. Settings

### 11.1 General Settings

Configure the core identity and SEO settings for your store.

#### Basic Information

| Field | Required | Description |
|---|---|---|
| Site Name | ✅ | Your store name displayed across the site |
| Site Tagline | | Short description or motto |

#### Contact Information

| Field | Required | Description |
|---|---|---|
| Admin Email | ✅ | Primary administrative email |
| Contact Phone | | Public-facing phone number |
| Contact Address | | Physical store or business address |

#### Branding

| Field | Description |
|---|---|
| Site Logo | Logo displayed on the storefront header |
| Admin Logo | Logo displayed in the admin panel |
| Site Favicon | Browser tab icon |

#### SEO

| Field | Description |
|---|---|
| Meta Title | Default title tag for pages without a specific meta title |
| Meta Description | Default meta description |
| Meta Keywords | Default meta keywords |

#### Analytics & Tracking

| Field | Description |
|---|---|
| Google Analytics ID | Your GA4 measurement ID (e.g., `G-XXXXXXXXXX`) |
| Google Tag Manager ID | GTM container ID (e.g., `GTM-XXXXXXX`) |
| Facebook Pixel ID | Meta Pixel tracking ID |

---

### 11.2 Store Configuration

Detailed business and operational settings for your store.

#### Business Information

| Field | Required | Description |
|---|---|---|
| Business Name | ✅ | Legal business name |
| Business Registration Number | | Company registration / incorporation number |
| VAT Number | | Value Added Tax identification number |
| License Number | | Business license number |
| Store Timezone | ✅ | Timezone for order timestamps, scheduling, and reports |
| Business Description | | Brief description of your business |

#### Contact Information

| Field | Required | Description |
|---|---|---|
| Primary Email | ✅ | Main business email |
| Support Email | | Customer support email |
| Phone Number | ✅ | Primary phone number |
| Alternate Phone | | Secondary phone number |
| WhatsApp Number | | WhatsApp contact number |

#### Store Address

| Field | Required | Description |
|---|---|---|
| Street Address | ✅ | Primary street address |
| Address Line 2 | | Suite, unit, floor, etc. |
| City | ✅ | City |
| State / Province | ✅ | State or province |
| Postal Code | ✅ | ZIP or postal code |
| Country | ✅ | Country |

#### Social Media Profiles

| Field | Description |
|---|---|
| Facebook | Facebook page URL |
| Instagram | Instagram profile URL |
| Twitter / X | Twitter/X profile URL |
| LinkedIn | LinkedIn company page URL |
| YouTube | YouTube channel URL |
| TikTok | TikTok profile URL |
| Pinterest | Pinterest profile URL |

#### Checkout Preferences

| Field | Description |
|---|---|
| Allow Guest Checkout | Toggle — allow orders without account creation |
| Require Account Creation | Toggle — force account creation at checkout |

#### Configuration Options

Additional store behavior configuration options.

#### Store Policies

| Field | Description |
|---|---|
| Privacy Policy | Your store's privacy policy text (rich text) |
| Terms & Conditions | Terms of service (rich text) |
| Return Policy | Product return and refund policy (rich text) |
| Shipping Policy | Shipping terms and delivery information (rich text) |

---

### 11.3 Locales & Currencies

Manage the languages and currencies your store supports.

#### Locales

| Column | Description |
|---|---|
| **Code** | Locale code (e.g., `en`, `fr`, `ar`) |
| **Name** | English name (e.g., "English", "French") |
| **Native Name** | Name in the native language (e.g., "Français") |
| **Direction** | Text direction — `LTR` (left-to-right) or `RTL` (right-to-left) |
| **Status** | Active or Inactive |
| **Default** | Whether this is the default locale |

#### Currencies

| Column | Description |
|---|---|
| **Code** | ISO 4217 code (e.g., `USD`, `EUR`, `INR`) |
| **Name** | Currency name (e.g., "US Dollar") |
| **Symbol** | Currency symbol (e.g., `$`, `€`, `₹`) |
| **Position** | Symbol position — before or after the amount |
| **Exchange Rate** | Conversion rate relative to the base currency |
| **Status** | Active or Inactive |
| **Default** | Whether this is the default currency |

---

### 11.4 Channels

Channels represent distinct storefronts or sales channels, each with its own configuration.

| Column | Description |
|---|---|
| **Channel Info** | Channel name and details |
| **Theme** | Assigned storefront theme |
| **Status** | Active or Inactive |
| **Default** | Whether this is the default channel |
| **Actions** | Edit, Configure |

Channels allow you to run multiple storefronts (e.g., different regions or brands) from a single Cartxis installation.

---

### 11.5 Payment Methods

Configure the payment gateways available to customers at checkout.

#### Available Payment Methods

| Method | Code | Description |
|---|---|---|
| **Cash on Delivery** | `cod` | Customer pays upon delivery |
| **Razorpay** | `cartxis-razorpay` | Indian payment gateway (UPI, cards, wallets, net banking) |
| **Bank Transfer** | — | Manual bank transfer with order confirmation |
| **Stripe** | `cartxis-stripe` | Global payment processing (cards, Apple Pay, Google Pay) |
| **PhonePe** | `cartxis-phonepe` | Indian UPI and digital wallet payments |
| **PayPal** | `cartxis-paypal` | Global PayPal payments and credit card processing |
| **PayUMoney** | `cartxis-payumoney` | Indian payment gateway |

#### Configuration

Each payment method has:

- **Description** — Customer-facing description shown at checkout
- **Active / Inactive** — Toggle to enable or disable the method
- **Configure** button — Opens gateway-specific settings (API keys, webhook URLs, sandbox/live mode, etc.)
- **Default Method** — Optionally set one method as the pre-selected default at checkout

> **Note:** Payment gateways other than Cash on Delivery and Bank Transfer are provided as extensions. See [Extensions](#123-extensions) for details.

---

### 11.6 Shipping Methods

Configure how products are delivered to customers.

#### Shipping Method Listing

| Column | Description |
|---|---|
| **Method Details** | Name and description |
| **Type** | Flat Rate or Calculated |
| **Rates** | Rate information |
| **Status** | Active or Inactive |
| **Default** | Whether this is the default method |
| **Actions** | Edit, Delete |

#### Pre-Configured Methods

| Method | Type | Rate Details |
|---|---|---|
| **Standard Shipping** | Flat Rate | $5.00 base + $0.50 per kg |
| **Express Shipping** | Calculated | Weight-based with 6 rate tiers |
| **Local Pickup** | Flat Rate | Free ($0.00) |

**Shipping Types:**

- **Flat Rate** — A fixed charge, optionally with a per-weight surcharge
- **Calculated** — Rates computed dynamically based on weight tiers, destination, or carrier APIs

Click **Add Shipping Method** to create additional methods with custom rate structures.

---

### 11.7 Tax Rules

Manage tax classes, rates, zones, and rules for tax calculation.

#### Tax Classes

Define categories of tax treatment applied to products.

| Column | Description |
|---|---|
| **Name** | Class name |
| **Code** | Unique code |
| **Description** | Class description |
| **Default** | Whether this is the default class |

**Pre-configured Classes:**

| Class | Description |
|---|---|
| **Digital Goods** | For digital/downloadable products |
| **Reduced Rate** | For products eligible for reduced tax rates |
| **Standard Rate** | Default tax class for most products |
| **Zero Rate** | For tax-exempt products |

#### Tax Rates

Define specific tax percentages.

| Column | Description |
|---|---|
| **Name** | Rate name |
| **Code** | Unique code |
| **Rate** | Tax percentage |
| **Priority** | Processing order |
| **Compound** | Whether this rate compounds on top of other rates |
| **Status** | Active or Inactive |

**Pre-configured Rates:**

| Rate | Percentage |
|---|---|
| GST (Generic) | 10% |
| GST India | 18% |
| US Sales Tax | 8.25% |
| VAT Reduced | 5% |
| VAT Standard | 20% |

#### Tax Zones

Geographic regions where specific tax rates apply.

| Column | Description |
|---|---|
| **Name** | Zone name |
| **Code** | Zone code |
| **Description** | Zone description |
| **Locations** | Countries/regions in the zone |
| **Status** | Active or Inactive |

**Pre-configured Zones:** Australia, EU, India, UK, US

#### Creating a Tax Rule

Tax rules connect tax classes, zones, and rates together.

| Field | Required | Description |
|---|---|---|
| Rule Name | ✅ | Descriptive name for the rule |
| Tax Class | ✅ | Which tax class this rule applies to (select) |
| Tax Zone | ✅ | Which geographic zone this rule applies to (select) |
| Tax Rate | ✅ | Which tax rate to apply (select) |
| Priority | | Rule priority (lower = evaluated first) |
| Include Shipping | | Toggle — whether to charge tax on shipping costs |
| Active Status | | Toggle to enable/disable the rule |

> **Example:** To charge 18% GST on standard-rate products sold in India: create a rule with Tax Class = "Standard Rate", Tax Zone = "India", Tax Rate = "GST India 18%".

---

### 11.8 Email Settings

Configure outbound email delivery and manage email templates.

#### General Configuration

| Field | Description |
|---|---|
| Mail Driver | `SMTP`, `Amazon SES`, or `Log/Testing` |
| From Email | Sender email address for all outbound emails |
| From Name | Sender display name |
| Reply-To Email | Email address for customer replies |

#### SMTP Server Details

| Field | Description |
|---|---|
| SMTP Host | Mail server hostname (e.g., `smtp.gmail.com`, `smtp.mailgun.org`) |
| SMTP Port | Server port (typically 587 for TLS, 465 for SSL) |
| SMTP Username | Authentication username |
| SMTP Password | Authentication password |
| Encryption | `TLS`, `SSL`, or `None` |

#### Email Templates

Manage the content and design of transactional emails. Templates are organized by category:

**Account Emails:**

| Template | Trigger |
|---|---|
| Email Verification | Sent when a new account needs email verification |
| Password Reset | Sent when a customer requests a password reset |
| Welcome Email | Sent upon successful account registration |

**Order Emails:**

| Template | Trigger |
|---|---|
| Order Placed | Sent when a new order is submitted |
| Order Shipped | Sent when an order shipment is created |
| Order Delivered | Sent when an order is marked as delivered |
| Order Cancelled | Sent when an order is cancelled |

**Invoice Emails:**

| Template | Trigger |
|---|---|
| Invoice Email | Sent when an invoice is generated |

**Payment Emails:**

| Template | Trigger |
|---|---|
| Payment Received | Sent upon successful payment confirmation |
| Payment Failed | Sent when a payment attempt fails |

**Shipment Emails:**

| Template | Trigger |
|---|---|
| Shipment Shipped | Sent when a shipment is dispatched |
| Shipment Delivered | Sent when a shipment is delivered |

**Credit Memo Emails:**

| Template | Trigger |
|---|---|
| Credit Memo Created | Sent when a refund credit memo is issued |

---

### 11.9 AI Settings

Configure AI-powered features for product descriptions, content generation, and intelligent assistance. The AI Settings page has **4 tabs** (note: saving applies to the currently active tab only).

#### Tab 1 — Providers

Configure connections to AI service providers.

| Field | Required | Description |
|---|---|---|
| Provider Name | ✅ | Display name for the provider |
| Type | ✅ | `OpenAI`, `Gemini`, `Anthropic`, or `Custom` |
| Base URL | | API endpoint URL (pre-filled for standard providers; required for Custom) |
| API Key | ✅ | Authentication API key |
| Organization ID | | Provider organization identifier (e.g., OpenAI org ID) |
| Project ID | | Provider project identifier |
| Primary provider | | Toggle — set as the default provider |

#### Tab 2 — Models

Register specific AI models available from your configured providers.

| Field | Required | Description |
|---|---|---|
| Model ID | ✅ | Model identifier (e.g., `gpt-4o`, `gemini-pro`, `claude-3-opus`) |
| Provider | ✅ | Select from configured providers |
| Mode | | `text`, `vision`, `embedding`, or `image generation` |
| Max context tokens | | Maximum token window for the model |
| Default model | | Toggle — set as the default model |

#### Tab 3 — AI Agents

Create purpose-specific AI agents with custom system prompts and parameters.

| Field | Required | Description |
|---|---|---|
| Agent Name | ✅ | Display name (e.g., "Product Description Writer") |
| Provider | ✅ | Select from configured providers |
| Model | ✅ | Select from configured models |
| Temperature | | Controls randomness (0 = deterministic, 1 = creative) |
| Max Output | | Maximum output token length |
| System Prompt | | Instructions defining the agent's behavior and style |
| Default agent | | Toggle — set as the default agent |

#### Tab 4 — Access & Defaults

Global AI feature toggles and default assignments.

| Field | Description |
|---|---|
| AI Features | Master toggle — enable or disable all AI features |
| Default Provider | Select the default provider for AI operations |
| Default Generic Agent | Select the default agent for general AI tasks |
| Product Description Agent | Select the agent used specifically for generating product descriptions |

---

## 12. System Administration

### 12.1 Cache Management

Manage application caching for performance optimization.

#### Cache Overview

| Metric | Description |
|---|---|
| **Cache Driver** | Current cache driver (e.g., `database`) |
| **Total Size** | Combined size of all cached data |
| **Total Keys** | Total number of cache entries |
| **Cache Types** | Number of cache type categories |

#### Cache Types

| Type | Description |
|---|---|
| **Application Cache** | General application data cache |
| **Configuration** | Cached configuration files |
| **Routes** | Cached route definitions |
| **Views** | Compiled Blade/view templates |
| **Events** | Cached event-listener mappings |

Each cache type displays its **size** and **key count**, with individual:

- **Clear** button — Remove all entries of this type
- **Rebuild** button — Rebuild the cache (where applicable, e.g., Configuration, Routes)

#### Bulk Actions

- **Select All** — Select all cache types
- **Clear Selected** — Clear all selected cache types at once
- **Rebuild Selected** — Rebuild all selected cache types
- **Auto-refresh** — Toggle automatic cache status refresh
- **Refresh** — Manually refresh cache statistics

---

### 12.2 Menu Configuration

> ⚠️ **Note:** The Menu Configuration route currently returns a **404 error**. This feature is not yet implemented. Menu management functionality is planned for a future release.

---

### 12.3 Extensions

Manage installable extensions that add functionality to Cartxis.

#### Extension Listing

| Column | Description |
|---|---|
| **Name** | Extension display name |
| **Code** | Unique extension code |
| **Version** | Installed version |
| **Source** | Extension source/origin |
| **Status** | Active or Inactive |
| **Actions** | Enable, Disable, Configure |

#### Bundled Extensions

| Extension | Code | Description |
|---|---|---|
| **PayPal Payment Gateway** | `cartxis-paypal` | PayPal checkout integration |
| **PayUMoney Payment Gateway** | `cartxis-payumoney` | PayUMoney payment processing |
| **PhonePe Payment Gateway** | `cartxis-phonepe` | PhonePe UPI payments |
| **Razorpay Payment Gateway** | `cartxis-razorpay` | Razorpay payment processing |
| **Stripe Payment Gateway** | `cartxis-stripe` | Stripe payment processing |

Click **Sync Extensions** to refresh extension manifests and detect newly installed or updated extensions.

---

### 12.4 Permissions

Manage system permissions and access controls for admin users.

- Click **Add Permission** to create a new permission entry
- Assign permissions to control which admin users can access specific modules and actions
- Use granular permissions to restrict access to sensitive areas like settings, reports, or system administration

---

### 12.5 Maintenance Mode

Put the storefront into maintenance mode during updates or planned downtime.

#### Controls

| Field | Description |
|---|---|
| Enable/Disable Maintenance | Master toggle to activate or deactivate maintenance mode |

#### Settings

| Field | Description |
|---|---|
| Title | Heading displayed on the maintenance page |
| Message | Body text shown to visitors |
| Contact Email | Email address for visitor inquiries during downtime |
| Retry After (seconds) | HTTP `Retry-After` header value for search engines and clients |
| Allowed IP Addresses | Comma-separated list of IPs that can bypass maintenance mode (for admin access) |

#### Schedule Maintenance

| Field | Description |
|---|---|
| Start Time | Scheduled maintenance start |
| End Time | Scheduled maintenance end |
| Reason | Internal reason for the maintenance window |

#### Statistics

| Metric | Description |
|---|---|
| Status | Current maintenance mode status |
| Total Logs | Number of maintenance events logged |
| Allowed IPs | Count of whitelisted IP addresses |

A **Maintenance History** log provides a record of all past maintenance events.

---

### 12.6 Data Migration

> ⚠️ **Note:** The Data Migration route currently returns a **404 error**. This feature is not yet implemented. Data import/migration functionality is planned for a future release.

---

### 12.7 API Sync

Monitor and manage synchronization between the Cartxis admin and connected mobile applications.

| Metric | Description |
|---|---|
| **Connection Status** | `Connected` or `Disconnected` |
| **Sync Status** | `Enabled` or `Disabled` |
| **Last Sync Result** | Outcome of the most recent synchronization |

**Actions:**

- **Refresh** — Update the displayed sync status
- **Sync Now** — Trigger an immediate synchronization

---

### 12.8 Backups

Create and manage database and file backups.

#### Backup Listing

| Column | Description |
|---|---|
| **File Name** | Backup file name |
| **Size** | File size |
| **Date** | Backup creation date |
| **Actions** | Download, Delete |

Click **Create Backup** to generate a new backup immediately. Store backups securely and test restoration periodically to ensure data recovery readiness.

---

## 13. Troubleshooting

### Common Issues

#### Admin Panel Won't Load

1. Verify the application is running: `php artisan serve`
2. Check the `.env` file for correct `APP_URL` and database credentials
3. Clear caches: `php artisan cache:clear && php artisan config:clear && php artisan view:clear`
4. Check storage permissions: `chmod -R 775 storage bootstrap/cache`

#### Storefront Shows Blank Page

1. Run `npm run build` to compile frontend assets
2. Check for JavaScript errors in the browser console
3. Verify that `php artisan storage:link` has been run
4. Check `storage/logs/laravel.log` for PHP errors

#### Payment Gateway Not Working

1. Confirm the payment method is set to **Active** in Settings → Payment Methods
2. Verify API keys are correctly entered in the gateway configuration
3. Check that the gateway extension is active in System → Extensions
4. Review `storage/logs/laravel.log` for API errors from the gateway

#### Products Not Showing on Storefront

1. Verify the product Status is **Enabled**
2. Check the product Visibility is set to **Catalog & Search** or **Catalog Only**
3. Confirm the product is assigned to at least one **active** category
4. Ensure the product has a price and stock quantity greater than zero

#### Email Notifications Not Sending

1. Navigate to Settings → Email Settings and verify the mail driver configuration
2. If using SMTP, check host, port, username, and password
3. Set the mail driver to `Log/Testing` temporarily to verify emails are being triggered (check `storage/logs/laravel.log`)
4. Ensure the "From Email" address is valid and not blocked by the SMTP provider

#### Pages Return 404

Some admin routes are not yet implemented:
- **Storefront Menus** (Content module)
- **Menu Configuration** (System module)
- **Data Migration** (System module)

These features are planned for a future release.

### Performance Optimization

- Use `database` or `redis` cache driver for production (configure in `.env`)
- Rebuild caches after configuration changes: System → Cache Management → Rebuild Selected
- Optimize autoloading: `composer dump-autoload --optimize`
- Enable route caching: `php artisan route:cache`
- Enable config caching: `php artisan config:cache`
- Enable view caching: `php artisan view:cache`

---

## 14. Quick Reference

### Keyboard Shortcuts

| Action | Shortcut |
|---|---|
| Save Form | `Ctrl/⌘ + S` (where supported) |
| Search | `Ctrl/⌘ + K` (where supported) |

### Status Reference

**Order Statuses:** Pending → Processing → Completed (or Cancelled / Refunded / Failed)

**Product Statuses:** Enabled, Disabled

**Product Visibility:** Catalog & Search, Catalog Only, Search Only, Not Visible

**Review Statuses:** Pending → Approved or Rejected

**Page Statuses:** Draft, Published, Disabled

### Product Types at a Glance

| Type | Physical | Shipping | Variants | Downloads |
|---|---|---|---|---|
| **Simple** | ✅ | ✅ | ❌ | ❌ |
| **Configurable** | ✅ | ✅ | ✅ | ❌ |
| **Virtual** | ❌ | ❌ | ❌ | ❌ |
| **Downloadable** | ❌ | ❌ | ❌ | ✅ |

### Coupon Discount Types

| Type | Example |
|---|---|
| **Percentage** | 20% off cart total |
| **Fixed Amount** | $10 off |
| **Free Shipping** | $0 shipping |
| **Buy X Get Y** | Buy 2 get 1 free |
| **Fixed Price** | Set item to $5.00 |

### Promotion Types

| Type | Scope |
|---|---|
| **Catalog Price Rule** | Applied to product display price |
| **Cart Price Rule** | Applied at checkout |
| **Bundle Deal** | Applied to grouped products |
| **Flash Sale** | Time-limited with optional countdown |
| **Tiered Pricing** | Quantity-based discounts |

### Tax Configuration Summary

| Component | Purpose | Examples |
|---|---|---|
| **Tax Classes** | Product tax categories | Standard Rate, Reduced Rate, Digital Goods, Zero Rate |
| **Tax Rates** | Specific tax percentages | VAT 20%, GST 18%, Sales Tax 8.25% |
| **Tax Zones** | Geographic regions | US, UK, EU, India, Australia |
| **Tax Rules** | Connect class + zone + rate | "Standard Rate products in India → GST 18%" |

### Payment Methods

| Method | Type | Region |
|---|---|---|
| Cash on Delivery | Built-in | Global |
| Bank Transfer | Built-in | Global |
| Stripe | Extension | Global |
| PayPal | Extension | Global |
| Razorpay | Extension | India |
| PhonePe | Extension | India |
| PayUMoney | Extension | India |

### Shipping Methods

| Method | Type | Rate |
|---|---|---|
| Standard Shipping | Flat Rate | $5.00 + $0.50/kg |
| Express Shipping | Calculated | 6 weight-based tiers |
| Local Pickup | Flat Rate | Free |

### Customer Groups

| Group | Discount | Auto-Assignment |
|---|---|---|
| General | 0% | Default for new customers |
| Wholesale | 15% | After 5 orders |
| VIP | 20% | After 10 orders |
| Retail | 5% | — |

### AI Provider Types

OpenAI · Gemini · Anthropic · Custom

### Email Drivers

SMTP · Amazon SES · Log/Testing

### Block Types

Text · HTML · Banner · Promotion · Newsletter

### Not Yet Implemented

The following features appear in the admin sidebar but currently return 404:

- Storefront Menus (Content module)
- Menu Configuration (System module)
- Data Migration (System module)

---

*Cartxis E-Commerce Platform — Version 1.0.4 — February 2026*
