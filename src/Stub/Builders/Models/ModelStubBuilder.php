<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Models;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self modelName(string $modelName)
 */
class ModelStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Models/Model.stub');
    }
}