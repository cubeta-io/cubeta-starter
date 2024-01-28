<?php

namespace Cubeta\CubetaStarter\app\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CubetaTable
{
    public string $modelName;

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
        $this->modelName = modelNaming($modelName);
        $this->tableName = tableNaming($tableName);
        $this->attributes = $attributes;
        $this->relations = $relations;
    }

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
     * @return Collection<CubetaRelation>
     */
    public function relations(): Collection
    {
        return collect($this->relations);
    }

    public function nullables(): Collection
    {
        return collect($this->attributes)
            ->filter(function (CubetaAttribute $attr) {
                return $attr->nullable == true;
            });
    }

    public function uniques(): Collection
    {
        return collect($this->attributes)
            ->filter(function (CubetaAttribute $attr) {
                return $attr->unique == true;
            });
    }

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

    public function save(): static
    {
        Settings::make()->addTable($this);
        return $this;
    }

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
    public function attributes(): Collection
    {
        return collect($this->attributes);
    }

    public function variableName(): string
    {
        return variableNaming($this->modelName);
    }

    public function keyName(): string
    {
        return strtolower(Str::singular($this->modelName)) . "_id";
    }
}
