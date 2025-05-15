<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Postman\Postman;
use Cubeta\CubetaStarter\Stub\Builders\Api\Controllers\ApiControllerStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;

class ApiControllerGenerator extends AbstractGenerator
{
    use RouteBinding;

    public function run(): void
    {
        $controllerPath = $this->table->getApiControllerPath();

        ApiControllerStubBuilder::make()
            ->namespace($this->table->getApiControllerNameSpace(false))
            ->modelName($this->table->modelName)
            ->serviceNamespace($this->table->getServiceNamespace(false))
            ->idVariable($this->table->idVariable())
            ->requestNamespace($this->table->getRequestNameSpace(false))
            ->serviceName($this->table->modelNaming())
            ->modelVariable($this->table->variableNaming())
            ->generate($controllerPath, $this->override);


        $this->addRoute($this->table, $this->actor);
        $controllerPath->format();

        try {
            Postman::make()->getCollection()->newCrud($this->table, $this->version, $this->actor)->save();
            CubeLog::success("Postman Collection Now Has Folder For The Generated Controller [{$this->table->getControllerName()}] \nRe-Import It In Postman");
        } catch (Exception $e) {
            CubeLog::add($e);
        }
    }
}
