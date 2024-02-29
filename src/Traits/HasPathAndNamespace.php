<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\app\Models\CubetaRelation;
use Cubeta\CubetaStarter\app\Models\CubetaTable;

/**
 * @mixin CubetaTable|CubetaRelation
 */
trait HasPathAndNamespace
{
    public function getModelPath(): string
    {
        return base_path(config('cubeta-starter.model_path')) . "/{$this->modelName}.php";
    }

    public function getModelClassString(): string
    {
        return "\\" . config('cubeta-starter.model_namespace') . "\\{$this->modelName}";
    }

    public function getApiControllerPath(): string
    {
        return base_path(config('cubeta-starter.api_controller_path')) . "/{$this->getControllerName()}.php";
    }

    public function getApiControllerClassString(): string
    {
        return "\\" . config('cubeta-starter.api_controller_namespace') . "\\{$this->getControllerName()}";
    }

    public function getWebControllerPath(): string
    {
        return base_path(config('cubeta-starter.web_controller_path')) . "/{$this->getControllerName()}.php";
    }

    public function getWebControllerClassString(): string
    {
        return "\\" . config('cubeta-starter.web_controller_namespace') . "\\{$this->getControllerName()}";
    }

    public function getRequestPath(): string
    {
        return base_path(config('cubeta-starter.request_path')) . "/{$this->getRequestName()}.php";
    }

    public function getRequestClassString(): string
    {
        return "\\" . config('cubeta-starter.request_namespace') . "\\{$this->getRequestName()}";
    }

    public function getResourcePath(): string
    {
        return base_path(config('cubeta-starter.resource_path')) . "/{$this->getResourceName()}.php";
    }

    public function getResourceClassString(): string
    {
        return "\\" . config('cubeta-starter.resource_namespace') . "\\{$this->getResourceName()}";
    }

    public function getFactoryPath(): string
    {
        return base_path(config('cubeta-starter.factory_path')) . "/{$this->getFactoryName()}.php";
    }

    public function getFactoryClassString(): string
    {
        return "\\" . config('cubeta-starter.factory_namespace') . "\\{$this->getFactoryName()}";
    }

    public function getSeederPath(): string
    {
        return base_path(config('cubeta-starter.seeder_path')) . "/{$this->getSeederName()}.php";
    }

    public function getSeederClassString(): string
    {
        return "\\" . config('cubeta-starter.seeder_namespace') . "\\{$this->getSeederName()}";
    }

    public function getRepositoryPath(): string
    {
        return base_path(config('cubeta-starter.repository_path')) . "/{$this->getRepositoryName()}.php";
    }

    public function getRepositoryClassString(): string
    {
        return "\\" . config('cubeta-starter.repository_namespace') . "\\{$this->getRepositoryName()}";
    }

    public function getServicePath(): string
    {
        return base_path(config('cubeta-starter.service_path')) . "/{$this->modelName}/{$this->getServiceName()}.php";
    }

    public function getServiceNamespace(): string
    {
        return "\\" . config('cubeta-starter.service_namespace') . "\\{$this->modelName}\\{$this->getServiceName()}";
    }

    public function getServiceInterfaceClassString(): string
    {
        return "\\" . config('cubeta-starter.service_namespace') . "\\{$this->modelName}\\{$this->getServiceInterfaceName()}";
    }

    public function getServiceInterfacePath(): string
    {
        return base_path(config('cubeta-starter.service_path')) . ("/{$this->modelName}/{$this->getServiceInterfaceName()}.php");
    }

    public function getTestPath(): string
    {
        return base_path(config('cubeta-starter.test_path')) . "/{$this->getTestName()}.php";
    }

    public function getTestClassString(): string
    {
        return "\\" . config('cubeta-starter.test_namespace') . "\\{$this->getTestName()}";
    }
}
