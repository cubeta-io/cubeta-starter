<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Illuminate\Support\Str;

trait StringsGenerator
{
    public function hasManyFunction(CubeTable|CubeRelation $model): string
    {
        $relationName = $model->relationMethodNaming(singular: false);
        return "public function $relationName()\n{\n\t return \$this->hasMany(" . $model->modelName . "::class);\n}\n\n";
    }

    public function manyToManyFunction(CubeTable|CubeRelation $model): string
    {
        $relationName = $model->relationMethodNaming(singular: false);
        return "public function $relationName()\n{\n\t return \$this->belongsToMany(" . $model->modelName . "::class);\n}\n\n";
    }

    public function belongsToFunction(CubeTable|CubeRelation $model): string
    {
        $relationName = $model->relationMethodNaming();
        return "public function $relationName()\n{\n\t return \$this->belongsTo(" . $model->modelName . "::class); \n}\n\n";
    }

    public function factoryRelationMethod(CubeTable|CubeRelation $model): string
    {
        $functionName = 'with' . ucfirst(Str::plural(Str::studly($model->modelName)));
        return "public function {$functionName}(\$count = 1)\n{\n\t return \$this->has(\\" . config('cubeta-starter.model_namespace') . "\\{$model->modelName}::factory(\$count));\n} \n";
    }

    /**
     * @param CubeAttribute $attribute
     * @return string
     */
    public function inertiaTranslatableInputComponent(CubeAttribute $attribute): string
    {
        return "\n<TranslatableInput
                    name={'{$attribute->name}'}
                    label={'{$attribute->titleNaming()}'}
                    onChange={(e) =>
                        setData('name', e.target.value)
                    }
                  />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @param array         $labels
     * @return string
     */
    public function inertiaRadioButtonComponent(CubeAttribute $attribute, array $labels): string
    {
        return "\n<Radio
                        name=\"{$attribute->name}\"
                        items={[
                            { label: \"{$labels['true']}\", value: true },
                            { label: \"{$labels['false']}\", value: false },
                        ]}
                        checked={(val: any) => val == true}
                        onChange={(e) =>
                            setData(
                                \"{$attribute->name}\",
                                e.target.value == \"true\"
                            )
                        }
                    />\n";
    }

    /**
     * @param CubeTable|null $relatedModel
     * @param string         $select2Route
     * @param CubeAttribute  $attribute
     * @return string
     */
    public function inertiaApiSelectComponent(?CubeTable $relatedModel, string $select2Route, CubeAttribute $attribute): string
    {
        return "\n<ApiSelect
                        api={(
                            page,
                            search
                        ): Promise<PaginatedResponse<{$relatedModel->modelName}>> =>
                            fetch(
                                route(\"{$select2Route}\", {
                                    page: page,
                                    search: search,
                                }),
                                {
                                    method: \"GET\",
                                    headers: {
                                        accept: \"application/json\",
                                    },
                                }
                            ).then((res) => res.json())
                        }
                        getDataArray={(response) => response.data ?? []}
                        getIsLast={(data) =>
                            data.pagination_date?.is_last ?? false
                        }
                        getTotalPages={(data) =>
                            data.pagination_date?.total_pages ?? 2
                        }
                        name={\"category_id\"}
                        label={\"Category\"}
                        onChange={(e) =>
                            setData(\"{$attribute->name}\", e.target.value)
                        }
                        optionLabel={\"{$relatedModel->titleable()->name}\"}
                        optionValue={\"id\"}
                    />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @return string
     */
    public function inertiaFileInputComponent(CubeAttribute $attribute): string
    {
        return "\n<Input
                        name={\"{$attribute->name}\"}
                        label={\"{$attribute->titleNaming()}\"}
                        onChange={(e) =>
                            setData(\"{$attribute->name}\", e.target.files?.[0])
                        }
                        type={\"file\"}
                    />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @return string
     */
    public function inertiaTextEditroComponent(CubeAttribute $attribute): string
    {
        return "\n<TextEditor
                        name={\"{$attribute->name}\"}
                        label=\"{$attribute->titleNaming()}\"
                        onChange={(e) =>
                            setData(\"{$attribute->name}\", e.target.value)
                        }
                    />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @return string
     */
    public function inertiaInputComponent(CubeAttribute $attribute): string
    {
        return "\n<Input
                        name={\"{$attribute->name}\"}
                        label={\"{$attribute->titleNaming()}\"}
                        onChange={(e) =>
                            setData(\"{$attribute->name}\", e.target.value)
                        }
                        type={\"{$this->getInputTagType($attribute)}\"}
                    />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @return string
     */
    public function inertiaTranslatableTextEditor(CubeAttribute $attribute): string
    {
        return "\n<TranslatableTextEditor
                            name={\"{$attribute->name}\"}
                            label=\"{$attribute->titleNaming()}\"
                            onChange={(e) =>
                                setData(\"{$attribute->name}\", e.target.value)
                            }
                        />\n";
    }
}
