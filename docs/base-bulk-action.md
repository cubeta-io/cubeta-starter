# BaseBulkAction

## Overview

The `BaseBulkAction` class is designed to handle bulk actions on Eloquent models or query builders. It provides a
flexible way to perform actions like delete on multiple records at once. This class is intended to be extended by
concrete bulk action classes for specific models or queries.

### Properties

- **protected Builder|QueryBuilder|Model $query**: The query or model instance to perform bulk actions on.
- **protected int $chunkSize**: The number of records to process in each chunk (default: 10).
- **protected string $selectableColumn**: The column used to select records for bulk actions (default: 'id').
- **protected string $actionKey**: The key used in the request to identify the bulk action (default: 'bulk-action').
- **protected Request $request**: The current HTTP request instance.
- **protected array $actions**: An array of defined bulk actions, each with a name, method, and validation rules.

### Constructor

- **public function __construct(Builder|QueryBuilder $query, Request $request)**
    - Initializes the bulk action with a query or model instance and the current request.
---

## Methods

### run

```php
public function run(): bool
```

Executes the bulk action based on the request parameters.

- **Returns:** `bool` — `true` if the action was executed, `false` otherwise.

---

### getMethodName

```php
protected function getMethodName(): ?string
```

Retrieves the method name for the bulk action based on the request.

- **Returns:** `string|null` — The method name or `null` if the action is invalid.

---

### getSelected

```php
protected function getSelected(): array
```

Retrieves the selected record IDs from the request.

- **Returns:** `array` — Array of selected record IDs.

---

### validateRequestData

```php
protected function validateRequestData(array $rules = []): true
```

Validates the request data against the specified rules.

- **Parameters:**
    - `$rules`: Array of validation rules.
- **Returns:** `true` if validation passes.

---

### delete

```php
protected function delete($item): void
```

Deletes a single item (demonstration method).

- **Parameters:**
    - `$item`: The item to delete.

---

## Usage Example

To use `BaseBulkAction`, extend it in your own bulk action class and specify the query or model:

```php
use App\BulkAction\BaseBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserBulkAction extends BaseBulkAction
{
    public function __construct(Builder $query, Request $request)
    {
        parent::__construct($query, $request);
    }

    // Define custom bulk actions here
}
```

---

## Notes

- This class is designed to be extended for specific bulk actions.
- Define custom bulk actions by adding entries to the `$actions` array.
- Override the `delete` method or add new methods to implement specific bulk actions. 