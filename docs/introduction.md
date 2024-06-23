# Introduction

cubeta-starter is a developer-centric package designed to streamline CRUD operations effortlessly. It boasts a
dependency-free setup, allowing you to choose between using it as a core part of your project or as a convenient dev
dependency. With a user-friendly GUI accessible via your project's route, cubeta-starter simplifies code generation,
making your development experience smooth and efficient. Say goodbye to repetitive tasks and hello to productivity with
cubeta-starter!

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

## installation

in your project root directory open the terminal and run `composer require cubeta/cubeta-starter`

## Complete Installation

After installing the package you have two choices :

1. use the GUI that the package provide
2. use the terminal to perform the required actions

## Using GUI

head to your browser and get into `http://yourdomain.com/cubeta-starter`

for example if you are working in your local using xampp `http://localhost/project-directory/public/cubeta-starter`

you will find a complete installation interface (available just in development environment)

in this page you'll be asked about your frontend stack , so you have three options :

1. Blade , Bootstrap , JQuery
2. Inertia , React , Typescript , Tailwind
3. No Frontend Just API

Choosing one depends on your project needs cause selecting one of the frontend stacks will give you the ability to fit
your CRUDS in a dashboard generated using the selected stack .

For example if you chose **(_Blade , Bootsrap , JQuery_)**  stack then you'll have the ability to generate blade pages
for each CRUD you have in the addition to a controller for those pages .

After selecting your frontend stack you'll have the following options based on the selected stack :

1. **Install Fore API Usage** : this will install all the needed classes , traits ,... for generating api based CRUDs .
2. **Install For Web Usage** : this will install all the needed classes , traits ,... for generating web based CRUDs .
3. **Install Both** : this will install both api and web files .
4. **install npm packages** : this option will install the npm packages that has been used in the blade pages (
   bootstrap , jQuery , select2 , .....) ,
   _this option is required when you are using web tools_.
5. **Install React TS Stack Tools** : This will install all the helper classes and traits for React , Typescript ,
   Tailwind , Inertia in addition to the ui components which will help you have a better development experience.
6. **Install react,typescript packages** : this option will install the npm packages that has been used in the react
   pages (inertia-laravel , inertiajs/react , react , ....) , _this option is required when you are using web tools_.

## Using Terminal

For the same installing steps your have three commands :

1. `php artisan cubeta:install api`
2. `php artisan cubeta:install web`
3. `php artisan cubeta:install web-packages`
4. `php artisan cubeta:install react-ts`
5. `php artisan cubeta:install react-ts-packages`

> [!note]
> as you'll notice you can generate for one frontend stack alongside the api stack so in the context of generation
> processes the process of generating for blade , or react.ts stacks will be referred as web generating
> for example when we say a web controller and the selected stack is react.ts then we mean a controller for react.ts
> stack

> [!note]
> when publishing there is two route files will be generated ( `protected` , `public` ) based on the usage (web or api)
> in `routes/v1/{selected-usage}/` directory in your project and be registered within your route service provider
> those files will hold your generated controllers routes

> [!warning]
> installing (api or web) is critical to make the generated endpoints or pages work properly

## Important Step !

after installing (web or api) a new provider will appear in the `app/Providers` directory, so you need to
register it in your project by going to `config/app.php` and in the file search for the `providers` array and add this
line to it : `\App\Providers\CubetaStarterServiceProvider::class
`


> [!warning]
> performing the installation processes using the package GUI will cause the to override any file that has the same name
> and directory of a file the package generates so pay attention to that or just use the terminal commands which have
> the `--force` tag to override any similar files and without it your files will stay
> _example_ : `php artisan cubeta:install api --force`


> [!info]
> it is a good practice to make sure that your project has a git repository that tracks all your changes since the
> package will generate a descent amount of your files

## Accepted Language Middleware

The package will publish a new middleware in case to handle your application localization so don't so don't forget to
register it withing your application middlewares

## Additional Step For Inertia , React.ts Installation

Inertia depends on a middleware named HandleInertiaRequests to be registered within your app middlewares so head
to `app/Http/Kernelphp` then in the web middlewares array add this
line : `\App\Http\Middleware\HandleInertiaRequests::class` like this :

```php
    protected $middlewareGroups = [
        'web' => [
            // your application middlewares ....
            \App\Http\Middleware\HandleInertiaRequests::class,
        ],
    ];
```



