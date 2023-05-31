<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait RouteBinding
{
    public function addRoute(string $modelName, $actor = null, string $container = 'api'): void
    {
        $pluralLowerModelName = routeUrlNaming($modelName);

        if (isset($actor) && $actor != 'none') {
            $actor = Str::singular(Str::lower($actor));
            $routePath = base_path() . "\\routes\\$container\\" . $actor . '.php';
            $routeName = $this->getRouteName($modelName, $container, $actor);
        } else {
            $routePath = base_path() . "\\routes\\$container.php";
            $routeName = $this->getRouteName($modelName, $container, $actor);
        }

        if ($container == 'web') {
            $route = "Route::get(\"dashboard/$pluralLowerModelName/data\", [v1\\$modelName" . "Controller::class, \"data\"])->name(\"$routeName.data\"); \n" .
                'Route::Resource("dashboard/' . $pluralLowerModelName . '" , v1\\' . $modelName . 'Controller::class)->names("' . $routeName . '") ;' . "\n";
            $importStatement = 'use App\Http\Controllers\WEB\v1;';
        } else {
            $route = 'Route::apiResource("/' . $pluralLowerModelName . '" , v1\\' . $modelName . 'Controller::class)->names("' . $routeName . '") ;' . "\n";
            $importStatement = 'use App\Http\Controllers\API\v1;';
        }
        if (file_exists($routePath)) {
            addImportStatement($importStatement, $routePath);

            if (!($this->checkIfRouteExist($routePath, $route))) {
                return;
            }

            if (file_put_contents($routePath, $route, FILE_APPEND)) {
                $this->info('Controller Route Appended Successfully');
                $this->formatFile($routePath);
            } else {
                $this->error('Failed to Append a Route For This Controller');
            }
        } else {
            $this->error("Actor Routes Files Doesn't exist");
        }
    }

    /**
     * @param null $actor
     */
    public function getRouteName(string $modelName, string $container = 'api', $actor = null): string
    {
        $modelLowerPluralName = routeNameNaming($modelName);
        if (!isset($actor) || $actor == '' || $actor = 'none') {
            return $container . '.' . $modelLowerPluralName;
        } else {
            return $container . '.' . $actor . '.' . $modelLowerPluralName;
        }
    }

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
