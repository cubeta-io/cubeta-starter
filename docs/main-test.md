# MainTestCase class

## Introduction

The `MainTestCase` class is a powerful testing utility designed to simplify and standardize API testing in Laravel applications. It extends Laravel's `TestCase` and incorporates the `TestHelpers` trait to provide a comprehensive set of methods for testing CRUD operations.

This class is particularly useful for testing RESTful APIs, as it provides methods for testing index, show, store, update, and delete operations with minimal code duplication.

## Class Overview

```php
namespace Tests\Contracts;

use App\Models\User;
use App\Traits\TestHelpers;
use Tests\TestCase;

class MainTestCase extends TestCase
{
    use TestHelpers;
    
    // Methods for testing CRUD operations
}
```

## Required Properties

When extending the `MainTestCase` class, you should define the following properties in your test class:

| Property | Type | Description |
|----------|------|-------------|
| `$model` | string | The fully qualified class name of the model being tested |
| `$resource` | string | The fully qualified class name of the resource being tested |
| `$userType` | string | The type of user to authenticate as (e.g., 'admin', 'user', 'none') |
| `$baseUrl` | string | The base URL for the routes being tested |
| `$relations` | array | An array of relations to load with the model |

## Available Methods

### deleteTest

Tests the deletion of a resource.

```php
public function deleteTest(array $additionalFactoryData = [], bool $ownership = false): static
```

#### Parameters:
- `$additionalFactoryData` (array): Optional data to pass to the model factory
- `$ownership` (bool): Determines if the action has to be on the authenticated user's data

#### What it tests:
1. Attempting to delete a resource with an invalid ID
2. If `$ownership` is true, attempting to delete another user's resource
3. Successfully deleting a resource
4. Verifying the resource is deleted (soft or hard delete)

### indexTest

Tests the listing/index functionality of resources.

```php
public function indexTest(array $additionalFactoryData = [], bool $ownership = false): static
```

#### Parameters:
- `$additionalFactoryData` (array): Optional data to pass to the model factory
- `$ownership` (bool): Determines if the action has to be on the authenticated user's data

#### What it tests:
1. Requesting the index when no resources exist
2. If `$ownership` is true, attempting to list another user's resources
3. Successfully listing resources
4. Verifying pagination and resource transformation

### showTest

Tests the retrieval of a single resource.

```php
public function showTest(array $additionalFactoryData = [], bool $ownership = false): static
```

#### Parameters:
- `$additionalFactoryData` (array): Optional data to pass to the model factory
- `$ownership` (bool): Determines if the action has to be on the authenticated user's data

#### What it tests:
1. Attempting to show a resource with an invalid ID
2. If `$ownership` is true, attempting to show another user's resource
3. Successfully showing a resource
4. Verifying resource transformation

### storeTest

Tests the creation of a new resource.

```php
public function storeTest(array $additionalAttributes = [], mixed $requestParams = null): static
```

#### Parameters:
- `$additionalAttributes` (array): Optional data to pass to the model factory
- `$requestParams` (mixed): Optional parameters to include in the request URL

#### What it tests:
1. Successfully creating a resource with the provided attributes
2. Verifying the resource is created in the database
3. Verifying resource transformation

### updateTest

Tests the updating of an existing resource.

```php
public function updateTest(array $attributes = [], array $additionalFactoryData = [], bool $ownership = false): static
```

#### Parameters:
- `$attributes` (array): Data to use for the update request
- `$additionalFactoryData` (array): Optional data to pass to the model factory
- `$ownership` (bool): Determines if the action has to be on the authenticated user's data

#### What it tests:
1. Attempting to update a resource with an invalid ID
2. If `$ownership` is true, attempting to update another user's resource
3. Successfully updating a resource
4. Verifying the resource is updated in the database
5. Verifying resource transformation

## TestHelpers Trait

The `MainTestCase` class uses the `TestHelpers` trait, which provides numerous helper methods for testing. Some of the most commonly used methods include:

### Authentication Methods
- `login(string $email, string $password)`: Logs in a user with the given credentials
- `signIn($type)`: Signs in a user of the specified type

### Response Assertion Methods
- `statusOk()`: Asserts that the response has a 200 status code
- `statusNotFound()`: Asserts that the response has a 404 status code
- `statusBadRequest()`: Asserts that the response has a 400 status code
- `statusForbidden()`: Asserts that the response has a 403 status code
- `statusNotAuthorized()`: Asserts that the response has a 401 status code
- `statusValidationError()`: Asserts that the response has a 422 status code

### Data Preparation Methods
- `dataResource(mixed $data)`: Sets the expected data resource
- `data(mixed $data)`: Sets the expected data
- `noData()`: Sets the expected data to null

### Success State Helpers
- `getSuccess()`: Sets up expectations for a successful GET request
- `storeSuccess()`: Sets up expectations for a successful POST request
- `updateSuccess()`: Sets up expectations for a successful PUT request
- `deleteSuccess()`: Sets up expectations for a successful DELETE request

### Pagination Helpers
- `paginate(int $total)`: Sets up expectations for paginated data
- `emptyPagination()`: Sets up expectations for empty paginated data
- `unPaginate()`: Sets up expectations for unpaginated data

### Other Helpers
- `requestPathHook(string $routeName)`: Sets the request path for the test
- `multiple()`: Indicates that multiple resources are expected
- `single()`: Indicates that a single resource is expected
- `message(string $message)`: Sets the expected message in the response

## Usage Example

Here's an example of how to use the `MainTestCase` class to test a Category model:

```php
<?php

namespace Tests\Feature;

use App\Http\Resources\v1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Tests\Contracts\MainTestCase;

class CategoryTest extends MainTestCase
{
    /** @var class-string<Category> */
    protected string $model = Category::class;

    /** @var class-string<CategoryResource> */
    protected string $resource = CategoryResource::class;

    protected string $userType = 'none';

    protected string $baseUrl = 'v1.api.public.categories.';

    protected array $relations = [];

    public function test_user_can_index_category()
    {
        $this->requestPathHook($this->baseUrl.'index');
        $this->indexTest();
    }

    public function test_user_can_show_a_category()
    {
        $this->requestPathHook($this->baseUrl.'show');
        $this->showTest();
    }

    public function test_user_can_create_a_category()
    {
        $this->requestPathHook($this->baseUrl.'store');
        $this->storeTest([
            'launch_date' => now()->format('Y-m-d'),
            'daily_update_time' => now()->format('H:i'),
            'last_promotion_date' => now()->format('Y-m-d H:i'),
            'last_accessed_at' => now()->format('Y-m-d H:i'),
            'image' => UploadedFile::fake()->image('image.png'),
        ]);
    }

    public function test_user_can_update_category()
    {
        $this->requestPathHook($this->baseUrl.'update');
        $this->updateTest([
            'launch_date' => now()->format('Y-m-d'),
            'daily_update_time' => now()->format('H:i'),
            'last_promotion_date' => now()->format('Y-m-d H:i'),
            'last_accessed_at' => now()->format('Y-m-d H:i'),
            'image' => UploadedFile::fake()->image('image.png'),
        ]);
    }

    public function test_user_can_delete_a_category()
    {
        $this->requestPathHook($this->baseUrl.'destroy');
        $this->deleteTest();
    }
}
```

## Best Practices

1. **Define Required Properties**: Always define the required properties (`$model`, `$resource`, `$userType`, `$baseUrl`, `$relations`) in your test class.

2. **Use requestPathHook**: Always call `requestPathHook()` before calling any of the test methods to set the correct route.

3. **Customize Factory Data**: Pass additional factory data to the test methods when needed to test specific scenarios.