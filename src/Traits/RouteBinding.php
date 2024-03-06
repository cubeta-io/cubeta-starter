<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\app\Models\CubeTable;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Info\SuccessGenerating;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait RouteBinding
{
    /**
     * @param CubeTable $table
     * @param string|null $actor
     * @param string $container
     * @param array $additionalRoutes
     * @return void
     */
    public function addRoute(CubeTable $table, ?string $actor = null, string $container = ContainerType::API, array $additionalRoutes = []): void
    {
        $pluralLowerModelName = $table->routeUrlNaming();

        $routePath = $this->getRouteFilePath($container, $actor);

        if (!$routePath->exist()) {
            $this->addRouteFile($actor, $container);
        }

        $routeName = $this->getRouteName($table, $container, $actor);

        if ($container == ContainerType::WEB) {
            $routes = $this->addAdditionalRoutesForAdditionalControllerMethods($table, $routeName, $additionalRoutes);
            $routes [] = "Route::get(\"dashboard/{$pluralLowerModelName}/data\", [v1\\{$table->modelNaming()}" . "Controller::class, \"data\"])->name(\"{$routeName}.data\");";
            $routes [] = 'Route::Resource("dashboard/' . $pluralLowerModelName . '" , v1\\' . $table->modelNaming() . 'Controller::class)->names("' . $routeName . '") ;';

            foreach ($routes as $key => $route) {
                if ($this->routeExist($routePath, $route)) {
                    CubeLog::add(new ContentAlreadyExist($route, $routePath->fullPath, "Adding New Route To : [$routePath->fullPath]"));
                    unset($routes[$key]);
                }
            }

            if (!count($routes)) {
                return;
            }

            $route = implode("\n", $routes);
            $importStatement = 'use ' . config('cubeta-starter.web_controller_namespace') . ';';
        } else {
            $route = 'Route::apiResource("/' . $pluralLowerModelName . '" , v1\\' . $table->modelNaming() . 'Controller::class)->names("' . $routeName . '") ;' . "\n";
            $importStatement = 'use ' . config('cubeta-starter.api_controller_namespace') . ';';
        }

        FileUtils::addImportStatement($importStatement, $routePath);

        if ($this->routeExist($routePath, $route)) {
            CubeLog::add(new ContentAlreadyExist($route, $routePath->fullPath, "Adding New Route To : [$routePath->fullPath]"));
            return;
        }

        if ($routePath->putContent($route, FILE_APPEND)) {
            CubeLog::add(new ContentAppended($route, $routePath->fullPath));
            $routePath->format();
        } else {
            CubeLog::add(new FailedAppendContent($route, $routePath->fullPath));
        }
    }

    /**
     * @param string $container
     * @param string|null $actor
     * @return CubePath
     */
    public function getRouteFilePath(string $container, ?string $actor = null): CubePath
    {
        if ($actor && $actor != "none") {
            return CubePath::make("routes/v1/{$container}/{$actor}.php");
        }
        return CubePath::make("routes/v1/{$container}/public.php");
    }

    /**
     * @param string|null $actor
     * @param string $container
     * @return void
     */
    public function addRouteFile(?string $actor = null, string $container = ContainerType::API): void
    {
        $actor = Str::singular(Str::lower($actor));

        $filePath = $this->getRouteFilePath($container, $actor);

        $filePath->ensureDirectoryExists();

        try {
            FileUtils::generateFileFromStub(
                ['{route}' => '//add-your-routes-here'],
                $filePath->fullPath,
                __DIR__ . '/../Commands/stubs/api.stub'
            );
        } catch (Exception|BindingResolutionException|FileNotFoundException $e) {
            CubeLog::add($e);
            return;
        }

        $this->addRouteFileToServiceProvider($filePath, $container);
    }

    /**
     * @param CubePath $routeFilePath
     * @param string $container
     * @return void
     */
    public function addRouteFileToServiceProvider(CubePath $routeFilePath, string $container = ContainerType::API): void
    {
        $routeServiceProvider = CubePath::make('app/Providers/RouteServiceProvider.php');

        $lineToAdd = '';

        if ($container == ContainerType::API) {
            $lineToAdd = "Route::middleware('api')\n->prefix('api')\n->group(base_path('{$routeFilePath->inProjectPath}'));\n";
        }

        if ($container == ContainerType::WEB) {
            $lineToAdd = "Route::middleware('web')\n->group(base_path('{$routeFilePath->inProjectPath}'));\n";
        }

        // Read the contents of the file
        $fileContent = $routeServiceProvider->getContent();

        // Check if the line to add already exists in the file
        if (!FileUtils::contentExistInFile($routeServiceProvider, $lineToAdd)) {
            // If the line does not exist, add it to the boot() method
            $pattern = '/\$this->routes\(function\s*\(\)\s*{\s*/';
            $replacement = "$0{$lineToAdd}";

            $fileContent = preg_replace($pattern, $replacement, $fileContent, 1);
            // Write the modified contents back to the file
            $routeServiceProvider->putContent($fileContent);
        }

        $routeServiceProvider->format();
        CubeLog::add(new SuccessGenerating($routeFilePath->fileName, $routeFilePath->fullPath));
    }

    /**
     * @param CubeTable $table
     * @param string $container
     * @param string|null $actor
     * @return string
     */
    public function getRouteName(CubeTable $table, string $container = ContainerType::API, ?string $actor = null): string
    {
        $modelLowerPluralName = $table->routeNameNaming();

        if (!isset($actor) || $actor == '' || $actor == 'none') {
            return "{$container}.public.{$modelLowerPluralName}";
        }

        return "{$container}.{$actor}.{$modelLowerPluralName}";
    }

    /**
     * @param CubeTable $table
     * @param string $routeName
     * @param array $additionalRoutes
     * @return array
     */
    public function addAdditionalRoutesForAdditionalControllerMethods(CubeTable $table, string $routeName, array $additionalRoutes = []): array
    {
        $pluralLowerModelName = $table->routeUrlNaming();
        $routes = [];

        if (in_array('allPaginatedJson', $additionalRoutes)) {
            $routes [] = "Route::get(\"dashboard/{$pluralLowerModelName}/all-paginated-json\", [v1\\{$table->modelNaming()}" . "Controller::class, \"allPaginatedJson\"])->name(\"{$routeName}.allPaginatedJson\");";
        }

        return $routes;
    }

    /**
     * @param CubePath $routePath
     * @param string $route
     * @return bool
     */
    public function routeExist(CubePath $routePath, string $route): bool
    {
        $file = $routePath->getContent();
        if (Str::contains($file, $route)) {
            return true;
        }

        $fileLines = File::lines($routePath->fullPath);
        foreach ($fileLines as $line) {
            if (Str::contains(FileUtils::extraTrim($line), FileUtils::extraTrim($route))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return void
     */
    public function addSetLocalRoute(): void
    {
        $middlewarePath = CubePath::make("/app/Http/Middleware/AcceptedLanguagesMiddleware.php");

        $controllerPath = CubePath::make('app/Http/Controllers/SetLocaleController.php');
        if ($controllerPath->exist()) {
            if ($middlewarePath->exist()) {
                $route = "Route::post('/locale', [\App\Http\Controllers\SetLocaleController::class, 'setLanguage'])->middleware('web')->withoutMiddleware([App\Http\Middleware\AcceptedLanguagesMiddleware::class])->name('set-locale');";
            } else {
                $route = "
                    // TODO:: the package didn't detect the AcceptedLanguageMiddleware so even you deleted or there is been an error while publishing it ,
                    // so please add the middleware that handle your selected locale to withoutMiddleware() method of this route
                    Route::post('/locale', [\App\Http\Controllers\SetLocaleController::class, 'setLanguage'])->middleware('web')->withoutMiddleware([])->name('set-locale');
                    ";
            }
        } else {
            if ($middlewarePath->exist()) {
                $route = "
                    // TODO:: this is the route that will handle the selected locale of your app but the package didn't detect the controller for it due to some error or you've deleted it ,
                    // so please define a controller for it or define the functionality of this rout within it
                    Route::post('/blank', function () {
                        return response()->noContent();
                    })->middleware('web')->middleware('web')->withoutMiddleware([App\Http\Middleware\AcceptedLanguagesMiddleware::class])->name('set-locale');";
            } else {
                $route = "
                    // TODO:: this is the route that will handle the selected locale of your app but the package didn't detect the controller for it due to some error or you've deleted it ,
                    // so please define a controller for it or define the functionality of this rout within it
                    Route::post('/blank', function () {
                        return response()->noContent();
                    })->middleware('web')->middleware('web')->name('set-locale');";
            }
        }

        $routePath = CubePath::make("routes/web.php");

        if ($routePath->exist() && !FileUtils::contentExistInFile($routePath , $route)) {
            $routePath->putContent($route, FILE_APPEND);
        }
    }
}
