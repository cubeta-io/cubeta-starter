## Cubeta Starter

Cubeta Starter (`cubeta/cubeta-starter`) is a Laravel code generator package. It scaffolds a full
CRUD "slice" for a model — migration, model, form request/DTO, api resource, factory, seeder,
repository, service, controller, web controller and feature test — from `php artisan create:*`
commands, following a Repository + Service design.

Prefer these generator commands over hand-writing this boilerplate. Never hand-edit the
`cubeta-starter.config.json` file at the project root — it is the package's own state (installed
stacks, frontend type, generated tables) and is read/written by the commands themselves.

### One-time stack setup

Before generating model files, the relevant stack must be installed once:

@verbatim
<code-snippet name="Install stacks" lang="shell">
php artisan cubeta:install api                 # base api scaffolding
php artisan cubeta:install web                 # base blade scaffolding
php artisan cubeta:install react-ts             # Inertia + React + TypeScript scaffolding
php artisan cubeta:install permissions          # spatie/laravel-permission + role scaffolding
php artisan cubeta:install auth v1 api          # auth endpoints for the given container
</code-snippet>
@endverbatim

### Generating a model and its CRUD files

`create:model` is the main entry point and, by default, generates every related file. Every
`create:*` command also works standalone (e.g. `create:migration` to only add a migration to an
existing model).

All of these commands can be run as a single non-interactive line — pass every argument
explicitly plus `--force` (overwrite existing files) and `--no-interaction` (skip any prompt and
fall back to safe defaults instead of hanging):

@verbatim
<code-snippet name="Generate a full model (model, migration, request, resource, factory, seeder, repository, service, controller, test)" lang="shell">
php artisan create:model Post "title:string,body:text,category_id:key,is_published:boolean" "" "slug" "comments:hasMany" none api --force --no-interaction
</code-snippet>
@endverbatim

Argument order for `create:model` (and most `create:*` commands): `name attributes nullables
uniques relations actor container`. Run `php artisan help create:model` (or `help` on any other
`create:*` command) for the exact argument order and options of that command — it documents the
formats below plus runnable examples.

### Argument formats

- **attributes** — `"field:type,field2:type2,..."`. Supported types: integer, bigInteger,
  unsignedBigInteger, double, float, string, text, json, boolean, date, time, dateTime, timestamp,
  file, key, translatable. A `key` column (e.g. `category_id`) automatically creates a `belongsTo`
  relation and foreign id migration column — do not also list it in `relations`.
- **relations** — `"relatedModel:relationType,..."`. Supported types: belongsTo, hasMany, hasOne,
  manyToMany.
- **nullables** / **uniques** — comma separated column names, e.g. `"slug,description"`.
- **actor** — the role allowed to use the generated endpoints (see `create:actor`), or `"none"`.
- **container** — `api`, `web` or `both`.

### Useful standalone commands

@verbatim
<code-snippet name="Standalone generators" lang="shell">
php artisan create:migration Post "title:string,category_id:key" "comments:hasMany" --force --no-interaction
php artisan create:actor admin "can-read,can-edit" api --force --no-interaction
php artisan create:controller Post none --force --no-interaction
</code-snippet>
@endverbatim

Full command reference: `php artisan list create` and `php artisan help <command>`.