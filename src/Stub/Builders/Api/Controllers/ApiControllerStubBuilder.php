<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method ApiControllerStubBuilder requestNamespace(string $requestNamespace)
 * @method ApiControllerStubBuilder serviceNamespace(string $serviceNamespace)
 * @method ApiControllerStubBuilder resourceNamespace(string $resourceNamespace)
 * @method ApiControllerStubBuilder modelNamespace(string $modelNamespace)
 * @method ApiControllerStubBuilder modelName(string $modelName)
 * @method ApiControllerStubBuilder serviceName(string $serviceName)
 * @method ApiControllerStubBuilder idVariable(string $idVariable)
 */
class ApiControllerStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Controllers/Controller.stub');
    }
}