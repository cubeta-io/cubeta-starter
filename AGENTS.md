# Cubeta Starter — Agent Guide

## Project Overview

Cubeta Starter (`cubeta/cubeta-starter`) is a Laravel package that automates the generation of CRUD scaffolding. It is distributed through Packagist and installed into a host Laravel application. The package provides:

- A web-based GUI for defining models, attributes, relations and generating code.
- Artisan commands (`create:model`, `create:controller`, etc.) for terminal-driven generation.
- A Repository + Service design pattern for generated code.
- Support for multiple output targets:
  - API-only backends (controllers, resources, routes, Postman collections, feature tests).
  - Blade/Bootstrap/jQuery web stacks.
  - Inertia.js + React + TypeScript + Tailwind web stacks.
- Optional add-ons: authentication, roles/permissions, translatable JSON attributes, bulk actions, Excel import/export bases.

The package does not run as a standalone application. It is loaded by a host Laravel app through `CubetaStarterServiceProvider` and publishes code into that app's directories.

## Technology Stack

| Layer | Technology | Notes |
|-------|------------|-------|
| Language | PHP 8.x | Package source is pure PHP. |
| Framework | Laravel 12.x | The package requires `laravel/framework ^v12.0.0` and `illuminate/support ^v12.0.0`. |
| Package tooling | `spatie/laravel-package-tools` | Used for service-provider configuration, config files and command registration. |
| CLI prompts | `laravel/prompts` | Interactive command questions. |
| Frontend (dev assets) | React 19, TypeScript 5.8, Vite 6, Tailwind CSS 4 | Only used for generated Inertia stacks and the package's own JS/CSS resources. |
| Frontend formatting | Prettier 3 + `prettier-plugin-blade` + `prettier-plugin-tailwindcss` | Used for generated Blade/TSX files. |
| PHP formatting | Laravel Pint | Preset: `laravel` with `no_unused_imports` disabled. |
| Testing | PHPUnit via `orchestra/testbench` | Configured in `phpunit.xml`. |
| Optional runtime deps (dev) | `maatwebsite/excel`, `inertiajs/inertia-laravel`, `tightenco/ziggy` | Only installed in the package dev environment; the host app installs matching packages via the installer commands. |

## Project Structure

```
/home/khaldoun/Projects/Backend/cubeta-starter/
├── composer.json              # PHP package manifest
├── composer.lock              # Locked PHP dependencies
├── package.json               # Node dev dependencies for frontend assets/formatting
├── package-lock.json
├── phpunit.xml                # PHPUnit configuration
├── pint.json                  # Laravel Pint rules
├── .prettierrc                # Prettier configuration
├── .prettierignore
├── tsconfig.json              # TypeScript paths for generated Inertia apps
├── config/
│   ├── cubeta-starter.php     # Main package config (namespaces, paths, locales)
│   └── views-names.php        # Named view keys for auth pages
├── docs/                      # Markdown documentation (Docsify site)
├── lang/
│   └── site.php               # Default translation strings the package publishes
├── public/                    # Static assets (images, JS) published to host apps
├── resources/
│   ├── css/                   # Blade and Inertia CSS
│   ├── js/                    # Blade JS and Inertia React/TS components
│   └── views/                 # Blade views published to host apps
├── src/                       # Package PHP source (namespace Cubeta\CubetaStarter)
│   ├── App/Http/Controllers   # GUI controllers (GeneratorController, etc.)
│   ├── Commands/              # Artisan command classes
│   │   ├── Generators/        # create:* commands
│   │   └── Installers/        # cubeta:install command
│   ├── Contracts/             # Interfaces used by the package
│   ├── Enums/                 # ColumnTypeEnum, ContainerType, RelationsTypeEnum, etc.
│   ├── Generators/            # Generator engine
│   │   ├── AbstractGenerator.php
│   │   ├── GeneratorFactory.php
│   │   ├── Installers/        # ApiInstaller, WebInstaller, AuthInstaller, etc.
│   │   └── Sources/           # Per-file generators (Model, Migration, Controller, etc.)
│   ├── Helpers/               # Utility classes (CubePath, FileUtils, Naming, etc.)
│   ├── Logs/                  # Generation-time logging (CubeLog, CubeError, etc.)
│   ├── Modules/               # Runtime helpers (e.g., ApiResponse)
│   ├── Resources/views        # Package GUI Blade views
│   ├── Routes/                # Package GUI routes (`ui-routes.php`)
│   ├── Settings/              # Settings/CubeTable/CubeAttribute/CubeRelation
│   ├── StringValues/          # Snippet builders for generated code
│   ├── Stub/                  # Stub builders and `.stub` templates
│   │   ├── Builders/          # PHP classes that assemble stub contents
│   │   ├── Contracts/         # Stub-builder interfaces
│   │   └── stubs/             # Raw `.stub` template files
│   ├── Traits/                # Reusable PHP traits
│   └── CubetaStarterServiceProvider.php
└── tests/
    └── Feature/
        └── InitTest.php       # Minimal smoke test
```

## Key Configuration Files

