<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\App\Models\Postman\Postman;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Info\SuccessMessage;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;

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
            "{modelNamespace}" => config('cubeta-starter.model_namespace')
        ];

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath);
        $this->addRoute($this->table, $this->actor);
        $controllerPath->format();

        try {
            Postman::make()->getCollection()->newCrud($this->table)->save();
            CubeLog::add(new SuccessMessage("Postman Collection Now Has Folder For The Generated Controller [{$this->table->getControllerName()}] \nRe-Import It In Postman"));
        } catch (Exception $e) {
            CubeLog::add($e);
        }
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/../../stubs/controller.api.stub';
    }
}
