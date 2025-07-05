<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Components;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\StubBuilder;

/**
 * @method self indexRoute(string $routeName)
 */
class SidebarStubBuilder extends StubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Components/Sidebar.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return $this->stubProperties;
    }
}