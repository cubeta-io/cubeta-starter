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

    /**
     *  a variable to store the many to many and has many relations to pass them to the resource
     */
    protected array $relations = [];

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

        //call to command base on the option flag
        $result = match ($option) {
            'migration' => $this->call('create:migration', ['name' => $name, 'attributes' => $attributes]),
            'controller' => $this->call('create:controller', ['name' => $name]),
            'request' => $this->call('create:request', ['name' => $name, 'attributes' => $attributes]),
            'resource' => $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
            'factory' => $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
            'seeder' => $this->call('create:seeder', ['name' => $name]),
            'repository' => $this->call('create:repository', ['name' => $name]),
            'service' => $this->call('create:service', ['name' => $name]),
            'test' => $this->call('create:test', ['name' => $name]),
//            'controller-api'    => $this->call('create:controller --api'    , ["name" => $name, 'attributes' => $attributes]),
//            'controller-base'   => $this->call('create:controller --base'   , ["name" => $name, 'attributes' => $attributes]),
            '', null => 'all',
        };
        if ($result === 'all') {
            $this->call('create:migration', ['name' => $name, 'attributes' => $attributes]);
            $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]);
            $this->call('create:seeder', ['name' => $name]);
            $this->call('create:request', ['name' => $name, 'attributes' => $attributes]);
            $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]);
            $this->call('create:controller', ['name' => $name]);
            $this->call('create:repository', ['name' => $name]);
            $this->call('create:service', ['name' => $name]);
            $this->call('create:test', ['name' => $name]);
//            $this->call('create:controller --base'  , ["name" => $name, 'attributes' => $attributes]);
        }

        $modelName = ucfirst(Str::singular($name));
        $manyToManyRelations = array_keys($this->relations, RelationsTypeEnum::ManyToMany);
        foreach ($manyToManyRelations as $relation) {
            $this->call('create:pivot', [
                'table1' => Str::plural(Str::lower($modelName)),
                'table2' => Str::plural(Str::lower($relation)),
            ]);
        }

//        $this->line(exec($this->appPath().'/vendor/bin/pint'));
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
     * Create the service
     *
     * @throws BindingResolutionException
     */
    public function createModel(string $className, array $attributes): void
    {
        $className = ucfirst(Str::singular($className));
        $namespace = $this->getNameSpace();

        $imagesAttribute = $this->getModelImage($attributes, $className);

        $stubProperties = [
            '{namespace}' => $namespace,
            '{modelName}' => $className,
            '{relations}' => $this->getModelRelation($attributes, $className),
            '{properties}' => $this->getModelProperty($attributes, $this->relations),
            '{images}' => $imagesAttribute['appends'],
            '{imageAttribute}' => $imagesAttribute['image'],
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

    private function getModelRelation($attributes, $modelName): string
    {
        $relationsFunctions = '';

        $foreignKeys = array_keys($attributes, 'key');

        foreach ($foreignKeys as $key => $value) {

            $result = $this->choice('The '.$value.' Column Represent a ', [RelationsTypeEnum::HasOne, RelationsTypeEnum::BelongsTo], RelationsTypeEnum::BelongsTo);

            if ($result == RelationsTypeEnum::HasOne) {

                $relationName = str_replace('_id', '', $value);
                $relationName = lcfirst(Str::singular($relationName));

                $relationsFunctions .= '
                public function '.$relationName.'():hasOne
                {
                    return $this->hasOne('.Str::singular(ucfirst($relationName))."::class);
                }\n";

                $this->relations[$relationName] = RelationsTypeEnum::HasOne;
            }

            if ($result == RelationsTypeEnum::BelongsTo) {

                $relationName = str_replace('_id', '', $value);
                $relationName = lcfirst(Str::singular($relationName));

                $relationsFunctions .= '
                public function '.$relationName.'():belongsTo
                {
                    return $this->belongsTo('.Str::singular(ucfirst($relationName))."::class);
                }\n";

                $this->relations[$relationName] = RelationsTypeEnum::BelongsTo;
            }
        }

        $thereIsHasMany = true;
        $decision = 'No';

        while ($thereIsHasMany) {

            if ($decision == 'No') {
                $result = $this->choice('Does this model related with another model by has many relation ?', ['No', 'Yes'], 'No');
            }

            if ($result == 'Yes') {
                $table = $this->ask('What is the name of the related model table ? ');

                $relationName = lcfirst(Str::plural($table));

                $relationsFunctions .= '
                public function '.$relationName.'():hasMany
                {
                    return $this->hasMany('.Str::singular(ucfirst($table))."::class);
                }\n";

                $decision = $this->choice('Does it has another has many relation ? ', ['No', 'Yes'], 'No');

                $this->relations[$relationName] = RelationsTypeEnum::HasMany;
            }

            $thereIsHasMany = $decision == 'Yes';
        }

        $thereIsManyToMany = true;
        $decision = 'No';

        while ($thereIsManyToMany) {

            if ($decision == 'No') {
                $result = $this->choice('Does this model related with another model by many to many relation ?', ['No', 'Yes'], 'No');
            }

            if ($result == 'Yes') {
                $table = $this->ask('What is the name of the related model table ? ');

                $relationName = lcfirst(Str::plural($table));

                $relationsFunctions .= '
                 public function '.$relationName.'() : BelongsToMany
                 {
                     return $this->belongsToMany('.ucfirst(Str::singular($relationName))."::class);
                 }\n";

                $decision = $this->choice('Does it has another many to many relation ? ', ['No', 'Yes'], 'No');

                $this->relations[$relationName] = RelationsTypeEnum::ManyToMany;
            }

            $thereIsManyToMany = $decision == 'Yes';
        }

        return $relationsFunctions;
    }

    private function getModelProperty($attributes, $relations): string
    {
        $properties = "/**  \n";
        foreach ($relations as $name => $type) {
            if ($type == RelationsTypeEnum::ManyToMany) {
                $properties .= '* @property BelongsToMany '.$name."\n";
            } elseif ($type == RelationsTypeEnum::HasMany) {
                $properties .= '* @property HasMany '.$name."\n";
            } elseif ($type == RelationsTypeEnum::BelongsTo) {
                $properties .= "* @property BelongsTo $name \n";
            } elseif ($type == RelationsTypeEnum::HasOne) {
                $properties .= "* @property HasOne $name \n";
            }
        }

        foreach ($attributes as $name => $type) {
            if ($type == 'key') {
                continue;
            }
            $properties .= "* @property $type $name \n";
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
