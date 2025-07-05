<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Tests;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self traitsNamespace(string $namespace)
 * @method self modelsNamespace(string $namespace)
 */
class MainTestCaseStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Tests/MainTestCase.stub');
    }
}