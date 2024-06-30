## How To Use Roles Permissions Tools

In this section we will talk about the roles permissions tools usage .

after installing the tools using `php artisan cubeta:install permissions`run your migration by this
command `php artisan migrate`

## Roles

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

## Permissions

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
    public static function authorizedActions(): array
    {
        return[
            'index' ,
            'show' ,
            // the rest of the authorized actions
        ];
    }
    
    // the rest of the model code
}
```

this is required for checking if the actions on this model has to be authorized by specific permissions

so based on the provided **Product** model example if you do this checks  :

```php
auth()->user()->assignPermission(['index' , 'show']);

auth()->user()->hasPermission('index' , Product::class); // true

auth()->user()->hasPermission('create' , Product::class); // true 
//because the permission don't have an authorized action in the model

auth()->user()->hasPermission('show' , Product::class); // false
```

> [!note]
> this check will check if one of the user roles has the provided permission and return true if exist .


```php
$adminRole = Role::getByName('admin')->assignPermission('index' , Product::class);

auth()->user()->assignRole("admin");

auth()->user()->hasPermission('index') // true
```

## Abilities

sometimes you want to specify the permissions on a specific records in the database like that the user can just delete
his products so the permissions feature can handle such cases .

let us assume the following

Product Model :

```php
namespace App\Models;

use App\Interfaces\ActionsMustBeAuthorized;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements ActionsMustBeAuthorized
{
    public static function authorizedActions(): array
    {
        return[
            'index' ,
            'show' ,
            'delete'
            // the rest of the authorized actions
        ];
    }
    // the rest of the model code
    
    public function canDelete() : bool {
        return auth()->user()->id == $this->user_id
    }
}
```

as you see we've added delete action to the authorized actions and added new public method ( _canDelete_ ) which returns
a bool value
this method implement the ability .
now you can provide a model instance to the **hasPermission** method so the method will automatically check for such
abilities like this :

```php
$authUserProduct = Product::factory()->create(['user_id' => auth()->user()->id]) ; 
$otherUserProduct = Product::factory()->create(['user_id' => 'some_user_id']);

auth()->user()->hasPermission('delete' , Product::class , $authUserProduct); // true

auth()->user()->hasPermission('delete' , Product::class , $otherUserProduct); // false
```

you can add as much as you want abilities as they match the pattern : _can**AuthorizedAction**_ in camel case and the
method must returns a boolean value

even for permissions you have a scope **byPermission()** to get all roles or users for a specific permission

```php
\App\Models\User::query()->byPermission('index' , Product::class)->get();

\App\Models\Role::query()->byPermission('index' , Product::class)->get();
```


finally you can remove user or role permission by this : 

```php
auth()->user()->removePermission('index' , Product::class);
```


