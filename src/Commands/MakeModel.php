<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MakeModel extends Command
{
    use AssistCommand;

    public $signature = 'create:model
        {name : The name of the model }
        {option? : string to generate a single file (migration,request,resource,factory,seeder,controller-api,controller-base,repository,service)}';

    public $description = 'Create a new model class';

    protected array $relations = [];

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
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $name = $this->argument('name');
        $option = $this->argument('option');

        if (! $name || empty(trim($name))) {
            $this->error('Please specify a valid model');

            return false;
        }

        $modelName = modelNaming($name);

        $paramsString = $this->getUserModelAttributes();

        $attributes = $this->convertToArrayOfAttributes($paramsString);

        $this->createModel($modelName, $attributes);

        $actor = $this->checkTheActor();

        $this->callAppropriateCommand($name, $attributes, $option, $actor);

        $this->createPivots($modelName);
    }

    /**
     * convert an array of attributes to array with attributes and their types
     */
    private function convertToArrayOfAttributes($fields): array
    {
        $fields = explode(',', $fields);
        $fieldsWithDataType = [];
        foreach ($fields as $field) {
            $field = Str::snake($field);
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
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createModel(string $className, array $attributes): void
    {
        $modelPath = $this->getModelPath($className);
        if (file_exists($modelPath)) {
            $this->error("$className Model Already Exists");

            return;
        }

        $stubProperties = [
            '{namespace}' => config('repository.model_namespace'),
            '{modelName}' => $className,
            '{relations}' => $this->getModelRelation($attributes),
            '{properties}' => $this->getModelProperty($attributes, $this->relations),
            '{fileGetter}' => $this->getModelImage($attributes, $className),
            '{scopes}' => $this->boolValuesScope($attributes),
        ];

        // create file
        generateFileFromStub($stubProperties, $modelPath, __DIR__.'/stubs/model.stub');

        $this->formatFile($modelPath);
        $this->info("Created model: $className");
    }

    private function getModelImage($attributes, $modelName): string
    {
        $file = '';
        $columnsNames = array_keys($attributes, 'file');
        foreach ($columnsNames as $colName) {
            $file .=
                'public function get'.ucfirst(Str::camel(Str::studly($colName)))."Path()
                {
                    return \$this->$colName != null ? asset('storage/'.\$this->$colName) : null;
                }\n";

            ensureDirectoryExists(storage_path('app/public/'.Str::lower($modelName).'/'.Str::plural($colName)));
        }

        return $file;
    }

    private function getModelRelation($attributes): string
    {
        $relationsFunctions = '';

        $foreignKeys = array_keys($attributes, 'key');

        foreach ($foreignKeys as $key => $value) {

            $result = $this->choice('The '.$value.' Column Represent a ', [RelationsTypeEnum::HasOne, RelationsTypeEnum::BelongsTo], RelationsTypeEnum::BelongsTo);

            if ($result == RelationsTypeEnum::HasOne) {

                $relationName = relationFunctionNaming(str_replace('_id', '', $value));

                $relationsFunctions .= '
                public function '.$relationName.'():hasOne
                {
                    return $this->hasOne('.modelNaming($relationName)."::class);
                }\n";

                $this->relations[$relationName] = RelationsTypeEnum::HasOne;
            }

            if ($result == RelationsTypeEnum::BelongsTo) {

                $relationName = relationFunctionNaming(str_replace('_id', '', $value));

                $relationsFunctions .= '
                public function '.$relationName.'():belongsTo
                {
                    return $this->belongsTo('.modelNaming($relationName)."::class);
                }\n";

                $this->relations[$relationName] = RelationsTypeEnum::BelongsTo;
            }
        }

        $thereIsHasMany = true;
        $decision = 'No';
        $result = 'No';

        while ($thereIsHasMany) {

            if ($decision == 'No') {
                $result = $this->choice('Does this model related with another model by <fg=red>has many</fg=red> relation ?', ['No', 'Yes'], 'No');
            }

            if ($result == 'Yes') {
                $table = $this->ask('What is the name of the related model table ? ');

                while (empty(trim($table))) {
                    $this->error('Invalid Input');
                    $table = $this->ask('What is the name of the related model table ? ');
                }

                $relationName = relationFunctionNaming($table, false);

                $relationsFunctions .= '
                public function '.$relationName.'():hasMany
                {
                    return $this->hasMany('.modelNaming($table)."::class);
                }\n";

                $result = $this->choice('Does it has another <fg=red>has many</fg=red> relation ? ', ['No', 'Yes'], 'No');

                $this->relations[$relationName] = RelationsTypeEnum::HasMany;
            }

            $thereIsHasMany = $result == 'Yes';
            $decision = 'Yes';
        }

        $thereIsManyToMany = true;
        $decision = 'No';
        $result = 'No';

        while ($thereIsManyToMany) {

            if ($decision == 'No') {
                $result = $this->choice('Does this model related with another model by <fg=red>many to many</fg=red> relation ?', ['No', 'Yes'], 'No');
            }

            if ($result == 'Yes') {
                $table = $this->ask('What is the name of the related model table ? ');

                while (empty(trim($table))) {
                    $this->error('Invalid Input');
                    $table = $this->ask('What is the name of the related model table ? ');
                }

                $relationName = relationFunctionNaming($table, false);

                $relationsFunctions .= '
                 public function '.$relationName.'() : BelongsToMany
                 {
                     return $this->belongsToMany('.modelNaming($table)."::class);
                 }\n";

                $result = $this->choice('Does it has another <fg=red>many to many</fg=red> relation ? ', ['No', 'Yes'], 'No');

                $this->relations[$relationName] = RelationsTypeEnum::ManyToMany;
            }

            $thereIsManyToMany = $result == 'Yes';
            $decision = 'Yes';
        }

        return $relationsFunctions;
    }

    private function getModelProperty($attributes, $relations): string
    {
        $properties = "/**  \n";
        foreach ($relations as $name => $type) {
            $modelName = modelNaming($name);
            $properties .= "* @property $modelName $name\n";
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

    private function getModelPath($className): string
    {
        $modelDirectory = base_path(config('repository.model_path'));
        ensureDirectoryExists($modelDirectory);

        return "$modelDirectory/$className.php";
    }

    public function checkTheActor(): array|string|null
    {
        if (class_exists('\App\Enums\RolesPermissionEnum')) {
            $roles = \App\Enums\RolesPermissionEnum::ALLROLES;
            $roles[] = 'none';

            return $this->choice('Who Is The Actor Of this Endpoint ? ', $roles, 'none');
        } else {
            return 'none';
        }
    }

    public function createPivots($modelName): void
    {
        $manyToManyRelations = array_keys($this->relations, RelationsTypeEnum::ManyToMany);
        foreach ($manyToManyRelations as $relation) {
            $this->call('create:pivot', [
                'table1' => $modelName,
                'table2' => $relation,
            ]);
        }
    }

    /**
     * get the container type from the user
     *
     * @return bool[]
     */
    #[ArrayShape(['api' => 'bool', 'web' => 'bool'])]
    public function checkContainer(): array
    {
        $container = $this->choice('<info>What is the container type of this model controller</info>', ['api', 'web', 'both'], 'api');

        return [
            'api' => $container == 'api' || $container == 'both',
            'web' => $container == 'web' || $container == 'both',
        ];
    }

    /**
     * get the model attributes from the user
     */
    public function getUserModelAttributes(): mixed
    {
        $paramsString = $this->ask('Enter your params like "name,started_at,..."');

        while (empty(trim($paramsString))) {
            $this->error('Invalid Input');
            $paramsString = $this->ask('Enter your params like "name,started_at,..."');
        }

        return $paramsString;
    }

    /**
     * call to command base on the option flag
     */
    public function callAppropriateCommand($name, $attributes, $option, $actor): void
    {
        $container = $this->checkContainer();

        $result = match ($option) {
            'migration' => $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
            'request' => $this->call('create:request', ['name' => $name, 'attributes' => $attributes]),
            'resource' => $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
            'factory' => $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
            'seeder' => $this->call('create:seeder', ['name' => $name]),
            'repository' => $this->call('create:repository', ['name' => $name]),
            'service' => $this->call('create:service', ['name' => $name]),
            'controller' => $container['api'] ? $this->call('create:controller', ['name' => $name, 'actor' => $actor]) : null,
            'web-controller' => $container['web'] ? $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes, 'relations' => $this->relations]) : null,
            'test' => $this->call('create:test', ['name' => $name, 'actor' => $actor]),
            'postman-collection' => $this->call('create:postman-collection', ['name' => $name, 'attributes' => $attributes]),
            '', null => 'all',
        };
        if ($result === 'all') {
            $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]);
            $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]);
            $this->call('create:seeder', ['name' => $name]);
            $this->call('create:request', ['name' => $name, 'attributes' => $attributes]);
            $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]);
            $this->call('create:repository', ['name' => $name]);
            $this->call('create:service', ['name' => $name]);
            $this->call('create:test', ['name' => $name, 'actor' => $actor]);
            $this->call('create:postman-collection', ['name' => $name, 'attributes' => $attributes]);

            if ($container['api']) {
                $this->call('create:controller', ['name' => $name, 'actor' => $actor]);
            }
            if ($container['web']) {
                $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]);
            }
        }
    }
}
