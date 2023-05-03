**Introduction**

 cubeta-starter is a package that will help you create your CRUDES easier than before with a pretty much        everything you will need

 **it is using Repository and Service design pattern so every model created there will be a corresponding repository class and service class**

Each created model will has a : 
- migration file 
- controller class
- form request class
- resource class
- factory class
- seeder class  
- repository class 
- service class  
- test class
- policy class

and a postman collection file will be generated for the whole model created (models creat by the package)

**installation**

1 - in your project root directory open packages directory (if it doesn't create it) then add this directory : `cubeta/cubeta-starter` then in the created directory clone this project

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



**Usage**

1 - run this command : php artisan create:model <YourMoodel> then an output will show : 

`Enter your params like "name,started_at,...":`

2 - write your model properties that will correspond to its table columns like this : 

`name,emai,password`

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

 - **notice** : the key type is a foreignId so if your column name is something like this : > user_id you need to define it as a key type 

 3 - an output will show asking you if you want it for api or for web or for both **but now only api version is available**  so select api 
 
 an output will appear : 
 
> Does This Model Has Translations ? 
> Notice That You Need To Run php artisan cubeta-init And Initialize Translatable Package [No]:
>   [0] No
>   [1] Yes

don't care about this command `php artisan cubeta-init` right now we will talk about it later

if you selected `yes` the output will be : 

 > Enter The Attributes That's Have Translations :
> 
>  Your Model Attributes :name
> 
>  Take Care Of Writing The Attributes in The Same Names

just type the columns names that need to have translations like this : `description,title`

4 - then the output will be : 

 > Does this model related with another model by has many relation ? [No]:
>   [0] No
>   [1] Yes

if you hit yes it will ask you about the name of the related model so just type it
then it will reask you if you have another has many related model just do the same 

5 - this output will come next : 

>  Does this model related with another model by many to many relation ? [No]:
>   [0] No
>   [1] Yes

so just like before type the model name if you're willing to hit yes

now we're done for this part

so you will find a : 
1- your model calss in your `app/models` directory
2- a migration for your model in `database/migrations` directory
3- a factory for your model in database/factories directory
4- a seeder for your model in database/seeders directory
5- a request for your model in app/Http/Requests/YourModelName directory
6- a resource for Your model in app/Http/Resources directory 
7- a controller in the app/Http/controllers/API/v1 directory filled with CRUD methodes
8- a named apiresource rout for your controller will be appended in the routes/api.php file
9- a repository class for your model in the app/Repositories directory
10- a service class and its interface for your model in the app/Services/YourModelName directory 
11- a test class for your model with a test for CRUD operations in the tests/Feature directory
12- a postman collection in your project root directory 
