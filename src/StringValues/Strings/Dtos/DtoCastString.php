<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Dtos;

use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class DtoCastString
{
    public string $name;

    /**
     * the cast instantiation string e.g. : new StringCast()
     */
    public string $cast;

    /**
     * @var PhpImportString[]|null
     */
    public ?array $imports = null;

    /**
     * @param  PhpImportString[]|null  $imports
     */
    public function __construct(string $name, string $cast, ?array $imports = null)
    {
        $this->name = $name;
        $this->cast = $cast;
        $this->imports = $imports;
    }

    public function __toString(): string
    {
        return "'{$this->name}' => {$this->cast}";
    }
}
