<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

class MakeController extends Command
{
    use AssistCommand;

    public $signature = 'create:controller
        {name : The name of the model }?';

    public $description = 'Create a new controller';

    /**
     * Handle the command
     *
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');

        $this->createController($modelName);
    }

    /**
     * @throws BindingResolutionException
     */
    private function createController($modelName)
    {
        $modelName = ucfirst($modelName);
        $stubProperties = [
            '{modelName}' => $modelName,
            '{modelNameLower}' => Str::lower($modelName),
        ];

        $controllerName = $this->getControllerName($modelName);

        //{class} model name , {namespace} , {traits}
        new CreateFile(
            $stubProperties,
            $this->getControllerPath($controllerName),
            __DIR__ . '/stubs/controller.api.stub'
        );
        $this->line("<info>Created controller:</info> $controllerName");
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
