# Features

Cubeta Starter provides a comprehensive set of features to accelerate your Laravel development. This guide explores the key features and how to use them effectively.

## Code Generation

### Model Generation

Cubeta Starter can generate complete models with all necessary files:

```bash
php artisan create:model Product
```

This command will interactively prompt you for:
- Model properties (table columns)
- Data types for each property
- Nullable and unique constraints
- Relationships with other models

The generated model includes:
- Proper attribute casting
- Relationship methods
- Searchable columns configuration
- File handling setup

### Complete CRUD Generation

With a single command, generate a complete CRUD implementation:

```bash
php artisan create:model Product
```

This generates:
- Model class
- Migration file
- Factory for testing
- Database seeder
- Form request validation
- API resource
- Controller with CRUD methods
- Repository class
- Service class
- Feature test
- Routes registration
- Postman collection

### Frontend Integration

Generate frontend components based on your selected stack:

#### Blade/Bootstrap

```bash
php artisan create:model Product --web_controller
```

This creates:
- Web controller with CRUD methods
- Blade views for listing, creating, editing, and viewing
- Form components with validation
- JavaScript for interactive features

#### Inertia/React/TypeScript

```bash
php artisan create:model Product --web_controller
```

With the React stack selected, this generates:
- Web controller for Inertia
- React components for CRUD operations
- TypeScript interfaces
- Form validation
- API integration

## Design Patterns

### Repository Pattern

Cubeta Starter implements the Repository pattern to separate data access logic:

- Each model gets its own repository class
- The `BaseRepository` provides common CRUD operations
- Custom query methods can be added to model-specific repositories
- Repositories handle database operations, file storage, and query optimization

Example repository usage:
```php
// Injected through dependency injection
public function __construct(ProductRepository $productRepository)
{
    $this->productRepository = $productRepository;
}

// Using the repository
$products = $this->productRepository->all();
$product = $this->productRepository->find($id);
$this->productRepository->create($data);
```

### Service Pattern

Services encapsulate business logic:

- Each model gets its own service class
- Services use repositories for data access
- Complex operations are handled in services
- Controllers remain thin by delegating to services

Example service usage:
```php
// Injected through dependency injection
public function __construct(ProductService $productService)
{
    $this->productService = $productService;
}

// Using the service
$result = $this->productService->createProduct($request->validated());
```

## Authentication & Authorization

### Multi-Actor Authentication

Support for multiple user types (actors):

```bash
php artisan create:actor Admin
php artisan create:actor Customer
```

This creates:
- Role definitions
- Authentication controllers for each actor
- Separate login/registration flows
- Role-based middleware

### Permissions System

Built-in roles and permissions system:

```bash
php artisan cubeta:install permissions
```

Features:
- Role management
- Permission assignment
- Policy enforcement
- Middleware for route protection

## Localization Support

### Translatable Model Attributes

Support for translatable model attributes:

```php
// In migration
$table->json('name'); // Will be handled as translatable

// In form request
'name' => ['required', 'json', new ValidTranslatableJson]

// In model
protected $casts = [
    'name' => Translatable::class,
];

// Usage
$product->name->en; // English name
$product->name->fr; // French name
```

### Locale Middleware

Automatic locale detection and switching:

- Detects user's preferred language
- Switches application locale based on request
- Supports API and web interfaces

## API Development

### Standardized API Responses

Consistent API response format:

```php
return rest()->ok()->data($data)->message('Success')->send();
return rest()->created()->data($resource)->message('Created successfully')->send();
return rest()->notFound()->message('Resource not found')->send();
```

### API Resources

Automatic generation of API resources:

- Consistent JSON structure
- Relationship handling
- Data transformation

### Postman Collection Generation

Automatic Postman collection generation:

- Collection for each model
- Endpoints for all CRUD operations
- Environment variables
- Authentication requests

## Testing Support

### Automated Test Generation

Generate tests for your models:

```bash
php artisan create:test ProductTest
```

Features:
- Tests for all CRUD operations
- Authentication testing
- Validation testing
- Database assertions

### MainTestCase Base Class

Base test class with helpful methods:

- Authentication helpers
- Request helpers
- Response assertions
- Database helpers

## GUI Interface

### Web-Based Code Generation

Access the GUI at `/cubeta-starter` in your browser:

- Visual model creation
- Field type selection
- Relationship configuration
- Code generation options

## Customization

### Configuration Options

Extensive configuration options in `config/cubeta-starter.php`:

- Directory and namespace customization
- Project settings
- Localization options
- Version control

### Template Customization

Customize the generated code by publishing templates:

```bash
php artisan vendor:publish --tag=cubeta-starter-templates
```

This allows you to modify how code is generated while still using the package's automation features.

## Conclusion

Cubeta Starter provides a comprehensive set of features to accelerate your Laravel development. By automating repetitive tasks and implementing best practices, it allows you to focus on building your application's unique features rather than boilerplate code.