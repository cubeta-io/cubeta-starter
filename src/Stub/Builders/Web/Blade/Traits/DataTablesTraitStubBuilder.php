<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Traits;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

class DataTablesTraitStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Traits/DataTablesTrait.stub');
    }
}