<?php

namespace Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators;

use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\InertiaReactTSController;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\TsImportString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\FormPageStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\IndexPageStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Pages\ShowPageStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\InertiaReact\Typescript\TsInterfaceStubBuilder;

class ReactTSPagesGenerator extends InertiaReactTSController
{
    public function run(bool $override = false): void
    {
        if (!Settings::make()->getFrontendType() == FrontendTypeEnum::REACT_TS) {
            CubeLog::add(new CubeError("Install react-ts tools by running [php artisan cubeta:install react-ts && php artisan cubeta:install react-ts-packages] then try again", happenedWhen: "Generating a {$this->table->modelName} react pages"));
            return;
        }

        $this->generateTypescriptModel();

        $this->generateUpdateFormPage();
        $this->generateCreateFormPage();

        $this->generateShowPage();
        $this->generateIndexPage();

        CodeSniffer::make()
            ->setModel($this->table)
            ->setActor($this->actor)
            ->checkForTsInterfaces()
            ->checkForReactTSPagesAndControllerRelations($this->actor);
    }

    public function generateShowPage(): void
    {
        $showPagePath = $this->table->showView($this->actor)->path;

        $builder = ShowPageStubBuilder::make()
            ->modelName($this->table->modelNaming())
            ->modelVariable($this->table->variableNaming())
            ->editRouteName($this->table->editRoute($this->actor)->name);

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
        $indexPagePath = $this->table->indexView($this->actor)->path;
        $builder = IndexPageStubBuilder::make()
            ->modelName($this->table->modelNaming())
            ->modelVariable($this->table->variableNaming())
            ->createRoute($this->table->createRoute($this->actor)->name)
            ->dataRoute($this->table->dataRoute($this->actor)->name)
            ->indexRoute($this->table->indexRoute($this->actor, ContainerType::WEB)->name)
            ->importRoute($this->table->importRoute($this->actor, ContainerType::WEB)->name)
            ->exportRoute($this->table->exportRoute($this->actor, ContainerType::WEB)->name)
            ->importExampleRoute($this->table->importExampleRoute($this->actor, ContainerType::WEB)->name);

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
                if ($rel->exists() && $rel->getTSModelPath()->exist() && !$rel->relationModel()->titleable()->isTextable()) {
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
                    if (!$item->relationModel()->getTSModelPath()->exist()) {
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
     * @return void
     */
    private function generateUpdateFormPage(): void
    {
        $updateRoute = $this->table->updateRoute($this->actor, ContainerType::WEB)->name;
        $formPath = $this->table->editView($this->actor)->path;
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
                if (!$attr->isFile() && !$attr->isKey()) {
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
            ->filter(fn(CubeRelation $relation) => $relation->relationModel()->getTSModelPath()->exist())
            ->each(function (CubeRelation|HasReactTsInputString $relation) use ($builder) {
                $builder->smallField($relation->inputComponent("update", $this->actor));
                $builder->defaultValue($relation->keyName(), "{$this->table->variableNaming()}?.{$relation->keyName()}");
            });

        $builder->generate($formPath, $this->override);
    }

    /**
     * @return void
     */
    private function generateCreateFormPage(): void
    {
        $storeRoute = $this->table->storeRoute($this->actor, ContainerType::WEB)->name;
        $formPath = $this->table->createView($this->actor)->path;
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
            ->filter(fn(CubeRelation $relation) => $relation->relationModel()->getTSModelPath()->exist())
            ->each(function (CubeRelation|HasReactTsInputString $relation) use ($builder) {
                $builder->smallField($relation->inputComponent("store", $this->actor));
            });

        $builder->generate($formPath, $this->override);
    }
}
