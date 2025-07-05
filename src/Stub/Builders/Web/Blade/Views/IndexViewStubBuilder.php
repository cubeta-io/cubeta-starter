<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\StubBuilder;

/**
 * @method self tableName(string $tableName)
 * @method self exportRoute(string $exportRoute)
 * @method self modelClassString (string $modelClassString)
 * @method self importRoute(string $importRoute)
 * @method self exampleRoute(string $exampleRoute)
 * @method self htmlColumns(string $columns)
 * @method self createRoute(string $createRoute)
 * @method self dataRoute(string $dataRoute)
 * @method self dataTableObjectColumns(string $columns)
 */
class IndexViewStubBuilder extends StubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Views/Index.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return $this->stubProperties;
    }
}