<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components;

class DisplayComponentString
{
    /**
     * @var array{array{key:string , value:string}}
     */
    private array $attributes = [];

    private string $tag;

    public function __construct(string $tag, array $attributes = [])
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
    }

    public function __toString(): string
    {
        if (in_array($this->tag, ['x-long-text-field', 'x-image-preview', 'x-translatable-text-editor'])) {
            $grid = 'col-sm-12 col-md-12';
        } else {
            $grid = 'col-sm-12 col-md-6';
        }

        $renderedAttributes = '';

        foreach ($this->attributes as $attribute) {
            if (! empty($attribute['value'])) {
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

    public function translationKey(string $translationKey): self
    {
        foreach ($this->attributes as &$attribute) {
            if (($attribute['key'] ?? null) === 'label') {
                $attribute['value'] = "{{ trans('site.{$translationKey}') }}";
            }
        }

        return $this;
    }
}
