<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Traits;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self modelsNamespace(string $namespace)
 */
class HasPermissionsStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Traits/HasPermissions.stub');
    }
}