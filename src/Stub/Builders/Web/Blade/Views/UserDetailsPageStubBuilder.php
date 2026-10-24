<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\StubBuilder;

/**
 * @method self updateUserDataRoute(string $routeName)
 */
class UserDetailsPageStubBuilder extends StubBuilder
{
    protected function getStubPropertyArray(): array
    {
        return $this->stubProperties;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Views/UserDetails.stub');
    }
}
