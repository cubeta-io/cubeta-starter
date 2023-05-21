<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait RouteBinding
{
    /**
     * @param string $modelName
     * @param $actor
     * @param string $container
     * @return void
     */
    public function addRoute(string $modelName, $actor = null, string $container = 'api'): void
    {
        $pluralLowerModelName = $this->routeNaming($modelName);

        if (isset($actor) && $actor != 'none') {
            $actor = Str::singular(Str::lower($actor));
            $routePath = base_path() . "\\routes\\$container\\" . $actor . '.php';
            $routeName = $this->getRouteName($modelName, $container, $actor);
        } else {
            $routePath = base_path() . "\\routes\\$container.php";
            $routeName = $this->getRouteName($modelName, $container);
        }

        if ($container == 'web') {
            $route = 'Route::Resource("/' . $pluralLowerModelName . '" , v1\\' . $modelName . 'Controller::class)->names("' . $routeName . '") ;' . "\n";
            $importStatement = 'use App\Http\Controllers\WEB\v1;';
        } else {
            $route = 'Route::apiResource("/' . $pluralLowerModelName . '" , v1\\' . $modelName . 'Controller::class)->names("' . $routeName . '") ;' . "\n";
            $importStatement = 'use App\Http\Controllers\API\v1;';
        }
        if (file_exists($routePath)) {
            $this->addImportStatement($importStatement, $routePath);

            if (!($this->checkIfRouteExist($routePath, $route))) {
                return;
            }

            if (file_put_contents($routePath, $route, FILE_APPEND)) {
                $this->line('<info>Controller Route Appended Successfully</info>');
                $this->formatFile($routePath);
            } else {
                $this->line('<info>Failed to Append a Route For This Controller</info>');
            }
        } else {
            $this->line("<danger>Actor Routes Files Doesn't exist</danger>");
        }
    }

    /**
     * @param string $importStatement
     * @param string $filePath
     * @return void
     */
    public function addImportStatement(string $importStatement, string $filePath): void
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
        $contents = substr_replace($contents, "\n" . $importStatement . "\n", $insertIndex, 0);

        // Write the updated contents back to the file
        file_put_contents($filePath, $contents);
    }

    /**
     * @param string $modelName
     * @param string $container
     * @param null $actor
     * @return string
     */
    public function getRouteName(string $modelName, string $container = 'api', $actor = null): string
    {
        $modelLowerPluralName = strtolower(Str::plural($modelName));
        if (!isset($actor) || $actor == '' || $actor = 'none') {
            return $container . '.' . $modelLowerPluralName;
        } else {
            return $container . '.' . $actor . '.' . $modelLowerPluralName;
        }
    }

    /**
     * @param string $routePath
     * @param string $route
     * @return bool
     */
    public function checkIfRouteExist(string $routePath, string $route): bool
    {
        $file = file_get_contents($routePath);
        if (Str::contains($file, $route)) {
            return false;
        }

        $fileLines = File::lines($routePath);
        foreach ($fileLines as $line) {
            $cleanLine = trim($line);
            if (Str::contains($cleanLine, $route)) {
                return false;
            }
        }

        return true;
    }
}
