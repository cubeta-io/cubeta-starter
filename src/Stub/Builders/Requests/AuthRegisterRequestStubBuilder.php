<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Requests;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class AuthRegisterRequestStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Requests/AuthRegisterRequest.stub');
    }
}