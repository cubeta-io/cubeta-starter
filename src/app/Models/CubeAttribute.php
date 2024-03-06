<?php

namespace Cubeta\CubetaStarter\app\Models;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Traits\NamingConventions;

/**
 *
 */
class CubeAttribute
{
    use NamingConventions;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var bool
     */
    public bool $nullable;

    /**
     * @var bool
     */
    public bool $unique;

    /**
     * @param string $name
     * @param string $type
     * @param bool $nullable
     * @param bool $unique
     */
    public function __construct(string $name, string $type, bool $nullable, bool $unique)
    {
        $this->name = Naming::column($name);
        $this->type = $type;
        $this->nullable = $nullable;
        $this->unique = $unique;
        $this->usedString = $this->name;
    }

    /**
     * @return bool|string
     */
    public function toJson(): bool|string
    {
        return json_encode([
            "name" => $this->name,
            "type" => $this->type,
            "nullable" => $this->nullable,
            "unique" => $this->unique
        ], JSON_PRETTY_PRINT);
    }

    /**
     * @return array{name:string , type:string , nullable:boolean , unique:boolean}
     */
    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "type" => $this->type,
            "nullable" => $this->nullable,
            "unique" => $this->unique
        ];
    }

    public function isKey(): bool
    {
        return $this->type == ColumnTypeEnum::KEY->value;
    }

    public function isFile(): bool
    {
        return $this->type == ColumnTypeEnum::FILE->value;
    }

    public function isTranslatable(): bool
    {
        return $this->type == ColumnTypeEnum::TRANSLATABLE->value;
    }

    public function isDateTime(): bool
    {
        return $this->type == ColumnTypeEnum::DATETIME->value;
    }
}
