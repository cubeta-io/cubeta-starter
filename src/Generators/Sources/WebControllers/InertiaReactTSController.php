<?php

namespace Cubeta\CubetaStarter\Generators\Sources\WebControllers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators\ReactPagesGenerator;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;

class InertiaReactTSController extends AbstractGenerator
{
    use RouteBinding, WebGeneratorHelper;

    public function run(bool $override = false): void
    {
        $modelNameCamelCase = $this->table->variableNaming();
        $idVariable = $this->table->idVariable();
        $controllerPath = $this->table->getWebControllerPath();

        if ($controllerPath->exist()) {
            $controllerPath->logAlreadyExist("Generating Web Controller For  ({$this->table->modelName}) Model");
            return;
        }

        $controllerPath->ensureDirectoryExists();
        $pagesPaths = $this->getTsxPagesPath();

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.web_controller_namespace'),
            '{requestNamespace}' => config('cubeta-starter.request_namespace'),
            '{modelName}' => $this->table->modelName,
            '{modelNamespace}' => config('cubeta-starter.model_namespace'),
            '{serviceNamespace}' => config('cubeta-starter.service_namespace'),
            '{modelNameCamelCase}' => $modelNameCamelCase,
            '{createForm}' => $pagesPaths['create'],
        ];

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath);
        $this->addRoute($this->table, $this->actor, ContainerType::WEB);
        $controllerPath->format();

        (new ReactPagesGenerator(
            fileName: $this->fileName ,
            attributes: $this->attributes ,
            nullables: $this->nullables ,
            actor: $this->actor
        ))->run();
    }

    /**
     * @return array{index:string , edit:string , create:string , show:string}
     */
    public function getTsxPagesPath(): array
    {
        $viewName = $this->table->viewNaming();
        return [
            'index' => 'dashboard/' . $viewName . '/Index',
            'edit' => 'dashboard/' . $viewName . '/Edit',
            'create' => 'dashboard/' . $viewName . '/Create',
            'show' => 'dashboard/' . $viewName . '/Show',
        ];
    }

    public function stubsPath(): string
    {
        return __DIR__ . '/../../../stubs/Inertia/php/controller.stub';
    }
}
