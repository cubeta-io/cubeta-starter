<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeController extends Command
{
    use AssistCommand;

    protected $signature = 'create:controller
        {name : The name of the model }?';

    protected $description = 'Create a new controller';

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
        $this->addRoute($modelName);
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

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws BindingResolutionException
     */
    public function addRoute($modelName)
    {
        $demandedRouteDirectory = $this->ask("What is The directory of your controller route file ? \n
         !!consider you are know in route directory!! \n
          if you want to chose api.php just type api.php
          <info>type like this : customer/protected.php</info>");

        $files = new Filesystem;
        $apiPath = base_path() . '\routes\\' . $demandedRouteDirectory;

        $pluralLowerModelName = Str::singular(Str::lower($modelName));
        $lowerModelName = Str::lower($modelName);

        $route = 'Route::apiResource("/' . $pluralLowerModelName . '" , v1\\' . $modelName . 'Controller::class)->names("api.' . $lowerModelName . '") ;' . "\n";
        $importStatement = "\n" . "use App\Http\Controllers\API\\v1 ;" . "\n";

        if ($demandedRouteDirectory == 'api.php') {
            $this->addImportStatement($importStatement);
            if ($files->exists($apiPath)) {
                if (file_put_contents($apiPath, $route, FILE_APPEND)) {
                    $this->line("<info>Route Added Successfully</info>");
                } else {
                    $this->line("<info>Route didn\'t Add Successfully</info>");
                }
            } else {
                $this->line("<info>api.php file doesn\'t exist:</info>");
            }
        } else {
            if ($files->exists($apiPath)) {
//                if (file_put_contents($apiPath, $route, FILE_APPEND)) {
//                    $this->addImportStatement($importStatement);
//                    $this->line("<info>Route Added Successfully</info>");
//                } else {
//                    $this->line("<info>Route didn\'t Add Successfully</info>");
//                }
            } else {
                File::makeDirectory($apiPath, 0755, true, true);
                new CreateFile(
                    ['{route}' => $route],
                    $apiPath,
                    __DIR__ . '/stubs/api.stub'
                );

            }
        }
    }

    /**
     * @param $importStatement
     * @param string $filePath
     * @return void
     */
    function addImportStatement($importStatement, string $filePath = 'routes/api.php'): void
    {
        $filename = base_path($filePath);
        $contents = file_get_contents($filename);

        // Check if import statement already exists
        if (str_contains($contents, $importStatement)) {
            return; // Import statement already exists, do nothing
        }

        // Find the last "use" statement and insert the new import statement after it
        $lastUseIndex = strrpos($contents, 'use ');
        $insertIndex = $lastUseIndex !== false ? $lastUseIndex - 1 : 0;
        $contents = substr_replace($contents, $importStatement . "\n", $insertIndex, 0);

        // Write the updated contents back to the file
        file_put_contents($filename, $contents);
    }
}
