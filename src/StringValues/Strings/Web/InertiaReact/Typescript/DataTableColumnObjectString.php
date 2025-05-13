<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript;

use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;

class DataTableColumnObjectString
{
    public ?string $name = null;
    public ?string $label = null;
    public bool $translatable = false;
    public bool $sortable = false;
    public ?string $render = null;

    /** @var TsImportString[] */
    public array $imports = [];

    /**
     * @param string|null      $name
     * @param string|null      $label
     * @param bool             $translatable
     * @param bool             $sortable
     * @param string|null      $render
     * @param TsImportString[] $imports
     */
    public function __construct(?string $name, ?string $label, bool $translatable = false, bool $sortable = true, ?string $render = null, array $imports = [])
    {
        $this->name = $name;
        $this->label = $label;
        $this->translatable = $translatable;
        $this->sortable = $sortable;
        $this->render = $render;
        $this->imports = $imports;
    }

    public function __toString(): string
    {
        $object = "{";
        if ($this->name) {
            $object .= "name: \"$this->name\" ,";
        }

        if ($this->label) {
            $object .= "label: \"$this->label\" ,";
        }

        if ($this->translatable) {
            $object .= "translatable:true,";
        }

        if ($this->sortable) {
            $object .= "sortable:true,";
        }

        if ($this->render) {
            $object .= "render: (cell, record, setHidden, revalidate) => {{$this->render}},";
        }

        $object .= "}";

        return $object;
    }
}