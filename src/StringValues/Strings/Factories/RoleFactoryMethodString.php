<?php

namespace Cubeta\CubetaStarter\StringValues\Strings\Factories;

use Cubeta\CubetaStarter\StringValues\Strings\MethodString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;

class RoleFactoryMethodString extends MethodString
{
    public function __construct(string $role)
    {
        $methodName = str($role)->studly()->camel()->singular()->toString();
        $enum = str($role)->snake()->singular()->upper()->toString();
        parent::__construct(
            $methodName,
            [],
            "return \$this->afterCreating(function(User \$user){\$user->assignRole(RolesPermissionEnum::{$enum}['role']);})",
            "public",
            "UserFactory",
            [
                new PhpImportString("\App\Enums\RolesPermissionEnum"),
                new PhpImportString(config('cubeta-starter.model_namespace') . "\\User")
            ]
        );
    }
}