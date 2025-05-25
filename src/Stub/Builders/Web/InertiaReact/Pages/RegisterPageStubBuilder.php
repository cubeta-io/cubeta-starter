<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;

/**
 * @method self registerRoute(string $routeName)
 * @method self loginPageRoute(string $routeName)
 */
class RegisterPageStubBuilder extends TypescriptFileBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Pages/Register.stub');
    }
}