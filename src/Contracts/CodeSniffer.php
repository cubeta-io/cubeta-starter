<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\Helpers\BladeFileUtils;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Helpers\TsFileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Settings\CubeRelation;
use Cubeta\CubetaStarter\Settings\CubeTable;
use Cubeta\CubetaStarter\StringValues\Contracts\Factories\HasFactoryRelationMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\HasDocBlockProperty;
use Cubeta\CubetaStarter\StringValues\Contracts\Models\HasModelRelationMethod;
use Cubeta\CubetaStarter\StringValues\Contracts\Resources\HasResourcePropertyString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeDisplayComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasBladeInputComponent;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Components\HasHtmlTableHeader;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Controllers\HasYajraDataTableRelationLinkColumnRenderer;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\Blade\Javascript\HasDatatableColumnString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsDisplayComponentString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Components\HasReactTsInputString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasDataTableColumnObjectString;
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
use Cubeta\CubetaStarter\StringValues\Strings\Web\InertiaReact\Typescript\InterfacePropertyString;
use Cubeta\CubetaStarter\Traits\Makable;
use Cubeta\CubetaStarter\Traits\RouteBinding;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Illuminate\Support\Str;

class CodeSniffer
{
    use StringsGenerator;
    use RouteBinding;
    use Makable;

    private ?CubeTable $table = null;

    private ?string $actor = null;

    private function __construct()
    {
        //
    }

