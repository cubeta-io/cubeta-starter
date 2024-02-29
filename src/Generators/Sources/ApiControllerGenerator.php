<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\LogsMessages\Errors\AlreadyExist;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;

class ApiControllerGenerator extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = 'api-controller';
    public static string $configPath = 'cubeta-starter.api_controller_path';

    public function run(): void
    {
        $controllerName = $this->table->getControllerName();
        $controllerPath = $this->getGeneratingPath($controllerName);

        if (file_exists($controllerPath)) {
            $this->logs[] = new AlreadyExist($controllerPath, "Generating Api Controller");
        }

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.api_controller_namespace'),
            '{modelName}' => $this->table->modelName,
            '{variableNaming}' => $this->table->variableNaming(),
            '{serviceNamespace}' => config('cubeta-starter.service_namespace'),
            '{requestNamespace}' => config('cubeta-starter.request_namespace'),
            '{resourceNamespace}' => config('cubeta-starter.resource_namespace'),
            '{idVariable}' => $this->table->idVariable(),
        ];

        try {
            $this->generateFileFromStub($stubProperties, $controllerPath);
        } catch (Exception $exception) {
            $this->logs[] = $exception;
        }

        try {
            $this->addRoute($this->table->modelName, $this->actor);
        } catch (Exception $exception) {
            $this->logs[] = $exception;
        }

        $this->formatFile($controllerPath);
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/controller.api.stub';
    }
}
