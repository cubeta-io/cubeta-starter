<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Typescript;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;

/**
 * @method self modelName(string $modelName)
 */
class TsInterfaceStubBuilder extends TypescriptFileBuilder
{
    /**
     * @var InterfacePropertyString[]
     */
    private array $properties = [];

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{properties}}' => implode("\n", $this->properties),
        ];
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Typescript/Interface.stub');
    }

    public function property(InterfacePropertyString $property): static
    {
        $this->properties[] = $property;
        if ($property->import) {
            $this->import($property->import);
        }
        $this->properties = collect($this->properties)->unique(fn (InterfacePropertyString $item) => $item->name)->toArray();

        return $this;
    }
}
