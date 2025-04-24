<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

class DocBlockProperty
{
    public string $name;
    public string $type;
    public string $tag;
    public ?ImportString $import;

    /**
     * @param string            $name
     * @param string|null       $type
     * @param string            $tag
     * @param ImportString|null $import
     */
    public function __construct(string $name, ?string $type = null, string $tag = "property", ?ImportString $import = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->import = $import;
        $this->tag = $tag;
    }


    public function __toString(): string
    {
        return "@$this->tag $this->type $this->name";
    }
}