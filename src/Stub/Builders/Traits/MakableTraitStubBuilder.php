<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Traits;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class MakableTraitStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Traits/Makable.stub');
    }
}