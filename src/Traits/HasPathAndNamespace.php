<?php

namespace Cubeta\CubetaStarter\Traits;

use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Settings\Settings;

use function Illuminate\Filesystem\join_paths;

/**
 * A trait providing methods to generate paths, namespaces, and class strings
 * for various application components, such as models, controllers, requests,
 * resources, factories, seeders, repositories, services, tests, migrations, and views.
 */
trait HasPathAndNamespace
{
    public function getApiControllerClassString(): string
    {
        return '\\'.config('cubeta-starter.api_controller_namespace')."\\$this->version\\{$this->getControllerName()}";
    }

    public function getApiControllerNameSpace($withStart = true): string
    {
        return ($withStart ? '\\' : '').config('cubeta-starter.api_controller_namespace')."\\$this->version";
    }

    public function getApiControllerPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.api_controller_path')."/$this->version/{$this->getControllerName()}.php");
    }

    public function getDtoClassString(): string
    {
        return '\\'.config('cubeta-starter.dto_namespace')."\\{$this->version}\\{$this->modelNaming()}\\{$this->getDtoName()}";
    }

    public function getDtoNameSpace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? '\\' : '').config('cubeta-starter.dto_namespace')."\\{$this->version}\\{$this->modelNaming()}"
            : ($withStart ? '\\' : '').config('cubeta-starter.dto_namespace')."\\{$this->version}\\{$this->modelNaming()}\\{$this->getDtoName()}";
    }

    public function getDtoPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.dto_path')."/{$this->version}/{$this->modelNaming()}/{$this->getDtoName()}.php");
    }

    public function getFactoryClassString(): string
    {
        return '\\'.config('cubeta-starter.factory_namespace')."\\{$this->getFactoryName()}";
    }

    public function getFactoryPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.factory_path')."/{$this->getFactoryName()}.php");
    }

    public function getMigrationPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.migration_path')."/{$this->getMigrationName()}.php");
    }

    public function getModelClassString(): string
    {
        return '\\'.config('cubeta-starter.model_namespace')."\\{$this->modelNaming()}";
    }

    public function getModelNameSpace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? '\\' : '').config('cubeta-starter.model_namespace')
            : ($withStart ? '\\' : '').config('cubeta-starter.model_namespace').'\\'.$this->modelNaming();
    }

    public function getModelPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.model_path')."/{$this->modelNaming()}.php");
    }

    public function getRepositoryClassString(): string
    {
        return '\\'.config('cubeta-starter.repository_namespace')."\\{$this->getRepositoryName()}";
    }

    public function getRepositoryNameSpace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? '\\' : '').config('cubeta-starter.repository_namespace')
            : ($withStart ? '\\' : '').config('cubeta-starter.repository_namespace').'\\'.$this->getRepositoryName();
    }

    public function getRepositoryPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.repository_path')."/{$this->getRepositoryName()}.php");
    }

    public function getRequestClassString(): string
    {
        return '\\'.config('cubeta-starter.request_namespace')."\\{$this->version}\\{$this->modelNaming()}\\{$this->getRequestName()}";
    }

    public function getRequestNameSpace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? '\\' : '').config('cubeta-starter.request_namespace')."\\{$this->version}\\{$this->modelNaming()}"
            : ($withStart ? '\\' : '').config('cubeta-starter.request_namespace')."\\{$this->version}\\{$this->modelNaming()}\\{$this->getRequestName()}";
    }

    public function getRequestPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.request_path')."/{$this->version}/{$this->modelNaming()}/{$this->getRequestName()}.php");
    }

    public function getResourceClassString(): string
    {
        return '\\'.config('cubeta-starter.resource_namespace')."\\{$this->version}\\{$this->getResourceName()}";
    }

    public function getResourceNameSpace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? '\\' : '').config('cubeta-starter.resource_namespace')."\\{$this->version}"
            : ($withStart ? '\\' : '').config('cubeta-starter.resource_namespace')."\\{$this->version}\\{$this->getResourceName()}";
    }

    public function getResourcePath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.resource_path')."/{$this->version}/{$this->getResourceName()}.php");
    }

    public function getSeederClassString(): string
    {
        return '\\'.config('cubeta-starter.seeder_namespace')."\\{$this->getSeederName()}";
    }

    public function getSeederNameSpace(): string
    {
        return config('cubeta-starter.seeder_namespace');
    }

    public function getSeederPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.seeder_path')."/{$this->getSeederName()}.php");
    }

    public function getServiceNamespace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? '\\' : '').config('cubeta-starter.service_namespace')."\\{$this->version}\\{$this->modelNaming()}"
            : ($withStart ? '\\' : '').config('cubeta-starter.service_namespace')."\\{$this->version}\\{$this->modelNaming()}\\{$this->getServiceName()}";
    }

    public function getServicePath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.service_path')."/{$this->version}/{$this->modelNaming()}/{$this->getServiceName()}.php");
    }

    public function getTestClassString(?string $actor = null): string
    {
        if (empty($actor) || $actor == 'none') {
            return '\\'.config('cubeta-starter.test_namespace')."\\{$this->getTestName()}";
        }

        $actor = str($actor)->studly()->singular();

        return '\\'.config('cubeta-starter.test_namespace')."\\$actor\\{$this->getTestName()}";
    }

    public function getTestNamespace(?string $actor = null, bool $withStart = true, bool $prefixOnly = false): string
    {
        if (empty($actor) || $actor == 'none') {
            return $prefixOnly
                ? ($withStart ? '\\' : '').config('cubeta-starter.test_namespace')
                : ($withStart ? '\\' : '').config('cubeta-starter.test_namespace').'\\'.$this->getTestName();
        }

        $actor = str($actor)->studly()->singular();

        return $prefixOnly
            ? ($withStart ? '\\' : '').config('cubeta-starter.test_namespace')."\\$actor"
            : ($withStart ? '\\' : '').config('cubeta-starter.test_namespace')."\\$actor\\".$this->getTestName();
    }

    public function getTestPath(?string $actor = null): CubePath
    {
        if (empty($actor) || $actor == 'none') {
            return CubePath::make(config('cubeta-starter.test_path')."/{$this->getTestName()}.php");
        }

        $actor = str($actor)->studly()->singular();

        return CubePath::make(config('cubeta-starter.test_path')."/$actor/{$this->getTestName()}.php");
    }

    public function getTSModelPath(): CubePath
    {
        return CubePath::make(join_paths('resources/js/models/', str($this->modelNaming())->kebab()->lower().'.ts'));
    }

    /**
     * how the generated controllers get the validated data from the injected validation class
     */
    public function getValidatedDataString(): string
    {
        return Settings::make()->getValidationType()->controllersUseDto()
            ? '$dto->toArray()'
            : '$request->validated()';
    }

    /**
     * the name of the validation class used by the generated controllers
     * it depends on the chosen validation type within the settings file
     */
    public function getValidationClassName(): string
    {
        return Settings::make()->getValidationType()->controllersUseDto()
            ? $this->getDtoName()
            : $this->getRequestName();
    }

    /**
     * the namespace of the validation class used by the generated controllers
     */
    public function getValidationNameSpace(bool $withStart = true): string
    {
        return Settings::make()->getValidationType()->controllersUseDto()
            ? $this->getDtoNameSpace($withStart)
            : $this->getRequestNameSpace($withStart);
    }

    /**
     * the variable name of the injected validation class within the generated controllers
     */
    public function getValidationVariableName(): string
    {
        return Settings::make()->getValidationType()->controllersUseDto()
            ? 'dto'
            : 'request';
    }

    public function getWebControllerClassString(): string
    {
        return '\\'.config('cubeta-starter.web_controller_namespace')."\\{$this->getControllerName()}";
    }

    public function getWebControllerNameSpace(bool $withStart = true, bool $prefixOnly = false): string
    {
        return $prefixOnly
            ? ($withStart ? '\\' : '').config('cubeta-starter.web_controller_namespace')."\\$this->version"
            : ($withStart ? '\\' : '').config('cubeta-starter.web_controller_namespace')."\\$this->version\\{$this->getControllerName()}";
    }

    public function getWebControllerPath(): CubePath
    {
        return CubePath::make(config('cubeta-starter.web_controller_path')."/$this->version/{$this->getControllerName()}.php");
    }

    public function tsModelImportPath(): string
    {
        return '@/models/'.str($this->modelNaming())->kebab()->lower();
    }
}
