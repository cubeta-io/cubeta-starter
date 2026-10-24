<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Resources;

use Illuminate\Support\Arr;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;
use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;

/**
 * @method self modelNamespace(string $modelNamespace)
 * @method self modelName(string $modelName)
 */
class ResourceStubBuilder extends ClassStubBuilder
{
    /** @var ResourcePropertyString[] */
    private array $resourceFields = [];

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{resource_fields}}' => implode(",\n", $this->resourceFields),
        ];
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Resources/Resource.stub');
    }

    public function resourceField(array|ResourcePropertyString $resourceField): static
    {
        /** @var ResourcePropertyString[] $resourceField */
        $resourceField = Arr::wrap($resourceField);
        foreach ($resourceField as $item) {
            $this->import($item->imports);
        }

        $this->resourceFields = array_merge($resourceField, $this->resourceFields);

        return $this;
    }
}
