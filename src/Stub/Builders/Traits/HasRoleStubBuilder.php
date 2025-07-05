<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Traits;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self exceptionsNamespace(string $namespace)
 * @method self modelsNamespace(string $namespace)
 */
class HasRoleStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Traits/HasRoles.stub');
    }
}