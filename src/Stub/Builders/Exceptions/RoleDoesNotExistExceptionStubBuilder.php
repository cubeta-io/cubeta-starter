<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Exceptions;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class RoleDoesNotExistExceptionStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Exceptions/RoleDoesNotExistException.stub');
    }
}