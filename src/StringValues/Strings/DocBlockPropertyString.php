<?php

namespace Cubeta\CubetaStarter\StringValues\Strings;

use Illuminate\Support\Arr;

class DocBlockPropertyString
{
    public string $name;
    public ?string $type;
    public string $tag;
    public array $imports = [];

    /**
     * @param string                                 $name
     * @param string|null                            $type
     * @param string                                 $tag
     * @param PhpImportString[]|PhpImportString|null $imports
     */
    public function __construct(string $name, ?string $type = null, string $tag = "property", null|array|PhpImportString $imports = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->imports = Arr::wrap($imports);
        $this->tag = $tag;
    }


    public function __toString(): string
    {
        return "@$this->tag $this->type $this->name";
    }
}