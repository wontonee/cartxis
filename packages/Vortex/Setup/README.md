# Setup Wizard - Installation Guide

## Overview

The Setup Wizard provides a first-time installation experience for the Vortex eCommerce platform. It allows users to select their business type and import relevant demo data to get started quickly.

## Features

✅ **Multi-Step Wizard Interface**
- Welcome screen with platform introduction
- Business type selection
- Demo data import with progress tracking
- Completion confirmation

✅ **Business Type Templates**
- **Retail Store**: General retail products (electronics, clothing, home goods)
- **Kirana/Grocery Store**: Daily essentials, fresh produce, dairy products
- **Electronics Store**: Tech products and gadgets (placeholder)
- **Fashion Store**: Apparel and accessories (placeholder)

✅ **Demo Data Import**
- Categories relevant to business type
- Sample products with descriptions
- Brand information
- CMS pages and blocks
- Import statistics tracking

## Architecture

### Package Structure
```
packages/Vortex/Setup/
├── composer.json
└── src/
    ├── Http/
    │   ├── Controllers/
    │   │   └── SetupController.php
    │   └── Middleware/
    │       ├── RedirectIfSetupComplete.php
    │       └── RedirectIfSetupIncomplete.php
    ├── Services/
    │   └── DemoDataService.php
    ├── Database/
    │   └── Seeders/
    │       ├── RetailDemoSeeder.php
    │       ├── KiranaDemoSeeder.php
    │       ├── ElectronicsDemoSeeder.php (placeholder)
    │       └── FashionDemoSeeder.php (placeholder)
    ├── Providers/
    │   └── SetupServiceProvider.php
    └── Routes/
        └── setup.php
```

### Frontend Components
```
resources/js/pages/Setup/
├── Welcome.vue          # Welcome screen
├── BusinessType.vue     # Business type selection
├── DemoData.vue        # Demo data import
└── Finish.vue          # Completion screen
```

## Routes

| Method | Route | Controller Method | Description |
|--------|-------|-------------------|-------------|
| GET | `/setup` | `welcome()` | Welcome screen |
| GET | `/setup/business-type` | `businessType()` | Business type selection |
| GET | `/setup/demo-data` | `demoData()` | Demo data import screen |
| POST | `/setup/import-demo-data` | `importDemoData()` | Import demo data API |
| POST | `/setup/complete` | `complete()` | Mark setup complete |
| GET | `/setup/finish` | `finish()` | Completion screen |

## Middleware

### `RedirectIfSetupIncomplete`
- Applied to admin and frontend routes
- Redirects to setup wizard if `setup_completed` setting is not true
- Checks if `settings` table exists before querying

### `RedirectIfSetupComplete`
- Applied to setup wizard routes
- Redirects to admin dashboard if setup is already complete
- Prevents re-running the wizard after completion

## Demo Data Service

### Methods

#### `getBusinessTypes(): array`
Returns available business types with ID, name, and description.

#### `importDemoData(string $businessType, bool $importProducts = true): array`
Imports demo data for the selected business type:
- Runs appropriate seeders
- Tracks import statistics
- Saves business type in settings
- Returns success status and statistics

#### `markSetupComplete(): void`
Sets `setup_completed` flag to `1` in settings table.

## Business Type Seeders

### Retail Demo Seeder
**Categories:**
- Electronics
- Clothing
- Home & Living
- Sports & Outdoors
- Books & Media

**Sample Products:**
- Wireless Bluetooth Headphones
- Cotton T-Shirt - Premium
- Modern Table Lamp

### Kirana Demo Seeder
**Categories:**
- Fruits & Vegetables
- Dairy Products
- Bakery & Snacks
- Beverages
- Household Essentials
- Personal Care

**Sample Products:**
- Fresh Bananas
- Full Cream Milk
- Wheat Bread
- Coca-Cola
- Dishwash Liquid
- Dove Soap

## Usage Flow

### 1. First Installation
When a user installs Vortex for the first time:
1. Navigate to any URL
2. `RedirectIfSetupIncomplete` middleware triggers
3. Redirected to `/setup` welcome screen

### 2. Welcome Screen
- Shows platform introduction
- Lists key features
- "Get Started" button → Business Type selection

### 3. Business Type Selection
- Display cards for each business type
- User selects one
- "Continue" button enabled after selection
- Progress: 33%

### 4. Demo Data Import
- Shows selected business type
- Checkbox to import sample products (default: checked)
- Warning about demo data nature
- Options:
  - "Import Demo Data" → Start import
  - "Skip for Now" → Skip to finish
  - "Back" → Return to business type selection
- Progress: 66%

### 5. Import Progress
- Shows loading spinner
- "Importing Demo Data..." message
- Cannot be cancelled

### 6. Import Success
- Success checkmark
- Import statistics displayed
- Auto-redirect to finish page after 2 seconds

### 7. Completion
- Congratulations message
- "Next Steps" checklist
- "Go to Admin Dashboard" button
- Calls `/setup/complete` API
- Sets `setup_completed = 1`
- Redirects to `/admin`
- Progress: 100%

