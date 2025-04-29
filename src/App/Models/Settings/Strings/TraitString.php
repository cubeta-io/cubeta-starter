<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

class TraitString
{
    public ?ImportString $import;
    public string $traitName;

    /**
     * @param ImportString|null $import
     * @param string            $traitName
     */
    public function __construct(string $traitName, ?ImportString $import = null)
    {
        $this->import = $import;
        $this->traitName = $traitName;
    }


    public function __toString(): string
    {
        $this->traitName = str_starts_with($this->traitName, "\\")
            ? str($this->traitName)
                ->replaceFirst("\\", "")
                ->toString()
            : $this->traitName;

        return "use $this->traitName;";
    }
}