# Postman Collection Guide

## Quick Start

### 1. Import Collection and Environment

1. Open Postman
2. Click **Import** button (top left)
3. Import both files:
   - `Vortex-API.postman_collection.json` - All API endpoints
   - `Vortex-API.postman_environment.json` - Environment variables

### 2. Select Environment

1. Click environment dropdown (top right)
2. Select **"Vortex API - Local"**

### 3. Start Testing

The collection is organized into folders:
- **Authentication** - Register, login, profile management
- **Products** - Browse, search, filter products
- **Categories** - Category navigation
- **Cart** - Shopping cart operations
- **Checkout** - Complete checkout flow
- **Customer** - Profile and address management
- **Orders** - Order history and tracking
- **Wishlist** - Save favorite products
- **Reviews** - Product reviews and ratings
- **Search** - Search and autocomplete

## Authentication Flow

### Automatic Token Management

The Register and Login endpoints automatically save the access token to your environment:

1. **Register a new user:**
   ```
   POST /api/v1/auth/register
   ```
   - Token automatically saved to `access_token` variable

2. **Or Login with existing credentials:**
   ```
   POST /api/v1/auth/login
   ```
   - Token automatically saved to `access_token` variable

3. **All protected endpoints automatically use the token**
   - No manual token copying needed
   - Token is sent as `Authorization: Bearer {token}` header

### Manual Token Update

If you need to manually update the token:

1. Click the **Environment quick look** icon (👁️ top right)
2. Click **Edit** next to "Vortex API - Local"
3. Update the `access_token` value
4. Click **Save**

## Testing Workflow

### Complete User Journey

1. **Register/Login** (`Authentication` folder)
   - Start with "Register" or "Login"
   - Token saved automatically

2. **Browse Products** (`Products` folder)
   - "List Products" - Browse all products
   - "Featured Products" - See featured items
   - "Product Details" - View specific product

3. **Add to Cart** (`Cart` folder)
   - "Add to Cart" - Add products
   - "View Cart" - Check cart contents
   - "Apply Coupon" - Test discount codes

4. **Complete Checkout** (`Checkout` folder)
   - "Initialize Checkout"
   - "Set Shipping Address"
   - "Set Billing Address"
   - "Set Shipping Method"
   - "Set Payment Method"
   - "Checkout Summary"
   - "Place Order"

5. **Manage Orders** (`Orders` folder)
   - "List Orders" - View order history
   - "Order Details" - Check specific order
   - "Track Order" - Get tracking info

### Testing Specific Features

#### Product Discovery
```
Products → List Products (with filters)
Products → Featured Products
Products → On Sale Products
Products → Search Products
Categories → Category Tree
```

#### Cart Management
```
Cart → Add to Cart
Cart → Update Cart Item
Cart → Apply Coupon
Cart → View Cart
Cart → Clear Cart
```

#### Customer Profile
```
Customer → Get Profile
Customer → Update Profile
Customer → List Addresses
Customer → Add Address
Customer → Set Default Address
```

#### Wishlist
```
Wishlist → Add to Wishlist
Wishlist → View Wishlist
Wishlist → Move to Cart
Wishlist → Remove from Wishlist
```

## Query Parameters

Many endpoints support query parameters for filtering and pagination:

### List Products
```
?page=1              # Page number
&per_page=20         # Items per page (max 100)
&sort=price          # Sort by: name, price, created_at
&order=asc           # Order: asc, desc
&category_id=1       # Filter by category
&min_price=100       # Minimum price
&max_price=1000      # Maximum price
&in_stock=1          # Show only in-stock items
```

### Search Products
```
?q=laptop            # Search query
&category_id=1       # Filter by category
&min_price=500       # Minimum price
&max_price=2000      # Maximum price
```

### List Orders
```
?page=1              # Page number
&status=pending      # Filter by status
```

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data
    },
    "meta": {
        "timestamp": "2025-12-20T12:00:00Z",
        "version": "v1"
    }
}
```

### Paginated Response
```json
{
    "success": true,
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 100,
        "from": 1,
        "to": 20
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["Validation error message"]
    },
    "meta": {
        "timestamp": "2025-12-20T12:00:00Z",
        "version": "v1"
    }
}
```

## Environment Variables

### Default Variables
- `base_url`: `http://localhost:8000`
- `access_token`: Auto-populated after login/register

### Custom Variables

You can add custom variables for testing:

1. Click **Environment quick look** icon (👁️)
2. Click **Edit**
3. Add new variables:
   - `product_id`: Test product ID
   - `category_id`: Test category ID
   - `order_id`: Test order ID

Then use in requests as `{{product_id}}`

## Testing Different Scenarios

### 1. Guest User (No Authentication)
Disable authentication for these folders:
- Products
- Categories
- Search

### 2. Authenticated User
Enable authentication (default for protected endpoints):
- Cart
- Checkout
- Customer
- Orders
- Wishlist
- Reviews

### 3. Testing Validation Errors
Try invalid data:
- Empty required fields
- Invalid email format
- Mismatched passwords
- Invalid product IDs

### 4. Testing Rate Limiting
- Guest: 60 requests per minute
- Authenticated: 300 requests per minute
- Send rapid requests to test throttling

## Troubleshooting

### Token Not Working
1. Check if token is saved in environment
2. Verify token format: `Bearer {token}`
3. Re-login to get fresh token

### 404 Errors
1. Verify base URL is correct
2. Check if Laravel server is running
3. Verify route exists: `php artisan route:list`

### 422 Validation Errors
- Check request body format
- Ensure all required fields are included
- Verify field value formats

### 401 Unauthorized
- Token expired or invalid
- Login again to get new token
- Check if endpoint requires authentication

## Advanced Features

### Pre-request Scripts
Some endpoints have pre-request scripts that:
- Auto-save tokens from responses
- Set dynamic variables
- Prepare request data

### Test Scripts
Some endpoints have test scripts that:
- Validate response structure
- Check status codes
- Extract data for next requests

### Collection Variables
Use collection variables for:
- API version
- Common test data
- Shared configuration

## Support

For issues or questions:
1. Check the API documentation: `packages/Vortex/API/README.md`
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check network tab for request/response details
4. Verify database has test data

## Tips

1. **Use folders** - Run entire folder to test complete flow
2. **Save responses** - Use Examples to save successful responses
3. **Use variables** - Replace hardcoded IDs with variables
4. **Test edge cases** - Try invalid data, missing fields, etc.
5. **Check logs** - Monitor Laravel logs for backend errors
6. **Version control** - Keep collection in git for team sharing

## Next Steps

1. Import the collection and environment
2. Start Laravel server: `php artisan serve`
3. Run "Register" or "Login" to get token
4. Test public endpoints (Products, Categories)
5. Test protected endpoints (Cart, Checkout, Orders)
6. Customize environment for your setup
7. Add test data to database if needed

Happy Testing! 🚀
