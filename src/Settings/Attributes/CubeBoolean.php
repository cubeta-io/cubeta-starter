<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelScopeMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Models\CastColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Models\ModelScopeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString as TsInputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;

class CubeBoolean extends CubeAttribute implements HasFakeMethod,
    HasMigrationColumn,
    HasDocBlockProperty,
    HasModelCastColumn,
    HasModelScopeMethod,
    HasPropertyValidationRule,
    HasBladeInputComponent,
    HasHtmlTableHeader,
    HasDatatableColumnString,
    HasInterfacePropertyString,
    HasReactTsInputString,
    HasReactTsDisplayComponentString,
    HasDataTableColumnObjectString
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString(
            $this->name,
            "fake()->boolean()"
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            "boolean",
            $this->nullable,
            $this->unique,
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            $this->name,
            str("boolean")
                ->when($this->nullable, fn($str) => $str->append("|null"))
                ->toString()
        );
    }

    public function modelCastColumn(): CastColumnString
    {
        return new CastColumnString($this->name, "boolean");
    }

    public function modelScopeMethod(): ModelScopeMethodString
    {
        return new ModelScopeMethodString($this->name);
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        return new PropertyValidationRuleString(
            $this->name,
            [
                ...$this->uniqueOrNullableValidationRules(),
                new ValidationRuleString('boolean'),
            ]
        );
    }

    public function bladeInputComponent(string $formType = "store", ?string $actor = null): InputComponentString
    {
        $attributes = [];
        $table = $this->getOwnerTable() ?? CubeTable::create($this->parentTableName);

        if ($formType == "update") {
            $attributes[] = [
                'key' => ":checked",
                'value' => "\${$table?->variableNaming()}->{$this->name}"
            ];
        } else {
            $attributes[] = [
                'key' => 'checked',
                'value' => null
            ];
        }

        return new InputComponentString(
            "radio",
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
            "if(data){return \"<i class='bi bi-check-circle-fill text-success'></i>\";}return \"<i class='bi bi-x-circle-fill text-danger'></i>\";"
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
            "boolean",
            $this->nullable,
        );
    }

    public function inputComponent(string $formType = "store", ?string $actor = null): TsInputComponentString
    {
        $variableName = $this->getOwnerTable()->variableNaming();
        $labels = $this->booleanLabels();
        $attributes = [
            [
                'key' => 'items',
                'value' => "[{label:\"{$labels['true']}\" , value:true}, {label:\"{$labels['false']}\" , value:false}]"
            ],
            [
                'key' => 'onChange',
                'value' => "(e) => setData(\"{$this->name}\" , e.target.value == \"true\")"
            ]
        ];

        if ($formType == "update") {
            $attributes[] = [
                'key' => 'checked',
                'value' => "(val: any) => val == $variableName.{$this->name}"
            ];
        }

        return new TsInputComponentString(
            "Radio",
            $this->name,
            required: false,
            attributes: $attributes,
            imports: [
                new TsImportString(
                    "Radio",
                    "@/Components/form/fields/Radio"
                )
            ]
        );
    }

    public function displayComponentString(): ReactTsDisplayComponentString
    {
        $modelVariable = $this->getOwnerTable()->variableNaming();
        $nullable = $this->nullable ? "?" : "";
        return new ReactTsDisplayComponentString(
            "SmallTextField",
            $this->labelNaming(),
            "{$modelVariable}{$nullable}.{$this->name} ? 'Yes' : 'No'",
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
            "return cell ? (<span>Yes</span>) : (<span>No</span>)"
        );
    }
}