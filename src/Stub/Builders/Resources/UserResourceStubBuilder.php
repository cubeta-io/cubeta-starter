<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Resources;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self modelNamespace(string $namespace)
 */
class UserResourceStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Resources/UserResource.stub');
    }
}