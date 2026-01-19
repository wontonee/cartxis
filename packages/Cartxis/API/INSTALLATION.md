# Cartxis API Package - Installation & Setup Guide

## 📦 Complete Package Structure Created

```
packages/Cartxis/API/
├── Config/
│   └── api.php                          ✅ API configuration
├── Http/
│   ├── Controllers/V1/
│   │   ├── AuthController.php           ✅ Authentication endpoints
│   │   ├── ProductController.php        ✅ Product browsing & details
│   │   ├── CategoryController.php       ✅ Category navigation
│   │   ├── CartController.php           ✅ Shopping cart management
│   │   ├── CheckoutController.php       ✅ Checkout process
│   │   ├── CustomerController.php       ✅ Customer profile & addresses
│   │   ├── OrderController.php          ✅ Order management
│   │   ├── WishlistController.php       ✅ Wishlist functionality
│   │   ├── ReviewController.php         ✅ Product reviews
│   │   └── SearchController.php         ✅ Product search
│   ├── Resources/
│   │   ├── UserResource.php             ✅ User data transformer
│   │   ├── ProductResource.php          ✅ Product data transformer
│   │   ├── CategoryResource.php         ✅ Category data transformer
│   │   ├── CartResource.php             ✅ Cart data transformer
│   │   ├── OrderResource.php            ✅ Order data transformer
│   │   ├── AddressResource.php          ✅ Address data transformer
│   │   └── ReviewResource.php           ✅ Review data transformer
│   ├── Requests/                        (Ready for custom validations)
│   └── Middleware/                      (Ready for API middleware)
├── Routes/
│   └── api.php                          ✅ All API routes (40+ endpoints)
├── Providers/
│   └── APIServiceProvider.php           ✅ Service provider
├── Helpers/
│   └── ApiResponse.php                  ✅ Response helper
├── composer.json                        ✅ Package definition
└── README.md                            ✅ Documentation
```

## 🚀 Installation Steps

### Step 1: Register the Package

Add to your main `composer.json` at the root:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/",
            "Cartxis\\API\\": "packages/Cartxis/API/"
        }
    }
}
```

Then run:

```bash
composer dump-autoload
```

### Step 2: Install Laravel Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### Step 3: Configure Sanctum

Update `config/sanctum.php`:

```php
'expiration' => env('SANCTUM_EXPIRATION', 1440), // 24 hours
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

Add to your `app/Http/Kernel.php`:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### Step 4: Add HasApiTokens to User Model

Update `app/Models/User.php`:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    // ... rest of your model
}
```

### Step 5: Register the API Service Provider

Add to `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    // ... other providers
    Cartxis\API\Providers\APIServiceProvider::class,
];
```

Or add to `config/app.php` (if using older Laravel):

```php
'providers' => [
    // ...
    Cartxis\API\Providers\APIServiceProvider::class,
],
```

### Step 6: Publish Configuration

```bash
php artisan vendor:publish --tag=vortex-api-config
```

This creates `config/vortex-api.php` with API settings.

### Step 7: Configure CORS (Optional)

Update `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['*'], // Update for production
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

### Step 8: Update Environment Variables

Add to `.env`:

```env
# API Configuration
API_CACHE_ENABLED=true
SANCTUM_EXPIRATION=1440

# Payment Gateways
STRIPE_ENABLED=true
RAZORPAY_ENABLED=true
COD_ENABLED=true

# App Currency
APP_CURRENCY=USD
```

## 🧪 Testing the API

### 1. Start Development Server

```bash
php artisan serve
```

### 2. Test Registration

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "terms_accepted": true
  }'
```

### 3. Test Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

Save the token from the response!

### 4. Test Protected Endpoint

```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

### 5. Test Product Listing

```bash
curl -X GET "http://localhost:8000/api/v1/products?per_page=10" \
  -H "Accept: application/json"
```

