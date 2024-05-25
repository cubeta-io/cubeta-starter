<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\BaseService;
use App\Services\Contracts\IBaseService;

class CubetaStarterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->bindAllRepositoriesAndServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(base_path('resources/views/components/form'));
        Blade::anonymousComponentPath(base_path('resources/views/components/form/checkboxes'));
        Blade::anonymousComponentPath(base_path('resources/views/components/form/fields'));
        Blade::anonymousComponentPath(base_path('resources/views/components/form/validation'));
        Blade::anonymousComponentPath(base_path('resources/views/components/show/'));
        Blade::anonymousComponentPath(base_path('resources/views/components/images'));
    }

    /**
     * Loop through all the repositories and the services and bind them
     */
    private function bindAllRepositoriesAndServices(): void
    {
        if (file_exists(app_path() . '/Repositories')) {
            $repositoryFiles = File::allFiles(app_path() . '/Repositories');
            foreach ($repositoryFiles as $repositoryFile) {
                $repositoryFileName = $repositoryFile->getBasename();
                $repository = str_replace('.php', '', $repositoryFileName);
                $model = str_replace('Repository', '', $repository);
                $path = str_replace('/', DIRECTORY_SEPARATOR, app_path() . '/Models/' . $model . '.php');
                if (file_exists($path)) {
                    $this->app->singleton('App\Repositories\\' . $repository, function ($app) use ($repository, $model) {
                        return new ('App\Repositories\\' . $repository)(
                            $app->make('\App\Models\\' . $model)
                        );
                    });
                }
            }
        }

        if (file_exists(app_path() . '/Services/Contracts')) {
            $this->app->bind(
                IBaseService::class,
                BaseService::class
            );
        }

        if (file_exists(app_path() . '/Services')) {
            $services = File::allFiles(app_path() . '/Services');
            foreach ($services as $serviceFile) {
                $serviceFileName = $serviceFile->getBasename();
                $service = str_replace('.php', '', $serviceFileName);
                $model = str_replace('Service', '', $service);
                $path = str_replace('/', DIRECTORY_SEPARATOR, app_path() . '/Models/' . $model . '.php');
                if (file_exists($path)) {
                    $this->app->singleton("App\Services\\$model\\" . $service, function ($app) use ($service, $model) {
                        return new ("App\Services\\$model\\" . $service)(
                            $app->make('App\Repositories\\' . $model . "Repository")
                        );
                    });
                }
            }
        }
    }
}
