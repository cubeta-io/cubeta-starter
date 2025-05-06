<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\StubBuilder;

/**
 * @method self modelClassString(string $className)
 * @method self modelVariable(string $modelVariable)
 * @method self modelName(string $name)
 * @method self titleable(string $titleable)
 * @method self editRoute(string $editRouteName)
 * @method self components(string $components)
 */
class ShowViewStubBuilder extends StubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Views/Show.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return $this->stubProperties;
    }
}