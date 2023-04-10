<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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
     * @throws FileNotFoundException
     */
    public function addRoute($modelName)
    {
        $demandedRouteDirectory = $this->ask("What is The directory of your controller route file ? \n
         !!consider you are know in route directory!! \n
          if you want to chose api.php just type api.php
          <info>type like this : customer/protected.php</info>");

        $apiPath = base_path() . '\routes\\' . $demandedRouteDirectory;

        $pluralLowerModelName = Str::singular(Str::lower($modelName));
        $routeName = $this->getRouteName($demandedRouteDirectory, $modelName);

        $route = 'Route::apiResource("/' . $pluralLowerModelName . '" , v1\\' . $modelName . 'Controller::class)->names("' . $routeName . '") ;' . "\n";
        $importStatement = "\n" . "use App\Http\Controllers\API\\v1 ;" . "\n";

        if (file_exists($apiPath)) {
            $this->addImportStatement($importStatement, $apiPath);
            if (file_put_contents($apiPath, $route, FILE_APPEND)) {
                $this->line("<info>Controller Route Appended Successfully</info>");
            } else {
                $this->line("<info>Failed to Append a Route For This Controller</info>");
            }
        } else {
            !(File::makeDirectory(dirname($apiPath), 0777, true, true)) ??
            $this->line("<info>Failed To Create Your Route Specified Directory</info>");

            new CreateFile(
                ['{route}' => $route],
                $apiPath,
                __DIR__ . '/stubs/api.stub'
            );

            $this->addImportStatement($importStatement, $apiPath);
            $this->addApiFileToServiceProvider($demandedRouteDirectory);
            $this->line("<info>Controller Route Appended Successfully</info>");
        }
    }

    /**
     * @param $importStatement
     * @param string $filePath
     * @return void
     */
    function addImportStatement($importStatement, string $filePath = 'routes/api.php'): void
    {
        $contents = file_get_contents($filePath);

        // Check if import statement already exists
        if (str_contains($contents, $importStatement)) {
            return; // Import statement already exists, do nothing
        }

        // Find the last "use" statement and insert the new import statement after it
        $lastUseIndex = strrpos($contents, 'use ');
        $insertIndex = $lastUseIndex !== false ? $lastUseIndex - 1 : 0;
        $contents = substr_replace($contents, $importStatement . "\n", $insertIndex, 0);

        // Write the updated contents back to the file
        file_put_contents($filePath, $contents);
    }

    /**
     * @param $filePath
     * @param $modelName
     * @return array|string|string[]
     */
    public function getRouteName($filePath, $modelName): array|string
    {
        $lowerModelName = Str::lower($modelName);

        $routeName = str_replace(
                ['/', '//', '\\', '\\\\'],
                '.',
                str_replace('.php', '', $filePath)
            ) . '.' . $lowerModelName;

        return $routeName;
    }


    /**
     * @param string $apiFilePath
     * @return void
     */
    public function addApiFileToServiceProvider(string $apiFilePath): void
    {
        $routeServiceProvider = app_path('Providers/RouteServiceProvider.php');
        $line_to_add = "\t\t Route::middleware('api')\n" .
            "\t\t\t->prefix('api')\n" .
            "\t\t\t->group(base_path('routes/$apiFilePath'));\n" ;

        // Read the contents of the file
        $file_contents = file_get_contents($routeServiceProvider);

        // Check if the line to add already exists in the file
        if (!str_contains($file_contents, $line_to_add)) {
            // If the line does not exist, add it to the boot() method
            $pattern = '/\$this->routes\(function\s*\(\)\s*{\s*/';
            $replacement = "$0{$line_to_add}";

            $file_contents = preg_replace($pattern, $replacement, $file_contents, 1);
            // Write the modified contents back to the file
            !(file_put_contents($routeServiceProvider, $file_contents)) ?? Log::info('succee    d');
        }
    }
}
