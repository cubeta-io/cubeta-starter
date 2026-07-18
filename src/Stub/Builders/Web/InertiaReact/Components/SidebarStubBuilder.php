<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;

/**
 * @method self indexRoute(string $routeName)
 */
class SidebarStubBuilder extends TypescriptFileBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Components/Sidebar.stub');
    }
}