<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Factories;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self modelsNamespace(string $namespace)
 */
class UserFactoryStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Factories/UserFactory.stub');
    }
}