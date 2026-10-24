<?php

namespace Cubeta\CubetaStarter\Settings;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Helpers\CubeCollection;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\HasViewsAndRoutes;
use Cubeta\CubetaStarter\Traits\NamingConventions;
use Cubeta\CubetaStarter\Traits\HasPathAndNamespace;

class CubeTable
{
    use HasPathAndNamespace, HasViewsAndRoutes, NamingConventions;

    /**
     * @var CubeAttribute[]
     */
    public array $attributes;

    public string $modelName;

    /**
     * @var CubeRelation[]
     */
    public array $relations = [];

    public string $tableName;

    public string $version;

    /**
     * @param CubeAttribute[] $attributes
     * @param CubeRelation[]  $relations
     */
    public function __construct(string $modelName, string $tableName, array $attributes, array $relations, string $version)
    {
        $this->modelName = Naming::model($modelName);
        $this->tableName = Naming::table($tableName);
        $this->attributes = $attributes;

        $this->sortAttributes();

        $this->relations = $relations;
        $this->usedString = $this->modelName;
        $this->version = $version;
    }

    private function sortAttributes(): void
    {
        usort($this->attributes, function (CubeAttribute $a, CubeAttribute $b) {
            // Helper function to get priority (0: normal, 1: textable, 2: file)
            $getPriority = function (CubeAttribute $attr) {
                if ($attr->isFile()) {
                    return 2; // Highest priority (will be at end)
                }
                if ($attr->isText() || ($attr->isTranslatable() && $attr->isTextable())) {
                    return 1; // Medium priority (will be in middle)
                }

                return 0; // Lowest priority (will be at start)
            };

            $priorityA = $getPriority($a);
            $priorityB = $getPriority($b);

            if ($priorityA === $priorityB) {
                return 0;
            }

            return $priorityA <=> $priorityB;
        });
    }

    /**
     * @return CubeCollection<int,CubeAttribute>
     */
    public function attributes(string|ColumnTypeEnum|null $type = null): CubeCollection
    {
        if (! $type) {
            return CubeCollection::make($this->attributes);
        }

        return CubeCollection::make($this->attributes)
            ->filter(function (CubeAttribute $attr) use ($type) {
                if ($type instanceof RelationsTypeEnum) {
                    return $attr->type == $type->value;
                }

                return $attr->type == $type;
            });
    }

    public function collect(): CubeCollection
    {
        return CubeCollection::make($this->toArray());
    }

    public static function create(string $modelName, array $attributes = [], array $relations = [], array $uniques = [], array $nullables = [], string $version = 'v1'): CubeTable
    {
        $tableName = Naming::table($modelName);

        return new self(
            Naming::model($modelName),
            $tableName,
            collect($attributes)->map(fn ($type, $name) => new CubeAttribute($name, $type, in_array($name, $nullables), in_array($name, $uniques)))->toArray(),
            collect($relations)->map(fn ($type, $rel) => new CubeRelation($type, Naming::model($rel), Naming::model($modelName), $version))->toArray(),
            $version
        );
    }

    public function getAttribute(string $name): ?CubeAttribute
    {
        return $this->attributes()
            ->filter(fn (CubeAttribute $attr) => ($attr->name == $name))
            ->first() ?? null;
    }

    public function hasAttribute(string $name, ?string $type): bool
    {
        return (bool) collect($this->attributes)
            ->filter(function (CubeAttribute $attr) use ($type, $name) {
                if ($type) {
                    return $attr->name == $name && $attr->type == $type;
                }

                return $attr->name == $name;
            })
            ->count();
    }

    public function hasRelation(string $modelName, ?string $type = null): bool
    {
        $modelName = Naming::model($modelName);

        return (bool) collect($this->relations)
            ->filter(function (CubeRelation $rel) use ($type, $modelName) {
                if ($type) {
                    return $rel->relationModel == $modelName && $rel->type == $type;
                }

                return $rel->relationModel == $modelName;
            })
            ->count();
    }

    public function hasRelationOfType(string|RelationsTypeEnum $type): bool
    {
        return $this->relations()
            ->filter(function (CubeRelation $rel) use ($type) {
                if ($type instanceof RelationsTypeEnum) {
                    return $rel->type == $type->value;
                } else {
                    return $rel->type == $type;
                }

            })
            ->count() > 0;
    }

    public function hasTranslatableAttribute(): bool
    {
        return (bool) $this->attributes()
            ->filter(fn (CubeAttribute $attr) => $attr->type == ColumnTypeEnum::TRANSLATABLE->value)
            ->count();
    }

    /**
     * @return Collection<CubeAttribute>
     */
    public function nullables(): Collection
    {
        return collect($this->attributes)
            ->filter(fn (CubeAttribute $attr) => $attr->nullable == true);
    }

    /**
     * @return CubeCollection<int ,CubeRelation>
     */
    public function relations(string|RelationsTypeEnum|null $type = null): CubeCollection
    {
        if (! $type) {
            return CubeCollection::make($this->relations);
        }

        return CubeCollection::make($this->relations)
            ->filter(function (CubeRelation $rel) use ($type) {
                if ($type instanceof RelationsTypeEnum) {
                    return $rel->type == $type->value;
                }

                return $rel->type == $type;
            });
    }

    /**
     * @return $this
     */
    public function save(): static
    {
        Settings::make()->addTable($this);

        return $this;
    }

    public function searchableColsAsString(): string
    {
        $cols = $this->attributes()
            ->filter(fn (CubeAttribute $attr) => $attr->isString())
            ->map(fn (CubeAttribute $attr) => $attr->name)
            ->implode("','");

        return "'$cols'";
    }

    public function searchables(): array
    {
        return $this->attributes()
            ->filter(fn (CubeAttribute $attr) => $attr->isString())
            ->map(fn (CubeAttribute $attr) => $attr->name)
            ->toArray();
    }

    public function titleable(): CubeAttribute
    {
        foreach ($this->attributes as $attribute) {
            if (Str::contains($attribute->name, ['name', 'title', 'header'], true)
                and in_array($attribute->type, ['string', 'text', 'json', 'translatable'])) {
                return $attribute;
            }
        }

        return $this->getAttribute('id') ?? new CubeAttribute('id', ColumnTypeEnum::INTEGER->value, false, false, $this->tableName);
    }

    /**
     * @return array{
     *     model_name:string,
     *     table_name:string,
     *     attributes:array{array{name:string , type:string , nullable:boolean , unique:boolean}},
     *     relations:array{array{type:string , model_name:string , key:null|string}},
     *     version:string
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

        $relations = collect($relations)->groupBy('type')->toArray();

        return [
            'model_name' => $this->modelName,
            'table_name' => $this->tableName,
            'attributes' => $attributes,
            'relations'  => $relations,
            'version'    => $this->version,
        ];
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

        $relations = collect($relations)->groupBy('type')->toArray();

        return json_encode([
            'model_name' => $this->modelName,
            'table_name' => $this->tableName,
            'attributes' => $attributes,
            'relations'  => $relations,
        ]);
    }

    /**
     * @return Collection<CubeAttribute>
     */
    public function translatables(): Collection
    {
        return $this->attributes()
            ->filter(fn (CubeAttribute $attr) => $attr->isTranslatable());
    }

    /**
     * @return Collection<CubeAttribute>
     */
    public function uniques(): Collection
    {
        return collect($this->attributes)
            ->filter(fn (CubeAttribute $attr) => $attr->unique == true);
    }
}
