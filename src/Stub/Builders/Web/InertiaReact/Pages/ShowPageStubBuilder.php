<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;

/**
 * @method self modelName(string $name)
 * @method self modelVariable(string $variable)
 * @method self editRouteName(string $routeName)
 */
class ShowPageStubBuilder extends TypescriptFileBuilder
{
    /**
     * @var ReactTsDisplayComponentString[]
     */
    private array $smallFields = [];
    /**
     * @var ReactTsDisplayComponentString[]
     */
    private array $bigFields = [];

    public function smallField(ReactTsDisplayComponentString $field): static
    {
        $this->smallFields[] = $field;
        if (count($field->imports) > 0) {
            $this->import($field->imports);
        }
        return $this;
    }

    public function bigField(ReactTsDisplayComponentString $field): static
    {
        $this->bigFields[] = $field;
        if (count($field->imports) > 0) {
            $this->import($field->imports);
        }
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('/Web/InertiaReact/Pages/Show.stub');
    }

    protected function getStubPropertyArray(): array
    {
        return [
            ...parent::getStubPropertyArray(),
            '{{small_fields}}' => implode("\n", $this->smallFields),
            "{{big_fields}}" => implode("\n", $this->bigFields),
        ];
    }
}