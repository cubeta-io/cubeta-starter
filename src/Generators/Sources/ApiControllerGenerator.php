<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\LogsMessages\Errors\AlreadyExist;
use Cubeta\CubetaStarter\LogsMessages\Log;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;

class ApiControllerGenerator extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = 'api-controller';
    public static string $configPath = 'cubeta-starter.api_controller_path';

    public function run(): void
    {
        $controllerPath = $this->table->getApiControllerPath();

        if (file_exists($controllerPath->fullPath)) {
            Log::add(new AlreadyExist($controllerPath->fullPath, "Generating Api Controller"));
        }

        $controllerPath->ensureDirectoryExists();

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.api_controller_namespace'),
            '{modelName}' => $this->table->modelName,
            '{variableNaming}' => $this->table->variableNaming(),
            '{serviceNamespace}' => config('cubeta-starter.service_namespace'),
            '{requestNamespace}' => config('cubeta-starter.request_namespace'),
            '{resourceNamespace}' => config('cubeta-starter.resource_namespace'),
            '{idVariable}' => $this->table->idVariable(),
        ];

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath);

        try {
            $this->addRoute($this->table, $this->actor);
        } catch (Exception $exception) {
            Log::add($exception);
        }

        $this->formatFile($controllerPath);
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/controller.api.stub';
    }
}
