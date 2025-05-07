<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components;

class DisplayComponentString
{
    private string $tag;

    /**
     * @var array{array{key:string , value:string}}
     */
    private array $attributes = [];

    public function __construct(string $tag, array $attributes = [])
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
    }

    public function __toString(): string
    {
        if (in_array($this->tag, ["x-long-text-field", "x-image-preview", "x-translatable-text-editor"])) {
            $grid = "col-sm-12 col-md-12";
        } else {
            $grid = "col-sm-12 col-md-6";
        }

        $renderedAttributes = "";

        foreach ($this->attributes as $attribute) {
            if (!empty($attribute['value'])) {
                $renderedAttributes .= " {$attribute['key']}=\"{$attribute['value']}\" ";
            } else {
                $renderedAttributes .= " {$attribute['key']} ";
            }
        }

        return "<div class=\"$grid\">
                    <{$this->tag} 
                        $renderedAttributes
                    />
                </div>";
    }
}