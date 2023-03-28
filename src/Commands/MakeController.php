<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Cubeta\CubetaStarter\Traits\AssistCommand;

class MakeController extends Command
{
    use AssistCommand;

    public $signature = 'create:controller
        {name : The name of the model }
        {attributes : columns with data types}?';

    public $description = 'Create a new controller';

    /**
     * Handle the command
     *
     * @return void
     */
    public function handle()
    {
        $modelName = $this->argument("name");
        $attributes = $this->argument("attributes");

        $this->createController($modelName,$attributes);
    }

    private function createController($modelName , array $attributes){

        $controllerName = $this->getControllerName($modelName);

        $stubProperties = [
            "{class}" => $controllerName,
            "{resource_fields}" => $this->generateCols($attributes),
        ];

        //{class} model name , {namespace} , {traits}
        new CreateFile(
            $stubProperties,
            $this->getControllerPath($controllerName),
            __DIR__ . "/stubs/resource.stub"
        );
        $this->line("<info>Created controller:</info> {$controllerName}");
    }

    private function getControllerName($modelName){
        return $modelName."Controller";
    }

    private function generateCols(array $attributes){
        $columns = "'id'=>\$this->id,";
        foreach ($attributes as $name => $type){
            $columns .= "'$name'=>\$this->$name,\n\t\t\t";
        }
        return $columns;
    }

    private function getControllerPath($controllerName)
    {
        $path = $this->appPath() . "/app/Http/Controllers/API/v1";

        $this->ensureDirectoryExists($path);

        return $path. "$controllerName" . ".php";
    }
}
