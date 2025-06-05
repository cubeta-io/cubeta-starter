# BaseRepository

## Overview

The `BaseRepository` class is an abstract repository layer class designed to provide a standardized set of data access
operations for Eloquent models. It encapsulates database interactions and provides methods for CRUD operations,
filtering, searching, and data import/export.

This class is intended to be extended by concrete repository classes for specific models. It relies on a model class (
specified by `$modelClass`) to perform actual data operations.

---

### Properties

- **protected string $modelClass**: The class name of the Eloquent model to use (defaults to `Model::class`).
- **private static $instance**: Singleton instance of the repository.
- **protected Model $model**: The Eloquent model instance.
- **private array $modelTableColumns**: Array of fillable columns from the model.
- **private array $filterKeys**: Array of filter keys defined in the model.
- **private array $relationSearchableKeys**: Array of searchable keys for related models.
- **private array $searchableKeys**: Array of searchable keys for the model.
- **private string $tableName**: The table name associated with the model.

### Constructor

- **public function __construct()**
    - Instantiates the model using the class specified in `$modelClass`.
    - Initializes `$tableName`, `$searchableKeys`, `$relationSearchableKeys`, and `$filterKeys` based on model methods.
    - Populates `$modelTableColumns` with fillable columns.

---

## Methods

### make

```php
public static function make(): static
```

Returns a singleton instance of the repository.

- **Returns:** The singleton instance of the repository.

---

### getTableColumns

```php
public function getTableColumns(): array
```

Retrieves the fillable columns of the model.

- **Returns:** Array of fillable columns.

---

### all

```php
public function all(array $relationships = []): Collection|array|RegularCollection
```

Retrieves all records, optionally eager loading relationships.

- **Parameters:**
    - `$relationships`: Array of relationship names to eager load.
- **Returns:** `Collection`, `array`, or `RegularCollection` of models.

---

### globalQuery

```php
public function globalQuery(array $relations = []): Builder
```

Builds a query with filters, search, and ordering applied.

- **Parameters:**
    - `$relations`: Array of relationship names to eager load.
- **Returns:** Eloquent `Builder` instance.

---

### allWithPagination

```php
public function allWithPagination(array $relationships = [], int $per_page = 10): LengthAwarePaginator
```

Retrieves records with pagination.

- **Parameters:**
    - `$relationships`: Array of relationship names to eager load.
    - `$per_page`: Number of records per page (default: 10).
- **Returns:** `LengthAwarePaginator` of models.

---

### create

```php
public function create(array $data, array $relationships = []): Model
```

Creates a new record.

- **Parameters:**
    - `$data`: Array of attributes for the new record.
    - `$relationships`: Array of related data to associate.
- **Returns:** The created `Model` instance.

---

### delete

```php
public function delete(string|int|Model $id): ?bool
```

Deletes a record by its ID.

- **Parameters:**
    - `$id`: The primary key or model instance to delete.
- **Returns:** `bool|null` — `true` if deleted, `false` if not, or `null` if not found.

---

### find

```php
public function find($id, array $relationships = []): ?Model
```

Retrieves a single record by ID, optionally eager loading relationships.

- **Parameters:**
    - `$id`: The primary key of the record to retrieve.
    - `$relationships`: Array of relationship names to eager load.
- **Returns:** The `Model` instance or `null` if not found.

---

### update

```php
public function update(array $data, string|int|Model $id, array $relationships = []): ?Model
```

Updates an existing record by ID.

- **Parameters:**
    - `$data`: Array of attributes to update.
    - `$id`: The primary key or model instance to update.
    - `$relationships`: Array of related data to update.
- **Returns:** The updated `Model` instance or `null` if not found.

---

### export

```php
public function export(array $ids = []): BinaryFileResponse
```

Exports records (optionally filtered by IDs) to a file (e.g., Excel or CSV).

- **Parameters:**
    - `$ids`: Array of record IDs to export (optional).
- **Returns:** `BinaryFileResponse` for file download.
- **Throws:** `Exception` on failure.

---

### getImportExample

```php
public function getImportExample(): BinaryFileResponse
```

Provides an example file for data import (e.g., Excel template).

- **Returns:** `BinaryFileResponse` for file download.
- **Throws:** `Exception`, `\PhpOffice\PhpSpreadsheet\Writer\Exception` on failure.

---

### import

```php
public function import(): void
```

Imports data from a file (implementation in repository).

- **Returns:** `void`

---

## Filtering, Searching, and Ordering

### Filtering

The `BaseRepository` class supports filtering records based on predefined filter keys. These keys are defined in the
model using the `filterArray` method. The `globalQuery` method applies these filters to the query.

#### How Filtering Works

- **Filter Keys**: Defined in the model using the `filterArray` method. Each filter key can specify a field, operator,
  relation, method, and callback.
- **Applying Filters**: The `filterFields` method iterates over the filter keys and applies them to the query based on
  the request parameters.

#### Example

```php
// In your model
public function filterArray(): array
{
    return [
        ['field' => 'status', 'operator' => '='],
        ['field' => 'created_at', 'operator' => '>=', 'method' => 'whereDate'],
    ];
}
```

### Searching

The `BaseRepository` class supports searching records based on predefined searchable keys. These keys are defined in the
model using the `searchableArray` and `relationsSearchableArray` methods.

#### How Searching Works

- **Searchable Keys**: Defined in the model using the `searchableArray` method for direct attributes and
  `relationsSearchableArray` for related attributes.
- **Applying Search**: The `addSearch` method checks for a `search` parameter in the request and applies the search
  conditions to the query.

#### Example

```php
// In your model
public function searchableArray(): array
{
    return ['name', 'email'];
}

public function relationsSearchableArray(): array
{
    return [
        'profile' => ['bio'],
    ];
}
```

### Ordering

The `BaseRepository` class supports ordering records based on request parameters. The `orderQueryBy` method applies the
ordering to the query.

#### How Ordering Works

- **Sort Columns**: The `sort_col` parameter in the request specifies the column to sort by, and `sort_dir` specifies
  the direction (asc or desc).
- **Applying Ordering**: The `orderQueryBy` method checks for the `sort_col` parameter and applies the ordering to the
  query.

#### Example

```php
// Request parameters
?sort_col=name&sort_dir=asc
```

---

## Usage Example

To use `BaseRepository`, extend it in your own repository class and specify the model class:

```php
use App\Repositories\Contracts\BaseRepository;
use App\Models\User;

/**
 * @extends BaseRepository<User>
 */
class UserRepository extends BaseRepository
{
    protected string $modelClass = User::class;
    // Add custom data access logic here
}
```

---

## Notes

- This class is abstract and should not be instantiated directly.
- All data operations are delegated to the model specified by `$modelClass`.
- Extend this class to implement model-specific data access logic in your application. 