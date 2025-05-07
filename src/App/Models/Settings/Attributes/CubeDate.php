<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelCastColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Tests\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Tests\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\InputComponentString;

class CubeDate extends CubeDateable implements HasFakeMethod, HasMigrationColumn, HasModelCastColumn, HasPropertyValidationRule, HasResourcePropertyString, HasTestAdditionalFactoryData, HasBladeInputComponent
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString(
            $this->name,
            "fake()->date()"
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            "date",
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
            "now()->format('Y-m-d')",
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
                'value' => "\${$table?->variableNaming()}->{$this->name}?->format('Y-m-d')"
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
                    "value" => "\${$modelVariable}->{$this->name}?->format('Y-m-d')"
                ],
                [
                    "key" => 'label' ,
                    'value' => $label
                ]
            ]
        );
    }
}