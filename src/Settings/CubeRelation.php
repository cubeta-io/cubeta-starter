<?php

namespace Cubeta\CubetaStarter\Settings;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\Relations\CubeBelongsTo;
use Cubeta\CubetaStarter\Settings\Relations\CubeHasMany;
use Cubeta\CubetaStarter\Settings\Relations\CubeManyToMany;
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
    public string $relationModel;

    private ?CubeTable $relationModelTableObject = null;

    /**
     * @var string|null
     */
    public ?string $key = null;

    /**
     * @var string
     */
    public string $version = 'v1';

    /**
     * @var string
     */
    public string $parentModel;

    private ?CubeTable $parentModelTableObject = null;

    private ?CubeRelation $reverseRelation = null;

    /**
     * @param string $type
     * @param string $relationModel
     * @param string $relatedModel
     * @param string $version
     */
    public function __construct(string $type, string $relationModel, string $relatedModel, string $version = 'v1')
    {
        $this->type = $type;
        $this->relationModel = Naming::model($relationModel);
        $this->parentModel = Naming::model($relatedModel);

        if ($this->type == RelationsTypeEnum::BelongsTo->value) {
            $this->key = Str::singular(strtolower($this->relationModel)) . '_id';
        }

        $this->usedString = $this->relationModel;
        $this->version = $version;
    }

    /**
     * @return bool|string
     */
    public function toJson(): bool|string
    {
        if ($this->key) {
            return json_encode([
                "type" => $this->type,
                "model_name" => $this->relationModel,
                "key" => $this->key,
            ], JSON_PRETTY_PRINT);
        }
        return json_encode([
            "type" => $this->type,
            "model_name" => $this->relationModel,
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
                "model_name" => $this->relationModel,
                "key" => $this->key,
            ];
        }
        return [
            "type" => $this->type,
            "model_name" => $this->relationModel,
        ];
    }

    /**
     * the relation is existing if its model class is defined
     * @param bool $withTypescriptChecking when true this mean check for the TypeScript model if exist
     * @param bool $withMethodChecking     when true this mean check for the relation method within the model if exists
     * @return bool
     */
    public function exists(bool $withTypescriptChecking = false, bool $withMethodChecking = false): bool
    {
        // assuming that this relation is the products relation of
        // the category has many products
        $relatedModel = $this->relationModel(); // category model
        $exist = $relatedModel->getModelPath()->exist();

        if ($withMethodChecking) {
            $exist = $exist && ClassUtils::isMethodDefined( // category model has a "product" method
                    $relatedModel->getModelPath(),
                    $this->method()
                );
        }

        if ($withTypescriptChecking) {
            $exist = $exist && $relatedModel->getTSModelPath()->exist();
        }

        return $exist;
    }

    /**
     * a relation is loadable when the current table model exists,
     * and it has the relation method and the related model exists
     * and has the current table method
     * @return bool
     */
    public function loadable(): bool
    {
        // assuming that this relation is the products relation of
        // the category has many products
        $related = $this->parentModel(); // category model
        $relatedModelPath = $related->getModelPath();

        return $relatedModelPath->exist() // category model exists
            && $this->getModelPath()->exist() // product model exists
            && ClassUtils::isMethodDefined($relatedModelPath, $this->method()) // the category model has the products method
            && ClassUtils::isMethodDefined($this->getModelPath(), $related->relationMethodNaming(singular: $this->isHasMany() || $this->isHasOne())); // the product model has the category method
    }

    /**
     * @return string
     */
    public function method(): string
    {
        // relationModel = "Product"
        // parentModel = "Category"
        // relation type = "has many"
        // return will be "products"
        if ($this->isHasMany() || $this->isManyToMany()) {
            return $this->relationMethodNaming(singular: false);
        } else return $this->relationMethodNaming();
    }

    public function reverseMethod(): string
    {
        // relationModel = "Product"
        // parentModel = "Category"
        // relation type = "has many"
        // return will be "category"
        return $this->relationMethodNaming($this->parentModel, $this->isHasMany() || $this->isHasOne());
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

    /**
     * @return CubeTable
     */
    public function relationModel(): CubeTable
    {
        if ($this->relationModelTableObject) {
            return $this->relationModelTableObject;
        }
        $this->relationModelTableObject = Settings::make()->getTable($this->relationModel) ?? CubeTable::create($this->relationModel);

        return $this->relationModelTableObject;
    }

    public function pivotTableName(): string
    {
        return Naming::pivotTableNaming($this->relationModel, $this->parentModel);
    }

    public static function factory(string $type, string $modelName, string $relatedModel, string $version = 'v1'): CubeRelation|CubeHasMany|CubeManyToMany
    {
        $type = RelationsTypeEnum::tryFrom($type);

        return match ($type) {
            RelationsTypeEnum::HasMany => new CubeHasMany($type->value, $modelName, $relatedModel, $version),
            RelationsTypeEnum::ManyToMany => new CubeManyToMany($type->value, $modelName, $relatedModel, $version),
            RelationsTypeEnum::BelongsTo => new CubeBelongsTo($type->value, $modelName, $relatedModel, $version),
            default => new self($type->value, $modelName, $relatedModel, $version)
        };
    }

    public function parentModel(): CubeTable
    {
        if ($this->parentModelTableObject) {
            return $this->parentModelTableObject;
        }

        $this->parentModelTableObject = Settings::make()->getTable($this->parentModel) ?? CubeTable::create($this->parentModel);
        return $this->parentModelTableObject;
    }

    public function reverseType(): string
    {
        return match ($this->type) {
            RelationsTypeEnum::HasMany->value => RelationsTypeEnum::BelongsTo->value,
            RelationsTypeEnum::ManyToMany->value => RelationsTypeEnum::ManyToMany->value,
            RelationsTypeEnum::BelongsTo->value => RelationsTypeEnum::HasMany->value,
            RelationsTypeEnum::HasOne->value => RelationsTypeEnum::HasOne->value,
        };
    }

    public function reverseRelation(): CubeRelation
    {
        if ($this->reverseRelation) {
            return $this->reverseRelation;
        }

        $this->reverseRelation = $this->relationModel()
            ->relations()
            ->filter(fn(CubeRelation $rel) => $rel->modelNaming() == $this->parentModel()->modelNaming())
            ->first()
            ?? CubeRelation::factory($this->reverseType(), $this->relationModel()->modelNaming(), $this->parentModel()->modelNaming());

        return $this->reverseRelation;
    }

    public function singularRelation(): bool
    {
        return $this->isBelongsTo() || $this->isHasOne();
    }
}
