<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Models;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class ModelHasRoleModelStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Models/ModelHasRoleModel.stub');
    }
}