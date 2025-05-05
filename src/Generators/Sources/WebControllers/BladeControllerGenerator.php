<?php

namespace Cubeta\CubetaStarter\Generators\Sources\WebControllers;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\Blade\Controllers\HasYajraDataTableRelationLinkColumnRenderer;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Components\SidebarItemString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Controllers\AllPaginatedJsonMethodString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Controllers\TranslatableColumnDataTableColumnOrderingOptionsArrayString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\Blade\Controllers\YajraDataTableTranslatableColumnOrderingHandler;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators\BladeViewsGenerator;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Controllers\ControllerStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;
use JetBrains\PhpStorm\ArrayShape;

class BladeControllerGenerator extends AbstractGenerator
{
    use RouteBinding, WebGeneratorHelper;

    public static string $key = 'web-controller';

    public function run(bool $override = false): void
    {
        if (!Settings::make()->getFrontendType() == FrontendTypeEnum::BLADE) {
            CubeLog::add(new CubeError("Install blade tools by running [php artisan cubeta:install web && php artisan cubeta:install web-packages] then try again", happenedWhen: "Generating a {$this->table->modelName} web controller"));
            return;
        }

        $controllerPath = $this->table->getWebControllerPath();
        $routesNames = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);
        $views = $this->getViewsNames($this->table, $this->actor);
        $loadedRelations = $this->table
            ->relations()
            ->filter(fn(CubeRelation $rel) => $rel->exists())
            ->stringifyEachOne(fn(CubeRelation $rel) => $rel->method())
            ->implode(',');

        $linkableAttributes = $this->table->attributes()
            ->filter(fn(CubeAttribute $attribute) => $attribute instanceof HasYajraDataTableRelationLinkColumnRenderer && $attribute->isKey())
            ->filter(function (HasYajraDataTableRelationLinkColumnRenderer|CubeAttribute $attribute) {
                $relatedModel = CubeTable::create($attribute->modelNaming());
                if (!$relatedModel->getWebControllerPath()->exist() || !$relatedModel->getModelPath()->exist()) {
                    return false;
                }
                if (ClassUtils::isMethodDefined($relatedModel->getWebControllerPath(), 'show')) {
                    return false;
                }
                return true;
            });

        ControllerStubBuilder::make()
            ->modelName($this->table->modelNaming())
            ->modelNameCamelCase($this->table->variableNaming())
            ->idVariable($this->table->idVariable())
            ->tableName($this->table->tableNaming())
            ->indexRoute($routesNames['index'])
            ->createView($views['create'])
            ->indexView($views['index'])
            ->showView($views['show'])
            ->updateView($views['edit'])
            ->namespace($this->table->getWebControllerNameSpace(false, true))
            ->requestNamespace($this->table->getRequestNameSpace(false))
            ->traitsNamespace(config('cubeta-starter.trait_namespace'))
            ->modelNamespace($this->table->getModelNameSpace(false))
            ->serviceNamespace($this->table->getServiceNamespace(false))
            ->loadedRelations($loadedRelations)
            ->baseRouteName($routesNames['resource'])
            ->additionalColumn(
                $linkableAttributes
                    ->map(
                        fn(HasYajraDataTableRelationLinkColumnRenderer $attribute) => $attribute
                            ->yajraDataTableAdditionalColumnRenderer($this->actor)
                    )->toArray()
            )->rawColumns($linkableAttributes->stringifyEachOne()->implode(","))
            ->translatableOrderQueries($this->generateOrderingQueriesForTranslatableColumns())
            ->when(
                $this->table->hasRelationOfType(RelationsTypeEnum::HasMany),
                fn($builder) => $builder->method(new AllPaginatedJsonMethodString($this->table->modelNaming()))
            )->generate($controllerPath, $this->override);

        $this->addRoute(
            $this->table,
            $this->actor,
            ContainerType::WEB,
            $this->table->hasRelationOfType(RelationsTypeEnum::HasMany) ? ["allPaginatedJson"] : []
        );

        (new BladeViewsGenerator(
            fileName: $this->fileName,
            attributes: $this->attributes,
            nullables: $this->nullables,
            actor: $this->actor
        ))->run();

        $this->addSidebarItem($routesNames['index']);

        CodeSniffer::make()
            ->setModel($this->table)
            ->checkForWebRelations(
                $this->getRouteNames($this->table, ContainerType::WEB, $this->actor)["all_paginated_json"]
            );
    }

    private function generateOrderingQueriesForTranslatableColumns(): string
    {
        $translatableColumns = $this->getJQueryDataTablesTranslatableColumnsIndexes();

        if (count($translatableColumns) <= 0) {
            return "";
        }

        $config = [];

        foreach ($translatableColumns as $col => $index) {
            $config[] = new TranslatableColumnDataTableColumnOrderingOptionsArrayString($index, $col);
        }

        return new YajraDataTableTranslatableColumnOrderingHandler($config);
    }

    private function getJQueryDataTablesTranslatableColumnsIndexes(): array
    {
        $translatableIndex = 1;
        $translatableColumnsIndexes = [];

        foreach ($this->table->attributes as $attribute) {
            if ($attribute->isTranslatable()) {
                $translatableColumnsIndexes[$attribute->name] = $translatableIndex;
            }
            if ($attribute->type == ColumnTypeEnum::TEXT->value) {
                continue;
            }
            $translatableIndex++;
        }

        return $translatableColumnsIndexes;
    }

    private function addSidebarItem(string $routeName): void
    {
        $sidebarPath = CubePath::make("resources/views/includes/sidebar.blade.php");
        if (!$sidebarPath->exist()) {
            return;
        }
        $sidebarItem = new SidebarItemString($this->table->modelNaming(), $routeName);
        $sidebar = $sidebarPath->getContent();
        $pattern = '/<aside(.*?)>(.*?)<ul(.*?)>(.*?)<\/ul>(.*?)<\/aside>/s';
        if (preg_match($pattern, $sidebar, $matches)) {
            $exactMatch = $matches[4] ?? null;
            if (empty($exactMatch)) {
                return;
            }
            $sidebar = str_replace($exactMatch, "$exactMatch\n$sidebarItem", $sidebar);
            $sidebarPath->putContent($sidebar);
            CubeLog::contentAppended($sidebarItem, $sidebarPath->fullPath);
        } else {
            CubeLog::failedAppending($sidebarItem, $sidebarPath->fullPath, "Adding sidebar item");
        }
    }

    /**
     * @param null $actor
     * @return string[]
     */
    #[ArrayShape(['index' => 'string', 'edit' => 'string', 'create' => 'string', 'show' => 'string'])]
    private function getViewsNames(CubeTable $model, $actor = null): array
    {
        $viewName = $model->viewNaming();
        if (!isset($actor) || $actor == '' || $actor = 'none') {
            return [
                'index' => 'dashboard.' . $viewName . '.index',
                'edit' => 'dashboard.' . $viewName . '.edit',
                'create' => 'dashboard.' . $viewName . '.create',
                'show' => 'dashboard.' . $viewName . '.show',
            ];
        }
        return [
            'index' => 'dashboard.' . $actor . '.' . $viewName . '.index',
            'edit' => 'dashboard.' . $actor . '.' . $viewName . '.edit',
            'create' => 'dashboard.' . $actor . '.' . $viewName . '.create',
            'show' => 'dashboard.' . $actor . '.' . $viewName . '.show',
        ];

    }
}
