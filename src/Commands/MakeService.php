<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeService extends Command
{
    use AssistCommand;

    public $signature = 'create:service
        {name : The name of the service }';

    public $description = 'Create a new service class';

    /**
     * Handle the command
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $modelName = ucfirst(Str::singular($name));

        $this->createService($modelName);
        $this->createServiceInterface($modelName);
    }

    /**
     * Create Service
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

        // check folder exist
        $folder = str_replace('\\', '/', $namespace);
        if (! file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }

        // create file
        new CreateFile(
            $stubProperties,
            $this->getServicePath($modelName),
            __DIR__.'/stubs/service.stub'
        );
        $this->line("<info>Created Service:</info> $serviceName");
    }

    public function createServiceInterface($modelName)
    {
        $namespace = $this->getNameSpace()."\\$modelName";

        $serviceInterfaceName = 'I'.$modelName.'Service';
        $stubProperties = [
            '{modelName}' => $modelName,
        ];

        // check folder exist
        $folder = str_replace('\\', '/', $namespace);
        if (! file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }

        new CreateFile(
            $stubProperties,
            $this->getServiceInterfacePath($modelName),
            __DIR__.'/stubs/service-interface.stub'
        );

        $this->line("<info>Created Service Interface:</info> $serviceInterfaceName");
    }

    /**
     * Get Service path
     */
    private function getServicePath($modelName): string
    {
        $serviceName = $modelName.'Service';

        return $this->appPath().'/'.
            config('repository.service_directory').
            "/$modelName/$serviceName".'.php';
    }

    /**
     * Get Service path
     */
    private function getServiceInterfacePath($modelName): string
    {
        $serviceInterfaceName = 'I'.$modelName.'Service';

        return $this->appPath().'/'.
            config('repository.service_directory').
            "/$modelName/$serviceInterfaceName".'.php';
    }

    /**
     * get namespace
     */
    private function getNameSpace(): string
    {
        return config('repository.service_namespace');
    }
}
