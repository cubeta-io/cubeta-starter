<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;

class PublishAllAssetsCommand extends Command
{
    use RouteBinding, AssistCommand;

    protected $description = 'publish all package dependencies';

    protected $signature = 'cubeta-publish';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $tags = [
            'cubeta-auth-views',
            'cubeta-starter-test-tools',
            'cubeta-starter-providers',
            'cubeta-starter-response',
            'cubeta-starter-crud',
            'cubeta-starter-locale',
            'cubeta-starter-assets',
            'cubeta-starter-config',
        ];

        $output = "";

        foreach ($tags as $tag) {
            Artisan::call('vendor:publish', [
                '--tag' => $tag,
            ]);
            $output = $output . "\n" . Artisan::output();
        }

        $this->addSetLocalRoute();

        Artisan::call('vendor:publish', [
            '--tag' => 'cubeta-starter-response',
            '--force' => true
        ]);
        $output . "\n" . Artisan::output();

        $this->info($output);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function addSetLocalRoute(): void
    {
        if (file_exists(base_path('app/Http/Controllers/SetLocaleController.php'))) {
            $route = "Route::post('/locale', [\App\Http\Controllers\SetLocaleController::class, 'setLanguage'])->name('set-locale');";
        } else {
            $route = "Route::post('/blank', function () {
                    return response()->noContent();
                })->middleware('web')->name('set-locale');";
        }

        $routePath = base_path("routes/v1/web/dashboard.php");

        if (!file_exists($routePath)) {
            $this->addRouteFile("dashboard", ContainerType::WEB);
        }

        if (file_exists($routePath)) {
            file_put_contents($routePath, $route, FILE_APPEND);
        }
    }
}
