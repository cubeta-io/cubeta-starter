<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Models\CastColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString as TsxInputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;

class CubeTranslatable extends CubeStringable implements HasFakeMethod,
    HasMigrationColumn,
    HasDocBlockProperty,
    HasModelCastColumn,
    HasPropertyValidationRule,
    HasBladeInputComponent,
    HasDatatableColumnString,
    HasHtmlTableHeader,
    HasReactTsInputString,
    HasReactTsDisplayComponentString,
    HasDataTableColumnObjectString
{
    public function fakeMethod(): FakeMethodString
    {
        $method = $this->guessStringMethod();
        if ($this->isTextable()) {
            $method = "text";
        }

        return new FakeMethodString(
            $this->name,
            "Translatable::fake('$method')",
            new PhpImportString("App\\Serializers\\Translatable")
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            "json",
            $this->nullable,
            $this->unique
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            $this->name,
            "TranslatableSerializer",
            imports: [
                new PhpImportString("\\App\\Serializers\\Translatable as TranslatableSerializer"),
            ]
        );
    }

    public function modelCastColumn(): CastColumnString
    {
        return new CastColumnString(
            $this->name,
            "Translatable::class",
            new PhpImportString("App\\Casts\\Translatable")
        );
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        return new PropertyValidationRuleString(
            $this->name,
            [
                new ValidationRuleString('json'),
                new ValidationRuleString(
                    'new ValidTranslatableJson',
                    [
                        new PhpImportString('App\Rules\ValidTranslatableJson'),
                    ]
                )
            ],
        );
    }


    public function bladeInputComponent(string $formType = "store", ?string $actor = null): InputComponentString
    {
        $attributes = [];
        $table = $this->getOwnerTable() ?? CubeTable::create($this->parentTableName);

        if ($formType == "update") {
            $attributes[] = [
                'key' => ':value',
                'value' => "\${$table?->variableNaming()}->{$this->name}?->toJson()"
            ];
        }

        return new InputComponentString(
            "text",
            $this->isTextable() ? "x-translatable-text-editor" : "x-translatable-input",
            $this->name,
            $this->isRequired,
            $this->titleNaming(),
            $attributes,
        );
    }

    public function bladeDisplayComponent(): DisplayComponentString
    {
        $table = $this->getOwnerTable() ?? CubeTable::create($this->parentTableName);
        $modelVariable = $table->variableNaming();
        $label = $this->labelNaming();
        return new DisplayComponentString(
            $this->isTextable() ? "x-translatable-text-editor" : "x-translatable-small-text-field",
            [
                [
                    "key" => ":value",
                    "value" => "\${$modelVariable}->{$this->name}?->toJson()"
                ],
                [
                    "key" => 'label',
                    'value' => $label
                ]
            ]
        );
    }

    public function dataTableColumnString(): DataTableColumnString
    {
        return new DataTableColumnString(
            $this->name,
            "return translate(data);"
        );
    }

    public function htmlTableHeader(): HtmlTableHeaderString
    {
        return new HtmlTableHeaderString(
            $this->labelNaming(),
        );
    }

    public function inputComponent(string $formType = "store", ?string $actor = null): TsxInputComponentString
    {
        if ($this->isTextable()) {
            $attributes = [
                [
                    'key' => 'onChange',
                    'value' => "(e: ChangeEvent<HTMLTextAreaElement>) => setData(\"{$this->name}\", e.target.value)"
                ]
            ];
            $tag = "TranslatableEditor";
            $imports = [
                new TsImportString("ChangeEvent", "react", false),
                new TsImportString("TranslatableEditor", "@/Components/form/fields/TranslatableEditor")
            ];
        } else {
            $attributes = [
                [
                    'key' => 'onChange',
                    'value' => "(e) => setData(\"{$this->name}\", e.target.value)"
                ]
            ];
            $tag = "TranslatableInput";
            $imports = [
                new TsImportString("TranslatableInput", "@/Components/form/fields/TranslatableInput")
            ];
        }

        if ($formType == "update") {
            $attributes[] = [
                'key' => 'defaultValue',
                'value' => "{$this->getOwnerTable()->variableNaming()}.{$this->name}"
            ];
        }

        return new TsxInputComponentString(
            $tag,
            $this->name,
            $this->labelNaming(),
            $this->isRequired,
            $attributes,
            $imports
        );
    }

    public function displayComponentString(): ReactTsDisplayComponentString
    {
        $modelVariable = $this->getOwnerTable()->variableNaming();
        $nullable = $this->nullable ? "?" : "";
        return new ReactTsDisplayComponentString(
            $this->isTextable() ? "LongTextField" : "SmallTextField",
            $this->labelNaming(),
            "translate({$modelVariable}{$nullable}.{$this->name})",
            [
                $this->isTextable()
                    ? new TsImportString("LongTextField", "@/Components/Show/LongTextField")
                    : new TsImportString("SmallTextField", "@/Components/Show/SmallTextField"),
                new TsImportString("translate", "@/Models/Translatable", false),
            ]
        );
    }

    public function datatableColumnObject(string $actor): DataTableColumnObjectString
    {
        return new DataTableColumnObjectString(
            $this->name,
            $this->labelNaming(),
            true,
            true,
        );
    }
}