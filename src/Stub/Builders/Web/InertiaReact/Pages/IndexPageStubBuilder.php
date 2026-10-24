<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;

/**
 * @method self modelName(string $name)
 * @method self createRoute(string $createRouteName)
 * @method self importRoute(string $importRouteName)
 * @method self exportRoute(string $exportRouteName)
 * @method self importExampleRoute(string $importExampleRouteName)
 * @method self dataRoute(string $dataRouteName)
 * @method self modelVariable(string $modelVariable)
 * @method self indexRoute(string $indexRouteName)
 */
class IndexPageStubBuilder extends TypescriptFileBuilder
{
    /**
     * @var DataTableColumnObjectString[]
     */
    private array $columns = [];

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{columns}}' => implode(",\n", $this->columns).',',
        ];
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Pages/Index.stub');
    }

    public function column(DataTableColumnObjectString $column)
    {
        $this->columns[] = $column;
        $this->import($column->imports);

        return $this;
    }
}
