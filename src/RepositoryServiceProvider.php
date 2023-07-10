<?php

namespace Cubeta\CubetaStarter;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->bindAllRepositoriesAndServices();
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
                $path = str_replace('/', '\\', app_path() . '/Models/' . $model . '.php');
                if (file_exists($path)) {
                    $this->app->bind('App\Repositories\\' . $repository, function ($app) use ($repository, $model) {
                        return new ('\App\Repositories\\' . $repository)(
                            $app->make('\App\Models\\' . $model)
                        );
                    });
                }
            }
        }

        if (file_exists(app_path() . '/Services')) {
            $serviceFiles = File::allFiles(app_path() . '/Services');
            $services = [];
            foreach ($serviceFiles as $serviceFile) {
                $serviceFileName = $serviceFile->getBasename();
                $service = str_replace('.php', '', $serviceFileName);
                $iService = 'I' . $service;
                $modelName = str_replace('Service', '', $service);

                $path = str_replace('/', '\\', app_path() . '/Services/' . $modelName . '/' . $iService . '.php');

                if (in_array($service, $services)) {
                    continue;
                }

                if ($service == 'UserWebService' && request()->acceptsHtml()) {
                    $modelName = "User";
                    $iService = "IUserService";
                    $this->app->bind(
                        'App\Services\\' . $modelName . '\\' . $iService,
                        'App\Services\\' . $modelName . '\\' . $service
                    );
                    $services[] = $service;
                    continue;
                }

                if (file_exists($path)) {
                    $this->app->bind(
                        'App\Services\\' . $modelName . '\\' . $iService,
                        'App\Services\\' . $modelName . '\\' . $service
                    );

                    $services[] = $service;
                }
            }
        }
    }
}
