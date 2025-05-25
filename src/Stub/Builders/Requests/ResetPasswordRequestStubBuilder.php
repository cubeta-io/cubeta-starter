<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Requests;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class ResetPasswordRequestStubBuilder extends ClassStubBuilder
{

    protected function stubPath(): string
    {
        return CubePath::stubPath('Requests/ResetPasswordRequest.stub');
    }
}