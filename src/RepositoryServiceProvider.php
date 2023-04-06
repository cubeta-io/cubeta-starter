<?php

namespace Cubeta\CubetaStarter;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use SplFileInfo;

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
        $this->files = $this->app->make(Filesystem::class);
        if ($this->isConfigPublished()) {
            $this->bindAllRepositories();
        }
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
    private function bindAllRepositories(): void
    {
        $repositoryFiles = File::allFiles(app_path().'/Repositories') ;
        foreach ($repositoryFiles as $repositoryFile){
            $repositoryFileName = $repositoryFile->getBasename() ;
            $repository = str_replace('.php' , '' , $repositoryFileName) ;
            $model = str_replace('Repository' , '' , $repository) ;

            if(file_exists(app_path().'/Models/'.$model.'php')){
                $this->app->bind('\App\Repositories\\'.$repository , '\App\Models\\'.$model);
            }
        }

        $serviceFiles = File::allFiles(app_path().'/Services') ;
        foreach ($serviceFiles as $serviceFile){
            $serviceFileName = $serviceFile->getBasename() ;
            $service = str_replace('.php' , '' , $serviceFileName) ;
            $IService = 'I'.$service ;
            $modelName = str_replace('Service', '', $service);

            if(file_exists(app_path().'/Services/'.$IService.'php')){
                $this->app->bind(
                    '\App\Services\\' . $modelName . '\\' . $iService,
                    '\App\Services\\' . $modelName . '\\' . $service
                );
            }
        }
    }

    /**
     * Check inside the repositories interfaces directory and get all interfaces
     *
     * @return Collection
     */
    private function getRepositories(): Collection
    {
        $repositories = collect([]);
        if (! $this->files->isDirectory($directory = $this->getRepositoryPath())) {
            return $repositories;
        }
        $files = $this->files->files($directory);
        if (is_array($files)) {
            $interfaces = collect($files)->map(function (SplFileInfo $file) {
                return str_replace('.php', '', $file->getFilename());
            });
        }

        return $interfaces;
    }

    /**
     * Get repositories path
     *
     * @return string
     */
    private function getRepositoryPath()
    {
        return $this->app->basePath().
            '/'.config('repository.repository_directory');
    }

    /**
     * Get current repository implementation path
     *
     * @return string
     */
    private function getRepositoryCurrentImplementationPath()
    {
        return $this->app->basePath().
            '/'.config('repository.repository_directory').
            '/'.config('repository.current_repository_implementation');
    }

    /**
     * Get repository interface namespace
     *
     * @return string
     */
    private function getRepositoryInterfaceNamespace()
    {
        return config('repository.repository_namespace')."\Interfaces\\";
    }

    /**
     * Get repository namespace
     *
     * @return string
     */
    private function getRepositoryNamespace()
    {
        return config('repository.repository_namespace').
            '\\'.config('repository.current_repository_implementation');
    }

    /**
     * Get repository file name
     *
     * @return string
     */
    private function getRepositoryFileName($className)
    {
        return $className.config('repository.repository_suffix');
    }

    /**
     * Get repository names
     *
     * @return Collection
     */
    private function getRepositoryFiles()
    {
        $repositories = collect([]);
        if (! $this->files->isDirectory($repositoryDirectory = $this->getRepositoryCurrentImplementationPath())) {
            return $repositories;
        }
        $files = $this->files->files($repositoryDirectory);
        if (is_array($files)) {
            $repositories = collect($files)->map(function (SplFileInfo $file) {
                return str_replace('.php', '', $file->getFilename());
            });
        }

        return $repositories;
    }

    /**
     * Check if config is published
     *
     * @return bool
     */
    private function isConfigPublished()
    {
        $path = config_path('repository.php');
        $exists = file_exists($path);

        return $exists;
    }
}
