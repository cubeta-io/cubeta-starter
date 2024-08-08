<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Info\SuccessGenerating;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

trait RouteBinding
{
    /**
     * @param bool $override
     * @return void
     */
    public function addAndRegisterAuthenticateMiddleware(bool $override = false): void
    {
        $this->generateFileFromStub(
            ['{{web-login-page-route}}' => $this->getAuthRouteNames(ContainerType::WEB, null, true)['login-page']],
            CubePath::make('/app/Http/Middleware/Authenticate.php')->fullPath,
            $override,
            CubePath::stubPath('middlewares/Authenticate.stub')
        );

        FileUtils::registerMiddleware(
            "'authenticated' => Authenticate::class",
            MiddlewareArrayGroupEnum::ALIAS,
            'use App\Http\Middleware\Authenticate;'
        );
    }

    /**
     * @param CubeTable   $table
     * @param string|null $actor
     * @param string      $container
     * @param array       $additionalRoutes
     * @param string      $version
     * @return void
     */
    public function addRoute(CubeTable $table, ?string $actor = null, string $container = ContainerType::API, array $additionalRoutes = [], string $version = 'v1'): void
    {
        $isWeb = ContainerType::isWeb($container);
        $pluralLowerModelName = $table->routeUrlNaming(withVersion: !$isWeb);

        $routePath = $this->getRouteFilePath($container, $actor, $version);

        if (!$routePath->exist()) {
            $this->addRouteFile($actor, $container, $version);
        }

        $routeName = $this->getRouteName($table, $container, $actor);

        $controllerName = $table->getControllerName();

        if ($isWeb) {
            $routes = $this->addAdditionalRoutesForAdditionalControllerMethods($table, $routeName, $additionalRoutes, $version);
            $routes[] = "Route::get('dashboard/{$pluralLowerModelName}/data', [{$version}\\{$controllerName}::class, 'data'])->name('{$routeName}.data');";
            $routes[] = "Route::post('dashboard/$pluralLowerModelName/export' , [{$version}\\{$controllerName}::class , 'export'])->name('$routeName.export');";
            $routes[] = "Route::get('dashboard/$pluralLowerModelName/get-import-example', [{$version}\\{$controllerName}::class, 'getImportExample'])->name('$routeName.get.example');";
            $routes[] = "Route::post('dashboard/$pluralLowerModelName/import', [{$version}\\{$controllerName}::class, 'import'])->name('$routeName.import');";
            $routes[] = "Route::post('dashboard/$pluralLowerModelName/export', [{$version}\\{$controllerName}::class, 'export'])->name('$routeName.export');";
            $routes[] = "Route::Resource('dashboard/{$pluralLowerModelName}' , {$version}\\{$controllerName}::class)->names('{$routeName}') ;";

            $importStatement = 'use ' . config('cubeta-starter.web_controller_namespace') . "\\$version" . ';';
        } else {
            $sub = ($actor && $actor != 'none') ? "$actor/" : '';
            $routes[] = "Route::post('/{$sub}{$pluralLowerModelName}/export', [{$version}\\{$controllerName}::class, 'export'])->name('$routeName.export');";
            $routes[] = "Route::post('/{$sub}{$pluralLowerModelName}/import', [{$version}\\{$controllerName}::class, 'import'])->name('$routeName.import');";
            $routes[] = "Route::get('/{$sub}{$pluralLowerModelName}/get-import-example', [{$version}\\{$controllerName}::class, 'getImportExample'])->name('$routeName.get.example');";
            $routes[] = "Route::apiResource('/{$sub}{$pluralLowerModelName}' , {$version}\\{$controllerName}::class)->names('$routeName') ;\n";

            $importStatement = 'use ' . config('cubeta-starter.api_controller_namespace') . "\\$version" . ';';
        }

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
     * @param string      $container
     * @param string|null $actor
     * @param string      $version
     * @return CubePath
     */
    public function getRouteFilePath(string $container, ?string $actor = null, string $version = 'v1'): CubePath
    {
        if ($actor && $actor != "none") {
            return CubePath::make("routes/{$version}/{$container}/{$actor}.php");
        } else {
            if (Settings::make()->installedAuth()) {
                return CubePath::make("routes/{$version}/{$container}/protected.php");
            } else {
                return CubePath::make("routes/{$version}/{$container}/public.php");
            }
        }
    }

    /**
     * @param string|null $actor
     * @param string      $container
     * @param string      $version
     * @param array       $middlewares
     * @return void
     */
    public function addRouteFile(?string $actor = null, string $container = ContainerType::API, string $version = 'v1', array $middlewares = []): void
    {
        $actor = Str::singular(Str::lower($actor));

        $filePath = $this->getRouteFilePath($container, $actor, $version);

        $filePath->ensureDirectoryExists();

        try {
            FileUtils::generateFileFromStub(
                ['{route}' => '//add-your-routes-here'],
                $filePath->fullPath,
                CubePath::stubPath('api.stub')
            );
            CubeLog::add(new SuccessGenerating($filePath->fileName, $filePath->fullPath, "Adding [$actor.php] Route File"));
        } catch (Exception|BindingResolutionException|FileNotFoundException $e) {
            CubeLog::add($e);
            return;
        }
        $this->registerRouteFile($filePath, $container, $middlewares);
    }

    /**
     * @param CubePath $routeFilePath
     * @param string   $container
     * @param array    $middlewares
     * @return void
     */
    public function registerRouteFile(CubePath $routeFilePath, string $container = ContainerType::API, array $middlewares = []): void
    {
        $bootstrapFilePath = CubePath::make('/bootstrap/app.php');

        if (!$bootstrapFilePath->exist()) {
            CubeLog::add(new NotFound($bootstrapFilePath->fullPath, "Registering [$routeFilePath->fullPath] in the app routes"));
        }

        $lineToAdd = '';

        if (count($middlewares)) {
            $middlewares = "'" . str_replace(',', "','", implode(",", $middlewares)) . "'";
        } else {
            $middlewares = "";
        }

        if ($container == ContainerType::API) {
            $lineToAdd = "\nRoute::middleware(['api' ,'locale', $middlewares])\n->prefix('api')\n->group(base_path('{$routeFilePath->inProjectPath}'));\n";
        }

        if ($container == ContainerType::WEB) {
            $lineToAdd = "\nRoute::middleware(['web', 'locale', $middlewares])\n->group(base_path('{$routeFilePath->inProjectPath}'));\n";
        }

        $bootstrapContent = $bootstrapFilePath->getContent();

        $patternWithThen = '/->\s*withRouting\s*\(\s*(.*?)then\s*:\s*function\s*\((.*?)\)\s*\{\s*(.*?)\s*}\s*(.*?)\s*\)/s';

        if (preg_match($patternWithThen, $bootstrapContent, $matches)) {
            if (isset($matches[3])) {
                $functionBody = $matches[3];
                if (
                    FileUtils::contentExistsInString($functionBody, $lineToAdd)
                    || FileUtils::contentExistsInString($functionBody, $routeFilePath->inProjectPath)
                ) {
                    CubeLog::add(new ContentAlreadyExist($lineToAdd, $bootstrapFilePath->fullPath, "Registering [$routeFilePath->fullPath] in the app routes"));
                    return;
                }
                $functionBody .= "\n$lineToAdd\n";
                $bootstrapContent = str_replace($matches[3], $functionBody, $bootstrapContent);
                $bootstrapFilePath->putContent($bootstrapContent);
                CubeLog::add(new ContentAppended($lineToAdd, $bootstrapFilePath->fullPath));
                FileUtils::addImportStatement('use Illuminate\Support\Facades\Route;', $bootstrapFilePath);
                $bootstrapFilePath->format();
            } else {
                CubeLog::add(new FailedAppendContent($lineToAdd, $bootstrapFilePath->fullPath, "Registering [$routeFilePath->fullPath] in the app routes"));
            }
            return;
        }

        $patternWithoutThen = '/->\s*withRouting\s*\(\s*(.*?)\s*\)/s';
        $newParameter = "then:function(){\n$lineToAdd\n}";
        if (preg_match($patternWithoutThen, $bootstrapContent, $matches)) {
            if (isset($matches[1])) {
                $parameters = $matches[1];
                if (FileUtils::contentExistsInString($parameters, $lineToAdd)) {
                    CubeLog::add(new ContentAlreadyExist($newParameter, $bootstrapFilePath->fullPath, "Registering [$routeFilePath->fullPath] in the app routes"));
                    return;
                }
                $parameters .= ",\n$newParameter,\n";
                $parameters = FileUtils::fixArrayOrObjectCommas($parameters);
                $bootstrapContent = str_replace($matches[1], $parameters, $bootstrapContent);
                $bootstrapFilePath->putContent($bootstrapContent);
                CubeLog::add(new ContentAppended($newParameter, $bootstrapFilePath->fullPath));
                FileUtils::addImportStatement('use Illuminate\Support\Facades\Route;', $bootstrapFilePath);
                $bootstrapFilePath->format();
            } else {
                CubeLog::add(new FailedAppendContent($lineToAdd, $bootstrapFilePath->fullPath, "Registering [$routeFilePath->fullPath] in the app routes"));
            }
            return;
        }

        CubeLog::add(new FailedAppendContent($lineToAdd, $bootstrapFilePath->fullPath, "Registering [$routeFilePath->fullPath] in the app routes"));
    }

    /**
     * @param CubeTable   $table
     * @param string      $container
     * @param string|null $actor
     * @return string
     */
    public function getRouteName(CubeTable $table, string $container = ContainerType::API, ?string $actor = null): string
    {
        $modelLowerPluralName = $table->routeNameNaming();
        $version = config('cubeta-starter.version');

        if (!isset($actor) || $actor == '' || $actor == 'none') {
            if (Settings::make()->installedAuth()) {
                return "$version.{$container}.protected.{$modelLowerPluralName}";
            } else {
                return "$version.{$container}.public.{$modelLowerPluralName}";
            }
        }

        return "$version.{$container}.{$actor}.{$modelLowerPluralName}";
    }

    /**
     * @param CubeTable $table
     * @param string    $routeName
     * @param array     $additionalRoutes
     * @param string    $version
     * @return array
     */
    public function addAdditionalRoutesForAdditionalControllerMethods(CubeTable $table, string $routeName, array $additionalRoutes = [], string $version = 'v1'): array
    {
        $version = config('cubeta-starter.version');
        $pluralLowerModelName = $table->routeUrlNaming();
        $routes = [];

        if (in_array('allPaginatedJson', $additionalRoutes)) {
            $routes[] = "Route::get(\"$version/dashboard/{$pluralLowerModelName}/all-paginated-json\", [{$version}\\{$table->modelNaming()}" . "Controller::class, \"allPaginatedJson\"])->name(\"{$routeName}.allPaginatedJson\");";
        }

        return $routes;
    }

    /**
     * @param CubePath $routePath
     * @param string   $route
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

        if ($routePath->exist() && !FileUtils::contentExistInFile($routePath, $route)) {
            $routePath->putContent($route, FILE_APPEND);
        }
    }

    /**
     * @param string      $container
     * @param string|null $actor
     * @param bool        $public
     * @return array{
     *     register:string ,
     *     register-page:string,
     *     login:string ,
     *     login-page:string,
     *     password-reset-request:string ,
     *     password-reset-request-page:string,
     *     validate-reset-code:string,
     *     password-reset:string,
     *     password-reset-page:string,
     *     refresh:string ,
     *     logout:string ,
     *     update-user-details:string,
     *     user-details:string
     * }
     */
    public function getAuthRouteNames(string $container = ContainerType::API, ?string $actor = null, bool $public = false): array
    {
        $version = config('cubeta-starter.version');
        if (!$actor || $actor == "none") {
            $actor = "protected";
        }

        if ($container == ContainerType::API) {
            if ($public) {
                return [
                    'register'               => "$version.api.public" . $actor == null ? '.' : ".$actor." . "register",
                    'login'                  => "$version.api.public" . $actor == null ? '.' : ".$actor." . "login",
                    "password-reset-request" => "$version.api.public" . $actor == null ? '.' : ".$actor." . "reset.password.request",
                    "validate-reset-code"    => "$version.api.public" . $actor == null ? '.' : ".$actor." . "check.reset.password.code",
                    'password-reset'         => "$version.api.public" . $actor == null ? '.' : ".$actor." . "password.reset",
                ];
            } else {
                return [
                    'refresh'             => "$version.api.$actor.refresh.token",
                    'logout'              => "$version.api.$actor.logout",
                    'update-user-details' => "$version.api.$actor.update.user.data",
                    'user-details'        => "$version.api.$actor.user.details",
                ];
            }
        } else {
            if ($public) {
                return [
                    'login'                       => "$version.web.public.login",
                    'login-page'                  => "$version.web.public.login.page",
                    'register'                    => "$version.web.public.register",
                    "register-page"               => "$version.web.public.register.page",
                    'password-reset-request'      => "$version.web.public.request.reset.password.code",
                    'password-reset-request-page' => "$version.web.public.request.reset.password.code-page",
                    'validate-reset-code'         => "$version.web.public.validate.reset.password.code",
                    'password-reset'              => "$version.web.public.change.password",
                    'password-reset-page'         => "$version.web.public.reset.password.page",
                ];
            } else {
                return [
                    'update-user-details' => "$version.web.$actor.update.user.data",
                    'user-details'        => "$version.web.$actor.user.details",
                    'logout'              => "$version.web.$actor.logout",
                ];
            }
        }
    }
}
