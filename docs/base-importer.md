# BaseImporter

## Introduction

The `BaseImporter` class is a powerful utility designed to simplify and standardize the process of importing data from
Excel files into Laravel applications. It leverages the Maatwebsite/Laravel-Excel package to provide a consistent
structure for importing Excel data into models, with support for customizing column mappings, handling special data
types, and processing rows efficiently.

This class serves as the foundation for all import operations in the application, ensuring that data imports follow a
consistent format and can be easily customized based on specific needs.

## Key Features

### Properties

| Property | Type  | Description                          |
|----------|-------|--------------------------------------|
| `$model` | Model | The model associated with the import |

### Constructor

```php
public function __construct(Model $model)
{
    $this->model = $model;
}
```

The constructor initializes the importer with the model that will be used to create new instances from the imported
data.

### Methods

#### mapping()

```php
private function mapping()
```

This private method determines which columns should be imported. It checks if the model has an `importable()` method,
and if so, uses that to get the list of columns. Otherwise, it falls back to using the model's `getFillable()` method.

#### model(array $row)

```php
public function model(array $row): Model|array|null
```

This method implements the `ToModel` interface and creates a new model instance from a row of data. It handles two
scenarios:

1. If the model has an `import()` method, it delegates the creation of the model to that method
2. Otherwise, it creates a new model instance using the mapping and processes each column value

#### processRow(string $colName, $row)

```php
private function processRow(string $colName, $row): mixed
```

This method processes a single column value from a row. It handles special cases like translatable fields, which are
stored as JSON in the database but need to be properly formatted during import.

## How to Use BaseImporter

The `BaseImporter` class is primarily used through the `import()` method in the `BaseRepository` class. This method is
called by the `import()` method in the `BaseService` class, which is in turn called by the `import()` method in
controllers.

### Importing Data

To import data from an Excel file, you can use the `import()` method in a controller:

```php
// In a controller
public function import(Request $request)
{
    $request->validate([
        'excel_file' => 'required|mimes:xls,xlsx',
    ]);

    $this->categoryService->import();
}

// In a service
public function import(): void
{
    $this->repository->import();
}

// In BaseRepository
public function import(): void
{
    Excel::import(new BaseImporter($this->model), request()->file('excel_file'));
}
```

### Customizing Imports in Models

You can customize how data is imported by implementing specific methods in your model class:

#### importable()

Define which columns should be included in the import:

```php
public function importable(): array
{
    return [
        'name',
        'email',
        'created_at',
    ];
}
```

#### import(array $row)

Provide custom logic for creating a model from a row of data:

```php
public function import(array $row): Model
{
    // Custom logic for creating a model from a row
    return self::create([
        'name' => $row['name'],
        'email' => $row['email'],
        'password' => Hash::make($row['password']),
    ]);
}
```
