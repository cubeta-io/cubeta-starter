## How To Use Roles & Permissions Tools

Cubeta Starter's roles & permissions feature is built on top of [`spatie/laravel-permission`](https://spatie.be/docs/laravel-permission), a well-established, battle-tested authorization package. The command `php artisan cubeta:install permissions` does the following for you:

1. Requires `spatie/laravel-permission` into your project via Composer.
2. Publishes Spatie's own migrations and `config/permission.php` (via `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`).
3. Adds the `Spatie\Permission\Traits\HasRoles` trait to your `User` model.
4. Registers the `role`, `permission`, and `role_or_permission` middleware aliases in `bootstrap/app.php` (`Spatie\Permission\Middleware\RoleMiddleware`, `PermissionMiddleware`, and `RoleOrPermissionMiddleware`).

After installing, run your migrations:

```bash
php artisan migrate
```

## Roles & Permissions API

Once installed, your `User` model (or any model you add `HasRoles` to) has the full Spatie API available: `assignRole()`, `hasRole()`, `removeRole()`, `givePermissionTo()`, `hasPermissionTo()`, `can()`, and more, plus the `Spatie\Permission\Models\Role` and `Spatie\Permission\Models\Permission` models.

```php
auth()->user()->assignRole('admin');

auth()->user()->hasRole('admin'); // bool

auth()->user()->givePermissionTo('edit articles');

auth()->user()->can('edit articles'); // bool
```

This page won't re-document Spatie's full API — refer to the [official `spatie/laravel-permission` documentation](https://spatie.be/docs/laravel-permission) for everything else (teams, super-admins, permission caching, Blade directives, etc.).

## `create:actor`

Cubeta Starter still provides `php artisan create:actor` to register an actor (role) for your project on top of Spatie's models. For a given actor it generates:

- A `RolesPermissionEnum::<ROLE>` const describing the role's name and its declared permissions, stored in `app/Enums/RolesPermissionEnum.php`.
- A `RoleSeeder` that creates the corresponding `Spatie\Permission\Models\Role` records:

```php
use Spatie\Permission\Models\Role;

Role::firstOrCreate(['name' => $role['role']]);
```

- A route group for the actor guarded by Spatie's `role` middleware, e.g. `'role:admin'` (previously `'has-role:admin'`).

Run the seeder after generating an actor:

```bash
php artisan db:seed RoleSeeder
```

> [!NOTE]
> `create:actor` requires the permissions feature to already be installed (`cubeta:install permissions`), and — for an authentication controller — the auth feature (`cubeta:install auth`).

## Further reading

For anything beyond the basics shown above — permission caching, teams/multi-tenancy, wildcard permissions, Blade directives (`@role`, `@can`), and the full middleware syntax — see the [`spatie/laravel-permission` documentation](https://spatie.be/docs/laravel-permission).
