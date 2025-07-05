<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Modules\Postman;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\Stub\Builders\Api\Controllers\ApiControllerStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Exception;

class ApiControllerGenerator extends AbstractGenerator
{
    use RouteBinding;

    public function run(): void
    {
        $freshTable = Settings::make()->getTable($this->table->modelName);
        $this->table = $freshTable ?? $this->table;

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

        if (config('cubeta-starter.generate_postman_collection_for_api_routes')) {
            $this->addToPostman();
        }
    }

    public function addToPostman(): void
    {
        try {
            Postman::make()->addCrud($this->table, $this->actor)->save();
            CubeLog::success("Postman Collection Now Has Folder For The Generated Controller [{$this->table->getControllerName()}] \nRe-Import It In Postman");
        } catch (Exception $e) {
            CubeLog::add($e);
        }
    }
}
