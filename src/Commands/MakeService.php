<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
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
        $namespace = config('repository.service_namespace') . "\\$modelName";
        $this->createService($modelName, $namespace);
        $this->createServiceInterface($modelName, $namespace);
    }

    /**
     * @param string $modelName
     * @param string $namespace
     * @return void
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
            "{namespace}" => $namespace
        ];

        $servicePath = $this->getServicePath($serviceName);
        if (file_exists($servicePath)) {
            $this->line("<info>$serviceName Already Exist</info>");
            return;
        }

        // create file
        new CreateFile(
            $stubProperties,
            $servicePath,
            __DIR__ . '/stubs/service.stub'
        );

        $this->formatFile($servicePath);
        $this->line("<info>Created Service:</info> $serviceName");
    }

    private function getServicePath($serviceName): string
    {
        $directory = base_path(config('repository.service_path'));
        $this->ensureDirectoryExists($directory);
        return "$directory/$serviceName.php";
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createServiceInterface(string $modelName, string $namespace): void
    {
        $serviceInterfaceName = 'I' . $modelName . 'Service';
        $stubProperties = [
            "{namespace}" => $namespace,
            '{modelName}' => $modelName,
        ];

        $serviceInterfacePath = $this->getServiceInterfacePath($modelName);
        if (file_exists($serviceInterfacePath)) {
            return;
        }

        new CreateFile(
            $stubProperties,
            $serviceInterfacePath,
            __DIR__ . '/stubs/service-interface.stub'
        );

        $this->formatFile($serviceInterfacePath);
        $this->line("<info>Created Service Interface:</info> $serviceInterfaceName");
    }

    private function getServiceInterfacePath($serviceInterfaceName): string
    {
        $directory = base_path(config('repository.service_path'));
        $this->ensureDirectoryExists($directory);
        return "$directory/$serviceInterfaceName.php";
    }
}
