<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
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
        {attributes : the model attributes}
        {actor? : The actor of the endpoint of this model }';

    protected $description = 'Create a new web controller';

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $actor = $this->argument('actor');
        $attributes = $this->argument('attributes');

        $modelName = modelNaming($name);

        $this->createWebController($modelName, $attributes, $actor);
        $this->addRoute($modelName, $actor, 'web');
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function createWebController(string $modelName, array $attributes, $actor = null)
    {
        $modelNameCamelCase = variableNaming($modelName);

        $controllerName = $modelName . 'Controller';
        $controllerPath = $this->getWebControllerPath($controllerName);

        if (file_exists($controllerPath)) {
            $this->line("<info>The Controller $controllerName <fg=red>Already Exists</fg=red></info>");
            return;
        }

        $tableName = tableNaming($modelName);
        $routesNames = $this->getRoutesNames($modelName, $actor);
        $views = $this->getViewsNames($modelName, $actor);

        $this->generateCreateOrUpdateForm($modelName, $attributes, $routesNames['store']);
        $this->generateShowView($modelName, $attributes, $routesNames['edit']);
        $this->generateIndexView($modelName, $attributes, $routesNames['create'], $routesNames['data']);
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
            '{editForm}' => $views['edit']
        ];

        if (!is_dir(base_path('app/Http/Controllers/WEB/v1/'))) {
            mkdir(base_path('app/Http/Controllers/WEB/v1/'), 0777, true);
        }

        new CreateFile(
            $stubProperties,
            $controllerPath,
            __DIR__ . '/stubs/controller.web.stub'
        );

        $this->line("<info> $controllerName Created </info>");
    }

    /**
     * @param string $modelName
     * @param null $actor
     * @return string[]
     */
    #[ArrayShape(['index' => "string", 'edit' => "string", 'create' => "string", 'show' => "string"])]
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
                'edit' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.edit',
                'create' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.create',
                'show' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.show',
            ];
        }
    }

    /**
     * @param string $modelName
     * @param $actor
     * @return string[]
     */
    #[ArrayShape(['index' => "string", 'show' => "string", 'edit' => "string", 'destroy' => "string", 'store' => "string", 'create' => "string", 'data' => "string", 'update' => 'string'])]
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
            'update' => $baseRouteName . '.update'
        ];
    }

    /**
     * @param string $controllerName
     * @return string
     */
    private function getWebControllerPath(string $controllerName): string
    {
        $directory = base_path(config('repository.web_controller_path'));
        $this->ensureDirectoryExists($directory);
        return "$directory/$controllerName.php" ;
    }
}
