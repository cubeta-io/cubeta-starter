<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Models;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class ModelHasPermissionModelStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Models/ModelHasPermissionModel.stub');
    }
}