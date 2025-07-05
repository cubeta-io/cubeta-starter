<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Tests;

use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class TestAdditionalFactoryDataString
{
    public string $key;
    public string $value;

    /** @var PhpImportString[]|null */
    public ?array $imports = null;

    /**
     * @param string                 $key
     * @param string                 $value
     * @param PhpImportString[]|null $imports
     */
    public function __construct(string $key, string $value, ?array $imports = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->imports = $imports;
    }

    public function __toString(): string
    {
        if (!is_null($this->imports)) {
            return "'$this->key' => $this->value";
        }

        return "'$this->key' => '$this->value'";
    }
}