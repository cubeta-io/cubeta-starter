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

    public function run(bool $override = false): void
    {
        $controllerPath = $this->table->getApiControllerPath();

        if ($controllerPath->exist()) {
            $controllerPath->logAlreadyExist("Generating Api Controller For ({$this->table->modelName}) Model");
            return;
        }

        $controllerPath->ensureDirectoryExists();

        $stubProperties = [
            '{namespace}' => $this->table->getApiControllerNameSpace(false),
            '{modelName}' => $this->table->modelName,
            '{variableNaming}' => $this->table->variableNaming(),
            '{serviceNamespace}' => $this->table->getServiceNamespace(false, true),
            '{requestNamespace}' => $this->table->getRequestNameSpace(false, true),
            '{resourceNamespace}' => $this->table->getResourceNameSpace(false, true),
            '{idVariable}' => $this->table->idVariable(),
            "{modelNamespace}" => $this->table->getModelNameSpace(),
        ];

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath);
        $this->addRoute($this->table, $this->actor);
        $controllerPath->format();

        try {
            Postman::make()->getCollection()->newCrud($this->table, $this->version, $this->actor)->save();
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
