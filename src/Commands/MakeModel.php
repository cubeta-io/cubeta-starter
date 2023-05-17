<?php

namespace Cubeta\CubetaStarter\Commands;

use App\Enums\RolesPermissionEnum;
use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
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
     * @throws BindingResolutionException|FileNotFoundException
     */
    public function handle()
    {
        $name = $this->argument('name');
        $option = $this->argument('option');

        if (!$name || empty(trim($name))) {
            $this->error('Please specify a valid model');
            return false;
        }

        $modelName = $this->modelNaming($name);

        $paramsString = $this->getUserModelAttributes();

        $attributes = $this->convertToArrayOfAttributes($paramsString);

        $this->checkIfRequiredDirectoriesExist();

        $this->createModel($modelName, $attributes);

        $actor = $this->checkTheActor();

        $this->callAppropriateCommand($name, $attributes, $option, $actor);

        $this->createPivots($modelName);
    }

    /**
     * convert an array of attributes to array with attributes and their types
     * @param $fields
     * @return array
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
     * Check to make sure if all required directories are available
     *
     * @throws BindingResolutionException
     */
    private function checkIfRequiredDirectoriesExist(): void
    {
        $this->ensureDirectoryExists(config('repository.model_directory'));
    }

    /**
     * @param string $className
     * @param array $attributes
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createModel(string $className, array $attributes): void
    {
        $namespace = $this->getNameSpace();

        $fileGetter = $this->getModelImage($attributes, $className);

        $stubProperties = [
            '{namespace}' => $namespace,
            '{modelName}' => $className,
            '{relations}' => $this->getModelRelation($attributes),
            '{properties}' => $this->getModelProperty($attributes, $this->relations),
            '{fileGetter}' => $fileGetter,
            '{scopes}' => $this->boolValuesScope($attributes),
        ];

        $modelPath = $this->getModelPath($className);
        if (file_exists($modelPath)) {
            return;
        }

        // check folder exist
        $folder = str_replace('\\', '/', $namespace);
        if (!file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }
        // create file
        new CreateFile(
            $stubProperties,
            $modelPath,
            __DIR__ . '/stubs/model.stub'
        );

        $this->formatfile($modelPath);
        $this->line("<info>Created model:</info> $className");
    }

    private function getNameSpace(): string
    {
        return config('repository.model_namespace');
    }

    /**
     * @throws BindingResolutionException
     */
    private function getModelImage($attributes, $modelName): string
    {
        $file = '';
        $columnsNames = array_keys($attributes, 'file');
        foreach ($columnsNames as $colName) {
            $file .=
                'public function get' . ucfirst(Str::camel(Str::studly($colName))) . "Path()
                {
                    return \$this->$colName != null ? asset('storage/'.\$this->$colName) : null;
                }\n";

            $this->ensureDirectoryExists(storage_path('app/public/' . Str::lower($modelName) . '/' . Str::plural($colName)));
        }

        return $file;
    }

    /**
     * @param $attributes
     * @return string
     */
    private function getModelRelation($attributes): string
    {
        $relationsFunctions = '';

        $foreignKeys = array_keys($attributes, 'key');

        foreach ($foreignKeys as $key => $value) {

            $result = $this->choice('The ' . $value . ' Column Represent a ', [RelationsTypeEnum::HasOne, RelationsTypeEnum::BelongsTo], RelationsTypeEnum::BelongsTo);

            if ($result == RelationsTypeEnum::HasOne) {

                $relationName = $this->relationFunctionNaming(str_replace('_id', '', $value));

                $relationsFunctions .= '
                public function ' . $relationName . '():hasOne
                {
                    return $this->hasOne(' . $this->modelNaming($relationName) . "::class);
                }\n";

                $this->relations[$relationName] = RelationsTypeEnum::HasOne;
            }

            if ($result == RelationsTypeEnum::BelongsTo) {

                $relationName = $this->relationFunctionNaming(str_replace('_id', '', $value));

                $relationsFunctions .= '
                public function ' . $relationName . '():belongsTo
                {
                    return $this->belongsTo(' . $this->modelNaming($relationName) . "::class);
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
                    $this->line('<fg=red>Invalid Input</fg=red>');
                    $table = $this->ask('What is the name of the related model table ? ');
                }

                $relationName = $this->relationFunctionNaming($table, false);

                $relationsFunctions .= '
                public function ' . $relationName . '():hasMany
                {
                    return $this->hasMany(' . $this->modelNaming($table) . "::class);
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
                    $this->line('<fg=red>Invalid Input</fg=red>');
                    $table = $this->ask('What is the name of the related model table ? ');
                }

                $relationName = $this->relationFunctionNaming($table, false);

                $relationsFunctions .= '
                 public function ' . $relationName . '() : BelongsToMany
                 {
                     return $this->belongsToMany(' . $this->modelNaming($table) . "::class);
                 }\n";

                $result = $this->choice('Does it has another <fg=red>many to many</fg=red> relation ? ', ['No', 'Yes'], 'No');

                $this->relations[$relationName] = RelationsTypeEnum::ManyToMany;
            }

            $thereIsManyToMany = $result == 'Yes';
            $decision = 'Yes';
        }

        return $relationsFunctions;
    }

    /**
     * @param $attributes
     * @param $relations
     * @return string
     */
    private function getModelProperty($attributes, $relations): string
    {
        $properties = "/**  \n";
        foreach ($relations as $name => $type) {
            if ($type == RelationsTypeEnum::ManyToMany) {
                $properties .= '* @property BelongsToMany ' . $name . "\n";
            } elseif ($type == RelationsTypeEnum::HasMany) {
                $properties .= '* @property HasMany ' . $name . "\n";
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
     * @param $attributes
     * @return string
     */
    public function boolValuesScope($attributes): string
    {
        $booleans = array_keys($attributes, 'boolean');

        $scopes = '';

        foreach ($booleans as $boolCol) {
            $scopes .= 'public function scope' . ucfirst(Str::studly($boolCol)) . "(\$query)
            {
                return \$query->where('" . $boolCol . "' , 1);
            } \n";
        }

        return $scopes;
    }

    /**
     * @param $className
     * @return string
     */
    private function getModelPath($className): string
    {
        return $this->appPath() . '/' .
            config('repository.model_directory') .
            "/$className" . '.php';
    }

    /**
     * @return array|string|null
     */
    public function checkTheActor(): array|string|null
    {
        if (file_exists(base_path() . '/app/Enums/RolesPermissionEnum.php')) {
            $roles = RolesPermissionEnum::ALLROLES;
            $roles[] = 'none';

            return $this->choice('Who Is The Actor Of this Endpoint ? ', $roles, 'none');
        } else {
            return 'none';
        }
    }

    /**
     * @param $modelName
     * @return void
     */
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
     * @return bool[]
     */
    #[ArrayShape(['api' => "bool", 'web' => "bool"])]
    public function checkContainer(): array
    {
        $container = $this->choice("<info>What is the container type of this model controller</info>", ['api', 'web', 'both'], 'api');
        return [
            'api' => $container == 'api' || $container == 'both',
            'web' => $container == 'web' || $container == 'both',
        ];
    }

    /**
     * get the model attributes from the user
     * @return mixed
     */
    public function getUserModelAttributes(): mixed
    {
        $paramsString = $this->ask('Enter your params like "name,started_at,..."');

        while (empty(trim($paramsString))) {
            $this->line('<fg=red>Invalid Input</fg=red>');
            $paramsString = $this->ask('Enter your params like "name,started_at,..."');
        }

        return $paramsString;
    }

    /**
     * call to command base on the option flag
     * @param $name
     * @param $attributes
     * @param $option
     * @param $actor
     * @return void
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
            'web-controller' => $container['web'] ? $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]) : null,
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
            } elseif ($container['web']) {
                $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]);
            }
        }
    }
}
