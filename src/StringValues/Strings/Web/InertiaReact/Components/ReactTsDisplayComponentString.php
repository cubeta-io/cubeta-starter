<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;

class ReactTsDisplayComponentString
{
    public string $tag;

    public string $label;

    public string $value;

    public array $attributes;

    /**
     * @var TsImportString[]
     */
    public array $imports = [];

    public ?string $translationKey;

    /**
     * @param  TsImportString[]  $imports
     */
    public function __construct(
        string $tag,
        string $label,
        string $value,
        array $imports = [],
        array $attributes = [],
        ?string $translationKey = null
    ) {
        $this->tag = $tag;
        $this->label = $label;
        $this->value = $value;
        $this->imports = $imports;
        $this->attributes = $attributes;
        $this->translationKey = $translationKey;
    }

    public function __toString(): string
    {
        $attributesString = '';
        foreach ($this->attributes as $attribute => $value) {
            $attributesString .= ' '."$attribute={$value}";
        }
        $label = $this->translationKey ? "{t(\"{$this->translationKey}\")}" : "\"{$this->label}\"";

        return "<$this->tag label={$label} value={{$this->value}} $attributesString />";
    }
}
