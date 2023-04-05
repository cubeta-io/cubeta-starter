<?php

namespace Cubeta\CubetaStarter;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use SplFileInfo;

class ServiceServiceProvider extends ServiceProvider
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
    public function boot()
    {
        //
    }

    /**
     * Loop through the repository interfaces and bind each interface to its
     * Service inside the implementations
     *
     * @return void
     */
    private function bindAllRepositories(): void
    {
        $serviceInterfaces = $this->getServiceInterfaces();
        foreach ($serviceInterfaces as $key => $serviceInterface) {
            $className = str_replace('Service', "", $serviceInterface);
            $className = str_replace('I' , "" , $className) ;
            $serviceName = $this->getServiceFileName($className);
            $services = $this->getServiceFiles();
            if ($services->contains($serviceName)) {
                $serviceInterface = $this->getServiceInterfaceNamespace() . $serviceInterface;
                $service = $this->getServiceNamespace() . "\\" . $serviceName;
                $this->app->bind($serviceInterface, $service);
            }
        }
    }

    /**
     * Check inside the repositories interfaces directory and get all interfaces
     *
     * @return Collection
     */
    private function getServiceInterfaces(): Collection
    {
        $interfaces = collect([]);
        if (! $this->files->isDirectory($directory = $this->getServiceInterfacesPath())) {
            return $interfaces;
        }
        $files = $this->files->files($directory);
        if (is_array($files)) {
            $interfaces = collect($files)->map(function (SplFileInfo $file) {
                return str_replace(".php", "", $file->getFilename());
            });
        }

        return $interfaces;
    }

    /**
     * Get repositories path
     *
     * @return string
     */
    private function getServiceInterfacesPath(): string
    {
        return $this->app->basePath() .
            "/" . config("repository.service_directory") ;
    }

    /**
     * Get current repository implementation path
     *
     * @return string
     */
    private function getServiceCurrentImplementationPath(): string
    {
        return $this->app->basePath() .
            "/" . config("repository.service_directory");
    }

    /**
     * Get repository interface namespace
     *
     * @return string
     */
    private function getServiceInterfaceNamespace(): string
    {
        return config("repository.service_namespace");
    }

    /**
     * Get repository namespace
     *
     * @return string
     */
    private function getServiceNamespace(): string
    {
        return config("repository.service_namespace");
    }

    /**
     * Get repository file name
     *
     * @param $className
     * @return string
     */
    private function getServiceFileName($className): string
    {
        return $className . config("repository.service_suffix");
    }

    /**
     * Get repository names
     *
     * @return Collection
     */
    private function getServiceFiles(): Collection
    {
        $services = collect([]);
        if (! $this->files->isDirectory($serviceDirectory = $this->getServiceCurrentImplementationPath())) {
            return $services;
        }
        $files = $this->files->files($serviceDirectory);
        if (is_array($files)) {
            $services = collect($files)->map(function (SplFileInfo $file) {
                return str_replace(".php", "", $file->getFilename());
            });
        }

        return $services;
    }

    /**
     * Check if config is published
     *
     * @return bool
     */
    private function isConfigPublished(): bool
    {
        $path = config_path("repository.php");
        return file_exists($path);
    }
}
