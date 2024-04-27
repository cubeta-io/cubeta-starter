<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\Helpers\CubePath;

/**
 * @mixin CubeTable|CubeRelation
 */
trait HasPathAndNamespace
{
    public $cubetaPath = '';

    public function getModelPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.model_path') . "/{$this->modelName}.php");
    }

    public function getModelClassString(): string
    {
        return "\\" . config('cubeta-starter.model_namespace') . "\\{$this->modelName}";
    }

    public function getModelNameSpace(): string
    {
        return config('cubeta-starter.model_namespace');
    }

    public function getSeederNameSpace(): string
    {
        return config('cubeta-starter.seeder_namespace');
    }

    public function getApiControllerPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.api_controller_path') . "/$this->version/{$this->getControllerName()}.php");
    }

    public function getApiControllerClassString(): string
    {
        return "\\" . config('cubeta-starter.api_controller_namespace') . "\\$this->version\\{$this->getControllerName()}";
    }

    public function getApiControllerNameSpace($withStart = true): string
    {
        return ($withStart ? "\\" : "") . config('cubeta-starter.api_controller_namespace') . "\\$this->version";
    }

    public function getWebControllerPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.web_controller_path') . "/$this->version/{$this->getControllerName()}.php");
    }

    public function getWebControllerNameSpace(): string
    {
        return config('cubeta-starter.web_controller_namespace') . "\\$this->version";
    }

    public function getWebControllerClassString(): string
    {
        return "\\" . config('cubeta-starter.web_controller_namespace') . "\\{$this->getControllerName()}";
    }

    public function getRequestPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.request_path') . "/{$this->version}/{$this->modelName}/{$this->getRequestName()}.php");
    }

    public function getRequestClassString(): string
    {
        return "\\" . config('cubeta-starter.request_namespace') . "\\{$this->version}\\{$this->modelName}\\{$this->getRequestName()}";
    }

    public function getRequestNameSpace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? "\\" : "") . config('cubeta-starter.request_namespace') . "\\{$this->version}"
            : ($withStart ? "\\" : "") . config('cubeta-starter.request_namespace') . "\\{$this->version}\\{$this->modelName}";
    }

    public function getResourcePath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.resource_path') . "/{$this->version}/{$this->getResourceName()}.php");
    }

    public function getResourceClassString(): string
    {
        return "\\" . config('cubeta-starter.resource_namespace') . "\\{$this->version}\\{$this->getResourceName()}";
    }

    public function getResourceNameSpace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? "\\" : "") . config('cubeta-starter.resource_namespace') . "\\{$this->version}"
            : ($withStart ? "\\" : "") . config('cubeta-starter.resource_namespace') . "\\{$this->version}\\{$this->getResourceName()}";
    }

    public function getFactoryPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.factory_path') . "/{$this->getFactoryName()}.php");
    }

    public function getFactoryClassString(): string
    {
        return "\\" . config('cubeta-starter.factory_namespace') . "\\{$this->getFactoryName()}";
    }

    public function getSeederPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.seeder_path') . "/{$this->getSeederName()}.php");
    }

    public function getSeederClassString(): string
    {
        return "\\" . config('cubeta-starter.seeder_namespace') . "\\{$this->getSeederName()}";
    }

    public function getRepositoryPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.repository_path') . "/{$this->getRepositoryName()}.php");
    }

    public function getRepositoryNameSpace(): string
    {
        return config('cubeta-starter.repository_namespace');
    }

    public function getRepositoryClassString(): string
    {
        return "\\" . config('cubeta-starter.repository_namespace') . "\\{$this->getRepositoryName()}";
    }

    public function getServicePath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.service_path') . "/{$this->version}/{$this->modelName}/{$this->getServiceName()}.php");
    }

    public function getServiceNamespace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? "\\" : "") . config('cubeta-starter.service_namespace') . "\\{$this->version}"
            : ($withStart ? "\\" : "") . config('cubeta-starter.service_namespace') . "\\{$this->version}\\{$this->modelName}";
    }

    public function getServiceInterfaceClassString(): string
    {
        return "\\" . config('cubeta-starter.service_namespace') . "\\{$this->modelName}\\{$this->getServiceInterfaceName()}";
    }

    public function getServiceInterfacePath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.service_path') . ("/{$this->version}/{$this->modelName}/{$this->getServiceInterfaceName()}.php"));
    }

    public function getTestPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.test_path') . "/{$this->getTestName()}.php");
    }

    public function getTestClassString(): string
    {
        return "\\" . config('cubeta-starter.test_namespace') . "\\{$this->getTestName()}";
    }

    public function getMigrationPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.migration_path') . "/{$this->getMigrationName()}.php");
    }

    public function getViewPath(string $type): CubePath
    {
        $viewsPath = 'resources/views/dashboard/' . $this->viewNaming();

        return match ($type) {
            'show' => CubePath::make("$viewsPath/show.blade.php"),
            "create" => CubePath::make("$viewsPath/create.blade.php"),
            "update", "edit" => CubePath::make("$viewsPath/edit.blade.php"),
            "index" => CubePath::make("$viewsPath/index.blade.php"),
        };
    }
}
