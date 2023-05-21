<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeController extends Command
{
    use AssistCommand;
    use RouteBinding;

    protected $signature = 'create:controller
        {name : The name of the model }?
        {actor? : The actor of the endpoint of this model }';

    protected $description = 'Create a new controller';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $actor = $this->argument('actor');

        $this->createController($modelName, $actor);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createController($modelName, $actor): void
    {
        $modelName = $this->modelNaming($modelName);
        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNameLower}' => strtolower($modelName),
        ];

        $controllerName = $this->getControllerName($modelName);
        $controllerPath = $this->getControllerPath($controllerName);

        new CreateFile(
            $stubProperties,
            $controllerPath,
            __DIR__ . '/stubs/controller.api.stub'
        );
        $this->line("<info>Created controller:</info> $controllerName");
        $this->addRoute($modelName, $actor, 'api');
        $this->formatFile($controllerPath);
    }

    private function getControllerName($modelName): string
    {
        return $modelName . 'Controller';
    }

    /**
     * @throws BindingResolutionException
     */
    private function getControllerPath($controllerName): string
    {
        $path = $this->appPath() . '/app/Http/Controllers/API/v1';
        $this->ensureDirectoryExists($path);
        return $path . "/$controllerName" . '.php';
    }
}
