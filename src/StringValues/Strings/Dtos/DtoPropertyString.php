<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Dtos;

use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class DtoPropertyString
{
    public string $name;

    public string $type;

    public bool $nullable;

    /**
     * @var PhpImportString[]|null
     */
    public ?array $imports = null;

    /**
     * @param  PhpImportString[]|null  $imports
     */
    public function __construct(string $name, string $type, bool $nullable = false, ?array $imports = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->imports = $imports;
    }

    public function __toString(): string
    {
        return $this->nullable
            ? "public ?{$this->type} \${$this->name} = null;"
            : "public {$this->type} \${$this->name};";
    }
}
