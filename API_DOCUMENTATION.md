# API Documentation - BitChest Platform

This document provides comprehensive documentation for all APIs used in the BitChest cryptocurrency trading platform.

## Table of Contents
1. [Internal REST API](#internal-rest-api)
2. [External APIs](#external-apis)
3. [Authentication](#authentication)
4. [Error Handling](#error-handling)

---

## Internal REST API

### Base URL
```
http://localhost/api
```
Production: `https://your-domain.com/api`

### Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

---

## Authentication Endpoints

### Register User
**POST** `/api/register`

Creates a new user account with pending status.

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "email_confirmation": "john.doe@example.com"
}
```

**Response (201):**
```json
{
  "message": "Account created. A temporary password has been sent to your email. Please change it and wait for admin validation.",
  "status": "pending",
  "must_change_password": true,
  "temporary_password_sent": true
}
```

### Login
**POST** `/api/login`

Authenticates a user and returns an access token.

**Request Body:**
```json
{
  "email": "john.doe@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "role": "client",
    "status": "active"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "must_change_password": false
}
```

**Error Responses:**
- `401`: Invalid email or password
- `403`: Account pending validation, blocked, or not active

### Logout
**POST** `/api/logout`

Revokes the current access token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

### Change Password
**POST** `/api/change-password`

Changes the user's password.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
  "message": "Password updated. Your account has been activated.",
  "status": "active"
}
```

---

## Cryptocurrency Market Endpoints

### Get Market Data (Public)
**GET** `/api/public/market`

Retrieves current cryptocurrency market data. No authentication required.

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "symbol": "BTC",
      "name": "Bitcoin",
      "price": 45000.50,
      "change24h": 2.5,
      "marketCap": 850000000000,
      "volume24h": 25000000000
    }
  ]
}
```

### Get Market Data (Authenticated)
**GET** `/api/market`

Retrieves market data with user-specific information.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** Same as public endpoint

---

## Transaction Endpoints

### Buy Cryptocurrency
**POST** `/api/transaction/buy`

Executes a buy transaction.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "symbol": "BTC",
  "quantity": 0.001
}
```

**Response (200):**
```json
{
  "message": "Purchase completed successfully",
  "transaction": {
    "id": 123,
    "portfolio_id": 45,
    "type": "buy",
    "quantity": 0.001,
    "price_at_transaction": 45000.50,
    "euro_amount": 45.00,
    "created_at": "2026-01-23T10:30:00.000000Z",
    "updated_at": "2026-01-23T10:30:00.000000Z",
    "portfolio": {
      "crypto": {
        "id": 1,
        "symbol": "BTC",
        "name": "Bitcoin"
      }
    }
  },
  "balance": 455.00
}
```

**Error Responses:**
- `400`: Invalid quantity, insufficient balance, or price not available
- `404`: Cryptocurrency not found or inactive
- `422`: Validation error

### Sell Cryptocurrency
**POST** `/api/transaction/sell`

