<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Cubeta\CubetaStarter\Traits\AssistCommand;

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
     * @return void
     */
    public function handle()
    {
        $modelName = $this->argument("name");
        $attributes = $this->argument("attributes");

        $this->createResource($modelName,$attributes);
    }

    private function createResource($modelName , array $attributes){

        $resourceName = $this->getResourceName($modelName);

        $stubProperties = [
            "{class}" => $resourceName,
            "{resource_fields}" => $this->generateCols($attributes),
        ];

        new CreateFile(
            $stubProperties,
            $this->getResourcePath($resourceName),
            __DIR__ . "/stubs/resource.stub"
        );
        $this->line("<info>Created resource:</info> {$resourceName}");
    }

    private function getResourceName($modelName){
        return $modelName."Resource";
    }

    private function generateCols(array $attributes){
        $columns = "'id'=>\$this->id,";
        foreach ($attributes as $name => $type){
            $columns .= "'$name'=>\$this->$name,\n\t\t\t";
        }
        return $columns;
    }

    private function getResourcePath($ResourceName)
    {
        $path = $this->appPath() . "/app/Http/Resources/";

        $this->ensureDirectoryExists($path);

        return $path. "$ResourceName" . ".php";
    }
}
