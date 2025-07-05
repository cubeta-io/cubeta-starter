<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self requestNamespace(string $namespace)
 * @method self serviceNamespace(string $namespace)
 * @method self resourceNamespace(string $namespace)
 */
class BaseAuthControllerStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Controllers/BaseAuthController.stub');
    }
}