## 📱 Flutter Integration Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class CartxisApiClient {
  static const String baseUrl = 'http://localhost:8000/api/v1';
  String? _token;

  // Set token after login
  void setToken(String token) {
    _token = token;
  }

  // Login
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      setToken(data['data']['token']);
      return data;
    }
    throw Exception('Login failed');
  }

  // Get Products
  Future<List<dynamic>> getProducts({int page = 1, int perPage = 20}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/products?page=$page&per_page=$perPage'),
      headers: {'Accept': 'application/json'},
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load products');
  }

  // Get Cart (Authenticated)
  Future<Map<String, dynamic>> getCart() async {
    final response = await http.get(
      Uri.parse('$baseUrl/cart'),
      headers: {
        'Authorization': 'Bearer $_token',
        'Accept': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data'];
    }
    throw Exception('Failed to load cart');
  }

  // Add to Cart
  Future<void> addToCart(int productId, int quantity) async {
    final response = await http.post(
      Uri.parse('$baseUrl/cart/add'),
      headers: {
        'Authorization': 'Bearer $_token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'product_id': productId,
        'quantity': quantity,
      }),
    );
    
    if (response.statusCode != 200) {
      throw Exception('Failed to add to cart');
    }
  }
}
```

## 📋 Available API Endpoints

### Authentication (Public)
- `POST /api/v1/auth/register` - Register new user
- `POST /api/v1/auth/login` - Login user
- `POST /api/v1/auth/password/forgot` - Request password reset
- `POST /api/v1/auth/password/reset` - Reset password

### Authentication (Protected)
- `POST /api/v1/auth/logout` - Logout user
- `GET /api/v1/auth/me` - Get user profile
- `PUT /api/v1/auth/profile` - Update profile
- `POST /api/v1/auth/password/change` - Change password
- `POST /api/v1/auth/avatar` - Upload avatar

### Products (Public)
- `GET /api/v1/products` - List products (paginated, filterable, sortable)
- `GET /api/v1/products/{id}` - Get product details
- `GET /api/v1/products/featured` - Featured products
- `GET /api/v1/products/on-sale` - Products on sale
- `GET /api/v1/products/new-arrivals` - New arrivals
- `GET /api/v1/products/{id}/related` - Related products
- `GET /api/v1/products/{id}/reviews` - Product reviews

### Categories (Public)
- `GET /api/v1/categories` - List categories
- `GET /api/v1/categories/tree` - Category tree
- `GET /api/v1/categories/{id}` - Category details
- `GET /api/v1/categories/{id}/products` - Products in category

### Search (Public)
- `GET /api/v1/search?q=query` - Search products
- `GET /api/v1/search/suggestions?q=query` - Search suggestions

### Cart (Protected)
- `GET /api/v1/cart` - Get cart
- `POST /api/v1/cart/add` - Add to cart
- `PUT /api/v1/cart/items/{id}` - Update cart item
- `DELETE /api/v1/cart/items/{id}` - Remove from cart
- `DELETE /api/v1/cart/clear` - Clear cart
- `POST /api/v1/cart/apply-coupon` - Apply coupon
- `DELETE /api/v1/cart/remove-coupon` - Remove coupon
- `GET /api/v1/cart/count` - Get cart count

### Checkout (Protected)
- `GET /api/v1/checkout/init` - Initialize checkout
- `POST /api/v1/checkout/shipping-address` - Set shipping address
- `POST /api/v1/checkout/billing-address` - Set billing address
- `GET /api/v1/checkout/shipping-methods` - Get shipping methods
- `POST /api/v1/checkout/shipping-method` - Set shipping method
- `GET /api/v1/checkout/payment-methods` - Get payment methods
- `POST /api/v1/checkout/payment-method` - Set payment method
- `GET /api/v1/checkout/summary` - Get checkout summary
- `POST /api/v1/checkout/place-order` - Place order

### Customer (Protected)
- `GET /api/v1/customer/profile` - Get profile
- `PUT /api/v1/customer/profile` - Update profile
- `GET /api/v1/customer/addresses` - Get addresses
- `POST /api/v1/customer/addresses` - Create address
- `PUT /api/v1/customer/addresses/{id}` - Update address
- `DELETE /api/v1/customer/addresses/{id}` - Delete address
- `POST /api/v1/customer/addresses/{id}/set-default` - Set default address

### Orders (Protected)
- `GET /api/v1/customer/orders` - Get orders
- `GET /api/v1/customer/orders/{id}` - Get order details
- `POST /api/v1/customer/orders/{id}/cancel` - Cancel order
- `GET /api/v1/customer/orders/{id}/invoice` - Get invoice
- `GET /api/v1/customer/orders/{id}/track` - Track order

### Wishlist (Protected)
- `GET /api/v1/customer/wishlist` - Get wishlist
- `POST /api/v1/customer/wishlist/add` - Add to wishlist
- `DELETE /api/v1/customer/wishlist/{id}` - Remove from wishlist
- `POST /api/v1/customer/wishlist/{id}/move-to-cart` - Move to cart

### Reviews (Protected)
- `POST /api/v1/reviews` - Create review
- `PUT /api/v1/reviews/{id}` - Update review
- `DELETE /api/v1/reviews/{id}` - Delete review
- `POST /api/v1/reviews/{id}/vote` - Vote on review

## 🔐 Rate Limiting

- Guest users: 60 requests/minute
- Authenticated users: 300 requests/minute
- Login endpoint: 5 attempts/minute
- Customizable in `config/vortex-api.php`

## 🎯 Next Steps

1. **Test all endpoints** with Postman or curl
2. **Customize validation** - Add custom Request classes in `Http/Requests/`
3. **Add middleware** - Create custom middleware in `Http/Middleware/`
4. **Implement payment** - Complete Stripe/Razorpay integration in CheckoutController
5. **Add email notifications** - Order confirmations, password resets
6. **Set up queues** - For email sending and heavy operations
7. **Add caching** - Implement Redis caching for products/categories
8. **Write tests** - Create API tests in `tests/Feature/Api/`

## 📖 Documentation

Full API documentation with request/response examples is available in:
- `/specificationandtask/api/` folder (if you want to reference implementation details)

## 🐛 Troubleshooting

### Issue: 404 Not Found on API routes
**Solution**: Clear route cache: `php artisan route:clear && php artisan route:cache`

### Issue: Unauthenticated errors
**Solution**: Check token is sent in `Authorization: Bearer TOKEN` header

### Issue: CORS errors from Flutter app
**Solution**: Update `config/cors.php` to allow your app's origin

### Issue: Class not found errors
**Solution**: Run `composer dump-autoload`

## 📞 Support

For issues or questions, check the main documentation or create an issue in the repository.

---

**Package Version**: 1.0.0  
**Laravel Version**: 11.x / 12.x  
**PHP Version**: 8.2+  
**License**: MIT
