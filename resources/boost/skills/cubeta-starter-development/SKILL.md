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

## Running commands non-interactively

Every `create:*` and `cubeta:install` command accepts all of its data as CLI arguments/options, so
it never needs to prompt. Always prefer a single explicit command line over the interactive flow:
pass every argument, add `--force` to overwrite files that already exist, and add `--no-interaction`
so any argument left unset falls back to a safe default (or the command fails fast with a clear
error) instead of blocking on stdin.

```shell
php artisan create:model Post "title:string,body:text,category_id:key" "" "slug" "comments:hasMany" none api --force --no-interaction
```

Run `php artisan help <command>` for a command's exact argument order, option list and more
examples — every command documents its own format.

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

## Command reference

One-time stack setup (`cubeta:install {plugin} {version=v1} {container?} {--validation=} {--force}`):

| Plugin              | Purpose                                                            |
|---------------------|--------------------------------------------------------------------|
| `api`               | base api scaffolding (routes, base controller, exception handling) |
| `web`               | base blade scaffolding                                             |
| `web-packages`      | front-end packages/build tooling for the blade stack               |
| `auth`              | authentication controllers/endpoints for the chosen container      |
| `permissions`       | installs spatie/laravel-permission + role scaffolding              |
| `react-ts`          | Inertia + React + TypeScript scaffolding                           |
| `react-ts-packages` | front-end packages/build tooling for the react-ts stack            |

```shell
php artisan cubeta:install api v1 --validation=DTO --force --no-interaction
php artisan cubeta:install auth v1 api --force --no-interaction
```

Model generators (all support `--force` and `--no-interaction`):

| Command                 | Signature (order)                                                                                                                                                                     | Notes                                                                                                                         |
|-------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------|
| `create:model`          | `name attributes nullables uniques relations actor container [--migration --request --dto --resource --factory --seeder --repository --service --controller --web_controller --test]` | Generates everything by default; pass one or more `--xxx` flags to limit to a subset.                                         |
| `create:migration`      | `name attributes relations nullables uniques`                                                                                                                                         |                                                                                                                               |
| `create:request`        | `name attributes nullables uniques container`                                                                                                                                         | FormRequest validation.                                                                                                       |
| `create:dto`            | `name attributes nullables uniques container`                                                                                                                                         | wendelladriel/laravel-validated-dto based DTO, alternative/addition to FormRequest (see `--validation=` on `cubeta:install`). |
| `create:resource`       | `name attributes relations container`                                                                                                                                                 | Eloquent API Resource.                                                                                                        |
| `create:factory`        | `name attributes relations uniques`                                                                                                                                                   |                                                                                                                               |
| `create:seeder`         | `name`                                                                                                                                                                                |                                                                                                                               |
| `create:repository`     | `name`                                                                                                                                                                                |                                                                                                                               |
| `create:service`        | `name`                                                                                                                                                                                |                                                                                                                               |
| `create:controller`     | `name actor`                                                                                                                                                                          | Api CRUD controller, requires `cubeta:install api`.                                                                           |
| `create:web-controller` | `name attributes relations nullables actor`                                                                                                                                           | Web controller + views/pages, requires `cubeta:install web` or `react-ts`.                                                    |
| `create:test`           | `name attributes actor`                                                                                                                                                               | Feature test exercising the CRUD api endpoints.                                                                               |
| `create:actor`          | `actor permissions container [--authenticated]`                                                                                                                                       | Registers a role via spatie/laravel-permission, requires `cubeta:install permissions`.                                        |

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
through `src/Settings/Settings.php`.
