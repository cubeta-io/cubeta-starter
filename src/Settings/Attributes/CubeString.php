<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;

class CubeString extends CubeStringable implements HasBladeInputComponent, HasDataTableColumnObjectString, HasDatatableColumnString, HasFakeMethod, HasHtmlTableHeader, HasMigrationColumn, HasPropertyValidationRule
{
    public function datatableColumnObject(?string $actor = null): DataTableColumnObjectString
    {
        return new DataTableColumnObjectString(
            $this->name,
            $this->labelNaming(),
            false,
            true,
        );
    }

    public function dataTableColumnString(): DataTableColumnString
    {
        return new DataTableColumnString(
            $this->name,
        );
    }

    public function fakeMethod(): FakeMethodString
    {
        $isUnique = $this->unique ? '->unique()' : '';
        $fakeMethod = $this->guessStringMethod();

        return new FakeMethodString(
            $this->name,
            "fake(){$isUnique}->{$fakeMethod}()",
        );
    }

    public function htmlTableHeader(): HtmlTableHeaderString
    {
        return new HtmlTableHeaderString(
            $this->labelNaming(),
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            'string',
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