## Database Changes

### Settings Table
Two new settings are created:

```sql
-- Business type selected during setup
INSERT INTO settings (key, value) VALUES ('business_type', 'retail');

-- Setup completion flag
INSERT INTO settings (key, value) VALUES ('setup_completed', '1');
```

## Testing

### Manual Testing

1. **Reset Setup Status**
```sql
DELETE FROM settings WHERE key IN ('setup_completed', 'business_type');
```

2. **Access Application**
- Navigate to `https://vortex.test`
- Should redirect to `/setup`

3. **Complete Wizard**
- Click "Get Started"
- Select "Retail Store"
- Click "Continue"
- Check "Import Sample Products"
- Click "Import Demo Data"
- Wait for success message
- Click "Go to Admin Dashboard"

4. **Verify Import**
```sql
SELECT COUNT(*) FROM categories;  -- Should show imported categories
SELECT COUNT(*) FROM products;    -- Should show imported products
SELECT * FROM settings WHERE key = 'setup_completed';  -- Should be '1'
```

### Testing Different Business Types

**Kirana Store:**
```
1. Reset: DELETE FROM settings WHERE key IN ('setup_completed', 'business_type');
2. Navigate to /setup
3. Select "Kirana/Grocery Store"
4. Import demo data
5. Verify grocery categories and products
```

**Skip Demo Data:**
```
1. Reset setup
2. Navigate to /setup
3. Select any business type
4. Click "Skip for Now"
5. Should go to finish page
6. No products should be imported
7. Business type should still be saved
```

## Customization

### Adding New Business Types

1. **Create Seeder**
```php
// packages/Vortex/Setup/src/Database/Seeders/YourBusinessSeeder.php
class YourBusinessSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedBrands();
        $this->seedProducts();
        $this->seedPages();
        $this->seedBlocks();
    }
}
```

2. **Register in DemoDataService**
```php
private const BUSINESS_TYPES = [
    'your-business' => [
        'name' => 'Your Business Type',
        'description' => 'Description of your business',
        'seeders' => [
            \Vortex\Setup\Database\Seeders\YourBusinessSeeder::class,
        ],
    ],
];
```

3. **Update Validation**
```php
// In SetupController::importDemoData()
'business_type' => 'required|string|in:retail,kirana,electronics,fashion,your-business',
```

### Customizing UI

**Business Type Cards:**
Edit `resources/js/pages/Setup/BusinessType.vue`

**Welcome Screen:**
Edit `resources/js/pages/Setup/Welcome.vue`

**Colors/Branding:**
All components use Tailwind CSS classes. Update gradients:
- Blue/Indigo: Primary branding
- Green/Emerald: Success states

## Troubleshooting

### Setup Wizard Not Appearing

**Check middleware registration:**
```php
// bootstrap/app.php or routes
Route::middleware(['web', 'setup.incomplete'])->group(...);
```

**Verify settings:**
```sql
SELECT * FROM settings WHERE key = 'setup_completed';
```

### Import Failing

**Check seeder classes exist:**
```bash
php artisan db:seed --class=Vortex\\Setup\\Database\\Seeders\\RetailDemoSeeder
```

**Check database connection:**
```bash
php artisan tinker
DB::connection()->getPdo();
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

### Redirect Loop

**Clear setup flag:**
```sql
DELETE FROM settings WHERE key = 'setup_completed';
```

**Check middleware order:**
Ensure `setup.incomplete` is not applied to setup routes.

## Security Considerations

1. **One-Time Setup**: Wizard can only run once
2. **No Authentication Required**: Setup runs before admin user exists
3. **Demo Data Warning**: Users are warned data is for demonstration
4. **Database Transactions**: Import uses transactions for atomicity

## Future Enhancements

### Phase 2 Features
- [ ] Store configuration step (name, email, currency)
- [ ] Admin user creation
- [ ] Email configuration
- [ ] Payment gateway setup
- [ ] Shipping method configuration
- [ ] Theme selection

### Phase 3 Features
- [ ] Multi-language support in setup
- [ ] More business type templates
- [ ] Custom demo data URLs
- [ ] Import from existing store
- [ ] Skip wizard for developers

## API Reference

### POST `/setup/import-demo-data`

**Request:**
```json
{
    "business_type": "retail",
    "import_products": true
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Demo data imported successfully for Retail Store",
    "stats": {
        "categories": 5,
        "products": 3,
        "brands": 5,
        "pages": 2,
        "blocks": 2
    }
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Failed to import demo data: Error details"
}
```

### POST `/setup/complete`

**Response:**
```json
{
    "success": true,
    "message": "Setup completed successfully",
    "redirect": "/admin"
}
```

## Credits

**Built for:** Vortex Open-Source eCommerce Platform  
**Created:** January 2026  
**Version:** 1.0.0

---

For support, please refer to the main project documentation or raise an issue on GitHub.
