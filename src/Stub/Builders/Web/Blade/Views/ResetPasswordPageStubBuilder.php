<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\StubBuilder;

/**
 * @method self passwordResetRoute(string $routeName)
 */
class ResetPasswordPageStubBuilder extends StubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Views/ResetPassword.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return $this->stubProperties;
    }
}