<?php

namespace Cubeta\CubetaStarter\Generators\Sources\WebControllers;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\AbstractGenerator;
use Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators\ReactTSPagesGenerator;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Components\SidebarItemString;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Controllers\ControllerStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class InertiaReactTSController extends AbstractGenerator
{
    use RouteBinding, WebGeneratorHelper;

    /**
     * @throws FileNotFoundException
     */
    public function run(bool $override = false): void
    {
        if (!Settings::make()->getFrontendType() == FrontendTypeEnum::REACT_TS) {
            CubeLog::error(
                "Install react-ts tools by running [php artisan cubeta:install react-ts && php artisan cubeta:install react-ts-packages] then try again",
                context: "Generating a {$this->table->modelName} web controller"
            );
            return;
        }

        $modelNameCamelCase = $this->table->variableNaming();
        $controllerPath = $this->table->getWebControllerPath();
        $loadedRelations = $this->table
            ->relations()
            ->filter(fn(CubeRelation $rel) => $rel->loadable())
            ->stringifyEachOne(fn(CubeRelation $relation) => $relation->method())
            ->implode(",");

        $controllerPath->ensureDirectoryExists();
        $indexRoute = $this->table->indexRoute($this->actor, ContainerType::WEB);


        ControllerStubBuilder::make()
            ->namespace($this->table->getWebControllerNameSpace(false, true))
            ->modelName($this->table->modelNaming())
            ->modelNameCamelCase($modelNameCamelCase)
            ->createPage($this->table->createView($this->actor)->name)
            ->updatePage($this->table->editView($this->actor)->name)
            ->showPage($this->table->showView($this->actor)->name)
            ->indexPage($this->table->indexView($this->actor)->name)
            ->indexRoute($indexRoute->name)
            ->relations($loadedRelations)
            ->serviceNamespace($this->table->getServiceNamespace(false))
            ->requestNamespace($this->table->getRequestNameSpace(false))
            ->modelNamespace($this->table->getModelNameSpace(false))
            ->serviceName($this->table->getServiceName())
            ->resourceName($this->table->getResourceName())
            ->resourceNamespace($this->table->getResourceNameSpace(false))
            ->generate($controllerPath, $this->override);

        $this->addRoute($this->table, $this->actor, ContainerType::WEB);
        $this->addSidebarItem($indexRoute->name, $this->table->modelName);

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

    public function addSidebarItem(string $indexRoute, string $title): void
    {
        $sidebarPath = CubePath::make("/resources/js/components/ui/Sidebar.tsx");

        if (!$sidebarPath->exist()) {
            CubeLog::add(new NotFound("$sidebarPath->fullPath", "Adding $title To Sidebar items when generating web controller"));
            return;
        }

        $fileContent = $sidebarPath->getContent();

        $newSidebarItem = new SidebarItemString($title, $indexRoute);

        // Regex pattern to match the sidebarItems array
        $pattern = '/(const\s+sidebarItems\s*=\s*\[\s*)(.*?)(\s*];)/si';

        if (!preg_match($pattern, $fileContent)) {
            CubeLog::failedAppending($newSidebarItem, $sidebarPath->fullPath, "adding the route : {$indexRoute} to the sidebar page");
            return;
        }

        if (FileUtils::contentExistInFile($sidebarPath, $newSidebarItem)) {
            CubeLog::contentAlreadyExists($newSidebarItem, $sidebarPath->fullPath, "Adding new sidebar item to the sidebar component");
            return;
        }

        $callback = function ($matches) use ($newSidebarItem) {
            return FileUtils::fixArrayOrObjectCommas($matches[1] . $matches[2] . "\n" . ",$newSidebarItem," . "\n" . $matches[3]);
        };

        $updatedContent = preg_replace_callback($pattern, $callback, $fileContent);
        $sidebarPath->putContent($updatedContent);
        FileUtils::tsAddImportStatement('import TableCells from "@/Components/icons/TableCells";', $sidebarPath);
        $sidebarPath->format();
        CubeLog::contentAppended($newSidebarItem, $sidebarPath->fullPath);
    }
}
