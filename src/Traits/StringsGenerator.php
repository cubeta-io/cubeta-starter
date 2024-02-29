<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\app\Models\CubetaRelation;
use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Illuminate\Support\Str;

trait StringsGenerator
{
    public function hasManyFunction(CubetaTable|CubetaRelation $model): string
    {
        $relationName = $model->relationFunctionNaming(singular: false);
        return "public function $relationName() : HasMany\n{\n\t return \$this->hasMany(" . $model->modelName . "::class);\n}\n\n";
    }

    public function manyToManyFunction(CubetaTable|CubetaRelation $model): string
    {
        $relationName = $model->relationFunctionNaming(singular: false);
        return "public function $relationName() : BelongsToMany\n{\n\t return \$this->belongsToMany(" . $model->modelName . "::class);\n}\n\n";
    }

    public function belongsToFunction(CubetaTable|CubetaRelation $model): string
    {
        $relationName = $model->relationFunctionNaming();
        return "public function $relationName():belongsTo \n {\n\t return \$this->belongsTo(" . $model->modelName . "::class); \n}\n\n";
    }

    public function factoryRelationMethod(CubetaTable|CubetaRelation $model): string
    {
        $functionName = 'with' . ucfirst(Str::plural(Str::studly($model->modelName)));
        return "public function {$functionName}(\$count = 1)\n{\n\t return \$this->has(\\" . config('cubeta-starter.model_namespace') . "\\{$model->name}::factory(\$count));\n} \n";
    }
}
