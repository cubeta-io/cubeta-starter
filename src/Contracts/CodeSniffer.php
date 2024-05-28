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
use Cubeta\CubetaStarter\Logs\Info\ContentAppended;
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
        dump("Inside the Sniffer");
        dump($this->table->relations(), $this->table->relations);
        if (!$this->table) {
            dump("Table Doesn't exists");
            return $this;
        }

        $this->table->relations()->each(function (CubeRelation $relation) {
            $relatedInterfacePath = $relation->getTSModelPath();
            dump("$relation->modelName model Path : {$relatedInterfacePath->fullPath}");
            dump("Does Exists : " . ($relatedInterfacePath->exist() ? "yes" : "no"));

            if (!$relatedInterfacePath->exist()) {
                dump("Relation Does not Exist {$relation->modelNaming()}");
                return true;
            }

            $propertyType = null;
            $propertyName = null;
            if ($relation->isBelongsTo() || $relation->isManyToMany()) {
                $propertyType = $this->table->modelNaming() . "[]";
                $propertyName = Str::plural($this->table->variableNaming());
            } else { // this means it is a has many relation
                $propertyType = $this->table->modelNaming();
                $propertyName = $this->table->variableNaming();
            }

            dump("property name : " . $propertyName);
            dump("property type : " . $propertyType);

            if ($propertyType) {
                FileUtils::tsAddPropertyToInterface(
                    $relatedInterfacePath,
                    $relation->modelNaming(),
                    $propertyName,
                    $propertyType . "[]"
                );
                FileUtils::tsAddImportStatement(
                    "import { Category } from \"./Category\";",
                    $relatedInterfacePath
                );
            }

            return true;
        });

        return $this;
    }
}
