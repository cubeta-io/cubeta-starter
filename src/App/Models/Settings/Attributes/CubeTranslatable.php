<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PhpImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\CastColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Javascript\DataTableColumnString;

class CubeTranslatable extends CubeStringable implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty, HasModelCastColumn, HasPropertyValidationRule, HasBladeInputComponent, HasDatatableColumnString, HasHtmlTableHeader
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
}