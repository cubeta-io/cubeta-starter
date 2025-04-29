<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;

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
                new ImportString($model->getModelNameSpace(false))
            ],
        );
    }
}