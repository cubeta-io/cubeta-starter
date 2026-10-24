<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript;

use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;

class DataTableColumnObjectString
{
    /** @var TsImportString[] */
    public array $imports = [];

    public ?string $label = null;

    public ?string $name = null;

    public ?string $render = null;

    public bool $sortable = false;

    public bool $translatable = false;

    public ?string $translationKey;

    /**
     * @param TsImportString[] $imports
     */
    public function __construct(?string $name, ?string $label, bool $translatable = false, bool $sortable = true, ?string $render = null, array $imports = [], ?string $translationKey = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->translatable = $translatable;
        $this->sortable = $sortable;
        $this->render = $render;
        $this->imports = $imports;
        $this->translationKey = $translationKey;
    }

    public function __toString(): string
    {
        $object = '{';
        if ($this->name) {
            $object .= "name: \"$this->name\" ,";
        }

        if ($this->label) {
            $object .= $this->translationKey
                ? "label: t(\"{$this->translationKey}\") ,"
                : "label: \"$this->label\" ,";
        }

        if ($this->translatable) {
            $object .= 'translatable:true,';
        }

        if ($this->sortable) {
            $object .= 'sortable:true,';
        }

        if ($this->render) {
            $object .= "render: (cell, record, setHidden, revalidate) => {{$this->render}},";
        }

        $object .= '}';

        return $object;
    }
}
