<?php

namespace Cubeta\CubetaStarter\StringValues\Strings;

use Illuminate\Support\Arr;

class DocBlockPropertyString
{
    public array $imports = [];

    public string $name;

    public string $tag;

    public ?string $type;

    /**
     * @param PhpImportString[]|PhpImportString|null $imports
     */
    public function __construct(string $name, ?string $type = null, string $tag = 'property', null|array|PhpImportString $imports = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->imports = Arr::wrap($imports);
        $this->tag = $tag;
    }

    public function __toString(): string
    {
        if ($this->tag == 'property') {
            return "@$this->tag $this->type \$$this->name";
        }

        return "@$this->tag $this->type $this->name";
    }
}
