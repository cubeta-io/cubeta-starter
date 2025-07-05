<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\StubBuilder;

/**
 * @method self title(string $formTitle)
 * @method self submitRoute(string $submitRoute)
 * @method self method(string $method)
 * @method self updateParameters(string $updateParameters)
 * @method self localizationSelector(string $localizationSelector)
 * @method self inputs(string $inputs)
 */
class FormViewStubBuilder extends StubBuilder
{
    private array $types = [];

    public function type(string $name, string $type): static
    {
        $this->types[$name] = $type;
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/Blade/Views/Form.stub');
    }

    protected function getStubPropertyArray(): array
    {
        if (count($this->types) > 0) {
            $types = "@php";
            foreach ($this->types as $name => $type) {
                $types .= "\n /** @var $type \$$name */ \n";
            }

            $types .= "@endphp";
        } else {
            $types = "";
        }

        return [
            ...$this->stubProperties,
            '{{types}}' => $types,
        ];
    }
}