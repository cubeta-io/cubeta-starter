### **V 1.1.5**

1. Now every input component has a `name` attribute this attribute is nullable, when it is `null` this attribute will
   take the value of the `label` attribute transformed to **lower** _and_ **snake case**
2. new attribute `exact` has been added to the `gallery-item` blade component so if you'd like to provide the exact
   source of the image
3. add 2 new components `<x-translatable-text-editor />` and `<x-translatable-long-text-field />` to handle the
   localized long text field (the ones those need text editors)
4. there is now three function for sweet alert
   messages `triggerSwallSuccess(message)` , `triggerSwalError(message)` , `triggerSwallMessage(message)` so you can use
   them in your project after including `public/js/CustomFunctions.js` , (in the generated dashboard this file is
   already included).

## **fixing some bugs**

1. remove `required` attribute from the input fields in the generated _edit_ forms
2. adding `.gallery` css class to the `gallery-item` blade component so the functions in the `public/js/PluginInitializer.js` file
   can handle it.

### **V 1.1.4**

1. Making the columns of type translatable being cast into `Translatable::class` custom cast instead of generating an
   accessor for each translated column . you can find the custom cast `Translatable::class` after
   publishing `cubeta-starter-locale` tag in the `app/Casts` directory

