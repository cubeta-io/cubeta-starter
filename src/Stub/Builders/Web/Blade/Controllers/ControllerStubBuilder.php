<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Controllers;

use Illuminate\Support\Arr;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self requestNamespace(string $requestNamespace)
 * @method self requestClass(string $requestClass)
 * @method self requestVariable(string $requestVariable)
 * @method self validatedData(string $validatedData)
 * @method self modelNamespace(string $modelNamespace)
 * @method self serviceNamespace(string $serviceNamespace)
 * @method self traitsNamespace(string $traitsNamespace)
 * @method self modelName(string $modelName)
 * @method self modelNameCamelCase(string $modelNameCamelCase)
 * @method self tableName(string $tableName)
 * @method self baseRouteName(string $baseRouteName)
 * @method self translatableOrderQueries(string $translatableOrderQueries)
 * @method self rawColumns(string $rawColumns)
 * @method self indexView(string $indexView)
 * @method self createView(string $createView)
 * @method self idVariable(string $idVariable)
 * @method self showView(string $showView)
 * @method self indexRoute(string $indexRoute)
 * @method self updateView(string $updateView)
 * @method self loadedRelations(string $loadedRelations)
 */
class ControllerStubBuilder extends ClassStubBuilder
{
    private array $additionalColumns = [];

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{additional_columns}}' => implode("\n", $this->additionalColumns),
        ];
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Controllers/Controller.stub');
    }

    /**
     * @param string|string[] $additionalColumn
     */
    public function additionalColumn(string|array $additionalColumn): static
    {
        $additionalColumn = Arr::wrap($additionalColumn);
        $this->additionalColumns = array_merge($additionalColumn, $this->additionalColumns);

        return $this;
    }
}
