<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\Enums\ContainerType;
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
use Cubeta\CubetaStarter\StringValues\Contracts\Web\InertiaReact\Typescript\HasInterfacePropertyString;
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
                        TsFileUtils::addImportStatement([$interfaceProperty->import], $relatedInterfacePath);
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

        $this->table->relations()->each(function (CubeRelation $rel) use ($actor) {
            if (!$rel->getTSModelPath()->exist()) {
                return true;
            }

            $relatedModelPath = $rel->getModelPath();
            $relatedViewNaming = $rel->viewNaming();
            $relatedIndexPagePath = CubePath::make("resources/js/Pages/dashboard/$relatedViewNaming/Index.tsx");

            if (!$relatedIndexPagePath->exist()) {
                return true;
            }

            if ($rel->isHasMany()) {
                if (!ClassUtils::isMethodDefined($relatedModelPath, $this->table->relationMethodNaming())) {
                    return true;
                }
                $calledAttribute = $this->table->variableNaming() . "." . $this->table->titleable()->name;
                $columnLabel = $this->table->modelName . " " . $this->table->titleable()->titleNaming();
                $currentModelShowRoute = $this->getRouteNames($this->table, ContainerType::WEB, $actor)["show"];
                $translatable = $this->table->titleable()->isTranslatable() ? "translatable:true," : "";
                $newColumn = "{
                                 name:\"$calledAttribute\" ,
                                 sortable:true ,
                                 label:\"$columnLabel\" ,
                                 {$translatable}
                                 render:({$this->table->titleable()->name} , {$this->table->variableNaming()}) => (
                                                <Link
                                                    className=\"hover:text-primary underline\"
                                                    href={route(\"$currentModelShowRoute\" , {$this->table->variableNaming()}.id)}>
                                                    {{$this->table->titleable()->name}}
                                                </Link>)}";
                $this->addColumnToReactTSDataTable($relatedIndexPagePath, $newColumn);

                FileUtils::tsAddImportStatement('import { Link } from "@inertiajs/react";', $rel->getReactTSPagesPaths('index'));

                $this->addRelationsToReactTSController($rel->getWebControllerPath(), [$this->table->relationMethodNaming()]);

                $relatedKeyAttribute = $rel->relationModel()->getAttribute($this->table->keyName());
                if ($relatedKeyAttribute) {
                    $apiSelectImport = "import ApiSelect from \"@/Components/form/fields/Select/ApiSelect\";";
                    FileUtils::tsAddImportStatement($apiSelectImport, $rel->getReactTSPagesPaths('create'));
                    FileUtils::tsAddImportStatement("import { translate } from \"@/Models/Translatable\";", $rel->getReactTSPagesPaths('create'));

                    $this->addNewInputToReactTSForm(
                        $this->inertiaApiSelectComponent(
                            $this->table,
                            $this->getRouteNames($this->table, ContainerType::WEB, $actor)["data"],
                            $relatedKeyAttribute
                        ),
                        $this->table->keyName() . ":" . "number;",
                        $rel->getReactTSPagesPaths('create')
                    );

                    FileUtils::tsAddImportStatement($apiSelectImport, $rel->getReactTSPagesPaths('update'));
                    FileUtils::tsAddImportStatement("import { translate } from \"@/Models/Translatable\";", $rel->getReactTSPagesPaths('create'));

                    $this->addNewInputToReactTSForm(
                        $this->inertiaApiSelectComponent(
                            $this->table,
                            $this->getRouteNames($this->table, ContainerType::WEB, $actor)["data"],
                            $relatedKeyAttribute,
                            true
                        ),
                        $this->table->keyName() . "?:" . "number;",
                        $rel->getReactTSPagesPaths('update')
                    );
                }
            } elseif ($rel->isManyToMany() || $rel->isBelongsTo()) {
                $this->addRelationsToReactTSController($rel->getWebControllerPath(), [$this->table->relationMethodNaming(singular: false)]);
            }

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

    public function addNewInputToReactTSForm(string $inputElement, string $formInterfaceProperty, CubePath $filePath): void
    {
        $operationContext = "Trying To Add New ApiSelect Component To The Form";
        if (!$filePath->exist()) {
            CubeLog::add(new NotFound(
                $filePath->fullPath,
                $operationContext
            ));
            return;
        }

        $fileContent = $filePath->getContent();

        if (FileUtils::contentExistInFile($filePath, $inputElement)) {
            CubeLog::add(new ContentAlreadyExist($inputElement, $filePath->fullPath, $operationContext));
            return;
        }

        $firstPattern = '#<Form\s*(.*?)\s*>\s*<div\s*(.*?)\s*>\s*(.*?)\s*</div>#s';
        $secondPattern = '#<Form\s*(.*?)\s*>\s*(.*?)\s*</Form>#s';

        if (preg_match($firstPattern, $fileContent, $matches)) {
            $formContent = $matches[3];
            $substitute = $matches[3];
        } elseif (preg_match($secondPattern, $fileContent, $matches)) {
            $formContent = $matches[2];
            $substitute = $matches[2];
        } else {
            CubeLog::add(new FailedAppendContent(
                $inputElement,
                $filePath->fullPath,
                $operationContext
            ));
            return;
        }

        $formContent .= "\n$inputElement\n";
        $fileContent = str_replace($substitute, $formContent, $fileContent);

        // adding new interface property
        $formInterfacePattern = '#useForm\s*<\s*\{\s*(.*?)\s*}\s*>#s';
        if (preg_match($formInterfacePattern, $fileContent, $matches)
            && !FileUtils::contentExistInFile($filePath, $formInterfaceProperty)
        ) {
            $interfaceProperties = $matches[1];
            $interfaceProperties .= "\n$formInterfaceProperty\n";
            $fileContent = str_replace($matches[1], $interfaceProperties, $fileContent);
        } else {
            CubeLog::add(new FailedAppendContent(
                $formInterfaceProperty,
                $filePath->fullPath,
                $operationContext
            ));
        }


        $filePath->putContent($fileContent);
        CubeLog::add(new ContentAppended($inputElement, $filePath->fullPath));
        $filePath->format();
    }
}
