<?php

namespace Cubeta\CubetaStarter\App\Models\Settings\Relations;

use Cubeta\CubetaStarter\App\Models\Settings\Attributes\CubeKey;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Models\HasModelRelationMethod;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\DocBlockPropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Models\ModelRelationString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\PhpImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Resources\ResourcePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Components\ReactTsDisplayComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Components\ReactTsInputComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Typescript\DataTableColumnObjectString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\RouteBinding;


class CubeBelongsTo extends CubeRelation implements HasModelRelationMethod,
    HasDocBlockProperty,
    HasResourcePropertyString,
    HasInterfacePropertyString,
    HasReactTsInputString,
    HasReactTsDisplayComponentString,
    HasDataTableColumnObjectString
{
    use RouteBinding;

    public function modelRelationMethod(): ModelRelationString
    {
        return new ModelRelationString(
            $this->modelName,
            RelationsTypeEnum::BelongsTo,
        );
    }

    public function docBlockProperty(): DocBlockPropertyString
    {
        return new DocBlockPropertyString(
            str($this->modelName)->singular()->lower()->toString(),
            "$this->modelName|null",
            imports: new PhpImportString($this->getModelNameSpace())
        );
    }

    public function resourcePropertyString(): ResourcePropertyString
    {
        return new ResourcePropertyString(
            str($this->modelName)->singular()->snake()->lower()->toString(),
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
        $relatedModel = $this->getRelatedModel();
        $column = $relatedModel
            ->attributes()
            ->filter(fn($att) => $att->isKey() && $att->modelNaming() == $modelName)
            ->first() ?? CubeKey::factory($this->key, ColumnTypeEnum::KEY->value, parentTableName: $relatedModel->tableNaming());
        $dataRoute = $this->getRouteNames(CubeTable::create($modelName), ContainerType::WEB, $actor)["data"];

        $attributes = [
            [
                'key' => 'api',
                'value' => "(page, search): Promise<ApiResponse<{$modelName}[]>> => Http.make().get(route(\"$dataRoute\"),{page:page,search:search})"
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
        $parentModel = $this->getRelatedModel();
        $column = $this->getTable()->titleable();
        $imports = [
            new TsImportString("SmallTextField", "@/Components/Show/SmallTextField"),
        ];

        if ($column->isTranslatable()) {
            $imports[] = new TsImportString("translate", "@/Models/Translatable");
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
        $column = $this->getTable()->titleable();
        $showRoute = $this->getRouteNames($this->getRelatedModel(), ContainerType::WEB, $actor)['show'];
        $viewValue = $column->isTranslatable() ? "translate(record?.{$column->name})" : "record?.{$column->name}";

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
            true,
            "return (<Link className=\"hover:text-primary underline\"
                        href={route(\"$showRoute\" , record.id)}>
                        {{$viewValue}}
                    </Link>)",
            $imports,
        );
    }
}