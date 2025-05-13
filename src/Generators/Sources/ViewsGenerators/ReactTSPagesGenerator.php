<?php

namespace Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators;

use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\App\Models\Settings\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\App\Models\Settings\CubeAttribute;
use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\App\Models\Settings\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\InertiaReactTSController;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\FormPageStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\IndexPageStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\ShowPageStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Typescript\TsInterfaceStubBuilder;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Cubeta\CubetaStarter\Traits\WebGeneratorHelper;

class ReactTSPagesGenerator extends InertiaReactTSController
{
    use WebGeneratorHelper, StringsGenerator;

    public function run(bool $override = false): void
    {
        if (!Settings::make()->getFrontendType() == FrontendTypeEnum::REACT_TS) {
            CubeLog::add(new CubeError("Install react-ts tools by running [php artisan cubeta:install react-ts && php artisan cubeta:install react-ts-packages] then try again", happenedWhen: "Generating a {$this->table->modelName} react pages"));
            return;
        }

        $routes = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);

        $this->generateTypescriptModel();

        $this->generateUpdateFormPage($routes['update']);
        $this->generateCreateFormPage($routes['store']);

        $this->generateShowPage();
        $this->generateIndexPage();

        CodeSniffer::make()
            ->setModel($this->table)
            ->checkForTsInterfaces()
            ->checkForReactTSPagesAndControllerRelations($this->actor);
    }

    public function generateShowPage(): void
    {
        $routes = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);
        $pageName = $this->table->viewNaming();
        $showPagePath = CubePath::make("resources/js/Pages/dashboard/$pageName/Show.tsx");

        $builder = ShowPageStubBuilder::make()
            ->modelName($this->table->modelNaming())
            ->modelVariable($this->table->variableNaming())
            ->editRouteName($routes['edit']);

        $this->table->attributes()
            ->whereInstanceOf(HasReactTsDisplayComponentString::class)
            ->each(function (CubeAttribute|HasReactTsDisplayComponentString $attr) use ($builder) {
                if ($attr->isText() || $attr->isTextable()) {
                    $builder->bigField($attr->displayComponentString());
                } else {
                    $builder->smallField($attr->displayComponentString());
                }
            });

        $this->table->relations()
            ->whereInstanceOf(HasReactTsDisplayComponentString::class)
            ->each(function (CubeRelation|HasReactTsDisplayComponentString $rel) use ($builder) {
                if ($rel->exists() && $rel->getTSModelPath()->exist()) {
                    $builder->smallField($rel->displayComponentString());
                }
            });

        $builder->generate($showPagePath, $this->override);
    }

    public function generateIndexPage(): void
    {
        $pageName = $this->table->viewNaming();
        $indexPagePath = CubePath::make("resources/js/Pages/dashboard/$pageName/Index.tsx");
        $routes = $this->getRouteNames($this->table, ContainerType::WEB, $this->actor);
        $builder = IndexPageStubBuilder::make()
            ->modelName($this->table->modelNaming())
            ->modelVariable($this->table->variableNaming())
            ->createRoute($routes['create'])
            ->dataRoute($routes['data'])
            ->indexRoute($routes['index'])
            ->importRoute($routes['import'])
            ->exportRoute($routes['export'])
            ->importExampleRoute($routes['import_example']);

        $this->table->attributes()
            ->whereInstanceOf(HasDataTableColumnObjectString::class)
            ->each(function (HasDataTableColumnObjectString|CubeAttribute $attr) use ($builder) {
                if (!$attr->isText() && !$attr->isTextable()) {
                    $builder->column($attr->datatableColumnObject($this->actor));
                }
            });

        $this->table->relations()
            ->whereInstanceOf(HasDataTableColumnObjectString::class)
            ->filter(fn(CubeRelation $relation) => $relation->exists())
            ->each(function (HasDataTableColumnObjectString|CubeRelation $rel) use ($builder) {
                if ($rel->exists() && $rel->getTSModelPath()->exist() && !$rel->getTable()->titleable()->isTextable()) {
                    $builder->column($rel->datatableColumnObject($this->actor));
                }
            });

        $builder->generate($indexPagePath, $this->override);
    }

    private function generateTypescriptModel(): void
    {
        $interfacePath = $this->table->getTSModelPath();
        $builder = TsInterfaceStubBuilder::make()
            ->modelName($this->table->modelNaming());

        $this->table
            ->attributes()
            ->merge($this->table->relations())
            ->whereInstanceOf(HasInterfacePropertyString::class)
            ->each(function (HasInterfacePropertyString|CubeAttribute|CubeRelation $item) use ($builder) {
                if ($item instanceof CubeRelation) {
                    if (!$item->getTable()->getTSModelPath()->exist()) {
                        return true;
                    } else {
                        $string = $item->interfacePropertyString();
                    }
                } else {
                    $string = $item->interfacePropertyString();
                }
                $builder->property($string);
                return true;
            });

        $builder->generate($interfacePath);
    }

    /**
     * @param string $updateRoute
     * @return void
     */
    private function generateUpdateFormPage(string $updateRoute): void
    {
        $pageName = $this->table->viewNaming();
        $formPath = CubePath::make("resources/js/Pages/dashboard/$pageName/Edit.tsx");
        $builder = FormPageStubBuilder::make()
            ->componentName("Edit")
            ->formTitle("Edit {$this->table->modelNaming()}")
            ->componentProps("{{$this->table->variableNaming()}}:{{$this->table->variableNaming()}:{$this->table->modelNaming()}}")
            ->import(new TsImportString($this->table->modelNaming(), "@/Models/{$this->table->modelNaming()}"))
            ->action("post(route(\"{$updateRoute}\" , {$this->table->variableNaming()}.id));")
            ->when(
                $this->table->hasTranslatableAttribute(),
                fn($builder) => $builder->translatableContextOpenTag("<TranslatableInputsContext>")
                    ->translatableContextCloseTag("</TranslatableInputsContext>")
                    ->import(new TsImportString("TranslatableInputsContext", "@/Contexts/TranslatableInputsContext"))
            )->formFieldInterface(new InterfacePropertyString("_method", "'PUT'|'POST'", true));

        $builder->defaultValue("_method", "'PUT'");

        $this->table->attributes()
            ->each(function (CubeAttribute|HasReactTsInputString $attr) use ($builder) {
                if (!$attr->isFile()) {
                    $builder->defaultValue($attr->name, "{$this->table->variableNaming()}?.{$attr->name}");
                }

                if ($attr instanceof HasReactTsInputString) {
                    if ($attr->isText() || $attr->isTextable()) {
                        $builder->bigField($attr->inputComponent("update", $this->actor));
                    } else {
                        $builder->smallField($attr->inputComponent("update", $this->actor));
                    }
                }

                if ($attr instanceof HasInterfacePropertyString) {
                    $builder->formFieldInterface($attr->interfacePropertyString());
                }
            });

        $this->table->relations()
            ->whereInstanceOf(HasReactTsInputString::class)
            ->filter(fn(CubeRelation $relation) => $relation->getTable()->getTSModelPath()->exist())
            ->each(function (CubeRelation|HasReactTsInputString $relation) use ($builder) {
                $builder->smallField($relation->inputComponent("update", $this->actor));
            });

        $builder->generate($formPath, $this->override);
    }

    /**
     * @param string $storeRoute
     * @return void
     */
    private function generateCreateFormPage(string $storeRoute): void
    {
        $pageName = $this->table->viewNaming();
        $formPath = CubePath::make("resources/js/Pages/dashboard/$pageName/Create.tsx");
        $builder = FormPageStubBuilder::make()
            ->componentName("Create")
            ->formTitle("Add New {$this->table->modelNaming()}")
            ->action("post(route(\"{$storeRoute}\"));")
            ->when(
                $this->table->hasTranslatableAttribute(),
                fn($builder) => $builder->translatableContextOpenTag("<TranslatableInputsContext>")
                    ->translatableContextCloseTag("</TranslatableInputsContext>")
                    ->import(new TsImportString("TranslatableInputsContext", "@/Contexts/TranslatableInputsContext"))
            )->formFieldInterface(new InterfacePropertyString("_method", "'PUT'|'POST'", true));

        $this->table->attributes()
            ->each(function (CubeAttribute $attr) use ($builder) {
                if ($attr instanceof HasReactTsInputString) {
                    if ($attr->isText() || $attr->isTextable()) {
                        $builder->bigField($attr->inputComponent("store", $this->actor));
                    } else {
                        $builder->smallField($attr->inputComponent("store", $this->actor));
                    }
                }

                if ($attr instanceof HasInterfacePropertyString) {
                    $builder->formFieldInterface($attr->interfacePropertyString());
                }
            });

        $this->table->relations()
            ->whereInstanceOf(HasReactTsInputString::class)
            ->filter(fn(CubeRelation $relation) => $relation->getTable()->getTSModelPath()->exist())
            ->each(function (CubeRelation|HasReactTsInputString $relation) use ($builder) {
                $builder->smallField($relation->inputComponent("store", $this->actor));
            });

        $builder->generate($formPath, $this->override);
    }
}
