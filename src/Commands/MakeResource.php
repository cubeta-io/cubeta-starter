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
     * @return void
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');
        $relations = $this->argument('relations');

        $this->createResource($modelName, $attributes, $relations);
    }

    /**
     * @param $modelName
     * @param array $attributes
     * @param $relations
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createResource($modelName, array $attributes, $relations): void
    {
        $modelName = ucfirst(Str::singular($modelName));
        $resourceName = $this->getResourceName($modelName);

        $stubProperties = [
            '{class}' => $resourceName,
            '{resource_fields}' => $this->generateCols($attributes, $relations),
        ];

        $resourcePath = base_path().'/app/Http/Resources/'.$resourceName.'.php';
        if (file_exists($resourcePath)) {
            return;
        }

        new CreateFile(
            $stubProperties,
            $this->getResourcePath($resourceName),
            __DIR__.'/stubs/resource.stub'
        );
        $this->line("<info>Created resource:</info> {$resourceName}");
    }

    /**
     * @param $modelName
     * @return string
     */
    private function getResourceName($modelName): string
    {
        return $modelName.'Resource';
    }

    /**
     * @param array $attributes
     * @param $relations
     * @return string
     */
    private function generateCols(array $attributes, $relations): string
    {
        $columns = "'id'                     =>  \$this->id, \n\t\t\t";
        foreach ($attributes as $name => $value) {
            $columns .= "'$name'         =>  \$this->$name,\n\t\t\t";
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasOne || $type == RelationsTypeEnum::BelongsTo) {
                $relation = lcfirst(Str::singular(str_replace('_id', '', $rel)));
                $relatedModelResource = ucfirst($relation).'Resource';
                $columns .= "'$relation'     =>  new $relatedModelResource(\$this->whenLoaded('$relation')) , \n\t\t\t";
            } elseif ($type == RelationsTypeEnum::ManyToMany || $type == RelationsTypeEnum::HasMany) {
                $relation = lcfirst(Str::plural($rel));
                $relatedModelResource = ucfirst(Str::singular($relation)).'Resource';
                $columns .= "'$relation'     =>  $relatedModelResource::collection(\$this->whenLoaded('$relation')) , \n\t\t\t";
            }
        }

        return $columns;
    }

    /**
     * @param $ResourceName
     * @return string
     * @throws BindingResolutionException
     */
    private function getResourcePath($ResourceName): string
    {
        $path = $this->appPath().'/app/Http/Resources/';

        $this->ensureDirectoryExists($path);

        return $path."$ResourceName".'.php';
    }
}
