<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\app\Models\CubetaRelation;
use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Cubeta\CubetaStarter\app\Models\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Error;
use Illuminate\Support\Facades\Route;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class WebControllerGenerator extends AbstractGenerator
{
    use AssistCommand, RouteBinding;

    public static string $key = 'web_controller';
    public static string $configPath = 'cubeta-starter.web_controller_path';

    protected string $rawColumns = "";

    protected array $additionalRoutes = [];

    protected CubetaTable $tableObject;


    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->modelName($this->fileName);
        $this->tableObject = $this->addToJsonFile();

        $modelNameCamelCase = variableNaming($modelName);
        $idVariable = $modelNameCamelCase . 'Id';
        $controllerName = $this->generatedFileName();
        $controllerPath = $this->getGeneratingPath($this->fileName);

        throw_if(file_exists($controllerPath), new Error("{$controllerName} Already Exists"));

        $tableName = tableNaming($modelName);
        $routesNames = $this->getRoutesNames($modelName, $this->actor);
        $views = $this->getViewsNames($modelName, $this->actor);

        $addColumns = $this->getKeyColumnsHyperlink($this->attributes, $this->actor);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNameCamelCase}' => $modelNameCamelCase,
            '{idVariable}' => $idVariable,
            '{tableName}' => $tableName,
            '{addColumns}' => $addColumns,
            '{rawColumns}' => $this->rawColumns ?? '',
            '{indexRoute}' => $routesNames['index'],
            '{createForm}' => $views['create'],
            '{indexView}' => $views['index'],
            '{showView}' => $views['show'],
            '{editForm}' => $views['edit'],
            '{namespace}' => config('cubeta-starter.web_controller_namespace'),
            '{requestNamespace}' => config('cubeta-starter.request_namespace'),
            '{modelNamespace}' => config('cubeta-starter.model_namespace'),
            '{serviceNamespace}' => config('cubeta-starter.service_namespace'),
            '{translationOrderQueries}' => $this->generateOrderingQueriesForTranslatableColumns($this->attributes),
            '{additionalMethods}' => $this->additionalControllerMethods($modelName, $this->relations),
            '{loadedRelations}' => $this->getLoadedRelations(),
            '{baseRouteName}' => $routesNames['base']
        ];

        $this->generateFileFromStub($stubProperties, $controllerPath, $this->stubsPath());

        $this->addRoute($modelName, $this->actor);
        $this->formatFile($controllerPath);

        (new ViewsGenerator(
            fileName: $this->fileName,
            attributes: $this->attributes,
            nullables: $this->nullables,
            actor: $this->actor
        ))->run();
    }

    protected function generatedFileName(): string
    {
        return $this->modelName($this->fileName) . "Controller";
    }

    /**
     * @param null $actor
     * @return string[]
     */
    #[ArrayShape(['index' => 'string', 'edit' => 'string', 'create' => 'string', 'show' => 'string'])]
    private function getViewsNames(string $modelName, $actor = null): array
    {
        $viewName = viewNaming($modelName);
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

    private function getKeyColumnsHyperlink(array $attributes = [], ?string $actor = null): string
    {
        $dataColumn = '';
        foreach ($attributes as $column => $type) {
            if ($type == 'key') {
                $relatedModel = modelNaming(str_replace('_id', '', $column));

                if (!file_exists(getWebControllerPath($relatedModel)) || !file_exists(getModelPath($relatedModel))) {
                    continue;
                }

                if (isMethodDefined(getWebControllerPath($relatedModel), 'show')) {
                    continue;
                }


                $showRouteName = $this->getRoutesNames($relatedModel, $actor)['show'];

                if (!Route::has($showRouteName)) {
                    continue;
                }

                $relatedTable = Settings::make()->getTable($relatedModel);
                $columnName = relationFunctionNaming($relatedTable->modelName) . '.' . $relatedTable->titleable()->name;
                $columnCalling = "\$row->" . relationFunctionNaming($relatedTable->modelName) . "->" . $relatedTable->titleable()->name;
                $dataColumn .= "
                    ->editColumn('{$columnName}', function (\$row) {
                    //TODO::check on the used show route of the related model key
                        return \"<a href='\" . route('{$showRouteName}', \$row->{$column}) . \"'>{$columnCalling}</a>\";
                    })";
                $this->rawColumns .= "'{$columnName}' ,";
            }
        }

        return $dataColumn;
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['index' => 'string', 'show' => 'string', 'edit' => 'string', 'destroy' => 'string', 'store' => 'string', 'create' => 'string', 'data' => 'string', 'update' => 'string', 'base' => 'string'])]
    protected function getRoutesNames(string $modelName, ?string $actor = null): array
    {
        $baseRouteName = $this->getRouteName($modelName, ContainerType::WEB, $actor);

        return [
            'index' => $baseRouteName . '.index',
            'show' => $baseRouteName . '.show',
            'edit' => $baseRouteName . '.edit',
            'destroy' => $baseRouteName . '.destroy',
            'store' => $baseRouteName . '.store',
            'create' => $baseRouteName . '.create',
            'data' => $baseRouteName . '.data',
            'update' => $baseRouteName . '.update',
            'base' => $baseRouteName
        ];
    }

    private function generateOrderingQueriesForTranslatableColumns(array $attributes = []): string
    {
        $translatableColumns = $this->getJQueryDataTablesTranslatableColumnsIndexes($attributes);
        $queries = '';

        if (count($translatableColumns) <= 0) {
            return $queries;
        }

        $queries .= "\$query = \$this->orderTranslatableColumns(\$query, [\n";
        foreach ($translatableColumns as $col => $index) {
            $queries .= "['orderIndex' => 0, 'columnIndex' => $index, 'columnName' => '$col'],\n";
        }

        $queries .= "\n]);";

        return $queries;
    }

    private function getJQueryDataTablesTranslatableColumnsIndexes(array $attributes = []): array
    {
        $translatableIndex = 1;
        $translatableColumnsIndexes = [];

        foreach ($attributes as $attribute => $type) {
            if ($type == 'translatable') {
                $translatableColumnsIndexes[$attribute] = $translatableIndex;
            }
            if ($type == 'text') {
                continue;
            }
            $translatableIndex++;
        }

        return $translatableColumnsIndexes;
    }

    private function additionalControllerMethods(string $modelName, array $relations = []): string
    {
        $methods = '';
        $variableName = variableNaming($modelName);

        if (in_array(RelationsTypeEnum::HasMany->value, $relations)) {
            $methods .= "public function allPaginatedJson()
                        {
                            \${$variableName} = \$this->{$variableName}Service->indexWithPagination([], 7);
                            return response()->json(\${$variableName} , 200);
                        }";
            $this->additionalRoutes[] = 'allPaginatedJson';
        }

        return $methods;
    }

    public function getLoadedRelations(): string
    {
        $loaded = $this->tableObject->relations()->filter(function (CubetaRelation $rel) {
            $relatedModelPath = getModelPath($rel->modelName);
            $currentModelPath = getModelPath($this->tableObject->modelName);
            return file_exists($relatedModelPath)
                and (
                ($rel->isHasMany()) ?
                    isMethodDefined($currentModelPath, relationFunctionNaming($rel->modelName, false)) :
                    isMethodDefined($currentModelPath, relationFunctionNaming($rel->modelName))
                );
        })->map(fn(CubetaRelation $rel) => $rel->method())->toArray();

        $loadedString = '';
        foreach ($loaded as $item) {
            $loadedString .= "'$item' , ";
        }

        return $loadedString;
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/controller.web.stub';
    }

    private function addSidebarItem(string $modelName, string $routeName): void
    {
        $modelName = titleNaming($modelName);
        $sidebarPath = base_path('resources/views/includes/sidebar.blade.php');
        if (!file_exists($sidebarPath)) {
            return;
        }

        $sidebarItem = "\t\t<li class=\"nav-item\">\n\t\t\t<a class=\"nav-link collapsed @if(request()->fullUrl() == route('{$routeName}')) active @endif\" href=\"{{route('{$routeName}')}}\">\n\t\t\t\t<i class=\"bi bi-circle\"></i><span>{$modelName}</span>\n\t\t\t</a>\n\t\t</li>\n</ul>";

        $sidebar = file_get_contents($sidebarPath);
        $sidebar = str_replace("</ul>", $sidebarItem, $sidebar);
        file_put_contents($sidebarPath, $sidebar);
    }
}
