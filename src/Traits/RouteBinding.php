<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
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
    public function addRoute(CubeTable $table, ?string $actor = null, string $container = ContainerType::API, array $additionalRoutes = []): void
    {
        $version = config('cubeta-starter.version', 'v1');
        $isWeb = ContainerType::isWeb($container);

        $routePath = $this->getRouteFilePath($container, $actor, $version);

        if (!$routePath->exist()) {
            $this->addRouteFile($actor, $container, $version);
        }

        $controllerName = $table->getControllerName();

        if ($isWeb) {
            $routeNames = $this->getRouteNames($table, ContainerType::WEB, $actor);
            $routeUrls = $this->getRouteUrls($table->modelName, ContainerType::WEB, $actor);
            $routes = $this->addAdditionalRoutesForAdditionalControllerMethods($table, $actor, $additionalRoutes, $version);
            $routes[] = "Route::get('{$routeUrls["data_table"]}', [{$version}\\{$controllerName}::class, 'data'])->name('{$routeNames["data"]}');";
            $routes[] = "Route::post('{$routeUrls["export"]}' , [{$version}\\{$controllerName}::class , 'export'])->name('{$routeNames["export"]}');";
            $routes[] = "Route::get('{$routeUrls["import_example"]}', [{$version}\\{$controllerName}::class, 'getImportExample'])->name('{$routeNames["import_example"]}');";
            $routes[] = "Route::post('{$routeUrls["import"]}', [{$version}\\{$controllerName}::class, 'import'])->name('{$routeNames["import"]}');";
            $routes[] = "Route::Resource('{$routeUrls["resource"]}' , {$version}\\{$controllerName}::class)->names('{$routeNames["resource"]}') ;";

            $importStatement = 'use ' . config('cubeta-starter.web_controller_namespace') . "\\$version" . ';';
        } else {
            $routeNames = $this->getRouteNames($table, ContainerType::API, $actor);
            $routeUrls = $this->getRouteUrls($table->modelName, ContainerType::API, $actor);
            $routes[] = "Route::post('{$routeUrls["export"]}', [{$version}\\{$controllerName}::class, 'export'])->name('{$routeNames["export"]}');";
            $routes[] = "Route::post('{$routeUrls["import"]}', [{$version}\\{$controllerName}::class, 'import'])->name('{$routeNames["import"]}');";
            $routes[] = "Route::get('{$routeUrls["import_example"]}', [{$version}\\{$controllerName}::class, 'getImportExample'])->name('{$routeNames["import_example"]}');";
            $routes[] = "Route::apiResource('{$routeUrls["resource"]}' , {$version}\\{$controllerName}::class)->names('{$routeNames["resource"]}') ;\n";

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
     * @param string    $actor
     * @param array     $additionalRoutes
     * @param string    $version
     * @return array
     */
    public function addAdditionalRoutesForAdditionalControllerMethods(CubeTable $table, ?string $actor = null, array $additionalRoutes = [], string $version = 'v1'): array
    {
        $version = config('cubeta-starter.version');
        $routes = [];

        if (in_array('allPaginatedJson', $additionalRoutes)) {
            $url = $this->getRouteUrls($table->modelName, ContainerType::WEB)['all_paginated_json'];
            $routeName = $this->getRouteNames($table, ContainerType::WEB, $actor)["all_paginated_json"];
            $routes[] = "Route::get(\"$url\", [{$version}\\{$table->getControllerName()}" . "::class, \"allPaginatedJson\"])->name(\"{$routeName}\");";
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

    public function getWebIndexPageRoute(?string $actor = null, FrontendTypeEnum $frontendType = FrontendTypeEnum::BLADE, bool $justName = false): string
    {
        $version = config('cubeta-starter.version');
        $name = "$version.web.$actor.index";
        if ($justName) {
            return $name;
        }
        if ($frontendType == FrontendTypeEnum::REACT_TS) {
            return "Route::inertia('/$version/dashboard/', 'dashboard/Index')->name('$name');";
        } else {
            return "Route::view('/$version/dashboard' , 'dashboard.index')->name('$name');";
        }
    }

    /**
     * @param string      $modelName
     * @param string      $container
     * @param string|null $actor
     * @return array{
     *     data_table:string ,export:string,import_example:string,import:string,
     *     resource:string,store:string,create:string,update:string,edit:string,
     *     show:string,delete:string,index:string,all_paginated_json:string
     * }
     */
    public function getRouteUrls(string $modelName, string $container, ?string $actor = null): array
    {
        $version = config('cubeta-starter.version', "v1");
        $actor = $actor && $actor != 'none' ? "/$actor" : "";
        $modelRouteName = CubeTable::create($modelName)->routeUrlNaming();
        $idVariable = Str::camel($modelRouteName) . "Id";

        if (ContainerType::isWeb($container)) {
            return [
                'data_table'         => "/$version/dashboard$actor/$modelRouteName/data",
                'export'             => "/$version/dashboard$actor/$modelRouteName/export",
                'import_example'     => "/$version/dashboard$actor/$modelRouteName/get-import-example",
                'import'             => "/$version/dashboard$actor/$modelRouteName/import",
                'resource'           => "/$version/dashboard$actor/$modelRouteName",
                "store"              => "/$version/dashboard$actor/$modelRouteName",
                "create"             => "/$version/dashboard$actor/$modelRouteName/create",
                "update"             => "/$version/dashboard$actor/$modelRouteName/$idVariable",
                'edit'               => "/$version/dashboard$actor/$modelRouteName/$idVariable/edit",
                'show'               => "/$version/dashboard$actor/$modelRouteName/$idVariable",
                'delete'             => "/$version/dashboard$actor/$modelRouteName/$idVariable/delete",
                "index"              => "/$version/dashboard$actor/$modelRouteName",
                "all_paginated_json" => "/$version/dashboard$actor/{$modelRouteName}/all-paginated-json",
            ];
        } else {
            return [
                'export'         => "/{$version}{$actor}/$modelRouteName/export",
                'import_example' => "/{$version}{$actor}/$modelRouteName/get-import-example",
                'import'         => "/{$version}{$actor}/$modelRouteName/import",
                'resource'       => "/{$version}{$actor}/$modelRouteName",
                "store"          => "/{$version}{$actor}/$modelRouteName",
                "update"         => "/{$version}{$actor}/$modelRouteName/$idVariable",
                'show'           => "/{$version}{$actor}/$modelRouteName/$idVariable",
                'delete'         => "/{$version}{$actor}/$modelRouteName/$idVariable/delete",
                "index"          => "/{$version}{$actor}/$modelRouteName",
            ];
        }
    }

    /**
     * @param CubeTable   $table
     * @param string      $container
     * @param string|null $actor
     * @return array{
     *     data:string, export:string, import:string, import_example:string, index:string, store:string,
     *     create:string, update:string, edit:string, show:string, delete:string,
     *     all_paginated_json:string,resource:string
     * }
     */
    public function getRouteNames(CubeTable $table, string $container, ?string $actor = null): array
    {
        $base = $this->getRouteName($table, $container, $actor);
        if (ContainerType::isApi($container)) {
            return [
                'export'         => "$base.export",
                'import'         => "$base.import",
                'import_example' => "$base.import.get.example",
                "index"          => "$base.index",
                "store"          => "$base.store",
                "update"         => "$base.update",
                "show"           => "$base.show",
                "delete"         => "$base.destroy",
                "resource"       => $base,
            ];
        } else {
            return [
                "data"               => "$base.data",
                'export'             => "$base.export",
                'import'             => "$base.import",
                'import_example'     => "$base.get.example",
                "index"              => "$base.index",
                "store"              => "$base.store",
                "create"             => "$base.create",
                "update"             => "$base.update",
                "edit"               => "$base.edit",
                "show"               => "$base.show",
                "delete"             => "$base.destroy",
                "all_paginated_json" => "$base.allPaginatedJson",
                "resource"           => $base,
            ];
        }
    }

    public function addIndexPageRoute(string $version, FrontendTypeEnum $frontendStack = FrontendTypeEnum::BLADE): void
    {
        $publicRoutFilePath = $this->getRouteFilePath(ContainerType::WEB, "protected" , $version);
        $content = $publicRoutFilePath->getContent();

        if (Settings::make()->installedAuth()) {
            $route = $this->getWebIndexPageRoute("protected", $frontendStack);
        } else {
            $route = $this->getWebIndexPageRoute("public", $frontendStack);
        }

        if (!FileUtils::contentExistInFile($publicRoutFilePath, $route)) {
            $content .= "\n$route\n";
        } else {
            return;
        }

        $publicRoutFilePath->putContent($content);
        $publicRoutFilePath->format();
    }
}
