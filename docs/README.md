<h1 id="created-classes-and-files">Created Classes And Files</h1>


<h2 id="models">Models</h2>

As mentioned before you'll find a model class corresponding to the name you entered

- the model will has the needed functions to represent your relations
- it will have a method to get your file path named after it, so you can access its storage path
- if you have a property of type `bool` you'll see a scope for it to make it easier to query data based on this
  property

- you'll notice the existence of `searchableArray()` method in the returned array of this method you can define the
  searchable columns in the table of this model so in the index method if you passed a query param named `search` with
  the value of the wanted value the index method will search within the defined columns in the `searchableArray()`
  method .
- you'll notice the existence of `relationsSearchableArray()` method in the returned array of this method you can define
  the related tables and their desired columns to search within in the same way for the `searchableArray()` method .
- the `filesKeys()` method will determine the columns you want to treat them as a files so
  the <a href="#baserepository">BaseRepository Class</a> can recognize them. in this I mean when you use the create
  method for example the BaseRepository Class will detect that this column is representing a file, so it will store the
  file in the storage path and its storage path will be in the record of the table .

**you'll find that we have already filled those arrays with appropriate values, but you can change them according to
your preferences**

<h2 id="migrations">Migrations</h2>

the corresponding created migration will match the types of the columns you entered before

> [!note]
> columns of type files will be placed on the migration file as a string columns with a nullable
> attribute

> [!note]
> columns of type key will be placed on the migration file as a `foreignIdFor` columns with these
> attributes :
> 1. constrained
> 2. cascadeOnDelete

> [!note]
> columns of type translatable will be placed on the migration file as a `json` columns

> [!tip]
> it is always better to check on the created files

<h2 id="controllers">Controllers</h2>

the created controller contains the five basic methods `(index , show , store , update , delete)`

it is extends the ApiController class which use the RestTrait
<br>
<br>
**RestTrait** : it is a trait with methods that will handle your json response using the following
methods `(apiResponse , apiValidation , formatPaginateData)`

<a href="#restful-trait">_you can have a look at it ._</a>

<h2 id='requests'>Requests</h2>

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

any word starts with `is_` (is_original , is_available , .... , any type that seems to be boolean value)
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

<tr>
<td>columns with file type</td>
<td>nullable|image|mimes:jpeg,png,jpg|max:2048</td>
</tr>

<tr>
<td>columns with text type</td>
<td>nullable|string</td>
</tr>

<tr>
<td>columns with translatable type</td>
<td>['required', 'json', new LanguageShape]</td>
</tr>

</tbody>
</table>

<br>

> [!attention]
> it is important to check on the rules of the created form request after each created model to
> make sure that these rules are compatible with your application purposes and to check if there is any invalid rule
> usage


<h1 id="resources">Resources</h1>
if you have checked on the created controllers you should notice that their return value is a resource named after the model name
this resource will structure your json response to be in a united structure across your application .

> [!warning]
> this resource will return the relations of this model also

<h2 id="factories">Factories</h2>
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
<td>key type columns</td>
<td>a factory for the related model</td>
</tr>

<tr>
<td>translatable type column</td>
<td>

json_encode(["en":fake()->word()]) in fact the array inside the `json_encode` method will be a fake word for each
available locale you defined in the `cubeta-starter.php` config file

</td>
</tr>

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



</tbody>
</table>

- sometimes the factory could be predicted according to its name like if you have a column named _image , logo , icon_
  the used faker will be `imageUrl()` or if the name of the column contain the phone word the used faker will
  be `phoneNumber()`.

if the model has one of this relation (has many , many to many) a function like below will be added to the factory :

```

 public function withProducts($count = 1)
 {
    return $this->has(\App\Models\Product::factory($count));
 };

```

<hr>

<h2 id="seeders">Seeders</h2>

the seeder will call the corresponding factory of the model with `10` as the factory count parameter

<h2 id="repositories">Repositories</h2>

if you're not familiar with the repository design pattern I'll give you a brief :
<br>

The main idea behind the Repository pattern is to create an abstraction layer between the application and the data
source. This abstraction layer is called the repository. The repository acts as a mediator between the application and
the data store. It encapsulates the logic required to access the data and provides a simple and consistent interface for
the application to interact with the data.

