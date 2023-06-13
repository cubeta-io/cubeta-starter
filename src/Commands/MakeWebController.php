<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\ViewGenerating;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MakeWebController extends Command
{
    use AssistCommand;
    use RouteBinding;
    use ViewGenerating;

    protected $signature = 'create:web-controller
        {name : The name of the model }
        {attributes? : the model attributes}
        {actor? : The actor of the endpoint of this model }';

    protected $description = 'Create a new web controller';

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $actor = $this->argument('actor') ?? null;
        $attributes = $this->argument('attributes') ?? [];

        $modelName = modelNaming($name);

        $this->createWebController($modelName, $attributes, $actor);
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function createWebController(string $modelName, array $attributes = [], $actor = null)
    {
        $modelNameCamelCase = variableNaming($modelName);

        $controllerName = $modelName . 'Controller';
        $controllerPath = $this->getWebControllerPath($controllerName);

        if (file_exists($controllerPath)) {
            $this->error("$controllerName Already Exist");

            return;
        }

        $tableName = tableNaming($modelName);
        $routesNames = $this->getRoutesNames($modelName, $actor);
        $views = $this->getViewsNames($modelName, $actor);

        $this->generateCreateOrUpdateForm($modelName, $attributes, $routesNames['store']);
        $this->generateShowView($modelName, $routesNames['edit'], $attributes);
        $this->generateIndexView($modelName, $routesNames['create'], $routesNames['data'], $attributes);
        $this->generateCreateOrUpdateForm($modelName, $attributes, null, $routesNames['update']);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNameCamelCase}' => $modelNameCamelCase,
            '{tableName}' => $tableName,
            '{showRouteName}' => $routesNames['show'],
            '{editRouteName}' => $routesNames['edit'],
            '{deleteRouteName}' => $routesNames['destroy'],
            '{createForm}' => $views['create'],
            '{indexView}' => $views['index'],
            '{showView}' => $views['show'],
            '{editForm}' => $views['edit'],
            '{namespace}' => config('cubeta-starter.web_controller_namespace'),
            '{requestNamespace}' => config('cubeta-starter.request_namespace'),
            '{modelNamespace}' => config('cubeta-starter.model_namespace'),
            '{serviceNamespace}' => config('cubeta-starter.service_namespace')
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
        $this->addRoute($modelName, $actor, 'web');

        $this->addSidebarItem($modelName, $routesNames['index']);
    }

    /**
     * @param null $actor
     * @return string[]
     */
    #[ArrayShape(['index' => 'string', 'edit' => 'string', 'create' => 'string', 'show' => 'string'])]
    public function getViewsNames(string $modelName, $actor = null): array
    {
        $modelLowerPluralName = strtolower(Str::plural($modelName));
        if (!isset($actor) || $actor == '' || $actor = 'none') {
            return [
                'index' => 'dashboard.' . $modelLowerPluralName . '.index',
                'edit' => 'dashboard.' . $modelLowerPluralName . '.edit',
                'create' => 'dashboard.' . $modelLowerPluralName . '.create',
                'show' => 'dashboard.' . $modelLowerPluralName . '.show',
            ];
        } else {
            return [
                'index' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.index',
                'edit' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.update',
                'create' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.create',
                'show' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.show',
            ];
        }
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

    private function getWebControllerPath(string $controllerName): string
    {
        $directory = base_path(config('cubeta-starter.web_controller_path'));
        ensureDirectoryExists($directory);

        return "$directory/$controllerName.php";
    }

    private function addSidebarItem(string $modelName, string $routeName)
    {
        $modelName = tableNaming($modelName);
        $sidebarPath = base_path('resources/views/includes/sidebar.blade.php');
        if (!file_exists($sidebarPath)) {
            \Log::info('there is no sidebar.blade.php in ' . dirname($sidebarPath));
            return;
        }

        $sidebarItem = "<li class=\"nav-item\">\n  <a class=\"nav-link collapsed\" href=\"{{route('$routeName')}}\">\n  <i class=\"bi bi-circle\"></i>\n  <span>$modelName</span>\n  </a>\n  </li>\n  </ul>";

        $sidebar = file_get_contents($sidebarPath);
        $sidebar = str_replace("</ul>", $sidebarItem, $sidebar);
        file_put_contents($sidebarPath, $sidebar);
        \Log::info('appended successfully');
    }
}
