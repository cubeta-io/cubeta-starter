# Configuration

Cubeta Starter provides several configuration options to customize its behavior according to your project's needs. This guide explains all available configuration options and how to use them effectively.

## Configuration File

After installing Cubeta Starter, you'll find a configuration file at `config/cubeta-starter.php`. This file contains all the settings you can adjust to customize the package's behavior.

The config file is published for you when you run any of the install commands (`cubeta:install api`, `web`, or `react-ts`). If you ever need to (re)publish it on its own, use:

```bash
php artisan vendor:publish --tag=cubeta-starter-config
```

## Available Configuration Options

The configuration is a **flat** array of keys — each generated artifact has its own `*_namespace` and `*_path` entry. The real defaults are shown below.

### Project Settings

| Option | Description | Default |
|--------|-------------|---------|
| `project_name` | The name of your project, used for naming the Postman collection | `'CubetaStarter'` |
| `version` | The version segment of the generated code structure (used in namespaces/paths, e.g. `App\Http\Resources\v1`) | `'v1'` |
| `project_url` | Your project's public URL, used in the Postman collection | `null` (falls back to `http://localhost/{project_name}/public/`) |
| `generate_postman_collection_for_api_routes` | Whether to (re)generate/append a Postman collection for each generated API controller | `true` |
| `postman_collection_path` | Where the Postman collection is written (empty = project root) | `''` |

### Localization Settings

| Option | Description | Default |
|--------|-------------|---------|
| `available_locales` | Array of locales your application supports | `['en']` |
| `default_locale` | The default locale for your application | `'en'` |

### Directory and Namespace Settings

Each of the following is a pair of flat keys — a `*_namespace` (PHP namespace) and a `*_path` (filesystem path built with `join_paths(...)`). These control where generated files are placed:

```php
// Models
'model_namespace' => "App\Models",
'model_path'      => join_paths('app', 'Models'),

// Repositories (the base class lives under a Contracts sub-namespace)
'repository_namespace' => "App\Repositories",
'repository_path'      => join_paths('app', 'Repositories'),

// Services (the base class lives under a Contracts sub-namespace)
'service_namespace' => 'App\Services',
'service_path'      => join_paths('app', 'Services'),

// API controllers
'api_controller_namespace' => 'App\Http\Controllers\API',
'api_controller_path'      => join_paths('app', 'Http', 'Controllers', 'API'),

// Web controllers
'web_controller_namespace' => 'App\Http\Controllers\WEB',
'web_controller_path'      => join_paths('app', 'Http', 'Controllers', 'WEB'),

// Requests
'request_namespace' => 'App\Http\Requests',
'request_path'      => join_paths('app', 'Http', 'Requests'),

// Resources
'resource_namespace' => 'App\Http\Resources',
'resource_path'      => join_paths('app', 'Http', 'Resources'),

// Migrations (path only)
'migration_path' => join_paths('database', 'migrations'),

// Seeders
'seeder_namespace' => 'Database\Seeders',
'seeder_path'      => join_paths('database', 'seeders'),

// Factories
'factory_namespace' => 'Database\Factories',
'factory_path'      => join_paths('database', 'factories'),

// Tests
'test_namespace' => 'Tests\Feature',
'test_path'      => join_paths('tests', 'Feature'),

// Traits
'trait_namespace' => 'App\Traits',
'trait_path'      => join_paths('app', 'Traits'),

// Exceptions
'exception_namespace' => 'App\Exceptions',
'exception_path'      => join_paths('app', 'Exceptions'),
```

> [!NOTE]
> The `version` value is woven into generated namespaces and paths. For example, with `version => 'v1'` a resource is generated as `App\Http\Resources\v1\ProductResource`. Base classes such as `BaseRepository` and `BaseService` are placed under a `Contracts` sub-namespace of their configured namespace.

## Example Configuration

Here's an example of a customized configuration:

```php
use function Illuminate\Filesystem\join_paths;

return [
    'project_name' => 'My E-commerce App',
    'project_url'  => 'https://myecommerce.example.com',
    'version'      => 'v1',

    'available_locales' => ['en', 'fr', 'es'],
    'default_locale'    => 'en',

    // move models into a domain folder
    'model_namespace' => 'App\Domain\Models',
    'model_path'      => join_paths('app', 'Domain', 'Models'),

    // customise the API controller location
    'api_controller_namespace' => 'App\Http\Controllers\Api',
    'api_controller_path'      => join_paths('app', 'Http', 'Controllers', 'Api'),

    // ...the rest of the keys shown above
];
```

## Using Configuration Values in Your Code

The configuration values are accessible using Laravel's `config` helper:

```php
$projectName = config('cubeta-starter.project_name');
$availableLocales = config('cubeta-starter.available_locales');
```

## Configuration Impact on Generated Code

The configuration settings directly affect how and where files are generated:

1. **Directory and Namespace Settings**: Control where files are created and what namespaces they use
2. **Localization Settings**: Determine what locales are supported for translatable fields
3. **Project Settings**: Affect the Postman collection and other project-specific features
4. **Version**: The `version` value is inserted into generated namespaces and paths (e.g. `App\Http\Resources\v1\ProductResource`)

## Changing Configuration After Generation

If you change configuration settings after generating files, new files will follow the new configuration, but existing files won't be moved or updated automatically. Consider this when planning your project structure.

For major changes to your project structure, it's recommended to make configuration changes before generating any files.