<h1 id="cubeta-permissions-usage">How To Use Roles Permissions Tools </h1>

In this section we will talk about the roles permissions tools usage .

after installing the tools using `php artisan cubeta:install-permissions`run your migration by this
command `php artisan migrate`

**Roles :** <br>
firstly you should make sure that the User model or whatever model you want to power it with the roles ,permissions
feature is using the trait `HasRoles` like this :

```php
namespace App\Models;
use App\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles
}
```

fill the roles table with your roles or just run the `RoleSeeder.php` which has been generated if you've used the
command `php artisan create:actor`

now simply you can check if a user has a specific role by this :

```php
auth()->user()->hasRole('admin');
```

this method will return a boolean value and throw an exception if the provided role doesn't exist in the roles table

you can give a specific use a role with this method :

```php
\App\Models\User::find(1)->assignRole('admin');
```

this method will return the same user instance and throw an exception if the role doesn't exist in the roles table.

you can get all the user roles by just calling the roles relation like this :

```php
auth()->user()->roles()->get();
```

and you have the scope `byRole` if you'd like to get all the users with a specific role

```php
\App\Models\User::where('email' , 'email@test.com')->byRole('admin')->get();
```

you can remove a role from the user by doing this :

```php
auth()->user()->removeRole('admin');
```

**Permissions :** <br>

permissions can be assigned to a user or to permission over a specific model
like when you'd like to give the user an index permission for the product model .

this can be achieved like this :

```php
auth()->user()->assignPermission('index' , \App\Models\Product::class)

// or provide an array of permissions like this

auth()->user()->assignPermission(['index' , 'show'] , \App\Models\Product::class)

// or assign the permission for a role : 

\App\Models\Role::getByName('admin')->assignPermission(['index' , 'show'] , \App\Models\Product::class);
```

now for every specific model permission you should implement the `App\Interfaces\ActionsMustBeAuthorized` interface and
implement the `authorizedActions():array` static method
so for example your model should look like this :

```php
namespace App\Models;

use App\Interfaces\ActionsMustBeAuthorized;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements ActionsMustBeAuthorized
{

    protected $fillable = [
                'name',
                'title',
                'category_id',
                'image',
            ];

    public static function authorizedActions(): array
    {
        return[
            'index' ,
            'create' ,
        ];
    }
}
```

this is required for checking if the actions on this model has to be authorized by specific permissions

