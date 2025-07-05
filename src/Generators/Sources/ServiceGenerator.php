<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Stub\Builders\Services\ServiceStubBuilder;

class ServiceGenerator extends AbstractGenerator
{
    public static string $key = 'service';

    public function run(bool $override = false): void
    {
        $servicePath = $this->table->getServicePath();

        ServiceStubBuilder::make()
            ->namespace($this->table->getServiceNamespace(false, true))
            ->modelNamespace($this->table->getModelNameSpace(false))
            ->serviceNamespace(config('cubeta-starter.service_namespace'))
            ->repositoryNamespace($this->table->getRepositoryNameSpace(false))
            ->traitsNamespace(config('cubeta-starter.trait_namespace'))
            ->modelName($this->table->modelNaming())
            ->generate($servicePath, $override);
    }
}
