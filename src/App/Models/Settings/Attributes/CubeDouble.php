<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasModelCastColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\CastColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ValidationRuleString;

class CubeDouble extends CubeNumeric implements HasFakeMethod, HasMigrationColumn, HasModelCastColumn, HasPropertyValidationRule
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