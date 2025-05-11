<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;


use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components\HasInputString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Components\InputComponentString as TsxInputComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\TsImportString;

class CubeText extends CubeStringable implements HasFakeMethod, HasMigrationColumn, HasPropertyValidationRule, HasBladeInputComponent, HasInputString
{
    public function fakeMethod(): FakeMethodString
    {
        $isUnique = $this->unique ? "->unique()" : "";
        return new FakeMethodString(
            $this->name,
            "fake(){$isUnique}->text()"
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        return new MigrationColumnString(
            $this->columnNaming(),
            "text",
            $this->nullable,
            $this->unique
        );
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        return new PropertyValidationRuleString(
            $this->name,
            [
                ...$this->uniqueOrNullableValidationRules(),
                new ValidationRuleString('max:5000'),
                new ValidationRuleString('min:0')
            ]
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
            "number",
            "x-text-editor",
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
            "x-long-text-field",
            [
                [
                    "key" => ":value",
                    "value" => "\${$modelVariable}->{$this->name}"
                ],
                [
                    "key" => 'label',
                    'value' => $label
                ]
            ]
        );
    }

    public function inputComponent(string $formType = "store", ?string $actor = null): TsxInputComponentString
    {
        $attributes = [
            [
                'key' => 'onChange',
                'value' => "(e:ChangeEvent<HTMLTextAreaElement>) => setData(\"{$this->name}\", e.target.value)"
            ],
        ];
        if ($formType == "update") {
            $attributes[] = [
                'key' => 'defaultValue',
                'value' => "{$this->getOwnerTable()->variableNaming()}.{$this->name}"
            ];
        }

        return new TsxInputComponentString(
            "TextEditor",
            $this->name,
            $this->labelNaming(),
            $this->isRequired,
            $attributes,
            [
                new TsImportString("TextEditor", "@/Components/form/fields/TextEditor"),
                new TsImportString("ChangeEvent", "react", false)
            ]
        );
    }
}