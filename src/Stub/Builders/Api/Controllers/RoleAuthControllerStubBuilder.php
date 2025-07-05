<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self serviceNamespace(string $serviceNamespace)
 * @method self role(string $role)
 * @method self roleEnumName(string $roleEnumName)
 */
class RoleAuthControllerStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Controllers/RoleAuthController.stub');
    }
}