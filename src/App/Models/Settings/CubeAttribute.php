<?php

namespace Cubeta\CubetaStarter\App\Models\Settings;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Traits\NamingConventions;

/**
 *
 */
class CubeAttribute
{
    use NamingConventions;

    public ?string $parentTableName;

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
     * @param string      $name
     * @param string      $type
     * @param bool        $nullable
     * @param bool        $unique
     * @param string|null $parentTableName
     */
    public function __construct(string $name, string $type, bool $nullable = false, bool $unique = false, ?string $parentTableName = null)
    {
        $this->name = Naming::column($name);
        $this->type = $type;
        $this->nullable = $nullable;
        $this->unique = $unique;
        $this->usedString = $this->name;
        $this->parentTableName = $parentTableName;
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
            "unique" => $this->unique,
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
            "unique" => $this->unique,
        ];
    }

    /**
     * @return bool
     */
    public function isKey(): bool
    {
        return $this->type == ColumnTypeEnum::KEY->value
            || $this->type == ColumnTypeEnum::FOREIGN_KEY->value;
    }

    /**
     * @return bool
     */
    public function isFile(): bool
    {
        return $this->type == ColumnTypeEnum::FILE->value;
    }

    /**
     * @return bool
     */
    public function isTranslatable(): bool
    {
        return $this->type == ColumnTypeEnum::TRANSLATABLE->value;
    }

    /**
     * @return bool
     */
    public function isDateTime(): bool
    {
        return $this->type == ColumnTypeEnum::DATETIME->value;
    }

    public function isDateable(): bool
    {
        return ColumnTypeEnum::isDateTimeType($this->type);
    }

    /**
     * @return bool
     */
    public function isString(): bool
    {
        return in_array($this->type, [
            ColumnTypeEnum::STRING->value,
            ColumnTypeEnum::TEXT->value,
            ColumnTypeEnum::JSON->value,
            ColumnTypeEnum::TRANSLATABLE->value,
        ]);
    }

    /**
     * @return bool
     */
    public function isNumeric(): bool
    {
        return in_array($this->type, [
            ColumnTypeEnum::INTEGER->value,
            ColumnTypeEnum::BIG_INTEGER->value,
            ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
            ColumnTypeEnum::DOUBLE->value,
            ColumnTypeEnum::UNSIGNED_DOUBLE->value,
            ColumnTypeEnum::FLOAT->value,
        ]);
    }

    /**
     * @return bool
     */
    public function isBoolean(): bool
    {
        return $this->type == ColumnTypeEnum::BOOLEAN->value;
    }

    /**
     * @return bool
     */
    public function isText(): bool
    {
        return $this->type == ColumnTypeEnum::TEXT->value;
    }

    /**
     * @return bool
     */
    public function isTextable(): bool
    {
        return in_array($this->name, ['desc', 'description', 'summary', 'post', 'note', 'message', 'body']);
    }

    /**
     * @return CubeTable|null
     */
    public function getOwnerTable(): ?CubeTable
    {
        return Settings::make()->getTable($this->parentTableName);
    }
}
