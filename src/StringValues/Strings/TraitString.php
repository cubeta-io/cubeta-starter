<?php

namespace Cubeta\CubetaStarter\StringValues\Strings;

class TraitString
{
    public ?PhpImportString $import;
    public string $traitName;

    /**
     * @param PhpImportString|null $import
     * @param string               $traitName
     */
    public function __construct(string $traitName, ?PhpImportString $import = null)
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