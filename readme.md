<h1>Content</h1>

- <a href="#introduction">Introduction</a>
- <a href="#installation">Installation</a>
- <a href="#usage">Usage</a>
  - <a href="#cubeta-init-command">cubeta-init Command</a>
- <a href= "#created-classes-and-files">Created Classes And Files</a>
    - <a href= "#models">Models</a>
    - <a href= "#migrations">Migrations</a>
    - <a href= "#controllers">Controllers</a>
    - <a href="#requests">Requests</a>
    - <a href="#resources">Resources</a> 
    - <a href="#factories">Factories</a>
    - <a href="#seeders">Seeders</a>
    - <a href="#repositories">Repositories</a>
    - <a href="#services">Services</a>
    - <a href="#tests">Tests</a>
    - <a href="#policies">Policies</a>


<h1 id="introduction">Introduction</h1>
<p>
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

</p>


**<h1 id="installation">installation</h1>**

<p>
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

</p>


**<h1 id="usage">Usage</h1>**

<p>
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
> Your Model Attributes :description,title,paragraph,author
>
> Take Care Of Writing The Attributes in The Same Names

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
2. a migration for your model in `database/migrations` directory
3. a factory for your model in `database/factories` directory
4. a seeder for your model in `database/seeders` directory
5. a request for your model in `app/Http/Requests/YourModelName` directory
6. a resource for Your model in `app/Http/Resources` directory
7. a controller in the `app/Http/controllers/API/v1 directory` filled with CRUD methods
8. a named apiresource rout for your controller will be appended in the `routes/api.php` file
9. a repository class for your model in the `app/Repositories` directory
10. a service class and its interface for your model in the `app/Services/YourModelName` directory
11. a test class for your model with a test for CRUD operations in the `tests/Feature` directory
12. a postman collection in your project root directory

</p>

**<h2 id="cubeta-init-command">cubeta-init command</h2>**

<p>
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
> What Is The Name Of This Actor ? eg:admin,customer:

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

this will initialize your actors permissions in the database seeders and make them in one Enum, so you can reach
them <br>
and in the `routes/api` directory you will find a routes file named after your actor where each actor will have his
endpoints in it <br>
and in the `app/Enums` directory you'll find a `RolesPermissionEnum.php` file which contain Enums represent your actors
and their permissions (_it is better to check on them_) <br>

3 - then an output will show :

> Does Your Project Need To Use Translation Package ? (astrotomic/laravel-translatable) [No]: <br>
> [0] No <br>
> [1] Yes <br>

if you have translations for a specific model then you need to hit yes <br>
_(for that when the model command asked you about cubeta-init command)_

**note:** if you ran this command and your choice was to have multi actors then on each created model an output will
ask
you about its controller actor and based on your choice the route for this controller will be placed in the compatible
route file</p>

<h1 id="created-classes-and-files">Created Classes And Files</h1>


<h2 id="models">Models</h2>
<p>

As mentioned before you'll find a model class corresponding to the name you entered in the `app/Models` directory

- the model will has the needed functions to represent your relations
- it will have an attribute for your file type property named after it, so you can access its storage path and will append
  it to your model
- if you have a property of type `bool` you'll see a scope for it to make it easier to query data based on this
  property
- if you have translated attributes you'll see the array that contains them with the name of `$translatedAttributes`

</p>

<h2 id="migrations">Migrations</h2>
<p>

the corresponding created migration will match the types of the columns you entered before

**notice 1:** columns of type files will be placed on the migration file as a string columns with a nullable
attribute <br>
**notice 2:** columns of type key will be placed on the migration file as a `foreignIdFor` columns with these
attributes : <br>

- constrained
- cascadeOnDelete <br>

<div class="alert alert-danger" style="color: crimson"> it is always better to check on the created files</div>

</p>

<h2 id="controllers">Controllers</h2>
<p>

the created controller contains the five basic methods `(index , show , store , update , delete)`

it is extends the ApiController class which use the RestfulTrait
<br>
<br>
**Restful Trait** : it is a trait with methods that will handle your json response using the following
methods `(apiResponse , apiValidation , formatPaginateData)`

_you can have a look at it ._

</p>

<h2 id='requests'>Requests</h2>
<p>

you'll find the created form request in the directory : `app/Http/Requests/YourModelName`

each model property will have this rules : `required|PropertyType` unless this :

<table>
<thead>

<tr>
<th>property name</th>
<th>rules</th>
</tr>
</thead>
<tbody>
<tr>
<td>name | first_name | last_name</td>
<td>required|string|min:3|max:255</td>
</tr>
<tr>
<td>email</td>
<td>required|string|max:255|email</td>
</tr>
<tr>
<td>password</td>
<td>required|string|max:255|min:6|confirmed</td>
</tr>
<tr>
<td>phone | phone_number | number</td>
<td>required|string|max:255|min:6</td>
</tr>
<tr>
<td>

any word ends with `_at` (started_at , ends_at , ... , any type that seems to be a date type)
</td>
<td>required|date</td>
</tr>


<tr>
<td>

