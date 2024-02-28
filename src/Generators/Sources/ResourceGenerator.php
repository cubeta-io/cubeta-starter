<?php

namespace Cubeta\CubetaStarter\Generators\Sources;

use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ColumnTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Error;
use Throwable;

class ResourceGenerator extends AbstractGenerator
{
    public static string $key = 'resource';
    public static string $configPath = 'cubeta-starter.resource_path';

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $modelName = $this->modelName($this->fileName);
        $resourceName = $this->generatedFileName();
        $this->addToJsonFile();

        $stubProperties = [
            '{model}' => "\\" . getModelClassName($modelName),
            '{namespace}' => config('cubeta-starter.resource_namespace'),
            '{class}' => $resourceName,
            '{resource_fields}' => $this->generateFields(),
        ];

        $resourcePath = $this->getGeneratingPath($resourceName);

        throw_if(file_exists($resourcePath), new Error("{$resourceName} Already Exists"));

        $this->generateFileFromStub($stubProperties, $resourcePath);

        CodeSniffer::make()->setModel($modelName)->checkForResourceRelations();

        $this->formatFile($resourcePath);
    }

    public function generatedFileName(): string
    {
        return $this->modelName($this->fileName) . 'Resource';
    }

    protected function stubsPath(): string
    {
        return __DIR__ . '/stubs/resource.stub';
    }

    private function generateFields(): string
    {
        $modelName = $this->modelName($this->fileName);
        $fields = "'id' => \$this->id, \n\t\t\t";
        foreach ($this->attributes as $attribute => $type) {

            $attribute = $this->columnName($attribute);

            if ($type == ColumnTypeEnum::FILE->value) {
                $fields .= "'{$attribute}' => \$this->get" . $this->modelName($attribute) . "Path(), \n\t\t\t";
                continue;
            }
            $fields .= "'{$attribute}' => \$this->{$attribute},\n\t\t\t";
        }

        foreach ($this->relations as $rel => $type) {
            $relatedModelName = $this->modelName(str_replace('_id', '', $rel));

            if (!file_exists(getModelPath($relatedModelName)) or !file_exists(getResourcePath($relatedModelName))) {
                continue;
            }

            if ($type == RelationsTypeEnum::HasOne->value || $type == RelationsTypeEnum::BelongsTo->value) {

                $relation = relationFunctionNaming(str_replace('_id', '', $rel));
                $relatedModelResource = modelNaming($relation) . 'Resource';

                // check that the resource model has the relation method
                if (!method_exists(getModelClassName($modelName), $relation)) {
                    continue;
                }

                $fields .= "'{$relation}' =>  new {$relatedModelResource}(\$this->whenLoaded('{$relation}')) , \n\t\t\t";
            } elseif ($type == RelationsTypeEnum::ManyToMany->value || $type == RelationsTypeEnum::HasMany->value) {
                $relation = relationFunctionNaming($rel, false);
                $relatedModelResource = modelNaming($relation) . 'Resource';

                // check that the resource model has the relation method
                if (!method_exists(getModelClassName($modelName), $relation)) {
                    continue;
                }

                $fields .= "'{$relation}' =>  {$relatedModelResource}::collection(\$this->whenLoaded('{$relation}')) , \n\t\t\t";
            }
        }

        return $fields;
    }
}
