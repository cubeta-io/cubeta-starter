<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Services;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self modelNamespace(string $modelNamespace)
 * @method self serviceNamespace(string $serviceNamespace)
 * @method self repositoryNamespace(string $repositoryNamespace)
 * @method self traitsNamespace(string $traitsNamespace)
 * @method self modelName(string $modelName)
 */
class ServiceStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Services/Service.stub');
    }
}