# Cartxis API Reference

**Version 1.0.4** | **Base URL**: `/api/v1` | **Last Updated**: February 2026

A complete REST API reference for Cartxis mobile app integration.

---

## Table of Contents

1. [Introduction](#introduction)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Error Codes](#error-codes)
5. [Rate Limiting](#rate-limiting)
6. [API Endpoints](#api-endpoints)
   - [Authentication API](#authentication-api)
   - [Products API](#products-api)
   - [Categories API](#categories-api)
   - [Cart API](#cart-api)
   - [Checkout API](#checkout-api)
   - [Customer API](#customer-api)
   - [Orders API](#orders-api)
   - [Wishlist API](#wishlist-api)
   - [Reviews API](#reviews-api)
   - [Search API](#search-api)
   - [Banners API](#banners-api)
   - [Currency API](#currency-api)
   - [Product AI API](#product-ai-api)
   - [API Sync](#api-sync)
7. [Feature Flags](#feature-flags)
8. [Data Models](#data-models)
9. [SDK Integration](#sdk-integration)
10. [Testing](#testing)

---

## Introduction

The Cartxis API provides a RESTful interface for mobile applications to interact with the e-commerce platform. It supports user authentication, product browsing, shopping cart management, checkout, order tracking, and more.

### Base URL

```
Production: https://your-domain/api/v1
Development: http://localhost:8000/api/v1
```

### Headers

All requests should include:

```http
Accept: application/json
Content-Type: application/json
```

For authenticated requests, add:

```http
Authorization: Bearer {token}
```

---

## Authentication

Cartxis uses **Laravel Sanctum** for API authentication with bearer tokens.

### Obtaining a Token

1. **Register** a new user via `POST /auth/register`
2. **Login** with credentials via `POST /auth/login`
3. Use the returned `token` in the `Authorization` header

### Token Lifecycle

- Tokens expire after **24 hours** (configurable)
- Use `POST /auth/refresh` to get a new token
- Use `POST /auth/logout` to revoke the current token

### Token Usage

```http
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

---

## Response Format

### Success Response

```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": { ... },
  "meta": {
    "timestamp": "2026-02-08T18:00:00+00:00",
    "version": "v1"
  }
}
```

### Paginated Response

```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8,
    "from": 1,
    "to": 20,
    "timestamp": "2026-02-08T18:00:00+00:00",
    "version": "v1"
  },
  "links": {
    "first": "http://domain.com/api/v1/products?page=1",
    "last": "http://domain.com/api/v1/products?page=8",
    "prev": null,
    "next": "http://domain.com/api/v1/products?page=2"
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Invalid credentials",
  "error_code": "INVALID_CREDENTIALS",
  "errors": null,
  "meta": {
    "timestamp": "2026-02-08T18:00:00+00:00",
    "version": "v1"
  }
}
```

### Validation Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "meta": {
    "timestamp": "2026-02-08T18:00:00+00:00",
    "version": "v1"
  }
}
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 422 | Request validation failed |
| `INVALID_CREDENTIALS` | 401 | Wrong email or password |
| `UNAUTHORIZED` | 401 | Missing or invalid token |
| `USER_NOT_FOUND` | 404 | User account not found |
| `PRODUCT_NOT_FOUND` | 404 | Product does not exist |
| `PRODUCT_UNAVAILABLE` | 403 | Product is disabled |
| `CART_EMPTY` | 400 | Cart has no items |
| `INVALID_COUPON` | 400 | Coupon code not valid |
| `INSUFFICIENT_STOCK` | 400 | Not enough stock |
| `ORDER_NOT_FOUND` | 404 | Order does not exist |
| `INVALID_PASSWORD` | 400 | Current password incorrect |
| `AI_GENERATION_FAILED` | 422 | AI description generation failed |
| `NOT_FOUND` | 404 | Generic resource not found |
| `SERVER_ERROR` | 500 | Internal server error |

---

## Rate Limiting

| User Type | Limit |
|-----------|-------|
| Guest (unauthenticated) | 60 requests/minute |
| Authenticated | 300 requests/minute |
| Login endpoint | 5 attempts/minute |
| Payment endpoints | 10 requests/minute |

When rate limited, you'll receive a `429 Too Many Requests` response.

---

## API Endpoints

---

### Authentication API

#### Register User

Create a new customer account.

```http
POST /api/v1/auth/register
```

**Request Body:**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+91 9876543210",
  "terms_accepted": true
}
```

**Response:** `201 Created`

```json
{
  "success": true,
  "message": "Registration successful. Welcome to Cartxis!",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+91 9876543210",
      "avatar_url": null,
      "email_verified_at": null,
      "created_at": "2026-02-08T18:00:00+00:00"
    },
    "token": "1|abc123xyz...",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

---

#### Login

Authenticate and get access token.

```http
POST /api/v1/auth/login
```

**Request Body:**

```json
{
  "email": "john@example.com",
  "password": "password123",
  "device_name": "iPhone 15 Pro",
  "remember_me": true
}
```

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+91 9876543210",
      "avatar_url": "https://domain.com/storage/avatars/user1.jpg"
    },
    "token": "1|abc123xyz...",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

---

#### Logout

Revoke current access token.

```http
POST /api/v1/auth/logout
```

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": null
}
```

---

#### Get Current User

Get authenticated user details.

```http
GET /api/v1/auth/me
```

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "User details retrieved",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+91 9876543210",
    "avatar_url": "https://domain.com/storage/avatars/user1.jpg",
    "email_verified_at": "2026-02-08T18:00:00+00:00",
    "created_at": "2026-02-08T18:00:00+00:00"
  }
}
```

---

#### Update Profile

Update user profile information.

```http
PUT /api/v1/auth/profile
```

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
  "name": "John Smith",
  "phone": "+91 9876543211",
  "date_of_birth": "1990-05-15",
  "gender": "male"
}
```

**Response:** `200 OK`

---

#### Change Password

Change account password.

```http
POST /api/v1/auth/password/change
```

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response:** `200 OK`

---

#### Upload Avatar

Upload profile picture.

```http
POST /api/v1/auth/avatar
```

**Headers:** 
- `Authorization: Bearer {token}`
- `Content-Type: multipart/form-data`

**Request Body:**

| Field | Type | Description |
|-------|------|-------------|
| avatar | File | Image file (max 2MB) |

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Avatar uploaded successfully",
  "data": {
    "avatar_url": "https://domain.com/storage/avatars/user1.jpg"
  }
}
```

---

#### Forgot Password

Request password reset email.

```http
POST /api/v1/auth/password/forgot
```

**Request Body:**

```json
{
  "email": "john@example.com"
}
```

---

#### Reset Password

Reset password with token.

```http
POST /api/v1/auth/password/reset
```

**Request Body:**

```json
{
  "email": "john@example.com",
  "token": "reset-token-from-email",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

#### Refresh Token

Get a new access token.

```http
POST /api/v1/auth/refresh
```

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "token": "2|newtoken...",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

---

### Products API

#### List Products

Get paginated list of products with filtering and sorting.

```http
GET /api/v1/products
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 20, max: 100) |
| `search` | string | Search by name, SKU, or description |
| `category_id` | integer | Filter by single category |
| `category_ids` | string | Filter by multiple categories (comma-separated) |
| `brand_id` | integer | Filter by brand |
| `brand_ids` | string | Filter by multiple brands (comma-separated) |
| `min_price` | number | Minimum price filter |
| `max_price` | number | Maximum price filter |
| `featured` | boolean | Only featured products |
| `new` | boolean | Only new arrivals |
| `on_sale` | boolean | Only products on sale |
| `in_stock` | boolean | Only in-stock products |
| `min_rating` | number | Minimum average rating |
| `sort_by` | string | Sort field: `price`, `name`, `created_at`, `popularity`, `rating`, `discount` |
| `sort_order` | string | Sort direction: `asc` or `desc` |

**Example:**

```http
GET /api/v1/products?category_id=5&min_price=100&max_price=500&sort_by=price&sort_order=asc&per_page=10
```

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": [
    {
      "id": 1,
      "sku": "PROD-001",
      "name": "Premium Yoga Mat",
      "slug": "premium-yoga-mat",
      "description": "High-quality yoga mat...",
      "short_description": "Premium yoga mat for professionals",
      "price": 29.99,
      "special_price": 24.99,
      "final_price": 24.99,
      "discount_percentage": 16.67,
      "currency": "INR",
      "stock_status": "in_stock",
      "quantity": 85,
      "in_stock": true,
      "is_featured": true,
      "is_new": false,
      "weight": 1.5,
      "dimensions": {
        "length": 180,
        "width": 60,
        "height": 0.5
      },
      "brand": {
        "id": 1,
        "name": "FitPro",
        "slug": "fitpro",
        "logo": "https://domain.com/storage/brands/fitpro.png"
      },
      "categories": [
        {
          "id": 5,
          "name": "Sports & Outdoors",
          "slug": "sports-outdoors"
        }
      ],
      "images": [
        {
          "id": 1,
          "path": "https://domain.com/storage/products/yoga-mat-1.jpg",
          "thumbnail": "https://domain.com/storage/products/yoga-mat-1.jpg",
          "position": 1,
          "is_main": true
        }
      ],
      "reviews_summary": {
        "average_rating": 4.5,
        "total_reviews": 23
      },
      "created_at": "2026-01-15T10:00:00+00:00",
      "updated_at": "2026-02-01T15:30:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 150,
    "last_page": 15
  },
  "links": { ... }
}
```

---

#### Get Product Details

Get single product with full details.

```http
GET /api/v1/products/{id}
```

**Response:** `200 OK`

Returns product with additional fields:
- `variants` - Product variations
- `attributes` - Product attributes
- `recent_reviews` - Last 5 reviews
- `meta` - SEO metadata

---

#### Featured Products

Get featured products.

```http
GET /api/v1/products/featured?limit=10
```

---

#### Products on Sale

Get products with active discounts.

```http
GET /api/v1/products/on-sale?per_page=20
```

---

#### New Arrivals

Get recently added products.

```http
GET /api/v1/products/new-arrivals?limit=20
```

---

#### Related Products

Get products related to a specific product.

```http
GET /api/v1/products/{id}/related
```

---

### Categories API

#### List Categories

Get all categories.

```http
GET /api/v1/categories
```

---

#### Category Tree

Get hierarchical category structure.

```http
GET /api/v1/categories/tree
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "image": "https://domain.com/storage/categories/electronics.jpg",
      "children": [
        {
          "id": 2,
          "name": "Smartphones",
          "slug": "smartphones",
          "children": []
        },
        {
          "id": 3,
          "name": "Laptops",
          "slug": "laptops",
          "children": []
        }
      ]
    }
  ]
}
```

---

#### Get Category

Get single category details.

```http
GET /api/v1/categories/{id}
```

---

#### Category Products

Get products in a category.

```http
GET /api/v1/categories/{id}/products?per_page=20
```

---

### Cart API

> **Note:** All cart endpoints require authentication.

#### Get Cart

Get current cart contents.

```http
GET /api/v1/cart
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "items": [
      {
        "id": 1,
        "product_id": 5,
        "product_name": "Premium Yoga Mat",
        "product_image": "https://domain.com/storage/products/yoga-mat.jpg",
        "price": 24.99,
        "quantity": 2,
        "total": 49.98
      }
    ],
    "subtotal": 49.98,
    "discount": 0,
    "shipping": 0,
    "tax": 9.00,
    "total": 58.98,
    "coupon_code": null,
    "item_count": 2
  }
}
```

---

#### Add to Cart

Add a product to cart.

```http
POST /api/v1/cart/add
```

**Request Body:**

```json
{
  "product_id": 5,
  "quantity": 2,
  "variant_id": null
}
```

---

#### Update Cart Item

Update quantity of a cart item.

```http
PUT /api/v1/cart/items/{id}
```

**Request Body:**

```json
{
  "quantity": 3
}
```

---

#### Remove Cart Item

Remove an item from cart.

```http
DELETE /api/v1/cart/items/{id}
```

---

#### Clear Cart

Remove all items from cart.

```http
DELETE /api/v1/cart/clear
```

---

#### Apply Coupon

Apply a discount coupon.

```http
POST /api/v1/cart/apply-coupon
```

**Request Body:**

```json
{
  "coupon_code": "SAVE20"
}
```

---

#### Remove Coupon

Remove applied coupon.

```http
DELETE /api/v1/cart/remove-coupon
```

---

#### Get Cart Count

Get number of items in cart.

```http
GET /api/v1/cart/count
```

**Response:**

```json
{
  "success": true,
  "data": {
    "count": 3
  }
}
```

---

### Checkout API

> **Note:** All checkout endpoints require authentication.

#### Initialize Checkout

Start checkout process.

```http
GET /api/v1/checkout/init
```

---

#### Set Shipping Address

Set or create shipping address.

```http
POST /api/v1/checkout/shipping-address
```

**Request Body:**

```json
{
  "address_id": 1
}
```

Or create new:

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+91 9876543210",
  "address_line_1": "123 Main Street",
  "address_line_2": "Apt 4B",
  "city": "Mumbai",
  "state": "Maharashtra",
  "postcode": "400001",
  "country": "IN"
}
```

---

#### Set Billing Address

Set billing address.

```http
POST /api/v1/checkout/billing-address
```

---

#### Get Shipping Methods

Get available shipping options.

```http
GET /api/v1/checkout/shipping-methods
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "code": "free_shipping",
      "name": "Free Shipping",
      "description": "3-5 business days",
      "rate": 0
    },
    {
      "code": "express",
      "name": "Express Delivery",
      "description": "1-2 business days",
      "rate": 9.99
    }
  ]
}
```

---

#### Set Shipping Method

Select shipping method.

```http
POST /api/v1/checkout/shipping-method
```

**Request Body:**

```json
{
  "shipping_method": "express"
}
```

---

#### Get Payment Methods

Get available payment options.

```http
GET /api/v1/checkout/payment-methods
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "code": "stripe",
      "name": "Credit/Debit Card",
      "description": "Pay with Visa, Mastercard, etc.",
      "type": "card"
    },
    {
      "code": "razorpay",
      "name": "Razorpay",
      "description": "UPI, Cards, Netbanking",
      "type": "gateway"
    },
    {
      "code": "cod",
      "name": "Cash on Delivery",
      "description": "Pay when delivered",
      "type": "offline"
    }
  ]
}
```

---

#### Set Payment Method

Select payment method.

```http
POST /api/v1/checkout/payment-method
```

**Request Body:**

```json
{
  "payment_method": "razorpay"
}
```

---

#### Get Checkout Summary

Get order summary before placing.

```http
GET /api/v1/checkout/summary
```

---

#### Place Order

Complete the order.

```http
POST /api/v1/checkout/place-order
```

**Request Body:**

```json
{
  "notes": "Please call before delivery",
  "payment_data": {
    "razorpay_payment_id": "pay_xyz123",
    "razorpay_order_id": "order_abc456",
    "razorpay_signature": "sig_..."
  }
}
```

**Response:** `201 Created`

```json
{
  "success": true,
  "message": "Order placed successfully",
  "data": {
    "order_id": 12345,
    "order_number": "ORD-2026-0001",
    "total": 58.98,
    "status": "pending",
    "payment_status": "paid"
  }
}
```

---

### Customer API

> **Note:** All customer endpoints require authentication.

#### Get Profile

Get customer profile.

```http
GET /api/v1/customer/profile
```

---

#### Update Profile

Update customer information.

```http
PUT /api/v1/customer/profile
```

---

#### Get Addresses

Get saved addresses.

```http
GET /api/v1/customer/addresses
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "shipping",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": "+91 9876543210",
      "address_line_1": "123 Main Street",
      "address_line_2": "Apt 4B",
      "city": "Mumbai",
      "state": "Maharashtra",
      "postcode": "400001",
      "country": "IN",
      "country_name": "India",
      "is_default_shipping": true,
      "is_default_billing": false
    }
  ]
}
```

---

#### Create Address

Add new address.

```http
POST /api/v1/customer/addresses
```

**Request Body:**

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+91 9876543210",
  "address_line_1": "456 Oak Avenue",
  "city": "Delhi",
  "state": "Delhi",
  "postcode": "110001",
  "country": "IN",
  "is_default_shipping": false,
  "is_default_billing": true
}
```

---

#### Update Address

Update existing address.

```http
PUT /api/v1/customer/addresses/{id}
```

---

#### Delete Address

Remove an address.

```http
DELETE /api/v1/customer/addresses/{id}
```

---

### Orders API

> **Note:** All order endpoints require authentication.

#### Get Orders

Get customer order history.

```http
GET /api/v1/customer/orders
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number |
| `per_page` | integer | Items per page |
| `status` | string | Filter by status |

---

#### Get Order Details

Get single order details.

```http
GET /api/v1/customer/orders/{id}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 12345,
    "order_number": "ORD-2026-0001",
    "status": "processing",
    "payment_status": "paid",
    "payment_method": "razorpay",
    "subtotal": 49.98,
    "discount": 0,
    "shipping": 0,
    "tax": 9.00,
    "total": 58.98,
    "currency": "INR",
    "items": [
      {
        "id": 1,
        "product_id": 5,
        "product_name": "Premium Yoga Mat",
        "sku": "YOGA-001",
        "price": 24.99,
        "quantity": 2,
        "total": 49.98
      }
    ],
    "shipping_address": { ... },
    "billing_address": { ... },
    "notes": "Please call before delivery",
    "created_at": "2026-02-08T10:00:00+00:00",
    "updated_at": "2026-02-08T12:00:00+00:00"
  }
}
```

---

#### Cancel Order

Request order cancellation.

```http
POST /api/v1/customer/orders/{id}/cancel
```

**Request Body:**

```json
{
  "reason": "Changed my mind"
}
```

---

#### Get Invoice

Download order invoice.

```http
GET /api/v1/customer/orders/{id}/invoice
```

---

#### Track Order

Get order tracking information.

```http
GET /api/v1/customer/orders/{id}/track
```

---

### Wishlist API

> **Note:** All wishlist endpoints require authentication.

#### Get Wishlist

Get wishlist items.

```http
GET /api/v1/customer/wishlist
```

---

#### Add to Wishlist

Add product to wishlist.

```http
POST /api/v1/customer/wishlist/add
```

**Request Body:**

```json
{
  "product_id": 5
}
```

---

#### Remove from Wishlist

Remove item from wishlist.

```http
DELETE /api/v1/customer/wishlist/{id}
```

---

#### Move to Cart

Move wishlist item to cart.

```http
POST /api/v1/customer/wishlist/{id}/move-to-cart
```

---

### Reviews API

#### Get Product Reviews

Get reviews for a product (public).

```http
GET /api/v1/products/{id}/reviews
```

---

#### Create Review

Submit a product review (authenticated).

```http
POST /api/v1/reviews
```

**Request Body:**

```json
{
  "product_id": 5,
  "rating": 5,
  "title": "Excellent product!",
  "comment": "Very satisfied with the quality..."
}
```

---

#### Update Review

Update own review.

```http
PUT /api/v1/reviews/{id}
```

---

#### Delete Review

Delete own review.

```http
DELETE /api/v1/reviews/{id}
```

---

#### Vote on Review

Mark review as helpful.

```http
POST /api/v1/reviews/{id}/vote
```

**Request Body:**

```json
{
  "helpful": true
}
```

---

### Search API

#### Search Products

Search products by keyword.

```http
GET /api/v1/search?q=yoga+mat
```

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | Search query (required) |
| `category_id` | integer | Filter by category |
| `per_page` | integer | Results per page |

---

#### Search Suggestions

Get autocomplete suggestions.

```http
GET /api/v1/search/suggestions?q=yo
```

**Response:**

```json
{
  "success": true,
  "data": [
    "yoga mat",
    "yoga block",
    "yoga strap"
  ]
}
```

---

### Banners API

#### Get Banners

Get mobile app banners.

```http
GET /api/v1/banners
```

---

#### Get Banner by Identifier

Get specific banner.

```http
GET /api/v1/banners/{identifier}
```

---

### Currency API

#### Get Default Currency

Get store default currency.

```http
GET /api/v1/currency/default
```

---

#### Get All Currencies

Get available currencies.

```http
GET /api/v1/currency
```

---

### Product AI API

> **Note:** Requires authentication. Requires AI features to be enabled in admin Settings → AI Settings.

#### Generate Product Description

Use AI to generate product descriptions based on product data and optional context.

```http
POST /api/v1/products/{id}/generate-description
```

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
  "product_title": "Premium Yoga Mat",
  "category": "Sports & Outdoors",
  "attributes": ["Non-slip", "6mm thickness", "Eco-friendly"],
  "brand": "FitPro",
  "key_features": ["Anti-tear", "Moisture resistant", "Carrying strap included"],
  "target_audience": "Fitness enthusiasts",
  "tone_preference": "professional",
  "language": "en",
  "agent": "Product Description Writer"
}
```

**Request Fields:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `product_title` | string | No | Override product name (max 200 chars) |
| `category` | string | No | Product category context (max 255 chars) |
| `attributes` | array | No | Product attributes for context |
| `images` | array | No | Image URLs for vision-capable models |
| `brand` | string | No | Brand name (max 120 chars) |
| `key_features` | array | No | Key selling points |
| `target_audience` | string | No | Intended audience (max 120 chars) |
| `tone_preference` | string | No | Writing tone: `professional`, `casual`, `luxury`, etc. (max 50 chars) |
| `language` | string | No | Output language code, e.g., `en`, `hi` (max 10 chars) |
| `agent` | string | No | Specific AI agent name to use (max 120 chars) |

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Product description generated",
  "data": {
    "short_description": "Professional-grade yoga mat with non-slip surface...",
    "description": "Elevate your yoga practice with the Premium Yoga Mat by FitPro..."
  }
}
```

**Error Response:** `422`

```json
{
  "success": false,
  "message": "AI generation failed: Provider not configured",
  "error_code": "AI_GENERATION_FAILED"
}
```

---

### API Sync

Endpoints for mobile app connectivity monitoring. Used by the admin panel's **System → API Sync** page to show connection status.

> **Note:** Requires authentication. All authenticated API requests automatically update the sync heartbeat via the `TrackApiSync` middleware.

#### Get Sync Status

Get current sync/connectivity status.

```http
GET /api/v1/system/api-sync/status
```

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`

```json
{
  "connected": true,
  "sync_enabled": true,
  "last_sync_at": "2026-02-09T10:30:00",
  "last_status": "success",
  "last_message": "All data synchronized",
  "last_checked_at": "2026-02-09T10:35:00"
}
```

> **Note:** `connected` is `true` if a heartbeat was received within the last 120 seconds.

---

#### Send Heartbeat

Report mobile app connectivity and sync status to the server.

```http
POST /api/v1/system/api-sync/heartbeat
```

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
  "connected": true,
  "sync_enabled": true,
  "last_status": "success",
  "last_message": "Synchronized 42 products",
  "last_sync_at": "2026-02-09T10:30:00"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `connected` | boolean | ✅ | Whether the app considers itself connected |
| `sync_enabled` | boolean | No | Whether auto-sync is enabled in the app |
| `last_status` | string | No | Status of the last sync operation |
| `last_message` | string | No | Descriptive message about the last sync |
| `last_sync_at` | datetime | No | Timestamp of the last sync operation |

**Response:** `200 OK`

```json
{
  "success": true
}
```

---

## Feature Flags

The API has configurable feature flags. Disabled features will return `404` or `403` responses.

| Feature | Default | Description |
|---------|---------|-------------|
| `wishlist` | ✅ Enabled | Wishlist functionality |
| `reviews` | ✅ Enabled | Product reviews and ratings |
| `coupons` | ✅ Enabled | Coupon/discount code support |
| `gift_cards` | ❌ Disabled | Gift card support (planned) |
| `loyalty_points` | ❌ Disabled | Loyalty points system (planned) |

---

## Data Models

### User

```typescript
interface User {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  avatar_url: string | null;
  email_verified_at: string | null;
  created_at: string;
}
```

### Product

```typescript
interface Product {
  id: number;
  sku: string;
  name: string;
  slug: string;
  description: string;
  short_description: string;
  price: number;
  special_price: number | null;
  final_price: number;
  discount_percentage: number;
  currency: string;
  stock_status: 'in_stock' | 'out_of_stock';
  quantity: number;
  in_stock: boolean;
  is_featured: boolean;
  is_new: boolean;
  weight: number;
  dimensions: {
    length: number;
    width: number;
    height: number;
  };
  brand: Brand | null;
  categories: Category[];
  images: ProductImage[];
  variants: ProductVariant[];
  attributes: ProductAttribute[];
  reviews_summary: {
    average_rating: number;
    total_reviews: number;
  };
  meta: {
    meta_title: string;
    meta_description: string;
    meta_keywords: string;
  };
  created_at: string;
  updated_at: string;
}
```

### Category

```typescript
interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  image: string | null;
  parent_id: number | null;
  children: Category[];
}
```

### Address

```typescript
interface Address {
  id: number;
  type: 'shipping' | 'billing';
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  address_line_1: string;
  address_line_2: string | null;
  city: string;
  state: string;
  postcode: string;
  country: string;
  country_name: string;
  is_default_shipping: boolean;
  is_default_billing: boolean;
}
```

### Order

```typescript
interface Order {
  id: number;
  order_number: string;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
  payment_method: string;
  subtotal: number;
  discount: number;
  shipping: number;
  tax: number;
  total: number;
  currency: string;
  items: OrderItem[];
  shipping_address: Address;
  billing_address: Address;
  notes: string | null;
  created_at: string;
  updated_at: string;
}
```

---

## SDK Integration

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class CartxisApi {
  static const String baseUrl = 'https://your-domain/api/v1';
  String? _token;

  Map<String, String> get _headers => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    if (_token != null) 'Authorization': 'Bearer $_token',
  };

  void setToken(String token) => _token = token;

  // Login
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: _headers,
      body: jsonEncode({'email': email, 'password': password}),
    );
    
    final data = jsonDecode(response.body);
    if (data['success']) {
      setToken(data['data']['token']);
    }
    return data;
  }

  // Get Products
  Future<List<dynamic>> getProducts({
    int page = 1,
    int perPage = 20,
    String? search,
    int? categoryId,
  }) async {
    final params = {
      'page': page.toString(),
      'per_page': perPage.toString(),
      if (search != null) 'search': search,
      if (categoryId != null) 'category_id': categoryId.toString(),
    };
    
    final uri = Uri.parse('$baseUrl/products').replace(queryParameters: params);
    final response = await http.get(uri, headers: _headers);
    
    final data = jsonDecode(response.body);
    return data['data'];
  }

  // Add to Cart
  Future<void> addToCart(int productId, int quantity) async {
    await http.post(
      Uri.parse('$baseUrl/cart/add'),
      headers: _headers,
      body: jsonEncode({
        'product_id': productId,
        'quantity': quantity,
      }),
    );
  }

  // Place Order
  Future<Map<String, dynamic>> placeOrder({
    required String paymentMethod,
    Map<String, dynamic>? paymentData,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/checkout/place-order'),
      headers: _headers,
      body: jsonEncode({
        'payment_data': paymentData,
      }),
    );
    return jsonDecode(response.body);
  }
}
```

### React Native/JavaScript Example

```javascript
const API_BASE = 'https://your-domain/api/v1';

class CartxisApi {
  constructor() {
    this.token = null;
  }

  setToken(token) {
    this.token = token;
  }

  async request(endpoint, options = {}) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
      ...options,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(this.token && { 'Authorization': `Bearer ${this.token}` }),
        ...options.headers,
      },
    });
    return response.json();
  }

  // Auth
  async login(email, password) {
    const result = await this.request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
    if (result.success) {
      this.setToken(result.data.token);
    }
    return result;
  }

  // Products
  async getProducts(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/products?${query}`);
  }

  // Cart
  async addToCart(productId, quantity) {
    return this.request('/cart/add', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId, quantity }),
    });
  }
}

export default new CartxisApi();
```

---

## Testing

### Postman Collection

Import the included Postman collection for testing:

- `packages/Cartxis/API/Vortex-API.postman_collection.json`
- `packages/Cartxis/API/Vortex-API.postman_environment.json`

### cURL Examples

**Register:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123","terms_accepted":true}'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

**Get Products:**
```bash
curl -X GET "http://localhost:8000/api/v1/products?per_page=10&category_id=5" \
  -H "Accept: application/json"
```

**Add to Cart:**
```bash
curl -X POST http://localhost:8000/api/v1/cart/add \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"product_id":5,"quantity":2}'
```

---

## Support

- **API Issues**: [GitHub Issues](https://github.com/wontonee/cartxis/issues)
- **Documentation**: Check `packages/Cartxis/API/` folder

---

**Cartxis API v1.0.4** | Built with Laravel Sanctum | MIT License
