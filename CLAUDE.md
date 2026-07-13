# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> A detailed companion guide exists at `AGENTS.md` — consult it for the full project overview, structure, and security notes. This file focuses on what's needed to be productive fast.

## What this is

`cubeta/cubeta-starter` is a **Laravel 12 package** (PHP 8, PSR-4 `Cubeta\CubetaStarter\` → `src/`) that generates CRUD scaffolding into a *host* Laravel app. It is **not a standalone app** — it is loaded by a host app via `CubetaStarterServiceProvider` and publishes code into that app's directories. To exercise generated output you must install the package into a real Laravel project and run `php artisan` there.

## Commands

```bash
composer install                 # PHP deps
npm install                      # Node deps (asset build + formatting only)

vendor/bin/phpunit               # run all tests (no `composer test` script defined)
vendor/bin/phpunit --filter InitTest        # single test class
vendor/bin/phpunit tests/Feature/InitTest.php   # single file

vendor/bin/pint                  # format PHP (laravel preset; no_unused_imports disabled)
npx prettier --write .           # format Blade / TSX / CSS
```

Note: `no_unused_imports` is intentionally **off** in `pint.json` — generated stubs often carry imports that are conditionally used, so don't strip "unused" imports.

## Generator pipeline (the core mental model)

Understanding this flow is the key to working here — it spans several directories:

1. **Command** (`src/Commands/Generators/Make*.php`) or the GUI controller parses input →
2. **`GeneratorFactory`** (`src/Generators/GeneratorFactory.php`) maps a source `$key` to a generator class →
3. **Generator** (`src/Generators/Sources/*`, `src/Generators/Installers/*`) extends `AbstractGenerator` and writes files into the host app →
4. **Stub builders** (`src/Stub/Builders/*`) assemble contents from raw `.stub` templates in `src/Stub/stubs/` →
5. **StringValues** (`src/StringValues/*`) supply small reusable code snippets injected into stubs.

Every generator exposes a static `$key`; `GeneratorFactory` has several classification lists (`independentFromContainer`, `notNeedForRelations`, `noNeedForColumns`, `getAllGeneratorsKeys`) that gate which generators run for a given table/container — update these lists when adding a generator.

- **Sources/** = per-model artifacts (Model, Migration, Controller, Request, Resource, Factory, Seeder, Repository, Service, Test; plus `WebControllers/` and `ViewsGenerators/`).
- **Installers/** = one-time stack setup (`ApiInstaller`, `WebInstaller`, `AuthInstaller`, `PermissionsInstaller`, `ReactTSInertiaInstaller`, and package installers). Design pattern for generated code is **Repository + Service** (each model gets both).

## Domain enums (drive generation behavior)

- `src/Enums/ColumnTypeEnum.php` — supported column types incl. `key` (foreign id → auto `BelongsTo`) and `translatable` (JSON with cast + `ValidTranslatableJson` rule).
- `src/Enums/RelationsTypeEnum.php` — `hasMany`, `belongsTo`, `belongsToMany`.
- `src/Enums/ContainerType.php` — `api`, `web`, `both`; routes generated output differently per container.

## Conventions

- **Naming:** use `src/Helpers/Naming.php` for all model/table/variable casing — do not hand-roll `Str::` conversions in generators.
- **Paths & I/O:** use `src/Helpers/CubePath.php` for paths and `src/Helpers/FileUtils.php` for directory creation.
- **Errors:** collect generation problems via `src/Logs/CubeLog.php` (`CubeError`, etc.) instead of throwing hard exceptions — the engine accumulates and reports logs.
- **Host-app state:** installed stacks, frontend type, and generated tables are persisted in `cubeta-starter.config.json` at the *host app* root (read/written via `src/Settings/Settings.php`) — never in package files.

## Service provider & publish groups

`src/CubetaStarterServiceProvider.php` (extends Spatie `PackageServiceProvider`) registers `create:*` and `cubeta:install`, loads GUI views (namespace `CubetaStarter`), and registers GUI routes from `src/Routes/ui-routes.php`. Publish groups: `cubeta-starter-web`, `cubeta-starter-api`, `react-ts`.

**Security guard — do not remove:** GUI routes load **only** when `app()->environment('local')` and therefore carry no auth middleware. `cubeta:install auth` overwrites the host `User` model and users migration. Installers can overwrite host files (`--force`).

## Tests

Base test case extends `Orchestra\Testbench\TestCase`. Tests live in `tests/Feature/` (currently only the `InitTest.php` smoke test; `tests/Unit` is referenced in `phpunit.xml` but does not yet exist). New tests should exercise the engine through `GeneratorFactory` or the registered Artisan commands.
