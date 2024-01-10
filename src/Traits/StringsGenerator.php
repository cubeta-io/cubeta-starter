<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Support\Str;

trait StringsGenerator
{
    public function hasManyFunction(string $modelName): string
    {
        $relationName = relationFunctionNaming($modelName, false);
        return "public function $relationName() : HasMany\n{\n\t return \$this->hasMany(" . modelNaming($modelName) . "::class);\n}\n\n";
    }

    public function manyToManyFunction(string $modelName): string
    {
        $relationName = relationFunctionNaming($modelName, false);
        return "public function $relationName() : BelongsToMany\n{\n\t return \$this->belongsToMany(" . modelNaming($modelName) . "::class);\n}\n\n";
    }

    public function belongsToFunction(string $modelName): string
    {
        $relationName = relationFunctionNaming($modelName);
        return "public function $relationName():belongsTo \n {\n\t return \$this->belongsTo(" . modelNaming($modelName) . "::class); \n}\n\n";
    }

    public function factoryRelationMethod($modelName): string
    {
        $modelName = modelNaming($modelName);
        $functionName = 'with' . ucfirst(Str::plural(Str::studly($modelName)));;
        return "public function {$functionName}(\$count = 1)\n{\n\t return \$this->has(\\" . config('cubeta-starter.model_namespace') . "\\{$modelName}::factory(\$count));\n} \n";
    }
}
