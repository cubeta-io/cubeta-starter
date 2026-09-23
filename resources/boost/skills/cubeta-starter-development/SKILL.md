---
name: cubeta-starter-development
description: Generate Laravel CRUD scaffolding (model, migration, request/DTO, resource, factory, seeder, repository, service, controller, web controller, tests, actors) with the cubeta/cubeta-starter package's artisan commands, and understand its generator pipeline when extending the package itself.
---

# Cubeta Starter Development

## When to use this skill

Use this skill when:

- Scaffolding a new Eloquent model and its full CRUD stack (api and/or web) in a host app that has
  `cubeta/cubeta-starter` installed.
- Adding a single missing artifact (migration, request, resource, factory, ...) for an existing
  model.
- Adding a new actor/role and its permissions.
- Installing or reconfiguring one of the package's stacks (api, web, react-ts, permissions, auth).
- Working on the `cubeta-starter` package's own source code (generators, stub builders, commands).

Do not hand-write CRUD boilerplate (migrations, requests, resources, repositories, services,
controllers) for a model that this package will generate — use the commands below instead, then
customize the generated files.

## The package is a dev-time generator, not a runtime dependency

`cubeta/cubeta-starter` is required with `--dev`. Never add a `use Cubeta\CubetaStarter\...;`
import, or otherwise call/extend/reference any class, enum, constant, facade or helper function
that lives under the package's own `Cubeta\CubetaStarter\*` namespace, from application code (models, controllers,
requests, providers, tests, config, ...). The app must boot and run without
the package installed.

This restriction does **not** cover the files the commands generate/publish *into the host app* —
those become part of the host app's own `App\*` namespace the moment they're written to disk, and
are meant to be used/extended freely: e.g. `App\Http\Resources\BaseResource\BaseResource`,
`App\Modules\ApiResponse`, `App\Http\Controllers\ApiController`, and every generated
model/repository/service/controller/resource/request/DTO. Only the package's own `src/` code is
off-limits.

## Documentation

The docs' Markdown source ships inside the package itself — read the file directly rather than
fetching a URL, it's the same content and works offline:
`vendor/cubeta/cubeta-starter/docs/<file>.md`. A rendered/browsable copy of the same content is
also published at **https://cubeta-io.github.io/cubeta-starter/<page>** if that's preferred.

| Topic                                                              | Local file (`vendor/cubeta/cubeta-starter/docs/`) | Page                               |
|--------------------------------------------------------------------|---------------------------------------------------|------------------------------------|
| Installation                                                       | `installation.md`                                 | `/installation`                    |
| Configuration (`cubeta-starter.php`, `cubeta-starter.config.json`) | `configuration.md`                                | `/configuration`                   |
| Feature overview                                                   | `features.md`                                     | `/features`                        |
| Basic usage walkthrough                                            | `usage.md`                                        | `/usage`                           |
| Full commands reference                                            | `commands.md`                                     | `/commands`                        |
| What gets published into the host app                              | `published-files.md`                              | `/published-files`                 |
| What gets generated per model                                      | `created-files.md`                                | `/created-files`                   |
| Anatomy of a generated model                                       | `created-model.md`                                | `/created-model`                   |
| Roles & permissions (actors)                                       | `permissions-usage.md`                            | `/permissions-usage`               |
| `BaseRepository`                                                   | `base-repository.md`                              | `/base-repository`                 |
| `BaseService`                                                      | `base-service.md`                                 | `/base-service`                    |
| `BaseResource`                                                     | `base-resource.md`                                | `/base-resource`                   |
| `ApiResponse`                                                      | `api-response.md`                                 | `/api-response`                    |
| `BaseExporter` / `BaseImporter` (Excel)                            | `base-exporter.md`, `base-importer.md`            | `/base-exporter`, `/base-importer` |
| `BaseBulkAction`                                                   | `base-bulk-action.md`                             | `/base-bulk-action`                |
| Feature test base                                                  | `main-test.md`                                    | `/main-test`                       |
| Translatable attributes                                            | `translatable-serializer.md`                      | `/translatable-serializer`         |
| Troubleshooting                                                    | `troubleshooting.md`                              | `/troubleshooting`                 |

