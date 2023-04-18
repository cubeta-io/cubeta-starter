<?php

namespace Cubeta\CubetaStarter\Commands;

use Cubeta\CubetaStarter\CreateFile;
use Cubeta\CubetaStarter\Enums\RelationsTypeEnum;
use Cubeta\CubetaStarter\Traits\AssistCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MakeFactory extends Command
{
    use AssistCommand;

    public $signature = 'create:factory
        {name       : The name of the model }
        {attributes : columns with data types}?
        {relations?  : the model relations}?';

    public $description = 'Create a new factory';

    private array $typeFaker = [
        'integer' => 'fake()->numberBetween(1,2000)',
        'bigInteger' => 'fake()->numberBetween(1,2000)',
        'unsignedBigInteger' => 'fake()->numberBetween(1,2000)',
        'unsignedDouble' => 'fake()->randomFloat(1,2000)',
        'double' => 'fake()->randomFloat(1,2000)',
        'float' => 'fake()->randomFloat(1,2000)',
        'string' => 'fake()->sentence()',
        'text' => 'fake()->text()',
        'json' => "{'" . 'fake()->word()' . "':'" . 'fake()->word()' . "'}",
    ];

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');
        $attributes = $this->argument('attributes');
        $relations = $this->argument('relations');

        $this->createFactory($modelName, $attributes, $relations);
    }

    /**
     * @param $modelName
     * @param array $attributes
     * @param array $relations
     * @return void
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    private function createFactory($modelName, array $attributes, array $relations): void
    {
        $modelName = ucfirst(Str::singular($modelName));

        $factoryName = $this->getFactoryName($modelName);

        $factoryAttributes = $this->generateCols($attributes, $relations);

        $factoryPath = base_path() . '/database/factories/' . $factoryName . '.php';
        if (file_exists($factoryPath)) {
            return;
        }

        $stubProperties = [
            '{class}' => $modelName,
            '{rows}' => $factoryAttributes['rows'],
            '//relationFactories' => $factoryAttributes['relatedFactories'],
        ];

        new CreateFile(
            $stubProperties,
            $this->getFactoryPath($factoryName),
            __DIR__ . '/stubs/factory.stub'
        );
        $this->line("<info>Created factory:</info> {$factoryName}");
    }

    /**
     * @param $modelName
     * @return string
     */
    private function getFactoryName($modelName): string
    {
        return $modelName . 'Factory';
    }

    /**
     * @param array $attributes
     * @param array $relations
     * @return string[]
     */
    #[ArrayShape(['rows' => "string", 'relatedFactories' => "string"])]
    private function generateCols(array $attributes, array $relations): array
    {
        $rows = '';
        $relatedFactories = '';
        foreach ($attributes as $name => $type) {
            if (Str::endsWith($name, '_at')) {
                $rows .= "\t\t\t'$name' => fake()->date(),\n";

                continue;
            }
            if (Str::startsWith($name, 'is_')) {
                $rows .= "\t\t\t'$name' => fake()->boolean(),\n";

                continue;
            }

            if ($type == 'key') {
                $relatedModel = ucfirst(Str::singular(str_replace('_id', '', $name)));
                $rows .= "\t\t\t'$name' => \App\Models\\$relatedModel::factory() ,\n";
            }

            if (array_key_exists($type, $this->typeFaker)) {
                $faker = $this->typeFaker["$type"];
                $rows .= "\t\t\t'$name' => $faker, \n";
            }
        }

        foreach ($relations as $rel => $type) {
            if ($type == RelationsTypeEnum::HasMany || $type == RelationsTypeEnum::ManyToMany) {
                $functionName = 'with' . ucfirst(Str::plural($rel));
                $className = ucfirst(Str::singular($rel));

                $relatedFactories .= "
                public function $functionName(\$count = 1)
                {
                    return \$this->has(\App\Models\\$className::factory(\$count),);
                } \n";
            }
        }

        return ['rows' => $rows, 'relatedFactories' => $relatedFactories];
    }

    /**
     * @param $factoryName
     * @return string
     */
    private function getFactoryPath($factoryName): string
    {
        return $this->appDatabasePath() . '/factories' .
            "/$factoryName" . '.php';
    }
}
