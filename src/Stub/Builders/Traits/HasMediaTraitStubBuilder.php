<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Traits;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class HasMediaTraitStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Traits/HasMedia.stub');
    }
}