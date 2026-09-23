<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Resources;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class PermissionResourceStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Resources/PermissionResource.stub');
    }
}
