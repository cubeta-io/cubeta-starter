<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Models;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;

class CastColumnString
{
    public string $keyName;
    public string $type;
    public ?ImportString $import = null;

    /**
     * @param string            $keyName
     * @param string            $type
     * @param ImportString|null $import
     */
    public function __construct(string $keyName, string $type, ?ImportString $import = null)
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