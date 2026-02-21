# Cartxis API Package

> RESTful API package for Cartxis eCommerce platform - Mobile app backend

## 📦 Package Structure

```
API/
├── Config/
│   └── api.php                 # API configuration
├── Http/
│   ├── Controllers/
│   │   └── V1/                 # Version 1 controllers
│   │       ├── AuthController.php
│   │       ├── ProductController.php
│   │       ├── CartController.php
│   │       ├── CheckoutController.php
│   │       ├── CustomerController.php
│   │       ├── OrderController.php
│   │       └── ...
│   ├── Middleware/             # API-specific middleware
│   ├── Requests/               # Form request validation
│   └── Resources/              # API resources (transformers)
│       └── UserResource.php
├── Routes/
│   └── api.php                 # API routes definition
├── Providers/
│   └── APIServiceProvider.php  # Service provider
├── Helpers/
│   └── ApiResponse.php         # Response helper
└── README.md                   # This file
```

## 🚀 Installation

1. **Register Service Provider**

Add to `config/app.php`:

```php
'providers' => [
    // ...
    Cartxis\API\Providers\APIServiceProvider::class,
],
```

Or use auto-discovery in `composer.json`:

```json
{
    "extra": {
        "laravel": {
            "providers": [
                "Cartxis\\API\\Providers\\APIServiceProvider"
            ]
        }
    }
}
```

2. **Install Laravel Sanctum**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

3. **Configure Sanctum**

Update `config/sanctum.php`:

```php
'expiration' => env('SANCTUM_EXPIRATION', 1440), // 24 hours
```

4. **Add Sanctum Middleware**

In `app/Http/Kernel.php`:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

5. **Publish Configuration**

```bash
php artisan vendor:publish --tag=cartxis-api-config
```

## 📝 API Routes

All routes are prefixed with `/api/v1`:

### Public Routes (No Auth Required)
- `POST /auth/register` - Register new user
- `POST /auth/login` - Login user
- `GET /products` - List products
- `GET /products/{id}` - Get product details
- `GET /categories` - List categories

### Protected Routes (Auth Required)
- `POST /auth/logout` - Logout user
- `GET /cart` - Get cart
- `POST /cart/add` - Add to cart
- `POST /checkout/place-order` - Place order
- `GET /customer/orders` - Get orders
- ... and more

## 🔧 Usage Examples

### Using ApiResponse Helper

```php
use Cartxis\API\Helpers\ApiResponse;

// Success response
return ApiResponse::success($data, 'Success message');

// Error response
return ApiResponse::error('Error message', $errors, 400, 'ERROR_CODE');

// Paginated response
return ApiResponse::paginated($paginator, 'Success message');

// Not found response
return ApiResponse::notFound('Resource not found', 'PRODUCT_NOT_FOUND');
```

### Creating API Resources

```php
use Cartxis\API\Http\Resources\UserResource;

return ApiResponse::success(
    new UserResource($user),
    'User retrieved successfully'
);
```

### Creating Controllers

```php
namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Cartxis\API\Helpers\ApiResponse;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::paginate(20);
        return ApiResponse::paginated($products);
    }
}
```

## 🔐 Authentication

This package uses **Laravel Sanctum** for API authentication.

### Token Generation

```php
$token = $user->createToken('mobile-app')->plainTextToken;
```

### Protected Routes

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/protected', [Controller::class, 'method']);
});
```

### Token in Flutter

```dart
// Add to request headers
headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
}
```

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": {},
  "meta": {
    "timestamp": "2025-12-20T10:30:00Z",
    "version": "v1"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "error_code": "ERROR_CODE",
  "errors": {},
  "meta": {
    "timestamp": "2025-12-20T10:30:00Z",
    "version": "v1"
  }
}
```

## ⚙️ Configuration

Edit `config/cartxis-api.php`:

```php
return [
    'version' => 'v1',
    'rate_limits' => [
        'guest' => 60,
        'authenticated' => 300,
    ],
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],
];
```

## 📚 Documentation

Full API documentation is available in `/specificationandtask/api/`:

- [API Architecture](../../../specificationandtask/api/01-API-ARCHITECTURE.md)
- [Authentication API](../../../specificationandtask/api/02-AUTHENTICATION.md)
- [Product API](../../../specificationandtask/api/03-PRODUCT-API.md)
- [Cart API](../../../specificationandtask/api/04-CART-API.md)
- [Checkout API](../../../specificationandtask/api/05-CHECKOUT-API.md)
- [Customer Account API](../../../specificationandtask/api/07-CUSTOMER-ACCOUNT-API.md)
- [Error Codes](../../../specificationandtask/api/99-ERROR-CODES.md)

## 🧪 Testing

```bash
# Run API tests
php artisan test --filter=Api
```

## 🔗 Dependencies

- Laravel 12+
- Laravel Sanctum
- PHP 8.2+

## 📄 License

MIT License - Part of Cartxis eCommerce Platform
