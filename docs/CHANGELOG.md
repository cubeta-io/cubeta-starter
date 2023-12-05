### **V 1.1.4**

1. Making the columns of type translatable being cast into `Translatable::class` custom cast instead of generating an
   accessor for each translated column . you can find the custom cast `Translatable::class` after
   publishing `cubeta-starter-locale` tag in the `app/Casts` directory
2. Now every input component has a `name` attribute this attribute is nullable, when it is `null` this attribute will take
   the value of the `label` attribute transformed to **lower** _and_ **snake case**
