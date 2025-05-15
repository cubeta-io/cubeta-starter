<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Api\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self requestNamespace(string $requestNamespace)
 * @method self serviceNamespace(string $serviceNamespace)
 * @method self modelName(string $modelName)
 * @method self serviceName(string $serviceName)
 * @method self idVariable(string $idVariable)
 * @method self modelVariable(string $modelVariable)
 */
class ApiControllerStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath('Api/Controllers/Controller.stub');
    }
}