any word starts with `_is` (is_original , is_available , .... , any type that seems to be boolean value)
</td>
<td>required|boolean</td>
</tr>

<tr>
<td>

any word ends with `_id` (user_id , product_id , .... , any type that seems to be foreign key)
</td>
<td>

required|integer|exists:_parent table_,id
</td>
</tr>
</tbody>
</table>

<br>

**notice** : it is important to check on the rules of the created form request after each created model to 
make sure that these rules are compatible with your application purposes and to check if there is any invalid rule usage

</p>

<h1 id="resources">Resources</h1>
<p>
if you have checked on the created controllers you should notice that their return value is a resource created after the model name
this resource will structure your json response to be in a united structure across your application 

**notice:** this resource will return the relations of this model also` (it is better to check on its code)`

the created resource will be placed in `app/Http/Resources` directory

</p>


<h2 id="factories">Factories</h2>
<p>
the created factory fill the database according to this : 

<table>
<thead>
<tr>
<th>Model Property Type</th>
<th>Faker Line</th>
</tr>
</thead>
<tbody>
<tr>
<td>integer|bigInteger|unsignedBigInteger</td>
<td>fake()->numberBetween(1,2000)</td>
</tr>
<tr>
<tr>
<td>unsignedDouble</td>
<td>fake()->randomFloat(1,2000)</td>
</tr>
<tr>
<td>double</td>
<td>fake()->randomFloat(1,2000)</td>
</tr>
<tr>
<td>float</td>
<td>fake()->randomFloat(1,2000)</td>
</tr>
<tr>
<td>string</td>
<td>fake()->sentence()</td>
</tr>
<tr>
<td>text</td>
<td>fake()->text()</td>
</tr>
<tr>
<td>json</td>
<td>{'".'fake()->word()'."':'".'fake()->word()'."'}</td>
</tr>
</tbody>
</table>

if the property is not in those types then this will be applied according to its name:


<table>
<thead>
<tr>
<td>Property Name</td>
<td>The Applied Faker</td>
</tr>
</thead>
<tbody>
<tr>
<tr>
<td>

any word ends with `_at` (started_at , ends_at , ... , any type that seems to be a date type)

</td>
<td>fake()->date()</td>
</tr>

<tr>
<td>



any word starts with `_is` (is_original , is_available , .... , any type that seems to be boolean value)


</td>
<td>
fake()->boolean()
</td>
</tr>

<tr>
<td>

any word ends with `_id` (user_id , product_id , .... , any type that seems to be foreign key)


</td>
<td>


\App\Models\ _**Related Model Class**_::factory()


</td>
</tr>


</tr>
</tbody>
</table>

if the model has one of this relation (has many , many to many) a function like below will be added to the factory : 

```

 public function $products($count = 1)
 {
    return $this->has(\App\Models\Product::factory($count));
 };

```

the created factory will be placed in `database\factories` directory

</p>

<h2 id="seeders">Seeders</h2>
<p>



a seeder will be created for your model in `database/seeders` directory

the seeder will call the corresponding factory with `10` as the factory count parameter


</p>


<h2 id="repositories">Repositories</h2>
<p>



if you're not familiar with the repository design pattern I'll give you a brief : 
<br>

The main idea behind the Repository pattern is to create an abstraction layer between the application and the data source. This abstraction layer is called the repository. The repository acts as a mediator between the application and the data store. It encapsulates the logic required to access the data and provides a simple and consistent interface for the application to interact with the data.

