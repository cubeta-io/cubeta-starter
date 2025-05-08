<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Tests\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PhpImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\CastColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Tests\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Javascript\DataTableColumnString;

class CubeDateable extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty, HasModelCastColumn, HasPropertyValidationRule, HasResourcePropertyString, HasTestAdditionalFactoryData, HasBladeInputComponent,HasDatatableColumnString,HasHtmlTableHeader
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString(
            $this->name,
            "fake()->dateTime()",
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            "dateTime",
            $this->nullable,
            $this->unique
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            $this->name,
            "Carbon",
            "property",
            new PhpImportString("Carbon\Carbon")
        );
    }

    public function modelCastColumn(): CastColumnString
    {
        return new CastColumnString($this->name, "datetime");
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        return new PropertyValidationRuleString(
            $this->name,
            [
                ...$this->uniqueOrNullableValidationRules(),
                new ValidationRuleString('date'),
                new ValidationRuleString('date_format:Y-m-d'),
            ]
        );
    }

    public function resourcePropertyString(): ResourcePropertyString
    {
        return new ResourcePropertyString(
            $this->name,
            "\$this->{$this->name}?->format('Y-m-d')"
        );
    }

    public function testAdditionalFactoryData(): TestAdditionalFactoryDataString
    {
        return new TestAdditionalFactoryDataString(
            $this->name,
            'now()->format("Y-m-d")',
            []
        );
    }


    public function bladeInputComponent(string $formType = "store", ?string $actor = null): InputComponentString
    {
        $attributes = [];
        $table = $this->getOwnerTable() ?? CubeTable::create($this->parentTableName);

        if ($formType == "update") {
            $attributes[] = [
                'key' => ':value',
                'value' => "\${$table?->variableNaming()}->{$this->name}"
            ];
        }

        return new InputComponentString(
            "date",
            "x-input",
            $this->name,
            $this->isRequired,
            $this->titleNaming(),
            $attributes,
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
}