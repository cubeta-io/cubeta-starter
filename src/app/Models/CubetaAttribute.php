<?php

namespace Cubeta\CubetaStarter\app\Models;

use Cubeta\CubetaStarter\Traits\NamingConventions;

class CubetaAttribute
{
    use NamingConventions;

    public string $name;

    public string $type;

    public bool $nullable;

    public bool $unique;

    /**
     * @param string $name
     * @param string $type
     * @param bool $nullable
     * @param bool $unique
     */
    public function __construct(string $name, string $type, bool $nullable, bool $unique)
    {
        $this->name = self::columnNaming($name);
        $this->type = $type;
        $this->nullable = $nullable;
        $this->unique = $unique;
        $this->usedString = $this->name;
    }

    public function toJson(): bool|string
    {
        return json_encode([
            "name" => $this->name,
            "type" => $this->type,
            "nullable" => $this->nullable,
            "unique" => $this->unique
        ], JSON_PRETTY_PRINT);
    }

    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "type" => $this->type,
            "nullable" => $this->nullable,
            "unique" => $this->unique
        ];
    }
}
