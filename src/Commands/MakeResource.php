<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

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
        $modelName = $this->modelNaming($modelName);
        $resourceName = $this->getResourceName($modelName);

        $stubProperties = [
            '{class}' => $resourceName,
            '{resource_fields}' => $this->generateCols($attributes, $relations),
        ];

        $resourcePath = $this->getResourcePath($resourceName);
        if (file_exists($resourcePath)) {
            return;
        }

        new CreateFile(
            $stubProperties,
            $resourcePath,
            __DIR__ . '/stubs/resource.stub'
        );

        $this->formatfile($resourcePath);
        $this->line("<info>Created resource:</info> $resourceName");
    }

    private function getResourceName($modelName): string
    {
        return $modelName . 'Resource';
    }

    private function generateCols(array $attributes, $relations): string
    {
        $columns = "'id' => \$this->id, \n\t\t\t";
        foreach ($attributes as $name => $value) {
            if ($value == 'file') {
                $columns .= "'$name' => \$this->get" . ucfirst(Str::camel(Str::studly($name))) . "Path(), \n\t\t\t";
                continue;
            }
            $columns .= "'$name' => \$this->$name,\n\t\t\t";
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasOne || $type == RelationsTypeEnum::BelongsTo) {
                $relation = $this->relationFunctionNaming(str_replace('_id', '', $rel));
                $relatedModelResource = $this->modelNaming($relation) . 'Resource';
                $columns .= "'$relation' =>  new $relatedModelResource(\$this->whenLoaded('$relation')) , \n\t\t\t";
            } elseif ($type == RelationsTypeEnum::ManyToMany || $type == RelationsTypeEnum::HasMany) {
                $relation = $this->relationFunctionNaming(($rel));
                $relatedModelResource = $this->modelNaming($relation) . 'Resource';
                $columns .= "'$relation' =>  $relatedModelResource::collection(\$this->whenLoaded('$relation')) , \n\t\t\t";
            }
        }

        return $columns;
    }

    /**
     * @throws BindingResolutionException
     */
    private function getResourcePath($ResourceName): string
    {
        $path = $this->appPath() . '/app/Http/Resources/';

        $this->ensureDirectoryExists($path);

        return $path . "$ResourceName" . '.php';
    }
}
