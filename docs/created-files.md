# Created Classes And Files

## Models

As mentioned before you'll find a model class corresponding to the name you entered

- the model will has the needed methods to represent your relations
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
  the [BaseRepository Class](base-repository.md#baserepository-class) can recognize them. in this I mean when you use
  the create
  method for example the BaseRepository Class will detect that this column is representing a file, so it will store the
  file in the storage path and its storage path will be in the record of the table .

**you'll find that we have already filled those arrays with appropriate values, but you can change them according to
your preferences**

an extended explanation [here](created-model.md#created-models)

## Migrations

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

## Controllers

the created controller contains the five basic methods `(index , show , store , update , delete)`

it extends the ApiController class
<br>
<br>

## Requests

each model property will have this rules : `required|PropertyType` unless this :

| property name	                                                                                         | rules                                      |
|:-------------------------------------------------------------------------------------------------------|--------------------------------------------|
| name,first_name ,last_name	                                                                            | required,string ,min:3 ,max:255            |
| email                                                                                                  | required,string,max:255 ,email             |
| password                                                                                               | required,string,max:255 ,min:6 ,confirmed  |
| phone , phone_number , number	                                                                         | required,string,max:255 ,min:6             |
| any word ends with `_at` (started_at , ends_at , … , any type that seems to be a date type)            | required,date                              |
| any word starts with `is_` (is_original , is_available , …. , any type that seems to be boolean value) | required,boolean                           |
| any word ends with `_id` (user_id , product_id , …. , any type that seems to be foreign key)           | required,integer,exists:parent table,id    |
| columns with file type	                                                                                | nullable,image,mimes:jpeg,png,jpg,max:2048 |
| columns with text type                                                                                 | nullable ,string                           |
| columns with translatable type                                                                         | required , json , new LanguageShape        |

> [!attention]
> it is important to check on the rules of the created form request after each created model to
> make sure that these rules are compatible with your application purposes and to check if there is any invalid rule
> usage

## Resources

if you have checked on the created controllers you should notice that their return value is a resource named after the
model name
this resource will structure your json response to be in a united structure across your application .

> [!warning]
> this resource will return the relations of this model also

## Factories

the created factory fill the database according to this :

| Model Property Type                     | Faker Line                                                                                                                                                                             |
|-----------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| integer\|bigInteger\|unsignedBigInteger | fake()->numberBetween(1,2000)                                                                                                                                                          |
| key type columns                        | a factory for the related model                                                                                                                                                        |
| translatable type column                | json_encode(["en":fake()->word()]) in fact the array inside the `json_encode` method will be a fake word for each available locale you defined in the `cubeta-starter.php` config file |
| unsignedDouble                          | fake()->randomFloat(1,2000)                                                                                                                                                            |
| double                                  | fake()->randomFloat(1,2000)                                                                                                                                                            |
| float                                   | fake()->randomFloat(1,2000)                                                                                                                                                            |
| string                                  | fake()->sentence()                                                                                                                                                                     |
| text                                    | fake()->text()                                                                                                                                                                         |
| json                                    | {'".'fake()->word()'."':'".'fake()->word()'."'}                                                                                                                                        |

if the property is not in those types then this will be applied according to its name:

| Property Name                                                                                            | The Applied Faker                                 |
|----------------------------------------------------------------------------------------------------------|---------------------------------------------------|
| any word ends with `_at` (started_at , ends_at , ... , any type that seems to be a date type)            | fake()->date()                                    |
| any word starts with `_is` (is_original , is_available , .... , any type that seems to be boolean value) | fake()->boolean()                                 |
| any word ends with `_id` (user_id , product_id , .... , any type that seems to be foreign key)           | \App\Models\ _**Related Model Class**_::factory() |

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

## Seeders

the seeder will call the corresponding factory of the model with `10` as the factory count parameter

## Repositories

if you're not familiar with the repository design pattern I'll give you a brief :

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

if you opened the created repository class you'll notice that it extends another class named
BaseRepository, and it is in the `app/Repositories/Contracts` Directory check on
it [here](base-repository.md#baserepository-class).

## Services

if you're not familiar with the service design pattern I'll give you a brief :

The Service Design Pattern consists of two main components: the service layer and the service interface. The service
layer is responsible for implementing the business logic and data access of the application. It typically contains
classes and methods that perform specific tasks, such as retrieving data from a database, performing calculations, or
sending emails. The service layer should be designed to be reusable and easy to test.

may you want to read more, so we recommend this
article : [Service Design Patterns](https://davislevine.medium.com/service-design-patterns-930203c8df37#:~:text=A%20service%20design%20pattern%20describes,design%20patterns%20and%20form%20patterns)

and based on that we placed the code that handle the logic on the service layer and this layer will be placed above the
repository layer .

after your model creation is done you'll find 2 php files in the services' in the directory you defined in the package
config
file :

1. `YourModelService.php` this is the service class
2. `IYourModelService.php` this is the service interface

if you opened the service class you will notice that the class extends a class named BaseService.

## Tests

foreach created model there will be a corresponding test class which extends `MainTestClass` you can find it in
the `tests/Feature` directory , this test class will test the CRUD endpoints , in the created test
you'll see the following variables : `$model , $resource , $userType , $baseUrl` and you'll see that
there is two of them have a value , but if we go to the others you need to know the following:
1. `$userType` : if your application use multi actors by this package so in this variable just give it the actor role
   for those endpoints if there is not just leave it as `'none'`.
2. `$baseUrl` : if you've checked on the appended rout of your model you'll notice that this route is named so here you
   just put the name of it like if the route name is 'brands' just put the value of it as 'brands'.

maybe you want to check on [`MainTestCase`](main-test.md#maintestcase-class) class trait to know how the test methods
work and see if they are good for you, or you have to create another ones .

## Postman Collection
the generated postman collection will have HTTP requests grouped by your model name .
it has two variables for the whole collection :

1. **{{local}}** :  this will be generated as we mentioned before from the value you defined in the config.
2. **{{locale}}** : this will represent the accept-language header in all the generated requests

when generating another postman collection assuming that you've already generated one it will not replace the older one
it will just add a new group of requests to the previous one .

