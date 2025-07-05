# ApiResponse

## Introduction

The `ApiResponse` class is a powerful utility designed to standardize and simplify API responses in Laravel
applications. It provides a consistent structure for JSON responses, with support for various HTTP status codes, custom
messages, and automatic resource transformation.

The `rest()` helper function provides a convenient way to create instances of the `ApiResponse` class without having to
use the full class name, making your controller code cleaner and more readable.

Together, they form a robust system for handling API responses in a consistent and maintainable way.

## Key Features

### Properties

| Property          | Type        | Description                                 |
|-------------------|-------------|---------------------------------------------|
| `$code`           | int         | The HTTP status code for the response       |
| `$message`        | string      | The message to include in the response      |
| `$data`           | mixed       | The data to include in the response         |
| `$paginationData` | array\|null | Pagination metadata for paginated responses |

### HTTP Status Methods

The `ApiResponse` class provides methods for setting various HTTP status codes:

| Method              | HTTP Status | Description                                       |
|---------------------|-------------|---------------------------------------------------|
| `ok()`              | 200         | Sets the status code to 200 OK                    |
| `unknown()`         | 500         | Sets the status code to 500 Internal Server Error |
| `notFound()`        | 404         | Sets the status code to 404 Not Found             |
| `badRequest()`      | 400         | Sets the status code to 400 Bad Request           |
| `forbidden()`       | 403         | Sets the status code to 403 Forbidden             |
| `notAuthorized()`   | 401         | Sets the status code to 401 Unauthorized          |
| `validationError()` | 422         | Sets the status code to 422 Unprocessable Entity  |
| `tokenExpiration()` | 406         | Sets the status code to 406 Not Acceptable        |
| `unverifiedEmail()` | 407         | Sets the status code to 407 (custom)              |

### Data and Message Methods

| Method                                  | Description                                                                                                    |
|-----------------------------------------|----------------------------------------------------------------------------------------------------------------|
| `data(mixed $data = null)`              | Sets the data to include in the response, with automatic resource transformation for models and paginated data |
| `message(string\|null $message = null)` | Sets the message to include in the response                                                                    |
| `noData(mixed $data = null)`            | Sets the data to null, sets a "no data" message, and sets the status code to 404                               |
| `paginationData(array $paginationData)` | Sets the pagination metadata for paginated responses                                                           |

### Success Message Methods

| Method            | Description                          |
|-------------------|--------------------------------------|
| `getSuccess()`    | Sets a "get successfully" message    |
| `storeSuccess()`  | Sets a "stored successfully" message |
| `updateSuccess()` | Sets a "update successfully" message |
| `deleteSuccess()` | Sets a "delete successfully" message |

### Response Methods

| Method                                                      | Description                           |
|-------------------------------------------------------------|---------------------------------------|
| `send()`                                                    | Sends the response as a JsonResponse  |
| `paginatedSuccessfully(mixed $data, array $paginationData)` | Sends a successful paginated response |
| `createdSuccessfully(mixed $data = null)`                   | Sends a successful creation response  |
| `getSuccessfully(mixed $data = null)`                       | Sends a successful get response       |
| `updatedSuccessfully(mixed $data = null)`                   | Sends a successful update response    |
| `deleteSuccessfully(mixed $data = true)`                    | Sends a successful delete response    |

### Utility Methods

| Method                                                   | Description                                           |
|----------------------------------------------------------|-------------------------------------------------------|
| `create()`                                               | Creates a new instance of the ApiResponse class       |
| `when($condition, Closure $then, ?Closure $else = null)` | Conditionally applies transformations to the response |
| `formatPaginateData(LengthAwarePaginator $data)`         | Formats pagination data for paginated responses       |

## The rest() Helper Function

The `rest()` helper function is a simple utility that creates a new instance of the `ApiResponse` class:

```php
function rest()
{
    return ApiResponse::create();
}
```

This function is defined in `app/Helpers/helpers.php` and is automatically loaded by Laravel. It provides a convenient
way to create instances of the `ApiResponse` class without having to use the full class name.

## How to Use ApiResponse and rest()

### Basic Usage

The most basic usage is to return a simple success response:

```php
return rest()
    ->ok()
    ->message('Operation completed successfully')
    ->send();
```

### Returning Data

You can return data in the response:

```php
return rest()
    ->ok()
    ->data($user)
    ->message('User retrieved successfully')
    ->send();
```

### Automatic Resource Transformation

When returning models or collections of models, the `data()` method automatically applies the appropriate resource
transformation:

```php
$category = \App\Models\Category::first();
$categories = \App\Models\Category::all();
// Returns a transformed Category model using CategoryResource
return rest()
    ->ok()
    ->data($category)
    ->send();

// Returns a transformed collection of Category models using CategoryResource
return rest()
    ->ok()
    ->data($categories)
    ->send();
```

> [!WARNING]
> The `ApiResponse` class or the `rest()` helper function will use the Laravel name convention to guess the propper
> resource class, so when you need to use a custom resource class within the response, you need to provide the resource
> to the `data` method instead of just providing the model or the models collection
> this can be done like below :

