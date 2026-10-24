<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Modules\Routes;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Enums\MiddlewareArrayGroupEnum;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\Stub\Builders\Routes\RoutesFileStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Middlewares\AuthMiddlewareStubBuilder;

trait RouteBinding
{
    private function actorFileNaming(?string $actor = null): ?string
    {
        return $actor
            ? str($actor)
                ->singular()
                ->lower()
                ->snake()
                ->replace('_', '-')
                ->toString()
            : null;
    }

    public function addAdditionalRoutesForAdditionalControllerMethods(CubeTable $table, ?string $actor = null, array $additionalRoutes = []): array
    {
        $routes = [];
        if (in_array('allPaginatedJson', $additionalRoutes)) {
            $routes[] = Routes::allPaginatedJson($table, $actor);
        }

        return $routes;
    }

    public function addAndRegisterAuthenticateMiddleware(bool $override = false): void
    {
        $middlewarePath = CubePath::make('/app/Http/Middleware/Authenticate.php');
        AuthMiddlewareStubBuilder::make()
            ->webLoginPageRoute(Routes::loginPage()->name)
            ->generate($middlewarePath, $override);

        FileUtils::registerMiddleware(
            "'authenticated' => Authenticate::class",
            MiddlewareArrayGroupEnum::ALIAS,
            new PhpImportString('App\Http\Middleware\Authenticate')
        );
    }

    public function addIndexPageRoute(): void
    {
        $version = config('cubeta-starter.version');
        $routeActor = Settings::make()->installedWebAuth() ? 'protected' : 'public';
        $routeFile = $this->getRouteFilePath(ContainerType::WEB, $routeActor, $version);
        $route = Routes::dashboardPage(Settings::make()->installedWebAuth());
        $content = $routeFile->getContent();

        if (! FileUtils::contentExistInFile($routeFile, $route)) {
            $content .= "\n$route\n";
        } else {
            return;
        }

        $routeFile->putContent($content);
        $routeFile->format();
    }

    /**
     * @throws FileNotFoundException
     */
    public function addRoute(CubeTable $table, ?string $actor = null, string $container = ContainerType::API, array $additionalRoutes = []): void
    {
        $version = config('cubeta-starter.version', 'v1');
        $isWeb = ContainerType::isWeb($container);

        $routePath = $this->getRouteFilePath($container, $actor, $version);

        if (! $routePath->exist()) {
            $this->addRouteFile($actor, $container, $version);
        }

        $routes = $table->crudRoutes($actor, $container);
        $additionalRoutes = $this->addAdditionalRoutesForAdditionalControllerMethods($table, $actor, $additionalRoutes);
        $routes->push(...$additionalRoutes);

        if ($isWeb) {
            $importStatement = new PhpImportString(config('cubeta-starter.web_controller_namespace')."\\$version");
        } else {
            $importStatement = new PhpImportString(config('cubeta-starter.api_controller_namespace')."\\$version");
        }

        foreach ($routes as $key => $route) {
            if ($this->routeExist($routePath, $route)) {
                CubeLog::contentAlreadyExists($route, $routePath->fullPath, "Adding New Route To : [$routePath->fullPath]");
                unset($routes[$key]);
            }
        }

        if (! count($routes)) {
            return;
        }

        $route = $routes->map(fn ($r) => $r->toString())->implode("\n");

        FileUtils::addImportStatement($importStatement, $routePath);

        if ($this->routeExist($routePath, $route)) {
            CubeLog::contentAlreadyExists($route, $routePath->fullPath, "Adding New Route To : [$routePath->fullPath]");

            return;
        }

        if ($routePath->putContent($route, FILE_APPEND)) {
            CubeLog::contentAppended($route, $routePath->fullPath);
            $routePath->format();
        } else {
            CubeLog::failedAppending($route, $routePath->fullPath);
        }
    }

    public function addRouteFile(?string $actor = null, string $container = ContainerType::API, string $version = 'v1', array $middlewares = [], bool $override = false): void
    {
        $actor = $this->actorFileNaming($actor);

        $filePath = $this->getRouteFilePath($container, $actor, $version);

        $filePath->ensureDirectoryExists();

        RoutesFileStubBuilder::make()
            ->generate($filePath, $override);
        $this->registerRouteFile($filePath, $container, $middlewares);
    }

    public function addSetLocalRoute(): void
    {
        $middlewarePath = CubePath::make('/app/Http/Middleware/AcceptedLanguagesMiddleware.php');

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

        $routePath = CubePath::make('routes/web.php');

        if ($routePath->exist() && ! FileUtils::contentExistInFile($routePath, $route)) {
            $routePath->putContent($route, FILE_APPEND);
        }
    }

    public function getRouteFilePath(string $container, ?string $actor = null, string $version = 'v1'): CubePath
    {
        if ($actor && $actor != 'none') {
            $actor = $this->actorFileNaming($actor);

            return CubePath::make("routes/{$version}/{$container}/{$actor}.php");
        } else {
            if (
                (ContainerType::isApi($container) && Settings::make()->installedApiAuth())
                || (ContainerType::isWeb($container) && Settings::make()->installedWebAuth())
            ) {
                return CubePath::make("routes/{$version}/{$container}/protected.php");
            } else {
                return CubePath::make("routes/{$version}/{$container}/public.php");
            }
        }
    }

    public function registerRouteFile(CubePath $routeFilePath, string $container = ContainerType::API, array $middlewares = []): void
    {
        $bootstrapFilePath = CubePath::make('/bootstrap/app.php');

        if (! $bootstrapFilePath->exist()) {
            CubeLog::add(new NotFound($bootstrapFilePath->fullPath, "Registering [$routeFilePath->fullPath] in the app routes"));
        }

        $lineToAdd = '';

        if (count($middlewares)) {
            $middlewares = "'".str_replace(',', "','", implode(',', $middlewares))."'";
        } else {
            $middlewares = '';
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
     * @throws FileNotFoundException
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
}
