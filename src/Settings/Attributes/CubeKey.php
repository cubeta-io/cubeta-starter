<?php

namespace Cubeta\CubetaStarter\Settings\Attributes;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Factories\HasFakeMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Migrations\HasMigrationColumn;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Requests\HasPropertyValidationRule;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Controllers\HasYajraDataTableRelationLinkColumnRenderer;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\Naming;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Factories\FakeMethodString;
use Cubeta\CubetaStarter\StringValues\Strings\Migrations\MigrationColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\PropertyValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Requests\ValidationRuleString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Controllers\YajraDataTableRelationLinkColumnRenderer;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Support\Str;

class CubeKey extends CubeAttribute implements HasFakeMethod, HasMigrationColumn, HasPropertyValidationRule, HasDocBlockProperty, HasYajraDataTableRelationLinkColumnRenderer, HasBladeInputComponent, HasDatatableColumnString, HasHtmlTableHeader,HasInterfacePropertyString
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
            new PhpImportString($relatedModel->getModelNameSpace(false))
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
            new PhpImportString($relatedModel->getModelNameSpace(false))
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
                        new PhpImportString('Illuminate\Validation\Rule')
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
                ],
                $model->titleable()->isTranslatable() ? [
                    'key' => 'translatable',
                    'value' => null
                ] : [
                    'key' => '',
                    'value' => null
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

    public function bladeDisplayComponent(): DisplayComponentString
    {
        $table = $this->getOwnerTable() ?? CubeTable::create($this->parentTableName);
        $modelVariable = $table->variableNaming();
        $label = $this->labelNaming();
        $related = Settings::make()->getTable($this->modelNaming()) ?? CubeTable::create($this->modelNaming());
        $column = $related->titleable();
        $relationName = $related->relationMethodNaming();
        if (ClassUtils::isMethodDefined($table->getModelPath(), $relationName)) {
            return new DisplayComponentString(
                $column->isTranslatable() ? "x-translatable-small-text-field" : "x-small-text-field",
                [
                    [
                        "key" => ":value",
                        "value" => $column->isTranslatable()
                            ? "\${$modelVariable}->{$relationName}?->{$column->name}?->toJson()"
                            : "\${$modelVariable}->{$relationName}?->{$column->name}"
                    ],
                    [
                        "key" => 'label',
                        'value' => $label
                    ]
                ]
            );
        } else {
            return parent::bladeDisplayComponent();
        }
    }

    public function interfacePropertyString(): InterfacePropertyString
    {
        return new InterfacePropertyString(
            $this->name ,
            "number",
            $this->nullable,
        );
    }
}