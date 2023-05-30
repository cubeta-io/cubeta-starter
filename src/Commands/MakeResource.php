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

    public $signature = 'create:resource
        {name : The name of the model }
        {attributes : columns with data types}?
        {relations? : the model relations}?';

    public $description = 'Create a new resource';

    /**
     * @throws BindingResolutionException|FileNotFoundException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');
        $relations = $this->argument('relations');

        $this->createResource($modelName, $attributes, $relations);
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createResource($modelName, array $attributes, $relations): void
    {
        $modelName = modelNaming($modelName);
        $resourceName = $this->getResourceName($modelName);

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.resource_namespace'),
            '{class}' => $resourceName,
            '{resource_fields}' => $this->generateCols($attributes, $relations),
        ];

        $resourcePath = $this->getResourcePath($resourceName);

        if (file_exists($resourcePath)) {
            $this->error("$resourceName Already Exist");

            return;
        }

        generateFileFromStub(
            $stubProperties,
            $resourcePath,
            __DIR__ . '/stubs/resource.stub'
        );

        $this->formatFile($resourcePath);
        $this->info("Created resource: $resourceName");
    }

    private function getResourceName($modelName): string
    {
        return $modelName . 'Resource';
    }

    private function generateCols(array $attributes, $relations): string
    {
        $columns = "'id' => \$this->id, \n\t\t\t";
        foreach ($attributes as $attribute => $type) {
            if ($type == 'file') {
                $columns .= "'$attribute' => \$this->get" . variableNaming($attribute) . "Path(), \n\t\t\t";

                continue;
            }

            if ($type == 'translatable') {
                $columns .= "'$attribute:" . app()->getLocale() . "' => getTranslation(\$this->$attribute), \n";
                $columns .= "'$attribute' => \$this->$attribute , \n";
            }
            $columns .= "'$attribute' => \$this->$attribute,\n\t\t\t";
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasOne || $type == RelationsTypeEnum::BelongsTo) {
                $relation = relationFunctionNaming(str_replace('_id', '', $rel));
                $relatedModelResource = modelNaming($relation) . 'Resource';
                $columns .= "'$relation' =>  new $relatedModelResource(\$this->whenLoaded('$relation')) , \n\t\t\t";
            } elseif ($type == RelationsTypeEnum::ManyToMany || $type == RelationsTypeEnum::HasMany) {
                $relation = relationFunctionNaming(($rel));
                $relatedModelResource = modelNaming($relation) . 'Resource';
                $columns .= "'$relation' =>  $relatedModelResource::collection(\$this->whenLoaded('$relation')) , \n\t\t\t";
            }
        }

        return $columns;
    }

    private function getResourcePath($ResourceName): string
    {
        $directory = base_path(config('cubeta-starter.resource_path'));

        ensureDirectoryExists($directory);

        return $directory . "/$ResourceName" . '.php';
    }
}
