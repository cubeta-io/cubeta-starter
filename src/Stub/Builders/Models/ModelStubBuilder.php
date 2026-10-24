<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Models;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;
use Cubeta\CubetaStarter\StringValues\Strings\Models\CastColumnString;

/**
 * @method self modelName(string $modelName)
 */
class ModelStubBuilder extends ClassStubBuilder
{
    private array $casts = [];

    private array $exportables = [];

    private array $fillables = [];

    private array $relationSearchables = [];

    private array $searchables = [];

    /**
     * @param string[] $attributes
     */
    private function getArrayOfAttributesString(array $attributes): string
    {
        $array = '';
        foreach ($attributes as $attribute) {
            if (! empty(trim($attribute))) {
                $array .= "'$attribute',\n";
            }
        }

        return $array;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Models/Model.stub');
    }

    public function cast(CastColumnString $cast): static
    {
        $this->casts[] = $cast;
        if ($cast->import) {
            $this->import($cast->import);
        }

        return $this;
    }

    public function exportable(string $propertyName): static
    {
        if (! in_array($propertyName, $this->exportables)) {
            $this->exportables[] = $propertyName;
        }

        return $this;
    }

    public function fillable(string $propertyName): static
    {
        if (! in_array($propertyName, $this->fillables)) {
            $this->fillables[] = $propertyName;
        }

        return $this;
    }

    public function getStubPropertyArray(): array
    {
        $relationSearchables = '';
        foreach ($this->relationSearchables as $relation => $searchables) {
            $relationSearchables .= "'$relation' => [".implode(',', array_map(fn ($item) => "'$item'", $searchables))."],\n";
        }

        return [
            ...parent::getStubPropertyArray(),
            '{{fillable}}'             => $this->getArrayOfAttributesString($this->fillables),
            '{{casts}}'                => implode(",\n", $this->casts),
            '{{exportables}}'          => $this->getArrayOfAttributesString($this->exportables),
            '{{searchable_keys}}'      => $this->getArrayOfAttributesString($this->searchables),
            '{{searchable_relations}}' => $relationSearchables,
        ];
    }

    public function relationSearchable(string $relationName, array $searchable = []): static
    {
        $this->relationSearchables[$relationName] = $searchable;

        return $this;
    }

    public function searchable(string $propertyName): static
    {
        if (! in_array($propertyName, $this->searchables)) {
            $this->searchables[] = $propertyName;
        }

        return $this;
    }
}
