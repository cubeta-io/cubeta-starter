<?php

namespace Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Controllers;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Stub\Contracts\ClassStubBuilder;

/**
 * @method self requestNamespace(string $namespace)
 * @method self modelNamespace(string $namespace)
 * @method self serviceNamespace(string $namespace)
 * @method self modelName(string $name)
 * @method self serviceName(string $name)
 * @method self modelNameCamelCase(string $name)
 * @method self indexPage(string $indexPagePathName)
 * @method self showPage(string $showPagePathName)
 * @method self createPage (string $createPagePathName)
 * @method self indexRoute(string $indexRouteName)
 * @method self updatePage (string $updatePageRouteName)
 * @method self relations (string $relations)
 */
class ControllerStubBuilder extends ClassStubBuilder
{
    protected function stubPath(): string
    {
        return CubePath::stubPath("Web/InertiaReact/Controllers/Controller.stub");
    }
}