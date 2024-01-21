<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
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

    public static function make(): CodeSniffer
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
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
        $tables = getJsonSettings();
        $currentTable = $this->searchForTable($tables["tables"], $this->currentModel);

        if (!$currentTable) {
            return $this;
        }

        if (isset($currentTable['relations']) and isset($currentTable['relations'][RelationsTypeEnum::HasMany])) {

            foreach ($currentTable['relations'][RelationsTypeEnum::HasMany] as $relation) {
                $relatedModelName = modelNaming($relation["model_name"]);
                $relatedClassName = getModelClassName($relatedModelName);
                $relatedPath = getModelPath($relatedModelName);

                // check if the related class exists
                if (!file_exists($relatedPath) || !class_exists($relatedClassName)) {
                    continue;
                }

                addMethodToClass(
                    relationFunctionNaming($this->currentModel),
                    $relatedClassName,
                    $relatedPath,
                    $this->belongsToFunction($this->currentModel)
                );
            }
        }

        if (isset($currentTable['relations']) and isset($currentTable['relations'][RelationsTypeEnum::ManyToMany])) {
            foreach ($currentTable['relations'][RelationsTypeEnum::ManyToMany] as $relation) {
                $relatedModelName = modelNaming($relation["model_name"]);
                $relatedClassName = getModelClassName($relatedModelName);
                $relatedPath = getModelPath($relatedModelName);

                // check if the related class exists
                if (!file_exists($relatedPath) || !class_exists($relatedClassName)) {
                    continue;
                }

                addMethodToClass(
                    relationFunctionNaming($this->currentModel, false),
                    $relatedClassName,
                    $relatedPath,
                    $this->manyToManyFunction($this->currentModel)
                );
            }
        }

        if (isset($currentTable['relations']) and isset($currentTable['relations'][RelationsTypeEnum::BelongsTo])) {
            foreach ($currentTable['relations'][RelationsTypeEnum::BelongsTo] as $relation) {
                $relatedModelName = modelNaming($relation["model_name"]);
                $relatedClassName = getModelClassName($relatedModelName);
                $relatedPath = getModelPath($relatedModelName);

                // check if the related class exists
                if (!file_exists($relatedPath) || !class_exists($relatedClassName)) {
                    continue;
                }

                addMethodToClass(
                    relationFunctionNaming($this->currentModel, false),
                    $relatedClassName,
                    $relatedPath,
                    $this->hasManyFunction($this->currentModel)
                );
            }
        }
        return $this;
    }

    public function checkForFactoryRelations(): static
    {
        $tables = getJsonSettings();
        $currentTable = $this->searchForTable($tables["tables"], $this->currentModel);

        if (!$currentTable) {
            return $this;
        }

        if (isset($currentTable['relations']) and isset($currentTable['relations'][RelationsTypeEnum::BelongsTo])) {
            foreach ($currentTable['relations'][RelationsTypeEnum::BelongsTo] as $relation) {
                $relatedModelName = modelNaming($relation["model_name"]);
                $relatedClassName = getFactoryClassName($relatedModelName);
                $relatedPath = getFactoryPath($relatedModelName);

                // check if the related class exists
                if (!file_exists($relatedPath) || !class_exists($relatedClassName)) {
                    continue;
                }

                $methodName = "with" . Str::plural($this->currentModel);
                addMethodToClass(
                    $methodName,
                    $relatedClassName,
                    $relatedPath,
                    $this->factoryRelationMethod($this->currentModel)
                );
            }
        }

        if (isset($currentTable['relations']) and isset($currentTable['relations'][RelationsTypeEnum::ManyToMany])) {
            foreach ($currentTable['relations'][RelationsTypeEnum::ManyToMany] as $relation) {
                $relatedModelName = modelNaming($relation["model_name"]);
                $relatedClassName = getFactoryClassName($relatedModelName);
                $relatedPath = getFactoryPath($relatedModelName);

                // check if the related class exists
                if (!file_exists($relatedPath) || !class_exists($relatedClassName)) {
                    continue;
                }

                $methodName = "with" . Str::plural($this->currentModel);
                addMethodToClass(
                    $methodName,
                    $relatedClassName,
                    $relatedPath,
                    $this->factoryRelationMethod($this->currentModel)
                );
            }
        }

        return $this;
    }

    public function checkForResourceRelations(): static
    {
        $tables = getJsonSettings();
        $currentTable = $this->searchForTable($tables["tables"], $this->currentModel);

        if (!$currentTable) {
            return $this;
        }


        if (isset($currentTable['relations']) and isset($currentTable['relations'][RelationsTypeEnum::HasMany])) {
            foreach ($currentTable['relations'][RelationsTypeEnum::HasMany] as $relation) {
                $relatedModelName = modelNaming($relation["model_name"]);
                $relatedClassName = getResourceClassName($relatedModelName);
                $relatedResourcePath = getResourcePath($relatedModelName);
                $currentResourceClass = getResourceClassName($this->currentModel);

                if (!file_exists($relatedResourcePath) || !class_exists($relatedClassName)) {
                    continue;
                }

                if (!file_exists(getModelPath($relatedModelName)) || !class_exists(getModelClassName($relatedModelName))) {
                    continue;
                }

                $relationName = relationFunctionNaming($this->currentModel);

                if (!isMethodDefined(getModelPath($relatedModelName), $relationName)) {
                    echo "Relation : $relationName does not exist in : " . getModelClassName($relatedModelName) . " \n";
                    break;
                }

                $content = "'$relationName' => new \\$currentResourceClass(\$this->whenLoaded('$relationName')) , \n";
                addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);

            }
        }

        if (isset($currentTable['relations']) and isset($currentTable['relations'][RelationsTypeEnum::ManyToMany])) {
            foreach ($currentTable['relations'][RelationsTypeEnum::ManyToMany] as $relation) {
                $relatedModelName = modelNaming($relation["model_name"]);
                $relatedClassName = getResourceClassName($relatedModelName);
                $relatedResourcePath = getResourcePath($relatedModelName);
                $currentResourceClass = getResourceClassName($this->currentModel);

                if (!file_exists($relatedResourcePath) || !class_exists($relatedClassName)) {
                    continue;
                }

                if (!file_exists(getModelPath($relatedModelName)) || !class_exists(getModelClassName($relatedModelName))) {
                    continue;
                }
                $relationName = relationFunctionNaming($this->currentModel, false);

                if (!isMethodDefined(getModelPath($relatedModelName), $relationName)) {
                    echo "Relation : $relationName does not exist in : " . getModelClassName($relatedModelName) . " \n";
                    break;
                }

                $content = "'$relationName' => \\$currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";
                addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);

            }
        }

        if (isset($currentTable['relations']) and isset($currentTable['relations'][RelationsTypeEnum::BelongsTo])) {
            foreach ($currentTable['relations'][RelationsTypeEnum::BelongsTo] as $relation) {
                $relatedModelName = modelNaming($relation["model_name"]);
                $relatedClassName = getResourceClassName($relatedModelName);
                $relatedResourcePath = getResourcePath($relatedModelName);
                $currentResourceClass = getResourceClassName($this->currentModel);

                if (!file_exists($relatedResourcePath) || !class_exists($relatedClassName)) {
                    continue;
                }

                if (!file_exists(getModelPath($relatedModelName)) || !class_exists(getModelClassName($relatedModelName))) {
                    continue;
                }

                $relationName = relationFunctionNaming($this->currentModel, false);
                $content = "'$relationName' => \\$currentResourceClass::collection(\$this->whenLoaded('$relationName')) , \n";

                if (!isMethodDefined(getModelPath($relatedModelName), $relationName)) {
                    echo "Relation : $relationName does not exist in : " . getModelClassName($relatedModelName) . " \n";
                    break;
                }

                addToMethodReturnArray($relatedResourcePath, $relatedClassName, 'toArray', $content);
            }
        }


        return $this;
    }
}
