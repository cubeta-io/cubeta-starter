<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript;

use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;

class InterfacePropertyString
{
    public ?TsImportString $import = null;

    public bool $isNullable;

    public string $name;

    public string $type;

    public function __construct(string $name, string $type, bool $isNullable, ?TsImportString $import = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->isNullable = $isNullable;
        $this->import = $import;
    }

    public function __toString(): string
    {
        $nullable = $this->isNullable ? '?' : '';

        return "{$this->name}$nullable: $this->type;";
    }
}
