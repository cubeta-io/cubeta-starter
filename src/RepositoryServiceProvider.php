<?php

namespace Cubeta\CubetaStarter;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
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
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->files = $this->app->make(Filesystem::class);
        if ($this->isConfigPublished()) {
            $this->bindAllServices();
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
    private function bindAllServices(): void
    {
        $repositories = $this->getRepositories();
        foreach ($repositories as $key => $repository) {
            $modelName = str_replace('Repository' , '' , $repository) ;

            $models = $this->getModelsFiles();
            if ($repositories->contains($modelName)) {
                $repository = $this->getRepositoryNamespace() . '\\' . $modelName.'Repository';
                $model = $this->getModelNameSpace() . $modelName;
                $this->app->bind($modelName, $repository);
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
        if (!$this->files->isDirectory($directory = $this->getRepositoryPath())) {
            return $repositories;
        }
        $files = $this->files->files($directory);
        if (is_array($files)) {
            $repositories = collect($files)->map(function (SplFileInfo $file) {
                return str_replace('.php', '', $file->getFilename());
            });
        }

        return $repositories;
    }

    /**
     * Get repositories path
     *
     * @return string
     */
    private function getRepositoryPath(): string
    {
        return $this->app->basePath() .
            '/' . config('repository.repository_directory');
    }

    /**
     * Get current repository implementation path
     *
     * @return string
     */
    private function getModelsPath(): string
    {
        return $this->app->basePath() .
            '/' . config('repository.model_directory');
    }

    /**
     * Get repository interface namespace
     *
     * @return string
     */
    private function getModelNameSpace(): string
    {
        return config('repository.model_namespace');
    }

    /**
     * Get repository namespace
     *
     * @return string
     */
    private function getRepositoryNamespace(): string
    {
        return config('repository.repository_namespace');
    }

    /**
     * Get repository names
     *
     * @return Collection
     */
    private function getModelsFiles(): Collection
    {
        $models = collect([]);

        if (!$this->files->isDirectory($modelDirectory = $this->getModelsPath())) {
            return $models;
        }

        $files = $this->files->files($modelDirectory);
        if (is_array($files)) {
            $models = collect($files)->map(function (SplFileInfo $file) {
                return str_replace('.php', '', $file->getFilename());
            });
        }

        return $models;
    }

    /**
     * Check if config is published
     *
     * @return bool
     */
    private function isConfigPublished(): bool
    {
        $path = config_path('repository.php');
        return file_exists($path);
    }
}
