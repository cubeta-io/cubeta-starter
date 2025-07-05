<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\StubBuilder;

/**
 * @method self registerRoute(string $routeName)
 * @method self loginPageRoute(string $routeName)
 */
class RegisterPageStubBuilder extends StubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Views/Register.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return $this->stubProperties;
    }
}