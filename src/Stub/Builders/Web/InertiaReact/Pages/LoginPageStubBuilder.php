<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;

/**
 * @method self loginRoute(string $routeName)
 * @method self passwordResetRequestPageRoute(string $routeName)
 * @method self registerPageRoute(string $routeName)
 */
class LoginPageStubBuilder extends TypescriptFileBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Pages/Login.stub');
    }
}