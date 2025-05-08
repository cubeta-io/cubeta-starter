<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Resources;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\PhpImportString;

class ResourcePropertyString
{
    public string $key;
    public string $value;
    /**
     * @var PhpImportString[]|null
     */
    public ?array $imports = [];

    /**
     * @param string                 $key
     * @param string|null            $value
     * @param PhpImportString[]|null $imports
     */
    public function __construct(string $key, ?string $value = null, ?array $imports = null)
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