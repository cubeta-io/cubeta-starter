# Commands Reference

Cubeta Starter provides two families of Artisan commands:

- **`cubeta:install`** — one-time setup commands that install a stack or feature into your app.
- **`create:*`** — generators that create per-model files (model, migration, controller, etc.).

All generator commands are **interactive**: if you omit an argument, the command prompts you for it. Every command accepts a `--force` flag to overwrite existing files without asking.

---

## Install Commands

### `cubeta:install`

Installs a stack or feature into your host application.

```bash
php artisan cubeta:install {name} {version=v1} {--force}
```

| Argument | Description |
|----------|-------------|
| `name` | One of: `api`, `web`, `web-packages`, `auth`, `permissions`, `react-ts`, `react-ts-packages` |
| `version` | The version segment for generated structure (default: `v1`) |
| `--force` | Overwrite existing files |

**Available stacks & features:**

| Command | What it installs |
|---------|------------------|
| `cubeta:install api` | API base classes (Repository, Service, Resource, ApiResponse), localization middleware, helpers, and the API route structure |
| `cubeta:install web` | Blade base classes, Blade components, web controller base, layout, and the web route structure |
| `cubeta:install web-packages` | Frontend npm packages for the Blade stack (Bootstrap, jQuery, etc.) |
| `cubeta:install react-ts` | Inertia + React + TypeScript + Tailwind stack, including the `HandleInertiaRequests` middleware and dashboard components |
| `cubeta:install react-ts-packages` | npm packages for the React/TypeScript stack |
| `cubeta:install auth` | Authentication scaffolding (`BaseAuthController`, auth requests, `User` model, `UserService`, notifications, routes). **Overrides your existing `User` model and users migration** |
| `cubeta:install permissions` | A lightweight, self-contained roles & permissions system (`Role` model, `HasRoles`/`HasPermissions` traits, migrations) — no external package required |

> [!WARNING]
> `cubeta:install auth` overwrites your existing `User` model and the users table migration. Commit your work first.

---

## Full CRUD Generator

### `create:model`

The primary command. Generates a complete CRUD stack for a model.

```bash
php artisan create:model {name?} {attributes?} {nullables?} {uniques?} {relations?} {actor?} {container?}
    {--migration} {--request} {--dto} {--resource} {--factory} {--seeder}
    {--repository} {--service} {--controller} {--web_controller} {--test} {--force}
```

**With no options**, it generates **everything**: model, migration, factory, seeder, request and/or DTO (per the validation type you chose while installing), resource, controller, repository, service, test, routes, and (optionally) a Postman collection.

**With options**, it generates only the model plus the requested pieces. For example:

```bash
# Only the model, a controller, and a resource
php artisan create:model Product --controller --resource
```

| Option | Generates |
|--------|-----------|
| `--migration` | Migration file |
| `--request` | Form request with validation |
| `--dto` | Validated DTO (`wendelladriel/laravel-validated-dto`) |
| `--resource` | JSON API resource |
| `--factory` | Model factory |
| `--seeder` | Database seeder |
| `--repository` | Repository class |
| `--service` | Service class |
| `--controller` | API controller |
| `--web_controller` | Web controller (Blade or React, per your installed stack) |
| `--test` | Feature test |

> [!TIP]
> The GUI cannot generate a subset of files the way `--options` can; use it via the "full generation" page or generate each file individually.

---

## Individual Generators

Each of these generates a single artifact for a model. All are interactive and accept `--force`.

### `create:migration`

```bash
php artisan create:migration {name?} {attributes?} {relations?} {nullables?} {uniques?} {--force}
```

Creates a migration whose columns match the attribute types you provide. Notes:
- `file` columns → nullable `string` columns
- `key` columns → `foreignIdFor` with `constrained()` and `cascadeOnDelete()`
- `translatable` columns → `json` columns

### `create:factory`

```bash
php artisan create:factory {name?} {attributes?} {relations?} {uniques?} {--force}
```

Creates a factory that fills each column with a sensible faker value inferred from its type and name. `hasMany` / `belongsToMany` relations get a `with<Relation>()` helper method.

### `create:seeder`

```bash
php artisan create:seeder {name?} {--force}
```

Creates a seeder that calls the model's factory with a count of `10`.

### `create:request`

```bash
php artisan create:request {name?} {attributes?} {nullables?} {uniques?} {container?} {--force}
```

Creates a form request with validation rules inferred from column names and types (see [Generated Files → Requests](created-files.md#requests)).

### `create:dto`

```bash
php artisan create:dto {name?} {attributes?} {nullables?} {uniques?} {container?} {--force}
```

Creates a validated DTO extending `WendellAdriel\ValidatedDTO\ValidatedDTO` with the same rules the form request gets, plus typed properties and casts. It requires the `wendelladriel/laravel-validated-dto` package, which `cubeta:install` adds for you when you pick `DTO` or `Both` as the validation type.

### `create:resource`

```bash
php artisan create:resource {name?} {attributes?} {relations?} {container?} {--force}
```

Creates a JSON resource extending `BaseResource`. It includes the model's relations.

### `create:repository`

```bash
php artisan create:repository {name?} {--force}
```

Creates a repository extending `BaseRepository`, obtained via `YourModelRepository::make()`.

### `create:service`

```bash
php artisan create:service {name?} {--force}
```

Creates a service extending `BaseService`, obtained via `YourModelService::make()`.

### `create:controller`

```bash
php artisan create:controller {name?} {actor?} {--force}
```

Creates an API controller with `index`, `show`, `store`, `update`, `destroy`, plus `export`, `import`, and `getImportExample`.

### `create:web-controller`

```bash
php artisan create:web-controller {name?} {attributes?} {relations?} {nullables?} {actor?} {--force}
```

Creates a web controller and its views/pages for your installed frontend stack (Blade or React/Inertia).

### `create:test`

```bash
php artisan create:test {name?} {attributes?} {actor?} {--force}
```

Creates a feature test extending `MainTestCase` that exercises the CRUD endpoints. The `name` argument is the **model name**, not a test class name.

### `create:actor`

```bash
php artisan create:actor {--force}
```

Fully interactive. Adds an actor (role) to your project — prompts for the actor name, its permissions, and the container (api/web/both), then generates its role definition, routes, and (optionally) an authentication controller.

> [!NOTE]
> `create:actor` requires the permissions feature (`cubeta:install permissions`) and, for an auth controller, the auth feature (`cubeta:install auth`).

---

## The Seven Parameters

Most `create:*` commands draw from the same set of parameters. You'll only be asked for the ones a given command needs:

| Parameter | Type | Meaning |
|-----------|------|---------|
| Model name | `string` | The model the generated file targets |
| Attributes | `array[colName => colType]` | Table columns and their types |
| Relations | `array[relationName => relationType]` | Model relationships |
| Nullables | `array[colName]` | Which columns are nullable |
| Uniques | `array[colName]` | Which columns are unique |
| Actor | `string` | The actor for the generated controller/routes |
| Container | `string` (`api`, `web`, `both`) | Which stack the output targets |

> [!TIP]
> You rarely type these as raw arguments — just run the command and answer the prompts. The package handles Laravel naming conventions for you (e.g. `products` → `Product`).