```php
 return rest()->data(CustomeCategoryResource::make($category))->ok()->send()
```

**OR**

```php
 return rest()->data(CustomeCategoryResource::collection($categories))->ok()->send()
```

### Handling Paginated Data

When returning paginated data, the `data()` method automatically handles pagination:

```php
$categories = Category::paginate(10);

return rest()
    ->ok()
    ->data($categories)
    ->send();
```

### Conditional Responses

The `when()` method allows you to conditionally apply transformations to the response:

```php
return rest()
    ->when(
        $item,
        fn ($rest) => $rest->getSuccess()->data($item),
        fn ($rest) => $rest->noData()
    )->send();
```

### Convenience Methods

The `ApiResponse` class provides convenience methods for common response patterns:

```php
// Get success
return rest()->getSuccessfully($data);

// Create success
return rest()->createdSuccessfully($data);

// Update success
return rest()->updatedSuccessfully($data);

// Delete success
return rest()->deleteSuccessfully();
```

### Error Responses

You can return error responses with appropriate status codes:

```php
// Not found
return rest()->noData()->send();

// Unauthorized
return rest()->notAuthorized()->send();

// Validation error
return rest()->validationError()->message('Validation failed')->send();
```

## Usage Examples

### Index Method (List Resources)

```php
public function index()
{
    $items = $this->categoryService->indexWithPagination($this->relations);

    return rest()
        ->ok()
        ->getSuccess()
        ->data($items)
        ->send();
}
```

### Show Method (Get a Resource)

```php
public function show($categoryId)
{
    $item = $this->categoryService->view($categoryId, $this->relations);

    return rest()
        ->when(
            $item,
            fn ($rest) => $rest->getSuccess()->data($item),
            fn ($rest) => $rest->noData()
        )->send();
}
```

### Store Method (Create a Resource)

```php
public function store(StoreUpdateCategoryRequest $request)
{
    $item = $this->categoryService->store($request->validated(), $this->relations);

    return rest()
        ->when(
            $item,
            fn ($rest) => $rest->storeSuccess()->data($item),
            fn ($rest) => $rest->noData()
        )->send();
}
```

### Update Method (Update a Resource)

```php
public function update($categoryId, StoreUpdateCategoryRequest $request)
{
    $item = $this->categoryService->update($request->validated(), $categoryId, $this->relations);

    return rest()
        ->when(
            $item,
            fn ($rest) => $rest->updateSuccess()->data($item),
            fn ($rest) => $rest->noData()
        )->send();
}
```

### Destroy Method (Delete a Resource)

```php
public function destroy($categoryId)
{
    $item = $this->categoryService->delete($categoryId);

    return rest()
        ->when(
            $item,
            fn ($rest) => $rest->ok()->deleteSuccess(),
            fn ($rest) => $rest->noData(),
        )->send();
}
```

### Error Handling in Exception Handler

```php
public function render($request, Throwable $e)
{
    if ($e instanceof ModelNotFoundException) {
        return rest()->noData()->send();
    }

    if ($e instanceof AuthenticationException) {
        return rest()->notAuthorized()->send();
    }

    // Other exception handling...
}
```

## Benefits of Using ApiResponse and rest()

### 1. Consistent API Response Format

By using `ApiResponse` and the `rest()` helper function, you ensure that all API responses follow a consistent format.
This makes it easier for clients to consume your API and reduces the likelihood of bugs caused by inconsistent response
structures.

### 2. Automatic Resource Transformation

The `data()` method automatically applies the appropriate resource transformation based on the model's class name. This
reduces boilerplate code in your controllers and ensures that all models are transformed consistently.

### 3. Fluent Interface

The fluent interface of `ApiResponse` allows you to chain methods together to build the response. This makes your code
more readable and expressive.

### 4. Conditional Responses

The `when()` method provides a clean way to conditionally apply transformations to the response. This reduces the need
for if/else statements in your controllers.

### 5. Centralized Response Handling

By centralizing response handling in the `ApiResponse` class, you can make changes to the response format in one place,
rather than having to update every controller.

### 6. Improved Error Handling

The `ApiResponse` class provides methods for handling various error conditions, such as not found, unauthorized, and
validation errors. This makes it easier to return appropriate error responses.

### 7. Simplified Controller Code

The `rest()` helper function and the convenience methods of `ApiResponse` simplify your controller code, making it more
readable and maintainable.

## Best Practices

1. **Use the rest() Helper**: Always use the `rest()` helper function instead of creating `ApiResponse` instances
   directly.

2. **Chain Methods**: Take advantage of the fluent interface to chain methods together for building responses.

3. **Use Conditional Responses**: Use the `when()` method for conditional responses instead of if/else statements.

4. **Set Appropriate Status Codes**: Always set the appropriate HTTP status code for the response using the status
   methods.

5. **Use Success Message Methods**: Use the success message methods (`getSuccess()`, `storeSuccess()`, etc.) to set
   appropriate success messages.

6. **Handle Errors Consistently**: Use the error methods (`noData()`, `notAuthorized()`, etc.) to handle errors
   consistently.
