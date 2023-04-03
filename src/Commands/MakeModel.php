<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\CommandTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MakeModel extends Command
{
    use AssistCommand;

    public $signature = 'create:model
        {name : The name of the model }
        {option? : string to generate a single file (migration,request,resource,factory,seeder,controller-api,controller-base,repository,service)}';

    /**
     * @var string
     */
    public $description = 'Create a new model class';

    /**
     * model properties type
     *
     * @var string[]
     */
    private array $types;

    private string $containerType;

    public function __construct()
    {
        parent::__construct();

        $this->types = [
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
            'key',
            RelationsTypeEnum::ManyToMany,
        ];
    }

    /**
     * @return false|void
     *
     * @throws BindingResolutionException
     */
    public function handle()
    {
        $name = $this->argument('name');
        $option = $this->argument('option');

        if (! $name) {
            $this->error('Please specify a valid model');

            return false;
        }

        $paramsString = $this->ask('Enter your params like "name,started_at,..."');

        $attributes = $this->convertToArrayOfAttributes($paramsString);

        $this->containerType = $this->choice('What Is Container Type ?', [CommandTypeEnum::API, CommandTypeEnum::WEB, CommandTypeEnum::BOTH], 0);

        $className = Str::studly($name);

        $this->checkIfRequiredDirectoriesExist();

        $this->createModel($className, $attributes);

        // removing the many-to-many relations from migration attributes
        $fixedAttributes = $attributes;
        $manyToManyAttributes = array_keys($attributes, RelationsTypeEnum::ManyToMany);
        foreach ($fixedAttributes as $attribute => $value) {
            if (in_array($attribute, $manyToManyAttributes)) {
                unset($fixedAttributes[$attribute]);
            }
        }

        foreach ($manyToManyAttributes as $attribute) {
            $this->call('create:pivot', ['table1' => Str::lower($name), 'table2' => $attribute]);
        }

        //call to command base on the option flag
        $result = match ($option) {
            'migration' => $this->call('create:migration', ['name' => $name, 'attributes' => $fixedAttributes]),
            'controller' => $this->call('create:controller', ['name' => $name]) ,
            'request' => $this->call('create:request', ['name' => $name, 'attributes' => $fixedAttributes]),
            'resource' => $this->call('create:resource', ['name' => $name, 'attributes' => $attributes]),
//            'factory' => $this->call('create:factory', ["name" => $name, 'attributes' => $fixedAttributes]),
//            'seeder' => $this->call('create:seeder', ["name" => $name]),
//            'controller-api' => $this->call('create:controller --api', ["name" => $name, 'attributes' => $attributes]),
//            'controller-base' => $this->call('create:controller --base', ["name" => $name, 'attributes' => $attributes]),
//            'repository' => $this->call('create:repository', ["name" => $name]),
//            'service' => $this->call('create:service', ["name" => $name]),
            '', null => 'all',
        };
        if ($result === 'all') {
            $this->call('create:migration', ['name' => $name, 'attributes' => $fixedAttributes]);
//            $this->call('create:factory', ["name" => $name, 'attributes' => $fixedAttributes]);
//            $this->call('create:seeder', ["name" => $name]);
            $this->call('create:request', ['name' => $name, 'attributes' => $fixedAttributes]);
            $this->call('create:resource', ['name' => $name, 'attributes' => $attributes]);
            $this->call('create:controller', ['name' => $name]);
//            $this->call('create:controller --base', ["name" => $name, 'attributes' => $attributes]);
//            $this->call('create:repository', ["name" => $name]);
//            $this->call('create:service', ["name" => $name]);
        }

        $this->info('Migration created successfully.');
    }

    /**
     * convert an array of attributes to array with attributes and their types
     */
    private function convertToArrayOfAttributes($fields): array
    {
        $fields = explode(',', $fields);
        $fieldsWithDataType = [];
        foreach ($fields as $field) {
            $type = $this->choice(
                "What is the data type of the (( $field field )) ? default is ",
                $this->types,
                6,
            );
            $fieldsWithDataType[$field] = $type;
        }

        return $fieldsWithDataType;
    }

    /**
     * this function checking the existence of foreign keys
     */
    private function checkForeignKeyExists($attributes): array
    {
        $results = [];
        $attributes = array_keys($attributes, 'key');
        foreach ($attributes as $col) {
            $this->line("================ $col Is Foreign Key !!! ====================");
            $result = $this->choice("What type of relationship does the $col column indicate ?", ['One To One', 'One To Many']);
            $results[str_replace('_id', '', $col)] = $result == 'One To Many' ? RelationsTypeEnum::HasMany : RelationsTypeEnum::HasOne;
        }

        return $results;
    }

    /**
     * Create the service
     *
     * @throws BindingResolutionException
     */
    public function createModel(string $className, array $attributes): void
    {
        $namespace = $this->getNameSpace();

        $imagesAttribute = $this->getModelImage($attributes, $className);

        $stubProperties = [
            '{namespace}' => $namespace,
            '{modelName}' => $className,
            '{properties}' => $this->getModelProperty($attributes),
            '{images}' => $imagesAttribute['appends'],
            '{imageAttribute}' => $imagesAttribute['image'],
            '{relations}' => $this->getModelRelation($attributes),
            '{scopes}' => $this->boolValuesScope($attributes),
        ];
        // check folder exist
        $folder = str_replace('\\', '/', $namespace);
        if (! file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }
        // create file
        new CreateFile(
            $stubProperties,
            $this->getModelPath($className),
            __DIR__.'/stubs/model.stub'
        );
        $this->line("<info>Created model:</info> $className");
    }

    private function getModelRelation($attributes): string
    {
        $relationsFunctions = '';
        $foreignKeys = $this->checkForeignKeyExists($attributes);
        foreach ($foreignKeys as $name => $type) {
            $relationName = $type == RelationsTypeEnum::HasMany ? Str::plural($name) : $name;
            $relationsFunctions .= '
            public function '.$relationName.'():'.(($type == RelationsTypeEnum::HasMany) ? 'HasMany' : 'HasOne').
                '{
                return $this->'.$type.'('.ucfirst($name)."::class);
             }\n";
        }

        $manyToManyRelations = array_keys($attributes, RelationsTypeEnum::ManyToMany);

        foreach ($manyToManyRelations as $rel) {
            $relationName = $rel;
            $relationsFunctions .= '
            public function '.$relationName.'() : BelongsToMany
            {
                return $this->belongsToMany('.ucfirst(Str::singular($relationName))."::class);
            }\n";
        }

        return $relationsFunctions;
    }

    private function getModelProperty($attributes): string
    {
        $properties = "/**  \n";
        foreach ($attributes as $name => $type) {
            if ($type == RelationsTypeEnum::ManyToMany) {
                $properties .= '* @property BelongsToMany '.$name."\n";
            } else {
                $properties .= "* @property $type $name \n";
            }
        }
        $properties .= "*/ \n";

        return $properties;
    }

    /**
     * @return string[]
     *
     * @throws BindingResolutionException
     */
    #[ArrayShape(['image' => 'string', 'appends' => 'string'])]
    private function getModelImage($attributes, $modelName): array
    {
        $image = '';
        $columnsNames = array_keys($attributes, 'file');
        $appends = '';
        foreach ($columnsNames as $colName) {
            $image .=
                'public function get'.ucfirst(Str::camel(Str::studly($colName)))."Attribute()
                {
                    return \$this->$colName != null ? asset('storage/'.\$this->$colName) : null;
                }\n";

            $appends = "'$colName',";
            $this->ensureDirectoryExists(storage_path('app/public/'.Str::lower($modelName).'/'.Str::plural($colName)));
        }

        return ['image' => $image, 'appends' => $appends];
    }

    /**
     * Get service path
     */
    private function getModelPath($className): string
    {
        return $this->appPath().'/'.
            config('repository.model_directory').
            "/$className".'.php';
    }

    /**
     * Check to make sure if all required directories are available
     *
     * @throws BindingResolutionException
     */
    private function checkIfRequiredDirectoriesExist(): void
    {
        $this->ensureDirectoryExists(config('repository.model_directory'));
    }

    /**
     * get namespace
     */
    private function getNameSpace(): string
    {
        return config('repository.model_namespace');
    }

    public function boolValuesScope($attributes): string
    {
        $booleans = array_keys($attributes, 'boolean');

        $scopes = '';

        foreach ($booleans as $boolCol) {
            $scopes .= 'public function scope'.ucfirst(Str::studly($boolCol))."(\$query)
            {
                return \$query->where('".$boolCol."' , 1);
            } \n";
        }

        return $scopes;
    }
}
