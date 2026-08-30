<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Dtos;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\StringValues\Strings\Dtos\DtoCastString;
use Cubeta\CubetaStarter\StringValues\Strings\Dtos\DtoPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;
use Illuminate\Support\Arr;

/**
 * @method self modelName(string $modelName)
 */
class DtoStubBuilder extends ClassStubBuilder
{
    private array $rules = [];

    private array $casts = [];

    private array $dtoProperties = [];

    /**
     * @param  PropertyValidationRuleString[]|PropertyValidationRuleString  $rule
     * @return $this
     */
    public function rule(array|PropertyValidationRuleString $rule): static
    {
        /** @var PropertyValidationRuleString[] $rule */
        $rule = Arr::wrap($rule);
        foreach ($rule as $item) {
            if ($item->imports) {
                $this->import($item->imports);
            }
        }

        $this->rules = array_merge($rule, $this->rules);

        return $this;
    }

    /**
     * @param  DtoCastString[]|DtoCastString  $cast
     * @return $this
     */
    public function cast(array|DtoCastString $cast): static
    {
        /** @var DtoCastString[] $cast */
        $cast = Arr::wrap($cast);
        foreach ($cast as $item) {
            if ($item->imports) {
                $this->import($item->imports);
            }
        }

        $this->casts = array_merge($cast, $this->casts);

        return $this;
    }

    /**
     * @param  DtoPropertyString[]|DtoPropertyString  $property
     * @return $this
     */
    public function dtoProperty(array|DtoPropertyString $property): static
    {
        /** @var DtoPropertyString[] $property */
        $property = Arr::wrap($property);
        foreach ($property as $item) {
            if ($item->imports) {
                $this->import($item->imports);
            }
        }

        $this->dtoProperties = array_merge($property, $this->dtoProperties);

        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Dtos/Dto.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{properties}}' => implode("\n\n    ", $this->dtoProperties),
            '{{rules}}' => implode(",\n            ", $this->rules),
            '{{casts}}' => implode(",\n            ", $this->casts),
        ];
    }
}
