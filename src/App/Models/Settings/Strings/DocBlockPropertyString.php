<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

use Illuminate\Support\Arr;

class DocBlockPropertyString
{
    public string $name;
    public string $type;
    public string $tag;
    public ?array $imports = null;

    /**
     * @param string                                 $name
     * @param string|null                            $type
     * @param string                                 $tag
     * @param PhpImportString[]|PhpImportString|null $imports
     */
    public function __construct(string $name, ?string $type = null, string $tag = "property", null|array|PhpImportString $imports = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->imports = $imports ? Arr::wrap($imports) : null;
        $this->tag = $tag;
    }


    public function __toString(): string
    {
        return "@$this->tag $this->type $this->name";
    }
}