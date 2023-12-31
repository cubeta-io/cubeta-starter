<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

trait RouteBinding
{
    use RouteFileTrait;

    /**
     * @param string $modelName
     * @param null $actor
     * @param string $container
     * @param array $additionalRoutes
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function addRoute(string $modelName, $actor = null, string $container = 'api', array $additionalRoutes = []): void
    {
        $pluralLowerModelName = routeUrlNaming($modelName);

        if (isset($actor) && $actor != 'none' && $actor != '') {
            $actor = Str::singular(Str::lower($actor));
            $routePath = base_path() . "\\routes\\v1\\{$container}\\" . $actor . '.php';
        } else {
            if ($container == ContainerType::API) {
                $routePath = base_path() . "\\routes\\v1\\{$container}\\{$container}.php";

                if (!file_exists($routePath)) {
                    $this->addRouteFile($container, ContainerType::API);
                }
            } else {
                $routePath = base_path() . "\\routes\\v1\\{$container}\\dashboard.php";

                if (!file_exists($routePath)) {
                    $this->addRouteFile("dashboard", ContainerType::WEB);
                }
            }
        }

        $routeName = $this->getRouteName($modelName, $container, $actor);

        if ($container == 'web') {
            $route = $this->addAdditionalRoutesForAdditionalControllerMethods($modelName, $routeName, $additionalRoutes);

            $route .= "Route::get(\"dashboard/{$pluralLowerModelName}/data\", [v1\\{$modelName}" . "Controller::class, \"data\"])->name(\"{$routeName}.data\"); \n" .
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
            $this->error("Actor Routes Files Does not exist");
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

    /**
     * @param string $modelName
     * @param string $container
     * @param         $actor
     * @return string
     */
    public function getRouteName(string $modelName, string $container = 'api', $actor = null): string
    {
        $modelLowerPluralName = routeNameNaming($modelName);
        if (!isset($actor) || $actor == '' || $actor = 'none') {
            return $container . '.' . $modelLowerPluralName;
        }

        return "$actor.$container.$modelLowerPluralName";
    }

    public function addAdditionalRoutesForAdditionalControllerMethods(string $modelName, string $routeName, array $additionalRoutes = []): string
    {
        $pluralLowerModelName = routeUrlNaming($modelName);
        $routes = "\n";

        if (in_array('allPaginatedJson', $additionalRoutes)) {
            $routes .= "Route::get(\"dashboard/{$pluralLowerModelName}/all-paginated-json\", [v1\\{$modelName}" . "Controller::class, \"allPaginatedJson\"])->name(\"{$routeName}.allPaginatedJson\"); \n";
        }

        return $routes;
    }
}
