<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Controllers;

use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\StringValues\Strings\MethodString;

class AllPaginatedJsonMethodString extends MethodString
{
    public function __construct(string $modelName)
    {
        $modelName = Naming::model($modelName);
        $variableName = str($modelName)->camel()->singular();
        $serviceName = "{$variableName}Service";
        parent::__construct("allPaginatedJson", [],
            [
                "\${$variableName} = \$this->{$serviceName}->indexWithPagination([], 7);",
                "return response()->json([
                    'data' => \${$variableName}?->items(),
                    'pagination_data' => \$this->paginationData(\${$variableName})
                ], 200);"
            ]
        );
    }
}