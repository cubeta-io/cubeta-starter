<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeService extends Command
{
    use AssistCommand;

    public $signature = 'create:service
        {name : The name of the service }';

    public $description = 'Create a new service class';

    public function handle()
    {
        $name = str_replace(config('repository.service_suffix'), '', $this->argument('name'));
        $className = Str::studly($name);

        $this->checkIfRequiredDirectoriesExist();

        $this->createService($className);
    }

    /**
     * Create the service
     *
     * @return void
     */
    public function createService(string $className)
    {
        $nameOfService = $this->getServiceName($className);
        $serviceName = $nameOfService.config('repository.service_suffix');
        $namespace = $this->getNameSpace($className);
        $stubProperties = [
            '{namespace}' => $namespace,
            '{serviceName}' => $serviceName,
            '{repositoryInterface}' => $this->getRepositoryInterfaceName($nameOfService),
            '{repositoryInterfaceNamespace}' => $this->getRepositoryInterfaceNamespace($nameOfService),
        ];
        // check folder exist
        $folder = str_replace('\\', '/', $namespace);
        if (! file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }
        // create file
        new CreateFile(
            $stubProperties,
            $this->getServicePath($className),
            __DIR__.'/stubs/service.stub'
        );
        $this->line("<info>Created service:</info> {$serviceName}");
    }

    /**
     * Get service path
     *
     * @return string
     */
    private function getServicePath($className)
    {
        return $this->appPath().'/'.
            config('repository.service_directory').
            "/$className".'Service.php';
    }

    /**
     * Get repository interface namespace
     *
     * @return string
     */
    private function getRepositoryInterfaceNamespace(string $className)
    {
        return config('repository.repository_namespace')."\Interfaces";
    }

    /**
     * Get repository interface name
     *
     * @return string
     */
    private function getRepositoryInterfaceName(string $className)
    {
        return $className.'RepositoryInterface';
    }

    /**
     * Check to make sure if all required directories are available
     *
     * @return void
     */
    private function checkIfRequiredDirectoriesExist()
    {
        $this->ensureDirectoryExists(config('repository.service_directory'));
    }

    /**
     * get service name
     */
    private function getServiceName($className): string
    {
        $explode = explode('/', $className);

        return $explode[array_key_last($explode)];
    }

    /**
     * get namespace
     */
    private function getNameSpace($className): string
    {
        $explode = explode('/', $className);
        if (count($explode) > 1) {
            $namespace = '';
            for ($i = 0; $i < count($explode) - 1; $i++) {
                $namespace .= '\\'.$explode[$i];
            }

            return config('repository.service_namespace').$namespace;
        } else {
            return config('repository.service_namespace');
        }
    }
}
