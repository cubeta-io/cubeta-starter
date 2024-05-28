<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\App\Models\Settings\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings\CubeTable;
use Cubeta\CubetaStarter\App\Models\Settings\Settings;
use Cubeta\CubetaStarter\Generators\Sources\ViewsGenerators\BladeViewsGenerator;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\CubePath;
use Cubeta\CubetaStarter\Helpers\FileUtils;
use Cubeta\CubetaStarter\Logs\CubeLog;
use Cubeta\CubetaStarter\Logs\Errors\FailedAppendContent;
use Cubeta\CubetaStarter\Logs\Errors\NotFound;
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
use Cubeta\CubetaStarter\Logs\Warnings\ContentAlreadyExist;
use Cubeta\CubetaStarter\Logs\Warnings\ContentNotFound;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Illuminate\Support\Str;

class CodeSniffer
{
    use StringsGenerator;

    private static $instance;

    private ?CubeTable $table = null;

    private function __construct()
    {
        //
    }

    public static function destroy(): void
    {
        self::$instance = null;
    }

    public function setModel(CubeTable $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function checkForModelsRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) {
            $relatedPath = $relation->getModelPath();

            if (!$relatedPath->exist()) {
                return true;
            }

            if ($relation->isHasMany()) {
                ClassUtils::addMethodToClass(
                    $relatedPath,
                    $this->table->relationMethodNaming(),
                    $this->belongsToFunction($this->table)
                );
            }

            if ($relation->isManyToMany()) {
                ClassUtils::addMethodToClass(
                    $relatedPath,
                    $this->table->relationMethodNaming(singular: false),
                    $this->manyToManyFunction($this->table)
                );
            }

            if ($relation->isBelongsTo() || $relation->isHasOne()) {
                ClassUtils::addMethodToClass(
                    $relatedPath,
                    $this->table->relationMethodNaming(singular: false),
                    $this->hasManyFunction($this->table)
                );
            }

            $relationSearchableArray = "'{$this->table->relationMethodNaming(singular:$relation->isHasMany())}' => [\n{$this->table->searchableColsAsString()}\n]\n,";
            ClassUtils::addToMethodReturnArray($relatedPath, $relation->getModelClassString(), 'relationsSearchableArray', $relationSearchableArray);

            return true;
        });

        return $this;
    }

    public function checkForFactoryRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) {
            $relatedPath = $relation->getFactoryPath();

            if (!$relation->loadable() or !$relatedPath->exist()) {
                return true;
            }

            if ($relation->isBelongsTo() || $relation->isHasOne() || $relation->isManyToMany()) {
                $methodName = "with" . Str::plural($this->table->modelName);
                ClassUtils::addMethodToClass(
                    $relatedPath,
                    $methodName,
                    $this->factoryRelationMethod($this->table)
                );
            }

            return true;
        });

        return $this;
    }

    public function checkForResourceRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) {
            $relatedClassName = $relation->getResourceClassString();
            $relatedResourcePath = $relation->getResourcePath();
            $currentResourceClass = $this->table->getResourceClassString();
            $relatedModelPath = $relation->getModelPath();

            if (!$relatedResourcePath->exist() or !$relation->loadable()) {
                return true;
            }

            if ($relation->isHasMany()) {
                $relationName = $this->table->relationMethodNaming();

                if ($relatedModelPath->exist() and ClassUtils::isMethodDefined($relatedModelPath, $relationName)) {
                    $content = "'$relationName' => new $currentResourceClass(\$this->whenLoaded('$relationName')) , \n";
                    ClassUtils::addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                } else {
                    CubeLog::add(new ContentNotFound(
                        "$relationName method",
                        $relatedModelPath->fullPath,
                        "Sniffing The Resource Code To Add The {$this->table->modelName} Resource To {$relation->modelName} Resource"
                    ));
                }
            }

            if ($relation->isManyToMany()) {
                $relationName = $this->table->relationMethodNaming(singular: false);

                if ($relatedModelPath->exist() and ClassUtils::isMethodDefined($relatedModelPath, $relationName)) {
                    $content = "'$relationName' => $currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";
                    ClassUtils::addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                } else {
                    CubeLog::add(new ContentNotFound(
                        "$relationName method",
                        $relatedModelPath->fullPath,
                        "Sniffing The Resource Code To Add The {$this->table->modelName} Resource To {$relation->modelName} Resource"
                    ));
                }
            }

            if ($relation->isBelongsTo() || $relation->isHasOne()) {
                $relationName = $this->table->relationMethodNaming(singular: false);
                $content = "'$relationName' => $currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";

                if ($relatedModelPath->exist() and ClassUtils::isMethodDefined($relatedModelPath, $relationName)) {
                    ClassUtils::addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                } else {
                    CubeLog::add(new ContentNotFound(
                        "$relationName method",
                        $relatedModelPath->fullPath,
                        "Sniffing The Resource Code To Add The {$this->table->modelName} Resource To {$relation->modelName} Resource"
                    ));
                }
            }

            return true;
        });

        return $this;
    }

    public function checkForWebRelations(string $select2RouteName): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) use ($select2RouteName) {
            $relatedControllerPath = $relation->getWebControllerPath();
            $relatedTable = Settings::make()->getTable($relation->modelName);

            if (!$relatedTable) {
                return true;
            }

            $relatedCreateView = $relation->getViewPath("create");
            $relatedUpdateView = $relation->getViewPath("update");
            $relatedIndexView = $relation->getViewPath("index");
            $relatedShowView = $relation->getViewPath("show");

            if ($relation->isHasMany()) {

                if (!$relation->loadable() and !$relatedControllerPath->exist()) {
                    return true;
                }

                $keyName = $this->table->keyName();
                $keyAttribute = $relatedTable->getAttribute($keyName);

                if (!$keyAttribute) {
                    return true;
                }

                if ($relatedCreateView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, 'allPaginatedJson')) {
                    if ($keyAttribute->nullable) {
                        $required = "required";
                    } else {
                        $required = "";
                    }

                    $this->addSelect2ToForm($keyName, $select2RouteName, $required, $relatedCreateView);
                }

                if ($relatedUpdateView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, 'allPaginatedJson')) {
                    $value = ":value=\"\$" . $relation->variableNaming() . "->$keyName\"";
                    $this->addSelect2ToForm($keyName, $select2RouteName, $value, $relatedUpdateView);
                }

                if ($relatedIndexView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, 'data')) {
                    $titleable = $this->table->titleable()->name;
                    $attributeName = $this->table->relationMethodNaming() . "." . $titleable;
                    $oldColName = strtolower(Str::singular($this->table->modelName)) . "_id";
                    $relatedIndexViewContent = $relatedIndexView->getContent();

                    // checking that if the user remove the key column from the view : like if he removed category_id
                    // if he didn't remove it then we can replace it with the relation column
                    if (str_contains($relatedIndexViewContent, $oldColName)) {
                        $relatedIndexViewContent = str_replace($oldColName, $attributeName, $relatedIndexViewContent);
                        $relatedIndexViewContent = preg_replace('/<th>\s*' . preg_quote($this->table->modelName, '/') . '\s*id\s*<\/th>/', "<th>{$this->table->modelName}</th>", $relatedIndexViewContent);
                        $relatedIndexView->putContent($relatedIndexViewContent);
                    } else { // or add new column to the view
                        $content = "\n\"data\":'$attributeName' , searchable:true , orderable:true";
                        BladeViewsGenerator::addColumnToDataTable($relatedIndexView, $content, $this->table->modelName);
                    }
                }

                if ($relatedShowView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, "show")) {
                    $relationName = $this->table->relationMethodNaming();
                    $showViewContent = $relatedShowView->getContent();
                    $value = "\${$relatedTable->variableNaming()}->{$relationName}->{$this->table->titleable()->name}";

                    if (str_contains($showViewContent, "\${$relatedTable->variableNaming()}->{$this->table->keyName()}")) {
                        $showViewContent = str_replace("\${$relatedTable->variableNaming()}->{$this->table->keyName()}", $value, $showViewContent);
                        $oldLabel = ucfirst(str_replace("_", ' ', $this->table->keyName()));
                        $showViewContent = str_replace($oldLabel, $this->table->modelName, $showViewContent);
                    } else {
                        $item = "\t\t<x-small-text-field :value=\"$value\" label=\"{$this->table->modelName}\" />\n\t</x-show-layout>";
                        $showViewContent = str_replace("</x-show-layout>", $item, $showViewContent);
                    }

                    $relatedShowView->putContent($showViewContent);
                }

                ClassUtils::addNewRelationsToWithMethod($relatedControllerPath, $relatedTable, [$this->table->relationMethodNaming()]);
            }

            return true;
        });

        return $this;
    }

    public static function make(): CodeSniffer
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string   $keyName
     * @param string   $select2RouteName
     * @param string   $tagAttributes
     * @param CubePath $relatedFormView
     * @return void
     */
    public function addSelect2ToForm(string $keyName, string $select2RouteName, string $tagAttributes, CubePath $relatedFormView): void
    {
        $inputField = "<x-select2 label=\"{$this->table->modelName}\" name=\"{$keyName}\" api=\"{{route('{$select2RouteName}')}}\" option-value=\"id\" option-inner-text=\"{$this->table->titleable()->name}\" $tagAttributes/> \n";

        $createView = $relatedFormView->getContent();

        $createView = str_replace("</x-form>", "\n \t $inputField\n</x-form>", $createView);

        $relatedFormView->putContent($createView);

        CubeLog::add(new ContentAppended($inputField, $relatedFormView->fullPath));
    }

    public function checkForTsInterfaces(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) {
            $relatedInterfacePath = $relation->getTSModelPath();
            if (!$relatedInterfacePath->exist()) {
                return true;
            }

            if ($relation->isBelongsTo() || $relation->isManyToMany()) {
                $propertyType = $this->table->modelNaming() . "[]";
                $propertyName = Str::plural($this->table->variableNaming());
            } else { // this means it is a has many relation
                $propertyType = $this->table->modelNaming();
                $propertyName = $this->table->variableNaming();
            }

            if ($propertyType) {
                $this->addPropertyToTsInterface(
                    $relatedInterfacePath,
                    $relation->modelNaming(),
                    $propertyName,
                    $propertyType . "[]"
                );
                FileUtils::tsAddImportStatement(
                    "import { {$this->table->modelName} } from \"./{$this->table->modelName}\";",
                    $relatedInterfacePath
                );
            }

            return true;
        });

        return $this;
    }

    private function addPropertyToTsInterface(
        CubePath $filePath,
        string   $interfaceName,
        string   $propertyName,
        string   $propertyType,
        bool     $isOptional = true
    ): void
    {
        if (!$filePath->exist()) {
            CubeLog::add(new NotFound($filePath->fullPath, "Trying to add new property [$propertyName] to [$interfaceName] TS interface"));
            return;
        }

        $fileContent = $filePath->getContent();

        // Regular expression to match the specific interface block
        $pattern = '/(export\s+interface\s+' . preg_quote($interfaceName, '/') . '\s*{)([^}]*)}/s';

        $newProperty = $propertyName . ($isOptional ? '?: ' : ': ') . $propertyType;

        if (preg_match($pattern, $fileContent, $matches)) {
            $interfaceBody = $matches[2];

            // Check if the property already exists
            if (Str::contains(FileUtils::extraTrim($interfaceBody), FileUtils::extraTrim($newProperty))) {
                CubeLog::add(new ContentAlreadyExist(
                    $newProperty,
                    $filePath->fullPath,
                    "Trying to add new property [$propertyName] to [$interfaceName] TS interface"
                ));
                return;
            }

            // Insert the new property before the closing brace
            $modifiedInterfaceBody = $interfaceBody . "\n    " . $newProperty . ";";
            $modifiedInterfaceCode = $matches[1] . $modifiedInterfaceBody . "\n}";
            $modifiedFileContent = str_replace($matches[0], $modifiedInterfaceCode, $fileContent);
            $filePath->putContent($modifiedFileContent);
            $filePath->format();
        } else {
            CubeLog::add(new FailedAppendContent($newProperty, $filePath->fullPath, "Trying to add new property [$propertyName] to [$interfaceName] TS interface"));
        }
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

    public function checkForReactTSPagesRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $rel) {
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
                $this->addColumnToReactTSDataTable($relatedIndexPagePath, "{name:\"$calledAttribute\" , sortable:true , label:\"$columnLabel\"}");
            }

            return true;
        });

        return $this;
    }
}
