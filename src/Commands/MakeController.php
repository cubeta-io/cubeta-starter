<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
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
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $actor = $this->argument('actor');

        $this->createController($modelName, $actor);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createController($modelName, $actor): void
    {
        $modelName = $this->modelNaming($modelName);
        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNameLower}' => strtolower($modelName),
        ];

        $controllerName = $this->getControllerName($modelName);
        $controllerPath = $this->getControllerPath($controllerName);

        new CreateFile(
            $stubProperties,
            $controllerPath,
            __DIR__.'/stubs/controller.api.stub'
        );
        $this->line("<info>Created controller:</info> $controllerName");
        $this->addRoute($modelName, $actor);
        $this->formatfile($controllerPath);
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

    public function addRoute($modelName, $actor): void
    {
        $pluralLowerModelName = $this->routeNaming($modelName);

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
                $this->formatfile($apiPath);
                $this->line('<info>Controller Route Appended Successfully</info>');
            } else {
                $this->line('<info>Failed to Append a Route For This Controller</info>');
            }
        } else {
            $this->line("<danger>Actor Routes Files Doesn't exist</danger>");
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
        $this->formatfile($filePath);
    }

    public function getRouteName($actor, $modelName): array|string
    {
        $lowerModelName = $this->routeNaming($modelName);

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
}
