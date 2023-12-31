<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

class MakeService extends Command
{
    use AssistCommand;

    public $description = 'Create a new service class';

    public $signature = 'create:service
        {name : The name of the service }';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createServiceInterface(string $modelName, string $namespace): void
    {
        $serviceInterfaceName = 'I' . $modelName . 'Service';
        $stubProperties = [
            '{namespace}' => $namespace,
            '{modelName}' => $modelName,
        ];

        $serviceInterfacePath = $this->getServiceInterfacePath($serviceInterfaceName, $modelName);
        if (file_exists($serviceInterfacePath)) {
            $this->error("{$serviceInterfaceName} Already Exists");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $serviceInterfacePath,
            __DIR__ . '/stubs/service-interface.stub'
        );

        $this->formatFile($serviceInterfacePath);
        $this->info("Created Service Interface: {$serviceInterfaceName}");
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $modelName = modelNaming($modelName);

        $namespace = config('cubeta-starter.service_namespace') . "\\{$modelName}";

        if (! $modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createService($modelName, $namespace);
        $this->createServiceInterface($modelName, $namespace);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createService(string $modelName, string $namespace): void
    {
        $repositoryName = $modelName . 'Repository';
        $serviceName = $modelName . 'Service';

        $stubProperties = [
            '{modelName}' => $modelName,
            '{repositoryName}' => $repositoryName,
            '{namespace}' => $namespace,
            '{repositoryNamespace}' => config('cubeta-starter.repository_namespace')
        ];

        $servicePath = $this->getServicePath($serviceName, $modelName);
        if (file_exists($servicePath)) {
            $this->error("{$serviceName} Already Exists");

            return;
        }

        // create file
        generateFileFromStub(
            $stubProperties,
            $servicePath,
            __DIR__ . '/stubs/service.stub'
        );

        $this->formatFile($servicePath);
        $this->info("Created Service: {$serviceName}");
    }

    private function getServiceInterfacePath(string $serviceInterfaceName, string $modelName): string
    {
        $directory = base_path(config('cubeta-starter.service_path')) . "/{$modelName}";
        ensureDirectoryExists($directory);

        return "{$directory}/{$serviceInterfaceName}.php";
    }

    private function getServicePath(string $serviceName, string $modelName): string
    {
        $directory = base_path(config('cubeta-starter.service_path')) . "/{$modelName}";
        ensureDirectoryExists($directory);

        return "{$directory}/{$serviceName}.php";
    }
}
