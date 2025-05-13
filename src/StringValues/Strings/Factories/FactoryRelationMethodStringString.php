<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Factories;

use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Strings\MethodString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class FactoryRelationMethodStringString extends MethodString
{
    public function __construct(string $relatedModelName)
    {
        $model = CubeTable::create($relatedModelName);
        $relatedModelName = str($relatedModelName)->plural()->studly()->toString();
        parent::__construct(
            "with$relatedModelName",
            [
                'count = 1' => 'int'
            ],
            "return \$this->has({$model->modelName}::factory(\$count));",
            returnType: "self",
            imports: [
                new PhpImportString($model->getModelNameSpace(false))
            ],
        );
    }
}