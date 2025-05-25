<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Services;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self repositoryNamespace(string $namespace)
 * @method self serviceNamespace(string $namespace)
 * @method self modelNamespace(string $namespace)
 * @method self traitsNamespace(string $namespace)
 */
class UserServiceStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Services/UserService.stub');
    }
}