Executes a sell transaction.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "symbol": "BTC",
  "quantity": 0.001
}
```

**Response (200):**
```json
{
  "message": "Sale completed successfully",
  "transaction": {
    "id": 124,
    "portfolio_id": 45,
    "type": "sell",
    "quantity": 0.001,
    "price_at_transaction": 45100.00,
    "euro_amount": 45.10,
    "created_at": "2026-01-23T10:35:00.000000Z",
    "updated_at": "2026-01-23T10:35:00.000000Z",
    "portfolio": {
      "crypto": {
        "id": 1,
        "symbol": "BTC",
        "name": "Bitcoin"
      }
    }
  },
  "balance": 500.10
}
```

**Error Responses:**
- `400`: Invalid quantity, insufficient quantity, or price not available
- `404`: Cryptocurrency not found or inactive
- `422`: Validation error

### Get Transaction History
**GET** `/api/transaction/history`

Retrieves paginated transaction history for the authenticated user.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 50)

**Response (200):**
```json
{
  "data": [
    {
      "id": 123,
      "portfolio_id": 45,
      "type": "buy",
      "quantity": 0.001,
      "price_at_transaction": 45000.50,
      "euro_amount": 45.00,
      "created_at": "2026-01-23T10:30:00.000000Z",
      "updated_at": "2026-01-23T10:30:00.000000Z",
      "portfolio": {
        "crypto": {
          "id": 1,
          "symbol": "BTC",
          "name": "Bitcoin"
        }
      }
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 50,
  "total": 250
}
```

---

## Portfolio Endpoints

### Get Portfolio
**GET** `/api/portfolio`

Retrieves the user's complete portfolio with current values.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "balance": 500.00,
  "portfolio": [
    {
      "id": 45,
      "crypto_currency_id": 1,
      "total_crypto_value": 45.00,
      "current_value": 45.10,
      "total_invested_value": 45.00,
      "gain_loss": 0.10,
      "gain_loss_percent": 0.22,
      "quantity": 0.001,
      "crypto": {
        "id": 1,
        "symbol": "BTC",
        "name": "Bitcoin",
        "price": 45100.00,
        "change24h": 2.5
      }
    }
  ]
}
```

### Get Purchase Details
**GET** `/api/portfolio/purchase-details/{cryptoCurrencyId}`

Retrieves detailed purchase information for a specific cryptocurrency.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "purchases": [
    {
      "transaction_id": 123,
      "quantity": 0.001,
      "price": 45000.50,
      "amount": 45.00,
      "date": "2026-01-23T10:30:00.000000Z"
    }
  ]
}
```

**Error Responses:**
- `404`: Cryptocurrency not found

---

## Profile Endpoints

### Update Profile
**PUT** `/api/profile`

Updates user profile information.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+33123456789"
}
```

**Response (200):**
```json
{
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+33123456789"
  }
}
```

### Upload Profile Picture
**POST** `/api/profile/picture`

Uploads a profile picture.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
- `profile_picture`: Image file (jpeg, jpg, png, gif, webp, max 5MB)

**Response (200):**
```json
{
  "message": "Profile picture uploaded successfully",
  "path": "storage/profile_pictures/1/abc123.jpg",
  "url": "http://localhost/storage/profile_pictures/1/abc123.jpg",
  "user": { ... }
}
```

### Delete Profile Picture
**DELETE** `/api/profile/picture`

Deletes the user's profile picture.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Profile picture deleted successfully",
  "user": { ... }
}
```

### Upload Profile Banner
**POST** `/api/profile/banner`

Uploads a profile banner.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
- `profile_banner`: Image file (jpeg, jpg, png, gif, webp, max 5MB)

**Response (200):**
```json
{
  "message": "Profile banner uploaded successfully",
  "path": "storage/profile_banners/1/abc123.jpg",
  "url": "http://localhost/storage/profile_banners/1/abc123.jpg",
  "user": { ... }
}
```

### Delete Profile Banner
**DELETE** `/api/profile/banner`

Deletes the user's profile banner.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Profile banner deleted successfully",
  "user": { ... }
}
```

---

## Notification Endpoints

### Get Notifications
**GET** `/api/notifications`

Retrieves paginated notifications for the authenticated user.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)
- `unread_only` (optional): Filter only unread notifications (default: false)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "type": "profit",
      "title": "Portfolio Profit",
      "message": "Your BTC portfolio has gained 5%",
      "crypto_symbol": "BTC",
      "gain_loss": 10.50,
      "gain_loss_percent": 5.0,
      "is_read": false,
      "created_at": "2026-01-23T10:30:00.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 3,
  "per_page": 20,
  "total": 45
}
```

### Get Unread Count
**GET** `/api/notifications/unread-count`

Retrieves the count of unread notifications.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "count": 5
}
```

### Mark Notification as Read
**PUT** `/api/notifications/{id}/read`

Marks a specific notification as read.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Notification marked as read"
}
```

**Error Responses:**
- `404`: Notification not found

### Mark All Notifications as Read
**PUT** `/api/notifications/read-all`

Marks all notifications as read.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "5 notification(s) marked as read",
  "count": 5
}
```

### Delete Notification
**DELETE** `/api/notifications/{id}`

Deletes a specific notification.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Notification deleted"
}
```

**Error Responses:**
- `404`: Notification not found

---

## Admin Endpoints

### Get All Users
**GET** `/api/admin/users`

Retrieves all client users (Admin only).

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Response (200):**
```json
[
  {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "status": "active",
    "role": "client",
    "euro_balance": 500.00
  }
]
```

### Get User Details
**GET** `/api/admin/users/{id}`

Retrieves detailed information about a specific user (Admin only).

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Response (200):**
```json
{
  "user": { ... },
  "balance": 500.00,
  "portfolio": [ ... ],
  "statistics": {
    "total_transactions": 25,
    "buy_transactions": 15,
    "sell_transactions": 10,
    "total_volume": 1250.50,
    "total_portfolio_value": 600.00,
    "total_invested": 500.00,
    "total_gain_loss": 100.00,
    "total_gain_loss_percent": 20.0
  },
  "recent_transactions": [ ... ]
}
```

### Create User
**POST** `/api/admin/users`

Creates a new user account (Admin only).

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Request Body:**
```json
{
  "name": "Jane Doe",
  "email": "jane.doe@example.com"
}
```

**Response (201):**
```json
{
  "message": "User created successfully. A temporary password has been sent to the user's email. They must change it in their private area. For the prototype phase, the account is credited with €500.",
  "user": {
    "id": 2,
    "name": "Jane Doe",
    "email": "jane.doe@example.com",
    "status": "pending",
    "euro_balance": 500.00
  }
}
```

### Get Admin Transactions
**GET** `/api/admin/transactions`

Retrieves all transactions with filtering options (Admin only).

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Query Parameters:**
- `user_id` (optional): Filter by user ID
- `symbol` (optional): Filter by cryptocurrency symbol
- `type` (optional): Filter by transaction type (buy/sell)
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 50)

**Response (200):**
```json
{
  "data": [ ... ],
  "current_page": 1,
  "last_page": 10,
  "per_page": 50,
  "total": 500
}
```

### Get Admin Dashboard
**GET** `/api/admin/dashboard`

Retrieves dashboard statistics (Admin only).

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Response (200):**
```json
{
  "total_users": 150,
  "active_users": 120,
  "pending_users": 10,
  "blocked_users": 20,
  "total_transactions": 5000,
  "buy_transactions": 3000,
  "sell_transactions": 2000,
  "recent_transactions": [ ... ],
  "pending_users": [
    {
      "id": 5,
      "name": "New User",
      "email": "newuser@example.com",
      "submitDate": "2026-01-23T10:00:00.000000Z"
    }
  ]
}
```

---

## External APIs

### Coinbase API v2

**Base URL:** `https://api.coinbase.com/v2`