If working inside the `cubeta-starter` package's own repository rather than a host app, the same
files are simply at `docs/<file>.md` from the repo root.

## Running commands non-interactively

Every `create:*` and `cubeta:install` command accepts all of its data as CLI arguments/options, so
it never needs to prompt. Always prefer a single explicit command line over the interactive flow:
pass every argument, add `--force` to overwrite files that already exist, and add `--no-interaction`
so any argument left unset falls back to a safe default (or the command fails fast with a clear
error naming the missing argument) instead of blocking on stdin.

Run `php artisan help <command>` at any time for a command's exact argument order, option list and
more examples.

## Argument string formats

| Argument            | Format                          | Example                                                       |
|---------------------|---------------------------------|---------------------------------------------------------------|
| attributes          | `field:type,field2:type2,...`   | `title:string,body:text,category_id:key,is_published:boolean` |
| relations           | `relatedModel:relationType,...` | `comments:hasMany,tags:manyToMany`                            |
| nullables / uniques | `field,field2,...`              | `slug,description`                                            |
| actor               | actor name or `none`            | `admin`                                                       |
| container           | `api` \| `web` \| `both`        | `api`                                                         |

Column types (`ColumnTypeEnum`): `integer, bigInteger, unsignedBigInteger, double, float, string,
text, json, boolean, date, time, dateTime, timestamp, file, key, translatable`.

