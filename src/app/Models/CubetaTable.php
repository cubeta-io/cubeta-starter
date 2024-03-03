<?php

namespace Cubeta\CubetaStarter\app\Models;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\HasPathAndNamespace;
use Cubeta\CubetaStarter\Traits\NamingConventions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CubetaTable
{
    use NamingConventions, HasPathAndNamespace;

    /**
     * @var string
     */
    public string $modelName;

    /**
     * @var string
     */
    public string $tableName;

    /**
     * @var CubetaAttribute[]
     */
    public array $attributes;

    /**
     * @var CubetaRelation[]
     */
    public array $relations = [];

    /**
     * @param string $modelName
     * @param string $tableName
     * @param CubetaAttribute[] $attributes
     * @param CubetaRelation[] $relations
     */
    public function __construct(string $modelName, string $tableName, array $attributes, array $relations)
    {
        $this->modelName = self::getModelName($modelName);
        $this->tableName = self::getTableName($tableName);
        $this->attributes = $attributes;
        $this->relations = $relations;
        $this->usedString = $this->modelName;
    }

    /**
     * @param string $modelName
     * @param array $attributes
     * @param array $relations
     * @param array $uniques
     * @param array $nullables
     * @return CubetaTable
     */
    public static function create(string $modelName, array $attributes = [], array $relations = [], array $uniques = [], array $nullables = []): CubetaTable
    {
        return new self(
            self::getModelName($modelName),
            self::getTableName($modelName),
            collect($attributes)->map(fn($type, $name) => new CubetaAttribute($name, $type, in_array($name, $nullables), in_array($name, $uniques)))->toArray(),
            collect($relations)->map(fn($type, $rel) => new CubetaRelation($type, self::getModelName($rel)))->toArray(),
        );
    }

    /**
     * @return array{
     *     model_name:string,
     *     table_name:string,
     *     attributes:array{array{name:string , type:string , nullable:boolean , unique:boolean}},
     *     relations:array{array{type:string , model_name:string , key:null|string}}
     *     }
     */
    public function toArray(): array
    {
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            $attributes[] = $attribute->toArray();
        }

        $relations = [];
        foreach ($this->relations as $relation) {
            $relations[] = $relation->toArray();
        }

        $relations = collect($relations)->groupBy("type")->toArray();

        return [
            "model_name" => $this->modelName,
            "table_name" => $this->tableName,
            "attributes" => $attributes,
            "relations" => $relations
        ];
    }

    /**
     * @return bool|string
     */
    public function toJson(): bool|string
    {
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            $attributes[] = $attribute->toArray();
        }

        $relations = [];
        foreach ($this->relations as $relation) {
            $relations[] = $relation->toArray();
        }

        $relations = collect($relations)->groupBy("type")->toArray();

        return json_encode([
            "model_name" => $this->modelName,
            "table_name" => $this->tableName,
            "attributes" => $attributes,
            "relations" => $relations
        ]);
    }

    /**
     * @return Collection<CubetaAttribute>
     */
    public function nullables(): Collection
    {
        return collect($this->attributes)
            ->filter(fn(CubetaAttribute $attr) => $attr->nullable == true);
    }

    /**
     * @return Collection<CubetaAttribute>
     */
    public function uniques(): Collection
    {
        return collect($this->attributes)
            ->filter(fn(CubetaAttribute $attr) => $attr->unique == true);
    }

    /**
     * @param string $name
     * @param string|null $type
     * @return bool
     */
    public function hasAttribute(string $name, ?string $type): bool
    {
        return (bool)collect($this->attributes)
            ->filter(function (CubetaAttribute $attr) use ($type, $name) {
                if ($type) {
                    return ($attr->name == $name && $attr->type == $type);
                }

                return $attr->name == $name;
            })
            ->count();
    }

    /**
     * @param string $modelName
     * @param string|null $type
     * @return bool
     */
    public function hasRelation(string $modelName, ?string $type = null): bool
    {
        $modelName = modelNaming($modelName);
        return (bool)collect($this->relations)
            ->filter(function (CubetaRelation $rel) use ($type, $modelName) {
                if ($type) {
                    return ($rel->modelName == $modelName && $rel->type == $type);
                }

                return $rel->modelName == $modelName;
            })
            ->count();
    }

    /**
     * @return $this
     */
    public function save(): static
    {
        Settings::make()->addTable($this);
        return $this;
    }

    /**
     * @return CubetaAttribute
     */
    public function titleable(): CubetaAttribute
    {
        foreach ($this->attributes as $attribute) {
            if (Str::contains($attribute->name, ['name', 'title'], true)
                and in_array($attribute->type, ['string', 'text', 'json', 'translatable'])) {
                return $attribute;
            }
        }

        return $this->getAttribute("id");
    }

    /**
     * @param string $name
     * @return CubetaAttribute|null
     */
    public function getAttribute(string $name): ?CubetaAttribute
    {
        return $this->attributes()
            ->filter(fn(CubetaAttribute $attr) => ($attr->name == $name))
            ->first() ?? null;
    }

    /**
     * @return Collection<CubetaAttribute>
     */
    public function attributes(string|ColumnTypeEnum|null $type = null): Collection
    {
        if (!$type) {
            return collect($this->attributes);
        }
        
        return collect($this->attributes)
            ->filter(function (CubetaAttribute $attr) use ($type) {
                if ($type instanceof RelationsTypeEnum) {
                    return $attr->type == $type->value;
                }
                return $attr->type == $type;
            });
    }

    /**
     * @return Collection<CubetaAttribute>
     */
    public function translatables(): Collection
    {
        return $this->attributes()
            ->filter(fn(CubetaAttribute $attr) => $attr->type == ColumnTypeEnum::TRANSLATABLE->value);
    }

    public function hasRelationOfType(string|RelationsTypeEnum $type): int
    {
        return $this->relations()
            ->filter(function (CubetaRelation $rel) use ($type) {
                if ($type instanceof RelationsTypeEnum) {
                    return $rel->type == $type->value;
                } else return $rel->type == $type;
            })
            ->count();
    }

    /**
     * @return Collection<CubetaRelation>
     */
    public function relations(string|RelationsTypeEnum|null $type = null): Collection
    {
        if (!$type) {
            return collect($this->relations);
        }

        return collect($this->relations)
            ->filter(function (CubetaRelation $rel) use ($type) {
                if ($type instanceof RelationsTypeEnum) {
                    return $rel->type == $type->value;
                }

                return $rel->type == $type;
            });
    }
}
