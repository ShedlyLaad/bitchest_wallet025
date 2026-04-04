# Technologies Used in BitChest Project

## Overview
BitChest is a cryptocurrency trading platform built with a modern full-stack architecture. This document provides a comprehensive overview of all technologies, frameworks, libraries, and tools used in the project.

## Backend Technologies

### Core Framework
- **Laravel 10.10** - PHP web framework providing MVC architecture, routing, authentication, and database abstraction
- **PHP 8.1+** - Server-side scripting language

### Database & Caching
- **MySQL/MariaDB** - Relational database for persistent data storage
- **Redis** - In-memory data structure store used for:
  - Caching cryptocurrency prices (< 5ms response time)
  - Session management
  - Queue management
  - Real-time data caching

### Authentication & Security
- **Laravel Sanctum 3.3** - API token authentication system
- **Hash** - Password hashing and verification
- **Middleware** - Request authentication and authorization

### HTTP Client
- **Guzzle HTTP 7.2** - HTTP client library for making API requests to external services

### Queue System
- **Redis Queue** - Asynchronous job processing for:
  - Transaction processing
  - Notification delivery
  - Email sending

### Testing
- **PHPUnit 10.1** - PHP testing framework
- **Mockery 1.4.4** - Mocking framework for unit tests
- **FakerPHP 1.9.1** - Fake data generator for testing

### Development Tools
- **Laravel Pint 1.0** - Code style fixer
- **Laravel Tinker 2.8** - REPL for Laravel
- **Laravel Sail 1.18** - Docker development environment

## Frontend Technologies

### Core Framework
- **Vue.js 3.5.26** - Progressive JavaScript framework
- **TypeScript 5.9.3** - Typed superset of JavaScript
- **Vite 5.4.21** - Next-generation frontend build tool

### State Management
- **Pinia 2.2.6** - Vue state management library

### Routing
- **Vue Router 4.6.3** - Official router for Vue.js

### UI & Styling
- **Tailwind CSS 3.4.18** - Utility-first CSS framework
- **PostCSS 8.5.6** - CSS post-processor
- **Autoprefixer 10.4.22** - CSS vendor prefixer

### Data Visualization
- **ApexCharts 5.3.6** - Modern charting library
- **Vue3-ApexCharts 1.10.0** - Vue wrapper for ApexCharts

### HTTP Client
- **Axios 1.7.7** - Promise-based HTTP client for API requests

### 3D Graphics
- **Three.js 0.161.0** - 3D graphics library for WebGL

### Animations
- **@motionone/vue 10.16.4** - Animation library for Vue
- **@vueuse/motion 3.0.3** - Motion utilities for VueUse
- **@vueuse/core 14.0.0** - Collection of Vue composition utilities

### Icons
- **Lucide Vue Next 0.553.0** - Icon library

### Development Tools
- **ESLint 9.9.1** - JavaScript linter
- **TypeScript ESLint Parser 8.3.0** - TypeScript parser for ESLint
- **@vitejs/plugin-vue 5.2.4** - Vue plugin for Vite

## External Services & APIs

### Cryptocurrency Data
- **Coinbase API v2** - Real-time cryptocurrency price data
  - Endpoint: `https://api.coinbase.com/v2/prices/{SYMBOL}-EUR/spot`
  - Used for fetching current prices
  - Rate limiting: 150ms delay between requests

### Email Services
- **Universal Mail Service** - Configurable email service supporting multiple providers (SMTP, Mailgun, SendGrid, etc.)

## Architecture Patterns

### Backend Architecture
- **MVC (Model-View-Controller)** - Laravel's default architecture
- **Service Layer Pattern** - Business logic separated into service classes:
  - `TransactionService` - Transaction processing
  - `PortfolioService` - Portfolio management
  - `CryptoService` - Cryptocurrency data management
  - `NotificationService` - Notification handling
  - `RedisPriceService` - Price caching
  - `CoinbaseAPIService` - External API integration

### Frontend Architecture
- **Component-Based Architecture** - Vue.js components
- **Composition API** - Vue 3 composition API for reactive logic
- **State Management** - Centralized state with Pinia stores

### Caching Strategy
- **Multi-Layer Caching**:
  1. Redis cache for ultra-fast price retrieval
  2. Database fallback if Redis is unavailable
  3. HTTP response caching for API endpoints

### Database Design
- **Relational Database** with proper indexing
- **Migrations** for version control
- **Factories & Seeders** for testing and development

## Development Environment

### Server Requirements
- **XAMPP** - Local development server (Apache, MySQL, PHP)
- **Docker** (optional) - Containerized development with Laravel Sail

### Version Control
- **Git** - Version control system

### Package Management
- **Composer** - PHP dependency manager
- **npm** - Node.js package manager

## Performance Optimizations

### Backend
- Redis caching for price data
- Database query optimization with indexes
- Eager loading to prevent N+1 queries
- Queue system for asynchronous processing

### Frontend
- Code splitting with Vue Router
- Lazy loading of components
- Vite build optimization
- Asset compression and minification

## Security Features

- **Authentication** - Token-based authentication with Sanctum
- **Authorization** - Role-based access control (Admin/Client)
- **Password Hashing** - Secure password storage
- **CSRF Protection** - Cross-site request forgery protection
- **Input Validation** - Request validation and sanitization
- **SQL Injection Prevention** - Eloquent ORM with parameter binding

## Testing Technologies

### Backend Testing
- **PHPUnit** - Unit and feature tests
- **Database Factories** - Test data generation
- **Mockery** - Mock objects for isolated testing

### Test Coverage
- Unit tests for services
- Feature tests for API endpoints
- Model tests for data integrity

## Deployment & DevOps

### Build Tools
- **Vite** - Frontend build tool
- **Laravel Mix** - Asset compilation (if used)

### Process Management
- **Queue Workers** - Background job processing
- **Cron Jobs** - Scheduled tasks (price updates, notifications)

## Monitoring & Logging

- **Laravel Logging** - Built-in logging system
- **Error Handling** - Custom exception handler
- **Debug Mode** - Development debugging tools

## Project Structure

### Backend Structure
```
bitchest-backend/
├── app/
│   ├── Console/Commands/    # Artisan commands
│   ├── Http/Controllers/    # Request handlers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   ├── Jobs/                # Queue jobs
│   ├── Events/              # Event classes
│   ├── Listeners/           # Event listeners
│   └── Notifications/       # Notification classes
├── database/
│   ├── migrations/          # Database migrations
│   ├── seeders/             # Database seeders
│   └── factories/           # Model factories
└── routes/                   # Route definitions
```

### Frontend Structure
```
bitchest-frontend/
├── src/
│   ├── admin/               # Admin panel components
│   ├── components/          # Reusable components
│   ├── pages/               # Page components
│   ├── services/            # API services
│   ├── stores/              # Pinia stores
│   ├── router/              # Route configuration
│   └── types/               # TypeScript types
└── public/                   # Static assets
```

## Summary

BitChest leverages modern web technologies to create a high-performance cryptocurrency trading platform. The backend uses Laravel for robust API development, Redis for caching, and MySQL for data persistence. The frontend utilizes Vue.js 3 with TypeScript for type safety and modern development practices. The architecture emphasizes performance through caching, scalability through queues, and maintainability through clean code organization.
