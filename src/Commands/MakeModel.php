<?php

namespace Cubeta\CubetaStarter\Commands;

use App\Enums\RolesPermissionEnum;
use Carbon\Carbon;
use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\CommandTypeEnum;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
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

        if (!$name) {
            $this->error('Please specify a valid model');

            return false;
        }

        $paramsString = $this->ask('Enter your params like "name,started_at,..."');

        $attributes = $this->convertToArrayOfAttributes($paramsString);

        $this->containerType = $this->choice('What Is Container Type ?', [CommandTypeEnum::API, CommandTypeEnum::WEB, CommandTypeEnum::BOTH], 0);

        // configuring the translation case //
        $translationResult = $this->createTranslation($name, $attributes);
        $attributes = $translationResult['normalAttributes'];
        $translatedAttributes = $translationResult['translatedAttributes'];
        //********************************//

        $className = Str::studly($name);

        $this->checkIfRequiredDirectoriesExist();

        $this->createModel($className, $attributes, $translatedAttributes);

        $actor = $this->checkTheActor();

        //call to command base on the option flag
        $result = match ($option) {
            'migration' => $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
            'controller' => $this->call('create:controller', ['name' => $name, 'actor' => $actor]),
            'request' => $this->call('create:request', ['name' => $name, 'attributes' => $attributes]),
            'resource' => $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
            'factory' => $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]),
            'seeder' => $this->call('create:seeder', ['name' => $name]),
            'repository' => $this->call('create:repository', ['name' => $name]),
            'service' => $this->call('create:service', ['name' => $name]),
            'test' => $this->call('create:test', ['name' => $name, 'actor' => $actor]),
            'policy' => $this->call('create:policy', ['name' => $name, 'actor' => $actor]),
            'postman-collection' => $this->call('create:postman-collection', ['name' => $name, 'attributes' => $attributes]),
            '', null => 'all',
        };
        if ($result === 'all') {
            $this->call('create:migration', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]);
            $this->call('create:factory', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]);
            $this->call('create:seeder', ['name' => $name]);
            $this->call('create:request', ['name' => $name, 'attributes' => $attributes]);
            $this->call('create:resource', ['name' => $name, 'attributes' => $attributes, 'relations' => $this->relations]);
            $this->call('create:controller', ['name' => $name, 'actor' => $actor]);
            $this->call('create:repository', ['name' => $name]);
            $this->call('create:service', ['name' => $name]);
            $this->call('create:test', ['name' => $name, 'actor' => $actor]);
            $this->call('create:policy', ['name' => $name, 'actor' => $actor]);
            $this->call('create:postman-collection', ['name' => $name, 'attributes' => $attributes]);
        }

        $modelName = ucfirst(Str::singular($name));
        $manyToManyRelations = array_keys($this->relations, RelationsTypeEnum::ManyToMany);
        foreach ($manyToManyRelations as $relation) {
            $this->call('create:pivot', [
                'table1' => Str::plural(Str::lower($modelName)),
                'table2' => Str::plural(Str::lower($relation)),
            ]);
        }

        $this->line(exec($this->appPath() . '/vendor/bin/pint'));
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
     * Check to make sure if all required directories are available
     *
     * @throws BindingResolutionException
     */
    private function checkIfRequiredDirectoriesExist(): void
    {
        $this->ensureDirectoryExists(config('repository.model_directory'));
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createModel(string $className, array $attributes, array $translatedAttributes = []): void
    {
        $className = ucfirst(Str::singular($className));
        $namespace = $this->getNameSpace();

        $imagesAttribute = $this->getModelImage($attributes, $className);

        $emptyTranslations = empty($translatedAttributes);
        $translatedAttributesAsString = implode(', ', array_keys($translatedAttributes));

        $stubProperties = [
            '{namespace}' => $namespace,
            '{modelName}' => $className,
            '{relations}' => $this->getModelRelation($attributes),
            '{properties}' => $this->getModelProperty($attributes, $this->relations),
            '{images}' => $imagesAttribute['appends'],
            '{imageAttribute}' => $imagesAttribute['image'],
            '{scopes}' => $this->boolValuesScope($attributes),
            '{importStatement}' => $emptyTranslations ? '' : "use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract; \n use Astrotomic\Translatable\Translatable; \n",
            '{implementStatement}' => $emptyTranslations ? '' : 'implements TranslatableContract',
            '{useTrait}' => $emptyTranslations ? '' : 'use Translatable;',
            '{translatedAttributes}' => $emptyTranslations ? '' : $this->fillTranslatedAttributesArray($translatedAttributes),
        ];

        $modelPath = base_path() . '/app/Models/' . $className . '.php';
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
            $this->getModelPath($className),
            __DIR__ . '/stubs/model.stub'
        );
        $this->line("<info>Created model:</info> $className");
    }

    private function getNameSpace(): string
    {
        return config('repository.model_namespace');
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
                'public function get' . ucfirst(Str::camel(Str::studly($colName))) . "Attribute()
                {
                    return \$this->$colName != null ? asset('storage/'.\$this->$colName) : null;
                }\n";

            $appends = "'$colName',";
            $this->ensureDirectoryExists(storage_path('app/public/' . Str::lower($modelName) . '/' . Str::plural($colName)));
        }

        return ['image' => $image, 'appends' => $appends];
    }

    private function getModelRelation($attributes): string
    {
        $relationsFunctions = '';

        $foreignKeys = array_keys($attributes, 'key');

        foreach ($foreignKeys as $key => $value) {

            $result = $this->choice('The ' . $value . ' Column Represent a ', [RelationsTypeEnum::HasOne, RelationsTypeEnum::BelongsTo], RelationsTypeEnum::BelongsTo);

            if ($result == RelationsTypeEnum::HasOne) {

                $relationName = str_replace('_id', '', $value);
                $relationName = lcfirst(Str::singular($relationName));

                $relationsFunctions .= '
                public function ' . $relationName . '():hasOne
                {
                    return $this->hasOne(' . Str::singular(ucfirst($relationName)) . "::class);
                }\n";

                $this->relations[$relationName] = RelationsTypeEnum::HasOne;
            }

            if ($result == RelationsTypeEnum::BelongsTo) {

                $relationName = str_replace('_id', '', $value);
                $relationName = lcfirst(Str::singular($relationName));

                $relationsFunctions .= '
                public function ' . $relationName . '():belongsTo
                {
                    return $this->belongsTo(' . Str::singular(ucfirst($relationName)) . "::class);
                }\n";

                $this->relations[$relationName] = RelationsTypeEnum::BelongsTo;
            }
        }

        $thereIsHasMany = true;
        $decision = 'No';

        while ($thereIsHasMany) {

            $result = 'No';

            if ($decision == 'No') {
                $result = $this->choice('Does this model related with another model by <fg=red>has many</fg=red> relation ?', ['No', 'Yes'], 'No');
            }

            if ($result == 'Yes') {
                $table = $this->ask('What is the name of the related model table ? ');

                $relationName = lcfirst(Str::plural($table));

                $relationsFunctions .= '
                public function ' . $relationName . '():hasMany
                {
                    return $this->hasMany(' . Str::singular(ucfirst($table)) . "::class);
                }\n";

                $decision = $this->choice('Does it has another <fg=red>has many</fg=red> relation ? ', ['No', 'Yes'], 'No');

                $this->relations[$relationName] = RelationsTypeEnum::HasMany;
            }

            $thereIsHasMany = $decision == 'Yes';
        }

        $thereIsManyToMany = true;
        $decision = 'No';

        while ($thereIsManyToMany) {

            $result = 'No';

            if ($decision == 'No') {
                $result = $this->choice('Does this model related with another model by <fg=red>many to many</fg=red> relation ?', ['No', 'Yes'], 'No');
            }

            if ($result == 'Yes') {
                $table = $this->ask('What is the name of the related model table ? ');

                $relationName = lcfirst(Str::plural($table));

                $relationsFunctions .= '
                 public function ' . $relationName . '() : BelongsToMany
                 {
                     return $this->belongsToMany(' . ucfirst(Str::singular($relationName)) . "::class);
                 }\n";

                $decision = $this->choice('Does it has another <fg=red>many to many</fg=red> relation ? ', ['No', 'Yes'], 'No');

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

    private function getModelPath($className): string
    {
        return $this->appPath() . '/' .
            config('repository.model_directory') .
            "/$className" . '.php';
    }

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
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createTranslation($modelName, $attributes): array
    {
        $choice = $this->choice("Does This Model Has Translations ? \n Notice That You Need To Run <fg=red>php artisan cubeta-init</fg=red> And Initialize Translatable Package", ['No', 'Yes'], 'No');

        if ($choice == 'No') {
            return [
                'normalAttributes' => $attributes,
                'translatedAttributes' => [],
            ];
        }

        do {
            $translatedAttributes = $this->ask("Enter The Attributes That's Have Translations :
                                                \n Your Model Attributes :<fg=red>" . implode(',', array_map('trim', array_keys($attributes))) . "</fg=red>
                                                \n Take Care Of Writing The Attributes in The Same Names \n");
            $translatedAttributes = explode(',', $translatedAttributes);
            $intersection = array_intersect(array_keys($attributes), $translatedAttributes);
        } while (!$intersection == $translatedAttributes);

        $translatedAttributesWithTypes = Arr::only($attributes, $translatedAttributes);
        $this->createTranslationMigration($modelName, $translatedAttributesWithTypes);

        return [
            'normalAttributes' => array_diff($attributes, $translatedAttributesWithTypes),
            'translatedAttributes' => $translatedAttributesWithTypes,
        ];
    }

    /**
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function createTranslationMigration($modelName, $attributes): void
    {
        if ($this->checkIfMigrationExists(Str::singular(strtolower($modelName)))) {
            return;
        }


        $date = Carbon::now()->subSecond()->format('Y_m_d_His');
        $migrationName = $date . '_create_' . Str::singular(strtolower($modelName)) . '_translations_table.php';

        $modelLower = Str::singular(Str::lower($modelName));
        $modelNamePlural = Str::plural($modelLower);
        $tableName = $modelLower . '_translations';
        $columns = $this->generateMigrationCols($attributes, []);

        $stubProperties = [
            '{{table}}' => $tableName,
            '{{modelLower}}' => $modelLower,
            '{{modelNamePlural}}' => $modelNamePlural,
            '{{col}}' => $columns,
        ];

        new CreateFile(
            $stubProperties,
            database_path('migrations') . '/' . $migrationName,
            __DIR__ . '/stubs/translation-migration.stub'
        );
    }

    public function fillTranslatedAttributesArray(array $translatedAttribute): string
    {
        $array = 'public array $translatedAttributes = [';
        foreach (array_keys($translatedAttribute) as $item) {
            $array .= "'" . "$item" . "',";
        }
        $array .= '];';

        return $array;
    }
}
