<?php

namespace Cubeta\CubetaStarter\Generators\Installers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Info\SuccessGenerating;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Facades\Artisan;

class WebInstaller extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = "install-web";

    public string $type = 'installer';

    public function run(bool $override = false): void
    {
        Artisan::call("vendor:publish", ['--force' => $override, "--tag" => "cubeta-starter-web"]);
        CubeLog::add(Artisan::output());

        $this->addRouteFile('public', ContainerType::WEB);
        $this->addRouteFile('protected', ContainerType::WEB);
        $this->addSetLocalRoute();
    }

    private function addSetLocalRoute(): void
    {
        $middlewarePath = CubePath::make("/app/Http/Middleware/AcceptedLanguagesMiddleware.php");
        $controllerPath = CubePath::make("app/Http/Controllers/SetLocaleController.php");

        if ($controllerPath->exist()) {

            if ($middlewarePath->exist()) {
                $comment = "";
                $route = "Route::post('/locale', [\App\Http\Controllers\SetLocaleController::class, 'setLanguage'])->middleware('web')->withoutMiddleware([App\Http\Middleware\AcceptedLanguagesMiddleware::class])->name('set-locale');";
            } else {
                $comment = "
                    // TODO:: the package didn't detect the AcceptedLanguageMiddleware so even you deleted or there is been an error while publishing it ,
                    // so please add the middleware that handle your selected locale to withoutMiddleware() method of this route
                    ";
                $route = "Route::post('/locale', [\App\Http\Controllers\SetLocaleController::class, 'setLanguage'])->middleware('web')->withoutMiddleware([])->name('set-locale');";
            }
        } else {
            $comment = "
                    // TODO:: this is the route that will handle the selected locale of your app but the package didn't detect the controller for it due to some error or you've deleted it ,
                    // so please define a controller for it or define the functionality of this rout within it
                ";
            if ($middlewarePath->exist()) {
                $route = "Route::post('/blank', function () {
                        return response()->noContent();
                    })->middleware('web')->middleware('web')->withoutMiddleware([App\Http\Middleware\AcceptedLanguagesMiddleware::class])->name('set-locale');";
            } else {
                $route = "Route::post('/blank', function () {
                        return response()->noContent();
                    })->middleware('web')->middleware('web')->name('set-locale');";
            }
        }


        $routePath = CubePath::make("routes/v1/web/public.php");
        $routePath->ensureDirectoryExists();

        if (!$routePath->exist()) {
            $this->addRouteFile("none", ContainerType::WEB);
            CubeLog::add(new SuccessGenerating($routePath->fileName, $routePath->fullPath, "Adding The Set Local Route"));
        }

        if (!$this->routeExist($routePath, $route)) {
            $routePath->putContent("$comment \n $route", FILE_APPEND);
            CubeLog::add(new ContentAppended("$route", $routePath->fullPath));
        }
    }
}
