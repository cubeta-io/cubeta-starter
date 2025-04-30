<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ValidationRuleString;

class CubeKey extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasPropertyValidationRule
{
    public function fakeMethod(): FakeMethodString
    {
        $relatedModel = CubeTable::create(str_replace('_id', '', $this->name));

        return new FakeMethodString(
            $this->name,
            "{$relatedModel->modelName}::factory()",
            new ImportString($relatedModel->getModelNameSpace(false))
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        $relatedModel = CubeTable::create(str_replace('_id', '', $this->name));
        return new MigrationColumnString(
            "{$relatedModel->modelName}::class",
            "foreignIdFor",
            $this->nullable,
            $this->unique,
            true,
            new ImportString($relatedModel->getModelNameSpace(false))
        );
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        $relatedTableName = str($this->name)->replace('_id', '')->snake()->plural()->toString();

        return new PropertyValidationRuleString(
            $this->name,
            [
                new ValidationRuleString('numeric'),
                ...$this->uniqueOrNullableValidationRules(),
                new ValidationRuleString(
                    "Rule::exists('{$relatedTableName}' , 'id')",
                    [
                        new ImportString('Illuminate\Validation\Rule')
                    ]
                ),
            ]
        );
    }
}