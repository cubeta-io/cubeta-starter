<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Controllers;

use Cubeta\CubetaStarter\App\Models\Settings\Strings\MethodString;
use Cubeta\CubetaStarter\Helpers\Naming;

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