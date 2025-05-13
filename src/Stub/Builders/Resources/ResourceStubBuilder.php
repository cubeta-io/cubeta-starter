<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Resources;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;
use Illuminate\Support\Arr;

/**
 * @method self modelNamespace(string $modelNamespace)
 * @method self modelName(string $modelName)
 */
class ResourceStubBuilder extends ClassStubBuilder
{
    /** @var ResourcePropertyString[] */
    private array $resourceFields = [];

    public function resourceField(array|ResourcePropertyString $resourceField): static
    {
        /** @var PropertyValidationRuleString[] $resourceField */
        $resourceField = Arr::wrap($resourceField);
        foreach ($resourceField as $item) {
            if ($item->imports) {
                $this->import($item->imports);
            }
        }

        $this->resourceFields = array_merge($resourceField, $this->resourceFields);
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Resources/Resource.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{resource_fields}}' => implode(",\n", $this->resourceFields),
        ];
    }
}