<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\App\Models\Table\Settings;
use Cubeta\CubetaStarter\Contracts\CodeSniffer;
use Cubeta\CubetaStarter\Enums\ContainerType;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Cubeta\CubetaStarter\Traits\CommandsPrompts;
use Cubeta\CubetaStarter\Traits\SettingsHandler;
use Cubeta\CubetaStarter\Traits\StringsGenerator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MakeModel extends Command
{
    use AssistCommand;
    use SettingsHandler;
    use CommandsPrompts;
    use StringsGenerator;

    public $description = 'Create a new model class';

    public $signature = 'create:model
        {name : The name of the model }
        {attributes?}
        {nullables? : nullable columns}
        {uniques? : unique columns}
        {relations?}
        {actor?}
        {container?}
        {gui?}
        {--migration} {--request} {--resource}
        {--factory} {--seeder} {--repository}
        {--service} {--controller} {--web_controller}
        {--test} {--postman_collection}';
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
        $options = $this->options();
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
            $this->relations = array_merge($this->relations, $relations, $this->getBelongToRelations($guiAttributes));
            Settings::make()->serialize($modelName, $guiAttributes, $relations, $nullables, $uniques);
            $this->createModel($modelName, $guiAttributes, $relations);
            $this->callAppropriateCommand($name, $options, $actor, $guiAttributes, $nullables, $uniques);
            $this->createPivots($modelName);
            return;
        }

        $attributes = $this->getModelAttributesFromPrompts();
        $this->relations = $this->getRelationsFromPrompts();

        $this->relations = array_merge($this->relations, $this->getBelongToRelations($guiAttributes));

        Settings::make()->serialize($modelName, $attributes, $this->relations, $nullables, $uniques);

        $this->createModel($modelName, $attributes, $this->relations);

        $actor = $this->checkTheActorUsingPrompts();

        $this->callAppropriateCommand($name, $options, $actor, $attributes, $nullables, $uniques);

        $this->createPivots($modelName);
    }

    public function getBelongToRelations(array $attributes = []): array
    {
        $attrs = [];
        foreach ($attributes as $attribute => $type) {
            if ($type == 'key') {
                $attrs["$attribute"] = RelationsTypeEnum::BelongsTo;
            }
        }

        return $attrs;
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createModel(string $className, array $attributes, array $relations = []): void
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
        $modelRelationsFunctions = $this->getModelRelation($attributes, $relations);

        $methodsArrayKeys = $this->fillArrayKeysMethodsInTheModel($attributes, $this->relations);

        $useTranslationsTrait = in_array('translatable', $attributes) ? "use HasFactory; \n use \App\Traits\Translations;\n" : "use HasFactory;";

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
            "use HasFactory;" => $useTranslationsTrait,
            '{casts}' => $this->getCastArray($attributes),
        ];

        generateFileFromStub($stubProperties, $modelPath, __DIR__ . '/stubs/model.stub');

        CodeSniffer::make()->setModel($className)->checkForModelsRelations();

        $this->formatFile($modelPath);
        $this->info("Created model: {$className}");
    }

    /**
     * return the model path from the config
     * @param string $className
     * @return string
     */
    private function getModelPath(string $className): string
    {
        $modelDirectory = base_path(config('cubeta-starter.model_path'));
        ensureDirectoryExists($modelDirectory);

        return "{$modelDirectory}/{$className}.php";
    }

    /**
     * @param array $attributes
     * @param array $relations
     * @return string
     */
    private function getModelRelation(array $attributes = [], array $relations = []): string
    {
        $relationsFunctions = '';

        if (isset($attributes) && count($attributes) > 0) {

            $foreignKeys = array_keys($attributes, 'key');

            foreach ($foreignKeys as $value) {
                $relatedModelName = modelNaming(str_replace('_id', '', $value));

                if (file_exists(getModelPath($relatedModelName))) {
                    $relationsFunctions .= $this->belongsToFunction($relatedModelName);
                }
            }

        }

        if (isset($relations) && count($relations) > 0) {
            foreach ($relations as $relation => $type) {

                if (!file_exists(getModelPath(modelNaming($relation)))) {
                    continue;
                }

                if ($type == RelationsTypeEnum::HasMany) {
                    $relationsFunctions .= $this->hasManyFunction($relation);
                }

                if ($type == RelationsTypeEnum::ManyToMany) {
                    $relationsFunctions .= $this->manyToManyFunction($relation);
                }

            }
        }

        return $relationsFunctions;
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
     * generates the PHPDoc properties for the model class
     * @param array $attributes
     * @param array $relations
     * @return string
     */
    private function getModelProperty(array $attributes = [], array $relations = []): string
    {
        $properties = "/**  \n";

        if (isset($relations) && count($relations) > 0) {
            foreach ($relations as $name => $type) {

                if ($type == RelationsTypeEnum::BelongsTo) {
                    continue;
                }

                $modelName = modelNaming($name);
                $properties .= "* @property {$modelName} {$name}\n";
            }
        }

        if (isset($attributes) && count($attributes) > 0) {
            foreach ($attributes as $name => $type) {
                if (in_array($type, ['translatable', 'file', 'text', 'json'])) {
                    $properties .= "* @property string {$name} \n";
                } elseif ($type == 'key') {
                    $properties .= "* @property integer {$name} \n";
                } elseif (in_array($type, ['time', 'date', 'dateTime', 'timestamp'])) {
                    $properties .= "* @property \DateTime {$name} \n";
                } elseif (in_array($type, ['bigInteger', 'unsignedBigInteger', 'unsignedDouble'])) {
                    $properties .= "* @property numeric {$name} \n";
                } else {
                    $properties .= "* @property {$type} {$name} \n";
                }
            }
        }

        $properties .= "*/ \n";

        return $properties;
    }

    /**
     * generate the getFileProperty method for each file typ columns in the model
     * @param         $modelName
     * @param array $attributes
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
     * create scopes for the model boolean values
     * @param array $attributes
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
     * @param array $attributes
     * @return string
     */
    private function getCastArray(array $attributes = []): string
    {
        $casts = '';
        foreach ($attributes as $name => $type) {

            if ($type == 'boolean') {
                $casts .= "'{$name}' => 'boolean' , \n";
            } elseif ($type == 'translatable') {
                $casts .= "'{$name}' => \\App\\Casts\\Translatable::class, \n";
            }

        }

        return $casts;
    }

    /**
     * call to command base on the option flag
     */
    public function callAppropriateCommand(string $name, $options, string $actor, array $attributes = [], array $nullables = [], array $uniques = []): void
    {
        $container = $this->checkContainer();
        $options = array_filter($options, function ($value) {
            return $value !== false && $value !== null;
        });

        if (!count($options)) {
            $result = 'all';
        } else {
            foreach ($options as $key => $option) {
                $result = match ($key) {
                    'migration' => $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations, 'nullables' => $nullables, 'uniques' => $uniques]),
                    'request' => $this->call('create:request', ['name' => $name, 'attributes' => $attributes, 'nullables' => $nullables, 'uniques' => $uniques]),
                    'resource' => $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
                    'factory' => $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations, 'uniques' => $uniques]),
                    'seeder' => $this->call('create:seeder', ['name' => $name]),
                    'repository' => $this->call('create:repository', ['name' => $name]),
                    'service' => $this->call('create:service', ['name' => $name]),
                    'controller' => $this->call('create:controller', ['name' => $name, 'actor' => $actor]),
                    'web_controller' => $this->call('create:web-controller', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes, 'relations' => $this->relations, 'nullables' => $nullables]),
                    'test' => $this->call('create:test', ['name' => $name, 'actor' => $actor, 'attributes' => $attributes]),
                    'postman_collection' => $this->call('create:postman-collection', ['name' => $name, 'attributes' => $attributes]),
                };
            }
        }

        if (!isset($result) || $result === 'all') {
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
            if (!in_array($container, ContainerType::ALL)) {
                $this->error('Invalid container use one of this strings as an input : [api , web , both]');
                return ['api' => false, 'web' => false];
            }
        }
        return [
            'api' => $container == ContainerType::API || $container == ContainerType::BOTH,
            'web' => $container == ContainerType::WEB || $container == ContainerType::BOTH,
        ];
    }

    /**
     * create a pivot table if there is a many-to-many relation
     * @param string $modelName
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
}
