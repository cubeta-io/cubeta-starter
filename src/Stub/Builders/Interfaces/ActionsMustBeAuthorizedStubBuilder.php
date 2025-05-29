<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Interfaces;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class ActionsMustBeAuthorizedStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Interfaces/ActionsMustBeAuthorized.stub');
    }
}