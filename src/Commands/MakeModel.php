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
            RelationsTypeEnum::HasOne ,
            RelationsTypeEnum::HasMany ,
            RelationsTypeEnum::BelongsTo ,
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

        $fixedAttributes = $this->fixingAttributesForMigrations($attributes) ;

        $manyToManyAttributes = array_keys($attributes , RelationsTypeEnum::ManyToMany) ;
        foreach ($manyToManyAttributes as $attribute) {
            $this->call('create:pivot', ['table1' => Str::lower($name), 'table2' => $attribute]);
        }

        //call to command base on the option flag
        $result = match ($option) {
            'migration'     => $this->call('create:migration'   , ['name' => $name, 'attributes' => $fixedAttributes]),
            'controller'    => $this->call('create:controller'  , ['name' => $name]) ,
            'request'       => $this->call('create:request'     , ['name' => $name, 'attributes' => $fixedAttributes]),
            'resource'      => $this->call('create:resource'    , ['name' => $name, 'attributes' => $attributes]),
            'factory'       => $this->call('create:factory'     , ["name" => $name, 'attributes' => $attributes]),
            'seeder'        => $this->call('create:seeder'      , ["name" => $name]),
//            'controller-api' => $this->call('create:controller --api', ["name" => $name, 'attributes' => $attributes]),
//            'controller-base' => $this->call('create:controller --base', ["name" => $name, 'attributes' => $attributes]),
//            'repository' => $this->call('create:repository', ["name" => $name]),
//            'service' => $this->call('create:service', ["name" => $name]),
            '', null => 'all',
        };
        if ($result === 'all') {
            $this->call('create:migration'          , ['name' => $name, 'attributes' => $fixedAttributes]);
            $this->call('create:factory'            , ["name" => $name, 'attributes' => $attributes]);
            $this->call('create:seeder'             , ["name" => $name]);
            $this->call('create:request'            , ['name' => $name, 'attributes' => $fixedAttributes]);
            $this->call('create:resource'           , ['name' => $name, 'attributes' => $attributes]);
            $this->call('create:controller'         , ['name' => $name]);
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

        // HasOne relations
        $hasOne = array_keys($attributes , RelationsTypeEnum::HasOne);
        foreach ($hasOne as $name) {
            $relationName = str_replace('_id' , '' , $name) ;
            $relationName = lcfirst(Str::singular($relationName));
            $relationsFunctions .= '
            public function '.$relationName.'():hasOne
            {
                return $this->hasOne('.Str::singular(ucfirst($relationName))."::class);
            }\n";
        }

        // HasMany Relations
        $hasMany = array_keys($attributes , RelationsTypeEnum::HasMany) ;
        foreach ($hasMany as $name) {
            $relationName = lcfirst(Str::plural($name));
            $relationsFunctions .= '
            public function '.$relationName.'():hasMany
            {
                return $this->hasMany('.Str::singular(ucfirst($name))."::class);
            }\n";
        }

        // belongsTo relations
        $belongsTo = array_keys($attributes , RelationsTypeEnum::BelongsTo) ;
        foreach ($belongsTo as $name) {
            $relationName = str_replace('_id' , '' , $name) ;
            $relationName = lcfirst(Str::singular($relationName));
            $relationsFunctions .= '
            public function '.$relationName.'():belongsTo
            {
                return $this->belongsTo('.Str::singular(ucfirst($relationName))."::class);
            }\n";
        }

        // ManyToMany Relations
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

    /**
     * some relations don't need a columns in the same table such many to many this function will remove these columns from the attributes array, so they wouldn't be created
     * @param array $attributes
     * @return array
     */
    public function fixingAttributesForMigrations(array $attributes): array
    {

        $fixedAttributes = $attributes;

        $manyToManyAttributes = array_keys($fixedAttributes, RelationsTypeEnum::ManyToMany);
        $hasManyAttributes = array_keys($fixedAttributes, RelationsTypeEnum::HasMany);

        foreach ($fixedAttributes as $attribute => $value) {

            // removing the many-to-many relations from migration attributes
            if (in_array($attribute, $manyToManyAttributes)) {
                unset($fixedAttributes[$attribute]);
            }

            // removing hasMany relations from migration attributes
            else if (in_array($attribute, $hasManyAttributes)) {
                unset($fixedAttributes[$attribute]);
            }
        }

        return $fixedAttributes;
    }
}
