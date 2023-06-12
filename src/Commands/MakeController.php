<?php

namespace Cubeta\CubetaStarter\Commands;

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
        {name : The name of the model }
        {actor? : The actor of the endpoint of this model }';

    protected $description = 'Create a new controller';

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name') ?? null;
        $actor = $this->argument('actor') ?? null;

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createController($modelName, $actor);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createController($modelName, $actor): void
    {
        $modelName = modelNaming($modelName);
        $stubProperties = [
            '{namespace}' => config('cubeta-starter.api_controller_namespace'),
            '{modelName}' => $modelName,
            '{variableNaming}' => variableNaming($modelName),
            '{serviceNamespace}' => config('cubeta-starter.service_namespace'),
            '{requestNamespace}' => config('cubeta-starter.request_namespace'),
            '{resourceNamespace}' => config('cubeta-starter.resource_namespace')
        ];

        $controllerName = $this->getControllerName($modelName);
        $controllerPath = $this->getControllerPath($controllerName);

        if (file_exists($controllerPath)) {
            $this->error("$controllerName Already Exists");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $controllerPath,
            __DIR__ . '/stubs/controller.api.stub'
        );
        $this->info("Created controller: $controllerName");
        $this->addRoute($modelName, $actor);
        $this->formatFile($controllerPath);
    }

    private function getControllerPath($controllerName): string
    {
        $path = base_path(config('cubeta-starter.api_controller_path'));
        ensureDirectoryExists($path);

        return "$path/$controllerName" . '.php';
    }

    function getControllerName($modelName): string
    {
        return $modelName . 'Controller';
    }
}
