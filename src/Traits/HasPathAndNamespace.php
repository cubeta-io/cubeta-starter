<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\app\Models\CubetaRelation;
use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Cubeta\CubetaStarter\app\Models\Path;

/**
 * @mixin CubetaTable|CubetaRelation
 */
trait HasPathAndNamespace
{
    public function getModelPath(): Path
    {
        return new Path(config('cubeta-starter.model_path') . "/{$this->modelName}.php");
    }

    public function getModelClassString(): string
    {
        return "\\" . config('cubeta-starter.model_namespace') . "\\{$this->modelName}";
    }

    public function getApiControllerPath(): Path
    {
        return new Path(config('cubeta-starter.api_controller_path') . "/{$this->getControllerName()}.php");
    }

    public function getApiControllerClassString(): string
    {
        return "\\" . config('cubeta-starter.api_controller_namespace') . "\\{$this->getControllerName()}";
    }

    public function getWebControllerPath(): Path
    {
        return new Path(config('cubeta-starter.web_controller_path') . "/{$this->getControllerName()}.php");
    }

    public function getWebControllerClassString(): string
    {
        return "\\" . config('cubeta-starter.web_controller_namespace') . "\\{$this->getControllerName()}";
    }

    public function getRequestPath(): Path
    {
        return new Path(config('cubeta-starter.request_path') . "/{$this->getRequestName()}.php");
    }

    public function getRequestClassString(): string
    {
        return "\\" . config('cubeta-starter.request_namespace') . "\\{$this->getRequestName()}";
    }

    public function getResourcePath(): Path
    {
        return new Path(config('cubeta-starter.resource_path') . "/{$this->getResourceName()}.php");
    }

    public function getResourceClassString(): string
    {
        return "\\" . config('cubeta-starter.resource_namespace') . "\\{$this->getResourceName()}";
    }

    public function getFactoryPath(): Path
    {
        return new Path(config('cubeta-starter.factory_path') . "/{$this->getFactoryName()}.php");
    }

    public function getFactoryClassString(): string
    {
        return "\\" . config('cubeta-starter.factory_namespace') . "\\{$this->getFactoryName()}";
    }

    public function getSeederPath(): Path
    {
        return new Path(config('cubeta-starter.seeder_path') . "/{$this->getSeederName()}.php");
    }

    public function getSeederClassString(): string
    {
        return "\\" . config('cubeta-starter.seeder_namespace') . "\\{$this->getSeederName()}";
    }

    public function getRepositoryPath(): Path
    {
        return new Path(config('cubeta-starter.repository_path') . "/{$this->getRepositoryName()}.php");
    }

    public function getRepositoryClassString(): string
    {
        return "\\" . config('cubeta-starter.repository_namespace') . "\\{$this->getRepositoryName()}";
    }

    public function getServicePath(): Path
    {
        return new Path(config('cubeta-starter.service_path') . "/{$this->modelName}/{$this->getServiceName()}.php");
    }

    public function getServiceNamespace(): string
    {
        return "\\" . config('cubeta-starter.service_namespace') . "\\{$this->modelName}\\{$this->getServiceName()}";
    }

    public function getServiceInterfaceClassString(): string
    {
        return "\\" . config('cubeta-starter.service_namespace') . "\\{$this->modelName}\\{$this->getServiceInterfaceName()}";
    }

    public function getServiceInterfacePath(): Path
    {
        return new Path(config('cubeta-starter.service_path') . ("/{$this->modelName}/{$this->getServiceInterfaceName()}.php"));
    }

    public function getTestPath(): Path
    {
        return new Path(config('cubeta-starter.test_path') . "/{$this->getTestName()}.php");
    }

    public function getTestClassString(): string
    {
        return "\\" . config('cubeta-starter.test_namespace') . "\\{$this->getTestName()}";
    }
}
