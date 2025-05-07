<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Controllers\HasYajraDataTableRelationLinkColumnRenderer;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\ImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Controllers\YajraDataTableRelationLinkColumnRenderer;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Str;

class CubeKey extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasPropertyValidationRule, HasDocBlockProperty, HasYajraDataTableRelationLinkColumnRenderer, HasBladeInputComponent, HasDatatableColumnString, HasHtmlTableHeader
{
    use RouteBinding;

    public function tableNaming(?string $name = null): string
    {
        if ($name) {
            return Naming::table($name);
        }

        return str($this->name)->replace('_id', '')->snake()->plural()->toString();
    }

    public function modelNaming(?string $name = null): string
    {
        if ($name) {
            return Naming::model($name);
        }

        return str($this->name)->replace('_id', '')->singular()->studly()->ucfirst()->toString();
    }

    public function titleNaming(?string $name = null): string
    {
        return $name
            ? Str::headline($name)
            : Str::headline(str_replace('_id', '', $this->name));
    }

    public function fakeMethod(): FakeMethodString
    {
        $relatedModel = CubeTable::create($this->modelNaming());

        return new FakeMethodString(
            $this->name,
            "{$relatedModel->modelName}::factory()",
            new ImportString($relatedModel->getModelNameSpace(false))
        );
    }

    public function migrationColumn(): MigrationColumnString
    {
        $relatedModel = CubeTable::create($this->modelNaming());
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
        return new PropertyValidationRuleString(
            $this->name,
            [
                new ValidationRuleString('numeric'),
                ...$this->uniqueOrNullableValidationRules(),
                new ValidationRuleString(
                    "Rule::exists('{$this->tableNaming()}' , 'id')",
                    [
                        new ImportString('Illuminate\Validation\Rule')
                    ]
                ),
            ]
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            $this->name,
            'integer',
        );
    }

    public function yajraDataTableAdditionalColumnRenderer(string $actor): YajraDataTableRelationLinkColumnRenderer
    {
        return new YajraDataTableRelationLinkColumnRenderer($this->name, $actor);
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
        $model = Settings::make()->getTable($this->modelNaming()) ?? CubeTable::create($this->modelNaming());
        $select2Route = $this->getRouteNames($model, ContainerType::WEB, $actor)["all_paginated_json"];

        return new InputComponentString(
            "number",
            "x-select2",
            $this->name,
            $this->isRequired,
            $this->titleNaming(),
            [
                ...$attributes,
                [
                    'key' => 'api',
                    'value' => "{{route('{$select2Route}')}}"
                ],
                [
                    'key' => 'option-value',
                    'value' => 'id'
                ],
                [
                    'key' => 'option-inner-text',
                    'value' => $model->titleable()->name,
                ]
            ]
        );
    }

    public function dataTableColumnString(): DataTableColumnString
    {
        $relatedModelName = $this->modelNaming();
        $relatedTable = Settings::make()->getTable($relatedModelName) ?? CubeTable::create($relatedModelName);
        $column = $relatedTable->titleable();

        if ($column->isTranslatable()) {
            $render = "return translate(data);";
        }

        $usedName = $relatedTable->relationMethodNaming() . "." . $column->name;
        return new DataTableColumnString(
            $usedName,
            $render ?? null
        );
    }

    public function htmlTableHeader(): HtmlTableHeaderString
    {
        return new HtmlTableHeaderString(
            $this->labelNaming(),
        );
    }
}