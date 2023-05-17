<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MakeWebController extends Command
{
    use AssistCommand;

    protected $signature = 'create:web-controller
        {name : The name of the model }
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

        $modelName = $this->modelNaming($name);

        $this->createWebController($modelName, $actor);
    }

    /**
     * @throws FileNotFoundException
     * @throws BindingResolutionException
     */
    public function createWebController(string $modelName, string $actor = null)
    {
        $modelNameLower = strtolower($modelName);

        $controllerName = $modelName . 'Controller';
        $controllerPath = base_path('app/Http/Controllers/WEB/' . $controllerName . 'php');

        if (file_exists($controllerPath)) {
            $this->line("<info>The Controller $controllerName <fg=red>Already Exists</fg=red></info>");
            return;
        }

        $modelLowerPluralName = strtolower(Str::plural($modelName));
        $baseRouteName = $this->getRouteName($modelName, $actor);
        $showRouteName = $baseRouteName . 'show';
        $deleteRouteName = $baseRouteName . 'destroy';
        $editRouteName = $baseRouteName . 'edit';
        $indexRouteName = $baseRouteName . 'index';
        $views = $this->getViewsNames($modelName, $actor);

        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNameLower}' => $modelNameLower,
            '{modelLowerPluralName}' => $modelLowerPluralName,
            '{indexRouteName}' => $indexRouteName,
            '{showRouteName}' => $showRouteName,
            '{editRouteName}' => $editRouteName,
            '{deleteRouteName}' => $deleteRouteName,
            '{createForm}' => $views['create'],
            '{indexView}' => $views['index'],
            '{showView}' => $views['show'],
            '{editForm}' => $views['edit'],
        ];

        new CreateFile(
            $stubProperties,
            $controllerPath,
            __DIR__ . '/stubs/controller.web.stub'
        );

        $this->line("<info> $controllerName Created </info>");
    }

    /**
     * @param string $modelName
     * @param string|null $actor
     * @return string
     */
    public function getRouteName(string $modelName, string $actor = null): string
    {
        $modelLowerPluralName = strtolower(Str::plural($modelName));
        if (!isset($actor) || $actor == '' || $actor = 'none') {
            return 'web.' . $modelLowerPluralName;
        } else return 'web.' . $actor . '.' . $modelLowerPluralName;
    }

    /**
     * @param string $modelName
     * @param string $actor
     * @return string[]
     */
    #[ArrayShape(['index' => "string", 'edit' => "string", 'create' => "string", 'show' => "string"])]
    public function getViewsNames(string $modelName, string $actor): array
    {
        $modelLowerPluralName = strtolower(Str::plural($modelName));
        if (!isset($actor) || $actor == '' || $actor = 'none') {
            return [
                'index' => 'dashboard.' . $modelLowerPluralName . '.index',
                'edit' => 'dashboard.' . $modelLowerPluralName . '.edit',
                'create' => 'dashboard.' . $modelLowerPluralName . '.create',
                'show' => 'dashboard.' . $modelLowerPluralName . '.show',
            ];
        } else return [
            'index' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.index',
            'edit' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.edit',
            'create' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.create',
            'show' => 'dashboard.' . $actor . '.' . $modelLowerPluralName . '.show',
        ];
    }
}
