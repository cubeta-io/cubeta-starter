<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Requests;

use Illuminate\Support\Arr;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;

/**
 * @method self modelName(string $modelName)
 */
class RequestStubBuilder extends ClassStubBuilder
{
    private array $rules = [];

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{rules}}' => implode(",\n", $this->rules),
        ];
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Requests/Request.stub');
    }

    /**
     * @param  PropertyValidationRuleString[]|PropertyValidationRuleString $rule
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
}
