<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\App\Models\CubeRelation;
use Cubeta\CubetaStarter\App\Models\Settings;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\SettingsHandler;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Cubeta\CubetaStarter\Traits\ViewGenerating;
use Illuminate\Support\Str;

class CodeSniffer
{
    use SettingsHandler;
    use StringsGenerator;
    use AssistCommand;
    use ViewGenerating;

    private static $instance;

    private string $currentModel;

    private string $currentModelClassName;

    private string $currentModelPath;

    private function __construct()
    {
        //
    }

    public static function destroy(): void
    {
        self::$instance = null;
    }

    public function setModel(string $modelName): static
    {
        $this->currentModel = modelNaming($modelName);
        $this->currentModelClassName = config("cubeta-starter.model_namespace", "App\Models") . "\\{$this->currentModel}";
        $this->currentModelPath = config("cubeta-starter.model_path", "app/Models") . "/{$this->currentModel}.php";
        return $this;
    }

    public function checkForModelsRelations(): static
    {
        $currentTable = Settings::make()->getTable($this->currentModel);

        if (!$currentTable) {
            return $this;
        }

        $currentTable->relations()->each(function (CubeRelation $relation) {
            $relatedClassName = getModelClassName($relation->modelName);
            $relatedPath = getModelPath($relation->modelName);
            if (file_exists($relatedPath)) {

                if ($relation->isHasMany()) {

                    addMethodToClass(
                        relationFunctionNaming($this->currentModel),
                        $relatedClassName,
                        $relatedPath,
                        $this->belongsToFunction($this->currentModel)
                    );

                }

                if ($relation->isManyToMany()) {
                    addMethodToClass(
                        relationFunctionNaming($this->currentModel, false),
                        $relatedClassName,
                        $relatedPath,
                        $this->manyToManyFunction($this->currentModel)
                    );
                }

                if ($relation->isBelongsTo() || $relation->isHasOne()) {
                    addMethodToClass(
                        relationFunctionNaming($this->currentModel, false),
                        $relatedClassName,
                        $relatedPath,
                        $this->hasManyFunction($this->currentModel)
                    );
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

    public function checkForFactoryRelations(): static
    {
        $currentTable = Settings::make()->getTable($this->currentModel);

        if (!$currentTable) {
            return $this;
        }

        $currentTable->relations()->each(function (CubeRelation $relation) {
            $relatedClassName = getFactoryClassName($relation->modelName);
            $relatedPath = getFactoryPath($relation->modelName);

            if (file_exists($relatedPath)) {
                if ($relation->isBelongsTo() || $relation->isHasOne() || $relation->isManyToMany()) {
                    $methodName = "with" . Str::plural($this->currentModel);
                    addMethodToClass(
                        $methodName,
                        $relatedClassName,
                        $relatedPath,
                        $this->factoryRelationMethod($this->currentModel)
                    );
                }
            }
        });

        return $this;
    }

    public function checkForResourceRelations(): static
    {
        $currentTable = Settings::make()->getTable($this->currentModel);

        if (!$currentTable) {
            return $this;
        }

        $currentTable->relations()->each(function (CubeRelation $relation) {
            $relatedClassName = getResourceClassName($relation->modelName);
            $relatedResourcePath = getResourcePath($relation->modelName);
            $currentResourceClass = getResourceClassName($this->currentModel);

            if (
                file_exists($relatedResourcePath)
                and
                file_exists(getModelPath($relation->modelName))
            ) {
                if ($relation->isHasMany()) {
                    $relationName = relationFunctionNaming($this->currentModel);

                    if (file_exists(getModelPath($relation->modelName)) and isMethodDefined(getModelPath($relation->modelName), $relationName)) {
                        $content = "'$relationName' => new \\$currentResourceClass(\$this->whenLoaded('$relationName')) , \n";
                        addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                    } else {
                        echo "Relation : $relationName does not exist in : " . getModelClassName($relation->modelName) . " \n";
                    }

                }

                if ($relation->isManyToMany()) {
                    $relationName = relationFunctionNaming($this->currentModel, false);

                    if (file_exists(getModelPath($relation->modelName)) and isMethodDefined(getModelPath($relation->modelName), $relationName)) {
                        $content = "'$relationName' => \\$currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";
                        addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                    } else {
                        echo "Relation : $relationName does not exist in : " . getModelClassName($relation->modelName) . " \n";
                    }

                }

                if ($relation->isBelongsTo() || $relation->isHasOne()) {
                    $relationName = relationFunctionNaming($this->currentModel, false);
                    $content = "'$relationName' => \\$currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";

                    if (file_exists(getModelPath($relation->modelName)) and isMethodDefined(getModelPath($relation->modelName), $relationName)) {
                        addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                    } else {
                        echo "Relation : $relationName does not exist in : " . getModelClassName($relation->modelName) . " \n";
                    }

                }
            }
        });

        return $this;
    }

    public function checkForWebRelations(string $select2RouteName): static
    {
        $currentTable = Settings::make()->getTable($this->currentModel);

        if (!$currentTable) {
            return $this;
        }

        $currentTable->relations()->each(function (CubeRelation $relation) use ($currentTable, $select2RouteName) {
            if ($relation->isHasMany()) {

                $relatedControllerPath = getWebControllerPath($relation->modelName);

                if (file_exists($relation->modelPath()) and file_exists($relatedControllerPath)) {
                    $attribute = strtolower($this->currentModel) . '_id';
                    $relatedTable = Settings::make()->getTable($relation->modelName);
                    $relatedModelControllerPath = getWebControllerPath($relation->modelName);
                    $relatedCreateView = getViewPath($relation->modelName, "create");
                    $relatedUpdateView = getViewPath($relation->modelName, "update");
                    $relatedIndexView = getViewPath($relation->modelName, 'index');
                    $relatedShowView = getViewPath($relation->modelName, 'show');

                    if ($relatedTable->getAttribute($attribute)->nullable) {
                        $required = "required";
                    } else $required = "";

                    if (file_exists($relatedCreateView) and isMethodDefined($relatedModelControllerPath, 'allPaginatedJson')) {
                        $inputField = "<x-select2 label=\"{$this->currentModel}\" name=\"{$attribute}\" api=\"{{route('{$select2RouteName}')}}\" option-value=\"id\" option-inner-text=\"{$currentTable->titleable()->name}\" $required/> \n";;

                        $createView = file_get_contents($relatedCreateView);

                        $createView = str_replace("</x-form>", "\n \t $inputField\n</x-form>", $createView);

                        file_put_contents($relatedCreateView, $createView);

                        echo "New Content Has Been Added To $relatedCreateView \n";
                    }

                    if (file_exists($relatedUpdateView) and isMethodDefined($relatedModelControllerPath, 'allPaginatedJson')) {
                        $value = ":value=\"\$" . variableNaming($relation->modelName) . "->$attribute\"";
                        $inputField = "<x-select2 label=\"{$this->currentModel}\" name=\"{$attribute}\" api=\"{{route('{$select2RouteName}')}}\" option-value=\"id\" option-inner-text=\"{$currentTable->titleable()->name}\" $value/> \n";;

                        $createView = file_get_contents($relatedUpdateView);

                        $createView = str_replace("</x-form>", "\n \t $inputField\n</x-form>", $createView);

                        file_put_contents($relatedUpdateView, $createView);

                        echo "New Content Has Been Added To $relatedUpdateView \n";
                    }

                    if (file_exists($relatedIndexView) and isMethodDefined($relatedModelControllerPath, 'data')) {
                        $titleable = $currentTable->titleable()->name;
                        $attributeName = relationFunctionNaming($currentTable->modelName) . "." . $titleable;
                        $oldColName = strtolower(Str::singular($currentTable->modelName)) . "_id";
                        $relatedIndexViewContent = file_get_contents($relatedIndexView);

                        // checking that if the user remove the key column from the view : like if he removed category_id
                        // if he didn't remove it then we can replace it with the relation column
                        if (str_contains($relatedIndexViewContent, $oldColName)) {
                            $relatedIndexViewContent = str_replace($oldColName, $attributeName, $relatedIndexViewContent);
                            $relatedIndexViewContent = preg_replace('/<th>\s*' . preg_quote($currentTable->modelName, '/') . '\s*id\s*<\/th>/', "<th>{$currentTable->modelName}</th>", $relatedIndexViewContent);
                            file_put_contents($relatedIndexView, $relatedIndexViewContent);
                        } else { // or add new column to the view
                            $content = "\n\"data\":'$attributeName' , searchable:true , orderable:true";
                            $this->addColumnToDataTable($relatedIndexView, $content, $currentTable->modelName);
                        }
                    }


                    if (file_exists($relatedShowView) and isMethodDefined($relatedModelControllerPath, "show")) {
                        $relationName = relationFunctionNaming($currentTable->modelName);
                        $showViewContent = file_get_contents($relatedShowView);
                        $value = "\${$relatedTable->variableName()}->{$relationName}->{$currentTable->titleable()->name}";
                        if (str_contains($showViewContent, "\${$relatedTable->variableName()}->{$currentTable->keyName()}")) {
                            $showViewContent = str_replace("\${$relatedTable->variableName()}->{$currentTable->keyName()}", $value, $showViewContent);
                            $oldLabel = ucfirst(str_replace("_", ' ', $currentTable->keyName()));
                            $showViewContent = str_replace($oldLabel, $currentTable->modelName, $showViewContent);
                        } else {
                            $item = "\t\t<x-small-text-field :value=\"$value\" label=\"{$currentTable->modelName}\" />\n\t</x-show-layout>";
                            $showViewContent = str_replace("</x-show-layout>", $item, $showViewContent);
                        }

                        file_put_contents($relatedShowView, $showViewContent);
                    }

                    addNewRelationsToWithMethod($relation->modelName, $relatedControllerPath, [relationFunctionNaming($this->currentModel)]);
                }
            }
        });

        return $this;
    }
}

