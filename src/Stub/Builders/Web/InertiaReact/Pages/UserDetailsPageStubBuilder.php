<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;

/**
 * @method self updateUserDataRoute(string $routeName)
 */
class UserDetailsPageStubBuilder extends TypescriptFileBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Pages/UserDetails.stub');
    }
}