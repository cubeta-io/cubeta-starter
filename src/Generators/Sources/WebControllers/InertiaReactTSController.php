<?php

namespace Cubeta\CubetaStarter\Generators\Sources\WebControllers;

use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators\ReactTSPagesGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Controllers\ControllerStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;

class InertiaReactTSController extends AbstractGenerator
{
    use RouteBinding, WebGeneratorHelper;

    public function run(bool $override = false): void
    {
        if (!Settings::make()->getFrontendType() == FrontendTypeEnum::REACT_TS) {
            CubeLog::add(new CubeError("Install react-ts tools by running [php artisan cubeta:install react-ts && php artisan cubeta:install react-ts-packages] then try again", happenedWhen: "Generating a {$this->table->modelName} web controller"));
            return;
        }

        $modelNameCamelCase = $this->table->variableNaming();
        $routesNames = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);
        $controllerPath = $this->table->getWebControllerPath();
        $loadedRelations = $this->table
            ->relations()
            ->filter(fn(CubeRelation $rel) => $rel->getModelPath()->exist())
            ->stringifyEachOne()
            ->implode(",");

        $controllerPath->ensureDirectoryExists();
        $pagesPaths = $this->getTsxPagesPath();


        ControllerStubBuilder::make()
            ->namespace($this->table->getWebControllerNameSpace(false, true))
            ->modelName($this->table->modelNaming())
            ->modelNameCamelCase($modelNameCamelCase)
            ->createPage($pagesPaths['create'])
            ->updatePage($pagesPaths['edit'])
            ->showPage($pagesPaths['show'])
            ->indexPage($pagesPaths['index'])
            ->indexRoute($routesNames['index'])
            ->relations($loadedRelations)
            ->serviceNamespace($this->table->getServiceNamespace(false))
            ->requestNamespace($this->table->getRequestNameSpace(false))
            ->modelNamespace($this->table->getModelNameSpace(false))
            ->serviceName($this->table->getServiceName())
            ->generate($controllerPath, $this->override);

        $this->addRoute($this->table, $this->actor, ContainerType::WEB);
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
            'index' => 'dashboard/' . $viewName . '/Index',
            'edit' => 'dashboard/' . $viewName . '/Edit',
            'create' => 'dashboard/' . $viewName . '/Create',
            'show' => 'dashboard/' . $viewName . '/Show',
        ];
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
            "    ,{\n        href: route(\"%s\"),\n        title: \"%s\",\n\ticon:() => <TableCells />,\n    },\n",
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
            return FileUtils::fixArrayOrObjectCommas($matches[1] . $matches[2] . "\n" . $newSidebarItem . "\n" . $matches[3]);
        };

        $updatedContent = preg_replace_callback($pattern, $callback, $fileContent);

        $sidebarPath->putContent($updatedContent);
        //TODO:: fix the import to use the alias
        FileUtils::tsAddImportStatement('import TableCells from "../icons/TableCells";', $sidebarPath);
        $sidebarPath->format();
        CubeLog::add(new ContentAppended($newSidebarItem, $sidebarPath->fullPath));
    }
}
