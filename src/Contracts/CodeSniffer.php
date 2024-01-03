<?php

namespace Cubeta\CubetaStarter\Contracts;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\SettingsHandler;
use Cubeta\CubetaStarter\Traits\StringsGenerator;

class CodeSniffer
{
    use SettingsHandler;
    use StringsGenerator;

    private static $instance;

    private string $currentModel;

    private string $currentModelClass;

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

    public function setModel(string $modelName): static
    {
        $this->currentModel = modelNaming($modelName);
        $this->currentModelClass = config("cubeta-starter.model_namespace", "App\Models") . "\\{$this->currentModel}";
        $this->currentModelPath = config("cubeta-starter.model_path", "app/Models") . "/{$this->currentModel}.php";
        return $this;
    }

    public function checkForRelations(): static
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
}
