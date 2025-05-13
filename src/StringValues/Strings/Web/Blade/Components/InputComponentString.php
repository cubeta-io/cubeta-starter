<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components;

class InputComponentString
{
    private string $type;
    private string $tag;
    private string $name;
    private bool $isRequired = false;
    private ?string $label = null;

    /**
     * @var array{array{key:string , value:string}}
     */
    private array $attributes = [];

    /**
     * @param string                                  $type
     * @param string                                  $tag
     * @param string                                  $name
     * @param bool                                    $isRequired
     * @param string|null                             $label
     * @param array{array{key:string , value:string}} $attributes
     */
    public function __construct(string $type, string $tag, string $name, bool $isRequired = false, ?string $label = null, array $attributes = [])
    {
        $this->type = $type;
        $this->tag = $tag;
        $this->name = $name;
        $this->label = $label;
        $this->attributes = $attributes;
        $this->isRequired = $isRequired;
    }


    public function __toString(): string
    {
        if (in_array($this->tag, ["x-translatable-text-editor", "x-text-editor"])) {
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

        if ($this->isRequired) {
            $renderedAttributes .= " required ";
        }

        if ($this->type == "radio"){
            return "<div class=\"col-sm-12 col-md-6\">
                        <label class=\"form-label\">{$this->label}</label>
                        <div class=\"d-flex gap-5\">
                            <x-form-check-radio name=\"{$this->name}\" :value=\"false\" $renderedAttributes/>
                            <x-form-check-radio name=\"{$this->name}\" :value=\"true\" $renderedAttributes/>
                        </div>
                    </div>";
        }

        return "<div class=\"$grid\">
                    <{$this->tag} 
                        type=\"{$this->type}\" 
                        name=\"{$this->name}\"
                        label=\"{$this->label}\"
                        $renderedAttributes
                    />
                </div>";
    }
}