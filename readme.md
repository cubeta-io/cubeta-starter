<h1 id="introduction">Introduction</h1>

cubeta-starter is a package that will help you create your CRUDS easier than before with a pretty much everything you
will need

_<p> it is using Repository and Service design pattern so every model created there will be a corresponding repository
class and service class</p>_

Each created model will have a :

- migration file
- controller
- form request
- resource
- factory
- seeder
- repository
- service
- test
- policy

and a postman collection file will be generated for the whole model created (models creat by the package)

<hr>

**<h1 id="installation">installation</h1>**

1 - in your project root directory open packages directory (if it doesn't create it) then add this
directory : `cubeta/cubeta-starter` then in the created directory clone this project

2 - open _composer.json_ file in your root directory and add this code :

```
"repositories": {
        "quickmetrics-laravel":{
            "type": "path" ,
            "url": "packages/cubeta/cubeta-starter" ,
            "options": {
                "symlink": true
            }
        }
    },
```

then in the require-dev entity add this line : `"cubeta/cubeta-starter" : "@dev",`

3 - run composer install


<hr>


**<h1 id="usage">Usage</h1>**

1 - run this command : `php artisan create:model <YourMoodel>` then an output will show :

`Enter your params like "name,started_at,...":`

2 - write your model properties that will correspond to its table columns like this :

`name,email,password`

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
```

those are the column type you just enter the number of the type

- **notice** : the key type is a foreignId so if your column name is something like this : ``user_id`` you need to
  define it as a key type

3 - an output will show asking you if you want it for api or for web or for both **but now only api version is
available**  so select api

an output will appear :

> Does This Model Has Translations ?
> Notice That You Need To Run php artisan cubeta-init And Initialize Translatable Package [No]:
> [0] No
> [1] Yes

don't care about this command `php artisan cubeta-init` right now we will talk about it later

if you selected `yes` the output will be :

> Enter The Attributes That's Have Translations :
>
>  Your Model Attributes :description,title,paragraph,author
>
>  Take Care Of Writing The Attributes in The Same Names

just type the columns names that need to have translations like this : `description,title`

4 - then the output will be :

> Does this model related with another model by has many relation ? [No]:
> [0] No
> [1] Yes

if you hit yes it will ask you about the name of the related model so just type it
then it will reask you if you have another has many related model just do the same

5 - this output will come next :

> Does this model related with another model by many to many relation ? [No]:
> [0] No
> [1] Yes

so just like before type the model name if you're willing to hit yes

now we're done for this part

so you will find a :

1. your model class in your `app/models` directory
1. a migration for your model in `database/migrations` directory
1. a factory for your model in `database/factories` directory
1. a seeder for your model in `database/seeders` directory
1. a request for your model in `app/Http/Requests/YourModelName` directory
1. a resource for Your model in `app/Http/Resources` directory
1. a controller in the `app/Http/controllers/API/v1 directory` filled with CRUD methods
1. a named apiresource rout for your controller will be appended in the `routes/api.php` file
1. a repository class for your model in the `app/Repositories` directory
1. a service class and its interface for your model in the `app/Services/YourModelName` directory
1. a test class for your model with a test for CRUD operations in the `tests/Feature` directory
1. a postman collection in your project root directory

**<h2 id="cubeta-init-command">cubeta-init command</h2>**

now this command will Initialize your project on specific roles :

**<label for="cubeta-init-command">how to use it :</label>**

1- run this command : `php artisan cubeta-init` so an output will appear :

> Does Your Project Has Multi Actors ? [No]: <br>
> [0] No <br>
> [1] Yes <br>

if you hit yes then the output will be :
> How Many Are They ? [2] :

enter the number of your actors : like 1 or 2 ,or , ..... <br>
so you'll get this : <br>
> Actor Number : 0
>
>What Is The Name Of This Actor ? eg:admin,customer:

so just type the name of your first actor <br>
then for every actor of yours a question like that will appear <br>

2 - for every actor an output will appear :
> Does This Actor Has Permissions ? eg : can-edit , can-read , can publish , .... [No]: <br>
> [0] No <br>
> [1] Yes <br>

hitting yes will output this :
> What Are The Permissions For This Actor <br>
> Please Note That You Have To Type Them like This : <br>
> can-edit , can-read , can publish , ....: <br>

just type your actor permissions and you're okay . <br>

this will initialize your actors permissions in the database seeders and make them in one Enum so you can reach
them <br>
and in the `routes/api` directory you will find a routes file named after your actor where each actor will have his
endpoints in it <br>
and in the `app/Enums` directory you'll find a `RolesPermissionEnum.php` file which contain Enums represent your actors
and their permissions (_it is better to check on them_) <br>

3 - then an output will show :
> Does Your Project Need To Use Translation Package ? (astrotomic/laravel-translatable) [No]: <br>
[0] No <br>
[1] Yes <br>

if you have translations for a specific model then you need to hit yes <br>
_(for that when the model command asked you about cubeta-init command)_
