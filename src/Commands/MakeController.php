<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeController extends Command
{
    use AssistCommand;

    protected $signature = 'create:controller
        {name : The name of the model }?
        {actor? : The actor of the endpoint of this model }';

    protected $description = 'Create a new controller';

    /**
     * Handle the command
     *
     * @throws BindingResolutionException|FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $actor = $this->argument('actor');

        $this->createController($modelName, $actor);
    }

    /**
     * @throws BindingResolutionException|FileNotFoundException
     * @throws Exception
     */
    private function createController($modelName, $actor)
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
            __DIR__.'/stubs/controller.api.stub'
        );
        $this->line("<info>Created controller:</info> $controllerName");
        $this->addRoute($modelName, $actor);
    }

    private function getControllerName($modelName): string
    {
        return $modelName.'Controller';
    }

    /**
     * @throws BindingResolutionException
     */
    private function getControllerPath($controllerName): string
    {
        $path = $this->appPath().'/app/Http/Controllers/API/v1';

        $this->ensureDirectoryExists($path);

        return $path."/$controllerName".'.php';
    }

    /**
     * @throws Exception
     */
    public function addRoute($modelName, $actor): void
    {
        $pluralLowerModelName = Str::singular(Str::lower($modelName));

        if (isset($actor) && $actor != 'none') {
            $actor = Str::singular(Str::lower($actor));
            $apiPath = base_path().'\routes\api\\'.$actor.'.php';
            $routeName = $this->getRouteName($actor, $modelName);
        } else {
            $apiPath = base_path().'\routes\\api.php';
            $routeName = 'api.'.$pluralLowerModelName;
        }

        $route = 'Route::apiResource("/'.$pluralLowerModelName.'" , v1\\'.$modelName.'Controller::class)->names("'.$routeName.'") ;'."\n";
        $importStatement = 'use App\Http\Controllers\API\v1;';

        if (file_exists($apiPath)) {
            $this->addImportStatement($importStatement, $apiPath);

            if (! ($this->checkIfRouteExist($apiPath, $route))) {
                return;
            }

            if (file_put_contents($apiPath, $route, FILE_APPEND)) {
                $this->line('<info>Controller Route Appended Successfully</info>');
            } else {
                $this->line('<info>Failed to Append a Route For This Controller</info>');
            }
        } else {
            $this->line("<danger>Actor Routes Files Deosn't exist</danger>");
        }
    }

    public function addImportStatement(string $importStatement, string $filePath = 'routes/api.php'): void
    {
        $contents = file_get_contents($filePath);

        if (Str::contains($contents, $importStatement)) {
            return;
        }

        // Check if import statement already exists
        $fileLines = File::lines($filePath);
        foreach ($fileLines as $line) {
            $cleanLine = trim($line);
            if (Str::contains($cleanLine, $importStatement)) {
                return;
            }
        }

        // Find the last "use" statement and insert the new import statement after it
        $lastUseIndex = strrpos($contents, 'use ');
        $insertIndex = $lastUseIndex !== false ? $lastUseIndex - 1 : 0;
        $contents = substr_replace($contents, "\n".$importStatement."\n", $insertIndex, 0);

        // Write the updated contents back to the file
        file_put_contents($filePath, $contents);
    }

    /**
     * @return array|string|string[]
     */
    public function getRouteName($actor, $modelName): array|string
    {
        $lowerModelName = Str::lower($modelName);

        return 'api.'.$actor.'.'.$lowerModelName;
    }

    public function checkIfRouteExist(string $apiPath, string $route): bool
    {
        $file = file_get_contents($apiPath);
        if (Str::contains($file, $route)) {
            return false;
        }

        $fileLines = File::lines($apiPath);
        foreach ($fileLines as $line) {
            $cleanLine = trim($line);
            if (Str::contains($cleanLine, $route)) {
                return false;
            }
        }

        return true;
    }

//    /**
//     * @param string $apiFilePath
//     * @return void
//     */
//    public function addApiFileToServiceProvider(string $apiFilePath): void
//    {
//        $routeServiceProvider = app_path('Providers/RouteServiceProvider.php');
//        $line_to_add = "\t\t Route::middleware('api')\n" .
//            "\t\t\t->prefix('api')\n" .
//            "\t\t\t->group(base_path('routes/$apiFilePath'));\n";
//
//        // Read the contents of the file
//        $file_contents = file_get_contents($routeServiceProvider);
//
//        // Check if the line to add already exists in the file
//        if (!str_contains($file_contents, $line_to_add)) {
//            // If the line does not exist, add it to the boot() method
//            $pattern = '/\$this->routes\(function\s*\(\)\s*{\s*/';
//            $replacement = "$0{$line_to_add}";
//
//            $file_contents = preg_replace($pattern, $replacement, $file_contents, 1);
//            // Write the modified contents back to the file
//            file_put_contents($routeServiceProvider, $file_contents);
//        }
//    }
}
