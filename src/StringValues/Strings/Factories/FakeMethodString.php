<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Factories;

use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class FakeMethodString
{
    public ?PhpImportString $import;

    public string $key;

    public string $value;

    public function __construct(string $key, string $value, ?PhpImportString $import = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->import = $import;
    }

    public function __toString(): string
    {
        return "'$this->key' => $this->value";
    }
}
