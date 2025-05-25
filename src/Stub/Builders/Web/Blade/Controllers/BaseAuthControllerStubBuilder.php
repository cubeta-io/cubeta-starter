<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self serviceNamespace(string $namespace)
 * @method self requestNamespace(string $namespace)
 * @method self userDetailsRoute(string $routeName)
 * @method self passwordResetPageRoute(string $routeName)
 * @method self loginPageRoute(string $routeName)
 * @method self userDetailsPageName(string $viewName)
 * @method self resetPasswordCodeFormPageName(string $viewName)
 */
class BaseAuthControllerStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Controllers/BaseAuthController.stub');
    }
}