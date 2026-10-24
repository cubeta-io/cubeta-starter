<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\Traits\Makable;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\Helpers\TsFileUtils;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\Helpers\BladeFileUtils;
use Cubeta\CubetaStarter\Helpers\TranslationFileUtils;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelRelationMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFactoryRelationMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeDisplayComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Controllers\HasYajraDataTableRelationLinkColumnRenderer;

class CodeSniffer
{
    use Makable;
    use RouteBinding;

    private ?string $actor = null;

    private ?CubeTable $table = null;

    private function __construct()
    {
        //
    }

    public function checkForFactoryRelations(): static
    {
        if (! $this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn (CubeRelation $relation) => $relation->getModelPath()->exist() && $relation->loadable())
            ->each(function (CubeRelation|HasFactoryRelationMethod $relation) {
                // Category model
                $model = $relation->relationModel();

                // products relation of the category model in this case it will be a category has many products
                $reverseRelation = $relation->reverseRelation();

                if ($reverseRelation instanceof HasFactoryRelationMethod) {
                    $method = $reverseRelation->factoryRelationMethod();
                    $relatedPath = $model->getFactoryPath();
                    ClassUtils::addMethodToClass(
                        $model->getFactoryPath(),
                        $method->name,
                        "$method"
                    );
                    foreach ($method->imports as $import) {
                        FileUtils::addImportStatement($import, $relatedPath);
                    }
                }
            });

        return $this;
    }

    public function checkForModelsRelations(): static
    {
        if (! $this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn (CubeRelation $relation) => $relation->getModelPath()->exist())
            ->each(function (CubeRelation|HasModelRelationMethod $relation) {
                // Product model
                $model = $relation->relationModel();

                // category relation of the product model in this case, it will be the product belongs to a category
                $reverseRelation = $relation->reverseRelation();
                $relatedPath = $model->getModelPath();

                if ($reverseRelation instanceof HasModelRelationMethod) {
                    $method = $reverseRelation->modelRelationMethod();
                    $imports = $method->imports;
                    ClassUtils::addMethodToClass(
                        $relatedPath,
                        $method->name,
                        $method
                    );

                    if ($reverseRelation instanceof HasDocBlockProperty) {
                        $property = $reverseRelation->docBlockProperty();
                        ClassUtils::addToClassDocBlock(
                            $property,
                            $relatedPath
                        );
                        $imports = array_merge($imports, $property->imports);
                    }

                    foreach ($imports as $import) {
                        FileUtils::addImportStatement($import, $relatedPath);
                    }

                    $relationSearchableArray = "'{$reverseRelation->method()}' => [\n{$this->table->searchableColsAsString()}\n]\n,";
                    ClassUtils::addToMethodReturnArray(
                        $relatedPath,
                        'relationsSearchableArray',
                        $relationSearchableArray
                    );
                }
            });

        return $this;
    }

    public function checkForReactTSPagesAndControllerRelations(?string $actor = null): static
    {
        if (! $this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn (CubeRelation $rel) => $rel->getTSModelPath()->exist() && $rel->loadable())
            ->each(function (CubeRelation $rel) {
                $relatedIndexPagePath = $rel->relationModel()->indexView($this->actor)->path;
                $relatedCreatePagePath = $rel->relationModel()->createView($this->actor)->path;
                $relatedUpdatePagePath = $rel->relationModel()->editView($this->actor)->path;
                $relatedShowPagePath = $rel->relationModel()->showView($this->actor)->path;

                $reversedRelation = $rel->reverseRelation();
                TranslationFileUtils::addModelTranslations($rel->relationModel(), withReactJson: true);
                if ($reversedRelation instanceof HasDataTableColumnObjectString && $relatedIndexPagePath->exist()) {
                    $object = $reversedRelation->datatableColumnObject($this->actor);
                    $object->translationKey = $reversedRelation->translationKey();
                    TsFileUtils::addColumnToDataTable($relatedIndexPagePath, $object);
                    TsFileUtils::addImportStatement($object->imports, $relatedIndexPagePath);
                }

                if ($rel->isBelongsTo()
                    && ! ClassUtils::isMethodDefined($reversedRelation->getWebControllerPath(), 'data')
                ) {
                    return true;
                }

                if ($reversedRelation instanceof HasReactTsInputString
                    && $reversedRelation instanceof HasInterfacePropertyString
                    && $relatedCreatePagePath->exist()
                ) {
                    $input = $reversedRelation->inputComponent('store', $this->actor);
                    $input->translationKey = $reversedRelation->translationKey();
                    $interfaceProperty = new InterfacePropertyString(
                        $reversedRelation->keyName(),
                        $reversedRelation->singularRelation() ? 'numeric' : 'numeric[]',
                        $rel->relationModel()->getAttribute($reversedRelation->keyName())?->nullable ?? false
                    );
                    TsFileUtils::addNewInputToReactTSForm($input, $interfaceProperty, $relatedCreatePagePath);
                    TsFileUtils::addImportStatement($input->imports, $relatedCreatePagePath);
                }

                if ($reversedRelation instanceof HasReactTsInputString
                    && $reversedRelation instanceof HasInterfacePropertyString
                    && $relatedUpdatePagePath->exist()
                ) {
                    $input = $reversedRelation->inputComponent('update', $this->actor);
                    $input->translationKey = $reversedRelation->translationKey();
                    $interfaceProperty = new InterfacePropertyString(
                        $reversedRelation->keyName(),
                        $reversedRelation->singularRelation() ? 'numeric' : 'numeric[]',
                        $rel->relationModel()->getAttribute($reversedRelation->keyName())?->nullable ?? false
                    );
                    TsFileUtils::addNewInputToReactTSForm($input, $interfaceProperty, $relatedUpdatePagePath, [
                        'key'   => $reversedRelation->keyName(),
                        'value' => $rel->variableNaming().'?.'.$reversedRelation->keyName(),
                    ]);
                    TsFileUtils::addImportStatement($input->imports, $relatedUpdatePagePath);
                }

                if ($reversedRelation instanceof HasReactTsDisplayComponentString) {
                    $component = $reversedRelation->displayComponentString($this->actor);
                    $component->translationKey = $reversedRelation->translationKey();
                    TsFileUtils::addComponentToShowPage($component, $relatedShowPagePath);
                    TsFileUtils::addImportStatement($component->imports, $relatedShowPagePath);
                }

                ClassUtils::addRelationsToControllerRelationsProperty($rel->getWebControllerPath(), [$reversedRelation->method()]);

                return true;
            });

        return $this;
    }

