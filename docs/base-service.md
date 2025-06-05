# BaseService

## Overview

The `BaseService` class is an abstract service layer class designed to provide a standardized set of CRUD and utility operations for Eloquent models in a Laravel application. It acts as an intermediary between controllers and repositories, encapsulating business logic and delegating data access to a corresponding repository.

This class is intended to be extended by concrete service classes for specific models. It relies on a repository class (implementing `BaseRepository`) to perform actual data operations.

---

### Properties
- **protected BaseRepository $repository**: The repository instance used for data operations.
- **protected string $repositoryClass**: The class name of the repository to instantiate (defaults to `BaseRepository::class`).

### Constructor
- **protected function __construct()**
    - Instantiates the repository using the class specified in `$repositoryClass`.

---

## Methods

### delete
```php
public function delete($id): ?bool
```
Deletes a record by its ID.
- **Parameters:**
    - `$id`: The primary key of the record to delete.
- **Returns:** `bool|null` — `true` if deleted, `false` if not, or `null` if not found.

---

### index
```php
public function index(array $relations = []): Collection|array|RegularCollection
```
Retrieves all records, optionally eager loading relationships.
- **Parameters:**
    - `$relations`: Array of relationship names to eager load.
- **Returns:** `Collection`, `array`, or `RegularCollection` of models.

---

### indexWithPagination
```php
public function indexWithPagination(array $relations = [], int $per_page = 10): LengthAwarePaginator
```
Retrieves records with pagination.
- **Parameters:**
    - `$relations`: Array of relationship names to eager load.
    - `$per_page`: Number of records per page (default: 10).
- **Returns:** `LengthAwarePaginator` of models.

---

### store
```php
public function store(array $data, array $relationships = []): Model
```
Creates a new record.
- **Parameters:**
    - `$data`: Array of attributes for the new record.
    - `$relationships`: Array of related data to associate.
- **Returns:** The created `Model` instance.

---

### update
```php
public function update(array $data, $id, array $relationships = []): ?Model
```
Updates an existing record by ID.
- **Parameters:**
    - `$data`: Array of attributes to update.
    - `$id`: The primary key of the record to update.
    - `$relationships`: Array of related data to update.
- **Returns:** The updated `Model` instance or `null` if not found.

---

### view
```php
public function view($id, array $relationships = []): ?Model
```
Retrieves a single record by ID, optionally eager loading relationships.
- **Parameters:**
    - `$id`: The primary key of the record to retrieve.
    - `$relationships`: Array of relationship names to eager load.
- **Returns:** The `Model` instance or `null` if not found.

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

## Usage Example

To use `BaseService`, extend it in your own service class and specify the repository class:

```php
use App\Services\Contracts\BaseService;
use App\Repositories\UserRepository;

/**
 * @extends BaseService<User>
 * @property UserRepository $repository
 */
class UserService extends BaseService
{
    protected string $repositoryClass = UserRepository::class;
    // Add custom business logic here
}
```

---

## Notes
- This class is abstract and should not be instantiated directly.
- All data operations are delegated to the repository specified by `$repositoryClass`.
- Extend this class to implement model-specific business logic in your application. 