<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

class FakeMethodString
{
    public string $key;
    public string $value;
    public ?ImportString $import;

    public function __construct(string $key, string $value, ?ImportString $import = null)
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