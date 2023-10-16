<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\ArrayShape;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\ViewGenerating;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

class MakeWebController extends Command
{
    use AssistCommand;
    use RouteBinding;
    use ViewGenerating;

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

        $this->createWebController($modelName, $attributes, $nullables, $relations, $actor);
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function createWebController(string $modelName, array $attributes = [], array $nullables = [], array $relations = [], $actor = null): void
    {
        $modelNameCamelCase = variableNaming($modelName);
        $idVariable = $modelNameCamelCase . 'Id';

        $controllerName = $modelName . 'Controller';
        $controllerPath = $this->getWebControllerPath($controllerName);

        if (file_exists($controllerPath)) {
            $this->error("$controllerName Already Exists");

            return;
        }

        $tableName = tableNaming($modelName);
        $routesNames = $this->getRoutesNames($modelName, $actor);
        $views = $this->getViewsNames($modelName, $actor);

        $this->generateCreateOrUpdateForm($modelName, $attributes, $nullables, $routesNames['store'], null, $actor);
        $this->generateShowView($modelName, $routesNames['edit'], $attributes);
        $this->generateIndexView($modelName, $routesNames['create'], $routesNames['data'], $attributes);
        $this->generateCreateOrUpdateForm($modelName, $attributes, $nullables, null, $routesNames['update'], $actor);
        $addColumns = $this->getKeyColumnsHyperlink($attributes);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNameCamelCase}' => $modelNameCamelCase,
            '{idVariable}' => $idVariable,
            '{tableName}' => $tableName,
            '{addColumns}' => $addColumns ,
            '{rawColumns}' => $this->rawColumns ?? '',
            '{showRouteName}' => $routesNames['show'],
            '{editRouteName}' => $routesNames['edit'],
            '{deleteRouteName}' => $routesNames['destroy'],
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
        ];

        if (!is_dir(base_path(config('cubeta-starter.web_controller_path')))) {
            mkdir(base_path(config('cubeta-starter.web_controller_path')), 0777, true);
        }

        generateFileFromStub(
            $stubProperties,
            $controllerPath,
            __DIR__ . '/stubs/controller.web.stub'
        );

        $this->info("$controllerName Created");
        $this->addRoute($modelName, $actor, 'web', $this->additionalRoutes);

        $this->addSidebarItem($modelName, $routesNames['index']);
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['index' => 'string', 'show' => 'string', 'edit' => 'string', 'destroy' => 'string', 'store' => 'string', 'create' => 'string', 'data' => 'string', 'update' => 'string'])]
    public function getRoutesNames(string $modelName, $actor = null): array
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

    private function addSidebarItem(string $modelName, string $routeName): void
    {
        $modelName = titleNaming($modelName);
        $sidebarPath = base_path('resources/views/includes/sidebar.blade.php');
        if (!file_exists($sidebarPath)) {
            return;
        }

        $sidebarItem = "<li class=\"nav-item\">\n  <a class=\"nav-link collapsed\" href=\"{{route('$routeName')}}\">\n  <i class=\"bi bi-circle\"></i>\n  <span>$modelName</span>\n  </a>\n  </li>\n  </ul>";

        $sidebar = file_get_contents($sidebarPath);
        $sidebar = str_replace("</ul>", $sidebarItem, $sidebar);
        file_put_contents($sidebarPath, $sidebar);
    }

    private function getWebControllerPath(string $controllerName): string
    {
        $directory = base_path(config('cubeta-starter.web_controller_path'));
        ensureDirectoryExists($directory);

        return "$directory/$controllerName.php";
    }

    private function getKeyColumnsHyperlink(array $attributes = []): string
    {
        $dataColumn = '';
        foreach ($attributes as $column => $type) {
            if ($type == 'key') {
                $relatedModel = modelNaming(str_replace('_id', '', $column));
                $showRouteName = $this->getRoutesNames($relatedModel)['show'];
                $dataColumn .= "
                    ->editColumn('$column', function (\$row) {
                    //TODO::check on the used show route of the related model key
                        return \"<a href='\" . route('$showRouteName', \$row->$column) . \"'>\$row->{$column}</a>\";
                    })";
                $this->rawColumns .= "'{$column}' ,";
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
}