- `key` → foreign id column + automatic `belongsTo` relation (don't also list it under `relations`).
- `translatable` → JSON column cast to a translatable value with a `ValidTranslatableJson` rule.

Relation types (`RelationsTypeEnum`): `belongsTo, hasMany, hasOne, manyToMany`.

## Stack installation — `cubeta:install`

`cubeta:install {name} {version=v1} {container?} {--validation=} {--force}`

| Plugin              | Purpose                                                            |
|---------------------|--------------------------------------------------------------------|
| `api`               | base api scaffolding (routes, base controller, exception handling) |
| `web`               | base blade scaffolding                                             |
| `web-packages`      | front-end packages/build tooling for the blade stack               |
| `auth`              | authentication controllers/endpoints for the chosen `container`    |
| `permissions`       | installs spatie/laravel-permission + role scaffolding              |
| `react-ts`          | Inertia + React + TypeScript scaffolding                           |
| `react-ts-packages` | front-end packages/build tooling for the react-ts stack            |

`--validation=FormRequest|DTO|Both` only applies to `api`/`web`/`react-ts` and controls whether
`create:model` generates a `FormRequest`, a validated DTO, or both for every model going forward.

```shell
php artisan cubeta:install api v1 --validation=DTO --force --no-interaction
php artisan cubeta:install web
php artisan cubeta:install react-ts && php artisan cubeta:install react-ts-packages
php artisan cubeta:install permissions
php artisan cubeta:install auth v1 api --force --no-interaction
```

## Model generators

### `create:model` — generate a model and (by default) its whole CRUD stack

`create:model {name?} {attributes?} {nullables?} {uniques?} {relations?} {actor?} {container?}
{--migration} {--request} {--dto} {--resource} {--factory} {--seeder} {--repository} {--service}
{--controller} {--web_controller} {--test} {--force}`

Generates everything (migration, model, request and/or DTO per the configured validation type,
resource, factory, seeder, repository, service, controller, web controller, test) unless one or
more of the `--xxx` flags is passed, in which case only those are generated.

```shell
php artisan create:model Post
php artisan create:model Post "title:string,body:text,category_id:key" "" "slug" "comments:hasMany" none api --force --no-interaction
php artisan create:model Post "title:string,body:text" "" "" "" none api --migration --factory --force --no-interaction
```

### `create:migration` — migration only

`create:migration {name?} {attributes?} {relations?} {nullables?} {uniques?} {--force}`

```shell
php artisan create:migration Post "title:string,body:text,category_id:key" "comments:hasMany" "" "slug" --force --no-interaction
```

### `create:request` — FormRequest only

`create:request {name?} {attributes?} {nullables?} {uniques?} {container?} {--force}`

```shell
php artisan create:request Post "title:string,body:text" "" "slug" api --force --no-interaction
```

### `create:dto` — validated DTO only

`create:dto {name?} {attributes?} {nullables?} {uniques?} {container?} {--force}`

Uses `wendelladriel/laravel-validated-dto`, as an alternative or addition to a `FormRequest` (see
`--validation=` on `cubeta:install`).

```shell
php artisan create:dto Post "title:string,body:text" "" "slug" api --force --no-interaction
```

### `create:resource` — API Resource only

`create:resource {name?} {attributes?} {relations?} {container?} {--force}`

```shell
php artisan create:resource Post "title:string,body:text" "comments:hasMany" api --force --no-interaction
```

### `create:factory` — model factory only

`create:factory {name?} {attributes?} {relations?} {uniques?} {--force}`

```shell
php artisan create:factory Post "title:string,body:text" "comments:hasMany" "slug" --force --no-interaction
```

### `create:seeder`, `create:repository`, `create:service` — name-only generators

`create:seeder {name?} {--force}`, `create:repository {name?} {--force}`, `create:service {name?}
{--force}`

```shell
php artisan create:seeder Post --force --no-interaction
php artisan create:repository Post --force --no-interaction
php artisan create:service Post --force --no-interaction
```

### `create:controller` — api CRUD controller only

`create:controller {name?} {actor?} {--force}`. Requires `cubeta:install api` first.

```shell
php artisan create:controller Post none --force --no-interaction
```

### `create:web-controller` — web controller + views/pages only

`create:web-controller {name?} {attributes?} {relations?} {nullables?} {actor?} {--force}`.
Requires `cubeta:install web` or `react-ts` first; generates blade views or React/Inertia pages
depending on which frontend stack is installed.

```shell
php artisan create:web-controller Post "title:string,body:text" "" "slug" none --force --no-interaction
```

### `create:test` — feature test only

`create:test {name?} {attributes?} {actor?} {--force}`

```shell
php artisan create:test Post "title:string,body:text" none --force --no-interaction
```

### `create:actor` — register a role

`create:actor {actor?} {permissions?} {container?} {--authenticated} {--force}`. Requires
`cubeta:install permissions` first.

```shell
php artisan create:actor admin
php artisan create:actor admin "can-read,can-edit" api --authenticated --force --no-interaction
```

## Generator pipeline (for package-internal changes)

If asked to modify the package itself rather than just use it, the mental model is:

1. **Command** (`src/Commands/Generators/Make*.php`, `src/Commands/Installers/Installer.php`) parses
   input and calls...
2. **`GeneratorFactory`** (`src/Generators/GeneratorFactory.php`) maps a source key to a generator
   class. It has classification lists (`independentFromContainer`, `notNeedForRelations`,
   `noNeedForColumns`, `getAllGeneratorsKeys`) that must be updated when adding a new generator.
3. **Generator** (`src/Generators/Sources/*`, `src/Generators/Installers/*`, extends
   `AbstractGenerator`) writes files into the host app using...
4. **Stub builders** (`src/Stub/Builders/*`) which assemble file contents from `.stub` templates in
   `src/Stub/stubs/`, injecting...
5. **StringValues** (`src/StringValues/*`) small reusable code snippets.

Conventions: use `src/Helpers/Naming.php` for casing (never hand-roll `Str::` conversions), use
`src/Helpers/CubePath.php`/`src/Helpers/FileUtils.php` for paths/directories, and report generation
problems via `src/Logs/CubeLog.php` instead of throwing. Host-app state (installed stacks, frontend
type, generated tables) lives in the host app's `cubeta-starter.config.json`, managed exclusively
through `src/Settings/Settings.php`. This internal-development guidance is the one exception where
touching `Cubeta\CubetaStarter\*` code is expected — it only applies when you are working inside the
`cubeta-starter` package's own repository, not inside a host application that merely consumes it.
