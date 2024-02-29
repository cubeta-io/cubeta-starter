<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Cubeta\CubetaStarter\app\Models\Path;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\LogsMessages\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\LogsMessages\Info\ContentAppended;
use Cubeta\CubetaStarter\LogsMessages\Info\SuccessGenerating;
use Cubeta\CubetaStarter\LogsMessages\Log;
use Cubeta\CubetaStarter\LogsMessages\Warnings\ContentAlreadyExist;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait RouteBinding
{
    /**
     * @param CubetaTable $table
     * @param string|null $actor
     * @param string $container
     * @param array $additionalRoutes
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function addRoute(CubetaTable $table, ?string $actor = null, string $container = ContainerType::API, array $additionalRoutes = []): void
    {
        $pluralLowerModelName = $table->routeUrlNaming();

        $routePath = $this->getRouteFilePath($container, $actor);

        if (!$routePath->exist()) {
            $this->addRouteFile($actor ?? $container, $container);
        }

        $routeName = $this->getRouteName($table, $container, $actor);

        if ($container == ContainerType::WEB) {
            $route = $this->addAdditionalRoutesForAdditionalControllerMethods($table, $routeName, $additionalRoutes);

            $route .= "Route::get(\"dashboard/{$pluralLowerModelName}/data\", [v1\\{$table->modelNaming()}" . "Controller::class, \"data\"])->name(\"{$routeName}.data\"); \n" .
                'Route::Resource("dashboard/' . $pluralLowerModelName . '" , v1\\' . $table->modelNaming() . 'Controller::class)->names("' . $routeName . '") ;' . "\n";

            $importStatement = 'use ' . config('cubeta-starter.web_controller_namespace') . ';';
        } else {
            $route = 'Route::apiResource("/' . $pluralLowerModelName . '" , v1\\' . $table->modelNaming() . 'Controller::class)->names("' . $routeName . '") ;' . "\n";
            $importStatement = 'use ' . config('cubeta-starter.api_controller_namespace') . ';';
        }

        FileUtils::addImportStatement($importStatement, $routePath);

        if (!($this->checkIfRouteExist($routePath, $route))) {
            Log::add(new ContentAlreadyExist($route, $routePath->fullPath, "Adding Import Statement To The Route File"));
            return;
        }

        if ($routePath->putContent($route, FILE_APPEND)) {
            Log::add(new ContentAppended($route, $routePath->fullPath));
            $routePath->format();
        } else {
            Log::add(new FailedAppendContent($route, $routePath->fullPath));
        }
    }

    /**
     * @param string $container
     * @param string|null $actor
     * @return Path
     */
    public function getRouteFilePath(string $container, ?string $actor = null): Path
    {
        if ($actor && $actor != "none") {
            return new Path("routes/v1/{$container}/{$actor}.php");
        }
        return new Path("routes/v1/{$container}/public.php");

    }

    /**
     * @param string|null $actor
     * @param                             $container
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function addRouteFile(?string $actor = null, $container = null): void
    {
        $actor = Str::singular(Str::lower($actor));

        $filePath = $this->getRouteFilePath($container, $actor);

        $filePath->ensureDirectoryExists();

        FileUtils::generateFileFromStub(
            ['{route}' => '//add-your-routes-here'],
            $filePath->fullPath,
            __DIR__ . '/../Commands/stubs/api.stub'
        );

        $this->addRouteFileToServiceProvider($filePath, $container);
    }

    /**
     * @param Path $routeFilePath
     * @param string $container
     * @return void
     */
    public function addRouteFileToServiceProvider(Path $routeFilePath, string $container = ContainerType::API): void
    {
        $routeServiceProvider = new Path('app/Providers/RouteServiceProvider.php');

        $lineToAdd = '';

        if ($container == ContainerType::API) {
            $lineToAdd = "\t\t Route::middleware('api')\n" .
                "\t\t\t->prefix('api')\n" .
                "\t\t\t->group(base_path('{$routeFilePath->inProjectDirectory}'));\n";
        }

        if ($container == ContainerType::WEB) {
            $lineToAdd = "\t\t Route::middleware('web')\n" .
                "\t\t\t->group(base_path('{$routeFilePath->inProjectDirectory}'));\n";
        }

        // Read the contents of the file
        $fileContent = $routeServiceProvider->getContent();

        // Check if the line to add already exists in the file
        if (!str_contains($fileContent, $lineToAdd)) {
            // If the line does not exist, add it to the boot() method
            $pattern = '/\$this->routes\(function\s*\(\)\s*{\s*/';
            $replacement = "$0{$lineToAdd}";

            $fileContent = preg_replace($pattern, $replacement, $fileContent, 1);
            // Write the modified contents back to the file
            $routeServiceProvider->putContent($fileContent);
        }

        $routeServiceProvider->format();
        Log::add(new SuccessGenerating($routeFilePath->fileName, $routeFilePath->fullPath));
    }

    /**
     * @param CubetaTable $table
     * @param string $container
     * @param string|null $actor
     * @return string
     */
    public function getRouteName(CubetaTable $table, string $container = 'api', ?string $actor = null): string
    {
        $modelLowerPluralName = $table->routeNameNaming();

        if (!isset($actor) || $actor == '' || $actor == 'none') {
            return $container . '.' . $modelLowerPluralName;
        }

        return "{$container}.{$actor}.{$modelLowerPluralName}";
    }

    /**
     * @param CubetaTable $table
     * @param string $routeName
     * @param array $additionalRoutes
     * @return string
     */
    public function addAdditionalRoutesForAdditionalControllerMethods(CubetaTable $table, string $routeName, array $additionalRoutes = []): string
    {
        $pluralLowerModelName = $table->routeUrlNaming();
        $routes = "\n";

        if (in_array('allPaginatedJson', $additionalRoutes)) {
            $routes .= "Route::get(\"dashboard/{$pluralLowerModelName}/all-paginated-json\", [v1\\{$table->modelNaming()}" . "Controller::class, \"allPaginatedJson\"])->name(\"{$routeName}.allPaginatedJson\"); \n";
        }

        return $routes;
    }

    /**
     * @param Path $routePath
     * @param string $route
     * @return bool
     */
    public function checkIfRouteExist(Path $routePath, string $route): bool
    {
        $file = $routePath->getContent();
        if (Str::contains($file, $route)) {
            return false;
        }

        $fileLines = File::lines($routePath->fullPath);
        foreach ($fileLines as $line) {
            $cleanLine = trim($line);
            if (Str::contains($cleanLine, $route)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return void
     */
    public function addSetLocalRoute(): void
    {
        $middlewarePath = new Path("/app/Http/Middleware/AcceptedLanguagesMiddleware.php");

        $controllerPath = new Path('app/Http/Controllers/SetLocaleController.php');
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

        $routePath = new Path("routes/web.php");

        if ($routePath->exist()) {
            $routePath->putContent($route, FILE_APPEND);
        }
    }
}
