<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;

class ServiceGenerator extends AbstractGenerator
{
    public static string $key = 'service';

    public function run(bool $override = false): void
    {
        $servicePath = $this->table->getServicePath();

        if ($servicePath->exist()) {
            $servicePath->logAlreadyExist("Generating Service Class For : ({$this->table->modelName}) Model");
            return;
        }

        $servicePath->ensureDirectoryExists();

        $stubProperties = [
            '{modelName}'           => $this->table->modelName,
            '{repositoryName}'      => $this->table->getRepositoryName(),
            '{namespace}'           => $this->table->getServiceNamespace(false, true),
            '{repositoryNamespace}' => $this->table->getRepositoryNameSpace(false),
            "{modelNamespace}"      => $this->table->getModelNameSpace(false),
            '{serviceNamespace}'    => config('cubeta-starter.service_namespace'),
            '{traitsNamespace}'     => config('cubeta-starter.trait_namespace'),
        ];

        $this->generateFileFromStub($stubProperties, $servicePath->fullPath);

        $servicePath->format();
    }

    protected function stubsPath(): string
    {
        return CubePath::stubPath('service.stub');
    }
}
