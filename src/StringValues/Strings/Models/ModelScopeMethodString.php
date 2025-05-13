<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Models;

use Cubeta\CubetaStarter\StringValues\Strings\MethodString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class ModelScopeMethodString extends MethodString
{
    public function __construct(string $attributeName)
    {
        $methodName = "scope" . str($attributeName)->studly()->ucfirst()->toString();

        parent::__construct(
            $methodName,
            [
                'query' => "Builder",
            ],
            [
                "return \$query->where('{$attributeName}' , true)"
            ],
            "public",
            "Builder",
            [
                new PhpImportString("Illuminate\\Database\\Eloquent\\Builder")
            ]
        );
    }
}