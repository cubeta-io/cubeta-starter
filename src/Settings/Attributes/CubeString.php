<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;


use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;

class CubeString extends CubeStringable implements HasFakeMethod,
    HasMigrationColumn,
    HasPropertyValidationRule,
    HasBladeInputComponent,
    HasDatatableColumnString,
    HasHtmlTableHeader,
    HasDataTableColumnObjectString
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

    public function dataTableColumnString(): DataTableColumnString
    {
        return new DataTableColumnString(
            $this->name,
        );
    }

    public function htmlTableHeader(): HtmlTableHeaderString
    {
        return new HtmlTableHeaderString(
            $this->labelNaming(),
        );
    }

    public function datatableColumnObject(string $actor): DataTableColumnObjectString
    {
        return new DataTableColumnObjectString(
            $this->name,
            $this->labelNaming(),
            false,
            true,
        );
    }
}