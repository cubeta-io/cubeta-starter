<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeResource extends Command
{
    use AssistCommand;

    public $description = 'Create a new resource';

    public $signature = 'create:resource
        {name : The name of the model }
        {attributes? : columns with data types}
        {relations? : the model relations}';

    /**
     * @throws BindingResolutionException|FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes') ?? [];
        $relations = $this->argument('relations') ?? [];

        if (!$modelName || empty(trim($modelName))) {
            $this->error('Invalid input');
            return;
        }

        $this->createResource($modelName, $attributes, $relations);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createResource($modelName, array $attributes = [], array $relations = []): void
    {
        $modelName = modelNaming($modelName);
        $resourceName = resourceNaming($modelName);

        $stubProperties = [
            '{model}' => getModelClassName($modelName),
            '{namespace}' => config('cubeta-starter.resource_namespace'),
            '{class}' => $resourceName,
            '{resource_fields}' => $this->generateCols($modelName, $attributes, $relations),
        ];

        $resourcePath = $this->getResourcePath($resourceName);

        if (file_exists($resourcePath)) {
            $this->error("{$resourceName} Already Exists");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $resourcePath,
            __DIR__ . '/stubs/resource.stub'
        );

        $this->formatFile($resourcePath);
        $this->info("Created resource: {$resourceName}");
    }

    private function generateCols(string $modelName, array $attributes = [], array $relations = []): string
    {
        $columns = "'id' => \$this->id, \n\t\t\t";
        foreach ($attributes as $attribute => $type) {

            $attribute = columnNaming($attribute);

            if ($type == 'file') {
                $columns .= "'{$attribute}' => \$this->get" . modelNaming($attribute) . "Path(), \n\t\t\t";

                continue;
            }
            $columns .= "'{$attribute}' => \$this->{$attribute},\n\t\t\t";
        }

        foreach ($relations as $rel => $type) {
            $relatedModelName = modelNaming(str_replace('_id', '', $rel));
            if (!class_exists(getModelClassName($relatedModelName)) or !class_exists(getResourceClassName($relatedModelName))) {
                continue;
            }

            if (!file_exists(getModelPath($relatedModelName)) or !file_exists(getResourcePath($relatedModelName))) {
                continue;
            }

            if ($type == RelationsTypeEnum::HasOne || $type == RelationsTypeEnum::BelongsTo) {

                $relation = relationFunctionNaming(str_replace('_id', '', $rel));
                $relatedModelResource = modelNaming($relation) . 'Resource';

                // check that the resource model has the relation method
                if (!method_exists(getModelClassName($modelName), $relation)) {
                    continue;
                }

                $columns .= "'{$relation}' =>  new {$relatedModelResource}(\$this->whenLoaded('{$relation}')) , \n\t\t\t";
            } elseif ($type == RelationsTypeEnum::ManyToMany || $type == RelationsTypeEnum::HasMany) {
                $relation = relationFunctionNaming($rel, false);
                $relatedModelResource = modelNaming($relation) . 'Resource';

                // check that the resource model has the relation method
                if (!method_exists(getModelClassName($modelName), $relation)) {
                    continue;
                }

                $columns .= "'{$relation}' =>  {$relatedModelResource}::collection(\$this->whenLoaded('{$relation}')) , \n\t\t\t";
            }
        }

        return $columns;
    }

    private function getResourcePath($ResourceName): string
    {
        $directory = base_path(config('cubeta-starter.resource_path'));

        ensureDirectoryExists($directory);

        return $directory . "/{$ResourceName}" . '.php';
    }
}
