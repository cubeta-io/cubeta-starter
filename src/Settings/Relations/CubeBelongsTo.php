<?php

namespace Cubeta\CubetaStarter\Settings\Relations;

use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Settings\Attributes\CubeKey;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelRelationMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeDisplayComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Controllers\HasYajraDataTableRelationLinkColumnRenderer;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Models\ModelRelationString;
use Cubeta\CubetaStarter\StringValues\Strings\PhpImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\DisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\HtmlTableHeaderString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\InputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Controllers\YajraDataTableRelationLinkColumnRenderer;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Javascript\DataTableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\ReactTsInputComponentString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\Traits\RouteBinding;


class CubeBelongsTo extends CubeRelation implements HasModelRelationMethod,
    HasDocBlockProperty,
    HasResourcePropertyString,
    HasInterfacePropertyString,
    HasReactTsInputString,
    HasReactTsDisplayComponentString,
    HasDataTableColumnObjectString,
    HasBladeInputComponent,
    HasDatatableColumnString,
    HasHtmlTableHeader,
    HasBladeDisplayComponent,
    HasYajraDataTableRelationLinkColumnRenderer
{
    use RouteBinding;

    public function modelRelationMethod(): ModelRelationString
    {
        return new ModelRelationString(
            $this->relationModel,
            RelationsTypeEnum::BelongsTo,
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            str($this->relationModel)->singular()->lower()->toString(),
            "$this->relationModel|null",
            imports: new PhpImportString($this->getModelNameSpace())
        );
    }

    public function resourcePropertyString(): ResourcePropertyString
    {
        return new ResourcePropertyString(
            str($this->relationModel)->singular()->snake()->lower()->toString(),
            "{$this->getResourceName()}::make(\$this->whenLoaded('{$this->relationMethodNaming()}'))",
            [
                new PhpImportString($this->getResourceNameSpace(false))
            ]
        );
    }

    public function interfacePropertyString(): InterfacePropertyString
    {
        $modelName = $this->modelNaming();
        return new InterfacePropertyString(
            $this->relationMethodNaming(),
            $modelName,
            true,
            new TsImportString(
                $modelName,
                "@/Models/{$modelName}"
            )
        );
    }

    public function inputComponent(string $formType = "store", ?string $actor = null): ReactTsInputComponentString
    {
        $modelName = $this->modelNaming();
        $relatedModel = $this->parentModel();
        $column = $relatedModel
            ->attributes()
            ->filter(fn($att) => $att->isKey() && $att->modelNaming() == $modelName)
            ->first() ?? CubeKey::factory($this->key, ColumnTypeEnum::KEY->value, parentTableName: $relatedModel->tableNaming());
        $dataRoute = $this->getRouteNames(CubeTable::create($modelName), ContainerType::WEB, $actor)["data"];

        $attributes = [
            [
                'key' => 'api',
                'value' => "(page, search) => Http.make<{$modelName}[]>().get(route(\"$dataRoute\"),{page:page,search:search})"
            ],
            [
                'key' => 'getDataArray',
                'value' => '(response) => response?.data ?? []',
            ],
            [
                'key' => 'getIsLast',
                'value' => '(data) => data?.paginate?.is_last_page ?? false',
            ],
            [
                'key' => 'getTotalPages',
                'value' => '(data) => data?.paginate?.total_pages ?? 0',
            ],
            [
                'key' => 'onChange',
                'value' => "(e) => setData(\"$this->key\", Number(e.target.value))",
            ],
            $relatedModel->titleable()->isTranslatable()
                ? [
                'key' => 'getOptionLabel',
                'value' => "(data) => translate(data.{$relatedModel->titleable()->name})"
            ] : [
                'key' => 'optionLabel',
                'value' => "{$relatedModel->titleable()->name}"
            ],
            [
                'key' => 'optionValue',
                'value' => '"id"',
            ],
        ];

        if ($formType == "update") {
            $attributes[] = [
                'key' => 'defaultValue',
                'value' => "{$relatedModel->variableNaming()}?.{$this->method()}"
            ];
        }

        $imports = [
            new TsImportString("ApiResponse", "@/Modules/Http/ApiResponse"),
            new TsImportString("Http", "@/Modules/Http/Http"),
            new TsImportString($modelName, "@/Models/{$modelName}"),
            new TsImportString("ApiSelect", "@/Components/form/fields/Select/ApiSelect"),
        ];

        if ($relatedModel->titleable()->isTranslatable()) {
            $imports[] = new TsImportString("translate", "@/Models/Translatable", false);
        }

        return new ReactTsInputComponentString(
            "ApiSelect",
            $this->key,
            $modelName,
            $column->isRequired,
            $attributes,
            $imports
        );
    }

    public function displayComponentString(): ReactTsDisplayComponentString
    {
        $parentModel = $this->parentModel();
        $column = $this->relationModel()->titleable();
        $imports = [
            new TsImportString("SmallTextField", "@/Components/Show/SmallTextField"),
        ];

        if ($column->isTranslatable()) {
            $imports[] = new TsImportString("translate", "@/Models/Translatable", false);
        }

        return new ReactTsDisplayComponentString(
            "SmallTextField",
            $this->titleNaming(),
            $column->isTranslatable()
                ? "translate(" . $parentModel->variableNaming() . "?." . $this->relationMethodNaming() . "?." . $column->name . ")"
                : $parentModel->variableNaming() . "?." . $this->relationMethodNaming() . "?." . $column->name,
            $imports,
        );
    }

    public function datatableColumnObject(string $actor): DataTableColumnObjectString
    {
        $column = $this->relationModel()->titleable();
        $showRoute = $this->getRouteNames($this->relationModel(), ContainerType::WEB, $actor)['show'];
        $viewValue = $column->isTranslatable()
            ? "translate(record?.{$this->method()}?.{$column->name})"
            : "record?.{$this->method()}?.{$column->name}";

        $imports = [
            new TsImportString("Link", "@inertiajs/react", false)
        ];

        if ($column->isTranslatable()) {
            $imports[] = new TsImportString("translate", "@/Models/Translatable", false);
        }

        return new DataTableColumnObjectString(
            $this->relationMethodNaming() . "." . $column->name,
            "{$this->modelNaming()} {$column->titleNaming()}",
            $column->isTranslatable(),
            false,
            "return (<Link className=\"hover:text-primary underline\"
                        href={route(\"$showRoute\" , record?.{$this->key})}>
                        {{$viewValue}}
                    </Link>)",
            $imports,
        );
    }

    public function bladeInputComponent(string $formType = "store", ?string $actor = null): InputComponentString
    {
        $attributes = [];
        $table = $this->parentModel();

        if ($formType == "update") {
            $attributes[] = [
                'key' => ':selected',
                'value' => "\${$table->variableNaming()}->{$this->method()}"
            ];
        }

        $relatedMode = $this->relationModel();
        $select2Route = $this->getRouteNames($relatedMode, ContainerType::WEB, $actor)["all_paginated_json"];

        return new InputComponentString(
            "number",
            "x-select2",
            $this->keyName(),
            !($table->getAttribute($this->keyName())?->nullable === true),
            $relatedMode->titleNaming(),
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
                    'value' => $relatedMode->titleable()->name,
                ],
                $relatedMode->titleable()->isTranslatable() ? [
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
        $relatedTable = $this->relationModel();
        $column = $relatedTable->titleable();

        $usedName = $this->method() . "." . $column->name;
        return new DataTableColumnString(
            $usedName,
        );
    }

    public function htmlTableHeader(): HtmlTableHeaderString
    {
        return new HtmlTableHeaderString(
            $this->titleNaming(),
        );
    }

    public function bladeDisplayComponent(): DisplayComponentString
    {
        $table = $this->parentModel();
        $related = $this->relationModel();

        $label = $this->titleNaming();
        $column = $related->titleable();
        $relationName = $related->relationMethodNaming();

        $modelVariable = $table->variableNaming();

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
    }

    public function yajraDataTableAdditionalColumnRenderer(string $actor): YajraDataTableRelationLinkColumnRenderer
    {
        return new YajraDataTableRelationLinkColumnRenderer($this->key, $actor);
    }
}