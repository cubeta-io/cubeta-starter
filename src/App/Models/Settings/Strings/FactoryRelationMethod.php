<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;

class FactoryRelationMethod extends Method
{
    public function __construct(string $relatedModelName)
    {
        $model = CubeTable::create($relatedModelName);
        $relatedModelName = str($relatedModelName)->plural()->studly()->toString();
        parent::__construct(
            "with$relatedModelName",
            [
                'int' => 'count = 1'
            ],
            "return \$this->has({$model->getModelNameSpace()}::factory(\$count));",
        );
    }
}