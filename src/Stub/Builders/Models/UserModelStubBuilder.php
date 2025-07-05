<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Models;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self implementsJwtInterface(string $implementation)
 */
class UserModelStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Models/UserModel.stub');
    }
}