<!-- docs/CHANGELOG.md -->

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
5. we've added `create:example` command for those who are trying to se what the package capable of , this command will give you three option : 
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

