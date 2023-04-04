<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;

class MakeResource extends Command
{
    use AssistCommand;

    public $signature = 'create:resource
        {name : The name of the model }
        {attributes : columns with data types}?';

    public $description = 'Create a new resource';

    /**
     * Handle the command
     *
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');

        $this->createResource($modelName, $attributes);
    }

    /**
     * @throws BindingResolutionException
     */
    private function createResource($modelName, array $attributes)
    {
        $resourceName = $this->getResourceName($modelName);

        $stubProperties = [
            '{class}' => $resourceName,
            '{resource_fields}' => $this->generateCols($attributes),
        ];

        new CreateFile(
            $stubProperties,
            $this->getResourcePath($resourceName),
            __DIR__.'/stubs/resource.stub'
        );
        $this->line("<info>Created resource:</info> {$resourceName}");
    }

    private function getResourceName($modelName): string
    {
        return $modelName.'Resource';
    }

    private function generateCols(array $attributes): string
    {
        $columns = "'id'                     =>  \$this->id, \n\t\t\t";
        foreach ($attributes as $name => $value) {
            if ($value == RelationsTypeEnum::HasOne || $value == RelationsTypeEnum::BelongsTo) {
                $columns .= "'$name'         =>  \$this->$name,\n\t\t\t";
                $relation = lcfirst(Str::singular(str_replace('_id', '', $name)));
                $columns .= "'$relation'     =>  \$this->whenLoaded('$relation') , \n\t\t\t";
            } elseif ($value == RelationsTypeEnum::ManyToMany || $value == RelationsTypeEnum::HasMany) {
                $relation = lcfirst(Str::plural($name));
                $columns .= "'$relation'     =>  \$this->whenLoaded('$relation') , \n\t\t\t";
            } else {
                $columns .= "'$name'         =>  \$this->$name,\n\t\t\t";
            }
        }

        return $columns;
    }

    /**
     * @throws BindingResolutionException
     */
    private function getResourcePath($ResourceName): string
    {
        $path = $this->appPath().'/app/Http/Resources/';

        $this->ensureDirectoryExists($path);

        return $path."$ResourceName".'.php';
    }
}
