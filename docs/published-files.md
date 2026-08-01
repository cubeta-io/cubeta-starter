# Published Files

When you run an install command, Cubeta Starter publishes a set of base classes, traits, and helpers into your application. This page explains what each file is and why it's there, so you know exactly what lives in your codebase.

All published files become **part of your app** — you own them and can edit them freely. The package does not hide logic in `vendor/`.

---

## Published by `cubeta:install api` and `cubeta:install web`

### Configuration

| File | Purpose |
|------|---------|
| `config/cubeta-starter.php` | Package configuration — paths, namespaces, locales, version. See [Configuration](configuration.md) |
| `pint.json` | Laravel Pint formatting preset |

### Core Modules

| File | Purpose |
|------|---------|
| `app/Modules/ApiResponse.php` | Standardized API response builder. Used via the `rest()` helper. See [ApiResponse](api-response.md) |
| `app/Helpers/helpers.php` | Global helper functions, including `rest()` |

### Base Classes (Repository + Service pattern)

| File | Purpose |
|------|---------|
| `app/Repositories/Contracts/BaseRepository.php` | Abstract repository with CRUD, search, filtering, ordering, pagination, and Excel import/export. See [BaseRepository](base-repository.md) |
| `app/Services/Contracts/BaseService.php` | Abstract service delegating to a repository. See [BaseService](base-service.md) |

### Resources

| File | Purpose |
|------|---------|
| `app/Http/Resources/BaseResource/BaseResource.php` | Base JSON resource all generated resources extend. See [BaseResource](base-resource.md) |
| `app/Http/Resources/BaseResource/AnonymousResourceCollection.php` | Custom collection wrapper for consistent pagination formatting |

### Casts & Serializers

| File | Purpose |
|------|---------|
| `app/Casts/Translatable.php` | Eloquent cast for translatable (multi-locale) columns |
| `app/Casts/MediaCast.php` | Eloquent cast for file/media columns — handles storage and cleanup |
| `app/Serializers/Translatable.php` | Value object for reading/writing localized strings (`$model->title->en`). See [Translatable](translatable-serializer.md) |
| `app/Serializers/SerializedMedia.php` | Value object representing a stored file (path, url, existence) |

### Validation Rules

| File | Purpose |
|------|---------|
| `app/Rules/ValidTranslatableJson.php` | Ensures translatable input is flat JSON matching your configured locales |
| `app/Rules/MediaValidationRule.php` | Validates uploaded files |

### Excel Import/Export

| File | Purpose |
|------|---------|
| `app/Excel/BaseExporter.php` | Base exporter used by `export()`. See [BaseExporter](base-exporter.md) |
| `app/Excel/BaseImporter.php` | Base importer used by `import()`. See [BaseImporter](base-importer.md) |

### Bulk Actions

| File | Purpose |
|------|---------|
| `app/BulkAction/BaseBulkAction.php` | Base class for bulk operations on multiple records. See [BaseBulkAction](base-bulk-action.md) |

### Middleware & Controllers

| File | Purpose |
|------|---------|
| `app/Http/Middleware/AcceptedLanguagesMiddleware.php` | Sets the app locale from the `Accept-Language` header. Registered as the `locale` alias in `bootstrap/app.php` |
| `app/Http/Controllers/ApiController.php` | Base API controller (api install) |
| `app/Http/Controllers/WebController.php` | Base web controller (web install) |
| `app/Http/Controllers/SetLocaleController.php` | Endpoint to switch the active locale |

---

## Additionally published by `cubeta:install web`

The Blade stack also publishes:

| File | Purpose |
|------|---------|
| `app/Providers/CubetaStarterServiceProvider.php` | Registers the published Blade components. Added to `bootstrap/providers.php` |
| `resources/views/components/*` | Reusable Blade form and layout components |
| `resources/views/includes/*` | Shared Blade partials |
| `resources/views/layout.blade.php` | Base dashboard layout |
| `resources/js/*`, `resources/css/*` | Blade stack assets |
| `.prettierignore` | Prettier ignore rules |
| `lang/en/site.php` | Translation strings used by generated messages |

---

## Published by `cubeta:install react-ts`

The Inertia/React/TypeScript stack publishes a full dashboard toolkit under `resources/js/`:

| Path | Purpose |
|------|---------|
| `resources/js/components/dashboard/*` | Dashboard shell components |
| `resources/js/components/datatable/*` | Data table with sorting, search, and pagination |
| `resources/js/components/form/*` | Form field components (inputs, selects, file uploads, etc.) |
| `resources/js/components/layouts/*` | Page layouts |
| `resources/js/components/ui/*` | UI primitives (gallery, image preview, page cards, detail items) |
| `resources/js/hooks/*`, `resources/js/providers/*`, `resources/js/models/*` | Hooks, context providers, and TypeScript models |
| `resources/js/cubeta-starter.tsx`, `global.d.ts`, `vite-env.d.ts`, `helper.ts` | Entry point, types, and helpers |
| `vite.config.js` | Vite configuration for the stack |
| `app/Http/Middleware/HandleInertiaRequestsMiddleware.php` | Inertia middleware, registered in `bootstrap/app.php` |

It also publishes the same casts, serializers, rules, base resource, `ApiResponse`, and Excel classes listed above.

---

## Published by `cubeta:install auth`

See [Usage → Install Auth Command](usage.md#install-auth-command) for the full list. In summary:

| File | Purpose |
|------|---------|
| `app/Http/Controllers/.../BaseAuthController.php` | Base authentication controller you extend per actor |
| `app/Http/Requests/AuthRequests/*` | Login, register, reset-password, and update-user requests |
| `app/Http/Resources/UserResource.php` | User JSON resource |
| `app/Models/User.php` | **Overwrites** your User model |
| `app/Services/User/UserService.php` | Authentication business logic (api + web) |
| `app/Notifications/ResetPasswordCodeEmail.php` | Password-reset notification |
| `database/migrations/..._create_users_table.php` | **Overwrites** the users migration |
| Auth routes & views | Registered for your chosen stack |

---

## Published by `cubeta:install permissions`

A lightweight, self-contained authorization system (no external package). See [Permissions Usage](permissions-usage.md).

| File | Purpose |
|------|---------|
| `app/Models/Role.php` | Role model |
| `app/Models/ModelHasRole.php`, `app/Models/ModelHasPermission.php` | Pivot models |
| `app/Traits/HasRoles.php` | Adds role methods (`assignRole`, `hasRole`, `byRole` scope, …) to a model |
| `app/Traits/HasPermissions.php` | Adds permission methods (`assignPermission`, `hasPermission`, abilities, …) |
| `app/Interfaces/ActionsMustBeAuthorized.php` | Interface for models with authorized actions |
| `app/Exceptions/RoleDoesNotExistException.php` | Thrown for unknown roles |
| `database/migrations/*` | Roles, permissions, and pivot tables |

---

## The Config State File

> [!NOTE]
> Separate from `config/cubeta-starter.php`, a `cubeta-starter.config.json` file is created at your **project root** after the first generation. It records your generated tables, chosen frontend stack, and which features you've installed. The package reads it to power the GUI and make smarter generation decisions. Keep it in version control; don't edit it by hand.
