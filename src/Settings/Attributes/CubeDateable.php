<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\StringValues\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Tests\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Models\CastColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Tests\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;

class CubeDateable extends CubeAttribute implements HasFakeMethod,
    HasMigrationColumn,
    HasDocBlockProperty,
    HasModelCastColumn,
    HasPropertyValidationRule,
    HasResourcePropertyString,
    HasTestAdditionalFactoryData,
    HasBladeInputComponent,
    HasDatatableColumnString,
    HasHtmlTableHeader,
    HasInterfacePropertyString,
    HasReactTsDisplayComponentString,
    HasDataTableColumnObjectString
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

    public function interfacePropertyString(): InterfacePropertyString
    {
        return new InterfacePropertyString(
            $this->name,
            "string",
            $this->nullable,
        );
    }

    public function displayComponentString(): ReactTsDisplayComponentString
    {
        $modelVariable = $this->getOwnerTable()->variableNaming();
        $nullable = $this->nullable ? "?" : "";
        return new ReactTsDisplayComponentString(
            "SmallTextField",
            $this->labelNaming(),
            "{$modelVariable}{$nullable}.{$this->name}",
            [
                new TsImportString("SmallTextField", "@/Components/Show/SmallTextField")
            ]
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