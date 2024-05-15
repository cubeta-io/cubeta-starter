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
    public function hasManyFunction(CubeTable | CubeRelation $model): string
    {
        $relationName = $model->relationMethodNaming(singular: false);
        return "public function $relationName()\n{\n\t return \$this->hasMany(" . $model->modelName . "::class);\n}\n\n";
    }

    /**
     * @param CubeTable|CubeRelation $model
     * @return string
     */
    public function manyToManyFunction(CubeTable | CubeRelation $model): string
    {
        $relationName = $model->relationMethodNaming(singular: false);
        return "public function $relationName()\n{\n\t return \$this->belongsToMany(" . $model->modelName . "::class);\n}\n\n";
    }

    /**
     * @param CubeTable|CubeRelation $model
     * @return string
     */
    public function belongsToFunction(CubeTable | CubeRelation $model): string
    {
        $relationName = $model->relationMethodNaming();
        return "public function $relationName()\n{\n\t return \$this->belongsTo(" . $model->modelName . "::class); \n}\n\n";
    }

    /**
     * @param CubeTable|CubeRelation $model
     * @return string
     */
    public function factoryRelationMethod(CubeTable | CubeRelation $model): string
    {
        $functionName = 'with' . ucfirst(Str::plural(Str::studly($model->modelName)));
        return "public function {$functionName}(\$count = 1)\n{\n\t return \$this->has(\\" . config('cubeta-starter.model_namespace') . "\\{$model->modelName}::factory(\$count));\n} \n";
    }

    /***********************Inertia - React - Typescript*************************/

    /**
     * @param CubeAttribute $attribute
     * @param bool          $isUpdate
     * @return string
     */
    public function inertiaTranslatableInputComponent(CubeAttribute $attribute, bool $isUpdate = false): string
    {
        if (!$isUpdate) {
            $required = $attribute->nullable ? "false" : "true";
        } else {
            $required = "false";
        }
        $value = $this->getDefaultValue($isUpdate, $attribute);

        return "\n<TranslatableInput
                    name={'{$attribute->name}'}
                    label={'{$attribute->titleNaming()}'}
                    onChange={(e) =>
                        setData('{$attribute->name}', e.target.value)
                    }
                    required={{$required}}
                    $value
                  />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @param array         $labels
     * @param bool          $isUpdate
     * @return string
     */
    public function inertiaRadioButtonComponent(CubeAttribute $attribute, array $labels, bool $isUpdate = false): string
    {
        $value = $this->getDefaultValue($isUpdate, $attribute);

        return "\n<Radio
                        name=\"{$attribute->name}\"
                        items={[
                            { label: \"{$labels['true']}\", value: true },
                            { label: \"{$labels['false']}\", value: false },
                        ]}
                        onChange={(e) =>
                            setData(
                                \"{$attribute->name}\",
                                e.target.value == \"true\"
                            )
                        }
                        $value
                    />\n";
    }

    /**
     * @param CubeTable|null $relatedModel
     * @param string         $select2Route
     * @param CubeAttribute  $attribute
     * @param bool           $isUpdate
     * @return string
     */
    public function inertiaApiSelectComponent(?CubeTable $relatedModel, string $select2Route, CubeAttribute $attribute, bool $isUpdate = false): string
    {
        if (!$isUpdate) {
            $required = $attribute->nullable ? "false" : "true";
        } else {
            $required = "false";
        }

        $value = $this->getDefaultValue($isUpdate, $attribute, $relatedModel);

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
                        required={{$required}}
                        $value
                    />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @return string
     */
    public function inertiaFileInputComponent(CubeAttribute $attribute, bool $isUpdate = false): string
    {
        if (!$isUpdate) {
            $required = $attribute->nullable ? "false" : "true";
        } else {
            $required = "false";
        }

        return "\n<Input
                        name={\"{$attribute->name}\"}
                        label={\"{$attribute->titleNaming()}\"}
                        onChange={(e) =>
                            setData(\"{$attribute->name}\", e.target.files?.[0])
                        }
                        type={\"file\"}
                        required={{$required}}
                    />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @param bool          $isUpdate
     * @return string
     */
    public function inertiaTextEditorComponent(CubeAttribute $attribute, bool $isUpdate = false): string
    {
        if (!$isUpdate) {
            $required = $attribute->nullable ? "false" : "true";
        } else {
            $required = "false";
        }

        $value = $this->getDefaultValue($isUpdate, $attribute);

        return "\n<TextEditor
                        name={\"{$attribute->name}\"}
                        label=\"{$attribute->titleNaming()}\"
                        onChange={(e) =>
                            setData(\"{$attribute->name}\", e.target.value)
                        }
                        required={{$required}}
                        $value
                    />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @param bool          $isUpdate
     * @return string
     */
    public function inertiaInputComponent(CubeAttribute $attribute, bool $isUpdate = false): string
    {
        $required = $attribute->nullable && !$isUpdate ? "false" : "true";

        $value = $this->getDefaultValue($isUpdate, $attribute);

        if ($attribute->isNumeric()) {
            $event = "e.target.valueAsNumber";
        } else {
            $event = "e.target.value";
        }

        return "\n<Input
                        name={\"{$attribute->name}\"}
                        label={\"{$attribute->titleNaming()}\"}
                        onChange={(e) =>
                            setData(\"{$attribute->name}\", {$event})
                        }
                        type={\"{$this->getInputTagType($attribute)}\"}
                        required={{$required}}
                        $value
                    />\n";
    }

    /**
     * @param CubeAttribute $attribute
     * @param bool          $isUpdate
     * @return string
     */
    public function inertiaTranslatableTextEditor(CubeAttribute $attribute, bool $isUpdate = false): string
    {
        $required = $attribute->nullable && !$isUpdate ? "false" : "true";

        $value = $this->getDefaultValue($isUpdate, $attribute);

        return "\n<TranslatableTextEditor
                            name={\"{$attribute->name}\"}
                            label=\"{$attribute->titleNaming()}\"
                            onChange={(e) =>
                                setData(\"{$attribute->name}\", e.target.value)
                            }
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
            return "defaultValue={{$attribute->getOwnerTable()->variableNaming()}?.{$relatedModel->relationMethodNaming()} ? [{$attribute->getOwnerTable()->variableNaming()}.{$relatedModel->relationMethodNaming()}] : []}";
        } else {
            return "defaultValue={{$attribute->getOwnerTable()->variableNaming()}.{$attribute->name}}";
        }
    }
}
