<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Components;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\TsImportString;

class ReactTsDisplayComponentString
{
    public string $tag;
    public string $label;
    public string $value;
    /**
     * @var TsImportString[]
     */
    public array $imports = [];

    /**
     * @param string           $tag
     * @param string           $label
     * @param string           $value
     * @param TsImportString[] $imports
     */
    public function __construct(string $tag, string $label, string $value, array $imports = [])
    {
        $this->tag = $tag;
        $this->label = $label;
        $this->value = $value;
        $this->imports = $imports;
    }

    public function __toString(): string
    {
        if ($this->tag == "Gallery") {
            return "<div className=\"bg-gray-50 my-2 mb-5 p-4 rounded-md font-bold text-xl dark:bg-dark dark:text-white\">
                        <label className=\"font-semibold text-lg\">{$this->label} :</label>
                        <Gallery sources={[{$this->value}]} />
                    </div>";
        }

        return "<$this->tag label=\"{$this->label}\" value={{$this->value}} />";
    }
}