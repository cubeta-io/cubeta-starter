<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;


use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Models\CastColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;

class CubeDouble extends CubeNumeric implements HasFakeMethod, HasMigrationColumn, HasModelCastColumn, HasPropertyValidationRule, HasBladeInputComponent
{
    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            "double",
            $this->nullable,
            $this->unique
        );
    }

    public function modelCastColumn(): CastColumnString
    {
        return new CastColumnString($this->name, "double");
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        return new PropertyValidationRuleString(
            $this->name,
            [
                ...$this->uniqueOrNullableValidationRules(),
                new ValidationRuleString('numeric'),
                new ValidationRuleString('between:-1.7976931348623157E+308,1.7976931348623157E+308')
            ]
        );
    }
}