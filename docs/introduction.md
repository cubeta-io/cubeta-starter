<h1 id="introduction">Introduction</h1>
cubeta-starter is a developer-centric package designed to streamline CRUD operations effortlessly. It boasts a dependency-free setup, allowing you to choose between using it as a core part of your project or as a convenient dev dependency. With a user-friendly GUI accessible via your project's route, cubeta-starter simplifies code generation, making your development experience smooth and efficient. Say goodbye to repetitive tasks and hello to productivity with cubeta-starter!

it is using Repository and Service design pattern so every model created there will be a corresponding repository
class and service class

Each created model will have :

- migration file
- controller
- form request
- resource
- factory
- seeder
- repository
- service
- test

and a postman collection file will be generated for the whole model created (models created by the package)

**<h1 id="installation">installation</h1>**

in your project root directory open the terminal and run `composer require cubeta/cubeta-starter`

**<h2 id="complete-installation">Complete Installation</h1>**
After installing the package you have two choices :

1. use the GUI that the package provide
2. use the terminal to perform the required actions

**<h3 id="use-gui">Using GUI : </h3>**
head to your browser and get into `http://yourdomain.com/cubeta-starter`

for example if you are working in your local `http://localhost/public/cubeta-starter`

you will find a complete installation interface (available just in development environment)

in this page you'll find four options :

1. **Install Fore API Usage** : this will install all the needed classes , traits ,... for generating api based CRUDs .
2. **Install For Web Usage** : this will install all the needed classes , traits ,... for generating web based CRUDs .
3. **Install Both** : this will install both api and web files .
4. **install npm packages** : this option will install the npm packages that has been used in the blade pages (
   bootstrap , jQuery , select2 , .....) ,
   _this option is required when you are using web tools_.

**<h3 id="use-terminal">Using Terminal : </h3>**
For the same installing steps your have three commands :

1. `php artisan cubeta:install api`
2. `php artisan cubeta:install web`
3. `php artisan cubeta:install web-packages`

> [!note]
> when publishing there is two route files will be generated ( `protected` , `public` ) based on the usage (web or api)
> son in `routes/v1/{selected-usage}/` directory in your project and be registered within your route service provider
> those files will hold your generated controllers routes

> [!warning]
> installing (api or web) is critical to make the generated endpoints or pages work properly

<h1>Important Step !</h1>

after installing (web or api) a new provider will appear in the `app/Providers` directory, so you need to
register it in your project by going to `config/app.php` and in the file search for the `providers` array and add this
line to it : `\App\Providers\CubetaStarterServiceProvider::class
`
