<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Factories;

use Cubeta\CubetaStarter\StringValues\Strings\MethodString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class RoleFactoryMethodString extends MethodString
{
    public function __construct(string $role)
    {
        $methodName = str($role)->studly()->camel()->singular()->toString();
        $caseName = str($role)->studly()->singular()->toString();
        parent::__construct(
            $methodName,
            [],
            "return \$this->afterCreating(function(User \$user){\$user->assignRole(RoleEnum::{$caseName}->value);})",
            "public",
            "UserFactory",
            [
                new PhpImportString("\App\Enums\RoleEnum"),
                new PhpImportString(config('cubeta-starter.model_namespace') . "\\User")
            ]
        );
    }
}