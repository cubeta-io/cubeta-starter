<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    use AssistCommand;

    public $signature = 'create:service
        {name : The name of the service }';

    public $description = 'Create a new service class';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $modelName = $this->modelNaming($name);
        $this->createService($modelName);
        $this->createServiceInterface($modelName);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createService($modelName): void
    {
        $namespace = $this->getNameSpace()."\\$modelName";
        $repositoryName = $modelName.'Repository';
        $serviceName = $modelName.'Service';

        $stubProperties = [
            '{modelName}' => $modelName,
            '{repositoryName}' => $repositoryName,
        ];

        $servicePath = $this->getServicePath($modelName);
        if (file_exists($servicePath)) {
            return;
        }

        // check folder exist
        $folder = str_replace('\\', '/', $namespace);
        if (! file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }

        // create file
        new CreateFile(
            $stubProperties,
            $servicePath,
            __DIR__.'/stubs/service.stub'
        );

        $this->formatFile($servicePath);
        $this->line("<info>Created Service:</info> $serviceName");
    }

    private function getNameSpace(): string
    {
        return config('repository.service_namespace');
    }

    private function getServicePath($modelName): string
    {
        $serviceName = $modelName.'Service';

        return $this->appPath().'/'.
            config('repository.service_directory').
            "/$modelName/$serviceName".'.php';
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createServiceInterface($modelName): void
    {
        $namespace = $this->getNameSpace()."\\$modelName";

        $serviceInterfaceName = 'I'.$modelName.'Service';
        $stubProperties = [
            '{modelName}' => $modelName,
        ];

        $serviceInterfacePath = $this->getServiceInterfacePath($modelName);
        if (file_exists($serviceInterfacePath)) {
            return;
        }

        // check folder exist
        $folder = str_replace('\\', '/', $namespace);
        if (! file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }

        new CreateFile(
            $stubProperties,
            $serviceInterfacePath,
            __DIR__.'/stubs/service-interface.stub'
        );

        $this->formatFile($serviceInterfacePath);
        $this->line("<info>Created Service Interface:</info> $serviceInterfaceName");
    }

    private function getServiceInterfacePath($modelName): string
    {
        $serviceInterfaceName = 'I'.$modelName.'Service';

        return $this->appPath().'/'.
            config('repository.service_directory').
            "/$modelName/$serviceInterfaceName".'.php';
    }
}
