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

    /**
     * @param string $tag
     * @param string $label
     * @param string $value
     * @param TsImportString[] $imports
     * @param array $attributes
     */
    public function __construct(
        string $tag,
        string $label,
        string $value,
        array  $imports = [],
        array  $attributes = []
    )
    {
        $this->tag = $tag;
        $this->label = $label;
        $this->value = $value;
        $this->imports = $imports;
        $this->attributes = $attributes;
    }

    public function __toString(): string
    {
        $attributesString = "";
        foreach ($this->attributes as $attribute => $value) {
            $attributesString .= " " . "$attribute={$value}";
        }
        return "<$this->tag label=\"{$this->label}\" value={{$this->value}} $attributesString />";
    }
}