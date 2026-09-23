## Cubeta Starter

Cubeta Starter (`cubeta/cubeta-starter`) is a Laravel **code generator**, required as a `--dev`
dependency. It scaffolds a full CRUD "slice" for a model — migration, model, form request/DTO, api
resource, factory, seeder, repository, service, controller, web controller and feature test — from
`php artisan create:*` commands, following a Repository + Service design.

The full documentation source ships with the package itself as plain Markdown at
`vendor/cubeta/cubeta-starter/docs/*.md` — read those files directly for anything not covered here
or in the `cubeta-starter-development` skill, especially `commands.md`, `usage.md`,
`configuration.md`, `permissions-usage.md` and `troubleshooting.md`. The same content is also
published at **https://cubeta-io.github.io/cubeta-starter/** if a rendered/browsable version is
preferred.

### Critical rule: never depend on the package's own namespace at runtime

`Cubeta\CubetaStarter\*` (its classes, enums, constants, facades, helper functions, GUI
routes/controllers, everything under the package's own `src/`) is a **build-time tool only**. Do
**not**:
- add a `use Cubeta\CubetaStarter\...;` import anywhere in the host application's code,
- call, extend, or reference any `Cubeta\CubetaStarter\*` class/enum/constant/function from
  application code (models, controllers, requests, tests, config, service providers, etc.),
- rely on the package being present outside of local/dev (`composer require cubeta/cubeta-starter
  --dev`) — it must not be required for the app to boot or run in production.

This does **not** apply to the files the commands *publish/generate into the host app* (e.g.
`app/Http/Resources/BaseResource/BaseResource.php`, `app/Modules/ApiResponse.php`,
`app/Http/Controllers/ApiController.php`, and every generated model/controller/repository/service).
Once generated, those live in the host app's own `App\` namespace and are yours to use, extend and
edit freely — only the `Cubeta\CubetaStarter\*` package code itself is off-limits.

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
existing model). See the `cubeta-starter-development` skill for the full per-command reference, or
`php artisan help <command>`.

Run every command as a single non-interactive line: pass every argument explicitly plus `--force`
(overwrite existing files) and `--no-interaction` (skip any prompt and fall back to safe defaults
instead of hanging):

@verbatim
<code-snippet name="Generate a full model (model, migration, request, resource, factory, seeder, repository, service, controller, test)" lang="shell">
php artisan create:model Post "title:string,body:text,category_id:key,is_published:boolean" "" "slug" "comments:hasMany" none api --force --no-interaction
</code-snippet>
@endverbatim

Argument order for `create:model` (and most `create:*` commands): `name attributes nullables
uniques relations actor container`.

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

Full command reference: `php artisan list create`, `php artisan help <command>`, the
`cubeta-starter-development` skill, or `vendor/cubeta/cubeta-starter/docs/commands.md`.
