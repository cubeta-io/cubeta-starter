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

        foreach ($currentTable['relations'] as $type => $relation) {
            $relatedModelName = $relation[0]["model_name"];
            $relatedClassName = getModelClassName($relatedModelName);
            $relatedPath = getModelPath($relatedModelName);

            // check if the related class exists
            if (!file_exists($relatedPath) || !class_exists($relatedClassName)) {
                continue;
            }

            switch ($type) {
                case RelationsTypeEnum::HasMany:
                    addMethodToClass(
                        relationFunctionNaming($this->currentModel),
                        $relatedClassName,
                        $relatedPath,
                        $this->belongsToFunction($this->currentModel)
                    );
                    break;
                case RelationsTypeEnum::ManyToMany:
                    addMethodToClass(
                        relationFunctionNaming($this->currentModel, false),
                        $relatedClassName,
                        $relatedPath,
                        $this->manyToManyFunction($this->currentModel)
                    );
                    break;
                case RelationsTypeEnum::BelongsTo:
                    addMethodToClass(
                        relationFunctionNaming($this->currentModel, false),
                        $relatedClassName,
                        $relatedPath,
                        $this->hasManyFunction($this->currentModel)
                    );
                    break;
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

        foreach ($currentTable['relations'] as $type => $relation) {
            $relatedModelName = $relation[0]["model_name"];
            $relatedClassName = getFactoryClassName($relatedModelName);
            $relatedPath = getFactoryPath($relatedModelName);

            // check if the related class exists
            if (!file_exists($relatedPath) || !class_exists($relatedClassName)) {
                continue;
            }

            switch ($type) {
                case RelationsTypeEnum::BelongsTo :
                    $methodName = "with" . Str::plural($this->currentModel);
                    addMethodToClass(
                        $methodName,
                        $relatedClassName,
                        $relatedPath,
                        $this->factoryRelationMethod($this->currentModel)
                    );
                    break;

                case RelationsTypeEnum::ManyToMany :
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
}
