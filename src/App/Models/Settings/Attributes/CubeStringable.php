<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ValidationRuleString;
use Illuminate\Support\Str;

class CubeStringable extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty, HasPropertyValidationRule
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
}