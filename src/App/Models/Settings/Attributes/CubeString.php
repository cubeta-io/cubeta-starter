<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;


use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\InputComponentString;

class CubeString extends CubeStringable implements HasFakeMethod, HasMigrationColumn, HasPropertyValidationRule, HasBladeInputComponent
{
    public function fakeMethod(): FakeMethodString
    {
        $isUnique = $this->unique ? "->unique()" : "";
        $fakeMethod = $this->guessStringMethod();
        return new FakeMethodString(
            $this->name,
            "fake(){$isUnique}->{$fakeMethod}()",
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            "string",
            $this->nullable,
            $this->unique
        );
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        $rules = [
            ...$this->uniqueOrNullableValidationRules(),
            new ValidationRuleString('string'),
            new ValidationRuleString('max:255'),
        ];

        if ($this->isEmail()) {
            $rules[] = new ValidationRuleString('email');
            $rules[] = new ValidationRuleString('min:6');
        } elseif ($this->isPassword()) {
            $rules[] = new ValidationRuleString('confirmed');
            $rules[] = new ValidationRuleString('min:8');
        } else {
            $rules[] = new ValidationRuleString('min:3');
        }

        return new PropertyValidationRuleString(
            $this->name,
            $rules
        );
    }
}