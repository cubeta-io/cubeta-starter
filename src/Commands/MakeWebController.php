<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\app\Models\CubetaRelation;
use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Cubeta\CubetaStarter\app\Models\Settings;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\ViewGenerating;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Route;
use JetBrains\PhpStorm\ArrayShape;

class MakeWebController extends Command
{
    use AssistCommand;
    use RouteBinding;
    use ViewGenerating;

    protected CubetaTable $tableObject;

    protected $description = 'Create a new web controller';

    protected $signature = 'create:web-controller
        {name : The name of the model }
        {attributes? : the model attributes}
        {relations? : the model relations}
        {nullables? : the nullables attributes}
        {actor? : The actor of the endpoint of this model }';

    protected string $rawColumns = "";

    protected array $additionalRoutes = [];

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $actor = $this->argument('actor') ?? null;
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];
        $nullables = $this->argument('nullables') ?? [];

        $modelName = modelNaming($name);

        $this->tableObject = Settings::make()->serialize($modelName, $attributes, $relations, $nullables, []);

        $this->createWebController($modelName, $attributes, $nullables, $relations, $actor);

        CodeSniffer::make()
            ->setModel($modelName)
            ->checkForWebRelations(
                $this->getRouteName($modelName, ContainerType::WEB, $actor) . '.allPaginatedJson'
            );
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function createWebController(string $modelName, array $attributes = [], array $nullables = [], array $relations = [], ?string $actor = null): void
    {
        $modelNameCamelCase = variableNaming($modelName);
        $idVariable = $modelNameCamelCase . 'Id';

        $controllerName = $modelName . 'Controller';
        $controllerPath = $this->getWebControllerPath($controllerName);

        if (file_exists($controllerPath)) {
            $this->error("{$controllerName} Already Exists");
            return;
        }

        $tableName = tableNaming($modelName);
        $routesNames = $this->getRoutesNames($modelName, $actor);
        $views = $this->getViewsNames($modelName, $actor);

        $this->generateCreateOrUpdateForm($modelName, $attributes, $nullables, $routesNames['store'], null, $actor);
        $this->generateShowView($modelName, $routesNames['edit'], $attributes);
        $this->generateIndexView($modelName, $routesNames['create'], $routesNames['data'], $attributes);
        $this->generateCreateOrUpdateForm($modelName, $attributes, $nullables, null, $routesNames['update'], $actor);
        $addColumns = $this->getKeyColumnsHyperlink($attributes, $actor);

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
            '{translationOrderQueries}' => $this->generateOrderingQueriesForTranslatableColumns($attributes),
            '{additionalMethods}' => $this->additionalControllerMethods($modelName, $relations),
            '{loadedRelations}' => $this->getLoadedRelations(),
            '{baseRouteName}' => $routesNames['base']
        ];

        if (!is_dir(base_path(config('cubeta-starter.web_controller_path')))) {
            mkdir(base_path(config('cubeta-starter.web_controller_path')), 0777, true);
        }

        generateFileFromStub(
            $stubProperties,
            $controllerPath,
            __DIR__ . '/stubs/controller.web.stub'
        );

        $this->info("{$controllerName} Created");

        $this->addRoute($modelName, $actor, ContainerType::WEB, $this->additionalRoutes);

        $this->addSidebarItem($modelName, $routesNames['index']);
    }

    private function getWebControllerPath(string $controllerName): string
    {
        $directory = base_path(config('cubeta-starter.web_controller_path'));
        ensureDirectoryExists($directory);

        return "{$directory}/{$controllerName}.php";
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['index' => 'string', 'show' => 'string', 'edit' => 'string', 'destroy' => 'string', 'store' => 'string', 'create' => 'string', 'data' => 'string', 'update' => 'string', 'base' => 'string'])]
    public function getRoutesNames(string $modelName, ?string $actor = null): array
    {
        $baseRouteName = $this->getRouteName($modelName, 'web', $actor);

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

    /**
     * @param null $actor
     * @return string[]
     */
    #[ArrayShape(['index' => 'string', 'edit' => 'string', 'create' => 'string', 'show' => 'string'])]
    public function getViewsNames(string $modelName, $actor = null): array
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

    private function additionalControllerMethods(string $modelName, array $relations = []): string
    {
        $methods = '';
        $variableName = variableNaming($modelName);

        if (in_array(RelationsTypeEnum::HasMany, $relations)) {
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

    private function addSidebarItem(string $modelName, string $routeName): void
    {
        $modelName = titleNaming($modelName);
        $sidebarPath = base_path('resources/views/includes/sidebar.blade.php');
        if (!file_exists($sidebarPath)) {
            return;
        }

        $sidebarItem = "<li class=\"nav-item\">\n  <a class=\"nav-link collapsed\" href=\"{{route('{$routeName}')}}\">\n  <i class=\"bi bi-circle\"></i>\n  <span>{$modelName}</span>\n  </a>\n  </li>\n  </ul>";

        $sidebar = file_get_contents($sidebarPath);
        $sidebar = str_replace("</ul>", $sidebarItem, $sidebar);
        file_put_contents($sidebarPath, $sidebar);
    }
}