    public function checkForResourceRelations(): static
    {
        if (! $this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn (CubeRelation $relation) => $relation->getResourcePath()->exist() && $relation->loadable())
            ->each(function (CubeRelation $relation) {
                // Category model
                $model = $relation->relationModel();

                // products relation of the category model in this case it will be a category has many products
                $reverseRelation = $relation->reverseRelation();

                if ($reverseRelation instanceof HasResourcePropertyString) {
                    $property = $reverseRelation->resourcePropertyString();
                    $relatedPath = $model->getResourcePath();
                    ClassUtils::addToMethodReturnArray($model->getResourcePath(), 'toArray', $reverseRelation->resourcePropertyString());
                    foreach ($property->imports as $import) {
                        FileUtils::addImportStatement($import, $relatedPath);
                    }
                }
            });

        return $this;
    }

    public function checkForTsInterfaces(): static
    {
        if (! $this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn (CubeRelation $rel) => $rel->getTSModelPath()->exist())
            ->each(function (CubeRelation $relation) {
                $related = $relation->relationModel(); // product
                $relatedInterfacePath = $related->getTSModelPath(); // product.ts
                $reversedRelation = $relation->reverseRelation(); // category

                if ($reversedRelation instanceof HasInterfacePropertyString) {
                    $interfaceProperty = $reversedRelation->interfacePropertyString();
                    TsFileUtils::addPropertyToInterface(
                        $relatedInterfacePath,
                        $interfaceProperty
                    );

                    if ($interfaceProperty->import) {
                        TsFileUtils::addImportStatement($interfaceProperty->import, $relatedInterfacePath);
                    }
                }
            });

        return $this;
    }

    public function checkForWebRelations(): static
    {
        if (! $this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn (CubeRelation $rel) => $rel->loadable() && $rel->getWebControllerPath()->exist())
            ->each(function (CubeRelation $relation) {
                $controllerPath = $this->table->getWebControllerPath();
                $relatedControllerPath = $relation->getWebControllerPath();
                $relatedTable = $relation->relationModel();
                $reversedRelation = $relation->reverseRelation();

                $relatedCreateView = $relation->relationModel()->createView($this->actor)->path;
                $relatedUpdateView = $relation->relationModel()->editView($this->actor)->path;
                $relatedIndexView = $relation->relationModel()->indexView($this->actor)->path;
                $relatedShowView = $relation->relationModel()->showView($this->actor)->path;
                TranslationFileUtils::addModelTranslations($relation->relationModel());

                if ($reversedRelation instanceof HasBladeInputComponent
                    && (! $reversedRelation->isBelongsTo() || ClassUtils::isMethodDefined($controllerPath, 'allPaginatedJson'))
                ) {
                    BladeFileUtils::addToNewInputToForm($reversedRelation->bladeInputComponent('store', $this->actor)->translationKey($reversedRelation->translationKey()), $relatedCreateView);
                    BladeFileUtils::addToNewInputToForm($reversedRelation->bladeInputComponent('update', $this->actor)->translationKey($reversedRelation->translationKey()), $relatedUpdateView);
                }

                if ($reversedRelation instanceof HasDatatableColumnString
                    && $reversedRelation instanceof HasHtmlTableHeader
                    && ClassUtils::isMethodDefined($relatedControllerPath, 'data')
                ) {
                    if ($reversedRelation instanceof HasYajraDataTableRelationLinkColumnRenderer) {
                        $col = $reversedRelation->yajraDataTableAdditionalColumnRenderer($this->actor);
                        ClassUtils::addNewColumnToTheReturnedYajraColumns(
                            $col,
                            $relatedControllerPath
                        );
                        ClassUtils::addNewColumnToYajraRawColumnsInController($col->returnColName, $relatedControllerPath);
                    }
                    BladeFileUtils::addColumnToDataTable($relatedIndexView, $reversedRelation->dataTableColumnString(), $reversedRelation->htmlTableHeader()->translationKey($reversedRelation->translationKey()));
                    ClassUtils::addNewRelationsToWithMethod($relatedControllerPath, $relatedTable, [$reversedRelation->method()]);
                }

                if ($reversedRelation instanceof HasBladeDisplayComponent) {
                    BladeFileUtils::addNewDisplayComponentToShowView($reversedRelation->bladeDisplayComponent()->translationKey($reversedRelation->translationKey()), $relatedShowView);
                }

                ClassUtils::addRelationsToControllerRelationsProperty($relation->getWebControllerPath(), [$reversedRelation->method()]);

                return true;
            });

        return $this;
    }

    public function setActor(?string $actor = null): static
    {
        $this->actor = $actor;

        return $this;
    }

    public function setModel(CubeTable $table): static
    {
        $this->table = $table;

        return $this;
    }
}
