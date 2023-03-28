<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\CommandTypeEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\CreateFile;
use File;

class MakeModel extends Command
{
    use AssistCommand;

    public $signature = 'create:model
        {name : The name of the model }
        {option? : string to generate a single file (migration,request,resource,factory,seeder,controller-api,controller-base,repository,service)}';

    public $description = 'Create a new model class';

    private array $types;
    private string $containerType;

    public function __construct(){
        parent::__construct();

        $this->types=[
            'integer',
            'bigInteger',
            'unsignedBigInteger',
            'unsignedDouble',
            'double',
            'float',
            'string',
            'json',
            'text',
            'boolean',
            'date',
            'time',
            'dateTime',
            'timestamp',
            'file',
            'key'
        ];
    }
    public function handle()
    {
        $name = $this->argument("name");
        $option = $this->argument('option');
        if(!$name){
            $this->error('Please specify a valid model');
            return false;
        }

        $paramsString = $this->ask('Enter your params like "name,started_at,..."');

        $attributes = $this->convertToArrayOfAttributes($paramsString);

        $this->containerType = $this->choice('What Is Container Type ?',[CommandTypeEnum::API,CommandTypeEnum::WEB,CommandTypeEnum::BOTH],0);

        $foreign_keys = $this->checkForeignKeyExists($attributes);

        $className = Str::studly($name);

        $this->checkIfRequiredDirectoriesExist();

        $this->createModel($className,$attributes);

        //call to commands base on the option flag
        $result = match ($option){
            'migration'         => $this->call('create:migration', ["name" => $name,'attributes'=>$attributes]),
            'request'           => $this->call('create:request', ["name" => $name,'attributes'=>$attributes]),
            'resource'          => $this->call('create:resource', ["name" => $name,'attributes'=>$attributes]),
            'factory'           => $this->call('create:factory', ["name" => $name,'attributes'=>$attributes]),
            'seeder'            => $this->call('create:seeder', ["name" => $name]),
            'controller-api'    => $this->call('create:controller --api', ["name" => $name,'attributes'=>$attributes]),
            'controller-base'   => $this->call('create:controller --base', ["name" => $name,'attributes'=>$attributes]),
            'repository'        => $this->call('create:repository', ["name" => $name]),
            'service'           => $this->call('create:service', ["name" => $name]),
            '',null             => "all",
        };
        if($result === "all"){
            $this->call('create:migration', ["name" => $name,'attributes'=>$attributes]);
            $this->call('create:factory', ["name" => $name,'attributes'=>$attributes]);
            $this->call('create:seeder', ["name" => $name]);
            $this->call('create:request', ["name" => $name,'attributes'=>$attributes]);
            $this->call('create:resource', ["name" => $name,'attributes'=>$attributes]);
            $this->call('create:controller', ["name" => $name,'attributes'=>$attributes]);
//            $this->call('create:controller --base', ["name" => $name,'attributes'=>$attributes]);
            $this->call('create:repository', ["name" => $name]);
            $this->call('create:service', ["name" => $name]);
        }
//        $done = match ($this->containerType){
//            CommandTypeEnum::API => $this->createApiController($name,$attributes),
//            CommandTypeEnum::WEB => $this->createWebController($name,$attributes),
//            CommandTypeEnum::BOTH => $this->createBothControllers($name,$attributes),
//        };
        return Command::SUCCESS;
    }

//    private function createApiController($name,array $attributes):bool{
//        return true;
//    }
//
//    private function createWebController($name,array $attributes):bool{
//        return true;
//    }
//
//    private function createBothControllers($name,array $attributes):bool{
//        $this->createApiController($name,$attributes);
//        $this->createWebController($name,$attributes);
//        return true;
//    }

    private function convertToArrayOfAttributes($fields)
    {
        $fields = explode(',',$fields);
        $fieldsWithDataType=[];
        foreach ($fields as $field) {
            $type = $this->choice(
                "What is the data type of the (( $field field )) ? default is ",
                $this->types,
                6,
                $maxAttempts = null,
                $allowMultipleSelections = false
            );
            $fieldsWithDataType[$field]=$type;
        }
        return $fieldsWithDataType;
    }

    private function checkForeignKeyExists($attributes)
    {
        $results=[];
        $attributes=array_keys($attributes,'key');
        foreach ($attributes as $col){
            $this->line("================ $col Is Foreign Key !!! ====================");
            $result=$this->choice("What type of relationship does the $col column indicate ?",['One To One','One To Many']);
            $results[str_replace('_id','',$col)]=$result=='One To Many'?'hasMany':'hasOne';
        }
        return $results;
    }

    /**
     * Create the service
     *
     * @param string $className
     * @return void
     */
    public function createModel(string $className , array $attributes)
    {
        $nameOfModel = $this->getModelName($className);
        $modelName = $nameOfModel;
        $namespace = $this->getNameSpace($className);
        $stubProperties = [
            "{namespace}"   => $namespace,
            "{modelName}"   => $modelName,
            "{properties}"  => $this->getModelProperty($attributes),
            "{images}"      => $this->getModelImage($attributes,$modelName),
            "{relations}"   => $this->getModelRelation($attributes),
        ];
        // check folder exist
        $folder = str_replace('\\','/', $namespace);
        if (!file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }
        // create file
        new CreateFile(
            $stubProperties,
            $this->getModelPath($className),
            __DIR__ . "/stubs/model.stub"
        );
        $this->line("<info>Created model:</info> {$modelName}");
    }

    private function getModelRelation($attributes)
    {
        $relations_functions = "";
        $foreign_keys = array_keys($attributes,'key');
        foreach ($foreign_keys as $name=>$type)
        {
            $relationName = $type == 'hasMany' ? Str::plural($name) : $name;
            $relations_functions .= "public function ".$relationName."(){
                return \$this->".$type."(".ucfirst($name)."::class);
            }\n";
        }
        return $relations_functions;
    }

    private function getModelProperty($attributes){
        $properties = "/**  \n";
        foreach ($attributes as $name => $type){
            $properties .= "* @property $type $name \n";
        }
        $properties .= "*/ \n";
        return $properties;
    }

    private function getModelImage($attributes, $modelName)
    {
        $image = "";
        $columns_names = array_keys($attributes,'file');
        foreach ($columns_names as $colName) {
            $image .=
                "public function get".ucfirst($colName)."Path(){
                return \$this->$colName != null ? asset('storage/'.\$this->$colName) : null;
            }\n";
            $this->ensureDirectoryExists(storage_path('app/public/'.Str::lower($modelName).'/'.Str::plural($colName)));
        }
        return $image;
    }

    /**
     * Get service path
     *
     * @return string
     */
    private function getModelPath($className)
    {
        return $this->appPath() . "/" .
            config("repository.model_directory") .
            "/$className" . ".php";
    }

    /**
     * Check to make sure if all required directories are available
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function checkIfRequiredDirectoriesExist()
    {
        $this->ensureDirectoryExists(config("repository.model_directory"));
    }

    /**
     * get model name
     * @param $className
     * @return string
     */
    private function getModelName($className):string {
        $explode = explode('/', $className);
        return $explode[array_key_last($explode)];
    }

    /**
     * get namespace
     * @param $className
     * @return string
     */
    private function getNameSpace($className):string {
        return config("repository.model_namespace");
    }
}
