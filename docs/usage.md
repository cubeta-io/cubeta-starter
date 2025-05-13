# Usage

## Config
First I need you to take a look on the `cubeta-starter.php` file in the `config` directory and see the options
there .
The most elements in the config array are for the generated files directories and namespaces **except** :

1. `project_name` : the created postman collection will be named corresponding to it
2. `project_url` : here define your project public url, so we can add it to the postman collection
   if you let it **_null_** we will place it in the collection as like you're using xampp
   e.g :`http://localhost/example-project/public/` [read more about the generated postman
   collection](created-files#postman-collection)
3. `available_locales` : the package provides a way to store your table columns with their
   translations [(read more about it here)](translatable-serializer.md#translatable-attributes-handling) so in this case this situation you'll need to
   define your project available locales here
4. `default_locale` : here define your default project locales.
5. `version` : here you'll define the version of the generated code so your files we'll be structured based on it .

## The Package GUI

It is easy when you don't have to deal with the command line, so we've created an elegant interface to make your
experience smoother .
you can use it after installing the package by visiting the url :

`http://localhost/your-project-folder-name/public/cubeta-starter`

This interface will work only on the local environment .

## Available Commands

The package provide commands similar to the laravel base commands with the same functionality
with the benefit of getting a full generated CRUD support code from them .

the commands :

1. `php artisan cubeta:install api`
2. `php artisan cubeta:install web`
3. `php artisan cubeta:install auth`
4. `php artisan cubeta:install react-ts`
5. `php artisan cubeta:install react-ts-packages`
6. `php artisan cubeta:install web-packages`
7. `php artisan cubeta:install permissions`
8. `php artisan create:actor`
9. `php artisan create:model`
10. `php artisan create:migration`
11. `php artisan create:factory`
12. `php artisan create:seeder`
13. `php artisan create:request`
14. `php artisan create:repository`
15. `php artisan create:service`
16. `php artisan create:resource`
17. `php artisan create:controller`
18. `php artisan create:web-controller`
19. `php artisan create:test`

Now those commands depends on 7 parameters **_(of course not the 7 parameters required for each command)_** but I can
tell
you the most important one is the model name so for each command you have to provide the model name that the generated
file will target .

The seven parameters are :

| parameter                                       | type                                                |
|-------------------------------------------------|-----------------------------------------------------|
| Model name                                      | `string`                                            |
| Table columns and their types                   | `array[colName:string => colType:string]`           |
| The model relations                             | `array[relationName:string => relationType:string]` |
| The nullable columns for this model             | `array[colNames]`                                   |
| the unique columns for this model               | `array[colNames]`                                   |
| the actor _(this for the generated controller)_ | `string`                                            |
| container _(which mean api or web)_             | `string` in [api , web , both]                      |

All the commands accept those parameters but not all of them is required so instead of providing them manually just type
the command and the command will ask you about just the required parameters.

by default the package will handle the naming of your created files, so it will fit in the laravel naming convention
principle (like if you put _**products**_ as a model name the package will automatically set it to _**Product**_ ),
but it is always better to provide the right names to the commands instead on depending on them in case of facing an
unexpected behaviour .

> [!info]
> The `actor` parameter will define the actor on the created endpoints, and it is important for route placing and
> naming , you'll be asked about it if you used our roles permission tool via the
> command `php artisan cubeta:insall permissions`.

## Install Permissions Command

In previous versions we used `spatie/laravel-permission` which is a great package but to lightweight your project and
make it optional to you use it or not or to edit the way it works we have powered you with the
command `php artisan cubeta:install permissions` which will generate the following files for you :

1. RoleDoesNotExistException ( _exception class_ )
2. ActionsMustBeAuthorized ( _interface for models_ )
3. ModelHasPermission ( _model_ )
4. ModelHasRole ( _model_ )
5. Role ( _model_ )
6. HasPermissions ( _trait_ )
7. HasRoles ( _trait_ )
8. migration files for the generated models

now you can handle your actor roles and permissions easily and with an opinionated way

check the usage manual [here](permissions-usage.md#how-to-use-roles-permissions-tools) .

## Install Auth Command

To prepare your project to handle authentication and authorization we've provided a seamless way for this by executing
the command : `php artisan cubeta:install auth`
this command will initialize your project with the following :

1. BaseAuthController ( _Controller_ )
2. AuthLoginRequest ( _Form Request_ )
3. AuthRegisterRequest ( _Form Request_ )
4. CheckPasswordResetRequest ( _Form Request_ )
5. RequestResetPasswordRequest ( _Form Request_ )
6. ResetPasswordRequest ( _Form Request_ )
7. UpdateUserRequest ( _Form Request_ )
8. UserResource ( _Json Resource_ )
9. User.php ( _Model_ ) ( _this will override the existing User.php model file so be careful_ )
10. UserRepository ( _Repository Class_ )
11. UserService ( _Service Class_ )
12. IUserService ( _Service Interface_ )
13. 2014_10_12_000000_create_users_table.php ( _migration_ ) ( _this will override the existing users table migration
    file
    so be careful_ )
14. ResetPasswordCodeEmail ( _Notification_ )
15. set of routes for the dashboard authentication ( _for web_ )
16. reset-password-email.blade.php ( _view_ )
17. set of views for the authentication process ( _for web_ )
18. a set of pages to handle your authentication flow based on your frontend stack

Now as you see you are now ready to create your authentication endpoints powered up by those files and classes

> [!warning]
> Notice that running this command will ask you about overriding existing files this mean that any file has the same
> path and name as the generated files will be lost

Looking at the generated files you should focus on two files the first is : `UserService.php`
and `BaseAuthController.php` as you will notice that the **UserService** class handle the authentication logic for both
use cases ( _api and web_ ) and the class **BaseAuthController** use the **UserService** in a way that allows you to
extend it for every actor you want.

## Create Actor Command

This command will add the desired actor roles to your project like when you have (admin , customer , ....) roles in your
project

> [!note]
> using this command require you to install the Permissions Feature by executing the
> command `php artisan cubeta:install permissions`

> [!warning]
> make sure that your user model uses the `HasRoles` trait

#### **how to use it :**

1- run this command : `php artisan create:actor` so an output will appear :

```shell
What Is The Actor Name ? i.e:admin , customer , ... :    
> student
```

just put the name for this actor and press enter so the following output will appear :

```shell
Does This Actor Has A Specific Permissions You Want o Specify ? (student) (yes/no) [no]:   
```

Now the command asking if you would like to specify permissions for this role so just type in like this :

```shell
 Does This Actor Has A Specific Permissions You Want o Specify ? (student) (yes/no) [no]:                                                   
 > yes
```

Now just enter the permissions like this :

```shell
What Are (student) Permissions ?                                                                                                           
Write As Many Permissions You Want Just Keep Between Every Permissions And The Another A Comma i.e : can-read,can-index,can-edit:           
 > index,create,update,delete
```

Now the command will ask you about the container (as you'll see the package will distribute the generated endpoints to a
route files named after your project actors so when adding new actor new route file will be added for him, so you have
to
specify the container for that file)

```shell
 What Is The Container Type For This Operation :  [api]:                                                                                    
  [0] api                                                                                                                                   
  [1] web                                                                                                                                   
  [2] both                                                                                                                                  
 > 0 
```

Now this question will appear :

```shell
Do You Want To Create Authentication Api Controller For This Actor ? (yes/no) [yes]:                                                       
 > yes
```

by confirming you are generating Authentication controller for your actor .

The last question is :

```shell
Do You Want To Create Authentication Api Controller For This Actor ? (yes/no) [yes]:                                                       
 > yes
```

> [!note]
> You have to run `php artisan cubeta:install auth` if you'd like to create an authentication controller for your actor

Now you will notice that the there is multiple files generated and some changes in your project files:

1. **RolesPermissionsEnum.php** (basically in `app/Enums` Directory )
2. routes files named by your actor
3. **StudentAuthController.php**
4. RoleSeeder
5. **student.php** route file
6. the **RouteServiceProvider.php** in `app/Providers` directory has been changed to add the student.php route file to
   your app routes
7. the `student.php` and `public.php` route files has the auth routes for the generated controller

> [!warning]
> when overriding any file with the same name and directory of the generated file will be lost
> so be careful

> [!note]
> this command works with the generated files of these commands :  `cubeta:install permissions` , `cubeta:install auth`
> so make sure that you follow their steps before start adding actors

If you do a little check on `app/Enums/RolesPermissions.php` you'll find that your actor has been added to it .

now if you opened the generated **StudentAuthController** you'll see the following

```php
namespace App\Http\Controllers\API\v1;

use App\Enums\RolesPermissionEnum;
use App\Services\User\IUserService;

class StudentAuthController extends BaseAuthController
{
    public function __construct(IUserService $userService)
    {
        parent::__construct($userService);
        $this->roleHook(RolesPermissionEnum::STUDENT['role']);
    }
}
```

> [!note]
> all the namespaces and the generated directories are based on the config you defined in
> the `config/cubeta-starter.php` file

as this class extend the **BaseAuthController** controller class then it will has all public and protected methods
defined in it so if you opened the `routes/v1/api/public.php` route file you'll see that the generated routes are using
the same methods exist in the **BaseAuthController** class

now let us assume that you want to add a specific logic when you register a new student
in the **BaseAuthController** class you'll see that this is the register method :

```php
public function register(AuthRegisterRequest $request)
    {
        [$user , $token, $refresh_token] = $this->userService->register($request->validated(), $this->role);

        return rest()->ok()->data([
            'user' => new UserResource($user) ,
            'token' => $token ,
            'refresh_token' => $refresh_token
        ])->message(__('site.registered_successfully'))
        ->send();
    }
```

so in your generated controller i.e : **StudentAuthController** all what you have to do is to add the following method
to it :

```php
// or whatever namespace you defined in the package config file
use App\Http\Requests\AuthRequests\AuthRegisterRequest;

public function register(AuthRegisterRequest $request)
{
    // your specific logic
}
```

or if you want to extend its logic and save the **BaseAuthController** register method logic

```php
public function register(AuthRegisterRequest $request)
{
    // add your specific logic here ...
  
    return parent::register($request);
}
```

> [!note]
> the base purpose of this package isn't to strict you with specific ways to handle your project but is to give you
> classes and traits and tools in your project code so that you can edit them as you want so dig in and start scanning
> the code

## Generating Files

As mentioned [before](usage.md#available-commands) you can run every command in the list of the available commands
separately and for a full generated CRUD run `create:model` command without any options
like this :

1 - run this command : `php artisan create:model <YourMoodel>` then an output will show :

2 - write your model properties that will correspond to its table columns like this :

```shell
Enter your params like "name,started_at,...":
> name,email,password
```

then an output for each property of your model will appear like this :

```
What is the data type of the (( name field )) ? default is  [string]:
  [0 ] integer         
  [1 ] bigInteger      
  [2 ] unsignedBigInteger
  [3 ] unsignedDouble  
  [4 ] double          
  [5 ] float           
  [6 ] string          
  [7 ] json
  [8 ] text
  [9 ] boolean
  [10] date
  [11] time
  [12] dateTime
  [13] timestamp
  [14] file
  [15] key
  [16] translatable
```

those are the column type you just enter the number of the type

> [!note]
> the `key` type is a foreignId so if your column name is something like this : `user_id` you need to
> define it as a key type

### Translatable Column Type

Of course there isn't a column type called translatable in Laravel . to make it easier for you to use the prepared
things for the translation we called this column translatable in fact the generated column type is json but in this way
we've marked this column as translatable so the generated code will make sure to use the `ValidTranslatableJson` validation rule
on the validation and cast this column to `Translatable::class` .

the `Translatable::class` is a custom cast class you can check on it in `app/Casts` directory , and its job is to cast
the translated value (basically JSON string) to `\App\Serializers\Translatable` object which class that will make your
localized data handling easier [check on it here](translatable-serializer.md#translatable-attributes-handling).

The `ValidTranslatableJson` validation rule will make sure that the received json is simple and hasn't any nesting objects e.g :

```json
{
    "en": "name",
    "fr": "nom"
}
```

and not something like this for example

```json
[
    {
        "en": "name",
        "fr": [
            {
                "ar": "اسم"
            }
        ]
    }
]
```

and in addition to that it's make sure that the entered translation is corresponding to one of the locales defined in
the `config/cubeta-starter.php` config file if it is not it will return a validation error.

3 - then the output will be :

```shell
Is This Column Nullable ? (yes/no) [no]:
 > no

```

just chose whether the column is nullable or not

4 - then this question will appear :

```shell
Is This Column Unique ? (yes/no) [no]:
 > no
```

just chose whether the column is unique or not

```shell
Does (<YourModelName>) model related with another model by has many relation ? (yes/no) [no]:
 > yes
```

if you hit yes it will ask you about the name of the related model so just type it
then it will re-ask you if you have another has many related model just do the same

4 - this output will come next :

```shell 
Does (<YourModelName>) model related with another model by many to many relation ? (yes/no) [no]:
 > yes
```

so just like before type the model name if you're willing to hit yes

now we're done for this part

so you will find a :

1. your model class
2. migration for your model
3. factory for your model
4. seeder for your model
5. request for your model if you've chosen api instead of web
6. controller filled with CRUD methods
7. named resource rout for your controller will be appended in the appropriate route file
8. repository class for your model
9. service class and its interface for your model
10. test class for your model with a test for CRUD operations
11. postman collection

> [!warning]
> after the first generation a `cubeta-starter.config.js` file will be created in the base directory of your project
> for now this file is useless for you but helpful for us to make you generating experience better but in the coming
> releases it will give you a lot of features .

### Create Model Command Options

if you'd like you can generate the model with just a specific file you can just add the desired file as an option like
if you execute this command :

`php artisan create:model Product --controller --resource`

this command will generate the product model with a corresponding resource and controller .

> [!note]
> when not providing an option to the command it will generate the model and all its related files as we said before


**the available options for these commands are :**

1. `--migration`
2. `--request`
3. `--resource`
4. `--factory`
5. `--seeder`
6. `--repository`
7. `--service`
8. `--controller`
9. `--web_controller`
10. `--test`

> [!note]
> you cannot do the same thing using the gui . you have to generate every file alone
> but instead you have a **full generation operation** page to generate the whole files with the model
