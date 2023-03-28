<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Illuminate\Console\Command;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Support\Str;

class MakeRequest extends Command
{
    use AssistCommand;

    public $signature = 'create:request
        {name : The name of the model }
        {attributes : columns with data types}?';

    public $description = 'Create a new request';

    /**
     * Handle the command
     *
     * @return void
     */
    public function handle()
    {
        $modelName = $this->argument("name");
        $attributes = $this->argument("attributes");

        $this->createRequest($modelName,$attributes);
    }

    private function createRequest($modelName,$attributes){

        $requestName = $this->getRequestName($modelName);

        $stubProperties = [
            "{class}" => $modelName ,
            "{rules}" => $this->generateCols($attributes) ,
        ];

        new CreateFile(
            $stubProperties,
            $this->getRequestPath($requestName,$modelName),
            __DIR__ . "/stubs/request.stub"
        );
        $this->line("<info>Created request:</info> {$requestName}");
    }

    private function getRequestName($modelName){
        return $modelName."Request";
    }

    private function generateCols($attributes){
        $rules = "";
        foreach ($attributes as $name => $type){
            if(Str::endsWith($name,'_at')){
                $rules .= "\t\t\t'$name' => 'required|date',\n";
                continue;
            }
            if(Str::startsWith($name,'is_')){
                $rules .= "\t\t\t'$name' => 'required|boolean',\n";
                continue;
            }
            if(Str::endsWith($name,"_id")){
                $relationModel = str_replace("_id","",$name);
                $relationModelPluralName = Str::plural(strtolower($relationModel));
                $rules .= "\t\t\t'$name' => 'required|integer|exists:$relationModelPluralName,id',\n";
                continue;
            }
            if($type == "file"){
                $rules .= "\t\t\t'$name'=>'nullable|image|mimes:jpeg,png,jpg|max:2048',\n";
                continue;
            }
            $rules .= "\t\t\t'$name' => 'required|$type',\n";
        }
        return $rules;
    }

    private function getRequestPath($requestName,$modelName)
    {
        $path = $this->appPath() . "/app/Http/Requests/$modelName/";

        $this->ensureDirectoryExists($path);

        return  $path . "$requestName" . ".php";

    }
}
