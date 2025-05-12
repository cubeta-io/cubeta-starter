<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Illuminate\Support\Str;

/**
 *
 */
trait StringsGenerator
{
    /**
     * @param CubeTable|CubeRelation $model
     * @return string
     */
    public function hasManyFunction(CubeTable|CubeRelation $model): string
    {
        $relationName = $model->relationMethodNaming(singular: false);
        return "public function $relationName()\n{\n\t return \$this->hasMany(" . $model->modelName . "::class);\n}\n\n";
    }

    /**
     * @param CubeTable|CubeRelation $model
     * @param string                 $pivot
     * @return string
     */
    public function manyToManyFunction(CubeTable|CubeRelation $model, string $pivot): string
    {
        $relationName = $model->relationMethodNaming(singular: false);
        return "public function $relationName()\n{\n\t return \$this->belongsToMany(" . $model->modelName . "::class , '$pivot');\n}\n\n";
    }

    /**
     * @param CubeTable|CubeRelation $model
     * @return string
     */
    public function belongsToFunction(CubeTable|CubeRelation $model): string
    {
        $relationName = $model->relationMethodNaming();
        return "public function $relationName()\n{\n\t return \$this->belongsTo(" . $model->modelName . "::class); \n}\n\n";
    }

    /**
     * @param CubeTable|CubeRelation $model
     * @return string
     */
    public function factoryRelationMethod(CubeTable|CubeRelation $model): string
    {
        $functionName = 'with' . ucfirst(Str::plural(Str::studly($model->modelName)));
        return "public function {$functionName}(\$count = 1)\n{\n\t return \$this->has(\\" . config('cubeta-starter.model_namespace') . "\\{$model->modelName}::factory(\$count));\n} \n";
    }

    /***********************Inertia - React - Typescript*************************/

    /**
     * @param CubeTable|null $relatedModel
     * @param string         $dataRoute
     * @param CubeAttribute  $attribute
     * @param bool           $isUpdate
     * @return string
     */
    public function inertiaApiSelectComponent(?CubeTable $relatedModel, string $dataRoute, CubeAttribute $attribute, bool $isUpdate = false): string
    {
        if (!$isUpdate) {
            $required = $attribute->nullable ? "false" : "true";
        } else {
            $required = "false";
        }

        $value = $this->getDefaultValue($isUpdate, $attribute, $relatedModel);

        $optionLabel = !$relatedModel->titleable()->isTranslatable()
            ? "optionLabel={\"{$relatedModel->titleable()->name}\"}"
            : "getOptionLabel={(data) => translate(data.{$relatedModel->titleable()->name})}";

        return "\n<ApiSelect
                        api={(
                            page,
                            search
                        ): Promise<PaginatedResponse<{$relatedModel->modelName}>> =>
                            fetch(
                                route(\"{$dataRoute}\", {
                                    page: page,
                                    search: search,
                                }),
                                {
                                    method: \"GET\",
                                    headers: {
                                        \"Accept\": \"application/html\",
                                        \"Content-Type\": \"application/html\"
                                    },
                                }
                            ).then((res) => res.json())
                        }
                        getDataArray={(response) => response.data ?? []}
                        getIsLast={(data) =>
                            data.pagination_data?.is_last ?? false
                        }
                        getTotalPages={(data) =>
                            data.pagination_data?.total_pages ?? 2
                        }
                        name={\"category_id\"}
                        label={\"Category\"}
                        onChange={(e) =>
                            setData(\"{$attribute->name}\", Number(e.target.value))
                        }
                        {$optionLabel}
                        optionValue={\"id\"}
                        required={{$required}}
                        $value
                    />\n";
    }

    private function getDefaultValue(bool $isUpdate, CubeAttribute $attribute, ?CubeTable $relatedModel = null): string
    {
        if (!$isUpdate) {
            if ($attribute->isBoolean()) {
                return "checked={(val: any) => val}";
            }
            return "";
        }

        if ($attribute->isBoolean()) {
            return "checked={(val: any) => val == {$attribute->getOwnerTable()->variableNaming()}.{$attribute->name}}";
        } elseif ($attribute->isKey()) {
            return "defaultValues={{$attribute->getOwnerTable()->variableNaming()}?.{$relatedModel->relationMethodNaming()} ? [{$attribute->getOwnerTable()->variableNaming()}.{$relatedModel->relationMethodNaming()}] : []}";
        } else {
            return "defaultValue={{$attribute->getOwnerTable()->variableNaming()}.{$attribute->name}}";
        }
    }
}
