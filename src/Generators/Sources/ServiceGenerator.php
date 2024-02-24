<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Error;
use Throwable;

class ServiceGenerator extends AbstractGenerator
{
    public static string $key = 'service';
    public static string $configPath = 'cubeta-starter.service_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->modelName($this->fileName);
        $repositoryName = $modelName . "Repository";
        $serviceName = $this->generatedFileName();

        $servicePath = $this->getGeneratingPath($serviceName);

        throw_if(file_exists($servicePath), new Error("{$serviceName} Already Exists"));

        $stubProperties = [
            '{modelName}' => $modelName,
            '{repositoryName}' => $repositoryName,
            '{namespace}' => config('cubeta-starter.service_namespace') . "\\{$modelName}",
            '{repositoryNamespace}' => config('cubeta-starter.repository_namespace')
        ];

        $this->generateFileFromStub($stubProperties, $servicePath);

        $this->formatFile($servicePath);

        $serviceInterfaceName = "I" . $serviceName;
        $serviceInterfacePath = $this->getGeneratingPath($serviceInterfaceName);

        throw_if(file_exists($serviceInterfacePath), new Error("{$serviceInterfaceName} Already Exists"));

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.service_namespace') . "\\{$modelName}",
            '{modelName}' => $modelName,
        ];

        $this->generateFileFromStub($stubProperties, $serviceInterfacePath, otherStubsPath: $this->serviceInterfaceStubs());

        $this->formatFile($serviceInterfacePath);
    }

    protected function getAdditionalPath(): string
    {
        return "/" . $this->modelName($this->fileName);
    }

    public function generatedFileName(): string
    {
        return $this->modelName($this->fileName) . 'Service';
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/service.stub';
    }

    protected function serviceInterfaceStubs(): string
    {
        return __DIR__ . '/stubs/service-interface.stub';
    }
}