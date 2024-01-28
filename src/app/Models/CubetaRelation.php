<?php

namespace Cubeta\CubetaStarter\app\Models;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;

class CubetaRelation
{
    public string $type;

    public string $modelName;

    public ?string $key = null;

    /**
     * @param string $type
     * @param string $modelName
     * @param string|null $key
     */
    public function __construct(string $type, string $modelName, ?string $key = null)
    {
        $this->type = $type;
        $this->modelName = modelNaming($modelName);
        $this->key = $key;
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
        return $this->type == RelationsTypeEnum::BelongsTo;
    }

    public function isHasOne(): bool
    {
        return $this->type == RelationsTypeEnum::HasOne;
    }

    public function method(): string
    {
        if ($this->isHasMany() || $this->isManyToMany()) {
            return relationFunctionNaming($this->modelName, false);
        } else return relationFunctionNaming($this->modelName, true);
    }

    public function isHasMany(): bool
    {
        return $this->type == RelationsTypeEnum::HasMany;
    }

    public function isManyToMany(): bool
    {
        return $this->type == RelationsTypeEnum::ManyToMany;
    }

    public function modelPath(): string
    {
        return getModelPath($this->modelName);
    }
}