- `composer.json` — Defines package metadata, autoloading (`Cubeta\CubetaStarter\` → `src/`), dev dependencies, and Laravel provider auto-discovery.
- `config/cubeta-starter.php` — Runtime config for the host app: project name/URL, version, generated namespaces and paths, localization, Postman collection generation.
- `config/views-names.php` — View name map used when generating auth views.
- `phpunit.xml` — PHPUnit suites for `tests/Unit` and `tests/Feature`, coverage over `src/`.
- `pint.json` — Laravel preset with `no_unused_imports` disabled.
- `.prettierrc` — Blade + Tailwind formatting for generated views and React components.
- `tsconfig.json` — Path mapping `@/*` to `./resources/js/inertia/*` and `ziggy-js` to the vendor copy.

## Build, Test and Quality Commands

Working directory: `/home/khaldoun/Projects/Backend/cubeta-starter`.

```bash
# Install PHP dependencies
composer install

# Install Node dev dependencies (for asset building and formatting)
npm install

# Run PHP tests
vendor/bin/phpunit
# or
composer test   # if a script is defined; verify in composer.json scripts first

# Format PHP source
vendor/bin/pint

# Format frontend / Blade files
npx prettier --write .
```

> The package itself does not define an application server; it is loaded by a host Laravel app. To exercise generated code you must install the package into a Laravel project and run `php artisan` there.

## How Code Is Organized

### Service Provider

`src/CubetaStarterServiceProvider.php` extends `Spatie\LaravelPackageTools\PackageServiceProvider`.

- Registers all `create:*` commands and `cubeta:install`.
- Loads the GUI views from `src/Resources/views` under the namespace `CubetaStarter`.
- Registers GUI routes from `src/Routes/ui-routes.php` only in the `local` environment.
- Defines publish groups:
  - `cubeta-starter-web` — Blade stack assets and base classes.
  - `cubeta-starter-api` — API base classes.
  - `react-ts` — Inertia + React + TypeScript stack assets and config files.

### Generator Engine

1. **Commands** (`src/Commands/Generators/*`) parse CLI/GUI input and call `GeneratorFactory`.
2. **GeneratorFactory** (`src/Generators/GeneratorFactory.php`) instantiates the appropriate generator class based on a source key.
3. **Generators** (`src/Generators/Sources/*`, `src/Generators/Installers/*`) extend `AbstractGenerator` and write files into the host app using stub builders.
4. **Stub builders** (`src/Stub/Builders/*`) construct file contents from `.stub` templates stored in `src/Stub/stubs/`.
5. **String values** (`src/StringValues/*`) provide small, reusable code snippets.
6. **Settings** (`src/Settings/Settings.php`) reads/writes `cubeta-starter.config.json` in the host app root to remember installed stacks, frontend type, generated tables, etc.

### Supported Column Types

Defined in `src/Enums/ColumnTypeEnum.php`:

`integer`, `bigInteger`, `unsignedBigInteger`, `double`, `float`, `string`, `text`, `json`, `boolean`, `date`, `time`, `dateTime`, `timestamp`, `file`, `key` (foreign id), `translatable` (JSON with cast + validation rule).

### Supported Relations

Defined in `src/Enums/RelationsTypeEnum.php`: `hasMany`, `belongsTo`, `belongsToMany`.

Attributes typed as `key` are automatically converted into a `BelongsTo` relation.

### Containers

`src/Enums/ContainerType.php` defines `api`, `web`, `both`. Generated files are routed differently depending on the container.

## Development Conventions

- **PHP namespace:** `Cubeta\CubetaStarter\` maps to `src/`.
- **Autoloading:** PSR-4 via `composer.json`.
- **Code style:** Laravel Pint with the `laravel` preset. Unused imports are intentionally allowed (`no_unused_imports: false`).
- **Blade/TS formatting:** Prettier with Tailwind class sorting and Blade parsing.
- **Naming helpers:** Use `src/Helpers/Naming.php` for model/migration/table/variable casing conversions; do not hand-roll `Str` conversions in generators.
- **File I/O:** Use `src/Helpers/CubePath.php` for path handling and `src/Helpers/FileUtils.php` for directory creation/formatting.
- **Logs:** Generation issues are collected through `src/Logs/CubeLog.php` rather than thrown as hard exceptions in most cases.
- **Settings persistence:** Package state for the host app is stored in `cubeta-starter.config.json` at the host app root, not in package files.

## Testing Instructions

- Tests live in `tests/Feature/` (the `phpunit.xml` also references `tests/Unit`, but the directory does not currently exist).
- The base test case extends `Orchestra\Testbench\TestCase`.
- Currently there is only a trivial smoke test (`InitTest.php`). When adding tests, create feature tests that exercise the generator engine through `GeneratorFactory` or the Artisan commands registered by the service provider.
- Run tests with `vendor/bin/phpunit`.

## Security Considerations

- **GUI routes are local-only.** The package explicitly loads `src/Routes/ui-routes.php` only when `app()->environment('local')`. Do not remove that guard.
- **File overwrite risk.** Installer and generator commands can overwrite host app files (`--force`). Generated files modify the host app's `composer.json`, `routes/`, `app/`, `database/`, etc. Always advise users to commit their host app before running installers.
- **Generated auth.** The `cubeta:install auth` command overwrites the host's `User.php` model and users migration. Treat auth-generated code as a starting point and review password-reset flows before production use.
- **Translatable JSON.** The generated `ValidTranslatableJson` rule ensures translations are flat key/value JSON objects matching configured locales; validation occurs in form requests, but storage is ultimately user-controlled JSON.
- **Secrets.** The repository ignores `.env`, `.env.*`, vendor and node_modules. Do not commit lock files or environment files.
- **No authentication on package GUI.** Because routes are restricted to `local`, they do not include auth middleware. If you ever change that guard, add appropriate authentication/authorization.

## Deployment Notes

- This is a Composer package, not a deployable service. Deployment happens by tagging releases and publishing to Packagist.
- The host Laravel application installs the package via `composer require cubeta/cubeta-starter` and runs the relevant `cubeta:install` and `create:*` commands.
- Frontend assets for generated stacks are built by the host app with `npm run build`; this package only ships source assets and stubs.
- When publishing a new version, update the changelog (`docs/_changelog.md`) and version references per the SemVer guidance in `docs/contributing.md`.
