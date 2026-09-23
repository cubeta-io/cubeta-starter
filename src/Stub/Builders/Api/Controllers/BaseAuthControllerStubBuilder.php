<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self serviceNamespace(string $namespace)
 * @method self resourceNamespace(string $namespace)
 * @method self loginClass(string $class)
 * @method self registerClass(string $class)
 * @method self requestResetClass(string $class)
 * @method self resetClass(string $class)
 * @method self updateUserClass(string $class)
 * @method self checkResetClass(string $class)
 * @method self requestVar(string $variable)
 * @method self validatedCall(string $call)
 */
class BaseAuthControllerStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Controllers/BaseAuthController.stub');
    }
}
