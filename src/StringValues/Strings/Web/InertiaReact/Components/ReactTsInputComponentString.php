<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;

class ReactTsInputComponentString
{
    /**
     * @var array{array{key:string , value:string}}
     */
    private array $attributes = [];

    private ?string $label;

    private string $name;

    private bool $required;

    private string $tag;

    /**
     * @var TsImportString[]
     */
    public array $imports = [];

    public ?string $translationKey;

    /**
     * @param TsImportString[] $imports
     */
    public function __construct(string $tag, string $name, ?string $label = null, bool $required = false, array $attributes = [], array $imports = [], ?string $translationKey = null)
    {
        $this->tag = $tag;
        $this->name = $name;
        $this->label = $label;
        $this->required = $required;
        $this->attributes = $attributes;
        $this->imports = $imports;
        $this->translationKey = $translationKey;
    }

    public function __toString(): string
    {
        $renderedAttributes = '';

        if ($this->label || $this->translationKey) {
            $renderedAttributes .= $this->translationKey
                ? " label={t(\"{$this->translationKey}\")} "
                : " label={\"$this->label\"} ";
        }

        foreach ($this->attributes as $attribute) {
            if (! empty($attribute['value'])) {
                $renderedAttributes .= " {$attribute['key']}={{$attribute['value']}} ";
            } else {
                $renderedAttributes .= " {$attribute['key']} ";
            }
        }

        if ($this->required) {
            $renderedAttributes .= ' required ';
        }

        return "<$this->tag name=\"$this->name\" $renderedAttributes />";
    }
}
