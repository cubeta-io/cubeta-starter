# BaseResource

## Introduction

The `BaseResource` class is a powerful extension of Laravel's `JsonResource` designed to standardize and enhance API
responses in your application. It provides a consistent structure for transforming models into JSON responses, with
additional features for customizing the output based on specific needs.

This class serves as the foundation for all resource classes in the application, ensuring that API responses follow a
consistent format and can be easily extended with additional data when needed.

## Key Features

### Properties

| Property     | Type  | Description                                                          |
|--------------|-------|----------------------------------------------------------------------|
| `$additions` | array | Stores additional data to be included in the resource response       |
| `$detailed`  | bool  | Flag to indicate if the resource should include detailed information |
| `$data`      | mixed | The underlying data being transformed                                |

### Methods

#### detailed()

Marks the resource as requiring detailed information.

```php
public function detailed(): static
{
    $this->detailed = true;
    return $this;
}
```

This method is useful when you want to include additional information in the resource that might not be needed in all
contexts. In your resource classes, you can check this flag to conditionally include more detailed data.

#### extra()

Adds extra data to the resource response.

```php
public function extra(array $extraData = []): static
{
    $this->additions = [...$this->additions, ...$extraData];
    return $this;
}
```

This method allows you to include additional data in the resource response that isn't directly related to the model's
attributes. This is useful for including metadata, related information, or computed values.

## How to Use BaseResource

### Creating a Resource Class

To create a resource class that extends `BaseResource`, follow these steps:

1. Create a new class that extends `BaseResource`
2. Implement the `toArray()` method to define how the model should be transformed
3. Optionally, use the `$detailed` flag to conditionally include more information

```php
<?php

namespace App\Http\Resources\v1;

use App\Http\Resources\BaseResource\BaseResource;
use App\Models\Category;
use Illuminate\Http\Request;

/** @mixin Category */
class CategoryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            // Basic attributes
            $this->when($this->detailed , fn() => [
                'description' => $this->description,
                'meta_description' => $this->meta_description,
            ])
        ];
        
        return $data;
    }
}
```

### Using Resources in Controllers

Resources are automatically used by the `ApiResponse` class when returning models or collections of models. The `rest()`
helper function creates an instance of `ApiResponse`, which then uses the appropriate resource class to transform the
data.

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

In this example, the `data()` method of `ApiResponse` automatically finds and uses the `CategoryResource` class to
transform the category model.

> [!WARNING]
> The `ApiResponse` class or the `rest()` helper function will use the Laravel name convention to guess the propper
> resource class, so when you need to use a custom resource class within the response, you need to provide the resource
> to the `data` method instead of just providing the model or the models collection
> this can be done like this : ` return rest()->data(CustomeCategoryResource::make($category))->ok()->send() ` **OR**
> like this : ` return rest()->data(CustomeCategoryResource::collection($categories))->ok()->send() `

### Adding Extra Data to Resources

You can add extra data to a resource using the `extra()` method:

```php
CategoryResource::make($category)->extra(['related_count' => $products->count]);
CategoryResource::collection($categories)->extra(['related_count' => $products->count]);
```

### Using Detailed Resources

You can mark a resource as detailed using the `detailed()` method:

```php
CategoryResource::make($category)->detailed();
CategoryResource::collection($categories)->detailed();
```
## Benefits of Using BaseResource

### 1. Consistent API Response Format

By using `BaseResource` as the base class for all your resources, you ensure that all API responses follow a consistent
format. This makes it easier for clients to consume your API and reduces the likelihood of bugs caused by inconsistent
response structures.

### 2. Automatic Resource Resolution

The `ApiResponse` class automatically finds and uses the appropriate resource class based on the model's class name.
This reduces boilerplate code in your controllers and ensures that all models are transformed consistently.

### 3. Flexible Response Customization

The `detailed()` and `extra()` methods provide flexible ways to customize the response based on the specific needs of
each request. This allows you to reuse the same resource class for different contexts without duplicating code.

### 4. Improved Code Organization

By centralizing the transformation logic in resource classes, you keep your controllers clean and focused on handling
the request and response flow. This improves code organization and makes your application easier to maintain.