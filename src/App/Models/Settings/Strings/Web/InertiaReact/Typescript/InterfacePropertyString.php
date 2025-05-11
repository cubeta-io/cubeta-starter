<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Typescript;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\TsImportString;

class InterfacePropertyString
{
    public string $name;
    public string $type;
    public ?TsImportString $import = null;
    public bool $isNullable;

    /**
     * @param string              $name
     * @param string              $type
     * @param bool                $isNullable
     * @param TsImportString|null $import
     */
    public function __construct(string $name, string $type, bool $isNullable, ?TsImportString $import = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->isNullable = $isNullable;
        $this->import = $import;
    }

    public function __toString(): string
    {
        $nullable = $this->isNullable ? "?" : "";
        return "{$this->name}$nullable: $this->type;";
    }
}