may you want to read more, so we recommend this
article : [introduction to repository design pattern](https://cubettech.com/resources/blog/introduction-to-repository-design-pattern/)

and based on that we placed the code that handle the database operations and queries on the repository layer and this
layer will be placed above the model layer and before the service layer (we will talk about it later) .

so any database operation related to your model we prefer you do it in the corresponding repository class .

each repository class will be bind in the service provider by default (if it was created by the package)

if you opened the created repository class you'll notice that it extends another class named <h4 id="baserepository">
BaseRepository</h4> and it is in the `app/Repositories/Contracts` (You have to publish `cubeta-starter-repositories` tag
to find it) Directory this class contain the following methods :

- `all(array $relations = [])` : <br>
  this method will return all the corresponding model records without any format

  if there is no data the function return `null`

  the relations parameter is to return the related models' data within the response, so you just have to pass the
  relation name for the desired relations data as an array like this `['products' , 'users']` <br>

- `all_with_pagination(array $relationships = [], $per_page = 10)` : <br>
  as above this method return all the data but paginated
  the return type is an array of the shape :

  `['data' => $all, 'pagination_data' => $pagination_data]`

  if there is no data the function return `null`


- `create(array $data, array $relations = [])` : <br>
  as its name this function accept an array of data and create an instance from the corresponding model, and then it
  returns the created model with the choice of relations to be in the response

  it will return null if something happened and the data hasn't created

- `update(array $data, $id, array $relations = [])` :  <br>
  its purpose is obvious soo no need to brief

  it will return null if something happened and the data hasn't updated

- `find($id, array $relationships = [])` <br>

  it will return null if the data hasn't found

- `delete($id)` <br>
  it will return `true` if the data has been deleted else it will return `null` .

<h2 id="services">Services</h2>

if you're not familiar with the service design pattern I'll give you a brief :
<br>

The Service Design Pattern consists of two main components: the service layer and the service interface. The service
layer is responsible for implementing the business logic and data access of the application. It typically contains
classes and methods that perform specific tasks, such as retrieving data from a database, performing calculations, or
sending emails. The service layer should be designed to be reusable and easy to test.

The service interface is a set of methods that the presentation layer can call to access the service layer. It defines
the contract between the presentation layer and the service layer, including the input parameters and return values of
each method.

may you want to read more, so we recommend this
article : [Service Design Patterns](https://davislevine.medium.com/service-design-patterns-930203c8df37#:~:text=A%20service%20design%20pattern%20describes,design%20patterns%20and%20form%20patterns)

and based on that we placed the code that handle the logic on the service layer and this layer will be placed above the
repository layer .

after your model creation is done you'll find 2 php files in the services' in the directory you defined in the package
config
file :

1. `YourModelService.php` this is the service class
2. `IYourModelService.php` this is the service interface

if you opened the service class you will notice that the class extends a class named BaseService this class is in
the `app/Services/Contracts` (You have to publish `cubeta-starter-services` tag to find it) .

if you remember the BaseRepository class methods you'll find that BaseService class use those methods to do its job.

<h2 id="tests">Tests</h2>

each created model there will be a corresponding test class which extends `MainTestClass`
you can find it in the `tests/Feature` directory

this test class will test the CRUD endpoints

in the created test you'll see the following variables : `$model , $resource , $userType , $baseUrl` and you'll see that
the two of them have a value

but if we go to the others you need to know :

1. `$userType` : if your application use multi actors by this package so in this variable just give it the actor role
   for those endpoints if there is not just leave it as 'none'

2. `$baseUrl` : if you've checked on the appended rout of your model you'll notice that this route is named so here you
   just put the name of it like if the route name is 'brands' just put the value of it as 'brands' .

 <br>

maybe you want to check on `MainTestCase` class trait to know how the test methods work and see if they are good for
you, or you
have to create another ones .

<h2 id="postman-collection">Postman Collection</h2>
the generated postman collection will have HTTP requests grouped by your model name .
it has two variables for the whole collection :

1. **{{local}}** :  this will be generated as we mentioned before from the value you defined in the config or by
   assuming you're using `xampp` so the url will be like the projects route which use xampp as a host.
2. **{{locale}}** : this will represent the accept-language header in all the generated requests

when generating another postman collection assuming that you've already generated one it will not replace the older one
it will just add a new group of requests to the previous one .

<h3 id="main-testcase-class">MainTestCase Class Methods</h3>

> [!attention]
> those methods depends on the model resource and factory and the response of the endpoint to be generated by
> the `RestTrait` and the exceptions thrown by our exception handler.  <br>
> so each model must have a resource and a factory (not necessarily generated by the package) but the endpoint for it
> must be using RestTrait for handling its responses .

- `indexTest(array $additionalFactoryData = [], bool $ownership = false, bool $isDebug = false)` : <br>
  this method will test the index endpoint by creating 5 records of fake data and try to get them

  `$additionalFactoryData` : <br> maybe your model factory doesn't contain info about some columns on your table but
  their exists in the desired response, or you want to test this endpoint on a specific columns values , so this array
  will give you the ability to pass the required columns with their desired value to the factory .

  `$ownership` determine if the action has to be on the authenticated user data so if it has to be, the test will check
  if the ordered data belongs to the current user or not

  `$isDebug`  if it true it will dd() the endpoint response

- `showTest(array $additionalFactoryData = [], bool $ownership = false, bool $isDebug = false)` : <br> the parameters of
  this method are the same of the `indexTest` method
  in brief this function run the model factory and try to get one instance of the model by its `id` .

- `storeTest(array $additionalAttributes = [], mixed $requestParams = null, bool $isDebug = false)` : <br>
  regardless `$additionalAttributes , $isDebug` which we talked about here there is a new
  parameter `$requestParams` : <br>  
  sometimes your endpoint needs a parameter to perform its action like this
  route `/category/{category_id}/products/create` as you see to create a product you have to pass a category id, so you
  can pass it in this parameter and if they were a punch of parameters just pass them as an associative array .


- `updateTest(array $additionalFactoryData = [], array $attributes = [], bool $ownership = false, bool $replacing = true, bool $isDebug = false)` : <br>
  `$attributes` : if your requests needs data that your factory doesn't create it just send those data within this
  parameter, and it will be merged within the test request. <br>
  `$replacing` : sometimes your update endpoint doesn't edit the selected database record it is just create another one
  with the desired edits so if this what happens in your endpoint just make this parameter `false` . <br>

> [!note]
> it is important to mention that the storeTest and updateTest methods generate their requests body data from
> the model factory .

- `deleteTest(array $additionalFactoryData = [], bool $ownership = false, bool $isDebug = false)` : <br>
  all the parameters of this method has explained before . <br>

> [!warning]
> don't forget to configure the `phpunit.xml` file before running any test check
> on [Laravel Testing documentation](https://laravel.com/docs/10.x/testing)

> [!tip]
> I truly recommend to check on the MainTestCase Class in

<hr>
<h1 id="translations">Translations Handling</h1>

As we mentioned before the corresponding type for the translatable columns is json .
every model that has a translated column will use this trait : <code>app/Traits/Translation</code>

`updateTranslation(string $translatableColumn, mixed $value, string $locale = null)` Method:

This method is used to update the corresponding translation for a given locale in a translatable column.
Parameters:
`$translatableColumn`: The name of the translatable column in your model's database table.
`$value`: The value to set for the translation.
`$locale` (optional): The locale for which to update the translation. If not provided, it uses the current application
locale.
If the provided locale is not in the list of available locales (configured in the available_locales key in the package
config file), it throws an exception.
It updates the translation in the JSON-encoded column, then saves the model and returns itself (the model).
<br>
<br>
`getTranslation Method`:
This method retrieves the corresponding translation value for a given locale from a JSON-encoded column.
Parameters:
$translationColumn: The name of the JSON-encoded column containing translations.
$locale (optional): The locale for which to retrieve the translation. If not provided, it uses the current application
locale.
It first attempts to retrieve the translation for the provided locale. If not found, it falls back to the default locale
configured in the package's config file.
If a translation value is still not found, it returns a message indicating that there's no corresponding value for the
provided locale or the default locale.

`AcceptedLanguagesMiddleware` middleware :
to use this just put this line (`\App\Http\Middleware\AcceptedLanguagesMiddleware::class`) in your `$middleware` array
in the `app/Http/Kernel.php` class in this way every received request will
be checked for having an _accept-language_ header or the session for having key named `locale` and change the project
locale depending on it.
in addition to that you will notice that the generated postman collection will have a `"locale"` variable which will be
the default value of all the generated requests' locale you can set it in postman in the variables' section of the
collection .
and for web usage when you published the `cubeta-starter-assets` tag a `SetLocaleController` has been published to
the `app/Http/Controllers` directory.

> [!warning]
> if you published this tag from the command line make sure to add a rout for this
> controller

<h1 id="restful-trait">RestTrait</h1>
To use the RestTrait in your PHP application, follow these steps:

Include the RestTrait in your controller or service class by adding the use statement at the top of the child class:
php `use App\Traits\RestTrait;`but you need to publish `cubeta-starter-traits` tag before .
Implement the methods provided by the RestTrait within your controller or service class.<br>
These methods can be used to
handle API responses and validations. Here's a breakdown of each method:
`apiResponse($data = null, int $code = 200, $message = null, $paginate = null)`: This method creates a standardized API
response. It accepts parameters such as `data, code, message, and paginate` to customize the response. Use this method
to
format and return API responses within your controller or service methods.

`apiValidation($request, $array)`: Use this method to handle request data validation. It takes the incoming request
object
and an array of validation rules as parameters. If the validation fails, it returns an API response with appropriate
error messages. If the validation passes, it returns the validated data.

`formatPaginateData($data)`: This method is used to format pagination data returned by Laravel Paginator class. It
accepts the paginated data and returns an associative array containing fields like `currentPage, from, to, total`, and
`per_page`. Use this method when you need to format pagination data for API responses.

Customize the response messages, HTTP status codes, and validation rules to suit your application's needs. Update the
method parameters and content based on your specific requirements.

By using the RestTrait and its provided methods, you can simplify the handling of API responses, improve code
organization, and ensure consistent response structures and data validations throughout your application.

<hr>

<h1 id="file-handler-trait">FileHandler Trait</h1>
remember when I told you that the BaseRepository handle your files columns automatically without the need
from you to do that well it uses this trait to do that and so as you can.

but before make sure you have published `cubeta-starter-traits` tag, so you can use it.

this trait provides the following methods which you can use :

`storeFile($file, $dir, $to_compress = true, $is_base_64 = false, $width = 300)`: This method takes a file, directory
path, and optional parameters, and stores the file in the specified directory. If the file is a base64-encoded image, it
generates a unique name for the file based on the current timestamp. If compression is enabled, it resizes the image to
the specified width while maintaining the aspect ratio. Finally, it returns the name of the stored file.

`updateFile($new_file, $old_file, $dir, $to_compress = true, $is_base_64 = false, $width = 300)`: This method is similar
to storeFile, but it also takes an old file name as a parameter. It deletes the old file and then calls storeFile to
store the new file. It returns the name of the new file.

`deleteFile($file)`: This method takes a file name and deletes it from the filesystem. It returns true if the file was
deleted successfully and false if the file was not found.

`storeNormalFile($key)`: This method is used to store any type of file, not just images. It takes a request key and
stores
the file in the public storage directory. It generates a unique file name based on the current timestamp and returns the
generated name.

`storeOrUpdateRequestedFiles(array $data, array $filesKeys = [], bool $is_store = true, $item = null, $to_compress =
true, $is_base_64 = false, $width = 300)`: This method is a utility method that is used to store or update multiple
files
based on the provided data and file keys. It takes an array of data, an array of file keys, and optional parameters. It
iterates over the file keys and checks if the key exists in the data array. If it does, it calls either storeFile or
updateFile based on the $is_store parameter. It removes the file key from the data array and merges the generated file
name into the data array. Finally, it returns the updated data array.

`storeImageFromUrl($url, $dir)`: This method takes a URL and a directory path, creates the directory if it doesn't
exist,
generates a random name for the image, and saves the image from the URL in the specified directory. It returns an array
containing the name of the stored image and the image object.

Overall, this trait provides convenient methods for storing, updating, and deleting files, with specific support for
image handling, in a Laravel application.

> [!warning]
> after the first generation a `cubeta-starter.config.js` file will be created in the base directory of your project
> for now this file is useless for you but helpful for us to make you generating experience better but in the coming
> releases it will give you a lot of features .
