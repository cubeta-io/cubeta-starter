<?php

namespace Cubeta\CubetaStarter;

use App\Services\Address\AddressService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * File
     *
     * @property $files
     */
    private Filesystem $files;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
//        $this->app->bind(
//            \App\Services\Student\IStudentService::class,
//            StudentService::class
//        );
        $this->bindAllRepositoriesAndServices();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Loop through the repository interfaces and bind each interface to its
     * Repository inside the implementations
     *
     * @return void
     */
    private function bindAllRepositoriesAndServices(): void
    {
        $repositoryFiles = File::allFiles(app_path() . '/Repositories');
        foreach ($repositoryFiles as $repositoryFile) {
            $repositoryFileName = $repositoryFile->getBasename();
            $repository = str_replace('.php', '', $repositoryFileName);
            $model = str_replace('Repository', '', $repository);

            $path = str_replace('/', '\\', app_path() . '/Models/' . $model . '.php');
            if (file_exists($path)) {
                $this->app->bind(
                    'App\Repositories\\' . $repository,
                    'App\Models\\' . $model
                );
            }
        }

        $serviceFiles = File::allFiles(app_path() . '/Services');
        $services = [] ;
        foreach ($serviceFiles as $serviceFile) {
            $serviceFileName = $serviceFile->getBasename();
            $service = str_replace('.php', '', $serviceFileName);
            $iService = 'I' . $service;
            $modelName = str_replace('Service', '', $service);

            $path = str_replace('/', '\\', app_path() . '/Services/' . $modelName . '/' . $iService . '.php');

            if(in_array($service , $services)){
                continue ;
            }

            if (file_exists($path)) {
                $this->app->bind(
                    'App\Services\\' . $modelName . '\\' . $iService,
                    'App\Services\\' . $modelName . '\\' . $service
                );

                $services[] = $service ;
            }
        }
    }
}
