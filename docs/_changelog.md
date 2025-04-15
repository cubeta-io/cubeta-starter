<!-- docs/CHANGELOG.md -->

# **Changelog :**

## **V 3.0.0**
1. Adding support for the `Laravel 12` and drop support for previous versions.
2. Adding support for `inertia.js 2.0` and drop support for previous versions.
3. Adding support for `tailwindcss 4.0` and drop support for previous versions.
4. Adding support for `react 19` and drop support for previous versions.
5. Introduce `ApiResponse` class to replace the `RestTrait` to handle api responses
6. Introduce the new `MediaCast` mode cast class to replace the usage of the `FileHandler` trait
7. Bug fixes

## **V 2.2.0**
1. Adding support for the `Laravel 11` and drop support for previous versions. 
2. Bug fixes

## **V 2.1.0**

1. Introducing The `BaseBulkAction` class to make it easy to define your model available bulk
   actions .
2. Removing The BaseService and BaseRepository classes interfaces .

## **V 2.0.0**

1. Adding support generate dashboard CRUD'S using React , Typescript , Inertia.js , Tailwindcss
2. Adding the ability to generate the code structure based on a version number via the package config
   file [check here](usage.md#config)
3. Updating the structure of the repository and service patterns to be instantiated using the singleton pattern and
   remove their dependency injection process
4. Now just the blade based generation requires a service provider to register the blade components published via the
   package
5. All required middlewares , exceptions handlers and service providers for a specific stack is being registered via its install command

## **V 1.1.7**

### **fixing some bugs**

1. fixing the generated routes names for the generated controllers .
2. now when publishing for api or for web 2 route files will be generated ( `public` , `protected` ) to give more
   customizable routing instead of the generated route files based on the actor name .

## **V 1.1.6**

1. new attribute `exact` has been added to the `gallery-item` blade component so if you'd like to provide the exact
   source of the image
2. add 2 new components `<x-translatable-text-editor />` and `<x-translatable-long-text-field />` to handle the
   localized long text field (the ones those need text editors)
3. there is now three function for sweet alert
   messages `triggerSwallSuccess(message)` , `triggerSwalError(message)` , `triggerSwallMessage(message)` so you can use
   them in your project after including `public/js/CustomFunctions.js` , (in the generated dashboard this file is
   already included).
4. now there is a `cubeta-starter.config.json` in the root directory of your project this will guid the package for some
   tables data, and it is a first step on the next feature which is (presets handling) .
5. we've added `create:example` command for those who are trying to se what the package capable of , this command will
   give you three option :
    - the first one is to create the Category model which has many products
    - the second one is to create the Product model which related to brands by many to many relation
    - finally the brands model
6. we've tried to not generate a code that use a relation to a table not defined yet and then when it is defined we add
   the proper code (you can check that by running the command : `php artisan create:example` and generate the product
   before the category) .

### **fixing some bugs**

1. remove `required` attribute from the input fields in the generated _edit_ forms
2. adding `.gallery` css class to the `gallery-item` blade component so the functions in
   the `public/js/PluginInitializer.js` file
   can handle it.

## **V 1.1.5**

1. Now every input component has a `name` attribute this attribute is nullable, when it is `null` this attribute will
   take the value of the `label` attribute transformed to **lower** _and_ **snake case**

## **V 1.1.4**

1. Making the columns of type translatable being cast into `Translatable::class` custom cast instead of generating an
   accessor for each translated column . you can find the custom cast `Translatable::class` after
   publishing `cubeta-starter-locale` tag in the `app/Casts` directory

