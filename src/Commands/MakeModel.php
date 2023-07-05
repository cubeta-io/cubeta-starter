<?php

namespace Cubeta\CubetaStarter\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\ArrayShape;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;

class MakeModel extends Command
{
    use AssistCommand;

    public $description = 'Create a new model class';

    public $signature = 'create:model
        {name : The name of the model }
        {gui?}
        {attributes?}
        {relations?}
        {actor?}
        {container?}
        {option? : string to generate a single file (migration, request, resource, factory, seeder, repository, service, controller, test, postman-collection)}
        {nullables? : nullable columns}
        {uniques? : unique columns}';

    protected array $relations = [];

    protected bool $useGui = false;

    private array $types = [
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
        'translatable',
    ];

    /**
     * @return void
     *
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $option = $this->argument('option');
        $guiAttributes = $this->argument('attributes') ?? [];
        $nullables = $this->argument('nullables') ?? [];
        $uniques = $this->argument('uniques') ?? [];
        $relations = $this->argument('relations') ?? [];
        $actor = $this->argument('actor') ?? 'none';
        $this->useGui = $this->argument('gui') ?? false;

        if (!$name || empty(trim($name))) {
            $this->error('Invalid input');
            return;
        }

        $modelName = modelNaming($name);

        // handling the usage of the gui
        if ($this->useGui) {
            $this->relations = $relations;
            $this->createModel($modelName, $guiAttributes);
            $this->callAppropriateCommand($name, $option, $actor, $guiAttributes, $nullables, $uniques);
            $this->createPivots($modelName);
            return;
        }

        $paramsString = $this->getModelAttributesFromTheUser();

        $attributes = $this->convertToArrayOfAttributes($paramsString);

        $this->createModel($modelName, $attributes);

        $actor = $this->checkTheActor();

        $this->callAppropriateCommand($name, $option, $actor, $attributes, $nullables, $uniques);

        $this->createPivots($modelName);
    }

    /**
     * create scopes for the model boolean values
     * @param  array  $attributes
     * @return string
     */
    public function boolValuesScope(array $attributes = []): string
    {
        if (!isset($attributes) || count($attributes) == 0) {
            return '';
        }
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
     * call to command base on the option flag
     */
    public function callAppropriateCommand(string $name, $option, string $actor, array $attributes = [], array $nullables = [], array $uniques = []): void
    {
        $container = $this->checkContainer();
        $result = match ($option) {
            'migration' => $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations, 'nullables' => $nullables, 'uniques' => $uniques]),
            'request' => $this->call('create:request', ['name' => $name, 'attributes' => $attributes, 'nullables' => $nullables, 'uniques' => $uniques]),
            'resource' => $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
            'factory' => $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations, 'uniques' => $uniques]),
            'seeder' => $this->call('create:seeder', ['name' => $name]),
            'repository' => $this->call('create:repository', ['name' => $name]),
            'service' => $this->call('create:service', ['name' => $name]),
            'controller' => $this->call('create:controller', ['name' => $name, 'actor' => $actor]),
            'web-controller' => $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes, 'relations' => $this->relations, 'nullables' => $nullables]),
            'test' => $this->call('create:test', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]),
            'postman-collection' => $this->call('create:postman-collection', ['name' => $name, 'attributes' => $attributes]),
            '', null => 'all',
        };

        if ($result === 'all') {
            $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations, 'nullables' => $nullables, 'uniques' => $uniques]);
            $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations, 'uniques' => $uniques]);
            $this->call('create:seeder', ['name' => $name]);
            $this->call('create:request', ['name' => $name, 'attributes' => $attributes, 'nullables' => $nullables, 'uniques' => $uniques]);
            $this->call('create:repository', ['name' => $name]);
            $this->call('create:service', ['name' => $name]);

            if ($container['api']) {
                $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]);
                $this->call('create:controller', ['name' => $name, 'actor' => $actor]);
                $this->call('create:test', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]);
                $this->call('create:postman-collection', ['name' => $name, 'attributes' => $attributes]);
            }

            if ($container['web']) {
                $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes, 'relations' => $this->relations, 'nullables' => $nullables]);
            }
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
        if (!$this->useGui) {
            $container = $this->choice('<info>What is the container type of this model controller</info>', ['api', 'web', 'both'], 'api');
        } else {
            $container = $this->argument('container');
            if (!in_array($container, ['api', 'web', 'both'])) {
                $this->error('Invalid container use one of this strings as an input : [api , web , both]');
                return ['api' => false, 'web' => false];
            }
        }
        return [
            'api' => $container == 'api' || $container == 'both',
            'web' => $container == 'web' || $container == 'both',
        ];
    }

    /**
     * ask the user about the actor of the created model endpoints
     * @return array|string|null
     */
    public function checkTheActor(): array|string|null
    {
        if (file_exists(base_path('app/Enums/RolesPermissionEnum.php'))) {
            if (class_exists('\App\Enums\RolesPermissionEnum')) {
                /** @noinspection PhpUndefinedNamespaceInspection */
                /** @noinspection PhpFullyQualifiedNameUsageInspection */
                $roles = \App\Enums\RolesPermissionEnum::ALLROLES;
                $roles[] = 'none';
                return $this->choice('Who Is The Actor Of this Endpoint ? ', $roles, 'none');
            }
        }

        return 'none';
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createModel(string $className, array $attributes): void
    {
        $modelPath = $this->getModelPath($className);

        if (file_exists($modelPath)) {
            $this->error("{$className} Model Already Exists");
            return;
        }

        /**
         * removing this from here will cause the $this->relations variable to be empty when calling it
         * in the fillArrayKeysMethodsInTheModel() method and that if we're not using the gui
         */
        $modelRelationsFunctions = $this->getModelRelation($attributes, $this->relations);

        $methodsArrayKeys = $this->fillArrayKeysMethodsInTheModel($attributes, $this->relations);

        $useTranslationsTrait = in_array('translatable', $attributes) ? "use HasFactory; \n use \Cubeta\CubetaStarter\Traits\Translations;\n" : "use HasFactory;";

        $stubProperties = [
            '{namespace}' => config('cubeta-starter.model_namespace'),
            '{modelName}' => $className,
            '{relations}' => $modelRelationsFunctions,
            '{properties}' => $this->getModelProperty($attributes, $this->relations),
            '{fileGetter}' => $this->generateGetFilePropertyPathMethod($className, $attributes),
            '{fillable}' => $methodsArrayKeys['fillable'],
            '{filesKeys}' => $methodsArrayKeys['filesKeys'],
            '{scopes}' => $this->boolValuesScope($attributes),
            '{searchableKeys}' => $methodsArrayKeys['searchable'],
            '{searchableRelations}' => $methodsArrayKeys['relationSearchable'],
            '{translatedAttributes}' => $this->getTranslatableModelAttributes($attributes),
            "use HasFactory;" => $useTranslationsTrait,
        ];

        generateFileFromStub($stubProperties, $modelPath, __DIR__ . '/stubs/model.stub');

        $this->formatFile($modelPath);
        $this->info("Created model: {$className}");
    }

    /**
     * create a pivot table if there is a many-to-many relation
     * @param  string $modelName
     * @return void
     */
    public function createPivots(string $modelName): void
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
     * returns methods arrays : fillable = [] , filesKeys() , orderableArray() , searchableArray() , relationsSearchableArray()
     *
     * @return string[]
     */
    #[ArrayShape(['fillable' => 'string', 'filesKeys' => 'string', 'searchable' => 'string', 'relationSearchable' => 'string'])]
    public function fillArrayKeysMethodsInTheModel(array $attributes = [], array $relations = []): array
    {
        $fillable = '';
        $filesKeys = '';
        $searchable = '';
        $relationsSearchable = '';
        if (isset($attributes) && count($attributes) > 0) {
            foreach ($attributes as $attribute => $type) {
                $attribute = columnNaming($attribute);
                $fillable .= "'{$attribute}' ,\n";

                if (in_array($type, ['string', 'text', 'json', 'translatable'])) {
                    $searchable .= "'{$attribute}' , \n";
                }

                if ($type == 'file') {
                    $filesKeys .= "'{$attribute}' ,\n";
                }
            }
        }

        if (isset($relations) && count($relations) > 0) {
            foreach ($relations as $relation => $type) {
                $relationsSearchable .=
                    "'{$relation}' => [
                    //add your {$relation} desired column to be search within
                  ] , \n";
            }
        }

        return [
            'fillable' => $fillable,
            'filesKeys' => $filesKeys,
            'searchable' => $searchable,
            'relationSearchable' => $relationsSearchable,
        ];
    }

    /**
     * get the model attributes from the user
     */
    public function getModelAttributesFromTheUser(): mixed
    {
        $paramsString = $this->ask('Enter your params like "name,started_at,..."');

        while (empty(trim($paramsString))) {
            $this->error('Invalid Input');
            $paramsString = $this->ask('Enter your params like "name,started_at,..."');
        }

        return $paramsString;
    }

    /**
     * get the model attributes for the translatable columns
     * @param  array  $attributes
     * @return string
     */
    public function getTranslatableModelAttributes(array $attributes): string
    {
        $translatableAttributes = array_keys($attributes, 'translatable');
        $result = '';

        if (!count($translatableAttributes)) {
            return '';
        }

        foreach ($translatableAttributes as $attribute) {
            $attributeMethod = Str::camel($attribute);
            $result .= "/**
                        * get the model {$attribute} translated based on the app locale
                        */
                        public function {$attributeMethod}(): \Illuminate\Database\Eloquent\Casts\Attribute
                        {
                            return \Illuminate\Database\Eloquent\Casts\Attribute::make(
                                get: fn (string \$value) => getTranslation(\$value),
                            );
                        } \n";
        }

        return $result;
    }

    /**
     * convert an array of attributes to array with attributes and their types
     */
    private function convertToArrayOfAttributes($fields): array
    {
        $fields = explode(',', $fields);
        $fieldsWithDataType = [];
        foreach ($fields as $field) {
            $field = columnNaming($field);
            $type = $this->choice(
                "What is the data type of the (( {$field} field )) ? default is ",
                $this->types,
                6,
            );
            $fieldsWithDataType[$field] = $type;
        }

        return $fieldsWithDataType;
    }

    /**
     * generate the getFileProperty method for each file typ columns in the model
     * @param $modelName
     * @param  array  $attributes
     * @return string
     */
    private function generateGetFilePropertyPathMethod($modelName, array $attributes = []): string
    {
        if (!isset($attributes) || count($attributes) == 0) {
            return '';
        }

        $file = '';
        $columnsNames = array_keys($attributes, 'file');
        foreach ($columnsNames as $colName) {
            $file .=
                '/**
                 * return the full path of the stored ' . modelNaming($colName) . '
                 * @return string|null
                 */
                 public function get' . modelNaming($colName) . "Path() : ?string
                 {
                     return \$this->{$colName} != null ? asset('storage/'.\$this->{$colName}) : null;
                 }\n";

            ensureDirectoryExists(storage_path('app/public/' . Str::lower($modelName) . '/' . Str::plural($colName)));
        }

        return $file;
    }

    /**
     * return the model path from the config
     * @param  string $className
     * @return string
     */
    private function getModelPath(string $className): string
    {
        $modelDirectory = base_path(config('cubeta-starter.model_path'));
        ensureDirectoryExists($modelDirectory);

        return "{$modelDirectory}/{$className}.php";
    }

    /**
     * generates the PHPDoc properties for the model class
     * @param  array  $attributes
     * @param  array  $relations
     * @return string
     */
    private function getModelProperty(array $attributes = [], array $relations = []): string
    {
        $properties = "/**  \n";

        if (isset($relations) && count($relations) > 0) {
            foreach ($relations as $name => $type) {
                $modelName = modelNaming($name);
                $properties .= "* @property {$modelName} {$name}\n";
            }
        }

        if (isset($attributes) && count($attributes) > 0) {
            foreach ($attributes as $name => $type) {
                if (in_array($type, ['translatable', 'file', 'text'])) {
                    $properties .= "* @property string {$name} \n";
                } elseif ($type == 'key') {
                    $properties .= "* @property integer {$name} \n";
                } else {
                    $properties .= "* @property {$type} {$name} \n";
                }
            }
        }

        $properties .= "*/ \n";

        return $properties;
    }

    /**
     * @param  array  $attributes
     * @param  array  $relations
     * @return string
     */
    private function getModelRelation(array $attributes = [], array $relations = []): string
    {
        $relationsFunctions = '';

        if (isset($attributes) && count($attributes) > 0) {
            $foreignKeys = array_keys($attributes, 'key');

            foreach ($foreignKeys as $value) {
                $relationName = relationFunctionNaming(str_replace('_id', '', $value));

                $relationsFunctions .=
                    'public function ' . $relationName . '():belongsTo
                {
                    return $this->belongsTo(' . modelNaming($relationName) . "::class);
                }\n";

                $this->relations[$relationName] = RelationsTypeEnum::BelongsTo;
            }
        }

        if ($this->useGui) {
            if (isset($relations) && count($relations) > 0) {
                foreach ($relations as $relation => $type) {
                    $relationName = relationFunctionNaming($relation, false);
                    if ($type == RelationsTypeEnum::HasMany) {
                        $relationsFunctions .= '
                public function ' . $relationName . '():hasMany
                {
                    return $this->hasMany(' . modelNaming($relation) . "::class);
                }\n";
                    }

                    if ($type == RelationsTypeEnum::ManyToMany) {
                        $relationsFunctions .= '
                 public function ' . $relationName . '() : BelongsToMany
                 {
                     return $this->belongsToMany(' . modelNaming($relation) . "::class);
                 }\n";
                    }

                }
            } else {
                return $relationsFunctions;
            }
        } else {
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
                public function ' . $relationName . '():hasMany
                {
                    return $this->hasMany(' . modelNaming($table) . "::class);
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
                 public function ' . $relationName . '() : BelongsToMany
                 {
                     return $this->belongsToMany(' . modelNaming($table) . "::class);
                 }\n";

                    $result = $this->choice('Does it has another <fg=red>many to many</fg=red> relation ? ', ['No', 'Yes'], 'No');

                    $this->relations[$relationName] = RelationsTypeEnum::ManyToMany;
                }

                $thereIsManyToMany = $result == 'Yes';
                $decision = 'Yes';
            }
        }

        return $relationsFunctions;
    }
}