    public function setModel(CubeTable $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function setActor(?string $actor = null): static
    {
        $this->actor = $actor;
        return $this;
    }

    public function checkForModelsRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn(CubeRelation $relation) => $relation->getModelPath()->exist())
            ->each(function (CubeRelation|HasModelRelationMethod $relation) {
                // Product model
                $model = $relation->relationModel();

                // category relation of the product model in this case it will be product belongs to a category
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

    public function checkForFactoryRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn(CubeRelation $relation) => $relation->getModelPath()->exist() && $relation->loadable())
            ->each(function (CubeRelation|HasFactoryRelationMethod $relation) {
                // Category model
                $model = $relation->relationModel();

                // products relation of the category model in this case it will be category has many products
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

    public function checkForResourceRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn(CubeRelation $relation) => $relation->getResourcePath()->exist() && $relation->loadable())
            ->each(function (CubeRelation $relation) {
                // Category model
                $model = $relation->relationModel();

                // products relation of the category model in this case it will be category has many products
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

    public function checkForWebRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn(CubeRelation $rel) => $rel->loadable() && $rel->getWebControllerPath()->exist())
            ->each(function (CubeRelation $relation) {
                $controllerPath = $this->table->getWebControllerPath();
                $relatedControllerPath = $relation->getWebControllerPath();
                $relatedTable = $relation->relationModel();
                $reversedRelation = $relation->reverseRelation();

                $relatedCreateView = $relation->getViewPath("create");
                $relatedUpdateView = $relation->getViewPath("update");
                $relatedIndexView = $relation->getViewPath("index");
                $relatedShowView = $relation->getViewPath("show");

                if ($reversedRelation instanceof HasBladeInputComponent
                    && (!$reversedRelation->isBelongsTo() || ClassUtils::isMethodDefined($controllerPath, 'allPaginatedJson'))
                ) {
                    BladeFileUtils::addToNewInputToForm($reversedRelation->bladeInputComponent("store", $this->actor), $relatedCreateView);
                    BladeFileUtils::addToNewInputToForm($reversedRelation->bladeInputComponent("update", $this->actor), $relatedUpdateView);
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
                    BladeFileUtils::addColumnToDataTable($relatedIndexView, $reversedRelation->dataTableColumnString(), $reversedRelation->htmlTableHeader());
                    ClassUtils::addNewRelationsToWithMethod($relatedControllerPath, $relatedTable, [$reversedRelation->method()]);
                }

                if ($reversedRelation instanceof HasBladeDisplayComponent) {
                    BladeFileUtils::addNewDisplayComponentToShowView($reversedRelation->bladeDisplayComponent(), $relatedShowView);
                }

                return true;
            });

        return $this;
    }

    public function checkForTsInterfaces(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn(CubeRelation $rel) => $rel->getTSModelPath()->exist())
            ->each(function (CubeRelation $relation) {
                $related = $relation->relationModel(); // product
                $relatedInterfacePath = $related->getTSModelPath(); //product.ts
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

    public function addColumnToReactTSDataTable(CubePath $filePath, string $newColumnString): void
    {
        if (!$filePath->exist()) {
            CubeLog::add(new NotFound($filePath->fullPath, "Adding new column to the data table inside the file"));
        }

        $fileContent = $filePath->getContent();

        $pattern = '/schema\s*=\s*\{\s*\[(.*?)\s*]\s*}/s';

        if (preg_match($pattern, $fileContent, $matches)) {
            $schemaContent = $matches[1];

            if (FileUtils::contentExistInFile($filePath, $newColumnString)) {
                CubeLog::add(new ContentAlreadyExist(
                    $newColumnString,
                    $filePath->fullPath,
                    "Adding new column to the data table inside the file"
                ));
            }

            $pattern = '/\s*}\s*,\s*\{\s*/';
            $modifiedSchemaContent = preg_replace($pattern, "},$newColumnString,{", $schemaContent, 1);
            $modifiedSchemaArray = "schema = {[" . $modifiedSchemaContent . "]}";
            $modifiedFileContent = str_replace($matches[0], $modifiedSchemaArray, $fileContent);

            $filePath->putContent($modifiedFileContent);
            CubeLog::add(new ContentAppended($newColumnString, $filePath->fullPath));
            $filePath->format();
        } else {
            $pattern = '/schema\s*=\s*\{\s*\[\s*/';
            if (preg_match($pattern, $fileContent)) {
                $fileContent = preg_replace($pattern, "schema={[$newColumnString,", $fileContent, 1);
                $filePath->putContent($fileContent);
                $filePath->format();
                CubeLog::add(new ContentAppended($newColumnString, $filePath->fullPath));
            } else {
                CubeLog::add(new FailedAppendContent($newColumnString,
                    $filePath->fullPath,
                    "Adding new column to the data table inside the file"));
            }
        }
    }

    public function checkForReactTSPagesAndControllerRelations(?string $actor = null): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()
            ->filter(fn(CubeRelation $rel) => $rel->getTSModelPath()->exist() && $rel->loadable())
            ->each(function (CubeRelation $rel) use ($actor) {
                $relatedIndexPagePath = $rel->getReactTSPagesPaths("index");
                $relatedCreatePagePath = $rel->getReactTSPagesPaths('create');
                $relatedUpdatePagePath = $rel->getReactTSPagesPaths('update');
                $relatedShowPagePath = $rel->getReactTSPagesPaths('show');

                $reversedRelation = $rel->reverseRelation();
                if ($reversedRelation instanceof HasDataTableColumnObjectString && $relatedIndexPagePath->exist()) {
                    $object = $reversedRelation->datatableColumnObject($this->actor);
                    TsFileUtils::addColumnToDataTable($relatedIndexPagePath, $object);
                    TsFileUtils::addImportStatement($object->imports, $relatedIndexPagePath);
                }

                if ($rel->isBelongsTo()
                    && !ClassUtils::isMethodDefined($reversedRelation->getWebControllerPath(), "data")
                ) {
                    return true;
                }

                if ($reversedRelation instanceof HasReactTsInputString
                    && $reversedRelation instanceof HasInterfacePropertyString
                    && $relatedCreatePagePath->exist()
                ) {
                    $input = $reversedRelation->inputComponent("store", $this->actor);
                    $interfaceProperty = new InterfacePropertyString(
                        $reversedRelation->keyName(),
                        $reversedRelation->singularRelation() ? "numeric" : "numeric[]",
                        $rel->relationModel()->getAttribute($reversedRelation->keyName())?->nullable ?? false
                    );
                    TsFileUtils::addNewInputToReactTSForm($input, $interfaceProperty, $relatedCreatePagePath);
                    TsFileUtils::addImportStatement($input->imports, $relatedCreatePagePath);
                }

                if ($reversedRelation instanceof HasReactTsInputString
                    && $reversedRelation instanceof HasInterfacePropertyString
                    && $relatedUpdatePagePath->exist()
                ) {
                    $input = $reversedRelation->inputComponent("update", $this->actor);
                    $interfaceProperty = new InterfacePropertyString(
                        $reversedRelation->keyName(),
                        $reversedRelation->singularRelation() ? "numeric" : "numeric[]",
                        $rel->relationModel()->getAttribute($reversedRelation->keyName())?->nullable ?? false
                    );
                    TsFileUtils::addNewInputToReactTSForm($input, $interfaceProperty, $relatedUpdatePagePath, [
                        'key' => $reversedRelation->keyName(),
                        'value' => $rel->variableNaming() . "?." . $reversedRelation->keyName()
                    ]);
                    TsFileUtils::addImportStatement($input->imports, $relatedUpdatePagePath);
                }

                if ($reversedRelation instanceof HasReactTsDisplayComponentString){
                    $component = $reversedRelation->displayComponentString();
                    TsFileUtils::addComponentToShowPage($component , $relatedShowPagePath);
                    TsFileUtils::addImportStatement($component->imports , $relatedShowPagePath);
                }

                $this->addRelationsToReactTSController($rel->getWebControllerPath(), [$reversedRelation->method()]);
                return true;
            });

        return $this;
    }

    private function addRelationsToReactTSController(CubePath $controllerPath, array $relations = []): void
    {
        if (!$controllerPath->exist()) {
            CubeLog::add(new NotFound($controllerPath->fullPath, "Adding new relations to the loaded relation in the controller"));
            return;
        }

        $fileContent = $controllerPath->getContent();

        $pattern = '/relations\s*=\s*\[(.*?)]/s';
        if (preg_match($pattern, $fileContent, $matches)) {
            $loadedRelations = $matches[1];
            foreach ($relations as $relation) {
                if (!FileUtils::isInPhpArrayString($loadedRelations, $relation)) {
                    $loadedRelations .= ",\"$relation\",";
                }
            }
            $loadedRelations = preg_replace('/\s*,\s*,\s*/', ',', $loadedRelations);
            if (Str::startsWith($loadedRelations, ',')) {
                $loadedRelations = FileUtils::replaceFirstMatch($loadedRelations, ',', '');
            }
            $newContent = preg_replace($pattern, 'relations = [' . $loadedRelations . ']', $fileContent);
            $controllerPath->putContent($newContent);
            CubeLog::add(new ContentAppended(implode(",", $relations), $controllerPath->fullPath));
            $controllerPath->format();
        } else {
            CubeLog::add(new FailedAppendContent(
                "[]",
                $controllerPath->fullPath,
                "Adding new relations to the loaded relation in the controller"
            ));
        }
    }
}
