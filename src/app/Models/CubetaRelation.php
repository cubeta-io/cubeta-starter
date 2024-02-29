<?php

namespace Cubeta\CubetaStarter\app\Models;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\HasPathAndNamespace;
use Cubeta\CubetaStarter\Traits\NamingConventions;
use Illuminate\Support\Str;

class CubetaRelation
{
    use NamingConventions, HasPathAndNamespace;

    public string $type;

    public string $modelName;

    public ?string $key = null;

    /**
     * @param string $type
     * @param string $modelName
     */
    public function __construct(string $type, string $modelName)
    {
        $this->type = $type;
        $this->modelName = self::getModelName($modelName);

        if ($this->type == RelationsTypeEnum::BelongsTo->value) {
            $this->key = Str::singular(strtolower($this->modelName)) . '_id';
        }

        $this->usedString = $this->modelName;
    }

    public function toJson(): bool|string
    {
        if ($this->key) {
            return json_encode([
                "type" => $this->type,
                "model_name" => $this->modelName,
                "key" => $this->key
            ], JSON_PRETTY_PRINT);
        }
        return json_encode([
            "type" => $this->type,
            "model_name" => $this->modelName,
        ], JSON_PRETTY_PRINT);
    }

    public function toArray(): array
    {
        if ($this->key) {
            return [
                "type" => $this->type,
                "model_name" => $this->modelName,
                "key" => $this->key
            ];
        }
        return [
            "type" => $this->type,
            "model_name" => $this->modelName,
        ];
    }

    public function isBelongsTo(): bool
    {
        return $this->type == RelationsTypeEnum::BelongsTo->value;
    }

    public function isHasOne(): bool
    {
        return $this->type == RelationsTypeEnum::HasOne->value;
    }

    public function method(): string
    {
        if ($this->isHasMany() || $this->isManyToMany()) {
            return relationFunctionNaming($this->modelName, false);
        } else return relationFunctionNaming($this->modelName, true);
    }

    public function isHasMany(): bool
    {
        return $this->type == RelationsTypeEnum::HasMany->value;
    }

    public function isManyToMany(): bool
    {
        return $this->type == RelationsTypeEnum::ManyToMany->value;
    }
}
