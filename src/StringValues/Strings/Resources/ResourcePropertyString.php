<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Resources;

use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class ResourcePropertyString
{
    /**
     * @var PhpImportString[]
     */
    public array $imports = [];

    public string $key;

    public string $value;

    /**
     * @param PhpImportString[]|null $imports
     */
    public function __construct(string $key, ?string $value = null, array $imports = [])
    {
        $this->key = $key;
        $this->value = $value ?? "\$this->$key";
        $this->imports = $imports;
    }

    public function __toString(): string
    {
        return "'$this->key' => $this->value";
    }
}
