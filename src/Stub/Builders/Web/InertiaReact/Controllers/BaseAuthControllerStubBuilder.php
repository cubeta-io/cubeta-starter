<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self serviceNamespace(string $namespace)
 * @method self requestNamespace(string $namespace)
 * @method self userDetailsRoute(string $routeName)
 * @method self passwordResetPageRoute(string $routeName)
 * @method self loginPageRoute(string $routeName)
 * @method self resourceNamespace(string $namespace)
 * @method self userDetailsPageName(string $pageName)
 * * @method self resetPasswordCodeFormPageName(string $pageName)
 */
class BaseAuthControllerStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Controllers/BaseAuthController.stub');
    }
}