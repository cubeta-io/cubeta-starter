<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Strings;

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
                new ImportString("Illuminate\\Database\\Eloquent\\Builder")
            ]
        );
    }
}