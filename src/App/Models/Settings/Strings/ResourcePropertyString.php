<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

class ResourcePropertyString
{
    public string $key;
    public string $value;
    /**
     * @var ImportString[]|null
     */
    public ?array $imports = [];

    /**
     * @param string              $key
     * @param string|null         $value
     * @param ImportString[]|null $imports
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