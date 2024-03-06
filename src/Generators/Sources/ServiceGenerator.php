<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;

class ServiceGenerator extends AbstractGenerator
{
    public static string $key = 'service';
    public static string $configPath = 'cubeta-starter.service_path';

    public function run(bool $override = false): void
    {
        $servicePath = $this->table->getServicePath();

        if ($servicePath->exist()) {
            $servicePath->logAlreadyExist("Generating Service Class For : ({$this->table->modelName}) Model");
        }

        $stubProperties = [
            '{modelName}' => $this->table->modelName,
            '{repositoryName}' => $this->table->getRepositoryName(),
            '{namespace}' => config('cubeta-starter.service_namespace') . "\\{$this->table->modelName}",
            '{repositoryNamespace}' => config('cubeta-starter.repository_namespace')
        ];

        $servicePath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $servicePath->fullPath);

        $servicePath->format();

        $serviceInterfacePath = $this->table->getServiceInterfacePath();

        if ($serviceInterfacePath->exist()) {
            $serviceInterfacePath->logAlreadyExist("Generating Service Interface For ({$this->table->modelName}) Model");
        }

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.service_namespace') . "\\{$this->table->modelName}",
            '{modelName}' => $this->table->modelName,
        ];

        $serviceInterfacePath->ensureDirectoryExists();

        $this->generateFileFromStub($stubProperties, $serviceInterfacePath->fullPath, otherStubsPath: $this->serviceInterfaceStubs());

        $serviceInterfacePath->format();
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/service.stub';
    }

    protected function serviceInterfaceStubs(): string
    {
        return __DIR__ . '/../../stubs/service-interface.stub';
    }
}
