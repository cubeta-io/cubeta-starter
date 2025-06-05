# BaseExporter Documentation

## Introduction

The `BaseExporter` class is a powerful utility designed to simplify and standardize the process of exporting data to
Excel files. It leverages the Maatwebsite/Laravel-Excel package to provide a consistent
structure for exporting models and collections to Excel, with support for customizing headings, mapping data, and
handling large datasets efficiently.

## Key Features

### Properties

| Property       | Type                    | Description                                   |
|----------------|-------------------------|-----------------------------------------------|
| `$collection`  | array\|Collection\|null | The data collection to be exported            |
| `$model`       | Model\|null             | The model associated with the export          |
| `$requestCols` | array\|null             | Optional columns to include in the export     |
| `$isExample`   | bool                    | Flag to indicate if this is an example export |

### Constructor

```php
public function __construct(Collection|array $collection, Model $model, ?array $requestCols, bool $isExample = false)
{
    $this->collection = $collection;
    $this->model = $model;
    $this->requestCols = $requestCols;
    $this->isExample = $isExample;
}
```

The constructor initializes the exporter with the data collection, model, requested columns, and a flag indicating
whether this is an example export.

### Methods

#### collection()

```php
public function collection()
```

This method implements the `FromCollection` interface and returns the data collection to be exported. It handles three
scenarios:

1. If the model has an `export()` method, it uses that method to get the data
2. If `$isExample` is true and the model has an `importExample()` method, it uses that method to get example data
3. Otherwise, it returns the collection provided in the constructor or the model's fillable attributes for examples

#### map($row)

```php
public function map($row): array
```

This method implements the `WithMapping` interface and maps each row of data to an array for export. It:

1. Returns an empty array if this is an example export
2. Gets the exportable columns from the model (using `exportable()` method if available, otherwise using
   `getFillable()`)
3. Filters columns based on `$requestCols` if provided
4. Handles dot notation for accessing nested relation properties
5. Maps each column to its corresponding value in the row

#### headings()

```php
public function headings(): array
```

This method implements the `WithHeadings` interface and returns the column headings for the export. It:

1. Gets the exportable columns from the model (using `exportable()` method if available, otherwise using
   `getFillable()`)
2. Filters columns based on `$requestCols` if provided
3. Formats the column names by replacing dots, dashes, and underscores with spaces and title-casing the result

#### chunkSize()

```php
public function chunkSize(): int
```

This method implements the `WithCustomChunkSize` interface and returns the chunk size for processing large datasets. It
returns a default value of 500, which helps optimize memory usage when exporting large amounts of data.

## How to Use BaseExporter

The `BaseExporter` class is primarily used through the `export()` and `getImportExample()` methods in the
`BaseRepository` class. These methods handle the creation of the exporter and the downloading of the Excel file.

### Exporting Data

To export data to an Excel file, you can use the `export()` method in a repository that extends `BaseRepository`:

```php
// In a controller
public function export(Request $request)
{
    $ids = $request->ids ?? [];
    
    return $this->categoryService->export($ids);
}

// In a service
public function export(array $ids = []): BinaryFileResponse
{
    return $this->repository->export($ids);
}

// In BaseRepository
public function export(array $ids = []): BinaryFileResponse
{
    if (!count($ids)) {
        $collection = $this->globalQuery()->get();
    } else {
        $collection = $this->globalQuery()->whereIn('id', $ids)->get();
    }

    $requestedColumns = request('columns') ?? null;

    return Excel::download(
        new BaseExporter($collection, $this->model, $requestedColumns),
        $this->model->getTable().'.xlsx',
    );
}
```

### Generating Import Examples

To generate an example Excel file for importing data, you can use the `getImportExample()` method:

```php
// In a controller
public function getImportExample()
{
    return $this->categoryService->getImportExample();
}

// In a service
public function getImportExample(): BinaryFileResponse
{
    return $this->repository->getImportExample();
}

// In BaseRepository
public function getImportExample(): BinaryFileResponse
{
    return Excel::download(
        new BaseExporter(collect(), $this->model, null, true),
        $this->model->getTable().'-example.xlsx'
    );
}
```

### Customizing Exports in Models

You can customize how a model is exported by implementing specific methods in your model class:

#### exportable()

Define which columns should be included in the export:

```php
public function exportable(): array
{
    return [
        'name',
        'email',
        'created_at',
        'user.name', // Nested relation
    ];
}
```

#### export()

Provide a custom collection for export:

```php
public function export(): Collection
{
    return $this->with('user')->get();
}
```

#### importExample()

Provide example data for the import template:

```php
public function importExample(): Collection
{
    return collect([
        [
            'name' => 'Example Name',
            'email' => 'example@example.com',
        ]
    ]);
}
```