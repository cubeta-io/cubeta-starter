<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString as TsxInputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Illuminate\Support\Str;

class CubeStringable extends CubeAttribute implements
    HasFakeMethod,
    HasMigrationColumn,
    HasDocBlockProperty,
    HasPropertyValidationRule,
    HasBladeInputComponent,
    HasInterfacePropertyString,
    HasReactTsInputString,
    HasReactTsDisplayComponentString
{
    public function fakeMethod(): FakeMethodString
    {
        return new FakeMethodString($this->name, "fake()->word()");
    }

    protected function guessStringMethod(): string
    {
        $name = $this->name;
        if (Str::contains($name, 'phone')) {
            $name = 'phone';
        } elseif (Str::contains($name, ['latitude ', '_lat', 'lat_']) || $name == 'lat' || $name == 'latitude') {
            $name = 'lat';
        } elseif (Str::contains($name, ['longitude', '_lon', '_lng', 'lon_', 'lng_']) || $name == 'lng' || $name == 'lon' || $name == 'longitude') {
            $name = 'lng';
        } elseif (Str::contains($name, 'address')) {
            $name = 'address';
        } elseif (Str::contains($name, 'street')) {
            $name = 'street';
        } elseif (Str::contains($name, 'city')) {
            $name = 'city';
        } elseif (Str::contains($name, 'country')) {
            $name = 'country';
        } elseif (Str::contains($name, ['zip', 'post_code', 'postcode', 'PostCode', 'postCode', 'ZIP'])) {
            $name = 'postcode';
        } elseif (Str::contains($name, 'gender')) {
            $name = 'gender';
        }

        return match ($name) {
            'name', 'username', 'first_name', 'last_name', 'user_name' => "firstName",
            'email' => "email",
            'phone' => "phoneNumber",
            'lat' => "latitude",
            'lng' => "longitude",
            'address' => "address",
            'street' => "streetName",
            'city' => "city",
            'country' => "country",
            'postcode' => "postcode",
            'gender' => "randomElement(['male' , 'female'])",
            default => "word"
        };
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

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString($this->name, "string");
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        return new PropertyValidationRuleString(
            $this->name,
            [
                ...$this->uniqueOrNullableValidationRules(),
                new ValidationRuleString('string'),
            ]
        );
    }

    public function isPassword(): bool
    {
        return str($this->name)->contains('password');
    }

    public function isEmail(): bool
    {
        return str($this->name)->contains('email');
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

        $type = $this->getInputType();

        return new InputComponentString(
            $type,
            "x-input",
            $this->name,
            $this->isRequired,
            $this->titleNaming(),
            $attributes
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

    public function inputComponent(string $formType = "store", ?string $actor = null): TsxInputComponentString
    {
        $attributes = [
            [
                'key' => 'type',
                'value' => "'{$this->getInputType()}'",
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

    protected function getInputType(): string
    {
        if (str_contains($this->name, "email")) {
            $type = "email";
        } elseif (str_contains($this->name, "password")) {
            $type = "password";
        } elseif (str($this->name)->contains(['phone', 'phone_number', 'home_number', 'work_number', 'tel', 'telephone'])) {
            $type = "tel";
        } elseif (str_contains($this->name, "url")) {
            $type = "url";
        } else {
            $type = "text";
        }
        return $type;
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
}