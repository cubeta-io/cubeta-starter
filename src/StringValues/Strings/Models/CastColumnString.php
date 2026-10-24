<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Models;

use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class CastColumnString
{
    public ?PhpImportString $import = null;

    public string $keyName;

    public string $type;

    public function __construct(string $keyName, string $type, ?PhpImportString $import = null)
    {
        $this->keyName = $keyName;
        $this->type = $type;
        $this->import = $import;
    }

    public function __toString(): string
    {
        if ($this->import) {
            return "'$this->keyName' => $this->type";
        }

        return "'$this->keyName' => '$this->type'";
    }
}
