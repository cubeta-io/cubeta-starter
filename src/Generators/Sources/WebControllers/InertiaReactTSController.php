<?php

namespace Cubeta\CubetaStarter\Generators\Sources\WebControllers;

use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators\ReactTSPagesGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;

class InertiaReactTSController extends AbstractGenerator
{
    use RouteBinding, WebGeneratorHelper;

    public function run(bool $override = false): void
    {
        $modelNameCamelCase = $this->table->variableNaming();
        $routesNames = $this->getRoutesNames($this->table, $this->actor);
        $controllerPath = $this->table->getWebControllerPath();

        if ($controllerPath->exist()) {
            $controllerPath->logAlreadyExist("Generating Web Controller For  ({$this->table->modelName}) Model");
            return;
        }

        $loadedRelations = $this->table
            ->relations()
            ->filter(fn(CubeRelation $rel) => $rel->getModelPath()->exist())
            ->map(fn(CubeRelation $rel) => "'{$rel->method()}'")
            ->implode(',');

        $controllerPath->ensureDirectoryExists();
        $pagesPaths = $this->getTsxPagesPath();

        $stubProperties = [
            '{{namespace}}'          => $this->table->getWebControllerNameSpace(false, true),
            '{{modelName}}'          => $this->table->modelName,
            '{{modelNameCamelCase}}' => $modelNameCamelCase,
            '{{createForm}}'         => $pagesPaths['create'],
            '{{updateForm}}'         => $pagesPaths['edit'],
            '{{indexRoute}}'         => $routesNames['index'],
            '{{showPage}}'           => $pagesPaths['show'],
            '{{indexPage}}'          => $pagesPaths['index'],
            "{{relations}}"          => $loadedRelations,
            '{{serviceNamespace}}'   => $this->table->getServiceNamespace(false),
            '{{requestNamespace}}'   => $this->table->getRequestNameSpace(),
            '{{modelNamespace}}'     => $this->table->getModelNameSpace(false),
            '{{serviceName}}'        => $this->table->getServiceName()
        ];

        $this->generateFileFromStub($stubProperties, $controllerPath->fullPath);
        $this->addRoute($this->table, $this->actor, ContainerType::WEB);
        $controllerPath->format();

        $this->addSidebarItem($routesNames['index'], $this->table->modelName);

        (new ReactTSPagesGenerator(
            fileName: $this->fileName,
            attributes: $this->attributes,
            relations: $this->relations,
            nullables: $this->nullables,
            uniques: $this->uniques,
            actor: $this->actor,
            generatedFor: $this->generatedFor
        ))->run();
    }

    /**
     * @return array{index:string , edit:string , create:string , show:string}
     */
    public function getTsxPagesPath(): array
    {
        $viewName = $this->table->viewNaming();
        return [
            'index'  => 'dashboard/' . $viewName . '/Index',
            'edit'   => 'dashboard/' . $viewName . '/Edit',
            'create' => 'dashboard/' . $viewName . '/Create',
            'show'   => 'dashboard/' . $viewName . '/Show',
        ];
    }

    public function stubsPath(): string
    {
        return __DIR__ . '/../../../stubs/Inertia/php/controller.stub';
    }

    public function addSidebarItem(string $indexRoute, string $title): void
    {
        $sidebarPath = CubePath::make("/resources/js/components/ui/Sidebar.tsx");

        if (!$sidebarPath->exist()) {
            CubeLog::add(new NotFound("$sidebarPath->fullPath", "Adding $title To Sidebar items when generating web controller"));
            return;
        }

        $fileContent = $sidebarPath->getContent();

        $newSidebarItem = sprintf(
            "    ,{\n        href: route(\"%s\"),\n        title: \"%s\",\n    },\n",
            $indexRoute,
            $title
        );

        // Regex pattern to match the sidebarItems array
        $pattern = '/(const\s+sidebarItems\s*=\s*\[\s*)(.*?)(\s*];)/si';

        if (!preg_match($pattern, $fileContent)) {
            CubeLog::add(new FailedAppendContent($newSidebarItem, $sidebarPath->fullPath, "adding the route : {$indexRoute} to the sidebar page"));
            return;
        }

        $callback = function ($matches) use ($newSidebarItem) {
            return FileUtils::fixArrayOrObjectCommas($matches[1] . $matches[2] . "\n                " . $newSidebarItem . "\n            " . $matches[3]);
        };

        $updatedContent = preg_replace_callback($pattern, $callback, $fileContent);

        $sidebarPath->putContent($updatedContent);
        CubeLog::add(new ContentAppended($newSidebarItem, $sidebarPath->fullPath));
    }
}
