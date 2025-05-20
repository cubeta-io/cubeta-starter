<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Services;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self repositoryNamespace(string $namespace)
 */
class BaseServiceStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Services/BaseService.stub');
    }
}