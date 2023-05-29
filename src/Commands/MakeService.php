<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

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
        $modelName = modelNaming($name);
        $namespace = config('repository.service_namespace')."\\$modelName";
        $this->createService($modelName, $namespace);
        $this->createServiceInterface($modelName, $namespace);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createService(string $modelName, string $namespace): void
    {
        $repositoryName = $modelName.'Repository';
        $serviceName = $modelName.'Service';

        $stubProperties = [
            '{modelName}' => $modelName,
            '{repositoryName}' => $repositoryName,
            '{namespace}' => $namespace,
        ];

        $servicePath = $this->getServicePath($serviceName, $modelName);
        if (file_exists($servicePath)) {
            $this->error("$serviceName Already Exist");

            return;
        }

        // create file
        generateFileFromStub(
            $stubProperties,
            $servicePath,
            __DIR__.'/stubs/service.stub'
        );

        $this->formatFile($servicePath);
        $this->info("Created Service: $serviceName");
    }

    private function getServicePath(string $serviceName, string $modelName): string
    {
        $directory = base_path(config('repository.service_path'))."/$modelName";
        ensureDirectoryExists($directory);

        return "$directory/$serviceName.php";
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createServiceInterface(string $modelName, string $namespace): void
    {
        $serviceInterfaceName = 'I'.$modelName.'Service';
        $stubProperties = [
            '{namespace}' => $namespace,
            '{modelName}' => $modelName,
        ];

        $serviceInterfacePath = $this->getServiceInterfacePath($serviceInterfaceName, $modelName);
        if (file_exists($serviceInterfacePath)) {
            $this->error("$serviceInterfaceName Already Exist");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $serviceInterfacePath,
            __DIR__.'/stubs/service-interface.stub'
        );

        $this->formatFile($serviceInterfacePath);
        $this->info("Created Service Interface: $serviceInterfaceName");
    }

    private function getServiceInterfacePath(string $serviceInterfaceName, string $modelName): string
    {
        $directory = base_path(config('repository.service_path'))."/$modelName";
        ensureDirectoryExists($directory);

        return "$directory/$serviceInterfaceName.php";
    }
}
