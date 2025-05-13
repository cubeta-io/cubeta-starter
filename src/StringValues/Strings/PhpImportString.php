<?php

namespace Cubeta\CubetaStarter\StringValues\Strings;

class PhpImportString
{
    private string $classFullName;

    /**
     * @param string $classFullName
     */
    public function __construct(string $classFullName)
    {
        $this->classFullName = str_starts_with($classFullName, "\\")
            ? str($classFullName)
                ->replaceFirst("\\", "")
                ->toString()
            : $classFullName;
    }

    public function __toString(): string
    {
        return "use $this->classFullName;";
    }
}