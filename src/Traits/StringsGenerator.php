<?php

namespace Cubeta\CubetaStarter\Traits;

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
}
