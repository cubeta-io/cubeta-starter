<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self serviceNamespace(string $namespace)
 * @method self userDetailsRoute(string $routeName)
 * @method self passwordResetPageRoute(string $routeName)
 * @method self loginPageRoute(string $routeName)
 * @method self resourceNamespace(string $namespace)
 * @method self userDetailsPageName(string $pageName)
 * @method self resetPasswordCodeFormPageName(string $pageName)
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
        return CubePath::stubPath('Web/InertiaReact/Controllers/BaseAuthController.stub');
    }
}
