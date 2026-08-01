# Quickstart Guide

Get up and running with Cubeta Starter in under 5 minutes.

## Prerequisites

- PHP 8.3+
- Laravel 13+
- Composer

## Installation

Install the package via Composer:

```bash
composer require cubeta/cubeta-starter
```

## Setup

Choose your stack and run the installer:

### For API Development

```bash
php artisan cubeta:install api
```

This installs:
- API route structure
- Base classes (Repository, Service, Resource, ApiResponse)
- Middleware for localization
- Helper functions

### For Web Development (Blade)

```bash
php artisan cubeta:install web
```

Or for React + TypeScript with Inertia.js:

```bash
php artisan cubeta:install react-ts
php artisan cubeta:install react-ts-packages
```

### Optional: Authentication & Permissions

```bash
php artisan cubeta:install auth
php artisan cubeta:install permissions
```

## Generate Your First Model

The fastest way to see Cubeta Starter in action is to generate a complete CRUD resource:

```bash
php artisan create:model Product
```

Follow the interactive prompts to define your model attributes. For example:

```
name:string
description:text
price:double
stock:integer
is_available:boolean
```

This single command generates:
- ✅ Model class with casts, searchable arrays, and scopes
- ✅ Migration file
- ✅ Factory with realistic fake data
- ✅ Seeder
- ✅ Repository with filtering, search, and pagination
- ✅ Service layer for business logic
- ✅ API Controller with index/show/store/update/destroy
- ✅ Form Request with validation rules
- ✅ JSON Resource for API responses
- ✅ Feature test suite
- ✅ API routes (registered in `routes/v1/api/`)
- ✅ Postman collection (optional)

## Run Migrations and Seed Data

```bash
php artisan migrate
php artisan db:seed ProductSeeder
```

## Test Your API

Start your development server:

```bash
php artisan serve
```

Test the generated endpoints:

```bash
# List products (with pagination, search, filtering)
GET http://localhost:8000/api/v1/products

# Show a product
GET http://localhost:8000/api/v1/products/1

# Create a product
POST http://localhost:8000/api/v1/products
Content-Type: application/json

{
  "name": "Laptop",
  "description": "High-performance laptop",
  "price": 999.99,
  "stock": 50,
  "is_available": true
}

# Update a product
PUT http://localhost:8000/api/v1/products/1

# Delete a product
DELETE http://localhost:8000/api/v1/products/1
```

## Next Steps

Now that you have a working CRUD resource, explore:

- **[Configuration](configuration.md)** — Customize paths, namespaces, and locales
- **[Usage Guide](usage.md)** — Learn all the options for `create:model` and other commands
- **[BaseRepository](base-repository.md)** — Built-in filtering, search, and query methods
- **[ApiResponse](api-response.md)** — Standardized JSON response formatting
- **[Testing](main-test.md)** — Write and extend feature tests
- **[Permissions](permissions-usage.md)** — Role-based access control

## Tips for Success

> [!TIP]
> **Track your changes with Git** — Cubeta Starter generates many files at once. Commit before generating and review the diff after.

> [!TIP]
> **Customize after generation** — The generated files are a starting point. Review and adjust validation rules, factory data, and test cases to match your domain.

> [!TIP]
> **Use the interactive prompts** — The package asks intelligent questions. For example, it auto-detects foreign keys from `_id` suffixes and suggests relationships.

> [!NOTE]
> After the first generation, a `cubeta-starter.config.json` file is created at your project root. This tracks your generated tables and settings — keep it under version control and avoid editing it by hand.
