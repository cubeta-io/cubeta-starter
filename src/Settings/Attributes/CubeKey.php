<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use Illuminate\Support\Str;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Contracts\Dtos\HasDtoCast;
use Cubeta\CubetaStarter\StringValues\Strings\Dtos\DtoCastString;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Dtos\DtoPropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;

class CubeKey extends CubeAttribute implements HasDocBlockProperty, HasDtoCast, HasFakeMethod, HasInterfacePropertyString, HasMigrationColumn, HasPropertyValidationRule
{
    use RouteBinding;

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            $this->name,
            str('integer')
                ->when($this->nullable, fn ($str) => $str->append('|null'))
                ->toString(),
        );
    }

    public function dtoCast(): DtoCastString
    {
        return new DtoCastString(
            $this->name,
            'new IntegerCast()',
            [new PhpImportString('WendellAdriel\ValidatedDTO\Casting\IntegerCast')]
        );
    }

    public function dtoProperty(): DtoPropertyString
    {
        return new DtoPropertyString(
            $this->name,
            'int',
            $this->nullable
        );
    }

    public function fakeMethod(): FakeMethodString
    {
        $relatedModel = CubeTable::create($this->modelNaming());

        return new FakeMethodString(
            $this->name,
            "{$relatedModel->modelName}::factory()",
            new PhpImportString($relatedModel->getModelNameSpace(false))
        );
    }

    public function interfacePropertyString(): InterfacePropertyString
    {
        return new InterfacePropertyString(
            $this->name,
            'number',
            $this->nullable,
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        $relatedModel = CubeTable::create($this->modelNaming());

        return new MigrationColumnString(
            "{$relatedModel->modelName}::class",
            'foreignIdFor',
            $this->nullable,
            $this->unique,
            true,
            new PhpImportString($relatedModel->getModelNameSpace(false))
        );
    }

    public function modelNaming(?string $name = null): string
    {
        if ($name) {
            return Naming::model($name);
        }

        return str($this->name)->replace('_id', '')->singular()->studly()->ucfirst()->toString();
    }

    public function propertyValidationRule(): PropertyValidationRuleString
    {
        return new PropertyValidationRuleString(
            $this->name,
            [
                new ValidationRuleString('numeric'),
                ...$this->uniqueOrNullableValidationRules(),
                new ValidationRuleString(
                    "Rule::exists('{$this->tableNaming()}' , 'id')",
                    [
                        new PhpImportString('Illuminate\Validation\Rule'),
                    ]
                ),
            ]
        );
    }

    public function tableNaming(?string $name = null): string
    {
        if ($name) {
            return Naming::table($name);
        }

        return str($this->name)->replace('_id', '')->snake()->plural()->toString();
    }

    public function titleNaming(?string $name = null): string
    {
        return $name
            ? Str::headline($name)
            : Str::headline(str_replace('_id', '', $this->name));
    }
}
