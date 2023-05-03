**Introduction**
 cubeta-starter is a package that will help you create your CRUDES easier than before with a pretty much        everything you will need

 **it is using Repository and Service design pattern so every model created will there is a corresponding repository class and service class**

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



 
