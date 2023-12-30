<?php

namespace Cubeta\CubetaStarter\Traits;

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
}