may you want to read more, so we recommend this article : [introduction to repository design pattern](https://cubettech.com/resources/blog/introduction-to-repository-design-pattern/)

and based on that we placed the code that handle the database operations and queries on the repository layer and this layer will be placed above the model layer and before the service layer (we will talk about it later) . 

so any database operation related to your model we prefer you do it in the corresponding repository class .

each repository class will be bind in the service provider by default (if it was created by the package)


if you opened the created repository class which will be in `app/Repositories` directory you'll notice that the repository class extends another class named BaseRepository this class contain the following methods : 

- `all(array $relations = [])` : <br>
  this method will return all the corresponding model records without any format 

  if there is no data the function return `null`

  the relations parameter is to return the related models data within the response, so you just have to pass the relation name for the desired relations data as an array like this `['products' , 'users']` <br> 

- `all_with_pagination(array $relationships = [], $per_page = 10)` : <br>
  as above this method return all the data but paginated 
  the return type is an array of the shape : 
  
  `['data' => $all, 'pagination_data' => $pagination_data]`

  if there is no data the function return `null`


- `create(array $data, array $relations = [])` : <br>
  as its name this function accept an array of data and create an instance from the corresponding model, and then it returns the created model with the choice of relations to be in the response

  it will return null if something happened and the data hasn't created

- `update(array $data, $id, array $relations = [])` :  <br>
  its purpose is obvious soo no need to brief

  it will return null if something happened and the data hasn't updated

- `find($id, array $relationships = [])` <br>

  it will return null if the data hasn't found

- `delete($id)` <br>
  it will return `true` if the data has been deleted else it will return `null` .
</p>

<h2 id="services">Services</h2>
<p>


if you're not familiar with the service design pattern I'll give you a brief : 
<br>

The Service Design Pattern consists of two main components: the service layer and the service interface. The service layer is responsible for implementing the business logic and data access of the application. It typically contains classes and methods that perform specific tasks, such as retrieving data from a database, performing calculations, or sending emails. The service layer should be designed to be reusable and easy to test.

The service interface is a set of methods that the presentation layer can call to access the service layer. It defines the contract between the presentation layer and the service layer, including the input parameters and return values of each method.


may you want to read more, so we recommend this article : [Service Design Patterns](https://davislevine.medium.com/service-design-patterns-930203c8df37#:~:text=A%20service%20design%20pattern%20describes,design%20patterns%20and%20form%20patterns)

and based on that we placed the code that handle the logic on the service layer and this layer will be placed above the repository layer . 


after your model creation is done you'll find this directory : `app/Services/YourModelName`  in it, you will find 2 php files : 
1. `YourModelService.php` this is the service class
2. `IYourModelService.php` this is the service interface

if you opened the service class you will notice that the class extends a class named BaseService . 

if you remember the BaseRepository class  methods you'll find that BaseService class implement those methods .


</p>

<h2 id="tests">Tests</h2>
<p>



each created model there will be a corresponding test class for its controller
you can find it in the `tests/Feature` directory 

this test class will test the CRUD endpoints

in the created test you'll see the following variables : `$model , $resource , $userType , $baseUrl` and you'll see that the two of them have a value 

but if we go to the others you need to know : 
1. `$userType` : if your application use multi actors by this package so in this variable just give it the actor role for those endpoints if there is not just leave it as 'none'

2. `$baseUrl` : if you've checked on the appended rout of your model you'll notice that this route is named so here you just put the name of it like if the route name is 'brands' just put the value of it as 'brands' .


another thing you have to do is to use MainTestCase trait in the TestCase Class in the `tests` directory (just put this line inside the class : `use \Cubeta\CubetaStarter\Traits\MainTestCase;`) . <br>

maybe you want to check on MainTestCase trait to know how the test methods work and see if they are good for you, or you have to create another ones .

- <h3>MainTestCase Methods</h3>

**notice :** those methods depends on the model resource and factory . <br>
 

  - `indexTest(array $additionalFactoryData = [], bool $ownership = false, bool $isDebug = false)` : <br> 
    this method will test the index endpoint by creating 5 records of fake data and try to get them

    `$additionalFactoryData` : <br> maybe your model factory doesn't contain info about some columns on your table but their exists in the desired response, or you want to test this endpoint on a specific columns values , so this array will give you the ability to pass the required columns with their desired value to the factory .

    `$ownership` determine if the action has to be on the authenticated user data so if it has to be the test will check if the ordered data belongs to the current user or not

    `$isDebug`  if it true it will dd() the endpoint response

- `showTest(array $additionalFactoryData = [], bool $ownership = false, bool $isDebug = false)` : <br> the parameters of this method are the same of the `indexTest` method
   in brief this function run the model factory and try to get one instance of the model by its `id` . 

- `storeTest(array $additionalAttributes = [], mixed $requestParams = null, bool $isDebug = false)` : <br> regardless `$additionalAttributes , $isDebug` which we talked about here there is a new parameter `$requestParams` : <br>  
  sometimes your endpoint needs a parameter to perform its action like this route `/category/{category_id}/products/create` as you see to create a product you have to pass a category id, so you can pass it in this parameter and if they were a punch of parameters just pass them as an associative array . 


- `updateTest(array $additionalFactoryData = [], array $attributes = [], bool $ownership = false, bool $replacing = true, bool $isDebug = false)` : <br> 
`$attributes` : if your requests needs data that your factory doesn't create it just send those data within this parameter, and it will be merged within the test request. <br>
`$replacing` : sometimes your update endpoint doesn't edit the selected database record it is just create another one with the desired edits so if this wat happens in your endpoint just make this parameter false . <br>


**notice :** it is important to mention that the storeTest and updateTest methods generate their requests body data from the model factory .<br>


- `deleteTest(array $additionalFactoryData = [], bool $ownership = false, bool $isDebug = false)` : <br> 
all the parameters of this method has explained before . <br>


**notice :** all of these methods expect the response as it formed in the RestfulTrait which we talked about before in the <a href="#controllers">controllers section</a> <br>

**notice :** you have to configure the `phpunit.xml` file before running any test check on [Laravel Testing documentation](https://laravel.com/docs/10.x/testing) <br>

<h4 style="color:red;">I truly recommend to check on the MainTestCase Trait </h4>

</p>

<h2 id="policies">Policies</h2>
<p>



a policy will be created just if there is an actor for this model, and you'll find it in  `app/Policies` directory. <br>

if you opened it you'll see that it extends the BasePolicy Class which contains the policy functions for the CRUDS actions and those functions depends on the roles given to each actor on the model but assuming your model name is : Brand then the policy will make sure that this user has this roles : `('index brand , show brand , store brand , update brand' , 'delete brand')`



</p>


