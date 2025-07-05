<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Traits;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self traitsNamespace(string $namespace)
 * @method self resourcesNamespace(string $namespace)
 * @method self modelsNamespace(string $namespace)
 */
class TestHelpersStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Traits/TestHelpers.stub');
    }
}