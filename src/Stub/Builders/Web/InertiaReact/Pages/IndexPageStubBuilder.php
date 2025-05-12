<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;

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

    public function column(DataTableColumnObjectString $column)
    {
        $this->columns[] = $column;
        $this->import($column->imports);
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Pages/Index.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            "{{columns}}" => implode(",\n", $this->columns) . ",",
        ];
    }
}