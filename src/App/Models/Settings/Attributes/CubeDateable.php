<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasModelCastColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasTestAdditionalFactoryData;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\CastColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\TestAdditionalFactoryDataString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ValidationRuleString;

class CubeDateable extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasDocBlockProperty, HasModelCastColumn, HasPropertyValidationRule, HasResourcePropertyString, HasTestAdditionalFactoryData
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
            new ImportString("Carbon\Carbon")
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
            "\$this->{$this->name}?->format('Y-m-d H:i')"
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
}