#### Get Spot Price
**GET** `/prices/{SYMBOL}-EUR/spot`

Retrieves the current spot price for a cryptocurrency in EUR.

**Supported Symbols:**
- BTC, ETH, XRP, BCH, ADA, LTC, XLM, DASH
- XEM (mapped to AVAX)
- MIOTA (mapped to AAVE)

**Example Request:**
```
GET https://api.coinbase.com/v2/prices/BTC-EUR/spot
```

**Response:**
```json
{
  "data": {
    "amount": "45000.50",
    "base": "BTC",
    "currency": "EUR"
  }
}
```

**Rate Limiting:**
- 150ms delay between requests
- 429 status code if rate limit exceeded

**Error Handling:**
- Returns `null` if cryptocurrency not supported
- Logs warnings for API errors
- Falls back to database if API unavailable

**Implementation:**
- Service: `App\Services\CoinbaseAPIService`
- Method: `getCryptoData(string $symbol): ?array`
- Method: `getMultipleCryptoData(array $symbols): array`

---

## Error Handling

### Standard Error Response Format

All errors follow this structure:

```json
{
  "message": "Error description",
  "error": "Detailed error message (optional)",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request (validation errors, business logic errors)
- `401` - Unauthorized (invalid credentials)
- `403` - Forbidden (insufficient permissions, account status)
- `404` - Not Found (resource doesn't exist)
- `422` - Unprocessable Entity (validation errors)
- `429` - Too Many Requests (rate limiting)
- `500` - Internal Server Error

### Common Error Messages

**Authentication Errors:**
- `"Invalid email or password"` - Login credentials incorrect
- `"Account awaiting admin validation"` - Account pending approval
- `"Account blocked"` - Account has been blocked
- `"Account not active"` - Account is not in active status

**Transaction Errors:**
- `"Insufficient balance. Available balance: X EUR."` - Not enough funds
- `"Insufficient quantity for sale. You only own X."` - Not enough cryptocurrency
- `"Price not available for this cryptocurrency. Please try again later."` - Price data unavailable
- `"Cryptocurrency not found or inactive."` - Invalid or inactive crypto

**Validation Errors:**
- `"Validation error"` - Request validation failed
- `"Cryptocurrency symbol is required."` - Missing required field
- `"Quantity must be greater than 0."` - Invalid quantity value

**Server Errors:**
- `"Database error. Please try again later."` - Database operation failed
- `"An error occurred. Please try again later."` - Generic server error
- `"Route not found"` - Endpoint doesn't exist

---

## Rate Limiting

### Internal API
- No explicit rate limiting implemented
- Recommended: Implement rate limiting for production

### External API (Coinbase)
- 150ms delay between requests
- Automatic retry with exponential backoff
- Graceful fallback to database on rate limit

---

## Pagination

Most list endpoints support pagination:

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default varies by endpoint)

**Response Format:**
```json
{
  "data": [ ... ],
  "current_page": 1,
  "last_page": 10,
  "per_page": 50,
  "total": 500
}
```

---

## Caching

### Redis Cache
- Cryptocurrency prices cached in Redis (< 5ms response time)
- Automatic fallback to database if Redis unavailable
- Cache invalidation on price updates

### HTTP Cache
- Transaction history cached for 2 minutes
- Portfolio data cached with user-specific keys
- Notification cache for recent items

---

## WebSocket / Real-time Updates

Currently not implemented. Future enhancement:
- Real-time price updates
- Live transaction notifications
- Portfolio value updates

---

## API Versioning

Current version: v1 (implicit)

Future versions should use URL versioning:
- `/api/v1/...`
- `/api/v2/...`

---

## Testing

### Postman Collection
A Postman collection is available at:
`bitchest-backend/BitChest_API.postman_collection.json`

### Test Endpoints
Use the test environment for development:
- Base URL: `http://localhost/api`
- Test users available via seeders

---

## Support

For API support or questions:
- Check error messages for detailed information
- Review logs: `storage/logs/laravel.log`
- Verify authentication token is valid
- Ensure required parameters are provided
