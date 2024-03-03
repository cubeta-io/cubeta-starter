<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\app\Models\CubetaRelation;
use Cubeta\CubetaStarter\app\Models\CubetaTable;
use Cubeta\CubetaStarter\app\Models\Settings;
use Cubeta\CubetaStarter\Generators\Sources\ViewsGenerator;
use Cubeta\CubetaStarter\Helpers\ClassUtils;
use Cubeta\CubetaStarter\Helpers\ContentDoesNotExist;
use Cubeta\CubetaStarter\LogsMessages\CubeLog;
use Cubeta\CubetaStarter\LogsMessages\Info\ContentAppended;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\SettingsHandler;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Illuminate\Support\Str;

class CodeSniffer
{
    use SettingsHandler;
    use StringsGenerator;
    use AssistCommand;

    private static $instance;

    private ?CubetaTable $table = null;

    private function __construct()
    {
        //
    }

    public static function destroy(): void
    {
        self::$instance = null;
    }

    public function setModel(CubetaTable $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function checkForModelsRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubetaRelation $relation) {
            $relatedPath = $relation->getModelPath();
            if ($relatedPath->exist()) {

                if ($relation->isHasMany()) {

                    ClassUtils::addMethodToClass(
                        $this->table->relationFunctionNaming(),
                        $relatedPath,
                        $this->belongsToFunction($this->table)
                    );

                }

                if ($relation->isManyToMany()) {
                    ClassUtils::addMethodToClass(
                        $this->table->relationFunctionNaming(singular: false),
                        $relatedPath,
                        $this->manyToManyFunction($this->table)
                    );
                }

                if ($relation->isBelongsTo() || $relation->isHasOne()) {
                    ClassUtils::addMethodToClass(
                        $this->table->relationFunctionNaming(singular: false),
                        $relatedPath,
                        $this->hasManyFunction($this->table)
                    );
                }
            }
        });

        return $this;
    }

    public function checkForFactoryRelations(): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubetaRelation $relation) {
            $relatedPath = $relation->getFactoryPath();

            if ($relatedPath->exist()) {
                if ($relation->isBelongsTo() || $relation->isHasOne() || $relation->isManyToMany()) {
                    $methodName = "with" . Str::plural($this->table->modelName);
                    ClassUtils::addMethodToClass(
                        $methodName,
                        $relatedPath,
                        $this->factoryRelationMethod($this->table)
                    );
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

        $this->table->relations()->each(function (CubetaRelation $relation) {
            $relatedClassName = $relation->getResourceClassString();
            $relatedResourcePath = $relation->getResourcePath();
            $currentResourceClass = $this->table->getResourceClassString();
            $relatedModelPath = $relation->getModelPath();

            if (
                $relatedResourcePath->exist()
                and
                $relatedModelPath->exist()
            ) {
                if ($relation->isHasMany()) {
                    $relationName = $this->table->relationFunctionNaming();

                    if ($relatedModelPath->exist() and ClassUtils::isMethodDefined($relatedModelPath, $relationName)) {
                        $content = "'$relationName' => new \\$currentResourceClass(\$this->whenLoaded('$relationName')) , \n";
                        ClassUtils::addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                    } else {
                        CubeLog::add(new ContentDoesNotExist(
                            "$relationName method",
                            $relatedModelPath->fullPath,
                            "Sniffing The Resource Code To Add The {$this->table->modelName} Resource To {$relation->modelName} Resource"
                        ));
                    }
                }

                if ($relation->isManyToMany()) {
                    $relationName = $this->table->relationFunctionNaming(singular: false);

                    if ($relatedModelPath->exist() and ClassUtils::isMethodDefined($relatedModelPath, $relationName)) {
                        $content = "'$relationName' => \\$currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";
                        ClassUtils::addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                    } else {
                        CubeLog::add(new ContentDoesNotExist(
                            "$relationName method",
                            $relatedModelPath->fullPath,
                            "Sniffing The Resource Code To Add The {$this->table->modelName} Resource To {$relation->modelName} Resource"
                        ));
                    }
                }

                if ($relation->isBelongsTo() || $relation->isHasOne()) {
                    $relationName = $this->table->relationFunctionNaming(singular: false);
                    $content = "'$relationName' => \\$currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";

                    if ($relatedModelPath->exist() and ClassUtils::isMethodDefined($relatedModelPath, $relationName)) {
                        ClassUtils::addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                    } else {
                        CubeLog::add(new ContentDoesNotExist(
                            "$relationName method",
                            $relatedModelPath->fullPath,
                            "Sniffing The Resource Code To Add The {$this->table->modelName} Resource To {$relation->modelName} Resource"
                        ));
                    }
                }
            }
        });

        return $this;
    }

    public function checkForWebRelations(string $select2RouteName): static
    {
        if (!$this->table) {
            return $this;
        }

        $this->table->relations()->each(function (CubetaRelation $relation) use ($select2RouteName) {
            if ($relation->isHasMany()) {

                $relatedControllerPath = $relation->getWebControllerPath();

                if ($relation->getModelPath()->exist() and $relatedControllerPath->exist()) {
                    $attribute = strtolower($this->table->modelName) . '_id';
                    $relatedTable = Settings::make()->getTable($relation->modelName);
                    $relatedCreateView = $relation->getViewPath("create");
                    $relatedUpdateView = $relation->getViewPath("update");
                    $relatedIndexView = $relation->getViewPath("index");
                    $relatedShowView = $relation->getViewPath("show");

                    if ($relatedTable->getAttribute($attribute)->nullable) {
                        $required = "required";
                    } else $required = "";

                    if ($relatedCreateView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, 'allPaginatedJson')) {
                        $inputField = "<x-select2 label=\"{$this->table->modelName}\" name=\"{$attribute}\" api=\"{{route('{$select2RouteName}')}}\" option-value=\"id\" option-inner-text=\"{$this->table->titleable()->name}\" $required/> \n";

                        $createView = $relatedCreateView->getContent();

                        $createView = str_replace("</x-form>", "\n \t $inputField\n</x-form>", $createView);

                        $relatedCreateView->putContent($createView);

                        CubeLog::add(new ContentAppended($inputField, $relatedCreateView->fullPath));
                    }

                    if ($relatedUpdateView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, 'allPaginatedJson')) {
                        $value = ":value=\"\$" . $relation->variableNaming() . "->$attribute\"";
                        $inputField = "<x-select2 label=\"{$this->table->modelName}\" name=\"{$attribute}\" api=\"{{route('{$select2RouteName}')}}\" option-value=\"id\" option-inner-text=\"{$this->table->titleable()->name}\" $value/> \n";

                        $createView = $relatedUpdateView->getContent();

                        $createView = str_replace("</x-form>", "\n \t $inputField\n</x-form>", $createView);

                        $relatedUpdateView->putContent($createView);

                        CubeLog::add(new ContentAppended($inputField, $relatedUpdateView->fullPath));
                    }

                    if ($relatedIndexView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, 'data')) {
                        $titleable = $this->table->titleable()->name;
                        $attributeName = $this->table->relationFunctionNaming() . "." . $titleable;
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
                            ViewsGenerator::addColumnToDataTable($relatedIndexView, $content, $this->table->modelName);
                        }
                    }


                    if ($relatedShowView->exist() and ClassUtils::isMethodDefined($relatedControllerPath, "show")) {
                        $relationName = $this->table->relationFunctionNaming();
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

                    ClassUtils::addNewRelationsToWithMethod($relatedTable, $relatedControllerPath, [$this->table->relationFunctionNaming()]);
                }
            }
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
}

