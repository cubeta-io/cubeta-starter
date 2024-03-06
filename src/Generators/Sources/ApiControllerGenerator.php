<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class ApiControllerGenerator extends AbstractGenerator
{
    use RouteBinding;

    public static string $key = 'api-controller';
    public static string $configPath = 'cubeta-starter.api_controller_path';

    public function run(bool $override = false): void
    {
        $controllerPath = $this->table->getApiControllerPath();

        if ($controllerPath->exist()) {
            $controllerPath->logAlreadyExist("Generating Api Controller For ({$this->table->modelName}) Model");
            return;
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
        $this->addRoute($this->table, $this->actor);
        $controllerPath->format();
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/controller.api.stub';
    }
}
