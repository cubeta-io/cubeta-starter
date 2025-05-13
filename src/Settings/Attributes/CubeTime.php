<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\StringValues\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Tests\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Tests\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString as TsxInputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;

class CubeTime extends CubeDateable implements HasFakeMethod, HasMigrationColumn, HasPropertyValidationRule, HasResourcePropertyString, HasTestAdditionalFactoryData, HasBladeInputComponent,HasReactTsInputString
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString(
            $this->name,
            "fake()->time()"
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            "time",
            $this->nullable,
            $this->unique,
        );
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        return new PropertyValidationRuleString(
            $this->name,
            [
                ...$this->uniqueOrNullableValidationRules(),
                new ValidationRuleString('date_format:H:i'),
            ]
        );
    }

    public function resourcePropertyString(): ResourcePropertyString
    {
        return new ResourcePropertyString(
            $this->name,
            "\$this->{$this->name}?->format('H:i')"
        );
    }

    public function testAdditionalFactoryData(): TestAdditionalFactoryDataString
    {
        return new TestAdditionalFactoryDataString(
            $this->name,
            "now()->format('H:i')",
            [],
        );
    }


    public function bladeInputComponent(string $formType = "store", ?string $actor = null): InputComponentString
    {
        $attributes = [];
        $table = $this->getOwnerTable() ?? CubeTable::create($this->parentTableName);

        if ($formType == "update") {
            $attributes[] = [
                'key' => ':value',
                'value' => "\${$table?->variableNaming()}->{$this->name}?->format('H:i')"
            ];
        }

        return new InputComponentString(
            "time",
            "x-input",
            $this->name,
            $this->isRequired,
            $this->titleNaming(),
            $attributes
        );
    }

    public function bladeDisplayComponent(): DisplayComponentString
    {
        $table = $this->getOwnerTable() ?? CubeTable::create($this->parentTableName);
        $modelVariable = $table->variableNaming();
        $label = $this->labelNaming();
        return new DisplayComponentString(
            "x-small-text-field",
            [
                [
                    "key" => ":value",
                    "value" => "\${$modelVariable}->{$this->name}?->format('H:i')"
                ],
                [
                    "key" => 'label' ,
                    'value' => $label
                ]
            ]
        );
    }

    public function inputComponent(string $formType = "store", ?string $actor = null): TsxInputComponentString
    {
        $attributes = [
            [
                'key' => 'type',
                'value' => '"time"'
            ],
            [
                'key' => 'onChange',
                'value' => "(e) => setData(\"{$this->name}\", e.target?.value)"
            ]
        ];

        if ($formType == "update") {
            $attributes[] = [
                'key' => 'defaultValue',
                'value' => "{$this->getOwnerTable()->variableNaming()}.{$this->name}"
            ];
        }

        return new TsxInputComponentString(
            "Input",
            $this->name,
            $this->labelNaming(),
            $this->isRequired,
            $attributes,
            [
                new TsImportString("Input", "@/Components/form/fields/Input")
            ]
        );
    }
}