<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeController extends Command
{
    use AssistCommand;

    public $signature = 'create:controller
        {name : The name of the model }?';

    public $description = 'Create a new controller';

    /**
     * Handle the command
     *
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');

        $this->createController($modelName);
    }

    /**
     * @throws BindingResolutionException
     */
    private function createController($modelName)
    {
        $modelName = ucfirst($modelName);
        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNameLower}' => Str::lower($modelName),
        ];

        $controllerName = $this->getControllerName($modelName);

        //{class} model name , {namespace} , {traits}
        new CreateFile(
            $stubProperties,
            $this->getControllerPath($controllerName),
            __DIR__ . '/stubs/controller.api.stub'
        );
        $this->line("<info>Created controller:</info> $controllerName");
        $this->addRoute($modelName) ;
    }

    private function getControllerName($modelName): string
    {
        return $modelName . 'Controller';
    }

    /**
     * @throws BindingResolutionException
     */
    private function getControllerPath($controllerName): string
    {
        $path = $this->appPath() . '/app/Http/Controllers/API/v1';

        $this->ensureDirectoryExists($path);

        return $path . "/$controllerName" . '.php';
    }

    public function addRoute($modelName)
    {
        $apiConstString ='/*
        |--------------------------------------------------------------------------
        | API Routes
        |--------------------------------------------------------------------------
        |
        | Here is where you can register API routes for your application. These
        | routes are loaded by the RouteServiceProvider and all of them will
        | be assigned to the "api" middleware group. Make something great!
        |
        */' ;

        $files = new Filesystem ;
        $apiPath = base_path().'/routes/api.php' ;

        if ($files->exists($apiPath)){
            $pluralLowerModelName = Str::singular(Str::lower($modelName)) ;
            $lowerModelName = Str::lower($modelName) ;
            $route = 'Route::apiResource("/'.$pluralLowerModelName.'" , \App\Http\Controllers\API\v1\\'.$modelName.'Controller::class)->names("api.'.$lowerModelName.'") ;' ;

            if(file_put_contents($apiPath , $route , FILE_APPEND)){
                $this->line("<info>Route Added Successfully</info>");
            } else {
                $this->line("<info>Route didn\'t Add Successfully</info>");
            }
        } else {
            $this->line("<info>api.php file doesn\'t exist:</info>");
        }
    }
}
