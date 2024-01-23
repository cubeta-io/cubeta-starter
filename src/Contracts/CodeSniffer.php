<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\app\Models\CubetaRelation;
use Cubeta\CubetaStarter\app\Models\Settings;
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

        $currentTable->relations()->each(function (CubetaRelation $relation) {
            $relatedClassName = getModelClassName($relation->modelName);
            $relatedPath = getModelPath($relation->modelName);
            if (file_exists($relatedPath) && class_exists($relatedClassName)) {

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

        $currentTable->relations()->each(function (CubetaRelation $relation) {
            $relatedClassName = getFactoryClassName($relation->modelName);
            $relatedPath = getFactoryPath($relation->modelName);

            if (file_exists($relatedPath) && class_exists($relatedClassName)) {

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

        $currentTable->relations()->each(function (CubetaRelation $relation) {
            $relatedClassName = getResourceClassName($relation->modelName);
            $relatedResourcePath = getResourcePath($relation->modelName);
            $currentResourceClass = getResourceClassName($this->currentModel);

            if (
                file_exists($relatedResourcePath) and
                class_exists($relatedClassName) and
                file_exists(getModelPath($relation->modelName)) and
                class_exists(getModelClassName($relation->modelName))
            ) {
                if ($relation->isHasMany()) {
                    $relationName = relationFunctionNaming($this->currentModel);

                    if (isMethodDefined(getModelPath($relation->modelName), $relationName)) {
                        $content = "'$relationName' => new \\$currentResourceClass(\$this->whenLoaded('$relationName')) , \n";
                        addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                    } else {
                        echo "Relation : $relationName does not exist in : " . getModelClassName($relation->modelName) . " \n";
                    }

                }

                if ($relation->isManyToMany()) {
                    $relationName = relationFunctionNaming($this->currentModel, false);

                    if (isMethodDefined(getModelPath($relation->modelName), $relationName)) {
                        $content = "'$relationName' => \\$currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";
                        addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
                    } else {
                        echo "Relation : $relationName does not exist in : " . getModelClassName($relation->modelName) . " \n";
                    }

                }

                if ($relation->isBelongsTo() || $relation->isHasOne()) {
                    $relationName = relationFunctionNaming($this->currentModel, false);
                    $content = "'$relationName' => \\$currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";

                    if (isMethodDefined(getModelPath($relation->modelName), $relationName)) {
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

        $currentTable->relations()->each(function (CubetaRelation $relation) use ($select2RouteName) {
            if ($relation->isHasMany()) {

                if (file_exists(getModelPath($relation->modelName)) and file_exists(getWebControllerPath($relation->modelName))) {

                    $attribute = strtolower($this->currentModel) . '_id';
                    $relatedTable = Settings::make()->getTable($relation->modelName);

                    if ($relatedTable->getAttribute($attribute)->nullable) {
                        $required = "required";
                    } else $required = "";

                    if (file_exists(getViewPath($relation->modelName, "create"))) {
                        $inputField = "<x-select2 label=\"{$this->currentModel}\" name=\"{$attribute}\" api=\"{{route('{$select2RouteName}')}}\" option-value=\"id\" option-inner-text=\"id\" $required/> \n";;

                        $createView = file_get_contents(getViewPath($relation->modelName, "create"));

                        $createView = str_replace("</x-form>", "\n \t $inputField\n</x-form>", $createView);

                        file_put_contents(getViewPath($relation->modelName, "create"), $createView);

                        echo getViewPath($relation->modelName, "create") . "Has Been Edited \n";
                    }

                    if (file_exists(getViewPath($relation->modelName, "update"))) {
                        $value = ":value=\"\$" . variableNaming($relation->modelName) . "->$attribute\"";
                        $inputField = "<x-select2 label=\"{$this->currentModel}\" name=\"{$attribute}\" api=\"{{route('{$select2RouteName}')}}\" option-value=\"id\" option-inner-text=\"id\" $value/> \n";;

                        $createView = file_get_contents(getViewPath($relation->modelName, "update"));

                        $createView = str_replace("</x-form>", "\n \t $inputField\n</x-form>", $createView);

                        file_put_contents(getViewPath($relation->modelName, "update"), $createView);

                        echo getViewPath($relation->modelName, "update") . "Has Been Edited \n";
                    }

                }
            }
        });

        return $this;
    }
}

