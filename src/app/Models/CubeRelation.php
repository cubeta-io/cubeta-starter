<?php

namespace Cubeta\CubetaStarter\App\Models;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Traits\HasPathAndNamespace;
use Cubeta\CubetaStarter\Traits\NamingConventions;
use Illuminate\Support\Str;

class CubeRelation
{
    use NamingConventions, HasPathAndNamespace;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var string
     */
    public string $modelName;

    /**
     * @var string|null
     */
    public ?string $key = null;

    /**
     * @var string
     */
    public string $relatedModel;

    /**
     * @param string $type
     * @param string $modelName
     * @param string $relatedModel
     */
    public function __construct(string $type, string $modelName, string $relatedModel)
    {
        $this->type = $type;
        $this->modelName = Naming::model($modelName);
        $this->relatedModel = Naming::model($relatedModel);

        if ($this->type == RelationsTypeEnum::BelongsTo->value) {
            $this->key = Str::singular(strtolower($this->modelName)) . '_id';
        }

        $this->usedString = $this->modelName;
    }

    /**
     * @return bool|string
     */
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

    /**
     * @return array{type:string , model_name:string , key:null|string}
     */
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

    public function loadable(): bool
    {
        $related = CubeTable::create($this->relatedModel);
        $relatedModelPath = $related->getModelPath();

        return $relatedModelPath->exist()
            && $this->getModelPath()->exist()
            && ClassUtils::isMethodDefined($relatedModelPath, $this->method())
            && ClassUtils::isMethodDefined($this->getModelPath(), $related->relationFunctionNaming(singular: $this->isHasMany() || $this->isHasOne()));
    }

    /**
     * @return string
     */
    public function method(): string
    {
        if ($this->isHasMany() || $this->isManyToMany()) {
            return $this->relationFunctionNaming(singular: false);
        } else return $this->relationFunctionNaming();
    }

    /**
     * @return bool
     */
    public function isHasMany(): bool
    {
        return $this->type == RelationsTypeEnum::HasMany->value;
    }

    /**
     * @return bool
     */
    public function isManyToMany(): bool
    {
        return $this->type == RelationsTypeEnum::ManyToMany->value;
    }

    /**
     * @return bool
     */
    public function isBelongsTo(): bool
    {
        return $this->type == RelationsTypeEnum::BelongsTo->value;
    }

    /**
     * @return bool
     */
    public function isHasOne(): bool
    {
        return $this->type == RelationsTypeEnum::HasOne->value;
    }
}
