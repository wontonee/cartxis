# Product Package

The Product package is a self-contained, modular package for managing products in the Vortex eCommerce platform.

## Package Structure

```
packages/Product/
├── Config/
│   └── product.php              # Product configuration
├── Database/
│   └── Migrations/              # Database migrations
│       ├── 2025_10_11_000001_create_products_table.php
│       ├── 2025_10_11_000002_create_product_images_table.php
│       ├── 2025_10_11_000003_create_categories_table.php
│       └── 2025_10_11_000004_create_product_attributes_table.php
├── Http/
│   └── Controllers/
│       └── Admin/
│           └── ProductController.php  # Admin product controller
├── Models/
│   ├── Product.php              # Product model
│   ├── ProductImage.php         # Product image model
│   ├── Category.php             # Category model
│   ├── Attribute.php            # Attribute model
│   ├── AttributeOption.php      # Attribute option model
│   └── ProductAttributeValue.php # Product attribute value model
├── Providers/
│   └── ProductServiceProvider.php # Service provider
├── Repositories/                # Repository pattern (coming soon)
├── Resources/
│   └── views/                   # Blade views (coming soon)
├── Routes/
│   ├── admin.php               # Admin routes
│   └── web.php                 # Public routes
└── Services/                    # Business logic (coming soon)
```

## Features

### Product Types
- Simple Products
- Configurable Products (with variants)
- Virtual Products
- Downloadable Products

### Product Management
- SKU management
- Pricing (regular & special prices)
- Inventory tracking
- Stock status management
- Product images (main, gallery, thumbnail)
- Product descriptions (short & full)
- SEO fields (meta title, description, keywords)
- Product categories (multiple categories per product)
- Product attributes (flexible attribute system)
- Product variants (for configurable products)

### Category Management
- Nested categories (unlimited levels)
- Nested Set implementation for efficient hierarchy queries
- Category images
- SEO fields per category
- Category status management

### Attribute System
- Flexible attribute types (select, multiselect, text, textarea, date, boolean, price)
- Configurable attributes (for product variants)
- Filterable attributes
- Attribute options with swatch support (colors, images)

## Installation

1. The package is automatically loaded via `composer.json`:
```json
"autoload": {
    "psr-4": {
        "Packages\\Product\\": "packages/Product/"
    }
}
```

2. Register the service provider in `bootstrap/providers.php`:
```php
Packages\Product\Providers\ProductServiceProvider::class,
```

3. Run migrations:
```bash
php artisan migrate
```

## Routes

### Admin Routes
- `GET /admin/products` - List products
- `GET /admin/products/create` - Create product form
- `POST /admin/products` - Store product
- `GET /admin/products/{id}/edit` - Edit product form
- `PUT /admin/products/{id}` - Update product
- `DELETE /admin/products/{id}` - Delete product
- `POST /admin/products/bulk-destroy` - Bulk delete products
- `POST /admin/products/bulk-status` - Bulk update product status

## Models

### Product
- Relationships: categories, images, attributeValues, variants
- Scopes: enabled, disabled, featured, new, inStock, outOfStock, visible
- Methods: getFinalPrice(), hasSpecialPrice(), isInStock(), etc.

### Category
- Relationships: products, parent, children, ancestors, descendants
- Nested Set pattern for hierarchy
- Scopes: enabled, disabled, root, showInMenu

### ProductImage
- Relationships: product
- Types: main, gallery, thumbnail
- Accessor: getUrlAttribute()

## Configuration

Publish the configuration file:
```bash
php artisan vendor:publish --tag=product-config
```

Configuration options in `config/product.php`:
- Default product status
- Default visibility
- Stock management settings
- Image upload settings
- Product types
- Stock statuses

## Usage Examples

### Create a Product
```php
use Packages\Product\Models\Product;

$product = Product::create([
    'sku' => 'PROD-001',
    'name' => 'Sample Product',
    'slug' => 'sample-product',
    'price' => 99.99,
    'status' => 'enabled',
    'visibility' => 'both',
    'quantity' => 100,
]);
```

### Add Categories
```php
$product->categories()->attach([1, 2, 3]);
```

### Add Images
```php
$product->images()->create([
    'path' => 'products/image.jpg',
    'type' => 'main',
    'sort_order' => 0,
]);
```

### Get Final Price
```php
$finalPrice = $product->getFinalPrice(); // Returns special price if active, otherwise regular price
```

## Development

This package follows the modular architecture outlined in the project specification. Each package is self-contained and can be independently:
- Installed
- Updated
- Disabled
- Removed

This makes it easy for other developers to create extensions and plugins that interact with the Product package.

## License

MIT
