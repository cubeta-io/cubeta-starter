<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\TsImportString;

class ReactTsInputComponentString
{
    private string $tag;
    private string $name;
    private ?string $label;
    private bool $required;
    /**
     * @var array{array{key:string , value:string}}
     */
    private array $attributes = [];

    /**
     * @var TsImportString[]
     */
    public array $imports = [];

    /**
     * @param string           $tag
     * @param string           $name
     * @param string|null      $label
     * @param bool             $required
     * @param array            $attributes
     * @param TsImportString[] $imports
     */
    public function __construct(string $tag, string $name, ?string $label = null, bool $required = false, array $attributes = [], array $imports = [])
    {
        $this->tag = $tag;
        $this->name = $name;
        $this->label = $label;
        $this->required = $required;
        $this->attributes = $attributes;
        $this->imports = $imports;
    }

    public function __toString(): string
    {
        $renderedAttributes = "";

        if ($this->label) {
            $renderedAttributes .= " label={\"$this->label\"} ";
        }

        foreach ($this->attributes as $attribute) {
            if (!empty($attribute['value'])) {
                $renderedAttributes .= " {$attribute['key']}={{$attribute['value']}} ";
            } else {
                $renderedAttributes .= " {$attribute['key']} ";
            }
        }

        if ($this->required) {
            $renderedAttributes .= " required ";
        }

        return "<$this->tag name=\"$this->name\" $renderedAttributes />";
    }
}
