<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\Stub\Contracts\TypescriptFileBuilder;
use Illuminate\Support\Stringable;

/**
 * @method self componentName(string $componentName)
 * @method self componentProps(string $componentProps)
 * @method self setPut(string $setPut)
 * @method self action (string $action)
 * @method self formTitle (string $formTitle)
 * @method self translatableContextOpenTag(string $translatableContextOpenTag)
 * @method self translatableContextCloseTag(string $translatableContextCloseTag)
 */
class FormPageStubBuilder extends TypescriptFileBuilder
{
    /**
     * @var ReactTsInputComponentString[]
     */
    private array $smallFields = [];

    /**
     * @var ReactTsInputComponentString[]
     */
    private array $bigFields = [];

    /**
     * @var string[]
     */
    private array $defaultValues = [];

    /**
     * @var InterfacePropertyString[]
     */
    private array $formFieldsInterface = [];

    public function init(): void
    {
        $this->stubProperties = [];
        $this->formFieldsInterface = [];
        $this->bigFields = [];
        $this->smallFields = [];
        $this->defaultValues = [];
    }

    public function defaultValue(string $key, string $value): static
    {
        $this->defaultValues[] = "$key:$value";
        return $this;
    }

    public function formFieldInterface(InterfacePropertyString $property): static
    {
        $this->formFieldsInterface[] = $property;
        if ($property->import) {
            $this->import($property->import);
        }
        $this->formFieldsInterface = collect($this->formFieldsInterface)
            ->unique(fn(InterfacePropertyString $item) => $item->name)
            ->toArray();
        return $this;
    }

    /**
     * @param ReactTsInputComponentString $field
     * @return $this
     */
    public function smallField(ReactTsInputComponentString $field): static
    {
        $this->smallFields[] = $field;
        if (count($field->imports) > 0) {
            $this->import($field->imports);
        }

        return $this;
    }

    public function bigField(ReactTsInputComponentString $field): static
    {
        $this->bigFields[] = $field;
        if (count($field->imports) > 0) {
            $this->import($field->imports);
        }
        return $this;
    }

    protected function stubPath(): string
    {
        return CubePath::stubPath('Web/InertiaReact/Pages/Form.stub');
    }

    protected function getStubPropertyArray(): array
    {
        $fields = implode("\n", $this->smallFields) .
            "\n" .
            implode(
                "\n",
                array_map(fn($field) => "<div className=\"md:col-span-2\">$field</div>", $this->bigFields)
            );

        return [
            ...parent::getStubPropertyArray(),
            "{{default_values}}" => str(implode("\n,", $this->defaultValues))->when(
                fn(Stringable $str) => !$str->isEmpty(),
                fn(Stringable $str) => $str->wrap("{", "}")
            ),
            "{{fields}}" => $fields,
            '{{form_field_interface}}' => str(implode("\n", $this->formFieldsInterface))
                ->replaceMatches('/Media\s*\|/', 'File|'),
        ];
    }
}