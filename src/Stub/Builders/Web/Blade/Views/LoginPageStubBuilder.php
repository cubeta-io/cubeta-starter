<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\StubBuilder;

/**
 * @method self loginRoute(string $routeName)
 * @method self registerPageRoute(string $routeName)
 * @method self passwordResetRequestPageRoute(string $routeName)
 */
class LoginPageStubBuilder extends StubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Views/Login.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return $this->stubProperties;
    }
}