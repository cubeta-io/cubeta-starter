<?php

namespace Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators;

use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\FrontendTypeEnum;
use Cubeta\CubetaStarter\Generators\Sources\WebControllers\BladeControllerGenerator;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Logs\CubeError;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Settings\CubeAttribute;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\Settings\Settings;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\Blade\Components\FormLocalSelectorString;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\FormViewStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\IndexViewStubBuilder;
use Cubeta\CubetaStarter\Stub\Builders\Web\Blade\Views\ShowViewStubBuilder;
use Cubeta\CubetaStarter\Traits\RouteBinding;

class BladeViewsGenerator extends BladeControllerGenerator
{
    use RouteBinding;

    public static string $key = "views";

    public function run(bool $override = false): void
    {
        if (!Settings::make()->getFrontendType() == FrontendTypeEnum::BLADE) {
            CubeLog::add(new CubeError("Install web tools by running [php artisan cubeta:install web && php artisan cubeta:install web-packages] then try again", happenedWhen: "Generating a {$this->table->modelName} blade views"));
            return;
        }

        $modelVariable = $this->table->variableNaming();
        $hasTranslatableFields = $this->table->hasTranslatableAttribute();

        $indexPath = $this->table->indexView($this->actor)->path;
        $showPath = $this->table->showView($this->actor)->path;
        $createFormPath = $this->table->createView($this->actor)->path;
        $updateFormPath = $this->table->editView($this->actor)->path;

        $createInputs = $this->getInputsFields();
        $updateInputs = $this->getInputsFields("update");

        // create view
        FormViewStubBuilder::make()
            ->method("POST")
            ->title("Create {$this->table->modelName}")
            ->localizationSelector($hasTranslatableFields ? new FormLocalSelectorString() : "")
            ->submitRoute($this->table->storeRoute($this->actor, ContainerType::WEB)->name)
            ->inputs($createInputs)
            ->generate($createFormPath, $this->override);

        // update view
        FormViewStubBuilder::make()
            ->method("PUT")
            ->title("Update {$this->table->modelName}")
            ->localizationSelector($hasTranslatableFields ? new FormLocalSelectorString() : "")
            ->submitRoute($this->table->updateRoute($this->actor, ContainerType::WEB)->name)
            ->updateParameters(", \${$modelVariable}->id")
            ->inputs($updateInputs)
            ->type($modelVariable, $this->table->getModelClassString())
            ->generate($updateFormPath, $this->override);

        // show view
        ShowViewStubBuilder::make()
            ->modelClassString($this->table->getModelClassString())
            ->modelVariable($this->table->variableNaming())
            ->modelName($this->table->modelNaming())
            ->titleable($this->table->titleable()->name)
            ->editRoute($this->table->editRoute($this->actor)->name)
            ->components(
                $this->table->attributes()
                    ->map(fn(CubeAttribute $attribute) => $attribute->bladeDisplayComponent()?->__toString())
                    ->implode("\n")
            )->generate($showPath, $this->override);

        // index view
        IndexViewStubBuilder::make()
            ->tableName(ucfirst($this->table->tableNaming()))
            ->createRoute($this->table->createRoute($this->actor)->name)
            ->dataRoute($this->table->dataRoute($this->actor)->name)
            ->exportRoute($this->table->exportRoute($this->actor, ContainerType::WEB)->name)
            ->importRoute($this->table->importRoute($this->actor, ContainerType::WEB)->name)
            ->exampleRoute($this->table->importExampleRoute($this->actor, ContainerType::WEB)->name)
            ->modelClassString($this->table->getModelClassString())
            ->htmlColumns(
                $this->table->attributes()
                    ->filter(fn(CubeAttribute $attribute) => !$attribute->isTextable())
                    ->merge($this->table->relations()->filter(fn(CubeRelation $relation) => $relation->exists()))
                    ->whereInstanceOf(HasHtmlTableHeader::class)
                    ->map(fn(HasHtmlTableHeader $attribute) => $attribute->htmlTableHeader()->__toString())
                    ->implode("\n")
            )->dataTableObjectColumns(
                $this->table->attributes()
                    ->filter(fn(CubeAttribute $attribute) => !$attribute->isTextable())
                    ->merge($this->table->relations()->filter(fn(CubeRelation $relation) => $relation->exists()))
                    ->whereInstanceOf(HasDatatableColumnString::class)
                    ->map(fn(HasDatatableColumnString $attribute) => $attribute->datatableColumnString()->__toString())
                    ->implode("\n")
            )->generate($indexPath, $this->override);
    }

    private function getInputsFields(string $formType = "store"): string
    {
        return $this->table
                ->attributes()
                ->whereInstanceOf(HasBladeInputComponent::class)
                ->map(fn(HasBladeInputComponent $attr) => $attr->bladeInputComponent($formType, $this->actor)->__toString())
                ->implode("\n") . "\n" . $this->table
                ->relations()
                ->whereInstanceOf(HasBladeInputComponent::class)
                ->filter(fn(CubeRelation $rel) => $rel->getWebControllerPath()->exist()
                    && $rel->relationModel()->getModelPath()->exist()
                    && ClassUtils::isMethodDefined($rel->getWebControllerPath(), "allPaginatedJson")
                )->map(fn(HasBladeInputComponent $attr) => $attr->bladeInputComponent($formType, $this->actor)->__toString())
                ->implode("\n");
    }
}
