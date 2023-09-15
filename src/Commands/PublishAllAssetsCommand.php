<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PublishAllAssetsCommand extends Command
{
    protected $description = 'publish all package dependencies';

    protected $signature = 'cubeta-publish';

    public function handle(): void
    {
        $tags = [
            'cubeta-starter-repositories',
            'cubeta-starter-services',
            'cubeta-starter-api-controller',
            'cubeta-starter-middlewares',
            'cubeta-starter-validation-rules',
            'cubeta-starter-traits',
            'cubeta-starter-config',
            'cubeta-starter-assets',
            'cubeta-starter-providers'
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
            '--tag' => 'cubeta-starter-handler',
            '--force' => true
        ]);
        $output . "\n" . Artisan::output();

        $this->info($output);
    }

    private function addSetLocalRoute()
    {
        if (file_exists(base_path('app/Http/Controllers/SetLocaleController.php'))) {
            $route = "Route::post('/locale', [\App\Http\Controllers\SetLocaleController::class, 'setLanguage'])->middleware('web')->name('set-locale');";
        } else {
            $route = "Route::post('/blank', function () {
                    return response()->noContent();
                })->middleware('web')->name('set-locale');";
        }

        $routePath = base_path("routes/web.php");

        if (file_exists($routePath)) {
            file_put_contents($routePath, $route, FILE_APPEND);
        }
    }
}
