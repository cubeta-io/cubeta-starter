<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ValidationRuleString;

class CubeTime extends CubeDateable implements HasFakeMethod, HasMigrationColumn, HasPropertyValidationRule, HasResourcePropertyString, HasTestAdditionalFactoryData
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
}