# Configuration

Cubeta Starter provides several configuration options to customize its behavior according to your project's needs. This guide explains all available configuration options and how to use them effectively.

## Configuration File

After installing Cubeta Starter, you'll find a configuration file at `config/cubeta-starter.php`. This file contains all the settings you can adjust to customize the package's behavior.

If the configuration file doesn't exist, you can publish it using:

```bash
php artisan vendor:publish --tag=cubeta-starter-config
```

## Available Configuration Options

### Project Settings

| Option | Description | Default |
|--------|-------------|---------|
| `project_name` | The name of your project, used for naming the Postman collection | Your Laravel app name |
| `project_url` | Your project's public URL, used in the Postman collection | `null` (defaults to `http://localhost/your-project/public/`) |
| `version` | The version of the generated code structure | `"2.0"` |

### Localization Settings

| Option | Description | Default |
|--------|-------------|---------|
| `available_locales` | Array of locales your application supports | `["en"]` |
| `default_locale` | The default locale for your application | `"en"` |

### Directory and Namespace Settings

The following settings control where generated files are placed and what namespaces they use:

#### Models

```php
'models' => [
    'directory' => 'app/Models',
    'namespace' => 'App\\Models',
],
```

#### Controllers

```php
'controllers' => [
    'api' => [
        'directory' => 'app/Http/Controllers/API/v1',
        'namespace' => 'App\\Http\\Controllers\\API\\v1',
    ],
    'web' => [
        'directory' => 'app/Http/Controllers/Web/v1',
        'namespace' => 'App\\Http\\Controllers\\Web\\v1',
    ],
],
```

#### Requests

```php
'requests' => [
    'directory' => 'app/Http/Requests',
    'namespace' => 'App\\Http\\Requests',
],
```

#### Resources

```php
'resources' => [
    'directory' => 'app/Http/Resources',
    'namespace' => 'App\\Http\\Resources',
],
```

#### Repositories

```php
'repositories' => [
    'directory' => 'app/Repositories',
    'namespace' => 'App\\Repositories',
    'contracts_directory' => 'app/Repositories/Contracts',
    'contracts_namespace' => 'App\\Repositories\\Contracts',
],
```

#### Services

```php
'services' => [
    'directory' => 'app/Services',
    'namespace' => 'App\\Services',
    'interfaces_directory' => 'app/Services/Interfaces',
    'interfaces_namespace' => 'App\\Services\\Interfaces',
],
```

#### Routes

```php
'routes' => [
    'api_directory' => 'routes/v1/api',
    'web_directory' => 'routes/v1/web',
],
```

## Example Configuration

Here's an example of a customized configuration:

```php
return [
    'project_name' => 'My E-commerce App',
    'project_url' => 'https://myecommerce.example.com',
    'available_locales' => ['en', 'fr', 'es'],
    'default_locale' => 'en',
    'version' => '2.0',
    
    'models' => [
        'directory' => 'app/Domain/Models',
        'namespace' => 'App\\Domain\\Models',
    ],
    
    'controllers' => [
        'api' => [
            'directory' => 'app/Http/Controllers/Api',
            'namespace' => 'App\\Http\\Controllers\\Api',
        ],
        // Other settings...
    ],
    // Other settings...
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
4. **Version**: Determines the structure of the generated code

## Changing Configuration After Generation

If you change configuration settings after generating files, new files will follow the new configuration, but existing files won't be moved or updated automatically. Consider this when planning your project structure.

For major changes to your project structure, it's recommended to make configuration changes before generating any files.